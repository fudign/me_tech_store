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
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained(); // Reference for admin

            // Snapshot data (never changes)
            $table->string('product_name');
            $table->string('product_slug');
            $table->integer('price'); // Price AT PURCHASE TIME (cents)
            $table->integer('quantity');
            $table->integer('subtotal'); // price * quantity

            $table->json('attributes')->nullable(); // e.g., {"Память": "256GB", "Цвет": "Black"}

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
