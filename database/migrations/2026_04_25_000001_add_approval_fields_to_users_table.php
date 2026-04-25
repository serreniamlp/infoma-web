<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Approval seller FJB: pending | approved | rejected
            $table->string('seller_status')->default('none')->after('is_seller');
            // KTP upload path untuk seller
            $table->string('seller_ktp')->nullable()->after('seller_status');
            // Catatan admin saat reject
            $table->text('seller_rejection_reason')->nullable()->after('seller_ktp');

            // Approval provider: pending | approved | rejected
            // (untuk provider_residence dan provider_event)
            $table->string('provider_status')->default('none')->after('seller_rejection_reason');
            $table->text('provider_rejection_reason')->nullable()->after('provider_status');

            // Status aktif/nonaktif user oleh admin
            $table->boolean('is_active')->default(true)->after('provider_rejection_reason');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'seller_status',
                'seller_ktp',
                'seller_rejection_reason',
                'provider_status',
                'provider_rejection_reason',
                'is_active',
            ]);
        });
    }
};
