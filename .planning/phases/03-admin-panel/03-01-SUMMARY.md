---
phase: 03-admin-panel
plan: 01
subsystem: admin-catalog
tags: [laravel, crud, admin-panel, image-upload, alpine-js, form-validation]

requires:
  - 02-03-admin-order-management
  - 01-01-foundation-setup

provides:
  - admin-product-crud
  - multi-image-upload
  - dynamic-attributes
  - tabbed-form-interface

affects:
  - 03-02-category-management

tech-stack:
  added:
    - "Alpine.js for dynamic form interactions"
    - "Laravel Form Requests for validation"
    - "Storage facade for file uploads"
  patterns:
    - "Resource controller pattern"
    - "Form Request validation"
    - "Eager loading to prevent N+1 queries"
    - "Image handling with Storage::disk('public')"
    - "Dynamic attributes sync pattern"
    - "Alpine.js component state management"

key-files:
  created:
    - app/Http/Controllers/Admin/ProductController.php
    - app/Http/Requests/StoreProductRequest.php
    - app/Http/Requests/UpdateProductRequest.php
    - resources/views/admin/products/index.blade.php
    - resources/views/admin/products/create.blade.php
    - resources/views/admin/products/edit.blade.php
  modified:
    - routes/web.php

decisions:
  - slug: "price-input-kgs-storage-cents"
    title: "Price input in KGS, storage in cents"
    rationale: "Form inputs accept KGS (easier for admin), prepareForValidation converts to cents for storage (prevents rounding errors)"
  - slug: "multi-image-replace-on-update"
    title: "New images replace all existing on update"
    rationale: "Simplifies image management logic - admin uploads complete new set rather than managing individual additions/deletions"
  - slug: "main-image-radio-selection"
    title: "Main image selected via radio buttons"
    rationale: "Simple interface for selecting which image is primary (per CONTEXT requirement)"
  - slug: "alpine-js-for-tabs-and-attributes"
    title: "Alpine.js for tab state and dynamic attributes"
    rationale: "Lightweight client-side state management without full Vue/React (per RESEARCH recommendation)"
  - slug: "form-request-validation-pattern"
    title: "Form Request validation instead of controller validation"
    ratency: "Cleaner separation of concerns, reusable validation logic, custom error messages (per RESEARCH recommendation)"
  - slug: "attributes-delete-and-recreate"
    title: "Delete all attributes and recreate on update"
    rationale: "Simpler than sync logic - attributes are lightweight key-value pairs"
  - slug: "storage-facade-auto-filenames"
    title: "Storage::put() auto-generates safe filenames"
    rationale: "Prevents filename collisions, sanitizes unsafe characters automatically"
  - slug: "four-tab-form-structure"
    title: "Four-tab form: Basic, Images, Attributes, SEO"
    rationale: "Per CONTEXT requirement - organizes large form into logical sections, not one long page"
  - slug: "categories-multiple-checkboxes"
    title: "Multiple category selection via checkboxes"
    rationale: "Products can belong to multiple categories (many-to-many relationship)"

metrics:
  duration: "5 minutes"
  tasks: 3
  commits: 3
  files_changed: 7
  lines_added: 1339
  completed: "2026-01-23"
---

# Phase 03 Plan 01: Admin Product CRUD Summary

**One-liner:** Complete product CRUD with multi-image upload, dynamic attributes, and 4-tab form interface using Alpine.js

## What Was Built

### ProductController (app/Http/Controllers/Admin/ProductController.php)
Full CRUD implementation with 6 RESTful methods:

**index():**
- Paginated product list (20 per page)
- Eager loads categories and attributes (prevents N+1)
- Latest products first

**create() / edit():**
- Loads active categories for selection
- Edit loads product with all relationships

**store() / update():**
- Handles multi-image uploads to `storage/app/public/products`
- Stores image paths in JSON array
- Selects main image (index-based or first by default)
- Syncs many-to-many categories
- Deletes and recreates attributes (simple pattern)
- On update: deletes old images from storage if new ones uploaded

**destroy():**
- Deletes all product images from storage
- Cascading deletion handles attributes

### Form Requests
**StoreProductRequest / UpdateProductRequest:**
- Comprehensive validation rules (name, price, slug, images, attributes)
- `prepareForValidation()` converts price from KGS to cents
- Checkbox `is_active` conversion (on/null → true/false)
- Custom Russian validation messages
- UpdateProductRequest: unique slug excluding current product

**Validation highlights:**
- Image: max 10, each max 2MB, types: jpg, jpeg, png, webp
- Attributes: required_with pattern (both key and value needed)
- Slug: unique, nullable (auto-generated if empty)
- Categories: exists validation for many-to-many

