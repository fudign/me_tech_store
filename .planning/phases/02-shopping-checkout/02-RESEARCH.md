# Phase 2: Shopping & Checkout - Research

**Researched:** 2026-01-23
**Domain:** Laravel E-commerce Cart & Guest Checkout
**Confidence:** HIGH

## Summary

This phase implements shopping cart functionality using the already-installed `darryldecode/cart` package (v4.2) with session-based storage for guest users, a single-page checkout process collecting minimal customer information (name, phone, address), and admin order management. The research reveals Laravel's mature ecosystem provides battle-tested solutions for cart management, AJAX updates, rate limiting, and database transactions.

**Key findings:**
- Darryldecode/cart package already installed - provides session-based cart with conditions support
- Session storage is recommended for guest carts (hybrid approach for future auth)
- Order items must snapshot product prices at purchase time to preserve historical accuracy
- Laravel's built-in rate limiting handles SEC-05 requirement with minimal configuration
- Database transactions ensure data integrity when creating orders with multiple items
- Alpine.js (ships with Livewire v3) provides zero-overhead AJAX cart updates

**Primary recommendation:** Use darryldecode/cart for session-based cart management, implement single-page checkout with strict validation (phone:KG rule), snapshot prices in order_items table, protect checkout route with throttle:3,10 middleware, and use DB::transaction() for atomic order creation.

## Standard Stack

The established libraries/tools for Laravel shopping cart and checkout:

### Core
| Library | Version | Purpose | Why Standard |
|---------|---------|---------|--------------|
| darryldecode/cart | 4.2 | Session-based cart management | Industry standard for Laravel carts, supports conditions (discounts), already installed in project |
| Laravel Session | 12.x | Cart data storage | Built-in, secure, configurable (file/database/redis) |
| Laravel Validation | 12.x | Form validation | Built-in, comprehensive rules including phone validation |
| Laravel Rate Limiting | 12.x | Throttle checkout submissions | Built-in, flexible, supports per-IP and per-field throttling |
| Laravel Database | 12.x | Transactions & migrations | Built-in, ensures atomic order creation |

### Supporting
| Library | Version | Purpose | When to Use |
|---------|---------|---------|-------------|
| propaganistas/laravel-phone | 5.x+ | Phone validation for +996 | Country-specific phone validation (CONTEXT requires +996 Kyrgyzstan) |
| usernotnull/tall-toasts | Latest | Toast notifications | TALL stack notification library (<1KB footprint), Alpine.js + Tailwind |
| Alpine.js | 3.x (ships with Livewire v3) | Client-side interactivity | AJAX cart updates, UI interactions without page reload |

### Alternatives Considered
| Instead of | Could Use | Tradeoff |
|------------|-----------|----------|
| darryldecode/cart | Custom session implementation | Package handles edge cases (concurrent updates, conditions), maintenance burden vs control |
| Session storage | Database-only storage | Sessions better for guests (no DB pollution), database better for persistence (requires cleanup job) |
| Single-page checkout | Multi-step wizard | Single-page simpler (CONTEXT decision), wizard better for complex flows |
| Alpine.js AJAX | Full page reload | AJAX provides better UX (CONTEXT requirement), full reload simpler but slower |

**Installation:**
```bash
# Phone validation (not yet installed)
composer require propaganistas/laravel-phone

# Toast notifications (optional, can use custom Alpine.js)
composer require usernotnull/tall-toasts

# darryldecode/cart already installed in composer.json
```

## Architecture Patterns

