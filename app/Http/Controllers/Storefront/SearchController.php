<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $query = $request->input('q', '');

        // Validate search query length
        $validated = $request->validate([
            'q' => 'nullable|string|max:200',
        ]);

        $products = Product::active()
            ->when($query, function ($queryBuilder) use ($query) {
                // CRITICAL: Use prepared statements, never concatenate (Pitfall #1: SQL Injection)
                $queryBuilder->where(function ($q) use ($query) {
                    $q->where('name', 'ILIKE', '%' . $query . '%')
                      ->orWhere('description', 'ILIKE', '%' . $query . '%')
                      ->orWhere('sku', 'ILIKE', '%' . $query . '%');
                });
            })
            ->with('categories')
            ->paginate(20)
            ->appends(['q' => $query]);

        return view('storefront.products.search', [
            'products' => $products,
            'query' => $query,
            'total' => $products->total(),
        ]);
    }

    /**
     * Search autocomplete for live suggestions
     */
    public function autocomplete(Request $request)
    {
        $request->validate([
            'q' => 'required|string|min:1|max:100',
        ]);

        $query = $request->input('q');

        $products = Product::active()
            ->where('name', 'ILIKE', "%{$query}%")
            ->select('id', 'name', 'slug', 'price', 'main_image')
            ->limit(5)
            ->get();

        return response()->json([
            'success' => true,
            'results' => $products->map(fn($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'slug' => $p->slug,
                'price' => number_format($p->price / 100, 0) . ' сом',
                'url' => route('product.show', $p->slug),
                'image' => $p->main_image,
            ])
        ]);
    }
}
