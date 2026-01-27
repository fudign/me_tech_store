# Architecture Research

**Domain:** E-commerce Platform (PHP/MySQL)
**Researched:** 2026-01-22
**Confidence:** HIGH

## Standard Architecture

### System Overview

```
┌─────────────────────────────────────────────────────────────┐
│                     PRESENTATION LAYER                       │
├──────────────┬──────────────┬──────────────┬────────────────┤
│  Storefront  │  Admin Panel │   API        │  SEO Routes    │
│  (Blade)     │  (Blade/Vue) │  (JSON)      │  (Slugs)       │
└──────┬───────┴──────┬───────┴──────┬───────┴────────┬───────┘
       │              │              │                │
┌──────┴──────────────┴──────────────┴────────────────┴───────┐
│                    APPLICATION LAYER                         │
├─────────────────────────────────────────────────────────────┤
│  ┌────────────┐  ┌────────────┐  ┌────────────┐            │
│  │Controllers │  │ Middleware │  │   Form     │            │
│  │            │  │            │  │ Requests   │            │
│  └─────┬──────┘  └─────┬──────┘  └─────┬──────┘            │
│        │               │               │                    │
│  ┌─────┴───────────────┴───────────────┴──────┐            │
│  │           BUSINESS LOGIC LAYER              │            │
│  ├─────────────────────────────────────────────┤            │
│  │  ┌──────────┐  ┌──────────┐  ┌──────────┐  │            │
│  │  │  Cart    │  │  Order   │  │ Payment  │  │            │
│  │  │ Service  │  │ Service  │  │ Service  │  │            │
│  │  └────┬─────┘  └────┬─────┘  └────┬─────┘  │            │
│  └───────┼─────────────┼─────────────┼─────────┘            │
│          │             │             │                      │
│  ┌───────┴─────────────┴─────────────┴─────────┐            │
│  │          DATA ACCESS LAYER                   │            │
│  ├──────────────────────────────────────────────┤            │
│  │  ┌────────────┐  ┌────────────┐             │            │
│  │  │Repository  │  │Repository  │             │            │
│  │  │ Pattern    │  │ Pattern    │             │            │
│  │  └─────┬──────┘  └─────┬──────┘             │            │
│  └────────┼───────────────┼────────────────────┘            │
│           │               │                                 │
├───────────┴───────────────┴─────────────────────────────────┤
│                      DOMAIN LAYER                            │
├─────────────────────────────────────────────────────────────┤
│  ┌─────────┐  ┌─────────┐  ┌─────────┐  ┌─────────┐        │
│  │ Product │  │  Order  │  │Customer │  │Category │        │
│  │  Model  │  │  Model  │  │  Model  │  │  Model  │        │
│  └────┬────┘  └────┬────┘  └────┬────┘  └────┬────┘        │
└───────┼────────────┼────────────┼────────────┼─────────────┘
        │            │            │            │
┌───────┴────────────┴────────────┴────────────┴─────────────┐
│                    PERSISTENCE LAYER                         │
├─────────────────────────────────────────────────────────────┤
│  ┌──────────┐  ┌──────────┐  ┌──────────┐  ┌──────────┐    │
│  │ products │  │  orders  │  │ customers│  │categories│    │
│  │  (MySQL) │  │ (MySQL)  │  │ (MySQL)  │  │ (MySQL)  │    │
│  └──────────┘  └──────────┘  └──────────┘  └──────────┘    │
└─────────────────────────────────────────────────────────────┘
```

### Component Responsibilities

| Component | Responsibility | Typical Implementation |
|-----------|----------------|------------------------|
| **Controllers** | Handle HTTP requests/responses, delegate to services | Thin controllers (50-100 lines each) |
| **Services** | Business logic, orchestration, validation | ProductService, OrderService, CartService |
| **Repositories** | Data access abstraction, query logic | ProductRepository, OrderRepository |
| **Models** | Domain entities, Eloquent relationships | Product, Order, Customer, Category |
| **Middleware** | Request filtering, authentication, authorization | Auth, Admin, CORS |
| **Form Requests** | Input validation, authorization | StoreProductRequest, CheckoutRequest |
| **Jobs** | Asynchronous tasks | SendOrderConfirmation, ProcessPayment |
| **Events/Listeners** | Decoupled event handling | OrderPlaced → UpdateInventory |

