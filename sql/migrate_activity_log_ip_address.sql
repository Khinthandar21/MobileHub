-- Add the client IP field expected by includes/functions.php::log_activity().
-- Run once on databases created before ip_address was added to activity_log.
ALTER TABLE `activity_log`
  ADD COLUMN `ip_address` VARCHAR(45) DEFAULT NULL AFTER `description`;
