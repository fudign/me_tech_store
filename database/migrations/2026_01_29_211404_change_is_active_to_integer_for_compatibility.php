<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Change is_active from boolean to integer to avoid PostgreSQL strict type checking
     */
    public function up(): void
    {
        if (DB::connection()->getDriverName() === 'pgsql') {
            // Products table - drop default first, then change type, then set new default
            DB::statement('ALTER TABLE products ALTER COLUMN is_active DROP DEFAULT');
            DB::statement('ALTER TABLE products ALTER COLUMN is_active TYPE INTEGER USING CASE WHEN is_active THEN 1 ELSE 0 END');
            DB::statement('ALTER TABLE products ALTER COLUMN is_active SET DEFAULT 1');

            // Categories table
            DB::statement('ALTER TABLE categories ALTER COLUMN is_active DROP DEFAULT');
            DB::statement('ALTER TABLE categories ALTER COLUMN is_active TYPE INTEGER USING CASE WHEN is_active THEN 1 ELSE 0 END');
            DB::statement('ALTER TABLE categories ALTER COLUMN is_active SET DEFAULT 1');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::connection()->getDriverName() === 'pgsql') {
            // Convert back to boolean
            DB::statement('ALTER TABLE products ALTER COLUMN is_active TYPE BOOLEAN USING CASE WHEN is_active = 1 THEN true ELSE false END');
            DB::statement('ALTER TABLE categories ALTER COLUMN is_active TYPE BOOLEAN USING CASE WHEN is_active = 1 THEN true ELSE false END');
        }
    }
};
