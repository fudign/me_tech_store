<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static array select(string $table, array $params = [])
 * @method static array|null insert(string $table, array $data)
 * @method static array|null update(string $table, array $data, array $conditions)
 * @method static bool delete(string $table, array $conditions)
 * @method static array|null uploadFile(string $bucket, string $path, $file)
 * @method static string getPublicUrl(string $bucket, string $path)
 * @method static bool deleteFile(string $bucket, string $path)
 * @method static array|null customQuery(string $endpoint, string $method = 'GET', array $options = [])
 *
 * @see \App\Services\SupabaseService
 */
class Supabase extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return \App\Services\SupabaseService::class;
    }
}
