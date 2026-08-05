-- Product featured images migrated from local catalog folder "COD. 05-030".
-- Sets products.featured_image; the actual image files under
-- public/uploads/products/ must be uploaded to production separately
-- (this only updates the database, not the filesystem).
-- All 4 SKUs below were confirmed to exist in the products table before
-- writing this migration.

UPDATE products SET featured_image = '05-030-144.webp' WHERE sku = '05-030-144';
UPDATE products SET featured_image = '05-030-152.webp' WHERE sku = '05-030-152';
UPDATE products SET featured_image = '05-030-154.webp' WHERE sku = '05-030-154';
UPDATE products SET featured_image = '05-030-156.webp' WHERE sku = '05-030-156';
