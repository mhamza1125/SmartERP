-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 01, 2026 at 06:07 PM
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
  `iban` varchar(255) DEFAULT NULL COMMENT 'International Bank Account Number',
  `address` text DEFAULT NULL COMMENT 'Bank address',
  `branch_code` varchar(255) DEFAULT NULL COMMENT 'Bank branch code',
  `swift_code` varchar(255) DEFAULT NULL COMMENT 'SWIFT/BIC code',
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `banks`
--

INSERT INTO `banks` (`bank_id`, `bank_holder`, `banker_id`, `head_id`, `account_title`, `account`, `iban`, `address`, `branch_code`, `swift_code`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'admin', 0, 32, 'Admin', '06710109764865', 'Iban no', 'check213 check', 'Branch code', 'swift cod', 1, '2024-04-02 14:39:37', '2025-09-20 03:06:29'),
(2, 'vendor', 6, 35, 'Shehzad Ahmed', '05710109764865', NULL, NULL, NULL, NULL, 1, '2024-04-02 14:40:44', '2024-04-02 14:40:44'),
(3, 'employee', 5, 35, 'Bashir', '02710109764865', NULL, NULL, NULL, NULL, 1, '2024-04-02 14:42:06', '2024-04-02 14:42:06'),
(4, 'employee', 5, 36, 'Bashir', '06710109744865', NULL, NULL, NULL, NULL, 1, '2024-04-02 14:49:04', '2024-04-02 14:49:04'),
(6, 'vendor', 6, 36, 'Shehzad Ahmed', '02714109764865', NULL, NULL, NULL, NULL, 1, '2024-04-04 03:25:37', '2024-04-04 03:25:37'),
(7, 'customer', 6, 36, 'Muhammad Hamza', '06711209764865', NULL, NULL, NULL, NULL, 1, '2024-04-19 08:16:35', '2024-04-19 08:16:35'),
(8, 'customer', 5, 37, 'Samad Ali', '06710109744123', NULL, NULL, NULL, NULL, 1, '2024-04-19 08:32:47', '2024-04-19 08:32:47'),
(9, 'admin', 0, 35, 'Admin', '0459334895765', NULL, NULL, NULL, NULL, 1, '2024-05-23 13:17:53', '2024-05-23 13:19:47'),
(10, 'admin', 0, 37, 'Admin', '0682139347634', NULL, NULL, NULL, NULL, 1, '2024-05-23 13:26:53', '2024-05-23 13:28:52'),
(18, 'admin', 0, 36, 'Dummy Check', '0271410976476', NULL, NULL, NULL, NULL, 1, '2025-02-16 04:45:10', '2025-02-16 04:45:10'),
(19, 'admin', 0, 36, 'Dummy Check 2', '05710109764787', NULL, NULL, NULL, NULL, 1, '2025-02-16 04:47:07', '2025-02-16 04:47:07'),
(20, 'vendor', 12, 32, 'Test User', '0321512154484', '252525', '123223', '1313', '25487', 1, '2025-09-19 11:08:59', '2025-09-19 11:13:32');

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
(10, 'First Category', 1, '2025-12-13 03:21:30', '2025-12-13 03:21:30');

-- --------------------------------------------------------

--
-- Table structure for table `company`
--

CREATE TABLE `company` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `ceo` varchar(255) NOT NULL,
  `ntn` varchar(255) DEFAULT NULL COMMENT 'National Tax Number',
  `rex_no` varchar(255) DEFAULT NULL COMMENT 'REX Number for commercial invoices',
  `address` text DEFAULT NULL COMMENT 'Company address',
  `city` varchar(255) DEFAULT NULL COMMENT 'City',
  `country` varchar(255) DEFAULT NULL COMMENT 'Country',
  `zip` varchar(255) DEFAULT NULL,
  `phone` varchar(255) NOT NULL,
  `fax` varchar(255) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `website` varchar(255) DEFAULT NULL,
  `logo` varchar(255) NOT NULL,
  `logo_path` varchar(255) DEFAULT NULL COMMENT 'Path to company logo file',
  `footer_text` text DEFAULT NULL COMMENT 'Footer text for documents',
  `statement_of_origin` text DEFAULT NULL COMMENT 'Statement of Origin for commercial invoices',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `company`
--

INSERT INTO `company` (`id`, `name`, `ceo`, `ntn`, `rex_no`, `address`, `city`, `country`, `zip`, `phone`, `fax`, `email`, `website`, `logo`, `logo_path`, `footer_text`, `statement_of_origin`, `created_at`, `updated_at`) VALUES
(1, 'SAJJADSON LAB EQUIPMENT', 'CEO', '031525215548', 'REX No', 'Near Sachi Sarkar Darbar, Opposite Qayyum Elahi Surgical, Harrar Sialkot, Pakistan\r\nPhone: +92 52 357 3727 || Email: info@sajjadsonlab.com || Web: sajjadsonlab.com. Footer text', 'Sialkot', 'Pakistan', '51310', '03314657496', '558879', 'admin@example.com', 'admin@example.com', 'logo.png', 'storage/logos/1760093776_NewLogo.png', 'Near Sachi Sarkar Darbar, Opposite Qayyum Elahi Surgical, Harrar Sialkot, Pakistan <br>\r\nPhone: +92 52 357 3727 || Email: info@sajjadsonlab.com || Web: sajjadsonlab.com', 'The exporter \"SAJJADSON LAB EQUIPMENT\" Rex no. PKREXPK8388402 of the products covered by this document delares that, except where otherwise clearly indicated, these products are of pakistan preferential origin according to rules of origin of the generalized system of preferences of the european union and that the origin criterion criterion met is 7326, 8211, 8205, 8213, 8215 and 8203 WE ALSO DECLAR THAT THE INPUTS USED FOR THE PRODUCTS IN THIS INVOICE ARE NOT OF RUSSIAN ORIGIN, ANNEX XVII OF REGULATION 833/2014”', '2025-09-27 01:44:54', '2025-12-30 03:43:00');

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
  `port_no` varchar(255) DEFAULT NULL COMMENT 'Port number for shipping',
  `description` longtext DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`customer_id`, `customer_no`, `country_id`, `currency_id`, `fname`, `lname`, `email`, `phone`, `fax`, `address`, `port_no`, `description`, `created_by`, `created_at`, `updated_at`) VALUES
(9, 'CU0001', 87, 93, 'Customer #1', '.', 'customer1@example.com', '.', '.', 'Address', NULL, NULL, 1, '2025-12-13 03:29:48', '2025-12-13 03:29:48'),
(10, 'CU0002', 90, 106, 'Customer #2', '.', '.', '.', '.', 'Customer address', 'Test Port name', NULL, 1, '2025-12-25 14:22:37', '2025-12-30 13:34:57'),
(11, 'CU0003', 87, 93, 'Customer #3', '.', '.', '.', '.', 'address', '.', NULL, 1, '2026-01-15 03:13:45', '2026-01-15 03:13:45'),
(12, 'CU0004', 87, 93, 'Customer #4', '.', '.', '.', '.', 'Address', '.', NULL, 1, '2026-02-26 05:07:48', '2026-02-26 05:07:48'),
(13, 'CU0005', 86, 93, 'Agile Customer', '.', '.', '.', '.', '.', '.', NULL, 1, '2026-02-28 05:51:07', '2026-02-28 05:51:07');

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
  `delivery_no` varchar(255) DEFAULT NULL COMMENT 'Auto-generated delivery number (SLE-001-26 format)',
  `delivery_date` date DEFAULT NULL COMMENT 'Actual delivery date',
  `fi_no` varchar(255) DEFAULT NULL COMMENT 'FI Number for commercial invoice',
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `deliveries`
--

INSERT INTO `deliveries` (`delivery_id`, `stock_id`, `fshipping`, `tshipping`, `fport_no`, `tport_no`, `delivery_method`, `delivery_status`, `delivery_no`, `delivery_date`, `fi_no`, `created_by`, `created_at`, `updated_at`) VALUES
(31, 10147, '1212', 'Neka Pura Nai Abadi Shuja Abad Sialkot 51310 Pakistan', '1212', 'Bin Qasim', 1, 3, NULL, NULL, 'ABL-12122025-22, ABL-12122025-22, ABL-12122025-22ABL-12122025-22, ABL-12122025-22, ABL-12122025-22', 1, '2025-12-18 04:34:39', '2025-12-30 03:29:58'),
(32, 10148, '1212', 'Address of customer ', 'Our port 123', 'Customer port 456', 1, 1, NULL, NULL, NULL, 1, '2025-12-21 14:01:40', '2025-12-21 14:01:40'),
(33, 10150, 'Sialkot', 'usa', '2154', '565', 1, 1, 'SLE-003-25', '2025-12-26', NULL, 1, '2025-12-25 14:28:48', '2025-12-25 14:28:48'),
(34, 10153, 'Harar siakot', 'Canada etc', '5858', '9898', 2, 2, 'Test#123', '2025-12-27', 'This is FI no', 1, '2025-12-27 03:05:18', '2025-12-27 03:05:18'),
(35, 10154, 'From', 'Address', 'Name', 'Name', 1, 1, 'SLE-D005-25', '2025-12-31', 'Fi No', 1, '2025-12-31 07:28:54', '2025-12-31 07:28:54'),
(36, 10155, '12', 'Customer address', '21', 'Test Port name', 1, 1, 'SLE-D001-26', '2026-01-01', '23', 1, '2026-01-01 12:20:33', '2026-01-01 12:20:33'),
(37, 10156, 'Pakistan sialkot', 'Address of customer', '2525', '.', 1, 1, 'SLE-D002-26', '2026-01-15', '.', 1, '2026-01-15 03:21:23', '2026-01-15 03:21:23'),
(38, 10159, '3', 'address', '3', '.', 1, 1, 'SLE-D003-26', '2026-01-15', '3', 1, '2026-01-15 04:05:46', '2026-01-15 04:05:46'),
(39, 10160, '.', 'address', '.', '.', 1, 1, 'SLE-D004-26', '2026-01-15', NULL, 1, '2026-01-15 04:13:58', '2026-01-15 04:13:58');

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
-- Table structure for table `delivery_returns`
--