## Recommended Project Structure

```
mi_tech/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/                    # Admin panel controllers
│   │   │   │   ├── ProductController.php
│   │   │   │   ├── OrderController.php
│   │   │   │   ├── CategoryController.php
│   │   │   │   └── SettingsController.php
│   │   │   ├── Storefront/               # Customer-facing controllers
│   │   │   │   ├── HomeController.php
│   │   │   │   ├── ProductController.php
│   │   │   │   ├── CartController.php
│   │   │   │   └── CheckoutController.php
│   │   │   └── Api/                      # API endpoints (optional)
│   │   │       └── ProductController.php
│   │   ├── Middleware/
│   │   │   ├── AdminAuth.php
│   │   │   └── CartSession.php
│   │   └── Requests/
│   │       ├── StoreProductRequest.php
│   │       └── CheckoutRequest.php
│   ├── Models/                           # Eloquent models
│   │   ├── Product.php
│   │   ├── Category.php
│   │   ├── Order.php
│   │   ├── OrderItem.php
│   │   ├── Customer.php
│   │   └── Payment.php
│   ├── Services/                         # Business logic layer
│   │   ├── CartService.php
│   │   ├── OrderService.php
│   │   ├── PaymentService.php
│   │   ├── ProductService.php
│   │   └── SeoService.php
│   ├── Repositories/                     # Data access layer
│   │   ├── ProductRepository.php
│   │   ├── OrderRepository.php
│   │   └── CategoryRepository.php
│   ├── Jobs/
│   │   ├── SendOrderConfirmation.php
│   │   └── ProcessPaymentNotification.php
│   └── Providers/
│       └── AppServiceProvider.php        # Service bindings
├── database/
│   ├── migrations/                       # Database schema
│   │   ├── 001_create_categories_table.php
│   │   ├── 002_create_products_table.php
│   │   ├── 003_create_customers_table.php
│   │   ├── 004_create_orders_table.php
│   │   └── 005_create_order_items_table.php
│   ├── seeders/                          # Sample data
│   │   └── ProductSeeder.php
│   └── factories/                        # Test data factories
├── resources/
│   ├── views/
│   │   ├── admin/                        # Admin panel views
│   │   │   ├── products/
│   │   │   ├── orders/
│   │   │   └── layouts/
│   │   └── storefront/                   # Customer-facing views
│   │       ├── home.blade.php
│   │       ├── products/
│   │       └── layouts/
│   └── css/                              # Tailwind CSS
│       └── app.css
├── routes/
│   ├── web.php                           # Web routes (storefront + admin)
│   └── api.php                           # API routes (optional)
└── public/
    ├── images/                           # Product images
    └── uploads/                          # User uploads
```

### Structure Rationale

- **app/Http/Controllers/Admin vs Storefront:** Clear separation prevents controller bloat, easier to apply different middleware/auth
- **app/Services/:** Keeps controllers thin (Laravel best practice), centralizes business logic for reusability
- **app/Repositories/:** Optional but recommended for complex queries, enables testing without database, adheres to SOLID principles
- **database/migrations/ numbered:** Ensures correct execution order (categories before products, products before order_items)
- **resources/views/ by area:** Admin and storefront have different layouts, assets, and concerns

## Architectural Patterns

### Pattern 1: Service-Repository Pattern

**What:** Separate business logic (Services) from data access (Repositories), keeping Controllers thin

**When to use:** Always for medium-to-large e-commerce. Essential when:
- Multiple controllers need same business logic
- Complex queries need to be reused
- Testing business logic without database

**Trade-offs:**
- Pros: Clean separation, testable, reusable, follows SOLID
- Cons: More files (acceptable trade-off), slight learning curve

