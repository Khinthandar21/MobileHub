-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 18, 2026 at 05:18 PM
-- Server version: 10.11.16-MariaDB
-- PHP Version: 8.5.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `mobile_hub`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_log`
--

CREATE TABLE `activity_log` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `activity_log`
--

INSERT INTO `activity_log` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES
(1, 1, 'login', 'User logged in', '::1', '2026-10-17 20:26:14'),
(2, 1, 'logout', 'User logged out', '::1', '2026-10-17 20:57:44'),
(3, 2, 'login', 'User logged in', '::1', '2026-10-17 20:57:53'),
(4, 1, 'login', 'User logged in', '::1', '2026-11-11 21:33:47'),
(5, 1, 'logout', 'User logged out', '::1', '2026-11-11 21:34:19'),
(6, 2, 'login', 'User logged in', '::1', '2026-11-11 21:34:26'),
(7, 1, 'login', 'User logged in', '::1', '2026-11-11 22:17:24'),
(8, 1, 'logout', 'User logged out', '::1', '2026-11-11 22:22:00'),
(9, 1, 'login', 'User logged in', '::1', '2026-11-11 22:22:08'),
(10, 1, 'product.update', 'Updated product Samsung Galaxy A55', '::1', '2026-11-11 22:28:06'),
(11, 1, 'product.update', 'Updated product Samsung Galaxy Tab S9 FE', '::1', '2026-11-11 22:29:58'),
(12, 2, 'order.status', 'Order ORD-202611-0004 status: pending → shipped', '::1', '2026-11-12 00:09:47'),
(13, 2, 'login', 'User logged in', '::1', '2026-11-12 13:39:13'),
(14, 2, 'order.delete', 'Deleted order ORD-202610-0001', '::1', '2026-11-12 13:41:13'),
(15, 2, 'order.delete', 'Deleted order ORD-202611-0005', '::1', '2026-11-12 13:42:45'),
(16, 2, 'product.update', 'Updated product Samsung 25W Fast Charger', '::1', '2026-11-12 13:52:05'),
(17, 2, 'login', 'User logged in', '::1', '2026-11-12 22:29:47'),
(18, 2, 'order.status', 'Order ORD-202611-0004 status: shipped → processing', '::1', '2026-11-12 22:30:16'),
(19, 2, 'product.update', 'Updated product iPad 10th Generation', '::1', '2026-11-12 22:37:35'),
(20, 2, 'category.create', 'Created category test', '::1', '2026-11-12 22:38:10'),
(21, 2, 'category.delete', 'Deleted category test', '::1', '2026-11-12 22:38:17'),
(22, 2, 'logout', 'User logged out', '::1', '2026-11-12 23:02:58'),
(23, 2, 'login', 'User logged in', '::1', '2026-11-12 23:03:04'),
(24, 2, 'report.generate', 'Generated Monthly sales report (PDF) for 2026', '::1', '2026-11-12 23:59:31'),
(25, 2, 'report.generate', 'Generated Stock report (CSV)', '::1', '2026-11-12 23:59:59'),
(26, 2, 'report.generate', 'Generated Popular mobile products (CSV)', '::1', '2026-11-13 00:05:28'),
(27, 2, 'login', 'User logged in', '::1', '2026-11-13 20:40:00'),
(28, 2, 'admin_user.create', 'Created admin user yuya@gmail.com (editor)', '::1', '2026-11-13 20:51:36'),
(29, 2, 'logout', 'User logged out', '::1', '2026-11-13 20:51:47'),
(30, 3, 'login', 'User logged in', '::1', '2026-11-13 20:52:00'),
(31, 3, 'logout', 'User logged out', '::1', '2026-11-13 20:52:22'),
(32, 2, 'login', 'User logged in', '::1', '2026-11-13 20:52:26'),
(33, 2, 'admin_user.update', 'Updated admin user yuya@gmail.com', '::1', '2026-11-13 20:52:38'),
(34, 2, 'logout', 'User logged out', '::1', '2026-11-13 20:52:44'),
(35, 2, 'login', 'User logged in', '::1', '2026-11-13 20:53:14'),
(36, 2, 'admin_user.activate', 'Active admin user ', '::1', '2026-11-13 20:59:29'),
(37, 2, 'admin_user.deactivate', 'Inactive admin user ', '::1', '2026-11-13 20:59:43'),
(38, 2, 'admin_user.create', 'Created admin user su@gmail.com (editor)', '::1', '2026-11-13 21:00:28'),
(39, 2, 'admin_user.deactivate', 'Inactive admin user ', '::1', '2026-11-13 21:14:13'),
(40, 2, 'admin_user.activate', 'Active admin user su ()', '::1', '2026-11-13 21:16:47'),
(41, 2, 'editor_user.deactivate', 'Deactivated editor user su ()', '::1', '2026-11-13 21:20:34'),
(42, 2, 'editor_user.activate', 'Activated editor user su - ', '::1', '2026-11-13 21:23:46'),
(43, 2, 'editor_user.deactivate', 'Deactivated editor user su - su@gmail.com', '::1', '2026-11-13 21:26:33'),
(44, 2, 'editor_user.activate', 'Activated editor user su - su@gmail.com', '::1', '2026-11-13 21:26:45'),
(45, 2, 'editor_user.create', 'Created editor user Nann - nann@gmail.com', '::1', '2026-11-13 21:27:20'),
(46, 2, 'login', 'User logged in', '::1', '2026-11-14 15:06:35'),
(47, 5, 'login', 'User logged in', '::1', '2026-11-15 18:11:50'),
(48, 5, 'password.change', 'Completed required first-login password change', '::1', '2026-11-15 18:12:03'),
(49, 5, 'logout', 'User logged out', '::1', '2026-11-15 19:26:01'),
(50, 2, 'login', 'User logged in', '::1', '2026-11-15 19:26:10'),
(51, 2, 'settings.update', 'Updated site settings', '::1', '2026-11-15 19:27:43'),
(52, 2, 'logout', 'User logged out', '::1', '2026-11-15 19:31:36'),
(53, 5, 'login', 'User logged in', '::1', '2026-11-15 19:32:42'),
(54, 5, 'product.update', 'Updated product iPad Air M4 11-inch 128GB Wi-Fi', '::1', '2026-11-15 21:33:46'),
(55, 5, 'product.create', 'Created product Deco Fun L Drawing Tablet', '::1', '2026-11-15 21:51:42'),
(56, 5, 'product.update', 'Updated product Deco Fun L Drawing Tablet', '::1', '2026-11-15 21:51:57'),
(57, 5, 'product.update', 'Updated product Deco Fun L Drawing Tablet', '::1', '2026-11-15 21:59:15'),
(58, 5, 'product.update', 'Updated product Deco Fun L Drawing Tablet', '::1', '2026-11-15 22:02:27'),
(59, 5, 'logout', 'User logged out', '::1', '2026-11-15 22:37:48'),
(60, 2, 'login', 'User logged in', '::1', '2026-11-15 22:37:52'),
(61, 2, 'login', 'User logged in', '::1', '2026-11-17 20:20:18'),
(62, 2, 'logout', 'User logged out', '::1', '2026-11-17 20:20:43'),
(63, 4, 'login', 'User logged in', '::1', '2026-11-17 20:20:54'),
(64, 4, 'logout', 'User logged out', '::1', '2026-11-17 20:21:40'),
(65, 2, 'login', 'User logged in', '::1', '2026-11-17 20:21:51'),
(66, 2, 'logout', 'User logged out', '::1', '2026-11-18 00:37:58'),
(67, 5, 'login', 'User logged in', '::1', '2026-11-18 00:38:17'),
(68, 5, 'logout', 'User logged out', '::1', '2026-11-18 00:38:36'),
(69, 2, 'login', 'User logged in', '::1', '2026-11-18 00:39:01'),
(70, 2, 'logout', 'User logged out', '::1', '2026-11-18 00:41:16'),
(71, 2, 'login', 'User logged in', '::1', '2026-11-18 00:41:29');

-- --------------------------------------------------------

--
-- Table structure for table `admin_users`
--

CREATE TABLE `admin_users` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','editor','user') NOT NULL DEFAULT 'user',
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `must_change_password` tinyint(1) NOT NULL DEFAULT 0,
  `avatar` varchar(255) DEFAULT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admin_users`
--

INSERT INTO `admin_users` (`id`, `name`, `email`, `password`, `role`, `status`, `must_change_password`, `avatar`, `phone`, `bio`, `created_at`, `updated_at`) VALUES
(1, 'khin', 'khin@gmail.com', '$2y$12$Byn9mNCQbbpUE75WzDaDauZhGw4kQlEPQBdUaErCGMKevrdHYraVK', 'editor', 'active', 0, NULL, NULL, NULL, '2026-10-17 20:18:48', '2026-11-11 22:21:51'),
(2, 'thin', 'thin@gmail.com', '$2y$12$J6uQCaZHZlGcjCXpeEnav.7L2Pc55m5vCgCzIYqeuf6JugXp.jr6G', 'admin', 'active', 0, NULL, NULL, NULL, '2026-10-17 20:18:48', '2026-11-11 21:34:26'),
(3, 'Yu Ya', 'yuya@gmail.com', '$2y$12$wE9mWjrlH6OL0axy50iQT.Lirkpbi8PFFUcko9gdA4w8JRRyTNKkK', 'editor', 'inactive', 0, NULL, NULL, NULL, '2026-11-13 20:51:36', '2026-11-13 20:59:43'),
(4, 'su', 'su@gmail.com', '$2y$12$rKaqgwPoThw8Pu0y4fDSBeTh8aVwade.VKS3VgnMeYfbHopS/JhlW', 'editor', 'active', 1, NULL, NULL, NULL, '2026-11-13 21:00:28', '2026-11-13 21:26:45'),
(5, 'Nann', 'nann@gmail.com', '$2y$12$IsIb1RVPYNWnwbGBUa9KQenuQ8rWMzTgAsleuRBpIX9IVqXY8gvmu', 'editor', 'active', 0, NULL, NULL, NULL, '2026-11-13 21:27:20', '2026-11-15 18:12:03');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(150) NOT NULL,
  `slug` varchar(160) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Mobile Phones', 'mobile-phones', 'Smartphones from popular brands.', 'active', '2026-10-17 20:31:01', '2026-10-17 20:31:01'),