### Recommended Project Structure
```
app/
├── Models/
│   ├── Order.php           # Order model with status enum
│   ├── OrderItem.php       # Order items with snapshot price
│   └── Customer.php        # Guest customer data (optional separate table)
├── Http/
│   ├── Controllers/
│   │   ├── CartController.php        # Add, update, remove cart items
│   │   ├── CheckoutController.php    # Show form, process order
│   │   └── Admin/
│   │       └── OrderController.php   # Admin order management
│   └── Requests/
│       └── CheckoutRequest.php       # Form validation with phone:KG rule
database/
├── migrations/
│   ├── create_orders_table.php       # Orders with customer info, status, totals
│   └── create_order_items_table.php  # Items with snapshot price, quantity
resources/
├── views/
│   ├── cart/
│   │   └── index.blade.php           # Cart page with AJAX quantity updates
│   ├── checkout/
│   │   └── index.blade.php           # Single-page checkout form
│   └── admin/
│       └── orders/
│           ├── index.blade.php       # Order list
│           └── show.blade.php        # Order detail with status dropdown
routes/
└── web.php                           # Routes with throttle middleware
```

### Pattern 1: Session-Based Cart for Guests
**What:** Use darryldecode/cart with session storage, bind to session ID (not user ID)
**When to use:** Guest checkout without authentication (CONTEXT requirement)
**Example:**
```php
// Source: https://github.com/darryldecode/laravelshoppingcart
// In CartController.php

use Cart;

// Add to cart (stay on current page, return JSON for AJAX)
public function add(Request $request)
{
    $product = Product::findOrFail($request->product_id);

    Cart::add([
        'id' => $product->id,
        'name' => $product->name,
        'price' => $product->price / 100, // Convert cents to decimal for cart
        'quantity' => $request->quantity ?? 1,
        'attributes' => [
            'slug' => $product->slug,
            'image' => $product->image,
        ]
    ]);

    return response()->json([
        'success' => true,
        'cart_count' => Cart::getTotalQuantity(),
        'message' => 'Товар добавлен в корзину'
    ]);
}

// Update quantity (AJAX, no page reload per CONTEXT)
public function update(Request $request, $itemId)
{
    Cart::update($itemId, [
        'quantity' => [
            'relative' => false,
            'value' => $request->quantity
        ]
    ]);

    return response()->json([
        'success' => true,
        'subtotal' => Cart::getSubTotal(),
        'total' => Cart::getTotal()
    ]);
}

// Get cart data for page display
public function index()
{
    $items = Cart::getContent();
    $total = Cart::getTotal();

    return view('cart.index', compact('items', 'total'));
}
```

### Pattern 2: Order with Snapshot Pricing
**What:** Store product price in order_items at purchase time (denormalization)
**When to use:** Always for e-commerce orders to preserve historical accuracy
**Example:**
```php
// Source: Best practice from database design research
// In Order model

// orders table migration
Schema::create('orders', function (Blueprint $table) {
    $table->id();
    $table->string('order_number')->unique(); // e.g., ORD-20260123-0001

    // Customer info (guest checkout)
    $table->string('customer_name');
    $table->string('customer_phone');
    $table->text('customer_address');

    // Payment
    $table->enum('payment_method', ['cash', 'online', 'installment']);

    // Status
    $table->enum('status', ['new', 'processing', 'delivering', 'completed'])
          ->default('new');

    // Totals (stored in cents)
    $table->integer('subtotal'); // Sum of items
    $table->integer('total');    // After conditions (future discounts)

    $table->timestamps();
});

// order_items table migration
Schema::create('order_items', function (Blueprint $table) {
    $table->id();
    $table->foreignId('order_id')->constrained()->onDelete('cascade');
    $table->foreignId('product_id')->constrained(); // Reference for admin

    // Snapshot data (never changes)
    $table->string('product_name');
    $table->string('product_slug');
    $table->integer('price'); // Price AT PURCHASE TIME (cents)
    $table->integer('quantity');
    $table->integer('subtotal'); // price * quantity

    $table->json('attributes')->nullable(); // e.g., {"Память": "256GB", "Цвет": "Black"}

    $table->timestamps();
});
```

