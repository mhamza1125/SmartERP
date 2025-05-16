-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 16, 2025 at 08:50 PM
-- Server version: 10.4.27-MariaDB
-- PHP Version: 8.2.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `smarterp`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_log`
--

CREATE TABLE `activity_log` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `log_name` varchar(255) DEFAULT NULL,
  `description` text NOT NULL,
  `subject_type` varchar(255) DEFAULT NULL,
  `event` varchar(255) DEFAULT NULL,
  `subject_id` bigint(20) UNSIGNED DEFAULT NULL,
  `causer_type` varchar(255) DEFAULT NULL,
  `causer_id` bigint(20) UNSIGNED DEFAULT NULL,
  `properties` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`properties`)),
  `batch_uuid` char(36) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `banks`
--

CREATE TABLE `banks` (
  `bank_id` bigint(20) UNSIGNED NOT NULL,
  `bank_holder` varchar(255) NOT NULL COMMENT 'Admin, Emploee, Vendor',
  `banker_id` bigint(20) UNSIGNED NOT NULL COMMENT '0-Admin',
  `head_id` bigint(20) UNSIGNED NOT NULL COMMENT 'Bank Type',
  `account_title` varchar(255) NOT NULL,
  `account` varchar(255) NOT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `banks`
--

INSERT INTO `banks` (`bank_id`, `bank_holder`, `banker_id`, `head_id`, `account_title`, `account`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'admin', 0, 32, 'Admin', '06710109764865', 1, '2024-04-02 14:39:37', '2024-04-02 15:23:37'),
(2, 'vendor', 6, 35, 'Shehzad Ahmed', '05710109764865', 1, '2024-04-02 14:40:44', '2024-04-02 14:40:44'),
(3, 'employee', 5, 35, 'Bashir', '02710109764865', 1, '2024-04-02 14:42:06', '2024-04-02 14:42:06'),
(4, 'employee', 5, 36, 'Bashir', '06710109744865', 1, '2024-04-02 14:49:04', '2024-04-02 14:49:04'),
(6, 'vendor', 6, 36, 'Shehzad Ahmed', '02714109764865', 1, '2024-04-04 03:25:37', '2024-04-04 03:25:37'),
(7, 'customer', 6, 36, 'Muhammad Hamza', '06711209764865', 1, '2024-04-19 08:16:35', '2024-04-19 08:16:35'),
(8, 'customer', 5, 37, 'Samad Ali', '06710109744123', 1, '2024-04-19 08:32:47', '2024-04-19 08:32:47'),
(9, 'admin', 0, 35, 'Admin', '0459334895765', 1, '2024-05-23 13:17:53', '2024-05-23 13:19:47'),
(10, 'admin', 0, 37, 'Admin', '0682139347634', 1, '2024-05-23 13:26:53', '2024-05-23 13:28:52'),
(18, 'admin', 0, 36, 'Dummy Check', '0271410976476', 1, '2025-02-16 04:45:10', '2025-02-16 04:45:10'),
(19, 'admin', 0, 36, 'Dummy Check 2', '05710109764787', 1, '2025-02-16 04:47:07', '2025-02-16 04:47:07');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `category_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`category_id`, `name`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'Boxing Gloves', 1, '2024-02-17 13:24:12', '2024-02-17 13:25:00'),
(2, 'Working Gloves', 1, '2024-02-17 13:24:43', '2024-02-17 13:24:43'),
(8, 'Leather Gloves', 1, '2024-02-25 12:48:59', '2024-02-25 12:48:59'),
(9, 'Winter Gloves', 1, '2024-02-25 12:49:05', '2024-02-25 12:49:05');

-- --------------------------------------------------------

--
-- Table structure for table `company`
--

CREATE TABLE `company` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `ceo` varchar(255) NOT NULL,
  `zip` varchar(255) DEFAULT NULL,
  `phone` varchar(255) NOT NULL,
  `fax` varchar(255) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `website` varchar(255) DEFAULT NULL,
  `logo` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `customer_id` bigint(20) UNSIGNED NOT NULL,
  `customer_no` varchar(255) NOT NULL,
  `country_id` bigint(20) UNSIGNED NOT NULL,
  `currency_id` bigint(20) UNSIGNED NOT NULL,
  `fname` varchar(255) NOT NULL,
  `lname` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(255) NOT NULL,
  `fax` varchar(255) NOT NULL,
  `address` longtext DEFAULT NULL,
  `description` longtext DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`customer_id`, `customer_no`, `country_id`, `currency_id`, `fname`, `lname`, `email`, `phone`, `fax`, `address`, `description`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'CU0001', 85, 92, 'Faizan', 'Pervaiz', 'faizan@gmail.com', '03000000000', '03000000000', 'Lahore Trunk Road', '<div style=\"line-height: 19px;\">Lorem ipsum dolor sit amet consectetur adipisicing elit. Sed eaque iure officiis nobis modi inventore consequuntur rem dolorum, obcaecati nihil delectus similique incidunt exercitationem quae soluta reprehenderit provident magnam blanditiis.</div>', 1, '2024-02-18 04:53:35', '2024-03-30 13:10:46'),
(2, 'CU0002', 85, 92, 'Afraz', 'Anwar', 'afraz@gmail.com', '03000000000', '0300 1122334', 'Address of Anwar', NULL, 1, '2024-02-25 13:34:35', '2024-02-25 13:34:35'),
(3, 'CU0003', 85, 92, 'Moazzam', 'Abdullah', 'moazzam@gmail.com', '03000000000', '0300 1122334', 'Address of Moazzam', NULL, 1, '2024-02-25 13:35:01', '2024-02-25 13:35:01'),
(5, 'CU0004', 85, 92, 'Samad', 'Ali', 'samad@gmail.com', '03000000000', '0300 1122334', 'Address of Samad Ali', '<div style=\"line-height: 19px;\">Lorem ipsum dolor sit amet consectetur adipisicing elit. Totam quo, sunt itaque veniam inventore corrupti amet illum? Vero distinctio commodi autem, aperiam unde dolores doloribus corporis natus quod ex ipsam.</div>', 1, '2024-02-25 13:43:49', '2024-02-25 13:49:44'),
(6, 'CU0005', 85, 92, 'Muhammad', 'Hamza', 'hamza@gmail.com', '03000000000', '0300 1122334', NULL, NULL, 1, '2024-03-30 13:02:17', '2024-03-30 13:02:17');

-- --------------------------------------------------------

--
-- Table structure for table `deliveries`
--