(2, 'Tablets', 'tablets', 'Tablets for work, study and entertainment.', 'active', '2026-10-17 20:31:01', '2026-10-17 20:31:01'),
(3, 'Chargers', 'chargers', 'Charging adapters and fast chargers.', 'active', '2026-10-17 20:31:01', '2026-10-17 20:31:01'),
(4, 'Earphones', 'earphones', 'Wired and wireless earphones.', 'active', '2026-10-17 20:31:01', '2026-10-17 20:31:01'),
(5, 'Phone Cases', 'phone-cases', 'Protective cases and covers for smartphones.', 'active', '2026-10-17 20:31:01', '2026-10-17 20:31:01'),
(6, 'Power Banks', 'power-banks', 'Portable power banks for mobile devices.', 'active', '2026-10-17 20:31:01', '2026-10-17 20:31:01'),
(7, 'Accessories', 'accessories', 'Other mobile phone accessories.', 'active', '2026-10-17 20:31:01', '2026-10-17 20:31:01');

-- --------------------------------------------------------

--
-- Table structure for table `customer_users`
--

CREATE TABLE `customer_users` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `phone` varchar(40) DEFAULT NULL,
  `password_hash` varchar(255) NOT NULL,
  `shipping_address` text DEFAULT NULL,
  `shipping_city` varchar(100) DEFAULT NULL,
  `shipping_zip` varchar(20) DEFAULT NULL,
  `shipping_country` varchar(100) DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `customer_users`
--

