<?php

function getEmpIncTypeTableSql() {
    return "CREATE TABLE IF NOT EXISTS `Emp_Inc_Type` (
      `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
      `type_of_income` VARCHAR(255) NOT NULL,
      `cost` DECIMAL(15,2) NOT NULL DEFAULT '0.00',
      `taxable` TINYINT(1) NOT NULL DEFAULT 1,
      `included_in_13month` TINYINT(1) NOT NULL DEFAULT 1,
      `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
      `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
}
