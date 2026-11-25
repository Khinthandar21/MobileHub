-- Bring every non-phone category to eight active products.
-- Prices are numeric MMK values. Stock stays zero pending a shop inventory check.
-- The category SVGs are illustrative thumbnails for these new catalog entries.

START TRANSACTION;

INSERT IGNORE INTO products
    (category_id, name, slug, sku, short_description, description,
     price, sale_price, stock, image, status, featured)
SELECT c.id, v.name, v.slug, v.sku, v.short_description, v.description,
       v.price, NULL, 0, v.image, 'active', 0
FROM categories c
JOIN (
    SELECT 'tablets' AS category, 'iPad Air M4 11-inch 128GB Wi-Fi' AS name, 'ipad-air-m4-11-128gb-wifi' AS slug, 'MH-TB-003' AS sku,
           'M4 performance with an 11-inch Liquid Retina display.' AS short_description, 'Apple iPad Air with M4 chip, 11-inch display, and 128GB storage.' AS description,
           3550000.00 AS price, 'tablet-ipad-air-m4.jpg' AS image
    UNION ALL SELECT 'tablets','Samsung Galaxy Tab S11 Wi-Fi 128GB','galaxy-tab-s11-wifi-128gb','MH-TB-004','11-inch AMOLED tablet with S Pen support.','Samsung Galaxy Tab S11 Wi-Fi tablet with 128GB storage and S Pen support.',4047000.00,'tablet-galaxy-tab-s11.jpg'
    UNION ALL SELECT 'tablets','Xiaomi Redmi Pad 2 Wi-Fi 128GB','redmi-pad-2-wifi-128gb','MH-TB-005','Everyday tablet with a large display and 128GB storage.','Xiaomi Redmi Pad 2 Wi-Fi tablet with 128GB storage.',947000.00,'tablet-redmi-pad-2.webp'
    UNION ALL SELECT 'tablets','Samsung Galaxy Tab S10 FE+ Wi-Fi 128GB','galaxy-tab-s10-fe-plus-wifi-128gb','MH-TB-006','Large-screen tablet with S Pen support.','Samsung Galaxy Tab S10 FE+ Wi-Fi tablet with 128GB storage and S Pen support.',2924000.00,'tablet-galaxy-tab-s10-fe-plus.jpg'
    UNION ALL SELECT 'tablets','iPad mini 7 Wi-Fi 128GB','ipad-mini-7-wifi-128gb','MH-TB-007','Compact iPad with an 8.3-inch display.','Apple iPad mini 7 Wi-Fi tablet with 128GB storage.',3080000.00,'tablet-ipad-mini-7.webp'
    UNION ALL SELECT 'tablets','iPad Pro M5 11-inch 256GB Wi-Fi','ipad-pro-m5-11-256gb-wifi','MH-TB-008','11-inch pro tablet with the M5 chip.','Apple iPad Pro 11-inch with M5 chip and 256GB storage.',6210000.00,'tablet-ipad-pro-m5.jpg'

    UNION ALL SELECT 'chargers','Apple USB-C 20W Power Adapter','apple-usb-c-20w-adapter','MH-CH-002','Compact 20W USB-C power adapter.','Apple USB-C 20W power adapter. Charging cable sold separately.',95000.00,'catalog-charger.svg'
    UNION ALL SELECT 'chargers','Samsung 45W USB-C Super Fast Charger','samsung-45w-usb-c-charger','MH-CH-003','45W USB-C fast charging adapter.','Samsung 45W USB-C Super Fast Charger with Power Delivery support.',125000.00,'catalog-charger.svg'
    UNION ALL SELECT 'chargers','Anker Nano 30W USB-C Charger','anker-nano-30w-usb-c-charger','MH-CH-004','Compact 30W USB-C charger.','Anker Nano USB-C 30W compact charger.',85000.00,'catalog-charger.svg'
    UNION ALL SELECT 'chargers','UGREEN Nexode 65W GaN Charger','ugreen-nexode-65w-gan-charger','MH-CH-005','Multi-port 65W GaN wall charger.','UGREEN Nexode 65W GaN charger for phones, tablets, and compatible laptops.',175000.00,'catalog-charger.svg'
    UNION ALL SELECT 'chargers','Baseus GaN5 Pro 65W Charger','baseus-gan5-pro-65w-charger','MH-CH-006','65W multi-port GaN charger.','Baseus GaN5 Pro 65W USB-C and USB-A wall charger.',155000.00,'catalog-charger.svg'
    UNION ALL SELECT 'chargers','Anker 735 Nano II 65W Charger','anker-735-nano-ii-65w-charger','MH-CH-007','Three-port 65W GaN charger.','Anker 735 Nano II 65W USB-C and USB-A wall charger.',195000.00,'catalog-charger.svg'
    UNION ALL SELECT 'chargers','UGREEN Nexode 100W GaN Charger','ugreen-nexode-100w-gan-charger','MH-CH-008','High-output multi-port 100W charger.','UGREEN Nexode 100W GaN charger for multiple compatible devices.',245000.00,'catalog-charger.svg'

    UNION ALL SELECT 'earphones','Apple AirPods Pro 3','apple-airpods-pro-3','MH-EP-003','Wireless earbuds with active noise cancellation.','Apple AirPods Pro 3 wireless earbuds with active noise cancellation and charging case.',1045000.00,'catalog-earbuds.svg'
    UNION ALL SELECT 'earphones','Apple AirPods 4 with ANC','apple-airpods-4-anc','MH-EP-004','Open-fit earbuds with active noise cancellation.','Apple AirPods 4 with Active Noise Cancellation and wireless charging case.',775000.00,'catalog-earbuds.svg'
    UNION ALL SELECT 'earphones','Samsung Galaxy Buds4 Pro','samsung-galaxy-buds4-pro','MH-EP-005','Premium wireless earbuds with adaptive audio.','Samsung Galaxy Buds4 Pro wireless earbuds with noise control and charging case.',890000.00,'catalog-earbuds.svg'
    UNION ALL SELECT 'earphones','Samsung Galaxy Buds4','samsung-galaxy-buds4','MH-EP-006','Wireless earbuds for everyday listening.','Samsung Galaxy Buds4 wireless earbuds with charging case.',590000.00,'catalog-earbuds.svg'
    UNION ALL SELECT 'earphones','Sony WF-1000XM6 Wireless Earbuds','sony-wf-1000xm6-earbuds','MH-EP-007','Noise-cancelling earbuds with high-resolution audio.','Sony WF-1000XM6 wireless noise-cancelling earbuds.',1190000.00,'catalog-earbuds.svg'
    UNION ALL SELECT 'earphones','Redmi Buds 6 Pro','redmi-buds-6-pro','MH-EP-008','Wireless earbuds with active noise cancellation.','Xiaomi Redmi Buds 6 Pro wireless earbuds with charging case.',290000.00,'catalog-earbuds.svg'

    UNION ALL SELECT 'phone-cases','iPhone 17 Silicone Case','iphone-17-silicone-case','MH-CS-002','Soft-touch protective case for iPhone 17.','Silicone protective case designed for Apple iPhone 17.',180000.00,'catalog-case.svg'
    UNION ALL SELECT 'phone-cases','iPhone 17 Pro Clear Case','iphone-17-pro-clear-case','MH-CS-003','Clear protective case for iPhone 17 Pro.','Transparent protective case designed for Apple iPhone 17 Pro.',125000.00,'catalog-case.svg'
    UNION ALL SELECT 'phone-cases','iPhone Air Bumper Case','iphone-air-bumper-case','MH-CS-004','Slim protective bumper for iPhone Air.','Slim bumper case designed for Apple iPhone Air.',145000.00,'catalog-case.svg'
    UNION ALL SELECT 'phone-cases','Galaxy S26 Ultra Protective Case','galaxy-s26-ultra-protective-case','MH-CS-005','Protective case designed for Galaxy S26 Ultra.','Protective case designed for Samsung Galaxy S26 Ultra.',95000.00,'catalog-case.svg'
    UNION ALL SELECT 'phone-cases','Galaxy S26+ Standing Grip Case','galaxy-s26-plus-standing-grip-case','MH-CS-006','Protective case with integrated standing grip.','Samsung Galaxy S26+ compatible standing grip protective case.',125000.00,'catalog-case.svg'
    UNION ALL SELECT 'phone-cases','Galaxy A57 5G Clear Case','galaxy-a57-5g-clear-case','MH-CS-007','Clear protective case for Galaxy A57 5G.','Transparent protective case designed for Samsung Galaxy A57 5G.',35000.00,'catalog-case.svg'
    UNION ALL SELECT 'phone-cases','Spigen Tough Armor iPhone 17 Pro Max Case','spigen-tough-armor-iphone-17-pro-max','MH-CS-008','Dual-layer protective case for iPhone 17 Pro Max.','Spigen Tough Armor protective case designed for Apple iPhone 17 Pro Max.',165000.00,'catalog-case.svg'

    UNION ALL SELECT 'power-banks','Anker Prime 20K 200W Power Bank','anker-prime-20k-200w','MH-PB-002','20,000mAh capacity with up to 200W output.','Anker Prime 20,000mAh power bank with high-output USB-C charging.',690000.00,'catalog-powerbank.svg'
    UNION ALL SELECT 'power-banks','Anker MagGo 10K Qi2 Power Bank','anker-maggo-10k-qi2','MH-PB-003','10,000mAh magnetic Qi2 wireless power bank.','Anker MagGo 10,000mAh magnetic Qi2 wireless power bank.',345000.00,'catalog-powerbank.svg'
    UNION ALL SELECT 'power-banks','Xiaomi 20K 33W Power Bank','xiaomi-20k-33w-power-bank','MH-PB-004','20,000mAh capacity with 33W fast charging.','Xiaomi 20,000mAh power bank with 33W fast charging.',145000.00,'catalog-powerbank.svg'
    UNION ALL SELECT 'power-banks','UGREEN Nexode 20K 130W Power Bank','ugreen-nexode-20k-130w','MH-PB-005','20,000mAh capacity with up to 130W output.','UGREEN Nexode 20,000mAh high-output power bank.',490000.00,'catalog-powerbank.svg'
    UNION ALL SELECT 'power-banks','Baseus 30K 65W Power Bank','baseus-30k-65w-power-bank','MH-PB-006','30,000mAh capacity with 65W USB-C output.','Baseus 30,000mAh power bank with 65W fast charging.',350000.00,'catalog-powerbank.svg'
    UNION ALL SELECT 'power-banks','Samsung 20K 45W Power Bank','samsung-20k-45w-power-bank','MH-PB-007','20,000mAh capacity with 45W wired charging.','Samsung 20,000mAh 45W USB-C power bank.',265000.00,'catalog-powerbank.svg'
    UNION ALL SELECT 'power-banks','Anker Zolo 20K 30W Power Bank','anker-zolo-20k-30w-power-bank','MH-PB-008','20,000mAh capacity with 30W USB-C output.','Anker Zolo 20,000mAh power bank with 30W USB-C fast charging.',195000.00,'catalog-powerbank.svg'

    UNION ALL SELECT 'accessories','Apple Pencil Pro','apple-pencil-pro','MH-AC-002','Creative stylus with pressure and tilt support.','Apple Pencil Pro stylus with squeeze, barrel roll, and Find My support.',525000.00,'catalog-accessory.svg'
    UNION ALL SELECT 'accessories','Apple AirTag (1 pack)','apple-airtag-1-pack','MH-AC-003','Item tracker that works with the Find My network.','Apple AirTag Bluetooth item tracker, single pack.',144000.00,'catalog-accessory.svg'
    UNION ALL SELECT 'accessories','Samsung S Pen for Galaxy Tab S11','samsung-s-pen-tab-s11','MH-AC-004','Replacement S Pen for compatible Galaxy tablets.','Samsung S Pen accessory compatible with Galaxy Tab S11.',175000.00,'catalog-accessory.svg'
    UNION ALL SELECT 'accessories','Apple MagSafe Charger','apple-magsafe-charger','MH-AC-005','Magnetic wireless charger for compatible iPhone models.','Apple MagSafe wireless charger for compatible iPhone models.',175000.00,'catalog-accessory.svg'
    UNION ALL SELECT 'accessories','USB-C 8-in-1 Hub','usb-c-8-in-1-hub','MH-AC-006','Multi-port USB-C hub for compatible devices.','USB-C hub with HDMI, USB-A, USB-C, and card reader ports.',95000.00,'catalog-accessory.svg'
    UNION ALL SELECT 'accessories','3-in-1 Wireless Charging Stand','3-in-1-wireless-charging-stand','MH-AC-007','Charge a phone, earbuds, and watch in one place.','Foldable 3-in-1 wireless charging stand for compatible phones, earbuds, and watches.',195000.00,'catalog-accessory.svg'
    UNION ALL SELECT 'accessories','Braided USB-C Cable 2m','braided-usb-c-cable-2m','MH-AC-008','Durable 2-metre USB-C charging and sync cable.','Braided USB-C to USB-C charging and data cable, 2 metres.',35000.00,'catalog-accessory.svg'
) v ON v.category = c.slug
WHERE c.status = 'active';

