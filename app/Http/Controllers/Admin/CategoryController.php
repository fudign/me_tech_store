<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use Illuminate\Support\Facades\Cache;

class CategoryController extends Controller
{
    /**
     * Display a listing of categories
     */
    public function index()
    {
        $categories = Category::withCount('products')
            ->orderBy('name')
            ->paginate(20);

        return view('admin.categories.index', compact('categories'));
    }

    /**
     * Show the form for creating a new category
     */
    public function create()
    {
        return view('admin.categories.create');
    }

    /**
     * Store a newly created category
     */
    public function store(StoreCategoryRequest $request)
    {
        $data = $request->validated();

        Category::create($data);

        // Flush catalog cache (categories affect product display)
        $this->flushCatalogCache();

        return redirect()->route('admin.categories.index')
            ->with('success', 'Категория успешно создана');
    }

    /**
     * Show the form for editing the specified category
     */
    public function edit(Category $category)
    {
        return view('admin.categories.edit', compact('category'));
    }

    /**
     * Update the specified category
     */
    public function update(UpdateCategoryRequest $request, Category $category)
    {
        $data = $request->validated();

        $category->update($data);

        // Flush catalog cache (categories affect product display)
        $this->flushCatalogCache();

        return redirect()->route('admin.categories.index')
            ->with('success', 'Категория успешно обновлена');
    }

    /**
     * Remove the specified category from storage
     */
    public function destroy(Category $category)
    {
        if ($category->products()->count() > 0) {
            return back()->with('error', 'Невозможно удалить категорию с товарами');
        }

        $category->delete();

        // Flush catalog cache (categories affect product display)
        $this->flushCatalogCache();

        return redirect()->route('admin.categories.index')
            ->with('success', 'Категория удалена');
    }

    /**
     * Flush catalog cache after category changes
     */
    protected function flushCatalogCache(): void
    {
        // If cache driver supports tags (Redis/Memcached), flush catalog tag
        $supportsTagging = in_array(config('cache.default'), ['redis', 'memcached']);

        if ($supportsTagging) {
            Cache::tags('catalog')->flush();
        } else {
            // For file/database driver, flush all cache
            Cache::flush();
        }
    }
}
