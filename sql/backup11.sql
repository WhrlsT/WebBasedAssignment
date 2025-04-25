-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 24, 2025 at 07:22 PM
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
-- Database: `sigmamart`
--
CREATE DATABASE IF NOT EXISTS `sigmamart` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `sigmamart`;

-- --------------------------------------------------------

--
-- Table structure for table `brand`
--

CREATE TABLE `brand` (
  `BrandID` int(4) NOT NULL,
  `BrandName` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `brand`
--

INSERT INTO `brand` (`BrandID`, `BrandName`) VALUES
(1001, 'Sanrio'),
(1002, 'League Of Legends'),
(1003, 'Labubu'),
(1004, 'JellyCat'),
(1005, 'Disney'),
(1006, 'Pokemon'),
(1007, 'YuGiOh'),
(1008, 'One Piece'),
(1009, 'HoloLive');

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `CartID` int(11) NOT NULL,
  `UserID` int(11) NOT NULL,
  `ProductID` int(11) NOT NULL,
  `Quantity` int(11) NOT NULL DEFAULT 1,
  `DateAdded` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `category`
--

CREATE TABLE `category` (
  `CategoryID` int(4) NOT NULL,
  `CategoryName` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `category`
--

INSERT INTO `category` (`CategoryID`, `CategoryName`) VALUES
(1001, 'Blind Box'),
(1002, 'Plush Toys'),
(1003, 'Trading Cards');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `order_reference` varchar(50) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `shipping_cost` decimal(10,2) NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'Pending',
  `payment_id` varchar(100) DEFAULT NULL,
  `payment_date` datetime DEFAULT NULL,
  `payment_session_id` varchar(255) DEFAULT NULL,
  `order_date` datetime NOT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `tracking_number` varchar(100) DEFAULT NULL,
  `admin_notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `order_reference`, `total_amount`, `shipping_cost`, `status`, `payment_id`, `payment_date`, `payment_session_id`, `order_date`, `payment_method`, `tracking_number`, `admin_notes`) VALUES
(27, 6, 'ORD-1E59B405', 99999999.99, 10.00, 'Paid', NULL, NULL, NULL, '2025-04-01 00:38:27', 'paypal', '', ''),
(28, 6, 'ORD-F6E03860', 559.00, 10.00, 'Cancelled', 'pi_3R8lOlPZSuoPG7KD3U8JQAXX', '2025-04-01 00:41:28', NULL, '2025-04-01 00:40:45', 'stripe', NULL, NULL),
(29, 6, 'ORD-0D0F7256', 81.00, 10.00, 'Cancelled', 'PP_1743439352_29', '2025-04-01 00:42:32', NULL, '2025-04-01 00:42:18', 'paypal', NULL, NULL),
(30, 7, 'ORD-7D9B582C', 223.00, 10.00, 'Pending', NULL, NULL, NULL, '2025-04-24 12:37:05', 'stripe', NULL, NULL),
(31, 7, 'ORD-EF153FCD', 129.00, 10.00, 'Cancelled', NULL, NULL, NULL, '2025-04-24 12:39:08', 'paypal', '', ''),
(32, 7, 'ORD-2B30F26E', 99999999.99, 10.00, 'Cancelled', NULL, NULL, NULL, '2025-04-24 12:40:19', 'stripe', '', ''),
(33, 7, 'ORD-B9AC2780', 81.00, 10.00, 'Received', 'pi_3RHHcvRtqjLfe2On168fI6RW', '2025-04-24 12:44:39', NULL, '2025-04-24 12:43:08', 'stripe', 'ABCD123123123123', 'abc'),
(34, 7, 'ORD-1FC64261', 81.00, 10.00, 'Paid', 'pi_3RHOguRtqjLfe2On17YpwXye', '2025-04-24 20:16:08', NULL, '2025-04-24 20:15:42', 'stripe', NULL, NULL),
(35, 6, 'ORD-1E80A754', 795.00, 10.00, 'Refunded', 'pi_3RHQbwRtqjLfe2On0giZiADO', '2025-04-24 22:19:03', NULL, '2025-04-24 22:18:43', 'stripe', '', ''),
(36, 6, 'ORD-61D8F943', 200.00, 10.00, 'Paid', 'pi_3RHS2KRtqjLfe2On0dTA4SaP', '2025-04-24 23:50:12', NULL, '2025-04-24 23:50:03', 'stripe', NULL, NULL),
(37, 6, 'ORD-8554172F', 81.00, 10.00, 'Paid', 'pi_3RHS7gRtqjLfe2On112rVzhA', '2025-04-24 23:55:45', NULL, '2025-04-24 23:55:35', 'stripe', NULL, NULL),
(38, 6, 'ORD-59CF2B21', 55.00, 10.00, 'Paid', 'pi_3RHSBZRtqjLfe2On0eowTm2r', '2025-04-24 23:59:46', NULL, '2025-04-24 23:59:36', 'stripe', NULL, NULL),
(39, 6, 'ORD-998C281D', 129.00, 10.00, 'Paid', 'PP_1745510528_39', '2025-04-25 00:02:08', NULL, '2025-04-25 00:01:35', 'paypal', NULL, NULL),
(40, 6, 'ORD-AC95FD91', 129.00, 10.00, 'Paid', 'PP_1745510637_40', '2025-04-25 00:03:57', NULL, '2025-04-25 00:03:41', 'paypal', NULL, NULL),
(41, 6, 'ORD-988B7127', 55.00, 10.00, 'Paid', 'pi_3RHSNMRtqjLfe2On1zjPBm7T', '2025-04-25 00:11:56', NULL, '2025-04-25 00:11:48', 'stripe', NULL, NULL),
(42, 6, 'ORD-B24A350E', 140.00, 10.00, 'Paid', 'pi_3RHSPhRtqjLfe2On0Vum7pcu', '2025-04-25 00:14:22', NULL, '2025-04-25 00:14:12', 'stripe', NULL, NULL),
(43, 6, 'ORD-CABAC2F6', 129.00, 10.00, 'Paid', 'pi_3RHSRCRtqjLfe2On1VrWI1wp', '2025-04-25 00:15:54', NULL, '2025-04-25 00:15:45', 'stripe', NULL, NULL),
(44, 6, 'ORD-8EE03A57', 75.00, 10.00, 'Paid', 'pi_3RHSVxRtqjLfe2On1XrAJi73', '2025-04-25 00:20:50', NULL, '2025-04-25 00:20:41', 'stripe', NULL, NULL),
(45, 6, 'ORD-89B1F65F', 81.00, 10.00, 'Paid', 'pi_3RHSZxRtqjLfe2On0nfOxDfl', '2025-04-25 00:24:58', NULL, '2025-04-25 00:24:48', 'stripe', NULL, NULL),
(46, 6, 'ORD-B8D59C9D', 129.00, 10.00, 'Paid', 'pi_3RHSbvRtqjLfe2On0nkxWStZ', '2025-04-25 00:27:04', NULL, '2025-04-25 00:26:50', 'stripe', NULL, NULL),
(47, 6, 'ORD-531B260A', 81.00, 10.00, 'Paid', 'pi_3RHSeCRtqjLfe2On1fVH6HW7', '2025-04-25 00:29:21', NULL, '2025-04-25 00:29:11', 'stripe', NULL, NULL),
(48, 6, 'ORD-5FA592F0', 75.00, 10.00, 'Paid', 'pi_3RHSfbRtqjLfe2On1NhPEuYi', '2025-04-25 00:30:48', NULL, '2025-04-25 00:30:38', 'stripe', NULL, NULL),
(49, 6, 'ORD-69C2BF79', 75.00, 10.00, 'Paid', 'pi_3RHSpsRtqjLfe2On1fd4Kiwr', '2025-04-25 00:41:32', NULL, '2025-04-25 00:41:15', 'stripe', NULL, NULL),
(50, 6, 'ORD-8238108B', 81.00, 10.00, 'Paid', 'pi_3RHStlRtqjLfe2On1HaBVorb', '2025-04-25 00:45:28', NULL, '2025-04-25 00:45:17', 'stripe', NULL, NULL),
(51, 6, 'ORD-E443CD5A', 75.00, 10.00, 'Paid', 'pi_3RHSxBRtqjLfe2On1pTjZBgt', '2025-04-25 00:48:58', NULL, '2025-04-25 00:48:48', 'stripe', NULL, NULL),
(52, 6, 'ORD-E36828FF', 55.00, 10.00, 'Pending', NULL, NULL, NULL, '2025-04-25 00:53:29', 'stripe', NULL, NULL),
(53, 6, 'ORD-D01444F7', 55.00, 10.00, 'Paid', 'PP_1745513699_53', '2025-04-25 00:54:59', NULL, '2025-04-25 00:54:23', 'paypal', NULL, NULL),
(54, 6, 'ORD-31685AB4', 55.00, 10.00, 'Paid', 'pi_3RHT4eRtqjLfe2On11WZX4Pg', '2025-04-25 00:56:41', NULL, '2025-04-25 00:56:31', 'stripe', NULL, NULL),
(55, 6, 'ORD-966B4FD5', 75.00, 10.00, 'Paid', 'pi_3RHT5FRtqjLfe2On0YKxL4fz', '2025-04-25 00:57:18', NULL, '2025-04-25 00:57:08', 'stripe', NULL, NULL),
(56, 6, 'ORD-9F88C2A7', 75.00, 10.00, 'Paid', 'pi_3RHT6kRtqjLfe2On0SOA2sOG', '2025-04-25 00:58:51', NULL, '2025-04-25 00:58:41', 'stripe', NULL, NULL),
(57, 6, 'ORD-481A1EC3', 75.00, 10.00, 'Pending', NULL, NULL, NULL, '2025-04-25 00:59:47', 'paypal', NULL, NULL),
(58, 6, 'ORD-53EB5809', 75.00, 10.00, 'Paid', 'PP_1745514022_58', '2025-04-25 01:00:22', NULL, '2025-04-25 00:59:58', 'paypal', NULL, NULL),
(59, 6, 'ORD-9B4264BE', 55.00, 10.00, 'Paid', 'pi_3RHT9kRtqjLfe2On1q7QM2QZ', '2025-04-25 01:02:06', NULL, '2025-04-25 01:01:47', 'stripe', NULL, NULL),
(60, 6, 'ORD-87EEB2A3', 55.00, 10.00, 'Paid', 'pi_3RHTBeRtqjLfe2On02YDugUJ', '2025-04-25 01:03:55', NULL, '2025-04-25 01:03:46', 'stripe', NULL, NULL),
(61, 6, 'ORD-8339442F', 55.00, 10.00, 'Paid', 'pi_3RHTCiRtqjLfe2On0biwNJwo', '2025-04-25 01:05:01', NULL, '2025-04-25 01:04:51', 'stripe', NULL, NULL),
(62, 6, 'ORD-D1F73E70', 55.00, 10.00, 'Paid', 'PP_1745514359_62', '2025-04-25 01:05:59', NULL, '2025-04-25 01:05:37', 'paypal', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `price`) VALUES
(37, 27, 31, 1, 65.00),
(38, 27, 40, 1, 119.00),
(39, 27, 36, 1, 145.00),
(40, 27, 39, 1, 99999999.99),
(41, 28, 28, 1, 50.00),
(42, 28, 33, 1, 180.00),
(43, 28, 35, 1, 200.00),
(44, 28, 40, 1, 119.00),
(45, 29, 41, 1, 71.00),
(46, 30, 41, 3, 71.00),
(47, 31, 40, 1, 119.00),
(48, 32, 39, 1, 99999999.99),
(49, 33, 41, 1, 71.00),
(50, 34, 41, 1, 71.00),
(51, 35, 41, 1, 71.00),
(52, 35, 40, 6, 119.00),
(53, 36, 40, 1, 119.00),
(54, 36, 41, 1, 71.00),
(55, 37, 41, 1, 71.00),
(56, 38, 32, 1, 45.00),
(57, 39, 40, 1, 119.00),
(58, 40, 40, 1, 119.00),
(59, 41, 32, 1, 45.00),
(60, 42, 34, 1, 130.00),
(61, 43, 40, 1, 119.00),
(62, 44, 31, 1, 65.00),
(63, 45, 41, 1, 71.00),
(64, 46, 40, 1, 119.00),
(65, 47, 41, 1, 71.00),
(66, 48, 31, 1, 65.00),
(67, 49, 31, 1, 65.00),
(68, 50, 41, 1, 71.00),
(69, 51, 31, 1, 65.00),
(70, 52, 32, 1, 45.00),
(71, 53, 32, 1, 45.00),
(72, 54, 32, 1, 45.00),
(73, 55, 31, 1, 65.00),
(74, 56, 31, 1, 65.00),
(75, 57, 31, 1, 65.00),
(76, 58, 31, 1, 65.00),
(77, 59, 32, 1, 45.00),
(78, 60, 32, 1, 45.00),
(79, 61, 32, 1, 45.00),
(80, 62, 32, 1, 45.00);

-- --------------------------------------------------------

--
-- Table structure for table `payment_logs`
--

CREATE TABLE `payment_logs` (
  `id` int(11) NOT NULL,
  `txn_id` varchar(100) DEFAULT NULL,
  `order_reference` varchar(50) DEFAULT NULL,
  `payment_amount` decimal(10,2) DEFAULT NULL,
  `payment_currency` varchar(10) DEFAULT NULL,
  `payer_email` varchar(100) DEFAULT NULL,
  `payment_status` varchar(20) NOT NULL,
  `ipn_data` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payment_logs`
--

INSERT INTO `payment_logs` (`id`, `txn_id`, `order_reference`, `payment_amount`, `payment_currency`, `payer_email`, `payment_status`, `ipn_data`, `created_at`) VALUES
(1, 'pi_3R8GuvPZSuoPG7KD3muB7TSC', 'ORD-2070AD-20250330', 13.00, 'myr', 'whrltan05@gmail.com', 'Completed', '{\"id\":\"cs_test_b1gopfsYsqHSFlZdOBjDBp1CZIXI8ERCBXMWhQJFGPd9RnOh993km76nLE\",\"object\":\"checkout.session\",\"adaptive_pricing\":{\"enabled\":true},\"after_expiration\":null,\"allow_promotion_codes\":null,\"amount_subtotal\":1300,\"amount_total\":1300,\"automatic_tax\":{\"enabled\":false,\"liability\":null,\"status\":null},\"billing_address_collection\":null,\"cancel_url\":\"http:\\/\\/localhost:8000\\/checkout.php\",\"client_reference_id\":null,\"client_secret\":null,\"collected_information\":{\"shipping_details\":null},\"consent\":null,\"consent_collection\":null,\"created\":1743322099,\"currency\":\"myr\",\"currency_conversion\":null,\"custom_fields\":[],\"custom_text\":{\"after_submit\":null,\"shipping_address\":null,\"submit\":null,\"terms_of_service_acceptance\":null},\"customer\":null,\"customer_creation\":\"if_required\",\"customer_details\":{\"address\":{\"city\":null,\"country\":\"MY\",\"line1\":null,\"line2\":null,\"postal_code\":null,\"state\":null},\"email\":\"whrltan05@gmail.com\",\"name\":\"TAN WHEY LONG\",\"phone\":null,\"tax_exempt\":\"none\",\"tax_ids\":[]},\"customer_email\":\"whrltan05@gmail.com\",\"discounts\":[],\"expires_at\":1743408498,\"invoice\":null,\"invoice_creation\":{\"enabled\":false,\"invoice_data\":{\"account_tax_ids\":null,\"custom_fields\":null,\"description\":null,\"footer\":null,\"issuer\":null,\"metadata\":[],\"rendering_options\":null}},\"livemode\":false,\"locale\":null,\"metadata\":{\"order_id\":\"10\",\"order_reference\":\"ORD-2070AD-20250330\"},\"mode\":\"payment\",\"payment_intent\":\"pi_3R8GuvPZSuoPG7KD3muB7TSC\",\"payment_link\":null,\"payment_method_collection\":\"if_required\",\"payment_method_configuration_details\":null,\"payment_method_options\":{\"card\":{\"request_three_d_secure\":\"automatic\"}},\"payment_method_types\":[\"card\"],\"payment_status\":\"paid\",\"permissions\":null,\"phone_number_collection\":{\"enabled\":false},\"recovered_from\":null,\"saved_payment_method_options\":null,\"setup_intent\":null,\"shipping_address_collection\":null,\"shipping_cost\":null,\"shipping_details\":null,\"shipping_options\":[],\"status\":\"complete\",\"submit_type\":null,\"subscription\":null,\"success_url\":\"http:\\/\\/localhost:8000\\/stripe_success.php?session_id={CHECKOUT_SESSION_ID}&order_id=10\",\"total_details\":{\"amount_discount\":0,\"amount_shipping\":0,\"amount_tax\":0},\"ui_mode\":\"hosted\",\"url\":null}', '2025-03-30 08:08:32'),
(2, 'PP_1743393583_22', 'ORD-4743F5-20250331', 131.00, 'MYR', 'whrltan05@gmail.com', 'Completed', '{\"order_id\":22,\"payment_method\":\"paypal\",\"success_page\":true}', '2025-03-31 03:59:43'),
(3, 'PP_1743396296_24', 'ORD-804186-20250331', 131.00, 'MYR', 'whrltan05@gmail.com', 'Completed', '{\"order_id\":24,\"payment_method\":\"paypal\",\"success_page\":true}', '2025-03-31 04:44:56'),
(4, 'pi_3R8aFmPZSuoPG7KD1XEczROg', 'ORD-817C07-20250331', 131.00, 'myr', 'whrltan05@gmail.com', 'succeeded', '{\"payment_intent\":\"pi_3R8aFmPZSuoPG7KD1XEczROg\",\"status\":\"succeeded\"}', '2025-03-31 04:47:27'),
(5, 'pi_3R8ehgPZSuoPG7KD1WR7fsBT', 'ORD-562E6B6E', 121222.00, 'myr', 'whrltan05@gmail.com', 'succeeded', '{\"payment_intent\":\"pi_3R8ehgPZSuoPG7KD1WR7fsBT\",\"status\":\"succeeded\"}', '2025-03-31 09:32:34'),
(6, 'PP_1743413688_26', 'ORD-2EA20EF9', 1354587.00, 'MYR', 'whrltan05@gmail.com', 'Completed', '{\"order_id\":26,\"payment_method\":\"paypal\",\"success_page\":true}', '2025-03-31 09:34:48'),
(7, 'pi_3R8lOlPZSuoPG7KD3U8JQAXX', 'ORD-F6E03860', 559.00, 'myr', 'whrltan05@gmail.com', 'succeeded', '{\"payment_intent\":\"pi_3R8lOlPZSuoPG7KD3U8JQAXX\",\"status\":\"succeeded\"}', '2025-03-31 16:41:28'),
(8, 'PP_1743439352_29', 'ORD-0D0F7256', 81.00, 'MYR', 'whrltan05@gmail.com', 'Completed', '{\"order_id\":29,\"payment_method\":\"paypal\",\"success_page\":true}', '2025-03-31 16:42:32'),
(9, 'pi_3RHHcvRtqjLfe2On168fI6RW', 'ORD-B9AC2780', 81.00, 'myr', 'lowwh-wp23@student.tarc.edu.my', 'succeeded', '{\"payment_intent\":\"pi_3RHHcvRtqjLfe2On168fI6RW\",\"status\":\"succeeded\"}', '2025-04-24 04:44:39'),
(10, 'pi_3RHOguRtqjLfe2On17YpwXye', 'ORD-1FC64261', 81.00, 'myr', 'lowwh-wp23@student.tarc.edu.my', 'succeeded', '{\"payment_intent\":\"pi_3RHOguRtqjLfe2On17YpwXye\",\"status\":\"succeeded\"}', '2025-04-24 12:16:08'),
(11, 'pi_3RHQbwRtqjLfe2On0giZiADO', 'ORD-1E80A754', 795.00, 'myr', 'whrltan05@gmail.com', 'succeeded', '{\"payment_intent\":\"pi_3RHQbwRtqjLfe2On0giZiADO\",\"status\":\"succeeded\"}', '2025-04-24 14:19:03'),
(12, 'pi_3RHS2KRtqjLfe2On0dTA4SaP', 'ORD-61D8F943', 200.00, 'myr', 'whrltan05@gmail.com', 'succeeded', '{\"payment_intent\":\"pi_3RHS2KRtqjLfe2On0dTA4SaP\",\"status\":\"succeeded\"}', '2025-04-24 15:50:12'),
(13, 'pi_3RHS7gRtqjLfe2On112rVzhA', 'ORD-8554172F', 81.00, 'myr', 'whrltan05@gmail.com', 'succeeded', '{\"payment_intent\":\"pi_3RHS7gRtqjLfe2On112rVzhA\",\"status\":\"succeeded\"}', '2025-04-24 15:55:45'),
(14, 'pi_3RHSBZRtqjLfe2On0eowTm2r', 'ORD-59CF2B21', 55.00, 'myr', 'whrltan05@gmail.com', 'succeeded', '{\"payment_intent\":\"pi_3RHSBZRtqjLfe2On0eowTm2r\",\"status\":\"succeeded\"}', '2025-04-24 15:59:46'),
(15, 'pi_3RHSNMRtqjLfe2On1zjPBm7T', 'ORD-988B7127', 55.00, 'myr', 'whrltan05@gmail.com', 'succeeded', '{\"payment_intent\":\"pi_3RHSNMRtqjLfe2On1zjPBm7T\",\"status\":\"succeeded\"}', '2025-04-24 16:11:56'),
(16, 'pi_3RHSPhRtqjLfe2On0Vum7pcu', 'ORD-B24A350E', 140.00, 'myr', 'whrltan05@gmail.com', 'succeeded', '{\"payment_intent\":\"pi_3RHSPhRtqjLfe2On0Vum7pcu\",\"status\":\"succeeded\"}', '2025-04-24 16:14:22'),
(17, 'pi_3RHSRCRtqjLfe2On1VrWI1wp', 'ORD-CABAC2F6', 129.00, 'myr', 'whrltan05@gmail.com', 'succeeded', '{\"payment_intent\":\"pi_3RHSRCRtqjLfe2On1VrWI1wp\",\"status\":\"succeeded\"}', '2025-04-24 16:15:54'),
(18, 'pi_3RHSVxRtqjLfe2On1XrAJi73', 'ORD-8EE03A57', 75.00, 'myr', 'whrltan05@gmail.com', 'succeeded', '{\"payment_intent\":\"pi_3RHSVxRtqjLfe2On1XrAJi73\",\"status\":\"succeeded\"}', '2025-04-24 16:20:50'),
(19, 'pi_3RHSZxRtqjLfe2On0nfOxDfl', 'ORD-89B1F65F', 81.00, 'myr', 'whrltan05@gmail.com', 'succeeded', '{\"payment_intent\":\"pi_3RHSZxRtqjLfe2On0nfOxDfl\",\"status\":\"succeeded\"}', '2025-04-24 16:24:58'),
(20, 'pi_3RHSbvRtqjLfe2On0nkxWStZ', 'ORD-B8D59C9D', 129.00, 'myr', 'whrltan05@gmail.com', 'succeeded', '{\"payment_intent\":\"pi_3RHSbvRtqjLfe2On0nkxWStZ\",\"status\":\"succeeded\"}', '2025-04-24 16:27:04'),
(21, 'pi_3RHSeCRtqjLfe2On1fVH6HW7', 'ORD-531B260A', 81.00, 'myr', 'whrltan05@gmail.com', 'succeeded', '{\"payment_intent\":\"pi_3RHSeCRtqjLfe2On1fVH6HW7\",\"status\":\"succeeded\"}', '2025-04-24 16:29:21'),
(22, 'pi_3RHSfbRtqjLfe2On1NhPEuYi', 'ORD-5FA592F0', 75.00, 'myr', 'whrltan05@gmail.com', 'succeeded', '{\"payment_intent\":\"pi_3RHSfbRtqjLfe2On1NhPEuYi\",\"status\":\"succeeded\"}', '2025-04-24 16:30:48'),
(23, 'pi_3RHSpsRtqjLfe2On1fd4Kiwr', 'ORD-69C2BF79', 75.00, 'myr', 'whrltan05@gmail.com', 'succeeded', '{\"payment_intent\":\"pi_3RHSpsRtqjLfe2On1fd4Kiwr\",\"status\":\"succeeded\"}', '2025-04-24 16:41:32'),
(24, 'pi_3RHStlRtqjLfe2On1HaBVorb', 'ORD-8238108B', 81.00, 'myr', 'whrltan05@gmail.com', 'succeeded', '{\"payment_intent\":\"pi_3RHStlRtqjLfe2On1HaBVorb\",\"status\":\"succeeded\"}', '2025-04-24 16:45:28'),
(25, 'pi_3RHSxBRtqjLfe2On1pTjZBgt', 'ORD-E443CD5A', 75.00, 'myr', 'whrltan05@gmail.com', 'succeeded', '{\"payment_intent\":\"pi_3RHSxBRtqjLfe2On1pTjZBgt\",\"status\":\"succeeded\"}', '2025-04-24 16:48:58'),
(26, 'pi_3RHT4eRtqjLfe2On11WZX4Pg', 'ORD-31685AB4', 55.00, 'myr', 'whrltan05@gmail.com', 'succeeded', '{\"payment_intent\":\"pi_3RHT4eRtqjLfe2On11WZX4Pg\",\"status\":\"succeeded\"}', '2025-04-24 16:56:41'),
(27, 'pi_3RHT5FRtqjLfe2On0YKxL4fz', 'ORD-966B4FD5', 75.00, 'myr', 'whrltan05@gmail.com', 'succeeded', '{\"payment_intent\":\"pi_3RHT5FRtqjLfe2On0YKxL4fz\",\"status\":\"succeeded\"}', '2025-04-24 16:57:18'),
(28, 'pi_3RHT6kRtqjLfe2On0SOA2sOG', 'ORD-9F88C2A7', 75.00, 'myr', 'whrltan05@gmail.com', 'succeeded', '{\"payment_intent\":\"pi_3RHT6kRtqjLfe2On0SOA2sOG\",\"status\":\"succeeded\"}', '2025-04-24 16:58:51'),
(29, 'pi_3RHT9kRtqjLfe2On1q7QM2QZ', 'ORD-9B4264BE', 55.00, 'myr', 'whrltan05@gmail.com', 'succeeded', '{\"payment_intent\":\"pi_3RHT9kRtqjLfe2On1q7QM2QZ\",\"status\":\"succeeded\"}', '2025-04-24 17:02:06'),
(30, 'pi_3RHTBeRtqjLfe2On02YDugUJ', 'ORD-87EEB2A3', 55.00, 'myr', 'whrltan05@gmail.com', 'succeeded', '{\"payment_intent\":\"pi_3RHTBeRtqjLfe2On02YDugUJ\",\"status\":\"succeeded\"}', '2025-04-24 17:03:55'),
(31, 'pi_3RHTCiRtqjLfe2On0biwNJwo', 'ORD-8339442F', 55.00, 'myr', 'whrltan05@gmail.com', 'succeeded', '{\"payment_intent\":\"pi_3RHTCiRtqjLfe2On0biwNJwo\",\"status\":\"succeeded\"}', '2025-04-24 17:05:01');

-- --------------------------------------------------------

--
-- Table structure for table `productpictures`
--

CREATE TABLE `productpictures` (
  `pictureID` int(5) NOT NULL,
  `productID` int(11) NOT NULL,
  `isCover` tinyint(1) NOT NULL,
  `picturePath` varchar(255) NOT NULL,
  `DisplayOrder` int(1) NOT NULL DEFAULT 1 COMMENT 'Values 1-5 for display order'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `productpictures`
--

INSERT INTO `productpictures` (`pictureID`, `productID`, `isCover`, `picturePath`, `DisplayOrder`) VALUES
(33, 28, 1, 'product_images/1743422564_1_20250326_095626_992352_____08_____1200x1548 (2).png', 1),
(34, 28, 0, 'product_images/1743422564_2_20250326_095626_992352_____08_____1200x1548 (4).png', 2),
(35, 28, 0, 'product_images/1743422564_3_20250326_095626_992352_____08_____1200x1548 (3).png', 3),
(36, 28, 0, 'product_images/1743422564_4_20250326_095626_992352_____08_____1200x1548 (1).png', 4),
(37, 29, 1, 'product_images/1743422756_1_20250318_175521_991965____1_____1200x1200.jpg', 1),
(38, 29, 0, 'product_images/1743422756_2_20250319_105136_631588_____01_____1200x708.webp', 2),
(39, 29, 0, 'product_images/1743422756_3_20250319_105136_023982_____03_____1200x1272.webp', 3),
(40, 29, 0, 'product_images/1743422756_4_20250319_105136_369515_____07_____1200x1096.webp', 4),
(41, 29, 0, 'product_images/1743422756_5_20250319_105135_409514_____09_____1200x1244.webp', 5),
(42, 30, 1, 'product_images/1743422890_1_20250226_144035_180983____1_____1200x1200.jpg', 1),
(43, 30, 0, 'product_images/1743422890_2_20250226_144046_266033_____02_____1200x950.webp', 2),
(44, 30, 0, 'product_images/1743422890_3_20250226_144046_724161_____04_____1200x1091.webp', 3),
(45, 30, 0, 'product_images/1743422890_4_20250226_144046_366028_____09_____1200x1227.webp', 4),
(46, 31, 1, 'product_images/1743423245_1_1681186064_thumbnail_4580683615280-7.jpg', 1),
(47, 31, 0, 'product_images/1743423245_2_GOODS-04343375.jpg', 2),
(48, 31, 0, 'product_images/1743423245_3_images.jfif', 3),
(49, 31, 0, 'product_images/1743423245_4_my-11134207-7r98y-lm9t9gx0q5eb89.jfif', 4),
(50, 32, 1, 'product_images/1743423357_1_9d8401f5507c4062a51d65cacd7ec5c2.jfif', 1),
(51, 32, 0, 'product_images/1743423357_2_c6cb00fdf0fa449394f69342bc19c241.jfif', 2),
(52, 32, 0, 'product_images/1743423357_3_b655692e515348348cf69dce160adac0.jfif', 3),
(53, 33, 1, 'product_images/1743423677_1_A3REDFH_4__51118.jpg', 1),
(54, 33, 0, 'product_images/1743423677_2_A3REDFH__73220.jpg', 2),
(55, 33, 0, 'product_images/1743423677_3_A3REDFH_2__16156.jpg', 3),
(56, 34, 1, 'product_images/1743423747_1_BAS4ELBC__39776.jpg', 1),
(57, 34, 0, 'product_images/1743423747_2_BAS4ELBC_4__15401.jpg', 2),
(58, 34, 0, 'product_images/1743423747_3_BAS4ELBC_2__39452.jpg', 3),
(59, 35, 1, 'product_images/1743423807_1_A2SNF_4__12470.jpg', 1),
(60, 35, 0, 'product_images/1743423807_2_A2SNF__03139.jpg', 2),
(61, 35, 0, 'product_images/1743423807_3_A2SNF_2__16312.jpg', 3),
(62, 36, 1, 'product_images/1743423936_1_415159439678APAC.webp', 1),
(63, 36, 0, 'product_images/1743423936_2_415159439678APAC-8.webp', 2),
(64, 36, 0, 'product_images/1743423936_3_415159439678APAC-7.webp', 3),
(65, 36, 0, 'product_images/1743423936_4_415159439678APAC-1.webp', 4),
(66, 37, 1, 'product_images/1743423986_1_415159690611-1.webp', 1),
(67, 37, 0, 'product_images/1743423986_2_415159690611-2.webp', 2),
(68, 37, 0, 'product_images/1743423986_3_415159690611.webp', 3),
(69, 38, 1, 'product_images/1743424044_1_415159811993.webp', 1),
(70, 38, 0, 'product_images/1743424044_2_415159811993-2.webp', 2),
(71, 38, 0, 'product_images/1743424044_3_415159811993-1.webp', 3),
(72, 39, 1, 'product_images/1743424157_1_415159810903.webp', 1),
(73, 39, 0, 'product_images/1743424157_2_415159810903-2.webp', 2),
(74, 39, 0, 'product_images/1743424157_3_415159810903-1.webp', 3),
(75, 40, 1, 'product_images/1743424699_1_1740100805102____surprise-shake____.webp', 1),
(76, 40, 0, 'product_images/1743424699_2_20241218_164256_265660_____01_____1200x1705.webp', 2),
(77, 40, 0, 'product_images/1743424699_3_20241218_164256_004783_____02_____1200x1309.webp', 3),
(78, 40, 0, 'product_images/1743424699_4_20241218_164256_183719_____07_____1200x1431.webp', 4),
(79, 41, 1, 'product_images/1743424803_1_1720611235209____形象7____.webp', 1),
(80, 41, 0, 'product_images/1743424803_2_20250306_155820_755344_____02_____1200x1223.webp', 2),
(81, 41, 0, 'product_images/1743424803_3_20250306_155820_102758_____03_____1200x1576.webp', 3),
(82, 41, 0, 'product_images/1743424803_4_20250306_155820_115999_____08_____1200x1433.webp', 4);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `ProductID` int(5) NOT NULL,
  `CategoryID` int(11) NOT NULL,
  `BrandID` int(11) NOT NULL,
  `ProductName` varchar(255) NOT NULL,
  `ProductPrice` int(11) NOT NULL,
  `ProductDescription` text DEFAULT NULL,
  `date_added` datetime DEFAULT current_timestamp(),
  `sales_count` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`ProductID`, `CategoryID`, `BrandID`, `ProductName`, `ProductPrice`, `ProductDescription`, `date_added`, `sales_count`) VALUES
(28, 1001, 1005, 'Disney Princess\'s Fairy Tales', 50, 'Brand: SigmaMart\r\n\r\nSize: \r\nStorybook shell height approximately 9.5cm;\r\nThe figure height is approximately 4.2-6.1cm\r\n\r\nMaterial: PVC/ABS/Hardware\r\n\r\n', '2025-03-31 20:02:44', 1),
(29, 1001, 1005, 'DIMOO WORLD × DISNEY Series Figures', 56, 'Brand: Sigma Mart\r\n\r\nSize: Height about 7-10cm\r\n\r\nMaterial: PVC/ABS/Nylon', '2025-03-31 20:05:56', 0),
(30, 1001, 1005, 'Mickey Family Cute Together Keychain Series Figures', 71, 'Brand: Sigma Mart\r\n\r\nSize: Height about 14-15cm\r\n\r\n(Lanyard not included)\r\n\r\nMaterial: PVC/ABS/Polyester', '2025-03-31 20:08:10', 0),
(31, 1001, 1009, 'Hololive Production Fuwacororin Box Vol.2', 65, 'Brand : Hololive\r\n\r\nSize approx. 100mm\r\n\r\n	Assorted according to the maker\'s rate from 7 types.\r\n\r\nLineup:\r\n-AZKi\r\n-Shirakami Fubuki\r\n-Murasaki Shion\r\n-Usada Pekora\r\n-Tsunomaki Watame\r\n-Momosuzu Nene\r\n-Takane Lui', '2025-03-31 20:14:05', 9),
(32, 1001, 1009, 'Hololive Deformation Collection Vol.1', 45, 'Brand: Hololive\r\n\r\nAge Recommend: 15+\r\n\r\nProduct size: Approx. 2cm-5cm\r\n\r\nMaterial: Plastic\r\n\r\nProduct Detail: 8 basic design', '2025-03-31 20:15:57', 9),
(33, 1002, 1004, 'Amuseables Beatie Heart', 180, 'Amuseables Beatie Heart is here to share love all year long. A bright red face with big stitched smile with a matching fluffy mane, Beatie has matching red fine cord legs, making this heart of hearts the perfect gift for someone special.\r\n\r\nDimensions: 24cm x 23cm x 7cm\r\nSitting Height: 20cm\r\nMain Materials: Polyester\r\nInner Filling: Polyester Fibres, PE Beans\r\nHard Eye\r\nSKU: A3REDFH', '2025-03-31 20:21:17', 1),
(34, 1002, 1004, 'Bashful Blush Bunny', 130, 'Bashful Blush Bunny Bag Charm makes every day rosy! Our iconic Bashful Bunny is soft and sweet in blush pink fur, with lop ears and a pale pink nose. Use the silver clasp to attach this bunny to any bag for the softest sidekick.\r\n\r\nDimensions: 18cm x 5cm x 4cm\r\nSitting Height: 13cm\r\nMain Materials: Polyester\r\nInner Filling: Polyester Fibres\r\nEmbroidered Eye\r\nSKU: BAS4ELBC', '2025-03-31 20:22:27', 1),
(35, 1002, 1004, 'Amuseables Sunflower', 200, 'Add a little sunshine to your day with Amuseables Sunflower. This trio of flowers have brown fur faces, bright yellow petals and deep green stalks. Sat in a beautiful brown linen pot with mocha soil, these bright flowers make every day a little sunnier.\r\n\r\nDimensions: 35cm x 11cm x 11cm\r\nSitting Height: 35cm\r\nMain Materials: Polyester\r\nInner Filling: Polyester Fibres, PE Beans\r\nEmbroidered Eye\r\nSKU: A2SNF', '2025-03-31 20:23:27', 1),
(36, 1002, 1005, 'Baymax Sakura Medium Plush, Big Hero 6', 145, 'Our super soft, velvety Baymax Sakura Plush comes from San Fransokyo exclusively via Disney Store Japan. Plump and reassuring, this huggable, all-too-adorable stuffed toy is decorated with cherry blossom appliqués and pastel accents.\r\n\r\nMagic in the details\r\n\r\nDetailed plush sculpting\r\nEmbroidered features\r\nVelvety velour covering\r\nSoft fill\r\nCherry blossom appliqués\r\nGlitter accents\r\nSoft ice cream cone with shimmering fabric scoops\r\nInspired by Disney\'s Big Hero 6 (2014)\r\nPart of the Sakura 2025 Collection\r\nCreated for Disney Store Japan\r\nThe bare necessities\r\n\r\nPolyester fiber / polyethylene foam\r\nApprox. 34.3cm H\r\nImported', '2025-03-31 20:25:36', 1),
(37, 1002, 1005, 'Rex Medium Plush, Toy Story Rex Game', 120, 'It’s game time with the Rex Medium Plush from the Rex Game collection! Join your favourite Toy Story characters for a fun-filled party full of laughter and excitement. With its soft plush texture and vibrant colours, this plush is guaranteed to brighten your day.\r\n\r\nMagic in the details\r\n\r\nDetailed plush sculpting\r\nEmbroidered features\r\nPart of the Rex Game Collection\r\nCreated for Disney Store\r\nThe bare necessities\r\n\r\nPolyester\r\nApprox. 28.5cm H x 27cm L x 35cm W\r\nImported', '2025-03-31 20:26:26', 0),
(38, 1002, 1005, 'Dale Sitting Plush, Chip \'n Dale', 120, 'Bring home the magic from Disney Store Japan with this Dale Sitting Plush. With an adorable head-tilt and sparkling eyes, Dale radiates charm in a gentle pastel hue. Soft, fluffy, and irresistibly cuddly—perfect for bringing comfort and joy!\r\n\r\nMagic in the details\r\n\r\nDetailed plush sculpting\r\nEmbroidered features\r\nPart of the Petanko Collection\r\nCreated for Disney Store Japan\r\nThe bare necessities\r\n\r\nPolyester\r\nApprox. 21cm H x 15cm W x 16cm D\r\nImported', '2025-03-31 20:27:24', 0),
(39, 1002, 1005, 'President Xinnie the Pooh', 2147483647, 'Bring home the magic from Disney Store Japan with this Xinnie the Pooh. With an adorable head-tilt and sparkling eyes, Pooh radiates charm in a gentle pastel hue. Soft, fluffy, and irresistibly cuddly—perfect for bringing comfort and joy!\r\n\r\nMagic in the details\r\n\r\nDetailed plush sculpting\r\nEmbroidered features\r\nInspired by Disney\'s The Many Adventures of Winnie the Pooh (1977)\r\nPart of the Petanko Collection\r\nCreated for Disney Store Japan\r\nThe bare necessities\r\n\r\nPolyester\r\nApprox. 33cm H x 22cm W x 22cm D\r\nImported', '2025-03-31 20:29:17', 2),
(40, 1001, 1003, 'Labubu The Monsters Coca-Cola Series Vinyl Face', 119, 'Brand: POP MART\r\nSize: Height about 15.5cm-17cm\r\nMaterial: Shell: Polyester/ABS/PVC; Stuffing: Polyester/Iron Wire', '2025-03-31 20:38:19', 14),
(41, 1001, 1003, 'The Monsters - Have a seat Vinyl Plush', 71, 'Brand: POP MART\r\nSize: \r\nHeight about 8*7*20cm(including hanging loop)\r\nHeight about 8*7*15cm(excluding hanging loop)\r\nMaterial: \r\nShell: 60%PVC, 40%Polyester\r\nStuffing: 70%Polyester, 20%ABS, 5%Iron Wire, 5%Nylon', '2025-03-31 20:40:03', 14);

-- --------------------------------------------------------

--
-- Table structure for table `product_stocks`
--

CREATE TABLE `product_stocks` (
  `StockID` int(11) NOT NULL,
  `ProductID` int(5) NOT NULL,
  `Quantity` int(11) NOT NULL DEFAULT 0,
  `LastUpdated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_stocks`
--

INSERT INTO `product_stocks` (`StockID`, `ProductID`, `Quantity`, `LastUpdated`) VALUES
(28, 28, 200, '2025-03-31 12:02:44'),
(29, 29, 250, '2025-03-31 12:05:56'),
(30, 30, 132, '2025-03-31 12:08:10'),
(31, 31, 9, '2025-03-31 12:14:05'),
(32, 32, 123, '2025-03-31 12:15:57'),
(33, 33, 255, '2025-03-31 12:21:17'),
(34, 34, 132, '2025-03-31 12:22:27'),
(35, 35, 433, '2025-03-31 12:23:27'),
(36, 36, 132, '2025-03-31 12:25:36'),
(37, 37, 123, '2025-03-31 12:26:26'),
(38, 38, 0, '2025-03-31 12:27:24'),
(39, 39, 1, '2025-03-31 12:29:17'),
(40, 40, 99, '2025-03-31 12:38:19'),
(41, 41, 2221, '2025-04-24 15:31:25');

-- --------------------------------------------------------

--
-- Table structure for table `refund_requests`
--

CREATE TABLE `refund_requests` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `reason` varchar(50) NOT NULL,
  `details` text DEFAULT NULL,
  `status` enum('pending','approved','rejected','processed') DEFAULT 'pending',
  `requested_at` datetime NOT NULL,
  `processed_at` datetime DEFAULT NULL,
  `admin_notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `refund_requests`
--

INSERT INTO `refund_requests` (`id`, `order_id`, `user_id`, `reason`, `details`, `status`, `requested_at`, `processed_at`, `admin_notes`) VALUES
(1, 33, 7, 'changed_mind', '', 'pending', '2025-04-24 20:07:11', NULL, NULL),
(2, 35, 6, 'changed_mind', 'abc', 'pending', '2025-04-24 23:03:46', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `shipping_addresses`
--

CREATE TABLE `shipping_addresses` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `address` varchar(255) NOT NULL,
  `city` varchar(100) NOT NULL,
  `state` varchar(100) NOT NULL,
  `zip_code` varchar(20) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `shipping_addresses`
--

INSERT INTO `shipping_addresses` (`id`, `user_id`, `order_id`, `first_name`, `last_name`, `email`, `phone`, `address`, `city`, `state`, `zip_code`, `created_at`) VALUES
(27, 6, 27, 'Kelvin', 'Singh', 'whrltan05@gmail.com', '0137383232', '30 Jalan Gita 1/6, Horizon Hills, 79100 Iskandar Puteri, Johor', 'Nusajaya', 'Johor', '79100', '2025-03-31 16:38:27'),
(28, 6, 28, 'Kelvin', 'Singh', 'whrltan05@gmail.com', '123123123213123', '12313213', '123123', '3123', '213213', '2025-03-31 16:40:45'),
(29, 6, 29, 'abc', 'def', 'whrltan05@gmail.com', '123', '123', '123', '123', '123', '2025-03-31 16:42:18'),
(30, 7, 30, 'low', 'hang', 'lowwh-wp23@student.tarc.edu.my', '0123456789', 'a-10-11', 'sripetaling', 'kl', '11111', '2025-04-24 04:37:05'),
(31, 7, 31, 'low', 'hang', 'lowwh-wp23@student.tarc.edu.my', '0123456789', 'a-10-11', 'sripetaling', 'kl', '11111', '2025-04-24 04:39:08'),
(32, 7, 32, 'low', 'hang', 'lowwh-wp23@student.tarc.edu.my', '0123456789', 'a-10-11', 'sripetaling', 'kl', '11111', '2025-04-24 04:40:19'),
(33, 7, 33, 'low', 'hang', 'lowwh-wp23@student.tarc.edu.my', '0123456789', 'a-10-11', 'sripetaling', 'kl', '11111', '2025-04-24 04:43:08'),
(34, 7, 34, 'low', 'hang', 'lowwh-wp23@student.tarc.edu.my', '0123456789', 'a-10-11', 'sripetaling', 'kl', '11111', '2025-04-24 12:15:42'),
(35, 6, 35, 'abc', 'def', 'whrltan05@gmail.com', '123', '123', '123', '123', '123', '2025-04-24 14:18:43'),
(36, 6, 36, 'abca', 'def', 'whrltan05@gmail.com', '123', '123', '123', '123', '123', '2025-04-24 15:50:03'),
(37, 6, 37, 'abca', 'def', 'rotanrontan@gmail.com', '123', '123', '123', '123', '123', '2025-04-24 15:55:35'),
(38, 6, 38, 'abca', 'def', 'whrltan05@gmail.com', '123', '123', '123', '123', '123', '2025-04-24 15:59:36'),
(39, 6, 39, 'abca', 'def', 'whrltan05@gmail.com', '123', '123', '123', '123', '123', '2025-04-24 16:01:35'),
(40, 6, 40, 'abca', 'def', 'whrltan05@gmail.com', '123', '123', '123', '123', '123', '2025-04-24 16:03:41'),
(41, 6, 41, 'abca', 'def', 'whrltan05@gmail.com', '123', '123', '123', '123', '123', '2025-04-24 16:11:48'),
(42, 6, 42, 'abca', 'def', 'whrltan05@gmail.com', '123', '123', '123', '123', '123', '2025-04-24 16:14:12'),
(43, 6, 43, 'abca', 'def', 'whrltan05@gmail.com', '123', '123', '123', '123', '123', '2025-04-24 16:15:45'),
(44, 6, 44, 'abca', 'def', 'whrltan05@gmail.com', '123', '123', '123', '123', '123', '2025-04-24 16:20:41'),
(45, 6, 45, 'abca', 'def', 'whrltan05@gmail.com', '123', '123', '123', '123', '123', '2025-04-24 16:24:48'),
(46, 6, 46, 'abca', 'def', 'whrltan05@gmail.com', '123', '123', '123', '123', '123', '2025-04-24 16:26:50'),
(47, 6, 47, 'abca', 'def', 'whrltan05@gmail.com', '123', '123', '123', '123', '123', '2025-04-24 16:29:11'),
(48, 6, 48, 'abca', 'def', 'whrltan05@gmail.com', '123', '123', '123', '123', '123', '2025-04-24 16:30:38'),
(49, 6, 49, 'abca', 'def', 'whrltan05@gmail.com', '123', '123', '123', '123', '123', '2025-04-24 16:41:15'),
(50, 6, 50, 'abca', 'def', 'whrltan05@gmail.com', '123', '123', '123', '123', '123', '2025-04-24 16:45:17'),
(51, 6, 51, 'abca', 'def', 'whrltan05@gmail.com', '123', '123', '123', '123', '123', '2025-04-24 16:48:48'),
(52, 6, 52, 'abca', 'def', 'whrltan05@gmail.com', '123', '123', '123', '123', '123', '2025-04-24 16:53:29'),
(53, 6, 53, 'abca', 'def', 'whrltan05@gmail.com', '123', '123', '123', '123', '123', '2025-04-24 16:54:23'),
(54, 6, 54, 'abca', 'def', 'whrltan05@gmail.com', '123', '123', '123', '123', '1213', '2025-04-24 16:56:31'),
(55, 6, 55, 'abca', 'def', 'whrltan05@gmail.com', '123', '123', '123', '123', '123', '2025-04-24 16:57:08'),
(56, 6, 56, 'abca', 'def', 'whrltan05@gmail.com', '123', '123', '123', '123', '123', '2025-04-24 16:58:41'),
(57, 6, 57, 'abca', 'def', 'whrltan05@gmail.com', '123', '123', '123', '123', '123', '2025-04-24 16:59:47'),
(58, 6, 58, 'abca', 'def', 'whrltan05@gmail.com', '123', '123', '123', '123', '123', '2025-04-24 16:59:58'),
(59, 6, 59, 'abca', 'def', 'whrltan05@gmail.com', '123', '123', '123', '123', '123', '2025-04-24 17:01:47'),
(60, 6, 60, 'abca', 'def', 'whrltan05@gmail.com', '123', '123', '123', '123', '123', '2025-04-24 17:03:46'),
(61, 6, 61, 'abca', 'def', 'whrltan05@gmail.com', '123', '123', '123', '123', '123', '2025-04-24 17:04:51'),
(62, 6, 62, 'abca', 'def', 'whrltan05@gmail.com', '123', '123', '123', '123', '123', '2025-04-24 17:05:37');

-- --------------------------------------------------------

--
-- Table structure for table `temp_users`
--

CREATE TABLE `temp_users` (
  `id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `verification_code` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `UserID` int(7) NOT NULL,
  `username` varchar(255) NOT NULL,
  `FirstName` varchar(255) NOT NULL,
  `LastName` varchar(255) NOT NULL,
  `Email` varchar(255) NOT NULL,
  `Password` varchar(255) NOT NULL,
  `UserType` varchar(255) NOT NULL DEFAULT 'Member',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `profileimgpath` varchar(255) DEFAULT NULL,
  `is_verified` tinyint(1) NOT NULL DEFAULT 0,
  `remember_token` varchar(255) DEFAULT NULL,
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_token_expires_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`UserID`, `username`, `FirstName`, `LastName`, `Email`, `Password`, `UserType`, `is_active`, `profileimgpath`, `is_verified`, `remember_token`, `reset_token`, `reset_token_expires_at`) VALUES
(1, 'Whrlong', 'abc', 'def', 'rotanrontan@gmail.com', '828881104b89c1d0121c98411a6faa5824cb9a11', 'Admin', 1, NULL, 1, NULL, NULL, NULL),
(3, 'admin', 'Ad', 'Min', 'lowwaihang@gmail.com', '$2y$10$AAzlCpxD3/5kGbZmCiy...FrMr9JgiGJ1RGUnPY4jrGdZAzQYA4JC', 'Admin', 1, '3_1743423385.gif', 1, NULL, NULL, NULL),
(4, 'Customer', 'abc', 'def', 'whrlstan05@gmail.com', '$2y$12$3CfI.IZdS/vLnnZMcw89Heenz6ywMPJDGTonfHzs1m8ChbentRM3e', 'Member', 0, NULL, 0, NULL, NULL, NULL),
(5, 'Customer1', 'hello', 'darkness', 'abc@gmail.com', '$2y$12$elPGvgorpON5QNtItQkX8OgX5PkMx7UwVSORIR78K4KpexMyrfsUK', 'Member', 1, NULL, 0, NULL, NULL, NULL),
(6, 'Customer2', 'abcad', 'def', 'whrltan05@gmail.com', 'aace80434a29a7abd9aa18a228c632059aa84ccd', 'Member', 1, '6_1745514498.jpg', 1, NULL, '76cf03cae2388d79e78e494904f505c813fa4055ad90d488726dfa4d340706a2', '2025-04-25 00:12:36'),
(7, 'hang', 'low', 'hang', 'lowwh-wp23@student.tarc.edu.my', '824ee68a8c81717087d431d66b6efaa8b63147cd', 'Member', 1, '7_1745468877.jpg', 1, NULL, NULL, NULL),
(9, 'admin1', 'ad', 'minn', 'tanwl-wp23@student.tarc.edu.my', 'c1277ca3863bb1dfa2cba5d3f9e9a60124e49ae2', 'Admin', 1, NULL, 1, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `verification_codes`
--

CREATE TABLE `verification_codes` (
  `id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `code` varchar(10) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` timestamp NULL DEFAULT NULL,
  `type` enum('registration','login','password_reset') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `verification_codes`
--

INSERT INTO `verification_codes` (`id`, `email`, `code`, `created_at`, `expires_at`, `type`) VALUES
(15, 'rotanrontan@gmail.com', '179647', '2025-03-29 16:36:04', '2025-03-29 16:46:04', 'registration'),
(18, 'whrlstan05@gmail.com', '114995', '2025-03-29 16:46:45', '2025-03-29 16:56:45', ''),
(22, 'niga@gmail.com', '093412', '2025-03-29 18:46:32', '2025-03-29 18:56:32', 'registration'),
(35, 'whrlstan05@gmail.com', '449105', '2025-03-31 08:11:53', '2025-03-31 08:21:53', 'registration'),
(41, 'lowwaihang@gmail.com', '493577', '2025-04-24 03:43:46', '2025-04-24 03:53:46', 'registration'),
(43, 'lowwh-wp23@student.tarc.edu.my', '793418', '2025-04-24 04:18:24', '2025-04-24 04:28:24', 'registration'),
(60, 'tan-wl23@student.tarc.edu.my', '974448', '2025-04-24 14:20:41', '2025-04-24 14:30:41', 'registration');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `brand`
--
ALTER TABLE `brand`
  ADD PRIMARY KEY (`BrandID`);

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`CartID`),
  ADD UNIQUE KEY `unique_user_product` (`UserID`,`ProductID`),
  ADD KEY `ProductID` (`ProductID`);

--
-- Indexes for table `category`
--
ALTER TABLE `category`
  ADD PRIMARY KEY (`CategoryID`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `payment_logs`
--
ALTER TABLE `payment_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `productpictures`
--
ALTER TABLE `productpictures`
  ADD PRIMARY KEY (`pictureID`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`ProductID`),
  ADD KEY `idx_category` (`CategoryID`),
  ADD KEY `idx_brand` (`BrandID`);

--
-- Indexes for table `product_stocks`
--
ALTER TABLE `product_stocks`
  ADD PRIMARY KEY (`StockID`),
  ADD KEY `ProductID` (`ProductID`);

--
-- Indexes for table `refund_requests`
--
ALTER TABLE `refund_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `shipping_addresses`
--
ALTER TABLE `shipping_addresses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `temp_users`
--
ALTER TABLE `temp_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`UserID`);

--
-- Indexes for table `verification_codes`
--
ALTER TABLE `verification_codes`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `brand`
--
ALTER TABLE `brand`
  MODIFY `BrandID` int(4) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1011;

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `CartID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=63;

--
-- AUTO_INCREMENT for table `category`
--
ALTER TABLE `category`
  MODIFY `CategoryID` int(4) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1005;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=63;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=81;

--
-- AUTO_INCREMENT for table `payment_logs`
--
ALTER TABLE `payment_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `productpictures`
--
ALTER TABLE `productpictures`
  MODIFY `pictureID` int(5) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=83;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `ProductID` int(5) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT for table `product_stocks`
--
ALTER TABLE `product_stocks`
  MODIFY `StockID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT for table `refund_requests`
--
ALTER TABLE `refund_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `shipping_addresses`
--
ALTER TABLE `shipping_addresses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=63;

--
-- AUTO_INCREMENT for table `temp_users`
--
ALTER TABLE `temp_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `UserID` int(7) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `verification_codes`
--
ALTER TABLE `verification_codes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=66;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`UserID`) REFERENCES `users` (`UserID`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`ProductID`) REFERENCES `products` (`ProductID`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`UserID`) ON DELETE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`ProductID`);

--
-- Constraints for table `refund_requests`
--
ALTER TABLE `refund_requests`
  ADD CONSTRAINT `refund_requests_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`),
  ADD CONSTRAINT `refund_requests_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`UserID`);

--
-- Constraints for table `shipping_addresses`
--
ALTER TABLE `shipping_addresses`
  ADD CONSTRAINT `shipping_addresses_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`UserID`) ON DELETE CASCADE,
  ADD CONSTRAINT `shipping_addresses_ibfk_2` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
