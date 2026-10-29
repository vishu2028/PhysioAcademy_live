<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],
    'razorpay' => [
        'key_id' => env('RAZORPAY_KEY_ID'),
        'key_secret' => env('RAZORPAY_KEY_SECRET'),
    ],
    'firebase' => [
        'credentials' => env('FIREBASE_CREDENTIALS'),
        'web' => [
            'apiKey' => env('FIREBASE_WEB_API_KEY', env('VITE_FIREBASE_API_KEY')),
            'authDomain' => env('FIREBASE_WEB_AUTH_DOMAIN', env('VITE_FIREBASE_AUTH_DOMAIN')),
            'projectId' => env('FIREBASE_WEB_PROJECT_ID', env('VITE_FIREBASE_PROJECT_ID')),
            'storageBucket' => env('FIREBASE_WEB_STORAGE_BUCKET', env('VITE_FIREBASE_STORAGE_BUCKET')),
            'messagingSenderId' => env('FIREBASE_WEB_MESSAGING_SENDER_ID', env('VITE_FIREBASE_MESSAGING_SENDER_ID')),
            'appId' => env('FIREBASE_WEB_APP_ID', env('VITE_FIREBASE_APP_ID')),
        ],
    ],

];
