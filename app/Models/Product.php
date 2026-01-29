<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Product extends Model
{
    use HasSlug;

    protected $fillable = [
        'name',
        'description',
        'specifications',
        'price',
        'old_price',
        'stock',
        'availability_status',
        'sku',
        'main_image',
        'images',
        'is_active',
        'meta_title',
        'meta_description',
    ];

    protected $casts = [
        'specifications' => 'array',
        'images' => 'array',
        'price' => 'integer',
        'old_price' => 'integer',
        'is_active' => 'boolean',
    ];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }

    public function attributes(): HasMany
    {
        return $this->hasMany(ProductAttribute::class);
    }

    // Helper for displaying price in KGS
    protected function formattedPrice(): Attribute
    {
        return Attribute::make(
            get: fn () => number_format($this->price / 100, 2) . ' сом',
        );
    }

    // Scope for active products (fixes PostgreSQL boolean comparison issue)
    public function scopeActive($query)
    {
        return $query->whereRaw('is_active = true');
    }

    // Scope for inactive products
    public function scopeInactive($query)
    {
        return $query->whereRaw('is_active = false');
    }
}
