-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 31, 2025 at 11:28 AM
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

--
-- Dumping data for table `cart`
--

INSERT INTO `cart` (`CartID`, `UserID`, `ProductID`, `Quantity`, `DateAdded`) VALUES
(4, 3, 20, 1, '2025-03-30 03:53:00'),
(13, 6, 25, 1, '2025-03-31 17:08:31');

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
  `payment_method` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(4, 'pi_3R8aFmPZSuoPG7KD1XEczROg', 'ORD-817C07-20250331', 131.00, 'myr', 'whrltan05@gmail.com', 'succeeded', '{\"payment_intent\":\"pi_3R8aFmPZSuoPG7KD1XEczROg\",\"status\":\"succeeded\"}', '2025-03-31 04:47:27');

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
(1, 1, 1, 'product_images/1743269345_1_images (1).jpeg', 1),
(2, 2, 1, 'product_images/1743270018_1_images (1).jpeg', 1),
(3, 2, 0, 'product_images/1743270018_2_labubujpg.jpg', 2),
(4, 3, 1, 'product_images/1743277631_1_rat-dancing-meme.gif', 1),
(5, 4, 1, 'product_images/1743277671_1_google_icon.png', 1),
(6, 5, 1, 'product_images/1743277681_1_images (1).jpeg', 1),
(7, 6, 1, 'product_images/1743277694_1_bar-graph.png', 1),
(8, 7, 1, 'product_images/1743277704_1_images (1).jpeg', 1),
(9, 8, 1, 'product_images/1743277717_1_images (1).jpeg', 1),
(10, 8, 0, 'product_images/1743277717_2_images (1).jpeg', 2),
(11, 8, 0, 'product_images/1743277717_3_images (1).jpeg', 3),
(12, 8, 0, 'product_images/1743277717_4_images (1).jpeg', 4),
(13, 8, 0, 'product_images/1743277717_5_images (1).jpeg', 5),
(14, 9, 1, 'product_images/1743277732_1_images (1).jpeg', 1),
(15, 10, 1, 'product_images/1743277739_1_images (1).jpeg', 1),
(16, 11, 1, 'product_images/1743277750_1_images (1).jpeg', 1),
(17, 12, 1, 'product_images/1743277760_1_images (1).jpeg', 1),
(18, 13, 1, 'product_images/1743277768_1_images (1).jpeg', 1),
(19, 14, 1, 'product_images/1743277776_1_images (1).jpeg', 1),
(20, 15, 1, 'product_images/1743277785_1_images (1).jpeg', 1),
(21, 16, 1, 'product_images/1743277797_1_images (1).jpeg', 1),
(22, 17, 1, 'product_images/1743277878_1_images (1).jpeg', 1),
(23, 18, 1, 'product_images/1743277950_1_images (1).jpeg', 1),
(24, 19, 1, 'product_images/1743277957_1_images (1).jpeg', 1),
(25, 20, 1, 'product_images/1743277963_1_images (1).jpeg', 1),
(26, 21, 1, 'product_images/1743277969_1_images (1).jpeg', 1),
(27, 22, 1, 'product_images/1743278178_1_images (1).jpeg', 1),
(28, 23, 1, 'product_images/1743278185_1_images (1).jpeg', 1),
(29, 24, 1, 'product_images/1743278194_1_images (1).jpeg', 1),
(30, 25, 1, 'product_images/1743278202_1_images (1).jpeg', 1);

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
(1, 1001, 1007, 'abc', 3, 'abc', '2025-03-30 01:51:41', 1),
(2, 1001, 1004, 'abcd', 22, 'abcd', '2025-03-30 01:51:41', 6),
(3, 1003, 1009, 'mcdonalds', 12312312, 'adasdasdasd', '2025-03-30 03:47:11', 0),
(4, 1001, 1003, 'abc', 13123, '31232', '2025-03-30 03:47:51', 0),
(5, 1001, 1004, '12313', 31231, '1231', '2025-03-30 03:48:01', 0),
(6, 1001, 1008, '12312312', 3123123, '123213', '2025-03-30 03:48:14', 0),
(7, 1001, 1002, '2313213', 3123213, '131321', '2025-03-30 03:48:24', 0),
(8, 1001, 1009, '123213', 31231, '31231', '2025-03-30 03:48:37', 0),
(9, 1001, 1001, '312321', 31231, '312321', '2025-03-30 03:48:52', 0),
(10, 1001, 1004, '13231', 31231, '132', '2025-03-30 03:48:59', 0),
(11, 1001, 1006, 'mcdonaldsds', 12312, '1232131', '2025-03-30 03:49:10', 0),
(12, 1001, 1006, '123123', 12321, '12321', '2025-03-30 03:49:20', 0),
(13, 1001, 1009, '12321', 312, '3123', '2025-03-30 03:49:28', 0),
(14, 1001, 1008, '132', 3123, '3123', '2025-03-30 03:49:36', 0),
(15, 1001, 1009, '123', 3312, '123', '2025-03-30 03:49:45', 0),
(16, 1001, 1005, '312', 213, '3123', '2025-03-30 03:49:57', 0),
(17, 1001, 1005, '1231', 123, '123', '2025-03-30 03:51:18', 0),
(18, 1001, 1005, 'asd', 123, 'asd', '2025-03-30 03:52:30', 0),
(19, 1002, 1004, '123', 123, '123', '2025-03-30 03:52:37', 0),
(20, 1001, 1005, '123', 123, '123', '2025-03-30 03:52:43', 1),
(21, 1001, 1005, '123', 123, '123', '2025-03-30 03:52:49', 0),
(22, 1001, 1005, '123', 123, '123', '2025-03-30 03:56:18', 0),
(23, 1001, 1005, '32131', 1312312, '23213123', '2025-03-30 03:56:25', 1),
(24, 1001, 1005, '123213123121', 42142, '14124124', '2025-03-30 03:56:34', 10),
(25, 1002, 1009, '12312312123131', 121212, '3123213', '2025-03-30 03:56:42', 11);

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
(1, 1, 333, '2025-03-29 17:29:05'),
(2, 2, 2222, '2025-03-29 17:40:18'),
(3, 3, 123123131, '2025-03-29 19:47:11'),
(4, 4, 12312, '2025-03-29 19:47:51'),
(5, 5, 31231, '2025-03-29 19:48:01'),
(6, 6, 313231, '2025-03-29 19:48:14'),
(7, 7, 123213, '2025-03-29 19:48:24'),
(8, 8, 131231, '2025-03-29 19:48:37'),
(9, 9, 1231, '2025-03-29 19:48:52'),
(10, 10, 31231, '2025-03-29 19:48:59'),
(11, 11, 1231, '2025-03-29 19:49:10'),
(12, 12, 31231, '2025-03-29 19:49:20'),
(13, 13, 312, '2025-03-29 19:49:28'),
(14, 14, 31, '2025-03-29 19:49:36'),
(15, 15, 31231, '2025-03-29 19:49:45'),
(16, 16, 1321, '2025-03-29 19:49:57'),
(17, 17, 123, '2025-03-29 19:51:18'),
(18, 18, 123, '2025-03-29 19:52:30'),
(19, 19, 123, '2025-03-29 19:52:37'),
(20, 20, 132, '2025-03-29 19:52:43'),
(21, 21, 123, '2025-03-29 19:52:49'),
(22, 22, 123, '2025-03-29 19:56:18'),
(23, 23, 123213, '2025-03-29 19:56:25'),
(24, 24, 12421, '2025-03-29 19:56:34'),
(25, 25, 2147483647, '2025-03-31 08:04:46');

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
  `profileimgpath` varchar(255) DEFAULT NULL,
  `is_verified` tinyint(1) NOT NULL DEFAULT 0,
  `remember_token` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`UserID`, `username`, `FirstName`, `LastName`, `Email`, `Password`, `UserType`, `profileimgpath`, `is_verified`, `remember_token`) VALUES