**Example:**
```php
// app/Services/CartService.php
class CartService
{
    public function __construct(
        private ProductRepository $products,
        private CartRepository $cart
    ) {}

    public function addToCart(int $productId, int $quantity): void
    {
        $product = $this->products->findOrFail($productId);

        // Business logic: check stock
        if ($product->stock < $quantity) {
            throw new OutOfStockException();
        }

        // Business logic: apply pricing rules
        $price = $this->calculatePrice($product, $quantity);

        $this->cart->add($productId, $quantity, $price);
    }
}

// app/Http/Controllers/Storefront/CartController.php
class CartController extends Controller
{
    public function add(Request $request, CartService $cart)
    {
        $cart->addToCart(
            $request->product_id,
            $request->quantity
        );

        return redirect()->back();
    }
}
```

### Pattern 2: Eloquent Relationships for Data Integrity

**What:** Use Laravel's Eloquent ORM to define model relationships instead of manual joins

**When to use:** Always. E-commerce is fundamentally relational (products → categories, orders → items)

**Trade-offs:**
- Pros: Cleaner code, automatic eager loading, prevents N+1 queries
- Cons: Learning curve for complex relationships

**Example:**
```php
// app/Models/Product.php
class Product extends Model
{
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}

// app/Models/Order.php
class Order extends Model
{
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }
}

// Usage: Eager loading prevents N+1 queries
$orders = Order::with(['customer', 'items.product', 'payment'])->get();
```

### Pattern 3: SEO-Friendly URLs with Slug + ID

**What:** Use human-readable slugs for SEO while maintaining unique IDs for routing

**When to use:** All public product/category pages

**Trade-offs:**
- Pros: Better SEO, non-breaking URLs even if product name changes
- Cons: Slightly more complex routing

**Example:**
```php
// Migration: add slug column
Schema::table('products', function (Blueprint $table) {
    $table->string('slug')->unique();
});

// Model: auto-generate slugs
class Product extends Model
{
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($product) {
            $product->slug = Str::slug($product->name);
        });
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }
}

// Route: {product:slug} uses slug for lookup
Route::get('/products/{product:slug}', [ProductController::class, 'show']);

// Fallback for ID-based URLs (non-breaking)
Route::get('/p/{id}', function ($id) {
    $product = Product::findOrFail($id);
    return redirect("/products/{$product->slug}", 301);
});
```

### Pattern 4: Session-Based Cart (MVP) → Database Cart (Scale)

**What:** Start with session storage for cart, migrate to database when needed

**When to use:**
- Session: MVP, low traffic, guest checkout only
- Database: User accounts, multi-device, abandoned cart recovery

**Trade-offs:**
- Session: Simple, fast, no database overhead | Lost on browser clear
- Database: Persistent, cross-device, analytics | Requires cleanup jobs

**Example:**
```php
// MVP: Session-based cart
class CartService
{
    public function add($productId, $quantity): void
    {
        $cart = session('cart', []);
        $cart[$productId] = [
            'quantity' => ($cart[$productId]['quantity'] ?? 0) + $quantity
        ];
        session(['cart' => $cart]);
    }
}

// Scale: Database cart (add later)
class CartService
{
    public function add($productId, $quantity): void
    {
        $userId = auth()->id() ?? session()->getId();

        Cart::updateOrCreate(
            ['user_id' => $userId, 'product_id' => $productId],
            ['quantity' => DB::raw('quantity + ' . $quantity)]
        );
    }
}
```

### Pattern 5: Admin Authorization with Gates/Policies

**What:** Use Laravel's authorization system for admin panel access control

**When to use:** Always for admin panel, prevents security issues

**Trade-offs:**
- Pros: Built-in, secure, testable
- Cons: None (this is standard)

**Example:**
```php
// app/Providers/AppServiceProvider.php
Gate::define('access-admin', function ($user) {
    return $user->is_admin;
});

Gate::define('manage-products', function ($user) {
    return $user->hasRole(['admin', 'catalog-manager']);
});

// Middleware: app/Http/Middleware/AdminAuth.php
class AdminAuth
{
    public function handle($request, Closure $next)
    {
        if (!Gate::allows('access-admin')) {
            abort(403);
        }
        return $next($request);
    }
}

// Route protection
Route::prefix('admin')->middleware(['auth', AdminAuth::class])->group(function () {
    Route::resource('products', ProductController::class);
});
```

## Data Flow

### Request Flow: Customer Checkout

