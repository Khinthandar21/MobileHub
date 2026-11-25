-- Add eight current Apple and Samsung phones to the Mobile Phones category.
-- Prices are MMK. Stock is set to zero until confirmed by the shop.
-- Product photos are stored in assets/uploads/products.

START TRANSACTION;

INSERT IGNORE INTO products
    (category_id, name, slug, sku, short_description, description,
     price, sale_price, stock, image, status, featured)
SELECT c.id, v.name, v.slug, v.sku, v.short_description, v.description,
       v.price, NULL, 0, v.image, 'active', 0
FROM categories c
CROSS JOIN (
    SELECT 'iPhone 17 256GB' AS name, 'iphone-17-256gb' AS slug, 'MH-PH-009' AS sku,
           '256GB storage with the A19 chip and 48MP Fusion camera.' AS short_description,
           'Apple iPhone 17 with 256GB storage, A19 chip, and 48MP Fusion camera. Available color photos: Black and Lavender.' AS description,
           5500000.00 AS price, 'iphone-17-black.jpg' AS image
    UNION ALL SELECT 'iPhone 17 Pro 256GB', 'iphone-17-pro-256gb', 'MH-PH-010',
           'Pro performance with A19 Pro and a 48MP camera system.',
           'Apple iPhone 17 Pro with 256GB storage, A19 Pro chip, and pro camera system. Color shown: Cosmic Orange.',
           6450000.00, 'iphone-17-pro-orange.jpg'
    UNION ALL SELECT 'iPhone 17 Pro Max 256GB', 'iphone-17-pro-max-256gb', 'MH-PH-011',
           'Large display, A19 Pro chip, and advanced camera system.',
           'Apple iPhone 17 Pro Max with 256GB storage, A19 Pro chip, and advanced camera system. Color shown: Deep Blue.',
           6990000.00, 'iphone-17-pro-max-blue.png'
    UNION ALL SELECT 'iPhone Air 256GB', 'iphone-air-256gb', 'MH-PH-012',
           'Ultra-thin design with A19 Pro and 256GB storage.',
           'Apple iPhone Air with 256GB storage and A19 Pro chip. Color shown: Space Black.',
           5260000.00, 'iphone-air-space-black.png'
    UNION ALL SELECT 'Samsung Galaxy S26 256GB', 'samsung-galaxy-s26-256gb', 'MH-PH-013',
           '256GB storage, Galaxy AI, and a 50MP camera system.',
           'Samsung Galaxy S26 with 256GB storage, Galaxy AI, and a 50MP camera system. Color shown: Black.',
           4967000.00, 'galaxy-s26-black.jpg'
    UNION ALL SELECT 'Samsung Galaxy S26+ 256GB', 'samsung-galaxy-s26-plus-256gb', 'MH-PH-014',
           'Large AMOLED display, Galaxy AI, and 256GB storage.',
           'Samsung Galaxy S26+ with 256GB storage, a large AMOLED display, and Galaxy AI. Available color photos: Black and Cobalt Violet.',
           5867000.00, 'galaxy-s26-plus-black.jpg'
    UNION ALL SELECT 'Samsung Galaxy A57 5G 256GB', 'samsung-galaxy-a57-5g-256gb', 'MH-PH-015',
           '256GB storage, 5G, and a 120Hz AMOLED display.',
           'Samsung Galaxy A57 5G with 256GB storage, a 120Hz AMOLED display, and 5G connectivity. Available color photos: Awesome Navy and Awesome Gray.',
           2718000.00, 'galaxy-a57-navy.jpg'
    UNION ALL SELECT 'Samsung Galaxy A37 5G 256GB', 'samsung-galaxy-a37-5g-256gb', 'MH-PH-016',
           '256GB storage, 5G, and a 120Hz AMOLED display.',
           'Samsung Galaxy A37 5G with 256GB storage, a 120Hz AMOLED display, and 5G connectivity. Color shown: Awesome Charcoal.',
           1943000.00, 'galaxy-a37-black.jpg'
) v
WHERE c.slug = 'mobile-phones';

