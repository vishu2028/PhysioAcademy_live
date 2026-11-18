<?php

namespace App\Services;

use GuzzleHttp\Psr7\Utils;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Psr\Http\Message\RequestInterface;
use RuntimeException;

class RootFlashTicketClient
{
    /**
     * @param  array<string, mixed>  $payload
     * @param  array<int, array{path: string, name: string}>  $attachments
     * @return array<string, mixed>
     */
    public function createTicket(array $payload, array $attachments, string $idempotencyKey): array
    {
        $request = $this->request($idempotencyKey);
        $streams = [];
        $requestPayload = $payload;

        try {
            if ($attachments !== [] && is_array($requestPayload['metadata'] ?? null)) {
                foreach ($requestPayload['metadata'] as $key => $value) {
                    $request = $request->attach('metadata['.$key.']', (string) $value);
                }

                unset($requestPayload['metadata']);
            }

            foreach ($attachments as $attachment) {
                $path = Storage::disk('local')->path($attachment['path']);
                $stream = fopen($path, 'rb');

                if ($stream === false) {
                    throw new RuntimeException('A stored support attachment could not be opened.');
                }

                $streams[] = $stream;
                $request = $request->attach('attachments[]', $stream, $attachment['name']);
            }

            return $this->data($request->post('/tickets', $requestPayload)->throw());
        } finally {
            foreach ($streams as $stream) {
                if (is_resource($stream)) {
                    fclose($stream);
                }
            }
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function showTicket(string $externalReference): array
    {
        return $this->data(
            $this->request()->get('/tickets/'.rawurlencode($externalReference))->throw(),
        );
    }

    public function downloadAttachment(string $externalReference, string $attachment): Response
    {
        if (! preg_match('/\A(?:ticket|reply-[0-9]+)\z/', $attachment)) {
            throw new RuntimeException('The RootFlash ticket attachment reference is invalid.');
        }

        return $this->request()
            ->get('/tickets/'.rawurlencode($externalReference).'/attachments/'.$attachment)
            ->throw();
    }

    public function isConfigured(): bool
    {
        foreach ([
            'api_url',
            'identifier',
            'key_id',
            'secret',
            'client_reference',
            'client_email',
        ] as $key) {
            if (! filled(config('services.rootflash.tickets.'.$key))) {
                return false;
            }
        }

        $apiUrl = parse_url((string) config('services.rootflash.tickets.api_url'));
        if (! is_array($apiUrl) || ! isset($apiUrl['scheme'], $apiUrl['host'])) {
            return false;
        }

        $scheme = strtolower($apiUrl['scheme']);
        $isLocalHost = in_array(strtolower($apiUrl['host']), ['localhost', '127.0.0.1', '::1'], true);
        if (
            ! in_array($scheme, ['http', 'https'], true)
            || ($scheme !== 'https' && ! $isLocalHost)
            || isset($apiUrl['user'])
            || isset($apiUrl['pass'])
            || isset($apiUrl['query'])
            || isset($apiUrl['fragment'])
            || ! in_array($apiUrl['path'] ?? '', ['', '/'], true)
        ) {
            return false;
        }

        return true;
    }

    private function request(string $idempotencyKey = ''): PendingRequest
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException('The RootFlash ticket integration is not configured.');
        }

        $headers = [
            'X-RootFlash-Website' => config('services.rootflash.tickets.identifier'),
            'X-RootFlash-Key-Id' => config('services.rootflash.tickets.key_id'),
        ];

        if ($idempotencyKey !== '') {
            $headers['Idempotency-Key'] = $idempotencyKey;
        }

        return Http::baseUrl(rtrim((string) config('services.rootflash.tickets.api_url'), '/').'/api/v1/integrations')
            ->acceptJson()
            ->connectTimeout(3)
            ->timeout(15)
            ->withHeaders($headers)
            ->withRequestMiddleware(function (RequestInterface $request) use ($idempotencyKey): RequestInterface {
                $body = $request->getBody();
                if ($body->isSeekable()) {
                    $body->rewind();
                }

                $bodyContents = $body->getContents();
                $request = $request->withBody(Utils::streamFor($bodyContents));
                $timestamp = (string) now()->timestamp;
                $uri = $request->getUri();
                $requestUri = $uri->getPath().($uri->getQuery() !== '' ? '?'.$uri->getQuery() : '');
                $canonicalRequest = implode("\n", [
                    $request->getMethod(),
                    $requestUri,
                    (string) config('services.rootflash.tickets.identifier'),
                    $timestamp,
                    $idempotencyKey,
                    hash('sha256', $bodyContents),
                ]);
                $signature = hash_hmac(
                    'sha256',
                    $canonicalRequest,
                    (string) config('services.rootflash.tickets.secret'),
                );

                return $request
                    ->withHeader('X-RootFlash-Timestamp', $timestamp)
                    ->withHeader('X-RootFlash-Signature', $signature);
            });
    }

    /**
     * @return array<string, mixed>
     */
    private function data(Response $response): array
    {
        $data = $response->json('data');
        if (! is_array($data)) {
            throw new RuntimeException('RootFlash returned an invalid ticket response.');
        }

        return $data;
    }
}
