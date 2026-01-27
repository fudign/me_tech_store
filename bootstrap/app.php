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
        //
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
        });
    })->create();
