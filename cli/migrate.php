<?php

/**
 * Runs pending database migrations and records them in the `migrations`
 * table so re-running this script is a no-op once everything is applied.
 *
 * The four bulk SQL dumps this repo shipped with (ecommerce.sql,
 * database_improvements.sql, database/erp_integration.sql,
 * database/update_for_tailwind.sql) are treated as migrations 0001-0004,
 * executed in that order. They mix idempotent statements (CREATE TABLE IF
 * NOT EXISTS, ADD COLUMN IF NOT EXISTS) with a few that are not (plain ADD
 * COLUMN, plain INSERT INTO on unique-keyed lookup tables) - a leftover of
 * being hand-written dumps rather than authored as migrations. On a
 * database where they were already applied by hand (as this project's own
 * dev DB was, before this script existed), MySQL will report "duplicate
 * column" / "duplicate entry" for those specific statements; this runner
 * treats exactly those error codes as "already applied" and continues,
 * rather than aborting. Any other error is a real failure and stops the run.
 *
 * New migrations go in database/migrations/*.sql, executed in filename
 * order after the four legacy ones - name them 0005_..., 0006_..., etc.
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/sql_helper.php';

use Dotenv\Dotenv;

$basePath = dirname(__DIR__);

if (file_exists($basePath . '/.env')) {
    Dotenv::createImmutable($basePath)->load();
}

// MySQL error codes that mean "this statement's effect already exists" -
// safe to skip when re-applying an already-migrated database.
const ALREADY_APPLIED_ERROR_CODES = [1050, 1060, 1061, 1062, 1068, 1826];

function connect(): PDO
{
    $dsn = sprintf(
        'mysql:host=%s;port=%s;dbname=%s;charset=%s',
        $_ENV['DB_HOST'] ?? 'localhost',
        $_ENV['DB_PORT'] ?? 3306,
        $_ENV['DB_DATABASE'] ?? 'ecommerce',
        $_ENV['DB_CHARSET'] ?? 'utf8mb4'
    );

    return new PDO($dsn, $_ENV['DB_USERNAME'] ?? 'root', $_ENV['DB_PASSWORD'] ?? '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
}

function runFile(PDO $pdo, string $label, string $path): void
{
    echo "Applying {$label}...\n";
    $sql = file_get_contents($path);
    $skipped = 0;

    foreach (splitSqlStatements($sql) as $statement) {
        try {
            $pdo->exec($statement);
        } catch (PDOException $e) {
            $code = (int) $e->errorInfo[1];
            if (in_array($code, ALREADY_APPLIED_ERROR_CODES, true)) {
                $skipped++;
                continue;
            }
            fwrite(STDERR, "  FAILED in {$label}: {$e->getMessage()}\n");
            fwrite(STDERR, "  Statement: " . substr($statement, 0, 200) . "\n");
            exit(1);
        }
    }

    echo $skipped > 0
        ? "  OK ({$skipped} statement(s) already applied, skipped)\n"
        : "  OK\n";
}

$pdo = connect();

// ecommerce.sql (line ~368) already ships a `migrations` table - id,
// migration, executed_at, unique key on `migration`. Match that exact
// shape here rather than inventing a new one, so this works against both
// a fresh install (table doesn't exist yet, this creates it) and this
// project's existing dev DB (table already exists from the dump, this is
// a no-op thanks to IF NOT EXISTS).
$pdo->exec(
    'CREATE TABLE IF NOT EXISTS migrations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        migration VARCHAR(255) NOT NULL,
        executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_migration (migration)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
);

$applied = $pdo->query('SELECT migration FROM migrations')->fetchAll(PDO::FETCH_COLUMN);
$record = $pdo->prepare('INSERT INTO migrations (migration) VALUES (?)');

$legacy = [
    '0001_ecommerce_schema' => $basePath . '/ecommerce.sql',
    '0002_database_improvements' => $basePath . '/database_improvements.sql',
    '0003_erp_integration' => $basePath . '/database/erp_integration.sql',
    '0004_update_for_tailwind' => $basePath . '/database/update_for_tailwind.sql',
];

$pending = [];
foreach ($legacy as $name => $path) {
    if (!in_array($name, $applied, true)) {
        $pending[$name] = $path;
    }
}

$migrationsDir = $basePath . '/database/migrations';
if (is_dir($migrationsDir)) {
    $files = glob($migrationsDir . '/*.sql');
    sort($files);
    foreach ($files as $path) {
        $name = basename($path, '.sql');
        if (!in_array($name, $applied, true)) {
            $pending[$name] = $path;
        }
    }
}

if (empty($pending)) {
    echo "Nothing to migrate.\n";
    exit(0);
}

foreach ($pending as $name => $path) {
    runFile($pdo, $name, $path);
    $record->execute([$name]);
}

echo "Migrated " . count($pending) . " migration(s).\n";
