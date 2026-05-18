<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('marketplace_transactions', function (Blueprint $table) {
            // Deadline upload bukti pembayaran — diisi saat transaksi dibuat
            // (berbeda dengan booking yang diisi saat approved)
            $table->timestamp('payment_deadline')->nullable()->after('cancelled_at');
        });
    }

    public function down(): void
    {
        Schema::table('marketplace_transactions', function (Blueprint $table) {
            $table->dropColumn('payment_deadline');
        });
    }
};