# Phase 3: Admin Panel - Research

**Researched:** 2026-01-23
**Domain:** Laravel Admin CRUD with file uploads, validation, and OpenCart-style UI
**Confidence:** HIGH

## Summary

This phase builds on the existing admin authentication and order management to add comprehensive content management capabilities. The research focused on Laravel patterns for admin CRUD operations, secure multi-file uploads, validation strategies, and OpenCart-style UI implementation.

**Key findings:**
- Laravel Form Requests provide the standard pattern for validation and authorization in admin operations
- File uploads should use Storage facade with public disk, storing files in `storage/app/public` with symbolic links
- Existing project already has Spatie Sluggable installed and configured, with patterns established in Phase 1
- Separate `product_attributes` table already exists for efficient filtering (Phase 1 decision)
- OpenCart-style admin layout already implemented in Phase 2 with sidebar navigation
- Alpine.js and Tailwind CSS already in use for frontend interactions

**Primary recommendation:** Build on existing patterns from Phase 2 admin implementation. Use Form Requests for validation, standard Laravel filesystem for uploads, maintain the established sidebar navigation structure, and leverage Alpine.js for dynamic form interactions (tabs, delete confirmations, dynamic attribute fields).

## Standard Stack

### Core (Already Installed)
| Library | Version | Purpose | Why Standard |
|---------|---------|---------|--------------|
| Laravel | 12.x | Backend framework | Latest stable, modern PHP patterns |
| Spatie Sluggable | 3.7 | Auto slug generation | Industry standard, already configured |
| Tailwind CSS | 3.x (CDN) | UI styling | Already used in project |
| Alpine.js | 3.x (CDN) | Frontend interactions | Already used for cart, lightweight |

### For This Phase
| Library | Version | Purpose | When to Use |
|---------|---------|---------|-------------|
| Form Requests | Built-in | Validation & authorization | Every create/update operation |
| Storage Facade | Built-in | File management | All image uploads |
| Intervention Image | Optional | Image processing | If need resize/optimize (recommended) |

### Alternatives Considered
| Instead of | Could Use | Tradeoff |
|------------|-----------|----------|
| Form Requests | Controller validation | Form Requests better for complex rules, reusability |
| Storage facade | Direct file operations | Storage provides abstraction, testing support |
| Spatie Settings | Custom settings table | Settings package overkill for 4 simple fields |

**Installation (if adding image optimization):**
```bash
composer require intervention/image-laravel
```

## Architecture Patterns

### Recommended Project Structure
```
app/
├── Http/
│   ├── Controllers/Admin/
│   │   ├── ProductController.php
│   │   ├── CategoryController.php
│   │   └── SettingsController.php
│   └── Requests/
│       ├── StoreProductRequest.php
│       ├── UpdateProductRequest.php
│       ├── StoreCategoryRequest.php
│       └── UpdateCategoryRequest.php
├── Models/
│   ├── Product.php          # Already exists
│   ├── Category.php         # Already exists
│   └── Setting.php          # Simple key-value model
resources/
├── views/
│   ├── admin/
│   │   ├── layouts/app.blade.php  # Already exists
│   │   ├── products/
│   │   │   ├── index.blade.php
│   │   │   ├── create.blade.php
│   │   │   └── edit.blade.php
│   │   ├── categories/
│   │   │   ├── index.blade.php
│   │   │   ├── create.blade.php
│   │   │   └── edit.blade.php
│   │   └── settings/
│   │       └── index.blade.php
│   └── errors/
│       └── 404.blade.php
storage/
└── app/
    └── public/
        └── products/         # Product images
```

