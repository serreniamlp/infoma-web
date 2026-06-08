<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // Timestamp kapan notifikasi perpanjang sewa terakhir dikirim.
            // Null = belum pernah dikirim. Digunakan untuk mencegah spam notifikasi.
            $table->timestamp('renewal_reminder_sent_at')->nullable()->after('payment_deadline');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn('renewal_reminder_sent_at');
        });
    }
};