### Pattern 3: Atomic Order Creation with Transactions
**What:** Wrap order + order_items + cart clearing in DB::transaction()
**When to use:** Always when creating orders to ensure data integrity
**Example:**
```php
// Source: Laravel 12 transaction documentation + best practices
// In CheckoutController.php

use Illuminate\Support\Facades\DB;

public function process(CheckoutRequest $request)
{
    try {
        $order = DB::transaction(function () use ($request) {
            // 1. Create order
            $order = Order::create([
                'order_number' => $this->generateOrderNumber(),
                'customer_name' => $request->name,
                'customer_phone' => $request->phone,
                'customer_address' => $request->address,
                'payment_method' => $request->payment_method,
                'status' => 'new',
                'subtotal' => Cart::getSubTotal() * 100, // Convert to cents
                'total' => Cart::getTotal() * 100,
            ]);

            // 2. Create order items (snapshot prices)
            $cartItems = Cart::getContent();
            foreach ($cartItems as $item) {
                $order->items()->create([
                    'product_id' => $item->id,
                    'product_name' => $item->name,
                    'product_slug' => $item->attributes->slug,
                    'price' => $item->price * 100, // Convert to cents
                    'quantity' => $item->quantity,
                    'subtotal' => ($item->price * $item->quantity) * 100,
                    'attributes' => json_encode($item->attributes),
                ]);
            }

            // 3. Clear cart
            Cart::clear();

            return $order;
        });

        return redirect()->route('checkout.success', $order->order_number);

    } catch (\Exception $e) {
        // Transaction auto-rolled back
        return back()->withErrors('Ошибка при оформлении заказа. Попробуйте снова.');
    }
}

private function generateOrderNumber(): string
{
    // Format: ORD-YYYYMMDD-NNNN
    $date = now()->format('Ymd');
    $count = Order::whereDate('created_at', now())->count() + 1;
    return sprintf('ORD-%s-%04d', $date, $count);
}
```

### Pattern 4: AJAX Cart Updates with Alpine.js
**What:** Use Alpine.js for client-side quantity updates with server synchronization
**When to use:** Cart page quantity changes (CONTEXT requires no page reload)
**Example:**
```html
<!-- Source: Alpine.js patterns + TALL stack best practices -->
<!-- In resources/views/cart/index.blade.php -->

<div x-data="cartManager()">
    @foreach($items as $item)
    <div class="cart-item">
        <div class="quantity-controls">
            <input
                type="number"
                x-model="quantities['{{ $item->id }}']"
                @change="updateQuantity('{{ $item->id }}', $event.target.value)"
                min="1"
                class="w-20"
            >
        </div>
        <div class="price">
            {{ number_format($item->price * $item->quantity, 0, ',', ' ') }} сом
        </div>
    </div>
    @endforeach

    <div class="totals">
        <div x-text="'Итого: ' + formatPrice(total) + ' сом'"></div>
    </div>
</div>

<script>
function cartManager() {
    return {
        quantities: @json($items->pluck('quantity', 'id')),
        total: {{ $total }},

        updateQuantity(itemId, newQty) {
            fetch(`/cart/${itemId}`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ quantity: parseInt(newQty) })
            })
            .then(response => response.json())
            .then(data => {
                this.total = data.total;
                // Update header cart count
                this.$dispatch('cart-updated', { count: data.cart_count });
            })
            .catch(error => {
                console.error('Error updating cart:', error);
                // Revert quantity on error
                this.quantities[itemId] = this.quantities[itemId];
            });
        },

        formatPrice(cents) {
            return new Intl.NumberFormat('ru-RU').format(cents);
        }
    }
}
</script>
```