CREATE TABLE `delivery_returns` (
  `delivery_return_id` bigint(20) UNSIGNED NOT NULL,
  `return_no` varchar(255) NOT NULL,
  `delivery_id` bigint(20) UNSIGNED NOT NULL,
  `return_date` date NOT NULL,
  `return_reason` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `delivery_return_items`
--

CREATE TABLE `delivery_return_items` (
  `delivery_return_item_id` bigint(20) UNSIGNED NOT NULL,
  `delivery_return_id` bigint(20) UNSIGNED NOT NULL,
  `stock_item_id` bigint(20) UNSIGNED NOT NULL,
  `quantity` decimal(10,2) NOT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
  `marital_status` enum('married','single','divorced','widower') DEFAULT NULL,
  `siblings_count` int(11) DEFAULT NULL,
  `children_details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`children_details`)),
  `education` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`education`)),
  `employment_history` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`employment_history`)),
  `additional_skills` text DEFAULT NULL,
  `joining_date` date NOT NULL,
  `employee_status` bigint(20) UNSIGNED NOT NULL DEFAULT 1 COMMENT 'Active / Inactive',
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employees`
--

INSERT INTO `employees` (`employee_id`, `employee_no`, `department_id`, `employee_type_id`, `attendance_id`, `name`, `fname`, `sname`, `cnic`, `phone1`, `phone2`, `city_id`, `address`, `designation`, `salary`, `description`, `marital_status`, `siblings_count`, `children_details`, `education`, `employment_history`, `additional_skills`, `joining_date`, `employee_status`, `created_by`, `created_at`, `updated_at`) VALUES
(15, 'E0001', 6, 39, 0, 'Salary Employee', '.', '.', '.', '.', '.', 70, 'Address', 'Admin', 25000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-13', 1, 1, '2025-12-13 03:32:11', '2025-12-15 14:56:36'),
(16, 'E0002', 8, 40, 0, 'Wage Employee', '.', '.', '.', '.', '.', 70, 'Address', 'Stitcher', 75000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-13', 0, 1, '2025-12-13 03:35:16', '2025-12-19 13:42:59'),
(17, 'E0003', 6, 39, 0, 'Employee #5', '.', '.', '.', '.', '.', 70, 'This is address', 'Packing', 0, 'Any extra details', 'married', 2, '[{\"name\":\"First\",\"gender\":\"male\",\"age\":\"10\"},{\"name\":\"Second\",\"gender\":\"female\",\"age\":null}]', '[{\"institution_name\":\"First\",\"degree\":\"Degree #1\",\"year_of_passing\":\"2017\",\"percentage\":\"80\"},{\"institution_name\":\"Second\",\"degree\":\"Degree #2\",\"year_of_passing\":\"2019\",\"percentage\":\"85\"}]', '[{\"company_name\":\"First Company\",\"designation\":\"Test\",\"from_date\":\"2025-12-01\",\"to_date\":\"2026-01-31\",\"salary\":null,\"reason_for_leaving\":null}]', 'Any Skills', '2026-01-01', 1, 1, '2026-01-01 13:57:26', '2026-01-01 13:57:26');

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
(92, 16, 'PKR', 1, 0, 1, '2024-03-30 12:51:51', '2024-03-30 12:51:51'),
(93, 16, 'Dollar', 1, 0, 1, '2024-03-30 12:52:11', '2024-03-30 12:52:11'),
(96, 10, 'Shipping / Delivery Vehicles', 1, 1, 1, '2024-04-30 08:43:20', '2024-04-30 08:43:49'),
(97, 4, 'Units', 1, 0, 1, '2024-04-30 08:48:40', '2024-04-30 08:48:40'),
(98, 7, 'Tax Charges', 1, 0, 1, '2024-05-10 08:16:44', '2024-05-10 08:16:44'),
(101, 10, 'Machine Material', 1, 1, 1, '2024-05-24 08:51:09', '2024-05-24 08:51:09'),
(102, 19, 'Joki Stitching', 1, 0, 1, '2024-05-24 11:19:21', '2024-05-24 11:19:21'),
(103, 19, 'Maghzi', 1, 0, 1, '2024-05-24 11:19:42', '2024-05-24 11:19:42'),
(104, 19, 'Embroidery', 1, 0, 1, '2024-05-24 11:20:01', '2024-05-24 11:20:01'),
(105, 12, 'Rejection', 1, 1, 1, '2024-05-28 05:52:35', '2024-05-28 05:52:35'),
(106, 16, 'Euro', 1, 0, 1, '2024-08-10 05:54:11', '2024-08-10 05:54:11'),
(114, 12, 'Stage 1', 1, 0, 1, '2025-12-06 10:24:43', '2025-12-06 10:24:43'),
(115, 12, 'Stage 2', 1, 0, 1, '2025-12-06 10:24:49', '2025-12-06 10:24:49'),
(116, 12, 'Stage 3', 1, 0, 1, '2025-12-06 10:24:59', '2025-12-06 10:24:59'),
(117, 12, 'Stage 4', 1, 0, 1, '2025-12-06 10:25:06', '2025-12-06 10:25:06'),
(118, 12, 'Stage 5', 1, 0, 1, '2025-12-06 10:25:14', '2025-12-06 10:25:14'),
(119, 8, 'Ugoki', 1, 0, 1, '2025-12-19 12:17:15', '2025-12-19 12:17:15'),
(120, 8, 'Qasur', 1, 0, 1, '2025-12-19 12:18:00', '2025-12-19 12:18:00'),
(121, 7, 'Bank Charges', 1, 1, 1, '2025-12-30 14:40:24', '2025-12-30 14:40:24'),
(122, 12, 'Cutting', 1, 0, 1, '2026-02-28 05:30:29', '2026-02-28 05:30:29'),
(123, 12, 'Stitching', 1, 0, 1, '2026-02-28 05:30:39', '2026-02-28 05:30:39'),
(124, 12, 'Checking', 1, 0, 1, '2026-02-28 05:30:45', '2026-02-28 05:30:45'),
(125, 12, 'Packing', 1, 0, 1, '2026-02-28 05:30:50', '2026-02-28 05:30:50');

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
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
(32, 'M0001', 65, 19, 'Material #1', 48, 500, 'Rack #1', NULL, 1, '2025-12-13 03:17:10', '2025-12-13 03:17:32'),
(33, 'M0002', 65, 19, 'Material #2', 51, 55, 'Rack #2', NULL, 1, '2025-12-13 03:18:04', '2025-12-17 11:47:16'),
(34, 'M0003', 65, 19, 'Material #3', 51, 0, NULL, NULL, 1, '2025-12-13 03:18:22', '2025-12-13 03:18:22'),
(35, 'M0004', 63, 19, 'Material #4', 47, 250, 'Rack #4', NULL, 1, '2025-12-13 03:18:45', '2025-12-13 03:18:45'),
(36, 'M0005', 61, 19, 'Packing Box', 52, 150, 'Rack #5', NULL, 1, '2025-12-15 10:18:46', '2025-12-30 14:08:01'),
(37, 'M0006', 101, 20, 'Machine Material', 48, 150, 'Rack #9', NULL, 1, '2025-12-16 09:55:23', '2025-12-31 07:08:53'),
(38, 'M0007', 54, 19, 'Leather #1', 49, 300, 'xyz', NULL, 1, '2026-02-28 05:16:12', '2026-02-28 05:20:44'),
(39, 'M0008', 65, 19, 'Velcro', 50, 100, 'xyz', NULL, 1, '2026-02-28 05:16:44', '2026-02-28 05:21:42');

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
(9, '2025_01_12_063410_add_batch_uuid_column_to_activity_log_table', 4),
(10, '2025_04_07_162120_create_roles_table', 5),
(11, '2025_04_07_162140_create_permissions_table', 6),
(12, '2025_04_07_162213_create_permission_role_table', 7),
(13, '2025_09_13_053806_create_delivery_returns_table', 7),
(14, '2025_09_13_053846_create_delivery_return_items_table', 7),
(15, '2025_09_13_054353_add_payment_fields_to_transactions_table', 7),
(16, '2025_01_19_000001_add_order_fields_to_orders_table', 8),
(17, '2025_01_19_000002_add_bank_fields_to_banks_table', 8),
(18, '2025_01_19_000003_add_product_id_to_vendors_table', 9),
(20, '2025_09_26_000002_remove_fi_rex_from_orders_add_to_customers', 10),
(21, '2025_09_26_000003_enhance_company_table', 11),
(22, '2025_10_05_000001_add_commercial_invoice_fields_to_deliveries_table', 12),
(24, '2025_09_26_000001_add_hs_code_to_products_table', 13),
(25, '2025_11_16_000001_refactor_transactions_table_schema', 14),
(26, '2025_11_25_000001_create_assets_table', 15),
(27, '2025_11_28_161542_add_ledger_flag_to_transactions_table', 16),
(28, '2025_11_30_000001_add_ptc_columns_to_stocks_table', 17),
(29, '2025_12_09_092651_add_product_component_to_product_materials_table', 18),
(31, '2025_12_09_154739_add_component_product_type_id_to_stock_items_table', 19),
(32, '2025_12_11_000001_add_transaction_fields_to_assets_table', 19),
(33, '2025_12_12_000001_create_packing_lists_table', 20),
(34, '2025_12_12_000002_create_packing_cartons_table', 20),
(35, '2025_12_12_000003_create_packing_carton_items_table', 20),
(36, '2025_12_18_000004_add_rex_and_statement_of_origin_to_company_table', 21),
(37, '2025_12_18_000005_move_tax_fields_from_deliveries_to_company_table', 21),
(38, '2025_12_19_184711_update_material_and_product_id_columns_in_vendors_table', 22),
(39, '2025_12_23_000001_simplify_currency_handling', 23),
(40, '2025_12_24_073750_add_delivery_no_and_delivery_date_to_deliveries_table', 24),
(41, '2025_12_29_000001_add_box_fields_to_packing_cartons_table', 25),
(42, '2025_12_29_000002_add_pallet_fields_to_packing_lists_table', 25),
(43, '2025_12_29_000001_rename_fees_expenses_and_add_db_charges', 26),
(44, '2025_12_30_000001_add_port_no_to_customers_table', 27),
(45, '2026_01_01_000001_add_personal_professional_info_to_employees_table', 28),
(46, '2026_02_17_160511_create_sessions_table', 29),
(47, '2026_02_17_160520_create_cache_table', 29),
(48, '2026_02_17_160528_create_jobs_table', 29);

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
(20, 87, 11, 551, 32, 20, 1, '2025-12-16 04:17:15', '2025-12-16 04:17:15');

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
  `due_date` date DEFAULT NULL COMMENT 'Due date for order completion',
  `payment_terms` text DEFAULT NULL COMMENT 'Payment terms and conditions',
  `so_origin` text DEFAULT NULL COMMENT 'Statement of origin',
  `description` longtext DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_id`, `order_no`, `job_no`, `customer_id`, `order_status`, `order_date`, `due_date`, `payment_terms`, `so_origin`, `description`, `created_by`, `created_at`, `updated_at`) VALUES
(46, 'First Order', 'CU0001/J25-001', 9, 2, '2025-12-13', '2025-12-13', '100% Payment', NULL, NULL, 1, '2025-12-13 04:34:54', '2025-12-15 14:06:54'),
(47, 'Second Order', 'CU0001/J25-002', 9, 2, '2025-12-19', '2025-12-19', '100% Payment', NULL, NULL, 1, '2025-12-19 02:48:53', '2025-12-19 02:48:53'),
(48, 'Third Order', 'CU0001/J25-003', 9, 2, '2025-12-24', '2025-12-24', '100% Payment', NULL, NULL, 1, '2025-12-23 14:13:20', '2025-12-23 14:13:20'),
(49, '15487', 'CU0001/J25-004', 10, 2, '2025-12-26', '2025-12-26', '5050', NULL, NULL, 1, '2025-12-25 14:13:38', '2025-12-25 14:25:45'),
(50, 'Dummy1234', 'CU0002/J25-002', 10, 3, '2025-12-27', '2025-12-27', '100% Payment', NULL, NULL, 1, '2025-12-27 01:52:53', '2025-12-27 03:05:18'),
(51, 'C#3-O#1', 'CU0003/J26-001', 11, 2, '2026-01-15', '2026-01-15', '100% Payment', NULL, NULL, 1, '2026-01-15 03:16:26', '2026-01-15 04:05:46'),
(52, 'Test order 2026', 'CU0003/J26-002', 11, 2, '2026-02-17', '2026-02-17', 'Not needed', NULL, NULL, 1, '2026-02-17 13:22:00', '2026-02-17 13:22:00'),
(53, 'AG-101', 'CU0005/J26-001', 13, 2, '2026-02-28', '2026-02-28', '.', NULL, NULL, 1, '2026-02-28 05:53:53', '2026-02-28 05:53:53');

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
  `total` double UNSIGNED NOT NULL COMMENT 'Quantity * Price',
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`order_item_id`, `order_id`, `product_type_id`, `product_stage_id`, `quantity`, `price`, `price2`, `total`, `created_by`, `created_at`, `updated_at`) VALUES
(118, 46, 124, 116, 150, 42000, 150, 6300000, 1, '2025-12-13 04:37:28', '2025-12-13 04:37:28'),
(119, 46, 127, 116, 150, 49000, 175, 7350000, 1, '2025-12-13 04:37:28', '2025-12-13 04:37:28'),
(120, 47, 126, 114, 100, 1400, 5, 140000, 1, '2025-12-19 02:48:53', '2025-12-19 02:48:53'),
(121, 47, 125, 116, 100, 1400, 5, 140000, 1, '2025-12-19 02:48:53', '2025-12-19 02:48:53'),
(122, 47, 124, 116, 100, 1400, 5, 140000, 1, '2025-12-19 02:48:53', '2025-12-19 02:48:53'),
(123, 47, 127, 116, 50, 1680, 6, 84000, 1, '2025-12-19 02:48:53', '2025-12-19 02:48:53'),
(124, 48, 124, 116, 10, 2800, 10, 28000, 1, '2025-12-23 14:13:20', '2025-12-23 14:13:20'),
(125, 49, 126, 116, 50, 50, NULL, 2500, 1, '2025-12-25 14:13:38', '2025-12-25 14:13:38'),
(126, 49, 124, 114, 12, 40, NULL, 480, 1, '2025-12-25 14:13:38', '2025-12-25 14:13:38'),
(127, 50, 124, 116, 50, 5, NULL, 250, 1, '2025-12-27 01:52:53', '2025-12-27 01:52:53'),
(128, 50, 125, 116, 15, 3, NULL, 45, 1, '2025-12-27 01:52:53', '2025-12-27 01:52:53'),
(129, 51, 124, 116, 150, 15, NULL, 2250, 1, '2026-01-15 03:16:26', '2026-01-15 03:16:26'),
(130, 51, 125, 116, 125, 18, NULL, 2250, 1, '2026-01-15 03:16:26', '2026-01-15 03:16:26'),
(131, 52, 124, 116, 150, 150, NULL, 22500, 1, '2026-02-17 13:22:00', '2026-02-17 13:22:00'),
(132, 52, 127, 116, 150, 150, NULL, 22500, 1, '2026-02-17 13:22:00', '2026-02-17 13:22:00'),
(133, 53, 129, 125, 150, 6, NULL, 900, 1, '2026-02-28 05:53:53', '2026-02-28 05:53:53'),
(134, 53, 130, 125, 150, 5, NULL, 750, 1, '2026-02-28 05:53:53', '2026-02-28 05:53:53'),
(135, 53, 131, 125, 150, 4, NULL, 600, 1, '2026-02-28 05:53:53', '2026-02-28 05:53:53');

-- --------------------------------------------------------

--
-- Table structure for table `packing_cartons`
--

CREATE TABLE `packing_cartons` (
  `packing_carton_id` bigint(20) UNSIGNED NOT NULL,
  `packing_list_id` bigint(20) UNSIGNED NOT NULL,
  `carton_from` int(11) NOT NULL,
  `carton_to` int(11) NOT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `box_dimension` varchar(255) DEFAULT NULL COMMENT 'Box dimensions like 65x42x22 cm',
  `box_weight` decimal(8,2) DEFAULT NULL COMMENT 'Weight per individual carton'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `packing_cartons`
--

INSERT INTO `packing_cartons` (`packing_carton_id`, `packing_list_id`, `carton_from`, `carton_to`, `created_by`, `created_at`, `updated_at`, `box_dimension`, `box_weight`) VALUES
(12, 5, 1, 2, 1, '2025-12-29 11:55:01', '2025-12-29 11:55:01', '65x42x22', 23.00),
(13, 5, 3, 3, 1, '2025-12-29 11:55:01', '2025-12-29 11:55:01', NULL, NULL),
(16, 6, 1, 1, 1, '2025-12-30 02:53:20', '2025-12-30 02:53:20', 'First box dimension', 12.00),
(17, 6, 2, 2, 1, '2025-12-30 02:53:20', '2025-12-30 02:53:20', '2nd bd', 15.00);

-- --------------------------------------------------------

--
-- Table structure for table `packing_carton_items`
--

CREATE TABLE `packing_carton_items` (
  `packing_carton_item_id` bigint(20) UNSIGNED NOT NULL,
  `packing_carton_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `pcs_each_carton` int(11) NOT NULL,
  `total_pcs` int(11) NOT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `packing_carton_items`
--

INSERT INTO `packing_carton_items` (`packing_carton_item_id`, `packing_carton_id`, `product_id`, `pcs_each_carton`, `total_pcs`, `created_by`, `created_at`, `updated_at`) VALUES
(17, 12, 43, 25, 50, 1, '2025-12-29 11:55:01', '2025-12-29 11:55:01'),
(18, 13, 43, 15, 15, 1, '2025-12-29 11:55:01', '2025-12-29 11:55:01'),
(23, 16, 43, 7, 17, 1, '2025-12-30 02:53:20', '2025-12-30 02:53:20'),
(24, 16, 44, 8, 18, 1, '2025-12-30 02:53:20', '2025-12-30 02:53:20'),
(25, 17, 44, 10, 18, 1, '2025-12-30 02:53:20', '2025-12-30 02:53:20'),
(26, 17, 43, 10, 17, 1, '2025-12-30 02:53:20', '2025-12-30 02:53:20');

-- --------------------------------------------------------

--
-- Table structure for table `packing_lists`
--

CREATE TABLE `packing_lists` (
  `packing_list_id` bigint(20) UNSIGNED NOT NULL,
  `delivery_id` bigint(20) UNSIGNED NOT NULL,
  `order_id` bigint(20) UNSIGNED NOT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `pallet_qty` int(11) DEFAULT NULL COMMENT 'Number of pallets',
  `pallet_weight` decimal(8,2) DEFAULT NULL COMMENT 'Weight per pallet',
  `pallet_dimension` varchar(255) DEFAULT NULL COMMENT 'Pallet dimensions like 100x120 cm'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `packing_lists`
--

INSERT INTO `packing_lists` (`packing_list_id`, `delivery_id`, `order_id`, `created_by`, `created_at`, `updated_at`, `pallet_qty`, `pallet_weight`, `pallet_dimension`) VALUES
(5, 34, 50, 1, '2025-12-27 03:23:05', '2025-12-29 11:55:01', 10, 4.32, '100x200 cm'),
(6, 31, 46, 1, '2025-12-29 12:58:41', '2025-12-30 02:53:20', 10, 4.32, 'pallet dimension');

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
(1, 'stocks_access', '2025-12-13 03:15:00', '2025-12-13 03:15:00'),
(2, 'stocks_show', '2025-12-13 03:15:00', '2025-12-13 03:15:00'),
(3, 'stocks_create', '2025-12-13 03:15:00', '2025-12-13 03:15:00'),
(4, 'stocks_edit', '2025-12-13 03:15:00', '2025-12-13 03:15:00'),
(5, 'stocks_delete', '2025-12-13 03:15:00', '2025-12-13 03:15:00'),
(6, 'issuances_access', '2025-12-13 03:15:00', '2025-12-13 03:15:00'),
(7, 'issuances_show', '2025-12-13 03:15:00', '2025-12-13 03:15:00'),
(8, 'issuances_create', '2025-12-13 03:15:00', '2025-12-13 03:15:00'),
(9, 'issuances_edit', '2025-12-13 03:15:00', '2025-12-13 03:15:00'),
(10, 'issuances_delete', '2025-12-13 03:15:00', '2025-12-13 03:15:00'),
(11, 'purchases_access', '2025-12-13 03:15:00', '2025-12-13 03:15:00'),
(12, 'purchases_show', '2025-12-13 03:15:00', '2025-12-13 03:15:00'),
(13, 'purchases_create', '2025-12-13 03:15:00', '2025-12-13 03:15:00'),
(14, 'purchases_edit', '2025-12-13 03:15:00', '2025-12-13 03:15:00'),
(15, 'purchases_delete', '2025-12-13 03:15:00', '2025-12-13 03:15:00'),
(16, 'transactions_access', '2025-12-13 03:15:00', '2025-12-13 03:15:00'),
(17, 'transactions_show', '2025-12-13 03:15:00', '2025-12-13 03:15:00'),
(18, 'transactions_create', '2025-12-13 03:15:00', '2025-12-13 03:15:00'),
(19, 'transactions_edit', '2025-12-13 03:15:00', '2025-12-13 03:15:00'),
(20, 'transactions_delete', '2025-12-13 03:15:00', '2025-12-13 03:15:00'),
(21, 'banks_access', '2025-12-13 03:15:00', '2025-12-13 03:15:00'),
(22, 'banks_show', '2025-12-13 03:15:00', '2025-12-13 03:15:00'),
(23, 'banks_create', '2025-12-13 03:15:00', '2025-12-13 03:15:00'),
(24, 'banks_edit', '2025-12-13 03:15:00', '2025-12-13 03:15:00'),
(25, 'banks_delete', '2025-12-13 03:15:00', '2025-12-13 03:15:00'),
(26, 'machines_access', '2025-12-13 03:15:00', '2025-12-13 03:15:00'),
(27, 'machines_show', '2025-12-13 03:15:00', '2025-12-13 03:15:00'),
(28, 'machines_create', '2025-12-13 03:15:00', '2025-12-13 03:15:00'),
(29, 'machines_edit', '2025-12-13 03:15:00', '2025-12-13 03:15:00'),
(30, 'machines_delete', '2025-12-13 03:15:00', '2025-12-13 03:15:00'),
(31, 'orders_access', '2025-12-13 03:15:00', '2025-12-13 03:15:00'),
(32, 'orders_show', '2025-12-13 03:15:00', '2025-12-13 03:15:00'),
(33, 'orders_create', '2025-12-13 03:15:00', '2025-12-13 03:15:00'),
(34, 'orders_edit', '2025-12-13 03:15:00', '2025-12-13 03:15:00'),
(35, 'orders_delete', '2025-12-13 03:15:00', '2025-12-13 03:15:00'),
(36, 'deliveries_access', '2025-12-13 03:15:00', '2025-12-13 03:15:00'),
(37, 'deliveries_show', '2025-12-13 03:15:00', '2025-12-13 03:15:00'),
(38, 'deliveries_create', '2025-12-13 03:15:00', '2025-12-13 03:15:00'),
(39, 'deliveries_edit', '2025-12-13 03:15:00', '2025-12-13 03:15:00'),
(40, 'deliveries_delete', '2025-12-13 03:15:00', '2025-12-13 03:15:00'),
(41, 'customers_access', '2025-12-13 03:15:00', '2025-12-13 03:15:00'),
(42, 'customers_show', '2025-12-13 03:15:00', '2025-12-13 03:15:00'),
(43, 'customers_create', '2025-12-13 03:15:00', '2025-12-13 03:15:00'),
(44, 'customers_edit', '2025-12-13 03:15:00', '2025-12-13 03:15:00'),
(45, 'customers_delete', '2025-12-13 03:15:00', '2025-12-13 03:15:00'),
(46, 'employees_access', '2025-12-13 03:15:00', '2025-12-13 03:15:00'),
(47, 'employees_show', '2025-12-13 03:15:00', '2025-12-13 03:15:00'),
(48, 'employees_create', '2025-12-13 03:15:00', '2025-12-13 03:15:00'),
(49, 'employees_edit', '2025-12-13 03:15:00', '2025-12-13 03:15:00'),
(50, 'employees_delete', '2025-12-13 03:15:00', '2025-12-13 03:15:00'),
(51, 'vendors_access', '2025-12-13 03:15:00', '2025-12-13 03:15:00'),
(52, 'vendors_show', '2025-12-13 03:15:00', '2025-12-13 03:15:00'),
(53, 'vendors_create', '2025-12-13 03:15:00', '2025-12-13 03:15:00'),
(54, 'vendors_edit', '2025-12-13 03:15:00', '2025-12-13 03:15:00'),
(55, 'vendors_delete', '2025-12-13 03:15:00', '2025-12-13 03:15:00'),
(56, 'contractors_access', '2025-12-13 03:15:00', '2025-12-13 03:15:00'),
(57, 'contractors_show', '2025-12-13 03:15:00', '2025-12-13 03:15:00'),
(58, 'contractors_create', '2025-12-13 03:15:00', '2025-12-13 03:15:00'),
(59, 'contractors_edit', '2025-12-13 03:15:00', '2025-12-13 03:15:00'),
(60, 'contractors_delete', '2025-12-13 03:15:00', '2025-12-13 03:15:00'),
(61, 'products_access', '2025-12-13 03:15:00', '2025-12-13 03:15:00'),
(62, 'products_show', '2025-12-13 03:15:00', '2025-12-13 03:15:00'),
(63, 'products_create', '2025-12-13 03:15:00', '2025-12-13 03:15:00'),
(64, 'products_edit', '2025-12-13 03:15:00', '2025-12-13 03:15:00'),
(65, 'products_delete', '2025-12-13 03:15:00', '2025-12-13 03:15:00'),
(66, 'materials_access', '2025-12-13 03:15:00', '2025-12-13 03:15:00'),
(67, 'materials_show', '2025-12-13 03:15:00', '2025-12-13 03:15:00'),
(68, 'materials_create', '2025-12-13 03:15:00', '2025-12-13 03:15:00'),
(69, 'materials_edit', '2025-12-13 03:15:00', '2025-12-13 03:15:00'),
(70, 'materials_delete', '2025-12-13 03:15:00', '2025-12-13 03:15:00'),
(71, 'attendance_access', '2025-12-13 03:15:00', '2025-12-13 03:15:00'),
(72, 'attendance_show', '2025-12-13 03:15:00', '2025-12-13 03:15:00'),
(73, 'attendance_create', '2025-12-13 03:15:00', '2025-12-13 03:15:00'),
(74, 'attendance_edit', '2025-12-13 03:15:00', '2025-12-13 03:15:00'),
(75, 'attendance_delete', '2025-12-13 03:15:00', '2025-12-13 03:15:00'),
(76, 'payroll_access', '2025-12-13 03:15:00', '2025-12-13 03:15:00'),
(77, 'payroll_show', '2025-12-13 03:15:00', '2025-12-13 03:15:00'),
(78, 'payroll_create', '2025-12-13 03:15:00', '2025-12-13 03:15:00'),
(79, 'payroll_edit', '2025-12-13 03:15:00', '2025-12-13 03:15:00'),
(80, 'payroll_delete', '2025-12-13 03:15:00', '2025-12-13 03:15:00'),
(81, 'reports_access', '2025-12-13 03:15:00', '2025-12-13 03:15:00'),
(82, 'reports_show', '2025-12-13 03:15:00', '2025-12-13 03:15:00'),
(83, 'reports_create', '2025-12-13 03:15:00', '2025-12-13 03:15:00'),
(84, 'reports_edit', '2025-12-13 03:15:00', '2025-12-13 03:15:00'),
(85, 'reports_delete', '2025-12-13 03:15:00', '2025-12-13 03:15:00'),
(86, 'settings_access', '2025-12-13 03:15:00', '2025-12-13 03:15:00'),
(87, 'settings_show', '2025-12-13 03:15:00', '2025-12-13 03:15:00'),
(88, 'settings_create', '2025-12-13 03:15:00', '2025-12-13 03:15:00'),
(89, 'settings_edit', '2025-12-13 03:15:00', '2025-12-13 03:15:00'),
(90, 'settings_delete', '2025-12-13 03:15:00', '2025-12-13 03:15:00'),
(91, 'assets_access', '2025-12-13 03:15:00', '2025-12-13 03:15:00'),
(92, 'assets_show', '2025-12-13 03:15:00', '2025-12-13 03:15:00'),
(93, 'assets_create', '2025-12-13 03:15:00', '2025-12-13 03:15:00'),
(94, 'assets_edit', '2025-12-13 03:15:00', '2025-12-13 03:15:00'),
(95, 'assets_delete', '2025-12-13 03:15:00', '2025-12-13 03:15:00');

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
(3, 3, 1),
(6, 3, 2),
(13, 3, 6),
(16, 3, 7),
(23, 3, 11),
(26, 3, 12),
(33, 3, 16),
(36, 3, 17),
(43, 3, 21),
(46, 3, 22),
(53, 3, 26),
(56, 3, 27),
(63, 3, 31),
(66, 3, 32),
(73, 3, 36),
(76, 3, 37),
(83, 3, 41),
(86, 3, 42),
(93, 3, 46),
(96, 3, 47),
(103, 3, 51),
(106, 3, 52),
(113, 3, 56),
(116, 3, 57),
(123, 3, 61),
(126, 3, 62),
(133, 3, 66),
(136, 3, 67),
(143, 3, 71),
(146, 3, 72),
(153, 3, 76),
(156, 3, 77),
(163, 3, 81),
(166, 3, 82),
(173, 3, 86),
(176, 3, 87),
(188, 8, 1),
(189, 8, 2),
(190, 8, 3),
(191, 8, 4),
(192, 8, 5),
(195, 3, 91),
(198, 3, 92),
(205, 3, 96),
(208, 3, 97),
(215, 3, 101),
(218, 3, 102),
(225, 3, 106),
(228, 3, 107),
(235, 3, 111),
(238, 3, 112),
(688, 9, 1),
(689, 9, 2),
(690, 9, 3),
(691, 9, 4),
(692, 9, 5),
(1400, 1, 1),
(1401, 1, 2),
(1402, 1, 3),
(1403, 1, 4),
(1404, 1, 5),
(1405, 1, 6),
(1406, 1, 7),
(1407, 1, 8),
(1408, 1, 9),
(1409, 1, 10),
(1410, 1, 11),
(1411, 1, 12),
(1412, 1, 13),
(1413, 1, 14),
(1414, 1, 15),
(1415, 1, 16),
(1416, 1, 17),
(1417, 1, 18),
(1418, 1, 19),
(1419, 1, 20),
(1420, 1, 21),
(1421, 1, 22),
(1422, 1, 23),
(1423, 1, 24),
(1424, 1, 25),
(1425, 1, 26),
(1426, 1, 27),
(1427, 1, 28),
(1428, 1, 29),
(1429, 1, 30),
(1430, 1, 31),
(1431, 1, 32),
(1432, 1, 33),
(1433, 1, 34),
(1434, 1, 35),
(1435, 1, 36),
(1436, 1, 37),
(1437, 1, 38),
(1438, 1, 39),
(1439, 1, 40),
(1440, 1, 41),
(1441, 1, 42),
(1442, 1, 43),
(1443, 1, 44),
(1444, 1, 45),
(1445, 1, 46),
(1446, 1, 47),
(1447, 1, 48),
(1448, 1, 49),
(1449, 1, 50),
(1450, 1, 51),
(1451, 1, 52),
(1452, 1, 53),
(1453, 1, 54),
(1454, 1, 55),
(1455, 1, 56),
(1456, 1, 57),
(1457, 1, 58),
(1458, 1, 59),
(1459, 1, 60),
(1460, 1, 61),
(1461, 1, 62),
(1462, 1, 63),
(1463, 1, 64),
(1464, 1, 65),
(1465, 1, 66),
(1466, 1, 67),
(1467, 1, 68),
(1468, 1, 69),
(1469, 1, 70),
(1470, 1, 71),
(1471, 1, 72),
(1472, 1, 73),
(1473, 1, 74),
(1474, 1, 75),
(1475, 1, 76),
(1476, 1, 77),
(1477, 1, 78),
(1478, 1, 79),
(1479, 1, 80),
(1480, 1, 81),
(1481, 1, 82),
(1482, 1, 83),
(1483, 1, 84),
(1484, 1, 85),
(1485, 1, 86),
(1486, 1, 87),
(1487, 1, 88),
(1488, 1, 89),
(1489, 1, 90),
(1490, 1, 91),
(1491, 1, 92),
(1492, 1, 93),
(1493, 1, 94),
(1494, 1, 95),
(1607, 2, 1),
(1608, 2, 2),
(1609, 2, 3),
(1610, 2, 6),
(1611, 2, 7),
(1612, 2, 8),
(1613, 2, 11),
(1614, 2, 12),
(1615, 2, 13),
(1616, 2, 16),
(1617, 2, 17),
(1618, 2, 18),
(1619, 2, 21),
(1620, 2, 22),
(1621, 2, 23),
(1622, 2, 26),
(1623, 2, 27),
(1624, 2, 28),
(1625, 2, 31),
(1626, 2, 32),
(1627, 2, 33),
(1628, 2, 36),
(1629, 2, 37),
(1630, 2, 38),
(1631, 2, 46),
(1632, 2, 47),
(1633, 2, 48),
(1634, 2, 51),
(1635, 2, 52),
(1636, 2, 53),
(1637, 2, 56),
(1638, 2, 57),
(1639, 2, 58),
(1640, 2, 61),
(1641, 2, 62),
(1642, 2, 63),
(1643, 2, 66),
(1644, 2, 67),
(1645, 2, 68),
(1646, 2, 71),
(1647, 2, 72),
(1648, 2, 73),
(1649, 2, 76),
(1650, 2, 77),
(1651, 2, 78),
(1652, 2, 81),
(1653, 2, 82),
(1654, 2, 83),
(1655, 2, 86),
(1656, 2, 87),
(1657, 2, 88),
(1658, 2, 91),
(1659, 2, 92),
(1660, 2, 93),
(1661, 2, 41),
(1662, 2, 42),
(1663, 2, 43);

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
  `hs_code` varchar(255) DEFAULT NULL,
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

INSERT INTO `products` (`product_id`, `category_id`, `material_id`, `stage_ids`, `article_no`, `hs_code`, `name`, `description`, `unit_id`, `product_status`, `created_by`, `created_at`, `updated_at`) VALUES
(43, 10, '35|34|33|32', '114|115|116', 'Pro#1', '0101', 'Product #1', NULL, 47, 1, 1, '2025-12-13 03:23:17', '2025-12-13 03:23:17'),
(44, 10, '33|32', '114|115|116', 'Pro#2', '0202', 'Product #2', NULL, 47, 1, 1, '2025-12-13 03:25:29', '2025-12-13 03:25:29'),
(45, 10, '35|34|33|32', '114|115|116|117|118', '252', '252', 'Product #5', NULL, 51, 1, 1, '2026-02-26 05:23:12', '2026-02-26 05:23:12'),
(46, 10, '39|38', '122|123|124|125', 'WG-101', 'WG-101', 'Working Gloves 101', NULL, 47, 1, 1, '2026-02-28 05:29:53', '2026-02-28 05:33:15');

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
(112, 43, 'general', 0, 84, 150, 1, '2025-12-15 14:32:18', '2025-12-15 14:32:18'),
(113, 46, 'general', 0, 78, 5, 1, '2026-02-28 05:43:22', '2026-02-28 05:43:22'),
(114, 46, 'general', 0, 79, 20, 1, '2026-02-28 05:43:22', '2026-02-28 05:43:22'),
(115, 46, 'general', 0, 83, 3, 1, '2026-02-28 05:43:22', '2026-02-28 05:43:22'),
(116, 46, 'general', 0, 84, 2, 1, '2026-02-28 05:43:22', '2026-02-28 05:43:22'),
(117, 46, 'employee', 16, 78, 6, 1, '2026-02-28 05:43:22', '2026-02-28 05:43:22');

-- --------------------------------------------------------

--
-- Table structure for table `product_materials`
--

CREATE TABLE `product_materials` (
  `product_material_id` bigint(20) UNSIGNED NOT NULL,
  `product_type_id` bigint(20) NOT NULL,
  `material_id` bigint(20) DEFAULT NULL,
  `component_type` enum('material','product') NOT NULL DEFAULT 'material',
  `component_product_type_id` bigint(20) DEFAULT NULL,
  `quantity` double UNSIGNED NOT NULL,
  `created_by` bigint(20) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_materials`
--

INSERT INTO `product_materials` (`product_material_id`, `product_type_id`, `material_id`, `component_type`, `component_product_type_id`, `quantity`, `created_by`, `created_at`, `updated_at`) VALUES
(575, 126, 32, 'material', NULL, 1, 1, '2025-12-15 10:19:05', '2025-12-15 10:19:05'),
(576, 126, 33, 'material', NULL, 2, 1, '2025-12-15 10:19:05', '2025-12-15 10:19:05'),
(577, 126, 34, 'material', NULL, 3, 1, '2025-12-15 10:19:05', '2025-12-15 10:19:05'),
(578, 126, 35, 'material', NULL, 4, 1, '2025-12-15 10:19:05', '2025-12-15 10:19:05'),
(579, 126, 36, 'material', NULL, 0.03333333333333333, 1, '2025-12-15 10:19:05', '2025-12-15 10:19:05'),
(580, 125, 32, 'material', NULL, 1, 1, '2025-12-15 10:21:23', '2025-12-15 10:21:23'),
(581, 125, 33, 'material', NULL, 2, 1, '2025-12-15 10:21:23', '2025-12-15 10:21:23'),
(582, 125, 34, 'material', NULL, 3, 1, '2025-12-15 10:21:23', '2025-12-15 10:21:23'),
(583, 125, 35, 'material', NULL, 4, 1, '2025-12-15 10:21:23', '2025-12-15 10:21:23'),
(584, 125, 36, 'material', NULL, 0.03333333333333333, 1, '2025-12-15 10:21:23', '2025-12-15 10:21:23'),
(585, 124, 32, 'material', NULL, 1, 1, '2025-12-15 10:21:30', '2025-12-15 10:21:30'),
(586, 124, 33, 'material', NULL, 2, 1, '2025-12-15 10:21:30', '2025-12-15 10:21:30'),
(587, 124, 34, 'material', NULL, 3, 1, '2025-12-15 10:21:30', '2025-12-15 10:21:30'),
(588, 124, 35, 'material', NULL, 4, 1, '2025-12-15 10:21:30', '2025-12-15 10:21:30'),
(589, 124, 36, 'material', NULL, 0.03333333333333333, 1, '2025-12-15 10:21:30', '2025-12-15 10:21:30'),
(590, 127, 32, 'material', NULL, 2, 1, '2025-12-15 10:21:55', '2025-12-15 10:21:55'),
(591, 127, 33, 'material', NULL, 4, 1, '2025-12-15 10:21:55', '2025-12-15 10:21:55'),
(592, 127, 36, 'material', NULL, 0.04, 1, '2025-12-15 10:21:55', '2025-12-15 10:21:55'),
(594, 127, NULL, 'product', 126, 2, 1, '2025-12-19 02:53:24', '2025-12-19 02:53:24'),
(595, 128, NULL, 'product', 124, 1, 1, '2026-02-26 05:23:12', '2026-02-26 05:23:12'),
(596, 131, 38, 'material', NULL, 1.5, 1, '2026-02-28 05:35:54', '2026-02-28 05:35:54'),
(597, 131, 39, 'material', NULL, 0.2, 1, '2026-02-28 05:35:54', '2026-02-28 05:35:54'),
(598, 131, 36, 'material', NULL, 0.02, 1, '2026-02-28 05:35:54', '2026-02-28 05:35:54'),
(599, 130, 38, 'material', NULL, 2, 1, '2026-02-28 05:36:42', '2026-02-28 05:36:42'),
(600, 130, 39, 'material', NULL, 0.3, 1, '2026-02-28 05:36:42', '2026-02-28 05:36:42'),
(601, 130, 36, 'material', NULL, 0.02, 1, '2026-02-28 05:36:42', '2026-02-28 05:36:42'),
(602, 129, 38, 'material', NULL, 3, 1, '2026-02-28 05:37:07', '2026-02-28 05:37:07'),
(603, 129, 39, 'material', NULL, 0.4, 1, '2026-02-28 05:37:07', '2026-02-28 05:37:07'),
(604, 129, 36, 'material', NULL, 0.02, 1, '2026-02-28 05:37:07', '2026-02-28 05:37:07');

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
(124, 43, 3, NULL, 1, 1, '2025-12-13 03:23:17', '2025-12-20 02:39:08'),
(125, 43, 2, NULL, 1, 1, '2025-12-13 03:23:17', '2025-12-20 02:39:08'),
(126, 43, 1, NULL, 1, 1, '2025-12-13 03:23:17', '2025-12-20 02:39:08'),
(127, 44, 4, NULL, 1, 1, '2025-12-13 03:25:29', '2025-12-20 02:36:22'),
(128, 45, 3, NULL, 1, 1, '2026-02-26 05:23:12', '2026-02-26 05:23:12'),
(129, 46, 3, NULL, 1, 1, '2026-02-28 05:29:53', '2026-02-28 05:33:15'),
(130, 46, 2, NULL, 1, 1, '2026-02-28 05:29:53', '2026-02-28 05:33:15'),
(131, 46, 1, NULL, 1, 1, '2026-02-28 05:29:53', '2026-02-28 05:33:15');

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
(84, 'P2512001', 'material', 0, 19, NULL, '2025-12-15', '2025-12-15', 1, '2025-12-15 13:26:23', '2025-12-15 13:26:23'),
(87, 'P2512002', 'mProcess', 46, 19, NULL, '2025-12-16', '2025-12-16', 1, '2025-12-16 04:17:15', '2025-12-16 04:17:15'),
(88, 'P2512003', 'product', 0, 19, NULL, '2025-12-16', '2025-12-16', 1, '2025-12-16 04:38:56', '2025-12-16 04:38:56'),
(89, 'P2512004', 'material', 0, 19, NULL, '2025-12-17', '2025-12-17', 1, '2025-12-17 11:47:16', '2025-12-17 11:47:16'),
(90, 'P2512005', 'product', 0, 21, NULL, '2025-12-20', '2025-12-20', 1, '2025-12-19 14:25:44', '2025-12-19 14:25:44'),
(91, 'P2512006', 'product', 50, 21, NULL, '2025-12-27', '2025-12-27', 1, '2025-12-27 02:25:01', '2025-12-27 02:25:01'),
(92, 'P2512007', 'product', 0, 19, NULL, '2025-12-29', '2025-12-29', 1, '2025-12-29 02:10:53', '2025-12-29 02:10:53'),
(93, 'P2512008', 'material', 0, 21, NULL, '2025-12-31', '2025-12-31', 1, '2025-12-30 14:08:01', '2025-12-30 14:08:01'),
(94, 'P2512009', 'material', 0, 19, NULL, '2025-12-31', '2025-12-31', 1, '2025-12-31 07:08:53', '2025-12-31 07:08:53'),
(95, 'P2602001', 'material', 0, 21, 'Order #1', '2026-02-28', '2026-02-28', 1, '2026-02-28 05:20:44', '2026-02-28 05:20:44'),
(96, 'P2602002', 'material', 0, 19, NULL, '2026-02-28', '2026-02-28', 1, '2026-02-28 05:21:42', '2026-02-28 05:21:42');

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
(10, 84, 0, 0, 33, 500, 250, 125000, 1, '2025-12-15 13:26:23', '2025-12-15 13:26:23'),
(11, 87, 0, 0, 33, 40, 150, 6000, 1, '2025-12-16 04:17:15', '2025-12-16 04:17:15'),
(12, 88, 125, 116, 0, 500, 175, 87500, 1, '2025-12-16 04:38:56', '2025-12-16 04:38:56'),
(13, 89, 0, 0, 33, 150, 55, 8250, 1, '2025-12-17 11:47:16', '2025-12-17 11:47:16'),
(14, 90, 124, 116, 0, 10, 150, 1500, 1, '2025-12-19 14:25:44', '2025-12-19 14:25:44'),
(15, 91, 125, 116, 0, 150, 780, 117000, 1, '2025-12-27 02:25:01', '2025-12-27 02:25:01'),
(16, 92, 124, 116, 0, 15, 150, 2250, 1, '2025-12-29 02:10:53', '2025-12-29 02:10:53'),
(17, 93, 0, 0, 36, 100, 150, 15000, 1, '2025-12-30 14:08:01', '2025-12-30 14:08:01'),
(18, 94, 0, 0, 37, 150, 150, 22500, 1, '2025-12-31 07:08:53', '2025-12-31 07:08:53'),
(19, 95, 0, 0, 38, 900, 300, 270000, 1, '2026-02-28 05:20:44', '2026-02-28 05:20:44'),
(20, 96, 0, 0, 38, 100, 300, 30000, 1, '2026-02-28 05:21:42', '2026-02-28 05:21:42'),
(21, 96, 0, 0, 39, 100, 100, 10000, 1, '2026-02-28 05:21:42', '2026-02-28 05:21:42');

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
(3, 'R1-P2512001', 84, '2025-12-15', 0, NULL, 1, '2025-12-15 13:28:47', '2025-12-15 13:28:47'),
(4, 'R1-P2512003', 88, '2025-12-16', 0, NULL, 1, '2025-12-16 04:39:07', '2025-12-16 04:39:07'),
(5, 'R1-P2512002', 87, '2025-12-16', 0, NULL, 1, '2025-12-16 04:45:00', '2025-12-16 04:45:00'),
(6, 'R1-P2512004', 89, '2025-12-17', 0, NULL, 1, '2025-12-17 11:47:45', '2025-12-17 11:47:45'),
(7, 'R1-P2512005', 90, '2025-12-19', 0, NULL, 1, '2025-12-19 14:26:13', '2025-12-19 14:26:13'),
(8, 'R2-P2512005', 90, '2025-12-19', 0, NULL, 1, '2025-12-19 14:26:37', '2025-12-19 14:26:37'),
(9, 'R1-P2512006', 91, '2025-12-27', 0, NULL, 1, '2025-12-27 02:27:58', '2025-12-27 02:27:58'),
(10, 'R2-P2512006', 91, '2025-12-27', 0, NULL, 1, '2025-12-27 02:45:18', '2025-12-27 02:45:18'),
(11, 'R1-P2512007', 92, '2025-12-29', 0, NULL, 1, '2025-12-29 02:11:17', '2025-12-29 02:11:17'),
(12, 'R1-P2602001', 95, '2026-02-28', 0, NULL, 1, '2026-02-28 05:24:19', '2026-02-28 05:24:19'),
(13, 'R1-P2602002', 96, '2026-02-28', 0, NULL, 1, '2026-02-28 05:26:35', '2026-02-28 05:26:35'),
(14, 'R2-P2602001', 95, '2026-02-28', 0, NULL, 1, '2026-02-28 05:28:17', '2026-02-28 05:28:17');

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
(10, 3, 10, 500, 0, 500, 0, '2025-12-15', 1, '2025-12-15 13:28:47', '2025-12-15 13:28:47'),
(11, 4, 12, 500, 0, 500, 0, '2025-12-16', 1, '2025-12-16 04:39:07', '2025-12-16 04:39:07'),
(12, 5, 11, 40, 0, 40, 0, '2025-12-16', 1, '2025-12-16 04:45:00', '2025-12-16 04:45:00'),
(13, 6, 13, 150, 0, 150, 0, '2025-12-17', 1, '2025-12-17 11:47:45', '2025-12-17 11:47:45'),
(14, 7, 14, 8, 3, 5, 0, '2025-12-20', 1, '2025-12-19 14:26:13', '2025-12-19 14:26:13'),
(15, 8, 14, 2, 0, 2, 0, '2025-12-20', 1, '2025-12-19 14:26:37', '2025-12-19 14:26:37'),
(16, 9, 15, 100, 50, 50, 0, '2025-12-27', 1, '2025-12-27 02:27:58', '2025-12-27 02:27:58'),
(17, 10, 15, 50, 0, 50, 0, '2025-12-27', 1, '2025-12-27 02:45:18', '2025-12-27 02:45:18'),
(18, 11, 16, 15, 0, 15, 0, '2025-12-29', 1, '2025-12-29 02:11:17', '2025-12-29 02:11:17'),
(19, 12, 19, 900, 0, 800, 100, '2026-02-28', 1, '2026-02-28 05:24:19', '2026-02-28 05:24:19'),
(20, 13, 20, 100, 0, 100, 0, '2026-02-28', 1, '2026-02-28 05:26:35', '2026-02-28 05:26:35'),
(21, 13, 21, 100, 0, 100, 0, '2026-02-28', 1, '2026-02-28 05:26:35', '2026-02-28 05:26:35'),
(22, 14, 19, 100, 0, 100, 0, '2026-02-28', 1, '2026-02-28 05:28:17', '2026-02-28 05:28:17');

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
(47, 3, 'Return1-R1-P2512001', '2025-12-15', NULL, 1, '2025-12-15 13:41:36', '2025-12-15 13:41:36'),
(48, 10, 'Return1-R2-P2512006', '2025-12-29', NULL, 1, '2025-12-29 02:06:16', '2025-12-29 02:06:16'),
(49, 12, 'Return1-R1-P2602001', '2026-02-28', NULL, 1, '2026-02-28 05:25:17', '2026-02-28 05:25:17');

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
(1, 47, 10, 125, NULL, 1, '2025-12-15 13:41:36', '2025-12-15 13:41:36'),
(2, 48, 17, 10, '10', 1, '2025-12-29 02:06:16', '2025-12-29 02:06:16'),
(3, 49, 19, 100, NULL, 1, '2026-02-28 05:25:17', '2026-02-28 05:25:17');

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
(1, 'Admin', '2025-12-13 03:15:00', '2025-12-13 03:15:00'),
(2, 'Manager', '2025-12-13 03:15:00', '2025-12-13 03:15:00'),
(3, 'Accountant', '2025-12-13 03:15:00', '2025-12-13 03:15:00'),
(8, 'Export', '2025-04-12 03:17:32', '2025-05-13 08:36:12'),
(9, 'Dummy123', '2025-08-23 03:17:08', '2025-08-23 03:17:08');

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
(11, 15, 25000, 1, 1, '2025-12-13 03:32:11', '2025-12-13 03:32:11'),
(12, 16, 75000, 1, 1, '2025-12-13 03:35:16', '2025-12-13 03:35:16'),
(13, 17, 0, 1, 1, '2026-01-01 13:57:26', '2026-01-01 13:57:26');

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('RitJwsxHYlDDrQ3ay19oWwGoJIYzs1iWkF6J8qQz', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'YTo1OntzOjY6Il90b2tlbiI7czo0MDoiOFlxcXg3OXNJSXFYemlxOVZSeDBIY21CVmszVzZwS0dvc3piZ1V6dSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzQ6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9jb21wYW55L2RhdGEiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX1zOjM6InVybCI7YToxOntzOjg6ImludGVuZGVkIjtzOjM0OiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvY29tcGFueS9kYXRhIjt9czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTt9', 1772277068);

-- --------------------------------------------------------

--
-- Table structure for table `stocks`
--

CREATE TABLE `stocks` (
  `stock_id` bigint(20) UNSIGNED NOT NULL,
  `issue_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ptc_id` bigint(20) UNSIGNED DEFAULT NULL COMMENT 'Links to parent PTC master record',
  `current_stage_id` bigint(20) UNSIGNED DEFAULT NULL COMMENT 'Current processing stage for PTC',
  `next_stage_id` bigint(20) UNSIGNED DEFAULT NULL COMMENT 'Next stage in PTC sequence',
  `is_ptc_master` tinyint(4) NOT NULL DEFAULT 0 COMMENT '1 if this is the PTC master record',
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

INSERT INTO `stocks` (`stock_id`, `issue_id`, `ptc_id`, `current_stage_id`, `next_stage_id`, `is_ptc_master`, `issue_for`, `stock_no`, `order_id`, `machine_id`, `table_name`, `employee_id`, `stock_type`, `stock_date`, `stock_status`, `description`, `created_by`, `created_at`, `updated_at`) VALUES
(0, NULL, NULL, NULL, NULL, 0, 0, 'I23000000', 0, NULL, 'mprocess', 0, 2, '2024-05-09', 0, NULL, 1, '2024-04-26 09:05:16', '2024-05-09 03:39:30'),
(1, NULL, NULL, NULL, NULL, 0, 0, 'I24000000', 0, NULL, 'openingStock', 0, 1, '2024-05-09', 0, NULL, 1, '2024-04-26 09:05:16', '2024-05-09 03:39:30'),
(10131, NULL, NULL, NULL, NULL, 0, 114, 'I25120001', 46, NULL, 'employee', 16, 2, '2025-12-15', 1, NULL, 1, '2025-12-15 10:39:07', '2025-12-15 10:44:40'),
(10132, 10131, NULL, NULL, NULL, 0, NULL, 'R1-I25120001', 46, NULL, 'employee', 16, 1, '2025-12-15', 1, NULL, 1, '2025-12-15 10:44:40', '2025-12-15 10:44:40'),
(10133, NULL, NULL, 115, NULL, 1, 114, '2512001', 46, NULL, 'employee', 16, 2, '2025-12-15', 7, '[QTY:15]', 1, '2025-12-15 12:43:28', '2025-12-20 08:34:26'),
(10134, NULL, NULL, NULL, NULL, 0, 117, 'I25120002', 46, NULL, 'vendor', 20, 2, '2025-12-15', 2, NULL, 1, '2025-12-15 12:44:32', '2025-12-15 12:58:46'),
(10135, 10134, NULL, NULL, NULL, 0, NULL, 'R1-I25120002', 46, NULL, 'vendor', 20, 1, '2025-12-15', 2, NULL, 1, '2025-12-15 12:58:46', '2025-12-15 12:58:46'),
(10136, NULL, NULL, NULL, NULL, 0, 117, 'I25120004', 46, NULL, 'employee', 15, 2, '2025-12-15', 2, NULL, 1, '2025-12-15 13:20:06', '2025-12-15 13:20:41'),
(10137, 10136, NULL, NULL, NULL, 0, NULL, 'R1-I25120004', 46, NULL, 'employee', 15, 1, '2025-12-15', 2, NULL, 1, '2025-12-15 13:20:41', '2025-12-15 13:20:41'),
(10140, NULL, NULL, NULL, NULL, 0, 114, 'I25120006', 46, NULL, 'vendor', 20, 2, '2025-12-16', 1, NULL, 1, '2025-12-15 14:35:23', '2025-12-15 14:38:46'),
(10141, 10140, NULL, NULL, NULL, 0, NULL, 'R1-I25120006', 46, NULL, 'vendor', 20, 1, '2025-12-17', 1, NULL, 1, '2025-12-15 14:38:46', '2025-12-15 14:38:46'),
(10142, 10133, 10133, 114, NULL, 0, NULL, 'R1-I2512001', 46, NULL, 'employee', 16, 1, '2025-12-16', 1, NULL, 1, '2025-12-16 10:40:50', '2025-12-16 10:40:50'),
(10143, NULL, 10133, 115, NULL, 0, 115, '001', 46, NULL, 'vendor', 20, 2, '2025-12-16', 6, NULL, 1, '2025-12-16 10:52:00', '2025-12-16 10:52:00'),
(10144, 10143, 10133, 115, NULL, 0, NULL, 'R1-I001', 46, NULL, 'vendor', 20, 1, '2025-12-16', 1, NULL, 1, '2025-12-16 12:42:11', '2025-12-16 12:42:11'),
(10145, NULL, NULL, 115, 116, 1, 115, '2512002', 46, NULL, 'employee', 16, 2, '2025-12-17', 6, '[QTY:150]', 1, '2025-12-17 02:24:32', '2025-12-17 02:24:32'),
(10147, NULL, NULL, NULL, NULL, 0, NULL, 'First Delivery', 46, NULL, 'delivery', 0, 2, '2025-12-18', 3, NULL, 1, '2025-12-18 04:34:39', '2025-12-18 04:34:39'),
(10148, NULL, NULL, NULL, NULL, 0, NULL, 'Multi-Order: Second Order, First Order', 47, NULL, 'delivery', 0, 2, '2025-12-22', 3, NULL, 1, '2025-12-21 14:01:40', '2025-12-21 14:01:40'),
(10149, NULL, NULL, 115, 116, 1, 115, '2512003', 46, NULL, 'vendor', 20, 2, '2025-12-21', 6, '[QTY:5]', 1, '2025-12-21 14:18:07', '2025-12-21 14:18:07'),
(10150, NULL, NULL, NULL, NULL, 0, NULL, '150', 49, NULL, 'delivery', 0, 2, '2025-12-26', 3, NULL, 1, '2025-12-25 14:28:48', '2025-12-25 14:28:48'),
(10151, NULL, NULL, 116, NULL, 1, 114, '2512004', 50, NULL, 'employee', 15, 2, '2025-12-27', 7, '[QTY:5]', 1, '2025-12-27 02:46:47', '2025-12-27 02:48:24'),
(10152, 10151, 10151, 116, NULL, 0, NULL, 'R1-I2512004', 50, NULL, 'employee', 15, 1, '2025-12-27', 1, NULL, 1, '2025-12-27 02:47:28', '2025-12-27 02:47:28'),
(10153, NULL, NULL, NULL, NULL, 0, NULL, 'Dummy', 50, NULL, 'delivery', 0, 2, '2025-12-27', 3, NULL, 1, '2025-12-27 03:05:18', '2025-12-27 03:05:18'),
(10154, NULL, NULL, NULL, NULL, 0, NULL, 'Multi-Order: Third Order, Second Order, First Order', 48, NULL, 'delivery', 0, 2, '2025-12-31', 3, NULL, 1, '2025-12-31 07:28:54', '2025-12-31 07:28:54'),
(10155, NULL, NULL, NULL, NULL, 0, NULL, '49', 49, NULL, 'delivery', 0, 2, '2026-01-01', 3, NULL, 1, '2026-01-01 12:20:33', '2026-01-01 12:20:33'),
(10156, NULL, NULL, NULL, NULL, 0, NULL, '511', 51, NULL, 'delivery', 0, 2, '2026-01-15', 3, NULL, 1, '2026-01-15 03:21:23', '2026-01-15 03:21:23'),
(10159, NULL, NULL, NULL, NULL, 0, NULL, '51', 51, NULL, 'delivery', 0, 2, '2026-01-15', 3, NULL, 1, '2026-01-15 04:05:46', '2026-01-15 04:05:46'),
(10160, NULL, NULL, NULL, NULL, 0, NULL, '39', 51, NULL, 'delivery', 0, 2, '2026-01-15', 3, NULL, 1, '2026-01-15 04:13:58', '2026-01-15 04:13:58'),
(10161, NULL, NULL, 122, 123, 1, 122, '2602001', 53, NULL, 'employee', 16, 2, '2026-02-28', 6, '[QTY:150]', 1, '2026-02-28 06:04:56', '2026-02-28 06:04:56');

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
  `component_product_type_id` bigint(20) UNSIGNED DEFAULT NULL,
  `work_logs` varchar(255) NOT NULL,
  `work_wages` varchar(255) DEFAULT '0',
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `stock_items`
--

INSERT INTO `stock_items` (`stock_item_id`, `stock_id`, `product_type_id`, `material_id`, `quantity`, `stage_id`, `component_product_type_id`, `work_logs`, `work_wages`, `created_by`, `created_at`, `updated_at`) VALUES
(527, 1, 0, 32, 750, 0, NULL, '0', '0', 1, '2025-12-13 03:17:10', '2025-12-13 03:17:32'),
(528, 1, 0, 33, 965, 0, NULL, '0', '0', 1, '2025-12-13 03:18:04', '2025-12-13 03:18:04'),
(529, 1, 0, 34, 0, 0, NULL, '0', '0', 1, '2025-12-13 03:18:22', '2025-12-13 03:18:22'),
(530, 1, 0, 35, 150, 0, NULL, '0', '0', 1, '2025-12-13 03:18:45', '2025-12-13 03:18:45'),
(532, 1, 0, 36, 80, 0, NULL, '0', '0', 1, '2025-12-15 10:18:46', '2025-12-15 10:18:46'),
(533, 10131, 127, 32, 50, 0, 126, '0', '0', 1, '2025-12-15 10:39:07', '2025-12-15 10:39:07'),
(534, 10131, 127, 0, 4, 0, NULL, '0', '0', 1, '2025-12-15 10:39:07', '2025-12-15 10:39:07'),
(535, 10132, 127, 0, 250, 116, NULL, '0', '0', 1, '2025-12-15 10:44:40', '2025-12-15 10:44:40'),
(538, 10133, 127, 33, 15, 114, 126, '0', '0', 1, '2025-12-15 12:43:28', '2025-12-15 12:43:28'),
(539, 10133, 127, 0, 5, 114, 0, '0', '0', 1, '2025-12-15 12:43:28', '2025-12-15 12:43:28'),
(540, 10134, 127, 32, 15, 0, 126, '0', '0', 1, '2025-12-15 12:44:32', '2025-12-15 12:44:32'),
(541, 10134, 127, 0, 3, 0, 126, '0', '0', 1, '2025-12-15 12:44:32', '2025-12-15 12:44:32'),
(542, 10135, 127, 0, 5, 0, 126, '0', '2500', 1, '2025-12-15 12:58:46', '2025-12-15 12:58:46'),
(543, 10135, 127, 0, 3, 0, 126, '0', '1500', 1, '2025-12-15 13:14:45', '2025-12-15 13:14:45'),
(544, 10136, 127, 0, 3, 0, 126, '0', '0', 1, '2025-12-15 13:20:06', '2025-12-15 13:20:06'),
(545, 10137, 127, 0, 2, 0, 126, '0', '0', 1, '2025-12-15 13:20:41', '2025-12-15 13:20:41'),
(549, 10140, 124, 32, 15, 0, NULL, '0', '0', 1, '2025-12-15 14:35:23', '2025-12-15 14:35:23'),
(550, 10141, 124, 0, 150, 116, NULL, '84', '150', 1, '2025-12-15 14:38:46', '2025-12-15 14:38:46'),
(551, 0, 0, 32, 20, 0, NULL, '0', '0', 1, '2025-12-16 04:17:15', '2025-12-16 04:17:15'),
(552, 1, 0, 37, 450, 0, NULL, '0', '0', 1, '2025-12-16 09:55:23', '2025-12-16 09:55:23'),
(553, 10142, 127, 0, 150, 116, 0, '0', '0', 1, '2025-12-16 10:40:50', '2025-12-16 10:40:50'),
(554, 10143, 127, 33, 25, 115, NULL, '0', '0', 1, '2025-12-16 10:52:00', '2025-12-16 10:52:00'),
(555, 10144, 127, 0, 10, 116, 0, '0', '0', 1, '2025-12-16 12:42:11', '2025-12-16 12:42:11'),
(556, 10145, 127, 32, 10, 115, 0, '0', '0', 1, '2025-12-17 02:24:32', '2025-12-17 02:24:32'),
(559, 10147, 124, 0, 17, 116, NULL, '0', '0', 1, '2025-12-18 04:34:39', '2025-12-18 04:34:39'),
(560, 10147, 127, 0, 18, 116, NULL, '0', '0', 1, '2025-12-18 04:34:39', '2025-12-18 04:34:39'),
(561, 1, 127, 0, 150, 115, NULL, '0', '0', 1, '2025-12-20 02:36:22', '2025-12-20 02:36:22'),
(562, 1, 127, 0, 15, 114, NULL, '0', '0', 1, '2025-12-20 02:36:22', '2025-12-20 02:36:22'),
(563, 1, 126, 0, 250, 114, NULL, '0', '0', 1, '2025-12-20 02:39:08', '2025-12-20 02:39:08'),
(564, 1, 126, 0, 535, 116, NULL, '0', '0', 1, '2025-12-20 02:39:08', '2025-12-20 02:39:08'),
(565, 1, 124, 0, 155, 115, NULL, '0', '0', 1, '2025-12-20 02:39:08', '2025-12-20 02:39:08'),
(566, 10148, 124, 0, 10, 116, NULL, '0', '0', 1, '2025-12-21 14:01:40', '2025-12-21 14:01:40'),
(567, 10148, 126, 0, 12, 114, NULL, '0', '0', 1, '2025-12-21 14:01:40', '2025-12-21 14:01:40'),
(568, 10148, 127, 0, 14, 116, NULL, '0', '0', 1, '2025-12-21 14:01:40', '2025-12-21 14:01:40'),
(569, 10149, 127, 0, 5, 114, 0, '0', '0', 1, '2025-12-21 14:18:07', '2025-12-21 14:18:07'),
(570, 10149, 127, 0, 5, 114, NULL, '0', '0', 1, '2025-12-21 14:18:38', '2025-12-21 14:18:38'),
(571, 10150, 126, 0, 25, 116, NULL, '0', '0', 1, '2025-12-25 14:28:48', '2025-12-25 14:28:48'),
(572, 10151, 125, 32, 40, 114, 0, '0', '0', 1, '2025-12-27 02:46:47', '2025-12-27 02:46:47'),
(573, 10151, 125, 33, 40, 114, 0, '0', '0', 1, '2025-12-27 02:46:47', '2025-12-27 02:46:47'),
(574, 10152, 125, 0, 20, 116, 0, '0', '0', 1, '2025-12-27 02:47:28', '2025-12-27 02:47:28'),
(575, 10153, 124, 0, 50, 116, NULL, '0', '0', 1, '2025-12-27 03:05:18', '2025-12-27 03:05:18'),
(576, 10153, 125, 0, 15, 116, NULL, '0', '0', 1, '2025-12-27 03:05:18', '2025-12-27 03:05:18'),
(577, 10154, 124, 0, 10, 116, NULL, '0', '0', 1, '2025-12-31 07:28:54', '2025-12-31 07:28:54'),
(578, 10155, 126, 0, 5, 116, NULL, '0', '0', 1, '2026-01-01 12:20:33', '2026-01-01 12:20:33'),
(579, 10156, 124, 0, 15, 116, NULL, '0', '0', 1, '2026-01-15 03:21:23', '2026-01-15 03:21:23'),
(580, 10156, 125, 0, 15, 116, NULL, '0', '0', 1, '2026-01-15 03:21:23', '2026-01-15 03:21:23'),
(581, 10159, 124, 0, 50, 116, NULL, '0', '0', 1, '2026-01-15 04:05:46', '2026-01-15 04:05:46'),
(582, 10159, 125, 0, 50, 116, NULL, '0', '0', 1, '2026-01-15 04:05:46', '2026-01-15 04:05:46'),
(583, 10160, 124, 0, 5, 116, NULL, '0', '0', 1, '2026-01-15 04:13:58', '2026-01-15 04:13:58'),
(584, 10160, 125, 0, 5, 116, NULL, '0', '0', 1, '2026-01-15 04:13:58', '2026-01-15 04:13:58'),
(585, 1, 128, 0, 250, 114, NULL, '0', '0', 1, '2026-02-26 05:23:12', '2026-02-26 05:23:12'),
(586, 1, 128, 0, 150, 115, NULL, '0', '0', 1, '2026-02-26 05:23:12', '2026-02-26 05:23:12'),
(587, 1, 0, 38, 500, 0, NULL, '0', '0', 1, '2026-02-28 05:16:12', '2026-02-28 05:16:12'),
(588, 1, 0, 39, 250, 0, NULL, '0', '0', 1, '2026-02-28 05:16:44', '2026-02-28 05:16:44'),
(589, 10161, 131, 38, 225, 122, 0, '0', '0', 1, '2026-02-28 06:04:56', '2026-02-28 06:04:56');

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
  `debit` decimal(15,2) DEFAULT NULL,
  `credit` decimal(15,2) DEFAULT NULL,
  `cc_amount` decimal(15,2) DEFAULT NULL COMMENT 'Customer currency amount (for dual-currency payments)',
  `fb_charges` decimal(15,2) DEFAULT NULL COMMENT 'Foreign bank charges',
  `db_charges` decimal(15,2) DEFAULT NULL COMMENT 'Domestic bank charges (in local currency)',
  `ledger_flag` tinyint(4) NOT NULL DEFAULT 1 COMMENT '1=affects cash ledger, 0=accounting entry only (general voucher)',
  `transaction_date` date NOT NULL,
  `description` longtext DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`transaction_id`, `transaction_to`, `transaction_type`, `bank_id`, `order_id`, `payee_id`, `payee_bank_id`, `debit`, `credit`, `cc_amount`, `fb_charges`, `db_charges`, `ledger_flag`, `transaction_date`, `description`, `created_by`, `created_at`, `updated_at`) VALUES
(220, 'vendor', 'openingBalance', 0, NULL, 19, 0, NULL, 25000.00, NULL, NULL, NULL, 1, '2025-12-15', NULL, 1, '2025-12-13 03:16:42', '2025-12-15 13:39:35'),
(221, 'customer', 'openingBalance', 0, NULL, 9, 0, 15474.00, NULL, NULL, NULL, NULL, 1, '2025-12-15', NULL, 1, '2025-12-13 03:29:48', '2025-12-15 14:15:45'),
(222, 'employee', 'openingBalance', 0, NULL, 15, 0, NULL, 17000.00, NULL, NULL, NULL, 1, '2025-12-16', NULL, 1, '2025-12-13 03:32:11', '2025-12-16 02:30:46'),
(223, 'vendor', 'openingBalance', 0, NULL, 20, 0, NULL, 25000.00, NULL, NULL, NULL, 1, '2025-12-15', NULL, 1, '2025-12-13 03:32:51', '2025-12-15 14:28:28'),
(224, 'employee', 'openingBalance', 0, NULL, 16, 0, 95000.00, NULL, NULL, NULL, NULL, 1, '2025-12-19', NULL, 1, '2025-12-13 03:35:16', '2025-12-19 13:42:59'),
(225, 'brs', 'brs', 0, NULL, NULL, 0, 1500000.00, NULL, NULL, NULL, NULL, 1, '2025-12-15', NULL, 1, '2025-12-15 13:30:34', '2025-12-15 13:30:34'),
(226, 'vendor', 'payment', 1, 92, 19, 0, NULL, 75000.00, NULL, NULL, NULL, 1, '2025-12-15', '<p>This is cheque no</p>', 1, '2025-12-15 13:31:03', '2025-12-29 15:47:55'),
(227, 'customer', 'orderPayment', 0, 46, 9, 0, 980000.00, NULL, 7584.00, 1575.00, NULL, 1, '2025-12-16', NULL, 1, '2025-12-15 14:09:53', '2025-12-15 14:09:53'),
(228, 'employee', 'advance', 0, NULL, 15, 0, 0.00, 3000.00, NULL, NULL, NULL, 1, '2025-12-16', NULL, 1, '2025-12-16 02:39:17', '2025-12-16 02:39:17'),
(231, 'employee', 'salary', 0, NULL, 15, 0, 0.00, 25000.00, NULL, NULL, NULL, 1, '2025-12-16', 'Monthly Salary - December 2025', 1, '2025-12-16 03:22:50', '2025-12-16 03:22:50'),
(232, 'employee', 'receiveAdvance', 0, NULL, 15, 0, 15000.00, NULL, NULL, NULL, NULL, 1, '2025-12-16', 'Loan Repayment - December 2025', 1, '2025-12-16 03:22:50', '2025-12-16 03:22:50'),
(233, 'vendor', 'openingBalance', 0, NULL, 21, 0, NULL, 0.00, NULL, NULL, NULL, 1, '2025-12-17', NULL, 1, '2025-12-17 02:42:27', '2025-12-17 02:44:22'),
(234, 'expense', 'expense', 0, NULL, 41, 0, NULL, 500.00, NULL, NULL, NULL, 1, '2025-12-17', NULL, 1, '2025-12-17 11:15:19', '2025-12-17 11:15:19'),
(235, 'vendor', 'generalVoucher', 0, NULL, 19, 0, NULL, 1500.00, NULL, NULL, NULL, 1, '2025-12-17', 'Repairing', 1, '2025-12-17 11:28:14', '2025-12-17 11:28:14'),
(236, 'vendor', 'generalVoucher', 0, NULL, 19, 0, 5500.00, NULL, NULL, NULL, NULL, 1, '2025-12-17', 'Repairing', 1, '2025-12-17 11:31:32', '2025-12-17 11:31:32'),
(237, 'expense', 'expense', 0, NULL, 38, 0, NULL, 1500.00, NULL, NULL, NULL, 1, '2025-12-20', NULL, 1, '2025-12-19 14:03:04', '2025-12-19 14:03:04'),
(238, 'expense', 'expense', 0, NULL, 53, 0, 1250.00, NULL, NULL, NULL, NULL, 1, '2025-12-20', NULL, 1, '2025-12-19 14:05:13', '2025-12-19 14:05:13'),
(239, 'transfer', 'transfer', 0, NULL, NULL, NULL, NULL, 1575.00, NULL, NULL, NULL, 1, '2025-12-22', 'Check No: 1245884', 1, '2025-12-21 14:11:54', '2025-12-21 14:11:54'),
(240, 'transfer', 'transfer', 1, NULL, NULL, NULL, 1575.00, NULL, NULL, NULL, NULL, 1, '2025-12-22', 'Check No: 1245884', 1, '2025-12-21 14:11:54', '2025-12-21 14:11:54'),
(241, 'customer', 'orderPayment', 0, 48, 9, 0, 85000.00, NULL, 1548.00, 2500.00, NULL, 1, '2025-12-24', NULL, 1, '2025-12-23 14:18:53', '2025-12-24 02:47:38'),
(242, 'customer', 'openingBalance', 0, NULL, 10, 0, 770.00, NULL, NULL, NULL, NULL, 1, '2025-12-31', NULL, 1, '2025-12-25 14:22:37', '2025-12-31 06:43:20'),
(243, 'customer', 'orderPayment', 0, 49, 10, 0, 154000.00, NULL, 475.00, 4500.00, NULL, 1, '2025-12-26', NULL, 1, '2025-12-25 14:31:03', '2025-12-25 14:46:43'),
(244, 'customer', 'orderPayment', 0, 50, 10, 0, 15000.00, NULL, 250.00, NULL, NULL, 1, '2025-12-26', NULL, 1, '2025-12-25 14:47:40', '2025-12-27 03:43:06'),
(245, 'vendor', 'payment', 0, 91, 21, 0, NULL, 20000.00, NULL, NULL, NULL, 1, '2025-12-27', NULL, 1, '2025-12-27 02:29:09', '2025-12-27 02:29:09'),
(246, 'vendor', 'payment', 1, 91, 21, 0, NULL, 1500.00, NULL, NULL, NULL, 1, '2025-12-27', 'This is check no or dec', 1, '2025-12-27 02:32:19', '2025-12-27 02:32:42'),
(247, 'customer', 'orderPayment', 1, 49, 10, 0, 35000.00, NULL, 125.00, NULL, NULL, 1, '2025-12-27', NULL, 1, '2025-12-27 03:40:31', '2025-12-27 03:40:31'),
(249, 'customer', 'generalVoucher', 0, NULL, 10, 0, 0.00, NULL, 4500.00, NULL, NULL, 0, '2025-12-29', '123', 1, '2025-12-29 03:12:29', '2025-12-29 03:12:29'),
(250, 'customer', 'generalVoucher', 0, NULL, 10, 0, NULL, 0.00, 100.00, NULL, NULL, 0, '2025-12-29', '123', 1, '2025-12-29 03:41:55', '2025-12-29 03:41:55'),
(251, 'customer', 'generalVoucher', 0, NULL, 10, 0, 0.00, NULL, 101.00, NULL, NULL, 0, '2025-12-29', '123', 1, '2025-12-29 03:42:16', '2025-12-29 03:42:16'),
(252, 'customer', 'generalVoucher', 0, NULL, 10, 0, 0.00, NULL, 150.00, NULL, NULL, 0, '2025-12-30', '1235', 1, '2025-12-30 02:57:18', '2025-12-30 02:57:18'),
(253, 'customer', 'generalVoucher', 0, NULL, 10, 0, 0.00, NULL, 475.00, NULL, NULL, 1, '2025-12-30', '123', 1, '2025-12-30 03:03:02', '2025-12-30 03:03:02'),
(254, 'customer', 'orderPayment', 9, 48, 9, 0, 270000.00, NULL, 10100.00, 100.00, 10000.00, 1, '2025-12-30', 'desc', 1, '2025-12-30 03:08:11', '2025-12-30 03:08:11'),
(255, 'employee', 'wages', 1, NULL, 16, 0, 0.00, 1000.00, NULL, NULL, NULL, 1, '2025-12-30', NULL, 1, '2025-12-30 03:11:19', '2025-12-30 03:11:19'),
(256, 'expense', 'expense', 1, NULL, 42, 0, NULL, 10000.00, NULL, NULL, NULL, 1, '2025-12-30', NULL, 1, '2025-12-30 03:16:36', '2025-12-30 03:16:36'),
(257, 'customer', 'generalVoucher', 0, NULL, 10, 0, NULL, 0.00, 100.00, NULL, NULL, 0, '2025-12-30', 'text', 1, '2025-12-30 13:26:45', '2025-12-30 13:26:45'),
(258, 'customer', 'orderPayment', 9, 50, 10, 0, 500000.00, NULL, 491.00, 11.00, 9500.00, 1, '2025-12-31', NULL, 1, '2025-12-30 14:55:59', '2025-12-30 14:58:21'),
(259, 'expense', 'bankCharges', 9, 258, 121, 0, NULL, 9500.00, NULL, NULL, NULL, 1, '2025-12-31', 'Bank charges for payment transaction #258', 1, '2025-12-30 14:55:59', '2025-12-30 14:58:21'),
(260, 'expense', 'expense', 0, NULL, 121, 0, NULL, 150.00, NULL, NULL, NULL, 1, '2025-12-31', 'check no 123', 1, '2025-12-31 06:41:03', '2025-12-31 06:41:03'),
(261, 'customer', 'orderPayment', 10, 48, 9, 0, 108000.00, NULL, 450.00, 50.00, 8000.00, 1, '2025-12-31', NULL, 1, '2025-12-31 07:02:41', '2025-12-31 07:04:30'),
(262, 'expense', 'bankCharges', 10, 261, 121, 0, NULL, 8000.00, NULL, NULL, NULL, 1, '2025-12-31', 'Bank charges for payment transaction #261', 1, '2025-12-31 07:04:30', '2025-12-31 07:04:30'),
(263, 'vendor', 'payment', 0, 94, 19, 0, NULL, 1500.00, NULL, NULL, NULL, 1, '2025-12-31', NULL, 1, '2025-12-31 07:09:42', '2025-12-31 07:09:42'),
(264, 'employee', 'openingBalance', 0, NULL, 17, 0, NULL, 0.00, NULL, NULL, NULL, 1, '2026-01-01', NULL, 1, '2026-01-01 13:57:26', '2026-01-01 13:57:26'),
(265, 'expense', 'expense', 1, NULL, 121, 0, NULL, 1000.00, NULL, NULL, NULL, 1, '2026-01-08', 'some desc', 1, '2026-01-08 02:10:14', '2026-01-08 02:19:17'),
(266, 'customer', 'openingBalance', 0, NULL, 11, 0, 250.00, NULL, NULL, NULL, NULL, 1, '2026-01-15', NULL, 1, '2026-01-15 03:13:45', '2026-01-15 03:13:45'),
(267, 'customer', 'orderPayment', 0, 51, 11, 0, 36400.00, NULL, 145.00, 15.00, 1400.00, 1, '2026-01-15', NULL, 1, '2026-01-15 03:23:13', '2026-01-15 03:23:13'),
(268, 'expense', 'bankCharges', 0, 267, 121, 0, NULL, 1400.00, NULL, NULL, NULL, 1, '2026-01-15', 'Bank charges for payment transaction #267', 1, '2026-01-15 03:23:13', '2026-01-15 03:23:13'),
(269, 'customer', 'generalVoucher', 0, NULL, 11, 0, 0.00, NULL, 75.00, NULL, NULL, 0, '2026-01-15', 'Take less 75', 1, '2026-01-15 03:24:33', '2026-01-15 03:24:33'),
(270, 'customer', 'openingBalance', 0, NULL, 12, 0, 450.00, NULL, NULL, NULL, NULL, 1, '2026-02-26', NULL, 1, '2026-02-26 05:07:48', '2026-02-26 05:07:48'),
(271, 'customer', 'openingBalance', 0, NULL, 13, 0, 0.00, NULL, NULL, NULL, NULL, 1, '2026-02-28', NULL, 1, '2026-02-28 05:51:07', '2026-02-28 05:51:07');

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
(1, 'Admin', '1', 'admin@gmail.com', NULL, '$2y$12$IsLNGwhB1PNBy6Lkxrtkx.ka9hfw2hEf/.qGrjPlN8EFnxkGIrcgW', 'budBIlcFNuu6hPc61PDbyMqveQHwrGmuwMBopZTrsiY96pvWDx8A170DR4pp', NULL, NULL),
(2, 'Manager', '2', 'manager@gmail.com', NULL, '$2y$12$iB5imGY9l.dbfpEYnZHGO.wl3fO/.zdj96rIjR/vJiYh7TVi33I6C', '8AgBGaBc9V2hc8R218743xvOz2lCm0IF20xHMHaWVUdScpCj97FhrA5eQwzv', '2024-06-04 01:49:44', '2025-01-12 01:51:16'),
(3, 'Accountant', '3', 'accountant@gmail.com', NULL, '$2y$12$eKMHyfJEB/21jluVhBl.ZuF2YY/h9AmxBDKyIlKO8YMiNFUM0Tfs2', NULL, '2025-01-12 01:46:41', '2025-01-12 01:59:10'),
(5, 'test', '9', 'test@admin.com', NULL, '$2y$12$8y5wXTDbwpQcCzNVGsAyDOFIdf0VbE82.3MrrBF.9B5evXlx8keXK', NULL, '2025-01-12 03:22:22', '2025-09-13 00:24:24');

-- --------------------------------------------------------

--
-- Table structure for table `vendors`
--

CREATE TABLE `vendors` (
  `vendor_id` bigint(20) UNSIGNED NOT NULL,
  `vendor_type_id` varchar(255) NOT NULL COMMENT 'HeadID',
  `vendor_no` varchar(255) NOT NULL,
  `vendor_type` bigint(20) UNSIGNED NOT NULL COMMENT '\r\nWorker / Not',
  `material_id` text NOT NULL,
  `product_id` text NOT NULL,
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

INSERT INTO `vendors` (`vendor_id`, `vendor_type_id`, `vendor_no`, `vendor_type`, `material_id`, `product_id`, `name`, `fname`, `cperson`, `phone1`, `phone2`, `city_id`, `address`, `description`, `created_by`, `created_at`, `updated_at`) VALUES
(19, '69', 'V0001', 0, '0', '0', 'Vendor #1', 'Vendor #1', '.', '.', '.', 70, '.', NULL, 1, '2025-12-13 03:16:42', '2025-12-13 03:16:42'),
(20, '', 'C0001', 1, '0', '0', 'Contractor #1', 'Contractor #1', NULL, '.', '.', 70, 'Address', NULL, 1, '2025-12-13 03:32:51', '2025-12-13 03:32:51'),
(21, '58', 'V0002', 0, '37|36', '44|43', 'Vendor #2', 'Vendor #2', '.', '.', '.', 70, '.', NULL, 1, '2025-12-17 02:42:27', '2025-12-17 02:44:22');

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
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`);

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
-- Indexes for table `delivery_returns`
--
ALTER TABLE `delivery_returns`
  ADD PRIMARY KEY (`delivery_return_id`),
  ADD KEY `delivery_returns_delivery_id_foreign` (`delivery_id`),
  ADD KEY `delivery_returns_created_by_foreign` (`created_by`);

--
-- Indexes for table `delivery_return_items`
--
ALTER TABLE `delivery_return_items`
  ADD PRIMARY KEY (`delivery_return_item_id`),
  ADD KEY `delivery_return_items_delivery_return_id_foreign` (`delivery_return_id`),
  ADD KEY `delivery_return_items_stock_item_id_foreign` (`stock_item_id`),
  ADD KEY `delivery_return_items_created_by_foreign` (`created_by`);

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
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

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
-- Indexes for table `packing_cartons`
--
ALTER TABLE `packing_cartons`
  ADD PRIMARY KEY (`packing_carton_id`),
  ADD KEY `packing_cartons_packing_list_id_foreign` (`packing_list_id`),
  ADD KEY `packing_cartons_created_by_foreign` (`created_by`);

--
-- Indexes for table `packing_carton_items`
--
ALTER TABLE `packing_carton_items`
  ADD PRIMARY KEY (`packing_carton_item_id`),
  ADD KEY `packing_carton_items_packing_carton_id_foreign` (`packing_carton_id`),
  ADD KEY `packing_carton_items_product_id_foreign` (`product_id`),
  ADD KEY `packing_carton_items_created_by_foreign` (`created_by`);

--
-- Indexes for table `packing_lists`
--
ALTER TABLE `packing_lists`
  ADD PRIMARY KEY (`packing_list_id`),
  ADD UNIQUE KEY `packing_lists_delivery_id_unique` (`delivery_id`),
  ADD KEY `packing_lists_order_id_foreign` (`order_id`),
  ADD KEY `packing_lists_created_by_foreign` (`created_by`);

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
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

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
  MODIFY `bank_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `company`
--
ALTER TABLE `company`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `customer_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `deliveries`
--
ALTER TABLE `deliveries`
  MODIFY `delivery_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `delivery_boxes`
--
ALTER TABLE `delivery_boxes`
  MODIFY `dbox_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `delivery_returns`
--
ALTER TABLE `delivery_returns`
  MODIFY `delivery_return_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `delivery_return_items`
--
ALTER TABLE `delivery_return_items`
  MODIFY `delivery_return_item_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `employee_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `heads`
--
ALTER TABLE `heads`
  MODIFY `head_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=126;

--
-- AUTO_INCREMENT for table `head_types`
--
ALTER TABLE `head_types`
  MODIFY `head_type_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `igroups`
--
ALTER TABLE `igroups`
  MODIFY `igroup_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `igroup_items`
--
ALTER TABLE `igroup_items`
  MODIFY `igroup_item_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `images`
--
ALTER TABLE `images`
  MODIFY `image_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=148;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `machines`
--
ALTER TABLE `machines`
  MODIFY `machine_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `materials`
--
ALTER TABLE `materials`
  MODIFY `material_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT for table `mprocess`
--
ALTER TABLE `mprocess`
  MODIFY `mprocess_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `order_item_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=136;

--
-- AUTO_INCREMENT for table `packing_cartons`
--
ALTER TABLE `packing_cartons`
  MODIFY `packing_carton_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `packing_carton_items`
--
ALTER TABLE `packing_carton_items`
  MODIFY `packing_carton_item_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `packing_lists`
--
ALTER TABLE `packing_lists`
  MODIFY `packing_list_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=96;

--
-- AUTO_INCREMENT for table `permission_role`
--
ALTER TABLE `permission_role`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1664;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` bigint(255) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT for table `product_costs`
--
ALTER TABLE `product_costs`
  MODIFY `product_cost_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=118;

--
-- AUTO_INCREMENT for table `product_materials`
--
ALTER TABLE `product_materials`
  MODIFY `product_material_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=605;

--
-- AUTO_INCREMENT for table `product_types`
--
ALTER TABLE `product_types`
  MODIFY `product_type_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=132;

--
-- AUTO_INCREMENT for table `purchases`
--
ALTER TABLE `purchases`
  MODIFY `purchase_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=97;

--
-- AUTO_INCREMENT for table `purchase_items`
--
ALTER TABLE `purchase_items`
  MODIFY `purchase_item_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `receives`
--
ALTER TABLE `receives`
  MODIFY `receive_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `receive_materials`
--
ALTER TABLE `receive_materials`
  MODIFY `receive_material_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `returns`
--
ALTER TABLE `returns`
  MODIFY `return_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT for table `return_materials`
--
ALTER TABLE `return_materials`
  MODIFY `return_material_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `salaries`
--
ALTER TABLE `salaries`
  MODIFY `salary_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `stocks`
--
ALTER TABLE `stocks`
  MODIFY `stock_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10162;

--
-- AUTO_INCREMENT for table `stock_items`
--
ALTER TABLE `stock_items`
  MODIFY `stock_item_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=590;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `transaction_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=272;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `vendors`
--
ALTER TABLE `vendors`
  MODIFY `vendor_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

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

--
-- Constraints for dumped tables
--

--
-- Constraints for table `delivery_returns`
--
ALTER TABLE `delivery_returns`
  ADD CONSTRAINT `delivery_returns_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `delivery_returns_delivery_id_foreign` FOREIGN KEY (`delivery_id`) REFERENCES `deliveries` (`delivery_id`) ON DELETE CASCADE;

--
-- Constraints for table `delivery_return_items`
--
ALTER TABLE `delivery_return_items`
  ADD CONSTRAINT `delivery_return_items_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `delivery_return_items_delivery_return_id_foreign` FOREIGN KEY (`delivery_return_id`) REFERENCES `delivery_returns` (`delivery_return_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `delivery_return_items_stock_item_id_foreign` FOREIGN KEY (`stock_item_id`) REFERENCES `stock_items` (`stock_item_id`) ON DELETE CASCADE;

--
-- Constraints for table `packing_cartons`
--
ALTER TABLE `packing_cartons`
  ADD CONSTRAINT `packing_cartons_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `packing_cartons_packing_list_id_foreign` FOREIGN KEY (`packing_list_id`) REFERENCES `packing_lists` (`packing_list_id`) ON DELETE CASCADE;

--
-- Constraints for table `packing_carton_items`
--
ALTER TABLE `packing_carton_items`
  ADD CONSTRAINT `packing_carton_items_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `packing_carton_items_packing_carton_id_foreign` FOREIGN KEY (`packing_carton_id`) REFERENCES `packing_cartons` (`packing_carton_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `packing_carton_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE;

--
-- Constraints for table `packing_lists`
--
ALTER TABLE `packing_lists`
  ADD CONSTRAINT `packing_lists_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `packing_lists_delivery_id_foreign` FOREIGN KEY (`delivery_id`) REFERENCES `deliveries` (`delivery_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `packing_lists_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
