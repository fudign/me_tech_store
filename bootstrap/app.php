<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Add database connection cache clearing for production
        $middleware->append(\App\Http\Middleware\ClearDatabaseConnectionCache::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Handle database connection errors (ERR-02)
        $exceptions->render(function (\PDOException $e, $request) {
            // Database connection errors (SQLSTATE[HY000] [2002])
            if (strpos($e->getMessage(), 'SQLSTATE') !== false) {
                \Illuminate\Support\Facades\Log::error('Database connection failed', [
                    'error' => $e->getMessage(),
                    'url' => $request->fullUrl(),
                    'ip' => $request->ip(),
                ]);

                if ($request->expectsJson()) {
                    return response()->json([
                        'error' => 'Сервис временно недоступен. Попробуйте позже.',
                        'code' => 'DATABASE_UNAVAILABLE'
                    ], 503);
                }

                // Use simple HTML response instead of view to avoid ViewServiceProvider dependency
                return response(
                    '<html><body><h1>Сервис временно недоступен</h1><p>Попробуйте позже.</p></body></html>',
                    503
                )->header('Content-Type', 'text/html');
            }
        });

        $exceptions->render(function (\Illuminate\Database\QueryException $e, $request) {
            // Catch query exceptions caused by connection loss during operation
            if (str_contains($e->getMessage(), 'server has gone away') ||
                str_contains($e->getMessage(), 'Connection refused')) {
                \Illuminate\Support\Facades\Log::error('Database query failed - connection lost', [
                    'error' => $e->getMessage(),
                    'url' => $request->fullUrl(),
                ]);

                if ($request->expectsJson()) {
                    return response()->json([
                        'error' => 'Сервис временно недоступен. Попробуйте позже.',
                        'code' => 'DATABASE_ERROR'
                    ], 503);
                }

                // Use simple HTML response instead of view to avoid ViewServiceProvider dependency
                return response(
                    '<html><body><h1>Сервис временно недоступен</h1><p>Попробуйте позже.</p></body></html>',
                    503
                )->header('Content-Type', 'text/html');
            }

            // Handle cached plan errors from connection pooler
            if (str_contains($e->getMessage(), 'cached plan must not change result type')) {
                \Illuminate\Support\Facades\Log::warning('Cached plan error detected - reconnecting', [
                    'error' => $e->getMessage(),
                    'url' => $request->fullUrl(),
                ]);

                // Reconnect and retry once
                try {
                    \Illuminate\Support\Facades\DB::reconnect();
                    \Illuminate\Support\Facades\DB::statement('DEALLOCATE ALL');
                } catch (\Exception $reconnectError) {
                    // If reconnect fails, show error page
                }

                // Don't redirect for static assets or API requests
                $path = $request->path();
                $isStatic = str_contains($path, 'favicon.ico') ||
                            str_contains($path, 'robots.txt') ||
                            str_starts_with($path, 'css/') ||
                            str_starts_with($path, 'js/') ||
                            str_starts_with($path, 'images/') ||
                            str_starts_with($path, 'build/');

                // Redirect to same page to retry with fresh connection
                if (!$request->expectsJson() && !$isStatic) {
                    return redirect($request->fullUrl());
                }
            }
        });
    })->create();
