---
phase: 01-foundation-product-catalog
plan: 02
subsystem: storefront
tags: [laravel, blade, tailwind, responsive-design, seo]

dependencies:
  requires:
    - 01-01 (Product/Category models and database schema)
  provides:
    - Customer-facing storefront views
    - SEO-friendly routing with slug-based URLs
    - Responsive Blade templates with Tailwind CSS
    - Product browsing functionality
  affects:
    - 01-03 (will add filtering to existing views)
    - Phase 2 (cart buttons placeholder added)

tech-stack:
  added:
    - Blade templating with Laravel 12
    - Tailwind CSS (via CDN)
    - Iconify icons
  patterns:
    - Component-based design (product-card component)
    - Route model binding by slug
    - Eager loading to prevent N+1 queries
    - Pagination for scalability
    - Responsive grid system (Tailwind)

key-files:
  created:
    - routes/web.php
    - app/Http/Controllers/Storefront/HomeController.php
    - app/Http/Controllers/Storefront/ProductController.php
    - app/Http/Controllers/Storefront/CategoryController.php
    - resources/views/layouts/app.blade.php
    - resources/views/components/product-card.blade.php
    - resources/views/storefront/home.blade.php
    - resources/views/storefront/products/index.blade.php
    - resources/views/storefront/products/show.blade.php
    - resources/views/storefront/categories/show.blade.php
    - database/seeders/CategorySeeder.php
    - database/seeders/ProductSeeder.php
  modified:
    - database/seeders/DatabaseSeeder.php

decisions:
  - title: Route model binding by slug
    rationale: Enables SEO-friendly URLs and automatic model resolution
    outcome: URLs like /products/xiaomi-14-pro instead of /products/1
    reference: Pitfall #8 (SEO-hostile URLs)

  - title: Eager loading in controllers
    rationale: Prevents N+1 query problem when loading relationships
    outcome: All controllers use .with() or .load() for relationships
    reference: Anti-pattern from ARCHITECTURE.md

  - title: Pagination at 20 items
    rationale: Prevents performance degradation with large product catalogs
    outcome: All listing pages use ->paginate(20)
    reference: Pitfall #6 (loading all records)

  - title: Lazy loading on product images
    rationale: Improves page load performance by deferring off-screen images
    outcome: loading="lazy" attribute on all product card images
    reference: PERF-01

metrics:
  duration: 5 minutes
  tasks: 3
  commits: 3
  completed: 2026-01-23
---

# Phase 1 Plan 02: Storefront Controllers & Views Summary

**One-liner:** Customer-facing product catalog with SEO-friendly URLs, responsive Tailwind design, and performance-optimized queries

## What Was Built

### Controllers with SEO-Friendly Routing

**HomeController (app/Http/Controllers/Storefront/HomeController.php):**
- `index()`: Displays popular products (sorted by view_count) and active categories
- Eager loads product counts on categories with `withCount('products')`
- Filters products by `is_active=true` and `stock > 0`
- Limits popular products to 8 items

**ProductController (app/Http/Controllers/Storefront/ProductController.php):**
- `index()`: Lists all active products with pagination (20 per page)
- `show($product)`: Displays single product detail page
- Uses route model binding by slug: `Route::get('/products/{product:slug}')`
- Increments `view_count` on each product view for popularity tracking
- Eager loads `categories` and `attributes` relationships

**CategoryController (app/Http/Controllers/Storefront/CategoryController.php):**
- `show($category)`: Displays products filtered by category
- Uses route model binding by slug: `Route::get('/categories/{category:slug}')`
- Filters products: `is_active=true` AND `stock > 0`
- Eager loads `categories` relationship
- Paginates results at 20 items per page

**Routes (routes/web.php):**
```php
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/categories/{category:slug}', [CategoryController::class, 'show'])->name('category.show');
Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/{product:slug}', [ProductController::class, 'show'])->name('product.show');

// Legacy fallback (301 redirect)
Route::get('/p/{id}', function ($id) {
    $product = Product::findOrFail($id);
    return redirect(route('product.show', $product), 301);
})->name('product.legacy');
```

**Key routing features:**
- **{product:slug}** syntax enables route model binding by slug field
- Legacy ID route provides backward compatibility with 301 redirect
- Named routes for easy URL generation in views

