-- Product featured images migrated from local catalog folder "COD. 05-020 p2".
-- Sets products.featured_image; the actual image files under
-- public/uploads/products/ must be uploaded to production separately
-- (this only updates the database, not the filesystem).
--
-- This folder also contained one stray file for a different SKU series,
-- 05-010-191.webp, which fills a gap left by migration 0009
-- (05-010_product_images.sql) where that SKU had no photo yet.
--
-- All 18 SKUs below were confirmed to exist in the products table before
-- writing this migration.

UPDATE products SET featured_image = '05-010-191.webp' WHERE sku = '05-010-191';

UPDATE products SET featured_image = '05-020-007.webp' WHERE sku = '05-020-007';
UPDATE products SET featured_image = '05-020-008.webp' WHERE sku = '05-020-008';
UPDATE products SET featured_image = '05-020-015.webp' WHERE sku = '05-020-015';
UPDATE products SET featured_image = '05-020-016.webp' WHERE sku = '05-020-016';
UPDATE products SET featured_image = '05-020-017.webp' WHERE sku = '05-020-017';
UPDATE products SET featured_image = '05-020-019.webp' WHERE sku = '05-020-019';
UPDATE products SET featured_image = '05-020-097.webp' WHERE sku = '05-020-097';
UPDATE products SET featured_image = '05-020-100.webp' WHERE sku = '05-020-100';
UPDATE products SET featured_image = '05-020-202.webp' WHERE sku = '05-020-202';
UPDATE products SET featured_image = '05-020-205.webp' WHERE sku = '05-020-205';
UPDATE products SET featured_image = '05-020-352.webp' WHERE sku = '05-020-352';
UPDATE products SET featured_image = '05-020-361.webp' WHERE sku = '05-020-361';
UPDATE products SET featured_image = '05-020-459.webp' WHERE sku = '05-020-459';
UPDATE products SET featured_image = '05-020-472.webp' WHERE sku = '05-020-472';
UPDATE products SET featured_image = '05-020-476.webp' WHERE sku = '05-020-476';
UPDATE products SET featured_image = '05-020-484.webp' WHERE sku = '05-020-484';
UPDATE products SET featured_image = '05-020-508.webp' WHERE sku = '05-020-508';