```
[Customer] clicks "Checkout"
    ↓
[Route] web.php → CheckoutController@process
    ↓
[Controller] validates request
    ↓
[Service] OrderService->createOrder()
    ├─→ CartService->getItems()
    │       ↓
    │   [Repository] CartRepository->findByUser()
    │       ↓
    │   [Model] Cart::with('product')->get()
    │       ↓
    │   [MySQL] SELECT * FROM carts JOIN products...
    │
    ├─→ InventoryService->reserveStock()
    │       ↓
    │   [Repository] ProductRepository->decrementStock()
    │
    ├─→ PaymentService->charge()
    │       ↓
    │   [External API] Stripe/PayPal
    │
    └─→ OrderRepository->create()
            ↓
        [Model] Order::create()
            ↓
        [MySQL] INSERT INTO orders...
            ↓
        [Event] OrderPlaced dispatched
            ↓
        [Job] SendOrderConfirmation (queued)
    ↓
[Response] redirect to confirmation page
```

### Request Flow: Admin Product Update

```
[Admin] submits product form
    ↓
[Route] admin/products/{id} → ProductController@update
    ↓
[Middleware] AdminAuth checks permission
    ↓
[Form Request] StoreProductRequest validates
    ↓
[Controller] delegates to ProductService
    ↓
[Service] ProductService->update()
    ├─→ Generate SEO slug
    ├─→ Handle image upload
    └─→ ProductRepository->update()
            ↓
        [Model] Product::findOrFail()->update()
            ↓
        [MySQL] UPDATE products SET...
    ↓
[Response] redirect with success message
```

### State Management: Shopping Cart

```
[Session Store] (MVP)
    ↑ (read/write)
    │
[CartService] ←→ [CartController] ← [User Action]
    │
    └─→ ProductRepository (fetch product details)

[Database Store] (Scale)
    ↑ (read/write)
    │
[Cart Model] ← [CartRepository] ← [CartService] ← [Controller]
```

### Key Data Flows

1. **Product Catalog Flow:** Category pages → Filter/Search → Product listings (with eager loaded relationships) → Individual product pages
2. **Order Processing Flow:** Cart → Checkout → Payment validation → Inventory deduction → Order creation → Email notification (async)
3. **Admin Management Flow:** Admin login → Permission check → CRUD operations → Cache invalidation → Storefront update
4. **SEO URL Resolution Flow:** URL with slug → Route model binding → Product lookup by slug → 404 if not found OR 301 redirect if old ID-based URL

## Database Schema Overview

### Core Tables and Relationships

```sql
-- Categories (hierarchical)
categories
  ├─ id (PK)
  ├─ parent_id (FK → categories.id, nullable)
  ├─ name
  ├─ slug (unique, indexed)
  └─ description

-- Products
products
  ├─ id (PK)
  ├─ name
  ├─ slug (unique, indexed)
  ├─ description
  ├─ price (decimal)
  ├─ stock (integer)
  ├─ sku (unique)
  ├─ image_path
  ├─ meta_title (SEO)
  ├─ meta_description (SEO)
  └─ is_active (boolean)

-- Category-Product (many-to-many)
category_product
  ├─ category_id (FK → categories.id)
  └─ product_id (FK → products.id)

-- Customers
customers
  ├─ id (PK)
  ├─ name
  ├─ email (unique)
  ├─ password
  └─ is_admin (boolean)

-- Orders
orders
  ├─ id (PK)
  ├─ customer_id (FK → customers.id)
  ├─ status (enum: pending, processing, completed, cancelled)
  ├─ total (decimal)
  ├─ shipping_address
  ├─ created_at
  └─ updated_at

-- Order Items (line items)
order_items
  ├─ id (PK)
  ├─ order_id (FK → orders.id)
  ├─ product_id (FK → products.id)
  ├─ quantity
  ├─ price (snapshot at time of order)
  └─ subtotal (quantity * price)

-- Payments
payments
  ├─ id (PK)
  ├─ order_id (FK → orders.id)
  ├─ payment_method (enum: stripe, paypal, cod)
  ├─ transaction_id
  ├─ status (enum: pending, completed, failed)
  └─ amount
```

### Indexing Strategy