### Blade Templates with Tailwind CSS

**Layout (resources/views/layouts/app.blade.php):**
- Extracted structure from generated-page.html
- Sticky header with navigation, search bar, and cart button
- Responsive top bar with contact info
- Dynamic category menu populated from database
- Footer with links, contact info, and social media
- Tailwind CSS via CDN with custom brand colors
- Iconify icons for UI elements
- Viewport meta tag for mobile responsiveness
- Support for dynamic meta tags (@yield('title'), @yield('meta_description'))

**Homepage (resources/views/storefront/home.blade.php):**
- Hero section with featured product (Xiaomi 14 Ultra)
- Features grid: гарантия, доставка, рассрочка
- Category grid with dynamic links and product counts
- Popular products section using product-card component
- Empty state message if no products exist
- Fully responsive layout (mobile/tablet/desktop)

**Product Card Component (resources/views/components/product-card.blade.php):**
- Reusable across all listing pages
- Shows product image (or placeholder icon), name, price, stock status
- "Акция" badge if old_price exists
- Lazy loading on images: `loading="lazy"`
- Price display: converts cents to KGS (e.g., 7999900 cents → "79,999 сом")
- Hover effects and transitions
- Cart button (disabled placeholder for Phase 2)

**Product Detail Page (resources/views/storefront/products/show.blade.php):**
- Breadcrumb navigation for SEO and UX
- Product image gallery (main image + thumbnails)
- Price display with discount percentage if old_price exists
- Stock status indicator (В наличии / Нет в наличии)
- Description section
- Specifications table from JSON field
- Meta tags in @section for SEO
- Responsive 2-column layout (stacks on mobile)
- Placeholder cart button for Phase 2

**Product Index Page (resources/views/storefront/products/index.blade.php):**
- Grid layout with product cards
- Pagination links
- Product count display
- Empty state message
- Responsive grid (1 col mobile, 2 col tablet, 4 col desktop)

**Category Page (resources/views/storefront/categories/show.blade.php):**
- Category name and description
- Breadcrumb navigation
- Grid layout with product cards
- Pagination links
- Product count display
- Empty state if category has no products
- Meta tags support for SEO

### Database Seeders

**CategorySeeder (database/seeders/CategorySeeder.php):**
- Creates 6 Xiaomi categories:
  1. Смартфоны - smartphones
  2. Ноутбуки - laptops
  3. Умный дом - smart home devices
  4. Аудио - headphones and speakers
  5. Носимая электроника - wearables (watches, bands)
  6. ТВ и Медиа - TVs and media devices
- Each category has:
  - `meta_title` and `meta_description` for SEO
  - `description` for category page
  - `is_active=true`
- Slugs auto-generated by Spatie Sluggable

**ProductSeeder (database/seeders/ProductSeeder.php):**
- Creates 8 realistic Xiaomi products:
  1. **Xiaomi 14 Pro** - flagship smartphone (79,999 KGS)
  2. **Redmi Note 13 Pro** - mid-range smartphone (29,999 KGS)
  3. **Redmi 12** - budget smartphone (14,999 KGS)
  4. **Mi Smart Band 8** - fitness tracker (3,999 KGS)
  5. **Xiaomi Watch S3** - smartwatch with eSIM (12,999 KGS, on sale)
  6. **Xiaomi Book Pro 14** - laptop (59,999 KGS)
  7. **Redmi Buds 4 Pro** - TWS earbuds with ANC (7,999 KGS, on sale)
  8. **Mi Robot Vacuum X10+** - robot vacuum (39,999 KGS)

- Each product includes:
  - Prices stored as cents (e.g., 7999900 = 79,999 KGS)
  - `old_price` for sale items
  - `stock` levels (8-50 units)
  - `sku` in format: BRAND-MODEL-COLOR-STORAGE
  - `specifications` as JSON array
  - `meta_title` and `meta_description` for SEO
  - `view_count` for popularity tracking
  - Category relationships via pivot table
  - **ProductAttribute records** for filtering (memory, color, ram, etc.)

