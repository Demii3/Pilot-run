-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 09, 2026 at 04:48 AM
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
  `Firstname` varchar(255) NOT NULL,
  `Lastname` varchar(255) NOT NULL,
  `Department` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employee`
--

INSERT INTO `employee` (`Emp_id`, `Firstname`, `Lastname`, `Department`) VALUES
(1, 'Joyce1', 'Bryce', 'HR'),
(2, 'Bianca', 'Mayor', 'Project Manager'),
(3, 'Demetri', 'Mayor', 'System Admin'),
(5, 'Andrea', 'Cruz', 'Human Resources'),
(6, 'Daniel', 'Reyes', 'Payroll'),
(7, 'Maria', 'Lopez', 'Recruitment'),
(8, 'Jason', 'Miller', 'Benifits'),
(9, 'Lea', 'Tan', 'Training');

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

CREATE TABLE `employees` (
  `employee_id` varchar(10) NOT NULL,
  `total_hours` int(11) NOT NULL,
  `rate_per_hour` decimal(10,2) NOT NULL,
  `special_holiday` decimal(10,2) NOT NULL,
  `legal_holiday` decimal(10,2) NOT NULL,
  `overtime_rate` decimal(10,2) NOT NULL,
  `late` decimal(10,2) NOT NULL,
  `absent` decimal(10,2) NOT NULL,
  `cash_advance` decimal(10,2) NOT NULL,
  `sss` decimal(10,2) NOT NULL,
  `philhealth` decimal(10,2) NOT NULL,
  `pagibig` decimal(10,2) NOT NULL,
  `tax` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employees`
--

INSERT INTO `employees` (`employee_id`, `total_hours`, `rate_per_hour`, `special_holiday`, `legal_holiday`, `overtime_rate`, `late`, `absent`, `cash_advance`, `sss`, `philhealth`, `pagibig`, `tax`) VALUES
('1', 8, 140.00, 5000.00, 5000.00, 150.00, 0.00, 0.00, 50000.00, 1500.00, 1500.00, 1500.00, 1500.00),
('12345678', 200, 100.00, 1000.00, 1000.00, 1000.00, 0.00, 0.00, 1000.00, 1000.00, 1000.00, 1000.00, 1000.00),
('202314090', 140, 187.50, 2000.00, 1500.00, 1500.00, 500.00, 1000.00, 500.00, 1000.00, 1000.00, 500.00, 500.00),
('E12345', 160, 187.50, 2000.00, 1500.00, 1500.00, 500.00, 1000.00, 500.00, 1000.00, 1000.00, 500.00, 500.00);

-- --------------------------------------------------------

--
-- Table structure for table `employee_attendance`
--

CREATE TABLE `employee_attendance` (
  `Date` date NOT NULL,
  `Location` varchar(255) NOT NULL,
  `Clock_in` time NOT NULL,
  `Clock_out` time NOT NULL,
  `Status` varchar(255) NOT NULL,
  `ID_loc` bigint(255) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(3, 1);

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
  `Type` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`User_id`, `Username`, `Password`, `Type`) VALUES
(1, 'HR', '123', 'HR'),
(2, 'bayang', '1234', 'Emp'),
(3, 'emitter', '12345', 'Emp'),
(5, 'Andrea', '123', 'Emp'),
(6, 'Daniel', '123', 'Emp'),
(7, 'Maria', '123', 'Emp'),
(8, 'Jason', '123', 'Emp'),
(9, 'Lea', '123', 'Emp');

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
  ADD PRIMARY KEY (`employee_id`);

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
-- AUTO_INCREMENT for table `geofences`
--
ALTER TABLE `geofences`
  MODIFY `id` bigint(255) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`User_id`) REFERENCES `employee` (`Emp_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