### Product List View (index.blade.php)
**Desktop table:**
- Columns: Image thumbnail | Name + slug | Price (with old_price strikethrough) | Categories | Status badge | Actions
- Edit and Delete buttons with icons
- Active highlight for current route

**Mobile cards:**
- Stacked layout with all info
- Full-width edit/delete buttons
- Same data as desktop table

**Features:**
- Success message banner with Alpine.js auto-dismiss (3s)
- Delete confirmation modal (Alpine.js, not browser confirm)
- Pagination links
- Empty state with icon
- Responsive: `md:block` for table, `md:hidden` for cards

### Product Forms (create.blade.php / edit.blade.php)
**4-tab structure:**
1. **Основное (Basic):** name*, slug, description, price*, old_price, categories (checkboxes), is_active
2. **Фото (Images):** multi-file input, drag & drop zone, main image selection (radio buttons on edit)
3. **Характеристики (Attributes):** dynamic key-value pairs with add/remove (Alpine.js)
4. **SEO:** meta_title, meta_description

**Alpine.js component (`productForm()`):**
- `tab` state for switching between tabs
- `attributes` array for dynamic attribute management
- `addAttribute()` pushes new empty pair
- `removeAttribute(index)` splices out attribute

**Edit form differences:**
- Pre-fills all fields with existing data
- Converts price cents → KGS for display
- Shows current images grid with main image indicator
- Attributes loaded from database into Alpine state
- Warning: new images replace all existing

**Form features:**
- Validation error banner at top (list of all errors)
- x-cloak prevents flash of unstyled content
- CSRF protection
- Enctype multipart/form-data for file uploads
- Cancel button returns to index

### Routes
Added to admin group (auth middleware):
```php
Route::resource('products', AdminProductController::class);
```

Generates 7 routes: index, create, store, show, edit, update, destroy

## Technical Implementation

### Image Handling Pattern
```php
// Upload
$path = Storage::disk('public')->put('products', $image);
// Auto-generates safe filename in storage/app/public/products/

// Delete
Storage::disk('public')->delete($imagePath);

// Store as JSON array in products.images column
$product->images = ['products/abc123.jpg', 'products/def456.jpg'];
$product->main_image = 'products/abc123.jpg';
```

**Benefits:**
- Auto-generated filenames prevent collisions
- No manual filename sanitization needed
- Easy to delete on product removal
- JSON storage allows multiple images per product

### Attributes Sync Pattern
```php
// Delete all existing
$product->attributes()->delete();

// Recreate from form input
foreach ($data['attributes'] as $attribute) {
    if (!empty($attribute['key']) && !empty($attribute['value'])) {
        $product->attributes()->create([
            'key' => $attribute['key'],
            'value' => $attribute['value'],
        ]);
    }
}
```

**Why not sync():**
- Attributes have no ID from frontend (not updating existing records)
- Simple delete+recreate is clearer than diffing arrays
- Attributes are lightweight - performance impact negligible

### Price Conversion Strategy
**Frontend:** Admin enters 5000 (KGS)
**prepareForValidation:** Multiplies by 100 → 500000 (cents)
**Storage:** 500000 (integer, no rounding errors)
**Display (edit):** `$product->price / 100` → 5000 (KGS)

**Why cents storage:**
- Prevents floating-point rounding errors
- Standard practice for currency (Stripe, Shopify use cents)
- Arithmetic operations safe (no 0.1 + 0.2 = 0.30000000004)

### Alpine.js Benefits
- **No build step:** Loaded from CDN, works immediately
- **Minimal learning curve:** Familiar Vue-like syntax
- **Perfect for this use case:** Tab switching, dynamic arrays
- **Lightweight:** ~15KB (vs 40KB Vue, 70KB React)

**Alternative considered:** Vanilla JS would work but Alpine.js makes it cleaner (x-show, x-for, x-data patterns)

## Deviations from Plan

None - plan executed exactly as written.

## Decisions Made

### 1. Image replacement strategy (update)
**Decision:** Uploading new images deletes all existing images
**Alternatives considered:**
- Individual image management (checkboxes to delete, add to existing)
- Drag & drop reordering

**Why chosen:** Simplifies logic significantly. Admin uploads complete new photo set rather than managing individual changes. Yellow warning displayed on edit form to prevent accidents.

### 2. Attribute management pattern
**Decision:** Delete all attributes, recreate from form
**Alternatives considered:**
- Track IDs and use sync() like categories
- Soft delete and restore

