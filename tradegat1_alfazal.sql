-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: 140.82.9.117:3306
-- Generation Time: Nov 10, 2026 at 06:40 AM
-- Server version: 11.4.13-MariaDB-ubu2404
-- PHP Version: 8.3.36

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `tradegat1_alfazal`
--

-- --------------------------------------------------------

--
-- Table structure for table `accounts`
--

CREATE TABLE `accounts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `account_code` varchar(191) NOT NULL,
  `account_name` varchar(191) NOT NULL,
  `account_type` enum('Customer','Supplier','Bank','Expense','Income','Labour') NOT NULL,
  `opening_balance` decimal(15,2) NOT NULL DEFAULT 0.00,
  `status` enum('Active','Inactive') NOT NULL DEFAULT 'Active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `accounts`
--

INSERT INTO `accounts` (`id`, `account_code`, `account_name`, `account_type`, `opening_balance`, `status`) VALUES
(1, 'BANK:14810101049190', 'Meezan Bank Ltd', 'Bank', 0.00, 'Active'),
(2, 'BANK:1702010013456', 'Hbl Bank Lte', 'Bank', 0.00, 'Active'),
(3, 'BANK:Cash', 'Cash', 'Bank', 0.00, 'Active');

-- --------------------------------------------------------

--
-- Table structure for table `bank_accounts`
--

CREATE TABLE `bank_accounts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `bankAccount` varchar(191) NOT NULL,
  `bankName` varchar(191) NOT NULL,
  `bankAddress` varchar(191) DEFAULT NULL,
  `branchCode` varchar(191) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `bank_accounts`
--

INSERT INTO `bank_accounts` (`id`, `bankAccount`, `bankName`, `bankAddress`, `branchCode`) VALUES
(1, '14810101049190', 'Meezan Bank Ltd', 'Block No 12 Sargodha A', '1481'),
(2, '1702010013456', 'Hbl Bank Lte', 'Katchery Bazar Sargodha', '1702'),
(4, 'Cash', 'Cash', 'Alfazal', '0');

-- --------------------------------------------------------

--
-- Table structure for table `boms`
--

CREATE TABLE `boms` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `productCode` varchar(191) NOT NULL,
  `batch_size` decimal(18,6) NOT NULL DEFAULT 1.000000,
  `batch_uom` varchar(20) NOT NULL DEFAULT 'piece',
  `wastage_percent` decimal(8,4) NOT NULL DEFAULT 0.0000,
  `status` varchar(16) NOT NULL DEFAULT 'Draft',
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bom_items`
--

CREATE TABLE `bom_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `bom_id` bigint(20) UNSIGNED NOT NULL,
  `materialCode` varchar(191) NOT NULL,
  `uom_id` bigint(20) UNSIGNED DEFAULT NULL,
  `qty_per_batch` decimal(18,6) NOT NULL,
  `per_dozen` decimal(18,6) NOT NULL DEFAULT 0.000000,
  `use_qty` decimal(18,6) NOT NULL DEFAULT 1.000000,
  `unit_rate` decimal(18,4) DEFAULT NULL,
  `wastage_percent` decimal(8,4) NOT NULL DEFAULT 0.0000,
  `note` varchar(191) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bom_labors`
--

CREATE TABLE `bom_labors` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `bom_id` bigint(20) UNSIGNED NOT NULL,
  `operation_name` varchar(191) NOT NULL,
  `time_minutes` decimal(18,6) NOT NULL DEFAULT 0.000000,
  `rate_per_minute` decimal(18,6) NOT NULL DEFAULT 0.000000,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bom_labour_works`
--

CREATE TABLE `bom_labour_works` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `work_name` varchar(100) NOT NULL,
  `rate` decimal(18,4) NOT NULL DEFAULT 0.0000,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bom_materials`
--

CREATE TABLE `bom_materials` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `material_code` varchar(50) NOT NULL,
  `material_name` varchar(100) NOT NULL,
  `packing` varchar(100) DEFAULT NULL,
  `rate` decimal(18,4) NOT NULL DEFAULT 0.0000,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bom_overheads`
--

CREATE TABLE `bom_overheads` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `bom_id` bigint(20) UNSIGNED NOT NULL,
  `overhead_name` varchar(191) NOT NULL,
  `type` enum('Fixed','Variable') NOT NULL DEFAULT 'Fixed',
  `allocation_base` enum('PerBatch','PerUnit','PerMinute','PercentMaterial') NOT NULL DEFAULT 'PerBatch',
  `value` decimal(18,6) NOT NULL DEFAULT 0.000000,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(191) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cache`
--

INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES
('tradegate_cache_admin384@tradegate.com|154.192.180.251', 'i:1;', 1794280740),
('tradegate_cache_admin384@tradegate.com|154.192.180.251:timer', 'i:1794280740;', 1794280740),
('tradegate_cache_admin384@tradegate.local|154.192.180.251', 'i:1;', 1794280759),
('tradegate_cache_admin384@tradegate.local|154.192.180.251:timer', 'i:1794280759;', 1794280759),
('tradegate_cache_sales_summary_overall_e218c0524bda5895ac6065bf5b027bc9', 'a:10:{s:13:\"invoice_count\";i:2;s:11:\"total_gross\";d:192900;s:9:\"total_qty\";d:160;s:14:\"total_discount\";d:51210;s:9:\"net_sales\";d:141690;s:10:\"cash_sales\";d:0;s:12:\"credit_sales\";d:141690;s:10:\"total_paid\";d:0;s:17:\"total_outstanding\";d:141690;s:16:\"invoice_discount\";d:0;}', 1794250136);

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(191) NOT NULL,
  `owner` varchar(191) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `client_url_restrictions`
--

CREATE TABLE `client_url_restrictions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `pattern` varchar(191) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `colour`
--

CREATE TABLE `colour` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `colourName` varchar(191) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `colour`
--

INSERT INTO `colour` (`id`, `colourName`) VALUES
(1, 'WHITE');

-- --------------------------------------------------------

--
-- Table structure for table `customerareas`
--

