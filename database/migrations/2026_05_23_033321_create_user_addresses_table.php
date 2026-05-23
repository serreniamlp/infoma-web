<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('user_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Label alamat — contoh: Rumah, Kos, Kantor, dll
            $table->string('label', 50)->default('Rumah');

            // Data penerima
            $table->string('recipient_name', 255);
            $table->string('phone', 20);

            // Alamat lengkap
            $table->text('address');

            // Apakah ini alamat default?
            $table->boolean('is_default')->default(false);

            $table->timestamps();

            $table->index(['user_id', 'is_default']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_addresses');
    }
};