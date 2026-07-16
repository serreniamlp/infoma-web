<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Firebase Project ID
    |--------------------------------------------------------------------------
    | Project ID dari Firebase Console.
    | Dashboard Firebase → Project Settings → General → Project ID
    */
    'project_id' => env('FIREBASE_PROJECT_ID', ''),

    /*
    |--------------------------------------------------------------------------
    | Service Account Credentials
    |--------------------------------------------------------------------------
    | Path ke file JSON service account yang didownload dari:
    | Firebase Console → Project Settings → Service Accounts → Generate new private key
    |
    | Simpan file JSON di: storage/app/firebase-credentials.json
    | JANGAN commit file ini ke git! Sudah ada di .gitignore.
    */
    'credentials' => env('FIREBASE_CREDENTIALS', storage_path('app/firebase-credentials.json')),

    /*
    |--------------------------------------------------------------------------
    | FCM HTTP v1 API Endpoint
    |--------------------------------------------------------------------------
    | Endpoint resmi FCM HTTP v1 API (bukan legacy).
    | Legacy API sudah deprecated oleh Google per Juni 2024.
    */
    'fcm_endpoint' => 'https://fcm.googleapis.com/v1/projects/{project_id}/messages:send',

    /*
    |--------------------------------------------------------------------------
    | Google OAuth2 Token Endpoint
    |--------------------------------------------------------------------------
    | Endpoint untuk mendapatkan access token dari service account.
    */
    'token_endpoint' => 'https://oauth2.googleapis.com/token',

];
