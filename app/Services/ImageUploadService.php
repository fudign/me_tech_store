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

        if ($isVercel && $this->hasCloudinaryConfig()) {
            // Use Cloudinary for production
            return $this->uploadToCloudinary($file, $folder);
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
     * Upload to Cloudinary
     *
     * @param UploadedFile $file
     * @param string $folder
     * @return string URL of uploaded file
     */
    protected function uploadToCloudinary(UploadedFile $file, string $folder): string
    {
        $cloudName = config('services.cloudinary.cloud_name');
        $apiKey = config('services.cloudinary.api_key');
        $apiSecret = config('services.cloudinary.api_secret');

        // Generate unique filename
        $filename = Str::random(40);

        // Create signature for upload
        $timestamp = time();
        $paramsToSign = [
            'folder' => $folder,
            'public_id' => $filename,
            'timestamp' => $timestamp,
        ];

        ksort($paramsToSign);
        $signatureString = http_build_query($paramsToSign, '', '&', PHP_QUERY_RFC3986);
        $signature = sha1($signatureString . $apiSecret);

        // Upload to Cloudinary
        $response = Http::asMultipart()
            ->attach('file', $file->get(), $file->getClientOriginalName())
            ->post("https://api.cloudinary.com/v1_1/{$cloudName}/image/upload", [
                'api_key' => $apiKey,
                'timestamp' => $timestamp,
                'signature' => $signature,
                'folder' => $folder,
                'public_id' => $filename,
            ]);

        if ($response->successful()) {
            $data = $response->json();
            return $data['secure_url'] ?? $data['url'];
        }

        throw new \RuntimeException('Failed to upload to Cloudinary: ' . $response->body());
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
     * Check if Cloudinary config is available
     *
     * @return bool
     */
    protected function hasCloudinaryConfig(): bool
    {
        return !empty(config('services.cloudinary.cloud_name'))
            && !empty(config('services.cloudinary.api_key'))
            && !empty(config('services.cloudinary.api_secret'));
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
