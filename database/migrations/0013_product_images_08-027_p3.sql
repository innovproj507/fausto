-- Product featured images migrated from local catalog folder "COD. 08-027 P3".
-- Sets products.featured_image; the actual image files under
-- public/uploads/products/ must be uploaded to production separately
-- (this only updates the database, not the filesystem).
--
-- The source folder had 6 shared photos with combined SKU names, split into
-- per-SKU copies before writing the UPDATEs below:
--   "08-027-662 y 663.webp"        -> 662, 663
--   "08-027-734 y 739.webp"        -> 734, 739
--   "08-027-750, 751 y 752.webp"   -> 750, 751, 752
--   "08-027-753,754,755.webp"      -> 753, 754, 755
--   "08-027-756,757 y 758.webp"    -> 756, 757, 758
--   "08-027-770 y 771.webp"        -> 770, 771
--
-- All 61 SKUs below were confirmed to exist in the products table before
-- writing this migration.

UPDATE products SET featured_image = '08-027-549.webp' WHERE sku = '08-027-549';
UPDATE products SET featured_image = '08-027-550.webp' WHERE sku = '08-027-550';
UPDATE products SET featured_image = '08-027-552.webp' WHERE sku = '08-027-552';
UPDATE products SET featured_image = '08-027-553.webp' WHERE sku = '08-027-553';
UPDATE products SET featured_image = '08-027-554.webp' WHERE sku = '08-027-554';
UPDATE products SET featured_image = '08-027-555.webp' WHERE sku = '08-027-555';
UPDATE products SET featured_image = '08-027-556.webp' WHERE sku = '08-027-556';
UPDATE products SET featured_image = '08-027-557.webp' WHERE sku = '08-027-557';
UPDATE products SET featured_image = '08-027-558.webp' WHERE sku = '08-027-558';
UPDATE products SET featured_image = '08-027-565.webp' WHERE sku = '08-027-565';
UPDATE products SET featured_image = '08-027-596.webp' WHERE sku = '08-027-596';
UPDATE products SET featured_image = '08-027-614.webp' WHERE sku = '08-027-614';
UPDATE products SET featured_image = '08-027-615.webp' WHERE sku = '08-027-615';
UPDATE products SET featured_image = '08-027-620.webp' WHERE sku = '08-027-620';
UPDATE products SET featured_image = '08-027-683.webp' WHERE sku = '08-027-683';
UPDATE products SET featured_image = '08-027-685.webp' WHERE sku = '08-027-685';
UPDATE products SET featured_image = '08-027-694.webp' WHERE sku = '08-027-694';
UPDATE products SET featured_image = '08-027-702.webp' WHERE sku = '08-027-702';
UPDATE products SET featured_image = '08-027-705.webp' WHERE sku = '08-027-705';
UPDATE products SET featured_image = '08-027-710.webp' WHERE sku = '08-027-710';
UPDATE products SET featured_image = '08-027-773.webp' WHERE sku = '08-027-773';
UPDATE products SET featured_image = '08-027-781.webp' WHERE sku = '08-027-781';
UPDATE products SET featured_image = '08-027-782.webp' WHERE sku = '08-027-782';
UPDATE products SET featured_image = '08-027-800.webp' WHERE sku = '08-027-800';
UPDATE products SET featured_image = '08-027-801.webp' WHERE sku = '08-027-801';
UPDATE products SET featured_image = '08-027-803.webp' WHERE sku = '08-027-803';
UPDATE products SET featured_image = '08-027-804.webp' WHERE sku = '08-027-804';
UPDATE products SET featured_image = '08-027-806.webp' WHERE sku = '08-027-806';
UPDATE products SET featured_image = '08-027-807.webp' WHERE sku = '08-027-807';
UPDATE products SET featured_image = '08-027-825.webp' WHERE sku = '08-027-825';
UPDATE products SET featured_image = '08-027-826.webp' WHERE sku = '08-027-826';
UPDATE products SET featured_image = '08-027-827.webp' WHERE sku = '08-027-827';
UPDATE products SET featured_image = '08-027-841.webp' WHERE sku = '08-027-841';
UPDATE products SET featured_image = '08-027-843.webp' WHERE sku = '08-027-843';
UPDATE products SET featured_image = '08-027-844.webp' WHERE sku = '08-027-844';
UPDATE products SET featured_image = '08-027-845.webp' WHERE sku = '08-027-845';
UPDATE products SET featured_image = '08-027-847.webp' WHERE sku = '08-027-847';
UPDATE products SET featured_image = '08-027-848.webp' WHERE sku = '08-027-848';
UPDATE products SET featured_image = '08-027-849.webp' WHERE sku = '08-027-849';
UPDATE products SET featured_image = '08-027-850.webp' WHERE sku = '08-027-850';
UPDATE products SET featured_image = '08-027-851.webp' WHERE sku = '08-027-851';
UPDATE products SET featured_image = '08-027-852.webp' WHERE sku = '08-027-852';
UPDATE products SET featured_image = '08-027-853.webp' WHERE sku = '08-027-853';
UPDATE products SET featured_image = '08-027-859.webp' WHERE sku = '08-027-859';
UPDATE products SET featured_image = '08-027-902.webp' WHERE sku = '08-027-902';
UPDATE products SET featured_image = '08-027-904.webp' WHERE sku = '08-027-904';

-- Shared photos, split across multiple SKUs
UPDATE products SET featured_image = '08-027-662.webp' WHERE sku = '08-027-662';
UPDATE products SET featured_image = '08-027-663.webp' WHERE sku = '08-027-663';
UPDATE products SET featured_image = '08-027-734.webp' WHERE sku = '08-027-734';
UPDATE products SET featured_image = '08-027-739.webp' WHERE sku = '08-027-739';
UPDATE products SET featured_image = '08-027-750.webp' WHERE sku = '08-027-750';
UPDATE products SET featured_image = '08-027-751.webp' WHERE sku = '08-027-751';
UPDATE products SET featured_image = '08-027-752.webp' WHERE sku = '08-027-752';
UPDATE products SET featured_image = '08-027-753.webp' WHERE sku = '08-027-753';
UPDATE products SET featured_image = '08-027-754.webp' WHERE sku = '08-027-754';
UPDATE products SET featured_image = '08-027-755.webp' WHERE sku = '08-027-755';
UPDATE products SET featured_image = '08-027-756.webp' WHERE sku = '08-027-756';
UPDATE products SET featured_image = '08-027-757.webp' WHERE sku = '08-027-757';
UPDATE products SET featured_image = '08-027-758.webp' WHERE sku = '08-027-758';
UPDATE products SET featured_image = '08-027-770.webp' WHERE sku = '08-027-770';
UPDATE products SET featured_image = '08-027-771.webp' WHERE sku = '08-027-771';
