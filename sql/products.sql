-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 27, 2025 at 06:16 PM
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
(41, 1001, 1003, 'The Monsters - Have a seat Vinyl Plush', 71, 'Brand: POP MART\r\nSize: \r\nHeight about 8*7*20cm(including hanging loop)\r\nHeight about 8*7*15cm(excluding hanging loop)\r\nMaterial: \r\nShell: 60%PVC, 40%Polyester\r\nStuffing: 70%Polyester, 20%ABS, 5%Iron Wire, 5%Nylon', '2025-03-31 20:40:03', 15),
(42, 1001, 1003, 'THE MONSTERS Big into Energy Series-Vinyl Plush Pendant Blind Box', 71, 'Brand: POP MART \r\nSize: Height about 15cm \r\nMaterial:Fabric: PVC/polyester Filling: Polyester/iron wire \r\nA whole set contains 6 blind boxes', '2025-04-27 21:53:01', 0),
(43, 1001, 1002, 'League of Legends K/DA ALL OUT Series Figures', 71, 'Brand: POP MART\r\nSize: Height about 12.7cm-14cm\r\nMaterial: PVC/ABS\r\nA whole set contains 5 blind boxes', '2025-04-27 22:00:35', 0),
(44, 1002, 1001, 'SANRIO Official Cinnamoroll Baby Care Set 512991 Plush Toy Doll', 122, 'Delight your child with a plush toy that brings the joy of pretend play to life. This cuddly companion, designed for kids aged 3 and up, offers a nurturing experience as children can feed it with a bottle, soothe it with a pacifier, and wrap it in a swaddling cloth for a peaceful sleep. The pacifier is cleverly designed with a magnet for an added element of interactive fun. However, please be aware of the safety warnings, as the toy contains small parts and magnets that could pose a choking hazard and is not suitable for washing.', '2025-04-27 22:23:26', 0),
(45, 1002, 1001, 'Sanrio Kuromi Plush Toy (Standard) S 853984', 112, 'A plush toy that soothes you every time you hug it. Smooth, soft and fluffy to the touch that makes you want to pet it all the time. The cute little sitting pose is sure to make you smile.\r\n\r\nSafety Warning: None\r\n\r\nBody size: approx. 17 x 11 x 24 cm. Main materials: polyester\r\nAges 3 and up. \r\nAdaptable to ages 3 and up.(C)2005񫺦 SANRIO CO.,LTD.(P)', '2025-04-27 22:25:56', 0),
(46, 1003, 1007, 'Justice Hunters Booster Pack', 20, 'Set Size	60\r\nProduct Type	Booster\r\nOfficial Tournament Store Launch Date	07/30/2025\r\nLaunch Date	08/01/2025\r\nKonami Tournament Legal Date	08/01/2025', '2025-04-27 22:30:45', 0),
(47, 1003, 1007, 'Duelist\'s Advance Booster Pack', 20, 'et Size	100\r\nProduct Type	Booster Pack\r\nOfficial Tournament Store Launch Date	07/02/2025\r\nLaunch Date	07/04/2025\r\nKonami Tournament Legal Date	07/04/2025', '2025-04-27 22:35:00', 0),
(48, 1003, 1007, 'Alliance Insight Booster Pack', 20, 'Set Size	101\r\nProduct Type	Booster Pack\r\nOfficial Tournament Store Launch Date	04/30/2025\r\nLaunch Date	05/02/2025\r\nKonami Tournament Legal Date	05/02/2025', '2025-04-27 22:38:02', 0),
(49, 1003, 1006, 'Scarlet & Violet-Destined Rivals Booster Display Box (36 Packs)', 707, 'We expect to ship this product in late May 2025. We will notify you of any changes to this date. We will not charge your card for this item until it ships. You may see a pre-authorization check on your card, but it is not a charge for this item. You may cancel your preorder by following the steps detailed here.\r\n\r\nPreorders that were placed during the preorder time frame will begin shipping in late-May. More inventory of this product will become available later this year. Please subscribe to our newsletter to receive updates.', '2025-04-27 22:49:58', 0),
(50, 1003, 1006, 'Scarlet & Violet-Twilight Masquerade Booster Display Box (36 Packs)', 707, 'A Festival of Mischief & Mystery!\r\nWelcome to the land of Kitakami, where people and Pokémon live harmoniously with nature. Folktales abound, but not all is as it seems... Uncover the mystery of the masked Legendary Pokémon Ogerpon, appearing as four fearsome types of Tera Pokémon ex, and team up with more newly discovered Pokémon, like Bloodmoon Ursaluna ex and Sinistcha ex. Growing in power, Greninja, Dragapult, and Magcargo dazzle as Tera Pokémon ex, and more ACE SPEC cards round out the festivities in the Pokémon TCG: Scarlet & Violet—Twilight Masquerade expansion!\r\n\r\nIncludes 36 Pokémon TCG: Scarlet & Violet—Twilight Masquerade booster packs\r\nEach booster pack contains 10 cards and 1 Basic Energy. Cards vary by pack.\r\nNote: Our packaging and boxes are designed to protect the contents inside. As packaging and boxes may inadvertently be subjected to wear and tear during shipping, we are unable to offer replacement items or replace packaging for any resulting imperfections, bends, scuffs, or indentations.\r\nSKU: 699-86340', '2025-04-27 22:52:38', 0),
(51, 1003, 1006, 'Scarlet & Violet-Paradox Rift Booster Display Box (36 Packs)', 707, 'Dive into the clouds and explore a land that appears to be unbound by time! With ferocious attacks, Ancient Pokémon like Roaring Moon ex and Sandy Shocks ex appear alongside artificial Future Pokémon like Iron Valiant ex and Iron Hands ex. Meanwhile, Garchomp ex, Mewtwo ex, and others Terastallize to gain new types, as Armarouge ex, Gholdengo ex, and more Pokémon ex join the fray. Adventure awaits as timelines collide in the Pokémon TCG: Scarlet & Violet—Paradox Rift expansion!\r\n\r\nIncludes 36 Pokémon TCG: Scarlet & Violet—Paradox Rift booster packs\r\nEach booster pack contains 10 cards and 1 Basic Energy. Cards vary by pack.\r\nNote: Our packaging and boxes are designed to protect the contents inside. As packaging and boxes may inadvertently be subjected to wear and tear during shipping, we are unable to offer replacement items or replace packaging for any resulting imperfections, bends, scuffs, or indentations.\r\nSKU: 699-85399', '2025-04-27 22:55:32', 0),
(52, 1001, 1001, 'Sanrio characters Hello Kitty 50th Anniversary Series Figures', 50, 'Brand: POP MART\r\nSize: Height about 5.5cm-9cm\r\nMaterial: PVC/ABS\r\nA whole set contains 12 blind boxes', '2025-04-27 23:11:18', 0),
(53, 1001, 1001, 'Sanrio characters Sweet Best Series', 43, 'Product Name： Sanrio characters Sweet Best Series \r\n\r\nBrand： POP MART\r\n\r\nMaterial： PVC/ABS \r\n\r\nSize： Height about 5.7-8.2cm', '2025-04-27 23:15:10', 0),
(54, 1003, 1007, 'Battles of Legend: Monster Mayhem Booster Pack', 20, 'Set Size	177\r\nProduct Type	Booster\r\nOfficial Tournament Store Launch Date	06/11/2025\r\nLaunch Date	06/13/2025\r\nKonami Tournament Legal Date	06/13/2025', '2025-04-27 23:19:38', 0),
(55, 1003, 1007, 'Wild Survivors Booster Pack', 20, 'Set Size	60\r\nProduct Type	Booster Pack\r\nOfficial Tournament Store Launch Date	05/31/2023\r\nLaunch Date	06/02/2023\r\nKonami Tournament Legal Date	06/02/2023', '2025-04-27 23:25:10', 0),
(56, 1003, 1007, 'Amazing Defenders Booster Pack', 20, 'Set Size	60\r\nProduct Type	Booster Pack\r\nOfficial Tournament Store Launch Date	01/18/2023\r\nLaunch Date	01/20/2023\r\nKonami Tournament Legal Date	01/20/202', '2025-04-27 23:30:02', 0),
(57, 1003, 1007, 'The Grand Creators Booster Pack', 20, 'Set Size	60\r\nProduct Type	Booster Pack\r\nOfficial Tournament Store Launch Date	01/26/2022\r\nLaunch Date	01/28/2022\r\nKonami Tournament Legal Date	01/28/2022', '2025-04-27 23:33:46', 0),
(58, 1003, 1007, 'Secret Slayers Booster Pack', 20, 'Set Size	60\r\nOfficial Tournament Store Launch Date	04/02/2020\r\nLaunch Date	04/03/2020\r\nKonami Tournament Legal Date	04/03/2020', '2025-04-27 23:35:04', 0),
(59, 1003, 1006, 'Sword & Shield-Darkness Ablaze Mini Portfolio & Booster Pack', 22, 'About the size of a Pokémon TCG card, mini portfolios are a compact, convenient way to store and display up to 60 cards from your collection. This one features dynamic artwork of Eternamax Eternatus and Gigantamax Charizard, and it comes with a Sword & Shield—Darkness Ablaze booster pack to get you started!\r\n\r\nMini portfolio measures about 3 ½ inches tall and 2 ½ inches wide, and it holds up to 60 cards\r\nBooster pack contains 10 cards and 1 basic Energy\r\nMini portfolio artwork features Eternamax Eternatus and Gigantamax Charizard\r\nSKU: 174-80730', '2025-04-27 23:38:03', 0),
(60, 1003, 1006, 'Sword & Shield-Brilliant Stars Mini Portfolio & Booster Pack', 22, 'Constellations Align in a Show of Force!\r\nAbout the size of a Pokémon TCG card, mini portfolios are a compact, convenient way to store and display up to 60 cards from your collection. This one features dynamic artwork of Arceus and Sky Forme Shaymin, and it comes with a Sword & Shield—Brilliant Stars booster pack to get you started!\r\n\r\nMini portfolio measures about 3 ½ inches tall and 2 ½ inches wide, and it holds up to 60 cards\r\nBooster pack contains 10 cards and either 1 basic Energy or 1 VSTAR marker\r\nMini portfolio artwork features Arceus and Shaymin (Sky Forme) illustrated by Anesaki Dynamic\r\nSKU: 180-85009', '2025-04-27 23:47:21', 0),
(61, 1003, 1006, 'Scarlet & Violet-Temporal Forces Booster Display Box (36 Packs)', 705, 'Ancient & Future Powers Endure!\r\nThe ranks of Ancient and Future Pokémon continue to grow! Walking Wake ex breaks free of the past alongside Raging Bolt ex, while Iron Leaves ex delivers high-tech justice with Iron Crown ex. Outside Area Zero, Wugtrio and Farigiraf shift types as Tera Pokémon ex, and Pokémon Trainers everywhere prepare for the return of ACE SPEC cards with uniquely powerful effects. A rupture in time brings wild beasts and cyber visions to battle in the Pokémon TCG: Scarlet & Violet—Temporal Forces expansion!\r\n\r\nIncludes 36 Pokémon TCG: Scarlet & Violet—Temporal Forces booster packs\r\nEach booster pack contains 10 cards and 1 Basic Energy. Cards vary by pack.\r\nNote: Our packaging and boxes are designed to protect the contents inside. As packaging and boxes may inadvertently be subjected to wear and tear during shipping, we are unable to offer replacement items or replace packaging for any resulting imperfections, bends, scuffs, or indentations.\r\nSKU: 699-86981', '2025-04-27 23:52:06', 0),
(62, 1001, 1002, 'League of Legends: Arcane Series Figures', 96, 'Brand: POP MART \r\nSize: Height about 14cm \r\nMaterial:Fabric: PVC/ABS \r\nA whole set contains 9 blind boxes', '2025-04-28 00:03:13', 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`ProductID`),
  ADD KEY `idx_category` (`CategoryID`),
  ADD KEY `idx_brand` (`BrandID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `ProductID` int(5) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=63;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
