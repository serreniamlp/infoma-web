<?php
// database/migrations/2026_04_25_000004_add_provider_verification_fields_to_users_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('provider_nik', 16)->nullable()->after('provider_status');
            $table->string('provider_ktp')->nullable()->after('provider_nik');
            $table->string('provider_selfie')->nullable()->after('provider_ktp');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['provider_nik', 'provider_ktp', 'provider_selfie']);
        });
    }
};