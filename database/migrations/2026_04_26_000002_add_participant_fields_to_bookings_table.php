<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // Field baru untuk pendaftar event
            $table->string('participant_name')->nullable()->after('notes');
            $table->string('participant_email')->nullable()->after('participant_name');
            $table->string('participant_phone')->nullable()->after('participant_email');

            // Jadikan documents nullable (tidak wajib lagi untuk event)
            $table->json('documents')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['participant_name', 'participant_email', 'participant_phone']);
            $table->json('documents')->nullable(false)->change();
        });
    }
};
