-- Run once after the `users` table has been renamed to `admin_users`.
-- Existing accounts remain unchanged; newly created accounts are flagged in PHP.
ALTER TABLE `admin_users`
  ADD COLUMN IF NOT EXISTS `must_change_password` TINYINT(1) NOT NULL DEFAULT 0 AFTER `status`;
