<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;

class HomeController extends Controller
{
    public function index()
    {
        try {
            // Popular products (view_count or created_at for new products)
            $popularProducts = Product::where('is_active', true)
                ->orderBy('view_count', 'desc')
                ->limit(8)
                ->get();

            // Categories are now shared globally via AppServiceProvider
            return view('storefront.home', compact('popularProducts'));
        } catch (\Illuminate\Database\QueryException $e) {
            // Log the error
            \Illuminate\Support\Facades\Log::error('HomeController index failed', [
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
            ]);

            // Handle database errors gracefully
            if (str_contains($e->getMessage(), 'cached plan') ||
                str_contains($e->getMessage(), 'connection') ||
                str_contains($e->getMessage(), 'Inconsistent project name')) {
                // Try to reconnect and retry
                try {
                    \Illuminate\Support\Facades\DB::reconnect();

                    $popularProducts = Product::where('is_active', true)
                        ->orderBy('view_count', 'desc')
                        ->limit(8)
                        ->get();

                    return view('storefront.home', compact('popularProducts'));
                } catch (\Exception $retryException) {
                    // Return error page
                    return response(
                        '<html><body><h1>Сервис временно недоступен</h1><p>Попробуйте позже.</p></body></html>',
                        503
                    )->header('Content-Type', 'text/html');
                }
            }

            // For other database errors, show error page
            return response(
                '<html><body><h1>Сервис временно недоступен</h1><p>Попробуйте позже.</p></body></html>',
                503
            )->header('Content-Type', 'text/html');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('HomeController unexpected error', [
                'error' => $e->getMessage(),
            ]);

            return response(
                '<html><body><h1>Сервис временно недоступен</h1><p>Попробуйте позже.</p></body></html>',
                503
            )->header('Content-Type', 'text/html');
        }
    }
}
