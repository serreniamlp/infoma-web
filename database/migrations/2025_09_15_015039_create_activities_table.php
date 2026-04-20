<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description');
            $table->string('location');
            $table->dateTime('event_date');
            $table->dateTime('registration_deadline');
            $table->decimal('price', 10, 2);
            $table->integer('capacity');
            $table->integer('available_slots');
            $table->json('images');
            $table->enum('discount_type', ['percentage', 'flat'])->nullable();
            $table->decimal('discount_value', 10, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(
                ['provider_id', 'category_id', 'event_date', 'registration_deadline', 'price', 'is_active'],
                'idx_activities_filters'
            );
        });
    }

    public function down(): void {
        Schema::dropIfExists('activities');
    }
};