(1, 'Whrlong', 'abc', 'def', 'rotanrontan@gmail.com', '828881104b89c1d0121c98411a6faa5824cb9a11', 'Admin', NULL, 1, NULL),
(3, 'admin', 'Ad', 'Min', 'tanwl-wp23@student.tarc.edu.my', '6c7ca345f63f835cb353ff15bd6c5e052ec08e7a', 'Admin', NULL, 1, NULL),
(4, 'Customer', 'abc', 'def', 'whrlstan05@gmail.com', '$2y$12$seRpZKpFK3AETaImMeIidOAIXoAWC4QaojUrc6xPdCMVSNdZpndWK', 'Member', NULL, 0, NULL),
(5, 'Customer1', 'hello', 'darkness', 'abc@gmail.com', '$2y$12$elPGvgorpON5QNtItQkX8OgX5PkMx7UwVSORIR78K4KpexMyrfsUK', 'Member', NULL, 0, NULL),
(6, 'Customer2', 'abc', 'def', 'whrltan05@gmail.com', 'aace80434a29a7abd9aa18a228c632059aa84ccd', 'Member', NULL, 1, NULL);

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
(35, 'whrlstan05@gmail.com', '449105', '2025-03-31 08:11:53', '2025-03-31 08:21:53', 'registration');

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
  MODIFY `CartID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `category`
--
ALTER TABLE `category`
  MODIFY `CategoryID` int(4) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1005;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `payment_logs`
--
ALTER TABLE `payment_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `productpictures`
--
ALTER TABLE `productpictures`
  MODIFY `pictureID` int(5) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `ProductID` int(5) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `product_stocks`
--
ALTER TABLE `product_stocks`
  MODIFY `StockID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `shipping_addresses`
--
ALTER TABLE `shipping_addresses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `temp_users`
--
ALTER TABLE `temp_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `UserID` int(7) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `verification_codes`
--
ALTER TABLE `verification_codes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

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
-- Constraints for table `shipping_addresses`
--
ALTER TABLE `shipping_addresses`
  ADD CONSTRAINT `shipping_addresses_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`UserID`) ON DELETE CASCADE,
  ADD CONSTRAINT `shipping_addresses_ibfk_2` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
