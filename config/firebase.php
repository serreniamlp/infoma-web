<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Firebase Project ID
    |--------------------------------------------------------------------------
    | Project ID dari Firebase Console.
    | Firebase Console → Project Settings → General → Project ID
    */
    'project_id' => env('FIREBASE_PROJECT_ID', ''),

    /*
    |--------------------------------------------------------------------------
    | Service Account Credentials
    |--------------------------------------------------------------------------
    | Path ke file JSON service account yang didownload dari:
    | Firebase Console → Project Settings → Service Accounts → Generate new private key
    |
    | Simpan file JSON ke: storage/app/firebase-credentials.json
    | JANGAN commit file ini ke git — sudah ada di .gitignore.
    */
    'credentials' => env('FIREBASE_CREDENTIALS', storage_path('app/firebase-credentials.json')),

    /*
    |--------------------------------------------------------------------------
    | FCM HTTP v1 API Endpoint
    |--------------------------------------------------------------------------
    | Endpoint resmi FCM HTTP v1 API (bukan legacy API yang sudah deprecated).
    */
    'fcm_endpoint' => 'https://fcm.googleapis.com/v1/projects/{project_id}/messages:send',

    /*
    |--------------------------------------------------------------------------
    | Google OAuth2 Token Endpoint
    |--------------------------------------------------------------------------
    */
    'token_endpoint' => 'https://oauth2.googleapis.com/token',

];
