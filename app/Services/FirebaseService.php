<?php

namespace App\Services;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Auth;

class FirebaseService
{
    protected $auth;

    public function isConfigured(): bool
    {
        $credentials = config('services.firebase.credentials');

        return is_string($credentials)
            && $credentials !== ''
            && is_file(
                str_starts_with($credentials, DIRECTORY_SEPARATOR)
                    ? $credentials
                    : base_path($credentials)
            );
    }

    public function __construct()
    {
        $credentials = config('services.firebase.credentials');

        if (! is_string($credentials) || $credentials === '') {
            throw new \RuntimeException('Firebase server credentials are not configured.');
        }

        $credentialsPath = str_starts_with($credentials, DIRECTORY_SEPARATOR)
            ? $credentials
            : base_path($credentials);

        if (! is_file($credentialsPath)) {
            throw new \RuntimeException('Firebase server credentials file was not found.');
        }

        $factory = (new Factory)->withServiceAccount($credentialsPath);
        $this->auth = $factory->createAuth();
    }

    /**
     * Verify Firebase ID Token.
     *
     * @param string $idToken
     * @return \Kreait\Firebase\Auth\UserRecord
     * @throws \Exception
     */
    public function verifyToken($idToken)
    {
        try {
            $verifiedIdToken = $this->auth->verifyIdToken($idToken);
            $uid = $verifiedIdToken->claims()->get('sub');
            return $this->auth->getUser($uid);
        } catch (\Throwable $e) {
            report($e);
            throw new \RuntimeException('Invalid Firebase token.');
        }
    }
}
