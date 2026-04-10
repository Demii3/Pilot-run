-- SQL file for HR employee management
-- Run this in your MySQL/MariaDB database to ensure the employee table exists.

USE `simpletest_db`;

CREATE TABLE IF NOT EXISTS `employee` (
  `Emp_id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `Firstname` VARCHAR(255) NOT NULL,
  `Lastname` VARCHAR(255) NOT NULL,
  `Department` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`Emp_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Optional sample data
INSERT INTO `employee` (`Firstname`, `Lastname`, `Department`) VALUES
('Joyce', 'Bryce', 'HR'),
('Bianca', 'Mayor', 'Project Manager'),
('Demetri', 'Mayor', 'System Admin');