UPDATE products SET description = 'Apple iPhone 17 with 256GB storage, A19 chip, and 48MP Fusion camera. Available color photos: Black and Lavender.'
WHERE slug = 'iphone-17-256gb';
UPDATE products SET description = 'Apple iPhone 17 Pro with 256GB storage, A19 Pro chip, and pro camera system. Color shown: Cosmic Orange.'
WHERE slug = 'iphone-17-pro-256gb';
UPDATE products SET description = 'Apple iPhone 17 Pro Max with 256GB storage, A19 Pro chip, and advanced camera system. Color shown: Deep Blue.'
WHERE slug = 'iphone-17-pro-max-256gb';
UPDATE products SET description = 'Apple iPhone Air with 256GB storage and A19 Pro chip. Color shown: Space Black.'
WHERE slug = 'iphone-air-256gb';
UPDATE products SET description = 'Samsung Galaxy S26 with 256GB storage, Galaxy AI, and a 50MP camera system. Color shown: Black.'
WHERE slug = 'samsung-galaxy-s26-256gb';
UPDATE products SET description = 'Samsung Galaxy S26+ with 256GB storage, a large AMOLED display, and Galaxy AI. Available color photos: Black and Cobalt Violet.'
WHERE slug = 'samsung-galaxy-s26-plus-256gb';
UPDATE products SET description = 'Samsung Galaxy A57 5G with 256GB storage, a 120Hz AMOLED display, and 5G connectivity. Available color photos: Awesome Navy and Awesome Gray.'
WHERE slug = 'samsung-galaxy-a57-5g-256gb';
UPDATE products SET description = 'Samsung Galaxy A37 5G with 256GB storage, a 120Hz AMOLED display, and 5G connectivity. Color shown: Awesome Charcoal.'
WHERE slug = 'samsung-galaxy-a37-5g-256gb';

INSERT INTO product_images (product_id, image, label, sort_order)
SELECT p.id, v.image, v.label, 1
FROM products p
JOIN (
    SELECT 'iphone-17-256gb' AS slug, 'iphone-17-black.jpg' AS image, 'Black' AS label
    UNION ALL SELECT 'iphone-17-256gb', 'iphone-17-lavender.jpg', 'Lavender'
    UNION ALL SELECT 'iphone-17-pro-256gb', 'iphone-17-pro-orange.jpg', 'Cosmic Orange'
    UNION ALL SELECT 'iphone-17-pro-max-256gb', 'iphone-17-pro-max-blue.png', 'Deep Blue'
    UNION ALL SELECT 'iphone-air-256gb', 'iphone-air-space-black.png', 'Space Black'
    UNION ALL SELECT 'samsung-galaxy-s26-256gb', 'galaxy-s26-black.jpg', 'Black'
    UNION ALL SELECT 'samsung-galaxy-s26-plus-256gb', 'galaxy-s26-plus-black.jpg', 'Black'
    UNION ALL SELECT 'samsung-galaxy-s26-plus-256gb', 'galaxy-s26-plus-violet.webp', 'Cobalt Violet'
    UNION ALL SELECT 'samsung-galaxy-a57-5g-256gb', 'galaxy-a57-navy.jpg', 'Awesome Navy'
    UNION ALL SELECT 'samsung-galaxy-a57-5g-256gb', 'galaxy-a57-gray.jpg', 'Awesome Gray'
    UNION ALL SELECT 'samsung-galaxy-a37-5g-256gb', 'galaxy-a37-black.jpg', 'Awesome Charcoal'
) v ON v.slug = p.slug
WHERE NOT EXISTS (
    SELECT 1 FROM product_images pi
    WHERE pi.product_id = p.id AND pi.image = v.image
);

COMMIT;
