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
        // For PostgreSQL: Change is_active from integer to boolean
        Schema::table('products', function (Blueprint $table) {
            // PostgreSQL requires explicit casting
            DB::statement('ALTER TABLE products ALTER COLUMN is_active TYPE BOOLEAN USING is_active::boolean');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to integer if needed
        Schema::table('products', function (Blueprint $table) {
            DB::statement('ALTER TABLE products ALTER COLUMN is_active TYPE INTEGER USING CASE WHEN is_active THEN 1 ELSE 0 END');
        });
    }
};
