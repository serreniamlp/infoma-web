<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('bookable_type');
            $table->unsignedBigInteger('bookable_id');
            $table->string('booking_code')->unique();
            $table->date('check_in_date');
            $table->date('check_out_date');
            $table->json('documents');
            $table->enum('status', ['pending', 'approved', 'rejected', 'completed', 'cancelled'])->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['bookable_type', 'bookable_id', 'status']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('bookings');
    }
};
