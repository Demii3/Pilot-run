-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 03, 2026 at 02:44 PM
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
-- Database: `employee_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `employees`	
--

CREATE TABLE employees (
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

INSERT INTO employees (`employee_id`, `total_hours`, `rate_per_hour`, `special_holiday`, `legal_holiday`, `overtime_rate`, `late`, `absent`, `cash_advance`, `sss`, `philhealth`, `pagibig`, `tax`) VALUES
('12345678', 160, 100.00, 1000.00, 1000.00, 1000.00, 100000.00, 2400.00, 1000.00, 1000.00, 1000.00, 1000.00, 1000.00),
('202314090', 140, 187.50, 2000.00, 1500.00, 1500.00, 500.00, 1000.00, 500.00, 1000.00, 1000.00, 500.00, 500.00),
('E12345', 160, 187.50, 2000.00, 1500.00, 1500.00, 500.00, 1000.00, 500.00, 1000.00, 1000.00, 500.00, 500.00);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `employees`
--
ALTER TABLE employees
  ADD PRIMARY KEY (`employee_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
