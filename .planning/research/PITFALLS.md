# Pitfalls Research

**Domain:** PHP E-commerce Platform (Xiaomi Electronics)
**Researched:** 2026-01-22
**Confidence:** MEDIUM

## Critical Pitfalls

### Pitfall 1: SQL Injection via Unvalidated User Input

**What goes wrong:**
Unvalidated user inputs from forms, search boxes, filters, and API endpoints allow attackers to inject malicious SQL queries. This is the #1 PHP security vulnerability and can expose customer data, payment information, and admin credentials. In e-commerce, this is particularly dangerous as product searches, filters, and checkout forms all accept user input.

**Why it happens:**
- Direct concatenation of user input into SQL queries
- Trusting form data, URL parameters, or cookie values
- Using deprecated mysql_* functions instead of PDO/MySQLi prepared statements
- Rushing to get features working without security review
- Assuming basic input sanitization is sufficient

**How to avoid:**
- ALWAYS use PDO prepared statements with parameterized queries
- Never concatenate user input directly into SQL strings
- Validate and whitelist input types (e.g., product ID must be integer)
- Use mysqli_real_escape_string() only as secondary defense, not primary
- Implement input validation layer before database interaction

**Warning signs:**
- Search functionality breaks with special characters like quotes
- SQL error messages visible to users (information leakage)
- Direct string concatenation visible in database query code
- No use of prepare() and execute() methods in codebase

**Phase to address:**
Phase 1 (Foundation) - Establish secure database abstraction layer from day one. Every subsequent feature must use this layer.

---

### Pitfall 2: Weak Session Management for Guest Checkout

**What goes wrong:**
Guest checkout sessions hijacked, cart data lost on page refresh, checkout state lost on network interruptions, or session fixation attacks allow attackers to take over guest sessions. Since your platform is guest-only (no user accounts), session security is your ONLY authentication mechanism.

**Why it happens:**
- Using default PHP session settings (session.cookie_httponly = 0)
- Not regenerating session IDs after checkout starts
- Storing sessions in files on shared hosting
- Short session timeouts that lose cart data
- No session validation beyond session ID existence
- Trusting session data without server-side validation

**How to avoid:**
- Set session.cookie_httponly = true and session.cookie_secure = true
- Regenerate session ID when transitioning to checkout: session_regenerate_id(true)
- Use Redis or database for session storage (not file-based)
- Set session lifetime to 30-60 minutes with activity extension
- Store critical cart data in database, not just session
- Implement CSRF tokens for all state-changing operations
- Add session fingerprinting (IP + User-Agent hash) for validation

**Warning signs:**
- Guest carts disappear on browser refresh
- Users report "session expired" during checkout
- Session files accumulating in /tmp directory
- No session cleanup cron job configured
- Session IDs visible in URL parameters

**Phase to address:**
Phase 1 (Foundation) - Session architecture must be solid before guest checkout implementation. This is your authentication system.

---

### Pitfall 3: Payment Integration Without Proper Error Handling

**What goes wrong:**
Payment gateway timeouts leave orders in limbo (payment succeeded but order not confirmed, or payment failed but order marked as paid). Customers charged multiple times due to retry logic. Webhook failures cause order/payment state mismatch. For Kyrgyzstan market with emerging payment infrastructure, these issues are amplified by network reliability concerns.

**Why it happens:**
- Assuming payment API calls always return immediately
- Not implementing idempotency keys for payment retries
- Trusting payment gateway callbacks without verification
- No reconciliation process between gateway and local database
- Storing payment credentials in plain text or code
- Not handling gateway maintenance windows or downtimes
- Insufficient logging of payment flow state transitions

**How to avoid:**
- Implement idempotent payment processing with unique transaction IDs
- Use webhook signature verification for all gateway callbacks
- Create payment state machine: pending → processing → completed/failed
- Store orders as "pending" until payment confirmation webhook received
- Implement daily reconciliation job comparing gateway vs. local records
- Never store card details - use tokenization
- Store payment gateway credentials in environment variables, not code
- Log every payment state transition with timestamps
- Set reasonable API timeouts (10-30 seconds) with retry logic
- Build admin dashboard to manually reconcile mismatched orders

