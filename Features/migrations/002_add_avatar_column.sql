-- Migration: Add avatar column to employees table
-- Add a column to store the avatar file path

ALTER TABLE `employees` ADD COLUMN `avatar_path` VARCHAR(255) DEFAULT NULL AFTER `status`;
CREATE INDEX `idx_avatar_path` ON `employees` (`avatar_path`);
