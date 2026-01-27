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
                            stock INTEGER DEFAULT 0,
                            is_active INTEGER DEFAULT 1,
                            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
                        );

                        CREATE TABLE IF NOT EXISTS settings (
                            id INTEGER PRIMARY KEY AUTOINCREMENT,
                            key TEXT NOT NULL UNIQUE,
                            value TEXT,
                            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
                        );

                        -- Insert demo data
                        INSERT OR IGNORE INTO settings (key, value) VALUES ('site_name', 'MeTech Store');
                        INSERT OR IGNORE INTO settings (key, value) VALUES ('site_description', 'Интернет-магазин техники');
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
