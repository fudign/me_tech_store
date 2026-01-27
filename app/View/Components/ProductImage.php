<?php

namespace App\View\Components;

use App\Services\ImageService;
use Illuminate\View\Component;
use Illuminate\View\View;

class ProductImage extends Component
{
    public string $image;
    public string $alt;
    public string $size;
    public array $paths;

    /**
     * Create a new component instance.
     */
    public function __construct(string $image, string $alt, string $size = 'medium')
    {
        $this->image = $image;
        $this->alt = $alt;
        $this->size = $size;

        // Get optimized image paths from ImageService
        $imageService = app(ImageService::class);
        $this->paths = $imageService->getOptimizedImage($image, $size);
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View
    {
        return view('components.product-image');
    }
}
