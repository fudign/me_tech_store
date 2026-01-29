<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\SeoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ProductController extends Controller
{
    public function __construct(protected SeoService $seoService)
    {
    }
    public function index(Request $request)
    {
        // Validate filter inputs
        $validated = $request->validate([
            'price_min' => 'nullable|integer|min:0',
            'price_max' => 'nullable|integer|min:0',
            'memory' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:50',
        ]);

        // Build cache key from filters and pagination
        $page = $request->get('page', 1);
        $filters = $request->only(['price_min', 'price_max', 'memory', 'color']);
        $cacheKey = 'products_' . md5(json_encode(array_merge($filters, ['page' => $page])));

        // Cache products query for 6 hours (invalidated on admin updates)
        // Note: Cache tags require Redis/Memcached. Fallback to simple cache for file driver.
        $cacheDriver = config('cache.default');
        $cacheTTL = now()->addHours(6);

        // Database driver also doesn't support tags - only Redis/Memcached do
        $supportsTagging = in_array($cacheDriver, ['redis', 'memcached']);

        $products = $supportsTagging
            ? Cache::tags('catalog')->remember($cacheKey, $cacheTTL, fn() => $this->getFilteredProducts($request))
            : Cache::remember($cacheKey, $cacheTTL, fn() => $this->getFilteredProducts($request));

        // Get unique values for filter dropdowns (cached separately)
        $memoryOptions = Cache::remember('filter_memory_options', now()->addDay(), function () {
            return \App\Models\ProductAttribute::where('key', 'Память')
                ->distinct()
                ->pluck('value');
        });

        $colorOptions = Cache::remember('filter_color_options', now()->addDay(), function () {
            return \App\Models\ProductAttribute::where('key', 'Цвет')
                ->distinct()
                ->pluck('value');
        });

        return view('storefront.products.index', compact(
            'products',
            'memoryOptions',
            'colorOptions'
        ));
    }

    /**
     * Get filtered products query (extracted for caching)
     */
    protected function getFilteredProducts(Request $request)
    {
        $query = Product::active()
            ->with('categories'); // Prevent N+1 queries

        // Price range filter (prices stored in cents)
        if ($request->filled('price_min')) {
            $query->where('price', '>=', $request->price_min * 100); // Convert KGS to cents
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

        return $query->paginate(20)->appends($request->except('page'));
    }

    public function show(Product $product)
    {
        // Eager load relationships to avoid N+1
        $product->load(['categories', 'attributes']);

        // Set SEO tags for product page
        $this->seoService->setSeoTags($product);

        // Increment view count for popularity tracking
        $product->increment('view_count');

        return view('storefront.products.show', compact('product'));
    }
}
