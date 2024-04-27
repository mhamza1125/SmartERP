-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 27, 2024 at 12:57 PM
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
(8, 'customer', 5, 37, 'Samad Ali', '06710109744123', 1, '2024-04-19 08:32:47', '2024-04-19 08:32:47');

-- --------------------------------------------------------

--
-- Table structure for table `boxes`
--

CREATE TABLE `boxes` (
  `box_id` bigint(20) UNSIGNED NOT NULL,
  `head_id` bigint(20) UNSIGNED NOT NULL COMMENT 'Box Material',
  `vendor_id` bigint(20) UNSIGNED NOT NULL,
  `box_no` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `length` double UNSIGNED NOT NULL,
  `width` double UNSIGNED NOT NULL,
  `height` double UNSIGNED NOT NULL,
  `weight` double UNSIGNED NOT NULL COMMENT 'Grams',
  `box_status` bigint(20) UNSIGNED NOT NULL DEFAULT 1,
  `description` longtext DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `boxes`
--

INSERT INTO `boxes` (`box_id`, `head_id`, `vendor_id`, `box_no`, `name`, `length`, `width`, `height`, `weight`, `box_status`, `description`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 75, 4, '#01', 'Box 01', 10, 10, 20, 10, 1, '<p>Desc</p>', 1, '2024-03-18 09:41:32', '2024-04-25 06:00:08'),
(2, 75, 3, '#02', 'Box 02', 10, 10, 20, 10, 1, '<p>Desc</p>', 1, '2024-03-18 09:41:32', '2024-03-18 13:34:18');

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
(1, 'C101', 85, 92, 'Faizan', 'Pervaiz', 'faizan@gmail.com', '03000000000', '03000000000', 'Lahore Trunk Road', '<div style=\"line-height: 19px;\">Lorem ipsum dolor sit amet consectetur adipisicing elit. Sed eaque iure officiis nobis modi inventore consequuntur rem dolorum, obcaecati nihil delectus similique incidunt exercitationem quae soluta reprehenderit provident magnam blanditiis.</div>', 1, '2024-02-18 04:53:35', '2024-03-30 13:10:46'),
(2, 'C-201', 85, 92, 'Afraz', 'Anwar', 'afraz@gmail.com', '03000000000', '0300 1122334', 'Address of Anwar', NULL, 1, '2024-02-25 13:34:35', '2024-02-25 13:34:35'),
(3, 'C-301', 85, 92, 'Moazzam', 'Abdullah', 'moazzam@gmail.com', '03000000000', '0300 1122334', 'Address of Moazzam', NULL, 1, '2024-02-25 13:35:01', '2024-02-25 13:35:01'),
(5, 'C-401', 85, 92, 'Samad', 'Ali', 'samad@gmail.com', '03000000000', '0300 1122334', 'Address of Samad Ali', '<div style=\"line-height: 19px;\">Lorem ipsum dolor sit amet consectetur adipisicing elit. Totam quo, sunt itaque veniam inventore corrupti amet illum? Vero distinctio commodi autem, aperiam unde dolores doloribus corporis natus quod ex ipsam.</div>', 1, '2024-02-25 13:43:49', '2024-02-25 13:49:44'),
(6, 'C24005', 85, 92, 'Muhammad', 'Hamza', 'hamza@gmail.com', '03000000000', '0300 1122334', NULL, NULL, 1, '2024-03-30 13:02:17', '2024-03-30 13:02:17');

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

CREATE TABLE `employees` (
  `employee_id` bigint(20) UNSIGNED NOT NULL,
  `employee_no` varchar(255) NOT NULL,
  `department_id` bigint(20) UNSIGNED NOT NULL COMMENT 'HeadID',
  `employee_type_id` bigint(20) UNSIGNED NOT NULL COMMENT 'HeadID',
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

INSERT INTO `employees` (`employee_id`, `employee_no`, `department_id`, `employee_type_id`, `name`, `fname`, `sname`, `cnic`, `phone1`, `phone2`, `city_id`, `address`, `salary`, `description`, `joining_date`, `employee_status`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'E24001', 6, 39, 'Zohaib Khalid', 'Khalid', 'Zohaib', '3460389902345', '03001122334', NULL, 44, 'Address of Zohaib', 32000, '<p><span style=\"font-weight: bolder;\">Employee form Lahore, \"</span>Placed in Admin Department<span style=\"font-weight: bolder;\">\"</span><br></p>', '2024-02-18', 1, 1, '2024-02-18 13:04:22', '2024-04-20 04:46:27'),
(5, 'E24002', 7, 39, 'Bashir Malik', 'Manoor', 'Basihr', '3460389902345', '03001122334', '03001122334', 43, 'Address of Bashir', 35000, '<div style=\"line-height: 19px;\">Lorem ipsum dolor sit amet consectetur adipisicing elit. Quia, voluptatibus. Laudantium corporis animi assumenda reprehenderit ipsum velit reiciendis nostrum esse id quod quisquam quasi veniam vel aliquid officia, voluptate debitis.</div>', '2024-02-25', 1, 1, '2024-02-25 13:56:59', '2024-04-20 07:56:56'),
(6, 'E24003', 8, 40, 'Adil Nawaz', 'Adil Nawaz', 'Adil', '3460389902345', '03001122334', '03001122334', 44, 'Address of Adil Nawaz', 0, '<div style=\"line-height: 19px;\">Lorem, ipsum dolor sit amet consectetur adipisicing elit. Obcaecati minima corporis quae, odit officiis facere labore autem beatae! Reprehenderit quasi corrupti ullam enim quia vitae suscipit, asperiores aut delectus possimus!</div>', '2024-02-25', 1, 1, '2024-02-25 14:02:20', '2024-02-25 14:02:20'),
(7, 'E24004', 9, 39, 'Haider Ali', 'Abdullah', 'Haider', '3460389902345', '03001122334', '03001122334', 45, 'Address of Haider', 20000, '<div style=\"line-height: 19px;\">Lorem, ipsum dolor sit amet consectetur adipisicing elit. Obcaecati minima corporis quae, odit officiis facere labore autem beatae! Reprehenderit quasi corrupti ullam enim quia vitae suscipit, asperiores aut delectus possimus!</div>', '2024-02-26', 1, 1, '2024-02-25 14:03:32', '2024-04-20 07:54:31'),
(8, 'E24005', 9, 39, 'Uzair Aslam', 'Aslam', 'Uzair', '3460389902345', '03001122334', '03001122334', 46, 'Address of Uzair', 16000, '<p>Desc</p>', '2024-04-20', 1, 1, '2024-04-20 04:54:15', '2024-04-20 07:51:49');

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
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `heads`
--

INSERT INTO `heads` (`head_id`, `head_type_id`, `name`, `head_status`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 1, 'S', 1, 1, '2024-02-16 15:42:10', '2024-02-16 15:42:10'),
(2, 1, 'M', 1, 1, '2024-02-16 15:48:44', '2024-02-16 15:48:44'),
(3, 1, 'L', 1, 1, '2024-02-17 00:35:01', '2024-02-17 00:35:01'),
(4, 1, 'XL', 1, 1, '2024-02-17 00:35:31', '2024-02-17 00:35:31'),
(5, 1, 'XXL', 1, 1, '2024-02-17 00:35:40', '2024-02-17 00:35:40'),
(6, 3, 'Admin', 1, 1, '2024-02-17 00:38:59', '2024-02-17 00:38:59'),
(7, 3, 'Cutting', 1, 1, '2024-02-17 00:40:17', '2024-02-17 00:40:17'),
(8, 3, 'Stitching', 1, 1, '2024-02-17 00:44:53', '2024-02-17 00:44:53'),
(9, 3, 'Quality Checking', 1, 1, '2024-02-17 00:46:03', '2024-02-17 00:46:03'),
(10, 3, 'Ironing', 1, 1, '2024-02-17 00:48:36', '2024-02-17 00:48:36'),
(24, 2, 'Blue', 1, 1, '2024-02-17 13:46:44', '2024-02-17 13:46:44'),
(32, 6, 'HBL Bank', 1, 1, '2024-02-18 09:05:28', '2024-02-18 09:05:28'),
(34, 5, 'Store Main', 1, 1, '2024-02-18 09:05:31', '2024-02-18 09:05:31'),
(35, 6, 'UBL Bank', 1, 1, '2024-02-18 09:05:38', '2024-02-18 09:05:38'),
(36, 6, 'Allied Bank', 1, 1, '2024-02-18 09:05:46', '2024-02-18 09:05:46'),
(37, 6, 'MCB Bank', 1, 1, '2024-02-18 09:05:51', '2024-02-18 09:05:51'),
(38, 7, 'Miscelenious', 1, 1, '2024-02-18 09:06:05', '2024-02-18 09:06:05'),
(39, 9, 'Salary', 1, 1, '2024-02-18 09:06:12', '2024-02-18 09:06:12'),
(40, 9, 'Wages', 1, 1, '2024-02-18 09:06:19', '2024-02-18 09:06:19'),
(41, 7, 'Vehicle Rent', 1, 1, '2024-02-18 09:08:28', '2024-02-18 09:08:28'),
(42, 7, 'Electric Expense', 1, 1, '2024-02-18 09:08:45', '2024-02-18 09:08:45'),
(43, 8, 'Sialkot', 1, 1, '2024-02-18 12:40:56', '2024-02-18 12:40:56'),
(44, 8, 'Lahore', 1, 1, '2024-02-18 12:41:05', '2024-02-18 12:41:05'),
(45, 8, 'Kingra', 1, 1, '2024-02-18 12:41:11', '2024-02-18 12:41:11'),
(46, 8, 'Pasrur', 1, 1, '2024-02-18 12:41:24', '2024-02-18 12:41:24'),
(47, 4, 'Pairs', 1, 1, '2024-02-19 07:21:21', '2024-02-19 07:21:21'),
(48, 4, 'Pieces', 1, 1, '2024-02-19 07:21:26', '2024-02-19 07:21:26'),
(49, 4, 'Feets', 1, 1, '2024-02-19 07:21:29', '2024-02-19 07:21:29'),
(50, 4, 'Meters', 1, 1, '2024-02-19 07:21:34', '2024-02-19 07:21:34'),
(51, 4, 'Liters', 1, 1, '2024-02-19 07:23:12', '2024-02-19 07:23:12'),
(52, 4, 'Boxes', 1, 1, '2024-02-19 07:23:26', '2024-02-19 07:23:26'),
(53, 7, 'Services', 1, 1, '2024-02-19 07:24:09', '2024-02-19 07:24:09'),
(54, 10, 'Leather', 1, 1, '2024-02-19 07:24:51', '2024-02-19 07:24:51'),
(55, 10, 'PU Leather', 1, 1, '2024-02-19 07:24:58', '2024-02-19 07:24:58'),
(56, 10, 'Zip', 1, 1, '2024-02-19 07:25:03', '2024-02-19 07:25:03'),
(57, 11, 'Leather Vendor', 1, 1, '2024-02-20 07:14:29', '2024-02-20 07:14:29'),
(58, 11, 'Zip Vendor', 1, 1, '2024-02-20 07:14:34', '2024-02-20 07:14:34'),
(59, 11, 'Latex Solution Vendor', 1, 1, '2024-02-20 07:14:53', '2024-02-20 07:14:53'),
(60, 11, 'Thread Vendor', 1, 1, '2024-02-20 07:15:06', '2024-02-20 07:15:06'),
(61, 10, 'Boxes', 1, 1, '2024-02-24 11:51:44', '2024-02-24 11:51:44'),
(62, 10, 'Thread', 1, 1, '2024-02-25 13:22:16', '2024-02-25 13:22:16'),
(63, 10, 'Liquid Items', 1, 1, '2024-02-25 13:22:26', '2024-02-25 13:22:26'),
(64, 10, 'Rubber Items', 1, 1, '2024-02-25 13:22:41', '2024-02-25 13:22:41'),
(65, 10, 'Cloths', 1, 1, '2024-02-25 13:23:03', '2024-02-25 13:23:03'),
(66, 10, 'Other Items', 1, 1, '2024-02-25 13:25:04', '2024-02-25 13:25:04'),
(67, 11, 'Boxes', 1, 1, '2024-02-25 14:07:27', '2024-02-25 14:07:27'),
(68, 11, 'Liquid Vendor', 1, 1, '2024-02-25 14:07:55', '2024-02-25 14:07:55'),
(69, 11, 'Other', 1, 1, '2024-02-25 14:08:08', '2024-02-25 14:08:08'),
(70, 8, 'Islamabad', 1, 1, '2024-02-26 00:39:51', '2024-02-26 00:39:51'),
(72, 12, 'Cutting', 1, 1, '2024-03-03 13:05:00', '2024-03-03 13:05:00'),
(73, 12, 'Stitched', 1, 1, '2024-03-03 13:05:15', '2024-03-03 13:05:15'),
(74, 12, 'Finished', 1, 1, '2024-03-03 13:05:20', '2024-03-03 13:05:20'),
(75, 13, 'Cardboard Box', 1, 1, '2024-03-18 03:11:07', '2024-03-18 03:11:07'),
(76, 13, 'Plastic Box', 1, 1, '2024-03-18 03:11:23', '2024-03-18 03:11:23'),
(77, 13, 'Wooden Box', 1, 1, '2024-03-18 03:11:38', '2024-03-18 03:11:38'),
(78, 14, 'Cutting', 1, 1, '2024-03-19 12:58:13', '2024-03-19 12:58:13'),
(79, 14, 'Fingers Stitching', 1, 1, '2024-03-19 12:58:25', '2024-03-19 12:58:25'),
(80, 14, 'Palm Stiching', 1, 1, '2024-03-19 12:58:46', '2024-03-19 12:58:46'),
(81, 14, 'Lining', 1, 1, '2024-03-19 12:59:56', '2024-03-19 12:59:56'),
(82, 14, 'Adding Grips', 1, 1, '2024-03-19 13:00:08', '2024-03-19 13:00:08'),
(83, 14, 'Fitting & Adjustments', 1, 1, '2024-03-19 13:00:28', '2024-03-19 13:00:28'),
(84, 14, 'Complete Cost', 1, 1, '2024-03-19 13:00:44', '2024-03-19 13:00:44'),
(85, 15, 'Pakistan', 1, 1, '2024-03-30 12:49:41', '2024-03-30 12:49:41'),
(86, 15, 'United States', 1, 1, '2024-03-30 12:50:24', '2024-03-30 12:50:24'),
(87, 15, 'Canada', 1, 1, '2024-03-30 12:50:37', '2024-03-30 12:50:37'),
(88, 15, 'Japan', 1, 1, '2024-03-30 12:50:45', '2024-03-30 12:50:45'),
(89, 15, 'Germany', 1, 1, '2024-03-30 12:50:49', '2024-03-30 12:50:49'),
(90, 15, 'France', 1, 1, '2024-03-30 12:50:53', '2024-03-30 12:50:53'),
(91, 15, 'China', 1, 1, '2024-03-30 12:50:56', '2024-03-30 12:50:56'),
(92, 16, 'Pakistani Rupee', 1, 1, '2024-03-30 12:51:51', '2024-03-30 12:51:51'),
(93, 16, 'Dollar', 1, 1, '2024-03-30 12:52:11', '2024-03-30 12:52:11'),
(94, 16, 'Denaar', 1, 1, '2024-03-30 12:52:16', '2024-03-30 12:52:16'),
(95, 16, 'Riyaal', 1, 1, '2024-03-30 12:52:22', '2024-03-30 12:52:22');

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
(13, 'Box Material', 'Cardboard, Wooden, Iron etc', '2024-03-18 07:55:01', '2024-03-18 07:55:01'),
(14, 'Product Costing', 'Cutting, Stitching, Ironing etc', '2024-03-19 17:57:16', '2024-03-19 17:57:16'),
(15, 'Country / State', 'Pakistan, Canada, England etc', '2024-03-30 17:49:21', '2024-03-30 17:49:21'),
(16, 'Currency', 'Pkr, Dollor, Riyal etc', '2024-03-30 17:49:21', '2024-03-30 17:49:21');

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
(138, 'products', 24, 'Admin - SmartERP_1714063441.xlsx', 'Title', '1', 1, '2024-04-25 11:44:01', '2024-04-25 11:44:01');

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
  `description` longtext DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `materials`
--

INSERT INTO `materials` (`material_id`, `material_no`, `material_type_id`, `vendor_id`, `name`, `unit_id`, `description`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'L-5010', 54, 4, 'Black Sheep Leather', 49, '<div style=\"line-height: 19px;\">Lorem ipsum dolor sit, amet consectetur adipisicing elit. Temporibus vel rem quis ratione suscipit reiciendis possimus mollitia laboriosam, iste cumque, eaque provident totam quaerat minima nulla nisi vero delectus quia.</div>', 1, '2024-02-19 11:57:34', '2024-04-25 03:50:00'),
(2, 'Zip 101', 56, 5, 'Zip', 50, NULL, 1, '2024-02-19 12:04:39', '2024-04-25 03:49:54'),
(3, 'L-5020', 54, 6, 'Cow Leather', 49, '<div style=\"line-height: 19px;\">Lorem ipsum dolor sit, amet consectetur adipisicing elit. Temporibus vel rem quis ratione suscipit reiciendis possimus mollitia laboriosam, iste cumque, eaque provident totam quaerat minima nulla nisi vero delectus quia.</div>', 1, '2024-02-25 13:09:33', '2024-04-25 03:49:49'),
(4, 'PU-6010', 55, 1, 'Crocodile Leather', 49, '<div style=\"line-height: 19px;\">Lorem ipsum dolor sit, amet consectetur adipisicing elit. Temporibus vel rem quis ratione suscipit reiciendis possimus mollitia laboriosam, iste cumque, eaque provident totam quaerat minima nulla nisi vero delectus quia.</div>', 1, '2024-02-25 13:10:06', '2024-02-25 13:10:06'),
(5, 'PU-6060', 55, 3, 'Printed Black PU Leather', 49, '<div style=\"line-height: 19px;\">Lorem ipsum dolor sit, amet consectetur adipisicing elit. Temporibus vel rem quis ratione suscipit reiciendis possimus mollitia laboriosam, iste cumque, eaque provident totam quaerat minima nulla nisi vero delectus quia.</div>', 1, '2024-02-25 13:10:31', '2024-04-25 03:49:42'),
(6, 'Z-3001', 56, 4, 'Double sided A Quality Zip', 50, '<div style=\"line-height: 19px;\">Lorem ipsum dolor sit, amet consectetur adipisicing elit. Temporibus vel rem quis ratione suscipit reiciendis possimus mollitia laboriosam, iste cumque, eaque provident totam quaerat minima nulla nisi vero delectus quia.</div>', 1, '2024-02-25 13:11:20', '2024-04-25 03:49:37'),
(7, 'Z-3015', 56, 5, 'Single Sided Zip', 50, '<div style=\"line-height: 19px;\">Lorem ipsum dolor sit, amet consectetur adipisicing elit. Temporibus vel rem quis ratione suscipit reiciendis possimus mollitia laboriosam, iste cumque, eaque provident totam quaerat minima nulla nisi vero delectus quia.</div>', 1, '2024-02-25 13:11:37', '2024-04-25 03:49:31'),
(8, 'B-101', 61, 6, 'Box #1 5KG', 48, '<div style=\"line-height: 19px;\">Lorem ipsum dolor sit, amet consectetur adipisicing elit. Temporibus vel rem quis ratione suscipit reiciendis possimus mollitia laboriosam, iste cumque, eaque provident totam quaerat minima nulla nisi vero delectus quia.</div>', 1, '2024-02-25 13:12:12', '2024-04-25 03:49:23'),
(9, 'B-102', 61, 1, 'Box #2 7KG', 48, '<div style=\"line-height: 19px;\">Lorem ipsum dolor sit, amet consectetur adipisicing elit. Temporibus vel rem quis ratione suscipit reiciendis possimus mollitia laboriosam, iste cumque, eaque provident totam quaerat minima nulla nisi vero delectus quia.</div>', 1, '2024-02-25 13:12:55', '2024-02-25 13:12:55'),
(10, 'B-1030', 61, 3, 'Box #3 10KG', 48, '<div style=\"line-height: 19px;\">Lorem ipsum dolor sit, amet consectetur adipisicing elit. Temporibus vel rem quis ratione suscipit reiciendis possimus mollitia laboriosam, iste cumque, eaque provident totam quaerat minima nulla nisi vero delectus quia.</div>', 1, '2024-02-25 13:13:14', '2024-04-25 03:49:14'),
(11, 'Lycra - 2020', 63, 4, 'Hyviz Yellow Lycra', 51, '<div style=\"line-height: 19px;\">Lorem ipsum dolor sit amet consectetur adipisicing elit. Alias, commodi deleniti, ea modi saepe aliquam consequatur dolorum error vel repudiandae, omnis soluta consectetur ipsa beatae architecto quidem fuga hic nisi!</div>', 1, '2024-02-25 13:24:00', '2024-04-25 03:49:05'),
(12, 'Sooter - 1929', 66, 5, 'Sooter # 18', 48, '<div style=\"line-height: 19px;\">Lorem ipsum dolor sit amet consectetur adipisicing elit. Alias, commodi deleniti, ea modi saepe aliquam consequatur dolorum error vel repudiandae, omnis soluta consectetur ipsa beatae architecto quidem fuga hic nisi!</div>', 1, '2024-02-25 13:24:45', '2024-04-25 03:48:59'),
(13, 'Hook - 4010', 66, 6, 'Hooks', 48, '<p><span style=\"color: rgb(0, 0, 0);\">Lorem ipsum dolor sit amet consectetur adipisicing elit. Alias, commodi deleniti, ea modi saepe aliquam consequatur dolorum error vel repudiandae, omnis soluta consectetur ipsa beatae architecto quidem fuga hic nisi!</span><br></p>', 1, '2024-02-25 13:26:04', '2024-04-25 03:48:53'),
(14, 'Velcro - 505', 66, 4, 'Velcro Black', 50, NULL, 1, '2024-04-25 03:26:29', '2024-04-25 03:41:36');

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
(16, 'Order 101', 'Job 101', 6, 1, '2024-04-26', '<p>Order 101</p>', 1, '2024-04-26 13:36:18', '2024-04-26 13:36:18');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `order_item_id` bigint(20) UNSIGNED NOT NULL,
  `order_id` bigint(20) UNSIGNED NOT NULL,
  `product_type_id` bigint(20) UNSIGNED NOT NULL,
  `quantity` double UNSIGNED NOT NULL,
  `price` double UNSIGNED DEFAULT NULL,
  `total` double UNSIGNED NOT NULL COMMENT 'Quantity * Price',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`order_item_id`, `order_id`, `product_type_id`, `quantity`, `price`, `total`, `created_at`, `updated_at`) VALUES
(59, 16, 49, 1000, 0, 0, '2024-04-26 13:36:18', '2024-04-26 13:36:18'),
(60, 16, 50, 1000, 0, 0, '2024-04-26 13:36:18', '2024-04-26 13:36:18'),
(61, 16, 51, 1000, 0, 0, '2024-04-26 13:36:18', '2024-04-26 13:36:18'),
(62, 16, 52, 1000, 0, 0, '2024-04-26 13:36:18', '2024-04-26 13:36:18'),
(63, 16, 53, 1000, 0, 0, '2024-04-26 13:36:18', '2024-04-26 13:36:18'),
(64, 16, 54, 100, 0, 0, '2024-04-26 13:36:51', '2024-04-26 13:36:51'),
(65, 16, 55, 100, 0, 0, '2024-04-26 13:36:51', '2024-04-26 13:36:51'),
(66, 16, 56, 100, 0, 0, '2024-04-26 13:36:51', '2024-04-26 13:36:51');

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

INSERT INTO `products` (`product_id`, `category_id`, `material_id`, `article_no`, `name`, `description`, `unit_id`, `product_status`, `created_by`, `created_at`, `updated_at`) VALUES
(6, 2, '14|13|12|11', 'WG-101', 'Working Gloves 101', '<p style=\"line-height: 19px;\">Lorem ipsum dolor sit amet consectetur adipisicing elit. Non voluptas accusamus doloremque aspernatur cum vero magnam tempora reiciendis reprehenderit earum ipsam, officia labore ficilis dolores, commodi facere provident, obceacati odio!﻿</p>', 47, 1, 1, '2024-02-19 13:20:03', '2024-04-26 13:14:07'),
(10, 1, '14|13|12|11', 'BG-251', 'Boxing Gloves 151', '<div style=\"line-height: 19px;\">Lorem ipsum dolor sit amet consectetur adipisicing elit. Non voluptas accusamus doloremque aspernatur cum vero magnam tempora reiciendis reprehenderit earum ipsam, officia labore ficilis dolores, commodi facere provident, obceacati odio!</div>', 47, 1, 1, '2024-02-25 12:58:59', '2024-04-26 13:02:13'),
(11, 8, '14|13|12', 'LG-301', 'Leather Gloves 301', '<p><span style=\"color: rgb(0, 0, 0);\">Lorem ipsum dolor sit amet consectetur adipisicing elit. Non voluptas accusamus doloremque aspernatur cum vero magnam tempora reiciendis reprehenderit earum ipsam, officia labore ficilis dolores, commodi facere provident, obceacati odio!</span><br></p>', 47, 1, 1, '2024-02-25 12:59:43', '2024-04-26 13:01:44'),
(12, 9, '7|6|5|4', 'WinG-515', 'Winter Gloves 515', '<p><span style=\"color: rgb(0, 0, 0);\">Lorem ipsum dolor sit amet consectetur adipisicing elit. Non voluptas accusamus doloremque aspernatur cum vero magnam tempora reiciendis reprehenderit earum ipsam, officia labore ficilis dolores, commodi facere provident, obceacati odio!</span><br></p>', 47, 1, 1, '2024-02-25 13:00:37', '2024-04-26 13:00:05'),
(23, 8, '14|13|12|11|7', 'LG - 4001', 'Leather Gloves 4001', '<p>Desc</p>', 47, 1, 1, '2024-04-25 11:43:02', '2024-04-25 11:43:02'),
(24, 8, '14|13|12|11', 'LG - 6010', 'Leather Gloves 6010', '<p>Desc</p>', 47, 1, 1, '2024-04-25 11:44:01', '2024-04-25 11:47:19');

-- --------------------------------------------------------

--
-- Table structure for table `product_boxes`
--

CREATE TABLE `product_boxes` (
  `product_box_id` bigint(20) UNSIGNED NOT NULL,
  `product_type_id` bigint(20) UNSIGNED NOT NULL,
  `box_id` bigint(20) UNSIGNED NOT NULL,
  `quantity` double NOT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_boxes`
--

INSERT INTO `product_boxes` (`product_box_id`, `product_type_id`, `box_id`, `quantity`, `created_by`, `created_at`, `updated_at`) VALUES
(13, 49, 1, 50, 1, '2024-04-26 13:06:21', '2024-04-26 13:06:21'),
(14, 50, 1, 50, 1, '2024-04-26 13:07:06', '2024-04-26 13:07:06'),
(15, 51, 1, 50, 1, '2024-04-26 13:07:12', '2024-04-26 13:07:12'),
(16, 52, 1, 50, 1, '2024-04-26 13:07:19', '2024-04-26 13:07:19'),
(17, 53, 1, 50, 1, '2024-04-26 13:07:23', '2024-04-26 13:07:23'),
(18, 54, 1, 50, 1, '2024-04-26 13:08:17', '2024-04-26 13:08:17'),
(19, 55, 1, 50, 1, '2024-04-26 13:08:23', '2024-04-26 13:08:23'),
(20, 55, 1, 50, 1, '2024-04-26 13:08:25', '2024-04-26 13:08:25'),
(21, 56, 1, 50, 1, '2024-04-26 13:09:49', '2024-04-26 13:09:49'),
(22, 89, 2, 30, 1, '2024-04-26 13:14:56', '2024-04-26 13:14:56'),
(23, 90, 2, 30, 1, '2024-04-26 13:15:03', '2024-04-26 13:15:03'),
(24, 91, 2, 30, 1, '2024-04-26 13:15:08', '2024-04-26 13:15:08'),
(25, 75, 2, 50, 1, '2024-04-26 13:30:03', '2024-04-26 13:30:03'),
(26, 76, 2, 50, 1, '2024-04-26 13:30:11', '2024-04-26 13:30:11'),
(27, 77, 2, 50, 1, '2024-04-26 13:30:18', '2024-04-26 13:30:18'),
(28, 78, 2, 50, 1, '2024-04-26 13:30:26', '2024-04-26 13:30:26'),
(29, 68, 1, 50, 1, '2024-04-26 13:31:01', '2024-04-26 13:31:01'),
(30, 69, 1, 50, 1, '2024-04-26 13:31:09', '2024-04-26 13:31:09'),
(31, 70, 1, 50, 1, '2024-04-26 13:31:16', '2024-04-26 13:31:16'),
(32, 71, 2, 40, 1, '2024-04-26 13:31:45', '2024-04-26 13:31:45'),
(33, 72, 2, 40, 1, '2024-04-26 13:31:51', '2024-04-26 13:31:51'),
(34, 73, 2, 40, 1, '2024-04-26 13:31:57', '2024-04-26 13:31:57'),
(35, 74, 2, 40, 1, '2024-04-26 13:32:03', '2024-04-26 13:32:03'),
(36, 49, 1, 50, 1, '2024-04-26 13:54:42', '2024-04-26 13:54:42'),
(37, 50, 1, 50, 1, '2024-04-26 13:54:47', '2024-04-26 13:54:47'),
(38, 51, 1, 50, 1, '2024-04-26 13:54:52', '2024-04-26 13:54:52'),
(39, 52, 1, 50, 1, '2024-04-26 13:54:57', '2024-04-26 13:54:57'),
(40, 53, 1, 50, 1, '2024-04-26 13:55:03', '2024-04-26 13:55:03'),
(41, 54, 1, 30, 1, '2024-04-26 13:55:27', '2024-04-26 13:55:27'),
(42, 55, 1, 30, 1, '2024-04-26 13:55:32', '2024-04-26 13:55:32'),
(43, 56, 1, 30, 1, '2024-04-26 13:55:37', '2024-04-26 13:55:37'),
(44, 68, 2, 40, 1, '2024-04-26 13:56:02', '2024-04-26 13:56:02'),
(45, 69, 2, 40, 1, '2024-04-26 13:56:07', '2024-04-26 13:56:07'),
(46, 70, 2, 40, 1, '2024-04-26 13:56:11', '2024-04-26 13:56:11'),
(47, 71, 2, 50, 1, '2024-04-26 13:56:27', '2024-04-26 13:56:27'),
(48, 72, 2, 50, 1, '2024-04-26 13:56:36', '2024-04-26 13:56:36'),
(49, 73, 2, 50, 1, '2024-04-26 13:56:42', '2024-04-26 13:56:42'),
(50, 74, 2, 50, 1, '2024-04-26 13:56:48', '2024-04-26 13:56:48'),
(51, 75, 2, 30, 1, '2024-04-26 13:57:12', '2024-04-26 13:57:12'),
(52, 76, 2, 30, 1, '2024-04-26 13:57:17', '2024-04-26 13:57:17'),
(53, 77, 2, 30, 1, '2024-04-26 13:57:22', '2024-04-26 13:57:22'),
(54, 78, 2, 30, 1, '2024-04-26 13:57:27', '2024-04-26 13:57:27'),
(55, 89, 2, 50, 1, '2024-04-26 13:57:48', '2024-04-26 13:57:48'),
(56, 90, 2, 50, 1, '2024-04-26 13:57:54', '2024-04-26 13:57:54'),
(57, 91, 2, 50, 1, '2024-04-26 13:57:59', '2024-04-26 13:57:59');

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
(96, 12, 'general', 0, 84, 12, 1, '2024-04-26 13:34:23', '2024-04-26 13:34:23');

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
(499, 91, 11, 2, 1, '2024-04-26 13:57:59', '2024-04-26 13:57:59'),
(500, 91, 12, 3, 1, '2024-04-26 13:57:59', '2024-04-26 13:57:59'),
(501, 91, 13, 2, 1, '2024-04-26 13:57:59', '2024-04-26 13:57:59'),
(502, 91, 14, 3, 1, '2024-04-26 13:57:59', '2024-04-26 13:57:59');

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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_types`
--

INSERT INTO `product_types` (`product_type_id`, `product_id`, `size_id`, `color_id`, `product_type_status`, `created_at`, `updated_at`) VALUES
(49, 24, 1, NULL, 1, '2024-04-26 12:53:20', '2024-04-26 12:53:20'),
(50, 24, 2, NULL, 1, '2024-04-26 12:53:20', '2024-04-26 12:53:20'),
(51, 24, 3, NULL, 1, '2024-04-26 12:53:20', '2024-04-26 12:53:20'),
(52, 24, 4, NULL, 1, '2024-04-26 12:53:20', '2024-04-26 12:53:20'),
(53, 24, 5, NULL, 1, '2024-04-26 12:53:20', '2024-04-26 12:53:20'),
(54, 23, 1, NULL, 1, '2024-04-26 12:53:36', '2024-04-26 12:53:36'),
(55, 23, 2, NULL, 1, '2024-04-26 12:53:36', '2024-04-26 12:53:36'),
(56, 23, 3, NULL, 1, '2024-04-26 12:53:36', '2024-04-26 12:53:36'),
(57, 22, 1, NULL, 1, '2024-04-26 12:54:36', '2024-04-26 12:54:36'),
(58, 22, 2, NULL, 1, '2024-04-26 12:54:36', '2024-04-26 12:54:36'),
(59, 22, 3, NULL, 1, '2024-04-26 12:54:36', '2024-04-26 12:54:36'),
(60, 21, 1, NULL, 1, '2024-04-26 12:58:13', '2024-04-26 12:58:13'),
(61, 21, 2, NULL, 1, '2024-04-26 12:58:13', '2024-04-26 12:58:13'),
(62, 21, 3, NULL, 1, '2024-04-26 12:58:13', '2024-04-26 12:58:13'),
(63, 21, 4, NULL, 1, '2024-04-26 12:58:13', '2024-04-26 12:58:13'),
(64, 21, 5, NULL, 1, '2024-04-26 12:58:13', '2024-04-26 12:58:13'),
(65, 13, 1, NULL, 1, '2024-04-26 12:58:46', '2024-04-26 12:58:46'),
(66, 13, 2, NULL, 1, '2024-04-26 12:58:46', '2024-04-26 12:58:46'),
(67, 13, 3, NULL, 1, '2024-04-26 12:58:46', '2024-04-26 12:58:46'),
(68, 12, 1, NULL, 1, '2024-04-26 13:00:05', '2024-04-26 13:00:05'),
(69, 12, 2, NULL, 1, '2024-04-26 13:00:05', '2024-04-26 13:00:05'),
(70, 12, 3, NULL, 1, '2024-04-26 13:00:05', '2024-04-26 13:00:05'),
(71, 11, 1, NULL, 1, '2024-04-26 13:01:44', '2024-04-26 13:01:44'),
(72, 11, 2, NULL, 1, '2024-04-26 13:01:44', '2024-04-26 13:01:44'),
(73, 11, 3, NULL, 1, '2024-04-26 13:01:44', '2024-04-26 13:01:44'),
(74, 11, 4, NULL, 1, '2024-04-26 13:01:44', '2024-04-26 13:01:44'),
(75, 10, 1, NULL, 1, '2024-04-26 13:02:13', '2024-04-26 13:02:13'),
(76, 10, 2, NULL, 1, '2024-04-26 13:02:13', '2024-04-26 13:02:13'),
(77, 10, 3, NULL, 1, '2024-04-26 13:02:13', '2024-04-26 13:02:13'),
(78, 10, 4, NULL, 1, '2024-04-26 13:02:13', '2024-04-26 13:02:13'),
(79, 9, 1, NULL, 1, '2024-04-26 13:02:47', '2024-04-26 13:02:47'),
(80, 9, 2, NULL, 1, '2024-04-26 13:02:47', '2024-04-26 13:02:47'),
(81, 9, 3, NULL, 1, '2024-04-26 13:02:47', '2024-04-26 13:02:47'),
(82, 9, 4, NULL, 1, '2024-04-26 13:02:47', '2024-04-26 13:02:47'),
(83, 8, 1, NULL, 1, '2024-04-26 13:03:08', '2024-04-26 13:03:08'),
(84, 8, 2, NULL, 1, '2024-04-26 13:03:08', '2024-04-26 13:03:08'),
(85, 8, 3, NULL, 1, '2024-04-26 13:03:08', '2024-04-26 13:03:08'),
(86, 7, 1, NULL, 1, '2024-04-26 13:13:40', '2024-04-26 13:13:40'),
(87, 7, 2, NULL, 1, '2024-04-26 13:13:40', '2024-04-26 13:13:40'),
(88, 7, 3, NULL, 1, '2024-04-26 13:13:40', '2024-04-26 13:13:40'),
(89, 6, 1, NULL, 1, '2024-04-26 13:14:07', '2024-04-26 13:14:07'),
(90, 6, 2, NULL, 1, '2024-04-26 13:14:07', '2024-04-26 13:14:07'),
(91, 6, 3, NULL, 1, '2024-04-26 13:14:07', '2024-04-26 13:14:07');

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

--
-- Dumping data for table `purchases`
--

INSERT INTO `purchases` (`purchase_id`, `purchase_no`, `order_id`, `vendor_id`, `description`, `purchase_date`, `require_date`, `created_by`, `created_at`, `updated_at`) VALUES
(24, 'P2404001', NULL, 7, '<p>Detail of New Max Purchase From Abdulrehman</p>', '2024-04-26', '2024-04-26', 1, '2024-04-26 11:30:53', '2024-04-26 11:30:53'),
(25, 'P2404002', NULL, 7, NULL, '2024-04-26', '2024-04-26', 1, '2024-04-26 11:43:36', '2024-04-26 11:43:36');

-- --------------------------------------------------------

--
-- Table structure for table `purchase_items`
--

CREATE TABLE `purchase_items` (
  `purchase_item_id` bigint(20) UNSIGNED NOT NULL,
  `purchase_id` bigint(20) UNSIGNED NOT NULL,
  `material_id` bigint(20) UNSIGNED NOT NULL,
  `quantity` double NOT NULL,
  `price` double UNSIGNED NOT NULL,
  `total` double UNSIGNED NOT NULL COMMENT 'Quantity * Price',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `purchase_items`
--

INSERT INTO `purchase_items` (`purchase_item_id`, `purchase_id`, `material_id`, `quantity`, `price`, `total`, `created_at`, `updated_at`) VALUES
(373, 24, 14, 1000, 100, 100000, '2024-04-26 11:30:53', '2024-04-26 11:30:53'),
(374, 24, 13, 1000, 100, 100000, '2024-04-26 11:30:53', '2024-04-26 11:30:53'),
(375, 24, 12, 1000, 100, 100000, '2024-04-26 11:30:53', '2024-04-26 11:30:53'),
(376, 24, 11, 1000, 100, 100000, '2024-04-26 11:30:53', '2024-04-26 11:30:53'),
(377, 24, 7, 1000, 100, 100000, '2024-04-26 11:30:53', '2024-04-26 11:30:53'),
(378, 24, 6, 1000, 100, 100000, '2024-04-26 11:30:53', '2024-04-26 11:30:53'),
(379, 24, 5, 1000, 100, 100000, '2024-04-26 11:30:53', '2024-04-26 11:30:53'),
(380, 24, 4, 1000, 100, 100000, '2024-04-26 11:30:53', '2024-04-26 11:30:53'),
(381, 24, 3, 1000, 100, 100000, '2024-04-26 11:30:53', '2024-04-26 11:30:53'),
(382, 24, 2, 1000, 100, 100000, '2024-04-26 11:30:53', '2024-04-26 11:30:53'),
(383, 25, 14, 12, 12, 144, '2024-04-26 11:43:36', '2024-04-26 11:43:36');

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
(39, 'R1-P2404002', 25, '2024-04-26', 0, NULL, 1, '2024-04-26 11:46:50', '2024-04-26 12:42:15');

-- --------------------------------------------------------

--
-- Table structure for table `receive_materials`
--

CREATE TABLE `receive_materials` (
  `receive_material_id` bigint(20) UNSIGNED NOT NULL,
  `receive_id` bigint(20) UNSIGNED NOT NULL,
  `purchase_item_id` bigint(20) UNSIGNED NOT NULL,
  `quantity` double NOT NULL,
  `inspection_status` bigint(20) UNSIGNED NOT NULL DEFAULT 1 COMMENT '1 Pending, 2 Approved, 3 Rejected',
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `receive_materials`
--

INSERT INTO `receive_materials` (`receive_material_id`, `receive_id`, `purchase_item_id`, `quantity`, `inspection_status`, `created_by`, `created_at`, `updated_at`) VALUES
(188, 38, 373, 900, 2, 1, '2024-04-26 11:33:16', '2024-04-26 11:33:16'),
(189, 38, 374, 900, 2, 1, '2024-04-26 11:33:16', '2024-04-26 11:33:16'),
(190, 38, 375, 900, 2, 1, '2024-04-26 11:33:16', '2024-04-26 11:33:16'),
(191, 38, 376, 900, 2, 1, '2024-04-26 11:33:16', '2024-04-26 11:33:16'),
(192, 38, 377, 900, 2, 1, '2024-04-26 11:33:16', '2024-04-26 11:33:16'),
(193, 38, 378, 900, 2, 1, '2024-04-26 11:33:16', '2024-04-26 11:33:16'),
(194, 38, 379, 900, 2, 1, '2024-04-26 11:33:16', '2024-04-26 11:33:16'),
(195, 38, 380, 900, 2, 1, '2024-04-26 11:33:16', '2024-04-26 11:33:16'),
(196, 38, 381, 900, 2, 1, '2024-04-26 11:33:16', '2024-04-26 11:33:16'),
(197, 38, 382, 900, 2, 1, '2024-04-26 11:33:16', '2024-04-26 11:33:16'),
(198, 39, 383, 12, 1, 1, '2024-04-26 11:46:50', '2024-04-26 11:46:50');

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
(33, 39, 'Return1-R1-P2404002', '2024-04-26', '<p>Desc</p>', 1, '2024-04-26 12:40:52', '2024-04-26 12:40:52');

-- --------------------------------------------------------

--
-- Table structure for table `return_materials`
--

CREATE TABLE `return_materials` (
  `return_material_id` bigint(20) UNSIGNED NOT NULL,
  `return_id` bigint(20) UNSIGNED NOT NULL,
  `receive_material_id` bigint(20) UNSIGNED NOT NULL,
  `quantity` double NOT NULL,
  `remarks` longtext DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `return_materials`
--

INSERT INTO `return_materials` (`return_material_id`, `return_id`, `receive_material_id`, `quantity`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(111, 33, 198, 12, 'Testing Return', 1, '2024-04-26 12:40:52', '2024-04-26 12:40:52');

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
(1, 8, 15000, 0, 1, '2024-04-20 04:54:16', '2024-04-20 07:51:49'),
(3, 8, 16000, 1, 1, '2024-04-20 07:51:49', '2024-04-20 07:51:49'),
(4, 7, 20000, 1, 1, '2024-04-20 07:56:29', '2024-04-20 07:56:29'),
(5, 5, 35000, 1, 1, '2024-04-20 07:56:56', '2024-04-20 07:56:56');

-- --------------------------------------------------------

--
-- Table structure for table `stocks`
--

CREATE TABLE `stocks` (
  `stock_id` bigint(20) UNSIGNED NOT NULL,
  `issue_id` bigint(20) UNSIGNED DEFAULT NULL,
  `stock_no` varchar(255) NOT NULL,
  `order_id` bigint(20) UNSIGNED NOT NULL,
  `table_name` varchar(255) NOT NULL,
  `employee_id` bigint(20) UNSIGNED NOT NULL,
  `stock_type` bigint(20) UNSIGNED NOT NULL DEFAULT 2 COMMENT 'Stock In/Out',
  `stock_date` date NOT NULL,
  `stock_status` bigint(20) UNSIGNED NOT NULL DEFAULT 1,
  `description` longtext DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `stocks`
--

INSERT INTO `stocks` (`stock_id`, `issue_id`, `stock_no`, `order_id`, `table_name`, `employee_id`, `stock_type`, `stock_date`, `stock_status`, `description`, `created_by`, `created_at`, `updated_at`) VALUES
(82, NULL, 'I24040001', 16, 'vendor', 6, 2, '2024-04-27', 2, NULL, 1, '2024-04-26 14:05:16', '2024-04-26 14:43:24'),
(83, 82, 'R1-I24040001', 16, 'vendor', 6, 1, '2024-04-27', 2, NULL, 1, '2024-04-26 14:18:45', '2024-04-26 14:18:45'),
(84, 82, 'R2-I24040001', 16, 'vendor', 6, 1, '2024-04-27', 1, NULL, 1, '2024-04-26 14:33:56', '2024-04-26 14:33:56');

-- --------------------------------------------------------

--
-- Table structure for table `stock_items`
--

CREATE TABLE `stock_items` (
  `stock_item_id` bigint(20) UNSIGNED NOT NULL,
  `stock_id` bigint(20) UNSIGNED NOT NULL,
  `product_type_id` bigint(20) UNSIGNED NOT NULL,
  `material_id` bigint(20) UNSIGNED NOT NULL,
  `quantity` double NOT NULL,
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
(171, 84, 54, 0, 10, 73, '78|79', '12|12', 1, '2024-04-26 14:33:56', '2024-04-26 14:42:56');

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `transaction_id` bigint(20) UNSIGNED NOT NULL,
  `transaction_to` varchar(255) NOT NULL COMMENT 'Vendor, Employee, Expense',
  `transaction_type` varchar(255) NOT NULL COMMENT 'Salary, Advance etc',
  `bank_id` bigint(20) UNSIGNED NOT NULL COMMENT 'Our Bank',
  `order_id` bigint(20) UNSIGNED DEFAULT NULL COMMENT 'Purchase / Order',
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
(12, 'customer', 'orderPayment', 0, 16, 6, 0, NULL, 5000000, '2024-04-27', '<p>Five Million Payment Received As Cash Payment</p>', 1, '2024-04-27 03:02:08', '2024-04-27 03:09:04'),
(13, 'expense', 'expense', 0, NULL, 42, 0, 500, NULL, '2024-04-27', '<p>Electrician Charges&nbsp;</p>', 1, '2024-04-27 03:04:24', '2024-04-27 03:04:24'),
(14, 'customer', 'orderPayment', 1, 16, 6, 0, NULL, 250000, '2024-04-27', NULL, 1, '2024-04-27 03:08:11', '2024-04-27 03:08:11'),
(15, 'employee', 'salaryAdvance', 0, NULL, 8, 0, 6000, NULL, '2024-04-27', '<p><br></p>', 1, '2024-04-27 03:18:30', '2024-04-27 03:20:04'),
(16, 'employee', 'receiveAdvance', 0, NULL, 7, 0, NULL, 5000, '2024-04-27', '<p>123</p>', 1, '2024-04-27 03:30:58', '2024-04-27 03:30:58'),
(17, 'vendor', 'wages', 0, NULL, 6, 0, 2000, NULL, '2024-04-27', NULL, 1, '2024-04-27 04:24:11', '2024-04-27 04:24:11'),
(18, 'vendor', 'advance', 0, NULL, 6, 0, 50000, NULL, '2024-04-27', NULL, 1, '2024-04-27 04:26:02', '2024-04-27 04:26:02'),
(19, 'vendor', 'receiveAdvance', 0, NULL, 6, 0, NULL, 20000, '2024-04-27', NULL, 1, '2024-04-27 04:26:12', '2024-04-27 04:26:12');

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

INSERT INTO `vendors` (`vendor_id`, `vendor_type_id`, `vendor_no`, `vendor_type`, `material_id`, `name`, `fname`, `phone1`, `phone2`, `city_id`, `address`, `description`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 69, 'V24001', 1, '0', 'Sarfraz', 'Sarfraz Salman', '03001122334', '03001122334', 44, 'Address of vendor', '<p>New Desc</p>', 1, '2024-02-20 07:35:03', '2024-04-26 11:12:19'),
(3, 57, 'V24002', 0, '0', 'Huzaifa', 'Huzaifa Shafeeq', '03001122334', '03001122334', 46, 'Address of Leather Vendor', '<p><span style=\"color: rgb(0, 0, 0);\">Lorem, ipsum dolor sit amet consectetur adipisicing elit. Obcaecati minima corporis quae, odit officiis facere labore autem beatae! Reprehenderit quasi corrupti ullam enim quia vitae suscipit, asperiores aut delectus possimus!</span><br></p>', 1, '2024-02-25 14:05:38', '2024-04-26 11:11:45'),
(4, 67, 'V24003', 0, '0', 'Rouf', 'Abdul Rouf', '03001122334', '03001122334', 46, 'Address of Box Vendor', '<p><span style=\"color: rgb(0, 0, 0);\">Lorem, ipsum dolor sit amet consectetur adipisicing elit. Obcaecati minima corporis quae, odit officiis facere labore autem beatae! Reprehenderit quasi corrupti ullam enim quia vitae suscipit, asperiores aut delectus possimus!</span><br></p>', 1, '2024-02-25 14:09:18', '2024-04-26 11:11:22'),
(5, 68, 'V24004', 0, '11', 'Arslan', 'Arslan Cheema', '03001122334', '03001122334', 43, 'Address of Liquid Vendor', '<p><span style=\"color: rgb(0, 0, 0);\">Lorem, ipsum dolor sit amet consectetur adipisicing elit. Obcaecati minima corporis quae, odit officiis facere labore autem beatae! Reprehenderit quasi corrupti ullam enim quia vitae suscipit, asperiores aut delectus possimus!</span><br></p>', 1, '2024-02-25 14:10:00', '2024-04-26 11:10:13'),
(6, 60, 'V24005', 1, '0', 'Shehzad', 'Shehzad Ahmed Stitcher', '03001122334', '03001122334', 43, 'Address of Vendor V24005', '<p>Desc</p>', 1, '2024-03-28 13:37:11', '2024-04-26 11:09:18'),
(7, 69, 'V24006', 0, '14|13|12|11|10|9|8|7|3', 'Abdulrehman', 'Abdulrehman Tahir', '03001122334', '03001122334', 45, 'Addresss', '<p><br></p>', 1, '2024-04-25 06:24:36', '2024-04-26 11:07:45');

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
-- Indexes for table `boxes`
--
ALTER TABLE `boxes`
  ADD PRIMARY KEY (`box_id`),
  ADD UNIQUE KEY `box_no` (`box_no`);

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
-- Indexes for table `images`
--
ALTER TABLE `images`
  ADD PRIMARY KEY (`image_id`);

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
-- Indexes for table `product_boxes`
--
ALTER TABLE `product_boxes`
  ADD PRIMARY KEY (`product_box_id`);

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
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `banks`
--
ALTER TABLE `banks`
  MODIFY `bank_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `boxes`
--
ALTER TABLE `boxes`
  MODIFY `box_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

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
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `employee_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `heads`
--
ALTER TABLE `heads`
  MODIFY `head_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=96;

--
-- AUTO_INCREMENT for table `head_types`
--
ALTER TABLE `head_types`
  MODIFY `head_type_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `images`
--
ALTER TABLE `images`
  MODIFY `image_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=139;

--
-- AUTO_INCREMENT for table `materials`
--
ALTER TABLE `materials`
  MODIFY `material_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `order_item_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=67;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` bigint(255) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `product_boxes`
--
ALTER TABLE `product_boxes`
  MODIFY `product_box_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58;

--
-- AUTO_INCREMENT for table `product_costs`
--
ALTER TABLE `product_costs`
  MODIFY `product_cost_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=97;

--
-- AUTO_INCREMENT for table `product_materials`
--
ALTER TABLE `product_materials`
  MODIFY `product_material_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=503;

--
-- AUTO_INCREMENT for table `product_types`
--
ALTER TABLE `product_types`
  MODIFY `product_type_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=92;

--
-- AUTO_INCREMENT for table `purchases`
--
ALTER TABLE `purchases`
  MODIFY `purchase_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `purchase_items`
--
ALTER TABLE `purchase_items`
  MODIFY `purchase_item_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=384;

--
-- AUTO_INCREMENT for table `receives`
--
ALTER TABLE `receives`
  MODIFY `receive_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `receive_materials`
--
ALTER TABLE `receive_materials`
  MODIFY `receive_material_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=199;

--
-- AUTO_INCREMENT for table `returns`
--
ALTER TABLE `returns`
  MODIFY `return_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `return_materials`
--
ALTER TABLE `return_materials`
  MODIFY `return_material_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=112;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `salaries`
--
ALTER TABLE `salaries`
  MODIFY `salary_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `stocks`
--
ALTER TABLE `stocks`
  MODIFY `stock_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=85;

--
-- AUTO_INCREMENT for table `stock_items`
--
ALTER TABLE `stock_items`
  MODIFY `stock_item_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=172;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `transaction_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `vendors`
--
ALTER TABLE `vendors`
  MODIFY `vendor_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
