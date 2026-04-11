-- Fix foreign key constraint on users linked to employee
-- This makes deleting an employee remove the related user row automatically.

USE `simpletest_db`;

ALTER TABLE `users`
  DROP FOREIGN KEY `users_ibfk_1`;

ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1`
  FOREIGN KEY (`User_id`) REFERENCES `employee` (`Emp_id`)
  ON DELETE CASCADE;
