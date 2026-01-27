# Xiaomi Store - Production Deployment Checklist

## Pre-Deployment

- [ ] Backup existing database (if updating existing site)
- [ ] Test all features in staging environment
- [ ] Run `composer install --no-dev --optimize-autoloader`
- [ ] Run `npm ci && npm run build` (Vite production build)

## Server Setup

- [ ] PHP 8.2+ installed with extensions: mbstring, xml, curl, gd, mysql
- [ ] MySQL 8.0+ or MariaDB 10.3+
- [ ] Composer installed globally
- [ ] Redis installed (optional but recommended for cache/session)
- [ ] Web server configured (nginx or Apache with mod_rewrite)

## Environment Configuration

- [ ] Copy `.env.production` to `.env`
- [ ] Update `APP_URL` with production domain (https://yourdomain.com)
- [ ] Run `php artisan key:generate` (generates APP_KEY)
- [ ] Update database credentials (DB_HOST, DB_DATABASE, DB_USERNAME, DB_PASSWORD)
- [ ] Set `CACHE_DRIVER=redis` if Redis available, otherwise `file`
- [ ] Set `SESSION_DRIVER=redis` if Redis available, otherwise `file`
- [ ] Verify `APP_DEBUG=false` and `APP_ENV=production`

## Database Setup

- [ ] Create production database: `CREATE DATABASE xiaomi_store_production CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;`
- [ ] Run migrations: `php artisan migrate --force`
- [ ] Seed database: `php artisan db:seed` (creates admin user and sample products)
- [ ] Verify admin login: email admin@example.com, password admin123 (CHANGE IMMEDIATELY)

## File Permissions

- [ ] `chmod -R 755 storage bootstrap/cache`
- [ ] `chown -R www-data:www-data storage bootstrap/cache` (Linux/nginx)
- [ ] Verify `php artisan storage:link` creates public/storage symlink

## Laravel Optimization

- [ ] `php artisan config:cache` (cache configuration)
- [ ] `php artisan route:cache` (cache routes)
- [ ] `php artisan view:cache` (precompile Blade templates)
- [ ] `php artisan event:cache` (cache events)
- [ ] Combined: `php artisan optimize` (runs all above)

## Initial Data

- [ ] Generate sitemap: `php artisan sitemap:generate`
- [ ] Verify sitemap accessible: https://yourdomain.com/sitemap.xml
- [ ] Verify robots.txt: https://yourdomain.com/robots.txt
- [ ] Test image optimization: Visit product page, check WebP loading in Network tab

## SSL/HTTPS

- [ ] Install Let's Encrypt certificate: `certbot --nginx -d yourdomain.com`
- [ ] Enable auto-renewal: `certbot renew --dry-run`
- [ ] Verify redirect HTTP -> HTTPS working
- [ ] Test SSL: https://www.ssllabs.com/ssltest/

## Scheduler (Cron)

- [ ] Add to crontab: `* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1`
- [ ] Verify scheduler working: `php artisan schedule:list` (should show sitemap:generate daily)

## Verification

- [ ] Homepage loads without errors
- [ ] Browse products and categories
- [ ] Add product to cart, complete checkout
- [ ] Login to admin panel (yourdomain.com/admin)
- [ ] Create test product with images in admin
- [ ] Verify images load as WebP in browser (inspect Network tab)
- [ ] Share product link, check OpenGraph preview
- [ ] Check PageSpeed Insights score (aim for 70+ mobile, 90+ desktop)

## Error Handling Test (ERR-02)

- [ ] Stop MySQL service temporarily: `sudo systemctl stop mysql`
- [ ] Visit site: Should show "Сервис временно недоступен" page (not stack trace)
- [ ] Check logs: `tail storage/logs/laravel.log` (should contain PDOException with URL/IP)
- [ ] Restart MySQL: `sudo systemctl start mysql`
- [ ] Site should work normally again

## Post-Deployment

- [ ] Change default admin password immediately
- [ ] Monitor error logs: `tail -f storage/logs/laravel.log`
- [ ] Set up monitoring (optional): Laravel Pulse, Sentry, or similar
- [ ] Submit sitemap to Google Search Console and Yandex Webmaster

## Troubleshooting

**500 Error:** Check `storage/logs/laravel.log`, verify file permissions

**Images not loading:** Run `php artisan storage:link`, check storage/ permissions

**Cache not working:** Verify CACHE_DRIVER setting, test with `php artisan cache:clear`

**Sitemap empty:** Run `php artisan sitemap:generate`, check products have is_active=1

**Database error page not showing:** Verify APP_DEBUG=false, check bootstrap/app.php exception handlers

---
Last updated: 2026-01-24