```sql
-- Performance-critical indexes
CREATE INDEX idx_products_slug ON products(slug);
CREATE INDEX idx_products_active ON products(is_active);
CREATE INDEX idx_orders_customer ON orders(customer_id);
CREATE INDEX idx_orders_status ON orders(status);
CREATE INDEX idx_order_items_order ON order_items(order_id);
CREATE INDEX idx_category_product ON category_product(category_id, product_id);
```

### Key Schema Decisions

1. **Price in order_items:** Snapshot product price at order time (prevents historical order corruption if product price changes)
2. **Slug uniqueness:** Enforced at database level for SEO URLs
3. **Soft deletes:** Consider for products/orders (preserves historical data)
4. **Denormalized totals:** Store order.total for performance (recalculate on order updates)

## Scaling Considerations

| Scale | Architecture Adjustments |
|-------|--------------------------|
| 0-1k users | Monolith is perfect. Single server, shared MySQL, session-based cart. Focus on features, not optimization. |
| 1k-10k users | Add Redis for sessions/cache. Database query optimization (indexes, eager loading). CDN for images. Queue for emails. |
| 10k-100k users | Separate database server. Full-page caching (Varnish). Horizontal scaling (multiple app servers behind load balancer). Database read replicas. |
| 100k+ users | Microservices consideration (catalog, order, payment as separate services). Elasticsearch for product search. Event-driven architecture. Database sharding. |

### Scaling Priorities

1. **First bottleneck: Database queries**
   - Symptom: Slow page loads on product listings, category pages
   - Fix: Add indexes, implement eager loading, query optimization, introduce Redis cache
   - When: 1000+ products or 100+ concurrent users

2. **Second bottleneck: Session storage**
   - Symptom: Cart issues, login problems, slow checkout
   - Fix: Move sessions from files to Redis/Memcached
   - When: Multiple app servers needed (load balancing)

3. **Third bottleneck: Image delivery**
   - Symptom: Slow product page loads, high bandwidth costs
   - Fix: CDN (CloudFlare, AWS CloudFront), image optimization
   - When: 1000+ daily visitors

## Anti-Patterns

### Anti-Pattern 1: Fat Controllers

**What people do:** Put all business logic directly in controllers
```php
// BAD: 200+ line controller method
public function checkout(Request $request)
{
    // Validate input
    // Check inventory
    // Calculate shipping
    // Process payment
    // Create order
    // Send email
    // Update stock
    // Clear cart
}
```

**Why it's wrong:**
- Untestable without HTTP requests
- Cannot reuse logic (e.g., admin creating order)
- Violates Single Responsibility Principle

**Do this instead:** Use Service classes
```php
// GOOD: Thin controller
public function checkout(CheckoutRequest $request, OrderService $orders)
{
    $order = $orders->createFromCart(
        auth()->user(),
        $request->validated()
    );

    return redirect()->route('order.confirmation', $order);
}
```

### Anti-Pattern 2: Ignoring N+1 Query Problem

**What people do:** Loop through products without eager loading relationships
```php
// BAD: N+1 queries (1 + 100 queries for 100 products)
$products = Product::all();
foreach ($products as $product) {
    echo $product->category->name; // Separate query each iteration
}
```

**Why it's wrong:** Generates hundreds of queries, kills performance

**Do this instead:** Eager load relationships
```php
// GOOD: 2 queries total
$products = Product::with('category')->get();
foreach ($products as $product) {
    echo $product->category->name;
}
```

### Anti-Pattern 3: Storing Cart Data Directly in Orders

**What people do:** Create order directly from cart without validation
```php
// BAD: What if product was deleted? Price changed?
$order = Order::create([
    'items' => session('cart') // Directly storing cart
]);
```

**Why it's wrong:**
- Product might be deleted (broken foreign keys)
- Price might have changed (wrong totals)
- Stock might be insufficient (overselling)

**Do this instead:** Validate and snapshot at order creation
```php
// GOOD: Validate and create immutable snapshot
public function createOrder($cartItems)
{
    foreach ($cartItems as $item) {
        $product = Product::findOrFail($item['id']);

        if ($product->stock < $item['quantity']) {
            throw new OutOfStockException();
        }

        $orderItems[] = [
            'product_id' => $product->id,
            'quantity' => $item['quantity'],
            'price' => $product->price, // Snapshot current price
        ];
    }

    return Order::create(['items' => $orderItems]);
}
```