### Pattern 5: Rate Limiting for Checkout
**What:** Apply throttle middleware to prevent checkout spam (SEC-05 requirement)
**When to use:** All checkout form submissions, optional for cart operations
**Example:**
```php
// Source: Laravel 12 rate limiting documentation
// In routes/web.php

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;

// Define custom rate limiter in AppServiceProvider::boot()
RateLimiter::for('checkout', function (Request $request) {
    return [
        // 3 attempts per 10 minutes per IP
        Limit::perMinutes(10, 3)->by($request->ip()),
        // 1 attempt per 2 minutes per phone number
        Limit::perMinutes(2, 1)->by($request->input('phone')),
    ];
});

// Apply to checkout route
Route::post('/checkout', [CheckoutController::class, 'process'])
    ->middleware(['throttle:checkout'])
    ->name('checkout.process');

// In CheckoutController, handle rate limit exceeded
public function process(CheckoutRequest $request)
{
    // Laravel automatically returns 429 status
    // Customize response in bootstrap/app.php withMiddleware()
}
```

### Anti-Patterns to Avoid
- **Storing cart in database for guests:** Creates DB pollution, requires cleanup jobs, slower than sessions
- **Not snapshotting prices:** Changing product prices alters historical orders, breaks accounting
- **No transaction for order creation:** Risk of partial orders (order created but items fail), data inconsistency
- **Full page reload on cart updates:** Poor UX, CONTEXT explicitly requires AJAX updates
- **Missing CSRF on AJAX requests:** Security vulnerability, include X-CSRF-TOKEN header
- **Not validating phone format:** Invalid data, use phone:KG rule for Kyrgyzstan
- **Using float for prices:** Rounding errors, always use integer cents (e.g., 10000 = 100.00 сом)

## Don't Hand-Roll

Problems that look simple but have existing solutions:

| Problem | Don't Build | Use Instead | Why |
|---------|-------------|-------------|-----|
| Shopping cart logic | Custom session array manipulation | darryldecode/cart package | Already installed, handles item uniqueness, quantity updates, conditions (discounts), concurrent requests, totals calculation |
| Phone validation | Custom regex for +996 | propaganistas/laravel-phone with phone:KG rule | Handles international format (E.164), mobile/landline types, country codes (ISO 3166-1 alpha-2), validation edge cases |
| Rate limiting | Custom attempt tracking | Laravel's built-in RateLimiter | Supports per-IP, per-field, multiple limits, automatic 429 responses, Redis-backed for scale |
| Toast notifications | Custom JavaScript alerts | usernotnull/tall-toasts or Alpine.js component | <1KB footprint, queue support, auto-dismiss, Tailwind styling, accessible |
| Order number generation | Manual string concatenation | UUID or structured format (ORD-YYYYMMDD-NNNN) | Handles uniqueness, collisions, human-readable, sortable |
| Database transactions | Manual try-catch with commits | DB::transaction() with closure | Auto-rollback on exception, supports nested transactions with savepoints, deadlock retry (attempts: 5) |

**Key insight:** Laravel's ecosystem is mature for e-commerce. Common pitfalls (cart merging, price changes, race conditions, validation) are solved by battle-tested packages and built-in features. Custom implementations introduce bugs and maintenance burden.

## Common Pitfalls

### Pitfall 1: Cart Data Loss on Session Expiration
**What goes wrong:** Guest adds items to cart, session expires (default 120 minutes), cart disappears
**Why it happens:** Laravel's default session lifetime is short, guests don't have persistent accounts
**How to avoid:**
- Increase session lifetime in `config/session.php` to 43200 (30 days) for cart persistence
- Use database session driver (already configured) instead of file driver for better persistence
- Implement cart recovery: store cart data in localStorage as backup, restore on next visit
**Warning signs:** Users complain about losing cart items, high cart abandonment rate, session timeout errors

### Pitfall 2: Race Condition on Concurrent Cart Updates
**What goes wrong:** User opens cart in multiple tabs, updates quantity in both, one update overwrites the other
**Why it happens:** darryldecode/cart uses session storage, which is file-based by default (no atomic operations)
**How to avoid:**
- Use database or Redis session driver for atomic updates
- Implement optimistic locking: send current quantity with update request, reject if mismatch
- Use Cart::update() with relative updates instead of absolute (safer for concurrent requests)
**Warning signs:** Cart quantities incorrect after updates, items disappear unexpectedly, duplicate items

