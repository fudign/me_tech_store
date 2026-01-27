<?php
/**
 * Remove dev-only service providers from cached package manifest
 * This ensures production deployments don't try to load dev dependencies
 */

$packagesPath = __DIR__ . '/../bootstrap/cache/packages.php';

if (!file_exists($packagesPath)) {
    echo "packages.php not found, skipping cleanup\n";
    exit(0);
}

$packages = require $packagesPath;

// List of dev-only packages to remove
$devPackages = [
    'barryvdh/laravel-debugbar',
    'laravel/pail',
    'laravel/sail',
    'nunomaduro/collision',
];

$removed = 0;
foreach ($devPackages as $package) {
    if (isset($packages[$package])) {
        unset($packages[$package]);
        echo "Removed $package from package manifest\n";
        $removed++;
    }
}

if ($removed > 0) {
    // Write back the cleaned packages with proper formatting
    $export = var_export($packages, true);
    file_put_contents(
        $packagesPath,
        '<?php' . PHP_EOL . PHP_EOL . 'return ' . $export . ';' . PHP_EOL
    );
    echo "Package manifest cleaned successfully ($removed packages removed)\n";
} else {
    echo "No dev packages found in manifest\n";
}