**Warning signs:**
- Customer complaints about duplicate charges
- Orders marked "paid" with no payment gateway record
- Payment gateway webhooks returning 500 errors
- No webhook endpoint testing or monitoring
- Hardcoded API keys visible in code
- No payment audit log table in database

**Phase to address:**
Phase 3 (Payment Integration) - Allocate 40% of payment phase time to error handling, not just happy path. Payment reconciliation must be built-in, not retrofitted.

---

### Pitfall 4: Missing CSRF Protection on Admin Panel

**What goes wrong:**
Attackers trick admin users into executing unwanted actions (deleting products, changing prices, creating admin accounts) by embedding malicious requests in emails or websites. This is particularly dangerous as your admin panel controls product catalog, pricing, and orders. Since admin is familiar with OpenCart, they may expect certain UX patterns that could conflict with proper CSRF implementation.

**Why it happens:**
- Assuming admin login is sufficient security
- Not implementing CSRF tokens for form submissions
- Using GET requests for state-changing operations
- Trusting Referer header for protection
- AJAX requests without token validation
- Copy-pasting admin panel code without security review

**How to avoid:**
- Generate unique CSRF token per session: $_SESSION['csrf_token'] = bin2hex(random_bytes(32))
- Include token in all forms as hidden field
- Validate token on every POST/PUT/DELETE request
- Use POST/PUT/DELETE for state changes, never GET
- Implement SameSite=Strict cookie attribute
- Add token to AJAX headers: X-CSRF-Token
- Token regeneration after successful admin login

**Warning signs:**
- Admin actions work via direct URL access (GET requests)
- No hidden token fields in admin forms
- Form submissions don't validate any token
- DELETE operations accessible via simple URL
- No CSRF middleware or validation layer

**Phase to address:**
Phase 4 (Admin Panel) - CSRF protection must be part of admin foundation, not added later. Every admin form/action must validate token from day one.

---

### Pitfall 5: File Upload Vulnerabilities in Admin Product Management

**What goes wrong:**
Attackers upload PHP shell scripts disguised as product images, gaining server-side code execution and full system compromise. They can steal database credentials, inject malware into product pages, or pivot to other systems. Admin product image uploads are a critical attack vector.

**Why it happens:**
- Validating only file extension, not file content
- Storing uploaded files in web-accessible directories with execute permissions
- Trusting Content-Type header from client
- Not randomizing uploaded filenames
- Using uploaded filename directly in file paths
- Insufficient file type whitelist

**How to avoid:**
- Validate file content with getimagesize() or fileinfo, not just extension
- Store uploads outside web root, serve via PHP script with content disposition
- Whitelist extensions: only .jpg, .jpeg, .png, .webp
- Generate random filenames: bin2hex(random_bytes(16)) . '.jpg'
- Set upload directory permissions to prevent execution (chmod 755)
- Implement maximum file size limits (2-5MB for product images)
- Re-encode images with GD/Imagick to strip potential malicious payloads
- Store original filename in database, never use for file path

**Warning signs:**
- Uploaded files keep original user-provided names
- Upload directory is directly web-accessible
- .php files can be uploaded and accessed
- No file content validation beyond extension check
- Upload handling code uses $_FILES['name'] in file paths

**Phase to address:**
Phase 4 (Admin Panel) - Product management is one of first admin features. File upload security must be correct from initial implementation.

---

### Pitfall 6: Poor Product Catalog Database Schema Design

**What goes wrong:**
Product queries become extremely slow as catalog grows. Complex product variations (Xiaomi phones with different storage/color options) cause denormalized data and inventory tracking nightmares. Price updates require updating hundreds of rows. Product attributes stored as serialized blobs prevent filtering and searching.