### Pitfall 3: Price Changes Between Cart and Checkout
**What goes wrong:** Admin changes product price while customer is checking out, order created with wrong price
**Why it happens:** Cart stores product ID (not price), price fetched from product table at checkout
**How to avoid:**
- darryldecode/cart already stores price with item (correct approach)
- Snapshot price in order_items during order creation (Pattern 2)
- Never query Product::find($id)->price during checkout
- Display price from cart, not from product model
**Warning signs:** Order totals don't match cart totals, customer complaints about wrong prices, accounting discrepancies

### Pitfall 4: Missing Transaction Rollback on Order Creation Failure
**What goes wrong:** Order created but order_items fail to insert, partial order in database
**Why it happens:** Separate Order::create() and OrderItem::create() calls without transaction
**How to avoid:**
- Always wrap order creation in DB::transaction() (Pattern 3)
- Use $order->items()->create() within transaction, not separate OrderItem::create()
- Don't clear cart until transaction commits successfully
- Log exceptions for debugging, show generic error to user
**Warning signs:** Orders with no items, orphaned order_items, cart not cleared after checkout error

### Pitfall 5: Insufficient Rate Limiting Leading to Spam Orders
**What goes wrong:** Bots submit hundreds of fake orders, filling database with garbage, admin overwhelmed
**Why it happens:** No rate limiting on checkout form (SEC-05 requirement not implemented)
**How to avoid:**
- Apply throttle:checkout middleware with strict limits (3 per 10 minutes per IP)
- Add secondary limit by phone number (1 per 2 minutes per phone)
- Implement CAPTCHA for suspicious patterns (optional for Phase 2, defer to future)
- Monitor 429 rate limit responses, adjust thresholds based on real usage
**Warning signs:** Spike in orders from same IP, sequential order numbers, identical customer data, invalid phone numbers

### Pitfall 6: Not Handling Sold-Out Products at Checkout
**What goes wrong:** Product in cart has stock=0, user proceeds to checkout, order created for unavailable item
**Why it happens:** No stock validation between "Add to Cart" and "Complete Order"
**How to avoid:**
- Check product stock during checkout processing before transaction
- Show error if any cart item is unavailable, remove from cart
- Lock product stock during transaction (SELECT ... FOR UPDATE) to prevent overselling
- Update product.stock after successful order creation (deferred to future phase if inventory tracking added)
**Warning signs:** Orders for out-of-stock products, customer complaints about unavailable items, negative stock values

### Pitfall 7: Phone Validation Accepts Invalid Kyrgyzstan Numbers
**What goes wrong:** User enters phone like "123456" or "+1-555-1234", passes basic validation, admin can't contact
**Why it happens:** Using 'numeric' or 'regex:/^[0-9]+$/' instead of proper E.164 validation
**How to avoid:**
- Install propaganistas/laravel-phone package
- Use 'phone:KG' validation rule in CheckoutRequest
- Store phone in E.164 format (+996XXXXXXXXX) in database
- Display formatted phone (e.g., +996 312 123 456) in admin panel
**Warning signs:** Invalid phone numbers in orders, admin unable to reach customers, complaints about undelivered orders

### Pitfall 8: Forgetting CSRF Token on AJAX Requests
**What goes wrong:** AJAX cart update returns 419 CSRF token mismatch error, cart not updated
**Why it happens:** Fetch/Axios requests don't include CSRF token by default
**How to avoid:**
- Add `<meta name="csrf-token" content="{{ csrf_token() }}">` in layout head
- Include X-CSRF-TOKEN header in all AJAX requests (see Pattern 4)
- Use Axios (includes CSRF automatically if meta tag present)
- Livewire components handle CSRF automatically (alternative to manual AJAX)
**Warning signs:** Console errors "419 CSRF token mismatch", AJAX updates fail, cart changes not persisted

