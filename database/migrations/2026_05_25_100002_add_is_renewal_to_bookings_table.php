<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // Flag perpanjangan sewa. Jika true:
            // - Saat approve: available_slots TIDAK dicek dan TIDAK dikurangi
            // - Saat cancel:  available_slots TIDAK ditambah kembali
            $table->boolean('is_renewal')->default(false)->after('renewal_reminder_sent_at');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn('is_renewal');
        });
    }
};
