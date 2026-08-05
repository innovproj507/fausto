-- Product featured images migrated from local catalog folder "COD. 01-150".
-- Sets products.featured_image; the actual image files under
-- public/uploads/products/ must be uploaded to production separately
-- (this only updates the database, not the filesystem).
--
-- The source folder had a single shared photo named
-- "01-150-341,342,343,344,345 Y 346.webp" covering 6 SKUs (341-346); it was
-- split into 6 identical per-SKU copies (01-150-341.webp .. 01-150-346.webp)
-- before writing the UPDATEs below.
--
-- All 33 SKUs below were confirmed to exist in the products table before
-- writing this migration.

UPDATE products SET featured_image = '01-150-042.webp' WHERE sku = '01-150-042';
UPDATE products SET featured_image = '01-150-043.webp' WHERE sku = '01-150-043';
UPDATE products SET featured_image = '01-150-044.webp' WHERE sku = '01-150-044';
UPDATE products SET featured_image = '01-150-045.webp' WHERE sku = '01-150-045';
UPDATE products SET featured_image = '01-150-046.webp' WHERE sku = '01-150-046';
UPDATE products SET featured_image = '01-150-048.webp' WHERE sku = '01-150-048';
UPDATE products SET featured_image = '01-150-052.webp' WHERE sku = '01-150-052';
UPDATE products SET featured_image = '01-150-053.webp' WHERE sku = '01-150-053';
UPDATE products SET featured_image = '01-150-054.webp' WHERE sku = '01-150-054';
UPDATE products SET featured_image = '01-150-055.webp' WHERE sku = '01-150-055';
UPDATE products SET featured_image = '01-150-056.webp' WHERE sku = '01-150-056';
UPDATE products SET featured_image = '01-150-058.webp' WHERE sku = '01-150-058';
UPDATE products SET featured_image = '01-150-063.webp' WHERE sku = '01-150-063';
UPDATE products SET featured_image = '01-150-092.webp' WHERE sku = '01-150-092';
UPDATE products SET featured_image = '01-150-140.webp' WHERE sku = '01-150-140';
UPDATE products SET featured_image = '01-150-141.webp' WHERE sku = '01-150-141';
UPDATE products SET featured_image = '01-150-176.webp' WHERE sku = '01-150-176';
UPDATE products SET featured_image = '01-150-262.webp' WHERE sku = '01-150-262';
UPDATE products SET featured_image = '01-150-263.webp' WHERE sku = '01-150-263';
UPDATE products SET featured_image = '01-150-264.webp' WHERE sku = '01-150-264';
UPDATE products SET featured_image = '01-150-300.webp' WHERE sku = '01-150-300';
UPDATE products SET featured_image = '01-150-302.webp' WHERE sku = '01-150-302';
UPDATE products SET featured_image = '01-150-304.webp' WHERE sku = '01-150-304';
UPDATE products SET featured_image = '01-150-320.webp' WHERE sku = '01-150-320';
UPDATE products SET featured_image = '01-150-325.webp' WHERE sku = '01-150-325';
UPDATE products SET featured_image = '01-150-330.webp' WHERE sku = '01-150-330';
UPDATE products SET featured_image = '01-150-356.webp' WHERE sku = '01-150-356';

-- Shared photo, split across these 6 SKUs
UPDATE products SET featured_image = '01-150-341.webp' WHERE sku = '01-150-341';
UPDATE products SET featured_image = '01-150-342.webp' WHERE sku = '01-150-342';
UPDATE products SET featured_image = '01-150-343.webp' WHERE sku = '01-150-343';
UPDATE products SET featured_image = '01-150-344.webp' WHERE sku = '01-150-344';
UPDATE products SET featured_image = '01-150-345.webp' WHERE sku = '01-150-345';
UPDATE products SET featured_image = '01-150-346.webp' WHERE sku = '01-150-346';
