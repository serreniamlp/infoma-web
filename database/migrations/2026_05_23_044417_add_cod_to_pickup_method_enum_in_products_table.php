<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Tambah 'cod' ke enum pickup_method
        DB::statement("ALTER TABLE marketplace_transactions MODIFY COLUMN pickup_method ENUM('pickup', 'delivery', 'meetup', 'cod') NOT NULL");
    }

    public function down(): void
    {
        // Rollback: hapus 'cod' dari enum
        // Catatan: data yang sudah pickup_method='cod' akan error jika di-rollback
        DB::statement("ALTER TABLE marketplace_transactions MODIFY COLUMN pickup_method ENUM('pickup', 'delivery', 'meetup') NOT NULL");
    }
};