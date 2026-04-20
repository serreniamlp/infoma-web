<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('residences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description');
            $table->text('address');
            $table->enum('rental_period', ['monthly', 'yearly']);
            $table->decimal('price', 10, 2);
            $table->integer('capacity');
            $table->integer('available_slots');
            $table->json('facilities');
            $table->json('images');
            $table->enum('discount_type', ['percentage', 'flat'])->nullable();
            $table->decimal('discount_value', 10, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(
                ['provider_id', 'category_id', 'rental_period', 'price', 'is_active'],
                'idx_residences_filters'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('residences');
    }
};
