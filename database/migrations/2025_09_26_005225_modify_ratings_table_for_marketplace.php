<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('ratings', function (Blueprint $table) {
            // Add new columns for marketplace functionality
            $table->foreignId('transaction_id')->nullable()->after('rateable_id')->constrained('marketplace_transactions')->onDelete('set null');
            $table->json('images')->nullable()->after('review');
            $table->boolean('is_recommended')->nullable()->after('images');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ratings', function (Blueprint $table) {
            // Drop foreign key constraint
            $table->dropForeign(['transaction_id']);
            
            // Drop columns
            $table->dropColumn(['transaction_id', 'images', 'is_recommended']);
        });
    }
};