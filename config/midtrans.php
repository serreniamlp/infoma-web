<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Midtrans Configuration
    |--------------------------------------------------------------------------
    |
    | Konfigurasi untuk integrasi payment gateway Midtrans.
    | Set MIDTRANS_IS_PRODUCTION=true saat go-live.
    |
    */

    'server_key'    => env('MIDTRANS_SERVER_KEY', ''),
    'client_key'    => env('MIDTRANS_CLIENT_KEY', ''),
    'is_production' => env('MIDTRANS_IS_PRODUCTION', false),
    'is_sanitized'  => env('MIDTRANS_IS_SANITIZED', true),
    'is_3ds'        => env('MIDTRANS_IS_3DS', true),

    /*
    |--------------------------------------------------------------------------
    | Snap URL
    |--------------------------------------------------------------------------
    |
    | URL Snap berbeda antara sandbox dan production.
    |
    */
    'snap_url' => env('MIDTRANS_IS_PRODUCTION', false)
        ? 'https://app.midtrans.com/snap/snap.js'
        : 'https://app.sandbox.midtrans.com/snap/snap.js',

    /*
    |--------------------------------------------------------------------------
    | API Base URL
    |--------------------------------------------------------------------------
    */
    'base_url' => env('MIDTRANS_IS_PRODUCTION', false)
        ? 'https://api.midtrans.com'
        : 'https://api.sandbox.midtrans.com',

];