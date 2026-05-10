-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 10, 2026 at 06:37 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

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

--
-- Dumping data for table `deleted_geofences`
--

INSERT INTO `deleted_geofences` (`id`, `original_id`, `name`, `coordinates`, `created_at`, `deleted_at`) VALUES
(0, 6, 'di ko alam kung saan ito', '[[14.600230597140046,120.98389041765269],[14.599789071415348,120.98381533860491],[14.599742321580852,120.98414783153085],[14.60016826415028,120.98423363615692],[14.600230597140046,120.98389041765269]]', '2026-05-10 15:56:48', '2026-05-10 15:56:51');

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

CREATE TABLE `employees` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `type` varchar(50) NOT NULL DEFAULT 'Emp',
  `position` varchar(255) NOT NULL,
  `department` varchar(255) NOT NULL,
  `salary` decimal(15,2) NOT NULL DEFAULT 0.00,
  `join_date` date NOT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'Active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employees`
--

INSERT INTO `employees` (`id`, `name`, `email`, `username`, `password`, `type`, `position`, `department`, `salary`, `join_date`, `status`) VALUES
(3, 'Romer Navoa', 'romer.navoa09@gmail.com', 'Romer', '123', 'HR', 'Director', 'CpE', 123456.00, '2004-09-26', 'Active'),
(4, 'Alexis Eron', 'axiserondc@gmail.com', 'Alexis', '123', 'Emp', 'Chief Operating Office', 'CpE', 45000.50, '2026-04-09', 'Active'),
(6, 'Justine Simone Garcia', 'justingarcia@adamson.edu.ph', 'Justine', '123', 'Emp', 'ewan ko', 'CpE', 123456.00, '2025-09-10', 'Active'),
(7, 'Demetri Mayor', 'demetri@yahoo.com', 'Demetri', '123', 'Emp', 'Baka', 'CpE', 123656.00, '2026-04-09', 'Active'),
(8, 'Jeremiah Guarino', 'palemlem@gmail.com', 'Jeremiah', '123', 'Emp', 'Doggy', 'CpE', 123456.00, '2026-04-09', 'Active'),
(9, 'Joseph Mayor', 'joseph@yahoo.com', 'Joseph', '123', 'Emp', 'asdfgsdfa', 'adfs', 1234.00, '2001-12-31', 'Active'),
(10, 'Simone Factor', 'factor@simone.com', 'Simone', '123', 'Emp', 'Super Adik', 'PDEA', 1236.00, '2001-03-31', 'Active'),
(11, 'Ken Axel Quanico', '12343556kenaxelquanico@gmail.com', 'Ken', '123', 'Emp', 'Yearner', 'CpE', 67667.00, '2003-12-31', 'Active');

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
(64, 4, '2026-04-01', 'Adamson OZ', '[[14.586672425819803,120.98653078079224],[14.586205189341426,120.98669171333314],[14.586257104554646,120.9868633747101],[14.586324594313552,120.98682582378389],[14.586511488922529,120.98669171333314],[14.586636085240368,120.98710477352144],[14.58729021475', '8:00 AM', '5:00 PM', 'On-time', 'Present', 480, 0),
(65, 4, '2026-04-02', 'Adamson OZ', '[[14.586672425819803,120.98653078079224],[14.586205189341426,120.98669171333314],[14.586257104554646,120.9868633747101],[14.586324594313552,120.98682582378389],[14.586511488922529,120.98669171333314],[14.586636085240368,120.98710477352144],[14.58729021475', '8:01 AM', '5:00 PM', 'Late', 'Present', 479, 0),
(66, 4, '2026-04-03', 'Adamson OZ', '[[14.586672425819803,120.98653078079224],[14.586205189341426,120.98669171333314],[14.586257104554646,120.9868633747101],[14.586324594313552,120.98682582378389],[14.586511488922529,120.98669171333314],[14.586636085240368,120.98710477352144],[14.58729021475', '8:00 AM', '5:00 PM', 'On-time', 'Present', 480, 0),
(67, 4, '2026-04-04', 'Adamson OZ', '[[14.586672425819803,120.98653078079224],[14.586205189341426,120.98669171333314],[14.586257104554646,120.9868633747101],[14.586324594313552,120.98682582378389],[14.586511488922529,120.98669171333314],[14.586636085240368,120.98710477352144],[14.58729021475', '8:00 AM', '6:00 PM', 'On-time', 'Over-time', 480, 0),
(68, 4, '2026-04-05', 'Adamson OZ', '[[14.586672425819803,120.98653078079224],[14.586205189341426,120.98669171333314],[14.586257104554646,120.9868633747101],[14.586324594313552,120.98682582378389],[14.586511488922529,120.98669171333314],[14.586636085240368,120.98710477352144],[14.58729021475', '8:00 AM', '5:36 PM', 'On-time', 'Over-time', 480, 0),
(69, 4, '2026-04-06', 'Adamson OZ', '[[14.586672425819803,120.98653078079224],[14.586205189341426,120.98669171333314],[14.586257104554646,120.9868633747101],[14.586324594313552,120.98682582378389],[14.586511488922529,120.98669171333314],[14.586636085240368,120.98710477352144],[14.58729021475', '6:02 AM', '5:39 PM', 'On-time', 'Over-time', 598, 0),
(70, 4, '2026-04-07', 'Adamson OZ', '[[14.586672425819803,120.98653078079224],[14.586205189341426,120.98669171333314],[14.586257104554646,120.9868633747101],[14.586324594313552,120.98682582378389],[14.586511488922529,120.98669171333314],[14.586636085240368,120.98710477352144],[14.58729021475', '8:00 AM', '7:41 PM', 'On-time', 'Over-time', 641, 0),
(71, 4, '2026-04-08', 'Adamson OZ', '[[14.586672425819803,120.98653078079224],[14.586205189341426,120.98669171333314],[14.586257104554646,120.9868633747101],[14.586324594313552,120.98682582378389],[14.586511488922529,120.98669171333314],[14.586636085240368,120.98710477352144],[14.58729021475', '7:00 AM', '9:25 PM', 'On-time', 'Over-time', 805, 1),
(83, 4, '2026-04-22', 'Adamson OZ', '[[14.586672425819803,120.98653078079224],[14.586205189341426,120.98669171333314],[14.586257104554646,120.9868633747101],[14.586324594313552,120.98682582378389],[14.586511488922529,120.98669171333314],[14.586636085240368,120.98710477352144],[14.58729021475', '8:00 AM', '7:00 PM', 'On-time', 'Over-time', 480, 0),
(84, 4, '2026-04-22', 'Adamson OZ', '[[14.586672425819803,120.98653078079224],[14.586205189341426,120.98669171333314],[14.586257104554646,120.9868633747101],[14.586324594313552,120.98682582378389],[14.586511488922529,120.98669171333314],[14.586636085240368,120.98710477352144],[14.58729021475', '8:00 AM', '6:00 PM', 'On-time', 'Over-time', 480, 0);

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
(4, 3, 4),
(5, 11, 1),
(6, 9, 1),
(7, 7, 1);

