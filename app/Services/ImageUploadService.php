<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ImageUploadService
{
    /**
     * Upload image to appropriate storage based on environment
     *
     * @param UploadedFile $file
     * @param string $folder
     * @return string Path or URL of uploaded image
     */
    public function upload(UploadedFile $file, string $folder = 'products'): string
    {
        // Check if running on Vercel (read-only filesystem)
        $isVercel = $this->isVercel();

        if ($isVercel && $this->hasSupabaseConfig()) {
            // Use Supabase Storage for production
            return $this->uploadToSupabase($file, $folder);
        } else {
            // Use local storage for development
            return $this->uploadToLocal($file, $folder);
        }
    }

    /**
     * Delete image from storage
     *
     * @param string $path
     * @return bool
     */
    public function delete(string $path): bool
    {
        // If it's an external URL, we can't delete it (managed externally)
        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return true;
        }

        // Delete from local storage
        return Storage::disk('public')->delete($path);
    }

    /**
     * Upload to local storage
     *
     * @param UploadedFile $file
     * @param string $folder
     * @return string
     */
    protected function uploadToLocal(UploadedFile $file, string $folder): string
    {
        return Storage::disk('public')->put($folder, $file);
    }

    /**
     * Upload to Supabase Storage
     *
     * @param UploadedFile $file
     * @param string $folder
     * @return string URL of uploaded file
     */
    protected function uploadToSupabase(UploadedFile $file, string $folder): string
    {
        $supabaseUrl = config('services.supabase.url');
        $supabaseKey = config('services.supabase.service_key') ?: config('services.supabase.key');
        $bucket = config('services.supabase.storage_bucket', 'products');

        // Generate unique filename
        $filename = Str::random(40) . '.' . $file->getClientOriginalExtension();
        $path = $folder . '/' . $filename;

        // Upload to Supabase Storage
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $supabaseKey,
            'Content-Type' => $file->getMimeType(),
        ])->attach(
            'file',
            $file->get(),
            $filename
        )->post("{$supabaseUrl}/storage/v1/object/{$bucket}/{$path}");

        if ($response->successful()) {
            // Return public URL
            return "{$supabaseUrl}/storage/v1/object/public/{$bucket}/{$path}";
        }

        // Fallback: If Supabase upload fails, try to save locally (will fail on Vercel but works for dev)
        throw new \RuntimeException('Failed to upload to Supabase Storage: ' . $response->body());
    }

    /**
     * Check if running on Vercel
     *
     * @return bool
     */
    protected function isVercel(): bool
    {
        return !empty(getenv('VERCEL')) || !empty(getenv('VERCEL_ENV'));
    }

    /**
     * Check if Supabase config is available
     *
     * @return bool
     */
    protected function hasSupabaseConfig(): bool
    {
        return !empty(config('services.supabase.url'))
            && !empty(config('services.supabase.key'));
    }

    /**
     * Upload multiple images
     *
     * @param array $files Array of UploadedFile
     * @param string $folder
     * @return array Array of paths/URLs
     */
    public function uploadMultiple(array $files, string $folder = 'products'): array
    {
        $paths = [];

        foreach ($files as $file) {
            if ($file instanceof UploadedFile) {
                $paths[] = $this->upload($file, $folder);
            }
        }

        return $paths;
    }

    /**
     * Delete multiple images
     *
     * @param array $paths
     * @return void
     */
    public function deleteMultiple(array $paths): void
    {
        foreach ($paths as $path) {
            $this->delete($path);
        }
    }
}
