-- Create table for employee to site assignment
CREATE TABLE IF NOT EXISTS `employee_location` (
  `tb_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `User_Id` int(10) unsigned NOT NULL,
  `loc_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`tb_id`),
  UNIQUE KEY `user_site_unique` (`User_Id`,`loc_id`),
  KEY `loc_id` (`loc_id`),
  CONSTRAINT `employee_location_user_fk` FOREIGN KEY (`User_Id`) REFERENCES `employees` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `employee_location_site_fk` FOREIGN KEY (`loc_id`) REFERENCES `geofences` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
