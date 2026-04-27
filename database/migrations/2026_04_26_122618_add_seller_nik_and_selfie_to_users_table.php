<?php
// database/migrations/2026_04_25_000002_add_seller_nik_and_selfie_to_users_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('seller_nik', 16)->nullable()->after('seller_ktp');
            $table->string('seller_selfie')->nullable()->after('seller_nik');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['seller_nik', 'seller_selfie']);
        });
    }
};