### Pattern 1: Form Request Validation
**What:** Dedicated request classes for validation and authorization
**When to use:** Every admin create/update operation
**Example:**
```php
// Source: https://laravel.com/docs/12.x/validation
// app/Http/Requests/StoreProductRequest.php
class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check(); // Could add role check here
    }

    public function rules(): array
    {
        return [
            'name' => 'required|max:200',
            'description' => 'nullable|string',
            'price' => 'required|integer|min:0',
            'old_price' => 'nullable|integer|min:0',
            'slug' => 'nullable|max:200|unique:products',
            'categories' => 'nullable|array',
            'categories.*' => 'exists:categories,id',

            // Image uploads (multiple)
            'images' => 'nullable|array|max:10',
            'images.*' => 'image|mimes:jpg,jpeg,png,webp|max:2048',

            // Main image selection
            'main_image_index' => 'nullable|integer|min:0',

            // Dynamic attributes (key-value pairs)
            'attributes' => 'nullable|array',
            'attributes.*.key' => 'required|string|max:100',
            'attributes.*.value' => 'required|string|max:255',

            // SEO fields
            'meta_title' => 'nullable|max:200',
            'meta_description' => 'nullable|max:300',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Название товара обязательно',
            'price.required' => 'Укажите цену',
            'images.*.max' => 'Размер изображения не должен превышать 2MB',
        ];
    }

    protected function prepareForValidation(): void
    {
        // Convert price from KGS to cents
        if ($this->filled('price')) {
            $this->merge(['price' => $this->price * 100]);
        }
        if ($this->filled('old_price')) {
            $this->merge(['old_price' => $this->old_price * 100]);
        }
    }
}
```

### Pattern 2: File Upload with Storage Facade
**What:** Store uploaded images with unique names in storage/app/public
**When to use:** All image uploads in admin panel
**Example:**
```php
// Source: https://laravel.com/docs/12.x/filesystem
public function store(StoreProductRequest $request)
{
    $imagePaths = [];

    if ($request->hasFile('images')) {
        foreach ($request->file('images') as $image) {
            // Store with auto-generated unique name
            $path = $image->store('products', 'public');
            $imagePaths[] = $path;
        }
    }

    // Determine main image
    $mainImageIndex = $request->input('main_image_index', 0);
    $mainImage = $imagePaths[$mainImageIndex] ?? $imagePaths[0] ?? null;

    $product = Product::create([
        'name' => $request->name,
        'price' => $request->price,
        'main_image' => $mainImage,
        'images' => $imagePaths,
        // ... other fields
    ]);

    return redirect()->route('admin.products.index')
        ->with('success', 'Товар успешно добавлен');
}
```

### Pattern 3: Dynamic Attribute Form Fields with Alpine.js
**What:** Allow adding/removing key-value attribute pairs dynamically
**When to use:** Product attributes form section
**Example:**
```html
<!-- Source: Alpine.js repeater pattern -->
<div x-data="attributeManager()">
    <template x-for="(attr, index) in attributes" :key="index">
        <div class="flex gap-2 mb-2">
            <input type="text"
                   x-model="attr.key"
                   :name="'attributes['+index+'][key]'"
                   placeholder="Память, Цвет..."
                   class="flex-1 rounded-lg border-gray-300">

            <input type="text"
                   x-model="attr.value"
                   :name="'attributes['+index+'][value]'"
                   placeholder="256GB, Черный..."
                   class="flex-1 rounded-lg border-gray-300">

            <button type="button"
                    @click="removeAttribute(index)"
                    class="text-red-600 hover:text-red-800">
                <iconify-icon icon="solar:trash-bin-trash-linear"></iconify-icon>
            </button>
        </div>
    </template>

    <button type="button"
            @click="addAttribute()"
            class="text-blue-600 hover:text-blue-800">
        + Добавить характеристику
    </button>
</div>

<script>
function attributeManager() {
    return {
        attributes: [{ key: '', value: '' }],
        addAttribute() {
            this.attributes.push({ key: '', value: '' });
        },
        removeAttribute(index) {
            this.attributes.splice(index, 1);
        }
    }
}
</script>
```

### Pattern 4: Tabbed Form Interface
**What:** Organize large product form into sections with client-side tabs
**When to use:** Product create/edit forms
**Example:**
```html
<!-- Source: Alpine.js tab pattern -->
<div x-data="{ tab: 'basic' }">
    <!-- Tab Navigation -->
    <div class="border-b border-gray-200 mb-6">
        <nav class="flex gap-4">
            <button @click="tab = 'basic'"
                    :class="tab === 'basic' ? 'border-b-2 border-blue-500 text-blue-600' : 'text-gray-500'"
                    class="pb-2 px-4">
                Основное
            </button>
            <button @click="tab = 'images'"
                    :class="tab === 'images' ? 'border-b-2 border-blue-500 text-blue-600' : 'text-gray-500'"
                    class="pb-2 px-4">
                Фото
            </button>
            <button @click="tab = 'attributes'"
                    :class="tab === 'attributes' ? 'border-b-2 border-blue-500 text-blue-600' : 'text-gray-500'"
                    class="pb-2 px-4">
                Характеристики
            </button>
            <button @click="tab = 'seo'"
                    :class="tab === 'seo' ? 'border-b-2 border-blue-500 text-blue-600' : 'text-gray-500'"
                    class="pb-2 px-4">
                SEO
            </button>
        </nav>
    </div>

    <!-- Tab Content -->
    <div x-show="tab === 'basic'">
        <!-- Name, description, price, category fields -->
    </div>
    <div x-show="tab === 'images'" x-cloak>
        <!-- Image upload interface -->
    </div>
    <div x-show="tab === 'attributes'" x-cloak>
        <!-- Dynamic attributes -->
    </div>
    <div x-show="tab === 'seo'" x-cloak>
        <!-- Meta fields -->
    </div>
</div>
```

