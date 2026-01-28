<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Services\SupabaseService;
use App\Database\Connectors\NeonPostgresConnector;
use Illuminate\Database\Connectors\ConnectionFactory;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register Supabase Service
        $this->app->singleton(SupabaseService::class, function ($app) {
            return new SupabaseService();
        });

        // Register custom Neon PostgreSQL connector
        $this->app->singleton('db.connector.pgsql', function () {
            return new NeonPostgresConnector();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Share categories with storefront views for navigation menu
        // Exclude admin views to avoid conflicts with pagination
        View::composer(['layouts.app', 'storefront.*'], function ($view) {
            // Skip if this is a static asset request (no views are rendered)
            $request = request();
            if ($request && $this->isStaticAssetRequest($request)) {
                return;
            }

            try {
                $categories = Category::where('is_active', true)
                    ->withCount(['products' => function ($query) {
                        $query->where('is_active', true);
                    }])
                    ->orderBy('name')
                    ->get();
            } catch (\Illuminate\Database\QueryException $e) {
                // Handle cached plan errors specifically
                if (str_contains($e->getMessage(), 'cached plan')) {
                    try {
                        \Illuminate\Support\Facades\DB::reconnect();
                        \Illuminate\Support\Facades\DB::statement('DEALLOCATE ALL');

                        // Retry query after reconnection
                        $categories = Category::where('is_active', true)
                            ->withCount(['products' => function ($query) {
                                $query->where('is_active', true);
                            }])
                            ->orderBy('name')
                            ->get();
                    } catch (\Exception $retryException) {
                        // If retry fails, use empty collection
                        $categories = collect([]);
                    }
                } else {
                    // For other database errors, use empty collection
                    $categories = collect([]);
                }
            } catch (\Exception $e) {
                // If database is not available (e.g., Vercel demo mode), use empty collection
                $categories = collect([]);
            }

            $view->with('categories', $categories);
        });

        // Rate limiter for checkout form (SEC-05 requirement)
        RateLimiter::for('checkout', function (Request $request) {
            return [
                // 3 attempts per 10 minutes per IP
                Limit::perMinutes(10, 3)->by($request->ip()),
                // 1 attempt per 2 minutes per phone number
                Limit::perMinutes(2, 1)->by($request->input('phone')),
            ];
        });
    }

    /**
     * Check if the request is for a static asset
     */
    private function isStaticAssetRequest($request): bool
    {
        $path = $request->path();

        // List of static asset patterns
        $staticPatterns = [
            'favicon.ico',
            'robots.txt',
            'sitemap.xml',
        ];

        // Check exact matches
        if (in_array($path, $staticPatterns)) {
            return true;
        }

        // Check if path starts with static directories
        $staticDirs = ['css/', 'js/', 'images/', 'fonts/', 'storage/', 'build/'];
        foreach ($staticDirs as $dir) {
            if (str_starts_with($path, $dir)) {
                return true;
            }
        }

        return false;
    }
}
