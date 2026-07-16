<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambah kolom fcm_token ke tabel users.
     *
     * fcm_token: token unik per device yang digunakan untuk kirim
     * push notification via Firebase Cloud Messaging (FCM).
     *
     * - Nullable karena: user yang hanya pakai web tidak punya token,
     *   dan user yang belum login di Flutter belum punya token.
     * - Token diperbarui oleh Flutter setiap kali Firebase menerbitkan token baru.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('fcm_token')->nullable()->after('last_seen_at')
                ->comment('Firebase Cloud Messaging device token untuk push notification');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('fcm_token');
        });
    }
};
