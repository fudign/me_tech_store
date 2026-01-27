<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Смартфоны',
                'slug' => 'smartfony',
                'description' => 'Флагманские и бюджетные смартфоны Xiaomi',
                'meta_title' => 'Смартфоны Xiaomi - купить в Бишкеке',
                'meta_description' => 'Большой выбор смартфонов Xiaomi с официальной гарантией. Доставка по Бишкеку.',
                'is_active' => true,
            ],
            [
                'name' => 'Ноутбуки',
                'slug' => 'noutbuki',
                'description' => 'Ноутбуки Xiaomi для работы и учебы',
                'meta_title' => 'Ноутбуки Xiaomi - официальный магазин',
                'meta_description' => 'Ноутбуки Xiaomi с процессорами Intel и AMD. Официальная гарантия 1 год.',
                'is_active' => true,
            ],
            [
                'name' => 'Умный дом',
                'slug' => 'umnyj-dom',
                'description' => 'Устройства для умного дома Xiaomi',
                'meta_title' => 'Xiaomi умный дом - гаджеты и аксессуары',
                'meta_description' => 'Умные устройства Xiaomi для дома: камеры, датчики, лампочки, розетки.',
                'is_active' => true,
            ],
            [
                'name' => 'Аудио',
                'slug' => 'audio',
                'description' => 'Наушники и колонки Xiaomi',
                'meta_title' => 'Наушники и колонки Xiaomi',
                'meta_description' => 'Беспроводные наушники и портативные колонки Xiaomi с отличным звуком.',
                'is_active' => true,
            ],
            [
                'name' => 'Носимая электроника',
                'slug' => 'nosimaya-elektronika',
                'description' => 'Фитнес-браслеты и смарт-часы',
                'meta_title' => 'Mi Band и смарт-часы Xiaomi',
                'meta_description' => 'Фитнес-браслеты Mi Band и умные часы с мониторингом здоровья.',
                'is_active' => true,
            ],
            [
                'name' => 'ТВ и Медиа',
                'slug' => 'tv-i-media',
                'description' => 'Телевизоры и ТВ-приставки',
                'meta_title' => 'Телевизоры Xiaomi Mi TV',
                'meta_description' => 'Смарт-телевизоры Xiaomi с Android TV и 4K разрешением.',
                'is_active' => true,
            ],
        ];

        foreach ($categories as $categoryData) {
            Category::create($categoryData);
        }
    }
}