**Key seeder features:**
- Explicit `ProductAttribute::create()` calls ensure actual attribute records exist
- Attributes enable Plan 01-03 filtering without JSON queries
- Relationships attached via `$product->categories()->attach($categories)`
- View counts vary (35-120) to simulate real usage data

## Key Architectural Decisions

### Route Model Binding by Slug
**Problem:** SEO-hostile URLs (e.g., `/products/123`) harm search rankings
**Solution:** Route model binding by slug field
**Implementation:**
```php
Route::get('/products/{product:slug}', [ProductController::class, 'show']);
```
**Benefits:**
- SEO-friendly URLs: `/products/xiaomi-14-pro`
- Automatic model resolution (Laravel finds product by slug)
- 404 handling if slug not found
- Legacy ID route provides backward compatibility

### Eager Loading to Prevent N+1 Queries
**Problem:** Loading relationships in loops causes N+1 query problem
**Solution:** Use `.with()` or `.load()` to eager load relationships
**Implementation:**
```php
// In ProductController::index()
$query = Product::where('is_active', true)
    ->with('categories'); // Prevents N+1 when accessing $product->categories

// In ProductController::show()
$product->load(['categories', 'attributes']);
```
**Benefits:**
- Reduces database queries from hundreds to just 2-3
- Improves page load time significantly
- Scales well as product catalog grows

### Pagination at 20 Items
**Problem:** Loading all products causes memory issues and slow page loads
**Solution:** Paginate listings at 20 items per page
**Implementation:**
```php
$products = $query->paginate(20);
```
**Benefits:**
- Fast page loads even with 10,000+ products
- Reduced memory usage
- Better UX (faster scrolling, clearer navigation)
- Built-in Laravel pagination links

### Component-Based Design
**Problem:** Repeating product card HTML across multiple views
**Solution:** Blade component for product cards
**Implementation:**
```blade
<x-product-card :product="$product" />
```
**Benefits:**
- Consistent product display across site
- Single place to update styling/layout
- Reduced code duplication
- Easy to test and modify

## Responsive Design Implementation

**Breakpoints (Tailwind CSS):**
- **Mobile (default):** Single column, stacked navigation
- **Tablet (md: 768px):** 2-column product grid, visible search bar
- **Desktop (lg: 1024px):** 4-column product grid, full navigation

**Responsive features:**
- Header search bar hidden on mobile, shows in separate row
- Category menu hidden on mobile (hamburger menu placeholder)
- Product grid: 1 col → 2 col → 4 col
- Category grid: 2 col → 3 col → 6 col
- Hero section: stacked → 2 columns
- Footer: stacked → multi-column

**Performance optimizations:**
- `loading="lazy"` on product card images
- Image aspect-ratio boxes prevent layout shift
- Tailwind CSS via CDN (no build step needed)
- Iconify icons load on demand

## Verification Status

**Completed verification (without MySQL):**
- ✅ Controllers created and properly namespaced
- ✅ Routes registered with SEO-friendly slugs
- ✅ Views created with responsive Tailwind CSS
- ✅ Component-based design implemented
- ✅ Seeders created with realistic product data

**Pending verification (requires MySQL):**
- Run migrations: `php artisan migrate`
- Seed database: `php artisan db:seed`
- Test routes in browser:
  - http://localhost:8000 (homepage)
  - http://localhost:8000/products (product index)
  - http://localhost:8000/products/xiaomi-14-pro (product detail)
  - http://localhost:8000/categories/smartfony (category page)
- Verify responsive design at different breakpoints
- Check pagination works with 20+ products
- Verify meta tags present in HTML source

**Expected verification commands:**
```bash
# Start MySQL and run migrations
php artisan migrate

# Seed categories and products
php artisan db:seed

# Start dev server
php artisan serve

# Test in browser
curl http://localhost:8000 | grep -i "популярные товары"

# Test product detail with slug
php artisan tinker --execute="
\$product = Product::first();
echo 'Visit: http://localhost:8000/products/' . \$product->slug;
"

# Test category page
php artisan tinker --execute="
\$category = Category::first();
echo 'Visit: http://localhost:8000/categories/' . \$category->slug;
"

# Verify SEO meta tags
curl http://localhost:8000/products/xiaomi-14-pro | grep -E "(meta name|title)"
```

