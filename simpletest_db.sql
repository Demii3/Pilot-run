-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 12, 2026 at 03:04 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `simpletest_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `assigned_emp_deduc`
--

CREATE TABLE `assigned_emp_deduc` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `type_of_deduction` varchar(255) NOT NULL,
  `cost` decimal(15,2) NOT NULL DEFAULT 0.00,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `assigned_emp_inc`
--

CREATE TABLE `assigned_emp_inc` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `type_of_income` varchar(255) NOT NULL,
  `cost` decimal(15,2) NOT NULL DEFAULT 0.00,
  `taxable` tinyint(1) NOT NULL DEFAULT 0,
  `month_13th` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `deleted_geofences`
--

CREATE TABLE `deleted_geofences` (
  `id` int(10) UNSIGNED NOT NULL,
  `original_id` int(10) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `coordinates` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

CREATE TABLE `employees` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `type` varchar(50) NOT NULL DEFAULT 'Emp',
  `position` varchar(255) NOT NULL,
  `department` varchar(255) NOT NULL,
  `salary` decimal(15,2) NOT NULL DEFAULT 0.00,
  `join_date` date NOT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'Active',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employees`
--

INSERT INTO `employees` (`id`, `name`, `email`, `username`, `password`, `type`, `position`, `department`, `salary`, `join_date`, `status`) VALUES
(3, 'Romer Navoa', 'romer.navoa@gmail.com', 'Romer', '123', 'HR', 'Director', 'CpE', 123456.00, '2004-09-26', 'Active'),
(4, 'Alexis Eron', 'axiserondc@gmail.com', 'Alexis', '123', 'Emp', 'Chief Operating Office', 'CpE', 45000.50, '2026-04-09', 'Active'),
(5, 'Romer Navoa', 'romer.navoa09@gmail.com', 'Omer', '123', 'Emp', 'Sex', 'Office', 100000.00, '2025-12-29', 'Inactive'),
(6, 'Justine Simone Garcia', 'justingarcia@adamson.edu.ph', 'Justine', '123', 'Emp', 'ewan ko', 'CpE', 123456.00, '2025-09-10', 'Active'),
(7, 'Demetri Mayor', 'demetri@yahoo.com', 'Demetri', '123', 'Emp', 'Baka', 'CpE', 123656.00, '2026-04-09', 'Active'),
(8, 'Jeremiah Guarino', 'palemlem@gmail.com', 'Jeremiah', '123', 'Emp', 'Doggy', 'CpE', 123456.00, '2026-04-09', 'Active'),
(9, 'Joseph Mayor', 'joseph@yahoo.com', 'Joseph', '123', 'Emp', 'asdfgsdfa', 'adfs', 1234.00, '2001-12-31', 'Active'),
(10, 'Simone Factor', 'factor@simone.com', 'Simone', '123', 'Emp', 'adik', 'PDEA', 1236.00, '2001-03-31', 'Active'),
(11, 'Ken Axel Quanico', '12343556kenaxelquanico@gmail.com', 'Ken', '123', 'Emp', 'Yearner', 'CpE', 676767.00, '2003-12-31', 'Active'),
(12, 'Eron De La Cruz', 'eronski@gmail.com', 'Eron', '123', 'Emp', 'Bully', 'CpE', 124245.00, '2026-04-09', 'Active'),
(13, 'Factor Bread', 'breadfactor@gmail.com', 'Factor', '123', 'Emp', 'Sakit sa Ulo', 'CpE', 689.00, '2026-04-09', 'Active'),
(14, 'Simone The Factor', 'simonef@gmail.com', 'Simone', '123', 'Emp', 'kahit ano', 'cpe', 12389.00, '2003-09-12', 'Active');

-- --------------------------------------------------------

--
-- Table structure for table `employee_attendance`
--

