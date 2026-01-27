<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Services\SeoService;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function __construct(protected SeoService $seoService)
    {
    }
    public function show(Category $category, Request $request)
    {
        // Set SEO tags for category page
        $this->seoService->setSeoTags($category);

        // Validate filter inputs
        $validated = $request->validate([
            'price_min' => 'nullable|integer|min:0',
            'price_max' => 'nullable|integer|min:0',
            'memory' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:50',
        ]);

        $query = $category->products()
            ->where('is_active', true)
            ->with('categories');

        // Price range filter (prices stored in cents)
        if ($request->filled('price_min')) {
            $query->where('price', '>=', $request->price_min * 100);
        }
        if ($request->filled('price_max')) {
            $query->where('price', '<=', $request->price_max * 100);
        }

        // Specification filters via attributes table
        if ($request->filled('memory')) {
            $query->whereHas('attributes', function ($q) use ($request) {
                $q->where('key', 'Память')
                  ->where('value', 'LIKE', '%' . $request->memory . '%');
            });
        }

        if ($request->filled('color')) {
            $query->whereHas('attributes', function ($q) use ($request) {
                $q->where('key', 'Цвет')
                  ->where('value', $request->color);
            });
        }

        $products = $query->paginate(20)->appends($request->except('page'));

        // Get unique values for filter dropdowns
        $memoryOptions = \App\Models\ProductAttribute::where('key', 'Память')
            ->distinct()
            ->pluck('value');

        $colorOptions = \App\Models\ProductAttribute::where('key', 'Цвет')
            ->distinct()
            ->pluck('value');

        return view('storefront.categories.show', compact('category', 'products', 'memoryOptions', 'colorOptions'));
    }
}