**Why it happens:**
- Storing product variations as separate products instead of using variation table
- Using JSON/serialized columns for attributes that need filtering
- No proper indexing on search columns (name, SKU, category)
- Storing images as BLOBs in database instead of file paths
- EAV (Entity-Attribute-Value) pattern for simple product catalogs
- Not planning for multi-language support (critical for Kyrgyzstan market)

**How to avoid:**
- Core tables: products (base product) → product_variations (SKU/price/stock per variation) → product_attributes (filterable specs)
- Index: product name, SKU, category_id, price, stock, status
- Store images as file paths, not BLOBs
- Use InnoDB engine for referential integrity (NOT MyISAM)
- Normalize attributes that users filter by (storage, color, brand)
- Use JSON columns only for non-searchable metadata
- Plan for product_translations table if supporting Russian + Kyrgyz languages

**Warning signs:**
- Product list query takes >500ms with 100 products
- Adding new product attribute requires schema migration
- Inventory updates require updating multiple tables manually
- EXPLAIN shows full table scans on product queries
- Variations stored as JSON instead of related table

**Phase to address:**
Phase 2 (Product Catalog) - Database schema is hardest thing to change later. Get it right during catalog phase, not refactor in Phase 6.

---

### Pitfall 7: Incorrect Tax and Price Calculation Rounding

**What goes wrong:**
Cart totals don't match sum of line items due to rounding errors. Tax calculations off by cents, causing accounting mismatches and payment gateway rejection. Multi-item orders accumulate rounding errors. This is a common issue across all PHP e-commerce platforms (Magento, WooCommerce, OpenCart, PrestaShop).

**Why it happens:**
- Using float/double for money values instead of integers or decimal
- Rounding at wrong stage (per-unit vs per-line vs order-level)
- Inconsistent rounding between price display and payment calculation
- Using round() instead of floor()/ceil() where appropriate
- Not accounting for tax-inclusive vs tax-exclusive display
- Currency conversion without proper decimal handling

**How to avoid:**
- Store all money values as integers (cents) in database: price INT (amount in cents)
- Use bcmath functions for monetary calculations: bcmul(), bcadd(), bcdiv()
- Define rounding strategy: round per-line item, then sum for order total
- Calculate: item_price * quantity → apply tax → round to 2 decimals → sum all lines
- Display helper: function formatMoney($cents) { return number_format($cents / 100, 2) }
- Write unit tests for common scenarios: 3 items at $10.99 with 15% tax
- Document rounding strategy in code comments

**Warning signs:**
- Cart total doesn't match displayed sum of items
- Payment gateway rejection errors due to amount mismatch
- Accounting reports off by cents at month-end
- Database schema uses FLOAT or DOUBLE for prices
- No unit tests for price calculations

**Phase to address:**
Phase 2 (Product Catalog) - Price calculation architecture must be correct before cart/checkout implementation. Define price calculation strategy early.

---

### Pitfall 8: SEO-Hostile URL Structure and Missing Meta Tags

**What goes wrong:**
Product pages have URLs like ?id=12345 instead of /xiaomi-redmi-note-13-pro, killing SEO. No structured data for products. Missing or duplicate meta descriptions. No sitemap or robots.txt. For Google/Yandex indexing in competitive electronics market, poor SEO means invisible store.

**Why it happens:**
- Using default PHP query parameters instead of URL rewriting
- Not implementing friendly URL routing
- Copying product titles to meta tags without optimization
- No schema.org markup for products
- Forgetting canonical URLs for pagination/filters
- Not generating XML sitemap dynamically

**How to avoid:**
- Use .htaccess URL rewriting for clean URLs: RewriteRule ^product/([^/]+)$ product.php?slug=$1
- Generate SEO-friendly slugs: xiaomi-redmi-note-13-pro-128gb
- Implement URL routing layer (or use framework router)
- Add Product schema.org JSON-LD to all product pages
- Unique meta title (50-60 chars) and description (150-160 chars) per product
- Canonical tags for paginated category pages
- Generate sitemap.xml dynamically from products table
- Create robots.txt allowing /product/, /category/, blocking /admin/, /cart/
- Implement Open Graph tags for social sharing
- Add alt text to all product images