INSERT INTO `customer_users` (`id`, `name`, `email`, `phone`, `password_hash`, `shipping_address`, `shipping_city`, `shipping_zip`, `shipping_country`, `avatar`, `created_at`, `updated_at`) VALUES
(1, 'Khin', 'khin@gmail.com', '09691148805', '$2y$12$aLrmZzBX.PNCYxiDiUea2.4Yv62vAT4uPt/22RPZlPM0vyEbPBudO', 'No 38, 21 Street, 1 Ward, Latha Township', 'Yangon', NULL, 'Myanmar', 'customer-avatars/customer_1_9f1977379d3451b3.jpg', '2026-11-11 22:57:48', '2026-11-16 21:21:28');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(10) UNSIGNED NOT NULL,
  `customer_id` int(10) UNSIGNED DEFAULT NULL,
  `order_number` varchar(40) NOT NULL,
  `customer_name` varchar(150) NOT NULL,
  `customer_email` varchar(150) NOT NULL,
  `customer_phone` varchar(40) DEFAULT NULL,
  `shipping_address` text NOT NULL,
  `shipping_city` varchar(100) DEFAULT NULL,
  `shipping_zip` varchar(20) DEFAULT NULL,
  `shipping_country` varchar(100) DEFAULT NULL,
  `subtotal` decimal(12,2) NOT NULL DEFAULT 0.00,
  `shipping_fee` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total` decimal(12,2) NOT NULL DEFAULT 0.00,
  `payment_method` varchar(40) NOT NULL DEFAULT 'cod',
  `fulfillment_method` enum('delivery','pickup') NOT NULL DEFAULT 'delivery',
  `status` enum('pending','processing','shipped','ready_for_pickup','completed','cancelled') NOT NULL DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `customer_id`, `order_number`, `customer_name`, `customer_email`, `customer_phone`, `shipping_address`, `shipping_city`, `shipping_zip`, `shipping_country`, `subtotal`, `shipping_fee`, `total`, `payment_method`, `fulfillment_method`, `status`, `notes`, `created_at`, `updated_at`) VALUES
(1, NULL, 'MH-2026-0001', 'Aung Aung', 'aung@gmail.com', '09123456781', 'No. 25, Pyay Road', 'Yangon', '11041', 'Myanmar', 438.00, 5.00, 443.00, 'cod', 'delivery', 'pending', 'Please call before delivery.', '2026-10-17 20:31:01', '2026-10-17 20:31:01'),
(2, NULL, 'MH-2026-0002', 'Su Su', 'susu@gmail.com', '09123456782', 'No. 18, University Avenue', 'Yangon', '11081', 'Myanmar', 749.00, 0.00, 749.00, 'cod', 'delivery', 'processing', NULL, '2026-10-17 20:31:01', '2026-10-17 20:31:01'),
(3, NULL, 'MH-2026-0003', 'Mg Mg', 'mgmg@gmail.com', '09123456783', 'No. 12, Mandalay Road', 'Mandalay', '05011', 'Myanmar', 108.00, 5.00, 113.00, 'cod', 'delivery', 'shipped', NULL, '2026-10-17 20:31:01', '2026-10-17 20:31:01'),
(4, NULL, 'MH-2026-0004', 'Hnin Hnin', 'hnin@gmail.com', '09123456784', 'No. 7, Bogyoke Street', 'Naypyidaw', '15011', 'Myanmar', 369.00, 0.00, 369.00, 'cod', 'delivery', 'completed', 'Delivered successfully.', '2026-10-17 20:31:01', '2026-10-17 20:31:01'),
(5, NULL, 'MH-2026-0005', 'Kyaw Kyaw', 'kyaw@gmail.com', '09123456785', 'No. 33, Strand Road', 'Yangon', '11181', 'Myanmar', 49.00, 5.00, 54.00, 'cod', 'delivery', 'cancelled', 'Customer cancelled the order.', '2026-10-17 20:31:01', '2026-10-17 20:31:01'),
(12, 1, 'ORD-202611-0001', 'Khin', 'khin@gmail.com', '093294234824', 'Yangon', 'Yangon', '', '', 749.00, 5000.00, 5749.00, 'bank_transfer', 'delivery', 'pending', '', '2026-11-11 21:12:05', '2026-11-13 20:14:47'),
(13, 1, 'ORD-202611-0002', 'Khin', 'khin@gmail.com', '5353535', 'ygn', 'ygn', '', '', 0.00, 5000.00, 5000.00, 'bank_transfer', 'delivery', 'pending', '', '2026-11-11 21:32:05', '2026-11-13 20:14:47'),
(14, NULL, 'ORD-202611-0003', 'tets', 'test@gmail.com', '54424', 'ygn', 'ygn', '', '', 220.00, 5000.00, 5220.00, 'bank_transfer', 'delivery', 'pending', '', '2026-11-11 22:07:57', '2026-11-11 22:07:57'),
(15, 1, 'ORD-202611-0004', 'Khin', 'khin@gmail.com', '5353535', 'Store pickup', '', '', '', 220.00, 0.00, 220.00, 'cod', 'pickup', 'processing', '', '2026-11-12 00:06:07', '2026-11-13 20:14:47'),
(17, 1, 'ORD-202611-0005', 'Khin', 'khin@gmail.com', '5353535', '5453', '534', '', '', 4480000.00, 5000.00, 4485000.00, 'cod', 'delivery', 'pending', '', '2026-11-13 20:17:11', '2026-11-13 20:21:27'),
(18, 1, 'ORD-202611-0006', 'Khin', 'khin@gmail.com', '4242', 'rw2', 'ရန်ကုန်', '11121', 'Myanmar (Burma)', 419.00, 5000.00, 5419.00, 'cod', 'delivery', 'pending', '', '2026-11-13 20:18:24', '2026-11-13 20:18:24'),
(19, 1, 'ORD-202611-0007', 'Khin', 'khin@gmail.com', '09691148805', 'Store pickup', '', '', '', 419.00, 0.00, 419.00, 'bank_transfer', 'pickup', 'pending', '', '2026-11-13 20:30:56', '2026-11-13 20:30:56'),
(20, 1, 'ORD-202611-0008', 'Khin', 'khin@gmail.com', '09691148805', 'No 38, 21 Street, 1 Ward, Latha Township', 'Yangon', '', 'Myanmar', 34400000.00, 5000.00, 34405000.00, 'cod', 'delivery', 'pending', '', '2026-11-14 15:26:57', '2026-11-14 15:26:57'),
(21, 1, 'ORD-202611-0009', 'Khin', 'khin@gmail.com', '09691148805', 'No 38, 21 Street, 1 Ward, Latha Township', 'Yangon', '', 'Myanmar', 4480000.00, 5000.00, 4485000.00, 'cod', 'delivery', 'pending', '', '2026-11-15 19:58:35', '2026-11-15 19:58:35');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(10) UNSIGNED NOT NULL,
  `order_id` int(10) UNSIGNED NOT NULL,
  `product_id` int(10) UNSIGNED DEFAULT NULL,
  `product_name` varchar(200) NOT NULL,
  `product_sku` varchar(80) DEFAULT NULL,
  `product_image` varchar(255) DEFAULT NULL,
  `product_color` varchar(80) DEFAULT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `quantity` int(11) NOT NULL,
  `line_total` decimal(12,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `product_name`, `product_sku`, `product_image`, `product_color`, `unit_price`, `quantity`, `line_total`) VALUES
(1, 1, 1, 'Samsung Galaxy A55', 'MH-PH-001', 'samsung-galaxy-a55.jpg', NULL, 419.00, 1, 419.00),
(2, 1, 12, 'USB-C Cable 1m', 'MH-AC-001', 'usb-c-cable.jpg', NULL, 8.00, 2, 16.00),
(3, 2, 2, 'iPhone 15', 'MH-PH-002', 'iphone-15.jpg', NULL, 749.00, 1, 749.00),
(4, 3, 8, 'Samsung Galaxy Buds FE', 'MH-EP-001', 'galaxy-buds-fe.jpg', NULL, 89.00, 1, 89.00),
(5, 3, 10, 'Clear Protective Phone Case', 'MH-CS-001', 'clear-phone-case.jpg', NULL, 9.00, 2, 18.00),
(6, 4, 4, 'Samsung Galaxy Tab S9 FE', 'MH-TB-001', 'galaxy-tab-s9-fe.jpg', NULL, 369.00, 1, 369.00),
(7, 5, 9, 'JBL Tune 510BT', 'MH-EP-002', 'jbl-tune-510bt.jpg', NULL, 49.00, 1, 49.00),
(9, 12, 2, 'iPhone 15', 'MH-PH-002', 'iphone-15-pink.jpg', NULL, 749.00, 1, 749.00),
(10, 13, 14, 'iPhone 18 Pro', 'MH-PH-005', 'iphone-18-pro.jpg', NULL, 0.00, 1, 0.00),
(11, 14, 3, 'Redmi Note 13', 'MH-PH-003', 'redmi-note13-5g.jpg', NULL, 220.00, 1, 220.00),
(12, 15, 3, 'Redmi Note 13', 'MH-PH-003', 'redmi-note13-5g.jpg', NULL, 220.00, 1, 220.00),
(15, 17, 2, 'iPhone 15', 'MH-PH-002', 'iphone-15-pink.jpg', NULL, 4480000.00, 1, 4480000.00),
(16, 18, 1, 'Samsung Galaxy A55', 'MH-PH-001', 'samsung-galaxy-a55-back.jpg', NULL, 419.00, 1, 419.00),
(17, 19, 1, 'Samsung Galaxy A55', 'MH-PH-001', 'samsung-galaxy-a55-back.jpg', NULL, 419.00, 1, 419.00),
(18, 20, 13, 'iPhone Duo', 'MH-PH-004', 'iphone-duo.jpg', NULL, 12990000.00, 2, 25980000.00),
(19, 20, 15, 'iPhone 18 Pro Max', 'MH-PH-006', 'iphone-18-pro-max.jpg', NULL, 8420000.00, 1, 8420000.00),
(20, 21, 2, 'iPhone 15', 'MH-PH-002', 'iphone-15-blue.jpg', 'Blue', 4480000.00, 1, 4480000.00);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(10) UNSIGNED NOT NULL,
  `category_id` int(10) UNSIGNED DEFAULT NULL,
  `name` varchar(200) NOT NULL,
  `slug` varchar(220) NOT NULL,
  `sku` varchar(80) DEFAULT NULL,
  `short_description` varchar(500) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `sale_price` decimal(10,2) DEFAULT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `image` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive','draft') NOT NULL DEFAULT 'active',
  `featured` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `category_id`, `name`, `slug`, `sku`, `short_description`, `description`, `price`, `sale_price`, `stock`, `image`, `status`, `featured`, `created_at`, `updated_at`) VALUES
(1, 1, 'Samsung Galaxy A55', 'samsung-galaxy-a55', 'MH-PH-001', '5G smartphone with 128GB storage.', 'The Samsung Galaxy A55 delivers a premium mid-range experience. It features a bright 6.6-inch Super AMOLED display with a 120Hz refresh rate for fluid scrolling. Powered by the Exynos 1480 processor, it handles everyday tasks and light gaming with ease. The versatile triple-camera setup includes a 50MP main sensor with OIS, a 12MP ultra-wide, and a 5MP macro lens. It offers 5G connectivity, a robust 5000mAh battery, IP67 water and dust resistance, and a USB-C 2.0 port for reliable charging.', 2259000.00, 2159000.00, 18, 'samsung-galaxy-a55-back.jpg', 'active', 0, '2026-10-17 20:31:01', '2026-11-15 22:09:43'),
(2, 1, 'iPhone 15', 'iphone-15', 'MH-PH-002', 'Apple smartphone with 128GB storage.', 'The iPhone 15 brings flagship Apple features to the mainstream. It boasts a stunning 6.1-inch Super Retina XDR OLED display featuring the Dynamic Island for intuitive alerts. Driven by the A16 Bionic chip, it delivers highly efficient processing and all-day battery life. Capture brilliant photos with the 48MP main camera offering 2x optical-quality zoom. It is built with a color-infused glass back, an aluminum frame, and features the universal USB-C port for streamlined charging and connectivity.', 4480000.00, NULL, 12, 'iphone-15-pink.jpg', 'active', 0, '2026-10-17 20:31:01', '2026-11-15 21:44:35'),
(3, 1, 'Redmi Note 13', 'redmi-note-13', 'MH-PH-003', 'Affordable smartphone for everyday use.', 'The Redmi Note 13 offers exceptional value for budget-conscious users. It sports a large 6.67-inch AMOLED display with a 120Hz refresh rate, providing vibrant colors and smooth visuals. Under the hood, the MediaTek Dimensity 6080 chip ensures snappy performance for everyday apps. The camera system is highlighted by a high-resolution 108MP main sensor for detailed photography. Complete with a large 5000mAh battery, 33W fast charging support, a 3.5mm headphone jack, and a USB-C port.', 599000.00, NULL, 28, 'redmi-note13-5g.jpg', 'active', 0, '2026-10-17 20:31:01', '2026-11-15 22:09:43'),
(4, 2, 'Samsung Galaxy Tab S9 FE', 'samsung-galaxy-tab-s9-fe', 'MH-TB-001', 'Tablet for productivity and entertainment.', 'The Samsung Galaxy Tab S9 FE is a versatile tablet for productivity and play. It features a bright 10.9-inch LCD display with a 90Hz refresh rate for smooth navigation. Powered by the Exynos 1380 chip, it handles multitasking and media streaming effortlessly. It comes with an IP68 water and dust-resistant S Pen for note-taking and drawing. Complete with an 8MP rear camera, dual speakers, and a USB-C port for fast charging.', 1959000.00, 1859000.00, 12, 'p_1794412798_0799820a.jpg', 'active', 1, '2026-10-17 20:31:01', '2026-11-15 22:09:43'),
(5, 2, 'iPad 10th Generation', 'ipad-10th-generation', 'MH-TB-002', 'Apple tablet for work and study.', 'The 10th Generation iPad features a completely redesigned, colorful aesthetic. It sports a gorgeous 10.9-inch Liquid Retina display that extends to the edges. Driven by the A14 Bionic chip, it delivers solid performance for everyday tasks, casual gaming, and schoolwork. The landscape 12MP ultra-wide front camera keeps you perfectly framed during video calls. It supports the Apple Pencil (USB-C) and features a standard USB-C port for charging and accessories.', 1650000.00, NULL, 10, 'p_1794499655_8f68fc8e.webp', 'active', 0, '2026-10-17 20:31:01', '2026-11-15 22:09:43'),
(6, 3, 'Anker 20W USB-C Charger', 'anker-20w-usb-c-charger', 'MH-CH-001', 'Compact USB-C fast charger.', 'The Anker 20W USB-C Charger provides fast, reliable charging in an ultra-compact form factor. Utilizing advanced PIQ 3.0 technology, it quickly charges modern smartphones, taking them from 0 to 50% in just 30 minutes. Its space-saving design ensures it won\'t block other outlets, and the universally compatible USB-C port works flawlessly with phones, tablets, and wireless earbuds.', 95000.00, 80000.00, 49, 'anker-20w-charger.jpg', 'active', 1, '2026-10-17 20:31:01', '2026-11-15 22:09:43'),
(7, 3, 'Samsung 25W Fast Charger', 'samsung-25w-fast-charger', 'MH-CH-002', '25W USB-C power adapter.', 'The Samsung 25W Fast Charger is designed to optimally power your Galaxy devices. Utilizing USB-C Power Delivery (PD) and Programmable Power Supply (PPS) protocols, it provides safe, rapid charging for compatible smartphones and tablets. The compact wall adapter ensures your devices reach a full charge quickly and efficiently, protecting battery health over time. (Cable sold separately).', 115000.00, NULL, 35, 'samsung-25w-charger.jpg', 'draft', 0, '2026-10-17 20:31:01', '2026-11-15 22:09:43'),
(8, 4, 'Samsung Galaxy Buds FE', 'samsung-galaxy-buds-fe', 'MH-EP-001', 'Wireless earbuds with active noise cancellation.', 'The Samsung Galaxy Buds FE offer seamless integration with the Galaxy ecosystem. They feature a compact, ergonomic design for a secure fit and Active Noise Cancellation to block out distractions. The advanced audio architecture delivers rich bass and clear highs. With intuitive touch controls, seamless device switching, and a compact USB-C charging case, they are perfect for everyday listening and clear phone calls.', 299000.00, 269000.00, 18, 'galaxy-buds-fe.jpg', 'active', 1, '2026-10-17 20:31:01', '2026-11-15 22:09:43'),
(9, 4, 'JBL Tune 510BT', 'jbl-tune-510bt', 'MH-EP-002', 'Wireless on-ear headphones.', 'The JBL Tune 510BT wireless headphones deliver powerful JBL Pure Bass sound. They feature Bluetooth 5.0 for high-quality wireless streaming and multi-point connectivity to seamlessly switch between devices. Enjoy up to 40 hours of battery life, with a quick 5-minute charge providing 2 hours of playback via the convenient USB-C port. The lightweight, foldable design includes built-in controls for hands-free calls and voice assistants.', 180000.00, NULL, 25, 'jbl-tune-510bt.jpg', 'active', 0, '2026-10-17 20:31:01', '2026-11-15 22:09:43'),
(10, 5, 'Clear Protective Phone Case', 'clear-protective-phone-case', 'MH-CS-001', 'Transparent protective smartphone case.', 'Protect your device without hiding its design with this Clear Protective Phone Case. Made from a durable blend of shock-absorbing TPU bumper and a scratch-resistant polycarbonate backplate, it provides excellent drop protection. The raised bezels safeguard the display and camera lenses. Precise cutouts ensure easy access to all buttons, speakers, and the USB-C charging port.', 35000.00, 25000.00, 100, 'clear-phone-case.jpg', 'active', 0, '2026-10-17 20:31:01', '2026-11-15 22:09:43'),
(11, 6, 'Anker PowerCore 10000', 'anker-powercore-10000', 'MH-PB-001', 'Portable 10000mAh power bank.', 'The Anker PowerCore 10000 is one of the smallest and lightest 10,000mAh portable chargers available. It holds enough capacity to charge most smartphones more than twice. It features Anker\'s exclusive PowerIQ and VoltageBoost technologies for a tailored, high-speed charge to your devices. It includes a USB-A output port and recharges quickly via its Micro-USB/USB-C input.', 135000.00, NULL, 22, 'anker-powercore-10000.jpg', 'active', 1, '2026-10-17 20:31:01', '2026-11-15 22:09:43'),
(12, 7, 'USB-C Cable 1m', 'usb-c-cable-1m', 'MH-AC-001', 'USB-C charging and data cable.', 'This 1-metre USB-C to USB-C Cable provides reliable charging and high-speed data transfer. Constructed with reinforced stress points and a durable outer jacket, it is designed to withstand daily bending and twisting. It supports fast charging protocols for smartphones, tablets, and laptops, and offers data transfer speeds up to 480 Mbps for syncing photos and music.', 25000.00, NULL, 80, 'usb-c-cable.jpg', 'active', 0, '2026-10-17 20:31:01', '2026-11-15 22:09:43'),
(13, 1, 'iPhone Duo', 'iphone-duo', 'MH-PH-004', 'Apple foldable iPhone with a 7.6-inch inner display and a 5.4-inch outer display.', 'Apple\'s revolutionary iPhone Duo redefines multitasking. It seamlessly switches between a convenient 5.4-inch outer OLED display for quick tasks and a vibrant 7.6-inch inner foldable OLED display for immersive media and side-by-side app usage. Driven by the powerful A19 Pro chip, it ensures smooth performance across both screens. The advanced dual-camera system features a 48MP main lens and ultra-wide capabilities. Engineered with a durable, ultra-thin mechanical hinge, it includes Face ID, all-day battery life, and a USB-C 3.2 port for fast charging and connectivity.', 12990000.00, NULL, 43, 'iphone-duo.jpg', 'active', 0, '2026-11-10 23:28:35', '2026-11-15 21:39:15'),
(14, 1, 'iPhone 18 Pro', 'iphone-18-pro', 'MH-PH-005', 'Pro iPhone with a variable-aperture camera.', 'Experience ultimate performance with the iPhone 18 Pro. It features a stunning 6.3-inch Super Retina XDR OLED display with 120Hz ProMotion technology for fluid scrolling. Powered by the next-generation A20 Pro chip, it handles AAA gaming and intensive multitasking with ease. The groundbreaking camera system highlights a new 48MP main sensor with a variable-aperture lens, alongside advanced ultra-wide and telephoto lenses. Encased in an aerospace-grade titanium body, it also includes a customizable Action Button and high-speed USB-C Thunderbolt 4 connectivity for rapid data transfer.', 7820000.00, NULL, 49, 'iphone-18-pro.jpg', 'active', 0, '2026-11-10 23:30:34', '2026-11-15 21:39:15'),
(15, 1, 'iPhone 18 Pro Max', 'iphone-18-pro-max', 'MH-PH-006', 'Large-screen Pro iPhone with extended battery life.', 'The iPhone 18 Pro Max is designed for power users who demand the best. It boasts a massive 6.9-inch Super Retina XDR OLED display with ProMotion, offering an unparalleled viewing experience. At its core is the incredibly efficient A20 Pro chip, maximizing both processing speed and its class-leading, extended battery life. Capture cinematic moments with a state-of-the-art 48MP multi-lens camera array featuring enhanced optical zoom. Built with a durable titanium frame, it supports ultra-fast charging and data syncing via its USB-C Thunderbolt 4 port.', 8420000.00, NULL, 44, 'iphone-18-pro-max.jpg', 'active', 0, '2026-11-10 23:30:34', '2026-11-15 21:39:15'),
(16, 1, 'iPhone 17e 256GB', 'iphone-17e-256gb', 'MH-PH-007', '256GB storage, A19 chip, and 48MP Fusion camera.', 'The iPhone 17e delivers premium Apple features in an accessible package. It sports a vibrant 6.1-inch Super Retina XDR OLED display that brings your content to life with deep blacks and rich colors. At its heart is the powerful A19 chip, ensuring swift performance across apps and demanding everyday tasks. Capture stunning, detailed photos with the advanced 48MP Fusion camera system, which includes next-generation computational photography. Designed for longevity, it offers all-day battery life, 5G connectivity, and a universal USB-C port.', 4000000.00, NULL, 20, 'iphone-17e-black.jpg', 'active', 0, '2026-11-15 20:33:51', '2026-11-15 22:05:57'),
(17, 1, 'Samsung Galaxy S26 Ultra 256GB', 'samsung-galaxy-s26-ultra-256gb', 'MH-PH-008', '256GB storage, Galaxy AI, S Pen, and a 200MP camera.', 'Master your workflow and creativity with the Samsung Galaxy S26 Ultra. It features a massive 6.8-inch Dynamic AMOLED 2X display with a 144Hz adaptive refresh rate for ultra-smooth navigation. Powered by the Snapdragon 8 Elite Gen 5 chip, it easily handles heavy multitasking and intensive Galaxy AI features. The pro-grade camera system is headlined by a 200MP primary sensor and a 50MP periscope telephoto lens for unparalleled optical zoom. Complete with an integrated S Pen, a durable titanium frame, and a high-speed USB-C 3.2 port for fast charging and data transfer.', 6896000.00, NULL, 20, 'galaxy-s26-ultra-black.png', 'active', 0, '2026-11-15 20:33:51', '2026-11-15 22:05:57'),
(18, 1, 'iPhone 17 256GB', 'iphone-17-256gb', 'MH-PH-009', '256GB storage with the A19 chip and 48MP Fusion camera.', 'Elevate your everyday carry with the iPhone 17. It features a brilliant 6.1-inch Super Retina XDR OLED display with ProMotion support for a smooth 120Hz experience. Powered by the highly efficient A19 chip, it effortlessly handles multitasking and intensive apps. The advanced 48MP Fusion camera system captures incredible detail in any light. It includes MagSafe wireless charging, all-day battery life, and a high-speed USB-C port for universal connectivity.', 5500000.00, NULL, 20, 'iphone-17-black.jpg', 'active', 0, '2026-11-15 20:49:27', '2026-11-15 22:05:57'),
(19, 1, 'iPhone 17 Pro 256GB', 'iphone-17-pro-256gb', 'MH-PH-010', 'Pro performance with A19 Pro and a 48MP camera system.', 'The iPhone 17 Pro is built for creators and power users. It boasts a 6.3-inch Super Retina XDR OLED display with Always-On technology and 120Hz ProMotion. The industry-leading A19 Pro chip delivers desktop-class gaming and processing speeds. The pro camera system features a 48MP main sensor, an upgraded ultra-wide lens, and a 3x telephoto camera. Encased in a lightweight, aerospace-grade titanium frame, it supports ultra-fast data transfers via its USB-C Thunderbolt port.', 6450000.00, NULL, 20, 'iphone-17-pro-orange.jpg', 'active', 0, '2026-11-15 20:49:27', '2026-11-15 22:05:57'),
(20, 1, 'iPhone 17 Pro Max 256GB', 'iphone-17-pro-max-256gb', 'MH-PH-011', 'Large display, A19 Pro chip, and advanced camera system.', 'Maximize your view with the iPhone 17 Pro Max. It features a massive 6.9-inch Super Retina XDR OLED ProMotion display, offering maximum screen real estate for media and productivity. Driven by the A19 Pro chip, it provides unparalleled performance and the longest battery life ever in an iPhone. The elite camera system includes a 48MP main sensor and a 5x optical zoom tetraprism lens. Built with a titanium chassis, it includes a customizable Action Button and a USB-C Thunderbolt port.', 6990000.00, NULL, 20, 'iphone-17-pro-max-blue.png', 'active', 0, '2026-11-15 20:49:27', '2026-11-15 22:05:57'),
(21, 1, 'iPhone Air 256GB', 'iphone-air-256gb', 'MH-PH-012', 'Ultra-thin design with A19 Pro and 256GB storage.', 'The iPhone Air introduces a revolutionary ultra-thin aesthetic without compromising performance. It features a vibrant 6.5-inch Super Retina XDR OLED display packed into Apple\'s slimmest profile yet. Powered by the A19 Pro chip, it delivers top-tier performance for all your creative needs. It includes a refined, flush-mounted 48MP camera system, advanced thermal management, Face ID, and a high-speed USB-C port for fast charging and syncing.', 5260000.00, NULL, 20, 'iphone-air-space-black.png', 'active', 0, '2026-11-15 20:49:27', '2026-11-15 22:05:57'),
(22, 1, 'Samsung Galaxy S26 256GB', 'samsung-galaxy-s26-256gb', 'MH-PH-013', '256GB storage, Galaxy AI, and a 50MP camera system.', 'Experience the best of Android with the Samsung Galaxy S26. It features a compact 6.2-inch Dynamic AMOLED 2X display with a smooth 120Hz adaptive refresh rate. Powered by the latest Snapdragon 8 Gen 4 processor, it powers through advanced Galaxy AI tasks and heavy multitasking. The versatile 50MP triple-camera setup captures stunning photos and 8K video. It includes ultrasonic fingerprint security, a durable Armor Aluminum frame, and a fast-charging USB-C 3.2 port.', 4967000.00, NULL, 20, 'galaxy-s26-black.jpg', 'active', 0, '2026-11-15 20:49:27', '2026-11-15 22:05:57'),
(23, 1, 'Samsung Galaxy S26+ 256GB', 'samsung-galaxy-s26-plus-256gb', 'MH-PH-014', 'Large AMOLED display, Galaxy AI, and 256GB storage.', 'The Samsung Galaxy S26+ offers flagship power with a larger canvas. It boasts a stunning 6.7-inch QHD+ Dynamic AMOLED 2X display, perfect for immersive gaming and media. Driven by the Snapdragon 8 Gen 4 chip, it handles the most demanding apps and Galaxy AI features with ease. The professional 50MP triple-camera system features enhanced low-light performance. Packed with a larger 4900mAh battery, it supports 45W super-fast wired charging via its USB-C 3.2 port.', 5867000.00, NULL, 20, 'galaxy-s26-plus-black.jpg', 'active', 0, '2026-11-15 20:49:27', '2026-11-15 22:05:57'),
(24, 1, 'Samsung Galaxy A57 5G 256GB', 'samsung-galaxy-a57-5g-256gb', 'MH-PH-015', '256GB storage, 5G, and a 120Hz AMOLED display.', 'The Samsung Galaxy A57 5G brings premium features to the mid-range. Enjoy smooth visuals on the 6.6-inch Super AMOLED display with a 120Hz refresh rate. Powered by an upgraded Exynos processor, it provides reliable 5G connectivity and smooth everyday performance. The camera array features a 50MP primary lens with Optical Image Stabilization for crisp, blur-free shots. Built with an IP67 rating, it includes a 5000mAh battery and a USB-C port for charging.', 2718000.00, NULL, 20, 'galaxy-a57-navy.jpg', 'active', 0, '2026-11-15 20:49:27', '2026-11-15 22:05:57'),
(25, 1, 'Samsung Galaxy A37 5G 256GB', 'samsung-galaxy-a37-5g-256gb', 'MH-PH-016', '256GB storage, 5G, and a 120Hz AMOLED display.', 'Stay connected with the accessible Samsung Galaxy A37 5G. It features a vibrant 6.5-inch Super AMOLED display with a 120Hz refresh rate for smooth scrolling. Equipped with an efficient octa-core processor and 5G capabilities, it ensures fast streaming and browsing. The versatile multi-lens camera system includes a 50MP main shooter. It boasts a long-lasting 5000mAh battery, expandable microSD storage, and a reliable USB-C port.', 1943000.00, NULL, 20, 'galaxy-a37-black.jpg', 'active', 0, '2026-11-15 20:49:27', '2026-11-15 22:05:57'),
(34, 2, 'iPad Air M4 11-inch 128GB Wi-Fi', 'ipad-air-m4-11-128gb-wifi', 'MH-TB-003', 'M4 performance with an 11-inch Liquid Retina display.', 'Supercharge your workflow with the iPad Air M4. It features a stunning 11-inch Liquid Retina display with P3 wide color and anti-reflective coating. Powered by the incredibly fast Apple M4 chip, it delivers desktop-class performance for video editing, 3D design, and advanced gaming. It includes a landscape 12MP front camera, supports the Apple Pencil Pro, features Wi-Fi 6E connectivity, and includes a versatile USB-C port with fast data transfer speeds.', 3550000.00, NULL, 50, 'tablet-ipad-air-m4.jpg', 'active', 0, '2026-11-15 20:59:53', '2026-11-15 21:44:35'),
(35, 2, 'Samsung Galaxy Tab S11 Wi-Fi 128GB', 'galaxy-tab-s11-wifi-128gb', 'MH-TB-004', '11-inch AMOLED tablet with S Pen support.', 'The Samsung Galaxy Tab S11 is your ultimate canvas for productivity and entertainment. It features a stunning 11-inch Dynamic AMOLED 2X display with vivid color reproduction and a fluid 120Hz refresh rate. Powered by a next-generation flagship processor, it effortlessly handles split-screen multitasking and graphics-intensive gaming. The tablet comes equipped with a low-latency S Pen for natural note-taking and drawing. It also includes a 13MP ultra-wide rear camera, quad speakers tuned by AKG, and a fast-charging USB-C port.', 4047000.00, NULL, 20, 'tablet-galaxy-tab-s11.jpg', 'active', 0, '2026-11-15 20:59:53', '2026-11-15 22:05:57'),
(36, 2, 'Xiaomi Redmi Pad 2 Wi-Fi 128GB', 'redmi-pad-2-wifi-128gb', 'MH-TB-005', 'Everyday tablet with a large display and 128GB storage.', 'The Xiaomi Redmi Pad 2 is the perfect entertainment hub for the whole family. It features a vibrant 10.6-inch 2K display with a 90Hz refresh rate for smooth video playback and browsing. Powered by an efficient MediaTek processor, it ensures reliable daily performance. It includes quad speakers with Dolby Atmos support for immersive audio, an 8MP rear camera, a large battery for all-day use, and a standard USB-C charging port.', 947000.00, NULL, 20, 'tablet-redmi-pad-2.webp', 'active', 0, '2026-11-15 20:59:53', '2026-11-15 22:05:57'),
(37, 2, 'Samsung Galaxy Tab S10 FE+ Wi-Fi 128GB', 'galaxy-tab-s10-fe-plus-wifi-128gb', 'MH-TB-006', 'Large-screen tablet with S Pen support.', 'Maximize your productivity with the large-screen Samsung Galaxy Tab S10 FE+. It boasts a massive 12.4-inch display, providing ample space for split-screen multitasking and media consumption. Powered by a robust octa-core processor, it easily handles demanding apps and creative workflows. It includes the responsive S Pen for precise drawing and writing, an 8MP ultra-wide front camera for video conferencing, and a USB-C port with fast charging capabilities.', 2924000.00, NULL, 20, 'tablet-galaxy-tab-s10-fe-plus.jpg', 'active', 0, '2026-11-15 20:59:53', '2026-11-15 22:05:57'),
(38, 2, 'iPad mini 7 Wi-Fi 128GB', 'ipad-mini-7-wifi-128gb', 'MH-TB-007', 'Compact iPad with an 8.3-inch display.', 'The iPad mini 7 delivers massive power in a highly portable design. It features a gorgeous 8.3-inch Liquid Retina display with True Tone and wide color support. Driven by the powerful A17 Pro chip, it handles console-level gaming and intensive apps flawlessly. It supports the Apple Pencil Pro for on-the-go creativity, features a 12MP ultra-wide front camera with Center Stage, and includes a fast USB-C port for connectivity and charging.', 3080000.00, NULL, 20, 'tablet-ipad-mini-7.webp', 'active', 0, '2026-11-15 20:59:53', '2026-11-15 22:05:57'),
(39, 2, 'iPad Pro M5 11-inch 256GB Wi-Fi', 'ipad-pro-m5-11-256gb-wifi', 'MH-TB-008', '11-inch pro tablet with the M5 chip.', 'Redefine portable power with the 11-inch iPad Pro. It boasts a breathtaking Tandem OLED display, offering extreme brightness and precise contrast for HDR workflows and media consumption. Driven by the revolutionary Apple M5 chip, it delivers desktop-class performance for video editing, 3D rendering, and demanding creative apps. It includes a 12MP rear camera with a LiDAR scanner for advanced augmented reality, a landscape ultra-wide front camera for video calls, and a Thunderbolt / USB 4 port for connecting to high-resolution external displays and fast storage drives.', 6210000.00, NULL, 20, 'tablet-ipad-pro-m5.jpg', 'active', 0, '2026-11-15 20:59:53', '2026-11-15 22:05:57'),
(40, 3, 'Apple USB-C 20W Power Adapter', 'apple-usb-c-20w-adapter', 'MH-CH-002', 'Compact 20W USB-C power adapter.', 'The Apple USB-C 20W Power Adapter offers fast, efficient charging at home, in the office, or on the go. While it is compatible with any USB-C-enabled device, Apple recommends pairing it with the iPad Pro and iPad Air for optimal charging performance. You can also pair it with an iPhone 8 or later to take advantage of the fast-charging feature. (Charging cable sold separately).', 95000.00, NULL, 20, 'mh-ch-002.jpg', 'active', 0, '2026-11-15 20:59:53', '2026-11-15 22:05:57'),
(41, 3, 'Samsung 45W USB-C Super Fast Charger', 'samsung-45w-usb-c-charger', 'MH-CH-003', '45W USB-C fast charging adapter.', 'The Samsung 45W USB-C Super Fast Charger delivers maximum power to compatible Galaxy smartphones and tablets. Utilizing Super Fast Charging 2.0 technology via USB PD 3.0 PPS, it rapidly refuels high-capacity batteries, drastically reducing downtime. The adapter is also backwards compatible with 15W and 25W devices, ensuring safe charging across your entire tech ecosystem.', 125000.00, NULL, 20, 'mh-ch-003.jpg', 'active', 0, '2026-11-15 20:59:53', '2026-11-15 22:05:57'),
(42, 3, 'Anker Nano 30W USB-C Charger', 'anker-nano-30w-usb-c-charger', 'MH-CH-004', 'Compact 30W USB-C charger.', 'The Anker Nano 30W USB-C Charger packs incredible power into a tiny, travel-friendly design. Utilizing advanced GaN (Gallium Nitride) technology, it safely delivers a full 30W output, enough to fast-charge smartphones, tablets, and even light laptops like the MacBook Air. Its folding plug and universally compatible USB-C port make it the ultimate minimalist travel companion.', 85000.00, NULL, 20, 'mh-ch-004.jpg', 'active', 0, '2026-11-15 20:59:53', '2026-11-15 22:05:57'),
(43, 3, 'UGREEN Nexode 65W GaN Charger', 'ugreen-nexode-65w-gan-charger', 'MH-CH-005', 'Multi-port 65W GaN wall charger.', 'The UGREEN Nexode 65W GaN Charger is a multi-device powerhouse. Featuring dual USB-C ports and a single USB-A port, it can intelligently distribute 65W of power to charge a laptop, tablet, and smartphone simultaneously. Built with advanced GaN II technology, it runs cooler and smaller than standard chargers, providing high-efficiency charging for all your essential devices.', 175000.00, NULL, 20, 'mh-ch-005.jpg', 'active', 0, '2026-11-15 20:59:53', '2026-11-15 22:05:57'),
(44, 3, 'Baseus GaN5 Pro 65W Charger', 'baseus-gan5-pro-65w-charger', 'MH-CH-006', '65W multi-port GaN charger.', 'The Baseus GaN5 Pro 65W Charger offers versatile, high-speed charging in a compact block. It is equipped with two USB-C ports and one USB-A port, supporting various fast-charging protocols including PD 3.0 and QC 4+. The upgraded GaN5 technology improves heat dissipation and power efficiency, ensuring safe, rapid charging for laptops, tablets, and phones simultaneously.', 155000.00, NULL, 20, 'mh-ch-006.jpg', 'active', 0, '2026-11-15 20:59:53', '2026-11-15 22:05:57'),
(45, 3, 'Anker 735 Nano II 65W Charger', 'anker-735-nano-ii-65w-charger', 'MH-CH-007', 'Three-port 65W GaN charger.', 'The Anker 735 Nano II 65W Charger replaces your bulky power bricks with a single, sleek solution. Powered by GaN II technology, it intelligently allocates power across its two USB-C ports and one USB-A port. It can easily fast-charge a flagship smartphone, a tablet, and a 13-inch laptop all at once, making it the perfect all-in-one charging station for home or travel.', 195000.00, NULL, 20, 'mh-ch-007.jpg', 'active', 0, '2026-11-15 20:59:53', '2026-11-15 22:05:57'),
(46, 3, 'UGREEN Nexode 100W GaN Charger', 'ugreen-nexode-100w-gan-charger', 'MH-CH-008', 'High-output multi-port 100W charger.', 'The UGREEN Nexode 100W GaN Charger is a heavy-duty desktop charging solution. Boasting an impressive 100W output, it can rapidly charge high-performance laptops. It features three USB-C ports and one USB-A port, allowing you to charge up to four devices simultaneously with intelligent power allocation. The advanced GaN chip ensures maximum efficiency and thermal safety.', 245000.00, NULL, 20, 'mh-ch-008.jpg', 'active', 0, '2026-11-15 20:59:53', '2026-11-15 22:05:57'),
(47, 4, 'Apple AirPods Pro 3', 'apple-airpods-pro-3', 'MH-EP-003', 'Wireless earbuds with active noise cancellation.', 'Immerse yourself in high-fidelity sound with the Apple AirPods Pro 3. Powered by the all-new H3 headphone chip, they feature next-level Active Noise Cancellation and Adaptive Audio that dynamically blends transparency and noise reduction based on your environment. Enjoy personalized spatial audio with dynamic head tracking and improved battery life for extended listening. The redesigned acoustic architecture supports high-resolution playback, and the included MagSafe case features a built-in speaker for Find My tracking and a convenient USB-C charging port.', 1045000.00, NULL, 20, 'mh-ep-003.jpg', 'active', 0, '2026-11-15 20:59:53', '2026-11-15 22:05:57'),
(48, 4, 'Apple AirPods 4 with ANC', 'apple-airpods-4-anc', 'MH-EP-004', 'Open-fit earbuds with active noise cancellation.', 'The Apple AirPods 4 bring advanced features to an open-ear design. Featuring a new acoustic architecture and the powerful H2 chip, they deliver incredibly clear sound and personalized spatial audio. They introduce Active Noise Cancellation to the standard AirPods lineup, drastically reducing background noise. They include a redesigned, compact charging case with USB-C and support for wireless charging.', 775000.00, NULL, 20, 'mh-ep-004.jpg', 'active', 0, '2026-11-15 20:59:53', '2026-11-15 22:05:57'),
(49, 4, 'Samsung Galaxy Buds4 Pro', 'samsung-galaxy-buds4-pro', 'MH-EP-005', 'Premium wireless earbuds with adaptive audio.', 'Experience ultimate studio-quality sound with the Samsung Galaxy Buds4 Pro. They feature a 2-way speaker system for crystal-clear audio and intelligent Active Noise Cancellation that adapts to your surroundings. The ergonomic design ensures all-day comfort, while the 360 Audio feature provides a cinematic listening experience. The sleek charging case supports both wireless charging and USB-C connectivity.', 890000.00, NULL, 20, 'mh-ep-005.jpg', 'active', 0, '2026-11-15 20:59:53', '2026-11-15 22:05:57'),
(50, 4, 'Samsung Galaxy Buds4', 'samsung-galaxy-buds4', 'MH-EP-006', 'Wireless earbuds for everyday listening.', 'The Samsung Galaxy Buds4 deliver a balanced, everyday listening experience. Designed for comfort, they offer a lightweight fit and reliable passive noise isolation. The custom-tuned drivers provide rich, dynamic audio for music and podcasts. They feature clear voice call technology, seamless pairing with Samsung devices, and a compact charging case with a USB-C port for fast top-ups.', 590000.00, NULL, 20, 'mh-ep-006.jpg', 'active', 0, '2026-11-15 20:59:53', '2026-11-15 22:05:57'),
(51, 4, 'Sony WF-1000XM6 Wireless Earbuds', 'sony-wf-1000xm6-earbuds', 'MH-EP-007', 'Noise-cancelling earbuds with high-resolution audio.', 'The Sony WF-1000XM6 wireless earbuds represent the pinnacle of noise cancellation technology. Equipped with the Integrated Processor V3, they adaptively block out environmental noise while delivering High-Resolution Audio Wireless support via LDAC. The bone conduction sensors ensure crystal-clear calls even in noisy environments. The premium charging case provides extended battery life and charges rapidly via USB-C.', 1190000.00, NULL, 20, 'mh-ep-007.jpg', 'active', 0, '2026-11-15 20:59:53', '2026-11-15 22:05:57'),
(52, 4, 'Redmi Buds 6 Pro', 'redmi-buds-6-pro', 'MH-EP-008', 'Wireless earbuds with active noise cancellation.', 'The Redmi Buds 6 Pro offer premium audio features at an incredible value. They feature custom coaxial dual drivers for deep bass and crisp treble, alongside robust Active Noise Cancellation up to 48dB. The low-latency mode is perfect for mobile gaming, while the multi-point connection allows seamless switching between two devices. The case supports fast charging via its USB-C port.', 290000.00, NULL, 20, 'mh-ep-008.jpg', 'active', 0, '2026-11-15 20:59:53', '2026-11-15 22:05:57'),
(53, 5, 'iPhone 17 Silicone Case', 'iphone-17-silicone-case', 'MH-CS-002', 'Soft-touch protective case for iPhone 17.', 'Designed specifically for the iPhone 17, this Silicone Case offers a premium feel and solid protection. The silky, soft-touch exterior feels great in your hand, while the soft microfiber interior protects your device from scratches. It features built-in magnets that align perfectly with the iPhone, offering a magical attach experience and faster MagSafe wireless charging.', 180000.00, NULL, 20, 'mh-cs-002.jpg', 'active', 0, '2026-11-15 20:59:53', '2026-11-15 22:05:57'),
(54, 5, 'iPhone 17 Pro Clear Case', 'iphone-17-pro-clear-case', 'MH-CS-003', 'Clear protective case for iPhone 17 Pro.', 'Showcase the striking design of your iPhone 17 Pro with this Clear Case. Crafted from a blend of optically clear polycarbonate and flexible materials, it fits right over the buttons for easy use. A scratch-resistant coating has been applied to both the interior and exterior, and all materials are optimized to prevent yellowing over time. Fully compatible with MagSafe accessories.', 125000.00, NULL, 20, 'mh-cs-003.webp', 'active', 0, '2026-11-15 20:59:53', '2026-11-15 22:05:57'),
(55, 5, 'iPhone Air Bumper Case', 'iphone-air-bumper-case', 'MH-CS-004', 'Slim protective bumper for iPhone Air.', 'The iPhone Air Bumper Case provides minimalist protection that complements the ultra-thin design of the device. Made from shock-absorbing TPU and aerospace-grade aluminum, it protects the edges and corners against drops while leaving the front and back entirely exposed to show off the phone\'s aesthetics and maintain its incredibly slim profile. Precise cutouts allow access to the USB-C port.', 145000.00, NULL, 20, 'mh-cs-004.png', 'active', 0, '2026-11-15 20:59:53', '2026-11-15 22:05:57'),
(56, 5, 'Galaxy S26 Ultra Protective Case', 'galaxy-s26-ultra-protective-case', 'MH-CS-005', 'Protective case designed for Galaxy S26 Ultra.', 'Keep your Samsung Galaxy S26 Ultra safe with this rugged Protective Case. Built to withstand tough environments, it features dual-layer construction with a shock-absorbing TPU inner layer and a tough polycarbonate hard shell. It includes a built-in kickstand for hands-free viewing, precise cutouts for the S Pen and USB-C port, and raised edges to protect the massive display and camera array.', 95000.00, NULL, 20, 'mh-cs-005.jpg', 'active', 0, '2026-11-15 20:59:53', '2026-11-15 22:05:57'),
(57, 5, 'Galaxy S26+ Standing Grip Case', 'galaxy-s26-plus-standing-grip-case', 'MH-CS-006', 'Protective case with integrated standing grip.', 'The Galaxy S26+ Standing Grip Case offers versatility and secure handling. The built-in, adjustable grip strap on the back provides a secure hold to prevent accidental drops while taking photos or texting. The strap also clicks securely into place to act as a sturdy kickstand for landscape media viewing. The silicone construction provides a comfortable, non-slip texture.', 125000.00, NULL, 20, 'mh-cs-006.jpg', 'active', 0, '2026-11-15 20:59:53', '2026-11-15 22:05:57'),
(58, 5, 'Galaxy A57 5G Clear Case', 'galaxy-a57-5g-clear-case', 'MH-CS-007', 'Clear protective case for Galaxy A57 5G.', 'Protect your Samsung Galaxy A57 5G with this essential Clear Case. Engineered from flexible, anti-yellowing TPU material, it provides reliable protection against daily bumps and scratches without adding bulk. The raised bezels safeguard the screen and camera lenses when placed on flat surfaces. It features highly responsive button covers and exact cutouts for the speaker and USB-C port.', 35000.00, NULL, 20, 'mh-cs-007.jpg', 'active', 0, '2026-11-15 20:59:53', '2026-11-15 22:05:57'),
(59, 5, 'Spigen Tough Armor iPhone 17 Pro Max Case', 'spigen-tough-armor-iphone-17-pro-max', 'MH-CS-008', 'Dual-layer protective case for iPhone 17 Pro Max.', 'The Spigen Tough Armor for the iPhone 17 Pro Max delivers extreme drop protection. It utilizes all-new Extreme Impact Foam embedded within dual layers of durable TPU and hard polycarbonate. Certified to military-grade drop standards, it protects your phone from the harshest impacts. It also features a built-in kickstand, precise port cutouts, and maintains compatibility with MagSafe chargers.', 165000.00, NULL, 20, 'mh-cs-008.jpg', 'active', 0, '2026-11-15 20:59:53', '2026-11-15 22:05:57'),
(60, 6, 'Anker Prime 20K 200W Power Bank', 'anker-prime-20k-200w', 'MH-PB-002', '20,000mAh capacity with up to 200W output.', 'Keep your high-performance devices powered on the go with the Anker Prime 20,000mAh power bank. Engineered for heavy-duty use, it delivers a massive 200W total power output, capable of fast-charging two laptops simultaneously. The intelligent digital display provides real-time monitoring of battery capacity, precise power output, and input speeds. It is equipped with two high-speed USB-C ports and one USB-A port for versatile multi-device connectivity, all housed in a compact design with advanced thermal management.', 690000.00, NULL, 20, 'mh-pb-002.jpg', 'active', 0, '2026-11-15 20:59:53', '2026-11-15 22:05:57'),
(61, 6, 'Anker MagGo 10K Qi2 Power Bank', 'anker-maggo-10k-qi2', 'MH-PB-003', '10,000mAh magnetic Qi2 wireless power bank.', 'The Anker MagGo 10K is a premium 10,000mAh magnetic wireless power bank featuring the latest Qi2 charging standard. It precisely aligns with MagSafe-compatible iPhones to deliver fast, 15W wireless charging on the go. It features a built-in folding stand for comfortable viewing while charging, and a bi-directional USB-C port that can be used to fast-charge the power bank itself or charge a secondary device.', 345000.00, NULL, 20, 'mh-pb-003.jpg', 'active', 0, '2026-11-15 20:59:53', '2026-11-15 22:05:57'),
(62, 6, 'Xiaomi 20K 33W Power Bank', 'xiaomi-20k-33w-power-bank', 'MH-PB-004', '20,000mAh capacity with 33W fast charging.', 'The Xiaomi 20K Power Bank combines high capacity with rapid 33W wired charging. With 20,000mAh, it can recharge a modern smartphone multiple times over a long weekend. It supports two-way fast charging via its USB-C port and includes additional USB-A ports for charging multiple devices simultaneously. Advanced circuit protection ensures safe charging for all connected electronics.', 145000.00, NULL, 20, 'mh-pb-004.jpg', 'active', 0, '2026-11-15 20:59:53', '2026-11-15 22:05:57'),
(63, 6, 'UGREEN Nexode 20K 130W Power Bank', 'ugreen-nexode-20k-130w', 'MH-PB-005', '20,000mAh capacity with up to 130W output.', 'The UGREEN Nexode 20K is a high-performance 20,000mAh power bank capable of a massive 130W total output. Designed for power users and frequent travelers, its primary high-speed USB-C port can deliver up to 100W to rapidly charge a laptop. The smart digital display shows remaining battery percentage and real-time charging status. It supports ultra-fast 65W recharging.', 490000.00, NULL, 20, 'mh-pb-005.webp', 'active', 0, '2026-11-15 20:59:53', '2026-11-15 22:05:57'),
(64, 6, 'Baseus 30K 65W Power Bank', 'baseus-30k-65w-power-bank', 'MH-PB-006', '30,000mAh capacity with 65W USB-C output.', 'The Baseus 30K Power Bank is an ultra-high-capacity charging station featuring a massive 30,000mAh battery. It delivers up to 65W of power via its high-speed USB-C port, making it fully capable of charging laptops, tablets, and smartphones. It features a robust design, an LED status display, and multiple USB-A and USB-C output ports to keep all your gear powered during long trips or power outages.', 350000.00, NULL, 20, 'mh-pb-006.jpg', 'active', 0, '2026-11-15 20:59:53', '2026-11-15 22:05:57'),
(65, 6, 'Samsung 20K 45W Power Bank', 'samsung-20k-45w-power-bank', 'MH-PB-007', '20,000mAh capacity with 45W wired charging.', 'The Samsung 20K 45W Power Bank is tailored for the Galaxy ecosystem. Boasting a 20,000mAh capacity and 45W Super Fast Charging 2.0 output, it quickly replenishes your flagship Samsung devices. Built with an eco-conscious exterior, it features dual high-speed USB-C ports to charge two devices simultaneously, ensuring your phone and tablet stay powered throughout the busiest days.', 265000.00, NULL, 20, 'mh-pb-007.jpg', 'active', 0, '2026-11-15 20:59:53', '2026-11-15 22:05:57'),
(66, 6, 'Anker Zolo 20K 30W Power Bank', 'anker-zolo-20k-30w-power-bank', 'MH-PB-008', '20,000mAh capacity with 30W USB-C output.', 'The Anker Zolo 20K offers reliable, everyday portable power. With a 20,000mAh capacity and 30W USB-C fast charging, it perfectly balances speed and portability for smartphones and tablets. It features Anker\'s MultiProtect safety system, a built-in braided USB-C cable for ultimate convenience, and an integrated digital display to instantly check your remaining battery life.', 195000.00, NULL, 20, 'mh-pb-008.jpg', 'active', 0, '2026-11-15 20:59:53', '2026-11-15 22:05:57'),
(67, 7, 'Apple Pencil Pro', 'apple-pencil-pro', 'MH-AC-002', 'Creative stylus with pressure and tilt support.', 'The Apple Pencil Pro elevates your creative experience on the iPad. It introduces advanced features like a squeeze sensor to quickly switch tools, a gyroscope for precise barrel roll control of shaped pen and brush tools, and haptic feedback for tactile confirmation. It attaches magnetically for pairing and charging, and now includes Find My support to easily locate it if misplaced.', 525000.00, NULL, 20, 'mh-ac-002.jpg', 'active', 0, '2026-11-15 20:59:53', '2026-11-15 22:05:57'),
(68, 7, 'Apple AirTag (1 pack)', 'apple-airtag-1-pack', 'MH-AC-003', 'Item tracker that works with the Find My network.', 'Keep track of your most important items with the Apple AirTag. Attach it to your keys, slip it in your backpack, or put it in your wallet to easily track them in the Find My app alongside your Apple devices. It features a built-in speaker to play a sound, Precision Finding using Ultra Wideband technology to guide you right to it, and runs on a standard, easily replaceable CR2032 coin battery.', 144000.00, NULL, 20, 'mh-ac-003.webp', 'active', 0, '2026-11-15 20:59:53', '2026-11-15 22:05:57'),
(69, 7, 'Samsung S Pen for Galaxy Tab S11', 'samsung-s-pen-tab-s11', 'MH-AC-004', 'Replacement S Pen for compatible Galaxy tablets.', 'The replacement S Pen for the Samsung Galaxy Tab S11 provides a natural writing and drawing experience with ultra-low latency. It features precise 4,096 levels of pressure sensitivity and a fine 0.7mm tip for accurate sketching and note-taking. It attaches magnetically to the back of the tablet for secure storage and wireless charging, and supports Bluetooth Air Actions for remote control of presentations and media.', 175000.00, NULL, 20, 'mh-ac-004.jpg', 'active', 0, '2026-11-15 20:59:53', '2026-11-15 22:05:57'),
(70, 7, 'Apple MagSafe Charger', 'apple-magsafe-charger', 'MH-AC-005', 'Magnetic wireless charger for compatible iPhone models.', 'The Apple MagSafe Charger makes wireless charging a snap. The perfectly aligned magnets attach securely to your iPhone 12 or later, providing safe and fast wireless charging up to 15W. It maintains compatibility with standard Qi charging, allowing you to wirelessly charge older iPhone models or AirPods with a wireless charging case. Requires a 20W USB-C power adapter (sold separately).', 175000.00, NULL, 20, 'mh-ac-005.jpg', 'active', 0, '2026-11-15 20:59:53', '2026-11-15 22:05:57'),
(71, 7, 'USB-C 8-in-1 Hub', 'usb-c-8-in-1-hub', 'MH-AC-006', 'Multi-port USB-C hub for compatible devices.', 'Expand your device\'s connectivity with this 8-in-1 USB-C Hub. Ideal for modern laptops and tablets, it plugs into a single USB-C port to provide a 4K@60Hz HDMI port for external displays, three high-speed USB-A 3.0 ports for peripherals, SD and micro SD card readers for quick photo transfers, a Gigabit Ethernet port for stable wired internet, and a 100W USB-C PD pass-through charging port.', 95000.00, NULL, 20, 'mh-ac-006.png', 'active', 0, '2026-11-15 20:59:53', '2026-11-15 22:05:57'),
(72, 7, '3-in-1 Wireless Charging Stand', '3-in-1-wireless-charging-stand', 'MH-AC-007', 'Charge a phone, earbuds, and watch in one place.', 'Declutter your nightstand with this sleek 3-in-1 Wireless Charging Stand. Designed to charge your daily essentials simultaneously, it features dedicated magnetic charging spots for your smartphone, wireless earbuds, and smartwatch. The phone mount holds your device at a comfortable viewing angle for bedside media or video calls, and connects to a single wall outlet via a provided USB-C cable.', 195000.00, NULL, 20, 'mh-ac-007.jpg', 'active', 0, '2026-11-15 20:59:53', '2026-11-15 22:05:57'),
(73, 7, 'Braided USB-C Cable 2m', 'braided-usb-c-cable-2m', 'MH-AC-008', 'Durable 2-metre USB-C charging and sync cable.', 'This 2-metre Braided USB-C to USB-C Cable combines high performance with ultimate durability. The tough nylon braided exterior prevents tangling and protects against fraying, while reinforced aluminum connectors ensure long-lasting wear. Supporting up to 100W Power Delivery, it is fully capable of fast-charging demanding devices like laptops, alongside smartphones and tablets.', 35000.00, NULL, 20, 'mh-ac-008.jpg', 'active', 0, '2026-11-15 20:59:53', '2026-11-15 22:05:57'),
(102, 2, 'Deco Fun L Drawing Tablet', 'deco-fun-l-drawing-tablet', 'MH-TB-010', 'A large 10x6-inch digital drawing tablet featuring a battery-free stylus', 'The XP-Pen Deco Fun L is a versatile drawing tablet designed for digital artists, students, and professionals. It features a spacious 10 x 6.27-inch active working area, providing ample room for precise strokes and unrestricted creativity. It comes equipped with an advanced, battery-free stylus offering 8,192 levels of pressure sensitivity and up to 60 degrees of tilt function for natural, fluid drawing and shading. Plug-and-play ready via its high-speed USB-C port, it offers broad multi-platform compatibility across Windows, macOS, Android, and ChromeOS devices, making it an essential tool for digital illustration, 3D sculpting, and interactive online tutoring.', 399000.00, NULL, 50, 'p_1794756102_9db09bf6.webp', 'active', 0, '2026-11-15 21:51:42', '2026-11-15 22:02:27');

-- --------------------------------------------------------

--
-- Table structure for table `product_images`
--

CREATE TABLE `product_images` (
  `id` int(10) UNSIGNED NOT NULL,
  `product_id` int(10) UNSIGNED NOT NULL,
  `image` varchar(255) NOT NULL,
  `label` varchar(80) DEFAULT NULL,
  `sort_order` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product_images`
