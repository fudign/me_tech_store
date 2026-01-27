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
        // Note: Products table indexes (price, is_active+created_at, created_at)
        // already exist from previous migration attempts. Verified in database.

        // Note: product_attributes already has indexes on 'key' and ['product_id', 'key']
        // from the initial migration (2026_01_23_180728_create_product_attributes_table.php)

        Schema::table('orders', function (Blueprint $table) {
            // Status index for admin order filtering (02-03 requirement)
            $table->index('status');

            // Created_at index for order list sorting
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Products indexes not dropped as they were created in previous attempts
        // and are essential for performance

        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['created_at']);
        });
    }
};