**Why chosen:** Attributes have no stable ID from frontend. Creating new records each time is simple and clear. Performance impact minimal (typical product has 5-10 attributes).

### 3. Categories: multiple selection
**Decision:** Many-to-many checkboxes (product can have multiple categories)
**Alternatives considered:**
- Single category dropdown (simpler but less flexible)
- Hierarchical category tree

**Why chosen:** Real-world products often fit multiple categories (Xiaomi Mi 11 → Smartphones, Flagships, 5G devices). Many-to-many relationship was already in database schema from Phase 1.

### 4. Main image selection method
**Decision:** Radio buttons on edit form (select from existing images)
**Alternatives considered:**
- Always use first image as main
- Drag & drop reordering (first = main)
- Separate main image upload field

**Why chosen:** Per CONTEXT requirement ("Radio button выбор"). Simple and explicit. On create, first uploaded image is main by default.

## Next Phase Readiness

### Ready for:
- **03-02 Category Management:** Products can be assigned to categories (checkboxes ready)
- **03-03 Settings Management:** Basic admin panel structure established

### Blockers/Concerns:
- **No image validation preview:** Admin can't preview images before upload (browser file input limitation without JS FileReader)
- **Image replacement is destructive:** No way to add single image or reorder existing ones (acceptable per decision, but admin must be careful)
- **No image optimization:** Uploaded images stored as-is (consider intervention/image package for resize/compress in future)
- **Categories link in menu:** Menu now has "Категории" link but 03-02 implementation not done yet
- **Settings link in menu:** Menu has "Настройки" link but 03-03 implementation not done yet

### Manual Testing Recommended:
1. Visit `/admin/products` (requires auth)
2. Click "Добавить товар" → fill all 4 tabs → save
3. Verify product appears in list with thumbnail
4. Click Edit → verify all data pre-filled including attributes
5. Upload new images → verify old ones deleted, new ones shown
6. Delete product → verify images removed from `storage/app/public/products`

## Files Created

### Controllers & Requests (190 lines)
- `app/Http/Controllers/Admin/ProductController.php` (212 lines)
- `app/Http/Requests/StoreProductRequest.php` (93 lines)
- `app/Http/Requests/UpdateProductRequest.php` (95 lines)

### Views (1,146 lines)
- `resources/views/admin/products/index.blade.php` (257 lines)
- `resources/views/admin/products/create.blade.php` (330 lines)
- `resources/views/admin/products/edit.blade.php` (359 lines)

### Routes Modified
- `routes/web.php` (+2 lines: use statement, resource route)

## Commits

| Commit | Files | Description |
|--------|-------|-------------|
| 53ebf9c | 4 | ProductController + Form Requests + Routes |
| 4868723 | 1 | Product list view (index) |
| 5409b85 | 2 | Create/Edit forms with 4-tab structure |

## Performance Notes

**Eager loading:**
- `index()` uses `->with('categories', 'attributes')` to prevent N+1
- Each product would otherwise trigger 2 queries (categories, attributes)
- With 20 products per page: 40 saved queries

**Image storage:**
- Images stored in `storage/app/public` (outside web root)
- Symlink created: `public/storage → storage/app/public`
- Direct web access via `/storage/products/filename.jpg`

**Pagination:**
- 20 products per page (consistent with orders pagination)
- Prevents memory issues with large catalogs

## Security Notes

**File upload validation:**
- Type whitelist: jpg, jpeg, png, webp (no SVG - XSS risk)
- Size limit: 2MB per file, max 10 files
- Laravel validates MIME type (not just extension)
- Storage::put() generates safe random filenames

**Authorization:**
- Routes protected by `auth` middleware
- No role checking (all authenticated users are admins currently)

**CSRF:**
- All forms include @csrf directive
- Laravel validates token on POST/PUT/DELETE

**SQL injection:**
- Eloquent ORM (parameterized queries)
- Form Request validation sanitizes input

## What's Next

**Immediate (Phase 3 remaining plans):**
- 03-02: Category CRUD management
- 03-03: Site settings (contact info, social links)

**Future enhancements (post-Phase 3):**
- Image optimization (resize, compress, WebP conversion)
- Individual image management (add/remove without replacing all)
- Drag & drop image reordering
- Rich text editor for description (TinyMCE/CKEditor)
- Bulk actions (delete multiple products)
- Product import/export (CSV/Excel)
- Product duplication feature
- Image alt text fields (SEO)

---

**Plan completed:** 2026-01-23
**Duration:** 5 minutes
**Status:** ✅ All tasks complete, no deviations
