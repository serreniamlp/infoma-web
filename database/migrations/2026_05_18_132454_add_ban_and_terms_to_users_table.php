<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // ── 1. Kolom ban & terms di tabel users ───────────────────────────
        Schema::table('users', function (Blueprint $table) {
            // Kapan user menyetujui S&K verifikasi (seller/provider)
            $table->timestamp('terms_accepted_at')->nullable()->after('is_active');

            // Tipe ban: null = tidak di-ban, 'temporary' = sementara, 'permanent' = permanen
            $table->enum('ban_type', ['temporary', 'permanent'])->nullable()->after('terms_accepted_at');

            // Berlaku hingga (null = permanen atau tidak di-ban)
            $table->timestamp('banned_until')->nullable()->after('ban_type');

            // Alasan ban dari admin
            $table->text('ban_reason')->nullable()->after('banned_until');

            // Siapa admin yang mem-ban
            $table->foreignId('banned_by')->nullable()->constrained('users')->nullOnDelete()->after('ban_reason');

            // Kapan di-ban
            $table->timestamp('banned_at')->nullable()->after('banned_by');
        });

        // ── 2. Tabel blacklist identitas (email & NIK) ────────────────────
        // Mencegah akun baru dengan identitas yang sama setelah ban permanen
        Schema::create('banned_identities', function (Blueprint $table) {
            $table->id();
            $table->string('type')->comment('email atau nik');
            $table->string('value')->comment('nilai email/NIK yang di-ban');
            $table->text('reason')->nullable();
            $table->foreignId('banned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete()
                ->comment('user asal yang menyebabkan ban ini');
            $table->timestamps();

            $table->unique(['type', 'value']);
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['banned_by']);
            $table->dropColumn([
                'terms_accepted_at',
                'ban_type',
                'banned_until',
                'ban_reason',
                'banned_by',
                'banned_at',
            ]);
        });

        Schema::dropIfExists('banned_identities');
    }
};