--

INSERT INTO `product_images` (`id`, `product_id`, `image`, `label`, `sort_order`, `created_at`) VALUES
(1, 1, 'samsung-galaxy-a55-back.jpg', 'Black', 1, '2026-11-10 22:13:23'),
(2, 1, 'samsung-galaxy-a55-blue.jpg', 'Ice Blue', 2, '2026-11-10 22:13:23'),
(3, 1, 'samsung-galaxy-a55-lilac.jpg', 'Lilac', 3, '2026-11-10 22:13:23'),
(4, 1, 'samsung-galaxy-a55-all-colors.jpg', 'All colors', 4, '2026-11-10 22:13:23'),
(8, 2, 'iphone-15-pink.jpg', 'Pink', 1, '2026-11-10 23:07:50'),
(9, 2, 'iphone-15-blue.jpg', 'Blue', 2, '2026-11-10 23:07:50'),
(10, 2, 'iphone-15-black.jpg', 'Black', 3, '2026-11-10 23:07:50'),
(11, 2, 'iphone-15-all-colors.jpg', 'All colors', 4, '2026-11-10 23:07:50'),
(12, 16, 'iphone-17e-black.jpg', 'Black', 1, '2026-11-15 20:33:51'),
(13, 16, 'iphone-17e-white.jpg', 'White', 2, '2026-11-15 20:33:51'),
(14, 16, 'iphone-17e-soft-pink.png', 'Soft Pink', 3, '2026-11-15 20:33:51'),
(15, 17, 'galaxy-s26-ultra-black.png', 'Black', 1, '2026-11-15 20:33:51'),
(16, 17, 'galaxy-s26-ultra-sky-blue.jpg', 'Sky Blue', 2, '2026-11-15 20:33:51'),
(17, 17, 'galaxy-s26-ultra-pink-gold.jpg', 'Pink Gold', 3, '2026-11-15 20:33:51'),
(19, 18, 'iphone-17-black.jpg', 'Black', 1, '2026-11-15 20:49:27'),
(20, 19, 'iphone-17-pro-orange.jpg', 'Cosmic Orange', 1, '2026-11-15 20:49:27'),
(21, 20, 'iphone-17-pro-max-blue.png', 'Deep Blue', 1, '2026-11-15 20:49:27'),
(22, 21, 'iphone-air-space-black.png', 'Space Black', 1, '2026-11-15 20:49:27'),
(23, 22, 'galaxy-s26-black.jpg', 'Black', 1, '2026-11-15 20:49:27'),
(24, 23, 'galaxy-s26-plus-black.jpg', 'Black', 1, '2026-11-15 20:49:27'),
(25, 24, 'galaxy-a57-navy.jpg', 'Awesome Navy', 1, '2026-11-15 20:49:27'),
(26, 25, 'galaxy-a37-black.jpg', 'Awesome Charcoal', 1, '2026-11-15 20:49:27'),
(34, 18, 'iphone-17-lavender.jpg', 'Lavender', 1, '2026-11-15 20:52:07'),
(35, 23, 'galaxy-s26-plus-violet.webp', 'Cobalt Violet', 1, '2026-11-15 20:52:07'),
(36, 24, 'galaxy-a57-gray.jpg', 'Awesome Gray', 1, '2026-11-15 20:52:07'),
(37, 102, 'p_1794756102_91e2d9b2.webp', NULL, 0, '2026-11-15 21:51:42'),
(38, 102, 'p_1794756117_544292ab.webp', NULL, 1, '2026-11-15 21:51:57');

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(10) UNSIGNED NOT NULL,
  `key_name` varchar(100) NOT NULL,
  `value` text DEFAULT NULL,
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `key_name`, `value`, `updated_at`) VALUES
(1, 'site_name', 'MobileHub', '2026-11-10 20:00:42'),
(2, 'site_email', 'admin@mobilehub.com', '2026-11-15 19:27:43'),
(3, 'site_about', 'MobileHub E-commerce System', '2026-11-10 21:40:18'),
(4, 'items_per_page', '10', '2026-11-10 19:45:47'),
(5, 'currency_code', 'MMK', '2026-11-10 21:40:30'),
(6, 'currency_symbol', 'MMK', '2026-11-10 21:40:40'),
(7, 'shipping_fee', '5000.00', '2026-11-10 21:41:00'),
(14, 'store_address', 'Open Daily (9:00 AM – 6:30 PM)\r\nNo.289, Pyay Road, Sanchaung Township, Yangon\r\nTel & Viber: 09450742359. Please contact the phone number if you have any inquiries.', '2026-11-12 00:03:08');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_log`
--
ALTER TABLE `activity_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_log_user` (`user_id`);

--
-- Indexes for table `admin_users`
--
ALTER TABLE `admin_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_users_role` (`role`),
  ADD KEY `idx_users_status` (`status`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `idx_categories_status` (`status`);

--
-- Indexes for table `customer_users`
--
ALTER TABLE `customer_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_customer_users_email` (`email`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_number` (`order_number`),
  ADD KEY `idx_orders_status` (`status`),
  ADD KEY `idx_orders_email` (`customer_email`),
  ADD KEY `idx_orders_created_at` (`created_at`),
  ADD KEY `idx_orders_customer_id` (`customer_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_oi_order` (`order_id`),
  ADD KEY `idx_oi_product` (`product_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `idx_products_category` (`category_id`),
  ADD KEY `idx_products_status` (`status`),
  ADD KEY `idx_products_featured` (`featured`);

--
-- Indexes for table `product_images`
--
ALTER TABLE `product_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_product_images_product` (`product_id`,`sort_order`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `key_name` (`key_name`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_log`
--
ALTER TABLE `activity_log`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=72;

--
-- AUTO_INCREMENT for table `admin_users`
--
ALTER TABLE `admin_users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `customer_users`
--
ALTER TABLE `customer_users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=103;

--
-- AUTO_INCREMENT for table `product_images`
--
ALTER TABLE `product_images`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_log`
--
ALTER TABLE `activity_log`
  ADD CONSTRAINT `fk_log_user` FOREIGN KEY (`user_id`) REFERENCES `admin_users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `fk_orders_customer` FOREIGN KEY (`customer_id`) REFERENCES `customer_users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `fk_oi_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_oi_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `fk_products_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `product_images`
--
ALTER TABLE `product_images`
  ADD CONSTRAINT `fk_product_images_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