CREATE TABLE `deliveries` (
  `delivery_id` bigint(20) UNSIGNED NOT NULL,
  `stock_id` bigint(20) UNSIGNED NOT NULL,
  `fshipping` varchar(255) DEFAULT NULL,
  `tshipping` varchar(255) DEFAULT NULL,
  `fport_no` varchar(255) DEFAULT NULL,
  `tport_no` varchar(255) DEFAULT NULL,
  `delivery_method` bigint(20) UNSIGNED NOT NULL,
  `delivery_status` bigint(20) UNSIGNED NOT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `deliveries`
--

INSERT INTO `deliveries` (`delivery_id`, `stock_id`, `fshipping`, `tshipping`, `fport_no`, `tport_no`, `delivery_method`, `delivery_status`, `created_by`, `created_at`, `updated_at`) VALUES
(5, 109, 'Pakistan', 'Canada', '51230', '34333', 1, 4, 1, '2024-05-07 04:26:42', '2024-05-08 13:50:13'),
(6, 112, 'Pakistan', 'Canada', '21321', '123213', 1, 1, 1, '2024-05-07 12:55:09', '2024-05-07 13:25:37'),
(7, 113, 'Pakistan', 'Canada', '123', '234', 2, 2, 1, '2024-05-07 13:43:20', '2024-05-07 13:43:20'),
(9, 117, 'Pakistan 123', 'Canada 123', '123 123', '123123', 2, 2, 1, '2024-05-08 14:04:20', '2024-05-08 14:25:26'),
(10, 118, 'Pakistan', 'Canada', '123', '123', 1, 1, 1, '2024-05-08 14:06:40', '2024-05-08 14:06:40'),
(11, 119, 'Pakistan', '123', '123', '123', 1, 1, 1, '2024-05-08 14:09:36', '2024-05-08 14:09:36'),
(12, 123, 'Pakistan', 'France', '2340', '23432', 1, 2, 1, '2024-05-09 13:52:55', '2024-05-09 13:52:55'),
(13, 138, 'Pakistan', 'Canada', '1010', '2020', 1, 2, 1, '2024-05-25 12:46:09', '2024-05-25 12:46:09'),
(14, 139, 'Pakistan', 'Canada', '1010', '2020', 1, 1, 1, '2024-05-25 12:49:28', '2024-05-25 12:49:28'),
(15, 140, 'Pakistan', 'Canada', '1010', '2020', 1, 2, 1, '2024-05-25 13:22:48', '2024-05-25 13:22:48'),
(16, 141, '123', '123', '123', '123', 2, 1, 1, '2024-05-25 13:26:16', '2024-05-25 13:26:16'),
(17, 10025, 'karachi', 'abc', 'xyz', 'xyz', 3, 4, 1, '2025-01-16 05:32:39', '2025-01-16 05:32:39');

-- --------------------------------------------------------

--
-- Table structure for table `delivery_boxes`
--

CREATE TABLE `delivery_boxes` (
  `dbox_id` bigint(20) UNSIGNED NOT NULL,
  `delivery_id` bigint(20) UNSIGNED NOT NULL,
  `vehicle_no` varchar(255) NOT NULL,
  `rowQty` varchar(255) NOT NULL,
  `totalQty` double UNSIGNED NOT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `delivery_boxes`
--

INSERT INTO `delivery_boxes` (`dbox_id`, `delivery_id`, `vehicle_no`, `rowQty`, `totalQty`, `created_by`, `created_at`, `updated_at`) VALUES
(6, 14, 'Veh 101', '10|10|10|10|10|0|0|0', 50, 1, '2024-05-30 05:01:02', '2024-05-30 05:01:02');

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

CREATE TABLE `employees` (
  `employee_id` bigint(20) UNSIGNED NOT NULL,
  `employee_no` varchar(255) NOT NULL,
  `department_id` bigint(20) UNSIGNED NOT NULL COMMENT 'HeadID',
  `employee_type_id` bigint(20) UNSIGNED NOT NULL COMMENT 'HeadID',
  `attendance_id` bigint(20) UNSIGNED DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `fname` varchar(255) NOT NULL,
  `sname` varchar(255) DEFAULT NULL,
  `cnic` varchar(255) NOT NULL,
  `phone1` varchar(255) NOT NULL,
  `phone2` varchar(255) DEFAULT NULL,
  `city_id` bigint(20) UNSIGNED NOT NULL COMMENT 'HeadID',
  `address` longtext NOT NULL,
  `designation` varchar(255) DEFAULT NULL,
  `salary` double DEFAULT 0,
  `description` longtext DEFAULT NULL,
  `joining_date` date NOT NULL,
  `employee_status` bigint(20) UNSIGNED NOT NULL DEFAULT 1 COMMENT 'Active / Inactive',
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employees`
--

INSERT INTO `employees` (`employee_id`, `employee_no`, `department_id`, `employee_type_id`, `attendance_id`, `name`, `fname`, `sname`, `cnic`, `phone1`, `phone2`, `city_id`, `address`, `designation`, `salary`, `description`, `joining_date`, `employee_status`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'E0001', 6, 39, 1, 'Zohaib Khalid', 'Khalid', 'Zohaib', '3460389902345', '03001122334', NULL, 44, 'Address of Zohaib', NULL, 32000, '<p><span style=\"font-weight: bolder;\">Employee form Lahore, \"</span>Placed in Admin Department<span style=\"font-weight: bolder;\">\"</span><br></p>', '2024-02-18', 1, 1, '2024-02-18 13:04:22', '2024-04-20 04:46:27'),
(5, 'E0002', 7, 40, 0, 'Bashir Malik', 'Manoor', 'Basihr', '3460389902345', '03001122334', '03001122334', 43, 'Address of Bashir', NULL, 35000, '<div style=\"line-height: 19px;\">Lorem ipsum dolor sit amet consectetur adipisicing elit. Quia, voluptatibus. Laudantium corporis animi assumenda reprehenderit ipsum velit reiciendis nostrum esse id quod quisquam quasi veniam vel aliquid officia, voluptate debitis.</div>', '2024-02-25', 1, 1, '2024-02-25 13:56:59', '2024-04-30 07:55:57'),
(6, 'E0003', 8, 40, 0, 'Adil Nawaz', 'Adil Nawaz', 'Adil', '3460389902345', '03001122334', '03001122334', 44, 'Address of Adil Nawaz', NULL, 0, '<div style=\"line-height: 19px;\">Lorem, ipsum dolor sit amet consectetur adipisicing elit. Obcaecati minima corporis quae, odit officiis facere labore autem beatae! Reprehenderit quasi corrupti ullam enim quia vitae suscipit, asperiores aut delectus possimus!</div>', '2024-02-25', 1, 1, '2024-02-25 14:02:20', '2024-02-25 14:02:20'),
(7, 'E0004', 9, 39, 12, 'Haider Ali', 'Abdullah', 'Haider', '3460389902345', '03001122334', '03001122334', 45, 'Address of Haider', NULL, 20000, '<div style=\"line-height: 19px;\">Lorem, ipsum dolor sit amet consectetur adipisicing elit. Obcaecati minima corporis quae, odit officiis facere labore autem beatae! Reprehenderit quasi corrupti ullam enim quia vitae suscipit, asperiores aut delectus possimus!</div>', '2024-02-26', 1, 1, '2024-02-25 14:03:32', '2024-04-20 07:54:31'),
(8, 'E0005', 9, 39, 48, 'Uzair Aslam', 'Aslam', 'Uzair', '3460389902345', '03001122334', '03001122334', 46, 'Address of Uzair', NULL, 16000, '<p>Desc</p>', '2024-04-20', 1, 1, '2024-04-20 04:54:15', '2024-04-20 07:51:49'),
(11, 'E0006', 8, 39, 43, 'Mubashir', 'Saleem', 'Bashir', '3460389902345', '03001122334', '03001122334', 44, 'Address', NULL, 12020, NULL, '2024-04-29', 1, 1, '2024-04-29 10:53:04', '2024-04-29 10:53:04'),
(12, 'E0007', 6, 40, 0, 'Kashif ali', 'Manzoor', 'Kashif', '3460389902345', '03001122334', '03001122334', 43, 'Sialkot', NULL, 0, NULL, '2024-04-30', 1, 1, '2024-04-30 07:51:22', '2024-05-01 02:56:17'),
(13, 'E0008', 10, 39, 9, 'Moazzam Ali', 'Abdullah', 'Moazzam', '3460389902345', '03001122334', '03001122334', 44, 'Sialkot', 'New Designation', 50000, NULL, '2024-05-31', 1, 1, '2024-05-31 13:39:41', '2025-04-12 01:46:38');

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `heads`
--

CREATE TABLE `heads` (
  `head_id` bigint(20) UNSIGNED NOT NULL,
  `head_type_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `head_status` bigint(20) UNSIGNED NOT NULL DEFAULT 1,
  `action` bigint(20) UNSIGNED NOT NULL DEFAULT 0 COMMENT '1 - No Deletion',
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `heads`
--

INSERT INTO `heads` (`head_id`, `head_type_id`, `name`, `head_status`, `action`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 1, 'S', 1, 0, 1, '2024-02-16 15:42:10', '2024-02-16 15:42:10'),
(2, 1, 'M', 1, 0, 1, '2024-02-16 15:48:44', '2024-02-16 15:48:44'),
(3, 1, 'L', 1, 0, 1, '2024-02-17 00:35:01', '2024-02-17 00:35:01'),
(4, 1, 'XL', 1, 0, 1, '2024-02-17 00:35:31', '2024-02-17 00:35:31'),
(5, 1, 'XXL', 1, 0, 1, '2024-02-17 00:35:40', '2024-02-17 00:35:40'),
(6, 3, 'Admin', 1, 0, 1, '2024-02-17 00:38:59', '2024-09-22 00:56:10'),
(7, 3, 'Cutting', 1, 0, 1, '2024-02-17 00:40:17', '2024-02-17 00:40:17'),
(8, 3, 'Stitching', 1, 0, 1, '2024-02-17 00:44:53', '2024-02-17 00:44:53'),
(9, 3, 'Quality Checking', 1, 0, 1, '2024-02-17 00:46:03', '2024-02-17 00:46:03'),
(10, 3, 'Ironing', 1, 0, 1, '2024-02-17 00:48:36', '2024-02-17 00:48:36'),
(32, 6, 'HBL Bank', 1, 0, 1, '2024-02-18 09:05:28', '2024-02-18 09:05:28'),
(34, 5, 'Store Main', 1, 0, 1, '2024-02-18 09:05:31', '2024-02-18 09:05:31'),
(35, 6, 'UBL Bank', 1, 0, 1, '2024-02-18 09:05:38', '2024-02-18 09:05:38'),
(36, 6, 'Allied Bank', 1, 0, 1, '2024-02-18 09:05:46', '2024-02-18 09:05:46'),
(37, 6, 'MCB Bank', 1, 0, 1, '2024-02-18 09:05:51', '2024-02-18 09:05:51'),
(38, 7, 'Miscelenious', 1, 0, 1, '2024-02-18 09:06:05', '2024-02-18 09:06:05'),
(39, 9, 'Salary', 1, 1, 1, '2024-02-18 09:06:12', '2024-02-18 09:06:12'),
(40, 9, 'Wages', 1, 1, 1, '2024-02-18 09:06:19', '2024-02-18 09:06:19'),
(41, 7, 'Vehicle Rent', 1, 0, 1, '2024-02-18 09:08:28', '2024-02-18 09:08:28'),
(42, 7, 'Electric Expense', 1, 0, 1, '2024-02-18 09:08:45', '2024-02-18 09:08:45'),
(43, 8, 'Sialkot', 1, 0, 1, '2024-02-18 12:40:56', '2024-02-18 12:40:56'),
(44, 8, 'Lahore', 1, 0, 1, '2024-02-18 12:41:05', '2024-02-18 12:41:05'),
(45, 8, 'Kingra', 1, 0, 1, '2024-02-18 12:41:11', '2024-02-18 12:41:11'),
(46, 8, 'Pasrur', 1, 0, 1, '2024-02-18 12:41:24', '2024-02-18 12:41:24'),
(47, 4, 'Pairs', 1, 0, 1, '2024-02-19 07:21:21', '2024-02-19 07:21:21'),
(48, 4, 'Pieces', 1, 0, 1, '2024-02-19 07:21:26', '2024-02-19 07:21:26'),
(49, 4, 'Feets', 1, 0, 1, '2024-02-19 07:21:29', '2024-02-19 07:21:29'),
(50, 4, 'Meters', 1, 0, 1, '2024-02-19 07:21:34', '2024-02-19 07:21:34'),
(51, 4, 'Liters', 1, 0, 1, '2024-02-19 07:23:12', '2024-02-19 07:23:12'),
(52, 4, 'Boxes', 1, 0, 1, '2024-02-19 07:23:26', '2024-02-19 07:23:26'),
(53, 7, 'Services', 1, 0, 1, '2024-02-19 07:24:09', '2024-02-19 07:24:09'),
(54, 10, 'Leather', 1, 0, 1, '2024-02-19 07:24:51', '2024-02-19 07:24:51'),
(55, 10, 'PU Leather', 1, 0, 1, '2024-02-19 07:24:58', '2024-02-19 07:24:58'),
(56, 10, 'Zip', 1, 0, 1, '2024-02-19 07:25:03', '2024-02-19 07:25:03'),
(57, 11, 'Leather', 1, 0, 1, '2024-02-20 07:14:29', '2024-05-23 13:52:07'),
(58, 11, 'Material', 1, 0, 1, '2024-02-20 07:14:34', '2024-05-23 13:53:02'),
(60, 11, 'Vehicle', 1, 0, 1, '2024-02-20 07:15:06', '2024-05-23 13:53:13'),
(61, 10, 'Packing Boxes', 1, 1, 1, '2024-02-24 11:51:44', '2024-04-27 06:38:48'),
(62, 10, 'Thread', 1, 0, 1, '2024-02-25 13:22:16', '2024-02-25 13:22:16'),
(63, 10, 'Liquid Items', 1, 0, 1, '2024-02-25 13:22:26', '2024-02-25 13:22:26'),
(64, 10, 'Rubber Items', 1, 0, 1, '2024-02-25 13:22:41', '2024-02-25 13:22:41'),
(65, 10, 'Cloths', 1, 0, 1, '2024-02-25 13:23:03', '2024-02-25 13:23:03'),
(66, 10, 'Other Items', 1, 0, 1, '2024-02-25 13:25:04', '2024-02-25 13:25:04'),
(67, 11, 'Boxes', 1, 0, 1, '2024-02-25 14:07:27', '2024-02-25 14:07:27'),
(68, 11, 'Liquid Items', 1, 0, 1, '2024-02-25 14:07:55', '2024-05-23 13:52:37'),
(69, 11, 'Other', 1, 0, 1, '2024-02-25 14:08:08', '2024-02-25 14:08:08'),
(70, 8, 'Islamabad', 1, 0, 1, '2024-02-26 00:39:51', '2024-02-26 00:39:51'),
(72, 12, 'Cutting', 1, 0, 1, '2024-03-03 13:05:00', '2024-03-03 13:05:00'),
(73, 12, 'Stitched', 1, 0, 1, '2024-03-03 13:05:15', '2024-03-03 13:05:15'),
(74, 12, 'Finished', 1, 0, 1, '2024-03-03 13:05:20', '2024-03-03 13:05:20'),
(78, 14, 'Cutting', 1, 0, 1, '2024-03-19 12:58:13', '2024-03-19 12:58:13'),
(79, 14, 'Fingers Stitching', 1, 0, 1, '2024-03-19 12:58:25', '2024-03-19 12:58:25'),
(80, 14, 'Palm Stiching', 1, 0, 1, '2024-03-19 12:58:46', '2024-03-19 12:58:46'),
(81, 14, 'Lining', 1, 0, 1, '2024-03-19 12:59:56', '2024-03-19 12:59:56'),
(82, 14, 'Adding Grips', 1, 0, 1, '2024-03-19 13:00:08', '2024-03-19 13:00:08'),
(83, 14, 'Fitting & Adjustments', 1, 0, 1, '2024-03-19 13:00:28', '2024-03-19 13:00:28'),
(84, 14, 'Complete Cost', 1, 0, 1, '2024-03-19 13:00:44', '2024-03-19 13:00:44'),
(85, 15, 'Pakistan', 1, 0, 1, '2024-03-30 12:49:41', '2024-03-30 12:49:41'),
(86, 15, 'United States', 1, 0, 1, '2024-03-30 12:50:24', '2024-03-30 12:50:24'),
(87, 15, 'Canada', 1, 0, 1, '2024-03-30 12:50:37', '2024-03-30 12:50:37'),
(88, 15, 'Japan', 1, 0, 1, '2024-03-30 12:50:45', '2024-03-30 12:50:45'),
(89, 15, 'Germany', 1, 0, 1, '2024-03-30 12:50:49', '2024-03-30 12:50:49'),
(90, 15, 'France', 1, 0, 1, '2024-03-30 12:50:53', '2024-03-30 12:50:53'),
(91, 15, 'China', 1, 0, 1, '2024-03-30 12:50:56', '2024-03-30 12:50:56'),
(92, 16, 'Pakistani Rupee', 1, 0, 1, '2024-03-30 12:51:51', '2024-03-30 12:51:51'),
(93, 16, 'Dollar', 1, 0, 1, '2024-03-30 12:52:11', '2024-03-30 12:52:11'),
(94, 16, 'Denaar', 1, 0, 1, '2024-03-30 12:52:16', '2024-03-30 12:52:16'),
(95, 16, 'Riyaal', 1, 0, 1, '2024-03-30 12:52:22', '2024-03-30 12:52:22'),
(96, 10, 'Shipping / Delivery Vehicles', 1, 1, 1, '2024-04-30 08:43:20', '2024-04-30 08:43:49'),
(97, 4, 'Units', 1, 0, 1, '2024-04-30 08:48:40', '2024-04-30 08:48:40'),
(98, 7, 'Tax Charges', 1, 0, 1, '2024-05-10 08:16:44', '2024-05-10 08:16:44'),
(101, 10, 'Machine Material', 1, 1, 1, '2024-05-24 08:51:09', '2024-05-24 08:51:09'),
(102, 19, 'Joki Stitching', 1, 0, 1, '2024-05-24 11:19:21', '2024-05-24 11:19:21'),
(103, 19, 'Maghzi', 1, 0, 1, '2024-05-24 11:19:42', '2024-05-24 11:19:42'),
(104, 19, 'Embroidery', 1, 0, 1, '2024-05-24 11:20:01', '2024-05-24 11:20:01'),
(105, 12, 'Rejection', 1, 1, 1, '2024-05-28 05:52:35', '2024-05-28 05:52:35'),
(106, 16, 'Euro', 1, 0, 1, '2024-08-10 05:54:11', '2024-08-10 05:54:11');

-- --------------------------------------------------------

--
-- Table structure for table `head_types`
--

CREATE TABLE `head_types` (
  `head_type_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` longtext DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `head_types`
--

INSERT INTO `head_types` (`head_type_id`, `name`, `description`, `created_at`, `updated_at`) VALUES
(1, 'Sizes', 'S, M, L, XL etc', '2024-02-15 12:01:25', '2024-02-15 12:01:25'),
(3, 'Departments', 'Packing, Stitching, Quality Checking Departments', '2024-02-15 12:01:25', '2024-02-15 12:01:25'),
(4, 'Measuring Units', 'Pairs, Pieces, Dozen, Meter etc', '2024-02-15 16:13:09', '2024-02-15 16:13:09'),
(6, 'Bank Accounts', 'MCB, HBL, Meezan Banks', '2024-02-15 19:21:32', '2024-02-15 19:21:32'),
(7, 'Expenses', 'Rent, Bill, Miscellaneous etc', '2024-02-15 19:21:32', '2024-02-15 19:21:32'),
(8, 'Cities / Towns / Villages', 'Sialkot, Lahore, Karachi etc', '2024-02-15 19:22:44', '2024-02-15 19:22:44'),
(9, 'Employee Types', 'Salary, Wages etc', '2024-02-16 09:56:31', '2024-02-16 09:56:31'),
(10, 'Material Types', 'Boxes, Liquid, Fabric etc', '2024-02-16 18:10:01', '2024-02-16 18:10:01'),
(11, 'Vendor Types', 'Boxes, Tips, Zip etc', '2024-02-20 12:14:00', '2024-02-20 12:14:00'),
(12, 'Product Stages', 'Cutting, Stitching, Packed etc', '2024-03-03 18:04:18', '2024-03-03 18:04:18'),
(14, 'Product Costing', 'Cutting, Stitching, Ironing etc', '2024-03-19 17:57:16', '2024-03-19 17:57:16'),
(15, 'Country / State', 'Pakistan, Canada, England etc', '2024-03-30 17:49:21', '2024-03-30 17:49:21'),
(16, 'Currency', 'Pkr, Dollor, Riyal etc', '2024-03-30 17:49:21', '2024-03-30 17:49:21'),
(19, 'Machine Types', 'Joki, Maghzi, Embroidery etc', '2024-05-24 16:18:56', '2024-05-24 16:18:56');

-- --------------------------------------------------------

--
-- Table structure for table `igroups`
--

CREATE TABLE `igroups` (
  `igroup_id` bigint(20) UNSIGNED NOT NULL,
  `igroup_no` varchar(255) NOT NULL,
  `order_id` bigint(20) UNSIGNED DEFAULT NULL,
  `igroup_date` date NOT NULL,
  `igroup_status` bigint(20) UNSIGNED NOT NULL DEFAULT 1,
  `description` longtext DEFAULT NULL,
  `created_by` bigint(20) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `igroups`
--

INSERT INTO `igroups` (`igroup_id`, `igroup_no`, `order_id`, `igroup_date`, `igroup_status`, `description`, `created_by`, `created_at`, `updated_at`) VALUES
(5, 'Group 1', 16, '2024-05-28', 1, NULL, 1, '2024-05-28 13:14:55', '2024-05-28 15:16:22'),
(6, 'Group 2', 17, '2024-05-28', 1, NULL, 1, '2024-05-28 13:53:15', '2024-05-28 14:12:49'),
(7, 'Group 3', 27, '2024-05-30', 1, NULL, 1, '2024-05-30 12:07:10', '2024-05-30 12:07:10'),
(8, 'Group 4', 26, '2024-06-03', 1, NULL, 1, '2024-06-03 13:35:16', '2024-06-03 13:35:16'),
(9, 'test', NULL, '2025-01-01', 1, NULL, 1, '2025-01-01 03:08:19', '2025-01-01 03:08:19'),
(10, 'test1', NULL, '2025-01-01', 1, NULL, 1, '2025-01-01 03:15:34', '2025-01-01 03:15:34'),
(11, 'New Surgical', NULL, '2025-05-12', 1, NULL, 1, '2025-05-12 13:04:29', '2025-05-12 13:04:29');

-- --------------------------------------------------------

--
-- Table structure for table `igroup_items`
--

CREATE TABLE `igroup_items` (
  `igroup_item_id` bigint(20) UNSIGNED NOT NULL,
  `igroup_id` bigint(20) UNSIGNED NOT NULL,
  `product_type_id` bigint(20) UNSIGNED NOT NULL,
  `material_id` bigint(20) UNSIGNED NOT NULL,
  `quantity` double UNSIGNED NOT NULL,
  `stage_id` bigint(20) UNSIGNED NOT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `igroup_items`
--

INSERT INTO `igroup_items` (`igroup_item_id`, `igroup_id`, `product_type_id`, `material_id`, `quantity`, `stage_id`, `created_by`, `created_at`, `updated_at`) VALUES
(3, 5, 54, 7, 20, 0, 1, '2024-05-28 13:14:55', '2024-05-28 13:14:55'),
(4, 5, 54, 14, 20, 0, 1, '2024-05-28 13:14:55', '2024-05-28 13:14:55'),
(5, 5, 54, 12, 20, 0, 1, '2024-05-28 13:14:55', '2024-05-28 13:14:55'),
(6, 5, 54, 13, 20, 0, 1, '2024-05-28 13:14:55', '2024-05-28 13:14:55'),
(8, 5, 54, 0, 10, 73, 1, '2024-05-28 13:14:55', '2024-05-28 15:16:22'),
(9, 6, 92, 11, 20, 0, 1, '2024-05-28 13:53:15', '2024-05-28 13:53:15'),
(12, 6, 92, 0, 1, 74, 1, '2024-05-28 15:15:43', '2024-05-28 15:15:43'),
(13, 7, 52, 14, 123, 0, 1, '2024-05-30 12:07:10', '2024-05-30 12:07:10'),
(15, 7, 52, 13, 12, 0, 1, '2024-05-30 12:07:59', '2024-05-30 12:07:59'),
(18, 9, 52, 12, 12, 0, 1, '2025-01-01 03:08:19', '2025-01-01 03:08:19'),
(19, 10, 51, 11, 12, 0, 1, '2025-01-01 03:15:34', '2025-01-01 03:15:34'),
(20, 11, 49, 11, 12, 0, 1, '2025-05-12 13:04:29', '2025-05-12 13:04:29'),
(21, 11, 49, 0, 12, 73, 1, '2025-05-12 13:04:29', '2025-05-12 13:04:29'),
(22, 8, 106, 10, 10, 72, 1, '2025-05-12 13:06:34', '2025-05-12 13:06:34'),
(23, 8, 105, 0, 15, 72, 1, '2025-05-12 13:06:34', '2025-05-12 13:06:34'),
(24, 8, 104, 0, 10, 72, 1, '2025-05-12 13:06:34', '2025-05-12 13:06:34');

-- --------------------------------------------------------

--
-- Table structure for table `images`
--

CREATE TABLE `images` (
  `image_id` bigint(20) UNSIGNED NOT NULL,
  `table_name` varchar(255) NOT NULL,
  `table_id` bigint(20) UNSIGNED NOT NULL,
  `image` varchar(255) NOT NULL,
  `file_title` varchar(255) DEFAULT NULL,
  `file_type` varchar(255) DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `images`
--

INSERT INTO `images` (`image_id`, `table_name`, `table_id`, `image`, `file_title`, `file_type`, `created_by`, `created_at`, `updated_at`) VALUES
(23, 'employees', 1, 'img01_1708363759.png', NULL, NULL, 1, '2024-02-19 12:29:19', '2024-02-19 12:29:19'),
(24, 'employees', 1, 'img02_1708363759.png', NULL, NULL, 1, '2024-02-19 12:29:19', '2024-02-19 12:29:19'),
(25, 'employees', 1, 'img03_1708363759.png', NULL, NULL, 1, '2024-02-19 12:29:19', '2024-02-19 12:29:19'),
(26, 'materials', 1, 'img04_1708363786.png', NULL, NULL, 1, '2024-02-19 12:29:46', '2024-02-19 12:29:46'),
(27, 'materials', 1, 'img05_1708363786.png', NULL, NULL, 1, '2024-02-19 12:29:46', '2024-02-19 12:29:46'),
(28, 'materials', 1, 'img06_1708363786.png', NULL, NULL, 1, '2024-02-19 12:29:46', '2024-02-19 12:29:46'),
(29, 'vendors', 1, 'img07_1708448931.png', NULL, NULL, 1, '2024-02-20 12:08:51', '2024-02-20 12:08:51'),
(30, 'vendors', 1, 'img08_1708448931.png', NULL, NULL, 1, '2024-02-20 12:08:51', '2024-02-20 12:08:51'),
(31, 'vendors', 1, 'img09_1708448931.png', NULL, NULL, 1, '2024-02-20 12:08:51', '2024-02-20 12:08:51'),
(35, 'products', 6, 'img01_1708791619.png', NULL, NULL, 1, '2024-02-24 11:20:19', '2024-02-24 11:20:19'),
(36, 'products', 6, 'img02_1708791619.png', NULL, NULL, 1, '2024-02-24 11:20:19', '2024-02-24 11:20:19'),
(37, 'products', 6, 'img03_1708791619.png', NULL, NULL, 1, '2024-02-24 11:20:19', '2024-02-24 11:20:19'),
(38, 'products', 6, 'img04_1708791619.png', NULL, NULL, 1, '2024-02-24 11:20:19', '2024-02-24 11:20:19'),
(39, 'products', 6, 'img05_1708791619.png', NULL, NULL, 1, '2024-02-24 11:20:19', '2024-02-24 11:20:19'),
(40, 'products', 6, 'img06_1708791619.png', NULL, NULL, 1, '2024-02-24 11:20:19', '2024-02-24 11:20:19'),
(41, 'products', 6, 'img07_1708791619.png', NULL, NULL, 1, '2024-02-24 11:20:19', '2024-02-24 11:20:19'),
(42, 'products', 6, 'img08_1708791619.png', NULL, NULL, 1, '2024-02-24 11:20:19', '2024-02-24 11:20:19'),
(43, 'products', 6, 'img09_1708791619.png', NULL, NULL, 1, '2024-02-24 11:20:19', '2024-02-24 11:20:19'),
(45, 'products', 9, '1_1708883883.png', NULL, NULL, 1, '2024-02-25 12:58:03', '2024-02-25 12:58:03'),
(46, 'products', 9, '2_1708883883.png', NULL, NULL, 1, '2024-02-25 12:58:03', '2024-02-25 12:58:03'),
(47, 'products', 9, '3_1708883883.png', NULL, NULL, 1, '2024-02-25 12:58:03', '2024-02-25 12:58:03'),
(48, 'products', 10, '12_1708883939.png', NULL, NULL, 1, '2024-02-25 12:58:59', '2024-02-25 12:58:59'),
(49, 'products', 10, '13_1708883939.png', NULL, NULL, 1, '2024-02-25 12:58:59', '2024-02-25 12:58:59'),
(50, 'products', 10, '14_1708883939.png', NULL, NULL, 1, '2024-02-25 12:58:59', '2024-02-25 12:58:59'),
(51, 'products', 11, '7_1708883983.png', NULL, NULL, 1, '2024-02-25 12:59:43', '2024-02-25 12:59:43'),
(52, 'products', 11, '8_1708883983.png', NULL, NULL, 1, '2024-02-25 12:59:43', '2024-02-25 12:59:43'),
(54, 'products', 12, '1_1708884037.png', NULL, NULL, 1, '2024-02-25 13:00:37', '2024-02-25 13:00:37'),
(55, 'products', 12, '2_1708884037.png', NULL, NULL, 1, '2024-02-25 13:00:37', '2024-02-25 13:00:37'),
(56, 'products', 12, '3_1708884037.png', NULL, NULL, 1, '2024-02-25 13:00:37', '2024-02-25 13:00:37'),
(57, 'products', 12, '4_1708884037.png', NULL, NULL, 1, '2024-02-25 13:00:37', '2024-02-25 13:00:37'),
(58, 'materials', 1, '1_1708884522.png', NULL, NULL, 1, '2024-02-25 13:08:42', '2024-02-25 13:08:42'),
(59, 'materials', 1, '2_1708884522.png', NULL, NULL, 1, '2024-02-25 13:08:42', '2024-02-25 13:08:42'),
(60, 'materials', 1, '3_1708884522.png', NULL, NULL, 1, '2024-02-25 13:08:42', '2024-02-25 13:08:42'),
(61, 'customers', 5, '1_1708886629.png', NULL, NULL, 1, '2024-02-25 13:43:49', '2024-02-25 13:43:49'),
(62, 'customers', 5, '2_1708886629.png', NULL, NULL, 1, '2024-02-25 13:43:49', '2024-02-25 13:43:49'),
(63, 'customers', 5, '3_1708886629.png', NULL, NULL, 1, '2024-02-25 13:43:49', '2024-02-25 13:43:49'),
(64, 'customers', 5, '4_1708886629.png', NULL, NULL, 1, '2024-02-25 13:43:49', '2024-02-25 13:43:49'),
(67, 'employees', 5, '1_1708887419.png', NULL, NULL, 1, '2024-02-25 13:56:59', '2024-02-25 13:56:59'),
(68, 'employees', 5, '2_1708887419.png', NULL, NULL, 1, '2024-02-25 13:56:59', '2024-02-25 13:56:59'),
(69, 'employees', 5, '3_1708887419.png', NULL, NULL, 1, '2024-02-25 13:56:59', '2024-02-25 13:56:59'),
(70, 'employees', 6, '6_1708887740.png', NULL, NULL, 1, '2024-02-25 14:02:20', '2024-02-25 14:02:20'),
(71, 'employees', 6, '7_1708887740.png', NULL, NULL, 1, '2024-02-25 14:02:20', '2024-02-25 14:02:20'),
(72, 'employees', 6, '8_1708887740.png', NULL, NULL, 1, '2024-02-25 14:02:20', '2024-02-25 14:02:20'),
(73, 'employees', 7, '6_1708887812.png', NULL, NULL, 1, '2024-02-25 14:03:32', '2024-02-25 14:03:32'),
(74, 'employees', 7, '7_1708887812.png', NULL, NULL, 1, '2024-02-25 14:03:32', '2024-02-25 14:03:32'),
(76, 'vendors', 3, '5_1708887938.png', NULL, NULL, 1, '2024-02-25 14:05:38', '2024-02-25 14:05:38'),
(77, 'vendors', 3, '6_1708887938.png', NULL, NULL, 1, '2024-02-25 14:05:38', '2024-02-25 14:05:38'),
(78, 'vendors', 3, '7_1708887938.png', NULL, NULL, 1, '2024-02-25 14:05:38', '2024-02-25 14:05:38'),
(79, 'vendors', 4, '4_1708888158.png', NULL, NULL, 1, '2024-02-25 14:09:18', '2024-02-25 14:09:18'),
(80, 'vendors', 4, '5_1708888158.png', NULL, NULL, 1, '2024-02-25 14:09:18', '2024-02-25 14:09:18'),
(81, 'vendors', 4, '6_1708888158.png', NULL, NULL, 1, '2024-02-25 14:09:18', '2024-02-25 14:09:18'),
(82, 'vendors', 4, '7_1708888158.png', NULL, NULL, 1, '2024-02-25 14:09:18', '2024-02-25 14:09:18'),
(83, 'vendors', 5, '2_1708888200.png', NULL, NULL, 1, '2024-02-25 14:10:00', '2024-02-25 14:10:00'),
(84, 'vendors', 5, '3_1708888200.png', NULL, NULL, 1, '2024-02-25 14:10:00', '2024-02-25 14:10:00'),
(85, 'vendors', 5, '4_1708888200.png', NULL, NULL, 1, '2024-02-25 14:10:00', '2024-02-25 14:10:00'),
(86, 'materials', 4, '1_1708926601.png', NULL, NULL, 1, '2024-02-26 00:50:01', '2024-02-26 00:50:01'),
(87, 'materials', 4, '2_1708926601.png', NULL, NULL, 1, '2024-02-26 00:50:01', '2024-02-26 00:50:01'),
(88, 'materials', 4, '3_1708926601.png', NULL, NULL, 1, '2024-02-26 00:50:01', '2024-02-26 00:50:01'),
(95, 'products', 21, 'cheque_1710619710.jpg', NULL, NULL, 1, '2024-03-16 15:08:30', '2024-03-16 15:08:30'),
(96, 'products', 21, 'laravel 01_1710619710.jpeg', 'Laravel 1', '1', 1, '2024-03-16 15:08:30', '2024-03-16 15:08:30'),
(97, 'products', 21, 'laravel 02_1710619710.jpeg', 'Laravel 2', '1', 1, '2024-03-16 15:08:30', '2024-03-16 15:08:30'),
(98, 'products', 21, 'laravel 03_1710619710.jpeg', 'Laravel 3', '1', 1, '2024-03-16 15:08:30', '2024-03-16 15:08:30'),
(99, 'products', 22, 'cheque_1710619806.jpg', NULL, NULL, 1, '2024-03-16 15:10:06', '2024-03-16 15:10:06'),
(107, 'boxes', 1, 'laravel 01_1710772892.jpeg', NULL, NULL, 1, '2024-03-18 09:41:32', '2024-03-18 09:41:32'),
(108, 'boxes', 1, 'laravel 02_1710772892.jpeg', NULL, NULL, 1, '2024-03-18 09:41:32', '2024-03-18 09:41:32'),
(110, 'boxes', 1, 'laravel 01_1710772892.jpeg', 'Attachment title', '1', 1, '2024-03-18 09:41:32', '2024-03-18 09:41:32'),
(111, 'vendors', 6, 'laravel 01_1711651031.jpeg', NULL, NULL, 1, '2024-03-28 13:37:11', '2024-03-28 13:37:11'),
(112, 'vendors', 6, 'laravel 02_1711651031.jpeg', NULL, NULL, 1, '2024-03-28 13:37:11', '2024-03-28 13:37:11'),
(113, 'vendors', 6, 'laravel 03_1711651031.jpeg', NULL, NULL, 1, '2024-03-28 13:37:11', '2024-03-28 13:37:11'),
(114, 'vendors', 6, 'laravel 04_1711651031.jpg', NULL, NULL, 1, '2024-03-28 13:37:11', '2024-03-28 13:37:11'),
(115, 'vendors', 6, 'laravel 10_1711651031.png', NULL, NULL, 1, '2024-03-28 13:37:11', '2024-03-28 13:37:11'),
(116, 'vendors', 6, 'laravel_1711651031.png', NULL, NULL, 1, '2024-03-28 13:37:11', '2024-03-28 13:37:11'),
(117, 'customers', 6, 'laravel 01_1711821738.jpeg', NULL, NULL, 1, '2024-03-30 13:02:18', '2024-03-30 13:02:18'),
(118, 'customers', 6, 'laravel 02_1711821738.jpeg', NULL, NULL, 1, '2024-03-30 13:02:18', '2024-03-30 13:02:18'),
(119, 'customers', 6, 'laravel 03_1711821738.jpeg', NULL, NULL, 1, '2024-03-30 13:02:18', '2024-03-30 13:02:18'),
(120, 'transactions', 1, 'laravel 01_1712081066.jpeg', NULL, NULL, 1, '2024-04-02 13:04:26', '2024-04-02 13:04:26'),
(121, 'transactions', 2, 'laravel 02_1712167599.jpeg', NULL, NULL, 1, '2024-04-03 13:06:39', '2024-04-03 13:06:39'),
(123, 'transactions', 3, 'laravel 02_1712206782.jpeg', NULL, NULL, 1, '2024-04-03 23:59:42', '2024-04-03 23:59:42'),
(124, 'transactions', 3, 'laravel 03_1712206782.jpeg', NULL, NULL, 1, '2024-04-03 23:59:42', '2024-04-03 23:59:42'),
(125, 'transactions', 11, 'laravel 01_1713533992.jpeg', NULL, NULL, 1, '2024-04-19 08:39:52', '2024-04-19 08:39:52'),
(126, 'employees', 10, 'logo-dummy_1713606986.png', NULL, NULL, 1, '2024-04-20 04:56:26', '2024-04-20 04:56:26'),
(127, 'vendors', 7, 'logo-dummy_1714045214.png', NULL, NULL, 1, '2024-04-25 06:40:14', '2024-04-25 06:40:14'),
(128, 'vendors', 7, 'logo-dummy_1714045214.jpg', NULL, NULL, 1, '2024-04-25 06:40:14', '2024-04-25 06:40:14'),
(129, 'products', 23, 'laravel 01_1714063382.jpeg', NULL, NULL, 1, '2024-04-25 11:43:02', '2024-04-25 11:43:02'),
(130, 'products', 23, 'laravel 02_1714063382.jpeg', NULL, NULL, 1, '2024-04-25 11:43:02', '2024-04-25 11:43:02'),
(131, 'products', 23, 'laravel 03_1714063382.jpeg', NULL, NULL, 1, '2024-04-25 11:43:02', '2024-04-25 11:43:02'),
(132, 'products', 23, 'laravel 04_1714063382.jpg', NULL, NULL, 1, '2024-04-25 11:43:02', '2024-04-25 11:43:02'),
(133, 'products', 23, 'Admin - SmartERP_1714063382.xlsx', 'Title', '1', 1, '2024-04-25 11:43:02', '2024-04-25 11:43:02'),
(134, 'products', 24, 'laravel 01_1714063441.jpeg', NULL, NULL, 1, '2024-04-25 11:44:01', '2024-04-25 11:44:01'),
(135, 'products', 24, 'laravel 02_1714063441.jpeg', NULL, NULL, 1, '2024-04-25 11:44:01', '2024-04-25 11:44:01'),
(136, 'products', 24, 'laravel 03_1714063441.jpeg', NULL, NULL, 1, '2024-04-25 11:44:01', '2024-04-25 11:44:01'),
(137, 'products', 24, 'laravel 04_1714063441.jpg', NULL, NULL, 1, '2024-04-25 11:44:01', '2024-04-25 11:44:01'),
(138, 'products', 24, 'Admin - SmartERP_1714063441.xlsx', 'Title', '1', 1, '2024-04-25 11:44:01', '2024-04-25 11:44:01'),
(142, 'stocks', 131, 'laravel 01_1716634521.jpeg', NULL, NULL, 1, '2024-05-25 05:55:21', '2024-05-25 05:55:21'),
(146, 'products', 29, 'upload_1737016165.jpg', NULL, NULL, 1, '2025-01-16 03:29:25', '2025-01-16 03:29:25'),
(147, 'transactions', 89, 'upload_1737025325.jpg', NULL, NULL, 1, '2025-01-16 06:02:05', '2025-01-16 06:02:05');

-- --------------------------------------------------------

--
-- Table structure for table `machines`
--

CREATE TABLE `machines` (
  `machine_id` bigint(20) UNSIGNED NOT NULL,
  `machine_no` varchar(255) NOT NULL,
  `machine_type_id` bigint(20) UNSIGNED NOT NULL,
  `employee_id` bigint(20) UNSIGNED NOT NULL,
  `location` varchar(255) DEFAULT NULL,
  `description` longtext DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `machines`
--

INSERT INTO `machines` (`machine_id`, `machine_no`, `machine_type_id`, `employee_id`, `location`, `description`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'M0001', 102, 0, '2nd Hall 1st Floor', NULL, 1, '2024-05-24 11:39:33', '2024-05-24 11:39:33'),
(2, 'M0002', 103, 12, 'Ground Floor Stitching Room', NULL, 1, '2024-05-24 11:40:06', '2024-05-24 12:51:09'),
(3, 'M0003', 104, 11, 'Hall 101', NULL, 1, '2024-05-29 08:26:58', '2024-05-29 08:27:22');

-- --------------------------------------------------------

--
-- Table structure for table `materials`
--

CREATE TABLE `materials` (
  `material_id` bigint(20) UNSIGNED NOT NULL,
  `material_no` varchar(255) NOT NULL,
  `material_type_id` bigint(20) UNSIGNED NOT NULL COMMENT 'Material Types',
  `vendor_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `unit_id` bigint(20) UNSIGNED NOT NULL COMMENT 'HeadID',
  `cprice` double UNSIGNED NOT NULL,
  `location` varchar(255) DEFAULT NULL,
  `description` longtext DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `materials`
--

INSERT INTO `materials` (`material_id`, `material_no`, `material_type_id`, `vendor_id`, `name`, `unit_id`, `cprice`, `location`, `description`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'M0001', 54, 4, 'Black Sheep Leather', 49, 100, 'Rack # 1', '<div style=\"line-height: 19px;\">Lorem ipsum dolor sit, amet consectetur adipisicing elit. Temporibus vel rem quis ratione suscipit reiciendis possimus mollitia laboriosam, iste cumque, eaque provident totam quaerat minima nulla nisi vero delectus quia.</div>', 1, '2024-02-19 11:57:34', '2024-05-25 07:06:48'),
(2, 'M0002', 56, 5, 'Zip', 50, 100, NULL, NULL, 1, '2024-02-19 12:04:39', '2024-05-25 07:06:48'),
(3, 'M0003', 54, 6, 'Cow Leather', 49, 1100, NULL, '<div style=\"line-height: 19px;\">Lorem ipsum dolor sit, amet consectetur adipisicing elit. Temporibus vel rem quis ratione suscipit reiciendis possimus mollitia laboriosam, iste cumque, eaque provident totam quaerat minima nulla nisi vero delectus quia.</div>', 1, '2024-02-25 13:09:33', '2024-05-25 07:06:48'),
(4, 'M0004', 55, 1, 'Crocodile Leather', 49, 0, NULL, '<div style=\"line-height: 19px;\">Lorem ipsum dolor sit, amet consectetur adipisicing elit. Temporibus vel rem quis ratione suscipit reiciendis possimus mollitia laboriosam, iste cumque, eaque provident totam quaerat minima nulla nisi vero delectus quia.</div>', 1, '2024-02-25 13:10:06', '2024-02-25 13:10:06'),
(5, 'M0005', 55, 3, 'Printed Black PU Leather', 49, 100, NULL, '<div style=\"line-height: 19px;\">Lorem ipsum dolor sit, amet consectetur adipisicing elit. Temporibus vel rem quis ratione suscipit reiciendis possimus mollitia laboriosam, iste cumque, eaque provident totam quaerat minima nulla nisi vero delectus quia.</div>', 1, '2024-02-25 13:10:31', '2024-05-25 07:06:48'),
(6, 'M0006', 56, 4, 'Double sided A Quality Zip', 50, 100, NULL, '<div style=\"line-height: 19px;\">Lorem ipsum dolor sit, amet consectetur adipisicing elit. Temporibus vel rem quis ratione suscipit reiciendis possimus mollitia laboriosam, iste cumque, eaque provident totam quaerat minima nulla nisi vero delectus quia.</div>', 1, '2024-02-25 13:11:20', '2024-05-25 07:06:48'),
(7, 'M0007', 56, 5, 'Single Sided Zip', 50, 100, NULL, '<div style=\"line-height: 19px;\">Lorem ipsum dolor sit, amet consectetur adipisicing elit. Temporibus vel rem quis ratione suscipit reiciendis possimus mollitia laboriosam, iste cumque, eaque provident totam quaerat minima nulla nisi vero delectus quia.</div>', 1, '2024-02-25 13:11:37', '2024-05-25 07:06:48'),
(8, 'M0008', 61, 6, 'Box #1 5KG', 52, 0, NULL, '<div style=\"line-height: 19px;\">Lorem ipsum dolor sit, amet consectetur adipisicing elit. Temporibus vel rem quis ratione suscipit reiciendis possimus mollitia laboriosam, iste cumque, eaque provident totam quaerat minima nulla nisi vero delectus quia.</div>', 1, '2024-02-25 13:12:12', '2024-04-27 10:31:31'),
(9, 'M0009', 61, 1, 'Box #2 7KG', 52, 0, NULL, '<div style=\"line-height: 19px;\">Lorem ipsum dolor sit, amet consectetur adipisicing elit. Temporibus vel rem quis ratione suscipit reiciendis possimus mollitia laboriosam, iste cumque, eaque provident totam quaerat minima nulla nisi vero delectus quia.</div>', 1, '2024-02-25 13:12:55', '2024-04-27 10:30:52'),
(10, 'M0010', 61, 3, 'Box #3 10KG', 52, 0, NULL, '<div style=\"line-height: 19px;\">Lorem ipsum dolor sit, amet consectetur adipisicing elit. Temporibus vel rem quis ratione suscipit reiciendis possimus mollitia laboriosam, iste cumque, eaque provident totam quaerat minima nulla nisi vero delectus quia.</div>', 1, '2024-02-25 13:13:14', '2024-04-27 10:30:48'),
(11, 'M0011', 63, 4, 'Hyviz Yellow Lycra', 51, 20, NULL, '<div style=\"line-height: 19px;\">Lorem ipsum dolor sit amet consectetur adipisicing elit. Alias, commodi deleniti, ea modi saepe aliquam consequatur dolorum error vel repudiandae, omnis soluta consectetur ipsa beatae architecto quidem fuga hic nisi!</div>', 1, '2024-02-25 13:24:00', '2024-07-01 08:01:35'),
(12, 'M0012', 66, 5, 'Sooter # 18', 48, 100, NULL, '<div style=\"line-height: 19px;\">Lorem ipsum dolor sit amet consectetur adipisicing elit. Alias, commodi deleniti, ea modi saepe aliquam consequatur dolorum error vel repudiandae, omnis soluta consectetur ipsa beatae architecto quidem fuga hic nisi!</div>', 1, '2024-02-25 13:24:45', '2024-05-25 07:06:48'),
(13, 'M0013', 66, 6, 'Hooks', 48, 100, NULL, '<p><span style=\"color: rgb(0, 0, 0);\">Lorem ipsum dolor sit amet consectetur adipisicing elit. Alias, commodi deleniti, ea modi saepe aliquam consequatur dolorum error vel repudiandae, omnis soluta consectetur ipsa beatae architecto quidem fuga hic nisi!</span><br></p>', 1, '2024-02-25 13:26:04', '2024-05-25 07:06:48'),
(14, 'M0014', 66, 4, 'Velcro Black', 50, 12, NULL, NULL, 1, '2024-04-25 03:26:29', '2025-04-06 15:13:26'),
(15, 'M0015', 96, 1, 'Vehicle Conatiner 0001', 97, 3000, NULL, '<p>Delivery Container</p>', 1, '2024-04-30 08:49:33', '2024-05-30 04:59:07'),
(16, 'M0016', 54, 11, 'Material 331', 49, 100, NULL, NULL, 1, '2024-05-15 12:27:30', '2024-05-25 07:06:48'),
(17, 'M0017', 54, 11, 'Material 332', 49, 100, NULL, NULL, 1, '2024-05-15 12:27:37', '2024-05-25 07:06:48'),
(18, 'M0018', 54, 11, 'Material 333', 49, 10, NULL, '<p>&nbsp;&nbsp;&nbsp;&nbsp;<br></p>', 1, '2024-05-15 12:27:46', '2024-05-25 07:06:48'),
(19, 'M0019', 54, 11, 'New Material M0001', 47, 100, NULL, '<p>Desc</p>', 1, '2024-05-22 13:45:34', '2024-05-25 06:30:20'),
(20, 'M0020', 54, 12, 'Check 2222', 47, 100, 'Rack 4', NULL, 1, '2024-05-23 08:20:13', '2024-05-23 08:20:13'),
(21, 'M0021', 54, 12, 'Check 333', 47, 100, 'Rack 5', NULL, 1, '2024-05-23 08:21:16', '2024-05-25 07:06:48'),
(22, 'M0022', 101, 12, 'Firki', 48, 10, 'Office', NULL, 1, '2024-05-24 08:56:29', '2024-05-24 08:57:20'),
(23, 'M0023', 101, 12, 'Needle', 48, 10, 'Office', NULL, 1, '2024-05-24 08:57:09', '2024-05-24 08:57:09'),
(24, 'M0024', 101, 12, 'Machine Belt', 48, 150, 'Office', '<p>&nbsp;&nbsp;&nbsp;&nbsp;<br></p>', 1, '2024-05-24 08:59:01', '2024-05-24 08:59:01'),
(25, 'M0025', 66, 12, 'Black Forway (Non Laminated)', 97, 100, 'Rack 10', NULL, 1, '2024-05-30 02:15:41', '2024-05-30 02:15:41'),
(26, 'M0026', 65, 7, 'Black Forway (Laminated - With Fusing)', 50, 100, 'Rack 20', '<p>Desc</p>', 1, '2024-05-30 02:16:20', '2024-05-30 02:29:43'),
(27, 'M0027', 66, 7, 'Embroidery for SAS', 50, 100, 'rack 101', NULL, 1, '2024-05-30 02:30:18', '2024-05-30 02:30:18'),
(28, 'M0028', 66, 6, 'Black forway sas embroidery laminated', 50, 100, 'radj 78', NULL, 1, '2024-05-30 02:39:36', '2024-05-30 02:39:36'),
(29, 'M0029', 65, 16, 'Material with OS', 52, 100, 'Rack 001', NULL, 1, '2024-08-17 06:21:17', '2024-08-17 06:21:17'),
(30, 'M0030', 65, 16, 'new OS', 52, 0, '123', NULL, 1, '2024-08-17 06:30:12', '2024-08-17 06:30:12'),
(31, 'M0031', 65, 16, 'abc', 52, 200, 'abc', NULL, 1, '2024-08-17 06:30:33', '2025-01-16 05:43:34');

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2014_10_12_000000_create_users_table', 1),
(2, '2014_10_12_100000_create_password_reset_tokens_table', 1),
(3, '2019_08_19_000000_create_failed_jobs_table', 1),
(4, '2019_12_14_000001_create_personal_access_tokens_table', 1),
(5, '2014_10_12_000000_create_roles_table', 2),
(6, '2025_01_11_150952_create_activity_logs_table', 3),
(7, '2025_01_12_063408_create_activity_log_table', 4),
(8, '2025_01_12_063409_add_event_column_to_activity_log_table', 4),
(9, '2025_01_12_063410_add_batch_uuid_column_to_activity_log_table', 4);

-- --------------------------------------------------------

--
-- Table structure for table `mprocess`
--

CREATE TABLE `mprocess` (
  `mprocess_id` bigint(20) UNSIGNED NOT NULL,
  `purchase_id` bigint(20) UNSIGNED NOT NULL,
  `purchase_item_id` bigint(20) UNSIGNED NOT NULL,
  `stock_item_id` bigint(20) UNSIGNED NOT NULL,
  `before_mid` bigint(20) UNSIGNED NOT NULL,
  `before_qty` double NOT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `mprocess`
--

INSERT INTO `mprocess` (`mprocess_id`, `purchase_id`, `purchase_item_id`, `stock_item_id`, `before_mid`, `before_qty`, `created_by`, `created_at`, `updated_at`) VALUES
(2, 39, 437, 297, 20, 100, 1, '2024-05-27 12:58:32', '2024-05-27 12:58:32'),
(5, 40, 440, 300, 16, 2, 1, '2024-05-27 14:24:37', '2024-05-27 14:24:37'),
(6, 40, 441, 301, 2, 100, 1, '2024-05-27 14:24:37', '2024-05-27 14:24:37'),
(7, 41, 442, 311, 1, 200, 1, '2024-05-29 04:57:20', '2024-05-29 04:57:20'),
(8, 42, 443, 312, 24, 12, 1, '2024-05-29 07:08:39', '2024-05-29 07:08:39'),
(9, 44, 446, 318, 25, 500, 1, '2024-05-30 02:20:48', '2024-05-30 02:20:48'),
(11, 45, 448, 320, 26, 800, 1, '2024-05-30 02:38:21', '2024-05-30 02:38:21'),
(13, 48, 451, 323, 24, 1, 1, '2024-05-30 05:23:29', '2024-05-30 05:23:29'),
(14, 48, 452, 324, 28, 12, 1, '2024-05-30 05:23:29', '2024-05-30 05:23:29'),
(15, 46, 453, 325, 28, 1, 1, '2024-05-30 05:26:39', '2024-05-30 05:26:39'),
(16, 49, 454, 326, 1, 90, 1, '2024-05-30 05:28:10', '2024-05-30 05:28:10'),
(17, 51, 457, 343, 27, 78.9, 1, '2024-08-17 05:59:23', '2024-08-17 05:59:23'),
(18, 52, 458, 344, 27, 12.12121212121212, 1, '2024-08-17 06:07:18', '2024-08-17 06:07:18'),
(19, 53, 459, 354, 27, 10, 1, '2024-09-02 01:57:20', '2024-09-02 01:57:20');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` bigint(20) UNSIGNED NOT NULL,
  `order_no` varchar(255) NOT NULL COMMENT 'From Client',
  `job_no` varchar(255) NOT NULL COMMENT 'Personal',
  `customer_id` bigint(20) UNSIGNED NOT NULL,
  `order_status` bigint(20) UNSIGNED NOT NULL DEFAULT 1 COMMENT '1 Pending, 2 Processing, 3 OnHold, 4 Partially Delivered, 5 Delivered, 6 Completed, 7 Cancelled, 8 Returned, 9 Disputed',
  `order_date` date NOT NULL,
  `description` longtext DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_id`, `order_no`, `job_no`, `customer_id`, `order_status`, `order_date`, `description`, `created_by`, `created_at`, `updated_at`) VALUES
(16, 'Order 101', 'Job 101', 6, 5, '2024-04-26', '<p>Order 101</p>', 1, '2024-04-26 13:36:18', '2025-01-16 05:32:39'),
(17, 'Order 202', 'Job 202', 5, 1, '2024-04-27', NULL, 1, '2024-04-27 10:32:56', '2024-04-27 10:32:56'),
(18, 'AS 101', 'ASJ 101', 6, 1, '2024-05-01', '<p>This is dummy order by Asad&nbsp;</p>', 1, '2024-05-01 02:41:22', '2024-05-25 13:26:16'),
(19, 'C101', 'J101', 6, 2, '2024-05-02', NULL, 1, '2024-05-02 05:56:39', '2024-05-09 13:36:16'),
(23, 'Order with Stage', 'Order with Stage', 6, 4, '2024-05-05', NULL, 1, '2024-05-05 00:46:18', '2024-05-09 13:52:55'),
(24, 'OrderMay23', 'JobMay23', 6, 1, '2024-05-23', NULL, 1, '2024-05-23 08:52:22', '2024-05-23 08:52:22'),
(25, 'Order 29', 'Job 29', 6, 1, '2024-05-29', 'Order with Currency and Exchange Rate', 1, '2024-05-29 13:56:28', '2024-05-29 13:56:28'),
(26, 'Order 30', 'Job 30', 1, 1, '2024-05-30', NULL, 1, '2024-05-29 14:17:27', '2024-05-29 14:17:27'),
(27, 'order 30 May', 'job 20 may', 5, 1, '2024-05-30', NULL, 1, '2024-05-30 02:13:13', '2024-05-30 02:13:13'),
(28, 'OK-101', 'JK - 101', 3, 1, '2024-07-01', NULL, 1, '2024-07-01 07:56:47', '2024-07-01 07:56:47'),
(29, 'check123', '123', 6, 1, '2024-08-10', '<p>Desc</p>', 1, '2024-08-10 05:49:50', '2024-08-10 05:49:50'),
(30, 'asdf', 'dsf', 6, 1, '2024-08-10', NULL, 1, '2024-08-10 05:53:17', '2024-08-10 05:53:17'),
(31, 'Surgical 101', 'Surgical 101', 6, 1, '2025-01-16', NULL, 1, '2025-01-16 03:22:20', '2025-01-16 03:22:20'),
(32, '123213', '123123', 6, 1, '2025-04-19', NULL, 1, '2025-04-19 03:40:26', '2025-04-19 03:40:26'),
(33, 'PP101', 'PP101', 6, 1, '2025-04-27', NULL, 1, '2025-04-27 04:32:50', '2025-04-27 04:32:50');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `order_item_id` bigint(20) UNSIGNED NOT NULL,
  `order_id` bigint(20) UNSIGNED NOT NULL,
  `product_type_id` bigint(20) UNSIGNED NOT NULL,
  `product_stage_id` bigint(20) UNSIGNED NOT NULL,
  `quantity` double UNSIGNED NOT NULL,
  `price` double UNSIGNED DEFAULT NULL,
  `price2` double UNSIGNED DEFAULT NULL,
  `head_id` bigint(20) UNSIGNED DEFAULT NULL,
  `exchange` double DEFAULT NULL,
  `total` double UNSIGNED NOT NULL COMMENT 'Quantity * Price',
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`order_item_id`, `order_id`, `product_type_id`, `product_stage_id`, `quantity`, `price`, `price2`, `head_id`, `exchange`, `total`, `created_by`, `created_at`, `updated_at`) VALUES
(59, 16, 49, 74, 1000, 0, NULL, 93, 280, 0, 1, '2024-04-26 13:36:18', '2024-04-26 13:36:18'),
(60, 16, 50, 74, 1000, 0, NULL, 93, 280, 0, 1, '2024-04-26 13:36:18', '2024-04-26 13:36:18'),
(61, 16, 51, 74, 1000, 0, NULL, 93, 280, 0, 1, '2024-04-26 13:36:18', '2024-04-26 13:36:18'),
(62, 16, 52, 74, 1000, 0, NULL, 93, 280, 0, 1, '2024-04-26 13:36:18', '2024-04-26 13:36:18'),
(63, 16, 53, 74, 1000, 0, NULL, 93, 280, 0, 1, '2024-04-26 13:36:18', '2024-04-26 13:36:18'),
(64, 16, 54, 74, 100, 0, NULL, 93, 280, 0, 1, '2024-04-26 13:36:51', '2024-04-26 13:36:51'),
(65, 16, 55, 74, 100, 0, NULL, 93, 280, 0, 1, '2024-04-26 13:36:51', '2024-04-26 13:36:51'),
(66, 16, 56, 74, 100, 0, NULL, 93, 280, 0, 1, '2024-04-26 13:36:51', '2024-04-26 13:36:51'),
(67, 17, 92, 74, 100, 1000, NULL, 93, 280, 100000, 1, '2024-04-27 10:32:56', '2024-04-27 10:32:56'),
(68, 17, 93, 74, 100, 1000, NULL, 93, 280, 100000, 1, '2024-04-27 10:32:56', '2024-04-27 10:32:56'),
(69, 17, 94, 74, 100, 1000, NULL, 93, 280, 100000, 1, '2024-04-27 10:32:56', '2024-04-27 10:32:56'),
(70, 18, 51, 74, 100, 1500, NULL, 93, 280, 150000, 1, '2024-05-01 02:41:22', '2024-05-01 02:41:22'),
(71, 18, 52, 74, 100, 1800, NULL, 93, 280, 180000, 1, '2024-05-01 02:41:22', '2024-05-01 02:41:22'),
(72, 19, 50, 74, 6000, 0, NULL, 93, 280, 0, 1, '2024-05-02 05:56:39', '2024-05-02 05:56:39'),
(73, 23, 49, 74, 20, 20, NULL, 93, 280, 400, 1, '2024-05-05 00:46:18', '2024-05-05 00:46:18'),
(75, 23, 70, 74, 20, 20, NULL, 93, 280, 400, 1, '2024-05-05 00:46:18', '2024-05-05 00:46:18'),
(76, 23, 52, 74, 12, 12, NULL, 93, 280, 144, 1, '2024-05-05 01:04:28', '2024-05-05 01:04:28'),
(77, 24, 68, 74, 120, 1200, 5, 93, 280, 144000, 1, '2024-05-23 08:52:22', '2024-05-23 08:58:24'),
(78, 24, 69, 74, 120, 1200, 5, 93, 280, 144000, 1, '2024-05-23 08:52:22', '2024-05-23 08:58:24'),
(79, 24, 70, 74, 120, 1200, 5, 93, 280, 144000, 1, '2024-05-23 08:52:22', '2024-05-23 08:57:41'),
(80, 25, 49, 72, 10, 1400, 5, 93, 280, 14000, 1, '2024-05-29 13:56:28', '2024-05-29 13:56:28'),
(81, 25, 50, 72, 10, 1400, 5, 93, 280, 14000, 1, '2024-05-29 13:56:28', '2024-05-29 13:56:28'),
(82, 19, 49, 74, 200, 2800, 10, 93, 280, 560000, 1, '2024-05-29 14:10:53', '2024-05-29 14:10:53'),
(83, 26, 68, 74, 200, 1200, 4, 93, 300, 240000, 1, '2024-05-29 14:17:27', '2024-05-29 14:21:15'),
(84, 26, 69, 74, 400, 1100, 0, 0, 0, 440000, 1, '2024-05-29 14:17:27', '2024-05-29 14:17:27'),
(85, 26, 70, 74, 500, 1120, 4, 93, 280, 560000, 1, '2024-05-29 14:17:27', '2024-05-29 14:17:27'),
(86, 27, 52, 72, 12, 2240, 8, 93, 280, 26880, 1, '2024-05-30 02:13:13', '2024-05-30 02:13:13'),
(87, 28, 50, 74, 100, 3312, 12, 93, 276, 331200, 1, '2024-07-01 07:56:47', '2024-07-01 07:56:47'),
(88, 28, 49, 74, 100, 3312, 12, 93, 276, 331200, 1, '2024-07-01 07:56:47', '2024-07-01 07:56:47'),
(89, 28, 51, 74, 100, 3312, 12, 93, 276, 331200, 1, '2024-07-01 07:56:47', '2024-07-01 07:56:47'),
(90, 29, 49, 74, 123, 4182, 123, 94, 34, 514386, 1, '2024-08-10 05:49:50', '2024-08-10 05:49:50'),
(91, 30, 49, 72, 123, 529, 23, 0, 23, 65067, 1, '2024-08-10 05:53:17', '2024-08-10 05:53:17'),
(92, 30, 52, 72, 2, 15129, 123, 106, 123, 30258, 1, '2024-08-10 05:54:41', '2024-08-10 05:54:41'),
(93, 31, 49, 74, 200, 2770, 10, 93, 277, 554000, 1, '2025-01-16 03:22:20', '2025-01-16 03:22:20'),
(94, 32, 103, 73, 12, 144, 12, 94, 12, 1728, 1, '2025-04-19 03:40:26', '2025-04-19 03:40:26'),
(95, 33, 106, 72, 20, 144, 12, 94, 12, 2880, 1, '2025-04-27 04:32:50', '2025-04-27 04:32:50'),
(96, 33, 105, 72, 20, 1344, 112, 94, 12, 26880, 1, '2025-04-27 04:32:50', '2025-04-27 04:32:50'),
(97, 33, 51, 72, 12, 264, 22, 94, 12, 3168, 1, '2025-04-27 04:32:50', '2025-04-27 04:32:50');

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `name`, `created_at`, `updated_at`) VALUES
(1, 'stocks_access', '2025-05-16 04:57:37', '2025-05-16 04:57:37'),
(2, 'stocks_show', '2025-05-16 04:57:37', '2025-05-16 04:57:37'),
(3, 'stocks_create', '2025-05-16 04:57:37', '2025-05-16 04:57:37'),
(4, 'stocks_edit', '2025-05-16 04:57:37', '2025-05-16 04:57:37'),
(5, 'stocks_delete', '2025-05-16 04:57:37', '2025-05-16 04:57:37'),
(6, 'issuances_access', '2025-05-16 04:57:37', '2025-05-16 04:57:37'),
(7, 'issuances_show', '2025-05-16 04:57:37', '2025-05-16 04:57:37'),
(8, 'issuances_create', '2025-05-16 04:57:37', '2025-05-16 04:57:37'),
(9, 'issuances_edit', '2025-05-16 04:57:37', '2025-05-16 04:57:37'),
(10, 'issuances_delete', '2025-05-16 04:57:37', '2025-05-16 04:57:37'),
(11, 'purchases_access', '2025-05-16 04:57:37', '2025-05-16 04:57:37'),
(12, 'purchases_show', '2025-05-16 04:57:37', '2025-05-16 04:57:37'),
(13, 'purchases_create', '2025-05-16 04:57:37', '2025-05-16 04:57:37'),
(14, 'purchases_edit', '2025-05-16 04:57:37', '2025-05-16 04:57:37'),
(15, 'purchases_delete', '2025-05-16 04:57:37', '2025-05-16 04:57:37'),
(16, 'transactions_access', '2025-05-16 04:57:37', '2025-05-16 04:57:37'),
(17, 'transactions_show', '2025-05-16 04:57:37', '2025-05-16 04:57:37'),
(18, 'transactions_create', '2025-05-16 04:57:37', '2025-05-16 04:57:37'),
(19, 'transactions_edit', '2025-05-16 04:57:37', '2025-05-16 04:57:37'),
(20, 'transactions_delete', '2025-05-16 04:57:37', '2025-05-16 04:57:37'),
(21, 'banks_access', '2025-05-16 04:57:37', '2025-05-16 04:57:37'),
(22, 'banks_show', '2025-05-16 04:57:37', '2025-05-16 04:57:37'),
(23, 'banks_create', '2025-05-16 04:57:37', '2025-05-16 04:57:37'),
(24, 'banks_edit', '2025-05-16 04:57:37', '2025-05-16 04:57:37'),
(25, 'banks_delete', '2025-05-16 04:57:37', '2025-05-16 04:57:37'),
(26, 'machines_access', '2025-05-16 04:57:37', '2025-05-16 04:57:37'),
(27, 'machines_show', '2025-05-16 04:57:37', '2025-05-16 04:57:37'),
(28, 'machines_create', '2025-05-16 04:57:37', '2025-05-16 04:57:37'),
(29, 'machines_edit', '2025-05-16 04:57:37', '2025-05-16 04:57:37'),
(30, 'machines_delete', '2025-05-16 04:57:37', '2025-05-16 04:57:37'),
(31, 'orders_access', '2025-05-16 04:57:37', '2025-05-16 04:57:37'),
(32, 'orders_show', '2025-05-16 04:57:37', '2025-05-16 04:57:37'),
(33, 'orders_create', '2025-05-16 04:57:37', '2025-05-16 04:57:37'),
(34, 'orders_edit', '2025-05-16 04:57:37', '2025-05-16 04:57:37'),
(35, 'orders_delete', '2025-05-16 04:57:37', '2025-05-16 04:57:37'),
(36, 'deliveries_access', '2025-05-16 04:57:37', '2025-05-16 04:57:37'),
(37, 'deliveries_show', '2025-05-16 04:57:37', '2025-05-16 04:57:37'),
(38, 'deliveries_create', '2025-05-16 04:57:37', '2025-05-16 04:57:37'),
(39, 'deliveries_edit', '2025-05-16 04:57:37', '2025-05-16 04:57:37'),
(40, 'deliveries_delete', '2025-05-16 04:57:37', '2025-05-16 04:57:37'),
(41, 'customers_access', '2025-05-16 04:57:37', '2025-05-16 04:57:37'),
(42, 'customers_show', '2025-05-16 04:57:37', '2025-05-16 04:57:37'),
(43, 'customers_create', '2025-05-16 04:57:37', '2025-05-16 04:57:37'),
(44, 'customers_edit', '2025-05-16 04:57:37', '2025-05-16 04:57:37'),
(45, 'customers_delete', '2025-05-16 04:57:37', '2025-05-16 04:57:37'),
(46, 'employees_access', '2025-05-16 04:57:37', '2025-05-16 04:57:37'),
(47, 'employees_show', '2025-05-16 04:57:37', '2025-05-16 04:57:37'),
(48, 'employees_create', '2025-05-16 04:57:37', '2025-05-16 04:57:37'),
(49, 'employees_edit', '2025-05-16 04:57:37', '2025-05-16 04:57:37'),
(50, 'employees_delete', '2025-05-16 04:57:37', '2025-05-16 04:57:37'),
(51, 'vendors_access', '2025-05-16 04:57:37', '2025-05-16 04:57:37'),
(52, 'vendors_show', '2025-05-16 04:57:37', '2025-05-16 04:57:37'),
(53, 'vendors_create', '2025-05-16 04:57:37', '2025-05-16 04:57:37'),
(54, 'vendors_edit', '2025-05-16 04:57:37', '2025-05-16 04:57:37'),
(55, 'vendors_delete', '2025-05-16 04:57:37', '2025-05-16 04:57:37'),
(56, 'contractors_access', '2025-05-16 04:57:37', '2025-05-16 04:57:37'),
(57, 'contractors_show', '2025-05-16 04:57:37', '2025-05-16 04:57:37'),
(58, 'contractors_create', '2025-05-16 04:57:37', '2025-05-16 04:57:37'),
(59, 'contractors_edit', '2025-05-16 04:57:37', '2025-05-16 04:57:37'),
(60, 'contractors_delete', '2025-05-16 04:57:37', '2025-05-16 04:57:37'),
(61, 'products_access', '2025-05-16 04:57:37', '2025-05-16 04:57:37'),
(62, 'products_show', '2025-05-16 04:57:37', '2025-05-16 04:57:37'),
(63, 'products_create', '2025-05-16 04:57:37', '2025-05-16 04:57:37'),
(64, 'products_edit', '2025-05-16 04:57:37', '2025-05-16 04:57:37'),
(65, 'products_delete', '2025-05-16 04:57:37', '2025-05-16 04:57:37'),
(66, 'materials_access', '2025-05-16 04:57:37', '2025-05-16 04:57:37'),
(67, 'materials_show', '2025-05-16 04:57:37', '2025-05-16 04:57:37'),
(68, 'materials_create', '2025-05-16 04:57:37', '2025-05-16 04:57:37'),
(69, 'materials_edit', '2025-05-16 04:57:37', '2025-05-16 04:57:37'),
(70, 'materials_delete', '2025-05-16 04:57:37', '2025-05-16 04:57:37'),
(71, 'attendance_access', '2025-05-16 04:57:37', '2025-05-16 04:57:37'),
(72, 'attendance_show', '2025-05-16 04:57:37', '2025-05-16 04:57:37'),
(73, 'attendance_create', '2025-05-16 04:57:37', '2025-05-16 04:57:37'),
(74, 'attendance_edit', '2025-05-16 04:57:37', '2025-05-16 04:57:37'),
(75, 'attendance_delete', '2025-05-16 04:57:37', '2025-05-16 04:57:37'),
(76, 'payroll_access', '2025-05-16 04:57:37', '2025-05-16 04:57:37'),
(77, 'payroll_show', '2025-05-16 04:57:37', '2025-05-16 04:57:37'),
(78, 'payroll_create', '2025-05-16 04:57:37', '2025-05-16 04:57:37'),
(79, 'payroll_edit', '2025-05-16 04:57:37', '2025-05-16 04:57:37'),
(80, 'payroll_delete', '2025-05-16 04:57:37', '2025-05-16 04:57:37'),
(81, 'reports_access', '2025-05-16 04:57:37', '2025-05-16 04:57:37'),
(82, 'reports_show', '2025-05-16 04:57:37', '2025-05-16 04:57:37'),
(83, 'reports_create', '2025-05-16 04:57:37', '2025-05-16 04:57:37'),
(84, 'reports_edit', '2025-05-16 04:57:37', '2025-05-16 04:57:37'),
(85, 'reports_delete', '2025-05-16 04:57:37', '2025-05-16 04:57:37'),
(86, 'settings_access', '2025-05-16 04:57:37', '2025-05-16 04:57:37'),
(87, 'settings_show', '2025-05-16 04:57:37', '2025-05-16 04:57:37'),
(88, 'settings_create', '2025-05-16 04:57:37', '2025-05-16 04:57:37'),
(89, 'settings_edit', '2025-05-16 04:57:37', '2025-05-16 04:57:37'),
(90, 'settings_delete', '2025-05-16 04:57:37', '2025-05-16 04:57:37');

-- --------------------------------------------------------

--
-- Table structure for table `permission_role`
--

CREATE TABLE `permission_role` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `role_id` bigint(255) UNSIGNED NOT NULL,
  `permission_id` bigint(255) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `permission_role`
--

INSERT INTO `permission_role` (`id`, `role_id`, `permission_id`) VALUES
(2, 2, 1),
(3, 3, 1),
(5, 2, 2),
(6, 3, 2),
(8, 2, 3),
(12, 2, 6),
(13, 3, 6),
(15, 2, 7),
(16, 3, 7),
(18, 2, 8),
(22, 2, 11),
(23, 3, 11),
(25, 2, 12),
(26, 3, 12),
(28, 2, 13),
(32, 2, 16),
(33, 3, 16),
(35, 2, 17),
(36, 3, 17),
(38, 2, 18),
(42, 2, 21),
(43, 3, 21),
(45, 2, 22),
(46, 3, 22),
(48, 2, 23),
(52, 2, 26),
(53, 3, 26),
(55, 2, 27),
(56, 3, 27),
(58, 2, 28),
(62, 2, 31),
(63, 3, 31),
(65, 2, 32),
(66, 3, 32),
(68, 2, 33),
(72, 2, 36),
(73, 3, 36),
(75, 2, 37),
(76, 3, 37),
(78, 2, 38),
(82, 2, 41),
(83, 3, 41),
(85, 2, 42),
(86, 3, 42),
(88, 2, 43),
(92, 2, 46),
(93, 3, 46),
(95, 2, 47),
(96, 3, 47),
(98, 2, 48),
(102, 2, 51),
(103, 3, 51),
(105, 2, 52),
(106, 3, 52),
(108, 2, 53),
(112, 2, 56),
(113, 3, 56),
(115, 2, 57),
(116, 3, 57),
(118, 2, 58),
(122, 2, 61),
(123, 3, 61),
(125, 2, 62),
(126, 3, 62),
(128, 2, 63),
(132, 2, 66),
(133, 3, 66),
(135, 2, 67),
(136, 3, 67),
(138, 2, 68),
(142, 2, 71),
(143, 3, 71),
(145, 2, 72),
(146, 3, 72),
(148, 2, 73),
(152, 2, 76),
(153, 3, 76),
(155, 2, 77),
(156, 3, 77),
(158, 2, 78),
(162, 2, 81),
(163, 3, 81),
(165, 2, 82),
(166, 3, 82),
(168, 2, 83),
(172, 2, 86),
(173, 3, 86),
(175, 2, 87),
(176, 3, 87),
(178, 2, 88),
(188, 8, 1),
(189, 8, 2),
(190, 8, 3),
(191, 8, 4),
(192, 8, 5),
(194, 2, 91),
(195, 3, 91),
(197, 2, 92),
(198, 3, 92),
(200, 2, 93),
(204, 2, 96),
(205, 3, 96),
(207, 2, 97),
(208, 3, 97),
(210, 2, 98),
(214, 2, 101),
(215, 3, 101),
(217, 2, 102),
(218, 3, 102),
(220, 2, 103),
(224, 2, 106),
(225, 3, 106),
(227, 2, 107),
(228, 3, 107),
(230, 2, 108),
(234, 2, 111),
(235, 3, 111),
(237, 2, 112),
(238, 3, 112),
(240, 2, 113),
(421, 1, 1),
(422, 1, 2),
(423, 1, 3),
(424, 1, 4),
(425, 1, 5),
(426, 1, 6),
(427, 1, 7),
(428, 1, 8),
(429, 1, 9),
(430, 1, 10),
(431, 1, 11),
(432, 1, 12),
(433, 1, 13),
(434, 1, 14),
(435, 1, 15),
(436, 1, 16),
(437, 1, 17),
(438, 1, 18),
(439, 1, 19),
(440, 1, 20),
(441, 1, 21),
(442, 1, 22),
(443, 1, 23),
(444, 1, 24),
(445, 1, 25),
(446, 1, 26),
(447, 1, 27),
(448, 1, 28),
(449, 1, 29),
(450, 1, 30),
(451, 1, 31),
(452, 1, 32),
(453, 1, 33),
(454, 1, 34),
(455, 1, 35),
(456, 1, 36),
(457, 1, 37),
(458, 1, 38),
(459, 1, 39),
(460, 1, 40),
(461, 1, 41),
(462, 1, 42),
(463, 1, 43),
(464, 1, 44),
(465, 1, 45),
(466, 1, 46),
(467, 1, 47),
(468, 1, 48),
(469, 1, 49),
(470, 1, 50),
(471, 1, 51),
(472, 1, 52),
(473, 1, 53),
(474, 1, 54),
(475, 1, 55),
(476, 1, 56),
(477, 1, 57),
(478, 1, 58),
(479, 1, 59),
(480, 1, 60),
(481, 1, 61),
(482, 1, 62),
(483, 1, 63),
(484, 1, 64),
(485, 1, 65),
(486, 1, 66),
(487, 1, 67),
(488, 1, 68),
(489, 1, 69),
(490, 1, 70),
(491, 1, 71),
(492, 1, 72),
(493, 1, 73),
(494, 1, 74),
(495, 1, 75),
(496, 1, 76),
(497, 1, 77),
(498, 1, 78),
(499, 1, 79),
(500, 1, 80),
(501, 1, 81),
(502, 1, 82),
(503, 1, 83),
(504, 1, 84),
(505, 1, 85),
(506, 1, 86),
(507, 1, 87),
(508, 1, 88),
(509, 1, 89),
(510, 1, 90);

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `product_id` bigint(255) UNSIGNED NOT NULL,
  `category_id` bigint(20) UNSIGNED NOT NULL,
  `material_id` varchar(255) NOT NULL,
  `stage_ids` varchar(255) NOT NULL,
  `article_no` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` longtext DEFAULT NULL,
  `unit_id` bigint(20) UNSIGNED NOT NULL,
  `product_status` bigint(20) UNSIGNED NOT NULL DEFAULT 1,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`product_id`, `category_id`, `material_id`, `stage_ids`, `article_no`, `name`, `description`, `unit_id`, `product_status`, `created_by`, `created_at`, `updated_at`) VALUES
(6, 2, '14|13|12|11', '72|74|73', 'WG-101', 'Working Gloves 101', '<p style=\"line-height: 19px;\">Lorem ipsum dolor sit amet consectetur adipisicing elit. Non voluptas accusamus doloremque aspernatur cum vero magnam tempora reiciendis reprehenderit earum ipsam, officia labore ficilis dolores, commodi facere provident, obceacati odio!﻿</p>', 47, 1, 1, '2024-02-19 13:20:03', '2024-08-22 13:42:07'),
(10, 1, '14|13|12|11', '72|73|74', 'BG-251', 'Boxing Gloves 151', '<div style=\"line-height: 19px;\">Lorem ipsum dolor sit amet consectetur adipisicing elit. Non voluptas accusamus doloremque aspernatur cum vero magnam tempora reiciendis reprehenderit earum ipsam, officia labore ficilis dolores, commodi facere provident, obceacati odio!</div>', 47, 1, 1, '2024-02-25 12:58:59', '2024-05-04 12:55:06'),
(11, 8, '14|13|12', '72|73|74', 'LG-301', 'Leather Gloves 301', '<p><span style=\"color: rgb(0, 0, 0);\">Lorem ipsum dolor sit amet consectetur adipisicing elit. Non voluptas accusamus doloremque aspernatur cum vero magnam tempora reiciendis reprehenderit earum ipsam, officia labore ficilis dolores, commodi facere provident, obceacati odio!</span><br></p>', 47, 1, 1, '2024-02-25 12:59:43', '2024-05-04 12:54:54'),
(12, 9, '7|6|5|4', '72|73|74', 'WinG-515', 'Winter Gloves 515', '<p><span style=\"color: rgb(0, 0, 0);\">Lorem ipsum dolor sit amet consectetur adipisicing elit. Non voluptas accusamus doloremque aspernatur cum vero magnam tempora reiciendis reprehenderit earum ipsam, officia labore ficilis dolores, commodi facere provident, obceacati odio!</span><br></p>', 47, 1, 1, '2024-02-25 13:00:37', '2024-05-04 12:54:44'),
(23, 8, '14|13|12|11|7', '72|73|74', 'LG - 4001', 'Leather Gloves 4001', '<p>Desc</p>', 47, 1, 1, '2024-04-25 11:43:02', '2024-05-04 12:54:32'),
(24, 8, '14|13|12|11', '72|73|74', 'LG - 6010', 'Leather Gloves 6010', '<p>Desc</p>', 47, 1, 1, '2024-04-25 11:44:01', '2024-05-04 12:54:21'),
(25, 1, '14|13|12|11', '72|74|73', 'BG 5050', 'Boxing Gloves 5050', NULL, 47, 1, 1, '2024-04-27 07:16:05', '2024-05-28 09:49:19'),
(26, 1, '14', '72|73|74', 'NH-1-14-EC', 'Mayo Hegar Needle Holder', NULL, 48, 1, 1, '2024-05-02 05:42:53', '2024-05-04 12:53:58'),
(27, 1, '15|14|13|12|11', '72|73|74', 'BG - 901', 'Boxing Gloves 901', NULL, 47, 1, 1, '2024-05-04 06:10:49', '2024-05-04 06:13:51'),
(28, 2, '14|12|11', '72|74|73', 'WG- 2520', 'Working Gloves 2520', NULL, 47, 1, 1, '2024-05-10 07:33:11', '2024-09-02 00:30:28'),
(29, 1, '31|30|29', '72|74|73', 'Beaker 101', 'Beaker of steel', '<p>any</p>', 47, 1, 1, '2025-01-16 03:29:25', '2025-01-16 03:29:25'),
(30, 2, '31', '72', 'PP101', 'Purchase Product P101', NULL, 49, 1, 1, '2025-04-27 04:25:19', '2025-04-27 04:25:19');

-- --------------------------------------------------------

--
-- Table structure for table `product_costs`
--

CREATE TABLE `product_costs` (
  `product_cost_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `table_name` varchar(255) NOT NULL,
  `table_id` bigint(20) UNSIGNED NOT NULL,
  `head_id` bigint(20) UNSIGNED NOT NULL,
  `amount` double UNSIGNED NOT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_costs`
--

INSERT INTO `product_costs` (`product_cost_id`, `product_id`, `table_name`, `table_id`, `head_id`, `amount`, `created_by`, `created_at`, `updated_at`) VALUES
(63, 6, 'general', 0, 78, 12, 1, '2024-04-26 13:16:34', '2024-04-26 13:16:34'),
(64, 6, 'general', 0, 79, 12, 1, '2024-04-26 13:16:34', '2024-04-26 13:16:34'),
(65, 6, 'general', 0, 80, 12, 1, '2024-04-26 13:16:34', '2024-04-26 13:16:34'),
(66, 6, 'general', 0, 81, 12, 1, '2024-04-26 13:16:34', '2024-04-26 13:16:34'),
(67, 24, 'general', 0, 78, 12, 1, '2024-04-26 13:18:51', '2024-04-26 13:18:51'),
(68, 24, 'general', 0, 79, 12, 1, '2024-04-26 13:18:51', '2024-04-26 13:18:51'),
(69, 24, 'general', 0, 80, 12, 1, '2024-04-26 13:18:51', '2024-04-26 13:18:51'),
(70, 24, 'general', 0, 81, 12, 1, '2024-04-26 13:18:51', '2024-04-26 13:18:51'),
(71, 24, 'general', 0, 83, 12, 1, '2024-04-26 13:18:51', '2024-04-26 13:18:51'),
(72, 24, 'general', 0, 84, 12, 1, '2024-04-26 13:18:51', '2024-04-26 13:18:51'),
(73, 23, 'general', 0, 78, 12, 1, '2024-04-26 13:19:23', '2024-04-26 13:19:23'),
(74, 23, 'general', 0, 79, 12, 1, '2024-04-26 13:19:23', '2024-04-26 13:19:23'),
(75, 23, 'general', 0, 80, 12, 1, '2024-04-26 13:19:23', '2024-04-26 13:19:23'),
(76, 23, 'general', 0, 81, 12, 1, '2024-04-26 13:19:23', '2024-04-26 13:19:23'),
(77, 23, 'general', 0, 82, 12, 1, '2024-04-26 13:19:23', '2024-04-26 13:19:23'),
(78, 10, 'general', 0, 78, 12, 1, '2024-04-26 13:32:42', '2024-04-26 13:32:42'),
(79, 10, 'general', 0, 79, 12, 1, '2024-04-26 13:32:42', '2024-04-26 13:32:42'),
(80, 10, 'general', 0, 80, 12, 1, '2024-04-26 13:32:42', '2024-04-26 13:32:42'),
(81, 10, 'general', 0, 81, 12, 1, '2024-04-26 13:32:42', '2024-04-26 13:32:42'),
(82, 10, 'general', 0, 82, 12, 1, '2024-04-26 13:32:42', '2024-04-26 13:32:42'),
(83, 10, 'general', 0, 83, 12, 1, '2024-04-26 13:32:42', '2024-04-26 13:32:42'),
(84, 11, 'general', 0, 78, 12, 1, '2024-04-26 13:33:54', '2024-04-26 13:33:54'),
(85, 11, 'general', 0, 79, 12, 1, '2024-04-26 13:33:54', '2024-04-26 13:33:54'),
(86, 11, 'general', 0, 80, 12, 1, '2024-04-26 13:33:54', '2024-04-26 13:33:54'),
(87, 11, 'general', 0, 81, 12, 1, '2024-04-26 13:33:54', '2024-04-26 13:33:54'),
(88, 11, 'general', 0, 82, 12, 1, '2024-04-26 13:33:54', '2024-04-26 13:33:54'),
(89, 11, 'general', 0, 83, 12, 1, '2024-04-26 13:33:54', '2024-04-26 13:33:54'),
(90, 12, 'general', 0, 78, 12, 1, '2024-04-26 13:34:23', '2024-04-26 13:34:23'),
(91, 12, 'general', 0, 79, 12, 1, '2024-04-26 13:34:23', '2024-04-26 13:34:23'),
(92, 12, 'general', 0, 80, 12, 1, '2024-04-26 13:34:23', '2024-04-26 13:34:23'),
(93, 12, 'general', 0, 81, 12, 1, '2024-04-26 13:34:23', '2024-04-26 13:34:23'),
(94, 12, 'general', 0, 82, 12, 1, '2024-04-26 13:34:23', '2024-04-26 13:34:23'),
(95, 12, 'general', 0, 83, 12, 1, '2024-04-26 13:34:23', '2024-04-26 13:34:23'),
(96, 12, 'general', 0, 84, 12, 1, '2024-04-26 13:34:23', '2024-04-26 13:34:23'),
(97, 25, 'general', 0, 78, 12, 1, '2024-04-27 07:20:24', '2024-04-27 07:20:24'),
(98, 25, 'general', 0, 79, 12, 1, '2024-04-27 07:20:24', '2024-04-27 07:20:24'),
(99, 25, 'general', 0, 80, 12, 1, '2024-04-27 07:20:24', '2024-04-27 07:20:24'),
(100, 25, 'general', 0, 81, 12, 1, '2024-04-27 07:20:24', '2024-04-27 07:20:24'),
(101, 25, 'general', 0, 82, 12, 1, '2024-04-27 07:20:24', '2024-04-27 07:20:24'),
(102, 25, 'general', 0, 83, 12, 1, '2024-04-27 07:20:24', '2024-04-27 07:20:24'),
(103, 25, 'general', 0, 84, 12, 1, '2024-04-27 07:20:24', '2024-04-27 07:20:24'),
(104, 23, 'general', 0, 84, 10, 1, '2024-04-30 08:17:10', '2024-04-30 08:17:10'),
(105, 24, 'vendor', 6, 78, 24, 1, '2024-05-01 03:05:55', '2024-05-01 03:05:55'),
(106, 28, 'general', 0, 78, 12, 1, '2024-05-10 07:35:19', '2024-05-10 07:35:19'),
(107, 28, 'vendor', 7, 78, 13, 1, '2024-05-10 07:35:19', '2024-05-10 07:35:19'),
(108, 28, 'general', 0, 79, 9, 1, '2024-09-02 02:18:15', '2024-09-02 02:18:15'),
(109, 28, 'general', 0, 82, 15, 1, '2024-09-02 02:18:15', '2024-09-02 02:18:15'),
(110, 29, 'general', 0, 78, 10, 1, '2025-01-16 03:38:00', '2025-01-16 03:38:00');

-- --------------------------------------------------------

--
-- Table structure for table `product_materials`
--

CREATE TABLE `product_materials` (
  `product_material_id` bigint(20) UNSIGNED NOT NULL,
  `product_type_id` bigint(20) NOT NULL,
  `material_id` bigint(20) NOT NULL,
  `quantity` double UNSIGNED NOT NULL,
  `created_by` bigint(20) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_materials`
--

INSERT INTO `product_materials` (`product_material_id`, `product_type_id`, `material_id`, `quantity`, `created_by`, `created_at`, `updated_at`) VALUES
(416, 49, 11, 2, 1, '2024-04-26 13:54:42', '2024-04-26 13:54:42'),
(417, 49, 12, 3, 1, '2024-04-26 13:54:42', '2024-04-26 13:54:42'),
(418, 49, 13, 2, 1, '2024-04-26 13:54:42', '2024-04-26 13:54:42'),
(419, 49, 14, 3, 1, '2024-04-26 13:54:42', '2024-04-26 13:54:42'),
(420, 50, 11, 2, 1, '2024-04-26 13:54:47', '2024-04-26 13:54:47'),
(421, 50, 12, 3, 1, '2024-04-26 13:54:47', '2024-04-26 13:54:47'),
(422, 50, 13, 2, 1, '2024-04-26 13:54:47', '2024-04-26 13:54:47'),
(423, 50, 14, 3, 1, '2024-04-26 13:54:47', '2024-04-26 13:54:47'),
(424, 51, 11, 2, 1, '2024-04-26 13:54:52', '2024-04-26 13:54:52'),
(425, 51, 12, 3, 1, '2024-04-26 13:54:52', '2024-04-26 13:54:52'),
(426, 51, 13, 2, 1, '2024-04-26 13:54:52', '2024-04-26 13:54:52'),
(427, 51, 14, 3, 1, '2024-04-26 13:54:52', '2024-04-26 13:54:52'),
(428, 52, 11, 2, 1, '2024-04-26 13:54:57', '2024-04-26 13:54:57'),
(429, 52, 12, 3, 1, '2024-04-26 13:54:57', '2024-04-26 13:54:57'),
(430, 52, 13, 2, 1, '2024-04-26 13:54:57', '2024-04-26 13:54:57'),
(431, 52, 14, 3, 1, '2024-04-26 13:54:57', '2024-04-26 13:54:57'),
(432, 53, 11, 2, 1, '2024-04-26 13:55:03', '2024-04-26 13:55:03'),
(433, 53, 12, 3, 1, '2024-04-26 13:55:03', '2024-04-26 13:55:03'),
(434, 53, 13, 2, 1, '2024-04-26 13:55:03', '2024-04-26 13:55:03'),
(435, 53, 14, 3, 1, '2024-04-26 13:55:03', '2024-04-26 13:55:03'),
(436, 54, 7, 2, 1, '2024-04-26 13:55:27', '2024-04-26 13:55:27'),
(437, 54, 11, 3, 1, '2024-04-26 13:55:27', '2024-04-26 13:55:27'),
(438, 54, 12, 2, 1, '2024-04-26 13:55:27', '2024-04-26 13:55:27'),
(439, 54, 13, 3, 1, '2024-04-26 13:55:27', '2024-04-26 13:55:27'),
(440, 54, 14, 2, 1, '2024-04-26 13:55:27', '2024-04-26 13:55:27'),
(441, 55, 7, 2, 1, '2024-04-26 13:55:32', '2024-04-26 13:55:32'),
(442, 55, 11, 3, 1, '2024-04-26 13:55:32', '2024-04-26 13:55:32'),
(443, 55, 12, 2, 1, '2024-04-26 13:55:32', '2024-04-26 13:55:32'),
(444, 55, 13, 3, 1, '2024-04-26 13:55:32', '2024-04-26 13:55:32'),
(445, 55, 14, 2, 1, '2024-04-26 13:55:32', '2024-04-26 13:55:32'),
(446, 56, 7, 2, 1, '2024-04-26 13:55:37', '2024-04-26 13:55:37'),
(447, 56, 11, 3, 1, '2024-04-26 13:55:37', '2024-04-26 13:55:37'),
(448, 56, 12, 2, 1, '2024-04-26 13:55:37', '2024-04-26 13:55:37'),
(449, 56, 13, 3, 1, '2024-04-26 13:55:37', '2024-04-26 13:55:37'),
(450, 56, 14, 2, 1, '2024-04-26 13:55:37', '2024-04-26 13:55:37'),
(451, 68, 4, 3, 1, '2024-04-26 13:56:02', '2024-04-26 13:56:02'),
(452, 68, 5, 4, 1, '2024-04-26 13:56:02', '2024-04-26 13:56:02'),
(453, 68, 6, 3, 1, '2024-04-26 13:56:02', '2024-04-26 13:56:02'),
(454, 68, 7, 4, 1, '2024-04-26 13:56:02', '2024-04-26 13:56:02'),
(455, 69, 4, 3, 1, '2024-04-26 13:56:07', '2024-04-26 13:56:07'),
(456, 69, 5, 4, 1, '2024-04-26 13:56:07', '2024-04-26 13:56:07'),
(457, 69, 6, 3, 1, '2024-04-26 13:56:07', '2024-04-26 13:56:07'),
(458, 69, 7, 4, 1, '2024-04-26 13:56:07', '2024-04-26 13:56:07'),
(459, 70, 4, 3, 1, '2024-04-26 13:56:11', '2024-04-26 13:56:11'),
(460, 70, 5, 4, 1, '2024-04-26 13:56:11', '2024-04-26 13:56:11'),
(461, 70, 6, 3, 1, '2024-04-26 13:56:11', '2024-04-26 13:56:11'),
(462, 70, 7, 4, 1, '2024-04-26 13:56:11', '2024-04-26 13:56:11'),
(463, 71, 12, 2, 1, '2024-04-26 13:56:27', '2024-04-26 13:56:27'),
(464, 71, 13, 3, 1, '2024-04-26 13:56:27', '2024-04-26 13:56:27'),
(465, 71, 14, 4, 1, '2024-04-26 13:56:27', '2024-04-26 13:56:27'),
(466, 72, 12, 2, 1, '2024-04-26 13:56:36', '2024-04-26 13:56:36'),
(467, 72, 13, 3, 1, '2024-04-26 13:56:36', '2024-04-26 13:56:36'),
(468, 72, 14, 4, 1, '2024-04-26 13:56:36', '2024-04-26 13:56:36'),
(469, 73, 12, 2, 1, '2024-04-26 13:56:42', '2024-04-26 13:56:42'),
(470, 73, 13, 3, 1, '2024-04-26 13:56:42', '2024-04-26 13:56:42'),
(471, 73, 14, 4, 1, '2024-04-26 13:56:42', '2024-04-26 13:56:42'),
(472, 74, 12, 2, 1, '2024-04-26 13:56:48', '2024-04-26 13:56:48'),
(473, 74, 13, 3, 1, '2024-04-26 13:56:48', '2024-04-26 13:56:48'),
(474, 74, 14, 4, 1, '2024-04-26 13:56:48', '2024-04-26 13:56:48'),
(475, 75, 11, 4, 1, '2024-04-26 13:57:12', '2024-04-26 13:57:12'),
(476, 75, 12, 3, 1, '2024-04-26 13:57:12', '2024-04-26 13:57:12'),
(477, 75, 13, 4, 1, '2024-04-26 13:57:12', '2024-04-26 13:57:12'),
(478, 75, 14, 3, 1, '2024-04-26 13:57:12', '2024-04-26 13:57:12'),
(479, 76, 11, 4, 1, '2024-04-26 13:57:17', '2024-04-26 13:57:17'),
(480, 76, 12, 3, 1, '2024-04-26 13:57:17', '2024-04-26 13:57:17'),
(481, 76, 13, 4, 1, '2024-04-26 13:57:17', '2024-04-26 13:57:17'),
(482, 76, 14, 3, 1, '2024-04-26 13:57:17', '2024-04-26 13:57:17'),
(483, 77, 11, 4, 1, '2024-04-26 13:57:22', '2024-04-26 13:57:22'),
(484, 77, 12, 3, 1, '2024-04-26 13:57:22', '2024-04-26 13:57:22'),
(485, 77, 13, 4, 1, '2024-04-26 13:57:22', '2024-04-26 13:57:22'),
(486, 77, 14, 3, 1, '2024-04-26 13:57:22', '2024-04-26 13:57:22'),
(487, 78, 11, 4, 1, '2024-04-26 13:57:27', '2024-04-26 13:57:27'),
(488, 78, 12, 3, 1, '2024-04-26 13:57:27', '2024-04-26 13:57:27'),
(489, 78, 13, 4, 1, '2024-04-26 13:57:27', '2024-04-26 13:57:27'),
(490, 78, 14, 3, 1, '2024-04-26 13:57:27', '2024-04-26 13:57:27'),
(491, 89, 11, 2, 1, '2024-04-26 13:57:48', '2024-04-26 13:57:48'),
(492, 89, 12, 3, 1, '2024-04-26 13:57:48', '2024-04-26 13:57:48'),
(493, 89, 13, 2, 1, '2024-04-26 13:57:48', '2024-04-26 13:57:48'),
(494, 89, 14, 3, 1, '2024-04-26 13:57:48', '2024-04-26 13:57:48'),
(495, 90, 11, 2, 1, '2024-04-26 13:57:54', '2024-04-26 13:57:54'),
(496, 90, 12, 3, 1, '2024-04-26 13:57:54', '2024-04-26 13:57:54'),
(497, 90, 13, 2, 1, '2024-04-26 13:57:54', '2024-04-26 13:57:54'),
(498, 90, 14, 3, 1, '2024-04-26 13:57:54', '2024-04-26 13:57:54'),
(499, 91, 11, 2, 1, '2024-04-26 13:57:59', '2024-04-27 06:50:19'),
(500, 91, 12, 3, 1, '2024-04-26 13:57:59', '2024-04-26 13:57:59'),
(501, 91, 13, 2, 1, '2024-04-26 13:57:59', '2024-04-26 13:57:59'),
(502, 91, 14, 3, 1, '2024-04-26 13:57:59', '2024-04-26 13:57:59'),
(503, 92, 11, 2, 1, '2024-04-27 07:17:19', '2024-04-27 07:17:19'),
(504, 92, 12, 3, 1, '2024-04-27 07:17:19', '2024-04-27 07:17:19'),
(505, 92, 13, 4, 1, '2024-04-27 07:17:19', '2024-04-27 07:17:19'),
(506, 92, 14, 4, 1, '2024-04-27 07:17:19', '2024-04-27 07:17:19'),
(507, 93, 1, 3, 1, '2024-04-27 08:28:57', '2024-04-27 08:28:57'),
(509, 94, 11, 2, 1, '2024-04-27 08:29:59', '2024-04-27 08:29:59'),
(510, 94, 12, 2, 1, '2024-04-27 08:29:59', '2024-04-27 08:29:59'),
(511, 94, 13, 2, 1, '2024-04-27 08:29:59', '2024-04-27 08:29:59'),
(512, 94, 14, 2, 1, '2024-04-27 08:29:59', '2024-04-27 08:29:59'),
(513, 94, 10, 0.02, 1, '2024-04-27 08:29:59', '2024-04-29 11:10:27'),
(514, 93, 10, 0.03333333333333333, 1, '2024-04-27 11:07:36', '2024-04-27 11:07:36'),
(515, 92, 10, 0.03333333333333333, 1, '2024-04-27 11:08:07', '2024-04-27 11:08:07'),
(516, 49, 9, 0.03333333333333333, 1, '2024-04-28 01:10:52', '2024-04-28 01:10:52'),
(517, 50, 9, 0.025, 1, '2024-04-28 01:11:01', '2024-04-28 01:11:01'),
(518, 51, 9, 0.03333333333333333, 1, '2024-04-28 01:11:07', '2024-04-28 01:11:07'),
(519, 52, 9, 0.025, 1, '2024-04-28 01:11:14', '2024-04-28 01:11:14'),
(520, 53, 9, 0.025, 1, '2024-04-28 01:11:21', '2024-04-28 01:11:21'),
(521, 56, 8, 0.01, 1, '2024-04-28 01:12:03', '2024-04-28 01:12:03'),
(522, 55, 8, 0.01, 1, '2024-04-28 01:12:08', '2024-04-28 01:12:08'),
(523, 54, 8, 0.01, 1, '2024-04-28 01:12:13', '2024-04-28 01:12:13'),
(524, 70, 10, 0.03333333333333333, 1, '2024-04-28 01:12:41', '2024-04-28 01:12:41'),
(525, 69, 10, 0.03333333333333333, 1, '2024-04-28 01:12:46', '2024-04-28 01:12:46'),
(526, 68, 10, 0.025, 1, '2024-04-28 01:12:51', '2024-04-28 01:12:51'),
(527, 71, 10, 0.02, 1, '2024-04-28 01:13:15', '2024-04-28 01:13:15'),
(528, 72, 10, 0.02, 1, '2024-04-28 01:13:20', '2024-04-28 01:13:20'),
(529, 73, 10, 0.02, 1, '2024-04-28 01:13:26', '2024-04-28 01:13:26'),
(530, 74, 10, 0.02, 1, '2024-04-28 01:13:32', '2024-04-28 01:13:32'),
(531, 78, 10, 0.03333333333333333, 1, '2024-04-28 01:13:57', '2024-04-28 01:13:57'),
(532, 77, 10, 0.025, 1, '2024-04-28 01:14:01', '2024-04-28 01:14:01'),
(533, 76, 10, 0.025, 1, '2024-04-28 01:14:05', '2024-04-28 01:14:05'),
(534, 75, 10, 0.025, 1, '2024-04-28 01:14:12', '2024-04-28 01:14:12'),
(535, 91, 9, 0.03333333333333333, 1, '2024-04-28 01:14:31', '2024-04-28 01:14:31'),
(536, 90, 9, 0.03333333333333333, 1, '2024-04-28 01:14:35', '2024-04-28 01:14:35'),
(537, 89, 9, 0.03333333333333333, 1, '2024-04-28 01:14:39', '2024-04-28 01:14:39'),
(538, 100, 11, 2, 1, '2024-05-10 13:05:03', '2024-05-10 13:05:03'),
(539, 100, 12, 2, 1, '2024-05-10 13:05:03', '2024-05-10 13:05:03'),
(541, 100, 14, 2, 1, '2024-05-10 13:05:03', '2024-05-10 13:05:03'),
(542, 100, 10, 0.1, 1, '2024-05-10 13:05:03', '2024-05-10 13:05:03'),
(543, 97, 11, 1, 1, '2024-08-08 22:50:20', '2024-08-08 22:50:20'),
(544, 97, 12, 1, 1, '2024-08-08 22:50:20', '2024-08-08 22:50:20'),
(545, 97, 13, 1, 1, '2024-08-08 22:50:20', '2024-08-08 22:50:20'),
(546, 97, 14, 0, 1, '2024-08-08 22:50:20', '2024-08-08 22:50:46'),
(547, 97, 15, 0, 1, '2024-08-08 22:50:20', '2024-08-08 22:50:20'),
(548, 97, 10, 0.008130081300813009, 1, '2024-08-08 22:50:20', '2024-08-08 22:50:20'),
(549, 103, 29, 200, 1, '2025-01-16 03:31:03', '2025-01-16 03:31:03'),
(550, 103, 30, 100, 1, '2025-01-16 03:31:03', '2025-01-16 03:31:03'),
(551, 103, 31, 50, 1, '2025-01-16 03:31:03', '2025-01-16 03:31:03'),
(552, 103, 8, 0.03333333333333333, 1, '2025-01-16 03:31:03', '2025-01-16 03:31:03'),
(553, 106, 31, 2, 1, '2025-04-27 05:15:37', '2025-04-27 05:15:37'),
(554, 106, 10, 0.02, 1, '2025-04-27 05:15:37', '2025-04-27 05:15:37'),
(555, 105, 31, 2, 1, '2025-04-27 05:15:45', '2025-04-27 05:15:45'),
(556, 105, 10, 0.02, 1, '2025-04-27 05:15:45', '2025-04-27 05:15:45'),
(557, 104, 31, 2, 1, '2025-04-27 05:15:54', '2025-04-27 05:15:54'),
(558, 104, 10, 0.02, 1, '2025-04-27 05:15:54', '2025-04-27 05:15:54');

-- --------------------------------------------------------

--
-- Table structure for table `product_types`
--

CREATE TABLE `product_types` (
  `product_type_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `size_id` bigint(20) UNSIGNED NOT NULL COMMENT 'HeadID',
  `color_id` bigint(20) UNSIGNED DEFAULT NULL COMMENT 'HeadID',
  `product_type_status` bigint(20) UNSIGNED NOT NULL DEFAULT 1,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_types`
--

INSERT INTO `product_types` (`product_type_id`, `product_id`, `size_id`, `color_id`, `product_type_status`, `created_by`, `created_at`, `updated_at`) VALUES
(49, 24, 1, NULL, 1, 1, '2024-04-26 12:53:20', '2024-05-04 12:54:21'),
(50, 24, 2, NULL, 1, 1, '2024-04-26 12:53:20', '2024-05-04 12:54:21'),
(51, 24, 3, NULL, 1, 1, '2024-04-26 12:53:20', '2024-05-04 12:54:21'),
(52, 24, 4, NULL, 1, 1, '2024-04-26 12:53:20', '2024-05-04 12:54:21'),
(53, 24, 5, NULL, 1, 1, '2024-04-26 12:53:20', '2024-05-04 12:54:21'),
(54, 23, 1, NULL, 1, 1, '2024-04-26 12:53:36', '2024-05-04 12:54:32'),
(55, 23, 2, NULL, 1, 1, '2024-04-26 12:53:36', '2024-05-04 12:54:32'),
(56, 23, 3, NULL, 1, 1, '2024-04-26 12:53:36', '2024-05-04 12:54:32'),
(57, 22, 1, NULL, 1, 1, '2024-04-26 12:54:36', '2024-04-26 12:54:36'),
(58, 22, 2, NULL, 1, 1, '2024-04-26 12:54:36', '2024-04-26 12:54:36'),
(59, 22, 3, NULL, 1, 1, '2024-04-26 12:54:36', '2024-04-26 12:54:36'),
(60, 21, 1, NULL, 1, 1, '2024-04-26 12:58:13', '2024-04-26 12:58:13'),
(61, 21, 2, NULL, 1, 1, '2024-04-26 12:58:13', '2024-04-26 12:58:13'),
(62, 21, 3, NULL, 1, 1, '2024-04-26 12:58:13', '2024-04-26 12:58:13'),
(63, 21, 4, NULL, 1, 1, '2024-04-26 12:58:13', '2024-04-26 12:58:13'),
(64, 21, 5, NULL, 1, 1, '2024-04-26 12:58:13', '2024-04-26 12:58:13'),
(65, 13, 1, NULL, 1, 1, '2024-04-26 12:58:46', '2024-04-26 12:58:46'),
(66, 13, 2, NULL, 1, 1, '2024-04-26 12:58:46', '2024-04-26 12:58:46'),
(67, 13, 3, NULL, 1, 1, '2024-04-26 12:58:46', '2024-04-26 12:58:46'),
(68, 12, 1, NULL, 1, 1, '2024-04-26 13:00:05', '2024-05-04 12:54:44'),
(69, 12, 2, NULL, 1, 1, '2024-04-26 13:00:05', '2024-05-04 12:54:44'),
(70, 12, 3, NULL, 1, 1, '2024-04-26 13:00:05', '2024-05-04 12:54:44'),
(71, 11, 1, NULL, 1, 1, '2024-04-26 13:01:44', '2024-05-04 12:54:54'),
(72, 11, 2, NULL, 1, 1, '2024-04-26 13:01:44', '2024-05-04 12:54:54'),
(73, 11, 3, NULL, 1, 1, '2024-04-26 13:01:44', '2024-05-04 12:54:54'),
(74, 11, 4, NULL, 1, 1, '2024-04-26 13:01:44', '2024-05-04 12:54:54'),
(75, 10, 1, NULL, 1, 1, '2024-04-26 13:02:13', '2024-05-04 12:55:07'),
(76, 10, 2, NULL, 1, 1, '2024-04-26 13:02:13', '2024-05-04 12:55:07'),
(77, 10, 3, NULL, 1, 1, '2024-04-26 13:02:13', '2024-05-04 12:55:07'),
(78, 10, 4, NULL, 1, 1, '2024-04-26 13:02:13', '2024-05-04 12:55:07'),
(79, 9, 1, NULL, 1, 1, '2024-04-26 13:02:47', '2024-04-26 13:02:47'),
(80, 9, 2, NULL, 1, 1, '2024-04-26 13:02:47', '2024-04-26 13:02:47'),
(81, 9, 3, NULL, 1, 1, '2024-04-26 13:02:47', '2024-04-26 13:02:47'),
(82, 9, 4, NULL, 1, 1, '2024-04-26 13:02:47', '2024-04-26 13:02:47'),
(83, 8, 1, NULL, 1, 1, '2024-04-26 13:03:08', '2024-04-26 13:03:08'),
(84, 8, 2, NULL, 1, 1, '2024-04-26 13:03:08', '2024-04-26 13:03:08'),
(85, 8, 3, NULL, 1, 1, '2024-04-26 13:03:08', '2024-04-26 13:03:08'),
(86, 7, 1, NULL, 1, 1, '2024-04-26 13:13:40', '2024-04-26 13:13:40'),
(87, 7, 2, NULL, 1, 1, '2024-04-26 13:13:40', '2024-04-26 13:13:40'),
(88, 7, 3, NULL, 1, 1, '2024-04-26 13:13:40', '2024-04-26 13:13:40'),
(89, 6, 1, NULL, 1, 1, '2024-04-26 13:14:07', '2024-08-22 13:42:07'),
(90, 6, 2, NULL, 1, 1, '2024-04-26 13:14:07', '2024-08-22 13:42:07'),
(91, 6, 3, NULL, 1, 1, '2024-04-26 13:14:07', '2024-08-22 13:42:07'),
(92, 25, 1, NULL, 1, 1, '2024-04-27 07:16:05', '2024-05-28 09:50:44'),
(93, 25, 2, NULL, 1, 1, '2024-04-27 07:16:05', '2024-05-28 09:50:44'),
(94, 25, 3, NULL, 1, 1, '2024-04-27 07:16:05', '2024-05-28 09:50:44'),
(95, 26, 3, NULL, 1, 1, '2024-05-02 05:42:53', '2024-05-04 12:53:58'),
(96, 26, 4, NULL, 1, 1, '2024-05-02 05:42:53', '2024-05-04 12:53:58'),
(97, 27, 1, NULL, 1, 1, '2024-05-04 06:10:49', '2024-05-04 06:13:51'),
(98, 27, 2, NULL, 1, 1, '2024-05-04 06:10:49', '2024-05-04 06:13:51'),
(99, 27, 3, NULL, 1, 1, '2024-05-04 06:10:49', '2024-05-04 06:13:51'),
(100, 28, 1, NULL, 1, 1, '2024-05-10 07:33:11', '2024-09-02 00:30:28'),
(101, 28, 2, NULL, 1, 1, '2024-05-10 07:33:11', '2024-09-02 00:30:28'),
(102, 28, 3, NULL, 1, 1, '2024-05-10 07:33:11', '2024-09-02 00:30:28'),
(103, 29, 2, NULL, 1, 1, '2025-01-16 03:29:25', '2025-01-16 03:29:25'),
(104, 30, 3, NULL, 1, 1, '2025-04-27 04:25:19', '2025-04-27 04:26:45'),
(105, 30, 2, NULL, 1, 1, '2025-04-27 04:26:45', '2025-04-27 04:26:45'),
(106, 30, 1, NULL, 1, 1, '2025-04-27 04:26:45', '2025-04-27 04:26:45');

-- --------------------------------------------------------

--
-- Table structure for table `purchases`
--

CREATE TABLE `purchases` (
  `purchase_id` bigint(20) UNSIGNED NOT NULL,
  `purchase_no` varchar(255) NOT NULL,
  `purchase_type` varchar(255) NOT NULL,
  `order_id` bigint(20) UNSIGNED DEFAULT NULL,
  `vendor_id` bigint(20) UNSIGNED NOT NULL,
  `description` longtext DEFAULT NULL,
  `purchase_date` date NOT NULL,
  `require_date` date NOT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `purchases`
--

INSERT INTO `purchases` (`purchase_id`, `purchase_no`, `purchase_type`, `order_id`, `vendor_id`, `description`, `purchase_date`, `require_date`, `created_by`, `created_at`, `updated_at`) VALUES
(24, 'P2404001', 'material', 16, 7, '<p>Detail of New Max Purchase From Abdulrehman</p>', '2024-04-26', '2024-04-26', 1, '2024-04-26 11:30:53', '2024-04-26 11:30:53'),
(25, 'P2404002', 'material', NULL, 7, NULL, '2024-04-26', '2024-04-26', 1, '2024-04-26 11:43:36', '2024-04-26 11:43:36'),
(26, 'P2404003', 'material', NULL, 5, NULL, '2024-04-27', '2024-04-27', 1, '2024-04-27 10:37:07', '2024-04-27 10:37:07'),
(27, 'P2404004', 'material', 17, 11, NULL, '2024-04-29', '2024-04-29', 1, '2024-04-29 11:57:42', '2024-04-29 11:57:42'),
(28, 'P2404005', 'material', 17, 7, NULL, '2024-04-29', '2024-04-29', 1, '2024-04-29 12:52:43', '2024-04-29 12:52:43'),
(29, 'P2404006', 'material', 16, 1, '<p>Container for Delivery Job 101</p>', '2024-04-30', '2024-04-30', 1, '2024-04-30 10:01:44', '2024-04-30 10:01:44'),
(30, 'P2405001', 'material', 16, 11, NULL, '2024-05-05', '2024-05-05', 1, '2024-05-05 12:42:25', '2024-05-05 12:42:25'),
(31, 'P2405002', 'material', 16, 11, NULL, '2024-05-10', '2024-05-10', 1, '2024-05-10 08:04:38', '2024-05-10 08:04:38'),
(32, 'P2405003', 'material', 0, 7, NULL, '2024-05-10', '2024-05-10', 1, '2024-05-10 13:32:47', '2024-05-10 13:32:47'),
(33, 'P2405004', 'material', 0, 11, NULL, '2024-05-15', '2024-05-15', 1, '2024-05-15 12:28:36', '2024-05-15 12:28:36'),
(34, 'P2405005', 'material', 0, 11, '<p>Desc</p>', '2024-05-16', '2024-05-16', 1, '2024-05-16 10:46:47', '2024-05-16 10:46:47'),
(35, 'P2405006', 'material', 0, 11, '<p>Desc</p>', '2024-05-23', '2024-05-23', 1, '2024-05-22 14:12:41', '2024-05-22 14:12:41'),
(36, 'P2405007', 'material', 0, 12, NULL, '2024-05-24', '2024-05-24', 1, '2024-05-24 09:06:00', '2024-05-24 09:06:00'),
(37, 'P2405008', 'material', 24, 4, NULL, '2024-05-25', '2024-05-25', 1, '2024-05-25 06:30:20', '2024-05-25 06:30:20'),
(38, 'P2405009', 'material', 16, 11, NULL, '2024-05-25', '2024-05-25', 1, '2024-05-25 07:06:48', '2024-05-25 07:06:48'),
(39, 'P2405010', 'material', 0, 11, '<p>Desc</p>', '2024-05-27', '2024-05-27', 1, '2024-05-27 12:58:32', '2024-05-27 12:58:32'),
(40, 'P2405011', 'material', 0, 5, NULL, '2024-05-27', '2024-05-27', 1, '2024-05-27 13:40:57', '2024-05-27 13:40:57'),
(41, 'P2405012', 'material', 24, 12, '<p>Desc</p>', '2024-05-29', '2024-05-29', 1, '2024-05-29 04:57:20', '2024-05-29 04:57:20'),
(42, 'P2405013', 'material', 24, 12, NULL, '2024-05-29', '2024-05-29', 1, '2024-05-29 07:08:39', '2024-05-29 07:08:39'),
(43, 'P2405014', 'material', 0, 7, '<p>Desc&nbsp;</p>', '2024-05-30', '2024-05-30', 1, '2024-05-30 02:17:35', '2024-05-30 02:17:35'),
(44, 'P2405015', 'material', 0, 11, NULL, '2024-05-30', '2024-05-30', 1, '2024-05-30 02:20:48', '2024-05-30 02:20:48'),
(45, 'P2405016', 'material', 0, 5, NULL, '2024-05-30', '2024-05-30', 1, '2024-05-30 02:36:38', '2024-05-30 02:36:38'),
(46, 'P2405017', 'material', 0, 5, NULL, '2024-05-30', '2024-05-30', 1, '2024-05-30 02:40:15', '2024-05-30 02:40:15'),
(47, 'P2405018', 'material', 18, 7, NULL, '2024-05-30', '2024-05-30', 1, '2024-05-30 04:59:07', '2024-05-30 04:59:07'),
(48, 'P2405019', 'material', 0, 4, NULL, '2024-05-30', '2024-05-30', 1, '2024-05-30 05:23:29', '2024-05-30 05:23:29'),
(49, 'P2405020', 'material', 0, 11, NULL, '2024-05-30', '2024-05-30', 1, '2024-05-30 05:28:10', '2024-05-30 05:28:10'),
(50, 'P2407001', 'material', 28, 7, NULL, '2024-07-01', '2024-07-01', 1, '2024-07-01 08:01:35', '2024-07-01 08:01:35'),
(51, 'P2408001', 'material', 30, 12, NULL, '2024-08-17', '2024-08-17', 1, '2024-08-17 05:59:23', '2024-08-17 05:59:23'),
(52, 'P2408002', 'material', 0, 12, NULL, '2024-08-17', '2024-08-17', 1, '2024-08-17 06:07:18', '2024-08-17 06:07:18'),
(53, 'P2409001', 'material', 29, 12, NULL, '2024-09-02', '2024-09-02', 1, '2024-09-02 01:57:20', '2024-09-02 01:57:20'),
(66, 'P2503002', 'product', 0, 12, NULL, '2025-03-23', '2025-03-23', 1, '2025-03-23 06:11:21', '2025-03-23 06:11:21'),
(67, 'P2504001', 'product', 0, 4, NULL, '2025-04-07', '2025-04-07', 1, '2025-04-06 14:47:58', '2025-04-06 14:47:58'),
(68, 'P2504002', 'material', 31, 11, NULL, '2025-04-07', '2025-04-07', 1, '2025-04-06 15:13:26', '2025-04-06 15:13:26'),
(69, 'P2504003', 'product', 0, 11, NULL, '2025-04-07', '2025-04-07', 1, '2025-04-06 15:13:59', '2025-04-06 15:13:59'),
(70, 'P2504004', 'product', 29, 7, '<p>Desc</p>', '2025-04-19', '2025-04-19', 1, '2025-04-19 03:21:05', '2025-04-19 03:21:05'),
(71, 'P2504005', 'product', 0, 7, NULL, '2025-04-19', '2025-04-19', 1, '2025-04-19 03:30:22', '2025-04-19 03:30:22'),
(72, 'P2504006', 'product', 0, 11, NULL, '2025-04-19', '2025-04-19', 1, '2025-04-19 03:38:29', '2025-04-19 03:38:29'),
(73, 'P2504007', 'product', 0, 11, NULL, '2025-04-27', '2025-04-27', 1, '2025-04-27 04:10:02', '2025-04-27 04:10:02'),
(74, 'P2504008', 'product', 0, 7, NULL, '2025-04-27', '2025-04-27', 1, '2025-04-27 04:25:55', '2025-04-27 04:25:55'),
(75, 'P2504009', 'product', 0, 7, NULL, '2025-04-27', '2025-04-27', 1, '2025-04-27 04:28:32', '2025-04-27 04:28:32');

-- --------------------------------------------------------

--
-- Table structure for table `purchase_items`
--

CREATE TABLE `purchase_items` (
  `purchase_item_id` bigint(20) UNSIGNED NOT NULL,
  `purchase_id` bigint(20) UNSIGNED NOT NULL,
  `product_type_id` bigint(20) UNSIGNED NOT NULL,
  `product_stage_id` bigint(20) UNSIGNED NOT NULL,
  `material_id` bigint(20) UNSIGNED NOT NULL,
  `quantity` double UNSIGNED NOT NULL,
  `price` double UNSIGNED NOT NULL,
  `total` double UNSIGNED NOT NULL COMMENT 'Quantity * Price',
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `purchase_items`
--

INSERT INTO `purchase_items` (`purchase_item_id`, `purchase_id`, `product_type_id`, `product_stage_id`, `material_id`, `quantity`, `price`, `total`, `created_by`, `created_at`, `updated_at`) VALUES
(373, 24, 0, 0, 14, 1000, 100, 100000, 1, '2024-04-26 11:30:53', '2024-04-26 11:30:53'),
(374, 24, 0, 0, 13, 1000, 100, 100000, 1, '2024-04-26 11:30:53', '2024-04-26 11:30:53'),
(375, 24, 0, 0, 12, 1000, 100, 100000, 1, '2024-04-26 11:30:53', '2024-04-26 11:30:53'),
(376, 24, 0, 0, 11, 1000, 100, 100000, 1, '2024-04-26 11:30:53', '2024-04-26 11:30:53'),
(377, 24, 0, 0, 7, 1000, 100, 100000, 1, '2024-04-26 11:30:53', '2024-04-26 11:30:53'),
(378, 24, 0, 0, 6, 1000, 100, 100000, 1, '2024-04-26 11:30:53', '2024-04-26 11:30:53'),
(379, 24, 0, 0, 5, 1000, 100, 100000, 1, '2024-04-26 11:30:53', '2024-04-26 11:30:53'),
(380, 24, 0, 0, 4, 1000, 100, 100000, 1, '2024-04-26 11:30:53', '2024-04-26 11:30:53'),
(381, 24, 0, 0, 3, 1000, 100, 100000, 1, '2024-04-26 11:30:53', '2024-04-26 11:30:53'),
(382, 24, 0, 0, 2, 1000, 100, 100000, 1, '2024-04-26 11:30:53', '2024-04-26 11:30:53'),
(383, 25, 0, 0, 14, 12, 12, 144, 1, '2024-04-26 11:43:36', '2024-04-26 11:43:36'),
(384, 26, 0, 0, 14, 1000, 100, 100000, 1, '2024-04-27 10:37:07', '2024-04-27 10:37:07'),
(385, 26, 0, 0, 13, 1000, 100, 100000, 1, '2024-04-27 10:37:07', '2024-04-27 10:37:07'),
(386, 26, 0, 0, 12, 1000, 100, 100000, 1, '2024-04-27 10:37:07', '2024-04-27 10:37:07'),
(387, 26, 0, 0, 11, 1000, 100, 100000, 1, '2024-04-27 10:37:07', '2024-04-27 10:37:07'),
(388, 26, 0, 0, 10, 1000, 100, 100000, 1, '2024-04-27 10:37:07', '2024-04-27 10:37:07'),
(389, 26, 0, 0, 9, 1000, 100, 100000, 1, '2024-04-27 10:37:07', '2024-04-27 10:37:07'),
(390, 26, 0, 0, 8, 1000, 100, 100000, 1, '2024-04-27 10:37:07', '2024-04-27 10:37:07'),
(391, 26, 0, 0, 7, 1000, 100, 100000, 1, '2024-04-27 10:37:07', '2024-04-27 10:37:07'),
(392, 26, 0, 0, 6, 1000, 100, 100000, 1, '2024-04-27 10:37:07', '2024-04-27 10:37:07'),
(393, 26, 0, 0, 5, 1000, 100, 100000, 1, '2024-04-27 10:37:07', '2024-04-27 10:37:07'),
(394, 26, 0, 0, 4, 1000, 100, 100000, 1, '2024-04-27 10:37:07', '2024-04-27 10:37:07'),
(395, 26, 0, 0, 3, 1000, 100, 100000, 1, '2024-04-27 10:37:07', '2024-04-27 10:37:07'),
(396, 26, 0, 0, 2, 1000, 100, 100000, 1, '2024-04-27 10:37:07', '2024-04-27 10:37:07'),
(397, 26, 0, 0, 1, 1000, 100, 100000, 1, '2024-04-27 10:37:07', '2024-04-27 10:37:07'),
(398, 27, 0, 0, 1, 100, 200, 20000, 1, '2024-04-29 11:57:42', '2024-04-29 11:57:42'),
(399, 28, 0, 0, 1, 50, 100, 5000, 1, '2024-04-29 12:52:43', '2024-04-29 12:52:43'),
(400, 28, 0, 0, 14, 400, 50, 20000, 1, '2024-04-29 12:52:43', '2024-04-29 12:52:43'),
(401, 29, 0, 0, 15, 1, 25000, 25000, 1, '2024-04-30 10:01:44', '2024-04-30 10:01:44'),
(402, 30, 0, 0, 15, 2, 600000, 1200000, 1, '2024-05-05 12:42:25', '2024-05-05 12:42:25'),
(403, 31, 0, 0, 13, 100, 100, 10000, 1, '2024-05-10 08:04:38', '2024-05-10 08:04:38'),
(404, 31, 0, 0, 14, 100, 100, 10000, 1, '2024-05-10 08:04:38', '2024-05-10 08:04:38'),
(405, 31, 0, 0, 12, 100, 100, 10000, 1, '2024-05-10 08:04:38', '2024-05-10 08:04:38'),
(406, 32, 0, 0, 3, 500, 100, 50000, 1, '2024-05-10 13:32:47', '2024-05-10 13:32:47'),
(407, 32, 0, 0, 13, 200, 100, 20000, 1, '2024-05-10 13:32:47', '2024-05-10 13:32:47'),
(408, 32, 0, 0, 11, 500, 100, 50000, 1, '2024-05-10 13:32:47', '2024-05-10 13:32:47'),
(409, 33, 0, 0, 16, 1000, 100, 100000, 1, '2024-05-15 12:28:36', '2024-05-15 12:28:36'),
(410, 33, 0, 0, 17, 1000, 100, 100000, 1, '2024-05-15 12:28:36', '2024-05-15 12:28:36'),
(411, 33, 0, 0, 18, 1000, 100, 100000, 1, '2024-05-15 12:28:36', '2024-05-15 12:28:36'),
(412, 34, 0, 0, 4, 1000, 100, 100000, 1, '2024-05-16 10:46:47', '2024-05-16 10:46:47'),
(413, 35, 0, 0, 12, 200, 150, 30000, 1, '2024-05-22 14:12:41', '2024-05-22 14:17:41'),
(414, 36, 0, 0, 24, 20, 150, 3000, 1, '2024-05-24 09:06:00', '2024-05-24 09:06:00'),
(415, 36, 0, 0, 22, 20, 10, 200, 1, '2024-05-24 09:06:00', '2024-05-24 09:06:00'),
(416, 36, 0, 0, 23, 100, 10, 1000, 1, '2024-05-24 09:06:00', '2024-05-24 09:06:00'),
(417, 37, 0, 0, 19, 100, 100, 10000, 1, '2024-05-25 06:30:20', '2024-05-25 06:30:20'),
(418, 38, 0, 0, 11, 1000, 10, 10000, 1, '2024-05-25 07:06:48', '2024-05-25 07:06:48'),
(419, 38, 0, 0, 12, 1000, 100, 100000, 1, '2024-05-25 07:06:48', '2024-05-25 07:06:48'),
(420, 38, 0, 0, 1, 1000, 100, 100000, 1, '2024-05-25 07:06:48', '2024-05-25 07:06:48'),
(421, 38, 0, 0, 7, 1000, 100, 100000, 1, '2024-05-25 07:06:48', '2024-05-25 07:06:48'),
(422, 38, 0, 0, 3, 1000, 1100, 1100000, 1, '2024-05-25 07:06:48', '2024-05-25 07:06:48'),
(423, 38, 0, 0, 2, 100, 100, 10000, 1, '2024-05-25 07:06:48', '2024-05-25 07:06:48'),
(424, 38, 0, 0, 5, 1000, 100, 100000, 1, '2024-05-25 07:06:48', '2024-05-25 07:06:48'),
(425, 38, 0, 0, 6, 1000, 100, 100000, 1, '2024-05-25 07:06:48', '2024-05-25 07:06:48'),
(426, 38, 0, 0, 13, 1000, 100, 100000, 1, '2024-05-25 07:06:48', '2024-05-25 07:06:48'),
(427, 38, 0, 0, 14, 1000, 100, 100000, 1, '2024-05-25 07:06:48', '2024-05-25 07:06:48'),
(428, 38, 0, 0, 19, 1000, 100, 100000, 1, '2024-05-25 07:06:48', '2024-05-25 07:06:48'),
(429, 38, 0, 0, 18, 1000, 10, 10000, 1, '2024-05-25 07:06:48', '2024-05-25 07:06:48'),
(430, 38, 0, 0, 17, 1000, 100, 100000, 1, '2024-05-25 07:06:48', '2024-05-25 07:06:48'),
(431, 38, 0, 0, 16, 1000, 100, 100000, 1, '2024-05-25 07:06:48', '2024-05-25 07:06:48'),
(432, 38, 0, 0, 21, 1000, 100, 100000, 1, '2024-05-25 07:06:48', '2024-05-25 07:06:48'),
(433, 38, 0, 0, 20, 1000, 100, 100000, 1, '2024-05-25 07:06:48', '2024-05-25 07:06:48'),
(437, 39, 0, 0, 20, 50, 100, 5000, 1, '2024-05-27 12:58:32', '2024-05-27 12:58:32'),
(440, 40, 0, 0, 14, 6, 100, 600, 1, '2024-05-27 14:24:37', '2024-05-27 14:24:37'),
(441, 40, 0, 0, 6, 100, 20, 2000, 1, '2024-05-27 14:24:37', '2024-05-27 14:24:37'),
(442, 41, 0, 0, 4, 200, 100, 20000, 1, '2024-05-29 04:57:20', '2024-05-29 04:57:20'),
(443, 42, 0, 0, 24, 12, 12, 144, 1, '2024-05-29 07:08:39', '2024-05-29 07:08:39'),
(444, 43, 0, 0, 25, 1000, 100, 100000, 1, '2024-05-30 02:17:35', '2024-05-30 02:17:35'),
(445, 43, 0, 0, 26, 500, 100, 50000, 1, '2024-05-30 02:17:35', '2024-05-30 02:17:35'),
(446, 44, 0, 0, 26, 1125, 40, 45000, 1, '2024-05-30 02:20:48', '2024-05-30 02:20:48'),
(448, 45, 0, 0, 27, 800, 500, 400000, 1, '2024-05-30 02:38:21', '2024-05-30 02:38:21'),
(450, 47, 0, 0, 15, 5, 3000, 15000, 1, '2024-05-30 04:59:07', '2024-05-30 04:59:07'),
(451, 48, 0, 0, 28, 23, 12, 276, 1, '2024-05-30 05:23:29', '2024-05-30 05:23:29'),
(452, 48, 0, 0, 25, 12, 12, 144, 1, '2024-05-30 05:23:29', '2024-05-30 05:23:29'),
(453, 46, 0, 0, 24, 12, 1, 12, 1, '2024-05-30 05:26:39', '2024-05-30 05:26:39'),
(454, 49, 0, 0, 2, 215, 100, 21500, 1, '2024-05-30 05:28:10', '2024-05-30 05:28:10'),
(455, 50, 0, 0, 11, 600, 20, 12000, 1, '2024-07-01 08:01:35', '2024-07-01 08:01:35'),
(456, 50, 0, 0, 14, 900, 120, 108000, 1, '2024-07-01 08:01:35', '2024-07-01 08:01:35'),
(457, 51, 0, 0, 28, 100, 120, 12000, 1, '2024-08-17 05:59:23', '2024-08-17 05:59:23'),
(458, 52, 0, 0, 28, 1212.12121212, 12, 14545.45454544, 1, '2024-08-17 06:07:18', '2024-08-17 06:07:18'),
(459, 53, 0, 0, 31, 2, 20, 40, 1, '2024-09-02 01:57:20', '2024-09-02 01:57:20'),
(471, 66, 49, 72, 0, 12, 12, 144, 1, '2025-03-23 06:11:21', '2025-03-23 06:11:21'),
(474, 67, 51, 72, 0, 100, 200, 20000, 1, '2025-04-06 14:47:58', '2025-04-06 14:47:58'),
(475, 67, 53, 72, 0, 78, 78, 6084, 1, '2025-04-06 14:51:40', '2025-04-06 14:51:40'),
(476, 68, 0, 0, 14, 12, 12, 144, 1, '2025-04-06 15:13:26', '2025-04-06 15:13:26'),
(477, 69, 49, 74, 0, 100, 10, 1000, 1, '2025-04-06 15:13:59', '2025-04-06 15:13:59'),
(478, 70, 69, 74, 0, 120, 120, 14400, 1, '2025-04-19 03:21:05', '2025-04-19 03:21:05'),
(479, 71, 49, 73, 0, 9, 900, 8100, 1, '2025-04-19 03:30:22', '2025-04-19 03:30:22'),
(481, 72, 103, 74, 0, 200, 200, 40000, 1, '2025-04-19 03:38:51', '2025-04-19 03:38:51'),
(482, 73, 103, 72, 0, 12, 12, 144, 1, '2025-04-27 04:10:02', '2025-04-27 04:10:02'),
(483, 74, 104, 72, 0, 12, 12, 144, 1, '2025-04-27 04:25:55', '2025-04-27 04:25:55'),
(484, 75, 105, 72, 0, 10, 10, 100, 1, '2025-04-27 04:28:32', '2025-04-27 04:28:32'),
(485, 75, 106, 72, 0, 20, 20, 400, 1, '2025-04-27 04:28:32', '2025-04-27 04:28:32'),
(486, 75, 104, 72, 0, 18, 12, 216, 1, '2025-04-27 04:29:50', '2025-04-27 04:29:50');

-- --------------------------------------------------------

--
-- Table structure for table `receives`
--

CREATE TABLE `receives` (
  `receive_id` bigint(20) UNSIGNED NOT NULL,
  `receive_no` varchar(255) NOT NULL,
  `purchase_id` bigint(20) UNSIGNED DEFAULT NULL,
  `receive_date` date NOT NULL,
  `receive_status` bigint(20) UNSIGNED NOT NULL DEFAULT 0 COMMENT '0 Pending, 1 Checked',
  `description` longtext DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `receives`
--

INSERT INTO `receives` (`receive_id`, `receive_no`, `purchase_id`, `receive_date`, `receive_status`, `description`, `created_by`, `created_at`, `updated_at`) VALUES
(38, 'R1-P2404001', 24, '2024-04-26', 0, NULL, 1, '2024-04-26 11:33:16', '2024-04-26 11:33:16'),
(39, 'R1-P2404002', 25, '2024-04-26', 1, NULL, 1, '2024-04-26 11:46:50', '2024-04-27 06:20:31'),
(40, 'R1-P2404003', 26, '2024-04-27', 0, NULL, 1, '2024-04-27 10:38:16', '2024-04-27 10:38:16'),
(41, 'R2-P2404001', 24, '2024-04-27', 0, NULL, 1, '2024-04-27 13:14:21', '2024-04-27 13:14:21'),
(42, 'R3-P2404001', 24, '2024-04-27', 0, NULL, 1, '2024-04-27 13:15:00', '2024-04-27 13:15:00'),
(43, 'R1-P2404006', 29, '2024-04-30', 0, 'Container Received', 1, '2024-04-30 10:04:12', '2024-04-30 10:04:12'),
(44, 'R1-P2405001', 30, '2024-05-06', 0, NULL, 1, '2024-05-06 03:51:13', '2024-05-06 03:51:13'),
(45, 'R1-P2405002', 31, '2024-05-10', 0, NULL, 1, '2024-05-10 08:05:19', '2024-05-10 08:05:19'),
(46, 'R1-P2405003', 32, '2024-05-10', 0, NULL, 1, '2024-05-10 13:40:26', '2024-05-10 13:40:26'),
(47, 'R1-P2405004', 33, '2024-05-15', 0, NULL, 1, '2024-05-15 12:29:24', '2024-05-15 12:29:24'),
(48, 'R1-P2405005', 34, '2024-05-16', 0, NULL, 1, '2024-05-16 10:47:26', '2024-05-16 10:47:26'),
(49, 'R2-P2405005', 34, '2024-05-18', 0, NULL, 1, '2024-05-18 13:21:27', '2024-05-18 13:21:27'),
(50, 'R3-P2405005', 34, '2024-05-18', 0, NULL, 1, '2024-05-18 13:21:57', '2024-05-18 13:21:57'),
(51, 'R2-P2405003', 32, '2024-05-18', 0, NULL, 1, '2024-05-18 13:24:10', '2024-05-18 13:24:10'),
(53, 'R2-P2405004', 33, '2024-05-18', 0, NULL, 1, '2024-05-18 13:25:22', '2024-05-18 13:25:22'),
(54, 'R3-P2405004', 33, '2024-05-18', 0, NULL, 1, '2024-05-18 13:26:06', '2024-05-18 13:26:06'),
(55, 'R4-P2405004', 33, '2024-05-18', 0, NULL, 1, '2024-05-18 13:26:50', '2024-05-18 13:26:50'),
(57, 'R4-P2405005', 34, '2024-05-18', 0, NULL, 1, '2024-05-18 13:58:12', '2024-05-18 13:58:12'),
(60, 'R5-P2405005', 34, '2024-05-18', 0, NULL, 1, '2024-05-18 13:59:52', '2024-05-18 13:59:52'),
(62, 'R1-P2404004', 27, '2024-05-19', 0, NULL, 1, '2024-05-18 14:46:01', '2024-05-18 14:46:01'),
(63, 'R6-P2405005', 34, '2024-05-20', 0, NULL, 1, '2024-05-20 12:04:12', '2024-05-20 12:04:12'),
(64, 'R7-P2405005', 34, '2024-05-20', 0, NULL, 1, '2024-05-20 12:05:36', '2024-05-20 12:05:36'),
(65, 'R1-P2405007', 36, '2024-05-24', 0, NULL, 1, '2024-05-24 09:06:53', '2024-05-24 09:06:53'),
(66, 'R1-P2405008', 37, '2024-05-25', 0, NULL, 1, '2024-05-25 06:32:04', '2024-05-25 06:32:04'),
(67, 'R2-P2405008', 37, '2024-05-25', 0, NULL, 1, '2024-05-25 06:32:43', '2024-05-25 06:32:43'),
(68, 'R3-P2405008', 37, '2024-05-25', 0, NULL, 1, '2024-05-25 06:35:57', '2024-05-25 06:35:57'),
(69, 'R1-P2405009', 38, '2024-05-25', 0, NULL, 1, '2024-05-25 07:08:24', '2024-05-25 07:08:24'),
(70, 'R1-P2405011', 40, '2024-05-27', 0, NULL, 1, '2024-05-27 14:32:44', '2024-05-27 14:32:44'),
(71, 'R1-P2405012', 41, '2024-05-29', 0, NULL, 1, '2024-05-29 04:57:51', '2024-05-29 04:57:51'),
(72, 'R1-P2405014', 43, '2024-05-30', 0, NULL, 1, '2024-05-30 02:17:55', '2024-05-30 02:17:55'),
(73, 'R1-P2405015', 44, '2024-05-30', 0, NULL, 1, '2024-05-30 02:22:01', '2024-05-30 02:22:01'),
(74, 'R1-P2405016', 45, '2024-05-30', 0, NULL, 1, '2024-05-30 02:38:37', '2024-05-30 02:38:37'),
(75, 'R1-P2405017', 46, '2024-05-30', 0, NULL, 1, '2024-05-30 02:40:44', '2024-05-30 02:40:44'),
(76, 'R1-P2405018', 47, '2024-05-30', 0, NULL, 1, '2024-05-30 04:59:23', '2024-05-30 04:59:23'),
(77, 'R1-P2405019', 48, '2024-05-30', 0, NULL, 1, '2024-05-30 05:24:00', '2024-05-30 05:24:00'),
(78, 'R1-P2405020', 49, '2024-05-30', 0, NULL, 1, '2024-05-30 05:28:33', '2024-05-30 05:28:33'),
(79, 'R1-P2405006', 35, '2024-05-30', 0, NULL, 1, '2024-05-30 07:58:43', '2024-05-30 07:58:43'),
(80, 'R2-P2405006', 35, '2024-05-30', 0, NULL, 1, '2024-05-30 08:00:11', '2024-05-30 08:00:11'),
(81, 'R1-P2407001', 50, '2024-07-01', 0, NULL, 1, '2024-07-01 08:06:29', '2024-07-01 08:06:29'),
(82, 'R1-P2408001', 51, '2024-08-17', 0, NULL, 1, '2024-08-17 06:01:43', '2024-08-17 06:01:43'),
(83, 'R1-P2501001', 54, '2025-01-16', 0, NULL, 1, '2025-01-16 05:49:39', '2025-01-16 05:49:39'),
(84, 'R1-P2409001', 53, '2025-03-23', 0, NULL, 1, '2025-03-23 06:50:55', '2025-03-23 06:50:55'),
(85, 'R1-P2503002', 66, '2025-03-23', 0, '<p>Total: 12, Receive: 10, Pending: 2, Approved: 6, Rejected: 2, Remaining: 2</p><p>Total: 12, Receive: 12, Pending: 2, Approved: 6, Rejected: 4, Remaining: 0</p>', 1, '2025-03-23 13:28:48', '2025-03-23 14:10:29'),
(86, 'R1-P2504001', 67, '2025-04-06', 0, NULL, 1, '2025-04-06 14:55:18', '2025-04-06 14:55:18'),
(87, 'R1-P2504004', 70, '2025-04-19', 0, NULL, 1, '2025-04-19 03:21:31', '2025-04-19 03:21:31'),
(88, 'R1-P2504005', 71, '2025-04-19', 0, NULL, 1, '2025-04-19 03:30:37', '2025-04-19 03:30:37'),
(89, 'R1-P2504006', 72, '2025-04-19', 0, NULL, 1, '2025-04-19 03:39:09', '2025-04-19 03:39:09'),
(90, 'R1-P2504007', 73, '2025-04-27', 0, NULL, 1, '2025-04-27 04:10:19', '2025-04-27 04:10:19'),
(91, 'R1-P2504008', 74, '2025-04-27', 0, NULL, 1, '2025-04-27 04:26:15', '2025-04-27 04:26:15'),
(92, 'R1-P2504009', 75, '2025-04-27', 0, NULL, 1, '2025-04-27 04:30:14', '2025-04-27 04:30:14');

-- --------------------------------------------------------

--
-- Table structure for table `receive_materials`
--

CREATE TABLE `receive_materials` (
  `receive_material_id` bigint(20) UNSIGNED NOT NULL,
  `receive_id` bigint(20) UNSIGNED NOT NULL,
  `purchase_item_id` bigint(20) UNSIGNED NOT NULL,
  `quantity` double UNSIGNED NOT NULL,
  `pending_qty` double UNSIGNED NOT NULL,
  `approved_qty` double UNSIGNED NOT NULL,
  `rejected_qty` double UNSIGNED NOT NULL,
  `inspection_date` date NOT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `receive_materials`
--

INSERT INTO `receive_materials` (`receive_material_id`, `receive_id`, `purchase_item_id`, `quantity`, `pending_qty`, `approved_qty`, `rejected_qty`, `inspection_date`, `created_by`, `created_at`, `updated_at`) VALUES
(188, 38, 373, 900, 0, 0, 0, '2024-05-18', 1, '2024-04-26 11:33:16', '2024-04-26 11:33:16'),
(189, 38, 374, 900, 0, 0, 0, '2024-05-18', 1, '2024-04-26 11:33:16', '2024-04-26 11:33:16'),
(190, 38, 375, 900, 0, 0, 0, '2024-05-18', 1, '2024-04-26 11:33:16', '2024-04-26 11:33:16'),
(191, 38, 376, 900, 0, 0, 0, '2024-05-18', 1, '2024-04-26 11:33:16', '2024-04-26 11:33:16'),
(192, 38, 377, 900, 0, 0, 0, '2024-05-18', 1, '2024-04-26 11:33:16', '2024-04-26 11:33:16'),
(193, 38, 378, 900, 0, 0, 0, '2024-05-18', 1, '2024-04-26 11:33:16', '2024-04-26 11:33:16'),
(194, 38, 379, 900, 0, 0, 0, '2024-05-18', 1, '2024-04-26 11:33:16', '2024-04-26 11:33:16'),
(195, 38, 380, 900, 0, 0, 0, '2024-05-18', 1, '2024-04-26 11:33:16', '2024-04-26 11:33:16'),
(196, 38, 381, 900, 0, 0, 0, '2024-05-18', 1, '2024-04-26 11:33:16', '2024-04-26 11:33:16'),
(197, 38, 382, 900, 0, 0, 0, '2024-05-18', 1, '2024-04-26 11:33:16', '2024-04-26 11:33:16'),
(198, 39, 383, 12, 0, 0, 0, '2024-05-18', 1, '2024-04-26 11:46:50', '2024-04-26 11:46:50'),
(199, 40, 384, 1000, 0, 0, 0, '2024-05-18', 1, '2024-04-27 10:38:16', '2024-04-27 10:38:16'),
(200, 40, 385, 1000, 0, 0, 0, '2024-05-18', 1, '2024-04-27 10:38:16', '2024-04-27 10:38:16'),
(201, 40, 386, 1000, 0, 0, 0, '2024-05-18', 1, '2024-04-27 10:38:16', '2024-04-27 10:38:16'),
(202, 40, 387, 1000, 0, 0, 0, '2024-05-18', 1, '2024-04-27 10:38:16', '2024-04-27 10:38:16'),
(203, 40, 388, 1000, 0, 0, 0, '2024-05-18', 1, '2024-04-27 10:38:16', '2024-04-27 10:38:16'),
(204, 40, 389, 1000, 0, 0, 0, '2024-05-18', 1, '2024-04-27 10:38:16', '2024-04-27 10:38:16'),
(205, 40, 390, 1000, 0, 0, 0, '2024-05-18', 1, '2024-04-27 10:38:16', '2024-04-27 10:38:16'),
(206, 40, 391, 1000, 0, 0, 0, '2024-05-18', 1, '2024-04-27 10:38:16', '2024-04-27 10:38:16'),
(207, 40, 392, 1000, 0, 0, 0, '2024-05-18', 1, '2024-04-27 10:38:16', '2024-04-27 10:38:16'),
(208, 40, 393, 1000, 0, 0, 0, '2024-05-18', 1, '2024-04-27 10:38:16', '2024-04-27 10:38:16'),
(209, 40, 394, 1000, 0, 0, 0, '2024-05-18', 1, '2024-04-27 10:38:16', '2024-04-27 10:38:16'),
(210, 40, 395, 1000, 0, 0, 0, '2024-05-18', 1, '2024-04-27 10:38:16', '2024-04-27 10:38:16'),
(211, 40, 396, 1000, 0, 0, 0, '2024-05-18', 1, '2024-04-27 10:38:16', '2024-04-27 10:38:16'),
(212, 40, 397, 1000, 0, 0, 0, '2024-05-18', 1, '2024-04-27 10:38:16', '2024-04-27 10:38:16'),
(213, 41, 373, 50, 0, 0, 0, '2024-05-18', 1, '2024-04-27 13:14:21', '2024-04-27 13:14:21'),
(214, 41, 374, 50, 0, 0, 0, '2024-05-18', 1, '2024-04-27 13:14:21', '2024-04-27 13:14:21'),
(215, 41, 375, 50, 0, 0, 0, '2024-05-18', 1, '2024-04-27 13:14:21', '2024-04-27 13:14:21'),
(216, 41, 376, 50, 0, 0, 0, '2024-05-18', 1, '2024-04-27 13:14:21', '2024-04-27 13:14:21'),
(217, 41, 377, 50, 0, 0, 0, '2024-05-18', 1, '2024-04-27 13:14:21', '2024-04-27 13:14:21'),
(218, 41, 378, 50, 0, 0, 0, '2024-05-18', 1, '2024-04-27 13:14:21', '2024-04-27 13:14:21'),
(219, 41, 379, 50, 0, 0, 0, '2024-05-18', 1, '2024-04-27 13:14:21', '2024-04-27 13:14:21'),
(220, 41, 380, 50, 0, 0, 0, '2024-05-18', 1, '2024-04-27 13:14:21', '2024-04-27 13:14:21'),
(221, 41, 381, 50, 0, 0, 0, '2024-05-18', 1, '2024-04-27 13:14:21', '2024-04-27 13:14:21'),
(222, 41, 382, 50, 0, 0, 0, '2024-05-18', 1, '2024-04-27 13:14:21', '2024-04-27 13:14:21'),
(223, 42, 373, 20, 0, 0, 0, '2024-05-18', 1, '2024-04-27 13:15:00', '2024-04-27 13:15:00'),
(224, 42, 374, 20, 0, 0, 0, '2024-05-18', 1, '2024-04-27 13:15:00', '2024-04-27 13:15:00'),
(225, 42, 375, 20, 0, 0, 0, '2024-05-18', 1, '2024-04-27 13:15:00', '2024-04-27 13:15:00'),
(226, 42, 376, 20, 0, 0, 0, '2024-05-18', 1, '2024-04-27 13:15:00', '2024-04-27 13:15:00'),
(227, 42, 377, 20, 0, 0, 0, '2024-05-18', 1, '2024-04-27 13:15:00', '2024-04-27 13:15:00'),
(228, 42, 378, 20, 0, 0, 0, '2024-05-18', 1, '2024-04-27 13:15:00', '2024-04-27 13:15:00'),
(229, 42, 379, 20, 0, 0, 0, '2024-05-18', 1, '2024-04-27 13:15:00', '2024-04-27 13:15:00'),
(230, 42, 380, 20, 0, 0, 0, '2024-05-18', 1, '2024-04-27 13:15:00', '2024-04-27 13:15:00'),
(231, 42, 381, 20, 0, 0, 0, '2024-05-18', 1, '2024-04-27 13:15:00', '2024-04-27 13:15:00'),
(232, 42, 382, 20, 0, 0, 0, '2024-05-18', 1, '2024-04-27 13:15:00', '2024-04-27 13:15:00'),
(233, 43, 401, 1, 0, 0, 0, '2024-05-18', 1, '2024-04-30 10:04:12', '2024-04-30 10:04:59'),
(234, 44, 402, 2, 0, 0, 0, '2024-05-18', 1, '2024-05-06 03:51:13', '2024-05-06 03:51:27'),
(235, 45, 403, 50, 0, 0, 0, '2024-05-18', 1, '2024-05-10 08:05:19', '2024-05-10 08:05:19'),
(236, 45, 404, 0, 0, 0, 0, '2024-05-18', 1, '2024-05-10 08:05:19', '2024-05-10 08:05:19'),
(237, 45, 405, 0, 0, 0, 0, '2024-05-18', 1, '2024-05-10 08:05:19', '2024-05-10 08:05:19'),
(238, 46, 406, 100, 0, 0, 0, '2024-05-18', 1, '2024-05-10 13:40:26', '2024-05-10 13:42:36'),
(239, 46, 407, 100, 0, 0, 0, '2024-05-18', 1, '2024-05-10 13:40:26', '2024-05-10 13:40:56'),
(240, 46, 408, 100, 0, 0, 0, '2024-05-18', 1, '2024-05-10 13:40:26', '2024-05-10 13:42:36'),
(241, 47, 409, 500, 0, 0, 0, '2024-05-18', 1, '2024-05-15 12:29:24', '2024-05-15 12:29:24'),
(242, 47, 410, 500, 0, 0, 0, '2024-05-18', 1, '2024-05-15 12:29:24', '2024-05-15 12:29:24'),
(243, 47, 411, 500, 0, 0, 0, '2024-05-18', 1, '2024-05-15 12:29:24', '2024-05-15 12:29:24'),
(244, 48, 412, 700, 0, 0, 0, '2024-05-18', 1, '2024-05-16 10:47:26', '2024-05-16 10:47:26'),
(245, 55, 409, 12, 0, 0, 0, '2024-05-18', 1, '2024-05-18 13:26:50', '2024-05-18 13:26:50'),
(246, 55, 410, 10, 0, 0, 0, '2024-05-18', 1, '2024-05-18 13:26:50', '2024-05-18 13:26:50'),
(247, 55, 411, 0, 0, 0, 0, '2024-05-18', 1, '2024-05-18 13:26:50', '2024-05-18 13:26:50'),
(248, 60, 412, 100, 0, 70, 30, '2024-05-18', 1, '2024-05-18 13:59:52', '2024-05-18 14:35:53'),
(249, 62, 398, 100, 15, 80, 5, '2024-05-19', 1, '2024-05-18 14:46:01', '2024-05-18 14:46:36'),
(250, 63, 412, 300, 20, 250, 30, '2024-05-20', 1, '2024-05-20 12:04:12', '2024-05-20 12:04:12'),
(251, 64, 412, 50, 30, 10, 10, '2024-05-20', 1, '2024-05-20 12:05:36', '2024-05-20 12:06:00'),
(252, 65, 414, 20, 0, 20, 0, '2024-05-24', 1, '2024-05-24 09:06:53', '2024-05-24 09:06:53'),
(253, 65, 415, 20, 0, 20, 0, '2024-05-24', 1, '2024-05-24 09:06:53', '2024-05-24 09:06:53'),
(254, 65, 416, 100, 0, 100, 0, '2024-05-24', 1, '2024-05-24 09:06:53', '2024-05-24 09:06:53'),
(255, 66, 417, 50, 0, 50, 0, '2024-05-25', 1, '2024-05-25 06:32:04', '2024-05-25 06:33:54'),
(256, 67, 417, 50, 0, 50, 0, '2024-05-25', 1, '2024-05-25 06:32:43', '2024-05-25 06:32:43'),
(257, 68, 417, 10, 0, 10, 0, '2024-05-25', 1, '2024-05-25 06:35:57', '2024-05-25 06:35:57'),
(258, 69, 418, 1000, 0, 1000, 0, '2024-05-25', 1, '2024-05-25 07:08:24', '2024-05-25 07:08:24'),
(259, 69, 419, 1000, 0, 1000, 0, '2024-05-25', 1, '2024-05-25 07:08:25', '2024-05-25 07:08:25'),
(260, 69, 420, 1000, 0, 1000, 0, '2024-05-25', 1, '2024-05-25 07:08:25', '2024-05-25 07:08:25'),
(261, 69, 421, 1000, 0, 1000, 0, '2024-05-25', 1, '2024-05-25 07:08:25', '2024-05-25 07:08:25'),
(262, 69, 422, 1000, 0, 1000, 0, '2024-05-25', 1, '2024-05-25 07:08:25', '2024-05-25 07:08:25'),
(263, 69, 423, 100, 0, 100, 0, '2024-05-25', 1, '2024-05-25 07:08:25', '2024-05-25 07:08:25'),
(264, 69, 424, 1000, 0, 1000, 0, '2024-05-25', 1, '2024-05-25 07:08:25', '2024-05-25 07:08:25'),
(265, 69, 425, 1000, 0, 1000, 0, '2024-05-25', 1, '2024-05-25 07:08:25', '2024-05-25 07:08:25'),
(266, 69, 426, 1000, 0, 1000, 0, '2024-05-25', 1, '2024-05-25 07:08:25', '2024-05-25 07:08:25'),
(267, 69, 427, 1000, 0, 1000, 0, '2024-05-25', 1, '2024-05-25 07:08:25', '2024-05-25 07:08:25'),
(268, 69, 428, 1000, 0, 1000, 0, '2024-05-25', 1, '2024-05-25 07:08:25', '2024-05-25 07:08:25'),
(269, 69, 429, 1000, 0, 1000, 0, '2024-05-25', 1, '2024-05-25 07:08:25', '2024-05-25 07:08:25'),
(270, 69, 430, 1000, 0, 1000, 0, '2024-05-25', 1, '2024-05-25 07:08:25', '2024-05-25 07:08:25'),
(271, 69, 431, 1000, 0, 1000, 0, '2024-05-25', 1, '2024-05-25 07:08:25', '2024-05-25 07:08:25'),
(272, 69, 432, 1000, 0, 1000, 0, '2024-05-25', 1, '2024-05-25 07:08:25', '2024-05-25 07:08:25'),
(273, 69, 433, 1000, 0, 1000, 0, '2024-05-25', 1, '2024-05-25 07:08:25', '2024-05-25 07:08:25'),
(274, 70, 440, 6, 0, 6, 0, '2024-05-28', 1, '2024-05-27 14:32:44', '2024-05-27 14:32:44'),
(275, 70, 441, 100, 0, 100, 0, '2024-05-28', 1, '2024-05-27 14:32:44', '2024-05-27 14:32:44'),
(276, 71, 442, 200, 0, 200, 0, '2024-05-29', 1, '2024-05-29 04:57:51', '2024-05-29 04:57:51'),
(277, 72, 444, 1000, 0, 1000, 0, '2024-05-30', 1, '2024-05-30 02:17:55', '2024-05-30 02:17:55'),
(278, 72, 445, 500, 0, 500, 0, '2024-05-30', 1, '2024-05-30 02:17:55', '2024-05-30 02:17:55'),
(279, 73, 446, 800, 0, 800, 0, '2024-05-30', 1, '2024-05-30 02:22:01', '2024-05-30 02:22:01'),
(280, 74, 448, 800, 0, 800, 0, '2024-05-30', 1, '2024-05-30 02:38:37', '2024-05-30 02:38:37'),
(281, 75, 449, 500, 0, 500, 0, '2024-05-30', 1, '2024-05-30 02:40:44', '2024-05-30 02:40:44'),
(282, 76, 450, 5, 0, 5, 0, '2024-05-30', 1, '2024-05-30 04:59:23', '2024-05-30 04:59:23'),
(283, 77, 451, 23, 0, 23, 0, '2024-05-30', 1, '2024-05-30 05:24:00', '2024-05-30 05:24:00'),
(284, 77, 452, 12, 0, 12, 0, '2024-05-30', 1, '2024-05-30 05:24:00', '2024-05-30 05:24:00'),
(285, 78, 454, 15, 0, 15, 0, '2024-05-30', 1, '2024-05-30 05:28:33', '2024-05-30 05:28:33'),
(286, 79, 413, 100, 0, 100, 0, '2024-05-30', 1, '2024-05-30 07:58:43', '2024-05-30 08:01:52'),
(287, 80, 413, 80, 0, 80, 0, '2024-05-30', 1, '2024-05-30 08:00:11', '2024-05-30 08:00:11'),
(288, 81, 455, 600, 0, 400, 200, '2024-07-01', 1, '2024-07-01 08:06:29', '2024-07-01 08:06:29'),
(289, 81, 456, 400, 0, 400, 0, '2024-07-01', 1, '2024-07-01 08:06:29', '2024-07-01 08:06:29'),
(290, 82, 457, 50, 0, 30, 20, '2024-08-17', 1, '2024-08-17 06:01:43', '2024-08-17 06:04:08'),
(291, 83, 460, 450, 50, 350, 50, '2025-01-16', 1, '2025-01-16 05:49:39', '2025-01-16 05:49:39'),
(292, 84, 459, 2, 0, 2, 0, '2025-03-23', 1, '2025-03-23 06:50:55', '2025-03-23 06:50:55'),
(293, 85, 471, 12, 2, 6, 4, '2025-03-23', 1, '2025-03-23 13:28:48', '2025-03-23 14:10:29'),
(294, 86, 474, 50, 0, 50, 0, '2025-04-07', 1, '2025-04-06 14:55:18', '2025-04-06 14:55:18'),
(295, 86, 475, 58, 0, 50, 8, '2025-04-07', 1, '2025-04-06 14:55:18', '2025-04-06 14:55:18'),
(296, 87, 478, 120, 0, 120, 0, '2025-04-19', 1, '2025-04-19 03:21:31', '2025-04-19 03:21:31'),
(297, 88, 479, 9, 0, 9, 0, '2025-04-19', 1, '2025-04-19 03:30:37', '2025-04-19 03:30:37'),
(298, 89, 481, 200, 50, 150, 0, '2025-04-19', 1, '2025-04-19 03:39:09', '2025-04-19 03:39:09'),
(299, 90, 482, 12, 0, 12, 0, '2025-04-27', 1, '2025-04-27 04:10:19', '2025-04-27 04:10:19'),
(300, 91, 483, 12, 0, 12, 0, '2025-04-27', 1, '2025-04-27 04:26:15', '2025-04-27 04:26:15'),
(301, 92, 484, 10, 0, 10, 0, '2025-04-27', 1, '2025-04-27 04:30:14', '2025-04-27 04:30:14'),
(302, 92, 485, 20, 0, 20, 0, '2025-04-27', 1, '2025-04-27 04:30:14', '2025-04-27 04:30:14'),
(303, 92, 486, 18, 0, 18, 0, '2025-04-27', 1, '2025-04-27 04:30:14', '2025-04-27 04:30:14');

-- --------------------------------------------------------

--
-- Table structure for table `returns`
--

CREATE TABLE `returns` (
  `return_id` bigint(20) UNSIGNED NOT NULL,
  `receive_id` bigint(20) UNSIGNED NOT NULL,
  `return_no` varchar(255) NOT NULL,
  `return_date` date NOT NULL,
  `description` longtext DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `returns`
--

INSERT INTO `returns` (`return_id`, `receive_id`, `return_no`, `return_date`, `description`, `created_by`, `created_at`, `updated_at`) VALUES
(33, 39, 'Return1-R1-P2404002', '2024-04-26', '<p>Desc</p>', 1, '2024-04-26 12:40:52', '2024-04-26 12:40:52'),
(34, 42, 'Return1-R3-P2404001', '2024-04-27', NULL, 1, '2024-04-27 13:15:53', '2024-04-27 13:15:53'),
(35, 42, 'Return2-R3-P2404001', '2024-04-27', NULL, 1, '2024-04-27 13:16:20', '2024-04-27 13:16:20'),
(36, 46, 'Return1-R1-P2405003', '2024-05-10', '<p>Material of Amount 15k Returned</p>', 1, '2024-05-10 13:43:11', '2024-05-10 13:43:11'),
(37, 47, 'Return1-R1-P2405004', '2024-05-15', NULL, 1, '2024-05-15 12:29:58', '2024-05-15 12:29:58'),
(38, 48, 'Return1-R1-P2405005', '2024-05-16', NULL, 1, '2024-05-16 10:50:51', '2024-05-16 10:50:51'),
(39, 66, 'Return1-R1-P2405008', '2024-05-25', NULL, 1, '2024-05-25 06:35:15', '2024-05-25 06:35:15'),
(40, 79, 'Return1-R1-P2405006', '2024-05-30', NULL, 1, '2024-05-30 07:59:38', '2024-05-30 07:59:38'),
(41, 80, 'Return1-R2-P2405006', '2024-05-30', NULL, 1, '2024-05-30 08:16:02', '2024-05-30 08:16:02'),
(42, 80, 'Return2-R2-P2405006', '2024-05-30', NULL, 1, '2024-05-30 08:17:25', '2024-05-30 08:17:25'),
(43, 82, 'Return1-R1-P2408001', '2024-08-17', NULL, 1, '2024-08-17 06:05:23', '2024-08-17 06:05:23'),
(44, 83, 'Return1-R1-P2501001', '2025-01-16', NULL, 1, '2025-01-16 05:51:14', '2025-01-16 05:51:14'),
(45, 85, 'Return1-R1-P2503002', '2025-04-06', '<p>&nbsp;&nbsp;&nbsp;&nbsp;<br></p>', 1, '2025-04-06 12:23:43', '2025-04-06 13:19:33'),
(46, 86, 'Return1-R1-P2504001', '2025-04-06', NULL, 1, '2025-04-06 15:01:42', '2025-04-06 15:01:42');

-- --------------------------------------------------------

--
-- Table structure for table `return_materials`
--

CREATE TABLE `return_materials` (
  `return_material_id` bigint(20) UNSIGNED NOT NULL,
  `return_id` bigint(20) UNSIGNED NOT NULL,
  `receive_material_id` bigint(20) UNSIGNED NOT NULL,
  `quantity` double UNSIGNED NOT NULL,
  `remarks` longtext DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `return_materials`
--

INSERT INTO `return_materials` (`return_material_id`, `return_id`, `receive_material_id`, `quantity`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(111, 33, 198, 12, 'Testing Return', 1, '2024-04-26 12:40:52', '2024-04-26 12:40:52'),
(112, 34, 223, 5, NULL, 1, '2024-04-27 13:15:53', '2024-04-27 13:15:53'),
(113, 34, 224, 5, NULL, 1, '2024-04-27 13:15:53', '2024-04-27 13:15:53'),
(114, 34, 225, 5, NULL, 1, '2024-04-27 13:15:53', '2024-04-27 13:15:53'),
(115, 34, 226, 5, NULL, 1, '2024-04-27 13:15:53', '2024-04-27 13:15:53'),
(116, 34, 227, 5, NULL, 1, '2024-04-27 13:15:53', '2024-04-27 13:15:53'),
(117, 34, 228, 5, NULL, 1, '2024-04-27 13:15:53', '2024-04-27 13:15:53'),
(118, 34, 229, 5, NULL, 1, '2024-04-27 13:15:53', '2024-04-27 13:15:53'),
(119, 34, 230, 5, NULL, 1, '2024-04-27 13:15:53', '2024-04-27 13:15:53'),
(120, 34, 231, 5, NULL, 1, '2024-04-27 13:15:53', '2024-04-27 13:15:53'),
(121, 34, 232, 5, NULL, 1, '2024-04-27 13:15:53', '2024-04-27 13:15:53'),
(122, 35, 223, 10, NULL, 1, '2024-04-27 13:16:20', '2024-04-27 13:16:20'),
(123, 35, 224, 10, NULL, 1, '2024-04-27 13:16:20', '2024-04-27 13:16:20'),
(124, 35, 225, 10, NULL, 1, '2024-04-27 13:16:20', '2024-04-27 13:16:20'),
(125, 35, 226, 10, NULL, 1, '2024-04-27 13:16:20', '2024-04-27 13:16:20'),
(126, 35, 227, 10, NULL, 1, '2024-04-27 13:16:20', '2024-04-27 13:16:20'),
(127, 35, 228, 10, NULL, 1, '2024-04-27 13:16:20', '2024-04-27 13:16:20'),
(128, 35, 229, 10, NULL, 1, '2024-04-27 13:16:20', '2024-04-27 13:16:20'),
(129, 35, 230, 10, NULL, 1, '2024-04-27 13:16:20', '2024-04-27 13:16:20'),
(130, 35, 231, 10, NULL, 1, '2024-04-27 13:16:20', '2024-04-27 13:16:20'),
(131, 35, 232, 10, NULL, 1, '2024-04-27 13:16:20', '2024-04-27 13:16:20'),
(132, 36, 238, 50, NULL, 1, '2024-05-10 13:43:11', '2024-05-10 13:43:11'),
(133, 36, 239, 50, NULL, 1, '2024-05-10 13:43:11', '2024-05-10 13:43:11'),
(134, 36, 240, 50, NULL, 1, '2024-05-10 13:43:11', '2024-05-10 13:43:11'),
(135, 37, 241, 100, 'Dummy Return', 1, '2024-05-15 12:29:58', '2024-05-15 12:29:58'),
(136, 37, 242, 100, 'Dummy Return', 1, '2024-05-15 12:29:58', '2024-05-15 12:29:58'),
(137, 37, 243, 0, NULL, 1, '2024-05-15 12:29:58', '2024-05-15 12:29:58'),
(138, 38, 244, 200, 'Check', 1, '2024-05-16 10:50:51', '2024-05-16 10:50:51'),
(139, 39, 255, 10, 'Check', 1, '2024-05-25 06:35:15', '2024-05-25 06:35:42'),
(140, 40, 286, 30, 'check', 1, '2024-05-30 07:59:38', '2024-05-30 07:59:38'),
(141, 41, 287, 10, 'check', 1, '2024-05-30 08:16:02', '2024-05-30 08:16:02'),
(142, 42, 287, 20, '20', 1, '2024-05-30 08:17:25', '2024-05-30 08:17:25'),
(143, 43, 290, 10, NULL, 1, '2024-08-17 06:05:23', '2024-08-17 06:05:23'),
(144, 44, 291, 100, NULL, 1, '2025-01-16 05:51:14', '2025-01-16 05:51:14'),
(145, 45, 293, 3, 'None Updated', 1, '2025-04-06 12:23:43', '2025-04-06 13:19:33'),
(146, 46, 294, 0, NULL, 1, '2025-04-06 15:01:42', '2025-04-06 15:01:42'),
(147, 46, 295, 8, 'Returned', 1, '2025-04-06 15:01:42', '2025-04-06 15:01:42');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `created_at`, `updated_at`) VALUES
(1, 'Admin', '2025-05-16 04:57:37', '2025-05-16 04:57:37'),
(2, 'Manager', '2025-05-16 04:57:37', '2025-05-16 04:57:37'),
(3, 'Accountant', '2025-05-16 04:57:37', '2025-05-16 04:57:37'),
(8, 'Export', '2025-04-12 03:17:32', '2025-05-13 08:36:12');

-- --------------------------------------------------------

--
-- Table structure for table `salaries`
--

CREATE TABLE `salaries` (
  `salary_id` bigint(20) UNSIGNED NOT NULL,
  `employee_id` bigint(20) UNSIGNED NOT NULL,
  `amount` bigint(20) UNSIGNED NOT NULL,
  `status` bigint(20) UNSIGNED NOT NULL DEFAULT 1,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `salaries`
--

INSERT INTO `salaries` (`salary_id`, `employee_id`, `amount`, `status`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 8, 15000, 0, 1, '2024-04-20 04:54:16', '2024-04-20 07:51:49'),
(3, 8, 16000, 1, 1, '2024-04-20 07:51:49', '2024-04-20 07:51:49'),
(4, 7, 20000, 1, 1, '2024-04-20 07:56:29', '2024-04-20 07:56:29'),
(5, 5, 35000, 1, 1, '2024-04-20 07:56:56', '2024-04-20 07:56:56'),
(6, 11, 12020, 1, 1, '2024-04-29 10:53:04', '2024-04-29 10:53:04'),
(7, 12, 0, 1, 1, '2024-04-30 07:51:22', '2024-04-30 07:51:22'),
(8, 13, 50000, 1, 1, '2024-05-31 13:39:41', '2024-05-31 13:39:41'),
(9, 1, 32000, 1, 1, '2025-04-12 03:05:45', '2025-04-12 03:05:45');

-- --------------------------------------------------------

--
-- Table structure for table `stocks`
--

CREATE TABLE `stocks` (
  `stock_id` bigint(20) UNSIGNED NOT NULL,
  `issue_id` bigint(20) UNSIGNED DEFAULT NULL,
  `issue_for` bigint(20) UNSIGNED DEFAULT NULL,
  `stock_no` varchar(255) NOT NULL,
  `order_id` bigint(20) UNSIGNED NOT NULL,
  `machine_id` bigint(20) UNSIGNED DEFAULT NULL,
  `table_name` varchar(255) NOT NULL,
  `employee_id` bigint(20) UNSIGNED NOT NULL,
  `stock_type` bigint(20) UNSIGNED NOT NULL DEFAULT 2 COMMENT 'Stock In/Out',
  `stock_date` date NOT NULL,
  `stock_status` bigint(20) UNSIGNED NOT NULL DEFAULT 1 COMMENT 'NotReceived#0, CompletelyReceived#1, PartiallyReceived#2, Delivery#3, MachineMaterial#4,\r\nMaterialProcessing#5',
  `description` longtext DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `stocks`
--

INSERT INTO `stocks` (`stock_id`, `issue_id`, `issue_for`, `stock_no`, `order_id`, `machine_id`, `table_name`, `employee_id`, `stock_type`, `stock_date`, `stock_status`, `description`, `created_by`, `created_at`, `updated_at`) VALUES
(0, NULL, 0, 'I24000000', 0, NULL, 'mprocess', 0, 2, '2024-05-09', 0, NULL, 1, '2024-04-26 14:05:16', '2024-05-09 08:39:30'),
(1, NULL, 0, 'I23000000', 0, NULL, 'openingStock', 0, 1, '2024-05-09', 0, NULL, 1, '2024-04-26 14:05:16', '2024-05-09 08:39:30'),
(82, NULL, 74, 'I24040001', 16, NULL, 'vendor', 6, 2, '2024-05-09', 2, NULL, 1, '2024-04-26 14:05:16', '2024-05-09 08:39:30'),
(83, 82, 0, 'R1-I24040001', 16, NULL, 'vendor', 6, 1, '2024-04-27', 2, NULL, 1, '2024-04-26 14:18:45', '2024-04-26 14:18:45'),
(84, 82, 0, 'R2-I24040001', 16, NULL, 'vendor', 6, 1, '2024-04-27', 1, NULL, 1, '2024-04-26 14:33:56', '2024-04-26 14:33:56'),
(85, NULL, 73, 'I24040002', 17, NULL, 'employee', 5, 2, '2024-05-09', 1, NULL, 1, '2024-04-27 10:53:46', '2024-05-09 08:39:24'),
(86, 85, 0, 'R1-I24040002', 17, NULL, 'employee', 5, 1, '2024-04-27', 1, NULL, 1, '2024-04-27 11:42:38', '2024-04-27 11:42:38'),
(87, NULL, 72, 'I24040003', 16, NULL, 'employee', 12, 2, '2024-05-09', 1, '<p>Stitching of L Size is issued for Packing</p>', 1, '2024-04-30 08:09:17', '2024-05-09 08:39:17'),
(88, 87, 0, 'R1-I24040003', 16, NULL, 'employee', 12, 1, '2024-04-30', 1, NULL, 1, '2024-04-30 08:13:20', '2024-04-30 08:13:20'),
(89, NULL, 74, 'I24050001', 18, NULL, 'employee', 6, 2, '2024-05-09', 1, NULL, 1, '2024-05-01 02:50:47', '2024-05-09 08:39:09'),
(90, NULL, 73, 'I24050002', 18, NULL, 'vendor', 6, 2, '2024-05-09', 2, NULL, 1, '2024-05-01 02:53:26', '2024-05-09 08:38:59'),
(91, 90, 0, 'R1-I24050002', 18, NULL, 'vendor', 6, 1, '2024-05-01', 2, NULL, 1, '2024-05-01 03:08:01', '2024-05-01 03:08:01'),
(92, 89, 0, 'R1-I24050001', 18, NULL, 'employee', 6, 1, '2024-05-01', 2, NULL, 1, '2024-05-01 03:10:23', '2024-05-01 03:10:23'),
(93, NULL, 72, 'I24050003', 19, NULL, 'employee', 12, 2, '2024-05-04', 1, '<p>Desc of Issuance for Cutting</p>', 1, '2024-05-04 12:29:42', '2024-05-04 13:15:49'),
(94, 93, NULL, 'R1-I24050003', 19, NULL, 'employee', 12, 1, '2024-05-04', 1, NULL, 1, '2024-05-04 13:15:49', '2024-05-04 13:15:49'),
(95, 89, NULL, 'R2-I24050001', 18, NULL, 'employee', 6, 1, '2024-05-05', 1, NULL, 1, '2024-05-04 14:38:39', '2024-05-04 14:38:39'),
(109, NULL, NULL, 'Order 16 Delivery', 16, NULL, 'delivery', 0, 2, '2024-05-07', 3, '<p>Desc to Store in Delivery Table</p>', 1, '2024-05-07 04:26:42', '2024-05-08 13:50:13'),
(110, NULL, 73, 'I24050005', 16, NULL, 'employee', 5, 2, '2024-05-09', 2, '<p>Issuance of Some materials for All Order Items</p>', 1, '2024-05-07 11:12:06', '2024-05-09 08:38:49'),
(111, 110, NULL, 'R1-I24050005', 16, NULL, 'employee', 5, 1, '2024-05-07', 2, '<p>Dummy Receiving of Order 101 All items Qty 100</p>', 1, '2024-05-07 11:15:08', '2024-05-07 11:15:08'),
(112, NULL, NULL, 'Order 16 Delivery 2', 16, NULL, 'delivery', 0, 2, '2024-05-07', 3, '<p>Desc</p>', 1, '2024-05-07 12:55:09', '2024-05-07 12:55:09'),
(113, NULL, NULL, 'Order 16 Delivery 3', 16, NULL, 'delivery', 0, 2, '2024-05-07', 3, '<p>Desc</p>', 1, '2024-05-07 13:43:20', '2024-05-07 13:43:20'),
(114, NULL, 74, 'I24050008', 16, NULL, 'employee', 5, 2, '2024-05-07', 0, '<p>Desc</p>', 1, '2024-05-07 13:49:24', '2024-05-07 13:49:24'),
(117, NULL, NULL, 'Check Expense Updated 123', 16, NULL, 'delivery', 0, 2, '2024-05-09', 3, 'Description of Delivery', 1, '2024-05-08 14:04:20', '2024-05-08 14:25:26'),
(118, NULL, NULL, 'Check Expense 213', 16, NULL, 'delivery', 0, 2, '2024-05-09', 3, NULL, 1, '2024-05-08 14:06:40', '2024-05-08 14:06:40'),
(119, NULL, NULL, 'Check 123', 16, NULL, 'delivery', 0, 2, '2024-05-09', 3, NULL, 1, '2024-05-08 14:09:36', '2024-05-08 14:09:36'),
(120, NULL, 74, 'I24050012', 17, NULL, 'employee', 6, 2, '2024-05-09', 0, NULL, 1, '2024-05-09 04:33:02', '2024-05-09 04:33:02'),
(121, NULL, 72, 'I24050016', 17, NULL, 'employee', 6, 2, '2024-05-11', 0, NULL, 1, '2024-05-09 08:53:31', '2024-05-11 05:31:02'),
(122, 82, NULL, 'R3-I24040001', 16, NULL, 'vendor', 6, 1, '2024-05-09', 2, NULL, 1, '2024-05-09 13:08:17', '2024-05-09 13:08:17'),
(123, NULL, NULL, 'Order 23 Delivery 1', 23, NULL, 'delivery', 0, 2, '2024-05-09', 3, NULL, 1, '2024-05-09 13:52:55', '2024-05-09 13:52:55'),
(124, NULL, 72, 'I24050018', 16, NULL, 'employee', 6, 2, '2024-05-10', 1, NULL, 1, '2024-05-10 08:28:09', '2024-05-10 08:29:11'),
(125, 124, NULL, 'R1-I24050018', 16, NULL, 'employee', 6, 1, '2024-05-10', 1, NULL, 1, '2024-05-10 08:29:11', '2024-05-10 08:29:11'),
(126, NULL, 72, 'I24050019', 16, NULL, 'employee', 12, 2, '2024-05-14', 2, NULL, 1, '2024-05-14 08:07:42', '2024-05-28 06:54:30'),
(127, NULL, 73, 'I24050020', 17, NULL, 'employee', 12, 2, '2024-05-14', 1, NULL, 1, '2024-05-14 08:10:38', '2024-05-28 07:18:20'),
(135, NULL, 73, 'I24050021', 16, NULL, 'employee', 5, 2, '2024-05-25', 1, NULL, 1, '2024-05-25 06:48:13', '2024-05-25 06:54:07'),
(136, 135, NULL, 'R1-I24050021', 16, 0, 'employee', 5, 1, '2024-05-25', 1, NULL, 1, '2024-05-25 06:54:07', '2024-05-25 06:54:07'),
(137, NULL, 0, 'I24050022', 0, 2, 'employee', 12, 2, '2024-05-25', 4, NULL, 1, '2024-05-25 07:10:24', '2024-05-25 07:10:24'),
(139, NULL, NULL, 'Delivery 18-01', 18, NULL, 'delivery', 0, 2, '2024-05-25', 3, NULL, 1, '2024-05-25 12:49:28', '2024-05-25 12:49:28'),
(141, NULL, NULL, 'Delivery 18-02', 18, NULL, 'delivery', 0, 2, '2024-05-25', 3, '<p>Desc</p>', 1, '2024-05-25 13:26:16', '2024-05-25 13:26:16'),
(143, 126, NULL, 'R1-I24050019', 16, NULL, 'employee', 12, 1, '2024-05-28', 2, NULL, 1, '2024-05-28 06:54:30', '2024-05-28 06:54:30'),
(145, 127, NULL, 'R1-I24050020', 17, NULL, 'employee', 12, 1, '2024-05-28', 1, NULL, 1, '2024-05-28 07:18:20', '2024-05-28 07:18:20'),
(146, 127, NULL, 'R2-I24050020', 17, NULL, 'employee', 12, 1, '2024-05-28', 1, NULL, 1, '2024-05-28 07:18:43', '2024-05-28 07:18:43'),
(147, NULL, 72, 'I24050025', 16, NULL, 'employee', 12, 2, '2024-05-28', 0, NULL, 1, '2024-05-28 10:56:04', '2024-05-28 10:56:04'),
(10012, NULL, 72, 'I24050027', 16, NULL, 'employee', 12, 2, '2024-05-30', 0, NULL, 1, '2024-05-30 12:03:26', '2024-05-30 12:03:26'),
(10013, NULL, 72, 'I24050028', 27, NULL, 'employee', 12, 2, '2024-05-30', 0, NULL, 1, '2024-05-30 12:08:48', '2024-05-30 12:08:48'),
(10014, NULL, 72, 'I24060001', 26, NULL, 'employee', 6, 2, '2024-06-03', 2, NULL, 1, '2024-06-03 13:33:27', '2024-06-03 13:34:02'),
(10015, 10014, NULL, 'R1-I24060001', 26, NULL, 'employee', 6, 1, '2024-06-03', 2, NULL, 1, '2024-06-03 13:34:02', '2024-06-03 13:34:02'),
(10016, NULL, 74, 'I24060002', 26, NULL, 'employee', 6, 2, '2024-06-03', 0, '<p>Desc</p>', 1, '2024-06-03 13:35:51', '2024-06-03 13:35:51'),
(10017, NULL, 74, 'I24060003', 27, NULL, 'vendor', 1, 2, '2024-06-05', 1, NULL, 1, '2024-06-05 10:44:21', '2024-06-05 10:44:43'),
(10018, 10017, NULL, 'R1-I24060003', 27, NULL, 'vendor', 1, 1, '2024-06-05', 1, NULL, 1, '2024-06-05 10:44:43', '2024-06-05 10:44:43'),
(10019, NULL, 72, 'I24080001', 30, NULL, 'employee', 6, 2, '2024-08-28', 0, NULL, 1, '2024-08-28 06:35:02', '2024-08-28 06:35:02'),
(10020, NULL, 72, 'I24080002', 24, NULL, 'employee', 12, 2, '2024-08-28', 2, NULL, 1, '2024-08-28 12:40:12', '2024-09-01 23:35:41'),
(10021, 10020, NULL, 'R1-I24080002', 24, NULL, 'employee', 12, 1, '2024-08-28', 2, NULL, 1, '2024-08-28 12:57:46', '2024-09-01 23:35:41'),
(10022, NULL, 72, 'I24080003', 16, NULL, 'employee', 12, 2, '2024-08-29', 0, NULL, 1, '2024-08-29 13:47:36', '2024-08-29 13:47:36'),
(10023, NULL, 72, 'I24090001', 29, NULL, 'employee', 12, 2, '2024-08-01', 0, NULL, 1, '2024-09-05 02:49:19', '2024-09-05 02:49:19'),
(10024, NULL, 72, 'I25010001', 30, NULL, 'employee', 12, 2, '2025-01-01', 0, NULL, 1, '2025-01-01 04:23:11', '2025-01-01 04:23:11'),
(10025, NULL, NULL, 'xyz', 16, NULL, 'delivery', 0, 2, '2025-01-16', 3, NULL, 1, '2025-01-16 05:32:39', '2025-01-16 05:32:39'),
(10026, NULL, 74, 'I25040001', 32, NULL, 'employee', 5, 2, '2025-04-19', 1, NULL, 1, '2025-04-19 03:41:04', '2025-04-19 03:41:46'),
(10027, 10026, NULL, 'R1-I25040001', 32, NULL, 'employee', 5, 1, '2025-04-19', 1, NULL, 1, '2025-04-19 03:41:46', '2025-04-19 03:41:46'),
(10028, NULL, 72, 'I25040002', 33, NULL, 'employee', 12, 2, '2025-04-27', 1, NULL, 1, '2025-04-27 04:35:18', '2025-04-27 04:36:32'),
(10029, 10028, NULL, 'R1-I25040002', 33, NULL, 'employee', 12, 1, '2025-04-27', 1, NULL, 1, '2025-04-27 04:36:32', '2025-04-27 04:36:32'),
(10030, NULL, 72, 'I25040003', 33, NULL, 'employee', 5, 2, '2025-04-27', 0, NULL, 1, '2025-04-27 05:22:06', '2025-04-27 05:22:06'),
(10031, NULL, 72, 'I25050001', 32, NULL, 'employee', 12, 2, '2025-05-12', 1, NULL, 1, '2025-05-12 13:28:25', '2025-05-12 13:29:09'),
(10032, 10031, NULL, 'R1-I25050001', 32, NULL, 'employee', 12, 1, '2025-05-12', 1, NULL, 1, '2025-05-12 13:29:09', '2025-05-12 13:29:09'),
(10033, 10031, NULL, 'R2-I25050001', 32, NULL, 'employee', 12, 1, '2025-05-12', 1, NULL, 1, '2025-05-12 13:47:11', '2025-05-12 13:47:11');

-- --------------------------------------------------------

--
-- Table structure for table `stock_items`
--

CREATE TABLE `stock_items` (
  `stock_item_id` bigint(20) UNSIGNED NOT NULL,
  `stock_id` bigint(20) UNSIGNED NOT NULL,
  `product_type_id` bigint(20) UNSIGNED NOT NULL,
  `material_id` bigint(20) UNSIGNED NOT NULL,
  `quantity` double UNSIGNED NOT NULL,
  `stage_id` bigint(20) UNSIGNED NOT NULL,
  `work_logs` varchar(255) NOT NULL,
  `work_wages` varchar(255) DEFAULT '0',
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `stock_items`
--

INSERT INTO `stock_items` (`stock_item_id`, `stock_id`, `product_type_id`, `material_id`, `quantity`, `stage_id`, `work_logs`, `work_wages`, `created_by`, `created_at`, `updated_at`) VALUES
(147, 82, 54, 14, 30, 0, '0', '0', 1, '2024-04-26 14:05:16', '2024-04-26 14:05:16'),
(148, 82, 54, 11, 20, 0, '0', '0', 1, '2024-04-26 14:05:16', '2024-04-26 14:05:16'),
(149, 82, 54, 13, 30, 0, '0', '0', 1, '2024-04-26 14:05:16', '2024-04-26 14:05:16'),
(150, 82, 55, 12, 30, 0, '0', '0', 1, '2024-04-26 14:05:16', '2024-04-26 14:05:16'),
(151, 82, 55, 11, 30, 0, '0', '0', 1, '2024-04-26 14:05:16', '2024-04-26 14:05:16'),
(152, 82, 56, 14, 30, 0, '0', '0', 1, '2024-04-26 14:05:16', '2024-04-26 14:05:16'),
(153, 82, 49, 14, 20, 0, '0', '0', 1, '2024-04-26 14:05:16', '2024-04-26 14:05:16'),
(154, 82, 49, 13, 20, 0, '0', '0', 1, '2024-04-26 14:05:16', '2024-04-26 14:05:16'),
(155, 82, 50, 14, 20, 0, '0', '0', 1, '2024-04-26 14:05:16', '2024-04-26 14:05:16'),
(156, 82, 51, 14, 20, 0, '0', '0', 1, '2024-04-26 14:05:16', '2024-04-26 14:05:16'),
(157, 82, 51, 13, 30, 0, '0', '0', 1, '2024-04-26 14:05:16', '2024-04-26 14:05:16'),
(158, 82, 52, 11, 10, 0, '0', '0', 1, '2024-04-26 14:05:16', '2024-04-26 14:05:16'),
(159, 82, 52, 12, 10, 0, '0', '0', 1, '2024-04-26 14:05:16', '2024-04-26 14:05:16'),
(160, 82, 53, 14, 20, 0, '0', '0', 1, '2024-04-26 14:05:16', '2024-04-26 14:05:16'),
(161, 82, 53, 12, 20, 0, '0', '0', 1, '2024-04-26 14:05:16', '2024-04-26 14:05:16'),
(162, 82, 53, 13, 30, 0, '0', '0', 1, '2024-04-26 14:05:16', '2024-04-26 14:05:16'),
(163, 83, 54, 0, 7, 72, '78', '12', 1, '2024-04-26 14:18:45', '2024-04-26 14:43:24'),
(164, 83, 55, 0, 10, 72, '78', '12', 1, '2024-04-26 14:18:45', '2024-04-26 14:43:24'),
(165, 83, 56, 0, 15, 72, '78', '12', 1, '2024-04-26 14:18:45', '2024-04-26 14:43:24'),
(166, 83, 49, 0, 7, 72, '78', '12', 1, '2024-04-26 14:18:45', '2024-04-26 14:43:24'),
(167, 83, 50, 0, 7, 72, '78', '12', 1, '2024-04-26 14:18:45', '2024-04-26 14:43:24'),
(168, 83, 51, 0, 8, 72, '78', '12', 1, '2024-04-26 14:18:45', '2024-04-26 14:43:24'),
(169, 83, 52, 0, 4, 72, '78', '12', 1, '2024-04-26 14:18:45', '2024-04-26 14:43:24'),
(171, 84, 54, 0, 10, 73, '78|79', '12|12', 1, '2024-04-26 14:33:56', '2024-04-26 14:42:56'),
(172, 85, 94, 11, 250, 0, '0', '0', 1, '2024-04-27 10:53:46', '2024-04-27 10:53:46'),
(173, 85, 94, 12, 250, 0, '0', '0', 1, '2024-04-27 10:53:46', '2024-04-27 10:53:46'),
(174, 85, 94, 13, 250, 0, '0', '0', 1, '2024-04-27 10:53:46', '2024-04-27 10:53:46'),
(175, 85, 94, 14, 250, 0, '0', '0', 1, '2024-04-27 10:53:46', '2024-04-27 10:53:46'),
(176, 85, 94, 10, 4, 0, '0', '0', 1, '2024-04-27 10:53:46', '2024-04-27 10:53:46'),
(177, 86, 94, 0, 120, 74, '84', '12', 1, '2024-04-27 11:42:38', '2024-04-27 11:42:38'),
(178, 87, 54, 0, 10, 73, '0', '0', 1, '2024-04-30 08:09:17', '2024-04-30 08:09:17'),
(179, 88, 54, 0, 10, 74, '84', '10', 1, '2024-04-30 08:13:20', '2024-04-30 08:17:51'),
(180, 89, 51, 11, 50, 0, '0', '0', 1, '2024-05-01 02:50:47', '2024-05-01 02:50:47'),
(181, 89, 51, 12, 80, 0, '0', '0', 1, '2024-05-01 02:50:47', '2024-05-01 02:50:47'),
(182, 89, 51, 13, 80, 0, '0', '0', 1, '2024-05-01 02:50:47', '2024-05-01 02:50:47'),
(183, 89, 52, 14, 80, 0, '0', '0', 1, '2024-05-01 02:50:47', '2024-05-01 02:50:47'),
(184, 89, 52, 12, 80, 0, '0', '0', 1, '2024-05-01 02:50:47', '2024-05-01 02:50:47'),
(185, 89, 52, 0, 4, 72, '0', '0', 1, '2024-05-01 02:50:47', '2024-05-01 02:50:47'),
(186, 90, 52, 12, 90, 72, '0', '0', 1, '2024-05-01 02:53:26', '2024-05-01 02:53:26'),
(187, 90, 52, 13, 90, 72, '0', '0', 1, '2024-05-01 02:53:26', '2024-05-01 02:53:26'),
(188, 90, 52, 11, 90, 72, '0', '0', 1, '2024-05-01 02:53:26', '2024-05-01 02:53:26'),
(189, 90, 51, 0, 8, 72, '0', '0', 1, '2024-05-01 02:53:26', '2024-05-01 02:53:26'),
(190, 91, 52, 0, 15, 72, '78', '24', 1, '2024-05-01 03:08:01', '2024-05-01 03:08:01'),
(191, 91, 52, 0, 15, 73, '79|80', '12|12', 1, '2024-05-01 03:08:01', '2024-05-01 03:08:01'),
(192, 91, 52, 11, 10, 0, '0', '0', 1, '2024-05-01 03:08:01', '2024-05-01 03:08:01'),
(193, 92, 52, 12, 1, 0, '0', '0', 1, '2024-05-01 03:10:23', '2024-05-01 03:10:23'),
(194, 92, 52, 0, 15, 72, '78', '12', 1, '2024-05-01 03:10:23', '2024-05-01 03:10:23'),
(195, 93, 50, 11, 35, 0, '0', '0', 1, '2024-05-04 12:29:42', '2024-05-04 12:29:42'),
(197, 94, 50, 0, 17, 72, '78', '12', 1, '2024-05-04 13:15:49', '2024-05-04 13:15:49'),
(198, 95, 51, 11, 10, 0, '0', '0', 1, '2024-05-04 14:38:39', '2024-05-04 14:38:39'),
(199, 95, 52, 12, 10, 0, '0', '0', 1, '2024-05-04 14:38:39', '2024-05-04 14:38:39'),
(200, 95, 52, 0, 10, 73, '79', '12', 1, '2024-05-04 14:38:39', '2024-05-04 14:38:39'),
(207, 109, 54, 0, 12, 74, '0', '0', 1, '2024-05-07 04:26:42', '2024-05-08 13:42:05'),
(209, 110, 54, 11, 100, 0, '0', '0', 1, '2024-05-07 11:12:06', '2024-05-07 11:12:06'),
(210, 110, 55, 11, 100, 0, '0', '0', 1, '2024-05-07 11:12:06', '2024-05-07 11:12:06'),
(211, 110, 56, 11, 100, 0, '0', '0', 1, '2024-05-07 11:12:06', '2024-05-07 11:12:06'),
(212, 110, 49, 14, 100, 0, '0', '0', 1, '2024-05-07 11:12:06', '2024-05-07 11:12:06'),
(213, 110, 50, 14, 100, 0, '0', '0', 1, '2024-05-07 11:12:06', '2024-05-07 11:12:06'),
(214, 110, 51, 12, 100, 0, '0', '0', 1, '2024-05-07 11:12:06', '2024-05-07 11:12:06'),
(215, 110, 52, 12, 100, 0, '0', '0', 1, '2024-05-07 11:12:06', '2024-05-07 11:12:06'),
(216, 110, 53, 12, 100, 0, '0', '0', 1, '2024-05-07 11:12:06', '2024-05-07 11:12:06'),
(217, 111, 54, 0, 100, 74, '84', '10', 1, '2024-05-07 11:15:08', '2024-05-07 11:15:08'),
(218, 111, 56, 0, 100, 74, '84', '10', 1, '2024-05-07 11:15:08', '2024-05-07 11:15:08'),
(219, 111, 49, 0, 100, 74, '84', '12', 1, '2024-05-07 11:15:08', '2024-05-07 11:15:08'),
(220, 111, 50, 0, 100, 74, '84', '12', 1, '2024-05-07 11:15:08', '2024-05-07 11:15:08'),
(221, 111, 51, 0, 100, 74, '84', '12', 1, '2024-05-07 11:15:08', '2024-05-07 11:15:08'),
(222, 111, 52, 0, 100, 74, '84', '12', 1, '2024-05-07 11:15:08', '2024-05-07 11:15:08'),
(223, 111, 53, 0, 100, 74, '84', '12', 1, '2024-05-07 11:15:09', '2024-05-07 11:15:09'),
(224, 111, 55, 0, 100, 74, '84', '10', 1, '2024-05-07 11:15:09', '2024-05-07 11:15:09'),
(225, 112, 49, 0, 10, 74, '0', '0', 1, '2024-05-07 12:55:09', '2024-05-07 12:55:09'),
(226, 112, 50, 0, 10, 74, '0', '0', 1, '2024-05-07 12:55:09', '2024-05-07 12:55:09'),
(227, 112, 51, 0, 10, 74, '0', '0', 1, '2024-05-07 12:55:09', '2024-05-07 12:55:09'),
(228, 112, 52, 0, 10, 74, '0', '0', 1, '2024-05-07 12:55:09', '2024-05-07 12:55:09'),
(229, 112, 53, 0, 10, 74, '0', '0', 1, '2024-05-07 12:55:09', '2024-05-07 12:55:09'),
(230, 112, 54, 0, 10, 74, '0', '0', 1, '2024-05-07 12:55:09', '2024-05-07 12:55:09'),
(231, 112, 55, 0, 10, 74, '0', '0', 1, '2024-05-07 12:55:09', '2024-05-07 12:55:09'),
(232, 112, 56, 0, 10, 74, '0', '0', 1, '2024-05-07 12:55:09', '2024-05-07 12:55:09'),
(233, 113, 49, 0, 20, 74, '0', '0', 1, '2024-05-07 13:43:20', '2024-05-07 13:43:20'),
(234, 113, 50, 0, 20, 74, '0', '0', 1, '2024-05-07 13:43:20', '2024-05-07 13:43:20'),
(235, 113, 51, 0, 20, 74, '0', '0', 1, '2024-05-07 13:43:20', '2024-05-07 13:43:20'),
(236, 113, 52, 0, 20, 74, '0', '0', 1, '2024-05-07 13:43:20', '2024-05-07 13:43:20'),
(237, 113, 53, 0, 20, 74, '0', '0', 1, '2024-05-07 13:43:20', '2024-05-07 13:43:20'),
(238, 113, 54, 0, 20, 74, '0', '0', 1, '2024-05-07 13:43:20', '2024-05-07 13:43:20'),
(239, 113, 55, 0, 20, 74, '0', '0', 1, '2024-05-07 13:43:20', '2024-05-07 13:43:20'),
(240, 113, 56, 0, 20, 74, '0', '0', 1, '2024-05-07 13:43:20', '2024-05-07 13:43:20'),
(241, 113, 0, 15, 1, 0, '0', '0', 1, '2024-05-07 13:43:20', '2024-05-07 13:43:20'),
(242, 114, 54, 0, 3, 74, '0', '0', 1, '2024-05-07 13:49:24', '2024-05-07 13:49:24'),
(251, 117, 49, 0, 20, 74, '0', '0', 1, '2024-05-08 14:04:20', '2024-05-08 14:04:20'),
(252, 118, 49, 0, 20, 74, '0', '0', 1, '2024-05-08 14:06:40', '2024-05-08 14:06:40'),
(253, 119, 50, 0, 12, 74, '0', '0', 1, '2024-05-08 14:09:36', '2024-05-08 14:09:36'),
(254, 117, 50, 0, 10, 74, '0', '0', 1, '2024-05-08 14:25:26', '2024-05-08 14:25:26'),
(256, 120, 92, 11, 20, 0, '0', '0', 1, '2024-05-09 04:33:02', '2024-05-09 04:33:02'),
(257, 120, 92, 12, 30, 0, '0', '0', 1, '2024-05-09 04:33:02', '2024-05-09 04:33:02'),
(258, 120, 92, 13, 40, 0, '0', '0', 1, '2024-05-09 04:33:02', '2024-05-09 04:33:02'),
(259, 120, 92, 14, 40, 0, '0', '0', 1, '2024-05-09 04:33:02', '2024-05-09 04:33:02'),
(260, 120, 93, 1, 30, 0, '0', '0', 1, '2024-05-09 04:33:02', '2024-05-09 04:33:02'),
(261, 120, 94, 11, 20, 0, '0', '0', 1, '2024-05-09 04:33:02', '2024-05-09 04:33:02'),
(262, 120, 94, 12, 22, 0, '0', '0', 1, '2024-05-09 04:33:02', '2024-05-09 04:33:02'),
(263, 120, 94, 13, 20, 0, '0', '0', 1, '2024-05-09 04:33:02', '2024-05-09 04:33:02'),
(264, 120, 94, 14, 20, 0, '0', '0', 1, '2024-05-09 04:33:02', '2024-05-09 04:33:02'),
(265, 121, 92, 11, 20, 0, '0', '0', 1, '2024-05-09 08:53:31', '2024-05-09 08:53:31'),
(266, 121, 93, 1, 60, 0, '0', '0', 1, '2024-05-09 08:58:19', '2024-05-09 08:58:19'),
(267, 122, 56, 0, 10, 73, '80', '12', 1, '2024-05-09 13:08:17', '2024-05-09 13:08:17'),
(268, 123, 49, 0, 10, 74, '0', '0', 1, '2024-05-09 13:52:55', '2024-05-09 13:52:55'),
(269, 123, 52, 0, 6, 74, '0', '0', 1, '2024-05-09 13:52:55', '2024-05-09 13:52:55'),
(270, 124, 54, 0, 2, 72, '0', '0', 1, '2024-05-10 08:28:09', '2024-05-10 08:28:09'),
(271, 124, 54, 0, 12, 74, '0', '0', 1, '2024-05-10 08:28:09', '2024-05-10 08:28:09'),
(272, 125, 54, 0, 100, 73, '0', '0', 1, '2024-05-10 08:29:11', '2024-05-10 08:29:11'),
(273, 121, 92, 10, 1, 0, '0', '0', 1, '2024-05-11 05:31:02', '2024-05-11 05:31:02'),
(274, 126, 50, 11, 40, 0, '0', '0', 1, '2024-05-14 08:07:42', '2024-05-14 08:07:42'),
(275, 127, 92, 12, 18, 0, '0', '0', 1, '2024-05-14 08:10:38', '2024-05-14 08:10:38'),
(282, 135, 49, 12, 7, 72, '0', '0', 1, '2024-05-25 06:48:13', '2024-05-25 06:48:13'),
(283, 136, 49, 0, 1, 73, '78|79', '12|12', 1, '2024-05-25 06:54:07', '2024-05-25 06:54:07'),
(284, 137, 0, 24, 2, 0, '0', '0', 1, '2024-05-25 07:10:24', '2024-05-25 07:10:24'),
(287, 139, 51, 0, 5, 74, '0', '0', 1, '2024-05-25 12:49:28', '2024-05-25 12:49:28'),
(288, 139, 52, 0, 5, 74, '0', '0', 1, '2024-05-25 12:49:28', '2024-05-25 12:49:28'),
(289, 140, 51, 0, 5, 74, '0', '0', 1, '2024-05-25 13:22:48', '2024-05-25 13:22:48'),
(290, 140, 52, 0, 5, 74, '0', '0', 1, '2024-05-25 13:22:48', '2024-05-25 13:22:48'),
(291, 141, 51, 0, 5, 74, '0', '0', 1, '2024-05-25 13:26:16', '2024-05-25 13:26:16'),
(292, 141, 52, 0, 5, 74, '0', '0', 1, '2024-05-25 13:26:16', '2024-05-25 13:26:16'),
(297, 0, 0, 20, 100, 0, '0', '0', 1, '2024-05-27 12:58:32', '2024-05-27 12:58:32'),
(300, 0, 0, 16, 2, 0, '0', '0', 1, '2024-05-27 14:24:37', '2024-05-27 14:24:37'),
(301, 0, 0, 2, 100, 0, '0', '0', 1, '2024-05-27 14:24:37', '2024-05-27 14:24:37'),
(302, 143, 50, 11, 10, 0, '0', '0', 1, '2024-05-28 06:54:30', '2024-05-28 06:54:30'),
(304, 145, 92, 0, 1, 72, '78', '12', 1, '2024-05-28 07:18:20', '2024-05-28 07:18:20'),
(305, 146, 92, 0, 9, 74, '78', '12', 1, '2024-05-28 07:18:43', '2024-05-28 07:18:43'),
(306, 147, 54, 13, 12, 0, '0', '0', 1, '2024-05-28 10:56:04', '2024-05-28 10:56:04'),
(307, 147, 54, 0, 1, 72, '0', '0', 1, '2024-05-28 10:56:04', '2024-05-28 10:56:04'),
(309, 150, 92, 11, 40, 0, '0', '0', 1, '2024-05-29 04:53:18', '2024-05-29 04:53:18'),
(310, 150, 92, 0, 2, 74, '0', '0', 1, '2024-05-29 04:53:18', '2024-05-29 04:53:18'),
(311, 0, 0, 1, 200, 0, '0', '0', 1, '2024-05-29 04:57:20', '2024-05-29 04:57:20'),
(312, 0, 0, 24, 12, 0, '0', '0', 1, '2024-05-29 07:08:39', '2024-05-29 07:08:39'),
(318, 0, 0, 25, 500, 0, '0', '0', 1, '2024-05-30 02:20:48', '2024-05-30 02:20:48'),
(320, 0, 0, 26, 800, 0, '0', '0', 1, '2024-05-30 02:38:21', '2024-05-30 02:38:21'),
(322, 139, 0, 15, 2, 0, '0', '0', 1, '2024-05-30 05:00:26', '2024-05-30 05:00:26'),
(323, 0, 0, 24, 1, 0, '0', '0', 1, '2024-05-30 05:23:29', '2024-05-30 05:23:29'),
(324, 0, 0, 28, 12, 0, '0', '0', 1, '2024-05-30 05:23:29', '2024-05-30 05:23:29'),
(325, 0, 0, 28, 1, 0, '0', '0', 1, '2024-05-30 05:26:39', '2024-05-30 05:26:39'),
(326, 0, 0, 1, 90, 0, '0', '0', 1, '2024-05-30 05:28:10', '2024-05-30 05:28:10'),
(327, 10012, 54, 7, 20, 0, '0', '0', 1, '2024-05-30 12:03:26', '2024-05-30 12:03:26'),
(328, 10012, 54, 14, 20, 0, '0', '0', 1, '2024-05-30 12:03:26', '2024-05-30 12:03:26'),
(329, 10012, 54, 12, 20, 0, '0', '0', 1, '2024-05-30 12:03:26', '2024-05-30 12:03:26'),
(330, 10012, 54, 13, 20, 0, '0', '0', 1, '2024-05-30 12:03:26', '2024-05-30 12:03:26'),
(331, 10012, 54, 0, 10, 73, '0', '0', 1, '2024-05-30 12:03:26', '2024-05-30 12:03:26'),
(332, 10013, 52, 11, 12, 0, '0', '0', 1, '2024-05-30 12:08:48', '2024-05-30 12:08:48'),
(334, 10013, 52, 13, 3, 0, '0', '0', 1, '2024-05-30 12:12:06', '2024-05-30 12:12:06'),
(335, 10013, 52, 12, 9, 0, '0', '0', 1, '2024-05-30 12:12:06', '2024-05-30 12:12:06'),
(336, 10014, 68, 6, 100, 0, '0', '0', 1, '2024-06-03 13:33:27', '2024-06-03 13:33:27'),
(337, 10015, 68, 0, 25, 72, '78', '12', 1, '2024-06-03 13:34:02', '2024-06-03 13:34:02'),
(338, 10014, 68, 0, 5, 72, '0', '0', 1, '2024-06-03 13:34:22', '2024-06-03 13:34:22'),
(339, 10016, 68, 5, 20, 0, '0', '0', 1, '2024-06-03 13:35:51', '2024-06-03 13:35:51'),
(340, 10016, 68, 0, 10, 72, '0', '0', 1, '2024-06-03 13:35:51', '2024-06-03 13:35:51'),
(341, 10017, 52, 11, 2, 0, '0', '0', 1, '2024-06-05 10:44:21', '2024-06-05 10:44:21'),
(342, 10018, 52, 0, 1, 74, '79', '12', 1, '2024-06-05 10:44:43', '2024-06-05 10:44:43'),
(343, 0, 0, 27, 78.9, 0, '0', '0', 1, '2024-08-17 05:59:23', '2024-08-17 05:59:23'),
(344, 0, 0, 27, 12.12121212121212, 0, '0', '0', 1, '2024-08-17 06:07:18', '2024-08-17 06:07:18'),
(345, 1, 0, 30, 0, 0, '0', '0', 1, '2024-08-17 06:30:12', '2024-08-17 06:30:12'),
(346, 1, 0, 31, 200, 0, '0', '0', 1, '2024-08-17 06:30:33', '2024-08-17 06:30:33'),
(347, 1, 0, 29, 100, 0, '0', '0', 1, '2024-08-17 06:32:40', '2024-08-17 06:33:31'),
(348, 10019, 49, 13, 100, 0, '0', '0', 1, '2024-08-28 06:35:02', '2024-08-28 06:35:02'),
(349, 10020, 68, 4, 10, 0, '0', '0', 1, '2024-08-28 12:40:12', '2024-08-28 12:40:12'),
(350, 10021, 68, 0, 1, 72, '78', '12', 1, '2024-08-28 12:57:46', '2024-08-28 12:57:46'),
(351, 1, 0, 10, 75, 0, '0', '0', 1, '2024-08-28 13:30:56', '2024-08-28 13:35:09'),
(353, 10022, 54, 12, 10, 0, '0', '0', 1, '2024-08-29 13:53:13', '2024-08-29 13:53:13'),
(354, 0, 0, 27, 10, 0, '0', '0', 1, '2024-09-02 01:57:20', '2024-09-02 01:57:20'),
(355, 10023, 49, 14, 12, 0, '0', '0', 1, '2024-09-05 02:49:19', '2024-09-05 02:49:19'),
(356, 10024, 52, 14, 246, 0, '0', '0', 1, '2025-01-01 04:23:11', '2025-01-01 04:23:11'),
(357, 10024, 52, 13, 24, 0, '0', '0', 1, '2025-01-01 04:23:11', '2025-01-01 04:23:11'),
(358, 10025, 49, 0, 10, 74, '0', '0', 1, '2025-01-16 05:32:39', '2025-01-16 05:32:39'),
(359, 10025, 50, 0, 10, 74, '0', '0', 1, '2025-01-16 05:32:39', '2025-01-16 05:32:39'),
(360, 10025, 51, 0, 10, 74, '0', '0', 1, '2025-01-16 05:32:39', '2025-01-16 05:32:39'),
(361, 10025, 52, 0, 10, 74, '0', '0', 1, '2025-01-16 05:32:39', '2025-01-16 05:32:39'),
(362, 10026, 103, 31, 20, 0, '0', '0', 1, '2025-04-19 03:41:04', '2025-04-19 03:41:04'),
(363, 10027, 103, 0, 100, 72, '0', '0', 1, '2025-04-19 03:41:46', '2025-04-19 03:41:46'),
(364, 10027, 103, 0, 90, 73, '0', '0', 1, '2025-04-19 03:41:46', '2025-04-19 03:41:46'),
(365, 10027, 103, 0, 80, 74, '0', '0', 1, '2025-04-19 03:41:46', '2025-04-19 03:41:46'),
(366, 10027, 103, 0, 70, 105, '0', '0', 1, '2025-04-19 03:41:46', '2025-04-19 03:41:46'),
(367, 10028, 106, 31, 2, 0, '0', '0', 1, '2025-04-27 04:35:18', '2025-04-27 04:35:18'),
(368, 10028, 105, 31, 4, 0, '0', '0', 1, '2025-04-27 04:35:18', '2025-04-27 04:35:18'),
(369, 10028, 51, 12, 10, 0, '0', '0', 1, '2025-04-27 04:35:18', '2025-04-27 04:35:18'),
(370, 10029, 51, 0, 10, 72, '78', '12', 1, '2025-04-27 04:36:32', '2025-04-27 04:36:32'),
(371, 10029, 106, 0, 30, 72, '0', '0', 1, '2025-04-27 04:36:32', '2025-04-27 04:36:32'),
(372, 10029, 105, 0, 40, 72, '0', '0', 1, '2025-04-27 04:36:32', '2025-04-27 04:36:32'),
(373, 10030, 106, 0, 10, 72, '0', '0', 1, '2025-04-27 05:22:06', '2025-04-27 05:22:06'),
(374, 10031, 103, 29, 12, 0, '0', '0', 1, '2025-05-12 13:28:25', '2025-05-12 13:28:25'),
(375, 10031, 103, 31, 12, 0, '0', '0', 1, '2025-05-12 13:28:25', '2025-05-12 13:28:25'),
(376, 10033, 103, 29, 12, 0, '0', '0', 1, '2025-05-12 13:47:11', '2025-05-12 13:47:11'),
(377, 10033, 103, 31, 12, 0, '0', '0', 1, '2025-05-12 13:47:11', '2025-05-12 13:47:11');

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `transaction_id` bigint(20) UNSIGNED NOT NULL,
  `transaction_to` varchar(255) NOT NULL COMMENT 'Vendor, Employee, Expense',
  `transaction_type` varchar(255) NOT NULL COMMENT 'Salary, Advance etc',
  `bank_id` bigint(20) UNSIGNED NOT NULL COMMENT 'Our Bank',
  `order_id` bigint(20) UNSIGNED DEFAULT NULL COMMENT 'Purchase / Order / Delivery',
  `payee_id` bigint(20) UNSIGNED DEFAULT NULL COMMENT 'Vendor, Employee, Customers, Expense Head',
  `payee_bank_id` bigint(20) UNSIGNED DEFAULT NULL,
  `debit` bigint(20) UNSIGNED DEFAULT NULL COMMENT 'Cash Out',
  `credit` bigint(20) UNSIGNED DEFAULT NULL COMMENT 'Cash In',
  `transaction_date` date NOT NULL,
  `description` longtext DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`transaction_id`, `transaction_to`, `transaction_type`, `bank_id`, `order_id`, `payee_id`, `payee_bank_id`, `debit`, `credit`, `transaction_date`, `description`, `created_by`, `created_at`, `updated_at`) VALUES
(13, 'expense', 'expense', 0, NULL, 42, 0, 500, NULL, '2024-04-27', '<p>Electrician Charges&nbsp;</p>', 1, '2024-04-27 03:04:24', '2024-04-27 03:04:24'),
(15, 'employee', 'salaryAdvance', 0, NULL, 8, 0, 6000, NULL, '2024-04-27', '<p><br></p>', 1, '2024-04-27 03:18:30', '2024-04-27 03:20:04'),
(16, 'employee', 'receiveAdvance', 0, NULL, 7, 0, NULL, 5000, '2024-04-27', '<p>123</p>', 1, '2024-04-27 03:30:58', '2024-04-27 03:30:58'),
(17, 'vendor', 'wages', 0, NULL, 6, 0, 200, 0, '2024-04-27', NULL, 1, '2024-04-27 04:24:11', '2024-05-28 05:08:50'),
(18, 'vendor', 'advance', 0, NULL, 6, 0, 50000, NULL, '2024-04-27', NULL, 1, '2024-04-27 04:26:02', '2024-04-27 04:26:02'),
(19, 'vendor', 'receiveAdvance', 0, NULL, 6, 0, NULL, 20000, '2024-04-27', NULL, 1, '2024-04-27 04:26:12', '2024-04-27 04:26:12'),
(20, 'vendor', 'payment', 0, 25, 7, 0, 22000, NULL, '2024-04-28', '<p>&nbsp;&nbsp;&nbsp;&nbsp;Twenty Two Thousand Paid</p>', 1, '2024-04-28 06:18:22', '2024-04-29 07:45:49'),
(21, 'vendor', 'openingBalance', 0, NULL, 11, 0, NULL, 10000, '2024-04-29', NULL, 1, '2024-04-29 10:10:04', '2024-04-29 10:22:14'),
(22, 'vendor', 'openingBalance', 0, NULL, 7, 0, NULL, 0, '2024-04-29', NULL, 1, '2024-04-29 10:22:52', '2024-04-29 10:22:52'),
(23, 'vendor', 'openingBalance', 0, NULL, 6, 0, 1000, NULL, '2024-04-29', NULL, 1, '2024-04-29 10:23:56', '2024-04-29 10:23:56'),
(24, 'vendor', 'openingBalance', 0, NULL, 5, 0, 15000, NULL, '2024-04-29', NULL, 1, '2024-04-29 10:24:10', '2024-04-29 10:24:10'),
(25, 'vendor', 'openingBalance', 0, NULL, 4, 0, NULL, 10000, '2024-04-29', NULL, 1, '2024-04-29 10:24:20', '2024-04-29 10:24:20'),
(26, 'vendor', 'openingBalance', 0, NULL, 3, 0, NULL, 12000, '2024-04-29', NULL, 1, '2024-04-29 10:24:32', '2024-04-29 10:24:32'),
(27, 'vendor', 'openingBalance', 0, NULL, 1, 0, 5000, NULL, '2024-04-29', NULL, 1, '2024-04-29 10:24:42', '2024-04-29 10:24:42'),
(28, 'employee', 'openingBalance', 0, NULL, 11, 0, 10000, NULL, '2024-04-29', NULL, 1, '2024-04-29 10:53:05', '2024-04-29 10:55:08'),
(29, 'customer', 'openingBalance', 0, NULL, 6, 0, NULL, 50000, '2024-04-29', NULL, 1, '2024-04-29 10:56:20', '2024-04-29 10:56:20'),
(30, 'employee', 'openingBalance', 0, NULL, 12, 0, 0, NULL, '2024-05-01', NULL, 1, '2024-04-30 07:51:22', '2024-05-01 02:56:17'),
(31, 'employee', 'openingBalance', 0, NULL, 5, 0, 0, NULL, '2024-04-30', NULL, 1, '2024-04-30 07:55:57', '2024-04-30 07:55:57'),
(32, 'vendor', 'payment', 1, 30, 11, 0, 150000, NULL, '2024-05-06', '<p>1.5 Lac for container</p>', 1, '2024-05-06 03:24:37', '2024-05-06 03:24:37'),
(35, 'expense', 'deliveryExpense', 0, 5, 38, 0, 20000, NULL, '2024-05-07', '20000 Desc', 1, '2024-05-07 04:26:42', '2024-05-08 14:20:14'),
(37, 'expense', 'deliveryExpense', 0, 10, 38, 0, 12, NULL, '2024-05-09', '12 Desc', 1, '2024-05-08 14:06:40', '2024-05-08 14:06:40'),
(38, 'expense', 'deliveryExpense', 0, 11, 42, 0, 123, NULL, '2024-05-09', '123 Desc', 1, '2024-05-08 14:09:36', '2024-05-08 14:09:36'),
(40, 'expense', 'deliveryExpense', 0, 5, 42, 0, 1212, NULL, '2024-05-07', '1212 Desc', 1, '2024-05-08 14:22:04', '2024-05-08 14:22:04'),
(42, 'brs', 'brs', 0, NULL, 0, 0, NULL, 76859, '2024-05-10', '<p>Increasing Cash Balance&nbsp;76859</p>', 1, '2024-05-09 14:18:38', '2024-05-14 00:51:30'),
(43, 'brs', 'brs', 0, NULL, 0, 0, 6000, NULL, '2024-05-10', '<p>Reason</p>', 1, '2024-05-10 08:15:29', '2024-05-14 03:54:14'),
(44, 'vendor', 'payment', 0, 31, 11, 0, 20000, NULL, '2024-05-10', '<p><br></p>', 1, '2024-05-10 13:11:11', '2024-05-10 13:15:32'),
(47, 'vendor', 'openingBalance', 0, NULL, 12, 0, NULL, 0, '2024-05-22', NULL, 1, '2024-05-22 14:30:32', '2024-05-22 14:30:32'),
(48, 'brs', 'brs', 9, NULL, NULL, 0, NULL, 2580001, '2024-05-23', NULL, 1, '2024-05-23 13:19:09', '2025-02-16 04:39:43'),
(49, 'vendor', 'openingBalance', 0, NULL, 13, 0, NULL, 0, '2024-05-23', NULL, 1, '2024-05-23 14:16:32', '2024-05-23 14:16:32'),
(50, 'vendor', 'openingBalance', 0, NULL, 14, 0, NULL, 0, '2024-05-23', NULL, 1, '2024-05-23 14:36:14', '2024-05-23 14:36:14'),
(51, 'expense', 'deliveryExpense', 0, 14, 42, 0, 10, NULL, '2024-05-25', 'Detail', 1, '2024-05-25 12:49:28', '2024-05-25 12:49:28'),
(52, 'expense', 'deliveryExpense', 0, 15, 42, 0, 100, NULL, '2024-05-25', '100 Rs', 1, '2024-05-25 13:22:48', '2024-05-25 13:22:48'),
(53, 'expense', 'deliveryExpense', 0, 16, 42, 0, 12, NULL, '2024-05-25', '12', 1, '2024-05-25 13:26:16', '2024-05-25 13:26:16'),
(54, 'employee', 'wages', 0, NULL, 12, 0, 10, NULL, '2024-05-01', '<p>&nbsp;&nbsp;&nbsp;&nbsp;<br></p>', 1, '2024-05-26 13:33:41', '2024-05-26 13:33:41'),
(55, 'employee', 'wages', 0, NULL, 12, 0, 10, NULL, '2024-05-04', '<p>&nbsp;&nbsp;&nbsp;&nbsp;<br></p>', 1, '2024-05-26 13:33:44', '2024-05-26 13:33:44'),
(56, 'employee', 'wages', 0, NULL, 12, 0, 10, NULL, '2024-05-12', '<p>&nbsp;&nbsp;&nbsp;&nbsp;<br></p>', 1, '2024-05-26 13:33:49', '2024-05-26 13:33:49'),
(57, 'employee', 'wages', 0, NULL, 12, 0, 10, NULL, '2024-05-22', '<p>&nbsp;&nbsp;&nbsp;&nbsp;<br></p>', 1, '2024-05-26 13:33:53', '2024-05-26 13:33:53'),
(58, 'employee', 'wages', 0, NULL, 12, 0, 10, NULL, '2024-05-25', '<p>&nbsp;&nbsp;&nbsp;&nbsp;<br></p>', 1, '2024-05-26 13:34:01', '2024-05-26 13:34:01'),
(59, 'employee', 'advance', 0, NULL, 12, 0, 5000, NULL, '2024-05-26', NULL, 1, '2024-05-26 13:34:53', '2024-05-26 13:34:53'),
(60, 'employee', 'salaryAdvance', 0, NULL, 12, 0, 1000, NULL, '2024-05-26', NULL, 1, '2024-05-26 13:35:05', '2024-05-26 13:35:05'),
(61, 'employee', 'receiveAdvance', 0, NULL, 12, 0, NULL, 2000, '2024-05-27', NULL, 1, '2024-05-26 13:35:21', '2024-05-26 13:35:21'),
(62, 'employee', 'salary', 0, NULL, 12, 0, 1200, NULL, '2024-05-27', NULL, 1, '2024-05-26 13:35:37', '2024-05-26 13:35:37'),
(65, 'vendor', 'wages', 9, NULL, 11, 0, 10, 0, '2024-05-28', NULL, 1, '2024-05-28 03:33:04', '2024-05-28 03:33:04'),
(66, 'brs', 'brs', 0, NULL, NULL, 0, NULL, 100000, '2024-05-28', NULL, 1, '2024-05-28 03:40:06', '2024-05-28 03:40:06'),
(67, 'brs', 'brs', 1, NULL, NULL, 0, NULL, 500000, '2024-05-28', NULL, 1, '2024-05-28 03:41:55', '2024-05-28 03:41:55'),
(68, 'brs', 'brs', 10, NULL, NULL, 0, NULL, 250000, '2024-05-28', NULL, 1, '2024-05-28 03:42:13', '2024-05-28 03:42:13'),
(69, 'employee', 'wages', 0, NULL, 12, 0, 104, 0, '2024-05-28', '<p>Desc</p>', 1, '2024-05-28 05:06:27', '2024-05-28 05:06:27'),
(70, 'employee', 'receiveAdvance', 0, NULL, 12, 0, NULL, 1000, '2024-05-28', NULL, 1, '2024-05-28 05:07:03', '2024-05-28 05:07:03'),
(71, 'vendor', 'receiveAdvance', 0, NULL, 6, 0, NULL, 32000, '2024-05-28', NULL, 1, '2024-05-28 05:11:03', '2025-01-07 01:58:45'),
(72, 'vendor', 'openingBalance', 0, NULL, 15, 0, NULL, 0, '2024-05-29', NULL, 1, '2024-05-29 06:52:49', '2024-05-29 06:52:49'),
(73, 'employee', 'openingBalance', 0, NULL, 13, 0, 0, NULL, '2025-04-12', NULL, 1, '2024-05-31 13:39:41', '2025-04-12 01:45:48'),
(74, 'employee', 'salaryAdvance', 0, NULL, 1, 0, 1200, 0, '2024-03-07', '<p>&nbsp;&nbsp;&nbsp;&nbsp;<br></p>', 1, '2024-06-03 11:48:27', '2024-06-03 11:48:27'),
(75, 'employee', 'salaryAdvance', 0, NULL, 1, 0, 20000, 0, '2024-05-08', NULL, 1, '2024-06-03 11:48:59', '2024-06-03 11:48:59'),
(76, 'employee', 'salaryAdvance', 0, NULL, 7, 0, 5000, 0, '2024-03-06', NULL, 1, '2024-06-03 11:49:18', '2024-06-03 11:49:18'),
(77, 'employee', 'salaryAdvance', 0, NULL, 7, 0, 4000, 0, '2024-04-25', NULL, 1, '2024-06-03 11:49:28', '2024-06-03 11:49:28'),
(78, 'employee', 'salaryAdvance', 0, NULL, 8, 0, 15000, 0, '2024-02-28', NULL, 1, '2024-06-03 11:49:42', '2024-06-03 11:49:42'),
(79, 'employee', 'salaryAdvance', 0, NULL, 8, 0, 3000, 0, '2024-04-25', NULL, 1, '2024-06-03 11:49:53', '2024-06-03 11:49:53'),
(80, 'vendor', 'advance', 0, NULL, 1, 0, 5000, 0, '2024-06-05', NULL, 1, '2024-06-05 10:43:28', '2024-06-05 10:43:28'),
(81, 'vendor', 'receiveAdvance', 0, NULL, 1, 0, NULL, 8000, '2024-06-05', NULL, 1, '2024-06-05 10:45:27', '2024-06-05 10:45:27'),
(82, 'vendor', 'openingBalance', 0, NULL, 16, 0, 1000, NULL, '2024-08-07', NULL, 1, '2024-08-07 10:54:44', '2024-08-07 10:54:44'),
(83, 'customer', 'orderPayment', 10, 19, 6, 0, 0, 2000, '2025-01-01', NULL, 1, '2025-01-01 13:09:59', '2025-01-01 13:09:59'),
(84, 'vendor', 'receiveAdvance', 1, 50, 7, 0, NULL, 8005, '2025-01-01', NULL, 1, '2025-01-01 13:20:49', '2025-01-07 01:59:16'),
(86, 'contractor', 'wages', 1, NULL, 15, 0, 12000, 0, '2025-01-11', NULL, 1, '2025-01-11 07:07:46', '2025-01-11 07:07:46'),
(87, 'brs', 'brs', 0, NULL, NULL, 0, 124, NULL, '2025-01-11', NULL, 1, '2025-01-11 08:36:26', '2025-01-11 08:36:26'),
(88, 'expense', 'deliveryExpense', 0, 17, 38, 0, 5000, NULL, '2025-01-16', 'xyz', 1, '2025-01-16 05:32:39', '2025-01-16 05:32:39'),
(89, 'customer', 'orderPayment', 1, 24, 6, 7, 0, 50000, '2025-01-16', NULL, 1, '2025-01-16 06:02:05', '2025-01-16 06:02:05'),
(90, 'admin', 'openingBalance', 18, NULL, NULL, 0, NULL, 15000, '2025-02-16', NULL, 1, '2025-02-16 04:45:10', '2025-02-16 05:09:09'),
(91, 'admin', 'openingBalance', 19, NULL, NULL, 0, NULL, 12000, '2025-02-16', NULL, 1, '2025-02-16 04:47:07', '2025-02-16 05:09:03'),
(92, 'vendor', 'payment', 0, 66, 12, 0, 100, 0, '2025-04-07', NULL, 1, '2025-04-06 14:27:38', '2025-04-06 14:27:38'),
(93, 'employee', 'openingBalance', 0, NULL, 1, 0, 0, NULL, '2025-04-12', NULL, 1, '2025-04-12 03:05:45', '2025-04-12 03:05:45');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `role_id` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `role_id`, `email`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'Admin', '1', 'admin@gmail.com', NULL, '$2y$12$IsLNGwhB1PNBy6Lkxrtkx.ka9hfw2hEf/.qGrjPlN8EFnxkGIrcgW', 'fEoALirAtBlNvxMU916Uv3aqedz2LbSbOd5aHkZOc1d1qhPDIfgJ2M99f3cU', NULL, NULL),
(2, 'Manager', '2', 'manager@gmail.com', NULL, '$2y$12$iB5imGY9l.dbfpEYnZHGO.wl3fO/.zdj96rIjR/vJiYh7TVi33I6C', '8AgBGaBc9V2hc8R218743xvOz2lCm0IF20xHMHaWVUdScpCj97FhrA5eQwzv', '2024-06-04 01:49:44', '2025-01-12 01:51:16'),
(3, 'Accountant', '3', 'accountant@gmail.com', NULL, '$2y$12$eKMHyfJEB/21jluVhBl.ZuF2YY/h9AmxBDKyIlKO8YMiNFUM0Tfs2', NULL, '2025-01-12 01:46:41', '2025-01-12 01:59:10'),
(5, 'test', '3', 'test@admin.com', NULL, '$2y$12$k6VIr564cKKZjQ/OHV7HV.l6WuzeXvsa/9.XQr0rLzaSWxZaBsHqW', NULL, '2025-01-12 03:22:22', '2025-01-12 04:03:36');

-- --------------------------------------------------------

--
-- Table structure for table `vendors`
--

CREATE TABLE `vendors` (
  `vendor_id` bigint(20) UNSIGNED NOT NULL,
  `vendor_type_id` bigint(20) UNSIGNED NOT NULL COMMENT 'HeadID',
  `vendor_no` varchar(255) NOT NULL,
  `vendor_type` bigint(20) UNSIGNED NOT NULL COMMENT '\r\nWorker / Not',
  `material_id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `fname` varchar(255) NOT NULL,
  `cperson` varchar(255) DEFAULT NULL,
  `phone1` varchar(255) NOT NULL,
  `phone2` varchar(255) DEFAULT NULL,
  `city_id` bigint(20) UNSIGNED NOT NULL COMMENT 'HeadID',
  `address` longtext NOT NULL,
  `description` longtext DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vendors`
--

INSERT INTO `vendors` (`vendor_id`, `vendor_type_id`, `vendor_no`, `vendor_type`, `material_id`, `name`, `fname`, `cperson`, `phone1`, `phone2`, `city_id`, `address`, `description`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 69, 'C0001', 1, '0', 'Sarfraz', 'Sarfraz Salman', 'Ali', '03001122334', '03001122334', 44, 'Address of vendor', '<p>New Desc</p>', 1, '2024-02-20 07:35:03', '2024-04-26 11:12:19'),
(3, 57, 'V0001', 0, '0', 'Huzaifa', 'Huzaifa Shafeeq', 'Ali', '03001122334', '03001122334', 46, 'Address of Leather Vendor', '<p><span style=\"color: rgb(0, 0, 0);\">Lorem, ipsum dolor sit amet consectetur adipisicing elit. Obcaecati minima corporis quae, odit officiis facere labore autem beatae! Reprehenderit quasi corrupti ullam enim quia vitae suscipit, asperiores aut delectus possimus!</span><br></p>', 1, '2024-02-25 14:05:38', '2024-04-26 11:11:45'),
(4, 67, 'V0002', 0, '0', 'Rouf', 'Abdul Rouf', 'Ali', '03001122334', '03001122334', 46, 'Address of Box Vendor', '<p><span style=\"color: rgb(0, 0, 0);\">Lorem, ipsum dolor sit amet consectetur adipisicing elit. Obcaecati minima corporis quae, odit officiis facere labore autem beatae! Reprehenderit quasi corrupti ullam enim quia vitae suscipit, asperiores aut delectus possimus!</span><br></p>', 1, '2024-02-25 14:09:18', '2024-04-26 11:11:22'),
(5, 68, 'V0003', 0, '11', 'Arslan', 'Arslan Cheema', 'Ali', '03001122334', '03001122334', 43, 'Address of Liquid Vendor', '<p><span style=\"color: rgb(0, 0, 0);\">Lorem, ipsum dolor sit amet consectetur adipisicing elit. Obcaecati minima corporis quae, odit officiis facere labore autem beatae! Reprehenderit quasi corrupti ullam enim quia vitae suscipit, asperiores aut delectus possimus!</span><br></p>', 1, '2024-02-25 14:10:00', '2024-04-26 11:10:13'),
(6, 60, 'C0002', 1, '0', 'Shehzad', 'Shehzad Ahmed Stitcher', 'Ali', '03001122334', '03001122334', 43, 'Address of Vendor V24005', '<p>Desc</p>', 1, '2024-03-28 13:37:11', '2024-04-26 11:09:18'),
(7, 69, 'V0004', 0, '14|13|12|11|10|9|8|7|3', 'Abdulrehman', 'Abdulrehman Tahir', 'Ali', '03001122334', '03001122334', 45, 'Addresss', '<p><br></p>', 1, '2024-04-25 06:24:36', '2024-04-26 11:07:45'),
(11, 69, 'V0005', 0, '0', 'Umer', 'Umer Malik', 'Ali', '03001122334', '03001122334', 44, 'Address of Vendor Umer Malik', NULL, 1, '2024-04-29 10:10:04', '2024-04-29 10:10:04'),
(12, 57, 'V0006', 0, '0', 'Check 123', 'Check', 'Ali', '123123213', '1232132131', 43, 'Sialkot', '<p>&nbsp;&nbsp;&nbsp;&nbsp;<br></p>', 1, '2024-05-22 14:30:32', '2024-05-22 14:30:49'),
(13, 0, 'C0003', 1, '0', 'Ansab', 'Ansab Sheikh', NULL, '03001122334', '03001122334', 70, 'Address', NULL, 1, '2024-05-23 14:16:32', '2024-05-23 14:39:09'),
(15, 0, 'C0004', 1, '0', 'check', '123', NULL, 'contact', '1010', 70, 'address', '<p>desc</p>', 1, '2024-05-29 06:52:49', '2024-05-29 06:52:49'),
(16, 0, 'C0005', 1, '0', 'Arshad', 'Mehmood', NULL, '03001122334', '03001122334', 43, 'Sialkot', '<p>Desc</p>', 1, '2024-08-07 10:54:44', '2024-08-07 10:54:44');

-- --------------------------------------------------------

--
-- Table structure for table `work_holidays`
--

CREATE TABLE `work_holidays` (
  `work_holiday_id` bigint(20) UNSIGNED NOT NULL,
  `date_from` date NOT NULL,
  `date_to` date NOT NULL,
  `description` longtext DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `work_holidays`
--

INSERT INTO `work_holidays` (`work_holiday_id`, `date_from`, `date_to`, `description`, `created_by`, `created_at`, `updated_at`) VALUES
(1, '2024-06-01', '2024-06-10', 'Eid Holidays', 1, '2024-06-01 08:40:09', '2024-06-01 08:41:15'),
(2, '2024-05-01', '2024-05-10', 'Check Holidays 123', 1, '2024-06-01 09:05:27', '2024-06-03 01:45:11');

-- --------------------------------------------------------

--
-- Table structure for table `work_times`
--

CREATE TABLE `work_times` (
  `work_time_id` bigint(20) UNSIGNED NOT NULL,
  `time_from` time NOT NULL,
  `time_to` time NOT NULL,
  `date_from` date NOT NULL,
  `date_to` date NOT NULL,
  `grace_time` bigint(20) UNSIGNED NOT NULL,
  `description` longtext DEFAULT NULL,
  `created_by` bigint(20) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `work_times`
--

INSERT INTO `work_times` (`work_time_id`, `time_from`, `time_to`, `date_from`, `date_to`, `grace_time`, `description`, `created_by`, `created_at`, `updated_at`) VALUES
(1, '09:30:00', '18:30:00', '2000-01-01', '2024-03-10', 5, 'Detail', 1, '2024-06-01 08:12:41', '2024-06-03 01:15:07'),
(2, '08:00:00', '17:30:00', '2024-03-11', '2024-04-09', 5, 'Ramazan 2024 Timing', 1, '2024-06-03 00:47:12', '2024-06-03 00:47:50'),
(3, '09:30:00', '18:30:00', '2024-04-10', '2025-04-09', 5, NULL, 1, '2024-06-03 03:16:02', '2024-06-03 03:16:02');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_log`
--
ALTER TABLE `activity_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `subject` (`subject_type`,`subject_id`),
  ADD KEY `causer` (`causer_type`,`causer_id`),
  ADD KEY `activity_log_log_name_index` (`log_name`);

--
-- Indexes for table `banks`
--
ALTER TABLE `banks`
  ADD PRIMARY KEY (`bank_id`),
  ADD UNIQUE KEY `account` (`account`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`);

--
-- Indexes for table `company`
--
ALTER TABLE `company`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`customer_id`),
  ADD UNIQUE KEY `customer_no` (`customer_no`),
  ADD KEY `customer_no_3` (`customer_no`);
ALTER TABLE `customers` ADD FULLTEXT KEY `customer_no_2` (`customer_no`);

--
-- Indexes for table `deliveries`
--
ALTER TABLE `deliveries`
  ADD PRIMARY KEY (`delivery_id`);

--
-- Indexes for table `delivery_boxes`
--
ALTER TABLE `delivery_boxes`
  ADD PRIMARY KEY (`dbox_id`);

--
-- Indexes for table `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`employee_id`),
  ADD UNIQUE KEY `employee_no` (`employee_no`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `heads`
--
ALTER TABLE `heads`
  ADD PRIMARY KEY (`head_id`);

--
-- Indexes for table `head_types`
--
ALTER TABLE `head_types`
  ADD PRIMARY KEY (`head_type_id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `igroups`
--
ALTER TABLE `igroups`
  ADD PRIMARY KEY (`igroup_id`);

--
-- Indexes for table `igroup_items`
--
ALTER TABLE `igroup_items`
  ADD PRIMARY KEY (`igroup_item_id`);

--
-- Indexes for table `images`
--
ALTER TABLE `images`
  ADD PRIMARY KEY (`image_id`);

--
-- Indexes for table `machines`
--
ALTER TABLE `machines`
  ADD PRIMARY KEY (`machine_id`);

--
-- Indexes for table `materials`
--
ALTER TABLE `materials`
  ADD PRIMARY KEY (`material_id`),
  ADD UNIQUE KEY `material_no` (`material_no`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `mprocess`
--
ALTER TABLE `mprocess`
  ADD PRIMARY KEY (`mprocess_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`order_item_id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `permission_role`
--
ALTER TABLE `permission_role`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`),
  ADD UNIQUE KEY `article_no` (`article_no`);

--
-- Indexes for table `product_costs`
--
ALTER TABLE `product_costs`
  ADD PRIMARY KEY (`product_cost_id`);

--
-- Indexes for table `product_materials`
--
ALTER TABLE `product_materials`
  ADD PRIMARY KEY (`product_material_id`);

--
-- Indexes for table `product_types`
--
ALTER TABLE `product_types`
  ADD PRIMARY KEY (`product_type_id`);

--
-- Indexes for table `purchases`
--
ALTER TABLE `purchases`
  ADD PRIMARY KEY (`purchase_id`),
  ADD UNIQUE KEY `purchase_no` (`purchase_no`);

--
-- Indexes for table `purchase_items`
--
ALTER TABLE `purchase_items`
  ADD PRIMARY KEY (`purchase_item_id`);

--
-- Indexes for table `receives`
--
ALTER TABLE `receives`
  ADD PRIMARY KEY (`receive_id`),
  ADD UNIQUE KEY `receive_no` (`receive_no`);

--
-- Indexes for table `receive_materials`
--
ALTER TABLE `receive_materials`
  ADD PRIMARY KEY (`receive_material_id`);

--
-- Indexes for table `returns`
--
ALTER TABLE `returns`
  ADD PRIMARY KEY (`return_id`),
  ADD UNIQUE KEY `return_no` (`return_no`);

--
-- Indexes for table `return_materials`
--
ALTER TABLE `return_materials`
  ADD PRIMARY KEY (`return_material_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `salaries`
--
ALTER TABLE `salaries`
  ADD PRIMARY KEY (`salary_id`);

--
-- Indexes for table `stocks`
--
ALTER TABLE `stocks`
  ADD PRIMARY KEY (`stock_id`),
  ADD UNIQUE KEY `issue_no` (`stock_no`);

--
-- Indexes for table `stock_items`
--
ALTER TABLE `stock_items`
  ADD PRIMARY KEY (`stock_item_id`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`transaction_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- Indexes for table `vendors`
--
ALTER TABLE `vendors`
  ADD PRIMARY KEY (`vendor_id`),
  ADD UNIQUE KEY `vendor_no` (`vendor_no`);

--
-- Indexes for table `work_holidays`
--
ALTER TABLE `work_holidays`
  ADD PRIMARY KEY (`work_holiday_id`);

--
-- Indexes for table `work_times`
--
ALTER TABLE `work_times`
  ADD PRIMARY KEY (`work_time_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_log`
--
ALTER TABLE `activity_log`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `banks`
--
ALTER TABLE `banks`
  MODIFY `bank_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `company`
--
ALTER TABLE `company`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `customer_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `deliveries`
--
ALTER TABLE `deliveries`
  MODIFY `delivery_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `delivery_boxes`
--
ALTER TABLE `delivery_boxes`
  MODIFY `dbox_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `employee_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `heads`
--
ALTER TABLE `heads`
  MODIFY `head_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=107;

--
-- AUTO_INCREMENT for table `head_types`
--
ALTER TABLE `head_types`
  MODIFY `head_type_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `igroups`
--
ALTER TABLE `igroups`
  MODIFY `igroup_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `igroup_items`
--
ALTER TABLE `igroup_items`
  MODIFY `igroup_item_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `images`
--
ALTER TABLE `images`
  MODIFY `image_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=148;

--
-- AUTO_INCREMENT for table `machines`
--
ALTER TABLE `machines`
  MODIFY `machine_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `materials`
--
ALTER TABLE `materials`
  MODIFY `material_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `mprocess`
--
ALTER TABLE `mprocess`
  MODIFY `mprocess_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `order_item_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=98;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=91;

--
-- AUTO_INCREMENT for table `permission_role`
--
ALTER TABLE `permission_role`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=511;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` bigint(255) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `product_costs`
--
ALTER TABLE `product_costs`
  MODIFY `product_cost_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=111;

--
-- AUTO_INCREMENT for table `product_materials`
--
ALTER TABLE `product_materials`
  MODIFY `product_material_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=559;

--
-- AUTO_INCREMENT for table `product_types`
--
ALTER TABLE `product_types`
  MODIFY `product_type_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=107;

--
-- AUTO_INCREMENT for table `purchases`
--
ALTER TABLE `purchases`
  MODIFY `purchase_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=76;

--
-- AUTO_INCREMENT for table `purchase_items`
--
ALTER TABLE `purchase_items`
  MODIFY `purchase_item_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=487;

--
-- AUTO_INCREMENT for table `receives`
--
ALTER TABLE `receives`
  MODIFY `receive_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=93;

--
-- AUTO_INCREMENT for table `receive_materials`
--
ALTER TABLE `receive_materials`
  MODIFY `receive_material_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=304;

--
-- AUTO_INCREMENT for table `returns`
--
ALTER TABLE `returns`
  MODIFY `return_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT for table `return_materials`
--
ALTER TABLE `return_materials`
  MODIFY `return_material_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=148;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `salaries`
--
ALTER TABLE `salaries`
  MODIFY `salary_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `stocks`
--
ALTER TABLE `stocks`
  MODIFY `stock_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10034;

--
-- AUTO_INCREMENT for table `stock_items`
--
ALTER TABLE `stock_items`
  MODIFY `stock_item_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=378;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `transaction_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=94;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `vendors`
--
ALTER TABLE `vendors`
  MODIFY `vendor_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `work_holidays`
--
ALTER TABLE `work_holidays`
  MODIFY `work_holiday_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `work_times`
--
ALTER TABLE `work_times`
  MODIFY `work_time_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
