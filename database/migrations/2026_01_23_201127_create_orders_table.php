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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique(); // e.g., ORD-20260123-0001

            // Customer info (guest checkout)
            $table->string('customer_name');
            $table->string('customer_phone');
            $table->text('customer_address');

            // Payment
            $table->enum('payment_method', ['cash', 'online', 'installment']);

            // Status
            $table->enum('status', ['new', 'processing', 'delivering', 'completed'])->default('new');

            // Totals (stored in cents)
            $table->integer('subtotal'); // Sum of items
            $table->integer('total');    // After conditions (future discounts)

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
