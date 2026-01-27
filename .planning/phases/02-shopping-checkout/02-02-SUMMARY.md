---
phase: 02-shopping-checkout
plan: 02
completed: 2026-01-23
duration: 5 minutes
subsystem: checkout
tags: [checkout, forms, validation, orders, rate-limiting, transactions]

requires:
  - 02-01 (Order models and migrations)
  - 01-01 (Product models for cart items)
  - darryldecode/cart package (session-based cart)

provides:
  - Guest checkout workflow
  - Phone validation for Kyrgyzstan (+996)
  - Atomic order creation with transaction
  - Rate limiting for checkout form
  - Order confirmation page

affects:
  - 02-03 (Cart functionality will redirect to this checkout)
  - 03-admin (Admin will view orders created here)

tech-stack:
  added:
    - propaganistas/laravel-phone: ^6.0 (Phone validation for KG)
  patterns:
    - DB::transaction() for atomic order creation
    - Laravel rate limiting with multiple limits (IP + phone)
    - Form Request validation with custom messages
    - Price snapshot in order_items (immutable historical data)

key-files:
  created:
    - app/Http/Requests/CheckoutRequest.php
    - app/Http/Controllers/CheckoutController.php
    - resources/views/checkout/index.blade.php
    - resources/views/checkout/success.blade.php
  modified:
    - routes/web.php (checkout routes)
    - app/Providers/AppServiceProvider.php (rate limiter)
    - composer.json (phone package)

decisions:
  - D1: "Use propaganistas/laravel-phone for phone:KG validation rule (E.164 format for +996)"
  - D2: "Rate limit checkout: 3 attempts per 10 minutes per IP, 1 per 2 minutes per phone"
  - D3: "Order number format: ORD-YYYYMMDD-NNNN (sortable, human-readable, unique per day)"
  - D4: "Snapshot cart prices in order_items during transaction (prices in cents)"
  - D5: "Clear cart only after successful transaction commit (prevents data loss)"
  - D6: "Single-page checkout with all fields visible (no multi-step wizard)"
---

# Phase 02 Plan 02: Guest Checkout with Validation Summary

**One-liner:** Guest checkout with phone:KG validation, atomic order creation via DB::transaction(), and rate limiting (3 per 10min per IP)

## What Was Built

Implemented complete guest checkout workflow from form display to order confirmation, with strict validation including Kyrgyzstan phone format (+996), atomic database transactions preventing partial orders, and rate limiting to prevent spam submissions.

### Core Components

**1. Phone Validation Package**
- Installed propaganistas/laravel-phone ^6.0
- Provides phone:KG validation rule for E.164 format
- Validates +996 country code with proper number structure
- Used in CheckoutRequest for customer phone field

**2. Checkout Request Validation**
- Created CheckoutRequest with authorize() returning true (guest checkout)
- Validation rules: name (min:2), phone:KG, address (min:10), payment_method (in:cash,online,installment)
- Custom Russian error messages per CONTEXT requirement
- Example: 'phone.phone' => 'Неверный формат телефона. Используйте +996 XXX XXX XXX'

**3. Checkout Controller**
- **index()**: Check cart not empty, get items/total, return checkout form view
- **process(CheckoutRequest)**: Atomic order creation with DB::transaction()
  - Create order with generated order number (ORD-YYYYMMDD-NNNN)
  - Create order items with snapshot prices from cart
  - Convert prices to cents (* 100) for database storage
  - Clear cart only after successful transaction
  - Redirect to success page on success, back with errors on failure
- **success($orderNumber)**: Load order with items, display confirmation
- **generateOrderNumber()**: Format ORD-YYYYMMDD-NNNN (unique, sortable)

**4. Rate Limiter Configuration**
- Configured in AppServiceProvider::boot()
- RateLimiter::for('checkout') with two limits:
  - Limit::perMinutes(10, 3)->by($request->ip()) - 3 attempts per 10 minutes per IP
  - Limit::perMinutes(2, 1)->by($request->input('phone')) - 1 attempt per 2 minutes per phone
- Applied to checkout.process route via middleware(['throttle:checkout'])
- Prevents spam orders (SEC-05 requirement)

**5. Checkout Routes**
- GET /checkout -> checkout.index (show form)
- POST /checkout -> checkout.process (create order, throttle:checkout middleware)
- GET /checkout/success/{orderNumber} -> checkout.success (confirmation)

