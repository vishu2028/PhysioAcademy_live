<?php

namespace App\Services;

use Firebase\JWT\JWK;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use stdClass;
use Throwable;

class AppleIdentityTokenService
{
    private const JWKS_URL = 'https://appleid.apple.com/auth/keys';

    public function isConfigured(): bool
    {
        $redirectUri = config('services.apple.redirect_uri');
        $redirectScheme = is_string($redirectUri) ? parse_url($redirectUri, PHP_URL_SCHEME) : null;

        return filled(config('services.apple.client_id'))
            && filter_var($redirectUri, FILTER_VALIDATE_URL)
            && $redirectScheme === 'https';
    }

    public function verify(string $identityToken, string $expectedNonce): stdClass
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException('Apple sign-in is not configured.');
        }

        try {
            $headers = new stdClass;
            $claims = $this->decode($identityToken, $headers);

            if (($headers->alg ?? null) !== 'RS256' || ! filled($headers->kid ?? null)) {
                throw new RuntimeException('Apple identity token uses an unsupported signing key.');
            }
            $audiences = is_array($claims->aud ?? null)
                ? $claims->aud
                : [$claims->aud ?? null];

            if (
                ($claims->iss ?? null) !== 'https://appleid.apple.com'
                || ! in_array(config('services.apple.client_id'), $audiences, true)
                || ! isset($claims->exp)
                || ! isset($claims->sub)
                || ! is_string($claims->sub)
                || $claims->sub === ''
            ) {
                throw new RuntimeException('Apple identity token claims are invalid.');
            }

            $tokenNonce = (string) ($claims->nonce ?? '');
            $nonceMatches = hash_equals($expectedNonce, $tokenNonce)
                || hash_equals(hash('sha256', $expectedNonce), $tokenNonce);

            if (! $nonceMatches) {
                throw new RuntimeException('Apple identity token nonce is invalid.');
            }

            if (isset($claims->email) && ! $this->isVerifiedEmail($claims->email_verified ?? false)) {
                throw new RuntimeException('Apple email address is not verified.');
            }

            return $claims;
        } catch (Throwable $exception) {
            if ($exception instanceof RuntimeException) {
                throw $exception;
            }

            throw new RuntimeException('Apple identity token could not be verified.', previous: $exception);
        }
    }

    private function decode(string $identityToken, ?stdClass &$headers): stdClass
    {
        $keys = $this->keys();
        try {
            return JWT::decode($identityToken, JWK::parseKeySet($keys), $headers);
        } catch (Throwable $exception) {
            $keyId = $this->keyId($identityToken);
            $hasKey = collect($keys['keys'] ?? [])
                ->contains(fn (array $key): bool => ($key['kid'] ?? null) === $keyId);

            if (! $keyId || $hasKey) {
                throw $exception;
            }

            Cache::forget('apple.identity_jwks');
            $keys = $this->keys();

            return JWT::decode($identityToken, JWK::parseKeySet($keys), $headers);
        }
    }

    private function keyId(string $identityToken): ?string
    {
        $header = explode('.', $identityToken, 2)[0] ?? '';
        $decoded = base64_decode(strtr($header, '-_', '+/'), true);
        $headerObject = is_string($decoded) ? json_decode($decoded) : null;

        return is_object($headerObject) && is_string($headerObject->kid ?? null)
            ? $headerObject->kid
            : null;
    }

    private function keys(): array
    {
        return Cache::remember('apple.identity_jwks', now()->addHours(6), function (): array {
            $response = Http::acceptJson()
                ->timeout(5)
                ->get(self::JWKS_URL)
                ->throw()
                ->json();

            if (! is_array($response) || ! isset($response['keys']) || ! is_array($response['keys'])) {
                throw new RuntimeException('Apple signing keys are unavailable.');
            }

            return $response;
        });
    }

    private function isVerifiedEmail(mixed $value): bool
    {
        return $value === true || $value === 'true';
    }
}
