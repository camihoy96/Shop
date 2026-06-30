-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3307
-- Generation Time: Jun 30, 2026 at 08:39 AM
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
-- Database: `shop`
--

-- --------------------------------------------------------

--
-- Table structure for table `conversation`
--

CREATE TABLE `conversation` (
  `id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `sender` enum('user','admin') NOT NULL,
  `message` text NOT NULL,
  `date_sent` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) DEFAULT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `message` text NOT NULL,
  `date_sent` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_read` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`id`, `name`, `email`, `message`, `date_sent`, `is_read`) VALUES
(1, 'Charlie Amihoy', 'clamihoy@gmail.com', 'Hey there', '2026-01-18 15:39:35', 1),
(2, 'jane', 'jane@gmail.com', 'i want to change my adress', '2026-06-30 05:03:55', 0);

-- --------------------------------------------------------

--
-- Table structure for table `new_users`
--

CREATE TABLE `new_users` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `street` varchar(255) DEFAULT NULL,
  `barangay` varchar(255) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `province` varchar(100) DEFAULT NULL,
  `zip_code` varchar(20) DEFAULT NULL,
  `country` varchar(50) DEFAULT 'PH',
  `password` varchar(255) NOT NULL,
  `user_type` enum('customer','admin') DEFAULT 'customer',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `new_users`
--

