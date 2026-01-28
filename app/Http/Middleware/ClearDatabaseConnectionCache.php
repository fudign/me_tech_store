<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class ClearDatabaseConnectionCache
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip middleware for static assets and favicon
        if ($this->isStaticAsset($request)) {
            return $next($request);
        }

        // For PostgreSQL connections (especially with pooler)
        // Disabled: DISCARD ALL causes connection issues with Neon pooler
        // The NeonPostgresConnector handles this internally on connection creation
        /* if (config('database.default') === 'pgsql') {
            try {
                // Force reconnect to get fresh connection from pool
                DB::purge('pgsql');
                DB::reconnect('pgsql');

                // Clear any cached prepared statements
                DB::statement('DISCARD ALL');
            } catch (\Exception $e) {
                // Log error but continue - we'll handle at query level
                \Illuminate\Support\Facades\Log::debug('Failed to clear DB cache in middleware', [
                    'error' => $e->getMessage()
                ]);
            }
        } */

        return $next($request);
    }

    /**
     * Check if the request is for a static asset
     */
    private function isStaticAsset(Request $request): bool
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