### Anti-Pattern 4: No Slug Uniqueness Check

**What people do:** Generate slugs without ensuring uniqueness
```php
// BAD: Duplicate slugs cause routing conflicts
$product->slug = Str::slug($product->name); // "xiaomi-phone"
```

**Why it's wrong:** Multiple products with same name create duplicate slugs, breaking routing

**Do this instead:** Append ID or increment
```php
// GOOD: Ensure uniqueness
$slug = Str::slug($product->name);
$count = Product::where('slug', 'LIKE', "{$slug}%")->count();
$product->slug = $count ? "{$slug}-{$count}" : $slug;

// Or use package: spatie/laravel-sluggable
```

### Anti-Pattern 5: Skipping Authorization in Admin Panel

**What people do:** Check `is_admin` manually in each controller method
```php
// BAD: Repeated, error-prone
public function destroy($id)
{
    if (!auth()->user()->is_admin) {
        abort(403);
    }
    // Delete logic
}
```

**Why it's wrong:** Easy to forget, not centralized, hard to change

**Do this instead:** Use Middleware and Gates
```php
// GOOD: Centralized authorization
Route::middleware(['auth', 'admin'])->group(function () {
    Route::delete('/products/{id}', [ProductController::class, 'destroy']);
});
```

## Integration Points

### External Services

| Service | Integration Pattern | Notes |
|---------|---------------------|-------|
| Stripe/PayPal | SDK in PaymentService | Use webhooks for async confirmation, store transaction_id |
| Email (SMTP) | Laravel Mail + Queue | Queue all emails, use Mailgun/SendGrid in production |
| Image Storage | Local (MVP) → S3 (scale) | Use Laravel's filesystem abstraction for easy migration |
| Search | Database (MVP) → Elasticsearch (scale) | Start with MySQL LIKE, migrate when >10k products |

### Internal Boundaries

| Boundary | Communication | Notes |
|----------|---------------|-------|
| Controller ↔ Service | Direct method call | Service injected via dependency injection |
| Service ↔ Repository | Direct method call | Repository injected via interface binding |
| Service ↔ External API | Try/catch, log failures | Use Laravel HTTP client, implement retry logic |
| Admin ↔ Storefront | Shared Models/Services | Different routes, controllers, views; same business logic |

## Frontend-Backend Integration

### Blade Templates (Recommended for MVP)

**Pattern:** Server-side rendering with Blade, enhanced with Alpine.js for interactivity

```php
// Controller passes data to view
public function show(Product $product)
{
    return view('storefront.products.show', [
        'product' => $product->load('categories')
    ]);
}

// Blade template renders HTML
// resources/views/storefront/products/show.blade.php
<div class="product">
    <h1>{{ $product->name }}</h1>
    <p>{{ $product->description }}</p>

    <!-- Alpine.js for cart interaction -->
    <div x-data="{ quantity: 1 }">
        <input x-model="quantity" type="number">
        <button @click="addToCart({{ $product->id }}, quantity)">
            Add to Cart
        </button>
    </div>
</div>
```

**Trade-offs:**
- Pros: Simple, SEO-friendly, leverages Laravel strengths
- Cons: Full page reloads (acceptable for e-commerce)

### API + SPA (Future Option)

**Pattern:** Laravel API backend + Vue/React frontend (headless commerce)

```php
// API Controller returns JSON
public function show(Product $product)
{
    return new ProductResource($product);
}

// Frontend fetches data
fetch('/api/products/123')
    .then(res => res.json())
    .then(product => renderProduct(product))
```

**Trade-offs:**
- Pros: Highly interactive, better UX for complex interactions
- Cons: More complex, SEO requires SSR, not needed for MVP

**Recommendation:** Start with Blade + Alpine.js. Migrate to API + SPA only if UX demands it (rarely necessary for traditional e-commerce).

## Admin Panel Architecture

### Recommended Approach: Backpack for Laravel

**Why:** Pre-built CRUD operations, saves 40+ hours of development

```bash
composer require backpack/crud
php artisan backpack:install
```

