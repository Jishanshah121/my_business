<?php

declare(strict_types=1);

/**
 * PHPUnit bootstrap.
 *
 * Loads .env.testing rather than .env so a test run can never touch the
 * development database, and migrates the test schema once per run.
 */

$root = dirname(__DIR__);

require $root . '/bootstrap/autoload.php';

use App\Support\Config;
use App\Support\Database;
use App\Support\Env;
use Database\Migrator;

Env::load($root . '/.env.testing');
Config::load($root . '/config');

date_default_timezone_set((string) Config::get('app.timezone', 'Asia/Kolkata'));
ini_set('display_errors', '1');
error_reporting(E_ALL);

if (Config::get('database.connections.mysql.database') !== 'supplykaro_test') {
    fwrite(STDERR, "Refusing to run: tests must target the supplykaro_test database.\n");
    exit(1);
}

// Fresh schema for every run, so tests never inherit yesterday's state.
$migrator = new Migrator(Database::connection());
$migrator->fresh();
$migrator->ensureRepository();
$migrator->run();

// Reference data only — the demo catalog is not needed to test auth, and
// leaving it out keeps the suite fast.
// Fully qualified: `use App\Support\Database` above aliases `Database`, so an
// unqualified `Database\Seeders\X` would resolve to App\Support\Database\Seeders\X.
foreach ([
    \Database\Seeders\SettingsSeeder::class,
    \Database\Seeders\RbacSeeder::class,
    \Database\Seeders\CustomerGroupSeeder::class,
    \Database\Seeders\BusinessTypeSeeder::class,
    \Database\Seeders\TaxRateSeeder::class,
    \Database\Seeders\WarehouseSeeder::class,
] as $seeder) {
    (new $seeder(Database::connection()))->run();
}
