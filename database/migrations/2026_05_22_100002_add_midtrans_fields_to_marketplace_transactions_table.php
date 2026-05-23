<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('marketplace_transactions', function (Blueprint $table) {
            // Token Snap — dipakai frontend untuk buka popup Midtrans
            $table->string('snap_token')->nullable()->after('payment_proof');

            // ID transaksi Midtrans
            $table->string('midtrans_transaction_id')->nullable()->after('snap_token');

            // Tipe pembayaran yang dipilih di Snap
            $table->string('midtrans_payment_type')->nullable()->after('midtrans_transaction_id');
        });
    }

    public function down(): void
    {
        Schema::table('marketplace_transactions', function (Blueprint $table) {
            $table->dropColumn(['snap_token', 'midtrans_transaction_id', 'midtrans_payment_type']);
        });
    }
};
