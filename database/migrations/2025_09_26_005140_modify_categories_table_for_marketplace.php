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
        Schema::table('categories', function (Blueprint $table) {
            // Modify existing enum to include 'marketplace'
            $table->enum('type', ['residence', 'activity', 'marketplace'])->change();
            
            // Add new columns
            $table->foreignId('parent_id')->nullable()->after('type')->constrained('categories')->onDelete('cascade');
            $table->string('icon')->nullable()->after('parent_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            // Drop foreign key constraint
            $table->dropForeign(['parent_id']);
            
            // Drop columns
            $table->dropColumn(['parent_id', 'icon']);
            
            // Revert enum back to original values
            $table->enum('type', ['residence', 'activity'])->change();
        });
    }
};