INSERT INTO `new_users` (`id`, `name`, `email`, `phone`, `street`, `barangay`, `city`, `province`, `zip_code`, `country`, `password`, `user_type`, `created_at`, `updated_at`) VALUES
(1, 'Juan', 'juan@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, 'PH', '$2y$10$2VV2mSmE0t1axoXPZl6k/uG7GQniaRYSK.3wA4MqxvNdOcAaTvREC', 'customer', '2026-01-23 02:43:53', '2026-01-23 02:43:53'),
(4, 'ceasna', 'ces@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, 'PH', '$2y$10$n1u79yQJFBFdf1JBXymezeXnsbTQIa/dfwkS6bf5HXS621QrM6j3u', 'customer', '2026-05-17 03:41:28', '2026-05-17 03:41:28'),
(5, 'balds', 'bal@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, 'PH', '$2y$10$1lbfu6zIcstUPN/jmoD95uM305so8VZ0MbzJz2NF.G.8IH19Tl8yW', 'customer', '2026-06-29 13:11:59', '2026-06-29 13:11:59'),
(6, 'kayye', 'kay@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, 'PH', '$2y$10$vWxpCI7JeTnUJnFGUPzIIOYuhmQD9A7y5TfmxzNjMJyQ0QDnvoUpC', 'customer', '2026-06-29 14:12:48', '2026-06-29 14:12:48'),
(7, 'a', 'a@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, 'PH', '$2y$10$GdAK3w2fo2f5wsNGBf2gTup7YtYxONxU5SGI84V6G1caI8PJ54WuC', 'customer', '2026-06-29 14:31:18', '2026-06-29 14:31:18'),
(8, 'arjay', 'arj@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, 'PH', '$2y$10$oVqgJkqUj9lWvzSAfVAhdeeiQIcA08A/dKRKPPFJ2w5AGyV.s.YXy', 'admin', '2026-06-29 15:10:16', '2026-06-30 04:54:25'),
(9, 'jane ', 'jane@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, 'PH', '$2y$10$wotvaGIpZf45pLUUaxsG4O6gxpEk61f8RV1pK6ptTFIb9Tr7oYv6W', 'customer', '2026-06-30 05:34:33', '2026-06-30 05:34:33');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `order_number` varchar(50) NOT NULL,
  `customer_name` varchar(255) NOT NULL,
  `customer_email` varchar(255) NOT NULL,
  `customer_phone` varchar(50) NOT NULL,
  `shipping_address` text NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `payment_status` enum('pending','paid','failed') DEFAULT 'pending',
  `order_status` enum('pending','processing','shipped','delivered','cancelled') DEFAULT 'pending',
  `shipping_method` varchar(50) DEFAULT NULL,
  `shipping_cost` decimal(10,2) DEFAULT 0.00,
  `subtotal` decimal(10,2) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `tracking_number` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_read` tinyint(1) DEFAULT 0,
  `delivered_at` datetime DEFAULT NULL,
  `expected_delivery_start` date DEFAULT NULL,
  `expected_delivery_end` date DEFAULT NULL,
  `payment_details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payment_details`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `order_number`, `customer_name`, `customer_email`, `customer_phone`, `shipping_address`, `payment_method`, `payment_status`, `order_status`, `shipping_method`, `shipping_cost`, `subtotal`, `total_amount`, `tracking_number`, `notes`, `created_at`, `updated_at`, `is_read`, `delivered_at`, `expected_delivery_start`, `expected_delivery_end`, `payment_details`) VALUES
(3, 'CV-87391699', 'Charlie Amihoy', 'hacknet395@gmail.com', '+639056152262', 'Cadawinonan, Cadawinon, Dumaguete City, Negros Oriental, 6200, PH', 'gcash', 'paid', 'delivered', 'express', 12.99, 484.00, 496.99, 'TRK-9B08C2A8B5', NULL, '2026-01-21 09:23:11', '2026-01-22 07:26:02', 1, '2026-01-22 08:26:02', '2026-01-21', '2026-01-24', '{\"provider\":\"gcash\",\"account\":\"camihoy96@gmail.com\",\"reference\":\"09056152262\"}'),
(5, 'CV-63249565', 'mic dan', 'mic@gmail.com', '0923237237', 'Purok 4, Actin, Basay, Negros Oriental, 6222, PH', 'cod', 'pending', 'cancelled', 'standard', 5.99, 299.00, 304.99, 'TRK-293FE9A71E', NULL, '2026-01-23 10:14:09', '2026-06-30 04:51:13', 0, NULL, '2026-01-23', '2026-01-30', '{\"method\":\"Cash on Delivery\"}'),
(6, 'CV-42496827', 'kay villa', 'kay@gmail.com', '099545454556', 'purok 7, Actin, Basay, Negros Oriental, 6222, PH', 'cod', 'pending', 'processing', 'standard', 5.99, 279.00, 284.99, 'TRK-D78F077616', NULL, '2026-06-29 14:14:56', '2026-06-30 03:07:08', 1, NULL, '2026-06-29', '2026-07-06', '{\"method\":\"Cash on Delivery\"}'),
(7, 'CV-43614527', 'John Doe', 'pedro@gmail.com', '09480070996', 'purok 7, Nagboa-alao, Dumaguete  City, Negros Oriental, 6222, US', 'cod', 'pending', 'pending', 'standard', 5.99, 229.00, 234.99, 'TRK-99691DAB07', NULL, '2026-06-29 14:33:34', '2026-06-29 14:33:34', 0, NULL, '2026-06-29', '2026-07-06', '{\"method\":\"Cash on Delivery\"}'),
(8, 'CV-45919770', 'arjay dally', 'arj@gmail.com', '092838233', 'Purok 4, Nagboa-alao, Dumaguete  City, Negros Oriental, 6200, PH', 'cod', 'paid', 'delivered', 'standard', 5.99, 349.00, 354.99, NULL, NULL, '2026-06-29 15:11:59', '2026-06-30 04:47:15', 0, '2026-06-30 12:47:15', '2026-06-29', '2026-07-06', '{\"method\":\"Cash on Delivery\"}'),
(9, 'CV-46170205', 'arjay dally', 'arj@gmail.com', '092838233', 'Purok 4, Nagboa-alao, Dumaguete  City, Negros Oriental, 6200, PH', 'cod', 'pending', 'shipped', 'standard', 5.99, 279.00, 284.99, 'TRK-F546B691F3', NULL, '2026-06-29 15:16:10', '2026-06-30 04:47:49', 1, NULL, '2026-06-29', '2026-07-06', '{\"method\":\"Cash on Delivery\"}'),
(10, 'CV-46703136', 'arjay dally', 'baldevinojayr@gmail.com', '092838233', 'Purok 4, Nagboa-alao, Dumaguete  City, Negros Oriental, 6200, PH', 'cod', 'pending', 'shipped', 'standard', 5.99, 229.00, 234.99, NULL, NULL, '2026-06-29 15:25:03', '2026-06-30 03:06:30', 0, NULL, '2026-06-29', '2026-07-06', '{\"method\":\"Cash on Delivery\"}'),
(11, 'CV-47073750', 'arjay dally', 'baldevinojayr@gmail.com', '092838233', 'Purok 4, Nagboa-alao, Dumaguete  City, Negros Oriental, 6200, PH', 'cod', 'pending', 'shipped', 'standard', 5.99, 257.00, 262.99, 'TRK-180B093B65', NULL, '2026-06-29 15:31:13', '2026-06-30 03:07:56', 1, NULL, '2026-06-29', '2026-07-06', '{\"method\":\"Cash on Delivery\"}'),
(12, 'CV-00478001', 'Juan dfsffdfdfd', 'juan@gmail.com', '4451852514', 'fdfdfdf, fdfdfdf, fdfdfd, dffdfdfdfd, 411121, PH', 'cod', 'pending', 'pending', 'standard', 5.99, 299.00, 304.99, 'TRK-07EC502858', NULL, '2026-06-30 06:21:18', '2026-06-30 06:21:18', 0, NULL, '2026-06-30', '2026-07-07', '{\"method\":\"Cash on Delivery\"}'),
(13, 'CV-00606697', 'Juan fdgd', 'juan@gmail.com', 'dfgfdg', 'fbdf, gfgfdgg, dfgdfgfdg, gfgdfgfdgfdg, fgfggf, PH', 'cod', 'pending', 'pending', 'standard', 5.99, 299.00, 304.99, 'TRK-FFAD72579A', NULL, '2026-06-30 06:23:26', '2026-06-30 06:23:26', 0, NULL, '2026-06-30', '2026-07-07', '{\"method\":\"Cash on Delivery\"}'),
(14, 'CV-00650305', 'Juan fdgd', 'juan@gmail.com', 'dfgfdg', 'fbdf, gfgfdgg, dfgdfgfdg, gfgdfgfdgfdg, fgfggf, PH', 'cod', 'pending', 'pending', 'standard', 5.99, 349.00, 354.99, 'TRK-E17653686B', NULL, '2026-06-30 06:24:10', '2026-06-30 06:24:10', 0, NULL, '2026-06-30', '2026-07-07', '{\"method\":\"Cash on Delivery\"}');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `product_price` decimal(10,2) NOT NULL,
  `quantity` int(11) NOT NULL,
  `total_price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `product_name`, `product_price`, `quantity`, `total_price`) VALUES
(3, 3, 10, 'ChronoVerse Classic', 255.00, 1, 255.00),
(4, 3, 21, 'ChronoVerse Vintage', 229.00, 1, 229.00),
(6, 5, 15, 'ChronoVerse Skeleton', 299.00, 1, 299.00),
(7, 6, 20, 'ChronoVerse Aurora', 279.00, 1, 279.00),
(8, 7, 21, 'ChronoVerse Vintage', 229.00, 1, 229.00),
(9, 8, 19, 'ChronoVerse Eclipse', 349.00, 1, 349.00),
(10, 9, 20, 'ChronoVerse Aurora', 279.00, 1, 279.00),
(11, 10, 21, 'ChronoVerse Vintage', 229.00, 1, 229.00),
(12, 11, 11, 'ChronoVerse Pro18', 257.00, 1, 257.00),
(13, 12, 15, 'ChronoVerse Skeleton', 299.00, 1, 299.00),
(14, 13, 15, 'ChronoVerse Skeleton', 299.00, 1, 299.00),
(15, 14, 19, 'ChronoVerse Eclipse', 349.00, 1, 349.00);

-- --------------------------------------------------------

--
-- Table structure for table `order_tracking`
--

CREATE TABLE `order_tracking` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `tracking_number` varchar(100) NOT NULL,
  `status` enum('pending','processing','shipped','out_for_delivery','delivered') DEFAULT 'pending',
  `location` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `estimated_delivery` date DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_tracking`
--

INSERT INTO `order_tracking` (`id`, `order_id`, `tracking_number`, `status`, `location`, `description`, `estimated_delivery`, `updated_at`, `created_at`) VALUES
(8, 3, 'TRK-9B08C2A8B5', 'pending', NULL, 'Order received and is being processed.', '2026-01-24', '2026-01-21 09:23:11', '2026-01-21 17:23:11'),
(9, 3, '', 'processing', 'Warehouse', 'Order accepted and now processing', NULL, '2026-01-21 10:30:20', '2026-01-21 18:30:20'),
(10, 3, '', 'shipped', NULL, 'Order has been shipped to customer', NULL, '2026-01-22 07:16:20', '2026-01-22 15:16:20'),
(11, 3, 'TRK-9B08C2A8B5', 'delivered', NULL, 'Order marked as delivered', NULL, '2026-01-22 07:17:24', '2026-01-22 15:17:24'),
(12, 3, 'TRK-9B08C2A8B5', 'delivered', NULL, 'Order delivered and payment marked as paid', NULL, '2026-01-22 07:26:03', '2026-01-22 15:26:03'),
(14, 5, 'TRK-293FE9A71E', 'pending', NULL, 'Order received and is being processed.', '2026-01-30', '2026-01-23 10:14:09', '2026-01-23 18:14:09'),
(15, 6, 'TRK-D78F077616', 'pending', NULL, 'Order received and is being processed.', '2026-07-06', '2026-06-29 14:14:56', '2026-06-29 22:14:56'),
(16, 7, 'TRK-99691DAB07', 'pending', NULL, 'Order received and is being processed.', '2026-07-06', '2026-06-29 14:33:34', '2026-06-29 22:33:34'),
(17, 8, 'TRK-08074906BC', 'pending', NULL, 'Order received and is being processed.', '2026-07-06', '2026-06-29 15:11:59', '2026-06-29 23:11:59'),
(18, 9, 'TRK-F546B691F3', 'pending', NULL, 'Order received and is being processed.', '2026-07-06', '2026-06-29 15:16:10', '2026-06-29 23:16:10'),
(19, 10, 'TRK-CD1DC405BD', 'pending', NULL, 'Order received and is being processed.', '2026-07-06', '2026-06-29 15:25:03', '2026-06-29 23:25:03'),
(20, 11, 'TRK-180B093B65', 'pending', NULL, 'Order received and is being processed.', '2026-07-06', '2026-06-29 15:31:13', '2026-06-29 23:31:13'),
(21, 11, '', 'processing', 'Warehouse', 'Order accepted and now processing', NULL, '2026-06-29 16:04:51', '2026-06-30 00:04:51'),
(22, 10, '', 'shipped', NULL, 'Order has been shipped', NULL, '2026-06-30 03:06:30', '2026-06-30 11:06:30'),
(23, 9, '', 'processing', 'Warehouse', 'Order accepted and now processing', NULL, '2026-06-30 03:07:00', '2026-06-30 11:07:00'),
(24, 6, '', 'processing', 'Warehouse', 'Order accepted and now processing', NULL, '2026-06-30 03:07:08', '2026-06-30 11:07:08'),
(25, 11, '', 'shipped', NULL, 'Order has been shipped to customer', NULL, '2026-06-30 03:07:56', '2026-06-30 11:07:56'),
(26, 8, '', 'delivered', NULL, 'Order delivered successfully', NULL, '2026-06-30 04:47:15', '2026-06-30 12:47:15'),
(27, 9, '', 'shipped', NULL, 'Order has been shipped to customer', NULL, '2026-06-30 04:47:49', '2026-06-30 12:47:49'),
(28, 5, 'TRK-293FE9A71E', '', NULL, 'Order cancelled', NULL, '2026-06-30 04:51:13', '2026-06-30 12:51:13'),
(29, 12, 'TRK-07EC502858', 'pending', NULL, 'Order received and is being processed.', '2026-07-07', '2026-06-30 06:21:18', '2026-06-30 14:21:18'),
(30, 13, 'TRK-FFAD72579A', 'pending', NULL, 'Order received and is being processed.', '2026-07-07', '2026-06-30 06:23:26', '2026-06-30 14:23:26'),
(31, 14, 'TRK-E17653686B', 'pending', NULL, 'Order received and is being processed.', '2026-07-07', '2026-06-30 06:24:10', '2026-06-30 14:24:10');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `category` varchar(100) DEFAULT NULL,
  `featured` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `stock` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `description`, `price`, `category`, `featured`, `created_at`, `updated_at`, `stock`) VALUES
(10, 'ChronoVerse Classic', 'Timeless elegance meets modern design. The Classic watch features a minimalist dial with subtle cosmic accents, perfect for both formal and casual occasions', 255.00, 'high-end', 1, '2026-01-19 06:40:20', '2026-01-20 03:14:35', 15),
(11, 'ChronoVerse Pro18', 'Built for professionals who demand precision and style. The Pro model features advanced complications, premium materials, and enhanced durability.', 257.00, 'high-end', 1, '2026-01-19 07:00:13', '2026-06-29 15:31:13', 19),
(15, 'ChronoVerse Skeleton', 'Experience the beauty of time with our signature skeleton watch. Exposed gears and intricate mechanics showcase the precision engineering, while the deep blue cosmic design adds a touch of elegance.', 299.00, 'limited', 0, '2026-01-19 07:56:38', '2026-06-30 06:23:26', 9),
(19, 'ChronoVerse Eclipse', 'Black on black stealth design for those who appreciate understated elegance. The Eclipse watch features a fully blacked-out aesthetic with subtle details.', 349.00, 'limited', 0, '2026-01-19 08:07:04', '2026-06-30 06:24:10', 7),
(20, 'ChronoVerse Aurora', 'Inspired by the Northern Lights, this watch features a mesmerizing dial that changes color with the light, creating a unique visual experience.', 279.00, 'premium', 0, '2026-01-19 08:09:35', '2026-06-29 15:16:10', 4),
(21, 'ChronoVerse Vintage', 'Retro design meets modern technology. The Vintage collection pays homage to classic watch designs while incorporating modern reliability.', 289.00, 'premium', 0, '2026-01-19 08:10:53', '2026-06-30 04:35:14', 14),
(23, 'ChronoVerse Mechanical', 'Its stainless steel construction, luminous hands, and 30-meter water resistance make it a reliable and sophisticated choice for both business and casual wear.', 324.00, 'premium', 0, '2026-06-30 04:43:30', '2026-06-30 04:43:30', 16);

-- --------------------------------------------------------

--
-- Table structure for table `product_categories`
--

CREATE TABLE `product_categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_categories`
--

INSERT INTO `product_categories` (`id`, `name`, `slug`, `created_at`) VALUES
(1, 'Limited', 'limited', '2026-01-19 06:31:30'),
(2, 'Premium', 'premium', '2026-01-19 06:37:56'),
(3, 'High End', 'high-end', '2026-01-19 07:52:29');

-- --------------------------------------------------------

--
-- Table structure for table `product_features`
--

CREATE TABLE `product_features` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `feature_text` text NOT NULL,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_features`
--

INSERT INTO `product_features` (`id`, `product_id`, `feature_text`, `sort_order`, `created_at`) VALUES
(179, 10, 'Minimalist cosmic-inspired dial design', 0, '2026-01-20 03:14:35'),
(180, 10, 'Premium leather strap with quick-release mechanism', 1, '2026-01-20 03:14:35'),
(181, 10, 'Scratch-resistant mineral glass', 2, '2026-01-20 03:14:35'),
(182, 10, '30m water resistance (3 ATM)', 3, '2026-01-20 03:14:35'),
(183, 10, 'Japanese quartz movement (battery life: 3 years)', 4, '2026-01-20 03:14:35'),
(184, 10, 'Stainless steel case (40mm diameter)', 5, '2026-01-20 03:14:35'),
(185, 10, 'Date display at 3 o\'clock position', 6, '2026-01-20 03:14:35'),
(186, 15, 'Exposed gear mechanism showing intricate movement', 0, '2026-01-20 03:14:44'),
(187, 15, 'Stainless steel 316L case (42mm diameter)', 1, '2026-01-20 03:14:44'),
(188, 15, 'Scratch-resistant sapphire crystal glass', 2, '2026-01-20 03:14:44'),
(189, 15, 'Water resistant up to 50 meters (5 ATM)', 3, '2026-01-20 03:14:44'),
(190, 15, 'Swiss automatic movement with 40-hour power reserve', 4, '2026-01-20 03:14:44'),
(191, 15, 'Hand-stitched genuine leather strap', 5, '2026-01-20 03:14:44'),
(192, 15, 'Luminous hands and markers', 6, '2026-01-20 03:14:44'),
(193, 19, 'Full black PVD stainless steel case', 0, '2026-01-20 03:14:54'),
(194, 19, 'Matte black dial with sunray finish', 1, '2026-01-20 03:14:54'),
(195, 19, 'Scratch-resistant sapphire crystal', 2, '2026-01-20 03:14:54'),
(196, 19, '50m water resistance (5 ATM)', 3, '2026-01-20 03:14:54'),
(197, 19, 'Swiss automatic movement', 4, '2026-01-20 03:14:54'),
(198, 19, 'Black silicone strap with deployment clasp', 5, '2026-01-20 03:14:54'),
(199, 19, 'Skeleton hands with red accents', 6, '2026-01-20 03:14:54'),
(200, 20, 'Aurora-inspired dial with color-shifting finish', 0, '2026-01-20 03:15:10'),
(201, 20, 'Stainless steel case with polished finish', 1, '2026-01-20 03:15:10'),
(202, 20, 'Domed mineral crystal', 2, '2026-01-20 03:15:10'),
(203, 20, '30m water resistance (3 ATM)', 3, '2026-01-20 03:15:10'),
(204, 20, 'Japanese automatic movement', 4, '2026-01-20 03:15:10'),
(205, 20, 'Stainless steel bracelet with butterfly clasp', 5, '2026-01-20 03:15:10'),
(206, 20, 'Exhibition case back', 6, '2026-01-20 03:15:10'),
(214, 11, 'Chronograph function with 1/10 second accuracy', 0, '2026-01-21 04:21:23'),
(215, 11, 'Titanium case with PVD coating (44mm diameter)', 1, '2026-01-21 04:21:23'),
(216, 11, 'Super-LumiNova hands and markers', 2, '2026-01-21 04:21:23'),
(217, 11, '100m water resistance (10 ATM)', 3, '2026-01-21 04:21:23'),
(218, 11, 'Swiss automatic chronograph movement', 4, '2026-01-21 04:21:23'),
(219, 11, 'Ceramic bezel with tachymeter scale', 5, '2026-01-21 04:21:23'),
(220, 11, 'Sapphire crystal with anti-reflective coating', 6, '2026-01-21 04:21:23'),
(221, 21, 'Vintage-inspired cream-colored dial', 0, '2026-06-30 04:35:14'),
(222, 21, 'Hand-wound mechanical movement', 1, '2026-06-30 04:35:14'),
(223, 21, 'Acrylic domed crystal for authentic look', 2, '2026-06-30 04:35:14'),
(224, 21, '30m water resistance (3 ATM)', 3, '2026-06-30 04:35:14'),
(225, 21, '42-hour power reserve indicator', 4, '2026-06-30 04:35:14'),
(226, 21, 'Genuine leather bund strap', 5, '2026-06-30 04:35:14'),
(227, 21, 'Small seconds sub-dial at 6 o\'clock', 6, '2026-06-30 04:35:14'),
(228, 23, 'Automatic self-winding mechanical movement', 0, '2026-06-30 04:43:30'),
(229, 23, 'Elegant skeleton dial design', 1, '2026-06-30 04:43:30'),
(230, 23, 'Durable stainless steel case and bracelet', 2, '2026-06-30 04:43:30'),
(231, 23, '30M (3ATM) water resistance', 3, '2026-06-30 04:43:30'),
(232, 23, 'Luminous hands and hour markers', 4, '2026-06-30 04:43:30'),
(233, 23, 'No battery required—powered by wrist movement', 5, '2026-06-30 04:43:30');

-- --------------------------------------------------------

--
-- Table structure for table `product_images`
--

CREATE TABLE `product_images` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `image_path` varchar(500) NOT NULL,
  `is_featured` tinyint(1) DEFAULT 0,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_images`
--

INSERT INTO `product_images` (`id`, `product_id`, `image_path`, `is_featured`, `sort_order`, `created_at`) VALUES
(5, 10, 'uploads/products/696dd1d454a3a_6.png', 1, 0, '2026-01-19 06:40:20'),
(6, 11, 'uploads/products/696dd67e076ed_3.png', 1, 0, '2026-01-19 07:00:14'),
(8, 15, 'uploads/products/696de3b639083_2.png', 1, 0, '2026-01-19 07:56:38'),
(12, 19, 'uploads/products/696de6283fd41_5.png', 1, 0, '2026-01-19 08:07:04'),
(13, 20, 'uploads/products/696de6bfb467d_4.png', 1, 0, '2026-01-19 08:09:35'),
(14, 21, 'uploads/products/696de70dad071_7.png', 1, 0, '2026-01-19 08:10:53'),
(16, 23, 'uploads/products/6a4349723cad7_mechanical.webp', 1, 0, '2026-06-30 04:43:30');

-- --------------------------------------------------------

--
-- Table structure for table `site_settings`
--

CREATE TABLE `site_settings` (
  `id` int(11) NOT NULL,
  `site_title` varchar(255) NOT NULL,
  `subtitle` varchar(255) DEFAULT NULL,
  `admin_email` varchar(255) NOT NULL,
  `timezone` varchar(100) DEFAULT 'UTC',
  `maintenance_mode` tinyint(1) DEFAULT 0,
  `user_registration` tinyint(1) DEFAULT 1,
  `email_notifications` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `site_description` text DEFAULT NULL,
  `subdescription` text DEFAULT NULL,
  `address` varchar(255) DEFAULT 'Cadawinonan, Dumaguete City, Negros Oriental',
  `phone` varchar(50) DEFAULT '09056152262',
  `hero_image` varchar(255) DEFAULT NULL,
  `google_analytics` tinyint(1) DEFAULT 1,
  `social_sharing` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `stock_logs`
--

CREATE TABLE `stock_logs` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `quantity_change` int(11) NOT NULL COMMENT 'Negative for deductions, positive for additions',
  `new_stock` int(11) NOT NULL,
  `reason` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `stock_logs`
--

INSERT INTO `stock_logs` (`id`, `product_id`, `order_id`, `quantity_change`, `new_stock`, `reason`, `created_at`) VALUES
(1, 15, 5, 1, 11, 'Order placed', '2026-01-23 10:14:09'),
(2, 20, 6, 1, 5, 'Order placed', '2026-06-29 14:14:56'),
(3, 21, 7, 1, 15, 'Order placed', '2026-06-29 14:33:34'),
(4, 19, 8, 1, 8, 'Order placed', '2026-06-29 15:11:59'),
(5, 20, 9, 1, 4, 'Order placed', '2026-06-29 15:16:10'),
(6, 21, 10, 1, 14, 'Order placed', '2026-06-29 15:25:03'),
(7, 11, 11, 1, 19, 'Order placed', '2026-06-29 15:31:13'),
(8, 15, 12, 1, 10, 'Order placed', '2026-06-30 06:21:18'),
(9, 15, 13, 1, 9, 'Order placed', '2026-06-30 06:23:26'),
(10, 19, 14, 1, 7, 'Order placed', '2026-06-30 06:24:10');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `name` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `type` enum('admin','user') NOT NULL DEFAULT 'user'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `email`, `password`, `name`, `created_at`, `type`) VALUES
(1, 'juan@gmail.com', '$2y$10$.5X6c67i2hdlSNSQ.FxSQOnR5TZkgGXbXQTIpwLPOBrXhY7slmMpC', 'juan2', '2026-01-18 12:44:04', 'admin'),
(2, 'admin@gmail.com', '$2y$10$AZpAUNavhM2AwOyuSs523u6P2nCwTcXGL7W3fYhjq3/5BiumG3GGq', 'Administrator', '2026-06-30 06:31:29', 'admin');

-- --------------------------------------------------------

--
-- Table structure for table `visitors`
--

CREATE TABLE `visitors` (
  `id` int(11) NOT NULL,
  `ip_address` varchar(50) NOT NULL,
  `visit_time` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `visitors`
--

INSERT INTO `visitors` (`id`, `ip_address`, `visit_time`) VALUES
(1, '::1', '2026-01-18 12:57:54'),
(2, '::1', '2026-01-19 03:42:28'),
(3, '::1', '2026-01-19 16:03:15'),
(4, '::1', '2026-01-21 02:15:17'),
(5, '::1', '2026-01-22 03:03:12'),
(6, '127.0.0.1', '2026-05-17 03:13:42'),
(7, '127.0.0.1', '2026-05-18 02:43:58'),
(8, '::1', '2026-06-09 15:13:16'),
(9, '::1', '2026-06-29 13:02:52'),
(10, '::1', '2026-06-29 16:05:59');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `conversation`
--
ALTER TABLE `conversation`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `new_users`
--
ALTER TABLE `new_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_number` (`order_number`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `order_tracking`
--
ALTER TABLE `order_tracking`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `product_categories`
--
ALTER TABLE `product_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `product_features`
--
ALTER TABLE `product_features`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `product_images`
--
ALTER TABLE `product_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `site_settings`
--
ALTER TABLE `site_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `stock_logs`
--
ALTER TABLE `stock_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `visitors`
--
ALTER TABLE `visitors`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `conversation`
--
ALTER TABLE `conversation`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `new_users`
--
ALTER TABLE `new_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `order_tracking`
--
ALTER TABLE `order_tracking`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `product_categories`
--
ALTER TABLE `product_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `product_features`
--
ALTER TABLE `product_features`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=234;

--
-- AUTO_INCREMENT for table `product_images`
--
ALTER TABLE `product_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `site_settings`
--
ALTER TABLE `site_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `stock_logs`
--
ALTER TABLE `stock_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `visitors`
--
ALTER TABLE `visitors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `order_tracking`
--
ALTER TABLE `order_tracking`
  ADD CONSTRAINT `order_tracking_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_features`
--
ALTER TABLE `product_features`
  ADD CONSTRAINT `product_features_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_images`
--
ALTER TABLE `product_images`
  ADD CONSTRAINT `product_images_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `stock_logs`
--
ALTER TABLE `stock_logs`
  ADD CONSTRAINT `stock_logs_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  ADD CONSTRAINT `stock_logs_ibfk_2` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
