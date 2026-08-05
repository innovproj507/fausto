-- Product featured images migrated from local catalog folder "COD. 04-015".
-- Sets products.featured_image; the actual image files under
-- public/uploads/products/ must be uploaded to production separately
-- (this only updates the database, not the filesystem).
--
-- This folder also contained one stray file for a different SKU series,
-- 05-010-205.webp, which fills another gap left by migration 0009
-- (05-010_product_images.sql) where that SKU had no photo yet.
--
-- All 5 SKUs below were confirmed to exist in the products table before
-- writing this migration.

UPDATE products SET featured_image = '04-015-002.webp' WHERE sku = '04-015-002';
UPDATE products SET featured_image = '04-015-005.webp' WHERE sku = '04-015-005';
UPDATE products SET featured_image = '04-015-006.webp' WHERE sku = '04-015-006';
UPDATE products SET featured_image = '04-015-007.webp' WHERE sku = '04-015-007';

UPDATE products SET featured_image = '05-010-205.webp' WHERE sku = '05-010-205';