CREATE TABLE `employee_attendance` (
  `Attendance_id` bigint(20) NOT NULL,
  `Emp_id` int(10) UNSIGNED NOT NULL,
  `Date` date NOT NULL,
  `Location` varchar(255) NOT NULL,
  `Coordinates` varchar(255) NOT NULL,
  `Clock_in` varchar(255) NOT NULL,
  `Clock_out` varchar(255) NOT NULL,
  `Clockin_status` varchar(255) NOT NULL,
  `Clockout_status` varchar(255) NOT NULL,
  `Duration` float NOT NULL,
  `AO` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employee_attendance`
--

INSERT INTO `employee_attendance` (`Attendance_id`, `Emp_id`, `Date`, `Location`, `Coordinates`, `Clock_in`, `Clock_out`, `Clockin_status`, `Clockout_status`, `Duration`, `AO`) VALUES
(64, 4, '2026-04-01', 'Adamson OZ', '[[14.586672425819803,120.98653078079224],[14.586205189341426,120.98669171333314],[14.586257104554646,120.9868633747101],[14.586324594313552,120.98682582378389],[14.586511488922529,120.98669171333314],[14.586636085240368,120.98710477352144],[14.58729021475', '08:00:00 AM', '05:00:00 PM', 'On-leave', 'Present', 480, 0),
(65, 4, '2026-04-02', 'Adamson OZ', '[[14.586672425819803,120.98653078079224],[14.586205189341426,120.98669171333314],[14.586257104554646,120.9868633747101],[14.586324594313552,120.98682582378389],[14.586511488922529,120.98669171333314],[14.586636085240368,120.98710477352144],[14.58729021475', '08:00:00 AM', '05:00:00 PM', 'On-time', 'Present', 480, 0),
(66, 4, '2026-04-03', 'Adamson OZ', '[[14.586672425819803,120.98653078079224],[14.586205189341426,120.98669171333314],[14.586257104554646,120.9868633747101],[14.586324594313552,120.98682582378389],[14.586511488922529,120.98669171333314],[14.586636085240368,120.98710477352144],[14.58729021475', '08:00:00 AM', '05:00:00 PM', 'On-time', 'Present', 480, 0),
(67, 4, '2026-04-04', 'Adamson OZ', '[[14.586672425819803,120.98653078079224],[14.586205189341426,120.98669171333314],[14.586257104554646,120.9868633747101],[14.586324594313552,120.98682582378389],[14.586511488922529,120.98669171333314],[14.586636085240368,120.98710477352144],[14.58729021475', '08:00:00 AM', '05:00:00 PM', 'On-time', 'Present', 480, 0),
(68, 4, '2026-04-05', 'Adamson OZ', '[[14.586672425819803,120.98653078079224],[14.586205189341426,120.98669171333314],[14.586257104554646,120.9868633747101],[14.586324594313552,120.98682582378389],[14.586511488922529,120.98669171333314],[14.586636085240368,120.98710477352144],[14.58729021475', '08:00:00 AM', '05:36:10 PM', 'On-time', 'Over-time (Rejected)', 516, 0),
(69, 4, '2026-04-06', 'Adamson OZ', '[[14.586672425819803,120.98653078079224],[14.586205189341426,120.98669171333314],[14.586257104554646,120.9868633747101],[14.586324594313552,120.98682582378389],[14.586511488922529,120.98669171333314],[14.586636085240368,120.98710477352144],[14.58729021475', '08:00:00 AM', '05:39:22 PM', 'On-time', 'Over-time (Allowed)', 519, 1),
(70, 4, '2026-04-07', 'Adamson OZ', '[[14.586672425819803,120.98653078079224],[14.586205189341426,120.98669171333314],[14.586257104554646,120.9868633747101],[14.586324594313552,120.98682582378389],[14.586511488922529,120.98669171333314],[14.586636085240368,120.98710477352144],[14.58729021475', '08:00:00 AM', '06:41:04 PM', 'On-time', 'Over-time (Allowed)', 581, 1),
(71, 4, '2026-04-08', 'Adamson OZ', '[[14.586672425819803,120.98653078079224],[14.586205189341426,120.98669171333314],[14.586257104554646,120.9868633747101],[14.586324594313552,120.98682582378389],[14.586511488922529,120.98669171333314],[14.586636085240368,120.98710477352144],[14.58729021475', '08:00:00 AM', '09:25:16 PM', 'On-time', 'Over-time (Rejected)', 745, 0),
(72, 4, '2026-04-09', 'Adamson OZ', '[[14.586672425819803,120.98653078079224],[14.586205189341426,120.98669171333314],[14.586257104554646,120.9868633747101],[14.586324594313552,120.98682582378389],[14.586511488922529,120.98669171333314],[14.586636085240368,120.98710477352144],[14.58729021475', '08:00:00 AM', '05:00:00 PM', 'On-time', 'Present', 480, 0),
(73, 4, '2026-04-10', 'Adamson OZ', '[[14.586672425819803,120.98653078079224],[14.586205189341426,120.98669171333314],[14.586257104554646,120.9868633747101],[14.586324594313552,120.98682582378389],[14.586511488922529,120.98669171333314],[14.586636085240368,120.98710477352144],[14.58729021475', '08:00:00 AM', '07:05:25 PM', 'On-time', 'Over-time (Allowed)', 605, 1),
(74, 4, '2026-04-11', 'Adamson OZ', '[[14.586672425819803,120.98653078079224],[14.586205189341426,120.98669171333314],[14.586257104554646,120.9868633747101],[14.586324594313552,120.98682582378389],[14.586511488922529,120.98669171333314],[14.586636085240368,120.98710477352144],[14.58729021475', '08:00:00 AM', '07:05:28 PM', 'On-time', 'Over-time (Allowed)', 605, 1),
(75, 4, '2026-04-12', 'Adamson OZ', '[[14.586672425819803,120.98653078079224],[14.586205189341426,120.98669171333314],[14.586257104554646,120.9868633747101],[14.586324594313552,120.98682582378389],[14.586511488922529,120.98669171333314],[14.586636085240368,120.98710477352144],[14.58729021475', '7:05:34 PM', '7:05:35 PM', 'Late', 'Over-time', 0, 0),
(76, 4, '2026-04-13', 'Adamson OZ', '[[14.586672425819803,120.98653078079224],[14.586205189341426,120.98669171333314],[14.586257104554646,120.9868633747101],[14.586324594313552,120.98682582378389],[14.586511488922529,120.98669171333314],[14.586636085240368,120.98710477352144],[14.58729021475', '08:00:00 AM', '07:05:36 PM', 'On-time', 'Over-time (Rejected)', 605, 0),
(77, 4, '2026-04-14', 'Adamson OZ', '[[14.586672425819803,120.98653078079224],[14.586205189341426,120.98669171333314],[14.586257104554646,120.9868633747101],[14.586324594313552,120.98682582378389],[14.586511488922529,120.98669171333314],[14.586636085240368,120.98710477352144],[14.58729021475', '08:00:00 AM', '07:05:38 PM', 'On-time', 'Over-time (Allowed)', 605, 1),
(78, 4, '2026-04-15', 'Adamson OZ', '[[14.586672425819803,120.98653078079224],[14.586205189341426,120.98669171333314],[14.586257104554646,120.9868633747101],[14.586324594313552,120.98682582378389],[14.586511488922529,120.98669171333314],[14.586636085240368,120.98710477352144],[14.58729021475', '08:00:00 AM', '07:05:39 PM', 'On-time', 'Over-time (Rejected)', 605, 0),
(79, 4, '2026-04-16', 'Adamson OZ', '[[14.586672425819803,120.98653078079224],[14.586205189341426,120.98669171333314],[14.586257104554646,120.9868633747101],[14.586324594313552,120.98682582378389],[14.586511488922529,120.98669171333314],[14.586636085240368,120.98710477352144],[14.58729021475', '08:00:00 AM', '05:00:00 PM', 'On-time', 'Present', 480, 0),
(80, 4, '2026-04-17', 'Adamson OZ', '[[14.586672425819803,120.98653078079224],[14.586205189341426,120.98669171333314],[14.586257104554646,120.9868633747101],[14.586324594313552,120.98682582378389],[14.586511488922529,120.98669171333314],[14.586636085240368,120.98710477352144],[14.58729021475', '08:00:00 AM', '05:00:00 PM', 'On-leave', 'Present', 480, 0),
(81, 4, '2026-04-18', 'Adamson OZ', '[[14.586672425819803,120.98653078079224],[14.586205189341426,120.98669171333314],[14.586257104554646,120.9868633747101],[14.586324594313552,120.98682582378389],[14.586511488922529,120.98669171333314],[14.586636085240368,120.98710477352144],[14.58729021475', '08:00:00 AM', '07:05:51 PM', 'On-time', 'Over-time (Rejected)', 605, 0),
(82, 4, '2026-04-12', 'Adamson OZ', '[[14.586672425819803,120.98653078079224],[14.586205189341426,120.98669171333314],[14.586257104554646,120.9868633747101],[14.586324594313552,120.98682582378389],[14.586511488922529,120.98669171333314],[14.586636085240368,120.98710477352144],[14.58729021475', '08:00:00 AM', '02:05:52 PM', 'On-time', 'Under-time', 305, 0);

-- --------------------------------------------------------

--
-- Table structure for table `employee_location`
--

CREATE TABLE `employee_location` (
  `tb_id` int(10) NOT NULL,
  `User_Id` int(10) UNSIGNED NOT NULL,
  `loc_id` bigint(255) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employee_location`
--

INSERT INTO `employee_location` (`tb_id`, `User_Id`, `loc_id`) VALUES
(1, 4, 1),
(2, 4, 2),
(3, 5, 2);

-- --------------------------------------------------------

--
-- Table structure for table `emp_deduc_type`
--

CREATE TABLE `emp_deduc_type` (
  `id` int(10) UNSIGNED NOT NULL,
  `type_of_deduction` varchar(255) NOT NULL,
  `cost` decimal(15,2) NOT NULL DEFAULT 0.00,
  `taxable` tinyint(1) NOT NULL DEFAULT 1,
  `included_in_13month` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `emp_inc_type`
--

CREATE TABLE `emp_inc_type` (
  `id` int(10) UNSIGNED NOT NULL,
  `type_of_income` varchar(255) NOT NULL,
  `cost` decimal(15,2) NOT NULL DEFAULT 0.00,
  `taxable` tinyint(1) NOT NULL DEFAULT 1,
  `included_in_13month` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `geofences`
--

CREATE TABLE `geofences` (
  `id` bigint(255) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `coordinates` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `geofences`
--

INSERT INTO `geofences` (`id`, `name`, `coordinates`, `created_at`, `updated_at`) VALUES
(1, 'Adamson OZ', '[[14.586672425819803,120.98653078079224],[14.586205189341426,120.98669171333314],[14.586257104554646,120.9868633747101],[14.586324594313552,120.98682582378389],[14.586511488922529,120.98669171333314],[14.586636085240368,120.98710477352144],[14.587290214752265,120.98689019680025],[14.587269448765882,120.98681509494783],[14.58708255480032,120.98672389984132],[14.586765872996489,120.98683655261995],[14.586672425819803,120.98653078079224]]', '2026-04-04 15:49:22', NULL),
(2, 'SM', '[[14.652350801029067,120.99225997924806],[14.643880624522094,120.99123001098634],[14.645375385333589,121.0001564025879],[14.65334727086327,120.99998474121095],[14.652350801029067,120.99225997924806]]', '2026-04-06 11:25:49', NULL),
(3, 'BahayniAno', '[[14.620233524081927,121.08998746908762],[14.620103839130735,121.09050236979236],[14.620643328024464,121.09065791271361],[14.620627765863413,121.09074909304674],[14.620715951428197,121.09077591079175],[14.620570704596721,121.09140880957469],[14.621514807284294,121.09165553282902],[14.621628929312259,121.09102799759515],[14.62134362413116,121.09093681726203],[14.621359186241513,121.09060964077251],[14.62092344673508,121.09047018849832],[14.62098569528893,121.09018592040093],[14.620233524081927,121.08998746908762]]', '2026-04-12 07:15:05', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `User_id` int(10) UNSIGNED NOT NULL,
  `Username` varchar(255) NOT NULL,
  `Password` varchar(255) NOT NULL,
  `Type` varchar(255) NOT NULL,
  `Clockin_status` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`User_id`, `Username`, `Password`, `Type`, `Clockin_status`) VALUES
(3, 'Romer', '123', 'HR', 'Tapped-out'),
(4, 'Alexis', '123', 'Emp', 'Tapped-out'),
(5, 'Omer', '123', 'Emp', 'Tapped-out'),
(6, 'Justine', '123', 'Emp', 'Tapped-out'),
(7, 'Demetri', '123', 'Emp', 'Tapped-out'),
(8, 'Jeremiah', '123', 'Emp', 'Tapped-out'),
(9, 'Joseph', '123', 'Emp', 'Tapped-out'),
(10, 'Simone', '123', 'Emp', 'Tapped-out'),
(11, 'Ken', '123', 'Emp', 'Tapped-out'),
(12, 'Eron', '123', 'Emp', 'Tapped-out'),
(13, 'Factor', '123', 'Emp', 'Tapped-out'),
(14, 'Simone', '123', 'Emp', 'Tapped-out');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `assigned_emp_deduc`
--
ALTER TABLE `assigned_emp_deduc`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `assigned_emp_inc`
--
ALTER TABLE `assigned_emp_inc`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `deleted_geofences`
--
ALTER TABLE `deleted_geofences`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `employee_attendance`
--
ALTER TABLE `employee_attendance`
  ADD PRIMARY KEY (`Attendance_id`),
  ADD KEY `Emp_id` (`Emp_id`);

--
-- Indexes for table `employee_location`
--
ALTER TABLE `employee_location`
  ADD PRIMARY KEY (`tb_id`),
  ADD KEY `loc_id` (`loc_id`),
  ADD KEY `User_Id` (`User_Id`);

--
-- Indexes for table `emp_deduc_type`
--
ALTER TABLE `emp_deduc_type`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `emp_inc_type`
--
ALTER TABLE `emp_inc_type`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `geofences`
--
ALTER TABLE `geofences`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD KEY `User_id` (`User_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `assigned_emp_deduc`
--
ALTER TABLE `assigned_emp_deduc`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `assigned_emp_inc`
--
ALTER TABLE `assigned_emp_inc`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `employee_attendance`
--
ALTER TABLE `employee_attendance`
  MODIFY `Attendance_id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=83;

--
-- AUTO_INCREMENT for table `employee_location`
--
ALTER TABLE `employee_location`
  MODIFY `tb_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `emp_deduc_type`
--
ALTER TABLE `emp_deduc_type`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `emp_inc_type`
--
ALTER TABLE `emp_inc_type`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `geofences`
--
ALTER TABLE `geofences`
  MODIFY `id` bigint(255) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `employee_attendance`
--
ALTER TABLE `employee_attendance`
  ADD CONSTRAINT `employee_attendance_ibfk_1` FOREIGN KEY (`Emp_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `employee_location`
--
ALTER TABLE `employee_location`
  ADD CONSTRAINT `employee_location_ibfk_1` FOREIGN KEY (`loc_id`) REFERENCES `geofences` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `employee_location_ibfk_2` FOREIGN KEY (`User_Id`) REFERENCES `employees` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`User_id`) REFERENCES `employees` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

-- --------------------------------------------------------

-- Table structure for table `Assigned_Inc`

CREATE TABLE IF NOT EXISTS `Assigned_Inc` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

-- Table structure for table `Emp_Deduc_Type`

CREATE TABLE IF NOT EXISTS `Emp_Deduc_Type` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `type_of_deduction` VARCHAR(255) NOT NULL,
  `cost` DECIMAL(15,2) NOT NULL DEFAULT '0.00',
  `taxable` TINYINT(1) NOT NULL DEFAULT 1,
  `included_in_13month` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

-- Table structure for table `Emp_Inc_Type`

CREATE TABLE IF NOT EXISTS `Emp_Inc_Type` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `type_of_income` VARCHAR(255) NOT NULL,
  `cost` DECIMAL(15,2) NOT NULL DEFAULT '0.00',
  `taxable` TINYINT(1) NOT NULL DEFAULT 1,
  `included_in_13month` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

-- Table structure for table `premiums`

CREATE TABLE IF NOT EXISTS `premiums` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `employee_id` INT UNSIGNED NOT NULL,
  `employee_name` VARCHAR(255) NOT NULL,
  `sss` DECIMAL(15,2) NOT NULL DEFAULT '0.00',
  `philhealth` DECIMAL(15,2) NOT NULL DEFAULT '0.00',
  `pagibig` DECIMAL(15,2) NOT NULL DEFAULT '0.00',
  `withholding_tax` DECIMAL(15,2) NOT NULL DEFAULT '0.00',
  `total_premium` DECIMAL(15,2) NOT NULL DEFAULT '0.00',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_employee_id` (`employee_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
