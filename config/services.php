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
        'token' => env('POSTMARK_TOKEN'),
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

    /*
    |--------------------------------------------------------------------------
    | SSO Hasnur Group
    |--------------------------------------------------------------------------
    */

    'sso' => [
        'base_url' => env('SSO_BASE_URL', 'https://sso.hasnurgroup.com'),
        'login_url' => env('SSO_LOGIN_URL'),
        'verify_url' => env('SSO_VERIFY_URL', 'https://sso.hasnurgroup.com/api/sso/verify-token'),
        'app_id' => env('SSO_APP_ID'),
        'app_secret' => env('SSO_APP_SECRET'),
        'callback_url' => env('SSO_CALLBACK_URL'),
    ],

    /*
    |--------------------------------------------------------------------------
    | WhatsApp API (untuk OTP - fase berikutnya)
    |--------------------------------------------------------------------------
    */

    'whatsapp' => [
        'enabled' => env('WHATSAPP_ENABLED', false),
        'base_url' => env('WHATSAPP_BASE_URL'),
        'token' => env('WHATSAPP_TOKEN'),
        'sender' => env('WHATSAPP_SENDER'),
    ],

];