## Code Examples

Verified patterns from official sources and best practices:

### Phone Validation for Kyrgyzstan
```php
// Source: https://github.com/Propaganistas/Laravel-Phone
// In app/Http/Requests/CheckoutRequest.php

<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Guest checkout, no auth required
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'phone' => ['required', 'phone:KG'], // Kyrgyzstan +996 format
            'address' => ['required', 'string', 'min:10', 'max:500'],
            'payment_method' => ['required', 'in:cash,online,installment'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Укажите ваше имя',
            'phone.required' => 'Укажите номер телефона',
            'phone.phone' => 'Неверный формат телефона. Используйте +996 XXX XXX XXX',
            'address.required' => 'Укажите адрес доставки',
            'payment_method.required' => 'Выберите способ оплаты',
        ];
    }
}
```

### Order Model with Status Management
```php
// Source: Laravel best practices + Phase 1 patterns
// In app/Models/Order.php

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'order_number',
        'customer_name',
        'customer_phone',
        'customer_address',
        'payment_method',
        'status',
        'subtotal',
        'total',
    ];

    protected $casts = [
        'subtotal' => 'integer',
        'total' => 'integer',
    ];

    // Relationship to order items
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    // Status constants
    public const STATUS_NEW = 'new';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_DELIVERING = 'delivering';
    public const STATUS_COMPLETED = 'completed';

    // Status labels for admin UI (Russian per CONTEXT)
    public static function statusLabels(): array
    {
        return [
            self::STATUS_NEW => 'Новый',
            self::STATUS_PROCESSING => 'В обработке',
            self::STATUS_DELIVERING => 'Доставляется',
            self::STATUS_COMPLETED => 'Выполнен',
        ];
    }

    // Format price for display (cents to сом)
    public function getFormattedTotalAttribute(): string
    {
        return number_format($this->total / 100, 0, ',', ' ') . ' сом';
    }

    // Scope for admin order list
    public function scopeRecent($query)
    {
        return $query->orderBy('created_at', 'desc');
    }
}
```

### Admin Order Controller
```php
// Source: Laravel CRUD patterns + admin panel best practices
// In app/Http/Controllers/Admin/OrderController.php

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    // Constructor with auth middleware (assuming admin auth implemented)
    public function __construct()
    {
        $this->middleware('auth'); // Add 'admin' middleware if implemented
    }

    // List all orders
    public function index()
    {
        $orders = Order::with('items')
            ->recent()
            ->paginate(20); // Consistent with Phase 1 pagination

        return view('admin.orders.index', compact('orders'));
    }

    // Show order detail
    public function show(Order $order)
    {
        $order->load('items'); // Eager load to prevent N+1

        return view('admin.orders.show', compact('order'));
    }

    // Update order status
    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => ['required', 'in:new,processing,delivering,completed'],
        ]);

        $order->update(['status' => $request->status]);

        return back()->with('success', 'Статус заказа обновлен');
    }
}
```

### Toast Notification Component (Alpine.js)
```html
<!-- Source: Alpine.js patterns + TALL Toasts concepts -->
<!-- In resources/views/components/toast.blade.php -->

<div
    x-data="{
        show: false,
        message: '',
        type: 'success',

        showToast(msg, toastType = 'success') {
            this.message = msg;
            this.type = toastType;
            this.show = true;

            setTimeout(() => { this.show = false }, 3000);
        }
    }"
    @cart-added.window="showToast($event.detail.message)"
    x-show="show"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0 translate-y-4"
    x-transition:enter-end="opacity-100 translate-y-0"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100 translate-y-0"
    x-transition:leave-end="opacity-0 translate-y-4"
    class="fixed bottom-4 right-4 z-50 max-w-sm"
    style="display: none;"
>
    <div
        class="rounded-lg shadow-lg px-6 py-4"
        :class="{
            'bg-green-500 text-white': type === 'success',
            'bg-red-500 text-white': type === 'error',
            'bg-blue-500 text-white': type === 'info'
        }"
    >
        <p x-text="message"></p>
    </div>
</div>

<!-- Usage in product card "Add to Cart" button -->
<button
    @click="addToCart({{ $product->id }})"
    class="btn-primary"
>
    Добавить в корзину
</button>

<script>
function addToCart(productId) {
    fetch('/cart/add', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ product_id: productId, quantity: 1 })
    })
    .then(response => response.json())
    .then(data => {
        // Dispatch event to show toast
        window.dispatchEvent(new CustomEvent('cart-added', {
            detail: { message: data.message }
        }));

        // Update header cart count
        document.querySelector('.cart-count').textContent = data.cart_count;
    })
    .catch(error => {
        console.error('Error adding to cart:', error);
    });
}
</script>
```

