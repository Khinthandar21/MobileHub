-- Run once on an existing MobileHub database to rename the admin account table.
-- Back up the database first. MySQL/InnoDB preserves the rows and updates the
-- activity_log foreign-key reference when the table is renamed.

RENAME TABLE `users` TO `admin_users`;
