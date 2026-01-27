---
type: quick
plan: 001
wave: 1
depends_on: []
files_modified:
  - app/Http/Controllers/WishlistController.php
  - app/Http/Controllers/SearchController.php
  - resources/views/wishlist/index.blade.php
  - resources/views/layouts/app.blade.php
  - resources/views/components/product-card.blade.php
  - routes/web.php
autonomous: true

must_haves:
  truths:
    - "User can toggle wishlist on product cards"
    - "Wishlist icon in header shows count"
    - "User can view all wishlist items on /wishlist page"
    - "Search shows live suggestions while typing"
    - "Clicking suggestion navigates to product"
  artifacts:
    - path: "app/Http/Controllers/WishlistController.php"
      provides: "Wishlist CRUD with session storage"
      exports: ["index", "toggle", "count"]
    - path: "app/Http/Controllers/SearchController.php"
      provides: "Search autocomplete API"
      exports: ["autocomplete"]
    - path: "resources/views/wishlist/index.blade.php"
      provides: "Wishlist page showing saved products"
      min_lines: 50
    - path: "resources/views/layouts/app.blade.php"
      provides: "Wishlist icon + counter, search autocomplete UI"
      contains: ["wishlistCount", "@wishlist-updated"]
  key_links:
    - from: "product-card heart button"
      to: "/wishlist/toggle"
      via: "Alpine.js click handler + fetch"
      pattern: "fetch.*wishlist/toggle"
    - from: "header wishlist icon"
      to: "Alpine.js wishlistCount"
      via: "reactive data binding"
      pattern: "x-text.*wishlistCount"
    - from: "search input"
      to: "/search/autocomplete"
      via: "Alpine.js @input.debounce"
      pattern: "fetch.*search/autocomplete"
---

<objective>
Add two interactive features to enhance user experience: wishlist system for saving favorite products and search autocomplete for faster product discovery. Both features use AJAX + Alpine.js patterns matching existing cart implementation.

Purpose: Improve product discovery and user engagement by allowing customers to save products for later and find products faster with live search suggestions.

Output: Functional wishlist with /wishlist page and header counter, plus live search autocomplete dropdown with product suggestions.
</objective>

<execution_context>
@~/.claude/get-shit-done/workflows/execute-plan.md
@~/.claude/get-shit-done/templates/summary.md
</execution_context>

<context>
@.planning/PROJECT.md
@.planning/STATE.md

# Existing patterns to follow
@app/Http/Controllers/CartController.php
@resources/views/layouts/app.blade.php
@resources/views/components/product-card.blade.php
@routes/web.php
</context>

<tasks>

<task type="auto">
  <name>Task 1: Create WishlistController with session storage</name>
  <files>
    app/Http/Controllers/WishlistController.php
    routes/web.php
  </files>
  <action>
Create WishlistController following CartController pattern:

**WishlistController.php:**
- `index()`: Display wishlist page, load product IDs from session, eager load products with images
- `toggle(Request $request)`: Add/remove product from wishlist array in session, return JSON with success status and updated count
- `count()`: Return JSON with current wishlist count (used by Alpine.js for header badge)

**Session storage:**
- Store array of product IDs in `session('wishlist', [])`
- Use `session()->put('wishlist', $ids)` to persist
- No database required (matches cart session pattern)

**JSON responses:**
```php
// Toggle response
return response()->json([
    'success' => true,
    'inWishlist' => true/false,
    'count' => count(session('wishlist', [])),
    'message' => 'Added/Removed'
]);
```

**Routes to add:**
```php
Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist.index');
Route::post('/wishlist/toggle', [WishlistController::class, 'toggle'])->name('wishlist.toggle');
Route::get('/wishlist/count', [WishlistController::class, 'count'])->name('wishlist.count');
```

**Validation:**
- Toggle requires product_id (exists:products,id)
- Check product is_active before adding
  </action>
  <verify>
Run tests:
```bash
php artisan route:list | grep wishlist
curl -X POST http://127.0.0.1:8000/wishlist/toggle -d "product_id=1" -H "Accept: application/json"
curl http://127.0.0.1:8000/wishlist/count
```
  </verify>
  <done>
- WishlistController exists with index/toggle/count methods
- Routes registered and visible in route:list
- Toggle API accepts product_id, returns JSON with count
- Session stores product IDs array
  </done>
</task>

<task type="auto">
  <name>Task 2: Add wishlist UI with Alpine.js reactivity</name>
  <files>
    resources/views/wishlist/index.blade.php
    resources/views/components/product-card.blade.php
    resources/views/layouts/app.blade.php
  </files>
  <action>
