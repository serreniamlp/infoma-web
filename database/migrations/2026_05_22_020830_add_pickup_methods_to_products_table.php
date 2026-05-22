<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('marketplace_products', function (Blueprint $table) {
            // Array metode yang seller aktifkan: ['cod', 'delivery', 'pickup']
            $table->json('pickup_methods')
                ->nullable()
                ->after('location')
                ->comment('Metode pengambilan yang tersedia: cod, delivery, pickup');

            // Alamat pickup jika seller aktifkan metode "ambil sendiri"
            $table->text('pickup_address')
                ->nullable()
                ->after('pickup_methods')
                ->comment('Alamat pengambilan barang — wajib diisi jika pickup tersedia');
        });
    }

    public function down(): void
    {
        Schema::table('marketplace_products', function (Blueprint $table) {
            $table->dropColumn(['pickup_methods', 'pickup_address']);
        });
    }
};