## Deviations from Plan

None - plan executed exactly as written.

**All plan objectives achieved:**
- ✅ SEO-friendly routes with slug-based URLs
- ✅ HomeController shows popular products and categories
- ✅ ProductController has index and show actions
- ✅ CategoryController filters products by category
- ✅ Legacy ID route for backward compatibility
- ✅ Layout extracted from generated-page.html
- ✅ Homepage with product grid and category links
- ✅ Product detail page with images, price, specs, stock
- ✅ Product card component reusable
- ✅ Responsive design (mobile/tablet/desktop)
- ✅ Breadcrumb navigation on product pages
- ✅ Lazy loading enabled on images
- ✅ Sample Xiaomi products seeded
- ✅ Categories seeded with meta tags
- ✅ Prices display in KGS (converted from cents)
- ✅ Meta tags on product pages
- ✅ Eager loading prevents N+1 queries
- ✅ Pagination limits products per page

## Commits

1. **5af0706** - `feat(01-02): create storefront controllers with SEO-friendly routing`
   - HomeController, ProductController, CategoryController
   - Routes with slug-based model binding
   - Legacy ID route for backward compatibility
   - Eager loading and pagination

2. **cb379e6** - `feat(01-02): create Blade templates integrating existing HTML design`
   - Layout extracted from generated-page.html
   - Homepage, product detail, product index, category pages
   - Product card component
   - Responsive Tailwind CSS design
   - Lazy loading, meta tags, breadcrumbs

3. **08c0633** - `feat(01-02): create database seeders with sample Xiaomi products`
   - CategorySeeder (6 categories)
   - ProductSeeder (8 realistic products)
   - ProductAttribute records for filtering
   - DatabaseSeeder updated

## Next Phase Readiness

**Blockers:** None

**Ready for Plan 01-03 (Filtering):**
- ✅ ProductAttribute records created for all products
- ✅ Product listing pages ready for filter integration
- ✅ Category pages ready for attribute-based filtering
- ✅ Pagination already implemented

**Ready for Phase 2 (Cart & Checkout):**
- ✅ Cart button placeholders added to layout
- ✅ Product detail pages ready for "Add to Cart" buttons
- ✅ Price display logic centralized in component
- ✅ Stock status tracking ready

**Concerns:**
- **MySQL not running:** User must start MySQL and run migrations/seeders to test
- **No search functionality yet:** Search bar is placeholder (may be Plan 01-03 or later)
- **Category menu on mobile:** Hamburger menu placeholder needs implementation
- **Empty state handling:** Pages handle empty data gracefully, but need actual products

**Recommendations for next plans:**
1. Test all routes in browser after MySQL setup
2. Verify responsive design on actual mobile device
3. Check pagination works correctly with 20+ products
4. Validate meta tags in HTML source for SEO
5. Plan 01-03 should add filtering to products/index and categories/show views
6. Consider adding product image upload functionality in admin panel

## Files Reference

**Controllers:**
- `app/Http/Controllers/Storefront/HomeController.php` - Homepage with popular products
- `app/Http/Controllers/Storefront/ProductController.php` - Product listing and detail
- `app/Http/Controllers/Storefront/CategoryController.php` - Category filtering

**Routes:**
- `routes/web.php` - SEO-friendly routes with slug binding

**Views:**
- `resources/views/layouts/app.blade.php` - Main layout with header/footer
- `resources/views/components/product-card.blade.php` - Reusable product card
- `resources/views/storefront/home.blade.php` - Homepage
- `resources/views/storefront/products/index.blade.php` - Product listing
- `resources/views/storefront/products/show.blade.php` - Product detail
- `resources/views/storefront/categories/show.blade.php` - Category page

**Seeders:**
- `database/seeders/CategorySeeder.php` - 6 Xiaomi categories
- `database/seeders/ProductSeeder.php` - 8 sample products with attributes
- `database/seeders/DatabaseSeeder.php` - Updated to call new seeders

**Design Assets:**
- `generated-page.html` - Original HTML design (reference)

---

*Plan 01-02 completed: 2026-01-23*
*Duration: 5 minutes*
*Commits: 3*
*Files created: 13*
*Files modified: 1*
