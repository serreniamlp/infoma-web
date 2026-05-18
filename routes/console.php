<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Task Scheduling
|--------------------------------------------------------------------------
|
| Jalankan scheduler di server:
|   * * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
|
| Atau untuk development, jalankan:
|   php artisan schedule:work
|
*/

// Jalankan setiap menit:
// - Auto-cancel booking yang payment_deadline-nya sudah lewat
// - Update booking completed yang check_out_date-nya sudah lewat
// - Nonaktifkan event yang registration_deadline-nya sudah lewat
Schedule::command('bookings:update-status')
    ->everyMinute()
    ->withoutOverlapping()          // hindari overlap jika proses sebelumnya belum selesai
    ->runInBackground();            // jalan di background agar tidak block request lain