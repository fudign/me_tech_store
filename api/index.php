<?php

try {
    // Create required directories for Laravel on Vercel
    $basePath = __DIR__ . '/..';
    $directories = [
        $basePath . '/storage/framework/cache/data',
        $basePath . '/storage/framework/sessions',
        $basePath . '/storage/framework/views',
        $basePath . '/storage/logs',
        $basePath . '/bootstrap/cache',
        $basePath . '/storage/database',
    ];

    foreach ($directories as $directory) {
        if (!is_dir($directory)) {
            @mkdir($directory, 0755, true);
        }
    }

    // Ensure writable permissions
    @chmod($basePath . '/storage', 0755);
    @chmod($basePath . '/bootstrap/cache', 0755);

    // Note: We don't clear cache files on Vercel as they are needed for bootstrap
    // and the filesystem is read-only. Instead, we ensure packages.php is clean
    // via the remove-dev-providers.php script that runs during build.

    // Configure environment for Vercel
    // Use /tmp for writable storage on Vercel
    $tmpStorage = '/tmp/storage';
    if (!is_dir($tmpStorage)) {
        @mkdir($tmpStorage, 0755, true);
        @mkdir($tmpStorage . '/views', 0755, true);
    }

    putenv('VIEW_COMPILED_PATH=' . $tmpStorage . '/views');
    putenv('SESSION_DRIVER=array');
    putenv('CACHE_STORE=array');

    // Disable Debugbar if not installed (production)
    if (getenv('VERCEL') === 'true') {
        putenv('DEBUGBAR_ENABLED=false');
    }

    // Use SQLite for demo mode on Vercel
    if (getenv('VERCEL_DEMO_MODE') === 'true') {
        $sqliteDb = $tmpStorage . '/database.sqlite';

        // Copy pre-built database if it doesn't exist
        if (!file_exists($sqliteDb)) {
            $sourceSqlite = $basePath . '/storage/database/database.sqlite';

            // If pre-built database exists, copy it
            if (file_exists($sourceSqlite)) {
                @copy($sourceSqlite, $sqliteDb);
                @chmod($sqliteDb, 0666);
            } else {
                // Create empty database and run basic schema
                @touch($sqliteDb);
                @chmod($sqliteDb, 0666);

                // Create minimal schema for demo
                try {
                    $pdo = new PDO('sqlite:' . $sqliteDb);
                    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                    // Create basic tables for demo
                    $pdo->exec("
                        CREATE TABLE IF NOT EXISTS categories (
                            id INTEGER PRIMARY KEY AUTOINCREMENT,
                            name TEXT NOT NULL,
                            slug TEXT NOT NULL UNIQUE,
                            description TEXT,
                            parent_id INTEGER,
                            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
                        );

                        CREATE TABLE IF NOT EXISTS products (
                            id INTEGER PRIMARY KEY AUTOINCREMENT,
                            name TEXT NOT NULL,
                            slug TEXT NOT NULL UNIQUE,
                            description TEXT,
                            price REAL NOT NULL,
                            image TEXT,
                            images TEXT,
                            stock INTEGER DEFAULT 0,
                            is_active INTEGER DEFAULT 1,
                            availability_status TEXT DEFAULT 'in_stock',
                            sku TEXT,
                            brand TEXT,
                            view_count INTEGER DEFAULT 0,
                            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
                        );

                        CREATE TABLE IF NOT EXISTS category_product (
                            category_id INTEGER NOT NULL,
                            product_id INTEGER NOT NULL,
                            PRIMARY KEY (category_id, product_id)
                        );

                        CREATE TABLE IF NOT EXISTS settings (
                            id INTEGER PRIMARY KEY AUTOINCREMENT,
                            key TEXT NOT NULL UNIQUE,
                            value TEXT,
                            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
                        );

                        CREATE TABLE IF NOT EXISTS users (
                            id INTEGER PRIMARY KEY AUTOINCREMENT,
                            name TEXT NOT NULL,
                            email TEXT NOT NULL UNIQUE,
                            password TEXT NOT NULL,
                            is_admin INTEGER DEFAULT 0,
                            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
                        );

                        -- Insert demo settings
                        INSERT OR IGNORE INTO settings (key, value) VALUES ('site_name', 'MeTech Store');
                        INSERT OR IGNORE INTO settings (key, value) VALUES ('site_description', 'Интернет-магазин техники');
                        INSERT OR IGNORE INTO settings (key, value) VALUES ('site_keywords', 'техника, электроника, магазин');
                        INSERT OR IGNORE INTO settings (key, value) VALUES ('contact_phone', '+996 XXX XXX XXX');
                        INSERT OR IGNORE INTO settings (key, value) VALUES ('contact_email', 'info@metech.kg');
                        INSERT OR IGNORE INTO settings (key, value) VALUES ('contact_address', 'Бишкек, Кыргызстан');

                        -- Insert demo category
                        INSERT OR IGNORE INTO categories (id, name, slug, description)
                        VALUES (1, 'Электроника', 'elektronika', 'Электроника и гаджеты');

                        -- Insert demo products
                        INSERT OR IGNORE INTO products (id, name, slug, description, price, stock, is_active, view_count, brand)
                        VALUES
                        (1, 'iPhone 15 Pro', 'iphone-15-pro', 'Новейший iPhone 15 Pro с чипом A17', 85000, 10, 1, 0, 'Apple'),
                        (2, 'Samsung Galaxy S24', 'samsung-galaxy-s24', 'Флагманский смартфон Samsung', 75000, 8, 1, 0, 'Samsung'),
                        (3, 'MacBook Pro 14', 'macbook-pro-14', 'Ноутбук Apple MacBook Pro 14 дюймов', 150000, 5, 1, 0, 'Apple');

                        -- Link products to categories
                        INSERT OR IGNORE INTO category_product (category_id, product_id)
                        VALUES (1, 1), (1, 2), (1, 3);
                    ");
                } catch (Exception $e) {
                    error_log('Failed to initialize SQLite: ' . $e->getMessage());
                }
            }
        }

        putenv('DB_CONNECTION=sqlite');
        putenv('DB_DATABASE=' . $sqliteDb);
    }

    // Forward Vercel requests to public/index.php
    require __DIR__ . '/../public/index.php';
} catch (\Throwable $e) {
    http_response_code(500);
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "Trace: " . $e->getTraceAsString();
    exit(1);
}
