<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // Deadline pembayaran — diisi saat booking disetujui (approved)
            // Null = belum disetujui atau sudah bayar / dibatalkan / ditolak
            $table->timestamp('payment_deadline')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn('payment_deadline');
        });
    }
};