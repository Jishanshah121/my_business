<?php

declare(strict_types=1);

/**
 * PSR-4 autoloader.
 *
 * Hand-rolled so the console and migrations run with zero Composer
 * dependencies. When `composer install` has been run, its autoloader is
 * preferred and this one only fills gaps.
 */

$root = dirname(__DIR__);

$composer = $root . '/vendor/autoload.php';
if (is_file($composer)) {
    require $composer;
}

spl_autoload_register(static function (string $class) use ($root): void {
    $prefixes = [
        'App\\'      => $root . '/app/',
        'Database\\' => $root . '/database/',
        'Tests\\'    => $root . '/tests/',
    ];

    foreach ($prefixes as $prefix => $baseDir) {
        if (!str_starts_with($class, $prefix)) {
            continue;
        }

        $relative = substr($class, strlen($prefix));
        $file = $baseDir . str_replace('\\', '/', $relative) . '.php';

        if (is_file($file)) {
            require $file;
            return;
        }
    }
});