-- --------------------------------------------------------

--
-- Table structure for table `emp_deduc_type`
--

CREATE TABLE `emp_deduc_type` (
  `id` int(10) UNSIGNED NOT NULL,
  `type_of_deduction` varchar(255) NOT NULL,
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
(3, 'BahayniAno', '[[14.620233524081927,121.08998746908762],[14.620103839130735,121.09050236979236],[14.620643328024464,121.09065791271361],[14.620627765863413,121.09074909304674],[14.620715951428197,121.09077591079175],[14.620570704596721,121.09140880957469],[14.621514807284294,121.09165553282902],[14.621628929312259,121.09102799759515],[14.62134362413116,121.09093681726203],[14.621359186241513,121.09060964077251],[14.62092344673508,121.09047018849832],[14.62098569528893,121.09018592040093],[14.620233524081927,121.08998746908762]]', '2026-04-12 07:15:05', NULL),
(4, 'JAPAN', '[[36.70451976337016,138.57330322265628],[36.682496478209124,138.49914550781253],[36.634023050101284,138.54309082031253],[36.634023050101284,138.60076904296878],[36.6604668854353,138.63372802734378],[36.66707642548053,138.66943359375003],[36.71773070621055,138.65295410156253],[36.70451976337016,138.57330322265628]]', '2026-05-10 09:53:08', NULL),
(5, 'SV', '[[14.585584233771892,120.98531842231752],[14.585412912998242,120.98493218421937],[14.585267549813043,120.98500728607179],[14.585397338375856,120.9853881597519],[14.585584233771892,120.98531842231752]]', '2026-05-10 09:55:01', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `hr_attendance_settings`
--

CREATE TABLE `hr_attendance_settings` (
  `id` int(11) NOT NULL,
  `Manual_mode` tinyint(1) NOT NULL,
  `Hide_attendance_id` tinyint(1) NOT NULL,
  `Hide_employee_id` tinyint(1) NOT NULL,
  `Hide_name` tinyint(1) NOT NULL,
  `Hide_department` tinyint(1) NOT NULL,
  `Hide_date` tinyint(1) NOT NULL,
  `Hide_locations` tinyint(1) NOT NULL,
  `Hide_clockin` tinyint(1) NOT NULL,
  `Hide_clockinstatus` tinyint(1) NOT NULL,
  `Hide_clockout` tinyint(1) NOT NULL,
  `Hide_clockoutstatus` tinyint(1) NOT NULL,
  `Hide_duration` tinyint(1) NOT NULL,
  `Hide_AO` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `hr_attendance_settings`
--

INSERT INTO `hr_attendance_settings` (`id`, `Manual_mode`, `Hide_attendance_id`, `Hide_employee_id`, `Hide_name`, `Hide_department`, `Hide_date`, `Hide_locations`, `Hide_clockin`, `Hide_clockinstatus`, `Hide_clockout`, `Hide_clockoutstatus`, `Hide_duration`, `Hide_AO`) VALUES
(1, 0, 1, 1, 0, 1, 0, 1, 0, 0, 0, 0, 0, 1);

-- --------------------------------------------------------

--
-- Table structure for table `otp_tokens`
--

CREATE TABLE `otp_tokens` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `user_email` varchar(255) NOT NULL,
  `otp_code` varchar(10) NOT NULL,
  `is_verified` tinyint(1) DEFAULT 0,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `verified_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `otp_tokens`
--

INSERT INTO `otp_tokens` (`id`, `user_id`, `user_email`, `otp_code`, `is_verified`, `expires_at`, `created_at`, `verified_at`) VALUES
(2, 3, 'romer.navoa09@gmail.com', '448330', 0, '2026-05-10 18:45:32', '2026-05-10 16:35:32', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `pagibig_table`
--

CREATE TABLE `pagibig_table` (
  `id` int(10) UNSIGNED NOT NULL,
  `year` int(11) NOT NULL,
  `salary_from` decimal(15,2) NOT NULL,
  `salary_to` decimal(15,2) DEFAULT NULL,
  `contribution_rate` decimal(5,4) NOT NULL,
  `maximum_contribution` decimal(10,2) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `password_reset_tokens`
--

INSERT INTO `password_reset_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES
(1, 3, '34293d91f9c1f8627bdcd5e97ad2fa889bb9398764b8da1fc2845e2e2df7d1d2', '2026-05-10 18:57:37', '2026-05-10 15:57:37'),
(2, 3, '784538dbc9c0b594531b7e0266623ddf215f7afd7d0a4b761acec67b6de08a34', '2026-05-10 18:58:31', '2026-05-10 15:58:31'),
(3, 3, '9788e9ed9ff38277192942975003f92ab81f07afb3a72cdd52e44e2dcba8624c', '2026-05-10 19:13:29', '2026-05-10 16:13:29'),
(4, 3, 'f446ef37f9c31a2a50b937c925e8983cec1b2bc0f865a88a957a27c717af4bdf', '2026-05-10 19:17:18', '2026-05-10 16:17:18'),
(5, 3, 'c566cedafd75a782872b5c1753b9b5bf837b45f1e164ed0fe65b8ff94ea5fbba', '2026-05-10 19:19:39', '2026-05-10 16:19:39');

-- --------------------------------------------------------

--
-- Table structure for table `philhealth_table`
--

CREATE TABLE `philhealth_table` (
  `id` int(10) UNSIGNED NOT NULL,
  `year` int(11) NOT NULL,
  `salary_from` decimal(15,2) NOT NULL,
  `salary_to` decimal(15,2) DEFAULT NULL,
  `contribution_rate` decimal(5,4) NOT NULL,
  `maximum_contribution` decimal(10,2) NOT NULL,
  `fixed_amount` decimal(10,2) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `premiums`
--

CREATE TABLE `premiums` (
  `id` int(10) UNSIGNED NOT NULL,
  `employee_id` int(10) UNSIGNED NOT NULL,
  `employee_name` varchar(255) NOT NULL,
  `sss` decimal(15,2) NOT NULL DEFAULT 0.00,
  `philhealth` decimal(15,2) NOT NULL DEFAULT 0.00,
  `pagibig` decimal(15,2) NOT NULL DEFAULT 0.00,
  `withholding_tax` decimal(15,2) NOT NULL DEFAULT 0.00,
  `total_premium` decimal(15,2) NOT NULL DEFAULT 0.00,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `premiums`
--

INSERT INTO `premiums` (`id`, `employee_id`, `employee_name`, `sss`, `philhealth`, `pagibig`, `withholding_tax`, `total_premium`, `created_at`, `updated_at`) VALUES
(1, 3, 'Romer Navoa', 1750.00, 1375.00, 200.00, 21907.80, 25232.80, '2026-04-13 14:55:55', '2026-04-13 14:55:55'),
(2, 4, 'Alexis Eron', 1750.00, 1237.51, 200.00, 3571.00, 6758.51, '2026-04-13 14:55:55', '2026-04-13 14:55:55'),
(3, 5, 'Romer Navoa', 1750.00, 1375.00, 200.00, 16043.80, 19368.80, '2026-04-13 14:55:55', '2026-04-13 14:55:55'),
(4, 6, 'Justine Simone Garcia', 1750.00, 1375.00, 200.00, 21907.80, 25232.80, '2026-04-13 14:55:55', '2026-04-13 14:55:55'),
(5, 7, 'Demetri Mayor', 1750.00, 1375.00, 200.00, 21957.80, 25282.80, '2026-04-13 14:55:55', '2026-04-13 14:55:55'),
(6, 8, 'Jeremiah Guarino', 1750.00, 1375.00, 200.00, 21907.80, 25232.80, '2026-04-13 14:55:55', '2026-04-13 14:55:55'),
(7, 9, 'Joseph Mayor', 250.00, 275.00, 12.34, 0.00, 537.34, '2026-04-13 14:55:55', '2026-04-13 14:55:55'),
(8, 10, 'Simone Factor', 250.00, 275.00, 12.36, 0.00, 537.36, '2026-04-13 14:55:55', '2026-04-13 14:55:55'),
(9, 11, 'Ken Axel Quanico', 1750.00, 1375.00, 200.00, 185913.05, 189238.05, '2026-04-13 14:55:55', '2026-04-13 14:55:55'),
(10, 12, 'Eron De La Cruz', 1750.00, 1375.00, 200.00, 22105.05, 25430.05, '2026-04-13 14:55:55', '2026-04-13 14:55:55'),
(11, 13, 'Factor Bread', 250.00, 275.00, 6.89, 0.00, 531.89, '2026-04-13 14:55:55', '2026-04-13 14:55:55'),
(12, 14, 'Simone The Factor', 625.00, 340.70, 200.00, 0.00, 1165.70, '2026-04-13 14:55:55', '2026-04-13 14:55:55');

-- --------------------------------------------------------

--
-- Table structure for table `sss_table`
--

CREATE TABLE `sss_table` (
  `id` int(10) UNSIGNED NOT NULL,
  `year` int(11) NOT NULL,
  `salary_from` decimal(15,2) NOT NULL,
  `salary_to` decimal(15,2) DEFAULT NULL,
  `monthly_contribution` decimal(10,2) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tax_table`
--

CREATE TABLE `tax_table` (
  `id` int(10) UNSIGNED NOT NULL,
  `year` int(11) NOT NULL,
  `income_from` decimal(15,2) NOT NULL,
  `income_to` decimal(15,2) DEFAULT NULL,
  `tax_rate` decimal(5,2) NOT NULL,
  `base_tax` decimal(15,2) DEFAULT 0.00,
  `description` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `User_id` int(10) UNSIGNED NOT NULL,
  `Username` varchar(255) NOT NULL,
  `Password` varchar(255) NOT NULL,
  `Type` varchar(255) NOT NULL,
  `Clockin_status` varchar(255) NOT NULL,
  `Work_status` varchar(255) NOT NULL DEFAULT 'Tapped-out'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`User_id`, `Username`, `Password`, `Type`, `Clockin_status`, `Work_status`) VALUES
(3, 'Romer', '123', 'HR', 'Tapped-out', 'Tapped-out'),
(4, 'Alexis', '123', 'Emp', 'Tapped-out', 'Tapped-out'),
(6, 'Justine', '123', 'Emp', 'Tapped-out', 'Tapped-out'),
(7, 'Demetri', '123', 'Emp', 'Tapped-out', 'Tapped-out'),
(8, 'Jeremiah', '123', 'Emp', 'Tapped-out', 'Tapped-out'),
(9, 'Joseph', '123', 'Emp', 'Tapped-out', 'Tapped-out'),
(10, 'Simone', '123', 'Emp', 'Tapped-out', 'Tapped-out'),
(11, 'Ken', '123', 'Emp', 'Tapped-out', 'Tapped-out');

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
-- Indexes for table `hr_attendance_settings`
--
ALTER TABLE `hr_attendance_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `otp_tokens`
--
ALTER TABLE `otp_tokens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_email` (`user_email`),
  ADD KEY `idx_otp_code` (`otp_code`),
  ADD KEY `idx_expires` (`expires_at`);

--
-- Indexes for table `pagibig_table`
--
ALTER TABLE `pagibig_table`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_year` (`year`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_token` (`token`),
  ADD KEY `idx_expires` (`expires_at`);

--
-- Indexes for table `philhealth_table`
--
ALTER TABLE `philhealth_table`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_year` (`year`);

--
-- Indexes for table `premiums`
--
ALTER TABLE `premiums`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_employee_id` (`employee_id`);

--
-- Indexes for table `sss_table`
--
ALTER TABLE `sss_table`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_year` (`year`);

--
-- Indexes for table `tax_table`
--
ALTER TABLE `tax_table`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_year` (`year`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD KEY `users_ibfk_1` (`User_id`);

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
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `employee_attendance`
--
ALTER TABLE `employee_attendance`
  MODIFY `Attendance_id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=85;

--
-- AUTO_INCREMENT for table `employee_location`
--
ALTER TABLE `employee_location`
  MODIFY `tb_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

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
  MODIFY `id` bigint(255) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `hr_attendance_settings`
--
ALTER TABLE `hr_attendance_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `otp_tokens`
--
ALTER TABLE `otp_tokens`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `pagibig_table`
--
ALTER TABLE `pagibig_table`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `philhealth_table`
--
ALTER TABLE `philhealth_table`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `premiums`
--
ALTER TABLE `premiums`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=69;

--
-- AUTO_INCREMENT for table `sss_table`
--
ALTER TABLE `sss_table`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tax_table`
--
ALTER TABLE `tax_table`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

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
-- Constraints for table `otp_tokens`
--
ALTER TABLE `otp_tokens`
  ADD CONSTRAINT `otp_fk_user` FOREIGN KEY (`user_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD CONSTRAINT `prt_fk_user` FOREIGN KEY (`user_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`User_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
