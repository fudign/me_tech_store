<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Product;
use Artesaos\SEOTools\Facades\JsonLd;
use Artesaos\SEOTools\Facades\OpenGraph;
use Artesaos\SEOTools\Facades\SEOMeta;

class SeoService
{
    /**
     * Set SEO tags for a given model (Product or Category)
     */
    public function setSeoTags($model): void
    {
        if ($model instanceof Product) {
            $this->setProductSeoTags($model);
        } elseif ($model instanceof Category) {
            $this->setCategorySeoTags($model);
        }
    }

    /**
     * Set SEO tags for a product
     */
    protected function setProductSeoTags(Product $product): void
    {
        // Meta title: custom or auto-generated
        $title = $product->meta_title ?: $this->generateProductTitle($product);

        // Meta description: custom or auto-generated
        $description = $product->meta_description ?: $this->generateProductDescription($product);

        // Canonical URL
        $url = route('product.show', $product);

        // Set SEO Meta tags
        SEOMeta::setTitle($title);
        SEOMeta::setDescription($description);
        SEOMeta::setCanonical($url);
        SEOMeta::addKeyword(['Xiaomi', $product->name, 'купить', 'Бишкек']);

        // Set OpenGraph tags
        OpenGraph::setTitle($title);
        OpenGraph::setDescription($description);
        OpenGraph::setUrl($url);
        OpenGraph::setType('product');

        if ($product->main_image) {
            OpenGraph::addImage(asset('storage/' . $product->main_image));
        }

        // Set JsonLd Product schema
        JsonLd::setType('Product');
        JsonLd::setTitle($product->name);
        JsonLd::setDescription($description);

        if ($product->main_image) {
            JsonLd::addImage(asset('storage/' . $product->main_image));
        }

        // Add offer information
        JsonLd::addValues([
            'offers' => [
                '@type' => 'Offer',
                'price' => number_format($product->price / 100, 2, '.', ''),
                'priceCurrency' => 'KGS',
                'availability' => $product->stock > 0
                    ? 'https://schema.org/InStock'
                    : 'https://schema.org/OutOfStock',
                'url' => $url,
            ],
        ]);
    }

    /**
     * Set SEO tags for a category
     */
    protected function setCategorySeoTags(Category $category): void
    {
        // Meta title: custom or auto-generated
        $title = $category->meta_title ?: $this->generateCategoryTitle($category);

        // Meta description: custom or auto-generated
        $description = $category->meta_description ?: $this->generateCategoryDescription($category);

        // Canonical URL
        $url = route('category.show', $category);

        // Set SEO Meta tags
        SEOMeta::setTitle($title);
        SEOMeta::setDescription($description);
        SEOMeta::setCanonical($url);
        SEOMeta::addKeyword(['Xiaomi', $category->name, 'купить', 'Бишкек']);

        // Set OpenGraph tags
        OpenGraph::setTitle($title);
        OpenGraph::setDescription($description);
        OpenGraph::setUrl($url);
        OpenGraph::setType('website');
    }

    /**
     * Generate automatic product title
     */
    protected function generateProductTitle(Product $product): string
    {
        $price = number_format($product->price / 100, 0, '', ' ');
        return "Купить {$product->name} - цена {$price} сом в Бишкеке | Xiaomi Store";
    }

    /**
     * Generate automatic product description from top 3 attributes
     */
    protected function generateProductDescription(Product $product): string
    {
        $attributes = $product->attributes()
            ->orderBy('id')
            ->limit(3)
            ->get()
            ->map(fn($attr) => "{$attr->key}: {$attr->value}")
            ->implode(', ');

        $attrText = $attributes ? " - {$attributes}." : '.';

        return "{$product->name}{$attrText} ✓ Официальная гарантия ✓ Доставка по Кыргызстану";
    }

    /**
     * Generate automatic category title
     */
    protected function generateCategoryTitle(Category $category): string
    {
        return "{$category->name} Xiaomi - купить в Бишкеке | Xiaomi Store";
    }

    /**
     * Generate automatic category description
     */
    protected function generateCategoryDescription(Category $category): string
    {
        return "Купить {$category->name} Xiaomi в Бишкеке. Большой выбор, официальная гарантия, доставка по Кыргызстану.";
    }
}
