<?php

/**
 * Loads database/seeds/test_data.sql. That file TRUNCATEs its own tables
 * before inserting, so it's safe to run more than once.
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/sql_helper.php';

use Dotenv\Dotenv;

$basePath = dirname(__DIR__);

if (file_exists($basePath . '/.env')) {
    Dotenv::createImmutable($basePath)->load();
}

$dsn = sprintf(
    'mysql:host=%s;port=%s;dbname=%s;charset=%s',
    $_ENV['DB_HOST'] ?? 'localhost',
    $_ENV['DB_PORT'] ?? 3306,
    $_ENV['DB_DATABASE'] ?? 'ecommerce',
    $_ENV['DB_CHARSET'] ?? 'utf8mb4'
);

$pdo = new PDO($dsn, $_ENV['DB_USERNAME'] ?? 'root', $_ENV['DB_PASSWORD'] ?? '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);

$seedFile = $basePath . '/database/seeds/test_data.sql';

if (!file_exists($seedFile)) {
    fwrite(STDERR, "Seed file not found: {$seedFile}\n");
    exit(1);
}

echo "Seeding from database/seeds/test_data.sql...\n";

foreach (splitSqlStatements(file_get_contents($seedFile)) as $statement) {
    $pdo->exec($statement);
}

echo "Done.\n";
