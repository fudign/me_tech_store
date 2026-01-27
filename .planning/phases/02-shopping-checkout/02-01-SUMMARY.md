---
phase: 02-shopping-checkout
plan: 01
subsystem: cart-management
tags: [cart, session, ajax, alpine-js, toast-notifications]

dependencies:
  requires:
    - Phase 01 (Product models and storefront views)
  provides:
    - Order/OrderItem database schema
    - Session-based shopping cart
    - AJAX cart operations
    - Cart page with reactive UI
    - Toast notification system
  affects:
    - 02-02 (checkout process depends on cart data)
    - 02-03 (admin orders depend on order schema)

tech-stack:
  added:
    - Alpine.js 3.x (via CDN for reactive components)
  patterns:
    - Session-based cart with darryldecode/cart package
    - Order snapshot pricing (immutable historical data)
    - Alpine.js reactive data binding
    - AJAX cart updates without page reload
    - Event-driven architecture (cart-updated, cart-added events)

key-files:
  created:
    - database/migrations/2026_01_23_201127_create_orders_table.php
    - database/migrations/2026_01_23_201133_create_order_items_table.php
    - app/Models/Order.php
    - app/Models/OrderItem.php
    - resources/views/cart/index.blade.php
    - resources/views/components/toast.blade.php
  modified:
    - resources/views/layouts/app.blade.php (Alpine.js, CSRF, cart badge)
    - resources/views/storefront/products/show.blade.php (Add to Cart button)
    - routes/web.php (cart routes already added in 02-02)
    - app/Http/Controllers/CartController.php (already created in 02-02)

decisions:
  - title: Use session-based cart for guest checkout
    rationale: No registration required per CONTEXT, avoids DB pollution for abandoned carts
    outcome: darryldecode/cart with session driver, survives page navigation
    reference: RESEARCH.md Pattern 1 (Session-Based Cart for Guests)

  - title: Snapshot product prices in order_items
    rationale: Preserve historical accuracy when product prices change
    outcome: price, product_name, product_slug stored in order_items at purchase time
    reference: RESEARCH.md Pattern 2 (Order with Snapshot Pricing)

  - title: AJAX cart updates with Alpine.js
    rationale: CONTEXT requires no page reload for quantity changes
    outcome: Alpine.js cartManager with reactive quantities, fetch API for server sync
    reference: RESEARCH.md Pattern 4 (AJAX Cart Updates with Alpine.js)

  - title: Event-driven cart count updates
    rationale: Header badge must update when cart changes without coupling components
    outcome: Custom events (cart-updated, cart-added) dispatched, Alpine.js listeners in header
    reference: Alpine.js best practices for component communication

metrics:
  duration: 5 minutes
  tasks: 3
  commits: 2
  completed: 2026-01-23
---

# Phase 2 Plan 01: Shopping Cart with AJAX Updates Summary

**One-liner:** Session-based shopping cart with Alpine.js reactive UI, AJAX quantity updates, and order snapshot pricing schema

## What Was Built

### Order Database Schema

Created 2 migrations following snapshot pricing pattern:

1. **orders table:**
   - `order_number` (unique) - Format: ORD-YYYYMMDD-NNNN
   - Customer info: `customer_name`, `customer_phone`, `customer_address`
   - `payment_method` enum: cash, online, installment
   - `status` enum: new, processing, delivering, completed (default: new)
   - Totals: `subtotal`, `total` (stored as integer cents)
   - Timestamps for audit trail

2. **order_items table:**
   - Foreign keys: `order_id` (cascade on delete), `product_id` (reference only)
   - **Snapshot fields:** `product_name`, `product_slug`, `price` (cents AT PURCHASE TIME), `quantity`, `subtotal`
   - `attributes` JSON (e.g., {"Память": "256GB", "Цвет": "Black"})
   - **Why snapshot:** Product prices may change; order must reflect original purchase price

### Eloquent Models

**Order Model:**
- Status constants: STATUS_NEW, STATUS_PROCESSING, STATUS_DELIVERING, STATUS_COMPLETED
- `statusLabels()` method returns Russian translations per CONTEXT
- `getFormattedTotalAttribute()` accessor: converts cents to "X сом" format
- `scopeRecent()` for admin order list (DESC by created_at)
- `items()` relationship: hasMany(OrderItem::class)

**OrderItem Model:**
- Integer casts for price, quantity, subtotal
- Array cast for attributes JSON
- `order()` relationship: belongsTo(Order::class)
- `product()` relationship: belongsTo(Product::class) - for admin reference only, NOT for price lookup

### Cart Functionality