## State of the Art

| Old Approach | Current Approach | When Changed | Impact |
|--------------|------------------|--------------|--------|
| Custom cart session arrays | darryldecode/cart package | ~2015 | Package handles edge cases (conditions, item uniqueness), reduces bugs |
| Simple regex for phone | propaganistas/laravel-phone | ~2018 | Proper E.164 validation, country-specific rules, handles international format |
| Manual rate limiting | Laravel built-in RateLimiter | Laravel 8+ (2020) | Simpler configuration, Redis-backed, automatic 429 responses |
| Livewire v2 wire:model.debounce | Livewire v3 wire:model.live | 2023 | Deferred by default (better performance), .live for real-time |
| jQuery AJAX | Alpine.js + Fetch API | ~2020 | Zero dependencies, smaller footprint, reactive data binding |
| Multi-table cart (cart + cart_items) | Session-based cart | Always preferred for guests | No DB pollution, faster, simpler cleanup |
| Storing prices as decimal/float | Storing prices as integer (cents) | Best practice since forever | Eliminates rounding errors, consistent with Phase 1 decision |
| DB::beginTransaction() + try-catch | DB::transaction(closure) | Laravel 5+ | Auto-rollback, cleaner syntax, supports deadlock retry |

**Deprecated/outdated:**
- **jQuery for AJAX:** Alpine.js is lighter, more modern, ships with Livewire v3 (no extra install needed)
- **Manual CSRF in AJAX:** Axios and Alpine.js $wire handle automatically if meta tag present
- **ThrottleRequests::class direct usage:** Use middleware alias 'throttle:limiter_name' instead
- **Guest cart in database:** Session storage is now standard for guests (hybrid for authenticated)
- **Separate customer table for guests:** Embed customer data in orders table unless reuse needed

## Open Questions

Things that couldn't be fully resolved:

1. **Database vs Session driver for cart persistence**
   - What we know: Phase 1 configured database session driver, darryldecode/cart supports any session backend
   - What's unclear: Whether project has Redis available (would be better for concurrent updates)
   - Recommendation: Use database driver (already configured), document Redis upgrade path for scale

2. **Livewire vs vanilla Alpine.js for cart updates**
   - What we know: Livewire v3 ships with Alpine.js, provides reactive components, AJAX handled automatically
   - What's unclear: Whether Livewire is installed (not found in composer.json grep)
   - Recommendation: Use vanilla Alpine.js + Fetch API for now (lighter, explicit, follows Phase 1 patterns), Livewire can be added in future phases for more complex interactions

3. **Stock deduction on order creation**
   - What we know: Products have stock field (from Phase 1 migration), CONTEXT doesn't mention inventory tracking
   - What's unclear: Whether to decrement stock on order creation or defer to fulfillment phase
   - Recommendation: Don't decrement stock in Phase 2 (out of scope), validate stock > 0 during checkout, add stock management in future phase when warehouse integration needed

4. **Payment stub implementation detail**
   - What we know: Online payment and installment are placeholders (CONTEXT: integration deferred)
   - What's unclear: How much placeholder UI to show (just store choice, or show fake payment form?)
   - Recommendation: Just store payment_method in orders table, show success message regardless of method, add "Payment pending" status in future phase when real integration added