**Warning signs:**
- URLs contain query parameters: ?page=product&id=123
- All pages have same meta description
- No structured data visible in Google Rich Results Test
- No sitemap.xml file generated
- Product images missing alt attributes

**Phase to address:**
Phase 2 (Product Catalog) - URL structure locked in early. SEO-friendly URLs must be part of initial product page implementation, not retrofitted.

---

### Pitfall 9: Using Outdated PHP Version or Extensions

**What goes wrong:**
Running PHP 8.0 or earlier means no security updates, exposing store to unpatched vulnerabilities. Deprecated mysql_* functions cause security holes. Missing essential PHP extensions for payment/security. Modern payment gateways may not support old PHP versions.

**Why it happens:**
- Shared hosting restrictions on PHP version
- Fear of breaking existing code during upgrade
- Not checking PHP compatibility of payment gateway SDKs
- Using legacy tutorials with deprecated functions
- Not monitoring PHP version end-of-life dates

**How to avoid:**
- Require PHP 8.1+ minimum (8.1 supported until Nov 2025, 8.2+ recommended for 2026)
- Use PDO or MySQLi exclusively, never mysql_* functions
- Essential extensions: pdo_mysql, openssl, curl, gd/imagick, mbstring, intl, zip
- Check payment gateway SDK requirements before PHP version decision
- Test on PHP version matching production environment
- Add PHP version check in deployment script
- Enable OPcache for performance: opcache.enable=1

**Warning signs:**
- PHP version below 8.1
- Deprecated warnings in error logs
- Using mysql_connect() instead of PDO
- Missing openssl extension (required for payment security)
- No opcache enabled (performance issue)

**Phase to address:**
Phase 1 (Foundation) - PHP version and extensions are infrastructure decisions that affect everything. Lock in version requirements before writing code.

---

### Pitfall 10: No Inventory Synchronization Strategy for Multi-Channel

**What goes wrong:**
Product marked as "in stock" on website but actually sold out, leading to order cancellations and customer complaints. Inventory count discrepancies between admin panel and actual stock. No reservation system for items in cart, causing overselling. Multiple people checking out same last item simultaneously.

**Why it happens:**
- Simple stock counter with no transaction handling
- Not decrementing stock atomically during checkout
- No cart reservation system
- No real-time stock checks before payment
- Manual inventory updates without audit trail
- Stock sync issues if expanding to multiple sales channels later

**How to avoid:**
- Use database transactions for inventory operations: BEGIN → UPDATE stock → INSERT order → COMMIT
- Atomic stock update: UPDATE products SET stock = stock - ? WHERE id = ? AND stock >= ?
- Check affected rows to detect insufficient stock
- Cart reservation: create cart_items table with expiration timestamp
- Run cron job to release expired reservations (30-60 min)
- Stock check before payment processing, not just at add-to-cart
- Admin inventory adjustment log table: inventory_logs (product_id, old_value, new_value, reason, admin_user, timestamp)
- Consider implementing "low stock" alerts at 5-10 items

**Warning signs:**
- No inventory_logs table for audit trail
- Stock updates not wrapped in transactions
- Multiple simultaneous checkouts can oversell
- Cart items don't expire or reserve stock
- Stock values go negative in database

**Phase to address:**
Phase 3 (Shopping Cart) - Cart and inventory are tightly coupled. Inventory reservation must be part of cart implementation, not added later when overselling occurs.

---

## Technical Debt Patterns

Shortcuts that seem reasonable but create long-term problems.

