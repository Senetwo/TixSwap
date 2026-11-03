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

    'resend' => [
        'key' => env('RESEND_KEY'),
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
    | M-Pesa (Safaricom Daraja) — STK Push checkout.
    | driver: 'daraja' talks to Safaricom; 'fake' simulates a successful
    | payment instantly and is only allowed outside production.
    */
    'mpesa' => [
        'driver' => env('MPESA_DRIVER', 'fake'),
        'environment' => env('MPESA_ENV', 'sandbox'), // sandbox | production
        'consumer_key' => env('MPESA_CONSUMER_KEY'),
        'consumer_secret' => env('MPESA_CONSUMER_SECRET'),
        'shortcode' => env('MPESA_SHORTCODE'),
        'passkey' => env('MPESA_PASSKEY'),
        // CustomerPayBillOnline (paybill) or CustomerBuyGoodsOnline (till)
        'transaction_type' => env('MPESA_TRANSACTION_TYPE', 'CustomerPayBillOnline'),
        // For till numbers, PartyB is the till; defaults to the shortcode.
        'party_b' => env('MPESA_PARTY_B'),
        // Public HTTPS base URL Safaricom can reach (defaults to APP_URL).
        'callback_base_url' => env('MPESA_CALLBACK_BASE_URL'),
        // Secret path segment so random requests can't hit the callback.
        'callback_secret' => env('MPESA_CALLBACK_SECRET'),
    ],

    /*
    | Marketplace rules.
    */
    'tixswap' => [
        'commission_percent' => (float) env('TIXSWAP_COMMISSION_PERCENT', 10),
        'reservation_minutes' => (int) env('TIXSWAP_RESERVATION_MINUTES', 10),
        'escrow_release_hours_after_event' => (int) env('TIXSWAP_ESCROW_RELEASE_HOURS', 24),
    ],
];
