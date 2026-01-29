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
        Schema::table('products', function (Blueprint $table) {
            // Add missing columns that exist in migration but not in DB
            if (!Schema::hasColumn('products', 'old_price')) {
                $table->integer('old_price')->unsigned()->nullable()->after('price');
            }
            if (!Schema::hasColumn('products', 'specifications')) {
                $table->text('specifications')->nullable()->after('description');
            }
            if (!Schema::hasColumn('products', 'main_image')) {
                $table->string('main_image', 255)->nullable()->after('sku');
            }
            if (!Schema::hasColumn('products', 'meta_title')) {
                $table->string('meta_title', 200)->nullable()->after('images');
            }
            if (!Schema::hasColumn('products', 'meta_description')) {
                $table->string('meta_description', 300)->nullable()->after('meta_title');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'old_price',
                'specifications',
                'main_image',
                'meta_title',
                'meta_description'
            ]);
        });
    }
};
