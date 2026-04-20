<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Application Settings
    |--------------------------------------------------------------------------
    */

    'app_name' => env('APP_NAME', 'INFOMA'),
    'app_description' => 'Aplikasi Informasi Kebutuhan Mahasiswa',

    /*
    |--------------------------------------------------------------------------
    | File Upload Settings
    |--------------------------------------------------------------------------
    */

    'max_file_size' => env('MAX_FILE_SIZE', 2048), // KB
    'allowed_image_types' => ['jpeg', 'png', 'jpg', 'gif'],
    'allowed_document_types' => ['pdf', 'doc', 'docx'],
    'max_images_per_item' => 10,

    /*
    |--------------------------------------------------------------------------
    | Booking Settings
    |--------------------------------------------------------------------------
    */

    'booking_code_prefix' => 'BK',
    'transaction_code_prefix' => 'TR',
    'auto_complete_days' => 1, // Days after checkout to auto-complete

    /*
    |--------------------------------------------------------------------------
    | Pagination Settings
    |--------------------------------------------------------------------------
    */

    'items_per_page' => 12,
    'bookings_per_page' => 10,
    'users_per_page' => 20,

    /*
    |--------------------------------------------------------------------------
    | Rating Settings
    |--------------------------------------------------------------------------
    */

    'min_rating' => 1,
    'max_rating' => 5,
    'allow_rating_edit' => true,

    /*
    |--------------------------------------------------------------------------
    | Notification Settings
    |--------------------------------------------------------------------------
    */

    'send_email_notifications' => env('SEND_EMAIL_NOTIFICATIONS', false),
    'notification_channels' => ['database', 'mail'],

    /*
    |--------------------------------------------------------------------------
    | Cache Settings
    |--------------------------------------------------------------------------
    */

    'cache_ttl' => 3600, // 1 hour
    'cache_featured_items' => true,
    'cache_statistics' => true,
];


