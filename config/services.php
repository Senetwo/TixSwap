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

        // Automatic seller payouts (B2C). Needs a separate Safaricom approval.
        'b2c_enabled' => (bool) env('MPESA_B2C_ENABLED', false),
        // Send payouts automatically when escrow is released (otherwise an admin clicks "Send").
        'b2c_auto' => (bool) env('MPESA_B2C_AUTO', false),
        'b2c_shortcode' => env('MPESA_B2C_SHORTCODE'),
        'b2c_initiator_name' => env('MPESA_B2C_INITIATOR_NAME'),
        // Encrypted initiator password, generated on the Daraja portal.
        'b2c_security_credential' => env('MPESA_B2C_SECURITY_CREDENTIAL'),
    ],

    /*
    | Notifications. Email uses the normal MAIL_* settings.
    | SMS: SMS_DRIVER=log (default, writes to the log), africastalking
    | (real SMS), or empty to switch SMS off.
    */
    'notifications' => [
        'mail' => (bool) env('NOTIFICATIONS_MAIL', true),
    ],

    'sms' => [
        'driver' => env('SMS_DRIVER', 'log'),
        'username' => env('AFRICASTALKING_USERNAME', 'sandbox'),
        'api_key' => env('AFRICASTALKING_API_KEY'),
        'sender_id' => env('AFRICASTALKING_SENDER_ID'),
    ],

    /*
    | AI proof-of-purchase check (advisory; admins still decide).
    | AI_PROOF_DRIVER: empty/none = off (default), claude = Anthropic API,
    | fake = local testing. Uses ANTHROPIC_API_KEY.
    */
    'ai_proof' => [
        'driver' => env('AI_PROOF_DRIVER', 'none'),
        'model' => env('AI_PROOF_MODEL', 'claude-opus-5-5'),
        'api_key' => env('ANTHROPIC_API_KEY'),
    ],

    /*
    | Marketplace rules.
    */
    'tixswap' => [
        'commission_percent' => (float) env('TIXSWAP_COMMISSION_PERCENT', 10),
        // Highest resale price allowed, as % above face value. Empty = no cap.
        'max_markup_percent' => env('TIXSWAP_MAX_MARKUP_PERCENT', 20) === '' || env('TIXSWAP_MAX_MARKUP_PERCENT', 20) === null
            ? null
            : (float) env('TIXSWAP_MAX_MARKUP_PERCENT', 20),
        'reservation_minutes' => (int) env('TIXSWAP_RESERVATION_MINUTES', 10),
        'escrow_release_hours_after_event' => (int) env('TIXSWAP_ESCROW_RELEASE_HOURS', 24),
        // Sellers with fewer completed sales than this are "new": their
        // payouts are held for extra hours, and they can't be "trusted".
        'trusted_seller_sales' => (int) env('TIXSWAP_TRUSTED_SELLER_SALES', 3),
        'new_seller_extra_hold_hours' => (int) env('TIXSWAP_NEW_SELLER_EXTRA_HOLD_HOURS', 48),
    ],
];