UPDATE products p
JOIN (
    SELECT 'ipad-air-m4-11-128gb-wifi' AS slug, 'tablet-ipad-air-m4.jpg' AS image
    UNION ALL SELECT 'galaxy-tab-s11-wifi-128gb', 'tablet-galaxy-tab-s11.jpg'
    UNION ALL SELECT 'redmi-pad-2-wifi-128gb', 'tablet-redmi-pad-2.webp'
    UNION ALL SELECT 'galaxy-tab-s10-fe-plus-wifi-128gb', 'tablet-galaxy-tab-s10-fe-plus.jpg'
    UNION ALL SELECT 'ipad-mini-7-wifi-128gb', 'tablet-ipad-mini-7.webp'
    UNION ALL SELECT 'ipad-pro-m5-11-256gb-wifi', 'tablet-ipad-pro-m5.jpg'
) v ON v.slug = p.slug
SET p.image = v.image;

UPDATE products p
JOIN (
    SELECT 'apple-usb-c-20w-adapter' AS slug, 'mh-ch-002.jpg' AS image
    UNION ALL SELECT 'samsung-45w-usb-c-charger', 'mh-ch-003.jpg'
    UNION ALL SELECT 'anker-nano-30w-usb-c-charger', 'mh-ch-004.jpg'
    UNION ALL SELECT 'ugreen-nexode-65w-gan-charger', 'mh-ch-005.jpg'
    UNION ALL SELECT 'baseus-gan5-pro-65w-charger', 'mh-ch-006.jpg'
    UNION ALL SELECT 'anker-735-nano-ii-65w-charger', 'mh-ch-007.jpg'
    UNION ALL SELECT 'ugreen-nexode-100w-gan-charger', 'mh-ch-008.jpg'
    UNION ALL SELECT 'apple-airpods-pro-3', 'mh-ep-003.jpg'
    UNION ALL SELECT 'apple-airpods-4-anc', 'mh-ep-004.jpg'
    UNION ALL SELECT 'samsung-galaxy-buds4-pro', 'mh-ep-005.jpg'
    UNION ALL SELECT 'samsung-galaxy-buds4', 'mh-ep-006.jpg'
    UNION ALL SELECT 'sony-wf-1000xm6-earbuds', 'mh-ep-007.jpg'
    UNION ALL SELECT 'redmi-buds-6-pro', 'mh-ep-008.jpg'
    UNION ALL SELECT 'iphone-17-silicone-case', 'mh-cs-002.jpg'
    UNION ALL SELECT 'iphone-17-pro-clear-case', 'mh-cs-003.webp'
    UNION ALL SELECT 'iphone-air-bumper-case', 'mh-cs-004.png'
    UNION ALL SELECT 'galaxy-s26-ultra-protective-case', 'mh-cs-005.jpg'
    UNION ALL SELECT 'galaxy-s26-plus-standing-grip-case', 'mh-cs-006.jpg'
    UNION ALL SELECT 'galaxy-a57-5g-clear-case', 'mh-cs-007.jpg'
    UNION ALL SELECT 'spigen-tough-armor-iphone-17-pro-max', 'mh-cs-008.jpg'
    UNION ALL SELECT 'anker-prime-20k-200w', 'mh-pb-002.jpg'
    UNION ALL SELECT 'anker-maggo-10k-qi2', 'mh-pb-003.jpg'
    UNION ALL SELECT 'xiaomi-20k-33w-power-bank', 'mh-pb-004.jpg'
    UNION ALL SELECT 'ugreen-nexode-20k-130w', 'mh-pb-005.webp'
    UNION ALL SELECT 'baseus-30k-65w-power-bank', 'mh-pb-006.jpg'
    UNION ALL SELECT 'samsung-20k-45w-power-bank', 'mh-pb-007.jpg'
    UNION ALL SELECT 'anker-zolo-20k-30w-power-bank', 'mh-pb-008.jpg'
    UNION ALL SELECT 'apple-pencil-pro', 'mh-ac-002.jpg'
    UNION ALL SELECT 'apple-airtag-1-pack', 'mh-ac-003.webp'
    UNION ALL SELECT 'samsung-s-pen-tab-s11', 'mh-ac-004.jpg'
    UNION ALL SELECT 'apple-magsafe-charger', 'mh-ac-005.jpg'
    UNION ALL SELECT 'usb-c-8-in-1-hub', 'mh-ac-006.png'
    UNION ALL SELECT '3-in-1-wireless-charging-stand', 'mh-ac-007.jpg'
    UNION ALL SELECT 'braided-usb-c-cable-2m', 'mh-ac-008.jpg'
) v ON v.slug = p.slug
SET p.image = v.image;

COMMIT;
