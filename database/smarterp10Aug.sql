-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 10, 2024 at 01:26 PM
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
(10, 'Working', 1, '2024-08-07 06:09:57', '2024-08-07 06:09:57');

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
(7, 'C24001', 140, 93, 'Private Brand (pvt) Ltd.', '.', '.', '.', '.', '.', NULL, 1, '2024-08-10 02:48:31', '2024-08-10 02:48:31');

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

INSERT INTO `employees` (`employee_id`, `employee_no`, `department_id`, `employee_type_id`, `attendance_id`, `name`, `fname`, `sname`, `cnic`, `phone1`, `phone2`, `city_id`, `address`, `salary`, `description`, `joining_date`, `employee_status`, `created_by`, `created_at`, `updated_at`) VALUES
(13, 'E0001', 6, 39, 1, 'M Saqib Arshad', 'M Arshad', NULL, '34603-6718805-1', '03316125190', '03316115426', 43, 'Shafi Da Patha', 0, NULL, '2022-05-09', 1, 1, '2024-08-02 00:16:52', '2024-08-02 00:16:52');

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
(6, 3, 'Admin', 1, 0, 1, '2024-02-17 00:38:59', '2024-02-17 00:38:59'),
(7, 3, 'Cutting', 1, 0, 1, '2024-02-17 00:40:17', '2024-02-17 00:40:17'),
(8, 3, 'Stitching', 1, 0, 1, '2024-02-17 00:44:53', '2024-02-17 00:44:53'),
(9, 3, 'Quality Checking', 1, 0, 1, '2024-02-17 00:46:03', '2024-02-17 00:46:03'),
(10, 3, 'Ironing', 1, 0, 1, '2024-02-17 00:48:36', '2024-02-17 00:48:36'),
(32, 6, 'HBL Bank', 1, 0, 1, '2024-02-18 09:05:28', '2024-02-18 09:05:28'),
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
(98, 7, 'Tax Charges', 1, 0, 1, '2024-05-10 08:16:44', '2024-05-10 08:16:44'),
(101, 10, 'Machine Material', 1, 1, 1, '2024-05-24 08:51:09', '2024-05-24 08:51:09'),
(102, 19, 'Joki Stitching', 1, 0, 1, '2024-05-24 11:19:21', '2024-05-24 11:19:21'),
(103, 19, 'Maghzi', 1, 0, 1, '2024-05-24 11:19:42', '2024-05-24 11:19:42'),
(104, 19, 'Embroidery', 1, 0, 1, '2024-05-24 11:20:01', '2024-05-24 11:20:01'),
(105, 12, 'Rejection', 0, 1, 1, '2024-05-28 05:52:35', '2024-05-28 05:52:35'),
(106, 4, 'Yards', 1, 0, 1, '2024-07-31 00:38:35', '2024-07-31 00:38:35'),
(107, 4, 'KG', 1, 0, 1, '2024-07-31 00:38:49', '2024-07-31 00:38:49'),
(108, 4, 'Cone', 1, 0, 1, '2024-07-31 00:40:38', '2024-07-31 00:40:38'),
(109, 4, 'SHEET', 1, 0, 1, '2024-07-31 00:41:16', '2024-07-31 00:41:16'),
(110, 4, 'PCS', 1, 0, 1, '2024-07-31 00:41:26', '2024-07-31 00:41:26'),
(111, 4, 'Meter', 1, 0, 1, '2024-07-31 00:41:46', '2024-07-31 00:41:46'),
(112, 11, 'Thread', 1, 0, 1, '2024-07-31 00:58:25', '2024-07-31 00:58:25'),
(113, 11, 'Label', 1, 0, 1, '2024-07-31 01:00:11', '2024-07-31 01:00:11'),
(114, 11, 'Fabric', 1, 0, 1, '2024-07-31 01:15:21', '2024-07-31 01:15:21'),
(115, 8, 'Karachi', 1, 0, 1, '2024-07-31 01:17:34', '2024-07-31 01:17:34'),
(116, 11, 'Screen Printing', 1, 0, 1, '2024-07-31 01:25:19', '2024-07-31 01:25:19'),
(117, 11, 'Cards Printing', 1, 0, 1, '2024-07-31 01:25:37', '2024-07-31 01:25:37'),
(118, 8, 'Faislabad', 1, 0, 1, '2024-07-31 01:27:44', '2024-07-31 01:27:44'),
(119, 11, 'Dyeing', 1, 0, 1, '2024-07-31 01:32:03', '2024-07-31 01:33:43'),
(120, 11, 'TPR Rubber Logo', 1, 0, 1, '2024-07-31 01:55:41', '2024-07-31 01:55:41'),
(121, 11, 'Embroidery', 1, 0, 1, '2024-07-31 02:03:28', '2024-07-31 02:03:28'),
(122, 11, 'CNC Dyes', 1, 0, 1, '2024-07-31 02:05:04', '2024-07-31 02:05:04'),
(123, 11, 'Digital Printing', 1, 0, 1, '2024-07-31 02:07:39', '2024-07-31 02:07:39'),
(124, 11, 'Packing Material', 1, 0, 1, '2024-07-31 02:09:25', '2024-07-31 02:09:25'),
(125, 11, 'Bar Code Sticker', 1, 0, 1, '2024-07-31 02:11:14', '2024-07-31 02:12:50'),
(126, 3, 'Packing', 1, 0, 1, '2024-08-02 00:12:36', '2024-08-02 00:12:36'),
(127, 3, 'Checking', 1, 0, 1, '2024-08-02 00:12:55', '2024-08-02 00:12:55'),
(128, 3, 'Receiving', 1, 0, 1, '2024-08-02 00:13:20', '2024-08-02 00:13:20'),
(129, 3, 'Embossing', 1, 0, 1, '2024-08-02 00:23:59', '2024-08-02 00:23:59'),
(130, 3, 'General', 1, 0, 1, '2024-08-02 00:24:27', '2024-08-02 00:24:27'),
(131, 3, 'Security', 1, 0, 1, '2024-08-02 00:25:03', '2024-08-02 00:25:03'),
(132, 12, 'Printing', 1, 0, 1, '2024-08-08 05:03:41', '2024-08-08 05:03:41'),
(133, 12, 'Embossing', 1, 0, 1, '2024-08-08 05:03:52', '2024-08-08 05:03:52'),
(134, 12, 'Inject', 1, 0, 1, '2024-08-08 05:04:16', '2024-08-08 05:04:16'),
(135, 12, 'Ironing Press', 1, 0, 1, '2024-08-08 05:08:35', '2024-08-08 05:08:35'),
(136, 14, 'Printing', 1, 0, 1, '2024-08-08 05:15:51', '2024-08-08 05:15:51'),
(137, 14, 'Embossing', 1, 0, 1, '2024-08-08 05:16:04', '2024-08-08 05:16:04'),
(138, 14, 'Ironing Press', 1, 0, 1, '2024-08-08 05:16:22', '2024-08-08 05:16:22'),
(139, 14, 'Inject', 1, 0, 1, '2024-08-08 05:16:38', '2024-08-08 05:16:38'),
(140, 15, 'Australia', 1, 0, 1, '2024-08-10 02:47:31', '2024-08-10 02:47:31');

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
  `order_id` bigint(20) UNSIGNED NOT NULL,
  `igroup_date` date NOT NULL,
  `igroup_status` bigint(20) UNSIGNED NOT NULL DEFAULT 1,
  `description` longtext DEFAULT NULL,
  `created_by` bigint(20) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(25, 'M0001', 66, 16, 'BLACK FOURWAY (NYLON)', 107, 1800, '1-1-1', NULL, 1, '2024-07-31 03:56:27', '2024-08-07 08:33:24'),
