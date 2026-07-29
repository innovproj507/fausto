<?php

/**
 * Sets products.status = 'active' for products whose featured_image points
 * at a file that actually exists under public/uploads/products/, and
 * 'inactive' for every other product (missing featured_image, or one that
 * points at a file that was never uploaded - see database/migrations/0005_product_images.sql).
 *
 * Dry-run by default (prints what would change). Pass --apply to write it.
 */

require_once __DIR__ . '/../vendor/autoload.php';

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

$uploadDir = $basePath . '/public/uploads/products/';
$apply = in_array('--apply', $argv, true);

$products = $pdo->query('SELECT id, sku, status, featured_image FROM products')->fetchAll(PDO::FETCH_ASSOC);

$toActivate = [];
$toDeactivate = [];

foreach ($products as $product) {
    $hasImage = !empty($product['featured_image'])
        && is_file($uploadDir . $product['featured_image']);

    $target = $hasImage ? 'active' : 'inactive';

    if ($product['status'] === $target) {
        continue;
    }

    if ($target === 'active') {
        $toActivate[] = $product;
    } else {
        $toDeactivate[] = $product;
    }
}

printf(
    "%d product(s) to activate (image found), %d to deactivate (no image on disk).\n",
    count($toActivate),
    count($toDeactivate)
);

if (!$apply) {
    echo "Dry run - no changes written. Re-run with --apply to update the database.\n";
    foreach ($toActivate as $p) {
        echo "  [-> active]   #{$p['id']} {$p['sku']} (was {$p['status']}, image: {$p['featured_image']})\n";
    }
    foreach ($toDeactivate as $p) {
        $reason = $p['featured_image'] ? "file missing: {$p['featured_image']}" : 'no featured_image';
        echo "  [-> inactive] #{$p['id']} {$p['sku']} (was {$p['status']}, {$reason})\n";
    }
    exit(0);
}

$update = $pdo->prepare('UPDATE products SET status = ? WHERE id = ?');

$pdo->beginTransaction();
foreach ($toActivate as $p) {
    $update->execute(['active', $p['id']]);
}
foreach ($toDeactivate as $p) {
    $update->execute(['inactive', $p['id']]);
}
$pdo->commit();

echo "Done.\n";