| Shortcut | Immediate Benefit | Long-term Cost | When Acceptable |
|----------|-------------------|----------------|-----------------|
| Storing passwords as MD5 | Easy to implement | Easily cracked, forces password reset migration later | Never - use password_hash() from start |
| Using GET for admin actions | Simple URL bookmarking | CSRF vulnerability, accidental triggers | Never - state changes require POST |
| Hardcoding payment keys in code | Faster dev setup | Credentials in version control, can't change per environment | Never - use .env files |
| Skipping prepared statements for "trusted" input | Code looks simpler | SQL injection vulnerability persists | Never - all input is untrusted |
| Storing money as FLOAT | PHP native type | Rounding errors in calculations | Never - use INT (cents) or DECIMAL |
| No database indexes initially | Faster writes when testing | Extremely slow queries at scale | Only in MVP with <100 products, index before launch |
| File-based sessions | Default PHP config | Performance issues, shared hosting problems | Only local dev, never production |
| Inline SQL queries throughout code | Quick feature implementation | Impossible to audit, hard to change DB | Only in throwaway prototypes |
| No error logging | Less disk usage | Can't diagnose production issues | Never - logging essential |
| Same admin/user database | Simpler schema | Admin compromise exposes customer data | Never - separate admin tables |

---

## Integration Gotchas

Common mistakes when connecting to external services.

| Integration | Common Mistake | Correct Approach |
|-------------|----------------|------------------|
| Payment Gateway (Kyrgyzstan) | Not handling gateway downtime | Implement retry queue, show "Payment processing, check email" message, reconcile via webhook |
| Payment Webhooks | Processing webhook without signature verification | Verify webhook signature using gateway secret key before trusting payload |
| Payment Gateway | Testing only with test cards, not real flow | Test both test mode AND production mode with real low-amount transaction before launch |
| HTML/Tailwind Template | Assuming static HTML works as PHP template | Template may use classes/IDs that conflict with PHP variable names, test integration early |
| Google/Yandex Analytics | Adding tracking code to <head> only | Also add conversion tracking to thank-you page, test with Tag Assistant |
| Email Service | Using mail() function directly | Use PHPMailer or SMTP library for reliability, handle bounces |
| Image Optimization | Serving original uploaded images | Resize/compress images server-side with GD/Imagick before serving |

---

## Performance Traps

Patterns that work at small scale but fail as usage grows.

| Trap | Symptoms | Prevention | When It Breaks |
|------|----------|------------|----------------|
| Loading all products in memory for catalog page | Page loads slow, high memory usage | Implement pagination (20-50 products/page), use LIMIT/OFFSET in SQL | >200 products |
| N+1 query problem (loading category per product) | 100+ queries per page load | Use JOIN or eager loading: SELECT products.*, categories.name FROM products JOIN categories | >50 products on page |
| No database query caching | Repeated identical queries | Use Redis/Memcached for product catalog, cache for 5-15 minutes | >500 products |
| Synchronous payment API calls in request cycle | Checkout timeouts, slow response | Use job queue for non-critical payment tasks, async webhook processing | Heavy traffic |
| Full-text search with LIKE %keyword% | Search becomes extremely slow | Use FULLTEXT index or external search (Elasticsearch), or MySQL MATCH AGAINST | >1000 products |
| Session storage in filesystem | Slow session reads, locking issues | Use Redis for session storage: session.save_handler = redis | >100 concurrent users |
| No image CDN | Slow page loads, high bandwidth | Serve images from CDN or optimize with WebP conversion | >1GB images |
| Regenerating thumbnails on every page load | Slow product pages | Generate thumbnails once during upload, cache | Any catalog size |

---

## Security Mistakes

Domain-specific security issues beyond general web security.

| Mistake | Risk | Prevention |
|---------|------|------------|
| Exposing admin panel at /admin or /administrator | Brute force attacks, enumeration | Use obscured URL like /[random-string], implement IP whitelist, add fail2ban |
| No rate limiting on checkout | Credit card testing, inventory depletion attacks | Limit checkout attempts: 3 per IP per 10 minutes |
| Verbose error messages in production | Information disclosure (DB schema, file paths) | Set display_errors=0, log errors to file, show generic "Error occurred" to users |
| No input length limits | Buffer overflow, DOS via huge inputs | Validate max length: product name <200 chars, description <5000 chars |
| Allowing HTML in product descriptions | Stored XSS attacks | Strip all HTML or use whitelist: strip_tags($input, '<b><i><u>') |
| Price tampering via form manipulation | Customer submits altered price in POST data | Never trust price from client, always fetch from database server-side |
| Guessable order numbers | Order enumeration, competitor intelligence | Use UUID or non-sequential IDs: ORDER-[random hash] |
| No HTTPS enforcement | Session hijacking, payment interception | Force HTTPS redirect: if (!$_SERVER['HTTPS']) redirect to https:// |
| Admin and customer share session storage | Session fixation, privilege escalation | Separate session handling for admin vs frontend |

