-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 31, 2025 at 07:55 PM
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
(26, 6, 40, 6, '2025-04-01 00:42:49');

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
(28, 6, 'ORD-F6E03860', 559.00, 10.00, 'Paid', 'pi_3R8lOlPZSuoPG7KD3U8JQAXX', '2025-04-01 00:41:28', NULL, '2025-04-01 00:40:45', 'stripe', NULL, NULL),
(29, 6, 'ORD-0D0F7256', 81.00, 10.00, 'Paid', 'PP_1743439352_29', '2025-04-01 00:42:32', NULL, '2025-04-01 00:42:18', 'paypal', NULL, NULL);

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
(45, 29, 41, 1, 71.00);

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
(8, 'PP_1743439352_29', 'ORD-0D0F7256', 81.00, 'MYR', 'whrltan05@gmail.com', 'Completed', '{\"order_id\":29,\"payment_method\":\"paypal\",\"success_page\":true}', '2025-03-31 16:42:32');

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
(31, 1001, 1009, 'Hololive Production Fuwacororin Box Vol.2', 65, 'Brand : Hololive\r\n\r\nSize approx. 100mm\r\n\r\n	Assorted according to the maker\'s rate from 7 types.\r\n\r\nLineup:\r\n-AZKi\r\n-Shirakami Fubuki\r\n-Murasaki Shion\r\n-Usada Pekora\r\n-Tsunomaki Watame\r\n-Momosuzu Nene\r\n-Takane Lui', '2025-03-31 20:14:05', 1),
(32, 1001, 1009, 'Hololive Deformation Collection Vol.1', 45, 'Brand: Hololive\r\n\r\nAge Recommend: 15+\r\n\r\nProduct size: Approx. 2cm-5cm\r\n\r\nMaterial: Plastic\r\n\r\nProduct Detail: 8 basic design', '2025-03-31 20:15:57', 0),
(33, 1002, 1004, 'Amuseables Beatie Heart', 180, 'Amuseables Beatie Heart is here to share love all year long. A bright red face with big stitched smile with a matching fluffy mane, Beatie has matching red fine cord legs, making this heart of hearts the perfect gift for someone special.\r\n\r\nDimensions: 24cm x 23cm x 7cm\r\nSitting Height: 20cm\r\nMain Materials: Polyester\r\nInner Filling: Polyester Fibres, PE Beans\r\nHard Eye\r\nSKU: A3REDFH', '2025-03-31 20:21:17', 1),
(34, 1002, 1004, 'Bashful Blush Bunny', 130, 'Bashful Blush Bunny Bag Charm makes every day rosy! Our iconic Bashful Bunny is soft and sweet in blush pink fur, with lop ears and a pale pink nose. Use the silver clasp to attach this bunny to any bag for the softest sidekick.\r\n\r\nDimensions: 18cm x 5cm x 4cm\r\nSitting Height: 13cm\r\nMain Materials: Polyester\r\nInner Filling: Polyester Fibres\r\nEmbroidered Eye\r\nSKU: BAS4ELBC', '2025-03-31 20:22:27', 0),
(35, 1002, 1004, 'Amuseables Sunflower', 200, 'Add a little sunshine to your day with Amuseables Sunflower. This trio of flowers have brown fur faces, bright yellow petals and deep green stalks. Sat in a beautiful brown linen pot with mocha soil, these bright flowers make every day a little sunnier.\r\n\r\nDimensions: 35cm x 11cm x 11cm\r\nSitting Height: 35cm\r\nMain Materials: Polyester\r\nInner Filling: Polyester Fibres, PE Beans\r\nEmbroidered Eye\r\nSKU: A2SNF', '2025-03-31 20:23:27', 1),
(36, 1002, 1005, 'Baymax Sakura Medium Plush, Big Hero 6', 145, 'Our super soft, velvety Baymax Sakura Plush comes from San Fransokyo exclusively via Disney Store Japan. Plump and reassuring, this huggable, all-too-adorable stuffed toy is decorated with cherry blossom appliqués and pastel accents.\r\n\r\nMagic in the details\r\n\r\nDetailed plush sculpting\r\nEmbroidered features\r\nVelvety velour covering\r\nSoft fill\r\nCherry blossom appliqués\r\nGlitter accents\r\nSoft ice cream cone with shimmering fabric scoops\r\nInspired by Disney\'s Big Hero 6 (2014)\r\nPart of the Sakura 2025 Collection\r\nCreated for Disney Store Japan\r\nThe bare necessities\r\n\r\nPolyester fiber / polyethylene foam\r\nApprox. 34.3cm H\r\nImported', '2025-03-31 20:25:36', 1),
(37, 1002, 1005, 'Rex Medium Plush, Toy Story Rex Game', 120, 'It’s game time with the Rex Medium Plush from the Rex Game collection! Join your favourite Toy Story characters for a fun-filled party full of laughter and excitement. With its soft plush texture and vibrant colours, this plush is guaranteed to brighten your day.\r\n\r\nMagic in the details\r\n\r\nDetailed plush sculpting\r\nEmbroidered features\r\nPart of the Rex Game Collection\r\nCreated for Disney Store\r\nThe bare necessities\r\n\r\nPolyester\r\nApprox. 28.5cm H x 27cm L x 35cm W\r\nImported', '2025-03-31 20:26:26', 0),
(38, 1002, 1005, 'Dale Sitting Plush, Chip \'n Dale', 120, 'Bring home the magic from Disney Store Japan with this Dale Sitting Plush. With an adorable head-tilt and sparkling eyes, Dale radiates charm in a gentle pastel hue. Soft, fluffy, and irresistibly cuddly—perfect for bringing comfort and joy!\r\n\r\nMagic in the details\r\n\r\nDetailed plush sculpting\r\nEmbroidered features\r\nPart of the Petanko Collection\r\nCreated for Disney Store Japan\r\nThe bare necessities\r\n\r\nPolyester\r\nApprox. 21cm H x 15cm W x 16cm D\r\nImported', '2025-03-31 20:27:24', 0),
(39, 1002, 1005, 'President Xinnie the Pooh', 2147483647, 'Bring home the magic from Disney Store Japan with this Xinnie the Pooh. With an adorable head-tilt and sparkling eyes, Pooh radiates charm in a gentle pastel hue. Soft, fluffy, and irresistibly cuddly—perfect for bringing comfort and joy!\r\n\r\nMagic in the details\r\n\r\nDetailed plush sculpting\r\nEmbroidered features\r\nInspired by Disney\'s The Many Adventures of Winnie the Pooh (1977)\r\nPart of the Petanko Collection\r\nCreated for Disney Store Japan\r\nThe bare necessities\r\n\r\nPolyester\r\nApprox. 33cm H x 22cm W x 22cm D\r\nImported', '2025-03-31 20:29:17', 1),
(40, 1001, 1003, 'Labubu The Monsters Coca-Cola Series Vinyl Face', 119, 'Brand: POP MART\r\nSize: Height about 15.5cm-17cm\r\nMaterial: Shell: Polyester/ABS/PVC; Stuffing: Polyester/Iron Wire', '2025-03-31 20:38:19', 8),
(41, 1001, 1003, 'The Monsters - Have a seat Vinyl Plush', 71, 'Brand: POP MART\r\nSize: \r\nHeight about 8*7*20cm(including hanging loop)\r\nHeight about 8*7*15cm(excluding hanging loop)\r\nMaterial: \r\nShell: 60%PVC, 40%Polyester\r\nStuffing: 70%Polyester, 20%ABS, 5%Iron Wire, 5%Nylon', '2025-03-31 20:40:03', 1);

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
(41, 41, 222, '2025-03-31 12:40:03');

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
(29, 6, 29, 'abc', 'def', 'whrltan05@gmail.com', '123', '123', '123', '123', '123', '2025-03-31 16:42:18');

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
(3, 'admin', 'Ad', 'Min', 'tanwl-wp23@student.tarc.edu.my', '6c7ca345f63f835cb353ff15bd6c5e052ec08e7a', 'Admin', '3_1743423385.gif', 1, NULL),
(4, 'Customer', 'abc', 'def', 'whrlstan05@gmail.com', '$2y$12$seRpZKpFK3AETaImMeIidOAIXoAWC4QaojUrc6xPdCMVSNdZpndWK', 'Member', NULL, 0, NULL),
(5, 'Customer1', 'hello', 'darkness', 'abc@gmail.com', '$2y$12$elPGvgorpON5QNtItQkX8OgX5PkMx7UwVSORIR78K4KpexMyrfsUK', 'Member', NULL, 0, NULL),
(6, 'Customer2', 'abc', 'def', 'whrltan05@gmail.com', 'aace80434a29a7abd9aa18a228c632059aa84ccd', 'Member', '6_1743416104.gif', 1, NULL);

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
  MODIFY `CartID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `category`
--
ALTER TABLE `category`
  MODIFY `CategoryID` int(4) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1005;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT for table `payment_logs`
--
ALTER TABLE `payment_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

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
-- AUTO_INCREMENT for table `shipping_addresses`
--
ALTER TABLE `shipping_addresses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

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
