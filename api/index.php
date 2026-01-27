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
    putenv('SESSION_DRIVER=cookie');
    putenv('CACHE_STORE=array');

    // Configure logging for serverless (stderr instead of files)
    putenv('LOG_CHANNEL=stderr');

    // Set APP_SERVICES_CACHE and other cache paths to /tmp
    putenv('APP_SERVICES_CACHE=' . $tmpStorage . '/services.php');
    putenv('APP_PACKAGES_CACHE=' . $tmpStorage . '/packages.php');
    putenv('APP_CONFIG_CACHE=' . $tmpStorage . '/config.php');
    putenv('APP_ROUTES_CACHE=' . $tmpStorage . '/routes.php');
    putenv('APP_EVENTS_CACHE=' . $tmpStorage . '/events.php');

    // Disable Debugbar if not installed (production)
    if (getenv('VERCEL') === 'true') {
        putenv('DEBUGBAR_ENABLED=false');
    }

    // PostgreSQL connection for Vercel (Supabase)
    // Database credentials are set via Vercel environment variables

    // Forward Vercel requests to public/index.php
    require __DIR__ . '/../public/index.php';
} catch (\Throwable $e) {
    // Don't try to set response code if headers already sent
    if (!headers_sent()) {
        http_response_code(500);
    }
    error_log("Error: " . $e->getMessage());
    error_log("File: " . $e->getFile() . ":" . $e->getLine());
    echo "Internal Server Error";
    exit(1);
}