### Pattern 5: Delete Confirmation Modal
**What:** Alpine.js modal for confirming destructive actions
**When to use:** Delete product, delete category operations
**Example:**
```html
<!-- Source: Alpine.js modal pattern -->
<div x-data="{ showModal: false, deleteUrl: '' }">
    <!-- Delete Button -->
    <button @click="showModal = true; deleteUrl = '{{ route('admin.products.destroy', $product) }}'"
            class="text-red-600 hover:text-red-800">
        Удалить
    </button>

    <!-- Modal Overlay -->
    <div x-show="showModal"
         x-cloak
         class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
         @click.self="showModal = false">

        <!-- Modal Content -->
        <div class="bg-white rounded-lg p-6 max-w-md">
            <h3 class="text-lg font-semibold mb-4">Подтвердите удаление</h3>
            <p class="text-gray-600 mb-6">Вы уверены? Это действие нельзя отменить.</p>

            <div class="flex gap-3 justify-end">
                <button @click="showModal = false"
                        class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                    Отмена
                </button>

                <form :action="deleteUrl" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                        Удалить
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
```

### Anti-Patterns to Avoid
- **Controller validation:** Use Form Requests instead for complex validation rules
- **Direct file path manipulation:** Use Storage facade, not direct filesystem operations
- **Browser confirm():** Use Alpine.js modal instead for better UX and styling control
- **Long single-page forms:** Use tabs to organize product form (per CONTEXT requirement)
- **Manual slug editing without validation:** Ensure unique constraint check when slug is manually edited

## Don't Hand-Roll

Problems that look simple but have existing solutions:

| Problem | Don't Build | Use Instead | Why |
|---------|-------------|-------------|-----|
| Slug generation | Manual transliteration logic | Spatie Sluggable (already installed) | Handles uniqueness, transliteration, edge cases |
| File upload security | Custom filename sanitization | Storage::put() with auto-generated names | Prevents path traversal, naming conflicts |
| Image resizing | Manual GD operations | Intervention Image package | Handles formats, quality, memory optimization |
| Validation rules | Manual checks in controller | Form Request classes | Cleaner, reusable, authorizable |
| Flash messages | Custom session handling | Laravel session flash (already used) | Built-in, simple, one-request lifecycle |
| Multiple file uploads | Custom loop with validation | Array validation with `images.*` | Laravel handles it natively |

**Key insight:** Laravel 12 provides mature patterns for all admin CRUD operations. The project already has correct stack installed (Sluggable, Alpine.js, Tailwind). Don't introduce new packages unless necessary (like Intervention Image for optimization).

## Common Pitfalls

### Pitfall 1: Client-Side Filename Trust
**What goes wrong:** Using `getClientOriginalName()` or `getClientOriginalExtension()` from uploaded file to determine storage name
**Why it happens:** Seems convenient to preserve user's filename
**How to avoid:** Always use `store()` which generates unique, safe filenames. Never trust client data for filesystem operations.
**Warning signs:** File upload fails, overwritten files, path traversal vulnerabilities
**Source:** https://securinglaravel.com/laravel-security-file-upload-vulnerability/

### Pitfall 2: Forgetting Storage Link
**What goes wrong:** Images stored but not accessible via web, 404 on image URLs
**Why it happens:** `storage/app/public` not linked to `public/storage`
**How to avoid:** Run `php artisan storage:link` after deployment. Check if `public/storage` symlink exists.
**Warning signs:** Storage::disk('public')->put() succeeds but asset('storage/file.jpg') returns 404

