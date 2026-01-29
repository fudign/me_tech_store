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
        $this->app->bind('db.connector.pgsql', function () {
            return new NeonPostgresConnector();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Fix for PostgreSQL boolean comparison when using emulated prepares
        // When ATTR_EMULATE_PREPARES is true, PHP converts true to '1' in SQL
        // But PostgreSQL boolean columns need explicit casting
        if (config('database.default') === 'pgsql') {
            \Illuminate\Database\Query\Builder::macro('whereBooleanColumn', function ($column, $value) {
                // Sanitize column name to prevent SQL injection
                $column = preg_replace('/[^a-zA-Z0-9_.]/', '', $column);
                $value = $value ? 'true' : 'false';
                return $this->whereRaw("\"$column\" = $value::boolean");
            });
        }
        // Share categories with storefront views for navigation menu
        // Exclude admin views to avoid conflicts with pagination
        View::composer(['layouts.app', 'storefront.*'], function ($view) {
            try {
                $categories = Category::active()
                    ->withCount(['products' => function ($query) {
                        // Use integer comparison for PostgreSQL compatibility
                        $query->where('is_active', 1);
                    }])
                    ->orderBy('name')
                    ->get();
            } catch (\Illuminate\Database\QueryException $e) {
                // Log the error for debugging
                \Illuminate\Support\Facades\Log::error('Category loading failed in View Composer', [
                    'error' => $e->getMessage(),
                    'code' => $e->getCode(),
                ]);

                // Handle cached plan errors specifically
                if (str_contains($e->getMessage(), 'cached plan')) {
                    try {
                        \Illuminate\Support\Facades\DB::reconnect();
                        \Illuminate\Support\Facades\DB::statement('DEALLOCATE ALL');

                        // Retry query after reconnection
                        $categories = Category::active()
                            ->withCount(['products' => function ($query) {
                                // Use integer comparison for PostgreSQL compatibility
                                $query->where('is_active', 1);
                            }])
                            ->orderBy('name')
                            ->get();
                    } catch (\Exception $retryException) {
                        // If retry fails, use empty collection
                        $categories = collect([]);
                        \Illuminate\Support\Facades\Log::error('Category retry failed', [
                            'error' => $retryException->getMessage(),
                        ]);
                    }
                } else {
                    // For other database errors, use empty collection
                    $categories = collect([]);
                }
            } catch (\Exception $e) {
                // If database is not available (e.g., Vercel demo mode), use empty collection
                \Illuminate\Support\Facades\Log::error('Unexpected error loading categories', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                $categories = collect([]);

                // In development, flash error to help debugging
                if (config('app.debug')) {
                    session()->flash('category_load_error', 'Не удалось загрузить категории: ' . $e->getMessage());
                }
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
}
