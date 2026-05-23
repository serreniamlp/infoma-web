<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Tambah 'cod_pending' dan 'failed' ke enum payment_status
        // 'failed' ditambahkan sekalian untuk handle payment gagal dari Midtrans
        DB::statement("ALTER TABLE marketplace_transactions MODIFY COLUMN payment_status ENUM('pending', 'paid', 'failed', 'cod_pending', 'refunded') NOT NULL DEFAULT 'pending'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE marketplace_transactions MODIFY COLUMN payment_status ENUM('pending', 'paid', 'refunded') NOT NULL DEFAULT 'pending'");
    }
};