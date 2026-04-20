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
        Schema::create('marketplace_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seller_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('category_id')->constrained('product_categories')->onDelete('cascade');
            $table->string('name');
            $table->text('description');
            $table->enum('condition', ['new', 'like_new', 'good', 'fair', 'needs_repair']);
            $table->decimal('price', 12, 2);
            $table->integer('stock_quantity')->default(1);
            $table->string('location');
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->json('images');
            $table->json('tags')->nullable();
            $table->enum('status', ['draft', 'active', 'sold', 'inactive', 'pending_approval'])->default('draft');
            $table->integer('views_count')->default(0);
            $table->timestamp('sold_at')->nullable();
            $table->timestamps();

            // Indexes
            $table->index(['category_id', 'condition', 'price', 'status', 'location'], 'idx_marketplace_filters');
            $table->index(['status', 'created_at', 'views_count'], 'idx_marketplace_search');
            $table->index(['name', 'status'], 'idx_search_products');
            $table->index(['location', 'status'], 'idx_location_search');
            $table->index(['price', 'status', 'category_id'], 'idx_price_range');
            $table->index(['condition', 'status'], 'idx_condition_search');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('marketplace_products');
    }
};