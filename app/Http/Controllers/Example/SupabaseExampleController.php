<?php

namespace App\Http\Controllers\Example;

use App\Http\Controllers\Controller;
use App\Facades\Supabase;
use Illuminate\Http\Request;

/**
 * Пример использования Supabase в Laravel контроллере
 * Этот контроллер демонстрирует различные операции с Supabase API
 */
class SupabaseExampleController extends Controller
{
    /**
     * Пример 1: Получение всех записей из таблицы
     */
    public function getAllProducts()
    {
        $products = Supabase::select('products', [
            'select' => '*',
            'order' => 'created_at.desc',
        ]);

        return response()->json($products);
    }

    /**
     * Пример 2: Получение записей с фильтрацией
     */
    public function getActiveProducts()
    {
        $products = Supabase::select('products', [
            'select' => 'id,name,price,image',
            'is_active' => 'eq.true',
            'price' => 'gte.100',
            'limit' => 20,
        ]);

        return response()->json($products);
    }

    /**
     * Пример 3: Получение одной записи по ID
     */
    public function getProductById($id)
    {
        $product = Supabase::select('products', [
            'select' => '*',
            'id' => "eq.{$id}",
            'limit' => 1,
        ]);

        if (empty($product)) {
            return response()->json(['error' => 'Product not found'], 404);
        }

        return response()->json($product[0]);
    }

    /**
     * Пример 4: Создание новой записи
     */
    public function createProduct(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
        ]);

        $product = Supabase::insert('products', [
            'name' => $validated['name'],
            'price' => $validated['price'],
            'description' => $validated['description'] ?? null,
            'is_active' => true,
        ]);

        return response()->json($product, 201);
    }

    /**
     * Пример 5: Обновление записи
     */
    public function updateProduct(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'price' => 'sometimes|numeric|min:0',
            'is_active' => 'sometimes|boolean',
        ]);

        $updated = Supabase::update('products', $validated, [
            'id' => "eq.{$id}",
        ]);

        if (empty($updated)) {
            return response()->json(['error' => 'Product not found or not updated'], 404);
        }

        return response()->json($updated[0]);
    }

    /**
     * Пример 6: Удаление записи
     */
    public function deleteProduct($id)
    {
        $deleted = Supabase::delete('products', [
            'id' => "eq.{$id}",
        ]);

        if (!$deleted) {
            return response()->json(['error' => 'Product not found or not deleted'], 404);
        }

        return response()->json(['message' => 'Product deleted successfully']);
    }

    /**
     * Пример 7: Загрузка файла в Supabase Storage
     */
    public function uploadProductImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|max:5120', // 5MB max
        ]);

        $file = $request->file('image');
        $filename = uniqid() . '.' . $file->extension();
        $path = 'products/' . $filename;

        $result = Supabase::uploadFile(
            config('supabase.storage_bucket', 'public'),
            $path,
            $file->get()
        );

        if (!$result) {
            return response()->json(['error' => 'File upload failed'], 500);
        }

        $publicUrl = Supabase::getPublicUrl(
            config('supabase.storage_bucket', 'public'),
            $path
        );

        return response()->json([
            'path' => $path,
            'url' => $publicUrl,
        ]);
    }

    /**
     * Пример 8: Удаление файла из Supabase Storage
     */
    public function deleteProductImage($path)
    {
        $deleted = Supabase::deleteFile(
            config('supabase.storage_bucket', 'public'),
            $path
        );

        if (!$deleted) {
            return response()->json(['error' => 'File not found or not deleted'], 404);
        }

        return response()->json(['message' => 'File deleted successfully']);
    }

    /**
     * Пример 9: Поиск по нескольким полям
     */
    public function searchProducts(Request $request)
    {
        $search = $request->input('q');

        $products = Supabase::select('products', [
            'select' => '*',
            'or' => "(name.ilike.*{$search}*,description.ilike.*{$search}*)",
            'is_active' => 'eq.true',
            'limit' => 50,
        ]);

        return response()->json($products);
    }

    /**
     * Пример 10: Получение с пагинацией
     */
    public function getPaginatedProducts(Request $request)
    {
        $page = $request->input('page', 1);
        $perPage = 20;
        $offset = ($page - 1) * $perPage;

        $products = Supabase::select('products', [
            'select' => '*',
            'limit' => $perPage,
            'offset' => $offset,
            'order' => 'created_at.desc',
        ]);

        return response()->json([
            'data' => $products,
            'page' => $page,
            'per_page' => $perPage,
        ]);
    }

    /**
     * Пример 11: Получение с JOIN (связанные данные)
     */
    public function getProductsWithCategories()
    {
        $products = Supabase::select('products', [
            'select' => 'id,name,price,category:categories(id,name)',
            'is_active' => 'eq.true',
        ]);

        return response()->json($products);
    }

    /**
     * Пример 12: Кастомный запрос
     */
    public function customQuery()
    {
        // Вызов кастомной PostgreSQL функции через Supabase RPC
        $result = Supabase::customQuery('/rest/v1/rpc/get_popular_products', 'POST', [
            'json' => [
                'limit_count' => 10,
            ],
        ]);

        return response()->json($result);
    }
}
