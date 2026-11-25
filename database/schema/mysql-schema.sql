/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
DROP TABLE IF EXISTS `activity_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `activity_log` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `log_name` varchar(255) DEFAULT NULL,
  `description` text NOT NULL,
  `subject_type` varchar(255) DEFAULT NULL,
  `event` varchar(255) DEFAULT NULL,
  `subject_id` bigint(20) unsigned DEFAULT NULL,
  `causer_type` varchar(255) DEFAULT NULL,
  `causer_id` bigint(20) unsigned DEFAULT NULL,
  `properties` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`properties`)),
  `batch_uuid` char(36) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `subject` (`subject_type`,`subject_id`),
  KEY `causer` (`causer_type`,`causer_id`),
  KEY `activity_log_log_name_index` (`log_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `assets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `assets` (
  `asset_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `kind` varchar(10) NOT NULL DEFAULT 'asset',
  `asset_name` varchar(255) NOT NULL,
  `transaction_type` varchar(255) NOT NULL DEFAULT 'purchase' COMMENT 'purchase, sale, depreciation, writeoff, adjustment',
  `description` text DEFAULT NULL COMMENT 'Transaction description/notes',
  `transaction_date` date DEFAULT NULL COMMENT 'Date of the transaction',
  `debit` decimal(15,2) DEFAULT NULL COMMENT 'Asset acquisition/increase amount',
  `credit` decimal(15,2) DEFAULT NULL COMMENT 'Asset disposal/decrease amount',
  `bank_id` bigint(20) unsigned DEFAULT NULL,
  `transaction_id` bigint(20) unsigned DEFAULT NULL,
  `attachment` varchar(255) DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`asset_id`),
  KEY `assets_transaction_id_foreign` (`transaction_id`),
  KEY `assets_kind_index` (`kind`),
  CONSTRAINT `assets_transaction_id_foreign` FOREIGN KEY (`transaction_id`) REFERENCES `transactions` (`transaction_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `banks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `banks` (
  `bank_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `bank_holder` varchar(255) NOT NULL COMMENT 'Admin, Emploee, Vendor',
  `banker_id` bigint(20) unsigned NOT NULL COMMENT '0-Admin',
  `head_id` bigint(20) unsigned NOT NULL COMMENT 'Bank Type',
  `account_title` varchar(255) NOT NULL,
  `account` varchar(255) NOT NULL,
  `iban` varchar(255) DEFAULT NULL COMMENT 'International Bank Account Number',
  `address` text DEFAULT NULL COMMENT 'Bank address',
  `branch_code` varchar(255) DEFAULT NULL COMMENT 'Bank branch code',
  `swift_code` varchar(255) DEFAULT NULL COMMENT 'SWIFT/BIC code',
  `created_by` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`bank_id`),
  UNIQUE KEY `account` (`account`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `categories` (
  `category_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `created_by` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `company`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `company` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `ceo` varchar(255) NOT NULL,
  `ntn` varchar(255) DEFAULT NULL COMMENT 'National Tax Number',
  `rex_no` varchar(255) DEFAULT NULL COMMENT 'REX Number for commercial invoices',
  `fi_no` varchar(255) DEFAULT NULL,
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
  `overtime_multiplier` decimal(5,2) NOT NULL DEFAULT 2.00,
  `late_multiplier` decimal(5,2) NOT NULL DEFAULT 1.00,
  `work_hours_per_day` decimal(5,2) NOT NULL DEFAULT 8.00,
  `attendance_db_path` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `customers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `customers` (
  `customer_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `customer_no` varchar(255) NOT NULL,
  `country_id` bigint(20) unsigned NOT NULL,
  `currency_id` bigint(20) unsigned NOT NULL,
  `fname` varchar(255) NOT NULL,
  `lname` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(255) NOT NULL,
  `fax` varchar(255) NOT NULL,
  `address` longtext DEFAULT NULL,
  `port_no` varchar(255) DEFAULT NULL COMMENT 'Port number for shipping',
  `description` longtext DEFAULT NULL,
  `created_by` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`customer_id`),
  UNIQUE KEY `customer_no` (`customer_no`),
  KEY `customer_no_3` (`customer_no`),
  FULLTEXT KEY `customer_no_2` (`customer_no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `deliveries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `deliveries` (
  `delivery_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `stock_id` bigint(20) unsigned NOT NULL,
  `fshipping` varchar(255) DEFAULT NULL,
  `tshipping` varchar(255) DEFAULT NULL,
  `fport_no` varchar(255) DEFAULT NULL,
  `tport_no` varchar(255) DEFAULT NULL,
  `delivery_method` bigint(20) unsigned NOT NULL,
  `delivery_status` bigint(20) unsigned NOT NULL,
  `delivery_no` varchar(255) DEFAULT NULL COMMENT 'Auto-generated delivery number (SLE-001-26 format)',
  `delivery_date` date DEFAULT NULL COMMENT 'Actual delivery date',
  `fi_no` varchar(255) DEFAULT NULL COMMENT 'FI Number for commercial invoice',
  `freight_charges` decimal(15,2) NOT NULL DEFAULT 0.00,
  `created_by` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`delivery_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `delivery_boxes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `delivery_boxes` (
  `dbox_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `delivery_id` bigint(20) unsigned NOT NULL,
  `vehicle_no` varchar(255) NOT NULL,
  `rowQty` varchar(255) NOT NULL,
  `totalQty` double unsigned NOT NULL,
  `created_by` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`dbox_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `delivery_return_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `delivery_return_items` (
  `delivery_return_item_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `delivery_return_id` bigint(20) unsigned NOT NULL,
  `stock_item_id` bigint(20) unsigned NOT NULL,
  `quantity` decimal(10,2) NOT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `created_by` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`delivery_return_item_id`),
  KEY `delivery_return_items_delivery_return_id_foreign` (`delivery_return_id`),
  KEY `delivery_return_items_stock_item_id_foreign` (`stock_item_id`),
  KEY `delivery_return_items_created_by_foreign` (`created_by`),
  CONSTRAINT `delivery_return_items_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `delivery_return_items_delivery_return_id_foreign` FOREIGN KEY (`delivery_return_id`) REFERENCES `delivery_returns` (`delivery_return_id`) ON DELETE CASCADE,
  CONSTRAINT `delivery_return_items_stock_item_id_foreign` FOREIGN KEY (`stock_item_id`) REFERENCES `stock_items` (`stock_item_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `delivery_returns`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `delivery_returns` (
  `delivery_return_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `return_no` varchar(255) NOT NULL,
  `delivery_id` bigint(20) unsigned NOT NULL,
  `return_date` date NOT NULL,
  `return_reason` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `created_by` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`delivery_return_id`),
  KEY `delivery_returns_delivery_id_foreign` (`delivery_id`),
  KEY `delivery_returns_created_by_foreign` (`created_by`),
  CONSTRAINT `delivery_returns_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `delivery_returns_delivery_id_foreign` FOREIGN KEY (`delivery_id`) REFERENCES `deliveries` (`delivery_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `employees`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `employees` (
  `employee_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `employee_no` varchar(255) NOT NULL,
  `department_id` bigint(20) unsigned NOT NULL COMMENT 'HeadID',
  `employee_type_id` bigint(20) unsigned NOT NULL COMMENT 'HeadID',
  `attendance_id` bigint(20) unsigned DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `fname` varchar(255) NOT NULL,
  `sname` varchar(255) DEFAULT NULL,
  `cnic` varchar(255) NOT NULL,
  `phone1` varchar(255) NOT NULL,
  `phone2` varchar(255) DEFAULT NULL,
  `city_id` bigint(20) unsigned NOT NULL COMMENT 'HeadID',
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
  `employee_status` bigint(20) unsigned NOT NULL DEFAULT 1 COMMENT 'Active / Inactive',
  `created_by` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`employee_id`),
  UNIQUE KEY `employee_no` (`employee_no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `head_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `head_types` (
  `head_type_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` longtext DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`head_type_id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `heads`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `heads` (
  `head_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `head_type_id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `head_status` bigint(20) unsigned NOT NULL DEFAULT 1,
  `work_start_time` time DEFAULT NULL,
  `work_end_time` time DEFAULT NULL,
  `action` bigint(20) unsigned NOT NULL DEFAULT 0 COMMENT '1 - No Deletion',
  `created_by` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`head_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `igroup_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `igroup_items` (
  `igroup_item_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `igroup_id` bigint(20) unsigned NOT NULL,
  `product_type_id` bigint(20) unsigned NOT NULL,
  `material_id` bigint(20) unsigned NOT NULL,
  `quantity` double unsigned NOT NULL,
  `stage_id` bigint(20) unsigned NOT NULL,
  `created_by` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`igroup_item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `igroups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `igroups` (
  `igroup_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `igroup_no` varchar(255) NOT NULL,
  `order_id` bigint(20) unsigned DEFAULT NULL,
  `igroup_date` date NOT NULL,
  `igroup_status` bigint(20) unsigned NOT NULL DEFAULT 1,
  `description` longtext DEFAULT NULL,
  `created_by` bigint(20) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`igroup_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `images`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `images` (
  `image_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `table_name` varchar(255) NOT NULL,
  `table_id` bigint(20) unsigned NOT NULL,
  `image` varchar(255) NOT NULL,
  `file_title` varchar(255) DEFAULT NULL,
  `file_type` varchar(255) DEFAULT NULL,
  `created_by` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`image_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) unsigned NOT NULL,
  `reserved_at` int(10) unsigned DEFAULT NULL,
  `available_at` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `machines`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `machines` (
  `machine_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `machine_no` varchar(255) NOT NULL,
  `machine_type_id` bigint(20) unsigned NOT NULL,
  `employee_id` bigint(20) unsigned NOT NULL,
  `location` varchar(255) DEFAULT NULL,
  `description` longtext DEFAULT NULL,
  `created_by` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`machine_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `material_price_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `material_price_logs` (
  `material_price_log_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `material_id` bigint(20) unsigned NOT NULL,
  `vendor_id` bigint(20) unsigned DEFAULT NULL,
  `price` decimal(15,3) NOT NULL DEFAULT 0.000,
  `source` varchar(20) NOT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`material_price_log_id`),
  KEY `material_price_logs_material_id_index` (`material_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `materials`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `materials` (
  `material_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `material_no` varchar(255) NOT NULL,
  `material_type_id` bigint(20) unsigned NOT NULL COMMENT 'Material Types',
  `vendor_id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `unit_id` bigint(20) unsigned NOT NULL COMMENT 'HeadID',
  `cprice` double unsigned NOT NULL,
  `location` varchar(255) DEFAULT NULL,
  `description` longtext DEFAULT NULL,
  `created_by` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`material_id`),
  UNIQUE KEY `material_no` (`material_no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `mprocess`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mprocess` (
  `mprocess_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `purchase_id` bigint(20) unsigned NOT NULL,
  `purchase_item_id` bigint(20) unsigned NOT NULL,
  `stock_item_id` bigint(20) unsigned NOT NULL,
  `before_mid` bigint(20) unsigned NOT NULL,
  `before_qty` double NOT NULL,
  `created_by` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`mprocess_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `order_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `order_items` (
  `order_item_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint(20) unsigned NOT NULL,
  `product_type_id` bigint(20) unsigned NOT NULL,
  `product_stage_id` bigint(20) unsigned NOT NULL,
  `quantity` double unsigned NOT NULL,
  `price` double unsigned DEFAULT NULL,
  `price2` double unsigned DEFAULT NULL,
  `total` double unsigned NOT NULL COMMENT 'Quantity * Price',
  `created_by` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`order_item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `orders` (
  `order_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `order_no` varchar(255) NOT NULL COMMENT 'From Client',
  `job_no` varchar(255) NOT NULL COMMENT 'Personal',
  `customer_id` bigint(20) unsigned NOT NULL,
  `order_status` bigint(20) unsigned NOT NULL DEFAULT 1 COMMENT '1 Pending, 2 Processing, 3 OnHold, 4 Partially Delivered, 5 Delivered, 6 Completed, 7 Cancelled, 8 Returned, 9 Disputed',
  `order_date` date NOT NULL,
  `due_date` date DEFAULT NULL COMMENT 'Due date for order completion',
  `payment_terms` text DEFAULT NULL COMMENT 'Payment terms and conditions',
  `so_origin` text DEFAULT NULL COMMENT 'Statement of origin',
  `fi_no` varchar(255) DEFAULT NULL COMMENT 'FI number',
  `rex_no` varchar(255) DEFAULT NULL COMMENT 'REX number',
  `description` longtext DEFAULT NULL,
  `created_by` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `packing_carton_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `packing_carton_items` (
  `packing_carton_item_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `packing_carton_id` bigint(20) unsigned NOT NULL,
  `product_id` bigint(20) unsigned NOT NULL,
  `pcs_each_carton` int(11) NOT NULL,
  `total_pcs` int(11) NOT NULL,
  `created_by` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`packing_carton_item_id`),
  KEY `packing_carton_items_packing_carton_id_foreign` (`packing_carton_id`),
  KEY `packing_carton_items_product_id_foreign` (`product_id`),
  KEY `packing_carton_items_created_by_foreign` (`created_by`),
  CONSTRAINT `packing_carton_items_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `packing_carton_items_packing_carton_id_foreign` FOREIGN KEY (`packing_carton_id`) REFERENCES `packing_cartons` (`packing_carton_id`) ON DELETE CASCADE,
  CONSTRAINT `packing_carton_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `packing_cartons`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `packing_cartons` (
  `packing_carton_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `packing_list_id` bigint(20) unsigned NOT NULL,
  `carton_from` int(11) NOT NULL,
  `carton_to` int(11) NOT NULL,
  `created_by` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `box_dimension` varchar(255) DEFAULT NULL COMMENT 'Box dimensions like 65x42x22 cm',
  `box_weight` decimal(8,2) DEFAULT NULL COMMENT 'Weight per individual carton',
  PRIMARY KEY (`packing_carton_id`),
  KEY `packing_cartons_packing_list_id_foreign` (`packing_list_id`),
  KEY `packing_cartons_created_by_foreign` (`created_by`),
  CONSTRAINT `packing_cartons_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `packing_cartons_packing_list_id_foreign` FOREIGN KEY (`packing_list_id`) REFERENCES `packing_lists` (`packing_list_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `packing_lists`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `packing_lists` (
  `packing_list_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `delivery_id` bigint(20) unsigned NOT NULL,
  `order_id` bigint(20) unsigned NOT NULL,
  `created_by` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `pallet_qty` int(11) DEFAULT NULL COMMENT 'Number of pallets',
  `pallet_weight` decimal(8,2) DEFAULT NULL COMMENT 'Weight per pallet',
  `pallet_dimension` varchar(255) DEFAULT NULL COMMENT 'Pallet dimensions like 100x120 cm',
  PRIMARY KEY (`packing_list_id`),
  UNIQUE KEY `packing_lists_delivery_id_unique` (`delivery_id`),
  KEY `packing_lists_order_id_foreign` (`order_id`),
  KEY `packing_lists_created_by_foreign` (`created_by`),
  CONSTRAINT `packing_lists_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `packing_lists_delivery_id_foreign` FOREIGN KEY (`delivery_id`) REFERENCES `deliveries` (`delivery_id`) ON DELETE CASCADE,
  CONSTRAINT `packing_lists_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `party_price_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `party_price_logs` (
  `party_price_log_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `party_type` varchar(10) NOT NULL,
  `party_id` bigint(20) unsigned NOT NULL,
  `material_id` bigint(20) unsigned NOT NULL DEFAULT 0,
  `product_type_id` bigint(20) unsigned NOT NULL DEFAULT 0,
  `stage_id` bigint(20) unsigned NOT NULL DEFAULT 0,
  `old_price` decimal(15,3) DEFAULT NULL,
  `price` decimal(15,3) DEFAULT NULL,
  `source` varchar(20) NOT NULL,
  `doc_id` bigint(20) unsigned DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`party_price_log_id`),
  KEY `party_price_logs_party_type_party_id_index` (`party_type`,`party_id`),
  KEY `party_price_logs_material_id_index` (`material_id`),
  KEY `party_price_logs_product_type_id_index` (`product_type_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `party_prices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `party_prices` (
  `party_price_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `party_type` varchar(10) NOT NULL,
  `party_id` bigint(20) unsigned NOT NULL,
  `material_id` bigint(20) unsigned NOT NULL DEFAULT 0,
  `product_type_id` bigint(20) unsigned NOT NULL DEFAULT 0,
  `stage_id` bigint(20) unsigned NOT NULL DEFAULT 0,
  `price` decimal(15,3) NOT NULL DEFAULT 0.000,
  `source` varchar(20) NOT NULL,
  `doc_id` bigint(20) unsigned DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`party_price_id`),
  UNIQUE KEY `party_prices_item_unique` (`party_type`,`party_id`,`material_id`,`product_type_id`,`stage_id`),
  KEY `party_prices_material_id_index` (`material_id`),
  KEY `party_prices_product_type_id_index` (`product_type_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `payroll_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payroll_items` (
  `payroll_item_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `payroll_id` bigint(20) unsigned NOT NULL,
  `employee_id` bigint(20) unsigned NOT NULL,
  `designation` varchar(255) DEFAULT NULL,
  `gross_salary` decimal(15,2) NOT NULL DEFAULT 0.00,
  `total_working_days` decimal(5,2) NOT NULL DEFAULT 0.00,
  `actual_working_days` decimal(5,2) NOT NULL DEFAULT 0.00,
  `paid_leaves` decimal(5,2) NOT NULL DEFAULT 0.00,
  `hours_per_day` decimal(5,2) DEFAULT NULL,
  `salary_for_days` decimal(15,2) NOT NULL DEFAULT 0.00,
  `overtime_hours` decimal(8,2) NOT NULL DEFAULT 0.00,
  `overtime_rate` decimal(15,2) NOT NULL DEFAULT 0.00,
  `overtime_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `late_minutes` int(10) unsigned NOT NULL DEFAULT 0,
  `late_hours` decimal(8,2) NOT NULL DEFAULT 0.00,
  `late_deduction` decimal(15,2) NOT NULL DEFAULT 0.00,
  `temp_advance` decimal(15,2) NOT NULL DEFAULT 0.00,
  `fixed_advance` decimal(15,2) NOT NULL DEFAULT 0.00,
  `fixed_advance_deduction` decimal(15,2) NOT NULL DEFAULT 0.00,
  `remaining_fixed_advance` decimal(15,2) NOT NULL DEFAULT 0.00,
  `net_salary` decimal(15,2) NOT NULL DEFAULT 0.00,
  `salary_transaction_id` bigint(20) unsigned DEFAULT NULL,
  `temp_deduction_transaction_id` bigint(20) unsigned DEFAULT NULL,
  `fixed_deduction_transaction_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`payroll_item_id`),
  UNIQUE KEY `payroll_items_payroll_id_employee_id_unique` (`payroll_id`,`employee_id`),
  CONSTRAINT `payroll_items_payroll_id_foreign` FOREIGN KEY (`payroll_id`) REFERENCES `payrolls` (`payroll_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `payrolls`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payrolls` (
  `payroll_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `month` varchar(7) NOT NULL,
  `total_working_days` decimal(5,2) NOT NULL,
  `overtime_multiplier` decimal(5,2) NOT NULL,
  `late_multiplier` decimal(5,2) NOT NULL,
  `payment_date` date NOT NULL,
  `bank_id` bigint(20) unsigned NOT NULL DEFAULT 0,
  `description` text DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`payroll_id`),
  UNIQUE KEY `payrolls_month_unique` (`month`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `permission_role`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `permission_role` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `role_id` bigint(255) unsigned NOT NULL,
  `permission_id` bigint(255) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `permissions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `personal_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `product_cost_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `product_cost_logs` (
  `product_cost_log_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint(20) unsigned NOT NULL,
  `head_id` bigint(20) unsigned NOT NULL,
  `table_name` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `table_id` bigint(20) unsigned NOT NULL DEFAULT 0,
  `old_amount` double DEFAULT NULL,
  `amount` double DEFAULT NULL,
  `action` varchar(10) NOT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`product_cost_log_id`),
  KEY `product_cost_logs_table_name_table_id_index` (`table_name`,`table_id`),
  KEY `product_cost_logs_product_id_index` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `product_costs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `product_costs` (
  `product_cost_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint(20) unsigned NOT NULL,
  `table_name` varchar(255) NOT NULL,
  `table_id` bigint(20) unsigned NOT NULL,
  `head_id` bigint(20) unsigned NOT NULL,
  `amount` double unsigned NOT NULL,
  `created_by` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`product_cost_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `product_materials`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `product_materials` (
  `product_material_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `product_type_id` bigint(20) NOT NULL,
  `material_id` bigint(20) DEFAULT NULL,
  `component_type` enum('material','product') NOT NULL DEFAULT 'material',
  `component_product_type_id` bigint(20) DEFAULT NULL,
  `quantity` double unsigned NOT NULL,
  `created_by` bigint(20) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`product_material_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `product_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `product_types` (
  `product_type_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint(20) unsigned NOT NULL,
  `size_id` bigint(20) unsigned NOT NULL COMMENT 'HeadID',
  `color_id` bigint(20) unsigned DEFAULT NULL COMMENT 'HeadID',
  `product_type_status` bigint(20) unsigned NOT NULL DEFAULT 1,
  `created_by` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`product_type_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `products` (
  `product_id` bigint(255) unsigned NOT NULL AUTO_INCREMENT,
  `category_id` bigint(20) unsigned NOT NULL,
  `material_id` varchar(255) NOT NULL,
  `stage_ids` varchar(255) NOT NULL,
  `article_no` varchar(255) NOT NULL,
  `hs_code` varchar(255) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `description` longtext DEFAULT NULL,
  `unit_id` bigint(20) unsigned NOT NULL,
  `product_status` bigint(20) unsigned NOT NULL DEFAULT 1,
  `created_by` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`product_id`),
  UNIQUE KEY `article_no` (`article_no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `purchase_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `purchase_items` (
  `purchase_item_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `purchase_id` bigint(20) unsigned NOT NULL,
  `product_type_id` bigint(20) unsigned NOT NULL,
  `product_stage_id` bigint(20) unsigned NOT NULL,
  `material_id` bigint(20) unsigned NOT NULL,
  `quantity` double unsigned NOT NULL,
  `price` double unsigned NOT NULL,
  `total` double unsigned NOT NULL COMMENT 'Quantity * Price',
  `created_by` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`purchase_item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `purchases`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `purchases` (
  `purchase_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `purchase_no` varchar(255) NOT NULL,
  `purchase_type` varchar(255) NOT NULL,
  `order_id` bigint(20) unsigned DEFAULT NULL,
  `vendor_id` bigint(20) unsigned NOT NULL,
  `description` longtext DEFAULT NULL,
  `purchase_date` date NOT NULL,
  `require_date` date NOT NULL,
  `created_by` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`purchase_id`),
  UNIQUE KEY `purchase_no` (`purchase_no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `receive_materials`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `receive_materials` (
  `receive_material_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `receive_id` bigint(20) unsigned NOT NULL,
  `purchase_item_id` bigint(20) unsigned NOT NULL,
  `batch_id` bigint(20) unsigned DEFAULT NULL,
  `quantity` double unsigned NOT NULL,
  `pending_qty` double unsigned NOT NULL,
  `approved_qty` double unsigned NOT NULL,
  `rejected_qty` double unsigned NOT NULL,
  `inspection_date` date NOT NULL,
  `created_by` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`receive_material_id`),
  KEY `receive_materials_batch_id_foreign` (`batch_id`),
  CONSTRAINT `receive_materials_batch_id_foreign` FOREIGN KEY (`batch_id`) REFERENCES `stock_batches` (`batch_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `receives`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `receives` (
  `receive_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `receive_no` varchar(255) NOT NULL,
  `purchase_id` bigint(20) unsigned DEFAULT NULL,
  `receive_date` date NOT NULL,
  `receive_status` bigint(20) unsigned NOT NULL DEFAULT 0 COMMENT '0 Pending, 1 Checked',
  `description` longtext DEFAULT NULL,
  `created_by` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`receive_id`),
  UNIQUE KEY `receive_no` (`receive_no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `return_materials`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `return_materials` (
  `return_material_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `return_id` bigint(20) unsigned NOT NULL,
  `receive_material_id` bigint(20) unsigned NOT NULL,
  `quantity` double unsigned NOT NULL,
  `stage_id` bigint(20) unsigned DEFAULT NULL,
  `rejected_stage_id` bigint(20) unsigned DEFAULT NULL,
  `remarks` longtext DEFAULT NULL,
  `created_by` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`return_material_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `returns`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `returns` (
  `return_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `receive_id` bigint(20) unsigned NOT NULL,
  `return_no` varchar(255) NOT NULL,
  `return_date` date NOT NULL,
  `description` longtext DEFAULT NULL,
  `created_by` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`return_id`),
  UNIQUE KEY `return_no` (`return_no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `roles` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `salaries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `salaries` (
  `salary_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` bigint(20) unsigned NOT NULL,
  `amount` bigint(20) unsigned NOT NULL,
  `status` bigint(20) unsigned NOT NULL DEFAULT 1,
  `created_by` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`salary_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `stock_batch_transfers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stock_batch_transfers` (
  `transfer_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `from_batch_id` bigint(20) unsigned NOT NULL,
  `to_batch_id` bigint(20) unsigned NOT NULL,
  `stage_id` bigint(20) unsigned NOT NULL,
  `quantity` double unsigned NOT NULL,
  `transfer_date` date NOT NULL,
  `description` longtext DEFAULT NULL,
  `created_by` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`transfer_id`),
  KEY `stock_batch_transfers_from_batch_id_index` (`from_batch_id`),
  KEY `stock_batch_transfers_to_batch_id_index` (`to_batch_id`),
  CONSTRAINT `stock_batch_transfers_from_batch_id_foreign` FOREIGN KEY (`from_batch_id`) REFERENCES `stock_batches` (`batch_id`),
  CONSTRAINT `stock_batch_transfers_to_batch_id_foreign` FOREIGN KEY (`to_batch_id`) REFERENCES `stock_batches` (`batch_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `stock_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stock_batches` (
  `batch_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `batch_no` varchar(255) NOT NULL,
  `product_type_id` bigint(20) unsigned NOT NULL,
  `source_type` varchar(20) NOT NULL,
  `source_id` bigint(20) unsigned DEFAULT NULL,
  `order_id` bigint(20) unsigned DEFAULT NULL,
  `parent_batch_id` bigint(20) unsigned DEFAULT NULL,
  `description` longtext DEFAULT NULL,
  `created_by` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`batch_id`),
  UNIQUE KEY `stock_batches_batch_no_unique` (`batch_no`),
  UNIQUE KEY `stock_batches_source_type_source_id_product_type_id_unique` (`source_type`,`source_id`,`product_type_id`),
  KEY `stock_batches_product_type_id_order_id_index` (`product_type_id`,`order_id`),
  KEY `stock_batches_order_id_foreign` (`order_id`),
  KEY `stock_batches_parent_batch_id_foreign` (`parent_batch_id`),
  CONSTRAINT `stock_batches_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`),
  CONSTRAINT `stock_batches_parent_batch_id_foreign` FOREIGN KEY (`parent_batch_id`) REFERENCES `stock_batches` (`batch_id`),
  CONSTRAINT `stock_batches_product_type_id_foreign` FOREIGN KEY (`product_type_id`) REFERENCES `product_types` (`product_type_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `stock_item_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stock_item_batches` (
  `stock_item_batch_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `stock_item_id` bigint(20) unsigned NOT NULL,
  `batch_id` bigint(20) unsigned NOT NULL,
  `quantity` double unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`stock_item_batch_id`),
  UNIQUE KEY `stock_item_batches_stock_item_id_batch_id_unique` (`stock_item_id`,`batch_id`),
  KEY `stock_item_batches_batch_id_index` (`batch_id`),
  CONSTRAINT `stock_item_batches_batch_id_foreign` FOREIGN KEY (`batch_id`) REFERENCES `stock_batches` (`batch_id`),
  CONSTRAINT `stock_item_batches_stock_item_id_foreign` FOREIGN KEY (`stock_item_id`) REFERENCES `stock_items` (`stock_item_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `stock_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stock_items` (
  `stock_item_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `stock_id` bigint(20) unsigned NOT NULL,
  `product_type_id` bigint(20) unsigned NOT NULL,
  `material_id` bigint(20) unsigned NOT NULL,
  `quantity` double unsigned NOT NULL,
  `stage_id` bigint(20) unsigned NOT NULL,
  `rejected_stage_id` bigint(20) unsigned DEFAULT NULL,
  `component_product_type_id` bigint(20) unsigned DEFAULT NULL,
  `ptc_virtual` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `order_id` bigint(20) unsigned DEFAULT NULL,
  `work_logs` varchar(255) NOT NULL,
  `work_wages` varchar(255) DEFAULT '0',
  `created_by` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`stock_item_id`),
  KEY `stock_items_order_id_foreign` (`order_id`),
  CONSTRAINT `stock_items_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `stocks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stocks` (
  `stock_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `issue_id` bigint(20) unsigned DEFAULT NULL,
  `ptc_id` bigint(20) unsigned DEFAULT NULL COMMENT 'Links to parent PTC master record',
  `current_stage_id` bigint(20) unsigned DEFAULT NULL COMMENT 'Current processing stage for PTC',
  `start_stage_id` bigint(20) unsigned DEFAULT NULL,
  `start_from` varchar(10) DEFAULT NULL,
  `next_stage_id` bigint(20) unsigned DEFAULT NULL COMMENT 'Next stage in PTC sequence',
  `end_stage_id` bigint(20) unsigned DEFAULT NULL,
  `is_ptc_master` tinyint(4) NOT NULL DEFAULT 0 COMMENT '1 if this is the PTC master record',
  `issue_for` bigint(20) unsigned DEFAULT NULL,
  `stock_no` varchar(255) NOT NULL,
  `order_id` bigint(20) unsigned NOT NULL,
  `source_id` bigint(20) unsigned DEFAULT NULL,
  `machine_id` bigint(20) unsigned DEFAULT NULL,
  `table_name` varchar(255) NOT NULL,
  `employee_id` bigint(20) unsigned NOT NULL,
  `stock_type` bigint(20) unsigned NOT NULL DEFAULT 2 COMMENT 'Stock In/Out',
  `stock_date` date NOT NULL,
  `stock_status` bigint(20) unsigned NOT NULL DEFAULT 1 COMMENT 'NotReceived#0, CompletelyReceived#1, PartiallyReceived#2, Delivery#3, MachineMaterial#4,\r\nMaterialProcessing#5',
  `description` longtext DEFAULT NULL,
  `created_by` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`stock_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `transactions` (
  `transaction_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `transfer_pair_id` bigint(20) unsigned DEFAULT NULL,
  `transaction_to` varchar(255) NOT NULL COMMENT 'Vendor, Employee, Expense',
  `transaction_type` varchar(255) NOT NULL COMMENT 'Salary, Advance etc',
  `advance_type` varchar(10) DEFAULT NULL,
  `bank_id` bigint(20) unsigned NOT NULL COMMENT 'Our Bank',
  `order_id` bigint(20) unsigned DEFAULT NULL COMMENT 'Purchase / Order / Delivery',
  `payee_id` bigint(20) unsigned DEFAULT NULL COMMENT 'Vendor, Employee, Customers, Expense Head',
  `payee_bank_id` bigint(20) unsigned DEFAULT NULL,
  `expense_head_id` bigint(20) unsigned DEFAULT NULL,
  `voucher_id` bigint(20) unsigned DEFAULT NULL,
  `debit` decimal(15,2) DEFAULT NULL,
  `credit` decimal(15,2) DEFAULT NULL,
  `cc_amount` decimal(15,2) DEFAULT NULL COMMENT 'Customer currency amount (for dual-currency payments)',
  `fb_charges` decimal(15,2) DEFAULT NULL COMMENT 'Foreign bank charges',
  `db_charges` decimal(15,2) DEFAULT NULL COMMENT 'Domestic bank charges (in local currency)',
  `ledger_flag` tinyint(4) NOT NULL DEFAULT 1 COMMENT '1=affects cash ledger, 0=accounting entry only (general voucher)',
  `transaction_date` date NOT NULL,
  `description` longtext DEFAULT NULL,
  `created_by` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`transaction_id`),
  KEY `transactions_expense_head_id_foreign` (`expense_head_id`),
  KEY `transactions_voucher_id_index` (`voucher_id`),
  CONSTRAINT `transactions_expense_head_id_foreign` FOREIGN KEY (`expense_head_id`) REFERENCES `heads` (`head_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `role_id` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `vendors`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `vendors` (
  `vendor_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `vendor_type_id` varchar(255) NOT NULL COMMENT 'HeadID',
  `vendor_no` varchar(255) NOT NULL,
  `vendor_type` bigint(20) unsigned NOT NULL COMMENT '\r\nWorker / Not',
  `material_id` text NOT NULL,
  `product_id` text DEFAULT NULL COMMENT 'Associated product IDs (pipe separated)',
  `name` varchar(255) NOT NULL,
  `fname` varchar(255) NOT NULL,
  `cperson` varchar(255) DEFAULT NULL,
  `phone1` varchar(255) NOT NULL,
  `phone2` varchar(255) DEFAULT NULL,
  `city_id` bigint(20) unsigned NOT NULL COMMENT 'HeadID',
  `address` longtext NOT NULL,
  `description` longtext DEFAULT NULL,
  `created_by` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`vendor_id`),
  UNIQUE KEY `vendor_no` (`vendor_no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `work_holidays`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `work_holidays` (
  `work_holiday_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `date_from` date NOT NULL,
  `date_to` date NOT NULL,
  `description` longtext DEFAULT NULL,
  `created_by` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`work_holiday_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `work_times`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `work_times` (
  `work_time_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `time_from` time NOT NULL,
  `time_to` time NOT NULL,
  `date_from` date NOT NULL,
  `date_to` date NOT NULL,
  `grace_time` bigint(20) unsigned NOT NULL,
  `description` longtext DEFAULT NULL,
  `created_by` bigint(20) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`work_time_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (1,'2014_10_12_000000_create_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (2,'2014_10_12_100000_create_password_reset_tokens_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (3,'2019_08_19_000000_create_failed_jobs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (4,'2019_12_14_000001_create_personal_access_tokens_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (5,'2014_10_12_000000_create_roles_table',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (6,'2025_01_11_150952_create_activity_logs_table',3);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (7,'2025_01_12_063408_create_activity_log_table',4);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (8,'2025_01_12_063409_add_event_column_to_activity_log_table',4);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (9,'2025_01_12_063410_add_batch_uuid_column_to_activity_log_table',4);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (10,'2025_04_07_162120_create_roles_table',5);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (11,'2025_04_07_162140_create_permissions_table',5);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (12,'2025_04_07_162213_create_permission_role_table',5);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (13,'2025_09_13_053806_create_delivery_returns_table',5);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (14,'2025_09_13_053846_create_delivery_return_items_table',5);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (15,'2025_09_13_054353_add_payment_fields_to_transactions_table',5);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (16,'2025_01_19_000001_add_order_fields_to_orders_table',6);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (17,'2025_01_19_000002_add_bank_fields_to_banks_table',6);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (18,'2025_01_19_000003_add_product_id_to_vendors_table',6);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (19,'2025_09_26_000001_add_hs_code_to_products_table',7);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (20,'2025_09_26_000003_enhance_company_table',7);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (21,'2025_10_05_000001_add_commercial_invoice_fields_to_deliveries_table',7);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (22,'2025_11_16_000001_refactor_transactions_table_schema',8);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (23,'2025_11_25_000001_create_assets_table',8);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (24,'2025_11_28_161542_add_ledger_flag_to_transactions_table',8);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (25,'2025_11_30_000001_add_ptc_columns_to_stocks_table',9);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (26,'2025_12_09_092651_add_product_component_to_product_materials_table',10);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (27,'2025_12_09_154739_add_component_product_type_id_to_stock_items_table',10);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (28,'2025_12_11_000001_add_transaction_fields_to_assets_table',10);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (29,'2025_12_12_000001_create_packing_lists_table',10);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (30,'2025_12_12_000002_create_packing_cartons_table',10);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (31,'2025_12_12_000003_create_packing_carton_items_table',10);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (32,'2025_12_18_000004_add_rex_and_statement_of_origin_to_company_table',11);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (33,'2025_12_18_000005_move_tax_fields_from_deliveries_to_company_table',11);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (34,'2025_12_19_184711_update_material_and_product_id_columns_in_vendors_table',12);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (35,'2025_12_23_000001_simplify_currency_handling',13);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (36,'2025_12_24_073750_add_delivery_no_and_delivery_date_to_deliveries_table',13);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (37,'2025_12_29_000001_add_box_fields_to_packing_cartons_table',14);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (38,'2025_12_29_000001_rename_fees_expenses_and_add_db_charges',14);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (39,'2025_12_29_000002_add_pallet_fields_to_packing_lists_table',14);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (40,'2025_12_30_000001_add_port_no_to_customers_table',15);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (41,'2026_01_01_000001_add_personal_professional_info_to_employees_table',16);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (42,'2026_02_17_160511_create_sessions_table',17);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (43,'2026_02_17_160520_create_cache_table',17);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (44,'2026_02_17_160528_create_jobs_table',17);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (45,'2026_03_27_remove_stock_no_unique_constraint',17);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (46,'2026_06_16_000001_add_transfer_pair_id_to_transactions_table',18);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (47,'2026_06_16_000002_add_source_id_to_stocks_table',18);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (48,'2026_09_30_000001_add_ptc_virtual_to_stock_items_table',19);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (49,'2026_09_30_000002_add_end_stage_id_to_stocks_table',19);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (50,'2026_10_12_000001_add_start_stage_id_to_stocks_table',20);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (51,'2026_10_14_000001_create_stock_batches_table',20);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (52,'2026_10_14_000002_create_stock_item_batches_table',20);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (53,'2026_10_14_000003_create_stock_batch_transfers_table',20);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (54,'2026_11_10_000001_add_freight_charges_and_company_fi_no',21);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (55,'2026_11_12_000001_add_start_from_to_stocks_table',21);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (56,'2026_11_13_000001_create_payroll_tables',21);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (57,'2026_11_14_000001_remove_packing_list_permissions',21);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (58,'2026_11_15_000001_add_attendance_db_path_to_company',21);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (59,'2026_11_15_000002_add_expense_head_id_to_transactions',21);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (60,'2026_11_15_000003_add_missing_permissions',21);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (61,'2026_11_16_000001_tidy_role_permissions',21);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (62,'2026_11_18_000001_create_material_price_logs_table',21);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (63,'2026_11_19_000001_vendor_voucher_payments_payroll_hours_department_times',21);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (64,'2026_11_21_000001_move_payroll_hours_per_day_to_items',22);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (65,'2026_11_21_000002_add_kind_and_money_link_to_assets',22);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (66,'2026_11_21_000003_add_liabilities_permissions',22);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (68,'2026_11_21_000004_create_party_prices_and_wage_logs',23);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (69,'2026_11_22_000001_rejected_stage_returns_and_stock_adjustments',24);
