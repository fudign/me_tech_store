# Phase 4: Polish & Launch - Research

**Researched:** 2026-01-23
**Domain:** Laravel SEO, Performance Optimization, Production Deployment
**Confidence:** HIGH

## Summary

Phase 4 focuses on production-readiness: SEO optimization, image performance, caching, and deployment. The Laravel ecosystem provides mature, well-tested packages for all these concerns.

**Standard approach:** Use artesaos/seotools (already installed) for meta tags and OpenGraph, spatie/laravel-sitemap for sitemap generation, native HTML lazy loading with WebP images via Intervention Image, Laravel's built-in pagination with cursor support, Redis for caching, and standard Laravel optimization commands for deployment.

The project already has strong foundations from phases 1-3:
- Slug-based routing implemented (spatie/laravel-sluggable)
- Meta fields in database (products.meta_title, products.meta_description)
- Indexes on key columns (slug, name, is_active)
- artesaos/seotools package already installed
- Laravel 12.48.1 (latest stable)

**Primary recommendation:** Focus on implementation over tool evaluation - the standard Laravel stack for this phase is well-established and battle-tested. All required packages have Laravel 12 support and active maintenance.

## Standard Stack

The established libraries/tools for Laravel SEO and production optimization:

### Core

| Library | Version | Purpose | Why Standard |
|---------|---------|---------|--------------|
| artesaos/seotools | ^1.3 | Meta tags, OpenGraph, Twitter Cards, JSON-LD | Most popular Laravel SEO package, 3K+ stars, supports Laravel 12, already installed |
| spatie/laravel-sitemap | ^7.3 | XML sitemap generation | Official Spatie package, Laravel 12 support, can crawl or use models |
| intervention/image-laravel | ^3.x | Image manipulation (resize, WebP conversion) | Current standard (v3 doesn't depend on old Intervention package), GD/Imagick drivers |
| Native HTML | loading="lazy" | Image lazy loading | 95%+ browser support, zero dependencies, recommended by MDN |
| Laravel Cache | Redis/File | Application caching | Built-in, no package needed, Redis recommended for production |

### Supporting

| Library | Version | Purpose | When to Use |
|---------|---------|---------|-------------|
| spatie/laravel-image-optimizer | ^3.x | Post-processing image compression | If you want CLI-based optimization (jpegoptim, pngquant) after Intervention |
| spatie/schema-org | ^3.x | Fluent Schema.org/JSON-LD builder | For complex structured data beyond artesaos/seotools JsonLd |
| diglactic/laravel-breadcrumbs | ^8.x | Breadcrumb generation | Recommended for SEO and UX, route-based definitions |
| verschuur/laravel-robotstxt | ^1.x | Dynamic robots.txt | Only if you need environment-based robots.txt (blocks staging) |

### Alternatives Considered

| Instead of | Could Use | Tradeoff |
|------------|-----------|----------|
| artesaos/seotools | butschster/LaravelMetaTags | More extensible but less popular, complex package system |
| intervention/image-laravel | imsus/laravel-imgproxy | Better for high-traffic (external service) but adds infrastructure |
| Native lazy loading | vanilla-lazyload JS | More control (cancel downloads) but adds 3KB dependency |
| Redis cache | File cache | Simpler setup but slower, no multi-server support |

**Installation:**

```bash
# SEO packages
composer require spatie/laravel-sitemap
composer require spatie/schema-org
composer require diglactic/laravel-breadcrumbs

# Image optimization
composer require intervention/image-laravel
# Optional: CLI-based compression
composer require spatie/laravel-image-optimizer

# Optional: dynamic robots.txt
composer require verschuur/laravel-robotstxt

# Redis driver (if not already installed)
composer require predis/predis
# OR install PhpRedis extension via PECL (better performance)
```

## Architecture Patterns

### Recommended Project Structure

```
app/
├── Services/
│   ├── ImageService.php       # WebP conversion, thumbnail generation
│   └── SeoService.php          # Meta tag generation from templates
├── View/
│   └── Components/
│       ├── ProductImage.php    # <picture> with WebP + fallback
│       └── Breadcrumbs.php     # Optional: breadcrumb component
routes/
├── web.php                     # Add sitemap, robots routes
resources/
└── views/
    └── layouts/
        └── app.blade.php       # Add SEOMeta, OpenGraph, JsonLd tags
public/
└── storage/                    # Symlinked, stores optimized images
    └── images/
        ├── originals/          # Full-size uploads
        └── thumbnails/         # Generated sizes (200x200, 600x600, 1200x1200)
```

### Pattern 1: SEO Meta Tag Management

**What:** Controller sets page-specific meta tags before rendering views; Blade layout renders them in `<head>`.

**When to use:** Every page that should be indexed by search engines.

**Example:**

```php
// app/Http/Controllers/ProductController.php
use Artesaos\SEOTools\Facades\SEOMeta;
use Artesaos\SEOTools\Facades\OpenGraph;
use Artesaos\SEOTools\Facades\JsonLd;

public function show(Product $product)
{
    // Meta tags
    SEOMeta::setTitle($product->meta_title ?? "Купить {$product->name} - цена {$product->formatted_price} в Бишкеке | Xiaomi Store");
    SEOMeta::setDescription($product->meta_description ?? $this->generateDescription($product));
    SEOMeta::setCanonical(route('products.show', $product));
    SEOMeta::addKeyword(['Xiaomi', $product->name, 'купить', 'Бишкек']);

    // OpenGraph for social sharing
    OpenGraph::setTitle($product->meta_title ?? $product->name);
    OpenGraph::setDescription($product->meta_description ?? $product->description);
    OpenGraph::setUrl(route('products.show', $product));
    OpenGraph::addImage(asset('storage/' . $product->main_image), ['height' => 600, 'width' => 600]);
    OpenGraph::addProperty('type', 'product');

    // JSON-LD Product schema
    JsonLd::setType('Product');
    JsonLd::setTitle($product->name);
    JsonLd::setDescription($product->description);
    JsonLd::setImages([asset('storage/' . $product->main_image)]);
    JsonLd::addValue('offers', [
        '@type' => 'Offer',
        'price' => $product->price / 100,
        'priceCurrency' => 'KGS',
        'availability' => $product->stock > 0 ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
    ]);

    return view('products.show', compact('product'));
}

private function generateDescription(Product $product): string
{
    $specs = collect($product->attributes)->take(3)->pluck('value', 'key');
    $specText = $specs->map(fn($v, $k) => "{$k}: {$v}")->implode(', ');
    return "{$product->name} - {$specText}. ✓ Официальная гарантия ✓ Доставка по Кыргызстану";
}
```

```blade
{{-- resources/views/layouts/app.blade.php --}}
<head>
    {!! SEOMeta::generate() !!}
    {!! OpenGraph::generate() !!}
    {!! Twitter::generate() !!}
    {!! JsonLd::generate() !!}
</head>
```

**Source:** https://github.com/artesaos/seotools (verified 2026-01-23)

### Pattern 2: Sitemap Generation

**What:** Command or scheduled task generates sitemap.xml from models, caches for 24 hours.

**When to use:** Products/categories change frequently, need automatic updates.

**Example:**

```php
// app/Console/Commands/GenerateSitemap.php
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;
use Carbon\Carbon;

public function handle()
{
    $sitemap = Sitemap::create()
        // Home page
        ->add(Url::create('/')
            ->setLastModificationDate(Carbon::now())
            ->setPriority(1.0)
            ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY))

        // Categories
        ->add(Category::where('is_active', true)->get())

        // Products
        ->add(Product::where('is_active', true)->get());

    $sitemap->writeToFile(public_path('sitemap.xml'));
    Cache::put('sitemap_generated_at', Carbon::now(), now()->addDay());
}
```

```php
// app/Models/Product.php
use Spatie\Sitemap\Contracts\Sitemapable;
use Spatie\Sitemap\Tags\Url;

class Product extends Model implements Sitemapable
{
    public function toSitemapTag(): Url|string|array
    {
        return Url::create(route('products.show', $this))
            ->setLastModificationDate($this->updated_at)
            ->setPriority(0.6)
            ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY);
    }
}
```

```php
// routes/web.php
Route::get('sitemap.xml', function () {
    return response()->file(public_path('sitemap.xml'), [
        'Content-Type' => 'application/xml',
    ]);
});
```

**Schedule in app/Console/Kernel.php:**

```php
protected function schedule(Schedule $schedule)
{
    $schedule->command('sitemap:generate')->daily();
}
```

**Source:** https://github.com/spatie/laravel-sitemap (verified 2026-01-23)

### Pattern 3: On-the-Fly Image Optimization with Caching

**What:** Generate WebP thumbnails on first request, cache them, serve via `<picture>` with fallback.

**When to use:** Product images need multiple sizes, WebP for modern browsers, JPEG fallback.

**Example:**

```php
// app/Services/ImageService.php
use Intervention\Image\Laravel\Facades\Image;
use Illuminate\Support\Facades\Storage;

class ImageService
{
    public const SIZES = [
        'thumb' => 200,   // Catalog listing
        'medium' => 600,  // Product card
        'large' => 1200,  // Zoom/gallery
    ];

    public function getOptimizedImage(string $originalPath, string $size = 'medium'): array
    {
        $width = self::SIZES[$size] ?? 600;
        $cacheKey = "img_{$size}_" . md5($originalPath);

        return Cache::remember($cacheKey, now()->addMonth(), function () use ($originalPath, $width) {
            $webpPath = $this->generateWebP($originalPath, $width);
            $jpegPath = $this->generateJpeg($originalPath, $width);

            return [
                'webp' => $webpPath,
                'jpeg' => $jpegPath,
            ];
        });
    }

    private function generateWebP(string $path, int $width): string
    {
        $outputPath = "images/thumbnails/{$width}/" . basename($path, '.jpg') . '.webp';

        if (!Storage::disk('public')->exists($outputPath)) {
            $image = Image::read(Storage::disk('public')->path($path));
            $image->scale(width: $width);
            $image->toWebp(quality: 80);
            Storage::disk('public')->put($outputPath, (string) $image);
        }

        return $outputPath;
    }

    private function generateJpeg(string $path, int $width): string
    {
        $outputPath = "images/thumbnails/{$width}/" . basename($path);

        if (!Storage::disk('public')->exists($outputPath)) {
            $image = Image::read(Storage::disk('public')->path($path));
            $image->scale(width: $width);
            $image->toJpeg(quality: 85);
            Storage::disk('public')->put($outputPath, (string) $image);
        }

        return $outputPath;
    }
}
```

```blade
{{-- resources/views/components/product-image.blade.php --}}
@props(['image', 'alt', 'size' => 'medium'])

@php
    $paths = app(App\Services\ImageService::class)->getOptimizedImage($image, $size);
@endphp

<picture>
    <source type="image/webp" srcset="{{ asset('storage/' . $paths['webp']) }}">
    <source type="image/jpeg" srcset="{{ asset('storage/' . $paths['jpeg']) }}">
    <img
        src="{{ asset('storage/' . $paths['jpeg']) }}"
        alt="{{ $alt }}"
        loading="lazy"
        {{ $attributes->merge(['class' => 'w-full h-auto']) }}
    >
</picture>
```

**Usage in views:**

```blade
<x-product-image :image="$product->main_image" :alt="$product->name" size="medium" />
```

**Source:** Intervention Image v3 documentation, MDN `<picture>` element guide

### Pattern 4: Cache Invalidation on Admin Updates

**What:** Clear relevant caches when content changes in admin panel.

**When to use:** Product/category updates should immediately reflect on frontend.

**Example:**

```php
// app/Http/Controllers/Admin/ProductController.php
public function update(UpdateProductRequest $request, Product $product)
{
    $product->update($request->validated());

    // Clear product-specific caches
    Cache::forget("product_{$product->id}");
    Cache::forget("product_json_ld_{$product->id}");
    Cache::tags(['products', "product_{$product->id}"])->flush();

    // Clear catalog cache if product became active/inactive
    if ($product->wasChanged('is_active')) {
        Cache::tags('catalog')->flush();
    }

    // Regenerate sitemap
    Artisan::call('sitemap:generate');

    return redirect()->route('admin.products.index')
        ->with('success', 'Товар обновлен');
}
```

```php
// app/Http/Controllers/ProductController.php
public function show(Product $product)
{
    $product = Cache::tags(['products', "product_{$product->id}"])
        ->remember("product_{$product->id}", now()->addHours(6), function () use ($product) {
            return $product->load('categories', 'attributes');
        });

    // Set SEO meta...
    return view('products.show', compact('product'));
}
```

**Source:** Laravel Cache documentation (tags require Redis/Memcached driver)

### Anti-Patterns to Avoid

- **Don't cache entire pages with SEO meta tags** - Cache data, not rendered HTML with `<meta>` tags. Meta tags must be dynamic per page.
- **Don't use `asset()` for images that need optimization** - Use dedicated image service that generates multiple sizes and formats.
- **Don't regenerate sitemap on every request** - Generate via scheduled command, cache for 24 hours, update only when content changes.
- **Don't forget canonical URLs** - Prevents duplicate content penalties when products appear in multiple categories.
- **Don't skip alt attributes** - Required for accessibility and image SEO.

## Don't Hand-Roll

Problems that look simple but have existing solutions:

| Problem | Don't Build | Use Instead | Why |
|---------|-------------|-------------|-----|
| Meta tag management | Custom helpers per page | artesaos/seotools | Handles OpenGraph, Twitter Cards, JSON-LD schemas, character limits, escaping |
| Sitemap generation | Manual XML construction | spatie/laravel-sitemap | Handles priorities, change frequency, lastmod, image sitemaps, auto-discovery |
| Image resizing | GD functions directly | intervention/image-laravel | Aspect ratio preservation, format conversion, quality management, memory-efficient |
| WebP conversion | exec() imagemagick commands | Intervention Image toWebp() | Cross-platform, fallback to GD, consistent API, error handling |
| Robots.txt | Static file | verschuur/laravel-robotstxt (optional) | Environment-based rules, auto-blocks staging/dev, sitemap integration |
| Breadcrumbs | Manual HTML | diglactic/laravel-breadcrumbs | Route-based, automatic parent detection, Schema.org markup support |
| Cache invalidation | Manual Cache::forget() calls | Cache tags + events | Batch invalidation, related cache groups, event-driven architecture |

**Key insight:** SEO and image optimization have many edge cases (character limits, special characters in URLs, image orientation, color profiles, browser compatibility). Mature packages handle these; custom code rarely does initially.

## Common Pitfalls

### Pitfall 1: APP_DEBUG=true in Production

**What goes wrong:** Stack traces expose sensitive information (database credentials, API keys, file paths) to end users and search engine crawlers.

**Why it happens:** Forgetting to update `.env` when deploying, or copying dev `.env` to production.

**How to avoid:**
- Set `APP_DEBUG=false` in production `.env`
- Set `APP_ENV=production`
- Add deployment checklist that verifies these settings
- Consider using Laravel's health check at `/up` endpoint

**Warning signs:** Error pages show detailed stack traces with file paths and environment variables.

### Pitfall 2: Missing php artisan optimize

**What goes wrong:** Application loads configuration from `.env` on every request, parses all route files, compiles Blade views on-demand, resulting in slow response times.

**Why it happens:** Developers forget optimization is separate from deployment, or confuse it with `composer install`.

**How to avoid:**
- Run `php artisan optimize` after every deployment
- Or run individually: `config:cache`, `route:cache`, `view:cache`, `event:cache`
- Add to deployment script/CI pipeline
- Remember: After `config:cache`, `env()` only works inside config files

**Warning signs:** Laravel Telescope shows configuration being loaded from `.env` instead of cache, route registration happening on every request.

### Pitfall 3: N+1 Query Problem in Catalog

**What goes wrong:** Loading 20 products with pagination creates 1 query for products + 20 queries for categories + 20 queries for attributes = 41 queries per page. Database becomes bottleneck.

**Why it happens:** Forgetting to eager load relationships, especially when adding new features that display related data.

**How to avoid:**
- Use `Product::with(['categories', 'attributes'])->paginate(20)` in controllers
- Enable `Model::preventLazyLoading()` in development (Laravel 12)
- Use Laravel Telescope or Debugbar to monitor query count
- Test with realistic data volume (100+ products, not 5)

**Warning signs:** More than 5-10 queries per page load, query count grows linearly with pagination size, slow response times with database as bottleneck.

### Pitfall 4: Not Caching Expensive Queries

**What goes wrong:** Catalog page queries database for products, categories, settings on every request. With 100 concurrent users, database gets overwhelmed.

**Why it happens:** Premature optimization avoidance taken too far, not identifying expensive queries.

**How to avoid:**
- Cache product listings: `Cache::tags('catalog')->remember('products_page_' . $page, 3600, fn() => ...)`
- Cache site settings: `Setting::get('site.name')` should cache results
- Cache categories navigation menu: Rarely changes, queried on every page
- Set TTL based on update frequency (6 hours for products, 1 day for categories)
- Invalidate cache in admin panel on updates

**Warning signs:** Database CPU usage spikes with traffic, same queries in Telescope logs repeatedly, response time degrades linearly with traffic.

### Pitfall 5: Uploading Full-Size Images Without Optimization

**What goes wrong:** 5MB JPEG from phone camera served directly to users. Mobile users on 3G wait 30+ seconds for page to load. Google penalizes slow sites in search rankings.

**Why it happens:** Forgetting images are uploaded from phones/cameras at 4000x3000px resolution, assuming "it's just an image".

**How to avoid:**
- Resize on upload or on-the-fly (max 1920px width for large size)
- Generate thumbnails: 200x200, 600x600, 1200x1200
- Convert to WebP (80% quality) with JPEG fallback
- Use `loading="lazy"` on all images below the fold
- Store originals separately, never serve them directly

**Warning signs:** PageSpeed Insights shows "Serve images in next-gen formats", "Properly size images". Network tab shows multi-megabyte image downloads.

### Pitfall 6: Missing Database Indexes on Filtered/Sorted Columns

**What goes wrong:** Filtering by price or sorting by created_at triggers full table scan. Query takes 2+ seconds with 10,000 products.

**Why it happens:** Indexes added to primary lookup columns (slug, id) but not to columns used in WHERE, ORDER BY clauses added later.

**How to avoid:**
- Index columns used in WHERE: `price`, `category_id`, `is_active`
- Index columns used in ORDER BY: `created_at`, `view_count`
- Composite indexes for common combinations: `(is_active, created_at)`
- Use `EXPLAIN` to verify indexes are used
- Test queries with realistic data volume (10K+ rows)

**Warning signs:** Queries take >100ms with moderate data size, `EXPLAIN` shows "Using filesort" or "Using where" without index, slow query log entries.

### Pitfall 7: Hardcoded APP_URL and Asset Paths

**What goes wrong:** Sitemap contains `http://localhost/products/...`. OpenGraph images have relative paths. Absolute URLs in emails point to dev server.

**Why it happens:** Forgetting to update `APP_URL` in production `.env`, using hardcoded URLs instead of helpers.

**How to avoid:**
- Set `APP_URL=https://yourdomain.com` in production `.env`
- Use `route()`, `url()`, `asset()` helpers, never hardcode
- For images in meta tags: `asset('storage/' . $image)` generates absolute URLs
- Test sitemap.xml and OpenGraph tags in production before launch

**Warning signs:** Social media previews broken, sitemap validator errors, email links point to wrong domain.

### Pitfall 8: HTTPS Redirect Loop or Mixed Content

**What goes wrong:** Site loads but CSS/JS fail with mixed content warnings, or infinite redirect between HTTP/HTTPS.

**Why it happens:** Web server handles HTTPS termination (nginx proxy), Laravel sees HTTP requests, forces HTTPS redirect, proxy already did HTTPS.

**How to avoid:**
- Set `$proxies = '*'` in `TrustProxies` middleware if behind nginx/load balancer
- Or add to `AppServiceProvider::boot()`:
  ```php
  if ($this->app->environment('production')) {
      \URL::forceScheme('https');
  }
  ```
- Ensure `APP_URL` starts with `https://`
- Configure nginx to pass `X-Forwarded-Proto` header

**Warning signs:** Browser console shows "Mixed content blocked", redirect loop detected, HTTPS works but assets load via HTTP.

## Code Examples

Verified patterns from official sources:

### Admin Meta Tag Editor

```blade
{{-- resources/views/admin/products/edit.blade.php --}}
<div x-data="{ activeTab: 'basic' }">
    {{-- Tabs navigation --}}
    <div class="border-b border-gray-200">
        <button @click="activeTab = 'seo'"
                :class="activeTab === 'seo' ? 'border-blue-500 text-blue-600' : ''">
            SEO
        </button>
    </div>

    {{-- SEO tab --}}
    <div x-show="activeTab === 'seo'" class="mt-6 space-y-6">
        <div>
            <label class="block text-sm font-medium text-gray-700">Meta Title</label>
            <input type="text"
                   name="meta_title"
                   value="{{ old('meta_title', $product->meta_title) }}"
                   maxlength="60"
                   class="mt-1 block w-full"
                   placeholder="Оставьте пустым для автогенерации">
            <p class="mt-1 text-sm text-gray-500">
                Оптимально: 50-60 символов.
                Авто: "Купить {{ $product->name }} - цена {{ $product->formatted_price }} в Бишкеке"
            </p>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Meta Description</label>
            <textarea name="meta_description"
                      rows="3"
                      maxlength="160"
                      class="mt-1 block w-full"
                      placeholder="Оставьте пустым для автогенерации">{{ old('meta_description', $product->meta_description) }}</textarea>
            <p class="mt-1 text-sm text-gray-500">
                Оптимально: 150-160 символов. Включите ключевые характеристики.
            </p>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">URL (slug)</label>
            <input type="text"
                   name="slug"
                   value="{{ old('slug', $product->slug) }}"
                   class="mt-1 block w-full font-mono text-sm">
            <p class="mt-1 text-sm text-gray-500">
                Генерируется автоматически из названия. Используйте латиницу.
            </p>
        </div>

        <div class="bg-gray-50 p-4 rounded">
            <h4 class="font-medium text-gray-900 mb-2">Предпросмотр в Google:</h4>
            <div class="space-y-1">
                <div class="text-blue-600 text-lg">
                    {{ $product->meta_title ?: "Купить {$product->name} - цена {$product->formatted_price} в Бишкеке | Xiaomi Store" }}
                </div>
                <div class="text-green-700 text-sm">
                    {{ config('app.url') }}/products/{{ $product->slug }}
                </div>
                <div class="text-gray-600 text-sm">
                    {{ Str::limit($product->meta_description ?: $product->description, 160) }}
                </div>
            </div>
        </div>
    </div>
</div>
```

**Source:** Google SERP guidelines, Alpine.js documentation

### Pagination with Cache

```php
// app/Http/Controllers/ProductController.php
public function index(Request $request)
{
    $page = $request->get('page', 1);
    $filters = $request->only(['category', 'price_min', 'price_max', 'search']);
    $cacheKey = 'products_' . md5(json_encode(array_merge($filters, ['page' => $page])));

    $products = Cache::tags('catalog')->remember($cacheKey, now()->addHours(6), function () use ($request) {
        $query = Product::with(['categories', 'attributes'])
                       ->where('is_active', true);

        // Apply filters (existing filter logic)

        return $query->paginate(20)->appends($request->query());
    });

    return view('products.index', compact('products'));
}
```

**Source:** Laravel Cache documentation, pagination docs

### Robots.txt Generation

```php
// routes/web.php
Route::get('robots.txt', function () {
    $environment = app()->environment();

    if ($environment === 'production') {
        $content = "User-agent: *\n";
        $content .= "Disallow: /admin\n";
        $content .= "Disallow: /api\n";
        $content .= "Disallow: /cart\n";
        $content .= "Disallow: /checkout\n";
        $content .= "\n";
        $content .= "Sitemap: " . url('sitemap.xml') . "\n";
    } else {
        // Block all on non-production
        $content = "User-agent: *\n";
        $content .= "Disallow: /\n";
    }

    return response($content, 200)
        ->header('Content-Type', 'text/plain');
});
```

**Alternative:** Use `verschuur/laravel-robotstxt` package for more complex rules.

**Source:** Google robots.txt specification

## State of the Art

| Old Approach | Current Approach | When Changed | Impact |
|--------------|------------------|--------------|--------|
| Laravel Mix | Vite | Laravel 9.19+ (2022) | Faster builds, native ES modules, automatic versioning |
| Intervention Image v2 | Intervention Image v3 | 2023 | No longer depends on intervention/image, direct Imagick/GD drivers |
| Manual lazy loading (vanilla-lazyload.js) | Native `loading="lazy"` | 2020 (95%+ support in 2026) | Zero dependencies, browser-optimized thresholds |
| paginate() for all cases | cursorPaginate() for large datasets | Laravel 8+ | 10x faster for offset >1000, no total count query |
| Static robots.txt | Dynamic (environment-based) | 2020+ | Prevents indexing dev/staging, automatic sitemap URL |
| JSON in database | Separate attributes table | Project decision (Phase 1) | Efficient filtering without JSON queries |

**Deprecated/outdated:**
- **Laravel Mix:** Still works but Vite is standard. Mix development stopped.
- **Intervention Image v2:** Use v3 (intervention/image-laravel). V2 requires old intervention/image dependency.
- **JavaScript lazy loading libraries:** Not needed unless supporting IE11 or need advanced features (cancel downloads, callbacks).
- **Calling env() outside config files:** After `config:cache`, `env()` returns null. Use `config('app.name')` instead.

## Open Questions

Things that couldn't be fully resolved:

1. **WebP Browser Support in Kyrgyzstan**
   - What we know: 95%+ global support, all modern browsers since 2020
   - What's unclear: Actual browser usage in Kyrgyzstan, whether users update browsers regularly
   - Recommendation: Implement WebP with JPEG fallback (no risk, best of both worlds). `<picture>` element provides automatic fallback.

2. **Redis vs File Cache for Shared Hosting**
   - What we know: Redis is faster and supports cache tags, file cache works everywhere
   - What's unclear: Production hosting environment (VPS with Redis? Shared hosting?)
   - Recommendation: Use file cache initially, upgrade to Redis if on VPS. Code is identical (`CACHE_DRIVER=redis` in `.env`).

3. **Sitemap Size with Large Catalog**
   - What we know: Google recommends <50,000 URLs per sitemap, project has Xiaomi catalog (hundreds of products)
   - What's unclear: Future growth plans (will catalog exceed 50K products?)
   - Recommendation: Single sitemap.xml is fine for now. Spatie package supports sitemap index if needed later.

4. **Image Optimization: Upload vs On-the-Fly**
   - What we know: Upload-time optimization is faster for users, on-the-fly is more flexible
   - What's unclear: Storage space constraints, expected traffic volume
   - Recommendation: On-the-fly with caching (implemented in Pattern 3). First request generates, subsequent requests serve from cache. Best balance of flexibility and performance.

## Sources

### Primary (HIGH confidence)

- **artesaos/seotools** - https://github.com/artesaos/seotools (Laravel 12 compatible, verified 2026-01-23)
- **spatie/laravel-sitemap v7.3.8** - https://github.com/spatie/laravel-sitemap (Latest release Nov 2025)
- **Laravel 11/12 Deployment Docs** - https://laravel.com/docs/11.x/deployment (Official documentation)
- **Intervention Image v3** - https://github.com/Intervention/image-laravel (2023+, current version)
- **MDN: Lazy Loading** - https://developer.mozilla.org/en-US/docs/Web/Performance/Guides/Lazy_loading (Browser standards)
- **Laravel Cache Documentation** - https://laravel.com/docs/12.x/cache (Redis configuration, cache tags)

### Secondary (MEDIUM confidence)

- **Medium: Laravel Database Optimization (Jan 2026)** - Developer Awam article on indexing, EXPLAIN, slow query logs (recent, practical)
- **WebSearch: "Laravel pagination performance 2026"** - Multiple sources agree on cursorPaginate() for large datasets
- **WebSearch: "Native lazy loading vs JS libraries 2026"** - Consensus: native for basic use, JS for advanced features
- **Delicious Brains: Laravel Database Indexing** - Case study: 500K users, 8s to 180ms with proper indexes

### Tertiary (LOW confidence - requires validation)

- **Various blog posts on WebP conversion** - Techniques consistent but quality settings vary (70-90%)
- **Laravel deployment checklists** - Multiple sources, some contradictory on specific nginx config
- **Breadcrumb package comparisons** - diglactic/laravel-breadcrumbs most popular but alternatives exist

## Metadata

**Confidence breakdown:**
- Standard stack: HIGH - All packages verified in Packagist, GitHub stars 1K+, Laravel 12 support confirmed
- Architecture: HIGH - Patterns from official docs, verified with real project structure
- Pitfalls: HIGH - From official Laravel docs, recent Medium articles (Jan 2026), WebSearch consensus

**Research date:** 2026-01-23
**Valid until:** ~2026-03-23 (60 days - stable ecosystem, slow-moving best practices)

**Project-specific notes:**
- artesaos/seotools already installed (composer.json)
- spatie/laravel-sluggable already installed - slug generation ready
- Database already has meta_title, meta_description columns
- Indexes on slug, name, is_active already present
- Laravel 12.48.1 (latest stable) - all packages compatible
- Existing eager loading patterns in phases 1-3 (good foundation)
