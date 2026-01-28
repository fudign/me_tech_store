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
                // Clear any cached prepared statements at request start
                DB::statement('DEALLOCATE ALL');
            } catch (\Exception $e) {
                // Ignore errors - DEALLOCATE might not be supported in session mode
                try {
                    // Alternative: reconnect to get fresh connection
                    DB::reconnect();
                } catch (\Exception $e2) {
                    // Still ignore - we'll handle errors at query level
                }
            }
        }

        return $next($request);
    }
}