5. **Admin notification mechanism**
   - What we know: ORD-01 requires admin notification, CONTEXT defers Telegram/Email to future phase
   - What's unclear: What notification to implement in Phase 2 (just in-app badge? Log entry?)
   - Recommendation: Display new orders count in admin panel header (real-time count of status='new'), add badge/highlight for unread orders, defer push notifications to Phase 3+

## Sources

### Primary (HIGH confidence)
- Laravel 12 Official Documentation - Rate Limiting: https://laravel.com/docs/12.x/rate-limiting (accessed 2026-01-23)
- Laravel 12 Official Documentation - Routing: https://laravel.com/docs/12.x/routing (accessed 2026-01-23)
- Laravel 12 Official Documentation - Session: https://laravel.com/docs/12.x/session (accessed 2026-01-23)
- Laravel 12 Official Documentation - CSRF Protection: https://laravel.com/docs/12.x/csrf (accessed 2026-01-23)
- Darryldecode Laravel Shopping Cart GitHub: https://github.com/darryldecode/laravelshoppingcart (accessed 2026-01-23)
- Propaganistas Laravel Phone GitHub: https://github.com/Propaganistas/Laravel-Phone (accessed 2026-01-23)

### Secondary (MEDIUM confidence)
- Vertabelo Blog - Price History Database Model: https://vertabelo.com/blog/price-history-database-model/ (accessed 2026-01-23, cross-referenced with Laravel patterns)
- Laravel News - TALL Toasts Package: https://laravel-news.com/package/usernotnull-tall-toasts (accessed 2026-01-23)
- Mastery of Laravel - Database Transactions (Medium, December 2024): https://masteryoflaravel.medium.com/mastering-laravel-database-transactions-from-automatic-rollbacks-to-deadlock-retries-e6c8fe5cf55e
- Complete Guide To Managing Laravel Sessions 2026: https://wpwebinfotech.com/blog/laravel-sessions/ (accessed 2026-01-23)
- Kyrgyzstan Phone Numbers Guide 2025: https://www.sent.dm/resources/kg (accessed 2026-01-23, verified E.164 format)

### Tertiary (LOW confidence)
- DEV Community - Shopping cart state database or cookie (2021): https://dev.to/wolfiton/shopping-cart-state-saved-in-database-or-cookie-for-guests-371a (concept validated with official docs)
- Laracasts discussions on cart session management (various dates, general consensus only)
- PenguinUI - Toast Notifications Tutorial (October 2024): https://www.penguinui.com/blog/toasty-alerts-create-user-friendly-notifications-with-tailwind-css-and-alpine-js

## Metadata

**Confidence breakdown:**
- Standard stack: HIGH - darryldecode/cart already installed, Laravel built-ins verified from official docs, phone validation package well-documented
- Architecture: HIGH - Order/OrderItem patterns are industry standard, transaction pattern from official Laravel docs, AJAX patterns verified with Alpine.js docs
- Pitfalls: MEDIUM-HIGH - Common e-commerce issues (race conditions, price changes, CSRF) verified from multiple sources, some scenarios theoretical but based on known patterns

**Research date:** 2026-01-23
**Valid until:** 2026-03-23 (60 days - stable domain, Laravel 12 is current LTS, e-commerce patterns mature)

**Notes:**
- darryldecode/cart v4.2 already installed in project (verified from composer.json)
- Phase 1 established price storage in cents (integer), session security (http_only, same_site), pagination at 20 items
- CONTEXT decisions constrain implementation: separate cart page (not drawer), stay on page when adding to cart, AJAX quantity updates, single-page checkout, phone validation for +996, four order statuses only
- Livewire not detected in composer.json - recommend vanilla Alpine.js + Fetch API for this phase
- Redis availability unknown - use database session driver (already configured) as safe default
