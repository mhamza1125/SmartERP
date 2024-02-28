-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 28, 2024 at 11:47 AM
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
  `bank_holder` bigint(20) UNSIGNED NOT NULL COMMENT '0-Admin, 1-Employee, 2-Vendor',
  `banker_id` bigint(20) UNSIGNED NOT NULL COMMENT '0-Admin',
  `head_id` bigint(20) UNSIGNED NOT NULL COMMENT 'Bank Type',
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
  `fname` varchar(255) NOT NULL,
  `lname` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
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

INSERT INTO `customers` (`customer_id`, `customer_no`, `fname`, `lname`, `email`, `phone`, `fax`, `address`, `description`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'C101', 'Faizan', 'Pervaiz', 'faizan@gmail.com', '03000000000', '03000000000', 'Lahore Trunk Road', '<div style=\"line-height: 19px;\">Lorem ipsum dolor sit amet consectetur adipisicing elit. Sed eaque iure officiis nobis modi inventore consequuntur rem dolorum, obcaecati nihil delectus similique incidunt exercitationem quae soluta reprehenderit provident magnam blanditiis.</div>', 1, '2024-02-18 04:53:35', '2024-02-25 13:33:47'),
(2, 'C-201', 'Afraz', 'Anwar', 'afraz@gmail.com', '03000000000', '0300 1122334', 'Address of Anwar', NULL, 1, '2024-02-25 13:34:35', '2024-02-25 13:34:35'),
(3, 'C-301', 'Moazzam', 'Abdullah', 'moazzam@gmail.com', '03000000000', '0300 1122334', 'Address of Moazzam', NULL, 1, '2024-02-25 13:35:01', '2024-02-25 13:35:01'),
(5, 'C-401', 'Samad', 'Ali', 'samad@gmail.com', '03000000000', '0300 1122334', 'Address of Samad Ali', '<div style=\"line-height: 19px;\">Lorem ipsum dolor sit amet consectetur adipisicing elit. Totam quo, sunt itaque veniam inventore corrupti amet illum? Vero distinctio commodi autem, aperiam unde dolores doloribus corporis natus quod ex ipsam.</div>', 1, '2024-02-25 13:43:49', '2024-02-25 13:49:44');

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

