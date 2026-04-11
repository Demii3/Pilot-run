-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 10, 2026 at 04:36 PM
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
-- Table structure for table `employee`
--

CREATE TABLE `employee` (
  `Emp_id` bigint(255) NOT NULL,
  `Name` varchar(255) NOT NULL,
  `Position` varchar(255) NOT NULL,
  `Department` varchar(255) NOT NULL,
  `Salary` bigint(255) NOT NULL,
  `Status` varchar(255) NOT NULL,
  `Extra` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employee`
--

INSERT INTO `employee` (`Emp_id`, `Name`, `Position`, `Department`, `Salary`, `Status`, `Extra`) VALUES
(1, 'Joyce Bryce', 'HR', 'HR', 0, 'Active', ''),
(2, 'Bianca Mayor', 'Project Manager', 'Field', 0, 'Active', ''),
(3, 'Demetri Mayor', 'System Admin', 'HR', 0, 'Active', ''),
(5, 'Ai Cruz', 'Human Resources', 'HR', 0, 'Active', ''),
(6, 'Lou Reyes', 'Payroll', 'HR', 0, 'Inactive', ''),
(7, 'George Lopez', 'Recruitment', 'HR', 0, 'Active', ''),
(8, 'Tan Miller', 'Benifits', 'HR', 0, 'Inactive', ''),
(9, 'Carlo Tan', 'Project Manager', 'Field', 0, 'Inactive', '');

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
(3, 'Romer Navoa', 'romer.navoa@gmail.com', '', '', 'Emp', 'Director', 'CpE', 123456.00, '2004-09-26', 'Inactive'),
(4, 'Alexis Eron', 'axiserondc@gmail.com', '', '', 'Emp', 'Chief Operating Office', 'CpE', 45000.50, '2026-04-09', 'Active'),
(5, 'Romer Navoa', 'romer.navoa09@gmail.com', '', '', 'Emp', 'Sex', 'Office', 100000.00, '2025-12-29', 'Inactive'),
(6, 'Justine Simone Garcia', 'justingarcia@adamson.edu.ph', '', '', 'Emp', 'ewan ko', 'CpE', 123456.00, '2025-09-10', 'Active'),
(7, 'Demetri Mayor', 'demetri@yahoo.com', '', '', 'Emp', 'Baka', 'CpE', 123656.00, '2026-04-09', 'Active'),
(8, 'Jeremiah Guarino', 'palemlem@gmail.com', '', '', 'Emp', 'Doggy', 'CpE', 123456.00, '2026-04-09', 'Active'),
(9, 'Joseph Mayor', 'joseph@yahoo.com', '', '', 'Emp', 'asdfgsdfa', 'adfs', 1234.00, '2001-12-31', 'Active'),
(10, 'Simone Factor', 'factor@simone.com', '', '', 'Emp', 'adik', 'PDEA', 1236.00, '2001-03-31', 'Active'),
(11, 'Ken Axel Quanico', '12343556kenaxelquanico@gmail.com', '', '', 'Emp', 'Yearner', 'CpE', 676767.00, '2003-12-31', 'Active'),
(12, 'Eron De La Cruz', 'eronski@gmail.com', '', '', 'Emp', 'Bully', 'CpE', 124245.00, '2026-04-09', 'Active'),
(13, 'Factor Bread', 'breadfactor@gmail.com', '', '', 'Emp', 'Sakit sa Ulo', 'CpE', 689.00, '2026-04-09', 'Active'),
(14, 'Simone The Factor', 'simonef@gmail.com', '', '', 'Emp', 'kahit ano', 'cpe', 12389.00, '2003-09-12', 'Active');

-- --------------------------------------------------------

--
-- Table structure for table `employee_attendance`
--

CREATE TABLE `employee_attendance` (
  `Emp_id` bigint(255) NOT NULL,
  `Date` date NOT NULL,
  `Location` varchar(255) NOT NULL,
  `Clock_in` time NOT NULL,
  `Clock_out` time NOT NULL,
  `Status` varchar(255) NOT NULL,
  `Duration` float NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employee_attendance`
--

INSERT INTO `employee_attendance` (`Emp_id`, `Date`, `Location`, `Clock_in`, `Clock_out`, `Status`, `Duration`) VALUES
(3, '2026-04-10', 'Adamson OZ', '03:57:05', '04:04:14', 'Late', 0),
(3, '2026-04-10', 'Adamson OZ', '16:08:00', '16:13:00', 'Late', 0),
(3, '2026-04-10', 'Adamson OZ', '16:08:00', '16:13:00', 'Late', 0),
(3, '2026-04-10', 'Adamson OZ', '16:15:00', '16:32:00', 'Late', 0),
(3, '2026-04-10', 'Adamson OZ', '16:33:00', '16:37:00', 'Late', 0),
(3, '2026-04-10', 'Adamson OZ', '16:37:00', '16:41:00', 'Late', 0),
(3, '2026-04-10', 'Adamson OZ', '16:41:00', '16:42:00', 'Late', 0),
(3, '2026-04-10', 'Adamson OZ', '16:44:00', '00:00:00', 'Late', 0),
(3, '2026-04-10', 'Adamson OZ', '16:48:00', '16:53:00', 'Late', 0),
(3, '2026-04-10', 'Adamson OZ', '16:58:00', '16:58:00', 'Late', 0),
(3, '2026-04-10', 'Adamson OZ', '17:00:00', '17:02:00', 'Late', 0),
(3, '2026-04-10', 'Adamson OZ', '17:02:00', '17:03:00', 'Late', 0),
(3, '2026-04-10', 'Adamson OZ', '17:07:00', '17:08:00', 'Late', 1);

-- --------------------------------------------------------

--
-- Table structure for table `employee_location`
--

CREATE TABLE `employee_location` (
  `User_Id` bigint(255) NOT NULL,
  `loc_id` bigint(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employee_location`
--

INSERT INTO `employee_location` (`User_Id`, `loc_id`) VALUES
(3, 1),
(3, 1),
(2, 2);

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
(2, 'SM', '[[14.652350801029067,120.99225997924806],[14.643880624522094,120.99123001098634],[14.645375385333589,121.0001564025879],[14.65334727086327,120.99998474121095],[14.652350801029067,120.99225997924806]]', '2026-04-06 11:25:49', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `User_id` bigint(255) NOT NULL,
  `Username` varchar(255) NOT NULL,
  `Password` varchar(255) NOT NULL,
  `Type` varchar(255) NOT NULL,
  `Work_status` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`User_id`, `Username`, `Password`, `Type`, `Work_status`) VALUES
(1, 'HR', '123', 'HR', 'Tapped-out'),
(2, 'bayang', '1234', 'Emp', 'Tapped-out'),
(3, 'emitter', '12345', 'Emp', 'Tapped-out'),
(5, 'Andrea', '123', 'Emp', 'Tapped-out'),
(6, 'Daniel', '123', 'Emp', 'Tapped-out'),
(7, 'Maria', '123', 'Emp', 'Tapped-out'),
(8, 'Jason', '123', 'Emp', 'Tapped-out'),
(9, 'Lea', '123', 'Emp', 'Tapped-out');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `deleted_geofences`
--
ALTER TABLE `deleted_geofences`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `employee`
--
ALTER TABLE `employee`
  ADD PRIMARY KEY (`Emp_id`);

--
-- Indexes for table `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `employee_attendance`
--
ALTER TABLE `employee_attendance`
  ADD KEY `Emp_id` (`Emp_id`);

--
-- Indexes for table `employee_location`
--
ALTER TABLE `employee_location`
  ADD KEY `User_Id` (`User_Id`);

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
-- AUTO_INCREMENT for table `deleted_geofences`
--
ALTER TABLE `deleted_geofences`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `employee`
--
ALTER TABLE `employee`
  MODIFY `Emp_id` bigint(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `geofences`
--
ALTER TABLE `geofences`
  MODIFY `id` bigint(255) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `employee_attendance`
--
ALTER TABLE `employee_attendance`
  ADD CONSTRAINT `employee_attendance_ibfk_1` FOREIGN KEY (`Emp_id`) REFERENCES `employee` (`Emp_id`);

--
-- Constraints for table `employee_location`
--
ALTER TABLE `employee_location`
  ADD CONSTRAINT `employee_location_ibfk_1` FOREIGN KEY (`User_Id`) REFERENCES `employee` (`Emp_id`);

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`User_id`) REFERENCES `employee` (`Emp_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
