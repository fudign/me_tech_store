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

        if ($isVercel && $this->hasImgbbConfig()) {
            // Use imgbb for production
            return $this->uploadToImgbb($file, $folder);
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
     * Upload to imgbb
     *
     * @param UploadedFile $file
     * @param string $folder
     * @return string URL of uploaded file
     */
    protected function uploadToImgbb(UploadedFile $file, string $folder): string
    {
        $apiKey = config('services.imgbb.api_key');

        // Convert image to base64
        $imageBase64 = base64_encode($file->get());

        // Upload to imgbb
        $response = Http::asForm()->post('https://api.imgbb.com/1/upload', [
            'key' => $apiKey,
            'image' => $imageBase64,
            'name' => $folder . '_' . Str::random(20),
        ]);

        if ($response->successful()) {
            $data = $response->json();
            return $data['data']['url'];
        }

        throw new \RuntimeException('Failed to upload to imgbb: ' . $response->body());
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
     * Check if imgbb config is available
     *
     * @return bool
     */
    protected function hasImgbbConfig(): bool
    {
        return !empty(config('services.imgbb.api_key'));
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
