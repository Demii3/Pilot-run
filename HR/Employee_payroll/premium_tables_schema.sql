-- Premium Tables Schema for CRUD Management
-- These tables store tax, SSS, PhilHealth, and Pag-IBIG computation data by year

-- Withholding Tax Table
CREATE TABLE IF NOT EXISTS `tax_table` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `year` INT NOT NULL,
  `income_from` DECIMAL(15,2) NOT NULL,
  `income_to` DECIMAL(15,2),
  `tax_rate` DECIMAL(5,2) NOT NULL,
  `base_tax` DECIMAL(15,2) DEFAULT 0,
  `description` VARCHAR(255),
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_year` (`year`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- SSS Contribution Table
CREATE TABLE IF NOT EXISTS `sss_table` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `year` INT NOT NULL,
  `salary_from` DECIMAL(15,2) NOT NULL,
  `salary_to` DECIMAL(15,2),
  `monthly_contribution` DECIMAL(10,2) NOT NULL,
  `description` VARCHAR(255),
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_year` (`year`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- PhilHealth Contribution Table
CREATE TABLE IF NOT EXISTS `philhealth_table` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `year` INT NOT NULL,
  `salary_from` DECIMAL(15,2) NOT NULL,
  `salary_to` DECIMAL(15,2),
  `contribution_rate` DECIMAL(5,4) NOT NULL,
  `maximum_contribution` DECIMAL(10,2) NOT NULL,
  `fixed_amount` DECIMAL(10,2),
  `description` VARCHAR(255),
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_year` (`year`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Pag-IBIG Contribution Table
CREATE TABLE IF NOT EXISTS `pagibig_table` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `year` INT NOT NULL,
  `salary_from` DECIMAL(15,2) NOT NULL,
  `salary_to` DECIMAL(15,2),
  `contribution_rate` DECIMAL(5,4) NOT NULL,
  `maximum_contribution` DECIMAL(10,2) NOT NULL,
  `description` VARCHAR(255),
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_year` (`year`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default 2026 data
INSERT IGNORE INTO `tax_table` (`year`, `income_from`, `income_to`, `tax_rate`, `base_tax`, `description`) VALUES
(2026, 0, 20833, 0.00, 0, 'Up to ₱20,833'),
(2026, 20833.01, 33333, 0.15, 0, '₱20,833 - ₱33,333'),
(2026, 33333.01, 66667, 0.20, 1875, '₱33,333 - ₱66,667'),
(2026, 66667.01, 166667, 0.25, 8541.80, '₱66,667 - ₱166,667'),
(2026, 166667.01, 666667, 0.30, 33541.80, '₱166,667 - ₱666,667'),
(2026, 666667.01, NULL, 0.35, 183541.80, 'Above ₱666,667');

INSERT IGNORE INTO `sss_table` (`year`, `salary_from`, `salary_to`, `monthly_contribution`, `description`) VALUES
(2026, 0, 5250, 250, 'Below ₱5,250'),
(2026, 5250.01, 5750, 275, '₱5,250 - ₱5,749'),
(2026, 5750.01, 6250, 300, '₱5,750 - ₱6,249'),
(2026, 6250.01, 6750, 325, '₱6,250 - ₱6,749'),
(2026, 6750.01, 7250, 350, '₱6,750 - ₱7,249'),
(2026, 7250.01, 7750, 375, '₱7,250 - ₱7,749'),
(2026, 7750.01, 8250, 400, '₱7,750 - ₱8,249'),
(2026, 8250.01, 8750, 425, '₱8,250 - ₱8,749'),
(2026, 8750.01, 9250, 450, '₱8,750 - ₱9,249'),
(2026, 9250.01, 9750, 475, '₱9,250 - ₱9,749'),
(2026, 9750.01, 10250, 500, '₱9,750 - ₱10,249'),
(2026, 10250.01, 10750, 525, '₱10,250 - ₱10,749'),
(2026, 10750.01, 11250, 550, '₱10,750 - ₱11,249'),
(2026, 11250.01, 11750, 575, '₱11,250 - ₱11,749'),
(2026, 11750.01, 12250, 600, '₱11,750 - ₱12,249'),
(2026, 12250.01, 12750, 625, '₱12,250 - ₱12,749'),
(2026, 12750.01, 13250, 650, '₱12,750 - ₱13,249'),
(2026, 13250.01, 13750, 675, '₱13,250 - ₱13,749'),
(2026, 13750.01, 14250, 700, '₱13,750 - ₱14,249'),
(2026, 14250.01, 14750, 725, '₱14,250 - ₱14,749'),
(2026, 14750.01, 15250, 750, '₱14,750 - ₱15,249'),
(2026, 15250.01, 15750, 775, '₱15,250 - ₱15,749'),
(2026, 15750.01, 16250, 800, '₱15,750 - ₱16,249'),
(2026, 16250.01, 16750, 825, '₱16,250 - ₱16,749'),
(2026, 16750.01, 17250, 850, '₱16,750 - ₱17,249'),
(2026, 17250.01, 17750, 875, '₱17,250 - ₱17,749'),
(2026, 17750.01, 18250, 900, '₱17,750 - ₱18,249'),
(2026, 18250.01, 18750, 925, '₱18,250 - ₱18,749'),
(2026, 18750.01, 19250, 950, '₱18,750 - ₱19,249'),
(2026, 19250.01, 19750, 975, '₱19,250 - ₱19,749'),
(2026, 19750.01, NULL, 1000, '₱19,750 and above');

INSERT IGNORE INTO `philhealth_table` (`year`, `salary_from`, `salary_to`, `contribution_rate`, `maximum_contribution`, `fixed_amount`, `description`) VALUES
(2026, 0, 10000, 0.0275, 275, 275, 'Up to ₱10,000'),
(2026, 10000.01, 49999, 0.0275, 1375, NULL, '₱10,000 - ₱49,999'),
(2026, 50000, NULL, 0.0275, 1375, NULL, '₱50,000 and above');

INSERT IGNORE INTO `pagibig_table` (`year`, `salary_from`, `salary_to`, `contribution_rate`, `maximum_contribution`, `description`) VALUES
(2026, 0, 1500, 0.01, 15, 'Up to ₱1,500'),
(2026, 1500.01, 10000, 0.02, 200, '₱1,500 - ₱10,000'),
(2026, 10000.01, NULL, 0.02, 200, '₱10,000 and above');