**1. Create wishlist/index.blade.php:**
- Extend layouts.app, title "Избранное - Xiaomi Store"
- Show grid of wishlist products using x-product-card component
- Empty state: "Ваш список избранного пуст" with icon + link to catalog
- Each card shows heart icon (filled if in wishlist)
- Mobile responsive grid (1 col mobile, 3 cols desktop)

**2. Update product-card.blade.php:**
Add wishlist toggle functionality to existing heart button (line 9):

```blade
<button @click.prevent="toggleWishlist({{ $product->id }})"
        class="absolute top-4 right-4 z-10 transition-colors"
        :class="wishlistIds.includes({{ $product->id }}) ? 'text-red-500' : 'text-gray-300 hover:text-red-500'">
    <iconify-icon icon="solar:heart-linear" width="20" stroke-width="2"></iconify-icon>
</button>
```

Wrap card in Alpine.js context:
```blade
<div x-data="productCard()" class="group bg-white ...">
    <!-- existing card content -->
</div>

@push('scripts')
<script>
function productCard() {
    return {
        wishlistIds: {{ json_encode(session('wishlist', [])) }},
        async toggleWishlist(productId) {
            const response = await fetch('/wishlist/toggle', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ product_id: productId })
            });
            const data = await response.json();

            // Update local state
            if (data.inWishlist) {
                this.wishlistIds.push(productId);
            } else {
                this.wishlistIds = this.wishlistIds.filter(id => id !== productId);
            }

            // Dispatch event for header counter
            window.dispatchEvent(new CustomEvent('wishlist-updated', {
                detail: { count: data.count }
            }));
        }
    }
}
</script>
@endpush
```

**3. Update layouts/app.blade.php header (line 95-98):**
Replace placeholder wishlist icon with reactive counter:

```blade
<a href="{{ route('wishlist.index') }}"
   class="p-2 text-gray-500 hover:text-gray-900 hover:bg-gray-50 rounded-full transition-all relative"
   x-data="{ wishlistCount: {{ count(session('wishlist', [])) }} }"
   @wishlist-updated.window="wishlistCount = $event.detail.count">
    <iconify-icon icon="solar:heart-linear" width="24" stroke-width="1.5"></iconify-icon>
    <span x-show="wishlistCount > 0"
          x-text="wishlistCount"
          class="absolute top-1 right-1 bg-red-500 text-white text-xs rounded-full w-4 h-4 flex items-center justify-center font-bold"></span>
</a>
```

**Toast notification:**
Add toast on toggle (reuse existing x-toast component pattern from cart)
  </action>
  <verify>
Manual browser testing:
1. Visit http://127.0.0.1:8000/products
2. Click heart icon on product card
3. Verify icon turns red, header counter increments
4. Click heart again, verify counter decrements
5. Visit /wishlist, verify products appear
6. Test empty state when no wishlist items
  </verify>
  <done>
- Wishlist page exists showing saved products
- Heart icon on cards toggles red/gray
- Header counter shows wishlist count
- Counter updates without page reload
- Empty state displays when wishlist empty
  </done>
</task>

<task type="auto">
  <name>Task 3: Add search autocomplete with live suggestions</name>
  <files>
    app/Http/Controllers/SearchController.php
    resources/views/layouts/app.blade.php
  </files>
  <action>
**1. Update SearchController.php:**
Add autocomplete method:

```php
public function autocomplete(Request $request)
{
    $request->validate([
        'q' => 'required|string|min:2|max:100',
    ]);

    $query = $request->input('q');

    $products = Product::where('is_active', true)
        ->where('name', 'LIKE', "%{$query}%")
        ->select('id', 'name', 'slug', 'price', 'main_image')
        ->limit(5)
        ->get();

    return response()->json([
        'success' => true,
        'results' => $products->map(fn($p) => [
            'id' => $p->id,
            'name' => $p->name,
            'slug' => $p->slug,
            'price' => number_format($p->price / 100, 0) . ' сом',
            'url' => route('product.show', $p->slug),
            'image' => $p->main_image,
        ])
    ]);
}
```

**Add route:**
```php
Route::get('/search/autocomplete', [SearchController::class, 'autocomplete'])->name('search.autocomplete');
```

**2. Update app.blade.php search forms:**

