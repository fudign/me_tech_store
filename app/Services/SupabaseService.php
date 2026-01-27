<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

class SupabaseService
{
    protected Client $client;
    protected string $url;
    protected string $key;
    protected string $serviceKey;

    public function __construct()
    {
        $this->url = config('supabase.url');
        $this->key = config('supabase.key');
        $this->serviceKey = config('supabase.service_key');

        $this->client = new Client([
            'base_uri' => $this->url,
            'headers' => [
                'apikey' => $this->key,
                'Authorization' => 'Bearer ' . $this->key,
                'Content-Type' => 'application/json',
                'Prefer' => 'return=representation',
            ],
        ]);
    }

    /**
     * Query data from a Supabase table
     *
     * @param string $table
     * @param array $params
     * @return array
     */
    public function select(string $table, array $params = []): array
    {
        try {
            $query = http_build_query($params);
            $response = $this->client->get("/rest/v1/{$table}?{$query}");
            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            Log::error('Supabase select error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Insert data into a Supabase table
     *
     * @param string $table
     * @param array $data
     * @return array|null
     */
    public function insert(string $table, array $data): ?array
    {
        try {
            $response = $this->client->post("/rest/v1/{$table}", [
                'json' => $data,
            ]);
            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            Log::error('Supabase insert error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Update data in a Supabase table
     *
     * @param string $table
     * @param array $data
     * @param array $conditions
     * @return array|null
     */
    public function update(string $table, array $data, array $conditions): ?array
    {
        try {
            $query = http_build_query($conditions);
            $response = $this->client->patch("/rest/v1/{$table}?{$query}", [
                'json' => $data,
            ]);
            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            Log::error('Supabase update error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Delete data from a Supabase table
     *
     * @param string $table
     * @param array $conditions
     * @return bool
     */
    public function delete(string $table, array $conditions): bool
    {
        try {
            $query = http_build_query($conditions);
            $this->client->delete("/rest/v1/{$table}?{$query}");
            return true;
        } catch (GuzzleException $e) {
            Log::error('Supabase delete error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Upload a file to Supabase Storage
     *
     * @param string $bucket
     * @param string $path
     * @param mixed $file
     * @return array|null
     */
    public function uploadFile(string $bucket, string $path, $file): ?array
    {
        try {
            $storageClient = new Client([
                'base_uri' => $this->url,
                'headers' => [
                    'apikey' => $this->serviceKey,
                    'Authorization' => 'Bearer ' . $this->serviceKey,
                ],
            ]);

            $response = $storageClient->post("/storage/v1/object/{$bucket}/{$path}", [
                'body' => $file,
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            Log::error('Supabase file upload error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get public URL for a file in Supabase Storage
     *
     * @param string $bucket
     * @param string $path
     * @return string
     */
    public function getPublicUrl(string $bucket, string $path): string
    {
        return "{$this->url}/storage/v1/object/public/{$bucket}/{$path}";
    }

    /**
     * Delete a file from Supabase Storage
     *
     * @param string $bucket
     * @param string $path
     * @return bool
     */
    public function deleteFile(string $bucket, string $path): bool
    {
        try {
            $storageClient = new Client([
                'base_uri' => $this->url,
                'headers' => [
                    'apikey' => $this->serviceKey,
                    'Authorization' => 'Bearer ' . $this->serviceKey,
                ],
            ]);

            $storageClient->delete("/storage/v1/object/{$bucket}/{$path}");
            return true;
        } catch (GuzzleException $e) {
            Log::error('Supabase file delete error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Execute a custom query using Supabase client
     *
     * @param string $endpoint
     * @param string $method
     * @param array $options
     * @return array|null
     */
    public function customQuery(string $endpoint, string $method = 'GET', array $options = []): ?array
    {
        try {
            $response = $this->client->request($method, $endpoint, $options);
            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            Log::error('Supabase custom query error: ' . $e->getMessage());
            return null;
        }
    }
}