CREATE TABLE `employees` (
  `employee_id` bigint(20) UNSIGNED NOT NULL,
  `employee_no` varchar(255) NOT NULL,
  `department_id` bigint(20) UNSIGNED NOT NULL COMMENT 'HeadID',
  `employee_type_id` bigint(20) UNSIGNED NOT NULL COMMENT 'Salary, Wages',
  `name` varchar(255) NOT NULL,
  `fname` varchar(255) NOT NULL,
  `sname` varchar(255) DEFAULT NULL,
  `cnic` varchar(255) NOT NULL,
  `phone1` varchar(255) NOT NULL,
  `phone2` varchar(255) DEFAULT NULL,
  `city_id` bigint(20) UNSIGNED NOT NULL COMMENT 'HeadID',
  `address` longtext NOT NULL,
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

INSERT INTO `employees` (`employee_id`, `employee_no`, `department_id`, `employee_type_id`, `name`, `fname`, `sname`, `cnic`, `phone1`, `phone2`, `city_id`, `address`, `description`, `joining_date`, `employee_status`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'Emp - 101', 6, 39, 'Zohaib Khalid', 'Khalid', 'Zohaib', '3460389902345', '03001122334', NULL, 44, 'Address of Zohaib', '<p><span style=\"font-weight: bolder;\">Employee form Lahore, \"</span>Placed in Admin Department<span style=\"font-weight: bolder;\">\"</span><br></p>', '2024-02-18', 1, 1, '2024-02-18 13:04:22', '2024-02-19 05:53:35'),
(5, 'Emp - 102', 7, 39, 'Bashir Malik', 'Manoor', 'Basihr', '3460389902345', '03001122334', '03001122334', 43, 'Address of Bashir', '<div style=\"line-height: 19px;\">Lorem ipsum dolor sit amet consectetur adipisicing elit. Quia, voluptatibus. Laudantium corporis animi assumenda reprehenderit ipsum velit reiciendis nostrum esse id quod quisquam quasi veniam vel aliquid officia, voluptate debitis.</div>', '2024-02-25', 1, 1, '2024-02-25 13:56:59', '2024-02-25 13:56:59'),
(6, 'Emp - 103', 8, 40, 'Adil Nawaz', 'Adil Nawaz', 'Adil', '3460389902345', '03001122334', '03001122334', 44, 'Address of Adil Nawaz', '<div style=\"line-height: 19px;\">Lorem, ipsum dolor sit amet consectetur adipisicing elit. Obcaecati minima corporis quae, odit officiis facere labore autem beatae! Reprehenderit quasi corrupti ullam enim quia vitae suscipit, asperiores aut delectus possimus!</div>', '2024-02-25', 1, 1, '2024-02-25 14:02:20', '2024-02-25 14:02:20'),
(7, 'Emp - 104', 9, 39, 'Haider Ali', 'Abdullah', 'Haider', '3460389902345', '03001122334', '03001122334', 45, 'Address of Haider', '<div style=\"line-height: 19px;\">Lorem, ipsum dolor sit amet consectetur adipisicing elit. Obcaecati minima corporis quae, odit officiis facere labore autem beatae! Reprehenderit quasi corrupti ullam enim quia vitae suscipit, asperiores aut delectus possimus!</div>', '2024-02-26', 1, 1, '2024-02-25 14:03:32', '2024-02-25 14:03:32');

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
(70, 8, 'Islamabad', 1, 1, '2024-02-26 00:39:51', '2024-02-26 00:39:51');

-- --------------------------------------------------------

--
-- Table structure for table `head_types`
--

CREATE TABLE `head_types` (
  `head_type_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `head_types`
--

INSERT INTO `head_types` (`head_type_id`, `name`, `created_at`, `updated_at`) VALUES
(1, 'Sizes', '2024-02-15 12:01:25', '2024-02-15 12:01:25'),
(2, 'Colors', '2024-02-15 12:01:25', '2024-02-15 12:01:25'),
(3, 'Departments', '2024-02-15 12:01:25', '2024-02-15 12:01:25'),
(4, 'Measuring Units', '2024-02-15 16:13:09', '2024-02-15 16:13:09'),
(5, 'Store / Rooms', '2024-02-15 18:01:58', '2024-02-15 18:01:58'),
(6, 'Bank Accounts', '2024-02-15 19:21:32', '2024-02-15 19:21:32'),
(7, 'Expenses', '2024-02-15 19:21:32', '2024-02-15 19:21:32'),
(8, 'Cities / Towns / Villages', '2024-02-15 19:22:44', '2024-02-15 19:22:44'),
(9, 'Employee Types', '2024-02-16 09:56:31', '2024-02-16 09:56:31'),
(10, 'Material Types', '2024-02-16 18:10:01', '2024-02-16 18:10:01'),
(11, 'Vendor Types', '2024-02-20 12:14:00', '2024-02-20 12:14:00');

-- --------------------------------------------------------

--
-- Table structure for table `images`
--

CREATE TABLE `images` (
  `image_id` bigint(20) UNSIGNED NOT NULL,
  `table_name` varchar(255) NOT NULL,
  `table_id` bigint(20) UNSIGNED NOT NULL,
  `image` varchar(255) NOT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `images`
--

INSERT INTO `images` (`image_id`, `table_name`, `table_id`, `image`, `created_by`, `created_at`, `updated_at`) VALUES
(23, 'employees', 1, 'img01_1708363759.png', 1, '2024-02-19 12:29:19', '2024-02-19 12:29:19'),
(24, 'employees', 1, 'img02_1708363759.png', 1, '2024-02-19 12:29:19', '2024-02-19 12:29:19'),
(25, 'employees', 1, 'img03_1708363759.png', 1, '2024-02-19 12:29:19', '2024-02-19 12:29:19'),
(26, 'materials', 1, 'img04_1708363786.png', 1, '2024-02-19 12:29:46', '2024-02-19 12:29:46'),
(27, 'materials', 1, 'img05_1708363786.png', 1, '2024-02-19 12:29:46', '2024-02-19 12:29:46'),
(28, 'materials', 1, 'img06_1708363786.png', 1, '2024-02-19 12:29:46', '2024-02-19 12:29:46'),
(29, 'vendors', 1, 'img07_1708448931.png', 1, '2024-02-20 12:08:51', '2024-02-20 12:08:51'),
(30, 'vendors', 1, 'img08_1708448931.png', 1, '2024-02-20 12:08:51', '2024-02-20 12:08:51'),
(31, 'vendors', 1, 'img09_1708448931.png', 1, '2024-02-20 12:08:51', '2024-02-20 12:08:51'),
(35, 'products', 6, 'img01_1708791619.png', 1, '2024-02-24 11:20:19', '2024-02-24 11:20:19'),
(36, 'products', 6, 'img02_1708791619.png', 1, '2024-02-24 11:20:19', '2024-02-24 11:20:19'),
(37, 'products', 6, 'img03_1708791619.png', 1, '2024-02-24 11:20:19', '2024-02-24 11:20:19'),
(38, 'products', 6, 'img04_1708791619.png', 1, '2024-02-24 11:20:19', '2024-02-24 11:20:19'),
(39, 'products', 6, 'img05_1708791619.png', 1, '2024-02-24 11:20:19', '2024-02-24 11:20:19'),
(40, 'products', 6, 'img06_1708791619.png', 1, '2024-02-24 11:20:19', '2024-02-24 11:20:19'),
(41, 'products', 6, 'img07_1708791619.png', 1, '2024-02-24 11:20:19', '2024-02-24 11:20:19'),
(42, 'products', 6, 'img08_1708791619.png', 1, '2024-02-24 11:20:19', '2024-02-24 11:20:19'),
(43, 'products', 6, 'img09_1708791619.png', 1, '2024-02-24 11:20:19', '2024-02-24 11:20:19'),
(45, 'products', 9, '1_1708883883.png', 1, '2024-02-25 12:58:03', '2024-02-25 12:58:03'),
(46, 'products', 9, '2_1708883883.png', 1, '2024-02-25 12:58:03', '2024-02-25 12:58:03'),
(47, 'products', 9, '3_1708883883.png', 1, '2024-02-25 12:58:03', '2024-02-25 12:58:03'),
(48, 'products', 10, '12_1708883939.png', 1, '2024-02-25 12:58:59', '2024-02-25 12:58:59'),
(49, 'products', 10, '13_1708883939.png', 1, '2024-02-25 12:58:59', '2024-02-25 12:58:59'),
(50, 'products', 10, '14_1708883939.png', 1, '2024-02-25 12:58:59', '2024-02-25 12:58:59'),
(51, 'products', 11, '7_1708883983.png', 1, '2024-02-25 12:59:43', '2024-02-25 12:59:43'),
(52, 'products', 11, '8_1708883983.png', 1, '2024-02-25 12:59:43', '2024-02-25 12:59:43'),
(53, 'products', 11, '9_1708883983.png', 1, '2024-02-25 12:59:43', '2024-02-25 12:59:43'),
(54, 'products', 12, '1_1708884037.png', 1, '2024-02-25 13:00:37', '2024-02-25 13:00:37'),
(55, 'products', 12, '2_1708884037.png', 1, '2024-02-25 13:00:37', '2024-02-25 13:00:37'),
(56, 'products', 12, '3_1708884037.png', 1, '2024-02-25 13:00:37', '2024-02-25 13:00:37'),
(57, 'products', 12, '4_1708884037.png', 1, '2024-02-25 13:00:37', '2024-02-25 13:00:37'),
(58, 'materials', 1, '1_1708884522.png', 1, '2024-02-25 13:08:42', '2024-02-25 13:08:42'),
(59, 'materials', 1, '2_1708884522.png', 1, '2024-02-25 13:08:42', '2024-02-25 13:08:42'),
(60, 'materials', 1, '3_1708884522.png', 1, '2024-02-25 13:08:42', '2024-02-25 13:08:42'),
(61, 'customers', 5, '1_1708886629.png', 1, '2024-02-25 13:43:49', '2024-02-25 13:43:49'),
(62, 'customers', 5, '2_1708886629.png', 1, '2024-02-25 13:43:49', '2024-02-25 13:43:49'),
(63, 'customers', 5, '3_1708886629.png', 1, '2024-02-25 13:43:49', '2024-02-25 13:43:49'),
(64, 'customers', 5, '4_1708886629.png', 1, '2024-02-25 13:43:49', '2024-02-25 13:43:49'),
(67, 'employees', 5, '1_1708887419.png', 1, '2024-02-25 13:56:59', '2024-02-25 13:56:59'),
(68, 'employees', 5, '2_1708887419.png', 1, '2024-02-25 13:56:59', '2024-02-25 13:56:59'),
(69, 'employees', 5, '3_1708887419.png', 1, '2024-02-25 13:56:59', '2024-02-25 13:56:59'),
(70, 'employees', 6, '6_1708887740.png', 1, '2024-02-25 14:02:20', '2024-02-25 14:02:20'),
(71, 'employees', 6, '7_1708887740.png', 1, '2024-02-25 14:02:20', '2024-02-25 14:02:20'),
(72, 'employees', 6, '8_1708887740.png', 1, '2024-02-25 14:02:20', '2024-02-25 14:02:20'),
(73, 'employees', 7, '6_1708887812.png', 1, '2024-02-25 14:03:32', '2024-02-25 14:03:32'),
(74, 'employees', 7, '7_1708887812.png', 1, '2024-02-25 14:03:32', '2024-02-25 14:03:32'),
(75, 'employees', 7, '8_1708887812.png', 1, '2024-02-25 14:03:32', '2024-02-25 14:03:32'),
(76, 'vendors', 3, '5_1708887938.png', 1, '2024-02-25 14:05:38', '2024-02-25 14:05:38'),
(77, 'vendors', 3, '6_1708887938.png', 1, '2024-02-25 14:05:38', '2024-02-25 14:05:38'),
(78, 'vendors', 3, '7_1708887938.png', 1, '2024-02-25 14:05:38', '2024-02-25 14:05:38'),
(79, 'vendors', 4, '4_1708888158.png', 1, '2024-02-25 14:09:18', '2024-02-25 14:09:18'),
(80, 'vendors', 4, '5_1708888158.png', 1, '2024-02-25 14:09:18', '2024-02-25 14:09:18'),
(81, 'vendors', 4, '6_1708888158.png', 1, '2024-02-25 14:09:18', '2024-02-25 14:09:18'),
(82, 'vendors', 4, '7_1708888158.png', 1, '2024-02-25 14:09:18', '2024-02-25 14:09:18'),
(83, 'vendors', 5, '2_1708888200.png', 1, '2024-02-25 14:10:00', '2024-02-25 14:10:00'),
(84, 'vendors', 5, '3_1708888200.png', 1, '2024-02-25 14:10:00', '2024-02-25 14:10:00'),
(85, 'vendors', 5, '4_1708888200.png', 1, '2024-02-25 14:10:00', '2024-02-25 14:10:00'),
(86, 'materials', 4, '1_1708926601.png', 1, '2024-02-26 00:50:01', '2024-02-26 00:50:01'),
(87, 'materials', 4, '2_1708926601.png', 1, '2024-02-26 00:50:01', '2024-02-26 00:50:01'),
(88, 'materials', 4, '3_1708926601.png', 1, '2024-02-26 00:50:01', '2024-02-26 00:50:01');

-- --------------------------------------------------------

--
-- Table structure for table `materials`
--

CREATE TABLE `materials` (
  `material_id` bigint(20) UNSIGNED NOT NULL,
  `material_no` varchar(255) NOT NULL,
  `material_type_id` bigint(20) UNSIGNED NOT NULL COMMENT 'Material Types',
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

INSERT INTO `materials` (`material_id`, `material_no`, `material_type_id`, `name`, `unit_id`, `description`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'L-5010', 54, 'Black Sheep Leather', 49, '<div style=\"line-height: 19px;\">Lorem ipsum dolor sit, amet consectetur adipisicing elit. Temporibus vel rem quis ratione suscipit reiciendis possimus mollitia laboriosam, iste cumque, eaque provident totam quaerat minima nulla nisi vero delectus quia.</div>', 1, '2024-02-19 11:57:34', '2024-02-25 13:08:42'),
(2, 'Zip 101', 56, 'Zip', 50, NULL, 1, '2024-02-19 12:04:39', '2024-02-19 12:04:39'),
(3, 'L-5020', 54, 'Cow Leather', 49, '<div style=\"line-height: 19px;\">Lorem ipsum dolor sit, amet consectetur adipisicing elit. Temporibus vel rem quis ratione suscipit reiciendis possimus mollitia laboriosam, iste cumque, eaque provident totam quaerat minima nulla nisi vero delectus quia.</div>', 1, '2024-02-25 13:09:33', '2024-02-25 13:09:33'),
(4, 'PU-6010', 55, 'Crocodile Leather', 49, '<div style=\"line-height: 19px;\">Lorem ipsum dolor sit, amet consectetur adipisicing elit. Temporibus vel rem quis ratione suscipit reiciendis possimus mollitia laboriosam, iste cumque, eaque provident totam quaerat minima nulla nisi vero delectus quia.</div>', 1, '2024-02-25 13:10:06', '2024-02-25 13:10:06'),
(5, 'PU-6060', 55, 'Printed Black PU Leather', 49, '<div style=\"line-height: 19px;\">Lorem ipsum dolor sit, amet consectetur adipisicing elit. Temporibus vel rem quis ratione suscipit reiciendis possimus mollitia laboriosam, iste cumque, eaque provident totam quaerat minima nulla nisi vero delectus quia.</div>', 1, '2024-02-25 13:10:31', '2024-02-25 13:10:31'),
(6, 'Z-3001', 56, 'Double sided A Quality Zip', 50, '<div style=\"line-height: 19px;\">Lorem ipsum dolor sit, amet consectetur adipisicing elit. Temporibus vel rem quis ratione suscipit reiciendis possimus mollitia laboriosam, iste cumque, eaque provident totam quaerat minima nulla nisi vero delectus quia.</div>', 1, '2024-02-25 13:11:20', '2024-02-25 13:11:20'),
(7, 'Z-3015', 56, 'Single Sided Zip', 50, '<div style=\"line-height: 19px;\">Lorem ipsum dolor sit, amet consectetur adipisicing elit. Temporibus vel rem quis ratione suscipit reiciendis possimus mollitia laboriosam, iste cumque, eaque provident totam quaerat minima nulla nisi vero delectus quia.</div>', 1, '2024-02-25 13:11:37', '2024-02-25 13:11:37'),
(8, 'B-101', 61, 'Box #1 5KG', 48, '<div style=\"line-height: 19px;\">Lorem ipsum dolor sit, amet consectetur adipisicing elit. Temporibus vel rem quis ratione suscipit reiciendis possimus mollitia laboriosam, iste cumque, eaque provident totam quaerat minima nulla nisi vero delectus quia.</div>', 1, '2024-02-25 13:12:12', '2024-02-25 13:12:12'),
(9, 'B-102', 61, 'Box #2 7KG', 48, '<div style=\"line-height: 19px;\">Lorem ipsum dolor sit, amet consectetur adipisicing elit. Temporibus vel rem quis ratione suscipit reiciendis possimus mollitia laboriosam, iste cumque, eaque provident totam quaerat minima nulla nisi vero delectus quia.</div>', 1, '2024-02-25 13:12:55', '2024-02-25 13:12:55'),
(10, 'B-1030', 61, 'Box #3 10KG', 48, '<div style=\"line-height: 19px;\">Lorem ipsum dolor sit, amet consectetur adipisicing elit. Temporibus vel rem quis ratione suscipit reiciendis possimus mollitia laboriosam, iste cumque, eaque provident totam quaerat minima nulla nisi vero delectus quia.</div>', 1, '2024-02-25 13:13:14', '2024-02-25 13:13:14'),
(11, 'Lycra - 2020', 63, 'Hyviz Yellow Lycra', 51, '<div style=\"line-height: 19px;\">Lorem ipsum dolor sit amet consectetur adipisicing elit. Alias, commodi deleniti, ea modi saepe aliquam consequatur dolorum error vel repudiandae, omnis soluta consectetur ipsa beatae architecto quidem fuga hic nisi!</div>', 1, '2024-02-25 13:24:00', '2024-02-25 13:24:00'),
(12, 'Sooter - 1929', 66, 'Sooter # 18', 48, '<div style=\"line-height: 19px;\">Lorem ipsum dolor sit amet consectetur adipisicing elit. Alias, commodi deleniti, ea modi saepe aliquam consequatur dolorum error vel repudiandae, omnis soluta consectetur ipsa beatae architecto quidem fuga hic nisi!</div>', 1, '2024-02-25 13:24:45', '2024-02-25 13:26:27'),
(13, 'Hook - 4010', 66, 'Hooks', 48, '<p><span style=\"color: rgb(0, 0, 0);\">Lorem ipsum dolor sit amet consectetur adipisicing elit. Alias, commodi deleniti, ea modi saepe aliquam consequatur dolorum error vel repudiandae, omnis soluta consectetur ipsa beatae architecto quidem fuga hic nisi!</span><br></p>', 1, '2024-02-25 13:26:04', '2024-02-25 13:26:04');

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
(11, 'Order - 1001', 'Job  -1001', 5, 1, '2024-02-26', '<p>Order By Samad Ali of 1473 Pairs of Gloves</p>', 1, '2024-02-25 14:22:44', '2024-02-25 14:27:23'),
(12, 'Order - 2001', 'Job - 2001', 3, 1, '2024-02-26', '<p>Order By Moazzam of 150 pairs</p>', 1, '2024-02-25 14:23:42', '2024-02-25 14:25:23'),
(13, 'Order - 3001', 'Job - 3001', 1, 1, '2024-02-26', '<p>Order By Afraz of 265 Pairs</p>', 1, '2024-02-25 14:24:52', '2024-02-25 14:24:52'),
(14, 'check223', '1232131', 3, 1, '2024-02-28', NULL, 1, '2024-02-28 04:54:21', '2024-02-28 04:54:21'),
(15, 'dsfdsf', 'dfdsfer', 3, 1, '2024-02-28', NULL, 1, '2024-02-28 04:58:08', '2024-02-28 04:58:08');

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
(25, 13, 28, 25, 0, 0, '2024-02-25 14:24:52', '2024-02-25 14:24:52'),
(26, 13, 15, 20, 0, 0, '2024-02-25 14:24:52', '2024-02-25 14:24:52'),
(27, 13, 20, 220, 0, 0, '2024-02-25 14:24:52', '2024-02-25 14:24:52'),
(28, 12, 23, 30, 0, 0, '2024-02-25 14:25:23', '2024-02-25 14:25:23'),
(29, 12, 24, 20, 0, 0, '2024-02-25 14:25:23', '2024-02-25 14:25:23'),
(30, 12, 25, 100, 0, 0, '2024-02-25 14:25:23', '2024-02-25 14:25:23'),
(31, 11, 14, 50, 0, 0, '2024-02-25 14:27:23', '2024-02-25 14:27:23'),
(32, 11, 12, 100, 0, 0, '2024-02-25 14:27:23', '2024-02-25 14:27:23'),
(33, 11, 13, 200, 0, 0, '2024-02-25 14:27:23', '2024-02-25 14:27:23'),
(34, 11, 33, 600, 0, 0, '2024-02-25 14:27:23', '2024-02-25 14:27:23'),
(35, 11, 23, 120, 0, 0, '2024-02-25 14:27:23', '2024-02-25 14:27:23'),
(36, 11, 26, 23, 0, 0, '2024-02-25 14:27:23', '2024-02-25 14:27:23'),
(37, 11, 20, 40, 0, 0, '2024-02-25 14:27:23', '2024-02-25 14:27:23'),
(38, 11, 30, 200, 0, 0, '2024-02-25 14:27:23', '2024-02-25 14:27:23'),
(39, 11, 24, 140, 0, 0, '2024-02-25 14:27:23', '2024-02-25 14:27:23'),
(41, 14, 13, 2, 0, 0, '2024-02-28 04:56:43', '2024-02-28 04:56:43');

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
  `article_no` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` longtext DEFAULT NULL,
  `product_status` bigint(20) UNSIGNED NOT NULL DEFAULT 1,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`product_id`, `category_id`, `article_no`, `name`, `description`, `product_status`, `created_by`, `created_at`, `updated_at`) VALUES
(6, 2, 'WG-101', 'Working Gloves 101', '<p style=\"line-height: 19px;\">Lorem ipsum dolor sit amet consectetur adipisicing elit. Non voluptas accusamus doloremque aspernatur cum vero magnam tempora reiciendis reprehenderit earum ipsam, officia labore ficilis dolores, commodi facere provident, obceacati odio!﻿</p>', 1, 1, '2024-02-19 13:20:03', '2024-02-25 12:55:05'),
(7, 2, 'WG-121', 'Working Gloves 121', '<div style=\"line-height: 19px;\">Lorem ipsum dolor sit amet consectetur adipisicing elit. Non voluptas accusamus doloremque aspernatur cum vero magnam tempora reiciendis reprehenderit earum ipsam, officia labore ficilis dolores, commodi facere provident, obceacati odio!</div>', 0, 1, '2024-02-25 12:56:15', '2024-02-25 13:02:50'),
(8, 2, 'WG-131', 'Working Gloves 131', NULL, 1, 1, '2024-02-25 12:56:43', '2024-02-25 12:56:43'),
(9, 1, 'BG-201', 'Boxing Gloves 201', '<div style=\"line-height: 19px;\">Lorem ipsum dolor sit amet consectetur adipisicing elit. Non voluptas accusamus doloremque aspernatur cum vero magnam tempora reiciendis reprehenderit earum ipsam, officia labore ficilis dolores, commodi facere provident, obceacati odio!</div>', 0, 1, '2024-02-25 12:58:03', '2024-02-25 13:03:26'),
(10, 1, 'BG-251', 'Boxing Gloves 151', '<div style=\"line-height: 19px;\">Lorem ipsum dolor sit amet consectetur adipisicing elit. Non voluptas accusamus doloremque aspernatur cum vero magnam tempora reiciendis reprehenderit earum ipsam, officia labore ficilis dolores, commodi facere provident, obceacati odio!</div>', 1, 1, '2024-02-25 12:58:59', '2024-02-25 12:58:59'),
(11, 8, 'LG-301', 'Leather Gloves 301', '<p><span style=\"color: rgb(0, 0, 0);\">Lorem ipsum dolor sit amet consectetur adipisicing elit. Non voluptas accusamus doloremque aspernatur cum vero magnam tempora reiciendis reprehenderit earum ipsam, officia labore ficilis dolores, commodi facere provident, obceacati odio!</span><br></p>', 1, 1, '2024-02-25 12:59:43', '2024-02-25 12:59:43'),
(12, 9, 'WinG-515', 'Winter Gloves 515', '<p><span style=\"color: rgb(0, 0, 0);\">Lorem ipsum dolor sit amet consectetur adipisicing elit. Non voluptas accusamus doloremque aspernatur cum vero magnam tempora reiciendis reprehenderit earum ipsam, officia labore ficilis dolores, commodi facere provident, obceacati odio!</span><br></p>', 1, 1, '2024-02-25 13:00:37', '2024-02-25 13:00:37'),
(13, 9, 'WinG-525', 'Winter Gloves 525', NULL, 1, 1, '2024-02-25 13:00:57', '2024-02-25 13:00:57');

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
(12, 15, 2, 20, 1, '2024-02-23 07:20:33', '2024-02-23 07:20:33'),
(13, 15, 1, 20, 1, '2024-02-23 07:20:33', '2024-02-23 07:20:33'),
(14, 16, 1, 12, 1, '2024-02-24 11:22:58', '2024-02-24 11:22:58'),
(15, 16, 2, 12123, 1, '2024-02-24 11:22:58', '2024-02-24 11:22:58'),
(16, 12, 13, 2, 1, '2024-02-25 13:30:22', '2024-02-25 13:30:22'),
(17, 12, 12, 20, 1, '2024-02-25 13:30:22', '2024-02-25 13:30:22'),
(18, 12, 9, 40, 1, '2024-02-25 13:30:22', '2024-02-25 13:30:22'),
(19, 12, 5, 0.5, 1, '2024-02-25 13:30:22', '2024-02-25 13:30:22'),
(20, 12, 4, 1, 1, '2024-02-25 13:30:22', '2024-02-25 13:30:22'),
(21, 25, 9, 0.05, 1, '2024-02-25 13:31:39', '2024-02-25 13:31:39'),
(22, 25, 11, 40, 1, '2024-02-25 13:31:39', '2024-02-25 13:31:39'),
(23, 25, 2, 2, 1, '2024-02-25 13:31:39', '2024-02-25 13:31:39'),
(24, 25, 7, 2, 1, '2024-02-25 13:31:39', '2024-02-25 13:31:39'),
(25, 14, 13, 10, 1, '2024-02-28 04:47:59', '2024-02-28 04:47:59'),
(27, 20, 10, 123, 1, '2024-02-28 04:51:30', '2024-02-28 04:51:30'),
(28, 20, 11, 10, 1, '2024-02-28 04:51:30', '2024-02-28 04:51:30');

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
(12, 6, 2, NULL, 1, '2024-02-19 14:50:17', '2024-02-25 12:55:05'),
(13, 6, 3, NULL, 1, '2024-02-19 14:52:53', '2024-02-25 12:55:05'),
(14, 6, 4, NULL, 1, '2024-02-19 14:53:30', '2024-02-25 12:55:05'),
(15, 6, 1, NULL, 1, '2024-02-19 23:06:00', '2024-02-25 12:55:05'),
(16, 6, 5, NULL, 0, '2024-02-24 11:21:24', '2024-02-25 12:55:05'),
(17, 7, 1, NULL, 1, '2024-02-25 12:56:15', '2024-02-25 13:02:50'),
(18, 7, 2, NULL, 1, '2024-02-25 12:56:15', '2024-02-25 13:02:50'),
(19, 7, 3, NULL, 1, '2024-02-25 12:56:15', '2024-02-25 13:02:50'),
(20, 8, 5, NULL, 1, '2024-02-25 12:56:43', '2024-02-25 12:56:43'),
(21, 9, 2, NULL, 1, '2024-02-25 12:58:03', '2024-02-25 13:03:26'),
(22, 9, 3, NULL, 1, '2024-02-25 12:58:03', '2024-02-25 13:03:26'),
(23, 10, 4, NULL, 1, '2024-02-25 12:58:59', '2024-02-25 12:58:59'),
(24, 10, 5, NULL, 1, '2024-02-25 12:58:59', '2024-02-25 12:58:59'),
(25, 11, 2, NULL, 1, '2024-02-25 12:59:43', '2024-02-25 12:59:43'),
(26, 11, 3, NULL, 1, '2024-02-25 12:59:43', '2024-02-25 12:59:43'),
(27, 11, 4, NULL, 1, '2024-02-25 12:59:43', '2024-02-25 12:59:43'),
(28, 12, 1, NULL, 1, '2024-02-25 13:00:37', '2024-02-26 00:47:12'),
(29, 12, 2, NULL, 1, '2024-02-25 13:00:37', '2024-02-26 00:47:12'),
(30, 12, 3, NULL, 0, '2024-02-25 13:00:37', '2024-02-26 00:47:12'),
(31, 12, 4, NULL, 0, '2024-02-25 13:00:37', '2024-02-26 00:47:12'),
(32, 12, 5, NULL, 0, '2024-02-25 13:00:37', '2024-02-26 00:47:12'),
(33, 13, 4, NULL, 1, '2024-02-25 13:00:57', '2024-02-25 13:00:57');

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
  `inspection_status` bigint(20) UNSIGNED NOT NULL DEFAULT 1 COMMENT '1 - Pending\r\n2 - Approved\r\n3 - Rejected',
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

-- --------------------------------------------------------

--
-- Table structure for table `stock_materials`
--

CREATE TABLE `stock_materials` (
  `store_material_id` bigint(20) UNSIGNED NOT NULL,
  `store_id` bigint(20) UNSIGNED NOT NULL COMMENT 'HeadID',
  `receive_material_id` bigint(20) UNSIGNED NOT NULL,
  `quantity` double UNSIGNED NOT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `stock_products`
--

CREATE TABLE `stock_products` (
  `stock_product_id` bigint(20) UNSIGNED NOT NULL,
  `stock_material_id` bigint(20) UNSIGNED NOT NULL,
  `department_id` bigint(20) UNSIGNED NOT NULL,
  `employee_id` bigint(20) UNSIGNED NOT NULL,
  `stock_type` bigint(20) UNSIGNED NOT NULL COMMENT 'Stock In/Out',
  `quantity` double UNSIGNED NOT NULL,
  `product_stage` bigint(20) UNSIGNED NOT NULL COMMENT '1-Raw, 2-Cutting, 3-Stitched, 4-Finished',
  `movement_date` date NOT NULL,
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
  `transaction_to` bigint(20) UNSIGNED NOT NULL COMMENT 'Vendor, Employee, Expense',
  `transaction_type` bigint(20) UNSIGNED NOT NULL COMMENT 'Salary, Advance',
  `transaction_date` date NOT NULL,
  `debit` bigint(20) UNSIGNED DEFAULT NULL COMMENT 'Cash Out',
  `credit` bigint(20) UNSIGNED DEFAULT NULL COMMENT 'Cash In',
  `payee_id` bigint(20) UNSIGNED NOT NULL COMMENT 'Vendor, Employee',
  `payee_bank_id` bigint(20) UNSIGNED DEFAULT NULL,
  `bank_id` bigint(20) UNSIGNED DEFAULT NULL COMMENT 'Our Bank',
  `payment_method` bigint(20) UNSIGNED NOT NULL COMMENT 'Cash, Deposit, Cross Cheque',
  `transaction_image` varchar(255) DEFAULT NULL,
  `description` longtext DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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

INSERT INTO `vendors` (`vendor_id`, `vendor_type_id`, `name`, `fname`, `phone1`, `phone2`, `city_id`, `address`, `description`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 58, 'Name of Vendor', 'Vendor Name', '03001122334', '03001122334', 44, 'Address of vendor', '<p>New Desc</p>', 1, '2024-02-20 07:35:03', '2024-02-20 07:53:47'),
(3, 57, 'Leather Vendor', 'Leather Vendor Full Name', '03001122334', '03001122334', 46, 'Address of Leather Vendor', '<p><span style=\"color: rgb(0, 0, 0);\">Lorem, ipsum dolor sit amet consectetur adipisicing elit. Obcaecati minima corporis quae, odit officiis facere labore autem beatae! Reprehenderit quasi corrupti ullam enim quia vitae suscipit, asperiores aut delectus possimus!</span><br></p>', 1, '2024-02-25 14:05:38', '2024-02-25 14:05:38'),
(4, 67, 'Box Vendor', 'Box Vendor Full Name', '03001122334', '03001122334', 46, 'Address of Box Vendor', '<p><span style=\"color: rgb(0, 0, 0);\">Lorem, ipsum dolor sit amet consectetur adipisicing elit. Obcaecati minima corporis quae, odit officiis facere labore autem beatae! Reprehenderit quasi corrupti ullam enim quia vitae suscipit, asperiores aut delectus possimus!</span><br></p>', 1, '2024-02-25 14:09:18', '2024-02-25 14:09:18'),
(5, 68, 'Liquid Vendor', 'Liquid Vendor Full Name', '03001122334', '03001122334', 43, 'Address of Liquid Vendor', '<p><span style=\"color: rgb(0, 0, 0);\">Lorem, ipsum dolor sit amet consectetur adipisicing elit. Obcaecati minima corporis quae, odit officiis facere labore autem beatae! Reprehenderit quasi corrupti ullam enim quia vitae suscipit, asperiores aut delectus possimus!</span><br></p>', 1, '2024-02-25 14:10:00', '2024-02-25 14:10:00');

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
  ADD UNIQUE KEY `customer_no` (`customer_no`);

--
-- Indexes for table `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`employee_id`);

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
-- Indexes for table `stock_materials`
--
ALTER TABLE `stock_materials`
  ADD PRIMARY KEY (`store_material_id`);

--
-- Indexes for table `stock_products`
--
ALTER TABLE `stock_products`
  ADD PRIMARY KEY (`stock_product_id`);

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
  ADD PRIMARY KEY (`vendor_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `banks`
--
ALTER TABLE `banks`
  MODIFY `bank_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

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
  MODIFY `customer_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `employee_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `heads`
--
ALTER TABLE `heads`
  MODIFY `head_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=71;

--
-- AUTO_INCREMENT for table `head_types`
--
ALTER TABLE `head_types`
  MODIFY `head_type_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `images`
--
ALTER TABLE `images`
  MODIFY `image_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=89;

--
-- AUTO_INCREMENT for table `materials`
--
ALTER TABLE `materials`
  MODIFY `material_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `order_item_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` bigint(255) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `product_materials`
--
ALTER TABLE `product_materials`
  MODIFY `product_material_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `product_types`
--
ALTER TABLE `product_types`
  MODIFY `product_type_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `purchases`
--
ALTER TABLE `purchases`
  MODIFY `purchase_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `purchase_items`
--
ALTER TABLE `purchase_items`
  MODIFY `purchase_item_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `receives`
--
ALTER TABLE `receives`
  MODIFY `receive_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `receive_materials`
--
ALTER TABLE `receive_materials`
  MODIFY `receive_material_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `returns`
--
ALTER TABLE `returns`
  MODIFY `return_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `return_materials`
--
ALTER TABLE `return_materials`
  MODIFY `return_material_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `salaries`
--
ALTER TABLE `salaries`
  MODIFY `salary_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `stock_materials`
--
ALTER TABLE `stock_materials`
  MODIFY `store_material_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `stock_products`
--
ALTER TABLE `stock_products`
  MODIFY `stock_product_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `transaction_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `vendors`
--
ALTER TABLE `vendors`
  MODIFY `vendor_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