**Desktop search (line 86-91):**
```blade
<form action="{{ route('search') }}" method="GET"
      class="hidden md:flex flex-1 max-w-lg relative group"
      x-data="searchAutocomplete()"
      @click.outside="showResults = false">
    <div class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-brand-500 transition-colors">
        <iconify-icon icon="solar:magnifer-linear" width="20" stroke-width="1.5"></iconify-icon>
    </div>
    <input type="text"
           name="q"
           x-model="query"
           @input.debounce.300ms="search"
           @focus="showResults = true"
           value="{{ request('q') }}"
           placeholder="Поиск товаров (например, Xiaomi 14)..."
           class="w-full bg-gray-50 border border-gray-200 rounded-full py-2.5 pl-10 pr-4 text-sm outline-none focus:ring-2 focus:ring-brand-100 focus:border-brand-500 transition-all placeholder:text-gray-400"
           maxlength="200"
           autocomplete="off">

    <!-- Dropdown results -->
    <div x-show="showResults && results.length > 0"
         x-transition
         class="absolute top-full left-0 right-0 mt-2 bg-white border border-gray-200 rounded-xl shadow-xl z-50 overflow-hidden">
        <template x-for="product in results" :key="product.id">
            <a :href="product.url"
               class="flex items-center gap-3 p-3 hover:bg-gray-50 transition-colors">
                <img :src="product.image || '/placeholder.png'"
                     :alt="product.name"
                     class="w-12 h-12 object-cover rounded">
                <div class="flex-1 min-w-0">
                    <div class="text-sm font-medium text-gray-900 truncate" x-text="product.name"></div>
                    <div class="text-xs text-gray-500" x-text="product.price"></div>
                </div>
            </a>
        </template>
    </div>
</form>
```

**Mobile search (line 116-123) - same pattern:**
Apply same x-data, x-model, @input.debounce, dropdown structure

**Alpine.js component:**
```blade
@push('scripts')
<script>
function searchAutocomplete() {
    return {
        query: '{{ request('q') }}',
        results: [],
        showResults: false,
        async search() {
            if (this.query.length < 2) {
                this.results = [];
                return;
            }

            const response = await fetch(`/search/autocomplete?q=${encodeURIComponent(this.query)}`);
            const data = await response.json();
            this.results = data.results || [];
        }
    }
}
</script>
@endpush
```

**Key requirements:**
- Debounce 300ms to avoid excessive requests
- Min 2 characters to trigger search
- Limit 5 results
- Click outside closes dropdown
- Results show product image, name, price
- Clicking result navigates to product page
  </action>
  <verify>
Manual browser testing:
1. Visit homepage or products page
2. Type "Xiaomi" in search bar (slowly)
3. Verify dropdown appears after 2+ characters
4. Verify max 5 results shown
5. Click result, verify navigation to product page
6. Click outside dropdown, verify it closes
7. Test mobile search has same behavior
  </verify>
  <done>
- SearchController has autocomplete method
- Route /search/autocomplete returns JSON
- Desktop search shows live dropdown
- Mobile search shows live dropdown
- Dropdown shows product image, name, price
- Clicking result navigates to product
- Dropdown closes on outside click
  </done>
</task>

</tasks>

<verification>
**Wishlist verification:**
1. Add products to wishlist from multiple pages (index, show, category)
2. Verify header counter updates without reload
3. Visit /wishlist page, verify products displayed
4. Remove products from wishlist, verify counter updates
5. Test empty state when no items

**Search autocomplete verification:**
1. Type various queries (Xiaomi, Redmi, 14, etc)
2. Verify results appear after 2 characters
3. Verify debouncing (no request spam)
4. Verify max 5 results
5. Click result, verify navigation
6. Test mobile search bar

**Integration verification:**
1. Both features work simultaneously
2. Session persists across page navigation
3. CSRF tokens work for AJAX requests
4. No console errors
5. Alpine.js events fire correctly
</verification>

<success_criteria>
**Wishlist complete when:**
- [ ] Heart icon on product cards toggles add/remove
- [ ] Header shows wishlist counter (red badge)
- [ ] /wishlist page displays saved products in grid
- [ ] Empty state shown when no wishlist items
- [ ] Session persists across page reloads
- [ ] Counter updates without page reload (Alpine.js)

**Search autocomplete complete when:**
- [ ] Typing in search shows live dropdown
- [ ] Dropdown appears after 2+ characters
- [ ] Max 5 results displayed with image/name/price
- [ ] Clicking result navigates to product page
- [ ] Dropdown closes on outside click
- [ ] Works on both desktop and mobile search bars

**Overall success:**
- [ ] Both features use AJAX (no page reload)
- [ ] Alpine.js handles reactive state
- [ ] Session storage for wishlist (no database)
- [ ] CSRF protection on POST requests
- [ ] No console errors in browser
- [ ] Mobile responsive
</success_criteria>

<output>
After completion, create `.planning/quick/001-wishlist-and-search-autocomplete/001-SUMMARY.md` documenting:
- Implementation approach (session storage, Alpine.js patterns)
- API endpoints created
- UI components modified
- Testing results
- Any edge cases handled
</output>
