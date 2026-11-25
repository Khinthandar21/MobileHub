-- Add current phone models to the MobileHub catalog.
-- Prices are in MMK. Stock is zero until the shop confirms quantities.
-- Image files must be present in assets/uploads/products before running this.

START TRANSACTION;

INSERT IGNORE INTO products
    (category_id, name, slug, sku, short_description, description,
     price, sale_price, stock, image, status, featured)
SELECT c.id, 'iPhone 17e 256GB', 'iphone-17e-256gb', 'MH-PH-007',
       '256GB storage, A19 chip, and 48MP Fusion camera.',
       'Apple iPhone 17e with 256GB storage, A19 chip, 48MP Fusion camera, and 5G connectivity. Available in Black, White, and Soft Pink.',
       4000000.00, NULL, 0, 'iphone-17e-black.jpg', 'active', 0
FROM categories c
WHERE c.slug = 'mobile-phones';

INSERT IGNORE INTO products
    (category_id, name, slug, sku, short_description, description,
     price, sale_price, stock, image, status, featured)
SELECT c.id, 'Samsung Galaxy S26 Ultra 256GB', 'samsung-galaxy-s26-ultra-256gb', 'MH-PH-008',
       '256GB storage, Galaxy AI, S Pen, and a 200MP camera.',
       'Samsung Galaxy S26 Ultra with 256GB storage, Snapdragon 8 Elite Gen 5, S Pen, and 200MP main camera. Available in Black, Sky Blue, and Pink Gold.',
       6896000.00, NULL, 0, 'galaxy-s26-ultra-black.png', 'active', 0
FROM categories c
WHERE c.slug = 'mobile-phones';

INSERT INTO product_images (product_id, image, label, sort_order)
SELECT p.id, v.image, v.label, v.sort_order
FROM products p
JOIN (
    SELECT 'iphone-17e-256gb' AS slug, 'iphone-17e-black.jpg' AS image, 'Black' AS label, 1 AS sort_order
    UNION ALL SELECT 'iphone-17e-256gb', 'iphone-17e-white.jpg', 'White', 2
    UNION ALL SELECT 'iphone-17e-256gb', 'iphone-17e-soft-pink.png', 'Soft Pink', 3
    UNION ALL SELECT 'samsung-galaxy-s26-ultra-256gb', 'galaxy-s26-ultra-black.png', 'Black', 1
    UNION ALL SELECT 'samsung-galaxy-s26-ultra-256gb', 'galaxy-s26-ultra-sky-blue.jpg', 'Sky Blue', 2
    UNION ALL SELECT 'samsung-galaxy-s26-ultra-256gb', 'galaxy-s26-ultra-pink-gold.jpg', 'Pink Gold', 3
) v ON v.slug = p.slug
WHERE NOT EXISTS (
    SELECT 1 FROM product_images pi
    WHERE pi.product_id = p.id AND pi.image = v.image
);

COMMIT;
