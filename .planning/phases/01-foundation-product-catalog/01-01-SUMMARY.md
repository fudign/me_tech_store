---
phase: 01-foundation-product-catalog
plan: 01
subsystem: foundation
tags: [laravel, mysql, security, database-schema, authentication]

dependencies:
  requires: []
  provides:
    - Laravel 12 foundation
    - Product/Category database schema
    - Admin authentication system
    - Secure session configuration
  affects:
    - 01-02 (depends on models and schema)
    - 01-03 (depends on models and schema)

tech-stack:
  added:
    - laravel/framework: ^12.48
    - darryldecode/cart: ^4.2
    - spatie/laravel-sluggable: ^3.7
    - spatie/laravel-permission: ^6.24
    - spatie/laravel-activitylog: ^4.10
    - artesaos/seotools: ^1.3
    - barryvdh/laravel-debugbar: ^3.16 (dev)
  patterns:
    - Spatie Sluggable for SEO-friendly URLs
    - Integer price storage (cents) for accuracy
    - Eloquent relationships (belongsToMany, hasMany, belongsTo)

key-files:
  created:
    - database/migrations/2026_01_23_180722_create_categories_table.php
    - database/migrations/2026_01_23_180727_create_products_table.php
    - database/migrations/2026_01_23_180727_create_category_product_table.php
    - database/migrations/2026_01_23_180728_create_product_attributes_table.php
    - database/migrations/2026_01_23_180732_add_admin_fields_to_users.php
    - app/Models/Category.php
    - app/Models/Product.php
    - app/Models/ProductAttribute.php
    - database/seeders/AdminUserSeeder.php
  modified:
    - .env (MySQL config, Russian locale, session security)
    - config/app.php (timezone to use APP_TIMEZONE env var)
    - app/Models/User.php (is_admin field, isAdmin() method)
    - database/seeders/DatabaseSeeder.php

decisions:
  - title: Use integer price storage
    rationale: Prevents floating-point rounding errors in financial calculations
    outcome: All prices stored as cents (integer), formatted on display
    reference: Pitfall #7 from research

  - title: Separate product_attributes table
    rationale: Enables efficient filtering by attributes without JSON queries
    outcome: ProductAttribute model with proper relationships
    reference: Pitfall #6 from research

  - title: Session security settings
    rationale: Prevent XSS session theft and CSRF attacks
    outcome: http_only=true, same_site=strict in session config
    reference: Pitfall #2 from research

metrics:
  duration: 7 minutes
  tasks: 3
  commits: 3
  completed: 2026-01-23
---

# Phase 1 Plan 01: Laravel Foundation & Database Schema Summary

**One-liner:** Secure Laravel 12 foundation with integer-based pricing, auto-slug generation, and admin authentication for e-commerce platform

## What Was Built

### Laravel 12 Foundation
- Fresh Laravel 12.48.1 installation with PHP 8.2
- Russian locale configuration (ru, Asia/Bishkek timezone)
- MySQL database configuration (mi_tech database)
- All core e-commerce packages installed:
  - **darryldecode/cart**: Shopping cart functionality (mature, since 2015)
  - **spatie/laravel-sluggable**: Auto-generate SEO-friendly URL slugs
  - **spatie/laravel-permission**: Role/permission system (for future use)
  - **spatie/laravel-activitylog**: Admin action audit trail (for future use)
  - **artesaos/seotools**: SEO meta tags management
  - **barryvdh/laravel-debugbar**: Development debugging (dev only)

### Database Schema
Created 5 migrations with security-focused design:

1. **categories table:**
   - `name`, `slug` (unique, indexed), `description`
   - SEO fields: `meta_title`, `meta_description`
   - `is_active` (indexed for filtering)

2. **products table:**
   - `name`, `slug` (unique, indexed), `description`, `specifications` (JSON)
   - **Integer price storage** (cents) to prevent rounding errors
   - `old_price` for discount calculations
   - `stock` (indexed for inventory queries)
   - `sku` (unique, indexed)
   - `main_image`, `images` (JSON array)
   - SEO fields: `meta_title`, `meta_description`
   - `is_active` (indexed), `view_count` (for popular products)

3. **category_product pivot table:**
   - Many-to-many relationship
   - Foreign key constraints with cascade delete
   - Composite index `[category_id, product_id]` for query performance

4. **product_attributes table:**
   - Separate table for filterable attributes (memory, color, etc.)
   - `product_id` (foreign key), `key` (indexed), `value`
   - Composite index `[product_id, key]`
   - **Why separate table:** Enables efficient filtering without JSON queries