**CartController (already existed from plan 02-02):**
- `index()` - Display cart page with items and total
- `add()` - AJAX endpoint, returns JSON with cart_count
- `update()` - AJAX quantity update, returns updated totals
- `remove()` - AJAX remove item, returns success message
- All methods wrapped in try-catch for graceful error handling

**Cart Routes:**
- GET /cart → cart.index
- POST /cart/add → cart.add (AJAX)
- PATCH /cart/{itemId} → cart.update (AJAX)
- DELETE /cart/{itemId} → cart.remove (AJAX)

### Cart Page UI (resources/views/cart/index.blade.php)

**Empty State:**
- Icon, message: "Ваша корзина пуста"
- Link to products page

**Cart Items (when not empty):**
- Product image, name (linked), price per unit
- Quantity controls: - button, input (type=number), + button
- Remove button with confirmation
- Subtotal per item (price × quantity)

**Order Summary Sidebar:**
- Total items count
- Total price
- "Оформить заказ" button → /checkout
- "Продолжить покупки" link → /products

**Alpine.js cartManager():**
- Reactive quantities object
- `incrementQuantity()` / `decrementQuantity()` methods
- `updateQuantity()` - Fetches PATCH /cart/{itemId}, updates totals
- `removeItem()` - Fetches DELETE /cart/{itemId}, reloads page
- `formatPrice()` - Formats number with Russian locale
- Dispatches 'cart-updated' event to update header badge

### Toast Notification Component (resources/views/components/toast.blade.php)

**Alpine.js reactive toast:**
- Listens for @cart-added.window and @cart-removed.window events
- Auto-dismiss after 3 seconds
- Support for success/error/info types with Tailwind colors
- Fixed bottom-right position
- Fade in/out transitions

### Updated Layout (resources/views/layouts/app.blade.php)

**Added to `<head>`:**
- CSRF meta tag: `<meta name="csrf-token" content="{{ csrf_token() }}">`
- Alpine.js CDN: `<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>`

**Updated cart button in header:**
- Changed from `<button>` to `<a href="/cart">`
- Added Alpine.js x-data with reactive cartCount
- Cart badge: shows count when > 0, hidden when empty
- Listens for @cart-updated.window event to update count

**Added before `</body>`:**
- Toast component: `<x-toast />`

### Updated Product Detail Page (resources/views/storefront/products/show.blade.php)

**Replaced placeholder button with functional "Add to Cart":**
- Alpine.js x-data with `adding` state
- Button disabled during AJAX request
- Shows "Добавление..." while processing
- `addToCart()` function:
  - Fetches POST /cart/add with product_id and quantity
  - Includes X-CSRF-TOKEN header
  - Dispatches 'cart-added' event on success (shows toast)
  - Dispatches 'cart-updated' event (updates header badge)
  - Error handling with console.error

## Key Architectural Decisions

### Session-Based Cart (Not Database)
**Problem:** Guest checkout requires cart without registration, database cart creates pollution
**Solution:** darryldecode/cart package with session storage
**Implementation:**
- Cart data stored in Laravel session (database session driver from Phase 1)
- Cart::add(), Cart::update(), Cart::remove(), Cart::getContent()
- Price stored as decimal in cart (converted from cents), converted back to cents for orders

**Benefits:**
- No DB pollution from abandoned carts
- Fast read/write operations (session is cached)
- Automatic cleanup with session expiration
- Survives page navigation (session lifetime: 120 minutes)

### Order Snapshot Pricing
**Problem:** If product price changes, historical orders show wrong prices
**Solution:** Store price, name, slug in order_items at purchase time
**Implementation:**
- OrderItem has product_name, product_slug, price fields
- CheckoutController (future plan) will copy data from cart to order_items
- product_id kept as foreign key for admin reference only

**Benefits:**
- Order totals never change when products update
- Accounting remains accurate
- Order emails/invoices show correct historical prices

### AJAX with Alpine.js (Not Full Page Reload)
**Problem:** CONTEXT requires quantity updates without page reload
**Solution:** Alpine.js reactive data + fetch API for AJAX
**Implementation:**
- Alpine.js x-data="cartManager()" manages client-side state
- fetch() calls PATCH /cart/{itemId} on quantity change
- Server returns updated totals, Alpine updates UI reactively
- CSRF token included in X-CSRF-TOKEN header

**Benefits:**
- Instant UI feedback (no loading spinner needed)
- Smooth UX (no page flash)
- Reduced server load (only JSON response, not full HTML)

### Event-Driven Cart Updates
**Problem:** Header cart badge must update when cart changes, but components are separate
**Solution:** Custom DOM events for component communication
**Implementation:**
- Product page dispatches 'cart-added' event on successful add
- Cart page dispatches 'cart-updated' event on quantity change
- Header listens with @cart-updated.window, updates cartCount
- Toast listens with @cart-added.window, shows notification

