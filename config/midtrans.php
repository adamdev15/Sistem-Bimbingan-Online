<?php

return [

    'server_key' => env('MIDTRANS_SERVER_KEY', ''),

    'client_key' => env('MIDTRANS_CLIENT_KEY', ''),

    'merchant_id' => env('MIDTRANS_MERCHANT_ID', ''),

    'is_production' => filter_var(env('MIDTRANS_IS_PRODUCTION', false), FILTER_VALIDATE_BOOL),

    'is_sanitized' => filter_var(env('MIDTRANS_IS_SANITIZED', true), FILTER_VALIDATE_BOOL),

    'is_3ds' => filter_var(env('MIDTRANS_IS_3DS', true), FILTER_VALIDATE_BOOL),

    /**
     * URL notifikasi HTTP Midtrans (POST). Set di dashboard Midtrans agar mengarah ke route ini.
     * Contoh: https://domainkamu.com/midtrans/notification
     */
    'notification_url' => env('MIDTRANS_CALLBACK_URL', env('APP_URL').'/midtrans/notification'),

    /**
     * URL redirect setelah Snap (opsional; status final tetap dari webhook).
     */
    'finish_redirect_path' => '/pembayaran',

];