### Pitfall 3: Price Input Without Conversion
**What goes wrong:** Prices stored incorrectly (e.g., 1000 KGS becomes 100000 cents)
**Why it happens:** Database stores in cents, form displays in KGS, conversion forgotten
**How to avoid:** Convert in `prepareForValidation()` method of Form Request (multiply by 100 for storage, divide by 100 for display)
**Warning signs:** Prices appear 100x too high or too low on frontend

### Pitfall 4: N+1 Queries in Admin Lists
**What goes wrong:** Admin product list loads categories for each product separately, slow page load
**Why it happens:** Blade loops over products, accesses `$product->categories` without eager loading
**How to avoid:** Use `Product::with('categories')->paginate()` in controller
**Warning signs:** Many SELECT queries in debugbar, slow admin pages
**Source:** Existing pattern established in Phase 2 OrderController

### Pitfall 5: No Validation on Attribute Keys
**What goes wrong:** Duplicate attribute keys (two "Память" entries), inconsistent naming
**Why it happens:** User can type anything, no frontend/backend validation
**How to avoid:**
- Backend: Validate attribute keys are non-empty, unique within product
- Frontend: Show error if duplicate keys detected
**Warning signs:** Product has "Память: 256GB" and "память: 512GB" (case mismatch)

### Pitfall 6: CSRF Token Missing on Delete
**What goes wrong:** Delete fails with 419 error
**Why it happens:** Delete form submitted without CSRF token
**How to avoid:** Always include `@csrf` directive in forms, `@method('DELETE')` for delete routes
**Warning signs:** 419 HTTP errors on form submission

### Pitfall 7: Image Upload Without Size Limit
**What goes wrong:** User uploads 50MB photo, server runs out of memory or storage
**Why it happens:** No `max:` validation rule on image upload
**How to avoid:** Always set `max:2048` (2MB) or similar reasonable limit in validation rules
**Warning signs:** PHP memory errors, slow uploads, full disk space

