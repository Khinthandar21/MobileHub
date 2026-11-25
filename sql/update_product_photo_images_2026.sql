-- Assign the downloaded model-specific product photos by unique product slug.
-- Slugs are used here because some existing SKUs are reused across products.
START TRANSACTION;

UPDATE products p
JOIN (
    SELECT 'ipad-air-m4-11-128gb-wifi' AS slug, 'tablet-ipad-air-m4.jpg' AS image
    UNION ALL SELECT 'galaxy-tab-s11-wifi-128gb', 'tablet-galaxy-tab-s11.jpg'
    UNION ALL SELECT 'redmi-pad-2-wifi-128gb', 'tablet-redmi-pad-2.webp'
    UNION ALL SELECT 'galaxy-tab-s10-fe-plus-wifi-128gb', 'tablet-galaxy-tab-s10-fe-plus.jpg'
    UNION ALL SELECT 'ipad-mini-7-wifi-128gb', 'tablet-ipad-mini-7.webp'
    UNION ALL SELECT 'ipad-pro-m5-11-256gb-wifi', 'tablet-ipad-pro-m5.jpg'
    UNION ALL SELECT 'apple-usb-c-20w-adapter', 'mh-ch-002.jpg'
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
) AS photo_map ON photo_map.slug = p.slug
SET p.image = photo_map.image;

COMMIT;
