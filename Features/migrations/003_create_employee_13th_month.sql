CREATE TABLE IF NOT EXISTS `employee_13th_month` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `employee_id` INT UNSIGNED NOT NULL,
  `employee_name` VARCHAR(255) NOT NULL,
  `process_year` SMALLINT NOT NULL,
  `monthly_salary` DECIMAL(15,2) NOT NULL DEFAULT '0.00',
  `total_basic_salary_earned` DECIMAL(15,2) NOT NULL DEFAULT '0.00',
  `month_13_pay` DECIMAL(15,2) NOT NULL DEFAULT '0.00',
  `computed_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_employee_year` (`employee_id`, `process_year`),
  KEY `idx_process_year` (`process_year`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
