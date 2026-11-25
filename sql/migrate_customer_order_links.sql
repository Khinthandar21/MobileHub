-- Run once on an existing MobileHub database before using the updated customer pages.
-- Back up the database first. Existing customer/order links are copied to orders.customer_id
-- before the old link table is removed. If duplicate customer links exist for an order,
-- the lowest customer id is retained.

ALTER TABLE `orders`
  ADD COLUMN `customer_id` INT UNSIGNED DEFAULT NULL AFTER `id`;

UPDATE `orders` AS o
JOIN (
  SELECT `order_id`, MIN(`customer_id`) AS `customer_id`
  FROM `customer_order_links`
  GROUP BY `order_id`
) AS old_links ON old_links.`order_id` = o.`id`
SET o.`customer_id` = old_links.`customer_id`;

ALTER TABLE `orders`
  ADD KEY `idx_orders_customer_id` (`customer_id`),
  ADD CONSTRAINT `fk_orders_customer`
    FOREIGN KEY (`customer_id`) REFERENCES `customer_users` (`id`) ON DELETE SET NULL;

DROP TABLE `customer_order_links`;
