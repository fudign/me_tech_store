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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name', 200)->index();
            $table->string('slug', 200)->unique()->index();
            $table->text('description')->nullable();
            $table->text('specifications')->nullable(); // JSON for specs
            $table->integer('price')->unsigned(); // Store in cents to avoid rounding errors
            $table->integer('old_price')->unsigned()->nullable(); // For discounts
            $table->integer('stock')->unsigned()->default(0)->index();
            $table->string('sku', 100)->unique()->nullable()->index();
            $table->string('main_image', 255)->nullable();
            $table->json('images')->nullable(); // Array of additional images
            $table->string('meta_title', 200)->nullable();
            $table->string('meta_description', 300)->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->integer('view_count')->unsigned()->default(0); // For popular products
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
