<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;

class ImageService
{
    /**
     * Thumbnail size definitions
     */
    public const SIZES = [
        'thumb' => 200,   // Catalog listing
        'medium' => 600,  // Product detail main image
        'large' => 1200,  // Product gallery zoom
    ];

    /**
     * Get optimized image paths (WebP and JPEG) for a given size
     *
     * @param string $originalPath Path relative to storage/public (e.g., "images/products/abc.jpg")
     * @param string $size One of 'thumb', 'medium', 'large'
     * @return array ['webp' => path, 'jpeg' => path] relative to storage/public
     */
    public function getOptimizedImage(string $originalPath, string $size = 'medium'): array
    {
        // Validate size
        if (!isset(self::SIZES[$size])) {
            throw new \InvalidArgumentException("Invalid size: {$size}");
        }

        $width = self::SIZES[$size];
        $cacheKey = "img_{$size}_" . md5($originalPath);

        // Cache for 1 month - generated images rarely change
        return Cache::remember($cacheKey, now()->addMonth(), function () use ($originalPath, $width) {
            return [
                'webp' => $this->generateWebP($originalPath, $width),
                'jpeg' => $this->generateJpeg($originalPath, $width),
            ];
        });
    }

    /**
     * Generate WebP thumbnail
     *
     * @param string $path Original image path relative to storage/public
     * @param int $width Target width (height calculated to maintain aspect ratio)
     * @return string Generated WebP path relative to storage/public
     */
    private function generateWebP(string $path, int $width): string
    {
        $filename = pathinfo($path, PATHINFO_FILENAME);
        $outputPath = "images/thumbnails/{$width}/{$filename}.webp";

        // Return if already exists
        if (Storage::disk('public')->exists($outputPath)) {
            return $outputPath;
        }

        // Generate WebP
        $originalFullPath = Storage::disk('public')->path($path);

        // Check if original exists
        if (!file_exists($originalFullPath)) {
            throw new \RuntimeException("Original image not found: {$path}");
        }

        $image = Image::read($originalFullPath);
        $image->scale(width: $width); // Maintains aspect ratio
        $encoded = $image->toWebp(quality: config('image.quality.webp', 80));

        // Ensure directory exists
        $outputDir = dirname(Storage::disk('public')->path($outputPath));
        if (!is_dir($outputDir)) {
            mkdir($outputDir, 0755, true);
        }

        Storage::disk('public')->put($outputPath, (string) $encoded);

        return $outputPath;
    }

    /**
     * Generate JPEG thumbnail
     *
     * @param string $path Original image path relative to storage/public
     * @param int $width Target width (height calculated to maintain aspect ratio)
     * @return string Generated JPEG path relative to storage/public
     */
    private function generateJpeg(string $path, int $width): string
    {
        $filename = basename($path);
        $outputPath = "images/thumbnails/{$width}/{$filename}";

        // Return if already exists
        if (Storage::disk('public')->exists($outputPath)) {
            return $outputPath;
        }

        // Generate JPEG
        $originalFullPath = Storage::disk('public')->path($path);

        // Check if original exists
        if (!file_exists($originalFullPath)) {
            throw new \RuntimeException("Original image not found: {$path}");
        }

        $image = Image::read($originalFullPath);
        $image->scale(width: $width); // Maintains aspect ratio
        $encoded = $image->toJpeg(quality: config('image.quality.jpeg', 85));

        // Ensure directory exists
        $outputDir = dirname(Storage::disk('public')->path($outputPath));
        if (!is_dir($outputDir)) {
            mkdir($outputDir, 0755, true);
        }

        Storage::disk('public')->put($outputPath, (string) $encoded);

        return $outputPath;
    }
}
