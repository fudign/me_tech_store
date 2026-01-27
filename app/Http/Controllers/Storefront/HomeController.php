<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;

class HomeController extends Controller
{
    public function index()
    {
        // Popular products (view_count or created_at for new products)
        $popularProducts = Product::where('is_active', true)
            ->orderBy('view_count', 'desc')
            ->limit(8)
            ->get();

        // Categories are now shared globally via AppServiceProvider
        return view('storefront.home', compact('popularProducts'));
    }
}
