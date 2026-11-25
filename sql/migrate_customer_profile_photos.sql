-- One-time migration: move each stored profile image path onto customer_users
-- and remove the old one-to-one profile photo table.
ALTER TABLE `customer_users`
  ADD COLUMN IF NOT EXISTS `avatar` VARCHAR(255) DEFAULT NULL AFTER `shipping_country`;

UPDATE `customer_users` c
JOIN `customer_profile_photos` p ON p.`customer_id` = c.`id`
SET c.`avatar` = p.`image`
WHERE c.`avatar` IS NULL OR c.`avatar` = '';

DROP TABLE `customer_profile_photos`;