### Pitfall 8: Not Handling Slug Collisions on Manual Edit
**What goes wrong:** User edits slug to existing value, unique constraint violation
**Why it happens:** Spatie Sluggable auto-handles uniqueness on creation, but manual edits bypass it
**How to avoid:** Add `unique:products,slug,{id}` validation rule in UpdateProductRequest (ignore current product's slug)
**Warning signs:** Database error on update, "Duplicate entry" error

## Code Examples

Verified patterns from official sources:

### Admin Controller with Form Request
```php
// Source: https://laravel.com/docs/12.x/validation
namespace App\Http\Controllers\Admin;

use App\Models\Product;
use App\Models\Category;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with('categories')
            ->latest()
            ->paginate(20);

        return view('admin.products.index', compact('products'));
    }

    public function create()
    {
        $categories = Category::where('is_active', true)->get();
        return view('admin.products.create', compact('categories'));
    }

    public function store(StoreProductRequest $request)
    {
        // Validation already passed, data available via $request->validated()

        $data = $request->validated();

        // Handle image uploads
        $imagePaths = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $imagePaths[] = $image->store('products', 'public');
            }
        }

        $data['images'] = $imagePaths;
        $data['main_image'] = $imagePaths[$request->input('main_image_index', 0)] ?? null;

        $product = Product::create($data);

        // Sync categories (many-to-many)
        if ($request->filled('categories')) {
            $product->categories()->sync($request->categories);
        }

        // Handle attributes (store in separate table, per Phase 1 decision)
        if ($request->filled('attributes')) {
            foreach ($request->attributes as $attr) {
                $product->attributes()->create([
                    'key' => $attr['key'],
                    'value' => $attr['value'],
                ]);
            }
        }

        return redirect()->route('admin.products.index')
            ->with('success', 'Товар успешно создан');
    }

    public function edit(Product $product)
    {
        $product->load('categories', 'attributes');
        $categories = Category::where('is_active', true)->get();

        return view('admin.products.edit', compact('product', 'categories'));
    }

    public function update(UpdateProductRequest $request, Product $product)
    {
        $data = $request->validated();

        // Handle new image uploads
        if ($request->hasFile('images')) {
            $newImages = [];
            foreach ($request->file('images') as $image) {
                $newImages[] = $image->store('products', 'public');
            }

            // Delete old images if replacing
            if ($product->images) {
                foreach ($product->images as $oldImage) {
                    Storage::disk('public')->delete($oldImage);
                }
            }

            $data['images'] = $newImages;
            $data['main_image'] = $newImages[$request->input('main_image_index', 0)] ?? null;
        }

        $product->update($data);

        // Sync relationships
        if ($request->has('categories')) {
            $product->categories()->sync($request->categories);
        }

        if ($request->has('attributes')) {
            // Replace all attributes
            $product->attributes()->delete();
            foreach ($request->attributes as $attr) {
                $product->attributes()->create($attr);
            }
        }

        return redirect()->route('admin.products.index')
            ->with('success', 'Товар обновлен');
    }

    public function destroy(Product $product)
    {
        // Delete associated images
        if ($product->images) {
            foreach ($product->images as $image) {
                Storage::disk('public')->delete($image);
            }
        }

        $product->delete();

        return redirect()->route('admin.products.index')
            ->with('success', 'Товар удален');
    }
}
```

### Custom 404 Error Page
```blade
{{-- Source: https://laravel.com/docs/12.x/errors --}}
{{-- resources/views/errors/404.blade.php --}}
@extends('layouts.app')

@section('title', 'Страница не найдена')

@section('content')
<div class="max-w-2xl mx-auto text-center py-16">
    <div class="mb-8">
        <iconify-icon icon="solar:ghost-linear" width="120" class="text-gray-400"></iconify-icon>
    </div>

    <h1 class="text-6xl font-bold text-gray-800 mb-4">404</h1>
    <p class="text-xl text-gray-600 mb-8">Страница не найдена</p>

    <p class="text-gray-500 mb-8">
        К сожалению, запрашиваемая страница не существует или была удалена.
    </p>

    <div class="flex gap-4 justify-center">
        <a href="{{ route('home') }}" class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
            На главную
        </a>
        <a href="{{ url()->previous() }}" class="px-6 py-3 border border-gray-300 rounded-lg hover:bg-gray-50">
            Назад
        </a>
    </div>
</div>
@endsection
```

### Settings Management (Simple Key-Value)
```php
// Model: app/Models/Setting.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    public static function get(string $key, $default = null)
    {
        $setting = static::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    public static function set(string $key, $value): void
    {
        static::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }
}

// Migration
Schema::create('settings', function (Blueprint $table) {
    $table->id();
    $table->string('key')->unique();
    $table->text('value')->nullable();
    $table->timestamps();
});

// Controller: app/Http/Controllers/Admin/SettingsController.php
public function index()
{
    $settings = [
        'phone' => Setting::get('site.phone', ''),
        'address' => Setting::get('site.address', ''),
        'email' => Setting::get('site.email', ''),
        'footer_text' => Setting::get('site.footer_text', ''),
    ];

    return view('admin.settings.index', compact('settings'));
}

public function update(Request $request)
{
    $request->validate([
        'phone' => 'required|string|max:20',
        'address' => 'required|string|max:255',
        'email' => 'required|email|max:100',
        'footer_text' => 'nullable|string|max:500',
    ]);

    Setting::set('site.phone', $request->phone);
    Setting::set('site.address', $request->address);
    Setting::set('site.email', $request->email);
    Setting::set('site.footer_text', $request->footer_text);

    return back()->with('success', 'Настройки сохранены');
}
```

## State of the Art

| Old Approach | Current Approach | When Changed | Impact |
|--------------|------------------|--------------|--------|
| Manual validation in controller | Form Request classes | Laravel 5.0+ (2015) | Cleaner controllers, reusable validation |
| `Input::file()` | `$request->file()` | Laravel 5.2 (2015) | Better request abstraction |
| `File::move()` | `Storage::put()` / `$file->store()` | Laravel 5.0+ (2015) | Disk abstraction, better testing |
| jQuery for tabs | Alpine.js | 2020+ trend | Lighter, reactive, no jQuery dependency |
| Bootstrap modals | Alpine.js + Tailwind modals | 2020+ trend | Utility-first CSS, less JS overhead |
| Single form page | Tabbed interface | UI/UX evolution | Better organization for complex forms |
| Dropzone.js | Native HTML5 drag-drop | 2024+ | Simpler, fewer dependencies |

**Deprecated/outdated:**
- **jQuery for admin interactions:** Modern projects use Alpine.js or Vue.js
- **Intervention Image 2.x:** Version 3.x released 2023, supports Laravel 11-12
- **Storage link in routes/web.php:** Use `php artisan storage:link` command instead

## Open Questions

Things that couldn't be fully resolved:

1. **Image optimization strategy**
   - What we know: Intervention Image can resize/optimize, WebP conversion reduces size 25-35%
   - What's unclear: Should images be optimized on upload, or on-demand? What quality settings?
   - Recommendation: Start without optimization, add if storage/bandwidth becomes issue. If adding, process on upload in background job to avoid timeout.

2. **Category assignment: single or multiple?**
   - What we know: Database has many-to-many relationship (category_product table exists), CONTEXT marked as Claude's discretion
   - What's unclear: User preference not specified
   - Recommendation: Implement multiple categories (more flexible, already supported by schema). Use checkbox list in form.

3. **Product status/draft feature**
   - What we know: `is_active` boolean exists on products table, CONTEXT marked as Claude's discretion
   - What's unclear: Should admin have explicit "Опубликован" checkbox?
   - Recommendation: Yes, add checkbox. Default to true for new products. Allows drafts/unpublished products.

4. **Rich text editor for description**
   - What we know: CONTEXT marked as Claude's discretion (textarea vs TinyMCE/CKEditor)
   - What's unclear: How much formatting does admin need?
   - Recommendation: Start with plain textarea. Phase 1 descriptions are plain text. Add rich editor only if formatting becomes necessary.

5. **Image sorting UI: arrows vs drag-drop**
   - What we know: CONTEXT specifies "Кнопки Вверх/Вниз" (up/down arrows), not drag-drop
   - What's unclear: How to implement with form submission
   - Recommendation: Store order in images array index. Provide arrow buttons that reorder array with Alpine.js, then submit form.

## Sources

### Primary (HIGH confidence)
- **Laravel 12.x Documentation - Validation:** https://laravel.com/docs/12.x/validation - Form Request patterns, file validation rules
- **Laravel 12.x Documentation - Filesystem:** https://laravel.com/docs/12.x/filesystem - Storage facade, file uploads, symbolic links
- **Laravel 12.x Documentation - Errors:** https://laravel.com/docs/12.x/errors - Custom 404 pages, fallback pages
- **Spatie Sluggable GitHub:** https://github.com/spatie/laravel-sluggable - Already installed, slug generation patterns
- **Existing codebase:** Phase 1 & 2 patterns (Form Requests pattern in CheckoutController throttle, Storage patterns, admin layout, Alpine.js usage)

### Secondary (MEDIUM confidence)
- **Kritimyantra - Laravel 12 CRUD Best Practices (2026):** Eager loading, resource controllers, short methods
- **Medium - Mastering Laravel Policies (2026):** Gate vs Policy patterns for admin authorization
- **Laravel Daily - JSON vs Separate Tables:** Product attributes - separate table better for filtering (aligns with Phase 1 decision)
- **Securing Laravel - File Upload Vulnerability:** Don't trust client filenames, use Storage with auto-generated names
- **Medium - Laravel 11 Dropzone & Sortable (2024):** Image upload with sorting patterns (up/down arrows can be Alpine.js variant)

### Secondary (MEDIUM confidence)
- **WebSearch - Laravel flash messages best practices:** Use session flash for one-request feedback (already implemented in Phase 2)
- **WebSearch - Alpine.js modal patterns:** Confirmed Alpine.js modals standard with Tailwind CSS in 2026
- **WebSearch - Form repeater patterns:** Dynamic key-value fields achievable with Alpine.js arrays

## Metadata

**Confidence breakdown:**
- Standard stack: HIGH - Laravel patterns well-established, project already uses correct stack
- Architecture: HIGH - Form Requests, Storage facade, Alpine.js patterns verified in Laravel 12.x docs
- File uploads: HIGH - Official documentation clear, security pitfalls well-documented
- Validation: HIGH - Form Request pattern is Laravel standard since 5.0
- UI patterns: MEDIUM - Alpine.js patterns confirmed but implementation details vary
- Settings management: MEDIUM - Multiple approaches work (simple model recommended over Spatie package for 4 fields)
- Image optimization: MEDIUM - Intervention Image is standard but optimization strategy depends on requirements

**Research date:** 2026-01-23
**Valid until:** 2026-02-23 (30 days - Laravel 12 is stable, patterns mature)