---

## UX Pitfalls

Common user experience mistakes in this domain.

| Pitfall | User Impact | Better Approach |
|---------|-------------|-----------------|
| Forcing account creation (even though guest checkout planned) | Cart abandonment, frustration | True guest checkout: only email required, no password |
| Multi-step form without progress indicator | User doesn't know how long checkout takes | Show "Step 2 of 4" progress bar |
| Not showing total price with tax upfront | Surprise at checkout, abandonment | Display tax-inclusive price on product page |
| Losing cart contents on browser close | User must re-add items, frustration | Store cart in database (tied to session), restore on return |
| No order confirmation email | User uncertainty if order succeeded | Send immediate confirmation email with order number |
| Complex product filters that don't work on mobile | Mobile users can't filter catalog effectively | Touch-friendly filter UI, consider mobile-first design |
| Product images not zoomable | Can't see product details clearly (important for electronics) | Implement image zoom on hover/tap |
| No currency display in Kyrgyzstan Som | Confusion about pricing | Show KGS symbol/code clearly: 15,000 сом or 15,000 KGS |
| Generic product descriptions | Users can't differentiate Xiaomi models | Detailed specs table: compare Redmi Note 13 vs 13 Pro differences |
| Slow admin panel product updates | Admin frustrated, avoids updating | Optimize admin queries, use AJAX for partial updates |

---

## "Looks Done But Isn't" Checklist

Things that appear complete but are missing critical pieces.

- [ ] **Guest Checkout:** Demo works but missing session expiration handling — verify cart recovery on expired session
- [ ] **Payment Integration:** Test cards work but webhook endpoint not verified — check webhook signature validation code exists
- [ ] **Product Images:** Upload works but files not validated — verify file content validation with getimagesize(), not just extension
- [ ] **Admin Login:** Password authentication works but no rate limiting — verify failed login attempts are tracked and blocked after 5 attempts
- [ ] **SEO URLs:** Products have friendly URLs but no sitemap — verify sitemap.xml auto-generated from products table
- [ ] **Price Display:** Prices show correctly but tax calculation untested — verify multi-item cart with tax matches expected total
- [ ] **Inventory Tracking:** Stock decrements on order but no oversell prevention — verify concurrent checkout tests with last item in stock
- [ ] **SSL Certificate:** HTTPS works but HTTP not redirected — verify http:// URLs redirect to https://
- [ ] **Email Notifications:** Order confirmation sends but bounces not handled — verify bounce tracking or SPF/DKIM configured
- [ ] **Error Handling:** Payment errors caught but not logged — verify error_log configured and payment failures written to log
- [ ] **Session Security:** Sessions work but httponly flag not set — verify session.cookie_httponly=1 in php.ini
- [ ] **Database Backups:** Database exists but no backup strategy — verify daily automated backups configured
- [ ] **Admin CSRF:** Admin forms work but no token validation — verify every admin POST checks CSRF token
- [ ] **Input Validation:** Forms validate on client but not server — verify server-side validation for all inputs

---

## Recovery Strategies

When pitfalls occur despite prevention, how to recover.

