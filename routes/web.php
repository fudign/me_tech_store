<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Storefront\HomeController;
use App\Http\Controllers\Storefront\ProductController;
use App\Http\Controllers\Storefront\CategoryController;
use App\Http\Controllers\Storefront\SearchController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\WishlistController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Auth\LoginController;
use App\Models\Product;
// Public storefront routes
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/search', [SearchController::class, 'index'])->name('search');
Route::get('/search/autocomplete', [SearchController::class, 'autocomplete'])->name('search.autocomplete');
Route::get('/categories/{category:slug}', [CategoryController::class, 'show'])->name('category.show');
Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/{product:slug}', [ProductController::class, 'show'])->name('product.show');

// Cart routes
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
Route::patch('/cart/{itemId}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/cart/{itemId}', [CartController::class, 'remove'])->name('cart.remove');

// Wishlist routes
Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist.index');
Route::post('/wishlist/toggle', [WishlistController::class, 'toggle'])->name('wishlist.toggle');
Route::get('/wishlist/count', [WishlistController::class, 'count'])->name('wishlist.count');

// Checkout routes
Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
Route::post('/checkout', [CheckoutController::class, 'process'])
    ->middleware(['throttle:checkout'])
    ->name('checkout.process');
Route::get('/checkout/success/{orderNumber}', [CheckoutController::class, 'success'])->name('checkout.success');

// Legacy fallback for ID-based URLs (301 redirect to slug)
Route::get('/p/{id}', function ($id) {
    $product = Product::findOrFail($id);
    return redirect(route('product.show', $product), 301);
})->name('product.legacy');

// Sitemap.xml
Route::get('sitemap.xml', function () {
    return response()->file(public_path('sitemap.xml'), [
        'Content-Type' => 'application/xml',
    ]);
});

// Robots.txt
Route::get('robots.txt', function () {
    $content = app()->environment('production')
        ? "User-agent: *\nDisallow: /admin\nDisallow: /api\nDisallow: /cart\nDisallow: /checkout\n\nSitemap: " . url('sitemap.xml')
        : "User-agent: *\nDisallow: /\n";
    return response($content, 200)->header('Content-Type', 'text/plain');
});

// Authentication routes
Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('login', [LoginController::class, 'login']);
Route::post('logout', [LoginController::class, 'logout'])->name('logout');
Route::get('admin/login', [LoginController::class, 'showLoginForm'])->name('admin.login');

// Admin routes
Route::prefix('admin')->middleware('auth')->name('admin.')->group(function () {
    // Admin dashboard
    Route::get('/', [App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');

    Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [AdminOrderController::class, 'show'])->name('orders.show');
    Route::post('/orders/{order}/status', [AdminOrderController::class, 'updateStatus'])->name('orders.updateStatus');

    Route::resource('products', AdminProductController::class);
    Route::resource('categories', AdminCategoryController::class);

    // Customers
    Route::get('customers', [App\Http\Controllers\Admin\CustomerController::class, 'index'])->name('customers.index');
    Route::get('customers/{customer}', [App\Http\Controllers\Admin\CustomerController::class, 'show'])->name('customers.show');

    // Reviews (to be implemented)
    Route::resource('reviews', App\Http\Controllers\Admin\ReviewController::class)->only(['index', 'show', 'update', 'destroy']);

    // Coupons (to be implemented)
    Route::resource('coupons', App\Http\Controllers\Admin\CouponController::class);

    Route::get('settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::post('settings', [SettingsController::class, 'update'])->name('settings.update');
});

// Temporary route to clear PostgreSQL cached plans - REMOVE AFTER USE
Route::get('/clear-db-cache-temp-2026', function () {
    try {
        // Clear prepared statements and temporary tables
        DB::connection()->getPdo()->exec('DISCARD ALL');

        // Reconnect to force new connection
        DB::purge('pgsql');
        DB::reconnect('pgsql');

        return response()->json([
            'status' => 'success',
            'message' => 'Database cache cleared successfully',
            'timestamp' => now()->toDateTimeString()
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage()
        ], 500);
    }
});
