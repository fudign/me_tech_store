<?php

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', '1');

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

    // Configure environment for Vercel
    // Use /tmp for writable storage on Vercel
    $tmpStorage = '/tmp/storage';
    if (!is_dir($tmpStorage)) {
        @mkdir($tmpStorage, 0755, true);
        @mkdir($tmpStorage . '/views', 0755, true);
        @mkdir($tmpStorage . '/framework', 0755, true);
        @mkdir($tmpStorage . '/framework/cache', 0755, true);
        @mkdir($tmpStorage . '/framework/sessions', 0755, true);
        @mkdir($tmpStorage . '/framework/views', 0755, true);
        @mkdir($tmpStorage . '/logs', 0755, true);
    }

    putenv('VIEW_COMPILED_PATH=' . $tmpStorage . '/framework/views');
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
    putenv('DEBUGBAR_ENABLED=false');

    // Check if we're in diagnostic mode
    if (isset($_GET['_vercel_debug']) && $_GET['_vercel_debug'] === 'true') {
        header('Content-Type: text/plain');
        echo "=== Vercel Laravel Diagnostics ===\n\n";
        echo "PHP Version: " . phpversion() . "\n";
        echo "Base Path: " . $basePath . "\n";
        echo "Public Path: " . $basePath . '/public' . "\n";
        echo "Tmp Storage: " . $tmpStorage . "\n\n";

        echo "Environment Variables:\n";
        echo "APP_ENV: " . getenv('APP_ENV') . "\n";
        echo "APP_KEY: " . (getenv('APP_KEY') ? 'Set (length: ' . strlen(getenv('APP_KEY')) . ')' : 'NOT SET') . "\n";
        echo "APP_DEBUG: " . getenv('APP_DEBUG') . "\n";
        echo "DB_CONNECTION: " . getenv('DB_CONNECTION') . "\n";
        echo "DB_HOST: " . getenv('DB_HOST') . "\n\n";

        echo "File Checks:\n";
        echo "bootstrap/app.php exists: " . (file_exists($basePath . '/bootstrap/app.php') ? 'YES' : 'NO') . "\n";
        echo "public/index.php exists: " . (file_exists($basePath . '/public/index.php') ? 'YES' : 'NO') . "\n";
        echo "vendor/autoload.php exists: " . (file_exists($basePath . '/vendor/autoload.php') ? 'YES' : 'NO') . "\n";
        echo ".env file exists: " . (file_exists($basePath . '/.env') ? 'YES' : 'NO') . "\n\n";

        echo "Directory Permissions:\n";
        echo "/tmp writable: " . (is_writable('/tmp') ? 'YES' : 'NO') . "\n";
        echo "storage writable: " . (is_writable($basePath . '/storage') ? 'YES' : 'NO') . "\n";
        echo "bootstrap/cache writable: " . (is_writable($basePath . '/bootstrap/cache') ? 'YES' : 'NO') . "\n";

        exit(0);
    }

    // Forward Vercel requests to public/index.php
    require __DIR__ . '/../public/index.php';
} catch (\Throwable $e) {
    // Don't try to set response code if headers already sent
    if (!headers_sent()) {
        http_response_code(500);
        header('Content-Type: text/html; charset=utf-8');
    }

    // Show detailed error in production (will be removed after debugging)
    echo '<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Server Error</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; padding: 40px; background: #f5f5f5; }
        .error-container { background: white; border-radius: 8px; padding: 30px; max-width: 800px; margin: 0 auto; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #e53e3e; margin-top: 0; }
        .error-details { background: #f7fafc; border-left: 4px solid #e53e3e; padding: 15px; margin: 20px 0; font-family: monospace; font-size: 14px; }
        .trace { background: #2d3748; color: #e2e8f0; padding: 15px; border-radius: 4px; overflow-x: auto; font-size: 12px; }
        .help { background: #ebf8ff; border-left: 4px solid #4299e1; padding: 15px; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="error-container">
        <h1>⚠️ Server Error</h1>
        <p>An error occurred while processing your request.</p>

        <div class="error-details">
            <strong>Error:</strong> ' . htmlspecialchars($e->getMessage()) . '<br>
            <strong>File:</strong> ' . htmlspecialchars($e->getFile()) . '<br>
            <strong>Line:</strong> ' . $e->getLine() . '
        </div>

        <details>
            <summary style="cursor: pointer; font-weight: bold; margin: 20px 0;">Stack Trace</summary>
            <div class="trace">' . nl2br(htmlspecialchars($e->getTraceAsString())) . '</div>
        </details>

        <div class="help">
            <strong>💡 Debugging Tips:</strong><br>
            1. Check that APP_KEY is set in Vercel environment variables<br>
            2. Verify database credentials (DB_HOST, DB_DATABASE, DB_USERNAME, DB_PASSWORD)<br>
            3. Visit <code>?_vercel_debug=true</code> to see diagnostic information<br>
            4. Check Vercel logs for more details
        </div>
    </div>
</body>
</html>';

    error_log("=== VERCEL ERROR ===");
    error_log("Error: " . $e->getMessage());
    error_log("File: " . $e->getFile() . ":" . $e->getLine());
    error_log("Trace: " . $e->getTraceAsString());

    exit(1);
}