CREATE TABLE `customerareas` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `areaCode` varchar(50) NOT NULL,
  `areaName` varchar(150) NOT NULL,
  `ajentName` varchar(150) DEFAULT NULL,
  `balance` decimal(15,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `customerareas`
--

INSERT INTO `customerareas` (`id`, `areaCode`, `areaName`, `ajentName`, `balance`) VALUES
(1, 'BAN', 'Banu', 'Imran Haider', 0.00),
(2, 'MNG', 'Mengora Sawat', 'Amjad Saleem', 0.00),
(3, 'MGP', 'Magharbi Punjab', 'Usman Shahid', 0.00),
(4, 'SUPLLIER', 'Supplier Party', NULL, 0.00);

-- --------------------------------------------------------

--
-- Table structure for table `customerledgers`
--

CREATE TABLE `customerledgers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `statuse` varchar(191) DEFAULT NULL,
  `customerDate` date NOT NULL,
  `invoiceNumber` varchar(191) DEFAULT NULL,
  `customerArea` varchar(191) NOT NULL,
  `customerCode` varchar(191) NOT NULL,
  `description` varchar(191) NOT NULL,
  `credit` decimal(15,2) NOT NULL DEFAULT 0.00,
  `debit` decimal(15,2) NOT NULL DEFAULT 0.00,
  `category` varchar(191) DEFAULT NULL,
  `accountType` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `customerledgers`
--

INSERT INTO `customerledgers` (`id`, `statuse`, `customerDate`, `invoiceNumber`, `customerArea`, `customerCode`, `description`, `credit`, `debit`, `category`, `accountType`) VALUES
(1, '0', '2026-10-26', NULL, 'MNG', 'DURANI11', 'Pre Balance', 0.00, 10000.00, 'AccountTransaction', NULL),
(2, '0', '2026-10-26', NULL, 'MNG', 'KAREEM', 'Pre Balance', 0.00, 10000.00, 'AccountTransaction', NULL),
(3, '0', '2026-10-26', NULL, 'MNG', 'CUST001', 'Pre Balance', 0.00, 10000.00, 'AccountTransaction', NULL),
(4, '0', '2026-10-26', NULL, 'BAN', 'IMR', 'Pre Balance', 0.00, 10000.00, 'AccountTransaction', NULL),
(5, '0', '2026-10-26', NULL, 'BAN', 'NZR', 'Pre Balance', 0.00, 10000.00, 'AccountTransaction', NULL),
(6, '0', '2026-10-26', NULL, 'BAN', 'SHB', 'Pre Balance', 0.00, 10000.00, 'AccountTransaction', NULL),
(7, '0', '2026-10-26', NULL, 'MGP', 'KASHIF', 'Pre Balance', 0.00, 10000.00, 'AccountTransaction', NULL),
(8, '0', '2026-10-26', NULL, 'MGP', 'SHOAIB', 'Pre Balance', 0.00, 10000.00, 'AccountTransaction', NULL),
(9, '0', '2026-10-26', NULL, 'MGP', 'TAHIR', 'Pre Balance', 0.00, 10000.00, 'AccountTransaction', NULL),
(10, '0', '2026-10-26', NULL, 'SUPLLIER', 'FAISAL', 'Pre Balance', 10000.00, 0.00, 'AccountTransaction', NULL),
(11, '0', '2026-10-26', NULL, 'SUPLLIER', 'HASSAN', 'Pre Balance', 10000.00, 0.00, 'AccountTransaction', NULL),
(12, '0', '2026-10-26', NULL, 'SUPLLIER', 'HASSAN', 'Cash Py', 0.00, 1000.00, 'AccountTransaction', NULL),
(13, '0', '2026-10-27', NULL, 'MNG', 'DURANI11', 'Online Transfer (transfer To Faisal)', 2000.00, 0.00, 'Transfer', NULL),
(14, '0', '2026-10-27', NULL, 'SUPLLIER', 'FAISAL', 'Online Transfer (transfer From Durani11)', 0.00, 2000.00, 'Transfer', NULL),
(18, '1', '2026-10-27', NULL, 'BAN', 'IMR', 'Cash (chq 65146569 — Mcb Bank Karachi 001)', 1000.00, 0.00, 'Cheque', NULL),
(19, '1', '2026-10-27', NULL, 'BAN', 'NZR', 'Cah Payment (chq 6548994 — Hbl Bank Ltd)', 1000.00, 0.00, 'Cheque', NULL),
(20, '1', '2026-10-27', NULL, 'BAN', 'SHB', 'Cash Paymen (chq 6456469 — Ubl Bank Ltd)', 1000.00, 0.00, 'Cheque', NULL),
(22, '0', '2026-10-28', 'ST-0001', 'SUPLLIER', 'FAISAL', 'Supplier Purchase St-0001 — Jambo Holder Folwer B-22', 75000.00, 0.00, 'SupplierPurchase', NULL),
(23, '0', '2026-10-28', 'ST-0002', 'SUPLLIER', 'FAISAL', 'Supplier Purchase St-0002 — Jumbo Holder Flover E-27', 75000.00, 0.00, 'SupplierPurchase', NULL),
(25, '0', '2026-10-28', 'ST-0003', 'SUPLLIER', 'FAISAL', 'Cash (pay) — Cash By Hand', 0.00, 10000.00, 'SupplierCash', NULL),
(26, '0', '2026-10-28', 'CHQ-4', 'SUPLLIER', 'FAISAL', 'Cheque Pay Chq 6456469 — Ubl Bank Ltd | Worker: Khuram Shahid', 0.00, 1000.00, 'ChequeTransfer', NULL),
(27, '0', '2026-10-28', 'INV-00001', 'MNG', 'KAREEM', 'Sale Invoice Inv-00001', 0.00, 611500.00, 'Sale Invoice', NULL),
(28, '0', '2026-10-29', 'INV-00002', 'MGP', 'KASHIF', 'Sale Invoice Inv-00002', 0.00, 44390.50, 'Sale Invoice', NULL),
(29, '0', '2026-10-29', 'PINV-00001', 'SUPLLIER', 'HASSAN', 'Purchase Invoice Pinv-00001', 900000.00, 0.00, 'Purchase', NULL),
(30, '0', '2026-10-29', 'PINV-00001', 'SUPLLIER', 'TAHIR', 'Transport Pinv-00001 | Vehicle 6546549 | Dispatch 6658 | Weight 2500 | Rate 15.00 | Amount 37,500.00', 37500.00, 0.00, 'Transport', NULL),
(31, '0', '2026-10-29', 'PINV-00002', 'SUPLLIER', 'HASSAN', 'Purchase Invoice Pinv-00002', 660000.00, 0.00, 'Purchase', NULL),
(32, '0', '2026-10-29', NULL, 'MGP', 'KASHIF', 'Cash Paymet', 15000.00, 0.00, 'Recovery', NULL),
(33, '0', '2026-10-29', NULL, 'MGP', 'SHOAIB', 'Cash Paymen', 8000.00, 0.00, 'Recovery', NULL),
(34, '0', '2026-10-29', NULL, 'MGP', 'KASHIF', 'Discount', 390.50, 0.00, 'Discount', NULL),
(35, '0', '2026-10-29', NULL, 'MGP', 'KASHIF', 'Bilty', 1000.00, 0.00, 'Bilty', NULL),
(36, '0', '2026-10-29', NULL, 'MGP', 'KASHIF', 'Cash', 8000.00, 0.00, 'Recovery', NULL),
(37, '0', '2026-10-29', 'SR-00001', 'BAN', 'IMR', 'Sale Return Sr-00001', 153420.00, 0.00, 'Sale Return', NULL),
(39, '0', '2026-10-29', 'INV-00003', 'MGP', 'KASHIF', 'Sale Invoice Inv-00003', 0.00, 17325.00, 'Sale Invoice', NULL),
(40, '0', '2026-10-29', 'SR-00002', 'SUPLLIER', 'HASSAN', 'Sale Return Sr-00002', 178830.00, 0.00, 'Sale Return', NULL),
(42, '0', '2026-10-30', 'PR-00001', 'SUPLLIER', 'HASSAN', 'Purchase Return Pr-00001', 0.00, 136150.00, 'Purchase Return', NULL),
(43, '0', '2026-11-06', 'INV-00004', 'SUPLLIER', 'HASSAN', 'Sale Invoice Inv-00004', 0.00, 22200.00, 'Sale Invoice', NULL),
(44, '0', '2026-11-06', NULL, 'MGP', 'KASHIF', 'Cash', 15000.00, 0.00, 'Recovery', NULL),
(45, '0', '2026-11-06', 'INV-00005', 'SUPLLIER', 'TAHIR', 'Sale Invoice Inv-00005', 0.00, 119490.00, 'Sale Invoice', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `customer_catagory`
--

CREATE TABLE `customer_catagory` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `catagoryName` varchar(191) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `customer_cheques`
--

CREATE TABLE `customer_cheques` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `customer_code` varchar(50) NOT NULL,
  `customer_area` varchar(50) DEFAULT NULL,
  `bank_name` varchar(150) NOT NULL,
  `cheque_no` varchar(100) NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `cheque_date` date NOT NULL,
  `due_date` date NOT NULL,
  `original_due_date` date DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'Pending',
  `cleared_at` date DEFAULT NULL,
  `ledger_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `worker_id` bigint(20) UNSIGNED DEFAULT NULL,
  `worker_code` varchar(50) DEFAULT NULL,
  `worker_name` varchar(150) DEFAULT NULL,
  `payee_type` varchar(20) DEFAULT NULL,
  `payee_code` varchar(100) DEFAULT NULL,
  `payee_name` varchar(150) DEFAULT NULL,
  `pay_remark` varchar(255) DEFAULT NULL,
  `paid_at` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `customer_cheques`
--

INSERT INTO `customer_cheques` (`id`, `customer_code`, `customer_area`, `bank_name`, `cheque_no`, `amount`, `cheque_date`, `due_date`, `original_due_date`, `description`, `status`, `cleared_at`, `ledger_id`, `created_at`, `updated_at`, `worker_id`, `worker_code`, `worker_name`, `payee_type`, `payee_code`, `payee_name`, `pay_remark`, `paid_at`) VALUES
(2, 'IMR', 'BAN', 'MCB bank karachi 001', '65146569', 1000.00, '2026-10-27', '2026-10-27', '2026-10-27', 'Cash', 'Cleared', NULL, 18, '2026-10-27 15:06:41', '2026-10-27 15:25:42', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(3, 'NZR', 'BAN', 'HBL Bank ltd', '6548994', 1000.00, '2026-10-27', '2026-10-28', '2026-10-28', 'cah payment', 'Pending', NULL, 19, '2026-10-27 15:07:31', '2026-10-28 19:46:17', 6, 'KURAM', 'Khuram Shahid', NULL, NULL, NULL, NULL, NULL),
(4, 'SHB', 'BAN', 'UBL bank ltd', '6456469', 1000.00, '2026-10-27', '2026-10-27', '2026-10-27', 'cash paymen', 'Cleared', '2026-10-28', 20, '2026-10-27 15:08:32', '2026-10-28 19:59:26', 6, 'KURAM', 'Khuram Shahid', 'party', 'FAISAL', 'Faisal | Faisal Holder Wala', NULL, '2026-10-28');

-- --------------------------------------------------------

--
-- Table structure for table `customer_product_rates`
--

CREATE TABLE `customer_product_rates` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `customerCode` varchar(191) NOT NULL,
  `productCode` varchar(191) NOT NULL,
  `work_name` varchar(100) DEFAULT NULL,
  `variant_id` bigint(20) UNSIGNED DEFAULT NULL,
  `variant_name` varchar(191) DEFAULT NULL,
  `variant_value` varchar(191) DEFAULT NULL,
  `effective_from` date NOT NULL DEFAULT '2025-11-21',
  `specialRate` decimal(15,2) NOT NULL,
  `rateType` varchar(20) NOT NULL DEFAULT 'Sale',
  `discountPercent` decimal(8,2) NOT NULL DEFAULT 0.00,
  `remarks` varchar(191) DEFAULT NULL,
  `status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `customer_product_rates`
--

INSERT INTO `customer_product_rates` (`id`, `customerCode`, `productCode`, `work_name`, `variant_id`, `variant_name`, `variant_value`, `effective_from`, `specialRate`, `rateType`, `discountPercent`, `remarks`, `status`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'FAISAL', '069P', NULL, NULL, NULL, NULL, '2026-10-28', 750.00, 'Purchase', 0.00, '', 'Active', 1, '2026-10-28 18:04:06', '2026-10-28 18:04:06'),
(2, 'FAISAL', '070P', NULL, NULL, NULL, NULL, '2026-10-28', 750.00, 'Purchase', 0.00, '', 'Active', 1, '2026-10-28 18:14:06', '2026-10-28 18:14:06'),
(3, 'HASSAN', '069P', NULL, NULL, NULL, NULL, '2026-10-29', 750.00, 'Purchase', 0.00, '', 'Inactive', 0, '2026-10-29 19:05:50', '2026-10-29 19:13:03'),
(4, 'HASSAN', 'WB-00S', NULL, NULL, NULL, NULL, '2026-10-29', 9000.00, 'Purchase', 0.00, '', 'Active', 1, '2026-10-29 19:13:15', '2026-10-29 19:13:15'),
(5, 'HASSAN', '034P', NULL, NULL, NULL, NULL, '2026-10-29', 350.00, 'Purchase', 0.00, '', 'Active', 1, '2026-10-29 19:24:55', '2026-10-29 19:24:55');

-- --------------------------------------------------------

--
-- Table structure for table `expenses`
--

CREATE TABLE `expenses` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `expenseCode` varchar(191) NOT NULL,
  `expenseName` varchar(191) NOT NULL,
  `expenseCategory` varchar(191) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `expenseDate` date DEFAULT NULL,
  `paymentMethod` varchar(191) DEFAULT NULL,
  `status` varchar(191) NOT NULL DEFAULT 'Active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `expense_ledger`
--

CREATE TABLE `expense_ledger` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `entryDate` date NOT NULL,
  `expenseCode` varchar(191) NOT NULL,
  `expenseCategory` varchar(191) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `paymentMethod` varchar(191) DEFAULT NULL,
  `accountCode` varchar(191) DEFAULT NULL,
  `debit` decimal(15,2) NOT NULL DEFAULT 0.00,
  `credit` decimal(15,2) NOT NULL DEFAULT 0.00,
  `referenceNo` varchar(191) DEFAULT NULL,
  `category` varchar(191) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(191) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `fitting_labours`
--

CREATE TABLE `fitting_labours` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `txn_no` varchar(50) NOT NULL,
  `labour_id` bigint(20) UNSIGNED NOT NULL,
  `labour_code` varchar(50) NOT NULL,
  `labour_name` varchar(255) DEFAULT NULL,
  `txn_type` varchar(20) NOT NULL,
  `entry_type` varchar(20) DEFAULT NULL,
  `txn_date` date NOT NULL,
  `total_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `remarks` varchar(255) DEFAULT NULL,
  `labour_account_transaction_id` bigint(20) UNSIGNED DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'Active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `fitting_labour_items`
--

CREATE TABLE `fitting_labour_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `fitting_labour_id` bigint(20) UNSIGNED NOT NULL,
  `txn_no` varchar(50) NOT NULL,
  `product_code` varchar(50) NOT NULL,
  `product_name` varchar(255) DEFAULT NULL,
  `work_name` varchar(100) DEFAULT NULL,
  `variant_id` bigint(20) UNSIGNED DEFAULT NULL,
  `variant_name` varchar(100) DEFAULT NULL,
  `variant_value` varchar(100) DEFAULT NULL,
  `qty` decimal(15,2) NOT NULL DEFAULT 0.00,
  `rate` decimal(15,2) NOT NULL DEFAULT 0.00,
  `amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `fitting_product_works`
--

CREATE TABLE `fitting_product_works` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `product_code` varchar(50) NOT NULL,
  `product_name` varchar(255) DEFAULT NULL,
  `work_name` varchar(100) NOT NULL,
  `rate` decimal(15,2) NOT NULL DEFAULT 0.00,
  `status` varchar(20) NOT NULL DEFAULT 'Active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `fitting_work_stocks`
--

CREATE TABLE `fitting_work_stocks` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `product_code` varchar(50) NOT NULL,
  `product_name` varchar(255) DEFAULT NULL,
  `work_name` varchar(100) NOT NULL,
  `unit_name` varchar(50) DEFAULT NULL,
  `qty` decimal(15,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `guds`
--

CREATE TABLE `guds` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `gudsName` varchar(191) NOT NULL,
  `gudsExpanse` decimal(10,2) NOT NULL DEFAULT 0.00,
  `gudsLoader` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `guds`
--

INSERT INTO `guds` (`id`, `gudsName`, `gudsExpanse`, `gudsLoader`, `status`, `created_at`, `updated_at`) VALUES
(1, 'MUSLIM GUDS', 150.00, 50.00, 'active', '2026-10-29 19:11:03', '2026-10-29 19:11:03');

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(191) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(191) NOT NULL,
  `name` varchar(191) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `labours`
--

CREATE TABLE `labours` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `labourCode` varchar(50) NOT NULL,
  `labourName` varchar(191) NOT NULL,
  `labourAddress` varchar(500) NOT NULL,
  `labourCatagory` varchar(50) NOT NULL,
  `labour_type` varchar(30) NOT NULL DEFAULT 'salary_labour',
  `salary` decimal(15,2) NOT NULL DEFAULT 0.00,
  `advance` decimal(15,2) NOT NULL DEFAULT 0.00,
  `balance` decimal(15,2) NOT NULL DEFAULT 0.00,
  `commissionPercentage` decimal(8,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `labours`
--

INSERT INTO `labours` (`id`, `labourCode`, `labourName`, `labourAddress`, `labourCatagory`, `labour_type`, `salary`, `advance`, `balance`, `commissionPercentage`, `created_at`, `updated_at`) VALUES
(1, 'AMJAD', 'Amjad Saleem', 'Rehman Pura Sargodha', 'ajent', 'salary_labour', 5000.00, 0.00, 0.00, 10.00, '2026-10-26 12:20:25', '2026-10-26 12:20:25'),
(2, 'USMAN', 'Usman Shahid', 'Urben Area Sargodha', 'ajent', 'salary_labour', 5000.00, 0.00, 0.00, 10.00, '2026-10-26 12:21:15', '2026-10-26 12:21:15'),
(3, 'IMRAN', 'Imran Haider', 'Rehman Pura Sargodha', 'ajent', 'salary_labour', 5000.00, 0.00, 0.00, 10.00, '2026-10-26 12:25:24', '2026-10-26 12:25:24'),
(4, 'ABDUR', 'Abdur Rehman', 'Istaqlala Abad', 'fitter', 'fitter_labour', 20000.00, 35000.00, 0.00, NULL, '2026-10-26 12:27:38', '2026-10-26 12:30:36'),
(5, 'KASHIF', 'Kashif Abass', 'Rehman Pura Sargodha', 'fitter', 'fitter_labour', 20000.00, 65000.00, 0.00, NULL, '2026-10-26 12:28:18', '2026-10-26 12:28:18'),
(6, 'KURAM', 'Khuram Shahid', 'Rehman Pura Sargodha', 'worker', 'salary_labour', 30000.00, 45000.00, 0.00, NULL, '2026-10-26 12:29:06', '2026-10-26 12:29:06');

-- --------------------------------------------------------

--
-- Table structure for table `labour_account_transactions`
--

CREATE TABLE `labour_account_transactions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `transactionDate` date NOT NULL,
  `labour_id` bigint(20) UNSIGNED NOT NULL,
  `labourCode` varchar(50) NOT NULL,
  `labourName` varchar(255) NOT NULL,
  `labourCatagory` varchar(50) NOT NULL,
  `description` varchar(255) NOT NULL,
  `bankAccount` varchar(100) DEFAULT NULL,
  `amount` decimal(15,2) NOT NULL,
  `entryType` enum('debit','credit') NOT NULL,
  `marketBalance` decimal(15,2) DEFAULT NULL,
  `recovery` decimal(15,2) DEFAULT NULL,
  `recoveryCommPercent` decimal(8,2) DEFAULT NULL,
  `commissionPercent` decimal(8,2) DEFAULT NULL,
  `isAdvance` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `labour_account_transactions`
--

INSERT INTO `labour_account_transactions` (`id`, `transactionDate`, `labour_id`, `labourCode`, `labourName`, `labourCatagory`, `description`, `bankAccount`, `amount`, `entryType`, `marketBalance`, `recovery`, `recoveryCommPercent`, `commissionPercent`, `isAdvance`, `created_at`, `updated_at`) VALUES
(1, '2026-10-26', 3, 'IMRAN', 'Imran Haider', 'ajent', 'Pre Balance', NULL, 10000.00, 'debit', NULL, NULL, 0.00, 10.00, 0, '2026-10-26 12:34:27', '2026-10-26 12:34:27'),
(2, '2026-10-26', 1, 'AMJAD', 'Amjad Saleem', 'ajent', 'Pre Balance', NULL, 10000.00, 'debit', NULL, NULL, 0.00, 10.00, 0, '2026-10-26 12:34:58', '2026-10-26 12:34:58'),
(3, '2026-10-26', 2, 'USMAN', 'Usman Shahid', 'ajent', 'Pre Balance', NULL, 10000.00, 'debit', NULL, NULL, 0.00, 10.00, 0, '2026-10-26 12:35:31', '2026-10-26 12:35:31'),
(4, '2026-10-26', 6, 'KURAM', 'Khuram Shahid', 'worker', 'Pre Balance r', NULL, 10000.00, 'debit', NULL, NULL, NULL, NULL, 0, '2026-10-26 12:42:18', '2026-10-26 12:43:45'),
(5, '2026-10-26', 6, 'KURAM', 'Khuram Shahid', 'worker', 'Cash Pay', 'Cash', 5000.00, 'debit', NULL, NULL, NULL, NULL, 0, '2026-10-26 13:17:55', '2026-10-26 13:20:50'),
(6, '2026-10-29', 1, 'AMJAD', 'Amjad Saleem', 'ajent', 'Cas', NULL, 150.00, 'debit', 100000.00, 1500.00, 1.50, 10.00, 0, '2026-10-29 20:51:31', '2026-10-29 20:51:31');

-- --------------------------------------------------------

--
-- Table structure for table `ledgers`
--

CREATE TABLE `ledgers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `entry_date` date NOT NULL,
  `reference_no` varchar(191) DEFAULT NULL,
  `market_voucher_id` bigint(20) UNSIGNED DEFAULT NULL,
  `account_id` bigint(20) UNSIGNED NOT NULL,
  `debit` decimal(15,2) NOT NULL DEFAULT 0.00,
  `credit` decimal(15,2) NOT NULL DEFAULT 0.00,
  `description` text DEFAULT NULL,
  `category` varchar(191) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ledgers`
--

INSERT INTO `ledgers` (`id`, `entry_date`, `reference_no`, `market_voucher_id`, `account_id`, `debit`, `credit`, `description`, `category`, `created_at`, `updated_at`) VALUES
(2, '2026-10-26', 'BAT-2', NULL, 3, 10000.00, 0.00, 'Pre Balance', 'BankAccountTransaction', '2026-10-26 13:00:05', '2026-10-26 13:00:05'),
(3, '2026-10-26', 'BAT-3', NULL, 1, 10000.00, 0.00, 'Pre Balance', 'BankAccountTransaction', '2026-10-26 13:00:45', '2026-10-26 13:00:45'),
(4, '2026-10-26', 'BAT-4', NULL, 2, 10000.00, 0.00, 'Pre Balance', 'BankAccountTransaction', '2026-10-26 13:01:21', '2026-10-26 13:03:09'),
(5, '2026-10-26', 'LAT-5', NULL, 3, 0.00, 5000.00, 'Cash Pay — Khuram Shahid', 'LabourAccountTransaction', '2026-10-26 13:17:55', '2026-10-26 13:20:50'),
(6, '2026-10-26', '12', NULL, 3, 0.00, 1000.00, 'Customer debit HASSAN - cash py', 'AccountTransaction', '2026-10-26 14:42:32', '2026-10-26 14:42:32');

-- --------------------------------------------------------

--
-- Table structure for table `login_activities`
--

CREATE TABLE `login_activities` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `logged_in_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `login_activities`
--

INSERT INTO `login_activities` (`id`, `user_id`, `ip_address`, `user_agent`, `logged_in_at`) VALUES
(1, 2, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-10-26 12:15:09'),
(2, 2, '127.0.0.1', '', '2026-10-26 15:07:23'),
(3, 2, '127.0.0.1', '', '2026-10-27 14:05:53'),
(4, 2, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-10-27 14:08:47'),
(5, 2, '127.0.0.1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.19041.6456', '2026-10-28 17:14:36'),
(6, 2, '127.0.0.1', '', '2026-10-28 17:15:26'),
(7, 2, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-10-28 17:19:06'),
(8, 2, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-10-28 19:09:40'),
(9, 2, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-10-28 19:23:13'),
(10, 2, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-10-28 20:55:54'),
(11, 2, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-10-29 18:49:16'),
(12, 2, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-10-29 19:31:01'),
(13, 2, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-10-30 16:07:16'),
(14, 2, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-11-06 04:32:25'),
(15, 2, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-11-06 06:03:04'),
(16, 2, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-11-06 06:06:21'),
(17, 2, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-11-07 14:07:28'),
(18, 2, '154.192.180.171', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/157.0.0.0 Safari/537.36', '2026-11-09 09:24:40'),
(19, 2, '154.192.180.171', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/157.0.0.0 Safari/537.36', '2026-11-09 10:31:49'),
(20, 2, '154.192.180.171', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/157.0.0.0 Safari/537.36', '2026-11-09 11:42:50'),
(21, 2, '103.81.241.136', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-11-09 12:44:01'),
(22, 2, '154.192.79.55', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/157.0.0.0 Safari/537.36', '2026-11-09 12:46:32'),
(23, 2, '103.81.241.136', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-11-09 13:15:44'),
(24, 2, '187.55.148.24', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Mobile Safari/537.36', '2026-11-09 16:43:21'),
(25, 2, '154.192.79.55', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/157.0.0.0 Safari/537.36', '2026-11-09 17:33:18'),
(26, 2, '103.81.241.136', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-11-10 01:50:10'),
(27, 2, '103.81.241.136', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/157.0.0.0 Safari/537.36', '2026-11-10 03:00:28'),
(28, 2, '154.192.180.251', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Mobile Safari/537.36', '2026-11-10 03:18:32');

-- --------------------------------------------------------

--
-- Table structure for table `market_vouchers`
--

CREATE TABLE `market_vouchers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `voucher_date` date NOT NULL,
  `customerArea` varchar(50) NOT NULL,
  `customerCode` varchar(50) NOT NULL,
  `description` varchar(255) NOT NULL,
  `recovery` decimal(15,2) NOT NULL DEFAULT 0.00,
  `discount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `misc` decimal(15,2) NOT NULL DEFAULT 0.00,
  `preBalance` decimal(15,2) NOT NULL DEFAULT 0.00,
  `lastBalance` decimal(15,2) NOT NULL DEFAULT 0.00,
  `delivered` decimal(15,2) NOT NULL DEFAULT 0.00,
  `market_vouchers_id` bigint(20) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(191) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(49, '0001_01_01_000000_create_users_table', 1),
(50, '0001_01_01_000001_create_cache_table', 1),
(51, '0001_01_01_000002_create_jobs_table', 1),
(52, '2025_08_10_042733_create_new_customer_table', 1),
(53, '2025_08_14_072954_create_customerareas_table', 1),
(54, '2025_08_14_165919_create_product_info_table', 1),
(55, '2025_08_15_102134_create_packing_table', 1),
(56, '2025_08_15_102152_create_colour_table', 1),
(57, '2025_08_15_102238_create_product_catagory_table', 1),
(58, '2025_08_15_103331_create_product_company_table', 1),
(59, '2025_08_29_105740_create_new_ajensts', 1),
(60, '2025_09_05_131502_create_account_table', 1),
(61, '2025_09_05_132003_create_market_vocher_table', 1),
(62, '2025_09_05_183238_create_bank_accounts_table', 1),
(63, '2025_10_15_181712_create_customerledgers_table', 1),
(64, '2025_10_24_000001_add_accounttype_to_customerledgers', 1),
(65, '2025_11_14_074849_create_agent_ledger_table', 1),
(66, '2025_11_14_111803_create_expenses_table', 1),
(67, '2025_11_14_111806_create_expense_ledger_table', 1),
(68, '2025_11_14_120000_create_purchase_invoices_table', 1),
(69, '2025_11_14_120100_create_purchase_invoice_items_table', 1),
(70, '2025_11_14_125325_create_sale_invoices_table', 1),
(71, '2025_11_14_125327_create_sale_invoice_items_table', 1),
(72, '2025_11_14_143601_create_guds_table', 1),
(73, '2025_11_21_000001_create_customer_product_rates_table', 2),
(74, '2025_11_21_173554_add_versioning_to_customer_product_rates_table', 3),
(75, '2025_11_21_181000_create_units_table', 4),
(76, '2025_11_21_180001_create_units_table', 5),
(77, '2025_11_21_180002_add_cost_columns_to_product_info_table', 6),
(78, '2025_11_21_180003_create_boms_table', 6),
(79, '2025_11_21_180004_create_bom_items_table', 6),
(80, '2025_11_21_180005_create_bom_labors_table', 6),
(81, '2025_11_21_180006_create_bom_overheads_table', 6),
(82, '2025_11_21_180007_create_product_cost_versions_table', 6),
(83, '2025_11_25_120000_create_product_variants_table', 7),
(84, '2025_11_26_000001_add_variant_columns_to_sale_invoice_items_table', 8),
(85, '2025_11_26_000002_add_variant_columns_to_purchase_invoice_items_table', 8),
(86, '2025_11_26_183629_add_variant_columns_to_customer_product_rates_table', 9),
(87, '2025_11_26_190401_update_customer_product_rates_unique_constraint', 10),
(88, '2025_11_26_190409_update_customer_product_rates_unique_constraint', 10),
(89, '2025_11_28_172422_create_recovery_bank_distributions_table', 11),
(90, '2025_11_28_172800_add_columns_to_recovery_bank_distributions_table', 12),
(97, '2025_11_28_172454_create_recovery_bank_distributions_table', 13),
(98, '2025_12_08_000001_add_market_voucher_id_to_ledgers_table', 13),
(99, '2025_12_08_000002_drop_recovery_bank_distributions_table', 13),
(100, '2025_12_08_000003_fix_market_vouchers_parent_id', 13),
(101, '2025_12_09_000001_remove_self_fk_market_vouchers', 13),
(102, '2025_12_09_000002_make_market_vouchers_id_nullable', 13),
(103, '2025_12_09_000003_create_voucher_group_sequence', 14),
(104, '2025_12_09_150000_add_cost_rate_to_sale_invoice_items_table', 15),
(105, '2026_01_22_183133_add_statuse_to_customerledgers_table', 16),
(106, '2026_01_09_000000_create_recovery_bank_distributions_table', 17),
(107, '2026_01_22_201719_add_distribution_date_to_recovery_bank_distributions', 18),
(108, '2026_05_05_190000_create_profit_loss_closings_table', 19),
(109, '2026_05_05_210000_create_orders_tables', 20),
(110, '2026_05_06_182000_add_ajent_category_to_new_ajensts_table', 21),
(111, '2026_05_06_184000_add_labour_compensation_columns_to_new_ajensts_table', 22),
(112, '2026_05_07_181500_drop_agent_and_labour_tables', 23),
(113, '2026_05_07_183500_create_labours_table', 24),
(114, '2026_05_08_003000_create_labour_account_transactions_table', 25),
(115, '2026_05_08_120000_add_role_to_users_table', 26),
(116, '2026_05_09_000001_create_client_url_restrictions_table', 27),
(117, '2026_05_13_120000_add_sale_type_to_sale_invoices_table', 28),
(118, '2026_05_13_120100_create_sale_invoice_bank_payments_table', 29),
(119, '2026_05_13_200000_add_last_login_and_login_activities', 30),
(120, '2026_05_15_120000_ensure_gl_control_accounts', 31),
(121, '2026_05_16_100000_add_labour_type_to_labours_table', 32),
(122, '2026_05_16_100100_create_fitter_labour_tables', 32),
(123, '2026_05_15_140000_add_batch_uom_and_unit_rate_to_bom', 33),
(124, '2026_05_16_200000_create_fitter_production_tables', 34),
(125, '2026_05_20_120000_add_indexes_to_sale_tables', 35),
(126, '2026_05_20_130000_add_payment_fields_to_sale_invoices_table', 36),
(127, '2026_05_21_100000_enhance_orders_for_erp_module', 37),
(128, '2026_05_21_120000_enhance_purchase_invoices_table', 38),
(129, '2026_05_22_000001_drop_fitter_labour_tables', 39),
(130, '2026_05_23_000001_add_erp_balance_columns_to_profit_loss_closings', 40),
(131, '2026_05_23_000002_drop_closing_date_unique_on_profit_loss_closings', 41),
(132, '2026_05_24_000001_create_bom_labour_works_table', 42),
(133, '2026_05_24_000002_create_bom_materials_table', 43),
(134, '2026_05_24_000003_add_per_dozen_use_qty_to_bom_items', 44),
(135, '2026_05_24_000004_add_work_type_to_bom_labour_works', 45),
(136, '2026_05_24_000005_drop_work_type_from_bom_labour_works', 46),
(137, '2026_05_25_000001_create_fitting_labour_module_tables', 47),
(138, '2026_05_26_000001_add_entry_category_to_fitting_labours', 48),
(139, '2026_05_27_000001_add_bank_account_to_fitting_labours', 49),
(140, '2026_05_28_000001_widen_fitting_labour_entry_category', 50),
(141, '2026_05_29_000001_create_fitting_labour_materials_table', 51),
(142, '2026_10_14_210000_create_unit_per_pack_table', 52),
(143, '2026_10_14_210100_add_unit_per_pack_to_product_info_table', 53),
(144, '2026_10_14_220000_add_image_to_product_info_and_variants', 54),
(145, '2026_10_14_230000_create_customer_catagory_table', 55),
(146, '2026_10_15_233500_add_rate_type_to_customer_product_rates_table', 56),
(147, '2026_10_15_235500_create_supplier_transactions_tables', 57),
(148, '2026_10_16_000100_add_entry_type_to_supplier_transactions', 58),
(149, '2026_10_16_101200_drop_fitting_labour_module_tables', 59),
(150, '2026_10_16_102000_create_fitting_labour_tables', 60),
(151, '2026_10_16_103000_create_fitting_product_works_table', 61),
(152, '2026_10_16_111000_create_fitting_work_stocks_table', 62),
(153, '2026_10_16_113000_add_unit_name_to_fitting_work_stocks_table', 63),
(154, '2026_10_16_120000_add_work_name_to_supplier_transaction_items', 64),
(155, '2026_10_16_121000_add_work_name_to_customer_product_rates', 65),
(156, '2026_10_23_012900_add_order_no_to_sale_invoices_table', 66),
(157, '2026_10_23_040000_add_transport_to_purchase_invoices_table', 67),
(158, '2026_10_23_041500_add_transport_rate_to_purchase_invoices_table', 68),
(159, '2026_10_23_043500_add_freight_per_unit_to_purchase_invoice_items', 69),
(160, '2026_10_23_221900_create_customer_cheques_table', 70),
(161, '2026_10_25_020956_add_password_plain_to_users_table', 71),
(162, '2026_10_25_023323_add_access_customized_to_users_table', 72),
(163, '2026_10_25_023325_create_user_module_permissions_table', 73),
(164, '2026_10_27_204500_add_original_due_and_cleared_at_to_customer_cheques', 74),
(165, '2026_10_27_221000_create_pack_sizes_table', 75),
(166, '2026_10_28_223000_add_base_unit_and_qty_to_packing_table', 76),
(167, '2026_10_28_234500_add_bank_account_to_supplier_transactions', 77),
(168, '2026_10_29_003200_add_worker_to_customer_cheques', 78),
(169, '2026_10_29_005500_add_payee_fields_to_customer_cheques', 79),
(170, '2026_10_30_004700_add_bilty_no_to_sale_invoices_table', 80),
(171, '2026_10_30_021500_create_sale_returns_tables', 81),
(172, '2026_10_30_211500_create_purchase_returns_tables', 82),
(173, '2025_12_09_143001_create_voucher_group_sequence', 83),
(174, '2026_11_06_124500_add_delivered_fields_to_sale_invoices_table', 84);

-- --------------------------------------------------------

--
-- Table structure for table `newcustomers`
--

CREATE TABLE `newcustomers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `custCode` varchar(191) NOT NULL,
  `custArea` varchar(191) DEFAULT NULL,
  `bussnessName` varchar(191) DEFAULT NULL,
  `custName` varchar(191) DEFAULT NULL,
  `custAddress` varchar(191) DEFAULT NULL,
  `custContect` varchar(191) DEFAULT NULL,
  `custCity` varchar(191) DEFAULT NULL,
  `custPage` varchar(191) DEFAULT NULL,
  `custdiscount` decimal(8,2) DEFAULT NULL,
  `custPayTermDay` int(11) DEFAULT NULL,
  `custCreditLimit` decimal(15,2) DEFAULT NULL,
  `custOppDate` date DEFAULT NULL,
  `custBalance` decimal(15,2) DEFAULT NULL,
  `custCatagory` varchar(191) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `newcustomers`
--

INSERT INTO `newcustomers` (`id`, `custCode`, `custArea`, `bussnessName`, `custName`, `custAddress`, `custContect`, `custCity`, `custPage`, `custdiscount`, `custPayTermDay`, `custCreditLimit`, `custOppDate`, `custBalance`, `custCatagory`) VALUES
(1, 'IMR', 'BAN', 'Imran Electric Store', 'Iman', 'Imran Electric Store Banu Road', '0300-6011553', 'Kohat', '12', 30.00, 30, 1000000.00, '2026-10-26', -143420.00, 'Retailers'),
(2, 'NZR', 'BAN', 'Nazeer Electric Store', 'Nazir', 'Nazeer Electric Store Banu Road', '03000-6011553', 'Kohat', '13', 30.00, 30, 1000000.00, '2026-10-26', 9000.00, 'Retailers'),
(3, 'SHB', 'BAN', 'Sahib Zada Electric Store', 'Sahib Khan', 'Sahib Zada Electric Store Main Bazar', '030006655', 'Banu', '14', 30.00, 30, 10000000.00, '2026-10-26', 9000.00, 'WholeSale'),
(4, 'KASHIF', 'MGP', 'Kashif Electric Store', 'Kashif', 'Kashif Electric Store Main Bazar', '0300-6011553', 'Mianawali', '1', 30.00, 30, 1500000.00, '2026-10-26', 32325.00, 'Retailers'),
(5, 'SHOAIB', 'MGP', 'Shoaib Electric Store', 'Shoaib', 'Shoaib Electric Store Main Bazar', '03006011', 'Bhakhar', '2', 30.00, 30, 1500000.00, '2026-10-26', 2000.00, 'WholeSale'),
(6, 'TAHIR', 'SUPLLIER', 'Tahir Trasnport Company', 'Tahir', 'Tahir Electric Store Main Bazar', '0300', 'Lahore', '12', 30.00, 30, 15000000.00, '2026-10-26', 91990.00, 'Transport'),
(7, 'KAREEM', 'MNG', 'Kareem Electric Store', 'Kareem', 'Kareem Electric Store', '03000', 'Sawat', '1', 30.00, 30, 1500000.00, '2026-10-26', 621500.00, 'Retailers'),
(8, 'CUST001', 'MNG', 'Noor Shah Electric Sotre', 'Noor Shah', 'Noor Shah Electroc Store Main Bazar', '0300', 'Mengora', '3', 30.00, 30, 15000000.00, '2026-10-26', 10000.00, 'WholeSale'),
(9, 'DURANI11', 'MNG', 'Durani Electric Store', 'Durani', 'Tahir Electric Store Main Bazar', '0300306', 'Mengora Sawat', '35', 30.00, 30, 150000.00, '2026-10-26', 8000.00, 'Retailers'),
(10, 'HASSAN', 'SUPLLIER', 'Hassan Khan', 'Hassan', 'Rehman Pura', '03000', 'Sargodha', '23', 0.00, 0, 0.00, '2026-10-26', 1589480.00, 'Raw Material Supplier'),
(11, 'FAISAL', 'SUPLLIER', 'Faisal Holder Wala', 'Faisal', 'Rehman Pura', '03000', 'Sargodha', '35', 0.00, 0, 0.00, '2026-10-26', 147000.00, 'Product Supplier');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `orderNo` varchar(191) NOT NULL,
  `orderDate` date NOT NULL,
  `customerCode` varchar(191) NOT NULL,
  `customerName` varchar(191) NOT NULL,
  `city` varchar(191) DEFAULT NULL,
  `gudsName` varchar(191) DEFAULT NULL,
  `noOfItems` int(11) NOT NULL DEFAULT 0,
  `orderTotal` decimal(15,2) NOT NULL DEFAULT 0.00,
  `orderDiscount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `grandTotal` decimal(15,2) NOT NULL DEFAULT 0.00,
  `comments` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `status` varchar(191) NOT NULL DEFAULT 'Active',
  `converted_invoice_no` varchar(50) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `orderNo`, `orderDate`, `customerCode`, `customerName`, `city`, `gudsName`, `noOfItems`, `orderTotal`, `orderDiscount`, `grandTotal`, `comments`, `notes`, `status`, `converted_invoice_no`, `created_at`, `updated_at`) VALUES
(1, 'ORD-00001', '2026-10-28', 'DURANI11', 'Durani Electric Store', 'Mengora Sawat', '', 2, 58380.00, 0.00, 58380.00, '', '', 'Confirmed', NULL, '2026-10-28 20:53:50', '2026-10-29 18:49:05');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `order_id` bigint(20) UNSIGNED NOT NULL,
  `orderNo` varchar(191) NOT NULL,
  `productCode` varchar(191) NOT NULL,
  `productName` varchar(191) NOT NULL,
  `qty` int(11) NOT NULL,
  `rate` decimal(15,2) NOT NULL DEFAULT 0.00,
  `discount` decimal(8,2) NOT NULL DEFAULT 0.00,
  `amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `remarks` varchar(500) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `orderNo`, `productCode`, `productName`, `qty`, `rate`, `discount`, `amount`, `remarks`, `created_at`, `updated_at`) VALUES
(1, 1, 'ORD-00001', '280P', 'Cieling Holder', 1, 380.00, 0.00, 380.00, NULL, '2026-10-28 20:53:50', '2026-10-28 20:53:50'),
(2, 1, 'ORD-00001', '100P', 'Power Plug Porceline', 100, 580.00, 0.00, 58000.00, NULL, '2026-10-28 20:53:50', '2026-10-28 20:53:50');

-- --------------------------------------------------------

--
-- Table structure for table `packing`
--

CREATE TABLE `packing` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `pakingName` varchar(191) NOT NULL,
  `baseUnit` varchar(50) DEFAULT NULL,
  `qtyInPack` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `packing`
--

INSERT INTO `packing` (`id`, `pakingName`, `baseUnit`, `qtyInPack`) VALUES
(7, 'BAG', 'KG', 25),
(9, 'BOX', 'DOZEN', 1),
(8, 'BOX', 'PIECE', 1),
(10, 'BOX', 'PIECE', 6),
(13, 'BOX', 'PIECE', 12),
(12, 'PACKIT', 'GURS', 1),
(11, 'PAKIT', 'GURS', 10);

-- --------------------------------------------------------

--
-- Table structure for table `pack_sizes`
--

CREATE TABLE `pack_sizes` (
  `id` int(10) UNSIGNED NOT NULL,
  `packing_type` varchar(50) NOT NULL,
  `unit_name` varchar(50) NOT NULL,
  `qty` decimal(12,2) NOT NULL,
  `label` varchar(120) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pack_sizes`
--

INSERT INTO `pack_sizes` (`id`, `packing_type`, `unit_name`, `qty`, `label`) VALUES
(14, 'Box', 'Dozen', 12.00, 'Box · 12 Dozen'),
(15, 'Box', 'Piece', 1.00, 'Box · 1 Piece'),
(16, 'Bag', 'Kg', 25.00, 'Bag · 25 Kg'),
(17, 'Pakit', 'Gurs', 10.00, 'Pakit · 10 Gurs');

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(191) NOT NULL,
  `token` varchar(191) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `product_catagory`
--

CREATE TABLE `product_catagory` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `catagoryName` varchar(191) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product_catagory`
--

INSERT INTO `product_catagory` (`id`, `catagoryName`) VALUES
(5, 'COMPUND'),
(4, 'HOLDER'),
(3, 'LIGHT PLUG'),
(1, 'PIANO'),
(2, 'POWER PLUG');

-- --------------------------------------------------------

--
-- Table structure for table `product_company`
--

CREATE TABLE `product_company` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `companyName` varchar(191) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product_company`
--

INSERT INTO `product_company` (`id`, `companyName`) VALUES
(2, 'COMPUND'),
(1, 'PANASONIC');

-- --------------------------------------------------------

--
-- Table structure for table `product_cost_versions`
--

CREATE TABLE `product_cost_versions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `productCode` varchar(191) NOT NULL,
  `effective_from` date NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `material_cost` decimal(18,4) NOT NULL DEFAULT 0.0000,
  `labor_cost` decimal(18,4) NOT NULL DEFAULT 0.0000,
  `overhead_cost` decimal(18,4) NOT NULL DEFAULT 0.0000,
  `wastage_cost` decimal(18,4) NOT NULL DEFAULT 0.0000,
  `batch_cost` decimal(18,4) NOT NULL DEFAULT 0.0000,
  `unit_cost` decimal(18,6) NOT NULL DEFAULT 0.000000,
  `currency` varchar(8) NOT NULL DEFAULT 'PKR',
  `remarks` varchar(191) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `product_info`
--

CREATE TABLE `product_info` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `productCode` varchar(191) NOT NULL,
  `productName` varchar(191) NOT NULL,
  `companyName` varchar(191) DEFAULT NULL,
  `productType` enum('Raw','Finished') NOT NULL DEFAULT 'Finished',
  `uom_id` bigint(20) UNSIGNED DEFAULT NULL,
  `colourName` varchar(191) DEFAULT NULL,
  `pakingName` varchar(191) DEFAULT NULL,
  `unitPerPackName` varchar(255) DEFAULT NULL,
  `piecesPerUnit` int(10) UNSIGNED DEFAULT NULL,
  `saleRate` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `costRate` decimal(10,2) DEFAULT NULL,
  `lessQty` decimal(10,2) DEFAULT NULL,
  `stockQty` decimal(10,2) DEFAULT NULL,
  `catagoryName` varchar(191) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product_info`
--

INSERT INTO `product_info` (`id`, `productCode`, `productName`, `companyName`, `productType`, `uom_id`, `colourName`, `pakingName`, `unitPerPackName`, `piecesPerUnit`, `saleRate`, `costRate`, `lessQty`, `stockQty`, `catagoryName`, `image`) VALUES
(3, '033P', 'Piano Switch Grace', 'Panasonic', 'Finished', NULL, 'White', 'Box', 'Dozen', 1, 780, 350.00, 10.00, 10000.00, 'Piano', NULL),
(4, '034P', 'Piano Socket Grace', 'Panasonic', 'Finished', NULL, 'White', 'Box', 'Dozen', 1, 680, 350.00, 10.00, 11500.00, 'Piano', NULL),
(5, '100P', 'Power Plug Porceline', 'Panasonic', 'Finished', NULL, 'White', 'Box', 'Piece', 1, 580, 250.00, 10.00, 10000.00, 'Power Plug', NULL),
(6, '105P', 'Power Plug Porcline 5+1 Load Master', 'Panasonic', 'Finished', NULL, 'White', 'Packit', 'Gurs', 1, 580, 250.00, 10.00, 10100.00, 'Power Plug', NULL),
(7, '227P', 'Light Plug 5+1 Porcline', 'Panasonic', 'Finished', NULL, 'White', 'Box', 'Piece', 6, 450, 150.00, 10.00, 10100.00, 'Light Plug', NULL),
(8, '069P', 'Jambo Holder Folwer B-22', 'Panasonic', 'Finished', NULL, 'White', 'Box', 'Dozen', 1, 1350, 750.00, 10.00, 10200.00, 'Holder', NULL),
(9, '070P', 'Jumbo Holder Flover E-27', 'Panasonic', 'Finished', NULL, 'White', 'Box', 'Dozen', 1, 1350, 750.00, 10.00, 10130.00, 'Holder', NULL),
(10, '280P', 'Cieling Holder', 'Panasonic', 'Finished', NULL, 'White', 'Box', 'Piece', 12, 380, 80.00, 10.00, 3000.00, 'Holder', NULL),
(11, 'WB-00S', 'Nobel Compund', 'Compund', 'Finished', NULL, 'White', 'Bag', 'Kg', 25, 10050, 9375.00, 10.00, 100100.00, 'Compund', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `product_variants`
--

CREATE TABLE `product_variants` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `productCode` varchar(191) NOT NULL,
  `variantName` varchar(191) NOT NULL,
  `variantValue` varchar(191) NOT NULL,
  `additionalCost` decimal(10,2) NOT NULL DEFAULT 0.00,
  `additionalPrice` decimal(10,2) NOT NULL DEFAULT 0.00,
  `stockQty` int(11) NOT NULL DEFAULT 0,
  `isActive` tinyint(1) NOT NULL DEFAULT 1,
  `image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product_variants`
--

INSERT INTO `product_variants` (`id`, `productCode`, `variantName`, `variantValue`, `additionalCost`, `additionalPrice`, `stockQty`, `isActive`, `image`) VALUES
(1, '280P', 'Golden', 'Peice', 80.00, 360.00, 1000, 1, NULL),
(2, '280P', 'Gray', 'Peice', 80.00, 370.00, 1015, 1, NULL),
(3, '280P', 'Coffee', 'Peice', 80.00, 380.00, 1000, 1, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `profit_loss_closings`
--

CREATE TABLE `profit_loss_closings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `from_date` date NOT NULL,
  `to_date` date NOT NULL,
  `closing_date` date NOT NULL,
  `sale_amount` decimal(18,2) NOT NULL DEFAULT 0.00,
  `cost_amount` decimal(18,2) NOT NULL DEFAULT 0.00,
  `gross_profit` decimal(18,2) NOT NULL DEFAULT 0.00,
  `sale_bilty_amount` decimal(18,2) NOT NULL DEFAULT 0.00,
  `expense_amount` decimal(18,2) NOT NULL DEFAULT 0.00,
  `labor_amount` decimal(18,2) NOT NULL DEFAULT 0.00,
  `ledger_discount_amount` decimal(18,2) NOT NULL DEFAULT 0.00,
  `ledger_bilty_amount` decimal(18,2) NOT NULL DEFAULT 0.00,
  `voucher_discount_amount` decimal(18,2) NOT NULL DEFAULT 0.00,
  `net_profit` decimal(18,2) NOT NULL DEFAULT 0.00,
  `customer_receivable` decimal(18,2) NOT NULL DEFAULT 0.00,
  `customer_payable` decimal(18,2) NOT NULL DEFAULT 0.00,
  `bank_balance` decimal(18,2) NOT NULL DEFAULT 0.00,
  `labour_balance` decimal(18,2) NOT NULL DEFAULT 0.00,
  `expense_balance` decimal(18,2) NOT NULL DEFAULT 0.00,
  `stock_balance` decimal(18,2) NOT NULL DEFAULT 0.00,
  `purchase_amount` decimal(18,2) NOT NULL DEFAULT 0.00,
  `closed_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `notes` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `purchase_invoices`
--

CREATE TABLE `purchase_invoices` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `invoiceNo` varchar(191) NOT NULL,
  `invoiceDate` date NOT NULL,
  `supplierCode` varchar(191) NOT NULL,
  `supplierName` varchar(191) NOT NULL,
  `city` varchar(191) DEFAULT NULL,
  `balance` decimal(14,2) NOT NULL DEFAULT 0.00,
  `corten` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `biltyExpanse` decimal(15,2) NOT NULL DEFAULT 0.00,
  `loaderExpanse` decimal(15,2) NOT NULL DEFAULT 0.00,
  `biltyBalance` decimal(15,2) NOT NULL DEFAULT 0.00,
  `comments` text DEFAULT NULL,
  `noOfItems` int(11) NOT NULL DEFAULT 0,
  `invoiceTotal` decimal(14,2) NOT NULL DEFAULT 0.00,
  `invoiceDiscount` decimal(14,2) NOT NULL DEFAULT 0.00,
  `grandTotal` decimal(14,2) NOT NULL DEFAULT 0.00,
  `paid_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `payment_status` varchar(20) NOT NULL DEFAULT 'unpaid',
  `status` varchar(191) NOT NULL DEFAULT 'Active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `transportCode` varchar(255) DEFAULT NULL,
  `transportName` varchar(255) DEFAULT NULL,
  `vehicleNumber` varchar(255) DEFAULT NULL,
  `dispatchNo` varchar(255) DEFAULT NULL,
  `weight` decimal(14,2) DEFAULT NULL,
  `transportRate` decimal(14,2) DEFAULT NULL,
  `transportAmount` decimal(14,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `purchase_invoices`
--

INSERT INTO `purchase_invoices` (`id`, `invoiceNo`, `invoiceDate`, `supplierCode`, `supplierName`, `city`, `balance`, `corten`, `biltyExpanse`, `loaderExpanse`, `biltyBalance`, `comments`, `noOfItems`, `invoiceTotal`, `invoiceDiscount`, `grandTotal`, `paid_amount`, `payment_status`, `status`, `created_at`, `updated_at`, `transportCode`, `transportName`, `vehicleNumber`, `dispatchNo`, `weight`, `transportRate`, `transportAmount`) VALUES
(1, 'PINV-00001', '2026-10-29', 'HASSAN', 'Hassan Khan', 'Sargodha', 9000.00, 0, 0.00, 0.00, 0.00, '', 1, 900000.00, 0.00, 900000.00, 0.00, 'unpaid', 'Active', '2026-10-29 19:14:34', '2026-10-29 19:14:34', 'TAHIR', 'Tahir', '6546549', '6658', 2500.00, 15.00, 37500.00),
(2, 'PINV-00002', '2026-10-29', 'HASSAN', 'Hassan Khan', 'Sargodha', 909000.00, 0, 0.00, 0.00, 0.00, '', 2, 660000.00, 0.00, 660000.00, 0.00, 'unpaid', 'Active', '2026-10-29 19:32:04', '2026-10-29 19:32:04', NULL, NULL, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `purchase_invoice_items`
--

CREATE TABLE `purchase_invoice_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `purchase_invoice_id` bigint(20) UNSIGNED NOT NULL,
  `invoiceNo` varchar(191) NOT NULL,
  `productCode` varchar(191) NOT NULL,
  `productName` varchar(191) NOT NULL,
  `variant_id` bigint(20) UNSIGNED DEFAULT NULL,
  `variant_name` varchar(191) DEFAULT NULL,
  `variant_value` varchar(191) DEFAULT NULL,
  `variant_added_price` decimal(15,2) DEFAULT NULL,
  `qty` int(11) NOT NULL,
  `rate` decimal(14,2) NOT NULL DEFAULT 0.00,
  `discount` decimal(14,2) NOT NULL DEFAULT 0.00,
  `amount` decimal(14,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `freightPerUnit` decimal(14,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `purchase_invoice_items`
--

INSERT INTO `purchase_invoice_items` (`id`, `purchase_invoice_id`, `invoiceNo`, `productCode`, `productName`, `variant_id`, `variant_name`, `variant_value`, `variant_added_price`, `qty`, `rate`, `discount`, `amount`, `created_at`, `updated_at`, `freightPerUnit`) VALUES
(1, 1, 'PINV-00001', 'WB-00S', 'Nobel Compund - Compund', NULL, NULL, NULL, 0.00, 100, 9000.00, 0.00, 900000.00, '2026-10-29 19:14:34', '2026-10-29 19:14:34', 375.00),
(2, 2, 'PINV-00002', 'WB-00S', 'Nobel Compund - Compund', NULL, NULL, NULL, 0.00, 15, 9000.00, 0.00, 135000.00, '2026-10-29 19:32:04', '2026-10-29 19:32:04', NULL),
(3, 2, 'PINV-00002', '034P', 'Piano Socket Grace - Panasonic', NULL, NULL, NULL, 0.00, 1500, 350.00, 0.00, 525000.00, '2026-10-29 19:32:04', '2026-10-29 19:32:04', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `purchase_invoice_payments`
--

CREATE TABLE `purchase_invoice_payments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `purchase_invoice_id` bigint(20) UNSIGNED NOT NULL,
  `invoiceNo` varchar(50) NOT NULL,
  `payment_date` date NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `payment_method` varchar(50) NOT NULL DEFAULT 'cash',
  `reference_no` varchar(100) DEFAULT NULL,
  `ledger_id` bigint(20) UNSIGNED DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `purchase_returns`
--

CREATE TABLE `purchase_returns` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `returnNo` varchar(191) NOT NULL,
  `returnDate` date NOT NULL,
  `supplierCode` varchar(191) NOT NULL,
  `supplierName` varchar(191) NOT NULL,
  `city` varchar(191) DEFAULT NULL,
  `balance` decimal(15,2) NOT NULL DEFAULT 0.00,
  `originalInvoiceNo` varchar(191) DEFAULT NULL,
  `comments` text DEFAULT NULL,
  `noOfItems` int(11) NOT NULL DEFAULT 0,
  `invoiceTotal` decimal(15,2) NOT NULL DEFAULT 0.00,
  `invoiceDiscount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `grandTotal` decimal(15,2) NOT NULL DEFAULT 0.00,
  `goodQtyTotal` decimal(15,2) NOT NULL DEFAULT 0.00,
  `damageQtyTotal` decimal(15,2) NOT NULL DEFAULT 0.00,
  `status` varchar(191) NOT NULL DEFAULT 'Active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `purchase_returns`
--

INSERT INTO `purchase_returns` (`id`, `returnNo`, `returnDate`, `supplierCode`, `supplierName`, `city`, `balance`, `originalInvoiceNo`, `comments`, `noOfItems`, `invoiceTotal`, `invoiceDiscount`, `grandTotal`, `goodQtyTotal`, `damageQtyTotal`, `status`, `created_at`, `updated_at`) VALUES
(1, 'PR-00001', '2026-10-30', 'HASSAN', 'Hassan', 'Sargodha', 1747830.00, NULL, '', 3, 136150.00, 0.00, 136150.00, 16.00, 100.00, 'Active', '2026-10-30 16:37:23', '2026-10-30 16:37:23');

-- --------------------------------------------------------

--
-- Table structure for table `purchase_return_items`
--

CREATE TABLE `purchase_return_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `purchase_return_id` bigint(20) UNSIGNED NOT NULL,
  `returnNo` varchar(191) NOT NULL,
  `productCode` varchar(191) NOT NULL,
  `productName` varchar(191) NOT NULL,
  `variant_id` bigint(20) UNSIGNED DEFAULT NULL,
  `variant_name` varchar(191) DEFAULT NULL,
  `variant_value` varchar(191) DEFAULT NULL,
  `variant_added_price` decimal(15,2) NOT NULL DEFAULT 0.00,
  `qty` decimal(15,2) NOT NULL DEFAULT 0.00,
  `rate` decimal(15,2) NOT NULL DEFAULT 0.00,
  `cost_rate` decimal(15,2) NOT NULL DEFAULT 0.00,
  `discount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `condition` varchar(20) NOT NULL DEFAULT 'good',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `purchase_return_items`
--

INSERT INTO `purchase_return_items` (`id`, `purchase_return_id`, `returnNo`, `productCode`, `productName`, `variant_id`, `variant_name`, `variant_value`, `variant_added_price`, `qty`, `rate`, `cost_rate`, `discount`, `amount`, `condition`, `created_at`, `updated_at`) VALUES
(4, 1, 'PR-00001', 'WB-00S', 'Nobel Compund', NULL, NULL, NULL, 0.00, 15.00, 9000.00, 0.00, 0.00, 135000.00, 'good', '2026-10-30 16:47:35', '2026-10-30 16:47:35'),
(5, 1, 'PR-00001', '227P', 'Light Plug 5+1 Porcline', NULL, NULL, NULL, 0.00, 1.00, 150.00, 0.00, 0.00, 150.00, 'good', '2026-10-30 16:47:35', '2026-10-30 16:47:35'),
(6, 1, 'PR-00001', '069P', 'Jambo Holder Folwer B-22', NULL, NULL, NULL, 0.00, 100.00, 10.00, 0.00, 0.00, 1000.00, 'damage', '2026-10-30 16:47:35', '2026-10-30 16:47:35');

-- --------------------------------------------------------

--
-- Table structure for table `recovery_bank_distributions`
--

CREATE TABLE `recovery_bank_distributions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `voucher_id` bigint(20) UNSIGNED DEFAULT NULL,
  `bank_account_code` varchar(191) NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `reference` varchar(191) DEFAULT NULL,
  `distribution_date` date DEFAULT NULL,
  `ledger_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sale_invoices`
--

CREATE TABLE `sale_invoices` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `invoiceNo` varchar(191) NOT NULL,
  `invoiceDate` date NOT NULL,
  `customerCode` varchar(191) NOT NULL,
  `customerName` varchar(191) NOT NULL,
  `orderNo` varchar(191) DEFAULT NULL,
  `city` varchar(191) DEFAULT NULL,
  `balance` decimal(15,2) NOT NULL DEFAULT 0.00,
  `gudsName` varchar(191) DEFAULT NULL,
  `corten` int(11) NOT NULL DEFAULT 0,
  `biltyNo` varchar(100) DEFAULT NULL,
  `biltyExpanse` decimal(15,2) NOT NULL DEFAULT 0.00,
  `loaderExpanse` decimal(15,2) NOT NULL DEFAULT 0.00,
  `biltyBalance` decimal(15,2) NOT NULL DEFAULT 0.00,
  `delivered` tinyint(1) NOT NULL DEFAULT 0,
  `delivered_date` timestamp NULL DEFAULT NULL,
  `comments` text DEFAULT NULL,
  `noOfItems` int(11) NOT NULL DEFAULT 0,
  `invoiceTotal` decimal(15,2) NOT NULL DEFAULT 0.00,
  `invoiceDiscount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `grandTotal` decimal(15,2) NOT NULL DEFAULT 0.00,
  `status` varchar(191) NOT NULL DEFAULT 'Active',
  `sale_type` varchar(20) NOT NULL DEFAULT 'credit',
  `payment_status` varchar(20) NOT NULL DEFAULT 'unpaid',
  `paid_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sale_invoices`
--

INSERT INTO `sale_invoices` (`id`, `invoiceNo`, `invoiceDate`, `customerCode`, `customerName`, `orderNo`, `city`, `balance`, `gudsName`, `corten`, `biltyNo`, `biltyExpanse`, `loaderExpanse`, `biltyBalance`, `delivered`, `delivered_date`, `comments`, `noOfItems`, `invoiceTotal`, `invoiceDiscount`, `grandTotal`, `status`, `sale_type`, `payment_status`, `paid_amount`, `created_at`, `updated_at`) VALUES
(1, 'INV-00001', '2026-10-28', 'KAREEM', 'Kareem Electric Store', NULL, 'Sawat', 10000.00, '', 0, NULL, 0.00, 0.00, 0.00, 0, NULL, '', 2, 611500.00, 0.00, 611500.00, 'Active', 'credit', 'unpaid', 0.00, '2026-10-28 20:28:13', '2026-10-28 20:28:13'),
(2, 'INV-00002', '2026-10-29', 'KASHIF', 'Kashif Electric Store', NULL, 'Mianawali', 10000.00, 'MUSLIM GUDS', 2, '65515', 150.00, 100.00, 200.00, 1, '2026-11-06 08:13:43', '', 3, 44390.50, 0.00, 44390.50, 'Active', 'credit', 'unpaid', 0.00, '2026-10-29 19:11:19', '2026-11-06 08:13:43'),
(3, 'INV-00003', '2026-10-29', 'KASHIF', 'Kashif Electric Store', NULL, 'Mianawali', 30000.00, '', 0, NULL, 0.00, 0.00, 0.00, 0, NULL, '', 2, 17325.00, 0.00, 17325.00, 'Active', 'credit', 'unpaid', 0.00, '2026-10-29 21:37:37', '2026-10-29 21:37:37'),
(4, 'INV-00004', '2026-11-06', 'HASSAN', 'Hassan Khan', NULL, 'Sargodha', 1611680.00, '', 0, NULL, 0.00, 0.00, 0.00, 0, NULL, '', 2, 22200.00, 0.00, 22200.00, 'Active', 'credit', 'unpaid', 0.00, '2026-11-06 04:35:32', '2026-11-06 04:35:32'),
(5, 'INV-00005', '2026-11-06', 'TAHIR', 'Tahir Trasnport Company', NULL, 'Lahore', -27500.00, 'MUSLIM GUDS', 3, NULL, 150.00, 150.00, 300.00, 1, '2026-11-06 08:16:01', '', 3, 119490.00, 0.00, 119490.00, 'Active', 'credit', 'unpaid', 0.00, '2026-11-06 07:31:32', '2026-11-06 08:16:01');

-- --------------------------------------------------------

--
-- Table structure for table `sale_invoice_bank_payments`
--

CREATE TABLE `sale_invoice_bank_payments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `sale_invoice_id` bigint(20) UNSIGNED NOT NULL,
  `bank_account_id` bigint(20) UNSIGNED NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `ledger_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sale_invoice_items`
--

CREATE TABLE `sale_invoice_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `sale_invoice_id` bigint(20) UNSIGNED NOT NULL,
  `invoiceNo` varchar(191) NOT NULL,
  `productCode` varchar(191) NOT NULL,
  `productName` varchar(191) NOT NULL,
  `variant_id` bigint(20) UNSIGNED DEFAULT NULL,
  `variant_name` varchar(191) DEFAULT NULL,
  `variant_value` varchar(191) DEFAULT NULL,
  `variant_added_price` decimal(15,2) DEFAULT NULL,
  `qty` int(11) NOT NULL DEFAULT 0,
  `rate` decimal(15,2) NOT NULL DEFAULT 0.00,
  `cost_rate` decimal(15,2) DEFAULT 0.00 COMMENT 'Cost rate for profit calculation',
  `discount` decimal(5,2) NOT NULL DEFAULT 0.00,
  `amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sale_invoice_items`
--

INSERT INTO `sale_invoice_items` (`id`, `sale_invoice_id`, `invoiceNo`, `productCode`, `productName`, `variant_id`, `variant_name`, `variant_value`, `variant_added_price`, `qty`, `rate`, `cost_rate`, `discount`, `amount`, `created_at`, `updated_at`) VALUES
(1, 1, 'INV-00001', '105P', 'Power Plug Porcline 5+1 Load Master - Panasonic', NULL, NULL, NULL, 0.00, 1000, 580.00, 250.00, 0.00, 580000.00, '2026-10-28 20:28:13', '2026-10-28 20:28:13'),
(2, 1, 'INV-00001', '227P', 'Light Plug 5+1 Porcline - Panasonic', NULL, NULL, NULL, 0.00, 100, 450.00, 150.00, 30.00, 31500.00, '2026-10-28 20:28:13', '2026-10-28 20:28:13'),
(3, 2, 'INV-00002', '100P', 'Power Plug Porceline - Panasonic', NULL, NULL, NULL, 0.00, 100, 580.00, 250.00, 30.00, 40600.00, '2026-10-29 19:11:19', '2026-10-29 19:11:19'),
(4, 2, 'INV-00002', '227P', 'Light Plug 5+1 Porcline - Panasonic', NULL, NULL, NULL, 0.00, 1, 15.00, 150.00, 30.00, 10.50, '2026-10-29 19:11:19', '2026-10-29 19:11:19'),
(5, 2, 'INV-00002', '280P', 'Cieling Holder - Panasonic', NULL, NULL, NULL, 0.00, 15, 360.00, 160.00, 30.00, 3780.00, '2026-10-29 19:11:19', '2026-10-29 19:11:19'),
(6, 3, 'INV-00003', '227P', 'Light Plug 5+1 Porcline - Panasonic', NULL, NULL, NULL, 0.00, 10, 450.00, 150.00, 30.00, 3150.00, '2026-10-29 21:37:37', '2026-10-29 21:37:37'),
(7, 3, 'INV-00003', '070P', 'Jumbo Holder Flover E-27 - Panasonic', NULL, NULL, NULL, 0.00, 15, 1350.00, 750.00, 30.00, 14175.00, '2026-10-29 21:37:37', '2026-10-29 21:37:37'),
(8, 4, 'INV-00004', '069P', 'Jambo Holder Folwer B-22 - Panasonic', NULL, NULL, NULL, 0.00, 10, 1350.00, 750.00, 0.00, 13500.00, '2026-11-06 04:35:32', '2026-11-06 04:35:32'),
(9, 4, 'INV-00004', '105P', 'Power Plug Porcline 5+1 Load Master - Panasonic', NULL, NULL, NULL, 0.00, 15, 580.00, 250.00, 0.00, 8700.00, '2026-11-06 04:35:32', '2026-11-06 04:35:32'),
(10, 5, 'INV-00005', '070P', 'Jumbo Holder Flover E-27 - Panasonic', NULL, NULL, NULL, 0.00, 105, 1350.00, 750.00, 30.00, 99225.00, '2026-11-06 07:31:33', '2026-11-06 07:31:33'),
(11, 5, 'INV-00005', '105P', 'Power Plug Porcline 5+1 Load Master - Panasonic', NULL, NULL, NULL, 0.00, 15, 580.00, 250.00, 30.00, 6090.00, '2026-11-06 07:31:33', '2026-11-06 07:31:33'),
(12, 5, 'INV-00005', '069P', 'Jambo Holder Folwer B-22 - Panasonic', NULL, NULL, NULL, 0.00, 15, 1350.00, 750.00, 30.00, 14175.00, '2026-11-06 07:31:33', '2026-11-06 07:31:33');

-- --------------------------------------------------------

--
-- Table structure for table `sale_returns`
--

CREATE TABLE `sale_returns` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `returnNo` varchar(191) NOT NULL,
  `returnDate` date NOT NULL,
  `customerCode` varchar(191) NOT NULL,
  `customerName` varchar(191) NOT NULL,
  `city` varchar(191) DEFAULT NULL,
  `balance` decimal(15,2) NOT NULL DEFAULT 0.00,
  `originalInvoiceNo` varchar(191) DEFAULT NULL,
  `comments` text DEFAULT NULL,
  `noOfItems` int(11) NOT NULL DEFAULT 0,
  `invoiceTotal` decimal(15,2) NOT NULL DEFAULT 0.00,
  `invoiceDiscount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `grandTotal` decimal(15,2) NOT NULL DEFAULT 0.00,
  `goodQtyTotal` decimal(15,2) NOT NULL DEFAULT 0.00,
  `damageQtyTotal` decimal(15,2) NOT NULL DEFAULT 0.00,
  `status` varchar(191) NOT NULL DEFAULT 'Active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sale_returns`
--

INSERT INTO `sale_returns` (`id`, `returnNo`, `returnDate`, `customerCode`, `customerName`, `city`, `balance`, `originalInvoiceNo`, `comments`, `noOfItems`, `invoiceTotal`, `invoiceDiscount`, `grandTotal`, `goodQtyTotal`, `damageQtyTotal`, `status`, `created_at`, `updated_at`) VALUES
(1, 'SR-00001', '2026-10-29', 'IMR', 'Iman', 'Kohat', 10000.00, NULL, '', 4, 153450.00, 30.00, 153420.00, 116.00, 15.00, 'Active', '2026-10-29 21:26:16', '2026-10-29 21:26:16'),
(2, 'SR-00002', '2026-10-29', 'HASSAN', 'Hassan', 'Sargodha', 1569000.00, NULL, '', 5, 178860.00, 30.00, 178830.00, 230.00, 52.00, 'Active', '2026-10-29 21:29:40', '2026-10-30 16:21:45');

-- --------------------------------------------------------

--
-- Table structure for table `sale_return_items`
--

CREATE TABLE `sale_return_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `sale_return_id` bigint(20) UNSIGNED NOT NULL,
  `returnNo` varchar(191) NOT NULL,
  `productCode` varchar(191) NOT NULL,
  `productName` varchar(191) NOT NULL,
  `variant_id` bigint(20) UNSIGNED DEFAULT NULL,
  `variant_name` varchar(191) DEFAULT NULL,
  `variant_value` varchar(191) DEFAULT NULL,
  `variant_added_price` decimal(15,2) NOT NULL DEFAULT 0.00,
  `qty` decimal(15,2) NOT NULL DEFAULT 0.00,
  `rate` decimal(15,2) NOT NULL DEFAULT 0.00,
  `cost_rate` decimal(15,2) NOT NULL DEFAULT 0.00,
  `discount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `condition` varchar(20) NOT NULL DEFAULT 'good',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sale_return_items`
--

INSERT INTO `sale_return_items` (`id`, `sale_return_id`, `returnNo`, `productCode`, `productName`, `variant_id`, `variant_name`, `variant_value`, `variant_added_price`, `qty`, `rate`, `cost_rate`, `discount`, `amount`, `condition`, `created_at`, `updated_at`) VALUES
(1, 1, 'SR-00001', '069P', 'Jambo Holder Folwer B-22', NULL, NULL, NULL, 0.00, 100.00, 1350.00, 750.00, 0.00, 135000.00, 'good', '2026-10-29 21:26:16', '2026-10-29 21:26:16'),
(2, 1, 'SR-00001', '227P', 'Light Plug 5+1 Porcline', NULL, NULL, NULL, 0.00, 15.00, 450.00, 150.00, 0.00, 6750.00, 'damage', '2026-10-29 21:26:16', '2026-10-29 21:26:16'),
(3, 1, 'SR-00001', '227P', 'Light Plug 5+1 Porcline', NULL, NULL, NULL, 0.00, 1.00, 450.00, 150.00, 0.00, 450.00, 'good', '2026-10-29 21:26:16', '2026-10-29 21:26:16'),
(4, 1, 'SR-00001', '280P', 'Cieling Holder', 2, 'Gray', 'Peice', 370.00, 15.00, 750.00, 160.00, 0.00, 11250.00, 'good', '2026-10-29 21:26:16', '2026-10-29 21:26:16'),
(9, 2, 'SR-00002', '070P', 'Jumbo Holder Flover E-27', NULL, NULL, NULL, 0.00, 15.00, 1350.00, 750.00, 0.00, 20250.00, 'good', '2026-10-30 16:21:45', '2026-10-30 16:21:45'),
(10, 2, 'SR-00002', '034P', 'Piano Socket Grace', NULL, NULL, NULL, 0.00, 52.00, 680.00, 350.00, 0.00, 35360.00, 'damage', '2026-10-30 16:21:45', '2026-10-30 16:21:45'),
(11, 2, 'SR-00002', '227P', 'Light Plug 5+1 Porcline', NULL, NULL, NULL, 0.00, 100.00, 450.00, 150.00, 0.00, 45000.00, 'good', '2026-10-30 16:21:45', '2026-10-30 16:21:45'),
(12, 2, 'SR-00002', '105P', 'Power Plug Porcline 5+1 Load Master', NULL, NULL, NULL, 0.00, 100.00, 580.00, 250.00, 0.00, 58000.00, 'good', '2026-10-30 16:21:45', '2026-10-30 16:21:45'),
(13, 2, 'SR-00002', '070P', 'Jumbo Holder Flover E-27', NULL, NULL, NULL, 0.00, 15.00, 1350.00, 750.00, 0.00, 20250.00, 'good', '2026-10-30 16:21:45', '2026-10-30 16:21:45');

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(191) NOT NULL,
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
('0bllplRaeox4RlkYjdz7TlYrPx00lrgvghvyaXqu', NULL, '142.250.32.9', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Mobile Safari/537.36 (compatible; Google-Read-Aloud; +https://support.google.com/webmasters/answer/1061943)', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoickc5R0VXV3N6bHlCRnJRTTRMMjlzdG81YmFOdmE4blAxNkV2ZGtOWSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vYWxmYXphbC50cmFkZWdhdGVway5jb20vbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1794281252),
('3KBPbIysALS1znCUWsTPEieE9VuJPTXLlwZD9c8T', NULL, '157.245.69.116', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.3', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiNFFBaXRoT0JiTHFreGpvd3RENjNSVjd4NWl4NXpqNElvTjZ0TjZmWSI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czozMDoiaHR0cDovL2FsZmF6YWwudHJhZGVnYXRlcGsuY29tIjt9czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzY6Imh0dHA6Ly9hbGZhemFsLnRyYWRlZ2F0ZXBrLmNvbS9sb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1794290640),
('7xwjx4wUOiXJiJurF1xNVxTNBkTcz4Bk7ft4t9UY', NULL, '142.250.32.7', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Mobile Safari/537.36 (compatible; Google-Read-Aloud; +https://support.google.com/webmasters/answer/1061943)', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiM3FUcVJPVEtGdTRLRWZzdXdHNFJuSjBIbWxBUkpRSUN4dXRvbFdHZSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vYWxmYXphbC50cmFkZWdhdGVway5jb20vbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1794280763),
('8VAv1ukwfOF2lmshz72KVQAk55vws8j3TRAth5jc', NULL, '142.250.32.8', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Mobile Safari/537.36 (compatible; Google-Read-Aloud; +https://support.google.com/webmasters/answer/1061943)', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoibFlMN2xYU0lLT1VneHlWTWMwRDhydHZvZzlVNk9Va2hEa1VkS244RyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vYWxmYXphbC50cmFkZWdhdGVway5jb20vbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1794281301),
('AZh5cMsZOd46e2qsRbEV3RKxN0BrBYUJ45v2IjLy', NULL, '142.250.32.7', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Mobile Safari/537.36 (compatible; Google-Read-Aloud; +https://support.google.com/webmasters/answer/1061943)', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiNm5IdE4zc1ZCclg1WmpDWmVJNnpJYkxNcFlUelZGNDBnVGtsRmlRVyI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czo1NDoiaHR0cHM6Ly9hbGZhemFsLnRyYWRlZ2F0ZXBrLmNvbS9yZXBvcnRzL21hcmtldC12b3VjaGVyIjt9czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NTQ6Imh0dHBzOi8vYWxmYXphbC50cmFkZWdhdGVway5jb20vcmVwb3J0cy9tYXJrZXQtdm91Y2hlciI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1794281208),
('Bu7EyUJ2VbSPfhyHCDcPyTw6TTI7ivtbiFuRyhWO', NULL, '34.101.35.127', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiVVA4OHM4YU9wbVZJUlNuNzYyMjhlUzhuWHdnNEhlcFFDMVBOYUpKcCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzE6Imh0dHBzOi8vYWxmYXphbC50cmFkZWdhdGVway5jb20iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1794283267),
('CdaxWRWAwLbav6zUxZkMGpZTepmXn0RSo0jteuC4', 2, '103.81.241.136', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 'YTo2OntzOjY6Il90b2tlbiI7czo0MDoiWkdnV2trNVZvdmFvWHRLcFE2ZXF3d0V0ZUFnRkhvd2FlcGtIOFlPbCI7czozOiJ1cmwiO2E6MDp7fXM6OToiX3ByZXZpb3VzIjthOjE6e3M6MzoidXJsIjtzOjM5OiJodHRwczovL2FsZmF6YWwudHJhZGVnYXRlcGsuY29tL3Byb2R1Y3QiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX1zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToyO3M6NDoiYXV0aCI7YToxOntzOjIxOiJwYXNzd29yZF9jb25maXJtZWRfYXQiO2k6MTc5NDI3NTQxMDt9fQ==', 1794278596),
('DZ3EanIIXkxHjode7gZLltik5cvSHBjY0gxXja5H', NULL, '142.250.32.9', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Mobile Safari/537.36 (compatible; Google-Read-Aloud; +https://support.google.com/webmasters/answer/1061943)', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiTjFtQjgyNWVZd1ZpNVhUQTlJNjV3Rk1ENWJ4ZUJRaTRiYUF2RnlYaSI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czo1MToiaHR0cHM6Ly9hbGZhemFsLnRyYWRlZ2F0ZXBrLmNvbS9hY2NvdW50LXRyYW5zYWN0aW9uIjt9czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NTE6Imh0dHBzOi8vYWxmYXphbC50cmFkZWdhdGVway5jb20vYWNjb3VudC10cmFuc2FjdGlvbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1794281086),
('IBY1yHio87vYT8UnKxbqbEudaEh71ZsBH8MeNAEX', NULL, '142.250.32.9', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Mobile Safari/537.36 (compatible; Google-Read-Aloud; +https://support.google.com/webmasters/answer/1061943)', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiWXVZNW5MMGZLWVdFZlBGRnNHVWwyaEFzaXlpQUowRUlSUDU4bFRRWSI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czo0NToiaHR0cHM6Ly9hbGZhemFsLnRyYWRlZ2F0ZXBrLmNvbS9wYXJ0eS1jaGVxdWVzIjt9czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NDU6Imh0dHBzOi8vYWxmYXphbC50cmFkZWdhdGVway5jb20vcGFydHktY2hlcXVlcyI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1794281300),
('iynLUJ2D3mrofAgT1v3IVEW6iU4Z37I75g3ZqaQF', NULL, '23.27.145.89', 'Mozilla/5.0 (X11; Linux aarch64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/157.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoieFY2OHBmZVc3UnJWRkVhcjFQNzJScjhuak41bUM1UjY5djF5STdLQiI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czozMToiaHR0cHM6Ly9hbGZhemFsLnRyYWRlZ2F0ZXBrLmNvbSI7fXM6OToiX3ByZXZpb3VzIjthOjE6e3M6MzoidXJsIjtzOjM3OiJodHRwczovL2FsZmF6YWwudHJhZGVnYXRlcGsuY29tL2xvZ2luIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1794292295),
('JEfc52TwKMYKjISyn83bA0OGPEqkDc66fFZ9RR2S', NULL, '34.101.35.127', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoicVdCc1VjUHYzclBNVUV5djVqem4wY3Z6dDBOTmNHZ0ZPQ0Y3OUM1ZSI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czozMToiaHR0cHM6Ly9hbGZhemFsLnRyYWRlZ2F0ZXBrLmNvbSI7fXM6OToiX3ByZXZpb3VzIjthOjE6e3M6MzoidXJsIjtzOjMxOiJodHRwczovL2FsZmF6YWwudHJhZGVnYXRlcGsuY29tIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1794283202),
('lFt2HMGxkcZKoXMO8GDzvmcMaEUvGrskSTHjlXVn', NULL, '141.148.153.213', 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Mobile/15E148 Safari/604.1', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiSW9FajhUVEQxVklGc1U3T2sxOWE1MVJBUGpwUk5HaDQ4d09RbXdNTyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vYWxmYXphbC50cmFkZWdhdGVway5jb20vbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1794290630),
('LPEI9qYOm5sFzrw9XS331HrlsSdR3qCweMWKx61h', NULL, '142.250.32.9', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Mobile Safari/537.36 (compatible; Google-Read-Aloud; +https://support.google.com/webmasters/answer/1061943)', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiR3JoT3hKSmJoeGNUSEVNQjh6YzJrQmxWNmVhdEVzQXVJUnhCOXNCQyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vYWxmYXphbC50cmFkZWdhdGVway5jb20vbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1794281208),
('n2xGXcgMxlRLHmKX9JQvzBAGjzmleXqgaqPKLM29', NULL, '142.250.32.8', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Mobile Safari/537.36 (compatible; Google-Read-Aloud; +https://support.google.com/webmasters/answer/1061943)', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiOUlyaXoyNTFKUFkyeDJJV21aR0dVVkdNeTBpQWtZbVNQb3NjQzBPRiI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czo0MzoiaHR0cHM6Ly9hbGZhemFsLnRyYWRlZ2F0ZXBrLmNvbS9zYWxlU3VtbWFyeSI7fXM6OToiX3ByZXZpb3VzIjthOjE6e3M6MzoidXJsIjtzOjQzOiJodHRwczovL2FsZmF6YWwudHJhZGVnYXRlcGsuY29tL3NhbGVTdW1tYXJ5Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1794281044),
('O5jY8IBfxvIFoQsfopPg2s1svvJpR11frMkoqp7P', NULL, '45.138.12.168', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/133.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiOWtXRkNFT1k1eEJlTmhZUU8xVEJtcFNRWlR3SkxObXpQVkhIZllFbiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NDY6Imh0dHBzOi8vYWxmYXphbC50cmFkZWdhdGVway5jb20vcGFzc3dvcmQvcmVzZXQiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1794291583),
('OGqsZob9oEdqvGwEirJfqVsEfoPi3HLZiHuykNCU', NULL, '142.250.32.7', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Mobile Safari/537.36 (compatible; Google-Read-Aloud; +https://support.google.com/webmasters/answer/1061943)', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoidnk4SXZ2bU91NlFRc3N2dnlXRHQ5T1BwZzhYZ0h5MWlEZDlXanJpZyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vYWxmYXphbC50cmFkZWdhdGVway5jb20vbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1794281071),
('ozBznWVua7n5PBvSN4lvf61ykKwo6KnhDowDa6bM', NULL, '45.138.12.168', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/133.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiOHFucDN6a3djelczYVhsMXZlS1VvOWpuODBKWVpobm1IcnpNM3c4dSI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czozMToiaHR0cHM6Ly9hbGZhemFsLnRyYWRlZ2F0ZXBrLmNvbSI7fXM6OToiX3ByZXZpb3VzIjthOjE6e3M6MzoidXJsIjtzOjMxOiJodHRwczovL2FsZmF6YWwudHJhZGVnYXRlcGsuY29tIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1794276976),
('qykDHZESbgt8QFPGv5ZnqtXxBL3HowrpNhjoCUdT', NULL, '45.138.12.168', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/133.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiaDVNRlBYTHZWVkF2R1R0MDFKc1hNRHIxOXRrRHlMVkhDYnZXamd6eiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vYWxmYXphbC50cmFkZWdhdGVway5jb20vbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1794276976),
('rJBVad3QZOPa196uPeaXca7tG5LUduhQdbOXsjhU', NULL, '141.148.153.213', 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Mobile/15E148 Safari/604.1', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiTjhwMGVaejdZNGZGa1Nta0xGMUtkQjB4NUJIcHpWUHhpbUdFdnB5SyI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czozMToiaHR0cHM6Ly9hbGZhemFsLnRyYWRlZ2F0ZXBrLmNvbSI7fXM6OToiX3ByZXZpb3VzIjthOjE6e3M6MzoidXJsIjtzOjMxOiJodHRwczovL2FsZmF6YWwudHJhZGVnYXRlcGsuY29tIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1794290629),
('rkvpiocHTmf1CIojmRSYFpEYTnsRT8aFADAC8WNk', NULL, '142.250.32.8', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Mobile Safari/537.36 (compatible; Google-Read-Aloud; +https://support.google.com/webmasters/answer/1061943)', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiRlU0eXdzdVBMeVpIWWRXdm51SEYzejduNEJiR0t2WXlEbTdOMEU2UyI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czozOToiaHR0cHM6Ly9hbGZhemFsLnRyYWRlZ2F0ZXBrLmNvbS9yZXBvcnRzIjt9czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzk6Imh0dHBzOi8vYWxmYXphbC50cmFkZWdhdGVway5jb20vcmVwb3J0cyI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1794280763),
('scebAOnApld2EfrI1JSK4fCjK36mx2xPqhug5gVd', NULL, '142.250.32.9', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Mobile Safari/537.36 (compatible; Google-Read-Aloud; +https://support.google.com/webmasters/answer/1061943)', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoia3owbVA3bXNDdVVGOVRRYlZHUTFzMWNvWEgwdkZaVkFkYnN5b0NiNiI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czo0MToiaHR0cHM6Ly9hbGZhemFsLnRyYWRlZ2F0ZXBrLmNvbS9uZXctb3JkZXIiO31zOjk6Il9wcmV2aW91cyI7YToxOntzOjM6InVybCI7czo0MToiaHR0cHM6Ly9hbGZhemFsLnRyYWRlZ2F0ZXBrLmNvbS9uZXctb3JkZXIiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1794281071),
('sRiceeGCAtrrQPKyxhMuaOyYoZKdq0yZRzrFq6Kc', NULL, '81.168.41.75', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiV0d3OENvdUVoRUxxZXJBQzhNNHdTVHVLMjczcVJFcGt6d0syb0ZxUCI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czozMToiaHR0cHM6Ly9hbGZhemFsLnRyYWRlZ2F0ZXBrLmNvbSI7fXM6OToiX3ByZXZpb3VzIjthOjE6e3M6MzoidXJsIjtzOjM3OiJodHRwczovL2FsZmF6YWwudHJhZGVnYXRlcGsuY29tL2xvZ2luIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1794291396),
('SS1kUApOEhK2bdKT03QcZeDkHUTWZDOIPV7Ij7ut', NULL, '142.250.32.8', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Mobile Safari/537.36 (compatible; Google-Read-Aloud; +https://support.google.com/webmasters/answer/1061943)', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiT0ZUTzRGY2ozamRYd3I1ZzB3ZldIdlBwaDladkt5ODU2eDczM0NjeSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vYWxmYXphbC50cmFkZWdhdGVway5jb20vbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1794281044),
('tIfHfIGB01M6ZAOZJFq2Tin9NTXkuCCaloY7lQuV', NULL, '142.250.32.8', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Mobile Safari/537.36 (compatible; Google-Read-Aloud; +https://support.google.com/webmasters/answer/1061943)', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiMHJFU2RIODdvWVc4aVBuM1pUbUx3RHJTYTgwQWVxM2FsdXhkRVpucyI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czoxMDk6Imh0dHBzOi8vYWxmYXphbC50cmFkZWdhdGVway5jb20vcmVwb3J0cy9tYXJrZXQtdm91Y2hlci9wcmludD9hcmVhPU1HUCZmcm9tX2RhdGU9MjAyNi0wOC0wMSZ0b19kYXRlPTIwMjYtMTEtMTAiO31zOjk6Il9wcmV2aW91cyI7YToxOntzOjM6InVybCI7czoxMDk6Imh0dHBzOi8vYWxmYXphbC50cmFkZWdhdGVway5jb20vcmVwb3J0cy9tYXJrZXQtdm91Y2hlci9wcmludD9hcmVhPU1HUCZmcm9tX2RhdGU9MjAyNi0wOC0wMSZ0b19kYXRlPTIwMjYtMTEtMTAiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1794281252),
('Xl76oWDcJBwuYkTRrpySx0vZYNt2bHklEaTttOCQ', 2, '103.81.241.136', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/157.0.0.0 Safari/537.36', 'YTo2OntzOjY6Il90b2tlbiI7czo0MDoiVlVIdHlZRjNaQjlPd01QQWNGUDM0WmxoV05BaEZLYWJtQXZmdTNYciI7czozOiJ1cmwiO2E6MDp7fXM6OToiX3ByZXZpb3VzIjthOjE6e3M6MzoidXJsIjtzOjQxOiJodHRwczovL2FsZmF6YWwudHJhZGVnYXRlcGsuY29tL2Rhc2hib2FyZCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjI7czo0OiJhdXRoIjthOjE6e3M6MjE6InBhc3N3b3JkX2NvbmZpcm1lZF9hdCI7aToxNzk0Mjc5NjI4O319', 1794281206),
('XxwFYHBhrNQvoGrjCRfRFublFGhzUefEhZWtsL5H', 2, '154.192.180.129', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Mobile Safari/537.36', 'YTo2OntzOjY6Il90b2tlbiI7czo0MDoiQWJRMjFyU2NEd0FCblQ0d21XUnVqZGRPVWxKeThINTNHSGdScWl5SyI7czozOiJ1cmwiO2E6MDp7fXM6OToiX3ByZXZpb3VzIjthOjE6e3M6MzoidXJsIjtzOjQxOiJodHRwczovL2FsZmF6YWwudHJhZGVnYXRlcGsuY29tL2Rhc2hib2FyZCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjI7czo0OiJhdXRoIjthOjE6e3M6MjE6InBhc3N3b3JkX2NvbmZpcm1lZF9hdCI7aToxNzk0MjgwNzEyO319', 1794285578),
('zSQm2ghJkAFpevyFnrZerYE70OWkPcZNjjkEVkaF', NULL, '142.250.32.7', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Mobile Safari/537.36 (compatible; Google-Read-Aloud; +https://support.google.com/webmasters/answer/1061943)', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoidFlSdE9PS1UwZ0RVS21ES1h1UHpIbEFsTmp6TzhDMHRPdkRDZTZmayI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vYWxmYXphbC50cmFkZWdhdGVway5jb20vbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1794281087);

-- --------------------------------------------------------

--
-- Table structure for table `supplier_transactions`
--

CREATE TABLE `supplier_transactions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `txn_no` varchar(50) NOT NULL,
  `supplier_code` varchar(50) NOT NULL,
  `supplier_name` varchar(255) DEFAULT NULL,
  `txn_type` varchar(20) NOT NULL,
  `entry_type` varchar(20) DEFAULT NULL,
  `bank_account` varchar(100) DEFAULT NULL,
  `txn_date` date NOT NULL,
  `total_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `remarks` varchar(255) DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'Active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `supplier_transactions`
--

INSERT INTO `supplier_transactions` (`id`, `txn_no`, `supplier_code`, `supplier_name`, `txn_type`, `entry_type`, `bank_account`, `txn_date`, `total_amount`, `remarks`, `status`, `created_at`, `updated_at`) VALUES
(1, 'ST-0001', 'FAISAL', 'Faisal', 'Purchase', NULL, NULL, '2026-10-28', 75000.00, NULL, 'Active', '2026-10-28 18:15:00', '2026-10-28 18:15:16'),
(2, 'ST-0002', 'FAISAL', 'Faisal', 'Purchase', NULL, NULL, '2026-10-28', 75000.00, NULL, 'Active', '2026-10-28 18:22:16', '2026-10-28 18:22:16'),
(3, 'ST-0003', 'FAISAL', 'Faisal', 'Cash', 'pay', NULL, '2026-10-28', 10000.00, 'cash by hand', 'Active', '2026-10-28 18:31:47', '2026-10-28 18:43:08');

-- --------------------------------------------------------

--
-- Table structure for table `supplier_transaction_items`
--

CREATE TABLE `supplier_transaction_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `supplier_transaction_id` bigint(20) UNSIGNED NOT NULL,
  `txn_no` varchar(50) NOT NULL,
  `product_code` varchar(50) NOT NULL,
  `product_name` varchar(255) DEFAULT NULL,
  `work_name` varchar(100) DEFAULT NULL,
  `variant_id` bigint(20) UNSIGNED DEFAULT NULL,
  `variant_name` varchar(100) DEFAULT NULL,
  `variant_value` varchar(100) DEFAULT NULL,
  `qty` decimal(15,2) NOT NULL DEFAULT 0.00,
  `rate` decimal(15,2) NOT NULL DEFAULT 0.00,
  `amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `supplier_transaction_items`
--

INSERT INTO `supplier_transaction_items` (`id`, `supplier_transaction_id`, `txn_no`, `product_code`, `product_name`, `work_name`, `variant_id`, `variant_name`, `variant_value`, `qty`, `rate`, `amount`, `created_at`, `updated_at`) VALUES
(2, 1, 'ST-0001', '069P', 'Jambo Holder Folwer B-22', NULL, NULL, NULL, NULL, 100.00, 750.00, 75000.00, '2026-10-28 18:15:16', '2026-10-28 18:15:16'),
(3, 2, 'ST-0002', '070P', 'Jumbo Holder Flover E-27', NULL, NULL, NULL, NULL, 100.00, 750.00, 75000.00, '2026-10-28 18:22:16', '2026-10-28 18:22:16'),
(5, 3, 'ST-0003', 'CASH', 'cash by hand', NULL, NULL, NULL, NULL, 1.00, 10000.00, 10000.00, '2026-10-28 18:43:08', '2026-10-28 18:43:08');

-- --------------------------------------------------------

--
-- Table structure for table `units`
--

CREATE TABLE `units` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(191) NOT NULL,
  `symbol` varchar(16) NOT NULL,
  `base_unit_id` bigint(20) UNSIGNED DEFAULT NULL,
  `to_base_factor` decimal(18,6) NOT NULL DEFAULT 1.000000,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `unit_per_pack`
--

CREATE TABLE `unit_per_pack` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `unitName` varchar(191) NOT NULL,
  `piecesPerUnit` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `unit_per_pack`
--

INSERT INTO `unit_per_pack` (`id`, `unitName`, `piecesPerUnit`) VALUES
(1, 'BOX', 12),
(2, 'BOX1', 1),
(3, 'KG', 25),
(4, 'PIECE', 1),
(5, 'DOZEN', 1),
(6, 'GURS', 1),
(7, 'FEET', 1);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(191) NOT NULL,
  `email` varchar(191) NOT NULL,
  `role` varchar(32) NOT NULL DEFAULT 'client',
  `access_customized` tinyint(1) NOT NULL DEFAULT 0,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(191) NOT NULL,
  `password_plain` varchar(191) DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `last_login_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `role`, `access_customized`, `email_verified_at`, `password`, `password_plain`, `remember_token`, `last_login_at`, `created_at`, `updated_at`) VALUES
(1, 'Safdar', 'safder384@gmail.com', 'admin', 0, NULL, '$2y$12$fWmJDoIJ7rr3qyppl6JMT.pZwxwh5sARZ8oVdfZFfEE0NU3F6r9Xi', NULL, 'nifQVlaGzF14EsfJBXRH1x7gIP57aq7dlaZ7sIJuPDB0e7Wabop540gGgH64', '2026-10-16 16:20:46', '2026-01-07 16:52:50', '2026-10-16 16:20:46'),
(2, 'Administrator', 'admin@tradegate.local', 'admin', 0, NULL, '$2y$12$4/xFQVjoY5NIlW46njPD0u3TUl49a6qAmdLsEXZBfH9c52a8PHm/m', 'Admin@123', NULL, '2026-11-10 03:18:32', '2026-05-07 21:54:44', '2026-11-10 03:18:32'),
(3, 'usman', 'usman@gmail.com', 'client', 1, NULL, '$2y$12$ROpCmWE3.kTHWSjQjBsF9urHRDcQP2fqDehse8ycGw6mmu/erynQ.', '123456789', NULL, '2026-10-25 03:09:41', '2026-05-07 23:35:37', '2026-10-25 03:09:41');

-- --------------------------------------------------------

--
-- Table structure for table `user_module_permissions`
--

CREATE TABLE `user_module_permissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `module` varchar(64) NOT NULL,
  `allowed` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `voucher_group_sequences`
--

CREATE TABLE `voucher_group_sequences` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `accounts`
--
ALTER TABLE `accounts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `accounts_account_code_unique` (`account_code`);

--
-- Indexes for table `bank_accounts`
--
ALTER TABLE `bank_accounts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `boms`
--
ALTER TABLE `boms`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `boms_productcode_unique` (`productCode`);

--
-- Indexes for table `bom_items`
--
ALTER TABLE `bom_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `bom_items_bom_id_foreign` (`bom_id`),
  ADD KEY `bom_items_uom_id_foreign` (`uom_id`);

--
-- Indexes for table `bom_labors`
--
ALTER TABLE `bom_labors`
  ADD PRIMARY KEY (`id`),
  ADD KEY `bom_labors_bom_id_foreign` (`bom_id`);

--
-- Indexes for table `bom_labour_works`
--
ALTER TABLE `bom_labour_works`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `bom_labour_works_work_name_unique` (`work_name`);

--
-- Indexes for table `bom_materials`
--
ALTER TABLE `bom_materials`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `bom_materials_material_code_unique` (`material_code`);

--
-- Indexes for table `bom_overheads`
--
ALTER TABLE `bom_overheads`
  ADD PRIMARY KEY (`id`),
  ADD KEY `bom_overheads_bom_id_foreign` (`bom_id`);

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
-- Indexes for table `client_url_restrictions`
--
ALTER TABLE `client_url_restrictions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `colour`
--
ALTER TABLE `colour`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `colour_colourname_unique` (`colourName`);

--
-- Indexes for table `customerareas`
--
ALTER TABLE `customerareas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `customerareas_areacode_unique` (`areaCode`);

--
-- Indexes for table `customerledgers`
--
ALTER TABLE `customerledgers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `customer_catagory`
--
ALTER TABLE `customer_catagory`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `customer_catagory_catagoryname_unique` (`catagoryName`);

--
-- Indexes for table `customer_cheques`
--
ALTER TABLE `customer_cheques`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `customer_cheques_customer_cheque_unique` (`customer_code`,`cheque_no`),
  ADD KEY `customer_cheques_customer_code_index` (`customer_code`),
  ADD KEY `customer_cheques_customer_area_index` (`customer_area`),
  ADD KEY `customer_cheques_due_date_index` (`due_date`),
  ADD KEY `customer_cheques_status_index` (`status`),
  ADD KEY `customer_cheques_ledger_id_index` (`ledger_id`),
  ADD KEY `customer_cheques_worker_id_index` (`worker_id`);

--
-- Indexes for table `customer_product_rates`
--
ALTER TABLE `customer_product_rates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `cpr_party_prod_var_type_date_uq` (`customerCode`,`productCode`,`variant_id`,`rateType`,`effective_from`);

--
-- Indexes for table `expenses`
--
ALTER TABLE `expenses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `expenses_expensecode_unique` (`expenseCode`);

--
-- Indexes for table `expense_ledger`
--
ALTER TABLE `expense_ledger`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `fitting_labours`
--
ALTER TABLE `fitting_labours`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `fitting_labours_txn_no_unique` (`txn_no`),
  ADD KEY `fitting_labours_labour_id_index` (`labour_id`),
  ADD KEY `fitting_labours_labour_code_index` (`labour_code`),
  ADD KEY `fitting_labours_txn_type_index` (`txn_type`),
  ADD KEY `fitting_labours_txn_date_index` (`txn_date`);

--
-- Indexes for table `fitting_labour_items`
--
ALTER TABLE `fitting_labour_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fli_fitting_labour_id_index` (`fitting_labour_id`),
  ADD KEY `fli_txn_no_index` (`txn_no`),
  ADD KEY `fli_product_code_index` (`product_code`);

--
-- Indexes for table `fitting_product_works`
--
ALTER TABLE `fitting_product_works`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `fpw_product_work_unique` (`product_code`,`work_name`),
  ADD KEY `fpw_product_code_index` (`product_code`),
  ADD KEY `fpw_work_name_index` (`work_name`);

--
-- Indexes for table `fitting_work_stocks`
--
ALTER TABLE `fitting_work_stocks`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `fws_product_work_unique` (`product_code`,`work_name`),
  ADD KEY `fws_product_code_index` (`product_code`),
  ADD KEY `fws_work_name_index` (`work_name`);

--
-- Indexes for table `guds`
--
ALTER TABLE `guds`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `guds_gudsname_unique` (`gudsName`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `labours`
--
ALTER TABLE `labours`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `labours_labourcode_unique` (`labourCode`);

--
-- Indexes for table `labour_account_transactions`
--
ALTER TABLE `labour_account_transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `labour_account_transactions_labour_id_transactiondate_index` (`labour_id`,`transactionDate`);

--
-- Indexes for table `ledgers`
--
ALTER TABLE `ledgers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ledgers_account_id_foreign` (`account_id`);

--
-- Indexes for table `login_activities`
--
ALTER TABLE `login_activities`
  ADD PRIMARY KEY (`id`),
  ADD KEY `login_activities_user_id_foreign` (`user_id`);

--
-- Indexes for table `market_vouchers`
--
ALTER TABLE `market_vouchers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `market_vouchers_market_vouchers_id_index` (`market_vouchers_id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `newcustomers`
--
ALTER TABLE `newcustomers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `newcustomers_custcode_unique` (`custCode`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `orders_orderno_unique` (`orderNo`),
  ADD KEY `orders_status_index` (`status`),
  ADD KEY `orders_order_date_index` (`orderDate`),
  ADD KEY `orders_customer_code_index` (`customerCode`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_items_order_id_foreign` (`order_id`),
  ADD KEY `order_items_product_code_index` (`productCode`);

--
-- Indexes for table `packing`
--
ALTER TABLE `packing`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `packing_name_unit_qty_unique` (`pakingName`,`baseUnit`,`qtyInPack`);

--
-- Indexes for table `pack_sizes`
--
ALTER TABLE `pack_sizes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `pack_sizes_unique_combo` (`packing_type`,`unit_name`,`qty`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `product_catagory`
--
ALTER TABLE `product_catagory`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `product_catagory_catagoryname_unique` (`catagoryName`);

--
-- Indexes for table `product_company`
--
ALTER TABLE `product_company`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `product_company_companyname_unique` (`companyName`);

--
-- Indexes for table `product_cost_versions`
--
ALTER TABLE `product_cost_versions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `product_cost_versions_productcode_effective_from_unique` (`productCode`,`effective_from`);

--
-- Indexes for table `product_info`
--
ALTER TABLE `product_info`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `product_info_productcode_unique` (`productCode`),
  ADD KEY `product_info_uom_id_foreign` (`uom_id`);

--
-- Indexes for table `product_variants`
--
ALTER TABLE `product_variants`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_variants_productcode_foreign` (`productCode`);

--
-- Indexes for table `profit_loss_closings`
--
ALTER TABLE `profit_loss_closings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `purchase_invoices`
--
ALTER TABLE `purchase_invoices`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `purchase_invoices_invoiceno_unique` (`invoiceNo`),
  ADD KEY `purchase_invoices_supplier_code_index` (`supplierCode`),
  ADD KEY `purchase_invoices_invoice_date_index` (`invoiceDate`),
  ADD KEY `purchase_invoices_payment_status_index` (`payment_status`);

--
-- Indexes for table `purchase_invoice_items`
--
ALTER TABLE `purchase_invoice_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `purchase_invoice_items_purchase_invoice_id_foreign` (`purchase_invoice_id`),
  ADD KEY `purchase_invoice_items_product_code_index` (`productCode`);

--
-- Indexes for table `purchase_invoice_payments`
--
ALTER TABLE `purchase_invoice_payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `purchase_invoice_payments_purchase_invoice_id_foreign` (`purchase_invoice_id`),
  ADD KEY `purchase_invoice_payments_invoiceno_index` (`invoiceNo`);

--
-- Indexes for table `purchase_returns`
--
ALTER TABLE `purchase_returns`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `purchase_returns_returnno_unique` (`returnNo`),
  ADD KEY `purchase_returns_suppliercode_index` (`supplierCode`),
  ADD KEY `purchase_returns_returndate_index` (`returnDate`);

--
-- Indexes for table `purchase_return_items`
--
ALTER TABLE `purchase_return_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `purchase_return_items_purchase_return_id_foreign` (`purchase_return_id`),
  ADD KEY `purchase_return_items_returnno_index` (`returnNo`),
  ADD KEY `purchase_return_items_productcode_index` (`productCode`);

--
-- Indexes for table `recovery_bank_distributions`
--
ALTER TABLE `recovery_bank_distributions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `recovery_bank_distributions_voucher_id_foreign` (`voucher_id`);

--
-- Indexes for table `sale_invoices`
--
ALTER TABLE `sale_invoices`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sale_invoices_invoiceno_unique` (`invoiceNo`),
  ADD KEY `sale_invoices_customer_code_index` (`customerCode`),
  ADD KEY `sale_invoices_invoice_date_index` (`invoiceDate`),
  ADD KEY `sale_invoices_date_customer_index` (`invoiceDate`,`customerCode`),
  ADD KEY `sale_invoices_payment_status_index` (`payment_status`);

--
-- Indexes for table `sale_invoice_bank_payments`
--
ALTER TABLE `sale_invoice_bank_payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sale_invoice_bank_payments_sale_invoice_id_foreign` (`sale_invoice_id`),
  ADD KEY `sale_invoice_bank_payments_bank_account_id_foreign` (`bank_account_id`);

--
-- Indexes for table `sale_invoice_items`
--
ALTER TABLE `sale_invoice_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sale_invoice_items_invoice_id_index` (`sale_invoice_id`),
  ADD KEY `sale_invoice_items_invoice_no_index` (`invoiceNo`),
  ADD KEY `sale_invoice_items_product_code_index` (`productCode`);

--
-- Indexes for table `sale_returns`
--
ALTER TABLE `sale_returns`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sale_returns_returnno_unique` (`returnNo`),
  ADD KEY `sale_returns_customercode_index` (`customerCode`),
  ADD KEY `sale_returns_returndate_index` (`returnDate`);

--
-- Indexes for table `sale_return_items`
--
ALTER TABLE `sale_return_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sale_return_items_sale_return_id_foreign` (`sale_return_id`),
  ADD KEY `sale_return_items_returnno_index` (`returnNo`),
  ADD KEY `sale_return_items_productcode_index` (`productCode`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `supplier_transactions`
--
ALTER TABLE `supplier_transactions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `supplier_transactions_txn_no_unique` (`txn_no`),
  ADD KEY `supplier_transactions_supplier_code_index` (`supplier_code`),
  ADD KEY `supplier_transactions_txn_type_index` (`txn_type`),
  ADD KEY `supplier_transactions_txn_date_index` (`txn_date`);

--
-- Indexes for table `supplier_transaction_items`
--
ALTER TABLE `supplier_transaction_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sti_transaction_id_index` (`supplier_transaction_id`),
  ADD KEY `sti_txn_no_index` (`txn_no`),
  ADD KEY `sti_product_code_index` (`product_code`);

--
-- Indexes for table `units`
--
ALTER TABLE `units`
  ADD PRIMARY KEY (`id`),
  ADD KEY `units_base_unit_id_foreign` (`base_unit_id`);

--
-- Indexes for table `unit_per_pack`
--
ALTER TABLE `unit_per_pack`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unit_per_pack_unitname_unique` (`unitName`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- Indexes for table `user_module_permissions`
--
ALTER TABLE `user_module_permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_module_permissions_user_id_module_unique` (`user_id`,`module`);

--
-- Indexes for table `voucher_group_sequences`
--
ALTER TABLE `voucher_group_sequences`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `accounts`
--
ALTER TABLE `accounts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `bank_accounts`
--
ALTER TABLE `bank_accounts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `boms`
--
ALTER TABLE `boms`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bom_items`
--
ALTER TABLE `bom_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bom_labors`
--
ALTER TABLE `bom_labors`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bom_labour_works`
--
ALTER TABLE `bom_labour_works`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bom_materials`
--
ALTER TABLE `bom_materials`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bom_overheads`
--
ALTER TABLE `bom_overheads`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `client_url_restrictions`
--
ALTER TABLE `client_url_restrictions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `colour`
--
ALTER TABLE `colour`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `customerareas`
--
ALTER TABLE `customerareas`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `customerledgers`
--
ALTER TABLE `customerledgers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT for table `customer_catagory`
--
ALTER TABLE `customer_catagory`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `customer_cheques`
--
ALTER TABLE `customer_cheques`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `customer_product_rates`
--
ALTER TABLE `customer_product_rates`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `expenses`
--
ALTER TABLE `expenses`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `expense_ledger`
--
ALTER TABLE `expense_ledger`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `fitting_labours`
--
ALTER TABLE `fitting_labours`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `fitting_labour_items`
--
ALTER TABLE `fitting_labour_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `fitting_product_works`
--
ALTER TABLE `fitting_product_works`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `fitting_work_stocks`
--
ALTER TABLE `fitting_work_stocks`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `guds`
--
ALTER TABLE `guds`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `labours`
--
ALTER TABLE `labours`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `labour_account_transactions`
--
ALTER TABLE `labour_account_transactions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `ledgers`
--
ALTER TABLE `ledgers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `login_activities`
--
ALTER TABLE `login_activities`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `market_vouchers`
--
ALTER TABLE `market_vouchers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=175;

--
-- AUTO_INCREMENT for table `newcustomers`
--
ALTER TABLE `newcustomers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `packing`
--
ALTER TABLE `packing`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `pack_sizes`
--
ALTER TABLE `pack_sizes`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `product_catagory`
--
ALTER TABLE `product_catagory`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `product_company`
--
ALTER TABLE `product_company`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `product_cost_versions`
--
ALTER TABLE `product_cost_versions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_info`
--
ALTER TABLE `product_info`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `product_variants`
--
ALTER TABLE `product_variants`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `profit_loss_closings`
--
ALTER TABLE `profit_loss_closings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `purchase_invoices`
--
ALTER TABLE `purchase_invoices`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `purchase_invoice_items`
--
ALTER TABLE `purchase_invoice_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `purchase_invoice_payments`
--
ALTER TABLE `purchase_invoice_payments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `purchase_returns`
--
ALTER TABLE `purchase_returns`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `purchase_return_items`
--
ALTER TABLE `purchase_return_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `recovery_bank_distributions`
--
ALTER TABLE `recovery_bank_distributions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sale_invoices`
--
ALTER TABLE `sale_invoices`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `sale_invoice_bank_payments`
--
ALTER TABLE `sale_invoice_bank_payments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sale_invoice_items`
--
ALTER TABLE `sale_invoice_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `sale_returns`
--
ALTER TABLE `sale_returns`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `sale_return_items`
--
ALTER TABLE `sale_return_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `supplier_transactions`
--
ALTER TABLE `supplier_transactions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `supplier_transaction_items`
--
ALTER TABLE `supplier_transaction_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `units`
--
ALTER TABLE `units`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `unit_per_pack`
--
ALTER TABLE `unit_per_pack`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `user_module_permissions`
--
ALTER TABLE `user_module_permissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `voucher_group_sequences`
--
ALTER TABLE `voucher_group_sequences`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bom_items`
--
ALTER TABLE `bom_items`
  ADD CONSTRAINT `bom_items_bom_id_foreign` FOREIGN KEY (`bom_id`) REFERENCES `boms` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bom_items_uom_id_foreign` FOREIGN KEY (`uom_id`) REFERENCES `units` (`id`);

--
-- Constraints for table `bom_labors`
--
ALTER TABLE `bom_labors`
  ADD CONSTRAINT `bom_labors_bom_id_foreign` FOREIGN KEY (`bom_id`) REFERENCES `boms` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `bom_overheads`
--
ALTER TABLE `bom_overheads`
  ADD CONSTRAINT `bom_overheads_bom_id_foreign` FOREIGN KEY (`bom_id`) REFERENCES `boms` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `fitting_labour_items`
--
ALTER TABLE `fitting_labour_items`
  ADD CONSTRAINT `fli_fitting_labour_fk` FOREIGN KEY (`fitting_labour_id`) REFERENCES `fitting_labours` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `ledgers`
--
ALTER TABLE `ledgers`
  ADD CONSTRAINT `ledgers_account_id_foreign` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `login_activities`
--
ALTER TABLE `login_activities`
  ADD CONSTRAINT `login_activities_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_info`
--
ALTER TABLE `product_info`
  ADD CONSTRAINT `product_info_uom_id_foreign` FOREIGN KEY (`uom_id`) REFERENCES `units` (`id`);

--
-- Constraints for table `product_variants`
--
ALTER TABLE `product_variants`
  ADD CONSTRAINT `product_variants_productcode_foreign` FOREIGN KEY (`productCode`) REFERENCES `product_info` (`productCode`) ON DELETE CASCADE;

--
-- Constraints for table `purchase_invoice_items`
--
ALTER TABLE `purchase_invoice_items`
  ADD CONSTRAINT `purchase_invoice_items_purchase_invoice_id_foreign` FOREIGN KEY (`purchase_invoice_id`) REFERENCES `purchase_invoices` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `purchase_invoice_payments`
--
ALTER TABLE `purchase_invoice_payments`
  ADD CONSTRAINT `purchase_invoice_payments_purchase_invoice_id_foreign` FOREIGN KEY (`purchase_invoice_id`) REFERENCES `purchase_invoices` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `purchase_return_items`
--
ALTER TABLE `purchase_return_items`
  ADD CONSTRAINT `purchase_return_items_purchase_return_id_foreign` FOREIGN KEY (`purchase_return_id`) REFERENCES `purchase_returns` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `recovery_bank_distributions`
--
ALTER TABLE `recovery_bank_distributions`
  ADD CONSTRAINT `recovery_bank_distributions_voucher_id_foreign` FOREIGN KEY (`voucher_id`) REFERENCES `market_vouchers` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `sale_invoice_bank_payments`
--
ALTER TABLE `sale_invoice_bank_payments`
  ADD CONSTRAINT `sale_invoice_bank_payments_bank_account_id_foreign` FOREIGN KEY (`bank_account_id`) REFERENCES `bank_accounts` (`id`),
  ADD CONSTRAINT `sale_invoice_bank_payments_sale_invoice_id_foreign` FOREIGN KEY (`sale_invoice_id`) REFERENCES `sale_invoices` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `sale_invoice_items`
--
ALTER TABLE `sale_invoice_items`
  ADD CONSTRAINT `sale_invoice_items_sale_invoice_id_foreign` FOREIGN KEY (`sale_invoice_id`) REFERENCES `sale_invoices` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `sale_return_items`
--
ALTER TABLE `sale_return_items`
  ADD CONSTRAINT `sale_return_items_sale_return_id_foreign` FOREIGN KEY (`sale_return_id`) REFERENCES `sale_returns` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `supplier_transaction_items`
--
ALTER TABLE `supplier_transaction_items`
  ADD CONSTRAINT `sti_transaction_fk` FOREIGN KEY (`supplier_transaction_id`) REFERENCES `supplier_transactions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `units`
--
ALTER TABLE `units`
  ADD CONSTRAINT `units_base_unit_id_foreign` FOREIGN KEY (`base_unit_id`) REFERENCES `units` (`id`);

--
-- Constraints for table `user_module_permissions`
--
ALTER TABLE `user_module_permissions`
  ADD CONSTRAINT `user_module_permissions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
