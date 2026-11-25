-- Add a distinct status for pickup orders that have been prepared.
ALTER TABLE `orders`
  MODIFY COLUMN `status` ENUM('pending','processing','shipped','ready_for_pickup','completed','cancelled')
  NOT NULL DEFAULT 'pending';

-- Existing pickup orders shown as "Ready for pickup" were stored as shipped.
UPDATE `orders`
SET `status` = 'ready_for_pickup'
WHERE `fulfillment_method` = 'pickup' AND `status` = 'shipped';