**Benefits:**
- Loose coupling (components don't know about each other)
- Easy to add new listeners (e.g., cart icon animation)
- Standard DOM events (works with any framework)

## Verification Status

**Migrations executed successfully:**
- ✅ orders table created with correct schema
- ✅ order_items table created with foreign keys
- ✅ Order::STATUS_NEW constant accessible
- ✅ Order::statusLabels() returns Russian labels

**Routes verified:**
- ✅ 4 cart routes registered (index, add, update, remove)
- ✅ CartController loads without errors

**UI Components (requires browser testing):**
- Cart page displays (not tested yet)
- Add to Cart button functional (not tested yet)
- Toast notifications appear (not tested yet)
- Header badge updates (not tested yet)

**Expected manual verification:**
1. Visit /products/{slug}, click "Добавить в корзину"
   - Should stay on page, show toast, header badge increments
2. Visit /cart, see added product
3. Change quantity on cart page
   - Should update total without page reload, header badge updates
4. Click remove button
   - Should show confirmation, remove item, header badge decrements
5. Empty cart shows "Ваша корзина пуста" message

## Deviations from Plan

### Auto-fixed Issues

None. Plan executed exactly as written.

### Plan Adherence

All plan objectives executed successfully:
- ✅ Task 1: Order database schema created with migrations and models
- ✅ Task 2: CartController with AJAX support (already existed from plan 02-02)
- ✅ Task 3: Cart page with Alpine.js reactive UI, toast notifications, updated layout

**Note:** CartController and routes were already created in commit e032424 (plan 02-02 executed before 02-01). This is expected as both plans were part of the same wave. No duplication occurred - the existing CartController was used without modification.

## Commits

1. **54e688d** - `feat(02-01): create order database schema`
   - Orders table migration (order_number, customer fields, payment_method, status, totals)
   - Order items table migration (snapshot pricing pattern with product data)
   - Order model with status constants, relationships, formatted total accessor
   - OrderItem model with casts and relationships

2. **115e1bc** - `feat(02-01): build cart page with Alpine.js AJAX updates`
   - Cart index view with empty state and item list
   - Alpine.js cartManager for reactive quantity updates
   - Toast notification component
   - Updated app layout: Alpine.js, CSRF meta tag, reactive cart badge
   - Updated product detail page with Add to Cart button
   - AJAX quantity controls with debouncing
   - Remove item functionality with confirmation

## Next Phase Readiness

**Blockers:** None

**Ready for Plan 02-02 (Checkout Process):**
- ✅ Order and OrderItem models exist
- ✅ Cart::getContent() available for checkout
- ✅ Order snapshot pricing pattern ready
- ✅ CSRF token in layout for checkout form

**Ready for Plan 02-03 (Admin Order Management):**
- ✅ Order model with status constants and Russian labels
- ✅ Order::scopeRecent() for admin order list
- ✅ OrderItem relationship for order detail view

**Concerns:**
None. All functionality implemented per CONTEXT requirements.

**Recommendations for next plans:**
1. Implement checkout form validation (phone:KG rule per RESEARCH.md)
2. Use DB::transaction() for order creation (RESEARCH.md Pattern 3)
3. Generate order_number with format ORD-YYYYMMDD-NNNN
4. Clear cart after successful order creation
5. Admin panel should use Order::statusLabels() for Russian dropdown

## Files Reference

**Migrations:**
- `database/migrations/2026_01_23_201127_create_orders_table.php` - Orders with customer info, payment, status
- `database/migrations/2026_01_23_201133_create_order_items_table.php` - Order items with snapshot pricing

**Models:**
- `app/Models/Order.php` - Status constants, statusLabels(), formattedTotal accessor, items() relationship
- `app/Models/OrderItem.php` - Casts, order() and product() relationships

**Controllers:**
- `app/Http/Controllers/CartController.php` - index, add, update, remove (created in 02-02)

**Views:**
- `resources/views/cart/index.blade.php` - Cart page with Alpine.js cartManager
- `resources/views/components/toast.blade.php` - Toast notification component
- `resources/views/layouts/app.blade.php` - Updated with Alpine.js, CSRF, cart badge
- `resources/views/storefront/products/show.blade.php` - Updated with Add to Cart button

**Routes:**
- `routes/web.php` - Cart routes (added in 02-02)

**Dependencies:**
- Alpine.js 3.x (CDN) - Reactive UI components
- darryldecode/cart 4.2 (installed in Phase 1) - Session-based cart management

---

*Plan 02-01 completed: 2026-01-23*
*Duration: 5 minutes*
*Commits: 2*
*Files created: 6*
*Files modified: 4*
