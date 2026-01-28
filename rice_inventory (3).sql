-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 28, 2026 at 09:59 AM
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
-- Database: `rice_inventory`
--

-- --------------------------------------------------------

--
-- Table structure for table `account_payable`
--

CREATE TABLE `account_payable` (
  `ap_id` int(11) NOT NULL,
  `purchase_id` int(11) DEFAULT NULL,
  `supplier_id` int(11) DEFAULT NULL,
  `total_amount` decimal(10,2) DEFAULT NULL,
  `amount_paid` decimal(10,2) DEFAULT NULL,
  `balance` decimal(10,2) DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `status` enum('paid','unpaid','partial','overdue') DEFAULT NULL,
  `approved` tinyint(1) NOT NULL DEFAULT 0,
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `account_payable`
--

INSERT INTO `account_payable` (`ap_id`, `purchase_id`, `supplier_id`, `total_amount`, `amount_paid`, `balance`, `due_date`, `status`, `approved`, `approved_by`, `approved_at`, `created_at`) VALUES
(1, 2, 1, 4000.00, 4000.00, 0.00, '2026-02-06', 'paid', 1, 5, '2026-01-28 07:14:37', '2026-01-28 06:48:30'),
(2, 3, 1, 10000.00, 10000.00, 0.00, '2026-02-06', 'paid', 1, 5, '2026-01-28 07:55:29', '2026-01-28 07:54:07'),
(3, 4, 1, 0.00, 0.00, 0.00, '2026-02-08', 'unpaid', 1, 5, '2026-01-28 08:40:35', '2026-01-28 08:38:18'),
(4, 6, 1, 11200.00, 11200.00, 0.00, '2026-02-15', 'paid', 1, 5, '2026-01-28 08:55:26', '2026-01-28 08:53:54');

-- --------------------------------------------------------

--
-- Table structure for table `account_receivable`
--

CREATE TABLE `account_receivable` (
  `ar_id` int(11) NOT NULL,
  `sales_id` int(11) DEFAULT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `total_amount` decimal(10,2) DEFAULT NULL,
  `amount_paid` decimal(10,2) DEFAULT NULL,
  `balance` decimal(10,2) DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `status` enum('paid','unpaid','partial','overdue') DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `account_receivable`
--

INSERT INTO `account_receivable` (`ar_id`, `sales_id`, `customer_id`, `total_amount`, `amount_paid`, `balance`, `due_date`, `status`, `created_at`) VALUES
(1, 16, 2, 1815.00, 1815.00, 0.00, '2026-01-31', 'paid', '2026-01-26 09:26:19'),
(2, 17, 1, 680.00, 680.00, 0.00, '2026-02-05', 'paid', '2026-01-26 10:42:27'),
(3, 19, 2, 550.00, 550.00, 0.00, '2026-02-12', 'paid', '2026-01-26 11:02:37'),
(4, 20, 3, 1375.00, 1375.00, 0.00, '2026-02-12', 'paid', '2026-01-27 03:02:10'),
(5, 21, 4, 1375.00, 1375.00, 0.00, '2026-01-30', 'paid', '2026-01-27 06:10:04'),
(6, 22, 5, 1375.00, 1375.00, 0.00, '2026-02-04', 'paid', '2026-01-27 07:24:29'),
(7, 26, 5, 680.00, 680.00, 0.00, '2026-02-14', 'paid', '2026-01-27 07:34:22'),
(8, 31, 3, 55.00, 55.00, 0.00, '2026-01-30', 'paid', '2026-01-28 08:15:42');

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `activity_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `activity_type` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `activity_logs`
--

INSERT INTO `activity_logs` (`activity_id`, `user_id`, `activity_type`, `description`, `created_at`) VALUES
(1, 1, 'USER_CREATE', 'Created new user (owner): sydney', '2026-01-25 06:32:10'),
(2, 1, 'USER_STATUS', 'Changed user_id 4 status to inactive', '2026-01-25 06:58:27'),
(3, 1, 'USER_STATUS', 'Changed user_id 3 status to inactive', '2026-01-25 06:58:32'),
(4, 1, 'USER_CREATE', 'Created new user (owner): kriz', '2026-01-25 07:26:32'),
(5, 2, 'SALE_CREATED', 'Created sale #3 total ₱10,000,000.00', '2026-01-25 09:12:56'),
(6, 2, 'DELIVERY_UPDATE', 'Updated delivery receipt for sale #3 (status: pending)', '2026-01-25 09:13:47'),
(7, 2, 'PAYMENT_REQUEST', 'Sent payment request for sale #3 to 09859958194', '2026-01-25 09:14:09'),
(8, 2, 'SALE_CREATED', 'Created sale #4 total ₱18,000.00', '2026-01-25 09:37:37'),
(9, 2, 'SALE_CREATED', 'Created sale #0 total ₱20,000.00', '2026-01-25 10:45:38'),
(10, 2, 'SALE_CREATED', 'Created sale #0 total ₱18,000.00', '2026-01-25 10:45:55'),
(11, 2, 'SALE_CREATE', 'Created sale #8 (CASH) total: 340', '2026-01-25 11:17:36'),
(12, 2, 'SALE_CREATE', 'Created sale #18 (paid) total ₱1,700.00', '2026-01-26 11:01:43'),
(13, 2, 'SALE_CREATE', 'Created sale #19 (unpaid) total ₱550.00', '2026-01-26 11:02:37'),
(14, 2, 'PAYMENT_RECEIVED', 'Payment received for Sale #19 amount ₱250.00', '2026-01-26 11:03:08'),
(15, 2, 'PAYMENT_RECEIVED', 'Payment received for Sale #17 amount ₱680.00', '2026-01-26 11:03:49'),
(16, 5, 'RETURN_APPROVED', 'RETURN_APPROVED for Return #2', '2026-01-26 11:09:40'),
(17, 1, 'USER_CREATE', 'Created new user (owner): johndoe', '2026-01-26 11:22:01'),
(18, 2, 'PAYMENT_RECEIVED', 'Payment received for Sale #19 amount ₱300.00', '2026-01-27 03:01:22'),
(19, 2, 'SALE_CREATE', 'Created sale #20 (unpaid) total ₱1,375.00', '2026-01-27 03:02:10'),
(20, 2, 'PAYMENT_RECEIVED', 'Payment received for Sale #20 amount ₱500.00', '2026-01-27 03:03:29'),
(21, 2, 'PAYMENT_RECEIVED', 'Payment received for Sale #20 amount ₱375.00', '2026-01-27 03:03:50'),
(22, 2, 'PAYMENT_RECEIVED', 'Payment received for Sale #20 amount ₱500.00', '2026-01-27 03:04:03'),
(23, 5, 'RETURN_APPROVED', 'RETURN_APPROVED for Return #3', '2026-01-27 03:11:14'),
(24, 2, 'SALE_CREATE', 'Created sale #21 (unpaid) total ₱1,375.00', '2026-01-27 06:10:04'),
(25, 2, 'PAYMENT_RECEIVED', 'Payment received for Sale #21 amount ₱375.00', '2026-01-27 06:59:34'),
(26, 2, 'PAYMENT_RECEIVED', 'Payment received for Sale #21 amount ₱1,000.00', '2026-01-27 06:59:53'),
(27, 2, 'SALE_CREATE', 'Created sale #22 (unpaid) total ₱1,375.00', '2026-01-27 07:24:29'),
(28, 2, 'SALE_CREATE', 'Created sale #24 (paid) total ₱40,715.00', '2026-01-27 07:28:03'),
(29, 2, 'SALE_CREATE', 'Created sale #26 (unpaid) total ₱680.00', '2026-01-27 07:34:22'),
(30, 1, 'PRODUCT', 'Added product: Princess Sydney - B (SKU: PS123)', '2026-01-27 07:41:54'),
(31, 1, 'PRODUCT', 'Edited product #9: Princess Sydney - B (SKU: PS123)', '2026-01-27 07:42:23'),
(32, 1, 'PRODUCT', 'Added product: Super Sydney - A (SKU: SS11)', '2026-01-27 07:47:48'),
(33, 1, 'USER_STATUS', 'Changed user_id 6 status to inactive', '2026-01-27 07:53:27'),
(34, 1, 'USER_STATUS', 'Changed user_id 6 status to active', '2026-01-27 07:53:37'),
(35, 5, 'RETURN_REJECTED', 'RETURN_REJECTED for Return #4', '2026-01-27 07:57:59'),
(36, 5, 'PROFILE_UPDATE', 'Updated profile username to \'kriezyl\'', '2026-01-27 10:31:23'),
(37, 6, 'RETURN_APPROVED', 'RETURN_APPROVED for Return #5', '2026-01-27 11:55:40'),
(38, 2, 'SALE_CREATE', 'Created sale #28 (paid) total ₱550.00', '2026-01-27 13:00:24'),
(39, 1, 'USER_STATUS', 'Changed user_id 4 status to active', '2026-01-28 05:22:17'),
(40, 1, 'USER_STATUS', 'Changed user_id 4 status to inactive', '2026-01-28 05:22:20'),
(41, 1, 'PRODUCT', 'Added product: Premium Rice - A (SKU: PR345)', '2026-01-28 05:48:37'),
(42, 2, 'SALE_CREATE', 'Created sale #30 (paid) total ₱1,500.00', '2026-01-28 07:45:35'),
(43, 2, 'PAYMENT_RECEIVED', 'Payment received for Sale #26 amount ₱680.00', '2026-01-28 07:45:48'),
(44, 2, 'SALE_CREATE', 'Created sale #31 (unpaid) total ₱55.00', '2026-01-28 08:15:42'),
(45, 2, 'PAYMENT_RECEIVED', 'Payment received for Sale #22 amount ₱1,375.00', '2026-01-28 08:17:30'),
(46, 2, 'PAYMENT_RECEIVED', 'Payment received for Sale #31 amount ₱55.00', '2026-01-28 08:18:01'),
(47, 1, 'PRODUCT', 'Added product: Sakura - B (SKU: SAK342)', '2026-01-28 08:37:44'),
(48, 1, 'PRODUCT', 'Added product: Malagkit Rice - B (SKU: MAL903)', '2026-01-28 08:56:35');

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `customer_id` int(11) NOT NULL,
  `first_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`customer_id`, `first_name`, `last_name`, `phone`, `address`, `created_at`) VALUES
(1, 'Kriezyl', 'Villlalobos', '09129823923', 'Zone 7 Block 6 Kabina Kauswagan Cagayan de Oro City', '2026-01-24 10:59:49'),
(2, 'Sydney', 'Magsalay', '0972838634', 'Carmen', '2026-01-25 11:54:27'),
(3, 'Kirk', 'Maxilom', '09138326434', 'COC', '2026-01-25 12:59:46'),
(4, 'Kristina Cass', 'Merida', '091234567897', 'Carmen', '2026-01-26 10:59:43'),
(5, 'Jayson', 'Belmes', '091234567895', 'carmen', '2026-01-27 07:22:51');

-- --------------------------------------------------------

--
-- Table structure for table `delivery_receipts`
--

CREATE TABLE `delivery_receipts` (
  `receipts_id` int(11) NOT NULL,
  `sale_id` int(11) DEFAULT NULL,
  `delivery_date` date DEFAULT NULL,
  `delivered_by` varchar(100) DEFAULT NULL,
  `received_by` varchar(100) DEFAULT NULL,
  `status` enum('pending','delivery','delivered') DEFAULT NULL,
  `remarks` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `delivery_receipts`
--

INSERT INTO `delivery_receipts` (`receipts_id`, `sale_id`, `delivery_date`, `delivered_by`, `received_by`, `status`, `remarks`) VALUES
(1, 3, '2026-01-25', '', '', 'pending', '');

-- --------------------------------------------------------

--
-- Table structure for table `discounts`
--

CREATE TABLE `discounts` (
  `discount_id` int(11) NOT NULL,
  `sale_id` int(11) DEFAULT NULL,
  `discount_amount` decimal(10,2) DEFAULT NULL,
  `reason` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `inventory_transactions`
--

CREATE TABLE `inventory_transactions` (
  `inventTrans_id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `qty_kg` decimal(10,2) DEFAULT NULL,
  `reference_id` int(11) DEFAULT NULL,
  `reference_type` enum('sale','purchase','return','delivery','adjust') DEFAULT NULL,
  `type` enum('in','out','adjust') DEFAULT NULL,
  `note` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory_transactions`
--

INSERT INTO `inventory_transactions` (`inventTrans_id`, `product_id`, `qty_kg`, `reference_id`, `reference_type`, `type`, `note`, `created_at`) VALUES
(1, NULL, NULL, NULL, 'purchase', 'in', NULL, '2026-01-24 04:53:57'),
(2, NULL, NULL, NULL, 'purchase', 'in', NULL, '2026-01-24 04:53:59'),
(3, NULL, NULL, NULL, 'purchase', 'in', NULL, '2026-01-24 04:54:00'),
(4, NULL, NULL, NULL, 'sale', 'out', NULL, '2026-01-24 04:54:04'),
(5, NULL, NULL, NULL, 'sale', 'out', NULL, '2026-01-24 04:55:00'),
(6, NULL, NULL, NULL, 'sale', 'out', NULL, '2026-01-24 04:55:00'),
(7, NULL, NULL, NULL, 'sale', 'out', NULL, '2026-01-24 04:55:02'),
(8, NULL, NULL, NULL, 'purchase', 'in', NULL, '2026-01-24 04:55:10'),
(9, NULL, NULL, NULL, 'purchase', 'in', NULL, '2026-01-24 04:56:19'),
(10, NULL, NULL, NULL, 'sale', 'out', NULL, '2026-01-24 06:12:35'),
(11, NULL, NULL, NULL, 'sale', 'out', NULL, '2026-01-24 06:14:25'),
(12, NULL, NULL, NULL, 'sale', 'out', NULL, '2026-01-24 06:20:33'),
(13, NULL, NULL, NULL, 'sale', 'out', NULL, '2026-01-24 06:34:01'),
(16, 1, 200.00, 101, 'purchase', 'in', 'Initial stock', '2026-01-24 07:38:43'),
(17, 2, 150.00, 102, 'purchase', 'in', 'Initial stock', '2026-01-24 07:38:43'),
(18, 1, 1.00, 2147483647, '', 'out', 'wala lang', '2026-01-24 07:48:28'),
(19, 2, 2.00, NULL, '', 'out', 'padala sa imong mama', '2026-01-24 08:34:18'),
(20, 2, 1000.00, 2147483647, 'purchase', 'in', 'stock pa more', '2026-01-24 10:43:16'),
(21, 2, -100.00, NULL, '', 'adjust', 'change lang', '2026-01-24 11:51:56'),
(22, 1, -1.00, 2, 'sale', 'out', 'Sold via cashier', '2026-01-24 12:30:18'),
(23, 2, 100.00, NULL, '', 'adjust', 'stock add', '2026-01-25 05:00:49'),
(24, 1, 5.00, 8, 'sale', 'out', 'Sale deduction', '2026-01-25 11:17:36'),
(25, 2, 10.00, 9, 'sale', 'out', NULL, '2026-01-25 11:39:07'),
(26, 1, 1000.00, 2147483647, 'purchase', 'in', 'delivered by supplier', '2026-01-25 12:22:36'),
(27, 2, 5.00, 11, 'sale', 'out', 'Sale #11 - deducted stock', '2026-01-25 12:59:08'),
(31, 1, 5.00, 15, 'sale', 'out', 'Sale #15 - deducted stock', '2026-01-26 09:25:38'),
(32, 2, 33.00, 16, 'sale', 'out', 'Sale #16 - deducted stock', '2026-01-26 09:26:19'),
(33, 1, 10.00, 17, 'sale', 'out', 'Sale #17 - deducted stock', '2026-01-26 10:42:27'),
(34, 6, 500.00, 0, 'purchase', 'in', 'Dliverd by supplier', '2026-01-26 10:46:37'),
(35, 2, 100.00, 0, 'purchase', 'in', 'Delivered by supplier', '2026-01-26 10:47:11'),
(36, 6, 20.00, 18, 'sale', 'out', 'Sale #18 - deducted stock', '2026-01-26 11:01:43'),
(37, 2, 10.00, 19, 'sale', 'out', 'Sale #19 - deducted stock', '2026-01-26 11:02:37'),
(38, 2, 10.00, 2, 'return', 'in', 'Approved return #2', '2026-01-26 11:09:40'),
(39, 2, 25.00, 20, 'sale', 'out', 'Sale #20 - deducted stock', '2026-01-27 03:02:10'),
(40, 2, 25.00, 3, 'return', 'in', 'Approved return #3', '2026-01-27 03:11:14'),
(41, 2, 25.00, 21, 'sale', 'out', 'Sale #21 - deducted stock', '2026-01-27 06:10:04'),
(42, 2, 25.00, 22, 'sale', 'out', 'Sale #22 - deducted stock', '2026-01-27 07:24:29'),
(43, 6, 479.00, 24, 'sale', 'out', 'Sale #24 - deducted stock', '2026-01-27 07:28:03'),
(44, 1, 10.00, 26, 'sale', 'out', 'Sale #26 - deducted stock', '2026-01-27 07:34:22'),
(45, 9, 500.00, NULL, '', 'in', 'Manual stock adjustment', '2026-01-27 07:43:22'),
(46, 9, 50.00, NULL, '', 'in', 'Manual stock adjustment', '2026-01-27 07:46:07'),
(47, 10, 500.00, 134352345, 'purchase', 'in', 'Stock received (Add Stock)', '2026-01-27 07:50:32'),
(48, 2, 25.00, 5, 'return', 'in', 'Approved return #5', '2026-01-27 11:55:40'),
(49, 2, 10.00, 28, 'sale', 'out', 'Sale #28 - deducted stock', '2026-01-27 13:00:24'),
(50, 11, 10.00, 1, 'purchase', 'in', 'Delivered by the supplier', '2026-01-28 05:49:42'),
(51, 11, 100.00, 2, 'purchase', 'in', 'received by staff', '2026-01-28 06:48:30'),
(52, 10, 50.00, 30, 'sale', 'out', 'Sale #30 - deducted stock', '2026-01-28 07:45:35'),
(53, 6, 200.00, 3, 'purchase', 'in', 'delivered', '2026-01-28 07:54:07'),
(54, 2, 1.00, 31, 'sale', 'out', 'Sale #31 - deducted stock', '2026-01-28 08:15:42'),
(55, 12, 100.00, 4, 'purchase', 'in', 'received by staff', '2026-01-28 08:38:18'),
(56, 10, 20.00, 5, 'purchase', 'in', 'delivered by supplier', '2026-01-28 08:47:21'),
(57, 8, 200.00, 6, 'purchase', 'in', 'None', '2026-01-28 08:53:54'),
(58, 13, 50.00, 7, 'purchase', 'in', 'done', '2026-01-28 08:57:23');

-- --------------------------------------------------------

--
-- Table structure for table `login_logs`
--

CREATE TABLE `login_logs` (
  `log_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `login_time` timestamp NOT NULL DEFAULT current_timestamp(),
  `device_info` varchar(255) DEFAULT NULL,
  `ip_address` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `login_logs`
--

INSERT INTO `login_logs` (`log_id`, `user_id`, `login_time`, `device_info`, `ip_address`) VALUES
(1, 2, '2026-01-25 10:45:22', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1'),
(2, 5, '2026-01-25 11:41:46', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1'),
(3, 2, '2026-01-25 11:42:18', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1'),
(4, 1, '2026-01-25 11:53:07', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1'),
(5, 2, '2026-01-25 11:53:33', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1'),
(6, 2, '2026-01-25 12:14:16', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1'),
(7, 2, '2026-01-25 12:14:43', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1'),
(8, 2, '2026-01-25 12:16:25', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1'),
(9, 1, '2026-01-25 12:21:51', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1'),
(10, 2, '2026-01-25 12:25:25', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1'),
(11, 1, '2026-01-25 13:02:34', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1'),
(12, 5, '2026-01-25 13:04:00', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1'),
(13, 1, '2026-01-26 04:14:58', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1'),
(14, 5, '2026-01-26 04:15:46', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1'),
(15, 5, '2026-01-26 05:09:37', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1'),
(16, 1, '2026-01-26 05:10:17', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1'),
(17, 5, '2026-01-26 05:11:19', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1'),
(18, 1, '2026-01-26 05:39:01', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1'),
(19, 2, '2026-01-26 08:43:09', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1'),
(20, 2, '2026-01-26 08:58:25', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1'),
(21, 5, '2026-01-26 09:29:08', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1'),
(22, 2, '2026-01-26 09:29:49', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1'),
(23, 5, '2026-01-26 09:55:47', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1'),
(24, 2, '2026-01-26 10:30:29', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1'),
(25, 5, '2026-01-26 10:43:52', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1'),
(26, 1, '2026-01-26 10:45:55', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1'),
(27, 2, '2026-01-26 10:59:01', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1'),
(28, 5, '2026-01-26 11:06:54', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1'),
(29, 1, '2026-01-26 11:13:26', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1'),
(30, 1, '2026-01-26 11:16:58', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1'),
(31, 1, '2026-01-26 11:21:17', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1'),
(32, 6, '2026-01-26 11:22:18', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1'),
(33, 2, '2026-01-27 02:35:20', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1'),
(34, 5, '2026-01-27 03:09:47', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1'),
(35, 2, '2026-01-27 03:11:59', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1'),
(36, 1, '2026-01-27 03:12:16', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1'),
(37, 2, '2026-01-27 06:07:54', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1'),
(38, 1, '2026-01-27 06:10:30', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1'),
(39, 5, '2026-01-27 06:12:17', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1'),
(40, 2, '2026-01-27 06:58:55', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1'),
(41, 1, '2026-01-27 07:01:25', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1'),
(42, 5, '2026-01-27 07:02:10', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1'),
(43, 1, '2026-01-27 07:06:00', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1'),
(44, 2, '2026-01-27 07:11:48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1'),
(45, 5, '2026-01-27 07:12:44', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1'),
(46, 2, '2026-01-27 07:21:12', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1'),
(47, 1, '2026-01-27 07:38:13', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1'),
(48, 5, '2026-01-27 07:57:16', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1'),
(49, 1, '2026-01-27 08:02:39', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1'),
(50, 2, '2026-01-27 08:06:19', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1'),
(51, 5, '2026-01-27 08:06:37', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1'),
(52, 2, '2026-01-27 08:07:06', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1'),
(53, 1, '2026-01-27 08:08:01', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1'),
(54, 1, '2026-01-27 09:02:31', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1'),
(55, 1, '2026-01-27 09:05:46', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1'),
(56, 2, '2026-01-27 09:20:11', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1'),
(57, 2, '2026-01-27 09:20:52', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1'),
(58, 5, '2026-01-27 09:25:04', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1'),
(59, 5, '2026-01-27 10:22:36', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1'),
(60, 1, '2026-01-27 10:35:45', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1'),
(61, 2, '2026-01-27 11:06:05', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1'),
(62, 6, '2026-01-27 11:48:28', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1'),
(63, 2, '2026-01-27 11:56:01', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1'),
(64, 1, '2026-01-27 12:26:01', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1'),
(65, 2, '2026-01-27 12:29:32', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1'),
(66, 2, '2026-01-28 01:09:47', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1'),
(67, 1, '2026-01-28 01:10:56', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1'),
(68, 2, '2026-01-28 05:10:44', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1'),
(69, 1, '2026-01-28 05:19:43', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1'),
(70, 5, '2026-01-28 06:49:50', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1'),
(71, 5, '2026-01-28 07:38:23', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1'),
(72, 1, '2026-01-28 07:38:57', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1'),
(73, 2, '2026-01-28 07:45:20', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1'),
(74, 1, '2026-01-28 07:46:07', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1'),
(75, 5, '2026-01-28 07:54:59', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1'),
(76, 1, '2026-01-28 07:56:47', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1'),
(77, 2, '2026-01-28 08:00:39', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1'),
(78, 1, '2026-01-28 08:18:20', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1'),
(79, 5, '2026-01-28 08:19:15', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1'),
(80, 5, '2026-01-28 08:26:29', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1'),
(81, 1, '2026-01-28 08:26:49', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1'),
(82, 2, '2026-01-28 08:28:07', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1'),
(83, 1, '2026-01-28 08:28:57', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1'),
(84, 5, '2026-01-28 08:40:04', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1'),
(85, 1, '2026-01-28 08:40:41', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1'),
(86, 5, '2026-01-28 08:54:43', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1'),
(87, 1, '2026-01-28 08:55:55', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1'),
(88, 5, '2026-01-28 08:57:49', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `payment_id` int(11) NOT NULL,
  `sale_id` int(11) DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `method` enum('cash','gcash','bank') DEFAULT NULL,
  `status` enum('pending','paid','failed') DEFAULT NULL,
  `paid_at` timestamp NULL DEFAULT NULL,
  `external_ref` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`payment_id`, `sale_id`, `amount`, `method`, `status`, `paid_at`, `external_ref`) VALUES
(1, 8, 340.00, 'cash', 'paid', '2026-01-25 11:17:36', NULL),
(2, 9, 550.00, 'cash', 'paid', '2026-01-25 11:39:07', NULL),
(3, 16, 815.00, 'cash', 'paid', '2026-01-26 09:26:59', ''),
(4, 16, 1000.00, 'cash', 'paid', '2026-01-26 10:42:57', ''),
(5, 19, 250.00, 'cash', 'paid', '2026-01-26 11:03:08', ''),
(6, 17, 680.00, 'cash', 'paid', '2026-01-26 11:03:49', ''),
(7, 19, 300.00, 'cash', 'paid', '2026-01-27 03:01:22', ''),
(8, 20, 500.00, 'cash', 'paid', '2026-01-27 03:03:29', ''),
(9, 20, 375.00, 'cash', 'paid', '2026-01-27 03:03:50', ''),
(10, 20, 500.00, 'cash', 'paid', '2026-01-27 03:04:03', ''),
(11, 21, 375.00, 'cash', 'paid', '2026-01-27 06:59:34', ''),
(12, 21, 1000.00, 'cash', 'paid', '2026-01-27 06:59:53', ''),
(13, 26, 680.00, 'cash', 'paid', '2026-01-28 07:45:48', ''),
(14, 22, 1375.00, 'cash', 'paid', '2026-01-28 08:17:30', ''),
(15, 31, 55.00, 'cash', 'paid', '2026-01-28 08:18:01', '');

-- --------------------------------------------------------

--
-- Table structure for table `payment_request`
--

CREATE TABLE `payment_request` (
  `pay_req_id` int(11) NOT NULL,
  `sale_id` int(11) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `requested_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('sent','paid','expired') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payment_request`
--

INSERT INTO `payment_request` (`pay_req_id`, `sale_id`, `phone`, `requested_at`, `status`) VALUES
(1, 3, '09859958194', '2026-01-25 09:14:09', '');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `product_id` int(11) NOT NULL,
  `variety` varchar(100) DEFAULT NULL,
  `grade` varchar(50) DEFAULT NULL,
  `sku` varchar(50) DEFAULT NULL,
  `unit_weight_kg` decimal(10,2) DEFAULT NULL,
  `unit_price` decimal(10,2) DEFAULT NULL,
  `delivery_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `archived` tinyint(1) NOT NULL DEFAULT 0,
  `stock_kg` decimal(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`product_id`, `variety`, `grade`, `sku`, `unit_weight_kg`, `unit_price`, `delivery_date`, `created_at`, `archived`, `stock_kg`) VALUES
(1, 'Dinorado', 'B', 'DIN123', 50.00, 68.00, '2026-01-01', '2026-01-24 07:38:02', 0, 1000.00),
(2, 'Sinandomeng', 'B', 'SIN456', 45.00, 55.00, '2026-01-05', '2026-01-24 07:38:02', 0, 100.00),
(3, 'Princess Bea', 'A', '23423', 50.00, 72.00, '2026-01-27', '2026-01-24 09:26:13', 1, 0.00),
(4, 'Jasmine Rice', 'A', 'JAS001', 50.00, 75.00, '2026-01-10', '2026-01-25 11:15:32', 0, 500.00),
(5, 'Well Milled Rice', 'B', 'WM001', 50.00, 48.00, '2026-01-12', '2026-01-25 11:15:32', 0, 800.00),
(6, 'Brown Rice', 'A', 'BR001', 25.00, 85.00, '2026-01-15', '2026-01-25 11:15:32', 0, 900.00),
(7, 'Malagkit (Glutinous)', 'A', 'MAL001', 25.00, 78.00, '2026-01-18', '2026-01-25 11:15:32', 0, 150.00),
(8, 'Coco Pandan', 'A', 'CP001', 50.00, 72.00, '2026-01-20', '2026-01-25 11:15:32', 0, 300.00),
(9, 'Princess Sydney', 'B', 'PS123', 500.00, 25.00, '2026-01-27', '2026-01-27 07:41:54', 0, 550.00),
(10, 'Super Sydney', 'A', 'SS11', 1.00, 30.00, '2026-01-27', '2026-01-27 07:47:48', 0, 500.00),
(11, 'Premium Rice', 'A', 'PR345', 100.00, 80.00, '2026-02-06', '2026-01-28 05:48:37', 0, 110.00),
(12, 'Sakura', 'B', 'SAK342', NULL, 53.00, '2026-02-01', '2026-01-28 08:37:44', 0, 100.00),
(13, 'Malagkit Rice', 'B', 'MAL903', NULL, 47.00, '2026-02-05', '2026-01-28 08:56:35', 0, 0.00);

-- --------------------------------------------------------

--
-- Table structure for table `purchases`
--

CREATE TABLE `purchases` (
  `purchases_id` int(11) NOT NULL,
  `supplier_id` int(11) DEFAULT NULL,
  `purchase_date` date DEFAULT NULL,
  `total_amount` decimal(10,2) DEFAULT NULL,
  `status` enum('pending','received','cancelled') DEFAULT 'pending',
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `purchases`
--

INSERT INTO `purchases` (`purchases_id`, `supplier_id`, `purchase_date`, `total_amount`, `status`, `created_by`, `created_at`) VALUES
(1, 1, '2026-01-28', 0.00, 'received', 1, '2026-01-28 05:49:42'),
(2, 1, '2026-01-29', 4000.00, 'received', 1, '2026-01-28 06:48:30'),
(3, 1, '2026-01-30', 10000.00, 'received', 1, '2026-01-28 07:54:07'),
(4, 1, '2026-02-01', 0.00, 'received', 1, '2026-01-28 08:38:18'),
(5, 1, '2026-02-05', 0.00, 'received', 1, '2026-01-28 08:47:21'),
(6, 1, '2026-02-05', 11200.00, 'received', 1, '2026-01-28 08:53:54'),
(7, 2, '2026-01-30', 0.00, 'received', 1, '2026-01-28 08:57:23');

-- --------------------------------------------------------

--
-- Table structure for table `push_notif_logs`
--

CREATE TABLE `push_notif_logs` (
  `push_notif_id` int(11) NOT NULL,
  `payment_id` int(11) DEFAULT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `sent_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('sent','failed') DEFAULT NULL,
  `device_token` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `push_notif_logs`
--

INSERT INTO `push_notif_logs` (`push_notif_id`, `payment_id`, `customer_id`, `message`, `sent_at`, `status`, `device_token`) VALUES
(1, NULL, 1, 'Hi! You have an unpaid balance of ₱680.00 for Sale #17. Please settle it by 2026-02-05. Thank you!', '2026-01-26 10:42:27', 'sent', NULL),
(2, NULL, 2, 'Payment received for Sale #16: ₱1,000.00. Thank you!', '2026-01-26 10:42:57', 'sent', NULL),
(3, NULL, 2, 'Hi! You have an unpaid balance of ₱550.00 for Sale #19. Please settle it by 2026-02-12. Thank you!', '2026-01-26 11:02:37', 'sent', NULL),
(4, NULL, 2, 'Payment received for Sale #19: ₱250.00. Thank you!', '2026-01-26 11:03:08', 'sent', NULL),
(5, NULL, 1, 'Payment received for Sale #17: ₱680.00. Thank you!', '2026-01-26 11:03:49', 'sent', NULL),
(6, NULL, 2, 'Payment received for Sale #19: ₱300.00. Thank you!', '2026-01-27 03:01:22', 'sent', NULL),
(7, NULL, 3, 'Hi! You have an unpaid balance of ₱1,375.00 for Sale #20. Please settle it by 2026-02-12. Thank you!', '2026-01-27 03:02:10', 'sent', NULL),
(8, NULL, 3, 'Payment received for Sale #20: ₱500.00. Thank you!', '2026-01-27 03:03:29', 'sent', NULL),
(9, NULL, 3, 'Payment received for Sale #20: ₱375.00. Thank you!', '2026-01-27 03:03:50', 'sent', NULL),
(10, NULL, 3, 'Payment received for Sale #20: ₱500.00. Thank you!', '2026-01-27 03:04:03', 'sent', NULL),
(11, NULL, 4, 'Hi! You have an unpaid balance of ₱1,375.00 for Sale #21. Please settle it by 2026-01-30. Thank you!', '2026-01-27 06:10:04', 'sent', NULL),
(12, NULL, 4, 'Payment received for Sale #21: ₱375.00. Thank you!', '2026-01-27 06:59:34', 'sent', NULL),
(13, NULL, 4, 'Payment received for Sale #21: ₱1,000.00. Thank you!', '2026-01-27 06:59:53', 'sent', NULL),
(14, NULL, 5, 'Hi! You have an unpaid balance of ₱1,375.00 for Sale #22. Please settle it by 2026-02-04. Thank you!', '2026-01-27 07:24:29', 'sent', NULL),
(15, NULL, 5, 'Hi! You have an unpaid balance of ₱680.00 for Sale #26. Please settle it by 2026-02-14. Thank you!', '2026-01-27 07:34:22', 'sent', NULL),
(16, NULL, 5, 'Payment received for Sale #26: ₱680.00. Thank you!', '2026-01-28 07:45:48', 'sent', NULL),
(17, NULL, 3, 'Hi! You have an unpaid balance of ₱55.00 for Sale #31. Please settle it by 2026-01-30. Thank you!', '2026-01-28 08:15:42', 'sent', NULL),
(18, NULL, 5, 'Payment received for Sale #22: ₱1,375.00. Thank you!', '2026-01-28 08:17:30', 'sent', NULL),
(19, NULL, 3, 'Payment received for Sale #31: ₱55.00. Thank you!', '2026-01-28 08:18:01', 'sent', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `returns`
--

CREATE TABLE `returns` (
  `return_id` int(11) NOT NULL,
  `sale_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `qty_returned` decimal(10,2) DEFAULT NULL,
  `reason` text DEFAULT NULL,
  `return_date` date DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `returns`
--

INSERT INTO `returns` (`return_id`, `sale_id`, `product_id`, `qty_returned`, `reason`, `return_date`, `status`) VALUES
(1, 16, 2, 12.00, 'sera naman yanxxx', '2026-01-26', 'rejected'),
(2, 16, 2, 10.00, 'sira', '2026-01-26', 'approved'),
(3, 20, 2, 25.00, 'damaged', '2026-01-27', 'approved'),
(4, 24, 6, 300.00, 'basa', '2026-01-27', 'rejected'),
(5, 21, 2, 25.00, 'damaged', '2026-01-27', 'approved');

-- --------------------------------------------------------

--
-- Table structure for table `sales`
--

CREATE TABLE `sales` (
  `sale_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `sale_date` datetime NOT NULL,
  `total_amount` decimal(10,2) DEFAULT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sales`
--

INSERT INTO `sales` (`sale_id`, `user_id`, `customer_id`, `sale_date`, `total_amount`, `status`, `created_at`) VALUES
(1, 2, 1, '0000-00-00 00:00:00', 20000.00, '', '2026-01-24 12:24:23'),
(2, 2, 1, '0000-00-00 00:00:00', 20000.00, '', '2026-01-24 12:30:18'),
(3, 2, 1, '2026-01-25 00:00:00', 10000000.00, 'pending', '2026-01-25 09:12:56'),
(4, 2, 1, '2026-01-25 00:00:00', 18000.00, 'pending', '2026-01-25 09:37:37'),
(7, 2, 1, '2026-01-25 00:00:00', 340.00, 'paid', '2026-01-25 11:17:36'),
(8, 2, 1, '2026-01-25 00:00:00', 340.00, 'paid', '2026-01-25 11:17:36'),
(9, 2, 1, '2026-01-25 19:39:07', 550.00, 'paid', '2026-01-25 11:39:07'),
(11, 2, 2, '2026-01-25 20:59:08', 275.00, 'paid', '2026-01-25 12:59:08'),
(15, 2, 2, '2026-01-26 17:25:38', 340.00, 'paid', '2026-01-26 09:25:38'),
(16, 2, 2, '2026-01-26 17:26:19', 1815.00, 'paid', '2026-01-26 09:26:19'),
(17, 2, 1, '2026-01-26 18:42:27', 680.00, 'paid', '2026-01-26 10:42:27'),
(18, 2, 4, '2026-01-26 19:01:43', 1700.00, 'paid', '2026-01-26 11:01:43'),
(19, 2, 2, '2026-01-26 19:02:37', 550.00, 'paid', '2026-01-26 11:02:37'),
(20, 2, 3, '2026-01-27 11:02:10', 1375.00, 'paid', '2026-01-27 03:02:10'),
(21, 2, 4, '2026-01-27 14:10:04', 1375.00, 'paid', '2026-01-27 06:10:04'),
(22, 2, 5, '2026-01-27 15:24:29', 1375.00, 'paid', '2026-01-27 07:24:29'),
(24, 2, 5, '2026-01-27 15:28:03', 40715.00, 'paid', '2026-01-27 07:28:03'),
(26, 2, 5, '2026-01-27 15:34:22', 680.00, 'paid', '2026-01-27 07:34:22'),
(28, 2, 5, '2026-01-27 21:00:24', 550.00, 'paid', '2026-01-27 13:00:24'),
(30, 2, 2, '2026-01-28 15:45:35', 1500.00, 'paid', '2026-01-28 07:45:35'),
(31, 2, 3, '2026-01-28 16:15:42', 55.00, 'paid', '2026-01-28 08:15:42');

-- --------------------------------------------------------

--
-- Table structure for table `sales_forecast`
--

CREATE TABLE `sales_forecast` (
  `forecast_id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `forecasting_period` enum('daily','weekly','monthly') DEFAULT NULL,
  `forecasting_start_date` date DEFAULT NULL,
  `forecasting_end_date` date DEFAULT NULL,
  `predict_qty_kg` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `generated_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sales_items`
--

CREATE TABLE `sales_items` (
  `sales_item_id` int(11) NOT NULL,
  `sale_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `qty_kg` decimal(10,2) DEFAULT NULL,
  `unit_price` decimal(10,2) DEFAULT NULL,
  `line_total` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sales_items`
--

INSERT INTO `sales_items` (`sales_item_id`, `sale_id`, `product_id`, `qty_kg`, `unit_price`, `line_total`) VALUES
(1, 2, 1, 1.00, 20000.00, 20000.00),
(2, 3, 1, 500.00, 20000.00, 10000000.00),
(3, 4, 2, 1.00, 18000.00, 18000.00),
(6, 8, 1, 5.00, 68.00, 340.00),
(7, 9, 2, 10.00, 55.00, 550.00),
(8, 11, 2, 5.00, 55.00, 275.00),
(14, 15, 1, 5.00, 68.00, 340.00),
(15, 15, 1, 5.00, 68.00, 340.00),
(16, 16, 2, 33.00, 55.00, 1815.00),
(17, 16, 2, 33.00, 55.00, 1815.00),
(18, 17, 1, 10.00, 68.00, 680.00),
(19, 17, 1, 10.00, 68.00, 680.00),
(20, 18, 6, 20.00, 85.00, 1700.00),
(21, 18, 6, 20.00, 85.00, 1700.00),
(22, 19, 2, 10.00, 55.00, 550.00),
(23, 19, 2, 10.00, 55.00, 550.00),
(24, 20, 2, 25.00, 55.00, 1375.00),
(25, 20, 2, 25.00, 55.00, 1375.00),
(26, 21, 2, 25.00, 55.00, 1375.00),
(27, 21, 2, 25.00, 55.00, 1375.00),
(28, 22, 2, 25.00, 55.00, 1375.00),
(29, 22, 2, 25.00, 55.00, 1375.00),
(30, 24, 6, 479.00, 85.00, 40715.00),
(31, 24, 6, 479.00, 85.00, 40715.00),
(32, 26, 1, 10.00, 68.00, 680.00),
(33, 26, 1, 10.00, 68.00, 680.00),
(34, 28, 2, 10.00, 55.00, 550.00),
(35, 30, 10, 50.00, 30.00, 1500.00),
(36, 31, 2, 1.00, 55.00, 55.00);

-- --------------------------------------------------------

--
-- Table structure for table `suppliers`
--

CREATE TABLE `suppliers` (
  `supplier_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('active','inactive') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `suppliers`
--

INSERT INTO `suppliers` (`supplier_id`, `name`, `phone`, `address`, `created_at`, `status`) VALUES
(1, 'ABC Ride Trading', '', '', '2026-01-28 05:49:02', 'active'),
(2, 'Agri Oro', '0923457982', 'cdo', '2026-01-28 08:57:03', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `supplier_payments`
--

CREATE TABLE `supplier_payments` (
  `sp_id` int(11) NOT NULL,
  `ap_id` int(11) NOT NULL,
  `purchase_id` int(11) DEFAULT NULL,
  `supplier_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `method` enum('cash','gcash','bank') DEFAULT 'cash',
  `reference_no` varchar(100) DEFAULT NULL,
  `paid_at` datetime NOT NULL DEFAULT current_timestamp(),
  `paid_by` int(11) DEFAULT NULL,
  `note` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `supplier_payments`
--

INSERT INTO `supplier_payments` (`sp_id`, `ap_id`, `purchase_id`, `supplier_id`, `amount`, `method`, `reference_no`, `paid_at`, `paid_by`, `note`, `created_at`) VALUES
(1, 1, 2, 1, 4000.00, 'cash', '', '2026-01-28 15:15:06', 5, 'paid', '2026-01-28 07:15:06'),
(2, 2, 3, 1, 10000.00, 'cash', '', '2026-01-28 15:55:56', 5, '', '2026-01-28 07:55:56'),
(3, 4, 6, 1, 11200.00, 'cash', '', '2026-01-28 16:55:36', 5, '', '2026-01-28 08:55:36');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `first_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `role` varchar(20) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('active','inactive') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `password`, `first_name`, `last_name`, `phone`, `role`, `created_at`, `status`) VALUES
(1, 'admin', '$2y$10$n60PMwTRDSiiFkQgzgkiXet6VH8mb2n3SNS07L1Qk9Coxs08J4.Jm', 'System', 'Administrator', '09123456789', 'admin', '2026-01-23 11:33:23', 'active'),
(2, 'cashier', '$2y$10$ZkamQBz5nmIumbdKL0CyteboNyArIVbwG7UMYAjVTKw1x2xD3gx/e', 'Cashier', 'System', '09234567812', 'cashier', '2026-01-24 05:14:29', 'active'),
(3, 'owner', '$2y$10$t1DRLyLzgHAVACoL6/PY2ONF5ggtZk4jkDO.ejSacrDER9vBA59v.', 'System', 'Owner', '09123456789', '', '2026-01-24 11:58:06', 'inactive'),
(4, 'sydney', '$2y$10$4vVA7RsnWb2hlU.xIA1o9OtdbMHZdyKkQWr2Gh6coz2sPgVAHah92', 'Sydney', 'Magsalay', '09127837823', '', '2026-01-25 06:32:10', 'inactive'),
(5, 'kriezyl', '$2y$10$dGsfC8alj8A6Ldctd/ofROdTA6e6TlJy/TMZE54syQTmr4JMqT4L2', 'Kriz', 'Villlalobos', '09127837823', 'owner', '2026-01-25 07:26:32', 'active'),
(6, 'johndoe', '$2y$10$WZgt8/fc/2em7iVP9qFXk.18jgZFyMNturX2Fs7fp2Dv1H5ljWQhy', 'John', 'Doe', '097326432498', 'owner', '2026-01-26 11:22:01', 'active');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `account_payable`
--
ALTER TABLE `account_payable`
  ADD PRIMARY KEY (`ap_id`),
  ADD KEY `purchase_id` (`purchase_id`),
  ADD KEY `supplier_id` (`supplier_id`),
  ADD KEY `account_payable_approved_by_fk` (`approved_by`);

--
-- Indexes for table `account_receivable`
--
ALTER TABLE `account_receivable`
  ADD PRIMARY KEY (`ar_id`),
  ADD KEY `sales_id` (`sales_id`),
  ADD KEY `customer_id` (`customer_id`);

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`activity_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`customer_id`);

--
-- Indexes for table `delivery_receipts`
--
ALTER TABLE `delivery_receipts`
  ADD PRIMARY KEY (`receipts_id`),
  ADD KEY `sale_id` (`sale_id`);

--
-- Indexes for table `discounts`
--
ALTER TABLE `discounts`
  ADD PRIMARY KEY (`discount_id`),
  ADD KEY `sale_id` (`sale_id`);

--
-- Indexes for table `inventory_transactions`
--
ALTER TABLE `inventory_transactions`
  ADD PRIMARY KEY (`inventTrans_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `login_logs`
--
ALTER TABLE `login_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `sale_id` (`sale_id`);

--
-- Indexes for table `payment_request`
--
ALTER TABLE `payment_request`
  ADD PRIMARY KEY (`pay_req_id`),
  ADD KEY `sale_id` (`sale_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`);

--
-- Indexes for table `purchases`
--
ALTER TABLE `purchases`
  ADD PRIMARY KEY (`purchases_id`),
  ADD KEY `supplier_id` (`supplier_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `push_notif_logs`
--
ALTER TABLE `push_notif_logs`
  ADD PRIMARY KEY (`push_notif_id`),
  ADD KEY `payment_id` (`payment_id`),
  ADD KEY `customer_id` (`customer_id`);

--
-- Indexes for table `returns`
--
ALTER TABLE `returns`
  ADD PRIMARY KEY (`return_id`),
  ADD KEY `sale_id` (`sale_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `sales`
--
ALTER TABLE `sales`
  ADD PRIMARY KEY (`sale_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `customer_id` (`customer_id`);

--
-- Indexes for table `sales_forecast`
--
ALTER TABLE `sales_forecast`
  ADD PRIMARY KEY (`forecast_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `generated_by` (`generated_by`);

--
-- Indexes for table `sales_items`
--
ALTER TABLE `sales_items`
  ADD PRIMARY KEY (`sales_item_id`),
  ADD KEY `sale_id` (`sale_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `suppliers`
--
ALTER TABLE `suppliers`
  ADD PRIMARY KEY (`supplier_id`);

--
-- Indexes for table `supplier_payments`
--
ALTER TABLE `supplier_payments`
  ADD PRIMARY KEY (`sp_id`),
  ADD KEY `sp_ap_fk` (`ap_id`),
  ADD KEY `sp_supplier_fk` (`supplier_id`),
  ADD KEY `sp_paid_by_fk` (`paid_by`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `account_payable`
--
ALTER TABLE `account_payable`
  MODIFY `ap_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `account_receivable`
--
ALTER TABLE `account_receivable`
  MODIFY `ar_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `activity_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `customer_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `delivery_receipts`
--
ALTER TABLE `delivery_receipts`
  MODIFY `receipts_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `discounts`
--
ALTER TABLE `discounts`
  MODIFY `discount_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `inventory_transactions`
--
ALTER TABLE `inventory_transactions`
  MODIFY `inventTrans_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=59;

--
-- AUTO_INCREMENT for table `login_logs`
--
ALTER TABLE `login_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=89;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `payment_request`
--
ALTER TABLE `payment_request`
  MODIFY `pay_req_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `purchases`
--
ALTER TABLE `purchases`
  MODIFY `purchases_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `push_notif_logs`
--
ALTER TABLE `push_notif_logs`
  MODIFY `push_notif_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `returns`
--
ALTER TABLE `returns`
  MODIFY `return_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `sales`
--
ALTER TABLE `sales`
  MODIFY `sale_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `sales_forecast`
--
ALTER TABLE `sales_forecast`
  MODIFY `forecast_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sales_items`
--
ALTER TABLE `sales_items`
  MODIFY `sales_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `suppliers`
--
ALTER TABLE `suppliers`
  MODIFY `supplier_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `supplier_payments`
--
ALTER TABLE `supplier_payments`
  MODIFY `sp_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `account_payable`
--
ALTER TABLE `account_payable`
  ADD CONSTRAINT `account_payable_approved_by_fk` FOREIGN KEY (`approved_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `account_payable_ibfk_1` FOREIGN KEY (`purchase_id`) REFERENCES `purchases` (`purchases_id`),
  ADD CONSTRAINT `account_payable_ibfk_2` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`supplier_id`);

--
-- Constraints for table `account_receivable`
--
ALTER TABLE `account_receivable`
  ADD CONSTRAINT `account_receivable_ibfk_1` FOREIGN KEY (`sales_id`) REFERENCES `sales` (`sale_id`),
  ADD CONSTRAINT `account_receivable_ibfk_2` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`);

--
-- Constraints for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `activity_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `delivery_receipts`
--
ALTER TABLE `delivery_receipts`
  ADD CONSTRAINT `delivery_receipts_ibfk_1` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`sale_id`);

--
-- Constraints for table `discounts`
--
ALTER TABLE `discounts`
  ADD CONSTRAINT `discounts_ibfk_1` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`sale_id`);

--
-- Constraints for table `inventory_transactions`
--
ALTER TABLE `inventory_transactions`
  ADD CONSTRAINT `inventory_transactions_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`);

--
-- Constraints for table `login_logs`
--
ALTER TABLE `login_logs`
  ADD CONSTRAINT `login_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`sale_id`);

--
-- Constraints for table `payment_request`
--
ALTER TABLE `payment_request`
  ADD CONSTRAINT `payment_request_ibfk_1` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`sale_id`);

--
-- Constraints for table `purchases`
--
ALTER TABLE `purchases`
  ADD CONSTRAINT `purchases_ibfk_1` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`supplier_id`),
  ADD CONSTRAINT `purchases_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `push_notif_logs`
--
ALTER TABLE `push_notif_logs`
  ADD CONSTRAINT `push_notif_logs_ibfk_1` FOREIGN KEY (`payment_id`) REFERENCES `payments` (`payment_id`),
  ADD CONSTRAINT `push_notif_logs_ibfk_2` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`);

--
-- Constraints for table `returns`
--
ALTER TABLE `returns`
  ADD CONSTRAINT `returns_ibfk_1` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`sale_id`),
  ADD CONSTRAINT `returns_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`);

--
-- Constraints for table `sales`
--
ALTER TABLE `sales`
  ADD CONSTRAINT `sales_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `sales_ibfk_2` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`);

--
-- Constraints for table `sales_forecast`
--
ALTER TABLE `sales_forecast`
  ADD CONSTRAINT `sales_forecast_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`),
  ADD CONSTRAINT `sales_forecast_ibfk_2` FOREIGN KEY (`generated_by`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `sales_items`
--
ALTER TABLE `sales_items`
  ADD CONSTRAINT `sales_items_ibfk_1` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`sale_id`),
  ADD CONSTRAINT `sales_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`);

--
-- Constraints for table `supplier_payments`
--
ALTER TABLE `supplier_payments`
  ADD CONSTRAINT `sp_ap_fk` FOREIGN KEY (`ap_id`) REFERENCES `account_payable` (`ap_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `sp_paid_by_fk` FOREIGN KEY (`paid_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `sp_supplier_fk` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`supplier_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
