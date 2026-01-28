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
        // For PostgreSQL connections (especially with pooler)
        if (config('database.default') === 'pgsql') {
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
        }

        return $next($request);
    }
}