**Structure:**
```
app/Http/Controllers/Admin/  # Backpack CRUD controllers
    ProductCrudController.php
    OrderCrudController.php

resources/views/vendor/backpack/  # Customizable admin views
```

**Alternative:** Build custom admin with same MVC pattern as storefront
- More control, more effort
- Useful if highly customized workflows needed

### Admin-Specific Patterns

1. **Bulk Operations:** Use Laravel's chunk() for processing large datasets
2. **Activity Logging:** Use spatie/laravel-activitylog for audit trail
3. **Settings Management:** Single `settings` table with key-value pairs
4. **File Uploads:** Use Intervention/Image for product image processing

## Build Order Implications

### Phase 1: Core Data Models (Build First)
**Why:** Everything depends on Products, Categories, Orders
- Create migrations (categories, products, category_product)
- Define Eloquent models with relationships
- Seed sample data
- **Dependency:** None

### Phase 2: Storefront Catalog (Build Second)
**Why:** Customers need to browse before buying
- Product listing pages
- Category filtering
- Search functionality
- Product detail pages
- **Dependency:** Phase 1 complete

### Phase 3: Shopping Cart (Build Third)
**Why:** Requires products to exist
- Session-based cart
- Add/remove/update items
- Cart summary page
- **Dependency:** Phase 2 complete (needs products)

### Phase 4: Checkout & Orders (Build Fourth)
**Why:** Most complex, depends on cart
- Order creation from cart
- Payment integration
- Order confirmation
- **Dependency:** Phase 3 complete (needs cart)

### Phase 5: Admin Panel (Build Fifth)
**Why:** Can be built in parallel, but lower priority than customer features
- Product management CRUD
- Order management
- Category management
- **Dependency:** Phase 1 complete (needs models)

### Phase 6: SEO & Polish (Build Last)
**Why:** Refinement after core functionality works
- SEO-friendly URLs (slugs)
- Meta tags
- Sitemap generation
- **Dependency:** Phases 2-5 complete

## SEO Architecture Considerations

### URL Structure
```
/                          # Homepage
/products                  # Product listing
/products/{slug}           # Product detail (e.g., /products/xiaomi-14-pro)
/categories/{slug}         # Category page (e.g., /categories/smartphones)
/p/{id}                    # Legacy ID-based redirect
```

### SEO Components in Models
```php
class Product extends Model
{
    protected $fillable = [
        'meta_title',        // <title> tag
        'meta_description',  // <meta name="description">
        'slug',              // URL slug
    ];

    public function getMetaTitleAttribute($value)
    {
        return $value ?: $this->name . ' | Mi Tech Store';
    }
}
```

### Sitemap Generation
```php
// Generate XML sitemap for search engines
Route::get('/sitemap.xml', function () {
    $products = Product::where('is_active', true)->get();
    return response()->view('sitemap', compact('products'))
        ->header('Content-Type', 'text/xml');
});
```

## Sources

**HIGH Confidence:**
- Laravel Official Docs: Structure - https://laravel.com/docs/11.x/structure
- Laravel Official Docs: Eloquent Relationships - https://laravel.com/docs/11.x/eloquent-relationships
- Vertabelo E-commerce ER Diagram - https://vertabelo.com/blog/er-diagram-for-online-shop/
- GeeksforGeeks E-commerce Database Design - https://www.geeksforgeeks.org/dbms/how-to-design-a-relational-database-for-e-commerce-website/

**MEDIUM Confidence:**
- DEV Community: Laravel Service-Repository Pattern - https://dev.to/blamsa0mine/structuring-a-laravel-project-with-the-repository-pattern-and-services-11pm
- Medium: Mastering Service-Repository Pattern - https://medium.com/@binumathew1988/mastering-the-service-repository-pattern-in-laravel-751da2bd3c86
- Laravel SEO Slugs Guide - https://sebastiandedeyne.com/non-breaking-seo-friendly-urls-in-laravel/
- Web and Crafts: Laravel E-commerce Packages 2026 - https://webandcrafts.com/blog/laravel-ecommerce-packages

**LOW Confidence (WebSearch only):**
- E-commerce architecture anti-patterns (general principles, not PHP-specific sources found)

---
*Architecture research for: Mi Tech E-commerce Platform*
*Researched: 2026-01-22*
