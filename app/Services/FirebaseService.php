<?php

namespace App\Services;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Auth;

class FirebaseService
{
    protected $auth;

    public function __construct()
    {
        $factory = (new Factory)->withServiceAccount(base_path(env('FIREBASE_CREDENTIALS')));
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
        } catch (\Exception $e) {
            throw new \Exception('Invalid Firebase Token: ' . $e->getMessage());
        }
    }
}
