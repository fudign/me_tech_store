<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UpdateProductImagesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $imageUrls = [
            'https://2pml6fgury6oq.ok.kimi.link/images/products/redmi-note-13-pro-plus.jpg',
            'https://2pml6fgury6oq.ok.kimi.link/images/products/redmi-13c.jpg',
            'https://2pml6fgury6oq.ok.kimi.link/images/products/iphone-15-pro.jpg',
            'https://2pml6fgury6oq.ok.kimi.link/images/products/samsung-galaxy-s24.jpg',
            'https://2pml6fgury6oq.ok.kimi.link/images/products/xiaomi-14.jpg',
            'https://2pml6fgury6oq.ok.kimi.link/images/products/airpods-pro.jpg',
        ];

        $products = DB::table('products')->select('id')->get();

        foreach ($products as $index => $product) {
            $imageUrl = $imageUrls[$index % count($imageUrls)];
            $imagesJson = json_encode([$imageUrl]);

            DB::table('products')
                ->where('id', $product->id)
                ->update([
                    'main_image' => $imageUrl,
                    'images' => $imagesJson,
                ]);

            $this->command->info("Updated product ID {$product->id} with image: {$imageUrl}");
        }

        $this->command->info('Product images updated successfully!');
    }
}