| Pitfall | Recovery Cost | Recovery Steps |
|---------|---------------|----------------|
| SQL Injection discovered | HIGH | 1) Take site offline immediately 2) Audit all user data for compromise 3) Force password reset 4) Review logs for data exfiltration 5) Refactor all queries to use prepared statements 6) Security audit before relaunch |
| Payment webhook failures | MEDIUM | 1) Export orders marked "pending" >24hrs 2) Check payment gateway dashboard for actual payment status 3) Manually update order status 4) Email customers confirmation 5) Implement reconciliation cron job |
| Price rounding errors | LOW | 1) Identify affected orders from database 2) Calculate correct amounts 3) Issue refunds for overcharges 4) Contact customers for undercharges 5) Fix calculation logic 6) Add unit tests |
| Missing CSRF protection | MEDIUM | 1) Add CSRF token generation to session init 2) Update all forms to include token 3) Add validation middleware 4) Review admin action logs for suspicious activity 5) Notify admin of security update |
| Inventory oversold | LOW | 1) Identify oversold orders 2) Contact customers to explain 3) Offer alternatives or refund 4) Implement atomic stock updates 5) Add cart reservation system |
| File upload vulnerability | HIGH | 1) Take admin panel offline 2) Scan uploads directory for .php files 3) Check web server logs for suspicious access 4) Remove malicious files 5) Move uploads outside web root 6) Implement proper validation 7) Change all passwords |
| Slow database queries | MEDIUM | 1) Enable slow query log 2) Identify missing indexes with EXPLAIN 3) Add indexes on frequently filtered columns 4) Optimize JOIN queries 5) Implement query caching |
| Session hijacking | HIGH | 1) Force logout all sessions (delete session files/Redis keys) 2) Regenerate session configuration 3) Implement session fingerprinting 4) Add CSRF protection 5) Review access logs 6) Notify affected customers |
| SEO-hostile URLs | MEDIUM | 1) Implement URL rewriting 2) Create 301 redirects from old URLs to new 3) Submit new sitemap to Google/Yandex 4) Monitor search console for crawl errors 5) Fix internal links |

---

## Pitfall-to-Phase Mapping

How roadmap phases should address these pitfalls.

| Pitfall | Prevention Phase | Verification |
|---------|------------------|--------------|
| SQL Injection | Phase 1 (Foundation) | Security audit: grep codebase for concatenated SQL, verify 100% prepared statements |
| Weak Session Management | Phase 1 (Foundation) | Test: check phpinfo() for secure session settings, verify Redis connection |
| Payment Integration Errors | Phase 3 (Payment Integration) | Test: simulate webhook failures, verify reconciliation process, check error logs |
| Missing CSRF Protection | Phase 4 (Admin Panel) | Security audit: verify token validation on all admin forms, test without token |
| File Upload Vulnerabilities | Phase 4 (Admin Panel) | Penetration test: attempt .php upload, verify content validation, check directory permissions |
| Poor Database Schema | Phase 2 (Product Catalog) | Review: EXPLAIN all product queries show index usage, no full table scans |
| Price Rounding Errors | Phase 2 (Product Catalog) | Unit tests: 20+ test cases for price/tax calculations, verify rounding strategy |
| SEO-Hostile URLs | Phase 2 (Product Catalog) | Audit: verify all product URLs are friendly, sitemap generated, meta tags unique |
| Outdated PHP Version | Phase 1 (Foundation) | Deploy check: verify PHP 8.1+ via command line, all required extensions loaded |
| No Inventory Sync | Phase 3 (Shopping Cart) | Concurrency test: simulate 10 users checking out last item, verify only 1 succeeds |

---

## Sources

**Security Research (2025-2026):**
- Medium: "Building Secure PHP Applications: 5 Mistakes to Avoid in 2025" - PHP security best practices
- SitePoint: "Top 7 PHP Security Blunders" - Common PHP vulnerabilities
- Akveo Blog: "9 Most Common eCommerce Security Vulnerabilities" - E-commerce specific threats
- Kinsta: "Security requirements and 9 best practices for robust e-commerce websites" - E-commerce security standards
- PHP.net Security Updates - Current PHP version vulnerabilities (0 in 2026, 15 in 2025)

