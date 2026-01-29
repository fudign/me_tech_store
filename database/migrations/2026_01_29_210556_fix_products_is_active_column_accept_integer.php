<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Only for PostgreSQL - ensure columns accept both integer and boolean
        if (DB::connection()->getDriverName() === 'pgsql') {
            // For products table - recreate is_active to properly handle conversions
            DB::statement('ALTER TABLE products ALTER COLUMN is_active TYPE BOOLEAN USING (is_active::text::boolean)');
            DB::statement('ALTER TABLE products ALTER COLUMN is_active SET DEFAULT true');

            // For categories table
            DB::statement('ALTER TABLE categories ALTER COLUMN is_active TYPE BOOLEAN USING (is_active::text::boolean)');
            DB::statement('ALTER TABLE categories ALTER COLUMN is_active SET DEFAULT true');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Nothing to reverse
    }
};
