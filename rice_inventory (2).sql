-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 25, 2026 at 02:03 PM
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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(11, 2, 'SALE_CREATE', 'Created sale #8 (CASH) total: 340', '2026-01-25 11:17:36');

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
(3, 'Kirk', 'Maxilom', '09138326434', 'COC', '2026-01-25 12:59:46');

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
(27, 2, 5.00, 11, 'sale', 'out', 'Sale #11 - deducted stock', '2026-01-25 12:59:08');

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
(11, 1, '2026-01-25 13:02:34', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1');

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
(2, 9, 550.00, 'cash', 'paid', '2026-01-25 11:39:07', NULL);

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
  `harvest_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `archived` tinyint(1) NOT NULL DEFAULT 0,
  `stock_kg` decimal(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`product_id`, `variety`, `grade`, `sku`, `unit_weight_kg`, `unit_price`, `harvest_date`, `created_at`, `archived`, `stock_kg`) VALUES
(1, 'Dinorado', 'B', 'DIN123', 50.00, 68.00, '2026-01-01', '2026-01-24 07:38:02', 0, 1000.00),
(2, 'Sinandomeng', 'B', 'SIN456', 45.00, 55.00, '2026-01-05', '2026-01-24 07:38:02', 0, 0.00),
(3, 'Princess Bea', 'A', '23423', 50.00, 72.00, '2026-01-27', '2026-01-24 09:26:13', 1, 0.00),
(4, 'Jasmine Rice', 'A', 'JAS001', 50.00, 75.00, '2026-01-10', '2026-01-25 11:15:32', 0, 500.00),
(5, 'Well Milled Rice', 'B', 'WM001', 50.00, 48.00, '2026-01-12', '2026-01-25 11:15:32', 0, 800.00),
(6, 'Brown Rice', 'A', 'BR001', 25.00, 85.00, '2026-01-15', '2026-01-25 11:15:32', 0, 200.00),
(7, 'Malagkit (Glutinous)', 'A', 'MAL001', 25.00, 78.00, '2026-01-18', '2026-01-25 11:15:32', 0, 150.00),
(8, 'Coco Pandan', 'A', 'CP001', 50.00, 72.00, '2026-01-20', '2026-01-25 11:15:32', 0, 300.00);

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
(11, 2, 2, '2026-01-25 20:59:08', 275.00, 'paid', '2026-01-25 12:59:08');

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
(8, 11, 2, 5.00, 55.00, 275.00);

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
(2, 'cashier', '$2y$10$P.LzAsMEpCNZo6X2zurMN.ENvyj3EI7BArpo1jxFhX2jA2wlUlYrW', 'Cashier', 'cashier', '0923456781', 'cashier', '2026-01-24 05:14:29', 'active'),
(3, 'owner', '$2y$10$t1DRLyLzgHAVACoL6/PY2ONF5ggtZk4jkDO.ejSacrDER9vBA59v.', 'System', 'Owner', '09123456789', '', '2026-01-24 11:58:06', 'inactive'),
(4, 'sydney', '$2y$10$4vVA7RsnWb2hlU.xIA1o9OtdbMHZdyKkQWr2Gh6coz2sPgVAHah92', 'Sydney', 'Magsalay', '09127837823', '', '2026-01-25 06:32:10', 'inactive'),
(5, 'kriz', '$2y$10$dGsfC8alj8A6Ldctd/ofROdTA6e6TlJy/TMZE54syQTmr4JMqT4L2', 'Kriz', 'Villlalobos', '09127837823', 'owner', '2026-01-25 07:26:32', 'active');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `account_payable`
--
ALTER TABLE `account_payable`
  ADD PRIMARY KEY (`ap_id`),
  ADD KEY `purchase_id` (`purchase_id`),
  ADD KEY `supplier_id` (`supplier_id`);

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
  MODIFY `ap_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `account_receivable`
--
ALTER TABLE `account_receivable`
  MODIFY `ar_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `activity_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `customer_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

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
  MODIFY `inventTrans_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `login_logs`
--
ALTER TABLE `login_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `payment_request`
--
ALTER TABLE `payment_request`
  MODIFY `pay_req_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `purchases`
--
ALTER TABLE `purchases`
  MODIFY `purchases_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `push_notif_logs`
--
ALTER TABLE `push_notif_logs`
  MODIFY `push_notif_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `returns`
--
ALTER TABLE `returns`
  MODIFY `return_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sales`
--
ALTER TABLE `sales`
  MODIFY `sale_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `sales_forecast`
--
ALTER TABLE `sales_forecast`
  MODIFY `forecast_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sales_items`
--
ALTER TABLE `sales_items`
  MODIFY `sales_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `suppliers`
--
ALTER TABLE `suppliers`
  MODIFY `supplier_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `account_payable`
--
ALTER TABLE `account_payable`
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
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