**Payment Integration (2025-2026):**
- Medium (Ibrahim Fahad): "Top 10 Mistakes in E-commerce Payment Integration" - PCI DSS, testing failures
- The One Technologies: "Common Challenges in Payment Gateway Integration" - Integration error handling
- Wealth Consulting: "E-commerce Payment Processing: Security Measures for 2025" - Payment security
- CM Alliance: "Payment Fraud Prevention: Strategies for eCommerce Businesses in 2026" - Fraud prevention
- Kyrgyzstan payment landscape research - Local gateway challenges, 60% cart abandonment due to payment limits

**Performance & Architecture (2025-2026):**
- OpenCart Community Forums: Performance optimization discussions, InnoDB vs MyISAM recommendations
- BigCommerce: "Scalable E-commerce Architecture: How to Build for Growth in 2026" - Monolithic challenges
- Alokai: "Major challenges of monolithic infrastructure for ecommerce" - Architectural pitfalls
- Antropy: "How To Speed Up OpenCart" - PHP caching, query optimization

**Database Design:**
- GeeksforGeeks (July 2025): "How to Design a Relational Database for E-commerce Website"
- Kanishka Software (July 2025): "Designing an Ecommerce Database for Efficient Data Management"
- BrainSpate (Aug 2025): "eCommerce Database: What it is & How to Design one?" - Shopify/MySQL patterns
- Reintech: "MySQL for E-commerce: Database Design Considerations"

**SEO Research (2025-2026):**
- Reflect Digital: "Top 10 eCommerce SEO Mistakes That Are Harming Your Sales" - Duplicate content, thin pages
- ICS-Digital: "Common E-Commerce Technical SEO Mistakes" - Technical SEO issues
- Neil Patel: "7 Critical SEO Errors of E-commerce Websites" - SEO fundamentals
- LOGEIX: "eCommerce SEO: 22 Mistakes from Auditing 1,200 Stores" - 29% stores had zero product descriptions

**Inventory & Stock Management (2025-2026):**
- Base Blog: "Top 5 Most Common E-commerce Inventory Management Mistakes and How to Avoid Them"
- Unleashed Software: "8 Common eCommerce Inventory Management Mistakes to Avoid" - Manual tracking issues
- SyncX: "Elevate Your eCommerce Inventory Management with Stock Sync" - Multi-channel sync
- Sku.io: "The Hidden Cost of Disconnected Inventory Management Systems" - $818B inventory distortion costs

**File Upload Security:**
- GitHub vulnerability reports: PHP/MySQLi e-commerce arbitrary file upload vulnerabilities
- Hackviser: "File Upload Vulnerabilities Attack Guide" - Bypass techniques
- PortSwigger: "File uploads | Web Security Academy" - Upload security best practices
- Acunetix: "File Upload Bypass: Upload Forms Threat Explained"

**Price Calculation Issues:**
- Magento GitHub Issues: Rounding problems with tax calculations
- WooCommerce GitHub: Incorrect rounding with >2 decimal places
- OpenCart Forums: Incorrect tax calculations (rounding)
- PrestaShop GitHub: Tax/totals rounding issues on invoices
- Drupal Commerce: VAT rounding causing higher than expected orders

**Session Management:**
- Corbado: "State of E-Commerce Authentication 2026" - Passwordless trends, passkeys
- Salesforce: "Ecommerce Checkout: 10 Best Practices for 2026" - Guest checkout importance
- MightyBigData: "How Magento 2 Handles Session Management" - Redis session storage
- commercetools: "Use Anonymous Sessions for Guest Checkout" - Anonymous session patterns

---

*Pitfalls research for: PHP E-commerce Platform (Xiaomi Electronics, Kyrgyzstan market)*
*Researched: 2026-01-22*
*Research confidence: MEDIUM - Based on extensive web search of 2025-2026 sources, verified across multiple PHP e-commerce platforms (OpenCart, Magento, WooCommerce, PrestaShop). Some findings marked LOW confidence where specific to theoretical scenarios not yet verified in production context.*
