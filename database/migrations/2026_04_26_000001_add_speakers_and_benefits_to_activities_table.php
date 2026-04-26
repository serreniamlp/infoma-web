<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            // Disimpan sebagai JSON: [{"name":"...", "title":"..."}, ...]
            $table->json('speakers')->nullable()->after('images');
            // Disimpan sebagai JSON: ["E-sertifikat", "Networking", ...]
            $table->json('benefits')->nullable()->after('speakers');
        });
    }

    public function down(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->dropColumn(['speakers', 'benefits']);
        });
    }
};
