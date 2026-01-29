<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Product;
use App\Models\Category;
use App\Services\ImageUploadService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;

class ProductController extends Controller
{
    /**
     * Display a listing of products
     */
    public function index()
    {
        $products = Product::with('categories', 'attributes')
            ->latest()
            ->paginate(20);

        return view('admin.products.index', compact('products'));
    }

    /**
     * Show the form for creating a new product
     */
    public function create()
    {
        try {
            $categories = Category::active()->get();
        } catch (\Exception $e) {
            // If cached plan error, try to reconnect and retry
            if (str_contains($e->getMessage(), 'cached plan')) {
                \Illuminate\Support\Facades\DB::reconnect();
                try {
                    \Illuminate\Support\Facades\DB::statement('DEALLOCATE ALL');
                } catch (\Exception $e2) {
                    // Ignore
                }
                $categories = Category::active()->get();
            } else {
                throw $e;
            }
        }

        return view('admin.products.create', compact('categories'));
    }

    /**
     * Store a newly created product
     */
    public function store(StoreProductRequest $request, ImageUploadService $imageUploadService)
    {
        $data = $request->validated();

        // Handle image uploads
        $imagePaths = [];
        $mainImagePath = null;

        // Handle file uploads
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $index => $image) {
                $path = $imageUploadService->upload($image, 'products');
                $imagePaths[] = $path;

                // Set main image
                if ($index == ($request->main_image_index ?? 0)) {
                    $mainImagePath = $path;
                }
            }
        }

        // Handle URL uploads
        if ($request->filled('image_urls')) {
            $urls = array_filter(array_map('trim', explode("\n", $request->image_urls)));
            $startIndex = count($imagePaths);

            foreach ($urls as $index => $url) {
                if (filter_var($url, FILTER_VALIDATE_URL)) {
                    try {
                        $path = $imageUploadService->uploadFromUrl($url, 'products');
                        $imagePaths[] = $path;

                        // Set main image for URL uploads
                        $actualIndex = $startIndex + $index;
                        if ($actualIndex == ($request->main_image_index ?? 0)) {
                            $mainImagePath = $path;
                        }
                    } catch (\Exception $e) {
                        // Log error but continue with other images
                        \Log::error('Failed to upload image from URL: ' . $url . ' - ' . $e->getMessage());
                    }
                }
            }
        }

        // Set main image to first if not specified
        if (!$mainImagePath && count($imagePaths) > 0) {
            $mainImagePath = $imagePaths[0];
        }

        // Prepare product data
        $productData = [
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'price' => $data['price'],
            'old_price' => $data['old_price'] ?? null,
            'slug' => $data['slug'] ?? null,
            'availability_status' => $data['availability_status'],
            'is_active' => filter_var($data['is_active'] ?? false, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false,
            'meta_title' => $data['meta_title'] ?? null,
            'meta_description' => $data['meta_description'] ?? null,
            'images' => $imagePaths,
            'main_image' => $mainImagePath,
        ];

        // Create product
        $product = Product::create($productData);

        // Attach categories
        if (!empty($data['categories'])) {
            $product->categories()->attach($data['categories']);
        }

        // Create attributes
        if (!empty($data['attributes'])) {
            foreach ($data['attributes'] as $attribute) {
                if (!empty($attribute['key']) && !empty($attribute['value'])) {
                    $product->attributes()->create([
                        'key' => $attribute['key'],
                        'value' => $attribute['value'],
                    ]);
                }
            }
        }

        // Flush catalog cache after creating product
        $this->flushCatalogCache();

        return redirect()->route('admin.products.index')
            ->with('success', 'Товар успешно создан');
    }

    /**
     * Show the form for editing the specified product
     */
    public function edit(Product $product)
    {
        $product->load('categories', 'attributes');

        try {
            $categories = Category::active()->get();
        } catch (\Exception $e) {
            // If cached plan error, try to reconnect and retry
            if (str_contains($e->getMessage(), 'cached plan')) {
                \Illuminate\Support\Facades\DB::reconnect();
                try {
                    \Illuminate\Support\Facades\DB::statement('DEALLOCATE ALL');
                } catch (\Exception $e2) {
                    // Ignore
                }
                $categories = Category::active()->get();
            } else {
                throw $e;
            }
        }

        return view('admin.products.edit', compact('product', 'categories'));
    }

    /**
     * Update the specified product
     */
    public function update(UpdateProductRequest $request, Product $product, ImageUploadService $imageUploadService)
    {
        $data = $request->validated();

        // Handle image uploads
        $imagePaths = $product->images ?? [];
        $mainImagePath = $product->main_image;

        // If new images uploaded or URLs provided, delete old ones and replace
        if ($request->hasFile('images') || $request->filled('image_urls')) {
            // Delete old images
            if (!empty($product->images)) {
                $imageUploadService->deleteMultiple($product->images);
            }

            // Upload new images
            $imagePaths = [];

            // Handle file uploads
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $index => $image) {
                    $path = $imageUploadService->upload($image, 'products');
                    $imagePaths[] = $path;

                    // Set main image
                    if ($index == ($request->main_image_index ?? 0)) {
                        $mainImagePath = $path;
                    }
                }
            }

            // Handle URL uploads
            if ($request->filled('image_urls')) {
                $urls = array_filter(array_map('trim', explode("\n", $request->image_urls)));
                $startIndex = count($imagePaths);

                foreach ($urls as $index => $url) {
                    if (filter_var($url, FILTER_VALIDATE_URL)) {
                        try {
                            $path = $imageUploadService->uploadFromUrl($url, 'products');
                            $imagePaths[] = $path;

                            // Set main image for URL uploads
                            $actualIndex = $startIndex + $index;
                            if ($actualIndex == ($request->main_image_index ?? 0)) {
                                $mainImagePath = $path;
                            }
                        } catch (\Exception $e) {
                            // Log error but continue with other images
                            \Log::error('Failed to upload image from URL: ' . $url . ' - ' . $e->getMessage());
                        }
                    }
                }
            }

            // Set main image to first if not specified
            if (!$mainImagePath && count($imagePaths) > 0) {
                $mainImagePath = $imagePaths[0];
            }
        }

        // Update product
        $product->update([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'price' => $data['price'],
            'old_price' => $data['old_price'] ?? null,
            'slug' => $data['slug'] ?? null,
            'availability_status' => $data['availability_status'],
            'is_active' => (bool)($data['is_active'] ?? false), // Cast to boolean for PostgreSQL
            'meta_title' => $data['meta_title'] ?? null,
            'meta_description' => $data['meta_description'] ?? null,
            'images' => $imagePaths,
            'main_image' => $mainImagePath,
        ]);

        // Sync categories
        if (isset($data['categories'])) {
            $product->categories()->sync($data['categories']);
        } else {
            $product->categories()->sync([]);
        }

        // Delete existing attributes and recreate
        $product->attributes()->delete();

        if (!empty($data['attributes'])) {
            foreach ($data['attributes'] as $attribute) {
                if (!empty($attribute['key']) && !empty($attribute['value'])) {
                    $product->attributes()->create([
                        'key' => $attribute['key'],
                        'value' => $attribute['value'],
                    ]);
                }
            }
        }

        // Flush catalog cache after updating product
        $this->flushCatalogCache();

        return redirect()->route('admin.products.index')
            ->with('success', 'Товар успешно обновлен');
    }

    /**
     * Remove the specified product
     */
    public function destroy(Product $product, ImageUploadService $imageUploadService)
    {
        // Delete product images from storage
        if (!empty($product->images)) {
            $imageUploadService->deleteMultiple($product->images);
        }

        // Delete product (cascading will handle attributes)
        $product->delete();

        // Flush catalog cache after deleting product
        $this->flushCatalogCache();

        return redirect()->route('admin.products.index')
            ->with('success', 'Товар успешно удален');
    }

    /**
     * Flush catalog cache after product changes
     */
    protected function flushCatalogCache(): void
    {
        // If cache driver supports tags (Redis/Memcached), flush catalog tag
        $supportsTagging = in_array(config('cache.default'), ['redis', 'memcached']);

        if ($supportsTagging) {
            Cache::tags('catalog')->flush();
        } else {
            // For file/database driver, flush all cache (less efficient but works)
            Cache::flush();
        }

        // Also flush filter options cache
        Cache::forget('filter_memory_options');
        Cache::forget('filter_color_options');
    }
}
