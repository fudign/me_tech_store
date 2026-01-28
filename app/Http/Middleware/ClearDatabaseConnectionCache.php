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
        // Only in production with PostgreSQL pooler
        if (config('app.env') === 'production' && config('database.default') === 'pgsql') {
            try {
                // Reconnect to get fresh connection from pool
                DB::reconnect();

                // Clear any cached prepared statements
                DB::statement('DEALLOCATE ALL');
            } catch (\Exception $e) {
                // Ignore errors - connection might not support this
            }
        }

        return $next($request);
    }
}