**6. Checkout Views**
- **index.blade.php**: Two-column layout (order summary left, form right)
  - Order summary: cart items with images, quantities, subtotals, total
  - Checkout form: name, phone (+996 placeholder), address (textarea), payment method (select)
  - Form preserves old() values on validation failure
  - Error display with @error directives per field
  - Mobile responsive: single column on mobile
- **success.blade.php**: Order confirmation page
  - Green success icon with "Спасибо за ваш заказ!" message
  - Order details: order_number, formatted_total, status badge
  - Customer info: name, phone, address, payment method
  - Order items list with quantities and prices
  - Next steps messaging: "Мы свяжемся с вами в ближайшее время"
  - Return to shopping link

## Technical Implementation

### Atomic Order Creation Pattern

```php
DB::transaction(function () use ($request) {
    // 1. Create order
    $order = Order::create([...]);

    // 2. Create order items (snapshot prices)
    foreach (Cart::getContent() as $item) {
        $order->items()->create([
            'price' => $item->price * 100, // Convert to cents
            'quantity' => $item->quantity,
            'subtotal' => ($item->price * $item->quantity) * 100,
            // ... other fields
        ]);
    }

    // 3. Clear cart
    Cart::clear();

    return $order;
});
```

**Why this matters:**
- All-or-nothing: if order_items fail, order is rolled back
- Cart cleared only after commit succeeds
- No partial orders in database
- Exception handling with automatic rollback

### Price Snapshot Strategy

Order items store price at purchase time, not reference to product price:
- `order_items.price` captures current product price during checkout
- Stored as integer cents (e.g., 10000 = 100.00 сом)
- Product price changes don't affect historical orders
- Order total always matches sum of order_items at time of purchase

### Rate Limiting Configuration

Two-tier rate limiting:
1. **IP-based**: 3 attempts per 10 minutes (prevents bot spam)
2. **Phone-based**: 1 attempt per 2 minutes (prevents same customer retry spam)

Returns 429 Too Many Requests when limit exceeded.

## Deviations from Plan

None - plan executed exactly as written.

## Testing Notes

**Manual verification required:**
1. Visit /checkout with empty cart - should redirect to /cart with error
2. Add products to cart, visit /checkout - form displays with order summary
3. Submit with invalid phone (e.g., "123456") - validation error in Russian
4. Submit with valid data (+996 555 123 456) - order created, redirects to success
5. Success page displays order number in ORD-YYYYMMDD-NNNN format
6. Check database: order and order_items created, prices in cents
7. Cart cleared after successful checkout
8. Try 4 rapid submissions - 4th blocked with 429 response

**Database verification:**
```sql
-- Check latest order
SELECT * FROM orders ORDER BY created_at DESC LIMIT 1;

-- Check order items with snapshot prices
SELECT * FROM order_items WHERE order_id = (SELECT id FROM orders ORDER BY created_at DESC LIMIT 1);

-- Verify prices in cents (e.g., 10000 for 100 сом)
-- Verify phone in E.164 format (+996XXXXXXXXX)
```

## Next Phase Readiness

**Ready for:**
- Plan 02-03: Cart functionality (can redirect to this checkout)
- Plan 03-admin: Order management (orders exist in database with complete data)

**Provides to future phases:**
- Order creation workflow for admin to view/manage
- Rate limiting pattern for other forms
- Phone validation pattern for other customer inputs
- Transaction pattern for other atomic operations

**Known limitations:**
- Phone validation accepts any +996 number (doesn't verify if number is real/active)
- Order number uniqueness relies on date + count (race condition possible under high load)
- Rate limiting uses cache (requires cache driver configured, defaults to file)
- No email confirmation sent (deferred to future phase per CONTEXT)
- Payment methods are placeholders (no real online payment integration yet)

## Commits

| Commit | Type | Description | Files |
|--------|------|-------------|-------|
| d8ea113 | chore | Install phone validation package | composer.json, composer.lock |
| e032424 | feat | Implement checkout request and controller | CheckoutRequest.php, CheckoutController.php, AppServiceProvider.php, web.php |
| be2f11c | feat | Build checkout form and success views | index.blade.php, success.blade.php |

## Dependencies for Next Plan

Plan 02-03 (Shopping Cart) will need:
- Route to checkout: route('checkout.index')
- Cart must be populated before checkout (Cart facade methods)
- Cart items must have attributes: slug, image for display

Plan 03-admin will need:
- Order model with items() relationship
- Order::statusLabels() for status display
- Order::recent() scope for listing
- formatted_total accessor for display

---

*Duration: 5 minutes*
*Completed: 2026-01-23*
