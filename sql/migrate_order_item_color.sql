-- Run once on an existing MobileHub database to store the selected color per order item.
ALTER TABLE `order_items`
  ADD COLUMN `product_color` VARCHAR(80) DEFAULT NULL AFTER `product_image`;
