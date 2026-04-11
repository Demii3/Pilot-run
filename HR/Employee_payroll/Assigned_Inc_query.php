<?php

function getAssignedIncTableSql() {
    return "CREATE TABLE IF NOT EXISTS `Assigned_Inc` (
      `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
      `employee_id` INT UNSIGNED NOT NULL,
      `income_type_id` INT UNSIGNED NOT NULL,
      `type_of_income` VARCHAR(255) NOT NULL,
      `cost` DECIMAL(15,2) NOT NULL DEFAULT '0.00',
      `taxable` TINYINT(1) NOT NULL DEFAULT 0,
      `included_in_13month` TINYINT(1) NOT NULL DEFAULT 0,
      `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`),
      INDEX (`employee_id`),
      INDEX (`income_type_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
}
