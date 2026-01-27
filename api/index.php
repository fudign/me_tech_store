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

    // PostgreSQL connection for Vercel (Supabase)
    // Database credentials are set via Vercel environment variables

    // Forward Vercel requests to public/index.php
    require __DIR__ . '/../public/index.php';
} catch (\Throwable $e) {
    http_response_code(500);
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "Trace: " . $e->getTraceAsString();
    exit(1);
}