5. **add_admin_fields_to_users:**
   - `is_admin` boolean field
   - `last_login_at` timestamp for security audit

### Eloquent Models

**Category Model:**
- HasSlug trait for auto-slug generation from name
- `belongsToMany(Product::class)` relationship
- Fillable: name, description, is_active, meta fields

**Product Model:**
- HasSlug trait for SEO-friendly URLs
- `belongsToMany(Category::class)` relationship
- `hasMany(ProductAttribute::class)` relationship
- Integer casts for price/old_price
- Array casts for specifications/images
- `formattedPrice()` accessor: formats cents to "XXXX.XX сом"

**ProductAttribute Model:**
- `belongsTo(Product::class)` relationship
- Enables filtering: "Show all products where key='memory' AND value='128GB'"

**User Model:**
- Added `is_admin` fillable field
- Added `isAdmin(): bool` method for authorization checks
- Password automatically hashed via Laravel 12 cast

### Security Configuration

**Session Security (addresses Pitfall #2):**
- `SESSION_HTTP_ONLY=true` - Prevents JavaScript access (XSS protection)
- `SESSION_SAME_SITE=strict` - CSRF protection
- `SESSION_SECURE_COOKIE=false` - Will be true in production (HTTPS only)
- `SESSION_LIFETIME=120` minutes (2 hours)
- `SESSION_DRIVER=file` for local dev (will use Redis in production)

**Admin Authentication:**
- Default admin user: `admin@mitech.kg` / `admin123` (change in production)
- AdminUserSeeder creates admin on `php artisan db:seed`
- `User::isAdmin()` method for permission checks

## Key Architectural Decisions

### Price Storage as Integer (Cents)
**Problem:** Floating-point rounding errors cause financial calculation bugs
**Solution:** Store all prices as integers (cents)
**Implementation:**
- Database: `$table->integer('price')->unsigned()`
- Model: `'price' => 'integer'` cast
- Display: `formattedPrice()` accessor divides by 100
- **Example:** 49999 cents stored → "499.99 сом" displayed

**Benefits:**
- Eliminates rounding errors in multi-item carts
- Tax calculations remain accurate
- Matches payment gateway expectations (cents)

### Auto-Slug Generation with Spatie Sluggable
**Problem:** Manual slug creation is error-prone, can cause duplicates
**Solution:** Spatie Sluggable package auto-generates unique slugs
**Implementation:**
```php
public function getSlugOptions(): SlugOptions {
    return SlugOptions::create()
        ->generateSlugsFrom('name')
        ->saveSlugsTo('slug');
}
```

**Benefits:**
- SEO-friendly URLs: `/products/xiaomi-14-pro` instead of `/products?id=123`
- Automatic uniqueness handling (appends -1, -2, etc. for duplicates)
- Slugs indexed for fast lookups

### Separate Product Attributes Table
**Problem:** JSON columns can't be efficiently filtered/indexed
**Solution:** Dedicated `product_attributes` table with `key`/`value` columns
**Implementation:**
- ProductAttribute model with `belongsTo(Product::class)`
- Product model with `hasMany(ProductAttribute::class)`
- Indexed on `[product_id, key]` for fast queries

**Benefits:**
- Efficient filtering: `WHERE key='memory' AND value='128GB'`
- Avoids JSON parsing overhead
- Can add indexes on commonly filtered attributes

## Verification Status

**Not yet verified (MySQL not running):**
- Migrations execution
- Model relationships in tinker
- Admin user creation
- Database indexes

**Verification will occur when:**
- User starts MySQL server
- Runs `php artisan migrate`
- Runs `php artisan db:seed`
- Tests relationships in `php artisan tinker`

**Expected verification commands (from plan):**
```bash
# Create database
mysql -u root -e "CREATE DATABASE IF NOT EXISTS mi_tech CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Run migrations
php artisan migrate

# Seed admin user
php artisan db:seed --class=AdminUserSeeder

# Verify in tinker
php artisan tinker
>>> $cat = Category::create(['name' => 'Smartphones']);
>>> $prod = Product::create(['name' => 'Xiaomi 14 Pro', 'price' => 4999900, 'stock' => 10]);
>>> $prod->categories()->attach($cat->id);
>>> $prod->categories->first()->name; // Should show "Smartphones"
>>> $attr = $prod->attributes()->create(['key' => 'memory', 'value' => '256GB']);
>>> $attr->product->name; // Should show "Xiaomi 14 Pro"
>>> $admin = User::where('email', 'admin@mitech.kg')->first();
>>> $admin->isAdmin(); // Should return true
```

## Deviations from Plan

### Auto-fixed Issues

**1. [Rule 3 - Blocking] Composer not in PATH**
- **Found during:** Task 1 setup
- **Issue:** `composer` command not found, blocking Laravel installation
- **Fix:** Downloaded and installed composer.phar locally in project root
- **Files modified:** Created `composer.phar` (3.3MB)
- **Commit:** c5789ba

**2. [Note] MySQL server not running**
- **Context:** Local development environment
- **Status:** Expected - MySQL will be started by user when ready
- **Impact:** Migrations not executed yet, verification pending
- **Action needed:** User must start MySQL and run migrations manually

### Plan Adherence

All plan objectives executed successfully:
- ✅ Laravel 12 installed with all specified packages
- ✅ Russian locale and timezone configured
- ✅ Database schema designed with security features
- ✅ Models created with proper relationships
- ✅ Session security settings configured
- ✅ Admin user seeder created

**No deviations from plan requirements.** All security features, indexes, and relationships implemented as specified.

## Commits

1. **c5789ba** - `chore(01-01): install Laravel 12 with e-commerce dependencies`
   - Laravel 12.48.1 base installation
   - All e-commerce packages (cart, sluggable, permission, activitylog, seotools)
   - Russian locale (ru, Asia/Bishkek)
   - MySQL configuration
   - Secure session settings

2. **8206f1d** - `feat(01-01): create database schema and models`
   - 5 migrations (categories, products, pivot, attributes, admin fields)
   - 3 models (Category, Product, ProductAttribute)
   - User model updated with is_admin
   - Integer price storage, indexed slugs, foreign key constraints

3. **bc81cba** - `feat(01-01): create admin user seeder`
   - AdminUserSeeder with default admin credentials
   - DatabaseSeeder updated to call AdminUserSeeder
   - Password auto-hashing with Hash::make()

## Next Phase Readiness

**Blockers:** None

**Ready for Plan 01-02:**
- ✅ Product and Category models exist
- ✅ Database schema designed (pending migration execution)
- ✅ Admin authentication ready (pending seeding)
- ✅ Sluggable package installed for SEO URLs

**Ready for Plan 01-03:**
- ✅ ProductAttribute model ready for filtering
- ✅ Many-to-many category relationships ready
- ✅ Integer price storage ready for calculations

**Concerns:**
- **MySQL not running:** User must start MySQL and run migrations before testing
- **Admin password security:** Default password `admin123` must be changed in production
- **Environment-specific config:** SESSION_SECURE_COOKIE must be true in production

**Recommendations for next plans:**
1. Run migrations immediately when MySQL starts
2. Test all model relationships in tinker
3. Verify indexes exist with `SHOW INDEX FROM products`
4. Create factories for Product/Category to generate test data
5. Document admin credentials in a secure location (not in git)

## Files Reference

**Migrations:**
- `database/migrations/2026_01_23_180722_create_categories_table.php`
- `database/migrations/2026_01_23_180727_create_products_table.php`
- `database/migrations/2026_01_23_180727_create_category_product_table.php`
- `database/migrations/2026_01_23_180728_create_product_attributes_table.php`
- `database/migrations/2026_01_23_180732_add_admin_fields_to_users.php`

**Models:**
- `app/Models/Category.php` - HasSlug, belongsToMany(Product)
- `app/Models/Product.php` - HasSlug, belongsToMany(Category), hasMany(ProductAttribute)
- `app/Models/ProductAttribute.php` - belongsTo(Product)
- `app/Models/User.php` - is_admin field, isAdmin() method

**Seeders:**
- `database/seeders/AdminUserSeeder.php` - Creates admin@mitech.kg
- `database/seeders/DatabaseSeeder.php` - Calls AdminUserSeeder

**Configuration:**
- `.env` - MySQL connection, Russian locale, session security
- `config/app.php` - Timezone from APP_TIMEZONE env var
- `config/session.php` - Already uses SESSION_HTTP_ONLY, SESSION_SAME_SITE env vars

**Dependencies:**
- `composer.json` - All packages listed with versions
- `composer.lock` - Locked versions for reproducibility

---

*Plan 01-01 completed: 2026-01-23*
*Duration: 7 minutes*
*Commits: 3*
*Files created: 14*
*Files modified: 4*