(26, 'M0002', 66, 16, 'HIVIZ YELLOW FOURWAY (NYLON)', 107, 1800, NULL, NULL, 1, '2024-08-07 00:45:04', '2024-08-07 08:33:46'),
(27, 'M0003', 66, 16, 'GREEN FOURWAY (NYLON)', 107, 1800, NULL, NULL, 1, '2024-08-07 00:49:32', '2024-08-07 08:32:22'),
(28, 'M0004', 66, 16, 'GREEN FOURWAY LAMINATED (NYLON)', 107, 1800, NULL, NULL, 1, '2024-08-07 00:50:51', '2024-08-08 05:39:03'),
(29, 'M0005', 66, 16, 'BLACK FOURWAY LAMINATED (NYLON)', 107, 1800, NULL, NULL, 1, '2024-08-07 00:51:52', '2024-08-08 05:41:52'),
(30, 'M0006', 66, 16, 'GREY FOURWAY LAMINATED (NYLON)', 107, 1800, NULL, NULL, 1, '2024-08-07 00:54:29', '2024-08-08 05:46:20'),
(31, 'M0007', 66, 16, 'HIVIZ YELLOW FOURWAY LAMINATED (NYLON)', 107, 1800, NULL, NULL, 1, '2024-08-07 00:55:49', '2024-08-08 05:47:05'),
(32, 'M0008', 66, 16, 'BLACK FOURWAY LAMINATED (POLYESTER)', 107, 1150, NULL, NULL, 1, '2024-08-07 00:59:45', '2024-08-08 05:33:54'),
(33, 'M0009', 66, 16, 'BLACK FOURWAY (POLYESTER)', 107, 1150, NULL, NULL, 1, '2024-08-07 01:00:55', '2024-08-07 01:00:55'),
(34, 'M0010', 66, 16, 'GREY FOURWAY LAMINATED ( POLYESTER)', 107, 1150, NULL, NULL, 1, '2024-08-07 01:02:02', '2024-08-08 05:45:22'),
(35, 'M0011', 66, 16, 'GREY FOURWAY (POLYESTER)', 107, 1150, NULL, NULL, 1, '2024-08-07 01:03:22', '2024-08-07 01:03:22'),
(36, 'M0012', 66, 16, 'PARROT GREEN FOURWAY LAMINATED (PLOYESTER)', 107, 1150, NULL, NULL, 1, '2024-08-07 01:04:41', '2024-08-08 05:48:23'),
(37, 'M0013', 66, 16, 'BLUE FOURWAY ( POLYESTER)', 107, 1150, NULL, NULL, 1, '2024-08-07 01:05:56', '2024-08-07 01:05:56'),
(38, 'M0014', 66, 16, 'SAS SILVER GREY FOURWAY LAMINATED (POLYESTER)', 107, 1150, NULL, NULL, 1, '2024-08-07 01:07:09', '2024-08-08 05:49:19'),
(39, 'M0015', 66, 16, '(CORDOVA) GREY FOURWAY (POLYESTER)', 107, 1150, NULL, NULL, 1, '2024-08-07 01:08:22', '2024-08-07 01:08:22'),
(40, 'M0016', 66, 16, 'HIVIZ YELLOW FOURWAY LAMINATED (POLYESTER)', 107, 1150, NULL, NULL, 1, '2024-08-07 01:10:05', '2024-08-08 05:47:51'),
(41, 'M0017', 66, 16, 'GREEN FOURWAY (POLYESTER)', 107, 1150, NULL, NULL, 1, '2024-08-07 01:11:05', '2024-08-07 01:11:05'),
(42, 'M0018', 66, 16, '(CADOVA) GREY FOURWAY LAMINATED (POLYESTER)', 107, 1150, NULL, NULL, 1, '2024-08-07 01:12:18', '2024-08-08 05:37:21'),
(43, 'M0019', 66, 16, 'GREEN FOURWAY LAMINATED (POLYESTER)', 107, 1150, NULL, NULL, 1, '2024-08-07 01:13:08', '2024-08-08 05:38:25'),
(44, 'M0020', 66, 16, 'ORANGE FOURWAY (POLYESTER)', 107, 1150, NULL, NULL, 1, '2024-08-07 01:14:31', '2024-08-07 01:14:31'),
(45, 'M0021', 66, 16, 'ORANGER FOURWAY LAMINATED (POLYESTER)', 107, 1150, NULL, NULL, 1, '2024-08-07 01:15:37', '2024-08-08 05:39:54'),
(46, 'M0022', 66, 16, 'GREY FOURWAY (POLYESTER)', 107, 1150, NULL, NULL, 1, '2024-08-07 01:16:30', '2024-08-07 01:16:30'),
(47, 'M0023', 66, 16, 'HIVIZ YELLOW FOURWAY (POLYESTER)', 107, 1150, NULL, NULL, 1, '2024-08-07 01:17:53', '2024-08-07 01:17:53'),
(48, 'M0024', 66, 16, 'PARROT GREEN FOURWAY (POLYESTER)', 107, 1150, NULL, NULL, 1, '2024-08-07 01:19:02', '2024-08-07 01:19:02'),
(50, 'M0025', 66, 16, 'BLUE FOURWAY LAMINATED (POLYESTER)', 107, 1150, NULL, NULL, 1, '2024-08-07 01:21:01', '2024-08-08 05:48:51'),
(51, 'M0026', 66, 16, 'SAS SILVER GREY FOURWAY (POLYESTER)', 107, 1150, NULL, NULL, 1, '2024-08-07 01:22:20', '2024-08-07 01:22:37'),
(52, 'M0027', 66, 16, 'GREY FOURWAY (NYLON)', 107, 1150, NULL, NULL, 1, '2024-08-07 02:14:06', '2024-08-07 02:14:06'),
(53, 'M0028', 54, 18, 'HIVIZ YELLOW SYNTHETIC LEATHER', 106, 0, NULL, NULL, 1, '2024-08-07 02:21:11', '2024-08-07 02:25:40'),
(54, 'M0029', 54, 18, 'BLACK  SG SYNTHETIC LEATHER', 106, 0, NULL, NULL, 1, '2024-08-07 02:23:18', '2024-08-07 02:25:26'),
(55, 'M0030', 54, 21, 'BUFFALO LEATHER WHITE', 49, 0, NULL, NULL, 1, '2024-08-07 02:25:05', '2024-08-07 02:25:05'),
(56, 'M0031', 54, 21, 'GOAT LEATHER YELLOW', 49, 0, NULL, NULL, 1, '2024-08-07 02:26:29', '2024-08-07 02:26:29'),
(57, 'M0032', 54, 21, 'GOAT LEATHER BROWN', 49, 0, NULL, NULL, 1, '2024-08-07 02:27:26', '2024-08-07 02:27:26'),
(58, 'M0033', 66, 17, 'BLACK NEOPRENE (NYLON) 2.55MM', 109, 0, NULL, NULL, 1, '2024-08-07 02:31:36', '2024-08-07 02:31:36'),
(59, 'M0034', 66, 17, 'GREY NEOPRENE (POLYESTER) 2.25MM', 109, 0, NULL, NULL, 1, '2024-08-07 02:33:57', '2024-08-07 02:33:57'),
(60, 'M0035', 54, 21, 'WHITE GOAT CRUST', 49, 0, NULL, NULL, 1, '2024-08-07 02:35:27', '2024-08-07 02:35:27'),
(61, 'M0036', 66, 21, 'WHITE GOAT CRUST A+', 49, 0, NULL, NULL, 1, '2024-08-07 02:36:23', '2024-08-07 02:36:23'),
(62, 'M0037', 66, 17, 'VELCRO HOOK 1.25MM BLACK', 50, 0, NULL, NULL, 1, '2024-08-07 02:39:40', '2024-08-07 02:39:40'),
(63, 'M0038', 66, 17, 'VELCRO HOOK 0.75MM (BLACK)', 50, 0, NULL, NULL, 1, '2024-08-07 02:41:12', '2024-08-07 02:41:12'),
(64, 'M0039', 66, 17, 'VELCRO LOOP 1.25MM BLACK', 50, 0, NULL, NULL, 1, '2024-08-07 02:42:34', '2024-08-07 02:42:34'),
(66, 'M0040', 66, 17, 'VELCRO LOOP 1\" (BLACK)', 50, 0, NULL, '<p><br></p><p><br></p><p><br></p><p><br></p>', 1, '2024-08-07 02:44:37', '2024-08-08 06:23:50'),
(67, 'M0041', 66, 17, 'G933G VALCRO HOOK 1.25\" (GREEN)', 50, 0, NULL, NULL, 1, '2024-08-07 02:46:16', '2024-08-07 02:46:16'),
(68, 'M0042', 66, 17, 'G933G VALCRO LOOP 1.25\" (GREEN)', 50, 0, NULL, NULL, 1, '2024-08-07 02:47:54', '2024-08-07 02:47:54'),
(69, 'M0043', 66, 33, 'RUBBER STRAP WORX', 47, 0, NULL, NULL, 1, '2024-08-07 02:50:14', '2024-08-07 02:50:14'),
(70, 'M0044', 66, 33, 'RUBBER STAP LOGO (FBR MX 1)', 47, 0, NULL, NULL, 1, '2024-08-07 02:51:56', '2024-08-08 06:12:12'),
(71, 'M0045', 66, 33, 'RUBBER SHEET 2MM', 47, 0, NULL, NULL, 1, '2024-08-07 02:53:13', '2024-08-07 02:53:13'),
(72, 'M0046', 66, 20, 'WORX 1 SIZE LABEL (S)', 48, 0, NULL, NULL, 1, '2024-08-07 02:55:20', '2024-08-07 02:55:20'),
(73, 'M0047', 66, 20, 'WORX WOVEN LABEL', 48, 0, NULL, NULL, 1, '2024-08-07 02:56:52', '2024-08-07 02:56:52'),
(74, 'M0048', 66, 19, 'BLACK THREAD 2 PLY', 108, 0, NULL, NULL, 1, '2024-08-07 02:59:34', '2024-08-07 02:59:34'),
(75, 'M0049', 66, 19, 'SILVER GREY THREAD 3 PLY', 108, 0, NULL, NULL, 1, '2024-08-07 03:00:49', '2024-08-07 03:00:49'),
(76, 'M0050', 66, 19, 'HIVIZ YELLOW THREAD 2 PLY', 108, 0, NULL, NULL, 1, '2024-08-07 03:01:49', '2024-08-07 03:01:49'),
(77, 'M0051', 66, 19, 'HIVIZ YELLOW THREAD 3 PLY', 108, 0, NULL, NULL, 1, '2024-08-07 03:03:15', '2024-08-07 03:03:15'),
(78, 'M0052', 66, 19, 'GREY THREAD 2 PLY', 108, 0, NULL, NULL, 1, '2024-08-07 03:05:09', '2024-08-07 03:05:09'),
(79, 'M0053', 66, 19, 'WHITE THREAD', 108, 0, NULL, NULL, 1, '2024-08-07 03:07:11', '2024-08-07 03:07:11'),
(80, 'M0054', 66, 19, 'GREEN THREAD 2 PLY', 108, 0, NULL, NULL, 1, '2024-08-07 03:08:57', '2024-08-07 03:08:57'),
(81, 'M0055', 66, 19, 'WHITE THREAD 2PLY (NYLON)', 108, 0, NULL, NULL, 1, '2024-08-07 03:11:07', '2024-08-07 03:11:07'),
(82, 'M0056', 66, 19, 'RED THREAD 2 PLY (NYLON)', 108, 0, NULL, NULL, 1, '2024-08-07 03:13:01', '2024-08-07 03:13:01'),
(83, 'M0057', 66, 19, 'GREEN THREAD 2 PLY (NYLON)', 108, 0, NULL, NULL, 1, '2024-08-07 03:14:20', '2024-08-07 03:14:20'),
(84, 'M0058', 66, 19, 'BROWN THREAD 2 PLY (NYLON)', 108, 0, NULL, NULL, 1, '2024-08-07 03:15:56', '2024-08-07 03:15:56'),
(85, 'M0059', 66, 19, 'BLUE THREAD 2 PLY (NYLON)', 108, 0, NULL, NULL, 1, '2024-08-07 03:17:24', '2024-08-07 03:17:24'),
(86, 'M0060', 66, 19, 'BLACK THREAD 2 PLY (NYLON)', 108, 0, NULL, '<p><br></p><p><br></p><p><br></p><p><br></p><p><br></p><p><br></p><p><br></p>', 1, '2024-08-07 03:18:51', '2024-08-07 03:18:51'),
(87, 'M0061', 66, 19, 'YELLOW THREAD (POLYESTER)', 108, 0, NULL, NULL, 1, '2024-08-07 03:20:09', '2024-08-07 03:20:09'),
(88, 'M0062', 66, 19, 'HIVIZ ORANGE THREAD 2 PLY (NYLON) FOR TPR', 108, 0, NULL, '<p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<br></p>', 1, '2024-08-07 03:21:40', '2024-08-07 03:21:40'),
(89, 'M0063', 66, 19, 'YELLOW GOLD THREAD 2 PLY', 108, 0, NULL, NULL, 1, '2024-08-07 03:24:04', '2024-08-07 03:24:04'),
(90, 'M0064', 66, 19, 'YELLOW GOLD THREAD 3 PLY', 108, 0, NULL, NULL, 1, '2024-08-07 03:24:53', '2024-08-07 03:24:53'),
(91, 'M0065', 66, 19, 'ORIGNAL KEVLAR THREAD', 108, 0, NULL, NULL, 1, '2024-08-07 03:25:38', '2024-08-07 03:25:38'),
(92, 'M0066', 66, 19, 'ORANGE THREAD 2 PLY', 108, 0, NULL, NULL, 1, '2024-08-07 03:26:36', '2024-08-07 03:26:36'),
(93, 'M0067', 66, 19, 'PARROT GREEN THREAD 2 PLY (NYLON) (FOR INJECT)', 108, 0, NULL, NULL, 1, '2024-08-07 03:27:50', '2024-08-07 03:27:50'),
(94, 'M0068', 66, 17, 'GREY LYCRA', 106, 0, NULL, NULL, 1, '2024-08-07 04:38:17', '2024-08-08 06:08:11'),
(95, 'M0069', 66, 17, 'BLACK LYCRA', 106, 0, NULL, NULL, 1, '2024-08-07 04:40:58', '2024-08-07 04:40:58'),
(96, 'M0070', 66, 17, 'RED LYCRA (S)', 106, 0, NULL, NULL, 1, '2024-08-07 04:42:15', '2024-08-07 04:42:15'),
(97, 'M0071', 66, 17, 'GREEN LYCRA (M)', 106, 0, NULL, NULL, 1, '2024-08-07 04:42:53', '2024-08-07 04:42:53'),
(98, 'M0072', 66, 17, 'BROWN LYCRA (L)', 106, 0, NULL, NULL, 1, '2024-08-07 04:43:43', '2024-08-07 04:43:43'),
(99, 'M0073', 66, 17, 'BLUE LYCRA (XL)', 106, 0, NULL, NULL, 1, '2024-08-07 04:44:44', '2024-08-07 04:44:44'),
(100, 'M0074', 66, 17, 'HIVIZ YELLOW LYCRA', 106, 0, NULL, NULL, 1, '2024-08-07 04:45:39', '2024-08-08 06:09:03'),
(101, 'M0075', 66, 17, 'HIVIZ ORANGE LYCRA (XXXL)', 106, 0, NULL, NULL, 1, '2024-08-07 04:46:40', '2024-08-07 04:46:40'),
(102, 'M0076', 66, 31, 'BLACK ELASTIC 0.75MM', 50, 0, NULL, NULL, 1, '2024-08-07 04:47:59', '2024-08-07 04:47:59'),
(103, 'M0077', 66, 31, 'BLACK ELASTIC 0.25MM', 50, 0, NULL, NULL, 1, '2024-08-07 04:49:21', '2024-08-07 04:49:21'),
(104, 'M0078', 66, 31, 'WHITE ELASTIC 0.75MM', 50, 0, NULL, NULL, 1, '2024-08-07 04:50:47', '2024-08-07 04:50:47'),
(105, 'M0079', 66, 31, 'WHITE ELASTIC 0.5CM', 50, 0, NULL, NULL, 1, '2024-08-07 04:51:59', '2024-08-07 04:51:59'),
(106, 'M0080', 66, 31, 'WHITE ELASTIC 10mm (1cm)', 50, 0, NULL, NULL, 1, '2024-08-07 04:53:25', '2024-08-07 04:53:25'),
(107, 'M0081', 66, 16, 'KEVLAR GRIPY', 49, 0, NULL, NULL, 1, '2024-08-07 05:00:45', '2024-08-07 05:00:45'),
(108, 'M0082', 66, 16, 'KEVLAR-CR-SC-F52Y-G()', 49, 0, NULL, NULL, 1, '2024-08-07 05:03:06', '2024-08-07 05:03:06'),
(109, 'M0083', 66, 16, 'PC 270-GSM YELLOW KEVLAR COLOR', 49, 0, NULL, NULL, 1, '2024-08-07 05:04:07', '2024-08-07 05:04:07'),
(110, 'M0084', 66, 16, 'GEL PAM', 51, 0, NULL, NULL, 1, '2024-08-07 05:05:01', '2024-08-07 05:05:01'),
(111, 'M0085', 66, 16, 'DYNEEMA LAMINATED WITH AMMARA', 50, 0, NULL, NULL, 1, '2024-08-07 05:06:25', '2024-08-07 05:06:25'),
(112, 'M0086', 66, 16, 'THINSULATE', 49, 0, NULL, NULL, 1, '2024-08-07 05:07:32', '2024-08-07 05:07:32'),
(113, 'M0087', 66, 16, 'FLEES BLACK', 49, 0, NULL, NULL, 1, '2024-08-07 05:08:28', '2024-08-07 05:08:28'),
(114, 'M0088', 66, 16, 'POPCORN FABRIC (BLACK)', 49, 0, NULL, NULL, 1, '2024-08-07 05:09:23', '2024-08-07 05:09:23'),
(115, 'M0089', 66, 16, 'POPCORN FABRIC (RED)', 49, 0, NULL, NULL, 1, '2024-08-07 05:11:05', '2024-08-07 05:11:05'),
(116, 'M0090', 66, 16, 'POPCORN FABRIC (ORANGE)', 49, 0, NULL, NULL, 1, '2024-08-07 05:11:55', '2024-08-07 05:11:55'),
(117, 'M0091', 66, 31, 'GELL SHEET 200G', 51, 0, NULL, NULL, 1, '2024-08-07 05:13:02', '2024-08-07 05:13:02'),
(118, 'M0092', 66, 33, 'G9050 TPR (BLACK)', 47, 0, NULL, NULL, 1, '2024-08-07 05:16:09', '2024-08-07 05:16:09'),
(119, 'M0093', 66, 33, 'G933G TPR (GREEN)', 47, 0, NULL, NULL, 1, '2024-08-07 05:17:26', '2024-08-07 05:17:26'),
(120, 'M0094', 66, 33, 'G933G-2 TPR (GREEN)', 47, 0, NULL, NULL, 1, '2024-08-07 05:19:01', '2024-08-07 05:19:01'),
(121, 'M0095', 66, 16, 'FLEES YELLOW', 50, 0, NULL, NULL, 1, '2024-08-07 05:20:32', '2024-08-07 05:20:32'),
(122, 'M0096', 66, 20, 'ANSI/ISEA 138 WOVEN LABEL', 48, 0, NULL, NULL, 1, '2024-08-07 05:22:42', '2024-08-07 05:22:42'),
(123, 'M0097', 66, 20, 'G 9050 SIZE LABEL (S)', 48, 0, NULL, NULL, 1, '2024-08-07 05:23:48', '2024-08-07 05:23:48'),
(124, 'M0098', 66, 20, 'G 9050 SIZE LABEL (M)', 48, 0, NULL, NULL, 1, '2024-08-07 05:25:32', '2024-08-07 05:25:32'),
(125, 'M0099', 66, 20, 'G 9050 SIZE LABEL (L)', 48, 0, NULL, NULL, 1, '2024-08-07 05:26:41', '2024-08-07 05:26:41'),
(126, 'M0100', 66, 20, 'G 9050 SIZE LABEL (XL)', 48, 0, NULL, NULL, 1, '2024-08-07 05:28:04', '2024-08-07 05:28:04'),
(127, 'M0101', 66, 20, 'G 9050 SIZE LABEL (XXL)', 48, 0, NULL, NULL, 1, '2024-08-07 05:28:52', '2024-08-07 05:28:52'),
(128, 'M0102', 66, 20, 'G 9050 SIZE LABEL (XXXL)', 48, 0, NULL, NULL, 1, '2024-08-07 05:29:44', '2024-08-07 05:29:44'),
(129, 'M0103', 66, 20, 'G 9060 SIZE LABEL (S)', 48, 0, NULL, NULL, 1, '2024-08-07 05:30:49', '2024-08-07 05:30:49'),
(130, 'M0104', 66, 33, 'G 9060 TPR (BLACK)', 47, 0, NULL, NULL, 1, '2024-08-07 05:32:16', '2024-08-07 05:32:16'),
(131, 'M0105', 66, 20, 'G 9060 SIZE LABEL (M)', 48, 0, NULL, NULL, 1, '2024-08-07 05:33:17', '2024-08-07 05:33:17'),
(132, 'M0106', 66, 20, 'G 9060 SIZE LABEL (L)', 48, 0, NULL, NULL, 1, '2024-08-07 05:33:59', '2024-08-07 05:33:59'),
(133, 'M0107', 66, 20, 'G 9060 SIZE LABEL (XL)', 48, 0, NULL, NULL, 1, '2024-08-07 05:34:52', '2024-08-07 05:34:52'),
(134, 'M0108', 66, 20, 'G 9060 SIZE LABEL (XXL)', 48, 0, NULL, NULL, 1, '2024-08-07 05:35:40', '2024-08-07 05:35:40'),
(135, 'M0109', 66, 20, 'G 9060 SIZE LABEL (XXXL)', 48, 0, NULL, NULL, 1, '2024-08-07 05:36:32', '2024-08-07 05:36:32'),
(136, 'M0110', 66, 20, 'G933G SIZE LABEL (S)', 48, 0, NULL, NULL, 1, '2024-08-07 05:37:37', '2024-08-07 05:37:37'),
(137, 'M0111', 66, 20, 'G933G SIZE LABEL (M)', 48, 0, NULL, NULL, 1, '2024-08-07 05:38:32', '2024-08-07 05:38:32'),
(138, 'M0112', 66, 20, 'G933G SIZE LABEL (L)', 48, 0, NULL, NULL, 1, '2024-08-07 05:39:31', '2024-08-07 05:39:31'),
(139, 'M0113', 66, 20, 'G933G SIZE LABEL (XL)', 48, 0, NULL, NULL, 1, '2024-08-07 05:40:21', '2024-08-07 05:40:21'),
(140, 'M0114', 66, 20, 'G933G SIZE LABEL (XXL)', 48, 0, NULL, NULL, 1, '2024-08-07 05:41:04', '2024-08-07 05:41:04'),
(141, 'M0115', 66, 20, 'G933G SIZE LABEL (XXXL)', 48, 0, NULL, NULL, 1, '2024-08-07 05:42:05', '2024-08-07 05:42:05'),
(142, 'M0116', 66, 20, 'G933G-2 SIZE LABEL (S)', 48, 0, NULL, NULL, 1, '2024-08-07 05:43:07', '2024-08-07 05:43:07'),
(143, 'M0117', 66, 20, 'G933G-2 SIZE LABEL (M)', 48, 0, NULL, NULL, 1, '2024-08-07 05:44:01', '2024-08-07 05:44:01'),
(144, 'M0118', 66, 20, 'G933G-2 SIZE LABEL (L)', 48, 0, NULL, NULL, 1, '2024-08-07 05:44:56', '2024-08-07 05:44:56'),
(145, 'M0119', 66, 20, 'G933G-2 SIZE LABEL (XL)', 48, 0, NULL, NULL, 1, '2024-08-07 05:46:08', '2024-08-07 05:46:08'),
(146, 'M0120', 66, 20, 'G933G-2 SIZE LABEL (XXL)', 48, 0, NULL, NULL, 1, '2024-08-07 05:46:50', '2024-08-07 05:46:50'),
(147, 'M0121', 66, 20, 'G933G-2 SIZE LABEL (XXXL)', 48, 0, NULL, NULL, 1, '2024-08-07 05:47:37', '2024-08-07 05:47:37'),
(148, 'M0122', 66, 16, 'GREEN MESH POLYESTER', 107, 0, NULL, NULL, 1, '2024-08-07 05:50:24', '2024-08-07 05:57:46'),
(149, 'M0123', 66, 16, 'SUTER # 18', 107, 0, NULL, NULL, 1, '2024-08-07 05:52:06', '2024-08-07 05:52:06'),
(150, 'M0124', 66, 16, 'SUTER # 21', 107, 0, NULL, '<p>&nbsp;&nbsp;&nbsp;&nbsp;<br></p><p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<br></p>', 1, '2024-08-07 05:52:50', '2024-08-07 05:52:50'),
(151, 'M0125', 66, 16, 'FOAM 6MM', 48, 0, NULL, NULL, 1, '2024-08-07 05:53:55', '2024-08-07 05:53:55'),
(152, 'M0126', 66, 16, 'RED CHAMKI POLYESTER (S)', 107, 0, NULL, NULL, 1, '2024-08-07 05:54:59', '2024-08-07 05:54:59'),
(153, 'M0127', 66, 16, 'GREEN CHAMKI POLYESTER (M)', 107, 0, NULL, NULL, 1, '2024-08-07 05:56:04', '2024-08-07 05:56:04'),
(154, 'M0128', 66, 16, 'BROWN CHAMKI POLYESTER (L)', 107, 0, NULL, NULL, 1, '2024-08-07 05:57:05', '2024-08-07 05:57:05'),
(155, 'M0129', 66, 16, 'BLUE CHAMKI POLYESTER (XL)', 107, 0, NULL, NULL, 1, '2024-08-07 05:58:49', '2024-08-07 05:58:49'),
(156, 'M0130', 66, 16, 'BLACK CHAMKI POLYESTER (XL)', 107, 0, NULL, NULL, 1, '2024-08-07 05:59:46', '2024-08-07 05:59:46'),
(157, 'M0131', 66, 16, 'HIVIZ ORANGE CHAMKI POLYESTER (XXXL)', 107, 0, NULL, NULL, 1, '2024-08-07 06:00:57', '2024-08-07 06:00:57'),
(158, 'M0132', 66, 21, 'BLACK SYNTHETIC LEATHER EAGLE', 49, 0, NULL, NULL, 1, '2024-08-08 05:27:53', '2024-08-08 05:28:23'),
(159, 'M0133', 66, 21, 'GREY SYNTHETIC LEATHER EAGLE', 49, 0, NULL, NULL, 1, '2024-08-08 05:29:14', '2024-08-08 05:29:14'),
(160, 'M0134', 66, 21, 'BROWN SYNTHETIC LEATHER EAGLE', 49, 0, NULL, '<p><br></p><p><br></p>', 1, '2024-08-08 05:30:07', '2024-08-08 05:30:07'),
(161, 'M0135', 66, 33, 'RUBBER STAP LOGO (FBR MX 2)', 47, 0, NULL, NULL, 1, '2024-08-08 06:13:14', '2024-08-08 06:13:14'),
(162, 'M0136', 66, 33, 'RUBBER STAP LOGO (FBR MX 3)', 47, 0, NULL, NULL, 1, '2024-08-08 06:13:50', '2024-08-08 06:13:50'),
(163, 'M0137', 66, 33, 'RUBBER STAP LOGO (FBR MX 4)', 47, 0, NULL, NULL, 1, '2024-08-08 06:14:36', '2024-08-08 06:14:36'),
(164, 'M0138', 66, 33, 'RUBBER STAP LOGO (FBR MX 5)', 47, 0, NULL, NULL, 1, '2024-08-08 06:15:14', '2024-08-08 06:15:14'),
(165, 'M0139', 66, 33, 'RUBBER STAP LOGO (FBR MX 8)', 47, 0, NULL, NULL, 1, '2024-08-08 06:15:45', '2024-08-08 06:15:45'),
(166, 'M0140', 66, 33, 'RUBBER STAP LOGO (FBR MX 11)', 47, 0, NULL, NULL, 1, '2024-08-08 06:16:11', '2024-08-08 06:16:11'),
(167, 'M0141', 66, 33, 'RUBBER STAP LOGO (FBR MX 12)', 47, 0, NULL, NULL, 1, '2024-08-08 06:16:46', '2024-08-08 06:16:46'),
(168, 'M0142', 66, 33, 'RUBBER STAP LOGO (FBR MX 30)', 47, 0, NULL, NULL, 1, '2024-08-08 06:17:16', '2024-08-08 06:17:16'),
(169, 'M0143', 66, 20, 'SIZE LABEL MX-1(S)', 48, 0, NULL, '<p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</p><p><br></p>', 1, '2024-08-08 06:28:48', '2024-08-08 06:28:48'),
(170, 'M0144', 66, 20, 'SIZE LABEL MX-1(M)', 48, 0, NULL, NULL, 1, '2024-08-08 06:29:19', '2024-08-08 06:29:19'),
(171, 'M0145', 66, 20, 'SIZE LABEL MX-1(L)', 48, 0, NULL, NULL, 1, '2024-08-08 06:29:45', '2024-08-08 06:29:45'),
(172, 'M0146', 66, 20, 'SIZE LABEL MX-1(XL)', 48, 0, NULL, NULL, 1, '2024-08-08 06:30:19', '2024-08-08 06:30:19'),
(173, 'M0147', 66, 20, 'SIZE LABEL MX-1(XXL)', 48, 0, NULL, NULL, 1, '2024-08-08 06:30:47', '2024-08-08 06:30:47'),
(174, 'M0148', 66, 20, 'CERTIFIED WOVEN LABEL FOR FORCE', 48, 0, NULL, NULL, 1, '2024-08-08 06:31:41', '2024-08-08 06:31:41'),
(175, 'M0149', 61, 46, 'CARTON FOR MX (40*32*39)', 52, 300, NULL, NULL, 1, '2024-08-08 07:47:58', '2024-08-08 07:47:58'),
(176, 'M0150', 66, 16, 'REFLECTOR', 48, 0, NULL, NULL, 1, '2024-08-08 08:28:28', '2024-08-08 08:28:28'),
(177, 'M0151', 66, 20, 'MX-2 SIZE LABEL (S)', 48, 0, NULL, NULL, 1, '2024-08-09 00:38:23', '2024-08-09 00:38:45'),
(178, 'M0152', 66, 20, 'MX-2 SIZE LABEL (M)', 48, 0, NULL, NULL, 1, '2024-08-09 00:44:59', '2024-08-09 00:44:59'),
(179, 'M0153', 66, 20, 'MX-2 SIZE LABEL (L)', 48, 0, NULL, NULL, 1, '2024-08-09 00:45:34', '2024-08-09 00:45:34'),
(180, 'M0154', 66, 20, 'MX-2 SIZE LABEL (XL)', 48, 0, NULL, NULL, 1, '2024-08-09 00:46:17', '2024-08-09 00:46:17'),
(181, 'M0155', 66, 20, 'MX-2 SIZE LABEL (XXL)', 48, 0, NULL, NULL, 1, '2024-08-09 00:46:51', '2024-08-09 00:46:51'),
(182, 'M0156', 66, 33, 'RUBBER INJECT FOR MX3', 47, 0, NULL, NULL, 1, '2024-08-09 01:28:17', '2024-08-09 01:28:17'),
(183, 'M0157', 66, 20, 'MX-3 SIZE LABEL (S)', 48, 0, NULL, NULL, 1, '2024-08-09 01:29:02', '2024-08-09 01:29:02'),
(184, 'M0158', 66, 20, 'MX-3 SIZE LABEL (M)', 48, 0, NULL, NULL, 1, '2024-08-09 01:29:41', '2024-08-09 01:29:41'),
(185, 'M0159', 66, 20, 'MX-3 SIZE LABEL (L)', 48, 0, NULL, NULL, 1, '2024-08-09 01:30:20', '2024-08-09 01:30:20'),
(186, 'M0160', 66, 20, 'MX-3 SIZE LABEL (XL)', 48, 0, NULL, NULL, 1, '2024-08-09 01:31:05', '2024-08-09 01:31:05'),
(187, 'M0161', 66, 20, 'MX-3 SIZE LABEL (XXL)', 48, 0, NULL, NULL, 1, '2024-08-09 01:31:35', '2024-08-09 01:31:35'),
(188, 'M0162', 66, 20, 'MX-4 SIZE LABEL (S)', 48, 0, NULL, NULL, 1, '2024-08-09 02:14:19', '2024-08-09 03:14:02'),
(189, 'M0163', 66, 20, 'MX-4 SIZE LABEL (M)', 48, 0, NULL, NULL, 1, '2024-08-09 02:15:04', '2024-08-09 03:13:49'),
(190, 'M0164', 66, 20, 'MX-4 SIZE LABEL (L)', 48, 0, NULL, NULL, 1, '2024-08-09 02:15:45', '2024-08-09 03:13:32'),
(191, 'M0165', 66, 20, 'MX-4 SIZE LABEL (XL)', 48, 0, NULL, NULL, 1, '2024-08-09 02:16:21', '2024-08-09 03:13:20'),
(192, 'M0166', 66, 20, 'MX-4 SIZE LABEL (XXL)', 48, 0, NULL, NULL, 1, '2024-08-09 02:17:09', '2024-08-09 03:13:09'),
(193, 'M0167', 61, 46, 'CARTON FOR MX (40*32*46)', 52, 0, NULL, NULL, 1, '2024-08-09 02:28:36', '2024-08-09 02:28:36'),
(194, 'M0168', 66, 20, 'MX-5 SIZE LABEL (S)', 48, 0, NULL, NULL, 1, '2024-08-09 03:12:16', '2024-08-09 03:12:16'),
(195, 'M0169', 66, 20, 'MX-5 SIZE LABEL (M)', 48, 0, NULL, NULL, 1, '2024-08-09 03:14:44', '2024-08-09 03:14:44'),
(196, 'M0170', 66, 20, 'MX-5 SIZE LABEL (L)', 48, 0, NULL, NULL, 1, '2024-08-09 03:15:20', '2024-08-09 03:15:20'),
(197, 'M0171', 66, 20, 'MX-5 SIZE LABEL (XL)', 48, 0, NULL, NULL, 1, '2024-08-09 03:15:54', '2024-08-09 03:15:54'),
(198, 'M0172', 66, 20, 'MX-5 SIZE LABEL (XXL)', 48, 0, NULL, NULL, 1, '2024-08-09 03:16:32', '2024-08-09 03:16:32'),
(199, 'M0173', 66, 35, 'DYNEEMA LAMINATED', 107, 0, NULL, NULL, 1, '2024-08-09 03:19:27', '2024-08-09 03:19:27'),
(200, 'M0174', 66, 20, 'MX-8 SIZE LABEL (S)', 48, 0, NULL, NULL, 1, '2024-08-09 05:23:09', '2024-08-09 05:23:09'),
(201, 'M0175', 66, 20, 'MX-8 SIZE LABEL (M)', 48, 0, NULL, NULL, 1, '2024-08-09 05:23:47', '2024-08-09 05:23:47'),
(202, 'M0176', 66, 20, 'MX-8 SIZE LABEL (L)', 48, 0, NULL, NULL, 1, '2024-08-09 05:24:26', '2024-08-09 05:24:26'),
(203, 'M0177', 66, 20, 'MX-8 SIZE LABEL (XL)', 48, 0, NULL, NULL, 1, '2024-08-09 05:25:08', '2024-08-09 05:25:08'),
(204, 'M0178', 66, 20, 'MX-8 SIZE LABEL (XXL)', 48, 0, NULL, NULL, 1, '2024-08-09 05:25:54', '2024-08-09 05:25:54'),
(205, 'M0179', 66, 17, 'YELLOW LYCRA', 106, 0, NULL, NULL, 1, '2024-08-09 06:08:54', '2024-08-09 06:08:54'),
(206, 'M0180', 66, 19, 'YELLOW THREAD 3 PLY (POLYESTER)', 108, 0, NULL, NULL, 1, '2024-08-09 06:10:20', '2024-08-09 06:10:20'),
(207, 'M0181', 66, 19, 'YELLOW THREAD 2 PLY (POLYESTER)', 108, 0, NULL, NULL, 1, '2024-08-09 06:11:31', '2024-08-09 06:11:31'),
(208, 'M0182', 66, 16, 'FOAM 4MM WHITE', 48, 0, NULL, NULL, 1, '2024-08-09 06:12:19', '2024-08-09 06:12:19'),
(209, 'M0183', 66, 20, 'MX-11 SIZE LABEL (S)', 48, 0, NULL, NULL, 1, '2024-08-09 06:19:05', '2024-08-09 06:19:05'),
(210, 'M0184', 66, 20, 'MX-11 SIZE LABEL (M)', 48, 0, NULL, NULL, 1, '2024-08-09 06:19:43', '2024-08-09 06:19:43'),
(211, 'M0185', 66, 20, 'MX-11 SIZE LABEL (L)', 48, 0, NULL, NULL, 1, '2024-08-09 06:20:25', '2024-08-09 06:20:25'),
(212, 'M0186', 66, 20, 'MX-11 SIZE LABEL (XL)', 48, 0, NULL, NULL, 1, '2024-08-09 06:20:58', '2024-08-09 06:20:58'),
(213, 'M0187', 66, 20, 'MX-11 SIZE LABEL (XXL)', 48, 0, NULL, '<p>&nbsp;&nbsp;&nbsp;&nbsp;<br></p>', 1, '2024-08-09 06:21:51', '2024-08-09 06:21:51'),
(214, 'M0188', 66, 20, 'MX-12 SIZE LABEL (S)', 48, 0, NULL, NULL, 1, '2024-08-09 06:57:52', '2024-08-09 06:57:52'),
(215, 'M0189', 66, 20, 'MX-12 SIZE LABEL (M)', 48, 0, NULL, NULL, 1, '2024-08-09 06:58:28', '2024-08-09 06:58:28'),
(216, 'M0190', 66, 20, 'MX-12 SIZE LABEL (L)', 48, 0, NULL, NULL, 1, '2024-08-09 06:59:11', '2024-08-09 06:59:11'),
(217, 'M0191', 66, 20, 'MX-12 SIZE LABEL (XL)', 48, 0, NULL, NULL, 1, '2024-08-09 07:00:04', '2024-08-09 07:00:04'),
(218, 'M0192', 66, 20, 'MX-12 SIZE LABEL (XXL)', 48, 0, NULL, NULL, 1, '2024-08-09 07:00:40', '2024-08-09 07:00:40'),
(219, 'M0193', 66, 20, 'MX-30 SIZE LABEL (S)', 48, 0, NULL, NULL, 1, '2024-08-09 07:51:10', '2024-08-09 07:51:10'),
(220, 'M0194', 66, 20, 'MX-30 SIZE LABEL (M)', 48, 0, NULL, NULL, 1, '2024-08-09 07:52:06', '2024-08-09 07:52:06'),
(221, 'M0195', 66, 20, 'MX-30 SIZE LABEL (L)', 48, 0, NULL, NULL, 1, '2024-08-09 07:52:40', '2024-08-09 07:52:40'),
(222, 'M0196', 66, 20, 'MX-30 SIZE LABEL (XL)', 48, 0, NULL, NULL, 1, '2024-08-09 07:53:17', '2024-08-09 07:53:17'),
(223, 'M0197', 66, 20, 'MX-30 SIZE LABEL (XXL)', 48, 0, NULL, NULL, 1, '2024-08-09 07:53:48', '2024-08-09 07:53:48'),
(224, 'M0198', 66, 33, 'GFPRMX-30 TPR (ORANGE)', 47, 0, NULL, NULL, 1, '2024-08-09 08:03:51', '2024-08-09 08:03:51'),
(225, 'M0199', 66, 16, 'ORANGE FOURWAY LAMINATED (POLYESTER)', 107, 0, NULL, NULL, 1, '2024-08-09 08:05:33', '2024-08-09 08:05:33'),
(226, 'M0200', 66, 16, 'KEVLAR STICKER LAMINATED', 49, 0, NULL, NULL, 1, '2024-08-09 08:06:42', '2024-08-09 08:06:42'),
(227, 'M0201', 66, 20, 'WORX 1 SIZE LABEL (M)', 48, 0, NULL, NULL, 1, '2024-08-09 23:44:05', '2024-08-09 23:44:05'),
(228, 'M0202', 66, 20, 'WORX 1 SIZE LABEL (L)', 48, 0, NULL, NULL, 1, '2024-08-09 23:44:43', '2024-08-09 23:44:43'),
(233, 'M0203', 66, 20, 'WORX 1 SIZE LABEL (XL)', 48, 0, NULL, NULL, 1, '2024-08-09 23:45:47', '2024-08-09 23:46:12'),
(234, 'M0204', 66, 20, 'WORX 1 SIZE LABEL (XXL)', 48, 0, NULL, NULL, 1, '2024-08-09 23:46:43', '2024-08-09 23:46:43'),
(235, 'M0205', 66, 33, 'RUBBER STRAP FORCE 360', 47, 0, NULL, NULL, 1, '2024-08-09 23:47:42', '2024-08-09 23:47:42'),
(236, 'M0206', 66, 17, 'GREY NEOPRENE (NYLON) 3MM', 109, 0, NULL, NULL, 1, '2024-08-10 00:21:19', '2024-08-10 00:21:19'),
(237, 'M0207', 66, 20, 'WORX-2 SIZE LABEL (S)', 48, 0, NULL, NULL, 1, '2024-08-10 00:22:14', '2024-08-10 00:22:14'),
(238, 'M0208', 66, 20, 'WORX-2 SIZE LABEL (M)', 48, 0, NULL, NULL, 1, '2024-08-10 00:22:53', '2024-08-10 00:22:53'),
(239, 'M0209', 66, 20, 'WORX-2 SIZE LABEL (L)', 48, 0, NULL, NULL, 1, '2024-08-10 00:23:15', '2024-08-10 00:23:15'),
(240, 'M0210', 66, 20, 'WORX-2 SIZE LABEL (XL)', 48, 0, NULL, NULL, 1, '2024-08-10 00:23:40', '2024-08-10 00:23:40'),
(241, 'M0211', 66, 20, 'WORX-2 SIZE LABEL (XXL)', 48, 0, NULL, NULL, 1, '2024-08-10 00:24:17', '2024-08-10 00:24:17'),
(242, 'M0212', 66, 20, 'WORX-3 SIZE LABEL (S)', 48, 0, NULL, NULL, 1, '2024-08-10 00:57:34', '2024-08-10 00:57:34'),
(243, 'M0213', 66, 20, 'WORX-3 SIZE LABEL (M)', 48, 0, NULL, NULL, 1, '2024-08-10 00:58:05', '2024-08-10 00:58:05'),
(244, 'M0214', 66, 20, 'WORX-3 SIZE LABEL (L)', 48, 0, NULL, NULL, 1, '2024-08-10 00:58:33', '2024-08-10 00:58:33'),
(245, 'M0215', 66, 20, 'WORX-3 SIZE LABEL (XL)', 48, 0, NULL, NULL, 1, '2024-08-10 00:59:00', '2024-08-10 00:59:00'),
(246, 'M0216', 66, 20, 'WORX-3 SIZE LABEL (XXL)', 48, 0, NULL, NULL, 1, '2024-08-10 00:59:29', '2024-08-10 00:59:29'),
(247, 'M0217', 66, 21, 'BLACK SG SYNTHENTIC LEATHER', 49, 0, NULL, NULL, 1, '2024-08-10 01:31:53', '2024-08-10 01:31:53'),
(248, 'M0218', 66, 20, 'WORX-4 SIZE LABEL (S)', 48, 0, NULL, NULL, 1, '2024-08-10 01:32:36', '2024-08-10 01:32:36'),
(249, 'M0219', 66, 20, 'WORX-4 SIZE LABEL (M)', 48, 0, NULL, NULL, 1, '2024-08-10 01:33:08', '2024-08-10 01:33:08'),
(250, 'M0220', 66, 20, 'WORX-4 SIZE LABEL (L)', 48, 0, NULL, NULL, 1, '2024-08-10 01:33:28', '2024-08-10 01:33:28'),
(251, 'M0221', 66, 20, 'WORX-4 SIZE LABEL (XL)', 48, 0, NULL, NULL, 1, '2024-08-10 01:33:53', '2024-08-10 01:33:53'),
(252, 'M0222', 66, 20, 'WORX-4 SIZE LABEL (XXL)', 48, 0, NULL, NULL, 1, '2024-08-10 01:34:22', '2024-08-10 01:34:22'),
(253, 'M0223', 61, 46, 'CARTON FOR GX (40*32*30)', 52, 0, NULL, NULL, 1, '2024-08-10 01:45:09', '2024-08-10 02:33:57'),
(254, 'M0224', 66, 21, 'GREY SYNTHETIC LEATHER', 49, 0, NULL, NULL, 1, '2024-08-10 02:12:22', '2024-08-10 02:12:22'),
(255, 'M0225', 66, 31, 'WHITE ELASTIC 1CM', 50, 0, NULL, NULL, 1, '2024-08-10 02:13:18', '2024-08-10 02:13:18'),
(256, 'M0226', 66, 20, 'WORX-5 SIZE LABEL (S)', 48, 0, NULL, NULL, 1, '2024-08-10 02:14:04', '2024-08-10 02:14:04'),
(257, 'M0227', 66, 20, 'WORX-5 SIZE LABEL (M)', 48, 0, NULL, NULL, 1, '2024-08-10 02:14:29', '2024-08-10 02:14:29'),
(258, 'M0228', 66, 20, 'WORX-5 SIZE LABEL (L)', 48, 0, NULL, NULL, 1, '2024-08-10 02:14:52', '2024-08-10 02:14:52'),
(259, 'M0229', 66, 20, 'WORX-5 SIZE LABEL (XL)', 48, 0, NULL, NULL, 1, '2024-08-10 02:15:22', '2024-08-10 02:15:22'),
(260, 'M0230', 66, 20, 'WORX-5 SIZE LABEL (XXL)', 48, 0, NULL, NULL, 1, '2024-08-10 02:15:44', '2024-08-10 02:15:44');

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
(5, '2014_10_12_000000_create_roles_table', 2);

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
(29, 10, '174|173|172|171|170|169|159|158|149|100|95|86|78|77|76|75|71|70|66|63|34|29', '72|133|74|134|135|132|73', 'GFPRMX-1', 'Optima Mechanics Gloves', NULL, 47, 1, 1, '2024-08-08 06:06:18', '2024-08-08 07:52:56'),
(30, 10, '181|180|179|178|177|176|174|161|159|150|100|95|86|77|76|75|71|66|63|40|30', '72|133|74|134|135|132|73', 'GFPRMX-2', 'OPTIMA HI-VIS MECHANICS GLOVE', NULL, 47, 1, 1, '2024-08-08 23:28:20', '2024-08-09 00:48:33'),
(31, 10, '187|186|185|184|183|182|174|162|158|149|107|100|95|86|78|77|76|75|66|63|34|29', '72|133|74|134|135|132|73', 'GFPRMX-3', 'ARMOUR MECHANICS GLOVE', NULL, 47, 1, 1, '2024-08-09 01:27:10', '2024-08-09 01:37:49'),
(32, 10, '192|191|190|189|188|174|163|159|158|149|117|100|95|86|78|77|76|75|66|63|34|29', '72|133|74|134|135|132|73', 'GFPRMX-4', 'VIBE CONTROL MECHANICS GLOVE', NULL, 47, 1, 1, '2024-08-09 02:13:38', '2024-08-09 02:17:54'),
(33, 10, '199|198|197|196|195|194|174|164|158|149|100|95|86|83|77|76|75|66|63|36|29', '72|133|74|134|135|132|73', 'GFPRMX-5', 'BLADE 5 MECHANICS GLOVE', NULL, 47, 1, 1, '2024-08-09 03:11:05', '2024-08-09 03:20:25'),
(34, 10, '204|203|202|201|200|174|159|158|149|114|104|100|86|77|76|75|53|30|29', '72|133|74|134|135|132|73', 'GFPRMX-8', 'TRADIE FAST FIT MECHANICS GLOVE', NULL, 47, 1, 1, '2024-08-09 05:22:26', '2024-08-09 05:38:02'),
(35, 10, '213|212|211|210|209|208|207|206|205|176|174|166|149|86|75|64|62|58|56|29', '72|133|74|134|135|132|73', 'GFPRMX-11', 'PREDATOR GOATSKIN MECHANICS GLOVE', NULL, 47, 1, 1, '2024-08-09 06:07:27', '2024-08-09 06:23:58'),
(36, 10, '218|217|216|215|214|208|207|206|205|176|174|167|149|112|95|86|75|64|62|58|56|29', '72|133|74|134|135|132|73', 'GFPRMX-12', 'PREDATOR GOATSKIN WINTER MECHANICS GLOVE', NULL, 52, 1, 1, '2024-08-09 06:56:55', '2024-08-09 07:01:20'),
(37, 10, '226|225|224|223|222|221|220|219|208|174|158|117|108|107|105|103|95|92|91|86|57|29', '72|133|74|134|135|132|73', 'GFPRMX-30', 'EVOLUTION RIGGER CUT 5 MECHANICS GLOVE', NULL, 47, 1, 1, '2024-08-09 08:01:52', '2024-08-09 08:07:51'),
(38, 10, '235|234|233|228|227|174|158|149|100|95|86|77|76|75|72|71|64|62|58|32', '72|133|74|134|135|132|73', 'GWORX-1', 'ORIGINAL MECHANICS GLOVE', NULL, 47, 1, 1, '2024-08-09 23:38:38', '2024-08-09 23:48:44'),
(39, 10, '241|240|239|238|237|236|176|174|159|150|94|78|77|75|71|69|64|62|40', '72|133|74|134|135|132|73', 'GWORX-2', 'ORIGINAL MECHANICS GLOVE', NULL, 47, 1, 1, '2024-08-10 00:19:35', '2024-08-10 00:29:48'),
(40, 10, '246|245|244|243|242|174|159|158|149|100|86|77|76|75|71|69|64|62|58|32', '72|133|74|134|135|132|73', 'GWORX-3', 'ORIGINAL MECHANICS FINGERLESS GLOVE', NULL, 47, 1, 1, '2024-08-10 00:56:34', '2024-08-10 01:00:23'),
(41, 10, '252|251|250|249|248|247|174|102|100|95|86|76|75|58|32|29', '72|133|74|134|135|132|73', 'GWORX-4', 'ORIGINAL FAST FIT MECHANICS GLOVE', NULL, 47, 1, 1, '2024-08-10 01:30:21', '2024-08-10 01:35:31'),
(42, 10, '260|259|258|257|256|255|254|174|94|78|76|59|40', '72|133|74|134|135|132|73', 'GWORX-5', 'ORIGINAL FAST FIT HI VIS MECHANICS GLOVE', NULL, 47, 1, 1, '2024-08-10 02:11:24', '2024-08-10 02:17:01');

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
(543, 105, 29, 0.036, 1, '2024-08-08 23:36:47', '2024-08-08 23:36:47'),
(544, 105, 34, 0.034, 1, '2024-08-08 23:36:47', '2024-08-08 23:36:47'),
(545, 105, 63, 0.061, 1, '2024-08-08 23:36:47', '2024-08-08 23:36:47'),
(546, 105, 66, 0.069, 1, '2024-08-08 23:36:47', '2024-08-08 23:36:47'),
(547, 105, 70, 1, 1, '2024-08-08 23:36:47', '2024-08-08 23:36:47'),
(548, 105, 71, 0.005, 1, '2024-08-08 23:36:47', '2024-08-08 23:36:47'),
(549, 105, 75, 0.0014, 1, '2024-08-08 23:36:47', '2024-08-08 23:36:47'),
(550, 105, 76, 0.0009, 1, '2024-08-08 23:36:47', '2024-08-08 23:36:47'),
(551, 105, 77, 0.0014, 1, '2024-08-08 23:36:47', '2024-08-08 23:36:47'),
(552, 105, 78, 0.0014, 1, '2024-08-08 23:36:47', '2024-08-08 23:36:47'),
(553, 105, 86, 0.0018, 1, '2024-08-08 23:36:47', '2024-08-08 23:36:47'),
(554, 105, 95, 0.01, 1, '2024-08-08 23:36:47', '2024-08-08 23:36:47'),
(555, 105, 100, 0.02, 1, '2024-08-08 23:36:47', '2024-08-08 23:36:47'),
(556, 105, 149, 0.0014, 1, '2024-08-08 23:36:47', '2024-08-08 23:36:47'),
(557, 105, 158, 0.083, 1, '2024-08-08 23:36:47', '2024-08-08 23:36:47'),
(558, 105, 159, 0.01, 1, '2024-08-08 23:36:47', '2024-08-08 23:36:47'),
(559, 105, 169, 2, 1, '2024-08-08 23:36:47', '2024-08-08 23:36:47'),
(560, 105, 170, 0, 1, '2024-08-08 23:36:47', '2024-08-08 23:36:47'),
(561, 105, 171, 0, 1, '2024-08-08 23:36:47', '2024-08-08 23:36:47'),
(562, 105, 172, 0, 1, '2024-08-08 23:36:47', '2024-08-08 23:36:47'),
(563, 105, 173, 0, 1, '2024-08-08 23:36:47', '2024-08-08 23:36:47'),
(564, 105, 174, 2, 1, '2024-08-08 23:36:48', '2024-08-08 23:36:48'),
(565, 105, 175, 0.013888888888888888, 1, '2024-08-08 23:36:48', '2024-08-08 23:36:48'),
(566, 104, 29, 0.039, 1, '2024-08-08 23:52:20', '2024-08-08 23:52:20'),
(567, 104, 34, 0.034, 1, '2024-08-08 23:52:20', '2024-08-08 23:52:20'),
(568, 104, 63, 0.061, 1, '2024-08-08 23:52:20', '2024-08-08 23:52:20'),
(569, 104, 66, 0.069, 1, '2024-08-08 23:52:20', '2024-08-08 23:52:20'),
(570, 104, 70, 1, 1, '2024-08-08 23:52:20', '2024-08-08 23:52:20'),
(571, 104, 71, 0.005, 1, '2024-08-08 23:52:21', '2024-08-08 23:52:21'),
(572, 104, 75, 0.0014, 1, '2024-08-08 23:52:21', '2024-08-08 23:52:21'),
(573, 104, 76, 0.0009, 1, '2024-08-08 23:52:21', '2024-08-08 23:52:21'),
(574, 104, 77, 0.0014, 1, '2024-08-08 23:52:21', '2024-08-08 23:52:21'),
(575, 104, 78, 0.0014, 1, '2024-08-08 23:52:21', '2024-08-08 23:52:21'),
(576, 104, 86, 0.0018, 1, '2024-08-08 23:52:21', '2024-08-08 23:52:21'),
(577, 104, 95, 0.001, 1, '2024-08-08 23:52:21', '2024-08-08 23:52:21'),
(578, 104, 100, 0.02, 1, '2024-08-08 23:52:21', '2024-08-08 23:52:21'),
(579, 104, 149, 0.1, 1, '2024-08-08 23:52:21', '2024-08-08 23:52:21'),
(580, 104, 158, 0.083, 1, '2024-08-08 23:52:21', '2024-08-08 23:52:21'),
(581, 104, 159, 0.01, 1, '2024-08-08 23:52:21', '2024-08-08 23:52:21'),
(582, 104, 169, 0, 1, '2024-08-08 23:52:21', '2024-08-08 23:52:21'),
(583, 104, 170, 2, 1, '2024-08-08 23:52:21', '2024-08-08 23:52:21'),
(584, 104, 171, 0, 1, '2024-08-08 23:52:21', '2024-08-08 23:52:21'),
(585, 104, 172, 0, 1, '2024-08-08 23:52:21', '2024-08-08 23:52:21'),
(586, 104, 173, 0, 1, '2024-08-08 23:52:21', '2024-08-08 23:52:21'),
(587, 104, 174, 2, 1, '2024-08-08 23:52:21', '2024-08-08 23:52:21'),
(588, 104, 175, 0.013888888888888888, 1, '2024-08-08 23:52:21', '2024-08-08 23:52:21'),
(589, 103, 29, 0.043, 1, '2024-08-08 23:57:04', '2024-08-08 23:57:04'),
(590, 103, 34, 0.036, 1, '2024-08-08 23:57:04', '2024-08-08 23:57:04'),
(591, 103, 63, 0.61, 1, '2024-08-08 23:57:04', '2024-08-08 23:57:04'),
(592, 103, 66, 0.069, 1, '2024-08-08 23:57:04', '2024-08-08 23:57:04'),
(593, 103, 70, 1, 1, '2024-08-08 23:57:05', '2024-08-08 23:57:05'),
(594, 103, 71, 0.005, 1, '2024-08-08 23:57:05', '2024-08-08 23:57:05'),
(595, 103, 75, 0.0014, 1, '2024-08-08 23:57:05', '2024-08-08 23:57:05'),
(596, 103, 76, 0.0009, 1, '2024-08-08 23:57:05', '2024-08-08 23:57:05'),
(597, 103, 77, 0.0014, 1, '2024-08-08 23:57:05', '2024-08-08 23:57:05'),
(598, 103, 78, 0.0014, 1, '2024-08-08 23:57:05', '2024-08-08 23:57:05'),
(599, 103, 86, 0.0018, 1, '2024-08-08 23:57:05', '2024-08-08 23:57:05'),
(600, 103, 95, 0.01, 1, '2024-08-08 23:57:05', '2024-08-08 23:57:05'),
(601, 103, 100, 0.02, 1, '2024-08-08 23:57:05', '2024-08-08 23:57:05'),
(602, 103, 149, 0.001, 1, '2024-08-08 23:57:05', '2024-08-08 23:57:05'),
(603, 103, 158, 0.098, 1, '2024-08-08 23:57:05', '2024-08-08 23:57:05'),
(604, 103, 159, 0.01, 1, '2024-08-08 23:57:05', '2024-08-08 23:57:05'),
(605, 103, 169, 0, 1, '2024-08-08 23:57:05', '2024-08-08 23:57:05'),
(606, 103, 170, 0, 1, '2024-08-08 23:57:05', '2024-08-08 23:57:05'),
(607, 103, 171, 2, 1, '2024-08-08 23:57:05', '2024-08-08 23:57:05'),
(608, 103, 172, 0, 1, '2024-08-08 23:57:05', '2024-08-08 23:57:05'),
(609, 103, 173, 0, 1, '2024-08-08 23:57:05', '2024-08-08 23:57:05'),
(610, 103, 174, 2, 1, '2024-08-08 23:57:05', '2024-08-08 23:57:05'),
(611, 103, 175, 0.013888888888888888, 1, '2024-08-08 23:57:05', '2024-08-08 23:57:05'),
(612, 106, 29, 0.045, 1, '2024-08-09 00:04:13', '2024-08-09 00:04:13'),
(613, 106, 34, 0.049, 1, '2024-08-09 00:04:13', '2024-08-09 00:04:13'),
(614, 106, 63, 0.061, 1, '2024-08-09 00:04:13', '2024-08-09 00:04:13'),
(615, 106, 66, 0.069, 1, '2024-08-09 00:04:13', '2024-08-09 00:04:13'),
(616, 106, 70, 1, 1, '2024-08-09 00:04:13', '2024-08-09 00:04:13'),
(617, 106, 71, 0.005, 1, '2024-08-09 00:04:13', '2024-08-09 00:04:13'),
(618, 106, 75, 0.0014, 1, '2024-08-09 00:04:13', '2024-08-09 00:04:13'),
(619, 106, 76, 0.0009, 1, '2024-08-09 00:04:14', '2024-08-09 00:04:14'),
(620, 106, 77, 0.0014, 1, '2024-08-09 00:04:14', '2024-08-09 00:04:14'),
(621, 106, 78, 0.0014, 1, '2024-08-09 00:04:14', '2024-08-09 00:04:14'),
(622, 106, 86, 0.0018, 1, '2024-08-09 00:04:14', '2024-08-09 00:04:14'),
(623, 106, 95, 0.01, 1, '2024-08-09 00:04:14', '2024-08-09 00:04:14'),
(624, 106, 100, 0.02, 1, '2024-08-09 00:04:14', '2024-08-09 00:04:14'),
(625, 106, 149, 0.001, 1, '2024-08-09 00:04:14', '2024-08-09 00:04:14'),
(626, 106, 158, 0.111, 1, '2024-08-09 00:04:14', '2024-08-09 00:04:14'),
(627, 106, 159, 0.01, 1, '2024-08-09 00:04:14', '2024-08-09 00:04:14'),
(628, 106, 169, 0, 1, '2024-08-09 00:04:14', '2024-08-09 00:04:14'),
(629, 106, 170, 0, 1, '2024-08-09 00:04:14', '2024-08-09 00:04:14'),
(630, 106, 171, 0, 1, '2024-08-09 00:04:14', '2024-08-09 00:04:14'),
(631, 106, 172, 2, 1, '2024-08-09 00:04:14', '2024-08-09 00:04:14'),
(632, 106, 173, 0, 1, '2024-08-09 00:04:14', '2024-08-09 00:04:14'),
(633, 106, 174, 2, 1, '2024-08-09 00:04:14', '2024-08-09 00:04:14'),
(634, 106, 175, 0.013888888888888888, 1, '2024-08-09 00:04:14', '2024-08-09 00:04:14'),
(635, 112, 29, 0.047, 1, '2024-08-09 00:16:55', '2024-08-09 00:16:55'),
(636, 112, 34, 0.051, 1, '2024-08-09 00:16:55', '2024-08-09 00:16:55'),
(637, 112, 63, 0.061, 1, '2024-08-09 00:16:55', '2024-08-09 00:16:55'),
(638, 112, 66, 0.069, 1, '2024-08-09 00:16:55', '2024-08-09 00:16:55'),
(639, 112, 70, 1, 1, '2024-08-09 00:16:55', '2024-08-09 00:16:55'),
(640, 112, 71, 0.0053, 1, '2024-08-09 00:16:55', '2024-08-09 00:16:55'),
(641, 112, 75, 0.0014, 1, '2024-08-09 00:16:55', '2024-08-09 00:16:55'),
(642, 112, 76, 0.0009, 1, '2024-08-09 00:16:55', '2024-08-09 00:16:55'),
(643, 112, 77, 0.0014, 1, '2024-08-09 00:16:55', '2024-08-09 00:16:55'),
(644, 112, 78, 0.0014, 1, '2024-08-09 00:16:55', '2024-08-09 00:16:55'),
(645, 112, 86, 0.0018, 1, '2024-08-09 00:16:55', '2024-08-09 00:16:55'),
(646, 112, 95, 0.01, 1, '2024-08-09 00:16:55', '2024-08-09 00:16:55'),
(647, 112, 100, 0.02, 1, '2024-08-09 00:16:55', '2024-08-09 00:16:55'),
(648, 112, 149, 0.001, 1, '2024-08-09 00:16:55', '2024-08-09 00:16:55'),
(649, 112, 158, 0.12, 1, '2024-08-09 00:16:55', '2024-08-09 00:16:55'),
(650, 112, 159, 0.01, 1, '2024-08-09 00:16:56', '2024-08-09 00:16:56'),
(651, 112, 169, 0, 1, '2024-08-09 00:16:56', '2024-08-09 00:16:56'),
(652, 112, 170, 0, 1, '2024-08-09 00:16:56', '2024-08-09 00:16:56'),
(653, 112, 171, 0, 1, '2024-08-09 00:16:56', '2024-08-09 00:16:56'),
(654, 112, 172, 0, 1, '2024-08-09 00:16:56', '2024-08-09 00:16:56'),
(655, 112, 173, 2, 1, '2024-08-09 00:16:56', '2024-08-09 00:16:56'),
(656, 112, 174, 2, 1, '2024-08-09 00:16:56', '2024-08-09 00:16:56'),
(657, 112, 175, 0.013888888888888888, 1, '2024-08-09 00:16:56', '2024-08-09 00:16:56'),
(658, 109, 30, 0.0125, 1, '2024-08-09 00:42:45', '2024-08-09 00:42:45'),
(659, 109, 40, 0.07, 1, '2024-08-09 00:42:46', '2024-08-09 00:42:46'),
(660, 109, 63, 0.061, 1, '2024-08-09 00:42:46', '2024-08-09 00:42:46'),
(661, 109, 66, 0.069, 1, '2024-08-09 00:42:46', '2024-08-09 00:42:46'),
(662, 109, 71, 0.005, 1, '2024-08-09 00:42:46', '2024-08-09 00:42:46'),
(663, 109, 75, 0.004, 1, '2024-08-09 00:42:46', '2024-08-09 00:42:46'),
(664, 109, 76, 0.0012, 1, '2024-08-09 00:42:46', '2024-08-09 00:42:46'),
(665, 109, 77, 0.003, 1, '2024-08-09 00:42:46', '2024-08-09 00:42:46'),
(666, 109, 86, 0.0015, 1, '2024-08-09 00:42:46', '2024-08-09 00:42:46'),
(667, 109, 95, 0.01, 1, '2024-08-09 00:42:46', '2024-08-09 00:42:46'),
(668, 109, 100, 0.02, 1, '2024-08-09 00:42:46', '2024-08-09 00:42:46'),
(669, 109, 150, 0.02, 1, '2024-08-09 00:42:46', '2024-08-09 00:42:46'),
(670, 109, 159, 0.094, 1, '2024-08-09 00:42:46', '2024-08-09 00:42:46'),
(671, 109, 161, 2, 1, '2024-08-09 00:42:46', '2024-08-09 00:42:46'),
(672, 109, 174, 2, 1, '2024-08-09 00:42:46', '2024-08-09 00:42:46'),
(673, 109, 176, 0.381, 1, '2024-08-09 00:42:46', '2024-08-09 00:42:46'),
(674, 109, 177, 2, 1, '2024-08-09 00:42:46', '2024-08-09 00:42:46'),
(675, 109, 175, 0.013888888888888888, 1, '2024-08-09 00:42:46', '2024-08-09 00:42:46'),
(676, 108, 30, 0.0127, 1, '2024-08-09 00:54:44', '2024-08-09 00:54:44'),
(677, 108, 40, 0.08, 1, '2024-08-09 00:54:44', '2024-08-09 00:54:44'),
(678, 108, 63, 0.061, 1, '2024-08-09 00:54:44', '2024-08-09 00:54:44'),
(679, 108, 66, 0.069, 1, '2024-08-09 00:54:44', '2024-08-09 00:54:44'),
(680, 108, 71, 0.005, 1, '2024-08-09 00:54:44', '2024-08-09 00:54:44'),
(681, 108, 75, 0.053, 1, '2024-08-09 00:54:44', '2024-08-09 00:54:44'),
(682, 108, 76, 0.025, 1, '2024-08-09 00:54:44', '2024-08-09 00:54:44'),
(683, 108, 77, 0.025, 1, '2024-08-09 00:54:44', '2024-08-09 00:54:44'),
(684, 108, 86, 0.0014, 1, '2024-08-09 00:54:44', '2024-08-09 00:54:44'),
(685, 108, 95, 0.01, 1, '2024-08-09 00:54:44', '2024-08-09 00:54:44'),
(686, 108, 100, 0.02, 1, '2024-08-09 00:54:44', '2024-08-09 00:54:44'),
(687, 108, 150, 0.02, 1, '2024-08-09 00:54:44', '2024-08-09 00:54:44'),
(688, 108, 159, 0.101, 1, '2024-08-09 00:54:44', '2024-08-09 00:54:44'),
(689, 108, 161, 2, 1, '2024-08-09 00:54:44', '2024-08-09 00:54:44'),
(690, 108, 174, 2, 1, '2024-08-09 00:54:44', '2024-08-09 00:54:44'),
(691, 108, 176, 0.4, 1, '2024-08-09 00:54:44', '2024-08-09 00:54:44'),
(692, 108, 177, 0, 1, '2024-08-09 00:54:44', '2024-08-09 00:54:44'),
(693, 108, 178, 2, 1, '2024-08-09 00:54:44', '2024-08-09 00:54:44'),
(694, 108, 179, 0, 1, '2024-08-09 00:54:44', '2024-08-09 00:54:44'),
(695, 108, 180, 0, 1, '2024-08-09 00:54:44', '2024-08-09 00:54:44'),
(696, 108, 181, 0, 1, '2024-08-09 00:54:44', '2024-08-09 00:54:44'),
(697, 108, 175, 0.013888888888888888, 1, '2024-08-09 00:54:45', '2024-08-09 00:54:45'),
(698, 107, 30, 0.013, 1, '2024-08-09 01:00:55', '2024-08-09 01:00:55'),
(699, 107, 40, 0.083, 1, '2024-08-09 01:00:55', '2024-08-09 01:00:55'),
(700, 107, 63, 0.069, 1, '2024-08-09 01:00:55', '2024-08-09 01:00:55'),
(701, 107, 66, 0.061, 1, '2024-08-09 01:00:55', '2024-08-09 01:00:55'),
(702, 107, 71, 0.005, 1, '2024-08-09 01:00:55', '2024-08-09 01:00:55'),
(703, 107, 75, 0.053, 1, '2024-08-09 01:00:55', '2024-08-09 01:00:55'),
(704, 107, 76, 0.025, 1, '2024-08-09 01:00:55', '2024-08-09 01:00:55'),
(705, 107, 77, 0.025, 1, '2024-08-09 01:00:55', '2024-08-09 01:00:55'),
(706, 107, 86, 0.0014, 1, '2024-08-09 01:00:55', '2024-08-09 01:00:55'),
(707, 107, 95, 0.01, 1, '2024-08-09 01:00:55', '2024-08-09 01:00:55'),
(708, 107, 100, 0.02, 1, '2024-08-09 01:00:55', '2024-08-09 01:00:55'),
(709, 107, 150, 0.02, 1, '2024-08-09 01:00:55', '2024-08-09 01:00:55'),
(710, 107, 159, 0.108, 1, '2024-08-09 01:00:55', '2024-08-09 01:00:55'),
(711, 107, 161, 2, 1, '2024-08-09 01:00:55', '2024-08-09 01:00:55'),
(712, 107, 174, 2, 1, '2024-08-09 01:00:56', '2024-08-09 01:00:56'),
(713, 107, 176, 0.4, 1, '2024-08-09 01:00:56', '2024-08-09 01:00:56'),
(714, 107, 177, 0, 1, '2024-08-09 01:00:56', '2024-08-09 01:00:56'),
(715, 107, 178, 0, 1, '2024-08-09 01:00:56', '2024-08-09 01:00:56'),
(716, 107, 179, 2, 1, '2024-08-09 01:00:56', '2024-08-09 01:00:56'),
(717, 107, 180, 0, 1, '2024-08-09 01:00:56', '2024-08-09 01:00:56'),
(718, 107, 181, 0, 1, '2024-08-09 01:00:56', '2024-08-09 01:00:56'),
(719, 107, 175, 0.013888888888888888, 1, '2024-08-09 01:00:56', '2024-08-09 01:00:56'),
(720, 110, 30, 0.013, 1, '2024-08-09 01:06:20', '2024-08-09 01:06:20'),
(721, 110, 40, 0.095, 1, '2024-08-09 01:06:21', '2024-08-09 01:06:21'),
(722, 110, 63, 0.061, 1, '2024-08-09 01:06:21', '2024-08-09 01:06:21'),
(723, 110, 66, 0.069, 1, '2024-08-09 01:06:21', '2024-08-09 01:06:21'),
(724, 110, 71, 0.005, 1, '2024-08-09 01:06:21', '2024-08-09 01:06:21'),
(725, 110, 75, 0.053, 1, '2024-08-09 01:06:21', '2024-08-09 01:06:21'),
(726, 110, 76, 0.025, 1, '2024-08-09 01:06:21', '2024-08-09 01:06:21'),
(727, 110, 77, 0.025, 1, '2024-08-09 01:06:21', '2024-08-09 01:06:21'),
(728, 110, 86, 0.0014, 1, '2024-08-09 01:06:21', '2024-08-09 01:06:21'),
(729, 110, 95, 0.01, 1, '2024-08-09 01:06:21', '2024-08-09 01:06:21'),
(730, 110, 100, 0.02, 1, '2024-08-09 01:06:21', '2024-08-09 01:06:21'),
(731, 110, 150, 0.02, 1, '2024-08-09 01:06:21', '2024-08-09 01:06:21'),
(732, 110, 159, 0.121, 1, '2024-08-09 01:06:21', '2024-08-09 01:06:21'),
(733, 110, 161, 2, 1, '2024-08-09 01:06:21', '2024-08-09 01:06:21'),
(734, 110, 174, 2, 1, '2024-08-09 01:06:21', '2024-08-09 01:06:21'),
(735, 110, 176, 0.4, 1, '2024-08-09 01:06:21', '2024-08-09 01:06:21'),
(736, 110, 177, 0, 1, '2024-08-09 01:06:21', '2024-08-09 01:06:21'),
(737, 110, 178, 0, 1, '2024-08-09 01:06:21', '2024-08-09 01:06:21'),
(738, 110, 179, 0, 1, '2024-08-09 01:06:21', '2024-08-09 01:06:21'),
(739, 110, 180, 2, 1, '2024-08-09 01:06:21', '2024-08-09 01:06:21'),
(740, 110, 181, 0, 1, '2024-08-09 01:06:21', '2024-08-09 01:06:21'),
(741, 110, 175, 0.013888888888888888, 1, '2024-08-09 01:06:21', '2024-08-09 01:06:21'),
(742, 111, 30, 0.0135, 1, '2024-08-09 01:13:49', '2024-08-09 01:13:49'),
(743, 111, 40, 0.097, 1, '2024-08-09 01:13:50', '2024-08-09 01:13:50'),
(744, 111, 63, 0.061, 1, '2024-08-09 01:13:50', '2024-08-09 01:13:50'),
(745, 111, 66, 0.069, 1, '2024-08-09 01:13:50', '2024-08-09 01:13:50'),
(746, 111, 71, 0.005, 1, '2024-08-09 01:13:50', '2024-08-09 01:13:50'),
(747, 111, 75, 0.053, 1, '2024-08-09 01:13:50', '2024-08-09 01:13:50'),
(748, 111, 76, 0.025, 1, '2024-08-09 01:13:50', '2024-08-09 01:13:50'),
(749, 111, 77, 0.025, 1, '2024-08-09 01:13:50', '2024-08-09 01:13:50'),
(750, 111, 86, 0.0014, 1, '2024-08-09 01:13:50', '2024-08-09 01:13:50'),
(751, 111, 95, 0.01, 1, '2024-08-09 01:13:50', '2024-08-09 01:13:50'),
(752, 111, 100, 0.02, 1, '2024-08-09 01:13:50', '2024-08-09 01:13:50'),
(753, 111, 150, 0.02, 1, '2024-08-09 01:13:50', '2024-08-09 01:13:50'),
(754, 111, 159, 0.131, 1, '2024-08-09 01:13:50', '2024-08-09 01:13:50'),
(755, 111, 161, 2, 1, '2024-08-09 01:13:50', '2024-08-09 01:13:50'),
(756, 111, 174, 2, 1, '2024-08-09 01:13:51', '2024-08-09 01:13:51'),
(757, 111, 176, 0.4, 1, '2024-08-09 01:13:51', '2024-08-09 01:13:51'),
(758, 111, 177, 0, 1, '2024-08-09 01:13:51', '2024-08-09 01:13:51'),
(759, 111, 178, 0, 1, '2024-08-09 01:13:51', '2024-08-09 01:13:51'),
(760, 111, 179, 0, 1, '2024-08-09 01:13:51', '2024-08-09 01:13:51'),
(761, 111, 180, 0, 1, '2024-08-09 01:13:51', '2024-08-09 01:13:51'),
(762, 111, 181, 2, 1, '2024-08-09 01:13:51', '2024-08-09 01:13:51'),
(763, 111, 175, 0.013888888888888888, 1, '2024-08-09 01:13:51', '2024-08-09 01:13:51'),
(764, 115, 29, 0.042, 1, '2024-08-09 01:41:58', '2024-08-09 01:41:58'),
(765, 115, 34, 0.034, 1, '2024-08-09 01:41:58', '2024-08-09 01:41:58'),
(766, 115, 63, 0.061, 1, '2024-08-09 01:41:58', '2024-08-09 01:41:58'),
(767, 115, 66, 0.069, 1, '2024-08-09 01:41:58', '2024-08-09 01:41:58'),
(768, 115, 75, 0.002, 1, '2024-08-09 01:41:58', '2024-08-09 01:41:58'),
(769, 115, 76, 0.0014, 1, '2024-08-09 01:41:58', '2024-08-09 01:41:58'),
(770, 115, 77, 0.002, 1, '2024-08-09 01:41:58', '2024-08-09 01:41:58'),
(771, 115, 78, 0.002, 1, '2024-08-09 01:41:58', '2024-08-09 01:41:58'),
(772, 115, 86, 0.0025, 1, '2024-08-09 01:41:58', '2024-08-09 01:41:58'),
(773, 115, 95, 0.01, 1, '2024-08-09 01:41:58', '2024-08-09 01:41:58'),
(774, 115, 100, 0.02, 1, '2024-08-09 01:41:58', '2024-08-09 01:41:58'),
(775, 115, 107, 0.015, 1, '2024-08-09 01:41:58', '2024-08-09 01:41:58'),
(776, 115, 149, 0.002, 1, '2024-08-09 01:41:59', '2024-08-09 01:41:59'),
(777, 115, 158, 0.071, 1, '2024-08-09 01:41:59', '2024-08-09 01:41:59'),
(778, 115, 162, 2, 1, '2024-08-09 01:41:59', '2024-08-09 01:41:59'),
(779, 115, 174, 2, 1, '2024-08-09 01:41:59', '2024-08-09 01:41:59'),
(780, 115, 182, 2, 1, '2024-08-09 01:41:59', '2024-08-09 01:41:59'),
(781, 115, 183, 2, 1, '2024-08-09 01:41:59', '2024-08-09 01:41:59'),
(782, 115, 184, 0, 1, '2024-08-09 01:41:59', '2024-08-09 01:41:59'),
(783, 115, 185, 0, 1, '2024-08-09 01:41:59', '2024-08-09 01:41:59'),
(784, 115, 186, 0, 1, '2024-08-09 01:41:59', '2024-08-09 01:41:59'),
(785, 115, 187, 0, 1, '2024-08-09 01:41:59', '2024-08-09 01:41:59'),
(787, 114, 29, 0.044, 1, '2024-08-09 01:48:33', '2024-08-09 01:48:33'),
(788, 114, 34, 0.036, 1, '2024-08-09 01:48:33', '2024-08-09 01:48:33'),
(789, 114, 63, 0.061, 1, '2024-08-09 01:48:33', '2024-08-09 01:48:33'),
(790, 114, 66, 0.069, 1, '2024-08-09 01:48:33', '2024-08-09 01:48:33'),
(791, 114, 75, 0.002, 1, '2024-08-09 01:48:33', '2024-08-09 01:48:33'),
(792, 114, 76, 0.0014, 1, '2024-08-09 01:48:33', '2024-08-09 01:48:33'),
(793, 114, 77, 0.002, 1, '2024-08-09 01:48:33', '2024-08-09 01:48:33'),
(794, 114, 78, 0.002, 1, '2024-08-09 01:48:33', '2024-08-09 01:48:33'),
(795, 114, 86, 0.0025, 1, '2024-08-09 01:48:33', '2024-08-09 01:48:33'),
(796, 114, 95, 0.01, 1, '2024-08-09 01:48:33', '2024-08-09 01:48:33'),
(797, 114, 100, 0.02, 1, '2024-08-09 01:48:34', '2024-08-09 01:48:34'),
(798, 114, 107, 0.015, 1, '2024-08-09 01:48:34', '2024-08-09 01:48:34'),
(799, 114, 149, 0.002, 1, '2024-08-09 01:48:34', '2024-08-09 01:48:34'),
(800, 114, 158, 0.076, 1, '2024-08-09 01:48:34', '2024-08-09 01:48:34'),
(801, 114, 162, 2, 1, '2024-08-09 01:48:34', '2024-08-09 01:48:34'),
(802, 114, 174, 2, 1, '2024-08-09 01:48:34', '2024-08-09 01:48:34'),
(803, 114, 182, 2, 1, '2024-08-09 01:48:34', '2024-08-09 01:48:34'),
(804, 114, 183, 0, 1, '2024-08-09 01:48:34', '2024-08-09 01:48:34'),
(805, 114, 184, 2, 1, '2024-08-09 01:48:34', '2024-08-09 01:48:34'),
(806, 114, 185, 0, 1, '2024-08-09 01:48:34', '2024-08-09 01:48:34'),
(807, 114, 186, 0, 1, '2024-08-09 01:48:34', '2024-08-09 01:48:34'),
(808, 114, 187, 0, 1, '2024-08-09 01:48:34', '2024-08-09 01:48:34'),
(810, 113, 29, 0.044, 1, '2024-08-09 01:54:10', '2024-08-09 01:54:10'),
(811, 113, 34, 0.039, 1, '2024-08-09 01:54:10', '2024-08-09 01:54:10'),
(812, 113, 63, 0.061, 1, '2024-08-09 01:54:10', '2024-08-09 01:54:10'),
(813, 113, 66, 0.069, 1, '2024-08-09 01:54:10', '2024-08-09 01:54:10'),
(814, 113, 75, 0.002, 1, '2024-08-09 01:54:10', '2024-08-09 01:54:10'),
(815, 113, 76, 0.0014, 1, '2024-08-09 01:54:10', '2024-08-09 01:54:10'),
(816, 113, 77, 0.002, 1, '2024-08-09 01:54:10', '2024-08-09 01:54:10'),
(817, 113, 78, 0.002, 1, '2024-08-09 01:54:10', '2024-08-09 01:54:10'),
(818, 113, 86, 0.0025, 1, '2024-08-09 01:54:10', '2024-08-09 01:54:10'),
(819, 113, 95, 0.01, 1, '2024-08-09 01:54:10', '2024-08-09 01:54:10'),
(820, 113, 100, 0.02, 1, '2024-08-09 01:54:10', '2024-08-09 01:54:10'),
(821, 113, 107, 0.016, 1, '2024-08-09 01:54:10', '2024-08-09 01:54:10'),
(822, 113, 149, 0.002, 1, '2024-08-09 01:54:10', '2024-08-09 01:54:10'),
(823, 113, 158, 0.083, 1, '2024-08-09 01:54:10', '2024-08-09 01:54:10'),
(824, 113, 162, 2, 1, '2024-08-09 01:54:10', '2024-08-09 01:54:10'),
(825, 113, 174, 2, 1, '2024-08-09 01:54:10', '2024-08-09 01:54:10'),
(826, 113, 182, 2, 1, '2024-08-09 01:54:10', '2024-08-09 01:54:10'),
(827, 113, 183, 0, 1, '2024-08-09 01:54:10', '2024-08-09 01:54:10'),
(828, 113, 184, 0, 1, '2024-08-09 01:54:10', '2024-08-09 01:54:10'),
(829, 113, 185, 2, 1, '2024-08-09 01:54:10', '2024-08-09 01:54:10'),
(830, 113, 186, 0, 1, '2024-08-09 01:54:10', '2024-08-09 01:54:10'),
(831, 113, 187, 0, 1, '2024-08-09 01:54:10', '2024-08-09 01:54:10'),
(833, 116, 29, 0.046, 1, '2024-08-09 01:59:37', '2024-08-09 01:59:37'),
(834, 116, 34, 0.042, 1, '2024-08-09 01:59:38', '2024-08-09 01:59:38'),
(835, 116, 63, 0.061, 1, '2024-08-09 01:59:38', '2024-08-09 01:59:38'),
(836, 116, 66, 0.069, 1, '2024-08-09 01:59:38', '2024-08-09 01:59:38'),
(837, 116, 75, 0.002, 1, '2024-08-09 01:59:38', '2024-08-09 01:59:38'),
(838, 116, 76, 0.0014, 1, '2024-08-09 01:59:38', '2024-08-09 01:59:38'),
(839, 116, 77, 0.002, 1, '2024-08-09 01:59:38', '2024-08-09 01:59:38'),
(840, 116, 78, 0.0025, 1, '2024-08-09 01:59:38', '2024-08-09 01:59:38'),
(841, 116, 86, 0.002, 1, '2024-08-09 01:59:38', '2024-08-09 01:59:38'),
(842, 116, 95, 0.012, 1, '2024-08-09 01:59:38', '2024-08-09 01:59:38'),
(843, 116, 100, 0.023, 1, '2024-08-09 01:59:38', '2024-08-09 01:59:38'),
(844, 116, 107, 0.018, 1, '2024-08-09 01:59:38', '2024-08-09 01:59:38'),
(845, 116, 149, 0.002, 1, '2024-08-09 01:59:38', '2024-08-09 01:59:38'),
(846, 116, 158, 0.092, 1, '2024-08-09 01:59:39', '2024-08-09 01:59:39'),
(847, 116, 162, 2, 1, '2024-08-09 01:59:39', '2024-08-09 01:59:39'),
(848, 116, 174, 2, 1, '2024-08-09 01:59:39', '2024-08-09 01:59:39'),
(849, 116, 182, 2, 1, '2024-08-09 01:59:39', '2024-08-09 01:59:39'),
(850, 116, 183, 0, 1, '2024-08-09 01:59:39', '2024-08-09 01:59:39'),
(851, 116, 184, 0, 1, '2024-08-09 01:59:39', '2024-08-09 01:59:39'),
(852, 116, 185, 0, 1, '2024-08-09 01:59:39', '2024-08-09 01:59:39'),
(853, 116, 186, 2, 1, '2024-08-09 01:59:39', '2024-08-09 01:59:39'),
(854, 116, 187, 0, 1, '2024-08-09 01:59:39', '2024-08-09 01:59:39'),
(856, 117, 29, 0.048, 1, '2024-08-09 02:05:13', '2024-08-09 02:05:13'),
(857, 117, 34, 0.051, 1, '2024-08-09 02:05:13', '2024-08-09 02:05:13'),
(858, 117, 63, 0.061, 1, '2024-08-09 02:05:13', '2024-08-09 02:05:13'),
(859, 117, 66, 0.069, 1, '2024-08-09 02:05:13', '2024-08-09 02:05:13'),
(860, 117, 75, 0.002, 1, '2024-08-09 02:05:13', '2024-08-09 02:05:13'),
(861, 117, 76, 0.0014, 1, '2024-08-09 02:05:13', '2024-08-09 02:05:13'),
(862, 117, 77, 0.002, 1, '2024-08-09 02:05:13', '2024-08-09 02:05:13'),
(863, 117, 78, 0.002, 1, '2024-08-09 02:05:13', '2024-08-09 02:05:13'),
(864, 117, 86, 0.0025, 1, '2024-08-09 02:05:14', '2024-08-09 02:05:14'),
(865, 117, 95, 0.015, 1, '2024-08-09 02:05:14', '2024-08-09 02:05:14'),
(866, 117, 100, 0.026, 1, '2024-08-09 02:05:14', '2024-08-09 02:05:14'),
(867, 117, 107, 0.021, 1, '2024-08-09 02:05:14', '2024-08-09 02:05:14'),
(868, 117, 149, 0.002, 1, '2024-08-09 02:05:14', '2024-08-09 02:05:14'),
(869, 117, 158, 0.105, 1, '2024-08-09 02:05:14', '2024-08-09 02:05:14'),
(870, 117, 162, 2, 1, '2024-08-09 02:05:14', '2024-08-09 02:05:14'),
(871, 117, 174, 2, 1, '2024-08-09 02:05:14', '2024-08-09 02:05:14'),
(872, 117, 182, 2, 1, '2024-08-09 02:05:14', '2024-08-09 02:05:14'),
(873, 117, 183, 0, 1, '2024-08-09 02:05:14', '2024-08-09 02:05:14'),
(874, 117, 184, 0, 1, '2024-08-09 02:05:14', '2024-08-09 02:05:14'),
(875, 117, 185, 0, 1, '2024-08-09 02:05:14', '2024-08-09 02:05:14'),
(876, 117, 186, 0, 1, '2024-08-09 02:05:14', '2024-08-09 02:05:14'),
(877, 117, 187, 2, 1, '2024-08-09 02:05:14', '2024-08-09 02:05:14'),
(879, 120, 29, 0.04, 1, '2024-08-09 02:24:07', '2024-08-09 02:24:07'),
(880, 120, 34, 0.034, 1, '2024-08-09 02:24:08', '2024-08-09 02:24:08'),
(881, 120, 63, 0.061, 1, '2024-08-09 02:24:08', '2024-08-09 02:24:08'),
(882, 120, 66, 0.069, 1, '2024-08-09 02:24:08', '2024-08-09 02:24:08'),
(883, 120, 75, 0.002, 1, '2024-08-09 02:24:08', '2024-08-09 02:24:08'),
(884, 120, 76, 0.001, 1, '2024-08-09 02:24:08', '2024-08-09 02:24:08'),
(885, 120, 77, 0.0014, 1, '2024-08-09 02:24:08', '2024-08-09 02:24:08'),
(886, 120, 78, 0.0014, 1, '2024-08-09 02:24:08', '2024-08-09 02:24:08'),
(887, 120, 86, 0.0025, 1, '2024-08-09 02:24:08', '2024-08-09 02:24:08'),
(888, 120, 95, 0.01, 1, '2024-08-09 02:24:08', '2024-08-09 02:24:08'),
(889, 120, 100, 0.02, 1, '2024-08-09 02:24:08', '2024-08-09 02:24:08'),
(890, 120, 117, 0.037, 1, '2024-08-09 02:24:08', '2024-08-09 02:24:08'),
(891, 120, 149, 0.002, 1, '2024-08-09 02:24:08', '2024-08-09 02:24:08'),
(892, 120, 158, 0.071, 1, '2024-08-09 02:24:08', '2024-08-09 02:24:08'),
(893, 120, 159, 0.042, 1, '2024-08-09 02:24:08', '2024-08-09 02:24:08'),
(894, 120, 163, 2, 1, '2024-08-09 02:24:08', '2024-08-09 02:24:08'),
(895, 120, 174, 2, 1, '2024-08-09 02:24:08', '2024-08-09 02:24:08'),
(896, 120, 188, 2, 1, '2024-08-09 02:24:08', '2024-08-09 02:24:08'),
(897, 120, 189, 0, 1, '2024-08-09 02:24:08', '2024-08-09 02:24:08'),
(898, 120, 190, 0, 1, '2024-08-09 02:24:08', '2024-08-09 02:24:08'),
(899, 120, 191, 0, 1, '2024-08-09 02:24:09', '2024-08-09 02:24:09'),
(900, 120, 192, 0, 1, '2024-08-09 02:24:09', '2024-08-09 02:24:09'),
(902, 115, 193, 0.013888888888889, 1, '2024-08-09 02:29:11', '2024-08-09 02:29:11'),
(903, 114, 193, 0.013888888888889, 1, '2024-08-09 02:29:39', '2024-08-09 02:29:39'),
(904, 113, 193, 0.013888888888889, 1, '2024-08-09 02:30:01', '2024-08-09 02:30:01'),
(905, 116, 193, 0.013888888888889, 1, '2024-08-09 02:30:16', '2024-08-09 02:30:16'),
(906, 117, 193, 0.013888888888889, 1, '2024-08-09 02:30:33', '2024-08-09 02:30:33'),
(907, 120, 193, 0.013888888888889, 1, '2024-08-09 02:30:53', '2024-08-09 02:30:53'),
(908, 119, 29, 0.042, 1, '2024-08-09 02:37:48', '2024-08-09 02:37:48'),
(909, 119, 34, 0.036, 1, '2024-08-09 02:37:48', '2024-08-09 02:37:48'),
(910, 119, 63, 0.061, 1, '2024-08-09 02:37:48', '2024-08-09 02:37:48'),
(911, 119, 66, 0.069, 1, '2024-08-09 02:37:48', '2024-08-09 02:37:48'),
(912, 119, 75, 0.002, 1, '2024-08-09 02:37:48', '2024-08-09 02:37:48'),
(913, 119, 76, 0.001, 1, '2024-08-09 02:37:48', '2024-08-09 02:37:48'),
(914, 119, 77, 0.0014, 1, '2024-08-09 02:37:48', '2024-08-09 02:37:48'),
(915, 119, 78, 0.0014, 1, '2024-08-09 02:37:49', '2024-08-09 02:37:49'),
(916, 119, 86, 0.0025, 1, '2024-08-09 02:37:49', '2024-08-09 02:37:49'),
(917, 119, 95, 0.01, 1, '2024-08-09 02:37:49', '2024-08-09 02:37:49'),
(918, 119, 100, 0.02, 1, '2024-08-09 02:37:49', '2024-08-09 02:37:49'),
(919, 119, 117, 0.037, 1, '2024-08-09 02:37:49', '2024-08-09 02:37:49'),
(920, 119, 149, 0.02, 1, '2024-08-09 02:37:49', '2024-08-09 02:37:49'),
(921, 119, 158, 0.076, 1, '2024-08-09 02:37:49', '2024-08-09 02:37:49'),
(922, 119, 159, 0.044, 1, '2024-08-09 02:37:49', '2024-08-09 02:37:49'),
(923, 119, 163, 2, 1, '2024-08-09 02:37:49', '2024-08-09 02:37:49'),
(924, 119, 174, 2, 1, '2024-08-09 02:37:49', '2024-08-09 02:37:49'),
(925, 119, 188, 0, 1, '2024-08-09 02:37:49', '2024-08-09 02:37:49'),
(926, 119, 189, 2, 1, '2024-08-09 02:37:49', '2024-08-09 02:37:49'),
(927, 119, 190, 0, 1, '2024-08-09 02:37:49', '2024-08-09 02:37:49'),
(928, 119, 191, 0, 1, '2024-08-09 02:37:49', '2024-08-09 02:37:49'),
(929, 119, 192, 0, 1, '2024-08-09 02:37:49', '2024-08-09 02:37:49'),
(930, 119, 193, 0.013888888888888888, 1, '2024-08-09 02:37:49', '2024-08-09 02:37:49'),
(931, 118, 29, 0.043, 1, '2024-08-09 02:45:50', '2024-08-09 02:45:50'),
(932, 118, 34, 0.039, 1, '2024-08-09 02:45:51', '2024-08-09 02:45:51'),
(933, 118, 63, 0.061, 1, '2024-08-09 02:45:51', '2024-08-09 02:45:51'),
(934, 118, 66, 0.069, 1, '2024-08-09 02:45:51', '2024-08-09 02:45:51'),
(935, 118, 75, 0.002, 1, '2024-08-09 02:45:51', '2024-08-09 02:45:51'),
(936, 118, 76, 0.001, 1, '2024-08-09 02:45:51', '2024-08-09 02:45:51'),
(937, 118, 77, 0.0014, 1, '2024-08-09 02:45:51', '2024-08-09 02:45:51'),
(938, 118, 78, 0.0014, 1, '2024-08-09 02:45:51', '2024-08-09 02:45:51'),
(939, 118, 86, 0.0025, 1, '2024-08-09 02:45:51', '2024-08-09 02:45:51'),
(940, 118, 95, 0.01, 1, '2024-08-09 02:45:51', '2024-08-09 02:45:51'),
(941, 118, 100, 0.02, 1, '2024-08-09 02:45:51', '2024-08-09 02:45:51'),
(942, 118, 117, 0.037, 1, '2024-08-09 02:45:51', '2024-08-09 02:45:51'),
(943, 118, 149, 0.002, 1, '2024-08-09 02:45:51', '2024-08-09 02:45:51'),
(944, 118, 158, 0.083, 1, '2024-08-09 02:45:51', '2024-08-09 02:45:51'),
(945, 118, 159, 0.05, 1, '2024-08-09 02:45:51', '2024-08-09 02:45:51'),
(946, 118, 163, 2, 1, '2024-08-09 02:45:51', '2024-08-09 02:45:51'),
(947, 118, 174, 2, 1, '2024-08-09 02:45:51', '2024-08-09 02:45:51'),
(948, 118, 188, 0, 1, '2024-08-09 02:45:51', '2024-08-09 02:45:51'),
(949, 118, 189, 0, 1, '2024-08-09 02:45:51', '2024-08-09 02:45:51'),
(950, 118, 190, 2, 1, '2024-08-09 02:45:51', '2024-08-09 02:45:51'),
(951, 118, 191, 0, 1, '2024-08-09 02:45:51', '2024-08-09 02:45:51'),
(952, 118, 192, 0, 1, '2024-08-09 02:45:51', '2024-08-09 02:45:51'),
(953, 118, 193, 0.013888888888888888, 1, '2024-08-09 02:45:51', '2024-08-09 02:45:51'),
(954, 121, 29, 0.045, 1, '2024-08-09 02:52:48', '2024-08-09 02:52:48'),
(955, 121, 34, 0.049, 1, '2024-08-09 02:52:48', '2024-08-09 02:52:48'),
(956, 121, 63, 0.061, 1, '2024-08-09 02:52:48', '2024-08-09 02:52:48'),
(957, 121, 66, 0.069, 1, '2024-08-09 02:52:48', '2024-08-09 02:52:48'),
(958, 121, 75, 0.002, 1, '2024-08-09 02:52:48', '2024-08-09 02:52:48'),
(959, 121, 76, 0.001, 1, '2024-08-09 02:52:48', '2024-08-09 02:52:48'),
(960, 121, 77, 0.0014, 1, '2024-08-09 02:52:48', '2024-08-09 02:52:48'),
(961, 121, 78, 0.0014, 1, '2024-08-09 02:52:48', '2024-08-09 02:52:48'),
(962, 121, 86, 0.0025, 1, '2024-08-09 02:52:48', '2024-08-09 02:52:48'),
(963, 121, 95, 0.01, 1, '2024-08-09 02:52:48', '2024-08-09 02:52:48'),
(964, 121, 100, 0.02, 1, '2024-08-09 02:52:48', '2024-08-09 02:52:48'),
(965, 121, 117, 0.037, 1, '2024-08-09 02:52:48', '2024-08-09 02:52:48'),
(966, 121, 149, 0.092, 1, '2024-08-09 02:52:48', '2024-08-09 02:52:48'),
(967, 121, 158, 0.052, 1, '2024-08-09 02:52:48', '2024-08-09 02:52:48'),
(968, 121, 159, 2, 1, '2024-08-09 02:52:48', '2024-08-09 02:52:48'),
(969, 121, 163, 2, 1, '2024-08-09 02:52:48', '2024-08-09 02:52:48'),
(970, 121, 174, 2, 1, '2024-08-09 02:52:48', '2024-08-09 02:52:48'),
(971, 121, 188, 0, 1, '2024-08-09 02:52:48', '2024-08-09 02:52:48'),
(972, 121, 189, 0, 1, '2024-08-09 02:52:48', '2024-08-09 02:52:48'),
(973, 121, 190, 0, 1, '2024-08-09 02:52:48', '2024-08-09 02:52:48'),
(974, 121, 191, 2, 1, '2024-08-09 02:52:48', '2024-08-09 02:52:48'),
(975, 121, 192, 0, 1, '2024-08-09 02:52:48', '2024-08-09 02:52:48'),
(976, 121, 193, 0.013888888888888888, 1, '2024-08-09 02:52:48', '2024-08-09 02:52:48'),
(977, 122, 29, 0.047, 1, '2024-08-09 02:58:36', '2024-08-09 02:58:36'),
(978, 122, 34, 0.051, 1, '2024-08-09 02:58:36', '2024-08-09 02:58:36'),
(979, 122, 63, 0.061, 1, '2024-08-09 02:58:36', '2024-08-09 02:58:36'),
(980, 122, 66, 0.069, 1, '2024-08-09 02:58:36', '2024-08-09 02:58:36'),
(981, 122, 75, 0.002, 1, '2024-08-09 02:58:36', '2024-08-09 02:58:36'),
(982, 122, 76, 0.001, 1, '2024-08-09 02:58:36', '2024-08-09 02:58:36'),
(983, 122, 77, 0.0014, 1, '2024-08-09 02:58:36', '2024-08-09 02:58:36'),
(984, 122, 78, 0.0014, 1, '2024-08-09 02:58:36', '2024-08-09 02:58:36'),
(985, 122, 86, 0.0025, 1, '2024-08-09 02:58:36', '2024-08-09 02:58:36'),
(986, 122, 95, 0.01, 1, '2024-08-09 02:58:36', '2024-08-09 02:58:36'),
(987, 122, 100, 0.02, 1, '2024-08-09 02:58:36', '2024-08-09 02:58:36'),
(988, 122, 117, 0.037, 1, '2024-08-09 02:58:36', '2024-08-09 02:58:36'),
(989, 122, 149, 0.002, 1, '2024-08-09 02:58:37', '2024-08-09 02:58:37'),
(990, 122, 158, 0.105, 1, '2024-08-09 02:58:37', '2024-08-09 02:58:37'),
(991, 122, 159, 0.052, 1, '2024-08-09 02:58:37', '2024-08-09 02:58:37'),
(992, 122, 163, 2, 1, '2024-08-09 02:58:37', '2024-08-09 02:58:37'),
(993, 122, 174, 2, 1, '2024-08-09 02:58:37', '2024-08-09 02:58:37'),
(994, 122, 188, 0, 1, '2024-08-09 02:58:37', '2024-08-09 02:58:37'),
(995, 122, 189, 0, 1, '2024-08-09 02:58:37', '2024-08-09 02:58:37'),
(996, 122, 190, 0, 1, '2024-08-09 02:58:37', '2024-08-09 02:58:37'),
(997, 122, 191, 0, 1, '2024-08-09 02:58:37', '2024-08-09 02:58:37'),
(998, 122, 192, 2, 1, '2024-08-09 02:58:37', '2024-08-09 02:58:37'),
(999, 122, 193, 0.013888888888888888, 1, '2024-08-09 02:58:37', '2024-08-09 02:58:37'),
(1000, 125, 29, 0.01, 1, '2024-08-09 04:53:11', '2024-08-09 04:53:11'),
(1001, 125, 36, 0.056, 1, '2024-08-09 04:53:11', '2024-08-09 04:53:11'),
(1002, 125, 63, 0.061, 1, '2024-08-09 04:53:11', '2024-08-09 04:53:11'),
(1003, 125, 66, 0.069, 1, '2024-08-09 04:53:11', '2024-08-09 04:53:11'),
(1004, 125, 75, 0.0014, 1, '2024-08-09 04:53:11', '2024-08-09 04:53:11'),
(1005, 125, 76, 0.053, 1, '2024-08-09 04:53:11', '2024-08-09 04:53:11'),
(1006, 125, 77, 0.0014, 1, '2024-08-09 04:53:11', '2024-08-09 04:53:11'),
(1007, 125, 83, 0.0014, 1, '2024-08-09 04:53:11', '2024-08-09 04:53:11'),
(1008, 125, 86, 0.053, 1, '2024-08-09 04:53:11', '2024-08-09 04:53:11'),
(1009, 125, 95, 0.001, 1, '2024-08-09 04:53:11', '2024-08-09 04:53:11'),
(1010, 125, 100, 0.02, 1, '2024-08-09 04:53:11', '2024-08-09 04:53:11'),
(1011, 125, 149, 0.01, 1, '2024-08-09 04:53:11', '2024-08-09 04:53:11'),
(1012, 125, 158, 0.032, 1, '2024-08-09 04:53:12', '2024-08-09 04:53:12'),
(1013, 125, 164, 2, 1, '2024-08-09 04:53:12', '2024-08-09 04:53:12'),
(1014, 125, 174, 2, 1, '2024-08-09 04:53:12', '2024-08-09 04:53:12'),
(1015, 125, 194, 2, 1, '2024-08-09 04:53:12', '2024-08-09 04:53:12'),
(1016, 125, 195, 0, 1, '2024-08-09 04:53:12', '2024-08-09 04:53:12'),
(1017, 125, 196, 0, 1, '2024-08-09 04:53:12', '2024-08-09 04:53:12'),
(1018, 125, 197, 0, 1, '2024-08-09 04:53:12', '2024-08-09 04:53:12'),
(1019, 125, 198, 0, 1, '2024-08-09 04:53:12', '2024-08-09 04:53:12'),
(1020, 125, 199, 0.067, 1, '2024-08-09 04:53:12', '2024-08-09 04:53:12'),
(1021, 125, 193, 0.013888888888888888, 1, '2024-08-09 04:53:12', '2024-08-09 04:53:12'),
(1022, 124, 29, 0.01, 1, '2024-08-09 04:59:07', '2024-08-09 04:59:07'),
(1023, 124, 36, 0.057, 1, '2024-08-09 04:59:07', '2024-08-09 04:59:07'),
(1024, 124, 63, 0.061, 1, '2024-08-09 04:59:07', '2024-08-09 04:59:07'),
(1025, 124, 66, 0.069, 1, '2024-08-09 04:59:07', '2024-08-09 04:59:07'),
(1026, 124, 75, 0.0015, 1, '2024-08-09 04:59:07', '2024-08-09 04:59:07'),
(1027, 124, 76, 0.054, 1, '2024-08-09 04:59:07', '2024-08-09 04:59:07'),
(1028, 124, 77, 0.0015, 1, '2024-08-09 04:59:07', '2024-08-09 04:59:07'),
(1029, 124, 83, 0.0015, 1, '2024-08-09 04:59:07', '2024-08-09 04:59:07'),
(1030, 124, 86, 0.054, 1, '2024-08-09 04:59:07', '2024-08-09 04:59:07'),
(1031, 124, 95, 0.001, 1, '2024-08-09 04:59:07', '2024-08-09 04:59:07'),
(1032, 124, 100, 0.02, 1, '2024-08-09 04:59:08', '2024-08-09 04:59:08'),
(1033, 124, 149, 0.01, 1, '2024-08-09 04:59:08', '2024-08-09 04:59:08'),
(1034, 124, 158, 0.33, 1, '2024-08-09 04:59:08', '2024-08-09 04:59:08'),
(1035, 124, 164, 2, 1, '2024-08-09 04:59:08', '2024-08-09 04:59:08'),
(1036, 124, 174, 2, 1, '2024-08-09 04:59:08', '2024-08-09 04:59:08'),
(1037, 124, 194, 0, 1, '2024-08-09 04:59:08', '2024-08-09 04:59:08'),
(1038, 124, 195, 2, 1, '2024-08-09 04:59:08', '2024-08-09 04:59:08'),
(1039, 124, 196, 0, 1, '2024-08-09 04:59:08', '2024-08-09 04:59:08'),
(1040, 124, 197, 0, 1, '2024-08-09 04:59:08', '2024-08-09 04:59:08'),
(1041, 124, 198, 0, 1, '2024-08-09 04:59:08', '2024-08-09 04:59:08'),
(1042, 124, 199, 0.067, 1, '2024-08-09 04:59:08', '2024-08-09 04:59:08'),
(1043, 124, 193, 0.013888888888888888, 1, '2024-08-09 04:59:08', '2024-08-09 04:59:08'),
(1044, 123, 29, 0.01, 1, '2024-08-09 05:05:32', '2024-08-09 05:05:32'),
(1045, 123, 36, 0.067, 1, '2024-08-09 05:05:32', '2024-08-09 05:05:32'),
(1046, 123, 63, 0.061, 1, '2024-08-09 05:05:32', '2024-08-09 05:05:32'),
(1047, 123, 66, 0.069, 1, '2024-08-09 05:05:32', '2024-08-09 05:05:32'),
(1048, 123, 75, 0.0017, 1, '2024-08-09 05:05:32', '2024-08-09 05:05:32'),
(1049, 123, 76, 0.055, 1, '2024-08-09 05:05:32', '2024-08-09 05:05:32'),
(1050, 123, 77, 0.0017, 1, '2024-08-09 05:05:32', '2024-08-09 05:05:32'),
(1051, 123, 83, 0.0017, 1, '2024-08-09 05:05:32', '2024-08-09 05:05:32'),
(1052, 123, 86, 0.055, 1, '2024-08-09 05:05:32', '2024-08-09 05:05:32'),
(1053, 123, 95, 0.001, 1, '2024-08-09 05:05:32', '2024-08-09 05:05:32'),
(1054, 123, 100, 0.02, 1, '2024-08-09 05:05:32', '2024-08-09 05:05:32'),
(1055, 123, 149, 0.01, 1, '2024-08-09 05:05:33', '2024-08-09 05:05:33'),
(1056, 123, 158, 0.035, 1, '2024-08-09 05:05:33', '2024-08-09 05:05:33'),
(1057, 123, 164, 2, 1, '2024-08-09 05:05:33', '2024-08-09 05:05:33'),
(1058, 123, 174, 2, 1, '2024-08-09 05:05:33', '2024-08-09 05:05:33'),
(1059, 123, 194, 0, 1, '2024-08-09 05:05:33', '2024-08-09 05:05:33'),
(1060, 123, 195, 0, 1, '2024-08-09 05:05:33', '2024-08-09 05:05:33'),
(1061, 123, 196, 2, 1, '2024-08-09 05:05:33', '2024-08-09 05:05:33'),
(1062, 123, 197, 0, 1, '2024-08-09 05:05:33', '2024-08-09 05:05:33'),
(1063, 123, 198, 0, 1, '2024-08-09 05:05:33', '2024-08-09 05:05:33'),
(1064, 123, 199, 0.067, 1, '2024-08-09 05:05:33', '2024-08-09 05:05:33'),
(1065, 123, 193, 0.013888888888888888, 1, '2024-08-09 05:05:33', '2024-08-09 05:05:33'),
(1066, 126, 29, 0.01, 1, '2024-08-09 05:10:41', '2024-08-09 05:10:41'),
(1067, 126, 36, 0.074, 1, '2024-08-09 05:10:41', '2024-08-09 05:10:41'),
(1068, 126, 63, 0.061, 1, '2024-08-09 05:10:41', '2024-08-09 05:10:41'),
(1069, 126, 66, 0.069, 1, '2024-08-09 05:10:41', '2024-08-09 05:10:41'),
(1070, 126, 75, 0.002, 1, '2024-08-09 05:10:41', '2024-08-09 05:10:41'),
(1071, 126, 76, 0.057, 1, '2024-08-09 05:10:41', '2024-08-09 05:10:41'),
(1072, 126, 77, 0.002, 1, '2024-08-09 05:10:41', '2024-08-09 05:10:41'),
(1073, 126, 83, 0.002, 1, '2024-08-09 05:10:41', '2024-08-09 05:10:41'),
(1074, 126, 86, 0.057, 1, '2024-08-09 05:10:41', '2024-08-09 05:10:41'),
(1075, 126, 95, 0.001, 1, '2024-08-09 05:10:41', '2024-08-09 05:10:41'),
(1076, 126, 100, 0.02, 1, '2024-08-09 05:10:41', '2024-08-09 05:10:41'),
(1077, 126, 149, 0.01, 1, '2024-08-09 05:10:41', '2024-08-09 05:10:41'),
(1078, 126, 158, 0.039, 1, '2024-08-09 05:10:41', '2024-08-09 05:10:41'),
(1079, 126, 164, 2, 1, '2024-08-09 05:10:41', '2024-08-09 05:10:41'),
(1080, 126, 174, 2, 1, '2024-08-09 05:10:42', '2024-08-09 05:10:42'),
(1081, 126, 194, 0, 1, '2024-08-09 05:10:42', '2024-08-09 05:10:42'),
(1082, 126, 195, 0, 1, '2024-08-09 05:10:42', '2024-08-09 05:10:42'),
(1083, 126, 196, 0, 1, '2024-08-09 05:10:42', '2024-08-09 05:10:42'),
(1084, 126, 197, 2, 1, '2024-08-09 05:10:42', '2024-08-09 05:10:42'),
(1085, 126, 198, 0, 1, '2024-08-09 05:10:42', '2024-08-09 05:10:42'),
(1086, 126, 199, 0.067, 1, '2024-08-09 05:10:42', '2024-08-09 05:10:42'),
(1087, 126, 193, 0.013888888888888888, 1, '2024-08-09 05:10:42', '2024-08-09 05:10:42'),
(1088, 127, 29, 0.01, 1, '2024-08-09 05:15:14', '2024-08-09 05:15:14'),
(1089, 127, 36, 0.087, 1, '2024-08-09 05:15:15', '2024-08-09 05:15:15'),
(1090, 127, 63, 0.061, 1, '2024-08-09 05:15:15', '2024-08-09 05:15:15'),
(1091, 127, 66, 0.069, 1, '2024-08-09 05:15:15', '2024-08-09 05:15:15'),
(1092, 127, 75, 0.002, 1, '2024-08-09 05:15:15', '2024-08-09 05:15:15'),
(1093, 127, 76, 0.06, 1, '2024-08-09 05:15:15', '2024-08-09 05:15:15'),
(1094, 127, 77, 0.002, 1, '2024-08-09 05:15:15', '2024-08-09 05:15:15'),
(1095, 127, 83, 0.002, 1, '2024-08-09 05:15:15', '2024-08-09 05:15:15'),
(1096, 127, 86, 0.06, 1, '2024-08-09 05:15:15', '2024-08-09 05:15:15'),
(1097, 127, 95, 0.001, 1, '2024-08-09 05:15:15', '2024-08-09 05:15:15'),
(1098, 127, 100, 0.02, 1, '2024-08-09 05:15:15', '2024-08-09 05:15:15'),
(1099, 127, 149, 0.01, 1, '2024-08-09 05:15:15', '2024-08-09 05:15:15'),
(1100, 127, 158, 0.043, 1, '2024-08-09 05:15:15', '2024-08-09 05:15:15'),
(1101, 127, 164, 2, 1, '2024-08-09 05:15:15', '2024-08-09 05:15:15'),
(1102, 127, 174, 2, 1, '2024-08-09 05:15:15', '2024-08-09 05:15:15'),
(1103, 127, 194, 0, 1, '2024-08-09 05:15:15', '2024-08-09 05:15:15'),
(1104, 127, 195, 0, 1, '2024-08-09 05:15:15', '2024-08-09 05:15:15'),
(1105, 127, 196, 0, 1, '2024-08-09 05:15:15', '2024-08-09 05:15:15'),
(1106, 127, 197, 0, 1, '2024-08-09 05:15:15', '2024-08-09 05:15:15'),
(1107, 127, 198, 2, 1, '2024-08-09 05:15:15', '2024-08-09 05:15:15'),
(1108, 127, 199, 0.067, 1, '2024-08-09 05:15:15', '2024-08-09 05:15:15'),
(1109, 127, 193, 0.013888888888888888, 1, '2024-08-09 05:15:15', '2024-08-09 05:15:15'),
(1110, 130, 29, 0.046, 1, '2024-08-09 05:34:14', '2024-08-09 05:34:14'),
(1111, 130, 30, 0.067, 1, '2024-08-09 05:34:14', '2024-08-09 05:34:14'),
(1112, 130, 53, 0.083, 1, '2024-08-09 05:34:14', '2024-08-09 05:34:14'),
(1113, 130, 75, 0.014, 1, '2024-08-09 05:34:14', '2024-08-09 05:34:14'),
(1114, 130, 76, 0.052, 1, '2024-08-09 05:34:14', '2024-08-09 05:34:14'),
(1115, 130, 77, 0.014, 1, '2024-08-09 05:34:14', '2024-08-09 05:34:14'),
(1116, 130, 86, 0.055, 1, '2024-08-09 05:34:14', '2024-08-09 05:34:14'),
(1117, 130, 100, 0.02, 1, '2024-08-09 05:34:14', '2024-08-09 05:34:14'),
(1118, 130, 104, 0.016, 1, '2024-08-09 05:34:14', '2024-08-09 05:34:14'),
(1119, 130, 114, 0.021, 1, '2024-08-09 05:34:14', '2024-08-09 05:34:14'),
(1120, 130, 149, 0.02, 1, '2024-08-09 05:34:14', '2024-08-09 05:34:14'),
(1121, 130, 158, 0.061, 1, '2024-08-09 05:34:14', '2024-08-09 05:34:14'),
(1122, 130, 174, 2, 1, '2024-08-09 05:34:14', '2024-08-09 05:34:14'),
(1123, 130, 200, 2, 1, '2024-08-09 05:34:14', '2024-08-09 05:34:14'),
(1124, 130, 201, 0, 1, '2024-08-09 05:34:14', '2024-08-09 05:34:14'),
(1125, 130, 202, 0, 1, '2024-08-09 05:34:14', '2024-08-09 05:34:14'),
(1126, 130, 203, 0, 1, '2024-08-09 05:34:14', '2024-08-09 05:34:14'),
(1127, 130, 204, 0, 1, '2024-08-09 05:34:14', '2024-08-09 05:34:14'),
(1128, 130, 175, 0.013888888888888888, 1, '2024-08-09 05:34:14', '2024-08-09 05:34:14'),
(1129, 129, 29, 0.048, 1, '2024-08-09 05:47:50', '2024-08-09 05:47:50'),
(1130, 129, 30, 0.069, 1, '2024-08-09 05:47:50', '2024-08-09 05:47:50'),
(1131, 129, 53, 0.085, 1, '2024-08-09 05:47:50', '2024-08-09 05:47:50'),
(1132, 129, 75, 0.014, 1, '2024-08-09 05:47:50', '2024-08-09 05:47:50'),
(1133, 129, 76, 0.052, 1, '2024-08-09 05:47:50', '2024-08-09 05:47:50'),
(1134, 129, 77, 0.014, 1, '2024-08-09 05:47:50', '2024-08-09 05:47:50'),
(1135, 129, 86, 0.055, 1, '2024-08-09 05:47:50', '2024-08-09 05:47:50'),
(1136, 129, 100, 0.2, 1, '2024-08-09 05:47:50', '2024-08-09 05:47:50'),
(1137, 129, 104, 0.016, 1, '2024-08-09 05:47:50', '2024-08-09 05:47:50'),
(1138, 129, 114, 0.023, 1, '2024-08-09 05:47:51', '2024-08-09 05:47:51'),
(1139, 129, 149, 0.2, 1, '2024-08-09 05:47:51', '2024-08-09 05:47:51'),
(1140, 129, 158, 0, 1, '2024-08-09 05:47:51', '2024-08-09 05:47:51'),
(1141, 129, 159, 0.063, 1, '2024-08-09 05:47:51', '2024-08-09 05:47:51'),
(1142, 129, 174, 2, 1, '2024-08-09 05:47:51', '2024-08-09 05:47:51'),
(1143, 129, 200, 0, 1, '2024-08-09 05:47:51', '2024-08-09 05:47:51'),
(1144, 129, 201, 2, 1, '2024-08-09 05:47:51', '2024-08-09 05:47:51'),
(1145, 129, 202, 0, 1, '2024-08-09 05:47:51', '2024-08-09 05:47:51'),
(1146, 129, 203, 0, 1, '2024-08-09 05:47:51', '2024-08-09 05:47:51'),
(1147, 129, 204, 0, 1, '2024-08-09 05:47:51', '2024-08-09 05:47:51'),
(1148, 129, 175, 0.013888888888888888, 1, '2024-08-09 05:47:51', '2024-08-09 05:47:51'),
(1149, 128, 29, 0.04, 1, '2024-08-09 05:51:25', '2024-08-09 05:51:25'),
(1150, 128, 30, 0.071, 1, '2024-08-09 05:51:25', '2024-08-09 05:51:25'),
(1151, 128, 53, 0.087, 1, '2024-08-09 05:51:25', '2024-08-09 05:51:25'),
(1152, 128, 75, 0.014, 1, '2024-08-09 05:51:25', '2024-08-09 05:51:25'),
(1153, 128, 76, 0.052, 1, '2024-08-09 05:51:25', '2024-08-09 05:51:25'),
(1154, 128, 77, 0.014, 1, '2024-08-09 05:51:25', '2024-08-09 05:51:25'),
(1155, 128, 86, 0.055, 1, '2024-08-09 05:51:25', '2024-08-09 05:51:25'),
(1156, 128, 100, 0.2, 1, '2024-08-09 05:51:25', '2024-08-09 05:51:25'),
(1157, 128, 104, 0.16, 1, '2024-08-09 05:51:26', '2024-08-09 05:51:26'),
(1158, 128, 114, 0.025, 1, '2024-08-09 05:51:26', '2024-08-09 05:51:26'),
(1159, 128, 149, 0.2, 1, '2024-08-09 05:51:26', '2024-08-09 05:51:26'),
(1160, 128, 158, 0, 1, '2024-08-09 05:51:26', '2024-08-09 05:51:26'),
(1161, 128, 159, 0.065, 1, '2024-08-09 05:51:26', '2024-08-09 05:51:26'),
(1162, 128, 174, 2, 1, '2024-08-09 05:51:26', '2024-08-09 05:51:26'),
(1163, 128, 200, 0, 1, '2024-08-09 05:51:26', '2024-08-09 05:51:26'),
(1164, 128, 201, 0, 1, '2024-08-09 05:51:26', '2024-08-09 05:51:26'),
(1165, 128, 202, 2, 1, '2024-08-09 05:51:26', '2024-08-09 05:51:26'),
(1166, 128, 203, 0, 1, '2024-08-09 05:51:26', '2024-08-09 05:51:26'),
(1167, 128, 204, 0, 1, '2024-08-09 05:51:26', '2024-08-09 05:51:26'),
(1168, 128, 175, 0.013888888888888888, 1, '2024-08-09 05:51:26', '2024-08-09 05:51:26'),
(1169, 131, 29, 0.042, 1, '2024-08-09 05:54:47', '2024-08-09 05:54:47'),
(1170, 131, 30, 0.073, 1, '2024-08-09 05:54:47', '2024-08-09 05:54:47'),
(1171, 131, 53, 0.089, 1, '2024-08-09 05:54:47', '2024-08-09 05:54:47'),
(1172, 131, 75, 0.014, 1, '2024-08-09 05:54:47', '2024-08-09 05:54:47'),
(1173, 131, 76, 0.52, 1, '2024-08-09 05:54:47', '2024-08-09 05:54:47'),
(1174, 131, 77, 0.014, 1, '2024-08-09 05:54:47', '2024-08-09 05:54:47'),
(1175, 131, 86, 0.055, 1, '2024-08-09 05:54:47', '2024-08-09 05:54:47'),
(1176, 131, 100, 0.2, 1, '2024-08-09 05:54:48', '2024-08-09 05:54:48'),
(1177, 131, 104, 0.16, 1, '2024-08-09 05:54:48', '2024-08-09 05:54:48'),
(1178, 131, 114, 0.027, 1, '2024-08-09 05:54:48', '2024-08-09 05:54:48'),
(1179, 131, 149, 0.2, 1, '2024-08-09 05:54:48', '2024-08-09 05:54:48'),
(1180, 131, 158, 0, 1, '2024-08-09 05:54:48', '2024-08-09 05:54:48'),
(1181, 131, 159, 0.067, 1, '2024-08-09 05:54:48', '2024-08-09 05:54:48'),
(1182, 131, 174, 2, 1, '2024-08-09 05:54:48', '2024-08-09 05:54:48'),
(1183, 131, 200, 0, 1, '2024-08-09 05:54:48', '2024-08-09 05:54:48'),
(1184, 131, 201, 0, 1, '2024-08-09 05:54:48', '2024-08-09 05:54:48'),
(1185, 131, 202, 0, 1, '2024-08-09 05:54:48', '2024-08-09 05:54:48'),
(1186, 131, 203, 2, 1, '2024-08-09 05:54:48', '2024-08-09 05:54:48'),
(1187, 131, 204, 0, 1, '2024-08-09 05:54:48', '2024-08-09 05:54:48'),
(1188, 131, 175, 0.013888888888888888, 1, '2024-08-09 05:54:48', '2024-08-09 05:54:48'),
(1189, 132, 29, 0.044, 1, '2024-08-09 05:57:40', '2024-08-09 05:57:40'),
(1190, 132, 30, 0.075, 1, '2024-08-09 05:57:40', '2024-08-09 05:57:40'),
(1191, 132, 53, 0.091, 1, '2024-08-09 05:57:40', '2024-08-09 05:57:40'),
(1192, 132, 75, 0.014, 1, '2024-08-09 05:57:40', '2024-08-09 05:57:40'),
(1193, 132, 76, 0.52, 1, '2024-08-09 05:57:40', '2024-08-09 05:57:40'),
(1194, 132, 77, 0.014, 1, '2024-08-09 05:57:40', '2024-08-09 05:57:40'),
(1195, 132, 86, 0.055, 1, '2024-08-09 05:57:40', '2024-08-09 05:57:40'),
(1196, 132, 100, 0.2, 1, '2024-08-09 05:57:40', '2024-08-09 05:57:40'),
(1197, 132, 104, 0.16, 1, '2024-08-09 05:57:40', '2024-08-09 05:57:40'),
(1198, 132, 114, 0.027, 1, '2024-08-09 05:57:40', '2024-08-09 05:57:40'),
(1199, 132, 149, 0.2, 1, '2024-08-09 05:57:40', '2024-08-09 05:57:40'),
(1200, 132, 158, 0, 1, '2024-08-09 05:57:40', '2024-08-09 05:57:40'),
(1201, 132, 159, 0.69, 1, '2024-08-09 05:57:40', '2024-08-09 05:57:40'),
(1202, 132, 174, 2, 1, '2024-08-09 05:57:41', '2024-08-09 05:57:41'),
(1203, 132, 200, 0, 1, '2024-08-09 05:57:41', '2024-08-09 05:57:41'),
(1204, 132, 201, 0, 1, '2024-08-09 05:57:41', '2024-08-09 05:57:41'),
(1205, 132, 202, 0, 1, '2024-08-09 05:57:41', '2024-08-09 05:57:41'),
(1206, 132, 203, 0, 1, '2024-08-09 05:57:41', '2024-08-09 05:57:41'),
(1207, 132, 204, 2, 1, '2024-08-09 05:57:41', '2024-08-09 05:57:41'),
(1208, 132, 175, 0.013888888888888888, 1, '2024-08-09 05:57:41', '2024-08-09 05:57:41'),
(1209, 135, 29, 0.59, 1, '2024-08-09 06:33:27', '2024-08-09 06:33:27'),
(1210, 135, 56, 1.07, 1, '2024-08-09 06:33:27', '2024-08-09 06:33:27'),
(1211, 135, 58, 0.015, 1, '2024-08-09 06:33:27', '2024-08-09 06:33:27'),
(1212, 135, 62, 0.069, 1, '2024-08-09 06:33:27', '2024-08-09 06:33:27'),
(1213, 135, 64, 0.069, 1, '2024-08-09 06:33:27', '2024-08-09 06:33:27'),
(1214, 135, 75, 0.0011, 1, '2024-08-09 06:33:27', '2024-08-09 06:33:27'),
(1215, 135, 86, 0.002, 1, '2024-08-09 06:33:27', '2024-08-09 06:33:27'),
(1216, 135, 149, 0.002, 1, '2024-08-09 06:33:27', '2024-08-09 06:33:27'),
(1217, 135, 166, 2, 1, '2024-08-09 06:33:27', '2024-08-09 06:33:27'),
(1218, 135, 174, 2, 1, '2024-08-09 06:33:27', '2024-08-09 06:33:27'),
(1219, 135, 176, 0.12, 1, '2024-08-09 06:33:27', '2024-08-09 06:33:27'),
(1220, 135, 205, 0.033, 1, '2024-08-09 06:33:27', '2024-08-09 06:33:27'),
(1221, 135, 206, 0.0025, 1, '2024-08-09 06:33:27', '2024-08-09 06:33:27'),
(1222, 135, 207, 0.0035, 1, '2024-08-09 06:33:27', '2024-08-09 06:33:27'),
(1223, 135, 208, 0.028, 1, '2024-08-09 06:33:27', '2024-08-09 06:33:27'),
(1224, 135, 209, 2, 1, '2024-08-09 06:33:27', '2024-08-09 06:33:27'),
(1225, 135, 210, 0, 1, '2024-08-09 06:33:27', '2024-08-09 06:33:27'),
(1226, 135, 211, 0, 1, '2024-08-09 06:33:27', '2024-08-09 06:33:27'),
(1227, 135, 212, 0, 1, '2024-08-09 06:33:27', '2024-08-09 06:33:27'),
(1228, 135, 213, 0, 1, '2024-08-09 06:33:27', '2024-08-09 06:33:27'),
(1229, 135, 175, 0.013888888888888888, 1, '2024-08-09 06:33:28', '2024-08-09 06:33:28'),
(1230, 134, 29, 0.62, 1, '2024-08-09 06:39:11', '2024-08-09 06:39:11'),
(1231, 134, 56, 1.09, 1, '2024-08-09 06:39:11', '2024-08-09 06:39:11'),
(1232, 134, 58, 0.016, 1, '2024-08-09 06:39:11', '2024-08-09 06:39:11'),
(1233, 134, 62, 0.069, 1, '2024-08-09 06:39:11', '2024-08-09 06:39:11'),
(1234, 134, 64, 0.069, 1, '2024-08-09 06:39:11', '2024-08-09 06:39:11'),
(1235, 134, 75, 0.0011, 1, '2024-08-09 06:39:11', '2024-08-09 06:39:11'),
(1236, 134, 86, 0.002, 1, '2024-08-09 06:39:12', '2024-08-09 06:39:12'),
(1237, 134, 149, 0.2, 1, '2024-08-09 06:39:12', '2024-08-09 06:39:12'),
(1238, 134, 166, 2, 1, '2024-08-09 06:39:12', '2024-08-09 06:39:12'),
(1239, 134, 174, 2, 1, '2024-08-09 06:39:12', '2024-08-09 06:39:12'),
(1240, 134, 176, 0.12, 1, '2024-08-09 06:39:12', '2024-08-09 06:39:12'),
(1241, 134, 205, 0.033, 1, '2024-08-09 06:39:12', '2024-08-09 06:39:12'),
(1242, 134, 206, 0.0025, 1, '2024-08-09 06:39:12', '2024-08-09 06:39:12'),
(1243, 134, 207, 0.0035, 1, '2024-08-09 06:39:12', '2024-08-09 06:39:12'),
(1244, 134, 208, 0.028, 1, '2024-08-09 06:39:12', '2024-08-09 06:39:12'),
(1245, 134, 209, 0, 1, '2024-08-09 06:39:12', '2024-08-09 06:39:12'),
(1246, 134, 210, 2, 1, '2024-08-09 06:39:12', '2024-08-09 06:39:12'),
(1247, 134, 211, 0, 1, '2024-08-09 06:39:12', '2024-08-09 06:39:12'),
(1248, 134, 212, 0, 1, '2024-08-09 06:39:12', '2024-08-09 06:39:12'),
(1249, 134, 213, 0, 1, '2024-08-09 06:39:12', '2024-08-09 06:39:12'),
(1250, 134, 175, 0.013888888888888888, 1, '2024-08-09 06:39:12', '2024-08-09 06:39:12'),
(1251, 133, 29, 0.71, 1, '2024-08-09 06:43:07', '2024-08-09 06:43:07'),
(1252, 133, 56, 1.09, 1, '2024-08-09 06:43:08', '2024-08-09 06:43:08'),
(1253, 133, 58, 0.017, 1, '2024-08-09 06:43:08', '2024-08-09 06:43:08'),
(1254, 133, 62, 0.069, 1, '2024-08-09 06:43:08', '2024-08-09 06:43:08'),
(1255, 133, 64, 0.069, 1, '2024-08-09 06:43:08', '2024-08-09 06:43:08'),
(1256, 133, 75, 0.0011, 1, '2024-08-09 06:43:08', '2024-08-09 06:43:08'),
(1257, 133, 86, 0.002, 1, '2024-08-09 06:43:08', '2024-08-09 06:43:08');
INSERT INTO `product_materials` (`product_material_id`, `product_type_id`, `material_id`, `quantity`, `created_by`, `created_at`, `updated_at`) VALUES
(1258, 133, 149, 0.2, 1, '2024-08-09 06:43:08', '2024-08-09 06:43:08'),
(1259, 133, 166, 2, 1, '2024-08-09 06:43:08', '2024-08-09 06:43:08'),
(1260, 133, 174, 2, 1, '2024-08-09 06:43:08', '2024-08-09 06:43:08'),
(1261, 133, 176, 0.12, 1, '2024-08-09 06:43:08', '2024-08-09 06:43:08'),
(1262, 133, 205, 0.033, 1, '2024-08-09 06:43:08', '2024-08-09 06:43:08'),
(1263, 133, 206, 0.0025, 1, '2024-08-09 06:43:08', '2024-08-09 06:43:08'),
(1264, 133, 207, 0.0035, 1, '2024-08-09 06:43:08', '2024-08-09 06:43:08'),
(1265, 133, 208, 0.028, 1, '2024-08-09 06:43:08', '2024-08-09 06:43:08'),
(1266, 133, 209, 0, 1, '2024-08-09 06:43:08', '2024-08-09 06:43:08'),
(1267, 133, 210, 0, 1, '2024-08-09 06:43:08', '2024-08-09 06:43:08'),
(1268, 133, 211, 2, 1, '2024-08-09 06:43:08', '2024-08-09 06:43:08'),
(1269, 133, 212, 0, 1, '2024-08-09 06:43:08', '2024-08-09 06:43:08'),
(1270, 133, 213, 0, 1, '2024-08-09 06:43:08', '2024-08-09 06:43:08'),
(1271, 133, 175, 0.013888888888888888, 1, '2024-08-09 06:43:08', '2024-08-09 06:43:08'),
(1272, 136, 29, 0.77, 1, '2024-08-09 06:47:05', '2024-08-09 06:47:05'),
(1273, 136, 56, 1.14, 1, '2024-08-09 06:47:06', '2024-08-09 06:47:06'),
(1274, 136, 58, 0.018, 1, '2024-08-09 06:47:06', '2024-08-09 06:47:06'),
(1275, 136, 62, 0.069, 1, '2024-08-09 06:47:06', '2024-08-09 06:47:06'),
(1276, 136, 64, 0.069, 1, '2024-08-09 06:47:06', '2024-08-09 06:47:06'),
(1277, 136, 75, 0.0014, 1, '2024-08-09 06:47:06', '2024-08-09 06:47:06'),
(1278, 136, 86, 0.0542, 1, '2024-08-09 06:47:06', '2024-08-09 06:47:06'),
(1279, 136, 149, 0.2, 1, '2024-08-09 06:47:06', '2024-08-09 06:47:06'),
(1280, 136, 166, 2, 1, '2024-08-09 06:47:06', '2024-08-09 06:47:06'),
(1281, 136, 174, 2, 1, '2024-08-09 06:47:06', '2024-08-09 06:47:06'),
(1282, 136, 176, 0.12, 1, '2024-08-09 06:47:06', '2024-08-09 06:47:06'),
(1283, 136, 205, 0.033, 1, '2024-08-09 06:47:06', '2024-08-09 06:47:06'),
(1284, 136, 206, 0.0156, 1, '2024-08-09 06:47:06', '2024-08-09 06:47:06'),
(1285, 136, 207, 0.0542, 1, '2024-08-09 06:47:06', '2024-08-09 06:47:06'),
(1286, 136, 208, 0.028, 1, '2024-08-09 06:47:06', '2024-08-09 06:47:06'),
(1287, 136, 209, 0, 1, '2024-08-09 06:47:06', '2024-08-09 06:47:06'),
(1288, 136, 210, 0, 1, '2024-08-09 06:47:06', '2024-08-09 06:47:06'),
(1289, 136, 211, 0, 1, '2024-08-09 06:47:06', '2024-08-09 06:47:06'),
(1290, 136, 212, 2, 1, '2024-08-09 06:47:06', '2024-08-09 06:47:06'),
(1291, 136, 213, 0, 1, '2024-08-09 06:47:06', '2024-08-09 06:47:06'),
(1292, 136, 175, 0.013888888888888888, 1, '2024-08-09 06:47:06', '2024-08-09 06:47:06'),
(1293, 137, 29, 0.077, 1, '2024-08-09 06:50:27', '2024-08-09 06:50:27'),
(1294, 137, 56, 1.2, 1, '2024-08-09 06:50:27', '2024-08-09 06:50:27'),
(1295, 137, 58, 0.019, 1, '2024-08-09 06:50:28', '2024-08-09 06:50:28'),
(1296, 137, 62, 0.069, 1, '2024-08-09 06:50:28', '2024-08-09 06:50:28'),
(1297, 137, 64, 0.069, 1, '2024-08-09 06:50:28', '2024-08-09 06:50:28'),
(1298, 137, 75, 0.0011, 1, '2024-08-09 06:50:28', '2024-08-09 06:50:28'),
(1299, 137, 86, 0.002, 1, '2024-08-09 06:50:28', '2024-08-09 06:50:28'),
(1300, 137, 149, 0.2, 1, '2024-08-09 06:50:28', '2024-08-09 06:50:28'),
(1301, 137, 166, 2, 1, '2024-08-09 06:50:28', '2024-08-09 06:50:28'),
(1302, 137, 174, 2, 1, '2024-08-09 06:50:28', '2024-08-09 06:50:28'),
(1303, 137, 176, 0.12, 1, '2024-08-09 06:50:28', '2024-08-09 06:50:28'),
(1304, 137, 205, 0.033, 1, '2024-08-09 06:50:28', '2024-08-09 06:50:28'),
(1305, 137, 206, 0.0025, 1, '2024-08-09 06:50:28', '2024-08-09 06:50:28'),
(1306, 137, 207, 0.0035, 1, '2024-08-09 06:50:28', '2024-08-09 06:50:28'),
(1307, 137, 208, 0.028, 1, '2024-08-09 06:50:28', '2024-08-09 06:50:28'),
(1308, 137, 209, 0, 1, '2024-08-09 06:50:28', '2024-08-09 06:50:28'),
(1309, 137, 210, 0, 1, '2024-08-09 06:50:28', '2024-08-09 06:50:28'),
(1310, 137, 211, 0, 1, '2024-08-09 06:50:28', '2024-08-09 06:50:28'),
(1311, 137, 212, 0, 1, '2024-08-09 06:50:28', '2024-08-09 06:50:28'),
(1312, 137, 213, 2, 1, '2024-08-09 06:50:28', '2024-08-09 06:50:28'),
(1313, 137, 175, 0.013888888888888888, 1, '2024-08-09 06:50:28', '2024-08-09 06:50:28'),
(1314, 140, 29, 0.051, 1, '2024-08-09 07:10:31', '2024-08-09 07:10:31'),
(1315, 140, 56, 1.051, 1, '2024-08-09 07:10:31', '2024-08-09 07:10:31'),
(1316, 140, 58, 0.015, 1, '2024-08-09 07:10:31', '2024-08-09 07:10:31'),
(1317, 140, 62, 0.069, 1, '2024-08-09 07:10:31', '2024-08-09 07:10:31'),
(1318, 140, 64, 0.069, 1, '2024-08-09 07:10:31', '2024-08-09 07:10:31'),
(1319, 140, 75, 0.0014, 1, '2024-08-09 07:10:31', '2024-08-09 07:10:31'),
(1320, 140, 86, 0.0542, 1, '2024-08-09 07:10:31', '2024-08-09 07:10:31'),
(1321, 140, 95, 0.016, 1, '2024-08-09 07:10:32', '2024-08-09 07:10:32'),
(1322, 140, 112, 0.18, 1, '2024-08-09 07:10:32', '2024-08-09 07:10:32'),
(1323, 140, 149, 0.2, 1, '2024-08-09 07:10:32', '2024-08-09 07:10:32'),
(1324, 140, 167, 2, 1, '2024-08-09 07:10:32', '2024-08-09 07:10:32'),
(1325, 140, 174, 2, 1, '2024-08-09 07:10:32', '2024-08-09 07:10:32'),
(1326, 140, 176, 0.12, 1, '2024-08-09 07:10:32', '2024-08-09 07:10:32'),
(1327, 140, 205, 0.033, 1, '2024-08-09 07:10:32', '2024-08-09 07:10:32'),
(1328, 140, 206, 0.0156, 1, '2024-08-09 07:10:32', '2024-08-09 07:10:32'),
(1329, 140, 207, 0.0542, 1, '2024-08-09 07:10:32', '2024-08-09 07:10:32'),
(1330, 140, 208, 0.028, 1, '2024-08-09 07:10:32', '2024-08-09 07:10:32'),
(1331, 140, 214, 2, 1, '2024-08-09 07:10:32', '2024-08-09 07:10:32'),
(1332, 140, 215, 0, 1, '2024-08-09 07:10:32', '2024-08-09 07:10:32'),
(1333, 140, 216, 0, 1, '2024-08-09 07:10:32', '2024-08-09 07:10:32'),
(1334, 140, 217, 0, 1, '2024-08-09 07:10:32', '2024-08-09 07:10:32'),
(1335, 140, 218, 0, 1, '2024-08-09 07:10:32', '2024-08-09 07:10:32'),
(1336, 140, 175, 0.013888888888888888, 1, '2024-08-09 07:10:32', '2024-08-09 07:10:32'),
(1337, 139, 29, 0.062, 1, '2024-08-09 07:34:58', '2024-08-09 07:34:58'),
(1338, 139, 56, 1.0409, 1, '2024-08-09 07:34:59', '2024-08-09 07:34:59'),
(1339, 139, 58, 0.016, 1, '2024-08-09 07:34:59', '2024-08-09 07:34:59'),
(1340, 139, 62, 0.069, 1, '2024-08-09 07:34:59', '2024-08-09 07:34:59'),
(1341, 139, 64, 0.069, 1, '2024-08-09 07:34:59', '2024-08-09 07:34:59'),
(1342, 139, 75, 0.0014, 1, '2024-08-09 07:34:59', '2024-08-09 07:34:59'),
(1343, 139, 86, 0.0542, 1, '2024-08-09 07:34:59', '2024-08-09 07:34:59'),
(1344, 139, 95, 0.018, 1, '2024-08-09 07:34:59', '2024-08-09 07:34:59'),
(1345, 139, 112, 0.2, 1, '2024-08-09 07:34:59', '2024-08-09 07:34:59'),
(1346, 139, 149, 0.2, 1, '2024-08-09 07:34:59', '2024-08-09 07:34:59'),
(1347, 139, 167, 2, 1, '2024-08-09 07:34:59', '2024-08-09 07:34:59'),
(1348, 139, 174, 2, 1, '2024-08-09 07:34:59', '2024-08-09 07:34:59'),
(1349, 139, 176, 0.12, 1, '2024-08-09 07:34:59', '2024-08-09 07:34:59'),
(1350, 139, 205, 0.033, 1, '2024-08-09 07:34:59', '2024-08-09 07:34:59'),
(1351, 139, 206, 0.0156, 1, '2024-08-09 07:34:59', '2024-08-09 07:34:59'),
(1352, 139, 207, 0.0542, 1, '2024-08-09 07:35:00', '2024-08-09 07:35:00'),
(1353, 139, 208, 0.028, 1, '2024-08-09 07:35:00', '2024-08-09 07:35:00'),
(1354, 139, 214, 0, 1, '2024-08-09 07:35:00', '2024-08-09 07:35:00'),
(1355, 139, 215, 2, 1, '2024-08-09 07:35:00', '2024-08-09 07:35:00'),
(1356, 139, 216, 0, 1, '2024-08-09 07:35:00', '2024-08-09 07:35:00'),
(1357, 139, 217, 0, 1, '2024-08-09 07:35:00', '2024-08-09 07:35:00'),
(1358, 139, 218, 0, 1, '2024-08-09 07:35:00', '2024-08-09 07:35:00'),
(1359, 139, 175, 0.013888888888888888, 1, '2024-08-09 07:35:00', '2024-08-09 07:35:00'),
(1360, 138, 29, 0.071, 1, '2024-08-09 07:39:36', '2024-08-09 07:39:36'),
(1361, 138, 56, 1.091, 1, '2024-08-09 07:39:37', '2024-08-09 07:39:37'),
(1362, 138, 58, 0.017, 1, '2024-08-09 07:39:37', '2024-08-09 07:39:37'),
(1363, 138, 62, 0.069, 1, '2024-08-09 07:39:37', '2024-08-09 07:39:37'),
(1364, 138, 64, 0.069, 1, '2024-08-09 07:39:37', '2024-08-09 07:39:37'),
(1365, 138, 75, 0.0014, 1, '2024-08-09 07:39:37', '2024-08-09 07:39:37'),
(1366, 138, 86, 0.0542, 1, '2024-08-09 07:39:37', '2024-08-09 07:39:37'),
(1367, 138, 95, 0.02, 1, '2024-08-09 07:39:37', '2024-08-09 07:39:37'),
(1368, 138, 112, 0.2, 1, '2024-08-09 07:39:37', '2024-08-09 07:39:37'),
(1369, 138, 149, 0.2, 1, '2024-08-09 07:39:37', '2024-08-09 07:39:37'),
(1370, 138, 167, 2, 1, '2024-08-09 07:39:37', '2024-08-09 07:39:37'),
(1371, 138, 174, 2, 1, '2024-08-09 07:39:37', '2024-08-09 07:39:37'),
(1372, 138, 176, 0.12, 1, '2024-08-09 07:39:37', '2024-08-09 07:39:37'),
(1373, 138, 205, 0.033, 1, '2024-08-09 07:39:37', '2024-08-09 07:39:37'),
(1374, 138, 206, 0.0156, 1, '2024-08-09 07:39:37', '2024-08-09 07:39:37'),
(1375, 138, 207, 0.0542, 1, '2024-08-09 07:39:37', '2024-08-09 07:39:37'),
(1376, 138, 208, 0.028, 1, '2024-08-09 07:39:37', '2024-08-09 07:39:37'),
(1377, 138, 214, 0, 1, '2024-08-09 07:39:37', '2024-08-09 07:39:37'),
(1378, 138, 215, 0, 1, '2024-08-09 07:39:37', '2024-08-09 07:39:37'),
(1379, 138, 216, 2, 1, '2024-08-09 07:39:37', '2024-08-09 07:39:37'),
(1380, 138, 217, 0, 1, '2024-08-09 07:39:37', '2024-08-09 07:39:37'),
(1381, 138, 218, 0, 1, '2024-08-09 07:39:37', '2024-08-09 07:39:37'),
(1382, 138, 175, 0.013888888888888888, 1, '2024-08-09 07:39:37', '2024-08-09 07:39:37'),
(1383, 141, 29, 0.077, 1, '2024-08-09 07:44:22', '2024-08-09 07:44:22'),
(1384, 141, 56, 1.14, 1, '2024-08-09 07:44:23', '2024-08-09 07:44:23'),
(1385, 141, 58, 0.018, 1, '2024-08-09 07:44:23', '2024-08-09 07:44:23'),
(1386, 141, 62, 0.069, 1, '2024-08-09 07:44:23', '2024-08-09 07:44:23'),
(1387, 141, 64, 0.069, 1, '2024-08-09 07:44:23', '2024-08-09 07:44:23'),
(1388, 141, 75, 0.0014, 1, '2024-08-09 07:44:23', '2024-08-09 07:44:23'),
(1389, 141, 86, 0.0542, 1, '2024-08-09 07:44:23', '2024-08-09 07:44:23'),
(1390, 141, 95, 0.022, 1, '2024-08-09 07:44:23', '2024-08-09 07:44:23'),
(1391, 141, 112, 0.22, 1, '2024-08-09 07:44:23', '2024-08-09 07:44:23'),
(1392, 141, 149, 0.2, 1, '2024-08-09 07:44:23', '2024-08-09 07:44:23'),
(1393, 141, 167, 2, 1, '2024-08-09 07:44:23', '2024-08-09 07:44:23'),
(1394, 141, 174, 2, 1, '2024-08-09 07:44:23', '2024-08-09 07:44:23'),
(1395, 141, 176, 0.12, 1, '2024-08-09 07:44:23', '2024-08-09 07:44:23'),
(1396, 141, 205, 0.033, 1, '2024-08-09 07:44:23', '2024-08-09 07:44:23'),
(1397, 141, 206, 0.0156, 1, '2024-08-09 07:44:23', '2024-08-09 07:44:23'),
(1398, 141, 207, 0.0542, 1, '2024-08-09 07:44:23', '2024-08-09 07:44:23'),
(1399, 141, 208, 0.028, 1, '2024-08-09 07:44:23', '2024-08-09 07:44:23'),
(1400, 141, 214, 0, 1, '2024-08-09 07:44:23', '2024-08-09 07:44:23'),
(1401, 141, 215, 0, 1, '2024-08-09 07:44:23', '2024-08-09 07:44:23'),
(1402, 141, 216, 0, 1, '2024-08-09 07:44:23', '2024-08-09 07:44:23'),
(1403, 141, 217, 2, 1, '2024-08-09 07:44:23', '2024-08-09 07:44:23'),
(1404, 141, 218, 0, 1, '2024-08-09 07:44:23', '2024-08-09 07:44:23'),
(1405, 141, 175, 0.013888888888888888, 1, '2024-08-09 07:44:23', '2024-08-09 07:44:23'),
(1406, 142, 29, 0.077, 1, '2024-08-09 07:49:11', '2024-08-09 07:49:11'),
(1407, 142, 56, 1.2, 1, '2024-08-09 07:49:11', '2024-08-09 07:49:11'),
(1408, 142, 58, 0.019, 1, '2024-08-09 07:49:11', '2024-08-09 07:49:11'),
(1409, 142, 62, 0.069, 1, '2024-08-09 07:49:11', '2024-08-09 07:49:11'),
(1410, 142, 64, 0.069, 1, '2024-08-09 07:49:12', '2024-08-09 07:49:12'),
(1411, 142, 75, 0.0014, 1, '2024-08-09 07:49:12', '2024-08-09 07:49:12'),
(1412, 142, 86, 0.0542, 1, '2024-08-09 07:49:12', '2024-08-09 07:49:12'),
(1413, 142, 95, 0.025, 1, '2024-08-09 07:49:12', '2024-08-09 07:49:12'),
(1414, 142, 112, 0.25, 1, '2024-08-09 07:49:12', '2024-08-09 07:49:12'),
(1415, 142, 149, 0.2, 1, '2024-08-09 07:49:12', '2024-08-09 07:49:12'),
(1416, 142, 167, 2, 1, '2024-08-09 07:49:12', '2024-08-09 07:49:12'),
(1417, 142, 174, 2, 1, '2024-08-09 07:49:12', '2024-08-09 07:49:12'),
(1418, 142, 176, 0.12, 1, '2024-08-09 07:49:12', '2024-08-09 07:49:12'),
(1419, 142, 205, 0.033, 1, '2024-08-09 07:49:12', '2024-08-09 07:49:12'),
(1420, 142, 206, 0.0156, 1, '2024-08-09 07:49:12', '2024-08-09 07:49:12'),
(1421, 142, 207, 0.0542, 1, '2024-08-09 07:49:12', '2024-08-09 07:49:12'),
(1422, 142, 208, 0.028, 1, '2024-08-09 07:49:12', '2024-08-09 07:49:12'),
(1423, 142, 214, 0, 1, '2024-08-09 07:49:12', '2024-08-09 07:49:12'),
(1424, 142, 215, 0, 1, '2024-08-09 07:49:12', '2024-08-09 07:49:12'),
(1425, 142, 216, 0, 1, '2024-08-09 07:49:12', '2024-08-09 07:49:12'),
(1426, 142, 217, 0, 1, '2024-08-09 07:49:12', '2024-08-09 07:49:12'),
(1427, 142, 218, 2, 1, '2024-08-09 07:49:12', '2024-08-09 07:49:12'),
(1428, 142, 175, 0.013888888888888888, 1, '2024-08-09 07:49:12', '2024-08-09 07:49:12'),
(1429, 145, 29, 0.053, 1, '2024-08-09 08:25:03', '2024-08-09 08:25:03'),
(1430, 145, 57, 1.5, 1, '2024-08-09 08:25:03', '2024-08-09 08:25:03'),
(1431, 145, 86, 0, 1, '2024-08-09 08:25:03', '2024-08-09 08:25:03'),
(1432, 145, 91, 0.005, 1, '2024-08-09 08:25:03', '2024-08-09 08:25:03'),
(1433, 145, 92, 0, 1, '2024-08-09 08:25:03', '2024-08-09 08:25:03'),
(1434, 145, 95, 0, 1, '2024-08-09 08:25:04', '2024-08-09 08:25:04'),
(1435, 145, 103, 0, 1, '2024-08-09 08:25:04', '2024-08-09 08:25:04'),
(1436, 145, 105, 0.01, 1, '2024-08-09 08:25:04', '2024-08-09 08:25:04'),
(1437, 145, 107, 0.01, 1, '2024-08-09 08:25:04', '2024-08-09 08:25:04'),
(1438, 145, 108, 0, 1, '2024-08-09 08:25:04', '2024-08-09 08:25:04'),
(1439, 145, 117, 0, 1, '2024-08-09 08:25:04', '2024-08-09 08:25:04'),
(1440, 145, 158, 0.018, 1, '2024-08-09 08:25:04', '2024-08-09 08:25:04'),
(1441, 145, 174, 0, 1, '2024-08-09 08:25:04', '2024-08-09 08:25:04'),
(1442, 145, 208, 0, 1, '2024-08-09 08:25:04', '2024-08-09 08:25:04'),
(1443, 145, 219, 0, 1, '2024-08-09 08:25:04', '2024-08-09 08:25:04'),
(1444, 145, 220, 0, 1, '2024-08-09 08:25:04', '2024-08-09 08:25:04'),
(1445, 145, 221, 0, 1, '2024-08-09 08:25:04', '2024-08-09 08:25:04'),
(1446, 145, 222, 0, 1, '2024-08-09 08:25:04', '2024-08-09 08:25:04'),
(1447, 145, 223, 0, 1, '2024-08-09 08:25:04', '2024-08-09 08:25:04'),
(1448, 145, 224, 0, 1, '2024-08-09 08:25:04', '2024-08-09 08:25:04'),
(1449, 145, 225, 0.045, 1, '2024-08-09 08:25:04', '2024-08-09 08:25:04'),
(1450, 145, 226, 0.1, 1, '2024-08-09 08:25:04', '2024-08-09 08:25:04'),
(1451, 145, 175, 0.013888888888888888, 1, '2024-08-09 08:25:04', '2024-08-09 08:25:04'),
(1452, 144, 29, 0.053, 1, '2024-08-09 23:23:12', '2024-08-09 23:23:12'),
(1453, 144, 57, 1.55, 1, '2024-08-09 23:23:12', '2024-08-09 23:23:12'),
(1454, 144, 86, 0, 1, '2024-08-09 23:23:12', '2024-08-09 23:23:12'),
(1455, 144, 91, 0.005, 1, '2024-08-09 23:23:12', '2024-08-09 23:23:12'),
(1456, 144, 92, 0, 1, '2024-08-09 23:23:12', '2024-08-09 23:23:12'),
(1457, 144, 95, 0, 1, '2024-08-09 23:23:12', '2024-08-09 23:23:12'),
(1458, 144, 103, 0, 1, '2024-08-09 23:23:12', '2024-08-09 23:23:12'),
(1459, 144, 105, 0.01, 1, '2024-08-09 23:23:12', '2024-08-09 23:23:12'),
(1460, 144, 107, 0.01, 1, '2024-08-09 23:23:12', '2024-08-09 23:23:12'),
(1461, 144, 108, 0, 1, '2024-08-09 23:23:12', '2024-08-09 23:23:12'),
(1462, 144, 117, 0, 1, '2024-08-09 23:23:12', '2024-08-09 23:23:12'),
(1463, 144, 158, 0.018, 1, '2024-08-09 23:23:12', '2024-08-09 23:23:12'),
(1464, 144, 174, 0, 1, '2024-08-09 23:23:12', '2024-08-09 23:23:12'),
(1465, 144, 208, 0, 1, '2024-08-09 23:23:12', '2024-08-09 23:23:12'),
(1466, 144, 219, 0, 1, '2024-08-09 23:23:12', '2024-08-09 23:23:12'),
(1467, 144, 220, 0, 1, '2024-08-09 23:23:12', '2024-08-09 23:23:12'),
(1468, 144, 221, 0, 1, '2024-08-09 23:23:13', '2024-08-09 23:23:13'),
(1469, 144, 222, 0, 1, '2024-08-09 23:23:13', '2024-08-09 23:23:13'),
(1470, 144, 223, 0, 1, '2024-08-09 23:23:13', '2024-08-09 23:23:13'),
(1471, 144, 224, 0, 1, '2024-08-09 23:23:13', '2024-08-09 23:23:13'),
(1472, 144, 225, 0.045, 1, '2024-08-09 23:23:13', '2024-08-09 23:23:13'),
(1473, 144, 226, 0.1, 1, '2024-08-09 23:23:13', '2024-08-09 23:23:13'),
(1474, 144, 175, 0.013888888888888888, 1, '2024-08-09 23:23:13', '2024-08-09 23:23:13'),
(1475, 143, 29, 0, 1, '2024-08-09 23:27:13', '2024-08-09 23:27:13'),
(1476, 143, 57, 0, 1, '2024-08-09 23:27:13', '2024-08-09 23:27:13'),
(1477, 143, 86, 0, 1, '2024-08-09 23:27:13', '2024-08-09 23:27:13'),
(1478, 143, 91, 0, 1, '2024-08-09 23:27:13', '2024-08-09 23:27:13'),
(1479, 143, 92, 0, 1, '2024-08-09 23:27:13', '2024-08-09 23:27:13'),
(1480, 143, 95, 0, 1, '2024-08-09 23:27:13', '2024-08-09 23:27:13'),
(1481, 143, 103, 0, 1, '2024-08-09 23:27:13', '2024-08-09 23:27:13'),
(1482, 143, 105, 0, 1, '2024-08-09 23:27:13', '2024-08-09 23:27:13'),
(1483, 143, 107, 0, 1, '2024-08-09 23:27:13', '2024-08-09 23:27:13'),
(1484, 143, 108, 0, 1, '2024-08-09 23:27:13', '2024-08-09 23:27:13'),
(1485, 143, 117, 0, 1, '2024-08-09 23:27:13', '2024-08-09 23:27:13'),
(1486, 143, 158, 0, 1, '2024-08-09 23:27:13', '2024-08-09 23:27:13'),
(1487, 143, 174, 0, 1, '2024-08-09 23:27:13', '2024-08-09 23:27:13'),
(1488, 143, 208, 0, 1, '2024-08-09 23:27:13', '2024-08-09 23:27:13'),
(1489, 143, 219, 0, 1, '2024-08-09 23:27:13', '2024-08-09 23:27:13'),
(1490, 143, 220, 0, 1, '2024-08-09 23:27:13', '2024-08-09 23:27:13'),
(1491, 143, 221, 0, 1, '2024-08-09 23:27:13', '2024-08-09 23:27:13'),
(1492, 143, 222, 0, 1, '2024-08-09 23:27:13', '2024-08-09 23:27:13'),
(1493, 143, 223, 0, 1, '2024-08-09 23:27:13', '2024-08-09 23:27:13'),
(1494, 143, 224, 0, 1, '2024-08-09 23:27:13', '2024-08-09 23:27:13'),
(1495, 143, 225, 0, 1, '2024-08-09 23:27:14', '2024-08-09 23:27:14'),
(1496, 143, 226, 0, 1, '2024-08-09 23:27:14', '2024-08-09 23:27:14'),
(1497, 143, 175, 0.013888888888888888, 1, '2024-08-09 23:27:14', '2024-08-09 23:27:14'),
(1498, 146, 29, 0, 1, '2024-08-09 23:30:19', '2024-08-09 23:30:19'),
(1499, 146, 57, 0, 1, '2024-08-09 23:30:19', '2024-08-09 23:30:19'),
(1500, 146, 86, 0, 1, '2024-08-09 23:30:19', '2024-08-09 23:30:19'),
(1501, 146, 91, 0, 1, '2024-08-09 23:30:19', '2024-08-09 23:30:19'),
(1502, 146, 92, 0, 1, '2024-08-09 23:30:19', '2024-08-09 23:30:19'),
(1503, 146, 95, 0, 1, '2024-08-09 23:30:20', '2024-08-09 23:30:20'),
(1504, 146, 103, 0, 1, '2024-08-09 23:30:20', '2024-08-09 23:30:20'),
(1505, 146, 105, 0, 1, '2024-08-09 23:30:20', '2024-08-09 23:30:20'),
(1506, 146, 107, 0, 1, '2024-08-09 23:30:20', '2024-08-09 23:30:20'),
(1507, 146, 108, 0, 1, '2024-08-09 23:30:20', '2024-08-09 23:30:20'),
(1508, 146, 117, 0, 1, '2024-08-09 23:30:20', '2024-08-09 23:30:20'),
(1509, 146, 158, 0, 1, '2024-08-09 23:30:20', '2024-08-09 23:30:20'),
(1510, 146, 174, 0, 1, '2024-08-09 23:30:20', '2024-08-09 23:30:20'),
(1511, 146, 208, 0, 1, '2024-08-09 23:30:20', '2024-08-09 23:30:20'),
(1512, 146, 219, 0, 1, '2024-08-09 23:30:20', '2024-08-09 23:30:20'),
(1513, 146, 220, 0, 1, '2024-08-09 23:30:20', '2024-08-09 23:30:20'),
(1514, 146, 221, 0, 1, '2024-08-09 23:30:20', '2024-08-09 23:30:20'),
(1515, 146, 222, 0, 1, '2024-08-09 23:30:20', '2024-08-09 23:30:20'),
(1516, 146, 223, 0, 1, '2024-08-09 23:30:20', '2024-08-09 23:30:20'),
(1517, 146, 224, 0, 1, '2024-08-09 23:30:20', '2024-08-09 23:30:20'),
(1518, 146, 225, 0, 1, '2024-08-09 23:30:20', '2024-08-09 23:30:20'),
(1519, 146, 226, 0, 1, '2024-08-09 23:30:20', '2024-08-09 23:30:20'),
(1520, 146, 175, 0.013888888888888888, 1, '2024-08-09 23:30:20', '2024-08-09 23:30:20'),
(1521, 147, 29, 0, 1, '2024-08-09 23:32:01', '2024-08-09 23:32:01'),
(1522, 147, 57, 0, 1, '2024-08-09 23:32:01', '2024-08-09 23:32:01'),
(1523, 147, 86, 0, 1, '2024-08-09 23:32:01', '2024-08-09 23:32:01'),
(1524, 147, 91, 0, 1, '2024-08-09 23:32:01', '2024-08-09 23:32:01'),
(1525, 147, 92, 0, 1, '2024-08-09 23:32:01', '2024-08-09 23:32:01'),
(1526, 147, 95, 0, 1, '2024-08-09 23:32:01', '2024-08-09 23:32:01'),
(1527, 147, 103, 0, 1, '2024-08-09 23:32:01', '2024-08-09 23:32:01'),
(1528, 147, 105, 0, 1, '2024-08-09 23:32:01', '2024-08-09 23:32:01'),
(1529, 147, 107, 0, 1, '2024-08-09 23:32:02', '2024-08-09 23:32:02'),
(1530, 147, 108, 0, 1, '2024-08-09 23:32:02', '2024-08-09 23:32:02'),
(1531, 147, 117, 0, 1, '2024-08-09 23:32:02', '2024-08-09 23:32:02'),
(1532, 147, 158, 0, 1, '2024-08-09 23:32:02', '2024-08-09 23:32:02'),
(1533, 147, 174, 0, 1, '2024-08-09 23:32:02', '2024-08-09 23:32:02'),
(1534, 147, 208, 0, 1, '2024-08-09 23:32:02', '2024-08-09 23:32:02'),
(1535, 147, 219, 0, 1, '2024-08-09 23:32:02', '2024-08-09 23:32:02'),
(1536, 147, 220, 0, 1, '2024-08-09 23:32:02', '2024-08-09 23:32:02'),
(1537, 147, 221, 0, 1, '2024-08-09 23:32:02', '2024-08-09 23:32:02'),
(1538, 147, 222, 0, 1, '2024-08-09 23:32:02', '2024-08-09 23:32:02'),
(1539, 147, 223, 0, 1, '2024-08-09 23:32:02', '2024-08-09 23:32:02'),
(1540, 147, 224, 0, 1, '2024-08-09 23:32:02', '2024-08-09 23:32:02'),
(1541, 147, 225, 0, 1, '2024-08-09 23:32:02', '2024-08-09 23:32:02'),
(1542, 147, 226, 0, 1, '2024-08-09 23:32:02', '2024-08-09 23:32:02'),
(1543, 147, 175, 0.013888888888888888, 1, '2024-08-09 23:32:02', '2024-08-09 23:32:02'),
(1544, 150, 32, 0.05, 1, '2024-08-09 23:56:22', '2024-08-09 23:56:22'),
(1545, 150, 58, 0.017, 1, '2024-08-09 23:56:22', '2024-08-09 23:56:22'),
(1546, 150, 62, 0.147, 1, '2024-08-09 23:56:22', '2024-08-09 23:56:22'),
(1547, 150, 64, 0.147, 1, '2024-08-09 23:56:22', '2024-08-09 23:56:22'),
(1548, 150, 71, 0.008, 1, '2024-08-09 23:56:22', '2024-08-09 23:56:22'),
(1549, 150, 72, 2, 1, '2024-08-09 23:56:23', '2024-08-09 23:56:23'),
(1550, 150, 75, 0.004, 1, '2024-08-09 23:56:23', '2024-08-09 23:56:23'),
(1551, 150, 76, 0.0015, 1, '2024-08-09 23:56:23', '2024-08-09 23:56:23'),
(1552, 150, 77, 0.002, 1, '2024-08-09 23:56:23', '2024-08-09 23:56:23'),
(1553, 150, 86, 0.004, 1, '2024-08-09 23:56:23', '2024-08-09 23:56:23'),
(1554, 150, 95, 0.018, 1, '2024-08-09 23:56:23', '2024-08-09 23:56:23'),
(1555, 150, 100, 0.02, 1, '2024-08-09 23:56:23', '2024-08-09 23:56:23'),
(1556, 150, 149, 0.0029, 1, '2024-08-09 23:56:23', '2024-08-09 23:56:23'),
(1557, 150, 158, 0.1, 1, '2024-08-09 23:56:23', '2024-08-09 23:56:23'),
(1558, 150, 174, 1, 1, '2024-08-09 23:56:23', '2024-08-09 23:56:23'),
(1559, 150, 227, 0, 1, '2024-08-09 23:56:23', '2024-08-09 23:56:23'),
(1560, 150, 228, 0, 1, '2024-08-09 23:56:23', '2024-08-09 23:56:23'),
(1561, 150, 233, 0, 1, '2024-08-09 23:56:23', '2024-08-09 23:56:23'),
(1562, 150, 234, 0, 1, '2024-08-09 23:56:23', '2024-08-09 23:56:23'),
(1563, 150, 235, 1, 1, '2024-08-09 23:56:23', '2024-08-09 23:56:23'),
(1564, 150, 175, 0.013888888888888888, 1, '2024-08-09 23:56:23', '2024-08-09 23:56:23'),
(1565, 149, 32, 0.053, 1, '2024-08-09 23:59:50', '2024-08-09 23:59:50'),
(1566, 149, 58, 0.018, 1, '2024-08-09 23:59:50', '2024-08-09 23:59:50'),
(1567, 149, 62, 0.147, 1, '2024-08-09 23:59:50', '2024-08-09 23:59:50'),
(1568, 149, 64, 0.147, 1, '2024-08-09 23:59:51', '2024-08-09 23:59:51'),
(1569, 149, 71, 0.008, 1, '2024-08-09 23:59:51', '2024-08-09 23:59:51'),
(1570, 149, 72, 0, 1, '2024-08-09 23:59:51', '2024-08-09 23:59:51'),
(1571, 149, 75, 0.004, 1, '2024-08-09 23:59:51', '2024-08-09 23:59:51'),
(1572, 149, 76, 0.0015, 1, '2024-08-09 23:59:51', '2024-08-09 23:59:51'),
(1573, 149, 77, 0.002, 1, '2024-08-09 23:59:51', '2024-08-09 23:59:51'),
(1574, 149, 86, 0.004, 1, '2024-08-09 23:59:51', '2024-08-09 23:59:51'),
(1575, 149, 95, 0.018, 1, '2024-08-09 23:59:51', '2024-08-09 23:59:51'),
(1576, 149, 100, 0.02, 1, '2024-08-09 23:59:51', '2024-08-09 23:59:51'),
(1577, 149, 149, 0.0029, 1, '2024-08-09 23:59:51', '2024-08-09 23:59:51'),
(1578, 149, 158, 0.104, 1, '2024-08-09 23:59:51', '2024-08-09 23:59:51'),
(1579, 149, 174, 2, 1, '2024-08-09 23:59:51', '2024-08-09 23:59:51'),
(1580, 149, 227, 2, 1, '2024-08-09 23:59:51', '2024-08-09 23:59:51'),
(1581, 149, 228, 0, 1, '2024-08-09 23:59:51', '2024-08-09 23:59:51'),
(1582, 149, 233, 0, 1, '2024-08-09 23:59:51', '2024-08-09 23:59:51'),
(1583, 149, 234, 0, 1, '2024-08-09 23:59:51', '2024-08-09 23:59:51'),
(1584, 149, 235, 1, 1, '2024-08-09 23:59:51', '2024-08-09 23:59:51'),
(1585, 149, 175, 0.013888888888888888, 1, '2024-08-09 23:59:51', '2024-08-09 23:59:51'),
(1586, 148, 32, 0.057, 1, '2024-08-10 00:03:11', '2024-08-10 00:03:11'),
(1587, 148, 58, 0.18, 1, '2024-08-10 00:03:12', '2024-08-10 00:03:12'),
(1588, 148, 62, 0.147, 1, '2024-08-10 00:03:12', '2024-08-10 00:03:12'),
(1589, 148, 64, 0.147, 1, '2024-08-10 00:03:12', '2024-08-10 00:03:12'),
(1590, 148, 71, 0.008, 1, '2024-08-10 00:03:12', '2024-08-10 00:03:12'),
(1591, 148, 72, 0, 1, '2024-08-10 00:03:12', '2024-08-10 00:03:12'),
(1592, 148, 75, 0.004, 1, '2024-08-10 00:03:12', '2024-08-10 00:03:12'),
(1593, 148, 76, 0.0015, 1, '2024-08-10 00:03:12', '2024-08-10 00:03:12'),
(1594, 148, 77, 0.002, 1, '2024-08-10 00:03:12', '2024-08-10 00:03:12'),
(1595, 148, 86, 0.004, 1, '2024-08-10 00:03:12', '2024-08-10 00:03:12'),
(1596, 148, 95, 0.018, 1, '2024-08-10 00:03:12', '2024-08-10 00:03:12'),
(1597, 148, 100, 0.02, 1, '2024-08-10 00:03:12', '2024-08-10 00:03:12'),
(1598, 148, 149, 0.0029, 1, '2024-08-10 00:03:12', '2024-08-10 00:03:12'),
(1599, 148, 158, 0.118, 1, '2024-08-10 00:03:12', '2024-08-10 00:03:12'),
(1600, 148, 174, 0, 1, '2024-08-10 00:03:12', '2024-08-10 00:03:12'),
(1601, 148, 227, 0, 1, '2024-08-10 00:03:12', '2024-08-10 00:03:12'),
(1602, 148, 228, 2, 1, '2024-08-10 00:03:12', '2024-08-10 00:03:12'),
(1603, 148, 233, 0, 1, '2024-08-10 00:03:12', '2024-08-10 00:03:12'),
(1604, 148, 234, 0, 1, '2024-08-10 00:03:12', '2024-08-10 00:03:12'),
(1605, 148, 235, 1, 1, '2024-08-10 00:03:12', '2024-08-10 00:03:12'),
(1606, 148, 175, 0.013888888888888888, 1, '2024-08-10 00:03:12', '2024-08-10 00:03:12'),
(1607, 151, 32, 0.06, 1, '2024-08-10 00:07:24', '2024-08-10 00:07:24'),
(1608, 151, 58, 0.02, 1, '2024-08-10 00:07:24', '2024-08-10 00:07:24'),
(1609, 151, 62, 0.147, 1, '2024-08-10 00:07:25', '2024-08-10 00:07:25'),
(1610, 151, 64, 0.147, 1, '2024-08-10 00:07:25', '2024-08-10 00:07:25'),
(1611, 151, 71, 0.008, 1, '2024-08-10 00:07:25', '2024-08-10 00:07:25'),
(1612, 151, 72, 0, 1, '2024-08-10 00:07:25', '2024-08-10 00:07:25'),
(1613, 151, 75, 0.004, 1, '2024-08-10 00:07:25', '2024-08-10 00:07:25'),
(1614, 151, 76, 0.0015, 1, '2024-08-10 00:07:25', '2024-08-10 00:07:25'),
(1615, 151, 77, 0.002, 1, '2024-08-10 00:07:25', '2024-08-10 00:07:25'),
(1616, 151, 86, 0.004, 1, '2024-08-10 00:07:25', '2024-08-10 00:07:25'),
(1617, 151, 95, 0.018, 1, '2024-08-10 00:07:25', '2024-08-10 00:07:25'),
(1618, 151, 100, 0.02, 1, '2024-08-10 00:07:25', '2024-08-10 00:07:25'),
(1619, 151, 149, 0.0029, 1, '2024-08-10 00:07:25', '2024-08-10 00:07:25'),
(1620, 151, 158, 0.127, 1, '2024-08-10 00:07:25', '2024-08-10 00:07:25'),
(1621, 151, 174, 2, 1, '2024-08-10 00:07:25', '2024-08-10 00:07:25'),
(1622, 151, 227, 0, 1, '2024-08-10 00:07:25', '2024-08-10 00:07:25'),
(1623, 151, 228, 0, 1, '2024-08-10 00:07:25', '2024-08-10 00:07:25'),
(1624, 151, 233, 2, 1, '2024-08-10 00:07:25', '2024-08-10 00:07:25'),
(1625, 151, 234, 0, 1, '2024-08-10 00:07:25', '2024-08-10 00:07:25'),
(1626, 151, 235, 1, 1, '2024-08-10 00:07:25', '2024-08-10 00:07:25'),
(1627, 151, 175, 0.013888888888888888, 1, '2024-08-10 00:07:25', '2024-08-10 00:07:25'),
(1628, 152, 32, 0.075, 1, '2024-08-10 00:12:36', '2024-08-10 00:12:36'),
(1629, 152, 58, 0.13, 1, '2024-08-10 00:12:36', '2024-08-10 00:12:36'),
(1630, 152, 62, 0.147, 1, '2024-08-10 00:12:36', '2024-08-10 00:12:36'),
(1631, 152, 64, 0.147, 1, '2024-08-10 00:12:36', '2024-08-10 00:12:36'),
(1632, 152, 71, 0.008, 1, '2024-08-10 00:12:36', '2024-08-10 00:12:36'),
(1633, 152, 72, 0, 1, '2024-08-10 00:12:37', '2024-08-10 00:12:37'),
(1634, 152, 75, 0.004, 1, '2024-08-10 00:12:37', '2024-08-10 00:12:37'),
(1635, 152, 76, 0.0015, 1, '2024-08-10 00:12:37', '2024-08-10 00:12:37'),
(1636, 152, 77, 0.002, 1, '2024-08-10 00:12:37', '2024-08-10 00:12:37'),
(1637, 152, 86, 0.0045, 1, '2024-08-10 00:12:37', '2024-08-10 00:12:37'),
(1638, 152, 95, 0.018, 1, '2024-08-10 00:12:37', '2024-08-10 00:12:37'),
(1639, 152, 32, 0.075, 1, '2024-08-10 00:12:37', '2024-08-10 00:12:37'),
(1640, 152, 100, 0.02, 1, '2024-08-10 00:12:37', '2024-08-10 00:12:37'),
(1641, 152, 58, 0.13, 1, '2024-08-10 00:12:37', '2024-08-10 00:12:37'),
(1642, 152, 149, 0.0029, 1, '2024-08-10 00:12:37', '2024-08-10 00:12:37'),
(1643, 152, 62, 0.147, 1, '2024-08-10 00:12:37', '2024-08-10 00:12:37'),
(1644, 152, 158, 0.13, 1, '2024-08-10 00:12:37', '2024-08-10 00:12:37'),
(1645, 152, 64, 0.147, 1, '2024-08-10 00:12:37', '2024-08-10 00:12:37'),
(1646, 152, 174, 2, 1, '2024-08-10 00:12:37', '2024-08-10 00:12:37'),
(1647, 152, 71, 0.008, 1, '2024-08-10 00:12:37', '2024-08-10 00:12:37'),
(1648, 152, 227, 0, 1, '2024-08-10 00:12:37', '2024-08-10 00:12:37'),
(1649, 152, 72, 0, 1, '2024-08-10 00:12:37', '2024-08-10 00:12:37'),
(1650, 152, 228, 0, 1, '2024-08-10 00:12:37', '2024-08-10 00:12:37'),
(1651, 152, 75, 0.004, 1, '2024-08-10 00:12:37', '2024-08-10 00:12:37'),
(1652, 152, 233, 0, 1, '2024-08-10 00:12:37', '2024-08-10 00:12:37'),
(1653, 152, 76, 0.0015, 1, '2024-08-10 00:12:37', '2024-08-10 00:12:37'),
(1654, 152, 234, 2, 1, '2024-08-10 00:12:37', '2024-08-10 00:12:37'),
(1655, 152, 77, 0.002, 1, '2024-08-10 00:12:37', '2024-08-10 00:12:37'),
(1656, 152, 235, 1, 1, '2024-08-10 00:12:37', '2024-08-10 00:12:37'),
(1657, 152, 86, 0.0045, 1, '2024-08-10 00:12:37', '2024-08-10 00:12:37'),
(1658, 152, 175, 0.013888888888888888, 1, '2024-08-10 00:12:37', '2024-08-10 00:12:37'),
(1659, 152, 95, 0.018, 1, '2024-08-10 00:12:37', '2024-08-10 00:12:37'),
(1660, 152, 100, 0.02, 1, '2024-08-10 00:12:37', '2024-08-10 00:12:37'),
(1661, 152, 149, 0.0029, 1, '2024-08-10 00:12:37', '2024-08-10 00:12:37'),
(1662, 152, 158, 0.13, 1, '2024-08-10 00:12:37', '2024-08-10 00:12:37'),
(1663, 152, 174, 2, 1, '2024-08-10 00:12:37', '2024-08-10 00:12:37'),
(1664, 152, 227, 0, 1, '2024-08-10 00:12:37', '2024-08-10 00:12:37'),
(1665, 152, 228, 0, 1, '2024-08-10 00:12:37', '2024-08-10 00:12:37'),
(1666, 152, 233, 0, 1, '2024-08-10 00:12:37', '2024-08-10 00:12:37'),
(1667, 152, 234, 2, 1, '2024-08-10 00:12:38', '2024-08-10 00:12:38'),
(1668, 152, 235, 1, 1, '2024-08-10 00:12:38', '2024-08-10 00:12:38'),
(1669, 152, 175, 0.013888888888888888, 1, '2024-08-10 00:12:38', '2024-08-10 00:12:38'),
(1670, 155, 40, 0.057, 1, '2024-08-10 00:35:29', '2024-08-10 00:35:29'),
(1671, 155, 62, 0.006, 1, '2024-08-10 00:35:30', '2024-08-10 00:35:30'),
(1672, 155, 64, 0.006, 1, '2024-08-10 00:35:30', '2024-08-10 00:35:30'),
(1673, 155, 69, 1, 1, '2024-08-10 00:35:30', '2024-08-10 00:35:30'),
(1674, 155, 71, 0.009, 1, '2024-08-10 00:35:30', '2024-08-10 00:35:30'),
(1675, 155, 75, 0.0014, 1, '2024-08-10 00:35:30', '2024-08-10 00:35:30'),
(1676, 155, 77, 0.0028, 1, '2024-08-10 00:35:30', '2024-08-10 00:35:30'),
(1677, 155, 78, 0.0035, 1, '2024-08-10 00:35:30', '2024-08-10 00:35:30'),
(1678, 155, 94, 0.018, 1, '2024-08-10 00:35:30', '2024-08-10 00:35:30'),
(1679, 155, 150, 0.002, 1, '2024-08-10 00:35:30', '2024-08-10 00:35:30'),
(1680, 155, 159, 0.1, 1, '2024-08-10 00:35:30', '2024-08-10 00:35:30'),
(1681, 155, 174, 2, 1, '2024-08-10 00:35:30', '2024-08-10 00:35:30'),
(1682, 155, 176, 0.2, 1, '2024-08-10 00:35:30', '2024-08-10 00:35:30'),
(1683, 155, 236, 0.017, 1, '2024-08-10 00:35:30', '2024-08-10 00:35:30'),
(1684, 155, 237, 2, 1, '2024-08-10 00:35:30', '2024-08-10 00:35:30'),
(1685, 155, 238, 0, 1, '2024-08-10 00:35:30', '2024-08-10 00:35:30'),
(1686, 155, 239, 0, 1, '2024-08-10 00:35:30', '2024-08-10 00:35:30'),
(1687, 155, 240, 0, 1, '2024-08-10 00:35:30', '2024-08-10 00:35:30'),
(1688, 155, 241, 0, 1, '2024-08-10 00:35:30', '2024-08-10 00:35:30'),
(1689, 155, 175, 0.013888888888888888, 1, '2024-08-10 00:35:30', '2024-08-10 00:35:30'),
(1690, 154, 40, 0.053, 1, '2024-08-10 00:38:32', '2024-08-10 00:38:32'),
(1691, 154, 62, 0.006, 1, '2024-08-10 00:38:32', '2024-08-10 00:38:32'),
(1692, 154, 64, 0.006, 1, '2024-08-10 00:38:32', '2024-08-10 00:38:32'),
(1693, 154, 69, 1, 1, '2024-08-10 00:38:32', '2024-08-10 00:38:32'),
(1694, 154, 71, 0.009, 1, '2024-08-10 00:38:32', '2024-08-10 00:38:32'),
(1695, 154, 75, 0.0014, 1, '2024-08-10 00:38:32', '2024-08-10 00:38:32'),
(1696, 154, 77, 0.0028, 1, '2024-08-10 00:38:32', '2024-08-10 00:38:32'),
(1697, 154, 78, 0.0035, 1, '2024-08-10 00:38:33', '2024-08-10 00:38:33'),
(1698, 154, 94, 0.018, 1, '2024-08-10 00:38:33', '2024-08-10 00:38:33'),
(1699, 154, 150, 0.002, 1, '2024-08-10 00:38:33', '2024-08-10 00:38:33'),
(1700, 154, 159, 0.104, 1, '2024-08-10 00:38:33', '2024-08-10 00:38:33'),
(1701, 154, 174, 2, 1, '2024-08-10 00:38:33', '2024-08-10 00:38:33'),
(1702, 154, 40, 0.053, 1, '2024-08-10 00:38:33', '2024-08-10 00:38:33'),
(1703, 154, 176, 0.2, 1, '2024-08-10 00:38:33', '2024-08-10 00:38:33'),
(1704, 154, 62, 0.006, 1, '2024-08-10 00:38:33', '2024-08-10 00:38:33'),
(1705, 154, 236, 0.018, 1, '2024-08-10 00:38:33', '2024-08-10 00:38:33'),
(1706, 154, 64, 0.006, 1, '2024-08-10 00:38:33', '2024-08-10 00:38:33'),
(1707, 154, 237, 0, 1, '2024-08-10 00:38:33', '2024-08-10 00:38:33'),
(1708, 154, 69, 1, 1, '2024-08-10 00:38:33', '2024-08-10 00:38:33'),
(1709, 154, 238, 2, 1, '2024-08-10 00:38:33', '2024-08-10 00:38:33'),
(1710, 154, 71, 0.009, 1, '2024-08-10 00:38:33', '2024-08-10 00:38:33'),
(1711, 154, 239, 0, 1, '2024-08-10 00:38:33', '2024-08-10 00:38:33'),
(1712, 154, 75, 0.0014, 1, '2024-08-10 00:38:33', '2024-08-10 00:38:33'),
(1713, 154, 240, 0, 1, '2024-08-10 00:38:33', '2024-08-10 00:38:33'),
(1714, 154, 77, 0.0028, 1, '2024-08-10 00:38:33', '2024-08-10 00:38:33'),
(1715, 154, 241, 0, 1, '2024-08-10 00:38:33', '2024-08-10 00:38:33'),
(1716, 154, 78, 0.0035, 1, '2024-08-10 00:38:33', '2024-08-10 00:38:33'),
(1717, 154, 175, 0.013888888888888888, 1, '2024-08-10 00:38:33', '2024-08-10 00:38:33'),
(1718, 154, 94, 0.018, 1, '2024-08-10 00:38:33', '2024-08-10 00:38:33'),
(1719, 154, 150, 0.002, 1, '2024-08-10 00:38:33', '2024-08-10 00:38:33'),
(1720, 154, 159, 0.104, 1, '2024-08-10 00:38:33', '2024-08-10 00:38:33'),
(1721, 154, 174, 2, 1, '2024-08-10 00:38:33', '2024-08-10 00:38:33'),
(1722, 154, 176, 0.2, 1, '2024-08-10 00:38:33', '2024-08-10 00:38:33'),
(1723, 154, 236, 0.018, 1, '2024-08-10 00:38:33', '2024-08-10 00:38:33'),
(1724, 154, 237, 0, 1, '2024-08-10 00:38:33', '2024-08-10 00:38:33'),
(1725, 154, 238, 2, 1, '2024-08-10 00:38:33', '2024-08-10 00:38:33'),
(1726, 154, 239, 0, 1, '2024-08-10 00:38:33', '2024-08-10 00:38:33'),
(1727, 154, 240, 0, 1, '2024-08-10 00:38:33', '2024-08-10 00:38:33'),
(1728, 154, 241, 0, 1, '2024-08-10 00:38:33', '2024-08-10 00:38:33'),
(1729, 154, 175, 0.013888888888888888, 1, '2024-08-10 00:38:33', '2024-08-10 00:38:33'),
(1730, 154, 40, 0.053, 1, '2024-08-10 00:38:33', '2024-08-10 00:38:33'),
(1731, 154, 62, 0.006, 1, '2024-08-10 00:38:34', '2024-08-10 00:38:34'),
(1732, 154, 64, 0.006, 1, '2024-08-10 00:38:34', '2024-08-10 00:38:34'),
(1733, 154, 69, 1, 1, '2024-08-10 00:38:34', '2024-08-10 00:38:34'),
(1734, 154, 71, 0.009, 1, '2024-08-10 00:38:34', '2024-08-10 00:38:34'),
(1735, 154, 75, 0.0014, 1, '2024-08-10 00:38:34', '2024-08-10 00:38:34'),
(1736, 154, 77, 0.0028, 1, '2024-08-10 00:38:34', '2024-08-10 00:38:34'),
(1737, 154, 78, 0.0035, 1, '2024-08-10 00:38:34', '2024-08-10 00:38:34'),
(1738, 154, 94, 0.018, 1, '2024-08-10 00:38:34', '2024-08-10 00:38:34'),
(1739, 154, 150, 0.002, 1, '2024-08-10 00:38:34', '2024-08-10 00:38:34'),
(1740, 154, 159, 0.104, 1, '2024-08-10 00:38:34', '2024-08-10 00:38:34'),
(1741, 154, 174, 2, 1, '2024-08-10 00:38:34', '2024-08-10 00:38:34'),
(1742, 154, 176, 0.2, 1, '2024-08-10 00:38:34', '2024-08-10 00:38:34'),
(1743, 154, 236, 0.018, 1, '2024-08-10 00:38:34', '2024-08-10 00:38:34'),
(1744, 154, 237, 0, 1, '2024-08-10 00:38:34', '2024-08-10 00:38:34'),
(1745, 154, 238, 2, 1, '2024-08-10 00:38:34', '2024-08-10 00:38:34'),
(1746, 154, 239, 0, 1, '2024-08-10 00:38:34', '2024-08-10 00:38:34'),
(1747, 154, 240, 0, 1, '2024-08-10 00:38:34', '2024-08-10 00:38:34'),
(1748, 154, 241, 0, 1, '2024-08-10 00:38:34', '2024-08-10 00:38:34'),
(1749, 154, 175, 0.013888888888888888, 1, '2024-08-10 00:38:34', '2024-08-10 00:38:34'),
(1750, 154, 40, 0.053, 1, '2024-08-10 00:38:35', '2024-08-10 00:38:35'),
(1751, 154, 62, 0.006, 1, '2024-08-10 00:38:35', '2024-08-10 00:38:35'),
(1752, 154, 64, 0.006, 1, '2024-08-10 00:38:35', '2024-08-10 00:38:35'),
(1753, 154, 69, 1, 1, '2024-08-10 00:38:35', '2024-08-10 00:38:35'),
(1754, 154, 71, 0.009, 1, '2024-08-10 00:38:35', '2024-08-10 00:38:35'),
(1755, 154, 75, 0.0014, 1, '2024-08-10 00:38:35', '2024-08-10 00:38:35'),
(1756, 154, 77, 0.0028, 1, '2024-08-10 00:38:35', '2024-08-10 00:38:35'),
(1757, 154, 78, 0.0035, 1, '2024-08-10 00:38:35', '2024-08-10 00:38:35'),
(1758, 154, 94, 0.018, 1, '2024-08-10 00:38:35', '2024-08-10 00:38:35'),
(1759, 154, 150, 0.002, 1, '2024-08-10 00:38:36', '2024-08-10 00:38:36'),
(1760, 154, 159, 0.104, 1, '2024-08-10 00:38:36', '2024-08-10 00:38:36'),
(1761, 154, 174, 2, 1, '2024-08-10 00:38:36', '2024-08-10 00:38:36'),
(1762, 154, 176, 0.2, 1, '2024-08-10 00:38:36', '2024-08-10 00:38:36'),
(1763, 154, 236, 0.018, 1, '2024-08-10 00:38:36', '2024-08-10 00:38:36'),
(1764, 154, 237, 0, 1, '2024-08-10 00:38:36', '2024-08-10 00:38:36'),
(1765, 154, 238, 2, 1, '2024-08-10 00:38:36', '2024-08-10 00:38:36'),
(1766, 154, 239, 0, 1, '2024-08-10 00:38:36', '2024-08-10 00:38:36'),
(1767, 154, 240, 0, 1, '2024-08-10 00:38:36', '2024-08-10 00:38:36'),
(1768, 154, 241, 0, 1, '2024-08-10 00:38:36', '2024-08-10 00:38:36'),
(1769, 154, 175, 0.013888888888888888, 1, '2024-08-10 00:38:36', '2024-08-10 00:38:36'),
(1770, 154, 40, 0.053, 1, '2024-08-10 00:38:37', '2024-08-10 00:38:37'),
(1771, 154, 62, 0.006, 1, '2024-08-10 00:38:37', '2024-08-10 00:38:37'),
(1772, 154, 64, 0.006, 1, '2024-08-10 00:38:37', '2024-08-10 00:38:37'),
(1773, 154, 69, 1, 1, '2024-08-10 00:38:37', '2024-08-10 00:38:37'),
(1774, 154, 71, 0.009, 1, '2024-08-10 00:38:37', '2024-08-10 00:38:37'),
(1775, 154, 75, 0.0014, 1, '2024-08-10 00:38:37', '2024-08-10 00:38:37'),
(1776, 154, 77, 0.0028, 1, '2024-08-10 00:38:37', '2024-08-10 00:38:37'),
(1777, 154, 78, 0.0035, 1, '2024-08-10 00:38:37', '2024-08-10 00:38:37'),
(1778, 154, 94, 0.018, 1, '2024-08-10 00:38:38', '2024-08-10 00:38:38'),
(1779, 154, 150, 0.002, 1, '2024-08-10 00:38:38', '2024-08-10 00:38:38'),
(1780, 154, 159, 0.104, 1, '2024-08-10 00:38:38', '2024-08-10 00:38:38'),
(1781, 154, 174, 2, 1, '2024-08-10 00:38:38', '2024-08-10 00:38:38'),
(1782, 154, 176, 0.2, 1, '2024-08-10 00:38:38', '2024-08-10 00:38:38'),
(1783, 154, 236, 0.018, 1, '2024-08-10 00:38:38', '2024-08-10 00:38:38'),
(1784, 154, 237, 0, 1, '2024-08-10 00:38:38', '2024-08-10 00:38:38'),
(1785, 154, 238, 2, 1, '2024-08-10 00:38:38', '2024-08-10 00:38:38'),
(1786, 154, 239, 0, 1, '2024-08-10 00:38:38', '2024-08-10 00:38:38'),
(1787, 154, 240, 0, 1, '2024-08-10 00:38:38', '2024-08-10 00:38:38'),
(1788, 154, 241, 0, 1, '2024-08-10 00:38:38', '2024-08-10 00:38:38'),
(1789, 154, 175, 0.013888888888888888, 1, '2024-08-10 00:38:38', '2024-08-10 00:38:38'),
(1790, 153, 40, 0.057, 1, '2024-08-10 00:44:40', '2024-08-10 00:44:40'),
(1791, 153, 62, 0.006, 1, '2024-08-10 00:44:41', '2024-08-10 00:44:41'),
(1792, 153, 64, 0.006, 1, '2024-08-10 00:44:41', '2024-08-10 00:44:41'),
(1793, 153, 69, 1, 1, '2024-08-10 00:44:41', '2024-08-10 00:44:41'),
(1794, 153, 71, 0.009, 1, '2024-08-10 00:44:41', '2024-08-10 00:44:41'),
(1795, 153, 75, 0.0014, 1, '2024-08-10 00:44:41', '2024-08-10 00:44:41'),
(1796, 153, 77, 0.0028, 1, '2024-08-10 00:44:41', '2024-08-10 00:44:41'),
(1797, 153, 78, 0.0035, 1, '2024-08-10 00:44:41', '2024-08-10 00:44:41'),
(1798, 153, 94, 0.018, 1, '2024-08-10 00:44:41', '2024-08-10 00:44:41'),
(1799, 153, 150, 0.002, 1, '2024-08-10 00:44:41', '2024-08-10 00:44:41'),
(1800, 153, 159, 0.118, 1, '2024-08-10 00:44:41', '2024-08-10 00:44:41'),
(1801, 153, 174, 2, 1, '2024-08-10 00:44:41', '2024-08-10 00:44:41'),
(1802, 153, 176, 0.2, 1, '2024-08-10 00:44:41', '2024-08-10 00:44:41'),
(1803, 153, 236, 0.018, 1, '2024-08-10 00:44:41', '2024-08-10 00:44:41'),
(1804, 153, 237, 0, 1, '2024-08-10 00:44:41', '2024-08-10 00:44:41'),
(1805, 153, 238, 0, 1, '2024-08-10 00:44:42', '2024-08-10 00:44:42'),
(1806, 153, 239, 2, 1, '2024-08-10 00:44:42', '2024-08-10 00:44:42'),
(1807, 153, 240, 0, 1, '2024-08-10 00:44:42', '2024-08-10 00:44:42'),
(1808, 153, 241, 0, 1, '2024-08-10 00:44:42', '2024-08-10 00:44:42'),
(1809, 153, 175, 0.013888888888888888, 1, '2024-08-10 00:44:42', '2024-08-10 00:44:42'),
(1810, 156, 40, 0.06, 1, '2024-08-10 00:48:07', '2024-08-10 00:48:07'),
(1811, 156, 62, 0.006, 1, '2024-08-10 00:48:08', '2024-08-10 00:48:08'),
(1812, 156, 64, 0.006, 1, '2024-08-10 00:48:08', '2024-08-10 00:48:08'),
(1813, 156, 69, 1, 1, '2024-08-10 00:48:08', '2024-08-10 00:48:08'),
(1814, 156, 71, 0.009, 1, '2024-08-10 00:48:08', '2024-08-10 00:48:08'),
(1815, 156, 75, 0.0014, 1, '2024-08-10 00:48:08', '2024-08-10 00:48:08'),
(1816, 156, 77, 0.0028, 1, '2024-08-10 00:48:08', '2024-08-10 00:48:08'),
(1817, 156, 78, 0.0035, 1, '2024-08-10 00:48:08', '2024-08-10 00:48:08'),
(1818, 156, 94, 0.018, 1, '2024-08-10 00:48:08', '2024-08-10 00:48:08'),
(1819, 156, 150, 0.002, 1, '2024-08-10 00:48:08', '2024-08-10 00:48:08'),
(1820, 156, 159, 0.127, 1, '2024-08-10 00:48:08', '2024-08-10 00:48:08'),
(1821, 156, 174, 2, 1, '2024-08-10 00:48:08', '2024-08-10 00:48:08'),
(1822, 156, 176, 0.2, 1, '2024-08-10 00:48:08', '2024-08-10 00:48:08'),
(1823, 156, 236, 0.02, 1, '2024-08-10 00:48:08', '2024-08-10 00:48:08'),
(1824, 156, 237, 0, 1, '2024-08-10 00:48:08', '2024-08-10 00:48:08'),
(1825, 156, 238, 0, 1, '2024-08-10 00:48:08', '2024-08-10 00:48:08'),
(1826, 156, 239, 0, 1, '2024-08-10 00:48:08', '2024-08-10 00:48:08'),
(1827, 156, 240, 2, 1, '2024-08-10 00:48:08', '2024-08-10 00:48:08'),
(1828, 156, 241, 0, 1, '2024-08-10 00:48:08', '2024-08-10 00:48:08'),
(1829, 156, 175, 0.013888888888888888, 1, '2024-08-10 00:48:08', '2024-08-10 00:48:08'),
(1830, 157, 40, 0.075, 1, '2024-08-10 00:51:22', '2024-08-10 00:51:22'),
(1831, 157, 62, 0.006, 1, '2024-08-10 00:51:22', '2024-08-10 00:51:22'),
(1832, 157, 64, 0.006, 1, '2024-08-10 00:51:22', '2024-08-10 00:51:22'),
(1833, 157, 69, 1, 1, '2024-08-10 00:51:22', '2024-08-10 00:51:22'),
(1834, 157, 71, 0.009, 1, '2024-08-10 00:51:22', '2024-08-10 00:51:22'),
(1835, 157, 75, 0.0014, 1, '2024-08-10 00:51:23', '2024-08-10 00:51:23'),
(1836, 157, 77, 0.0028, 1, '2024-08-10 00:51:23', '2024-08-10 00:51:23'),
(1837, 157, 78, 0.0035, 1, '2024-08-10 00:51:23', '2024-08-10 00:51:23'),
(1838, 157, 40, 0.075, 1, '2024-08-10 00:51:23', '2024-08-10 00:51:23'),
(1839, 157, 94, 0.018, 1, '2024-08-10 00:51:23', '2024-08-10 00:51:23'),
(1840, 157, 150, 0.002, 1, '2024-08-10 00:51:23', '2024-08-10 00:51:23'),
(1841, 157, 62, 0.006, 1, '2024-08-10 00:51:23', '2024-08-10 00:51:23'),
(1842, 157, 159, 0.13, 1, '2024-08-10 00:51:23', '2024-08-10 00:51:23'),
(1843, 157, 64, 0.006, 1, '2024-08-10 00:51:23', '2024-08-10 00:51:23'),
(1844, 157, 174, 2, 1, '2024-08-10 00:51:23', '2024-08-10 00:51:23'),
(1845, 157, 69, 1, 1, '2024-08-10 00:51:23', '2024-08-10 00:51:23'),
(1846, 157, 176, 0.2, 1, '2024-08-10 00:51:23', '2024-08-10 00:51:23'),
(1847, 157, 71, 0.009, 1, '2024-08-10 00:51:23', '2024-08-10 00:51:23'),
(1848, 157, 236, 0.02, 1, '2024-08-10 00:51:23', '2024-08-10 00:51:23'),
(1849, 157, 75, 0.0014, 1, '2024-08-10 00:51:23', '2024-08-10 00:51:23'),
(1850, 157, 237, 0, 1, '2024-08-10 00:51:23', '2024-08-10 00:51:23'),
(1851, 157, 77, 0.0028, 1, '2024-08-10 00:51:23', '2024-08-10 00:51:23'),
(1852, 157, 238, 0, 1, '2024-08-10 00:51:23', '2024-08-10 00:51:23'),
(1853, 157, 78, 0.0035, 1, '2024-08-10 00:51:23', '2024-08-10 00:51:23'),
(1854, 157, 239, 0, 1, '2024-08-10 00:51:23', '2024-08-10 00:51:23'),
(1855, 157, 94, 0.018, 1, '2024-08-10 00:51:23', '2024-08-10 00:51:23'),
(1856, 157, 240, 0, 1, '2024-08-10 00:51:23', '2024-08-10 00:51:23'),
(1857, 157, 150, 0.002, 1, '2024-08-10 00:51:23', '2024-08-10 00:51:23'),
(1858, 157, 241, 2, 1, '2024-08-10 00:51:23', '2024-08-10 00:51:23'),
(1859, 157, 159, 0.13, 1, '2024-08-10 00:51:23', '2024-08-10 00:51:23'),
(1860, 157, 175, 0.013888888888888888, 1, '2024-08-10 00:51:23', '2024-08-10 00:51:23'),
(1861, 157, 174, 2, 1, '2024-08-10 00:51:23', '2024-08-10 00:51:23'),
(1862, 157, 176, 0.2, 1, '2024-08-10 00:51:23', '2024-08-10 00:51:23'),
(1863, 157, 40, 0.075, 1, '2024-08-10 00:51:23', '2024-08-10 00:51:23'),
(1864, 157, 236, 0.02, 1, '2024-08-10 00:51:23', '2024-08-10 00:51:23'),
(1865, 157, 62, 0.006, 1, '2024-08-10 00:51:23', '2024-08-10 00:51:23'),
(1866, 157, 237, 0, 1, '2024-08-10 00:51:23', '2024-08-10 00:51:23'),
(1867, 157, 64, 0.006, 1, '2024-08-10 00:51:23', '2024-08-10 00:51:23'),
(1868, 157, 238, 0, 1, '2024-08-10 00:51:23', '2024-08-10 00:51:23'),
(1869, 157, 69, 1, 1, '2024-08-10 00:51:24', '2024-08-10 00:51:24'),
(1870, 157, 239, 0, 1, '2024-08-10 00:51:24', '2024-08-10 00:51:24'),
(1871, 157, 71, 0.009, 1, '2024-08-10 00:51:24', '2024-08-10 00:51:24'),
(1872, 157, 240, 0, 1, '2024-08-10 00:51:24', '2024-08-10 00:51:24'),
(1873, 157, 75, 0.0014, 1, '2024-08-10 00:51:24', '2024-08-10 00:51:24'),
(1874, 157, 241, 2, 1, '2024-08-10 00:51:24', '2024-08-10 00:51:24'),
(1875, 157, 77, 0.0028, 1, '2024-08-10 00:51:24', '2024-08-10 00:51:24'),
(1876, 157, 175, 0.013888888888888888, 1, '2024-08-10 00:51:24', '2024-08-10 00:51:24'),
(1877, 157, 78, 0.0035, 1, '2024-08-10 00:51:24', '2024-08-10 00:51:24'),
(1878, 157, 94, 0.018, 1, '2024-08-10 00:51:24', '2024-08-10 00:51:24'),
(1879, 157, 150, 0.002, 1, '2024-08-10 00:51:24', '2024-08-10 00:51:24'),
(1880, 157, 159, 0.13, 1, '2024-08-10 00:51:24', '2024-08-10 00:51:24'),
(1881, 157, 174, 2, 1, '2024-08-10 00:51:24', '2024-08-10 00:51:24'),
(1882, 157, 176, 0.2, 1, '2024-08-10 00:51:24', '2024-08-10 00:51:24'),
(1883, 157, 236, 0.02, 1, '2024-08-10 00:51:24', '2024-08-10 00:51:24'),
(1884, 157, 237, 0, 1, '2024-08-10 00:51:24', '2024-08-10 00:51:24'),
(1885, 157, 238, 0, 1, '2024-08-10 00:51:24', '2024-08-10 00:51:24'),
(1886, 157, 239, 0, 1, '2024-08-10 00:51:24', '2024-08-10 00:51:24'),
(1887, 157, 240, 0, 1, '2024-08-10 00:51:24', '2024-08-10 00:51:24'),
(1888, 157, 241, 2, 1, '2024-08-10 00:51:24', '2024-08-10 00:51:24'),
(1889, 157, 175, 0.013888888888888888, 1, '2024-08-10 00:51:24', '2024-08-10 00:51:24'),
(1890, 157, 40, 0.075, 1, '2024-08-10 00:51:25', '2024-08-10 00:51:25'),
(1891, 157, 62, 0.006, 1, '2024-08-10 00:51:25', '2024-08-10 00:51:25'),
(1892, 157, 64, 0.006, 1, '2024-08-10 00:51:25', '2024-08-10 00:51:25'),
(1893, 157, 69, 1, 1, '2024-08-10 00:51:26', '2024-08-10 00:51:26'),
(1894, 157, 71, 0.009, 1, '2024-08-10 00:51:26', '2024-08-10 00:51:26'),
(1895, 157, 75, 0.0014, 1, '2024-08-10 00:51:26', '2024-08-10 00:51:26'),
(1896, 157, 77, 0.0028, 1, '2024-08-10 00:51:26', '2024-08-10 00:51:26'),
(1897, 157, 78, 0.0035, 1, '2024-08-10 00:51:26', '2024-08-10 00:51:26'),
(1898, 157, 94, 0.018, 1, '2024-08-10 00:51:26', '2024-08-10 00:51:26'),
(1899, 157, 150, 0.002, 1, '2024-08-10 00:51:26', '2024-08-10 00:51:26'),
(1900, 157, 159, 0.13, 1, '2024-08-10 00:51:26', '2024-08-10 00:51:26'),
(1901, 157, 174, 2, 1, '2024-08-10 00:51:26', '2024-08-10 00:51:26'),
(1902, 157, 176, 0.2, 1, '2024-08-10 00:51:26', '2024-08-10 00:51:26'),
(1903, 157, 236, 0.02, 1, '2024-08-10 00:51:26', '2024-08-10 00:51:26'),
(1904, 157, 237, 0, 1, '2024-08-10 00:51:26', '2024-08-10 00:51:26'),
(1905, 157, 238, 0, 1, '2024-08-10 00:51:26', '2024-08-10 00:51:26'),
(1906, 157, 239, 0, 1, '2024-08-10 00:51:26', '2024-08-10 00:51:26'),
(1907, 157, 240, 0, 1, '2024-08-10 00:51:26', '2024-08-10 00:51:26'),
(1908, 157, 241, 2, 1, '2024-08-10 00:51:26', '2024-08-10 00:51:26'),
(1909, 157, 175, 0.013888888888888888, 1, '2024-08-10 00:51:26', '2024-08-10 00:51:26'),
(1910, 160, 32, 0.039, 1, '2024-08-10 01:06:21', '2024-08-10 01:06:21'),
(1911, 160, 58, 0.016, 1, '2024-08-10 01:06:21', '2024-08-10 01:06:21'),
(1912, 160, 62, 0.006, 1, '2024-08-10 01:06:22', '2024-08-10 01:06:22'),
(1913, 160, 64, 0.006, 1, '2024-08-10 01:06:22', '2024-08-10 01:06:22'),
(1914, 160, 69, 1, 1, '2024-08-10 01:06:22', '2024-08-10 01:06:22'),
(1915, 160, 71, 0.013, 1, '2024-08-10 01:06:22', '2024-08-10 01:06:22'),
(1916, 160, 75, 0.0014, 1, '2024-08-10 01:06:22', '2024-08-10 01:06:22'),
(1917, 160, 76, 0.0014, 1, '2024-08-10 01:06:22', '2024-08-10 01:06:22'),
(1918, 160, 77, 0.0014, 1, '2024-08-10 01:06:22', '2024-08-10 01:06:22'),
(1919, 160, 86, 0.0035, 1, '2024-08-10 01:06:22', '2024-08-10 01:06:22'),
(1920, 160, 100, 0.014, 1, '2024-08-10 01:06:22', '2024-08-10 01:06:22'),
(1921, 160, 149, 0.002, 1, '2024-08-10 01:06:22', '2024-08-10 01:06:22'),
(1922, 160, 158, 0.067, 1, '2024-08-10 01:06:22', '2024-08-10 01:06:22'),
(1923, 160, 159, 0.004, 1, '2024-08-10 01:06:22', '2024-08-10 01:06:22'),
(1924, 160, 174, 2, 1, '2024-08-10 01:06:22', '2024-08-10 01:06:22'),
(1925, 160, 242, 2, 1, '2024-08-10 01:06:22', '2024-08-10 01:06:22'),
(1926, 160, 243, 0, 1, '2024-08-10 01:06:22', '2024-08-10 01:06:22'),
(1927, 160, 244, 0, 1, '2024-08-10 01:06:22', '2024-08-10 01:06:22'),
(1928, 160, 245, 0, 1, '2024-08-10 01:06:22', '2024-08-10 01:06:22'),
(1929, 160, 246, 0, 1, '2024-08-10 01:06:22', '2024-08-10 01:06:22'),
(1930, 160, 175, 0.013888888888888888, 1, '2024-08-10 01:06:22', '2024-08-10 01:06:22'),
(1931, 159, 32, 0.041, 1, '2024-08-10 01:10:13', '2024-08-10 01:10:13'),
(1932, 159, 58, 0.018, 1, '2024-08-10 01:10:14', '2024-08-10 01:10:14'),
(1933, 159, 62, 0.006, 1, '2024-08-10 01:10:14', '2024-08-10 01:10:14'),
(1934, 159, 64, 0.006, 1, '2024-08-10 01:10:14', '2024-08-10 01:10:14'),
(1935, 159, 69, 1, 1, '2024-08-10 01:10:14', '2024-08-10 01:10:14'),
(1936, 159, 71, 0.013, 1, '2024-08-10 01:10:14', '2024-08-10 01:10:14'),
(1937, 159, 75, 0.0014, 1, '2024-08-10 01:10:14', '2024-08-10 01:10:14'),
(1938, 159, 76, 0.0014, 1, '2024-08-10 01:10:14', '2024-08-10 01:10:14'),
(1939, 159, 77, 0.0014, 1, '2024-08-10 01:10:14', '2024-08-10 01:10:14'),
(1940, 159, 86, 0.0035, 1, '2024-08-10 01:10:14', '2024-08-10 01:10:14'),
(1941, 159, 100, 0.016, 1, '2024-08-10 01:10:14', '2024-08-10 01:10:14'),
(1942, 159, 149, 0.002, 1, '2024-08-10 01:10:14', '2024-08-10 01:10:14'),
(1943, 159, 158, 0.069, 1, '2024-08-10 01:10:14', '2024-08-10 01:10:14'),
(1944, 159, 159, 0.004, 1, '2024-08-10 01:10:14', '2024-08-10 01:10:14'),
(1945, 159, 174, 1, 1, '2024-08-10 01:10:14', '2024-08-10 01:10:14'),
(1946, 159, 242, 0, 1, '2024-08-10 01:10:14', '2024-08-10 01:10:14'),
(1947, 159, 243, 2, 1, '2024-08-10 01:10:14', '2024-08-10 01:10:14'),
(1948, 159, 244, 0, 1, '2024-08-10 01:10:14', '2024-08-10 01:10:14'),
(1949, 159, 245, 0, 1, '2024-08-10 01:10:14', '2024-08-10 01:10:14'),
(1950, 159, 246, 0, 1, '2024-08-10 01:10:14', '2024-08-10 01:10:14'),
(1951, 159, 175, 0.013888888888888888, 1, '2024-08-10 01:10:14', '2024-08-10 01:10:14'),
(1952, 158, 32, 0.043, 1, '2024-08-10 01:14:39', '2024-08-10 01:14:39'),
(1953, 158, 58, 0.018, 1, '2024-08-10 01:14:40', '2024-08-10 01:14:40'),
(1954, 158, 62, 0.006, 1, '2024-08-10 01:14:40', '2024-08-10 01:14:40'),
(1955, 158, 64, 0.006, 1, '2024-08-10 01:14:40', '2024-08-10 01:14:40'),
(1956, 158, 69, 1, 1, '2024-08-10 01:14:40', '2024-08-10 01:14:40'),
(1957, 158, 71, 0.013, 1, '2024-08-10 01:14:40', '2024-08-10 01:14:40'),
(1958, 158, 75, 0.0014, 1, '2024-08-10 01:14:40', '2024-08-10 01:14:40'),
(1959, 158, 76, 0.0014, 1, '2024-08-10 01:14:40', '2024-08-10 01:14:40'),
(1960, 158, 77, 0.0014, 1, '2024-08-10 01:14:40', '2024-08-10 01:14:40'),
(1961, 158, 86, 0.0035, 1, '2024-08-10 01:14:40', '2024-08-10 01:14:40'),
(1962, 158, 100, 0.018, 1, '2024-08-10 01:14:40', '2024-08-10 01:14:40');
INSERT INTO `product_materials` (`product_material_id`, `product_type_id`, `material_id`, `quantity`, `created_by`, `created_at`, `updated_at`) VALUES
(1963, 158, 149, 0.002, 1, '2024-08-10 01:14:40', '2024-08-10 01:14:40'),
(1964, 158, 158, 0.071, 1, '2024-08-10 01:14:40', '2024-08-10 01:14:40'),
(1965, 158, 159, 0.004, 1, '2024-08-10 01:14:40', '2024-08-10 01:14:40'),
(1966, 158, 174, 2, 1, '2024-08-10 01:14:40', '2024-08-10 01:14:40'),
(1967, 158, 242, 0, 1, '2024-08-10 01:14:40', '2024-08-10 01:14:40'),
(1968, 158, 243, 0, 1, '2024-08-10 01:14:40', '2024-08-10 01:14:40'),
(1969, 158, 244, 2, 1, '2024-08-10 01:14:40', '2024-08-10 01:14:40'),
(1970, 158, 245, 0, 1, '2024-08-10 01:14:40', '2024-08-10 01:14:40'),
(1971, 158, 246, 0, 1, '2024-08-10 01:14:41', '2024-08-10 01:14:41'),
(1972, 158, 175, 0.013888888888888888, 1, '2024-08-10 01:14:41', '2024-08-10 01:14:41'),
(1973, 161, 32, 0.045, 1, '2024-08-10 01:20:01', '2024-08-10 01:20:01'),
(1974, 161, 58, 0.02, 1, '2024-08-10 01:20:01', '2024-08-10 01:20:01'),
(1975, 161, 62, 0.006, 1, '2024-08-10 01:20:01', '2024-08-10 01:20:01'),
(1976, 161, 64, 0.006, 1, '2024-08-10 01:20:01', '2024-08-10 01:20:01'),
(1977, 161, 69, 1, 1, '2024-08-10 01:20:01', '2024-08-10 01:20:01'),
(1978, 161, 71, 0.013, 1, '2024-08-10 01:20:01', '2024-08-10 01:20:01'),
(1979, 161, 75, 0.0014, 1, '2024-08-10 01:20:01', '2024-08-10 01:20:01'),
(1980, 161, 76, 0.0014, 1, '2024-08-10 01:20:01', '2024-08-10 01:20:01'),
(1981, 161, 77, 0.0014, 1, '2024-08-10 01:20:01', '2024-08-10 01:20:01'),
(1982, 161, 86, 0.0035, 1, '2024-08-10 01:20:01', '2024-08-10 01:20:01'),
(1983, 161, 100, 0.02, 1, '2024-08-10 01:20:02', '2024-08-10 01:20:02'),
(1984, 161, 149, 0.002, 1, '2024-08-10 01:20:02', '2024-08-10 01:20:02'),
(1985, 161, 158, 0.073, 1, '2024-08-10 01:20:02', '2024-08-10 01:20:02'),
(1986, 161, 159, 0.004, 1, '2024-08-10 01:20:02', '2024-08-10 01:20:02'),
(1987, 161, 174, 2, 1, '2024-08-10 01:20:02', '2024-08-10 01:20:02'),
(1988, 161, 242, 0, 1, '2024-08-10 01:20:02', '2024-08-10 01:20:02'),
(1989, 161, 243, 0, 1, '2024-08-10 01:20:02', '2024-08-10 01:20:02'),
(1990, 161, 244, 0, 1, '2024-08-10 01:20:02', '2024-08-10 01:20:02'),
(1991, 161, 245, 2, 1, '2024-08-10 01:20:02', '2024-08-10 01:20:02'),
(1992, 161, 246, 0, 1, '2024-08-10 01:20:02', '2024-08-10 01:20:02'),
(1993, 161, 175, 0.013888888888888888, 1, '2024-08-10 01:20:02', '2024-08-10 01:20:02'),
(1994, 162, 32, 0.047, 1, '2024-08-10 01:24:15', '2024-08-10 01:24:15'),
(1995, 162, 58, 0.022, 1, '2024-08-10 01:24:16', '2024-08-10 01:24:16'),
(1996, 162, 62, 0.006, 1, '2024-08-10 01:24:16', '2024-08-10 01:24:16'),
(1997, 162, 64, 0.006, 1, '2024-08-10 01:24:16', '2024-08-10 01:24:16'),
(1998, 162, 69, 1, 1, '2024-08-10 01:24:16', '2024-08-10 01:24:16'),
(1999, 162, 71, 0.013, 1, '2024-08-10 01:24:16', '2024-08-10 01:24:16'),
(2000, 162, 75, 0.0014, 1, '2024-08-10 01:24:16', '2024-08-10 01:24:16'),
(2001, 162, 76, 0.0014, 1, '2024-08-10 01:24:16', '2024-08-10 01:24:16'),
(2002, 162, 77, 0.0014, 1, '2024-08-10 01:24:16', '2024-08-10 01:24:16'),
(2003, 162, 86, 0.0035, 1, '2024-08-10 01:24:16', '2024-08-10 01:24:16'),
(2004, 162, 100, 0.022, 1, '2024-08-10 01:24:16', '2024-08-10 01:24:16'),
(2005, 162, 149, 0.002, 1, '2024-08-10 01:24:16', '2024-08-10 01:24:16'),
(2006, 162, 158, 0.075, 1, '2024-08-10 01:24:16', '2024-08-10 01:24:16'),
(2007, 162, 159, 0.004, 1, '2024-08-10 01:24:16', '2024-08-10 01:24:16'),
(2008, 162, 174, 2, 1, '2024-08-10 01:24:16', '2024-08-10 01:24:16'),
(2009, 162, 242, 0, 1, '2024-08-10 01:24:16', '2024-08-10 01:24:16'),
(2010, 162, 243, 0, 1, '2024-08-10 01:24:16', '2024-08-10 01:24:16'),
(2011, 162, 244, 0, 1, '2024-08-10 01:24:16', '2024-08-10 01:24:16'),
(2012, 162, 245, 0, 1, '2024-08-10 01:24:16', '2024-08-10 01:24:16'),
(2013, 162, 246, 2, 1, '2024-08-10 01:24:16', '2024-08-10 01:24:16'),
(2014, 162, 175, 0.013888888888888888, 1, '2024-08-10 01:24:16', '2024-08-10 01:24:16'),
(2015, 165, 29, 0.023, 1, '2024-08-10 01:43:31', '2024-08-10 01:43:31'),
(2016, 165, 32, 0.043, 1, '2024-08-10 01:43:31', '2024-08-10 01:43:31'),
(2017, 165, 58, 0.012, 1, '2024-08-10 01:43:31', '2024-08-10 01:43:31'),
(2018, 165, 75, 0.0014, 1, '2024-08-10 01:43:31', '2024-08-10 01:43:31'),
(2019, 165, 76, 0.0041, 1, '2024-08-10 01:43:31', '2024-08-10 01:43:31'),
(2020, 165, 86, 0.0035, 1, '2024-08-10 01:43:31', '2024-08-10 01:43:31'),
(2021, 165, 95, 0.035, 1, '2024-08-10 01:43:31', '2024-08-10 01:43:31'),
(2022, 165, 100, 0.029, 1, '2024-08-10 01:43:31', '2024-08-10 01:43:31'),
(2023, 165, 102, 0.007, 1, '2024-08-10 01:43:31', '2024-08-10 01:43:31'),
(2024, 165, 174, 2, 1, '2024-08-10 01:43:31', '2024-08-10 01:43:31'),
(2025, 165, 247, 0.069, 1, '2024-08-10 01:43:31', '2024-08-10 01:43:31'),
(2026, 165, 248, 2, 1, '2024-08-10 01:43:31', '2024-08-10 01:43:31'),
(2027, 165, 249, 0, 1, '2024-08-10 01:43:31', '2024-08-10 01:43:31'),
(2028, 165, 250, 0, 1, '2024-08-10 01:43:31', '2024-08-10 01:43:31'),
(2029, 165, 251, 0, 1, '2024-08-10 01:43:31', '2024-08-10 01:43:31'),
(2030, 165, 252, 0, 1, '2024-08-10 01:43:32', '2024-08-10 01:43:32'),
(2032, 163, 29, 0.026, 1, '2024-08-10 01:50:06', '2024-08-10 01:56:26'),
(2033, 163, 32, 0.047, 1, '2024-08-10 01:50:06', '2024-08-10 01:56:26'),
(2034, 163, 58, 0.012, 1, '2024-08-10 01:50:06', '2024-08-10 01:56:26'),
(2035, 163, 75, 0.0014, 1, '2024-08-10 01:50:06', '2024-08-10 01:50:06'),
(2036, 163, 76, 0.0041, 1, '2024-08-10 01:50:06', '2024-08-10 01:50:06'),
(2037, 163, 86, 0.0035, 1, '2024-08-10 01:50:06', '2024-08-10 01:50:06'),
(2038, 163, 95, 0.035, 1, '2024-08-10 01:50:06', '2024-08-10 01:56:26'),
(2039, 163, 100, 0.03, 1, '2024-08-10 01:50:06', '2024-08-10 01:56:26'),
(2040, 163, 102, 0.007, 1, '2024-08-10 01:50:06', '2024-08-10 01:56:26'),
(2041, 163, 174, 2, 1, '2024-08-10 01:50:06', '2024-08-10 01:56:26'),
(2042, 163, 247, 0.072, 1, '2024-08-10 01:50:06', '2024-08-10 01:56:26'),
(2043, 163, 248, 0, 1, '2024-08-10 01:50:06', '2024-08-10 01:50:06'),
(2044, 163, 249, 0, 1, '2024-08-10 01:50:06', '2024-08-10 01:56:26'),
(2045, 163, 250, 2, 1, '2024-08-10 01:50:07', '2024-08-10 01:56:26'),
(2046, 163, 251, 0, 1, '2024-08-10 01:50:07', '2024-08-10 01:50:07'),
(2047, 163, 252, 0, 1, '2024-08-10 01:50:07', '2024-08-10 01:50:07'),
(2049, 164, 29, 0.024, 1, '2024-08-10 01:58:32', '2024-08-10 01:58:32'),
(2050, 164, 32, 0.044, 1, '2024-08-10 01:58:33', '2024-08-10 01:58:33'),
(2051, 164, 58, 0.012, 1, '2024-08-10 01:58:33', '2024-08-10 01:58:33'),
(2052, 164, 75, 0.0014, 1, '2024-08-10 01:58:33', '2024-08-10 01:58:33'),
(2053, 164, 76, 0.0041, 1, '2024-08-10 01:58:33', '2024-08-10 01:58:33'),
(2054, 164, 86, 0.0035, 1, '2024-08-10 01:58:33', '2024-08-10 01:58:33'),
(2055, 164, 95, 0.035, 1, '2024-08-10 01:58:33', '2024-08-10 01:58:33'),
(2056, 164, 100, 0.029, 1, '2024-08-10 01:58:33', '2024-08-10 01:58:33'),
(2057, 164, 102, 0.007, 1, '2024-08-10 01:58:33', '2024-08-10 01:58:33'),
(2058, 164, 174, 2, 1, '2024-08-10 01:58:33', '2024-08-10 01:58:33'),
(2059, 164, 247, 0.071, 1, '2024-08-10 01:58:33', '2024-08-10 01:58:33'),
(2060, 164, 248, 0, 1, '2024-08-10 01:58:33', '2024-08-10 01:58:33'),
(2061, 164, 249, 2, 1, '2024-08-10 01:58:33', '2024-08-10 01:58:33'),
(2062, 164, 250, 0, 1, '2024-08-10 01:58:33', '2024-08-10 01:58:33'),
(2063, 164, 251, 0, 1, '2024-08-10 01:58:33', '2024-08-10 01:58:33'),
(2064, 164, 252, 0, 1, '2024-08-10 01:58:33', '2024-08-10 01:58:33'),
(2066, 166, 29, 0.027, 1, '2024-08-10 02:01:53', '2024-08-10 02:01:53'),
(2067, 166, 32, 0.05, 1, '2024-08-10 02:01:54', '2024-08-10 02:01:54'),
(2068, 166, 58, 0.013, 1, '2024-08-10 02:01:54', '2024-08-10 02:01:54'),
(2069, 166, 75, 0.0014, 1, '2024-08-10 02:01:54', '2024-08-10 02:01:54'),
(2070, 166, 76, 0.0041, 1, '2024-08-10 02:01:54', '2024-08-10 02:01:54'),
(2071, 166, 86, 0.0035, 1, '2024-08-10 02:01:54', '2024-08-10 02:01:54'),
(2072, 166, 95, 0.035, 1, '2024-08-10 02:01:54', '2024-08-10 02:01:54'),
(2073, 166, 100, 0.031, 1, '2024-08-10 02:01:54', '2024-08-10 02:01:54'),
(2074, 166, 102, 0.007, 1, '2024-08-10 02:01:54', '2024-08-10 02:01:54'),
(2075, 166, 174, 2, 1, '2024-08-10 02:01:54', '2024-08-10 02:01:54'),
(2076, 166, 247, 0.076, 1, '2024-08-10 02:01:54', '2024-08-10 02:01:54'),
(2077, 166, 248, 0, 1, '2024-08-10 02:01:54', '2024-08-10 02:01:54'),
(2078, 166, 249, 0, 1, '2024-08-10 02:01:54', '2024-08-10 02:01:54'),
(2079, 166, 250, 0, 1, '2024-08-10 02:01:54', '2024-08-10 02:01:54'),
(2080, 166, 251, 2, 1, '2024-08-10 02:01:54', '2024-08-10 02:01:54'),
(2081, 166, 252, 0, 1, '2024-08-10 02:01:54', '2024-08-10 02:01:54'),
(2083, 167, 29, 0.027, 1, '2024-08-10 02:06:04', '2024-08-10 02:06:04'),
(2084, 167, 32, 0.05, 1, '2024-08-10 02:06:04', '2024-08-10 02:06:04'),
(2085, 167, 58, 0.013, 1, '2024-08-10 02:06:04', '2024-08-10 02:06:04'),
(2086, 167, 75, 0.0014, 1, '2024-08-10 02:06:04', '2024-08-10 02:06:04'),
(2087, 167, 76, 0.0041, 1, '2024-08-10 02:06:04', '2024-08-10 02:06:04'),
(2088, 167, 86, 0.0035, 1, '2024-08-10 02:06:04', '2024-08-10 02:06:04'),
(2089, 167, 95, 0.035, 1, '2024-08-10 02:06:04', '2024-08-10 02:06:04'),
(2090, 167, 100, 0.032, 1, '2024-08-10 02:06:04', '2024-08-10 02:06:04'),
(2091, 167, 102, 0.007, 1, '2024-08-10 02:06:04', '2024-08-10 02:06:04'),
(2092, 167, 174, 2, 1, '2024-08-10 02:06:05', '2024-08-10 02:06:05'),
(2093, 167, 247, 0.077, 1, '2024-08-10 02:06:05', '2024-08-10 02:06:05'),
(2094, 167, 248, 0, 1, '2024-08-10 02:06:05', '2024-08-10 02:06:05'),
(2095, 167, 249, 0, 1, '2024-08-10 02:06:05', '2024-08-10 02:06:05'),
(2096, 167, 250, 0, 1, '2024-08-10 02:06:05', '2024-08-10 02:06:05'),
(2097, 167, 251, 0, 1, '2024-08-10 02:06:05', '2024-08-10 02:06:05'),
(2098, 167, 252, 2, 1, '2024-08-10 02:06:05', '2024-08-10 02:06:05'),
(2100, 170, 40, 0.066, 1, '2024-08-10 02:19:59', '2024-08-10 02:19:59'),
(2101, 170, 59, 0.012, 1, '2024-08-10 02:19:59', '2024-08-10 02:19:59'),
(2102, 170, 76, 0.0014, 1, '2024-08-10 02:19:59', '2024-08-10 02:19:59'),
(2103, 170, 78, 0.0025, 1, '2024-08-10 02:19:59', '2024-08-10 02:19:59'),
(2104, 170, 94, 0.029, 1, '2024-08-10 02:19:59', '2024-08-10 02:19:59'),
(2105, 170, 174, 2, 1, '2024-08-10 02:19:59', '2024-08-10 02:19:59'),
(2106, 170, 254, 0.069, 1, '2024-08-10 02:19:59', '2024-08-10 02:19:59'),
(2107, 170, 255, 0.0025, 1, '2024-08-10 02:19:59', '2024-08-10 02:19:59'),
(2108, 170, 256, 2, 1, '2024-08-10 02:19:59', '2024-08-10 02:19:59'),
(2109, 170, 257, 0, 1, '2024-08-10 02:19:59', '2024-08-10 02:19:59'),
(2110, 170, 258, 0, 1, '2024-08-10 02:19:59', '2024-08-10 02:19:59'),
(2111, 170, 259, 0, 1, '2024-08-10 02:19:59', '2024-08-10 02:19:59'),
(2112, 170, 260, 0, 1, '2024-08-10 02:19:59', '2024-08-10 02:19:59'),
(2114, 169, 40, 0.068, 1, '2024-08-10 02:22:03', '2024-08-10 02:22:03'),
(2115, 169, 59, 0.012, 1, '2024-08-10 02:22:03', '2024-08-10 02:22:03'),
(2116, 169, 76, 0.0014, 1, '2024-08-10 02:22:03', '2024-08-10 02:22:03'),
(2117, 169, 78, 0.0541, 1, '2024-08-10 02:22:03', '2024-08-10 02:22:03'),
(2118, 169, 94, 0.03, 1, '2024-08-10 02:22:03', '2024-08-10 02:22:03'),
(2119, 169, 174, 2, 1, '2024-08-10 02:22:03', '2024-08-10 02:22:03'),
(2120, 169, 254, 0.071, 1, '2024-08-10 02:22:03', '2024-08-10 02:22:03'),
(2121, 169, 255, 0.0025, 1, '2024-08-10 02:22:04', '2024-08-10 02:22:04'),
(2122, 169, 256, 0, 1, '2024-08-10 02:22:04', '2024-08-10 02:22:04'),
(2123, 169, 257, 2, 1, '2024-08-10 02:22:04', '2024-08-10 02:22:04'),
(2124, 169, 258, 0, 1, '2024-08-10 02:22:04', '2024-08-10 02:22:04'),
(2125, 169, 259, 0, 1, '2024-08-10 02:22:04', '2024-08-10 02:22:04'),
(2126, 169, 260, 0, 1, '2024-08-10 02:22:04', '2024-08-10 02:22:04'),
(2128, 168, 40, 0.073, 1, '2024-08-10 02:24:48', '2024-08-10 02:24:48'),
(2129, 168, 59, 0.012, 1, '2024-08-10 02:24:48', '2024-08-10 02:24:48'),
(2130, 168, 76, 0.0014, 1, '2024-08-10 02:24:48', '2024-08-10 02:24:48'),
(2131, 168, 78, 0.0541, 1, '2024-08-10 02:24:48', '2024-08-10 02:24:48'),
(2132, 168, 94, 0.03, 1, '2024-08-10 02:24:48', '2024-08-10 02:24:48'),
(2133, 168, 174, 2, 1, '2024-08-10 02:24:48', '2024-08-10 02:24:48'),
(2134, 168, 254, 0.072, 1, '2024-08-10 02:24:48', '2024-08-10 02:24:48'),
(2135, 168, 255, 0.0025, 1, '2024-08-10 02:24:48', '2024-08-10 02:24:48'),
(2136, 168, 256, 0, 1, '2024-08-10 02:24:48', '2024-08-10 02:24:48'),
(2137, 168, 257, 0, 1, '2024-08-10 02:24:48', '2024-08-10 02:24:48'),
(2138, 168, 258, 2, 1, '2024-08-10 02:24:48', '2024-08-10 02:24:48'),
(2139, 168, 259, 0, 1, '2024-08-10 02:24:48', '2024-08-10 02:24:48'),
(2140, 168, 260, 0, 1, '2024-08-10 02:24:48', '2024-08-10 02:24:48'),
(2142, 171, 40, 0.077, 1, '2024-08-10 02:27:13', '2024-08-10 02:27:13'),
(2143, 171, 59, 0.013, 1, '2024-08-10 02:27:13', '2024-08-10 02:27:13'),
(2144, 171, 76, 0.0014, 1, '2024-08-10 02:27:13', '2024-08-10 02:27:13'),
(2145, 171, 78, 0.0541, 1, '2024-08-10 02:27:13', '2024-08-10 02:27:13'),
(2146, 171, 94, 0.031, 1, '2024-08-10 02:27:13', '2024-08-10 02:27:13'),
(2147, 171, 174, 2, 1, '2024-08-10 02:27:13', '2024-08-10 02:27:13'),
(2148, 171, 254, 0.076, 1, '2024-08-10 02:27:13', '2024-08-10 02:27:13'),
(2149, 171, 255, 0.0025, 1, '2024-08-10 02:27:13', '2024-08-10 02:27:13'),
(2150, 171, 256, 0, 1, '2024-08-10 02:27:13', '2024-08-10 02:27:13'),
(2151, 171, 257, 0, 1, '2024-08-10 02:27:13', '2024-08-10 02:27:13'),
(2152, 171, 258, 0, 1, '2024-08-10 02:27:13', '2024-08-10 02:27:13'),
(2153, 171, 259, 2, 1, '2024-08-10 02:27:14', '2024-08-10 02:27:14'),
(2154, 171, 260, 0, 1, '2024-08-10 02:27:14', '2024-08-10 02:27:14'),
(2156, 172, 40, 0.073, 1, '2024-08-10 02:29:31', '2024-08-10 02:29:31'),
(2157, 172, 59, 0.013, 1, '2024-08-10 02:29:32', '2024-08-10 02:29:32'),
(2158, 172, 76, 0.0014, 1, '2024-08-10 02:29:32', '2024-08-10 02:29:32'),
(2159, 172, 78, 0.0541, 1, '2024-08-10 02:29:32', '2024-08-10 02:29:32'),
(2160, 172, 94, 0.032, 1, '2024-08-10 02:29:32', '2024-08-10 02:29:32'),
(2161, 172, 174, 2, 1, '2024-08-10 02:29:32', '2024-08-10 02:29:32'),
(2162, 172, 254, 0.077, 1, '2024-08-10 02:29:32', '2024-08-10 02:29:32'),
(2163, 172, 255, 0.0025, 1, '2024-08-10 02:29:32', '2024-08-10 02:29:32'),
(2164, 172, 256, 0, 1, '2024-08-10 02:29:32', '2024-08-10 02:29:32'),
(2165, 172, 257, 0, 1, '2024-08-10 02:29:32', '2024-08-10 02:29:32'),
(2166, 172, 258, 0, 1, '2024-08-10 02:29:32', '2024-08-10 02:29:32'),
(2167, 172, 259, 0, 1, '2024-08-10 02:29:32', '2024-08-10 02:29:32'),
(2168, 172, 260, 2, 1, '2024-08-10 02:29:32', '2024-08-10 02:29:32'),
(2170, 165, 253, 0.013888888888889, 1, '2024-08-10 02:34:48', '2024-08-10 02:34:48'),
(2171, 163, 253, 0.013888888888889, 1, '2024-08-10 02:35:14', '2024-08-10 02:35:14'),
(2172, 164, 253, 0.013888888888889, 1, '2024-08-10 02:35:38', '2024-08-10 02:35:38'),
(2173, 166, 253, 0.013888888888889, 1, '2024-08-10 02:35:58', '2024-08-10 02:35:58'),
(2174, 167, 253, 0.013888888888889, 1, '2024-08-10 02:36:15', '2024-08-10 02:36:15'),
(2175, 170, 253, 0.013888888888889, 1, '2024-08-10 02:36:34', '2024-08-10 02:36:34'),
(2176, 169, 253, 0.013888888888889, 1, '2024-08-10 02:36:52', '2024-08-10 02:36:52'),
(2177, 168, 253, 0.013888888888889, 1, '2024-08-10 02:37:07', '2024-08-10 02:37:07'),
(2178, 171, 253, 0.013888888888889, 1, '2024-08-10 02:37:22', '2024-08-10 02:37:22'),
(2179, 172, 253, 0.013888888888889, 1, '2024-08-10 02:37:34', '2024-08-10 02:37:34');

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
(103, 29, 3, NULL, 1, 1, '2024-08-08 06:06:19', '2024-08-09 00:05:58'),
(104, 29, 2, NULL, 1, 1, '2024-08-08 06:06:19', '2024-08-09 00:05:58'),
(105, 29, 1, NULL, 1, 1, '2024-08-08 06:06:19', '2024-08-09 00:05:58'),
(106, 29, 4, NULL, 1, 1, '2024-08-08 06:06:19', '2024-08-09 00:05:58'),
(107, 30, 3, NULL, 1, 1, '2024-08-08 23:28:20', '2024-08-09 00:48:34'),
(108, 30, 2, NULL, 1, 1, '2024-08-08 23:28:20', '2024-08-09 00:48:34'),
(109, 30, 1, NULL, 1, 1, '2024-08-08 23:28:20', '2024-08-09 00:48:34'),
(110, 30, 4, NULL, 1, 1, '2024-08-08 23:28:20', '2024-08-09 00:48:34'),
(111, 30, 5, NULL, 1, 1, '2024-08-08 23:28:20', '2024-08-09 00:48:34'),
(112, 29, 5, NULL, 1, 1, '2024-08-09 00:05:58', '2024-08-09 00:05:58'),
(113, 31, 3, NULL, 1, 1, '2024-08-09 01:27:11', '2024-08-09 01:37:50'),
(114, 31, 2, NULL, 1, 1, '2024-08-09 01:27:11', '2024-08-09 01:37:50'),
(115, 31, 1, NULL, 1, 1, '2024-08-09 01:27:11', '2024-08-09 01:37:50'),
(116, 31, 4, NULL, 1, 1, '2024-08-09 01:27:11', '2024-08-09 01:37:50'),
(117, 31, 5, NULL, 1, 1, '2024-08-09 01:27:11', '2024-08-09 01:37:50'),
(118, 32, 3, NULL, 1, 1, '2024-08-09 02:13:38', '2024-08-09 02:17:54'),
(119, 32, 2, NULL, 1, 1, '2024-08-09 02:13:38', '2024-08-09 02:17:54'),
(120, 32, 1, NULL, 1, 1, '2024-08-09 02:13:38', '2024-08-09 02:17:54'),
(121, 32, 4, NULL, 1, 1, '2024-08-09 02:13:38', '2024-08-09 02:17:54'),
(122, 32, 5, NULL, 1, 1, '2024-08-09 02:13:38', '2024-08-09 02:17:54'),
(123, 33, 3, NULL, 1, 1, '2024-08-09 03:11:05', '2024-08-09 03:20:26'),
(124, 33, 2, NULL, 1, 1, '2024-08-09 03:11:05', '2024-08-09 03:20:26'),
(125, 33, 1, NULL, 1, 1, '2024-08-09 03:11:05', '2024-08-09 03:20:26'),
(126, 33, 4, NULL, 1, 1, '2024-08-09 03:11:05', '2024-08-09 03:20:26'),
(127, 33, 5, NULL, 1, 1, '2024-08-09 03:11:05', '2024-08-09 03:20:26'),
(128, 34, 3, NULL, 1, 1, '2024-08-09 05:22:26', '2024-08-09 05:38:03'),
(129, 34, 2, NULL, 1, 1, '2024-08-09 05:22:26', '2024-08-09 05:38:03'),
(130, 34, 1, NULL, 1, 1, '2024-08-09 05:22:26', '2024-08-09 05:38:03'),
(131, 34, 4, NULL, 1, 1, '2024-08-09 05:22:26', '2024-08-09 05:38:03'),
(132, 34, 5, NULL, 1, 1, '2024-08-09 05:22:26', '2024-08-09 05:38:03'),
(133, 35, 3, NULL, 1, 1, '2024-08-09 06:07:27', '2024-08-09 06:23:58'),
(134, 35, 2, NULL, 1, 1, '2024-08-09 06:07:27', '2024-08-09 06:23:58'),
(135, 35, 1, NULL, 1, 1, '2024-08-09 06:07:27', '2024-08-09 06:23:58'),
(136, 35, 4, NULL, 1, 1, '2024-08-09 06:07:27', '2024-08-09 06:23:58'),
(137, 35, 5, NULL, 1, 1, '2024-08-09 06:07:27', '2024-08-09 06:23:58'),
(138, 36, 3, NULL, 1, 1, '2024-08-09 06:56:55', '2024-08-09 07:01:21'),
(139, 36, 2, NULL, 1, 1, '2024-08-09 06:56:55', '2024-08-09 07:01:21'),
(140, 36, 1, NULL, 1, 1, '2024-08-09 06:56:55', '2024-08-09 07:01:21'),
(141, 36, 4, NULL, 1, 1, '2024-08-09 06:56:55', '2024-08-09 07:01:21'),
(142, 36, 5, NULL, 1, 1, '2024-08-09 06:56:55', '2024-08-09 07:01:21'),
(143, 37, 3, NULL, 1, 1, '2024-08-09 08:01:53', '2024-08-09 08:07:52'),
(144, 37, 2, NULL, 1, 1, '2024-08-09 08:01:53', '2024-08-09 08:07:52'),
(145, 37, 1, NULL, 1, 1, '2024-08-09 08:01:53', '2024-08-09 08:07:52'),
(146, 37, 4, NULL, 1, 1, '2024-08-09 08:01:53', '2024-08-09 08:07:52'),
(147, 37, 5, NULL, 1, 1, '2024-08-09 08:01:53', '2024-08-09 08:07:52'),
(148, 38, 3, NULL, 1, 1, '2024-08-09 23:38:38', '2024-08-09 23:48:44'),
(149, 38, 2, NULL, 1, 1, '2024-08-09 23:38:38', '2024-08-09 23:48:44'),
(150, 38, 1, NULL, 1, 1, '2024-08-09 23:38:38', '2024-08-09 23:48:44'),
(151, 38, 4, NULL, 1, 1, '2024-08-09 23:38:38', '2024-08-09 23:48:44'),
(152, 38, 5, NULL, 1, 1, '2024-08-09 23:38:38', '2024-08-09 23:48:44'),
(153, 39, 3, NULL, 1, 1, '2024-08-10 00:19:35', '2024-08-10 00:29:49'),
(154, 39, 2, NULL, 1, 1, '2024-08-10 00:19:35', '2024-08-10 00:29:49'),
(155, 39, 1, NULL, 1, 1, '2024-08-10 00:19:35', '2024-08-10 00:29:49'),
(156, 39, 4, NULL, 1, 1, '2024-08-10 00:19:35', '2024-08-10 00:29:49'),
(157, 39, 5, NULL, 1, 1, '2024-08-10 00:19:35', '2024-08-10 00:29:49'),
(158, 40, 3, NULL, 1, 1, '2024-08-10 00:56:34', '2024-08-10 01:00:24'),
(159, 40, 2, NULL, 1, 1, '2024-08-10 00:56:34', '2024-08-10 01:00:24'),
(160, 40, 1, NULL, 1, 1, '2024-08-10 00:56:34', '2024-08-10 01:00:24'),
(161, 40, 4, NULL, 1, 1, '2024-08-10 00:56:34', '2024-08-10 01:00:24'),
(162, 40, 5, NULL, 1, 1, '2024-08-10 00:56:34', '2024-08-10 01:00:24'),
(163, 41, 3, NULL, 1, 1, '2024-08-10 01:30:21', '2024-08-10 01:50:59'),
(164, 41, 2, NULL, 1, 1, '2024-08-10 01:30:21', '2024-08-10 01:50:59'),
(165, 41, 1, NULL, 1, 1, '2024-08-10 01:30:21', '2024-08-10 01:50:59'),
(166, 41, 4, NULL, 1, 1, '2024-08-10 01:30:21', '2024-08-10 01:50:59'),
(167, 41, 5, NULL, 1, 1, '2024-08-10 01:30:21', '2024-08-10 01:50:59'),
(168, 42, 3, NULL, 1, 1, '2024-08-10 02:11:25', '2024-08-10 02:17:01'),
(169, 42, 2, NULL, 1, 1, '2024-08-10 02:11:25', '2024-08-10 02:17:01'),
(170, 42, 1, NULL, 1, 1, '2024-08-10 02:11:25', '2024-08-10 02:17:01'),
(171, 42, 4, NULL, 1, 1, '2024-08-10 02:11:25', '2024-08-10 02:17:01'),
(172, 42, 5, NULL, 1, 1, '2024-08-10 02:11:25', '2024-08-10 02:17:01');

