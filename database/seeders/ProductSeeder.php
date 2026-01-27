<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Category;
use App\Models\ProductAttribute;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $smartphones = Category::where('name', 'Смартфоны')->first();
        $smartHome = Category::where('name', 'Умный дом')->first();
        $wearables = Category::where('name', 'Носимая электроника')->first();
        $laptops = Category::where('name', 'Ноутбуки')->first();
        $audio = Category::where('name', 'Аудио')->first();

        $products = [
            [
                'name' => 'Xiaomi 14 Pro',
                'slug' => 'xiaomi-14-pro',
                'description' => 'Флагманский смартфон с процессором Snapdragon 8 Gen 3, камерой 50MP и экраном AMOLED 120Hz. Легендарная оптика Leica для профессиональной съемки.',
                'price' => 7999900, // 79,999 KGS in cents
                'old_price' => 8999900,
                'stock' => 15,
                'sku' => 'XM-14-PRO-BK-256',
                'specifications' => [
                    'Процессор' => 'Snapdragon 8 Gen 3',
                    'Память' => '12GB RAM + 256GB ROM',
                    'Камера' => '50MP основная, 50MP телефото, 50MP широкоугольная',
                    'Экран' => '6.73" AMOLED 120Hz',
                    'Батарея' => '4880 mAh, 120W быстрая зарядка',
                ],
                'meta_title' => 'Xiaomi 14 Pro - флагманский смартфон с камерой Leica',
                'meta_description' => 'Купить Xiaomi 14 Pro в Бишкеке. Процессор Snapdragon 8 Gen 3, камера Leica 50MP, экран AMOLED 120Hz.',
                'is_active' => true,
                'view_count' => 120,
                'categories' => [$smartphones->id],
                'attributes' => [
                    ['key' => 'Память', 'value' => '256GB'],
                    ['key' => 'Цвет', 'value' => 'Черный'],
                    ['key' => 'RAM', 'value' => '12GB'],
                ],
            ],
            [
                'name' => 'Redmi Note 13 Pro',
                'slug' => 'redmi-note-13-pro',
                'description' => 'Смартфон среднего класса с отличной камерой 200MP и большой батареей. Идеальное соотношение цены и качества для повседневного использования.',
                'price' => 2999900, // 29,999 KGS
                'stock' => 30,
                'sku' => 'RN-13-PRO-BL-128',
                'specifications' => [
                    'Процессор' => 'Snapdragon 7s Gen 2',
                    'Память' => '8GB RAM + 128GB ROM',
                    'Камера' => '200MP основная',
                    'Экран' => '6.67" AMOLED 120Hz',
                    'Батарея' => '5000 mAh',
                ],
                'meta_title' => 'Redmi Note 13 Pro - камера 200MP по доступной цене',
                'meta_description' => 'Купить Redmi Note 13 Pro в Бишкеке. Камера 200MP, батарея 5000 mAh, экран AMOLED 120Hz.',
                'is_active' => true,
                'view_count' => 85,
                'categories' => [$smartphones->id],
                'attributes' => [
                    ['key' => 'Память', 'value' => '128GB'],
                    ['key' => 'Цвет', 'value' => 'Синий'],
                    ['key' => 'RAM', 'value' => '8GB'],
                ],
            ],
            [
                'name' => 'Redmi 12',
                'slug' => 'redmi-12',
                'description' => 'Бюджетный смартфон для тех, кто ценит качество по доступной цене. Большой экран, хорошая батарея и современный дизайн.',
                'price' => 1499900, // 14,999 KGS
                'stock' => 50,
                'sku' => 'RD-12-BK-128',
                'specifications' => [
                    'Процессор' => 'MediaTek Helio G88',
                    'Память' => '4GB RAM + 128GB ROM',
                    'Камера' => '50MP основная',
                    'Экран' => '6.79" IPS 90Hz',
                    'Батарея' => '5000 mAh',
                ],
                'meta_title' => 'Redmi 12 - доступный смартфон с большим экраном',
                'is_active' => true,
                'view_count' => 45,
                'categories' => [$smartphones->id],
                'attributes' => [
                    ['key' => 'Память', 'value' => '128GB'],
                    ['key' => 'Цвет', 'value' => 'Черный'],
                    ['key' => 'RAM', 'value' => '4GB'],
                ],
            ],
            [
                'name' => 'Mi Smart Band 8',
                'slug' => 'mi-smart-band-8',
                'description' => 'Фитнес-браслет с мониторингом здоровья и спортивной активности. Большой AMOLED экран, водозащита 5 ATM, до 16 дней автономности.',
                'price' => 399900, // 3,999 KGS
                'stock' => 50,
                'sku' => 'MI-BAND-8-BK',
                'specifications' => [
                    'Экран' => '1.62" AMOLED',
                    'Водозащита' => '5 ATM',
                    'Батарея' => 'До 16 дней',
                    'Датчики' => 'Пульс, SpO2, сон',
                ],
                'meta_title' => 'Mi Smart Band 8 - фитнес-браслет с AMOLED экраном',
                'meta_description' => 'Купить Mi Band 8 в Бишкеке. Мониторинг здоровья, 16 дней автономности, водозащита 5 ATM.',
                'is_active' => true,
                'view_count' => 95,
                'categories' => [$wearables->id, $smartHome->id],
                'attributes' => [
                    ['key' => 'Цвет', 'value' => 'Черный'],
                    ['key' => 'Размер экрана', 'value' => '1.62"'],
                ],
            ],
            [
                'name' => 'Xiaomi Watch S3',
                'slug' => 'xiaomi-watch-s3',
                'description' => 'Премиальные смарт-часы с круглым дисплеем и автономностью до 15 дней. Поддержка eSIM, мониторинг здоровья, более 150 спортивных режимов.',
                'price' => 1299900, // 12,999 KGS
                'old_price' => 1499900,
                'stock' => 20,
                'sku' => 'XM-WATCH-S3-SL',
                'specifications' => [
                    'Экран' => '1.43" AMOLED',
                    'Водозащита' => '5 ATM',
                    'Батарея' => 'До 15 дней',
                    'Особенности' => 'eSIM, GPS, NFC',
                ],
                'meta_title' => 'Xiaomi Watch S3 - премиальные смарт-часы с eSIM',
                'is_active' => true,
                'view_count' => 60,
                'categories' => [$wearables->id],
                'attributes' => [
                    ['key' => 'Цвет', 'value' => 'Серебристый'],
                    ['key' => 'Связь', 'value' => 'eSIM'],
                ],
            ],
            [
                'name' => 'Xiaomi Book Pro 14',
                'slug' => 'xiaomi-book-pro-14',
                'description' => 'Ноутбук премиум-класса для профессионалов. Процессор Intel Core i5 12-го поколения, 2.5K дисплей 120Hz, металлический корпус.',
                'price' => 5999900, // 59,999 KGS
                'stock' => 10,
                'sku' => 'XM-BOOK-PRO-14-GY',
                'specifications' => [
                    'Процессор' => 'Intel Core i5-12450H',
                    'Память' => '16GB RAM + 512GB SSD',
                    'Экран' => '14" 2.5K 120Hz',
                    'Графика' => 'Intel Iris Xe',
                    'Батарея' => '56 Wh',
                ],
                'meta_title' => 'Xiaomi Book Pro 14 - профессиональный ноутбук',
                'is_active' => true,
                'view_count' => 35,
                'categories' => [$laptops->id],
                'attributes' => [
                    ['key' => 'Процессор', 'value' => 'Intel Core i5'],
                    ['key' => 'RAM', 'value' => '16GB'],
                    ['key' => 'Память', 'value' => '512GB SSD'],
                ],
            ],
            [
                'name' => 'Redmi Buds 4 Pro',
                'slug' => 'redmi-buds-4-pro',
                'description' => 'TWS наушники с активным шумоподавлением и Hi-Res звуком. Идеальны для музыки, звонков и спорта.',
                'price' => 799900, // 7,999 KGS
                'old_price' => 899900,
                'stock' => 40,
                'sku' => 'RD-BUDS-4-PRO-BK',
                'specifications' => [
                    'ANC' => 'До 43 дБ',
                    'Батарея' => 'До 36 часов с кейсом',
                    'Драйверы' => '11mm динамические',
                    'Защита' => 'IP54',
                ],
                'meta_title' => 'Redmi Buds 4 Pro - TWS наушники с шумоподавлением',
                'is_active' => true,
                'view_count' => 70,
                'categories' => [$audio->id],
                'attributes' => [
                    ['key' => 'Цвет', 'value' => 'Черный'],
                    ['key' => 'Шумоподавление', 'value' => 'Да'],
                ],
            ],
            [
                'name' => 'Mi Robot Vacuum X10+',
                'slug' => 'mi-robot-vacuum-x10-plus',
                'description' => 'Робот-пылесос с автоматической станцией самоочистки. Лазерная навигация, мощность всасывания 4000 Па, работает до 180 минут.',
                'price' => 3999900, // 39,999 KGS
                'stock' => 8,
                'sku' => 'MI-VACUUM-X10-WH',
                'specifications' => [
                    'Всасывание' => '4000 Па',
                    'Навигация' => 'Лазерная LDS',
                    'Батарея' => 'До 180 минут',
                    'Станция' => 'Автоочистка, 2.5L',
                ],
                'meta_title' => 'Mi Robot Vacuum X10+ - умный робот-пылесос',
                'is_active' => true,
                'view_count' => 55,
                'categories' => [$smartHome->id],
                'attributes' => [
                    ['key' => 'Мощность', 'value' => '4000 Pa'],
                    ['key' => 'Цвет', 'value' => 'Белый'],
                ],
            ],
        ];

        foreach ($products as $productData) {
            $categories = $productData['categories'];
            $attributes = $productData['attributes'] ?? [];
            unset($productData['categories'], $productData['attributes']);

            $product = Product::create($productData);
            $product->categories()->attach($categories);

            // Create ProductAttribute records for filtering (Plan 01-03)
            foreach ($attributes as $attr) {
                ProductAttribute::create([
                    'product_id' => $product->id,
                    'key' => $attr['key'],
                    'value' => $attr['value'],
                ]);
            }
        }
    }
}