-- --------------------------------------------------------

--
-- Table structure for table `purchases`
--

CREATE TABLE `purchases` (
  `purchase_id` bigint(20) UNSIGNED NOT NULL,
  `purchase_no` varchar(255) NOT NULL,
  `order_id` bigint(20) UNSIGNED DEFAULT NULL,
  `vendor_id` bigint(20) UNSIGNED NOT NULL,
  `description` longtext DEFAULT NULL,
  `purchase_date` date NOT NULL,
  `require_date` date NOT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `purchase_items`
--

CREATE TABLE `purchase_items` (
  `purchase_item_id` bigint(20) UNSIGNED NOT NULL,
  `purchase_id` bigint(20) UNSIGNED NOT NULL,
  `material_id` bigint(20) UNSIGNED NOT NULL,
  `quantity` double UNSIGNED NOT NULL,
  `price` double UNSIGNED NOT NULL,
  `total` double UNSIGNED NOT NULL COMMENT 'Quantity * Price',
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(8, 13, 0, 1, 1, '2024-08-02 00:16:52', '2024-08-02 00:16:52');

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
(0, 0, 0, 'I24000000', 0, 0, 'mprocess', 0, 2, '2024-04-30', 0, NULL, 1, '2024-04-30 07:10:42', '2024-04-30 07:10:42');

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
(73, 'vendor', 'openingBalance', 0, NULL, 16, 0, NULL, 0, '2024-07-31', NULL, 1, '2024-07-31 00:48:46', '2024-07-31 00:48:46'),
(74, 'vendor', 'openingBalance', 0, NULL, 17, 0, NULL, 0, '2024-07-31', NULL, 1, '2024-07-31 00:52:13', '2024-07-31 00:52:13'),
(75, 'vendor', 'openingBalance', 0, NULL, 18, 0, NULL, 0, '2024-07-31', NULL, 1, '2024-07-31 00:54:45', '2024-07-31 00:54:45'),
(76, 'vendor', 'openingBalance', 0, NULL, 19, 0, NULL, 0, '2024-07-31', NULL, 1, '2024-07-31 00:57:05', '2024-07-31 00:57:05'),
(77, 'vendor', 'openingBalance', 0, NULL, 20, 0, NULL, 0, '2024-07-31', NULL, 1, '2024-07-31 01:00:54', '2024-07-31 01:00:54'),
(78, 'vendor', 'openingBalance', 0, NULL, 21, 0, NULL, 0, '2024-07-31', NULL, 1, '2024-07-31 01:06:30', '2024-07-31 01:06:30'),
(79, 'vendor', 'openingBalance', 0, NULL, 22, 0, NULL, 0, '2024-07-31', NULL, 1, '2024-07-31 01:08:26', '2024-07-31 01:08:26'),
(80, 'vendor', 'openingBalance', 0, NULL, 23, 0, NULL, 0, '2024-07-31', NULL, 1, '2024-07-31 01:10:14', '2024-07-31 01:10:14'),
(81, 'vendor', 'openingBalance', 0, NULL, 24, 0, NULL, 0, '2024-07-31', NULL, 1, '2024-07-31 01:11:45', '2024-07-31 01:11:45'),
(82, 'vendor', 'openingBalance', 0, NULL, 25, 0, NULL, 0, '2024-07-31', NULL, 1, '2024-07-31 01:13:15', '2024-07-31 01:13:15'),
(83, 'vendor', 'openingBalance', 0, NULL, 26, 0, NULL, 0, '2024-07-31', NULL, 1, '2024-07-31 01:15:57', '2024-07-31 01:15:57'),
(84, 'vendor', 'openingBalance', 0, NULL, 27, 0, NULL, 0, '2024-07-31', NULL, 1, '2024-07-31 01:18:44', '2024-07-31 01:18:44'),
(85, 'vendor', 'openingBalance', 0, NULL, 28, 0, NULL, 0, '2024-07-31', NULL, 1, '2024-07-31 01:22:03', '2024-07-31 01:22:03'),
(86, 'vendor', 'openingBalance', 0, NULL, 29, 0, NULL, 0, '2024-07-31', NULL, 1, '2024-07-31 01:23:59', '2024-07-31 01:23:59'),
(87, 'vendor', 'openingBalance', 0, NULL, 30, 0, NULL, 0, '2024-07-31', NULL, 1, '2024-07-31 01:26:38', '2024-07-31 01:26:38'),
(88, 'vendor', 'openingBalance', 0, NULL, 31, 0, NULL, 0, '2024-07-31', NULL, 1, '2024-07-31 01:28:48', '2024-07-31 01:28:48'),
(89, 'vendor', 'openingBalance', 0, NULL, 32, 0, NULL, 0, '2024-07-31', NULL, 1, '2024-07-31 01:32:58', '2024-07-31 01:32:58'),
(90, 'vendor', 'openingBalance', 0, NULL, 33, 0, NULL, 0, '2024-07-31', NULL, 1, '2024-07-31 01:57:19', '2024-07-31 01:57:19'),
(91, 'vendor', 'openingBalance', 0, NULL, 34, 0, NULL, 0, '2024-07-31', NULL, 1, '2024-07-31 01:59:49', '2024-07-31 01:59:49'),
(92, 'vendor', 'openingBalance', 0, NULL, 35, 0, NULL, 0, '2024-07-31', NULL, 1, '2024-07-31 02:01:46', '2024-07-31 02:01:46'),
(93, 'vendor', 'openingBalance', 0, NULL, 36, 0, NULL, 0, '2024-07-31', NULL, 1, '2024-07-31 02:03:58', '2024-07-31 02:03:58'),
(94, 'vendor', 'openingBalance', 0, NULL, 37, 0, NULL, 0, '2024-07-31', NULL, 1, '2024-07-31 02:06:16', '2024-07-31 02:06:16'),
(95, 'vendor', 'openingBalance', 0, NULL, 38, 0, NULL, 0, '2024-07-31', NULL, 1, '2024-07-31 02:08:24', '2024-07-31 02:08:24'),
(96, 'vendor', 'openingBalance', 0, NULL, 39, 0, NULL, 0, '2024-07-31', NULL, 1, '2024-07-31 02:10:13', '2024-07-31 02:10:13'),
(97, 'vendor', 'openingBalance', 0, NULL, 40, 0, NULL, 0, '2024-07-31', NULL, 1, '2024-07-31 02:14:08', '2024-07-31 02:14:08'),
(98, 'vendor', 'openingBalance', 0, NULL, 41, 0, NULL, 0, '2024-07-31', NULL, 1, '2024-07-31 02:23:13', '2024-07-31 02:23:13'),
(99, 'vendor', 'openingBalance', 0, NULL, 42, 0, NULL, 0, '2024-07-31', NULL, 1, '2024-07-31 02:29:20', '2024-07-31 02:29:20'),
(100, 'vendor', 'openingBalance', 0, NULL, 43, 0, NULL, 0, '2024-07-31', NULL, 1, '2024-07-31 02:31:27', '2024-07-31 02:31:27'),
(101, 'vendor', 'openingBalance', 0, NULL, 44, 0, NULL, 0, '2024-07-31', NULL, 1, '2024-07-31 02:39:51', '2024-07-31 02:39:51'),
(102, 'vendor', 'openingBalance', 0, NULL, 45, 0, NULL, 0, '2024-08-02', NULL, 1, '2024-07-31 02:41:14', '2024-08-02 00:08:25'),
(103, 'employee', 'openingBalance', 0, NULL, 13, 0, 0, NULL, '2024-08-02', NULL, 1, '2024-08-02 00:16:53', '2024-08-02 00:16:53'),
(104, 'vendor', 'openingBalance', 0, NULL, 46, 0, NULL, 0, '2024-08-08', NULL, 1, '2024-08-08 07:45:54', '2024-08-08 07:45:54'),
(105, 'customer', 'openingBalance', 0, NULL, 7, 0, 0, NULL, '2024-08-10', NULL, 1, '2024-08-10 02:48:31', '2024-08-10 02:48:31');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `role` varchar(255) NOT NULL,
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

INSERT INTO `users` (`id`, `name`, `role`, `email`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'Admin', 'admin', 'admin@gmail.com', NULL, '$2y$12$IsLNGwhB1PNBy6Lkxrtkx.ka9hfw2hEf/.qGrjPlN8EFnxkGIrcgW', 'fEoALirAtBlNvxMU916Uv3aqedz2LbSbOd5aHkZOc1d1qhPDIfgJ2M99f3cU', NULL, NULL);

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
(16, 58, 'V0001', 0, '0', 'Afzaal Ahmad', 'Rehmania Traders', '+92 308 4054356', '+92 300 8158864', NULL, 43, 'Mianapura Sialkot', NULL, 1, '2024-07-31 00:48:46', '2024-07-31 01:04:18'),
(17, 58, 'V0002', 0, '0', 'Shani', 'Zeeshan Traders', NULL, '+92 321 6142263', NULL, 43, 'Ramtalai, Railway Road', NULL, 1, '2024-07-31 00:52:13', '2024-07-31 01:03:23'),
(18, 58, 'V0003', 0, '0', 'Usman', 'Usman Traders', NULL, '+92 333 0000000', NULL, 43, 'Nasir road', NULL, 1, '2024-07-31 00:54:45', '2024-07-31 01:03:00'),
(19, 112, 'V0004', 0, '0', 'Ashraf Sulehria', 'Sulehria Thread Works', NULL, '+92 300 6103571', NULL, 43, 'Classico Chowk', NULL, 1, '2024-07-31 00:57:04', '2024-07-31 01:02:39'),
(20, 113, 'V0005', 0, '0', 'Abdul Rehman', 'Madina Labels', NULL, '+92 326 8893926', NULL, 43, 'China Chowk , pasroor Road', NULL, 1, '2024-07-31 01:00:54', '2024-07-31 01:02:02'),
(21, 57, 'V0006', 0, '0', 'Haji Razzak', 'Razzak Tannery', '+92 304 6232628', '+92 300 6127644', NULL, 43, 'Sialkot Tannery Zone', NULL, 1, '2024-07-31 01:06:30', '2024-07-31 01:06:30'),
(22, 57, 'V0007', 0, '0', 'Munir', 'Munir Leather', NULL, '+92 300 0000000', NULL, 43, 'China chowk Muradpur Road', NULL, 1, '2024-07-31 01:08:25', '2024-07-31 01:08:25'),
(23, 57, 'V0008', 0, '0', 'Safder Hussain', 'Safder Leather Works', NULL, '+92 300 9836951', NULL, 43, 'China Chowk, Muradpur Road', NULL, 1, '2024-07-31 01:10:13', '2024-07-31 01:10:13'),
(24, 57, 'V0009', 0, '0', 'Shameer', 'Nazir Leather', NULL, '+92 333 0435042', NULL, 43, 'Mianapura', NULL, 1, '2024-07-31 01:11:45', '2024-07-31 01:11:45'),
(25, 57, 'V0010', 0, '0', 'Tipu Sultan', 'Sultan Leather', NULL, '+92 300 0000000', NULL, 43, 'Kashmir Road', NULL, 1, '2024-07-31 01:13:15', '2024-07-31 01:13:15'),
(26, 114, 'V0011', 0, '0', 'Arshad', 'Lahore Fabrics', NULL, '+92 321 4473410', NULL, 43, 'Shahabpura Road', NULL, 1, '2024-07-31 01:15:56', '2024-07-31 01:16:04'),
(27, 114, 'V0012', 0, '0', 'Midas', 'Shehbaz Garments', NULL, '+92 300 00000', NULL, 115, 'DHA Secter A Phase 1', NULL, 1, '2024-07-31 01:18:43', '2024-07-31 01:19:11'),
(28, 58, 'V0013', 0, '0', 'Mehar Toqeer', 'Mehar Rubber Sheet', NULL, '+92 300 6161623', NULL, 43, 'Sadra badra ,Daska Road', NULL, 1, '2024-07-31 01:22:02', '2024-07-31 01:22:02'),
(29, 58, 'V0014', 0, '0', 'Sohail', 'Geo Lamination', '+92 300 6145601', '+92 306 8798692', '+92 300 8545601', 43, 'Aimnabad Road', NULL, 1, '2024-07-31 01:23:59', '2024-07-31 01:23:59'),
(30, 116, 'V0015', 0, '0', 'Atif Shah', 'AR Printers', NULL, '+92 310 7252641', NULL, 43, 'Roras Road, Adalat Garrah', NULL, 1, '2024-07-31 01:26:37', '2024-07-31 01:26:48'),
(31, 58, 'V0016', 0, '0', 'Abdul Sattar', 'Ayyan Traders', '+92 331 7076206', '+92 305 7076206', NULL, 118, 'Ghulam Muhammadabad', NULL, 1, '2024-07-31 01:28:47', '2024-07-31 01:29:06'),
(32, 119, 'V0017', 0, '0', 'Shoakat Chohan', 'Chohan Dyeing & Color Matching', NULL, '+92 300 6101862', NULL, 43, 'Baba Beri Chowk, Pul Aik', NULL, 1, '2024-07-31 01:32:57', '2024-07-31 01:36:17'),
(33, 120, 'V0018', 0, '0', 'Zaheer Butt', 'Protection TPR Supplys', '+92 320 9615161', '+92 333 6888984', NULL, 43, 'Fatehgarh Agency Road Near Cococolla Factory', NULL, 1, '2024-07-31 01:57:19', '2024-07-31 01:59:03'),
(34, 120, 'V0019', 0, '0', 'Abdul Irfan', 'Irfan Rubber Logo', NULL, '+92 306 6281347', NULL, 43, 'Fatehgarh Agency Chowk', NULL, 1, '2024-07-31 01:59:48', '2024-07-31 01:59:48'),
(35, 58, 'V0020', 0, '0', 'Hamid Bajwa', 'M. Maqsood Corporation', NULL, '+92 345 6830490', '052 3258143', 43, 'Haji Pura Near Mughal e Azam Marrige Hall', NULL, 1, '2024-07-31 02:01:45', '2024-07-31 02:01:45'),
(36, 121, 'V0021', 0, '0', 'Hassan', 'Sawan International', NULL, '+92 3000 000000', NULL, 43, 'Small Industry Factory Area', NULL, 1, '2024-07-31 02:03:58', '2024-07-31 02:04:05'),
(37, 122, 'V0022', 0, '0', 'Shehzad Ali Khokhar', 'Munawar CNC', '+92 334 8079479', '+92 332 8444474', NULL, 43, 'Charch Road Haji Pura', NULL, 1, '2024-07-31 02:06:16', '2024-07-31 02:06:32'),
(38, 123, 'V0023', 0, '0', 'Haji Zubair', 'Sence Art', '+92 321 8868761', '+92 300 8868761', NULL, 44, 'Royal Park', NULL, 1, '2024-07-31 02:08:24', '2024-07-31 02:08:31'),
(39, 124, 'V0024', 0, '0', 'Rehaman', 'Rehman Plastic & Packing Material', '+92 331 3945900', '+92 336 7885058', NULL, 43, 'Greenwood Street', NULL, 1, '2024-07-31 02:10:12', '2024-07-31 02:10:25'),
(40, 125, 'V0025', 0, '0', 'M Umer', 'Barcode Stickers', NULL, '+92 321 6164826', NULL, 43, 'Classico Chowk', NULL, 1, '2024-07-31 02:14:08', '2024-07-31 02:14:08'),
(41, 0, 'V0026', 1, '0', 'Shamas', 'Mehar Shamas Din', NULL, '+92 318 0760225', '+92 316 1782986', 43, 'Gohadpur Road', NULL, 1, '2024-07-31 02:23:12', '2024-07-31 02:23:12'),
(42, 0, 'V0027', 1, '0', 'Mohsin', 'Mohsin Raza', NULL, '+92 315 0277660', NULL, 43, 'Nasir Road', NULL, 1, '2024-07-31 02:29:20', '2024-07-31 02:29:20'),
(43, 117, 'V0028', 0, '0', 'Asif', '2B Printers', NULL, '052 4581200', NULL, 43, 'Beri Plaza Murray College Road', NULL, 1, '2024-07-31 02:31:26', '2024-07-31 02:31:49'),
(44, 58, 'V0029', 0, '0', 'Malik Qaiser', 'Malik & Malik Traders', '+92 300 6202792', '+92 300 6184884', NULL, 43, 'Capital Road Near TCS Office', NULL, 1, '2024-07-31 02:39:51', '2024-07-31 02:39:51'),
(45, 58, 'V0030', 0, '0', 'Ali Malik', 'Winner Tex', '+92 301 4404360', '+92 333 8702878', NULL, 43, 'Karim Pura Road', NULL, 1, '2024-07-31 02:41:13', '2024-08-02 00:08:25'),
(46, 67, 'V0031', 0, '0', 'ZAIGHAM PACKGES', 'SHIEKH ABDULLAH', 'SHEIKH ABDULLAH', '03006109499', NULL, 43, 'CLASSICAL CHOWK SIALKOT', NULL, 1, '2024-08-08 07:45:54', '2024-08-08 07:45:54');

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
-- AUTO_INCREMENT for table `banks`
--
ALTER TABLE `banks`
  MODIFY `bank_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `company`
--
ALTER TABLE `company`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `customer_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `deliveries`
--
ALTER TABLE `deliveries`
  MODIFY `delivery_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `delivery_boxes`
--
ALTER TABLE `delivery_boxes`
  MODIFY `dbox_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

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
  MODIFY `head_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=141;

--
-- AUTO_INCREMENT for table `head_types`
--
ALTER TABLE `head_types`
  MODIFY `head_type_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `igroups`
--
ALTER TABLE `igroups`
  MODIFY `igroup_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `igroup_items`
--
ALTER TABLE `igroup_items`
  MODIFY `igroup_item_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `images`
--
ALTER TABLE `images`
  MODIFY `image_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=143;

--
-- AUTO_INCREMENT for table `machines`
--
ALTER TABLE `machines`
  MODIFY `machine_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `materials`
--
ALTER TABLE `materials`
  MODIFY `material_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=261;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `mprocess`
--
ALTER TABLE `mprocess`
  MODIFY `mprocess_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `order_item_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` bigint(255) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT for table `product_costs`
--
ALTER TABLE `product_costs`
  MODIFY `product_cost_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=108;

--
-- AUTO_INCREMENT for table `product_materials`
--
ALTER TABLE `product_materials`
  MODIFY `product_material_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2180;

--
-- AUTO_INCREMENT for table `product_types`
--
ALTER TABLE `product_types`
  MODIFY `product_type_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=173;

--
-- AUTO_INCREMENT for table `purchases`
--
ALTER TABLE `purchases`
  MODIFY `purchase_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT for table `purchase_items`
--
ALTER TABLE `purchase_items`
  MODIFY `purchase_item_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=443;

--
-- AUTO_INCREMENT for table `receives`
--
ALTER TABLE `receives`
  MODIFY `receive_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=72;

--
-- AUTO_INCREMENT for table `receive_materials`
--
ALTER TABLE `receive_materials`
  MODIFY `receive_material_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=277;

--
-- AUTO_INCREMENT for table `returns`
--
ALTER TABLE `returns`
  MODIFY `return_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `return_materials`
--
ALTER TABLE `return_materials`
  MODIFY `return_material_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=140;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `salaries`
--
ALTER TABLE `salaries`
  MODIFY `salary_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `stocks`
--
ALTER TABLE `stocks`
  MODIFY `stock_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=152;

--
-- AUTO_INCREMENT for table `stock_items`
--
ALTER TABLE `stock_items`
  MODIFY `stock_item_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=312;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `transaction_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=106;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `vendors`
--
ALTER TABLE `vendors`
  MODIFY `vendor_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

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
