-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- 主机： localhost
-- 生成日期： 2025-12-04 16:04:00
-- 服务器版本： 10.4.28-MariaDB
-- PHP 版本： 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- 数据库： `auction_website`
--

-- --------------------------------------------------------

--
-- 表的结构 `auctions`
--

CREATE TABLE `auctions` (
  `auctionId` int(10) UNSIGNED NOT NULL,
  `itemId` int(11) NOT NULL,
  `auctionStartTime` datetime NOT NULL,
  `auctionEndTime` datetime NOT NULL,
  `auctionStatus` enum('scheduled','running','ended','cancelled','relisted') NOT NULL DEFAULT 'scheduled',
  `startPrice` decimal(10,2) NOT NULL DEFAULT 0.00,
  `soldPrice` decimal(10,2) DEFAULT NULL,
  `winningBidId` int(10) UNSIGNED DEFAULT NULL,
  `reservedPrice` decimal(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 转存表中的数据 `auctions`
--

INSERT INTO `auctions` (`auctionId`, `itemId`, `auctionStartTime`, `auctionEndTime`, `auctionStatus`, `startPrice`, `soldPrice`, `winningBidId`, `reservedPrice`) VALUES
(1066, 2064, '2025-12-04 13:00:00', '2025-12-04 13:30:00', 'ended', 120.00, NULL, NULL, 200.00),
(1067, 2065, '2025-12-04 13:00:00', '2025-12-20 13:00:00', 'running', 80.00, NULL, NULL, 150.00),
(1068, 2066, '2025-12-06 12:00:00', '2025-12-16 12:00:00', 'scheduled', 300.00, NULL, NULL, 500.00),
(1069, 2067, '2025-12-10 12:00:00', '2025-12-18 12:00:00', 'scheduled', 50.00, NULL, NULL, 90.00),
(1070, 2068, '2025-12-04 13:00:00', '2025-12-18 13:00:00', 'running', 65.00, NULL, NULL, 110.00),
(1071, 2069, '2025-12-04 14:00:00', '2025-12-17 14:00:00', 'scheduled', 90.00, NULL, NULL, 150.00),
(1072, 2070, '2025-12-05 12:00:00', '2025-12-20 12:00:00', 'scheduled', 70.00, NULL, NULL, 130.00),
(1073, 2071, '2025-12-04 12:30:00', '2025-12-07 12:00:00', 'running', 150.00, NULL, NULL, 250.00),
(1074, 2072, '2025-12-04 13:00:00', '2025-12-20 13:00:00', 'running', 60.00, NULL, NULL, 100.00),
(1075, 2073, '2025-12-04 13:00:00', '2025-12-20 13:00:00', 'running', 120.00, NULL, NULL, 200.00),
(1076, 2074, '2025-12-04 14:00:00', '2025-12-30 14:00:00', 'scheduled', 3000.00, NULL, NULL, 4500.00),
(1077, 2075, '2025-12-04 13:30:00', '2025-12-31 13:30:00', 'running', 20000.00, NULL, NULL, 30000.00),
(1078, 2076, '2025-12-04 13:30:00', '2025-12-22 13:30:00', 'running', 1200.00, NULL, NULL, 1800.00),
(1079, 2077, '2025-12-10 13:00:00', '2026-01-01 13:00:00', 'scheduled', 12000.00, NULL, NULL, 15000.00),
(1080, 2078, '2025-12-04 14:00:00', '2026-01-04 14:00:00', 'scheduled', 20000.00, NULL, NULL, 25000.00),
(1081, 2079, '2025-12-04 14:00:00', '2026-01-04 14:00:00', 'scheduled', 300.00, NULL, NULL, 400.00),
(1082, 2080, '2025-12-04 14:30:00', '2025-12-20 14:30:00', 'scheduled', 100.00, NULL, NULL, 150.00),
(1083, 2081, '2025-12-04 13:30:00', '2025-12-26 13:30:00', 'running', 120.00, NULL, NULL, 220.00),
(1084, 2082, '2025-12-04 13:20:00', '2025-12-20 13:30:00', 'running', 50.00, NULL, NULL, 75.00),
(1085, 2084, '2025-12-04 13:40:00', '2025-12-20 13:40:00', 'running', 70.00, NULL, NULL, 80.00);

-- --------------------------------------------------------

--
-- 表的结构 `bids`
--

CREATE TABLE `bids` (
  `bidId` int(10) UNSIGNED NOT NULL,
  `auctionId` int(10) UNSIGNED NOT NULL,
  `buyerId` int(10) UNSIGNED NOT NULL,
  `bidPrice` decimal(10,2) NOT NULL,
  `bidTime` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 转存表中的数据 `bids`
--

INSERT INTO `bids` (`bidId`, `auctionId`, `buyerId`, `bidPrice`, `bidTime`) VALUES
(23, 1066, 10, 130.00, '2025-12-04 13:20:57'),
(24, 1070, 10, 70.00, '2025-12-04 13:38:44'),
(25, 1074, 10, 80.00, '2025-12-04 13:39:03'),
(26, 1067, 10, 90.00, '2025-12-04 13:39:19');

-- --------------------------------------------------------

--
-- 表的结构 `categories`
--

CREATE TABLE `categories` (
  `categoryId` int(11) NOT NULL,
  `categoryName` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 转存表中的数据 `categories`
--

INSERT INTO `categories` (`categoryId`, `categoryName`) VALUES
(5001, 'Vintage Jewellery & Watches'),
(5002, 'Furniture'),
(5003, 'Classic Car & Automobile'),
(5004, 'Vintage Fashion');

-- --------------------------------------------------------

--
-- 表的结构 `images`
--

CREATE TABLE `images` (
  `imageId` int(11) NOT NULL,
  `itemId` int(11) NOT NULL,
  `imageUrl` varchar(200) NOT NULL,
  `isPrimary` tinyint(1) NOT NULL DEFAULT 0,
  `uploadedAt` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 转存表中的数据 `images`
--

INSERT INTO `images` (`imageId`, `itemId`, `imageUrl`, `isPrimary`, `uploadedAt`) VALUES
(96, 2064, 'uploads/693176bfb14b7.jpg', 0, '2025-12-04 11:55:43'),
(97, 2064, 'uploads/693176ca9f3ed.jpg', 1, '2025-12-04 11:55:54'),
(98, 2064, 'uploads/693176e298e1e.jpg', 0, '2025-12-04 11:56:18'),
(99, 2065, 'uploads/6931775c90362.jpg', 1, '2025-12-04 11:58:20'),
(100, 2066, 'uploads/693177b6982d9.jpg', 0, '2025-12-04 11:59:50'),
(101, 2066, 'uploads/693177c1e39e0.jpg', 1, '2025-12-04 12:00:01'),
(102, 2066, 'uploads/693177cc90aa6.jpg', 0, '2025-12-04 12:00:12'),
(103, 2067, 'uploads/693178bd2476a.jpg', 1, '2025-12-04 12:04:13'),
(104, 2067, 'uploads/693178c46e636.jpg', 0, '2025-12-04 12:04:20'),
(105, 2068, 'uploads/69317915cd8dd.jpg', 1, '2025-12-04 12:05:41'),
(106, 2068, 'uploads/6931791d3669e.jpg', 0, '2025-12-04 12:05:49'),
(107, 2069, 'uploads/6931798161c11.jpg', 1, '2025-12-04 12:07:29'),
(108, 2069, 'uploads/693179abeacb8.jpg', 0, '2025-12-04 12:08:11'),
(109, 2070, 'uploads/693179f8d63a6.jpg', 1, '2025-12-04 12:09:28'),
(110, 2070, 'uploads/69317a0f5fad7.jpg', 0, '2025-12-04 12:09:51'),
(111, 2071, 'uploads/69317a5be620b.jpg', 1, '2025-12-04 12:11:07'),
(112, 2071, 'uploads/69317a672ae72.jpg', 0, '2025-12-04 12:11:19'),
(113, 2072, 'uploads/69317c62a3142.jpg', 0, '2025-12-04 12:19:46'),
(114, 2072, 'uploads/69317c68bfdd6.jpg', 1, '2025-12-04 12:19:52'),
(115, 2072, 'uploads/69317c78951e9.jpg', 0, '2025-12-04 12:20:08'),
(116, 2073, 'uploads/69317cc996f8b.jpg', 1, '2025-12-04 12:21:29'),
(117, 2073, 'uploads/69317cd3940f1.jpg', 0, '2025-12-04 12:21:39'),
(118, 2074, 'uploads/6931857a55512.jpg', 1, '2025-12-04 12:58:34'),
(119, 2074, 'uploads/693185814f0d2.jpg', 0, '2025-12-04 12:58:41'),
(120, 2075, 'uploads/6931860839851.jpg', 1, '2025-12-04 13:00:56'),
(121, 2075, 'uploads/6931861a8a36c.jpg', 0, '2025-12-04 13:01:14'),
(122, 2076, 'uploads/6931866f95e3f.jpg', 1, '2025-12-04 13:02:39'),
(123, 2076, 'uploads/69318676e1b0f.jpg', 0, '2025-12-04 13:02:46'),
(124, 2077, 'uploads/693186cb562e5.jpg', 1, '2025-12-04 13:04:11'),
(125, 2077, 'uploads/693186d42c7f7.jpg', 0, '2025-12-04 13:04:20'),
(126, 2078, 'uploads/69318711aaa61.jpg', 0, '2025-12-04 13:05:21'),
(127, 2078, 'uploads/6931871b27e15.jpg', 0, '2025-12-04 13:05:31'),
(128, 2078, 'uploads/6931872d6b698.jpg', 1, '2025-12-04 13:05:49'),
(129, 2079, 'uploads/6931877887885.jpg', 1, '2025-12-04 13:07:04'),
(130, 2079, 'uploads/6931877fc3ccc.jpg', 0, '2025-12-04 13:07:11'),
(131, 2080, 'uploads/693187bc3da2a.jpg', 0, '2025-12-04 13:08:12'),
(132, 2080, 'uploads/693187c971eaa.jpg', 1, '2025-12-04 13:08:25'),
(133, 2081, 'uploads/6931881b7a4f6.jpg', 1, '2025-12-04 13:09:47'),
(134, 2081, 'uploads/69318840f2ae6.jpg', 0, '2025-12-04 13:10:24'),
(135, 2082, 'uploads/6931888f81ce4.jpg', 1, '2025-12-04 13:11:43'),
(136, 2082, 'uploads/6931889888b07.jpg', 0, '2025-12-04 13:11:52'),
(138, 2083, 'uploads/69318b1a08641.jpg', 1, '2025-12-04 13:22:34'),
(139, 2083, 'uploads/69318b2aab349.jpg', 0, '2025-12-04 13:22:50'),
(140, 2084, 'uploads/69318de372e85.jpg', 0, '2025-12-04 13:34:27'),
(141, 2084, 'uploads/69318df1da1b8.jpg', 1, '2025-12-04 13:34:41');

-- --------------------------------------------------------

--
-- 表的结构 `items`
--

CREATE TABLE `items` (
  `itemId` int(11) NOT NULL,
  `itemName` varchar(50) NOT NULL,
  `itemDescription` varchar(2000) DEFAULT NULL,
  `sellerId` int(10) UNSIGNED NOT NULL,
  `categoryId` int(11) DEFAULT NULL,
  `itemUploadTime` datetime DEFAULT current_timestamp(),
  `itemStatus` enum('active','sold','inactive') DEFAULT 'inactive',
  `itemCondition` enum('new','used','refurbished') DEFAULT 'used'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 转存表中的数据 `items`
--

INSERT INTO `items` (`itemId`, `itemName`, `itemDescription`, `sellerId`, `categoryId`, `itemUploadTime`, `itemStatus`, `itemCondition`) VALUES
(2064, ' Art Deco Sapphire Ring', 'A stunning 1930s Art Deco sapphire ring featuring a deep royal-blue centre stone framed by geometric baguette-cut crystals. The white gold setting retains its original sharp lines, and despite minor age-related wear, the craftsmanship remains exceptional. This piece captures the bold elegance of pre-war European design.\r\n', 4, 5001, '2025-12-04 11:42:00', 'sold', 'used'),
(2065, '1950s Pearl Choker Necklace', 'A classic mid-century pearl choker composed of hand-selected freshwater pearls known for their even, soft glow. The original sterling silver clasp is engraved with a delicate floral motif. The necklace has been professionally restrung to ensure durability while maintaining its vintage charm.\r\n', 4, 5001, '2025-12-04 11:58:12', 'active', 'used'),
(2066, ' Vintage Omega Seamaster Watch (1964)', 'This fully serviced 1964 Omega Seamaster features an automatic movement housed in a stainless-steel case with a clean brushed finish. The dial shows subtle patina consistent with its age, giving it an authentic vintage character. A new leather strap was fitted while preserving all original internal components.\r\n', 4, 5001, '2025-12-04 11:59:40', 'inactive', 'refurbished'),
(2067, 'Golden Floral Brooch', 'A brooch crafted entirely in gold, showcasing exquisitely detailed hand-engraved floral patterns. The clasp mechanism is original and still functions smoothly. This brooch is both a wearable accessory and a collectable decorative item.', 4, 5001, '2025-12-04 12:03:52', 'inactive', 'used'),
(2068, 'Retro 1970s Gold Bracelet', 'A bold and glamorous gold-plated bracelet from the 1970s. Its flexible woven structure reflects light beautifully, making it an iconic representation of the decade’s fashion. The clasp closes securely and has been gently polished to restore its shine while keeping the vintage texture.\r\n', 4, 5001, '2025-12-04 12:05:27', 'active', 'used'),
(2069, 'Walnut Coffee Table', 'A minimalist 1960s walnut coffee table featuring clean lines and elegantly tapered legs. The tabletop has been lightly refinished to enhance the natural grain while preserving faint marks that testify to decades of use. A timeless addition to any modern or retro-inspired living space.\r\n', 4, 5002, '2025-12-04 12:06:52', 'inactive', 'refurbished'),
(2070, 'Vintage Rattan Lounge Chair', 'This 1970s handcrafted rattan lounge chair features a curved silhouette that offers exceptional comfort. The weaving remains tight and shows impressive durability. The warm honey-toned finish adds a natural, relaxed aesthetic perfect for sunrooms, studios, or reading corners.\r\n', 4, 5002, '2025-12-04 12:09:19', 'inactive', 'used'),
(2071, ' Victorian Mahogany Writing Desk', 'A richly detailed mahogany writing desk from the late Victorian period, complete with brass handles and dovetail joint drawers. The surface features gentle wear consistent with age, but the desk remains structurally sound. Ideal for collectors or anyone seeking a statement study piece.', 4, 5002, '2025-12-04 12:10:57', 'active', 'used'),
(2072, 'Bauhaus style Steel Bookshelf', 'A restored Bauhaus-style steel bookshelf originally used in a workshop setting. The metal frame has been sandblasted and powder-coated, giving it a sleek matte finish. Wood shelves show natural knots and variations, adding character and warmth.', 4, 5002, '2025-12-04 12:19:35', 'active', 'used'),
(2073, 'Classic Leather Armchair', 'A traditional leather armchair with deep cushioning and rolled arms. The leather has developed a beautiful patina over time, displaying natural creases and colour variation. Despite cosmetic aging, it remains extremely comfortable and structurally strong.', 4, 5002, '2025-12-04 12:21:20', 'active', 'new'),
(2074, ' 1968 Mini Cooper (Restored)', 'A fully restored 1968 Mini Cooper featuring its original 998cc engine. The bodywork has been repainted in classic British Racing Green with white racing stripes. Interior upholstery is new yet faithful to the original design. Runs smoothly and starts reliably — a perfect entry-level vintage car.', 4, 5003, '2025-12-04 12:58:24', 'inactive', 'refurbished'),
(2075, ' 1980 Porsche 911', 'An authentic steering wheel removed from a 1980 Porsche 911 SC during an interior upgrade. The leather shows light wear but remains intact and highly functional. A desirable piece for restoration projects or collectors of Porsche memorabilia.', 4, 5003, '2025-12-04 13:00:43', 'active', 'used'),
(2076, '1972 Honda CB350 Motorcycle', 'A well-preserved 1972 Honda CB350 in classic red-and-chrome finish. The bike features its original 325cc twin-cylinder engine, recently tuned for smoother ignition and improved fuel efficiency. The frame shows minor age-related wear but remains structurally solid. Both the exhaust pipes and carburetors have been cleaned and adjusted, while the seat has been reupholstered to match the original stitching. A reliable, lightweight vintage motorcycle ideal for collectors or weekend riders.', 4, 5003, '2025-12-04 13:02:34', 'active', 'refurbished'),
(2077, ' 1969 Jaguar XJ6 (Project Car)', 'A 1969 Jaguar XJ6 saloon, presented as a promising restoration project. The inline-six 4.2L engine turns over but requires servicing before road use. The body panels are original, with light corrosion in expected areas, and the chrome trim remains largely intact. The interior features classic British leather seats with patina and walnut veneer that can be restored to former luxury. Ideal for enthusiasts seeking a rewarding vintage car rebuild.\r\n', 4, 5003, '2025-12-04 13:03:45', 'inactive', 'used'),
(2078, '1984 BMW E30 318i Coupe', 'A 1984 BMW E30 318i coupe in iconic Alpine White. The 1.8L engine runs smoothly, and the 5-speed manual transmission shifts cleanly. Exterior paint shows light sun fading but no major dents. The interior is remarkably well-kept for its age, featuring original fabric seats and a crack-free dashboard. Recent work includes brake servicing and a new battery. A desirable and increasingly collectible classic BMW.', 4, 5003, '2025-12-04 13:05:13', 'inactive', 'used'),
(2079, 'Wool A-Line Coat', 'A beautifully structured dotA-line wool coat in soft camel brown. Features oversized buttons and hand-stitched inner seams. The fabric is warm and still remarkably smooth, with only light wear on the cuffs.', 4, 5004, '2025-12-04 13:06:58', 'inactive', 'new'),
(2080, '1980s Leather Bomber Jacket', 'An authentic 1980s black leather bomber jacket with ribbed hems and a quilted lining. The leather has developed a soft, worn-in feel that reflects years of use while retaining excellent durability. A classic streetwear statement piece.', 4, 5004, '2025-12-04 13:08:00', 'inactive', 'used'),
(2081, 'White Polka Dot Dress', 'A charming tea-length dress with a fitted bodice and flared skirt, inspired by the elegance of 1950s fashion. The polka dot pattern remains sharp, and the waist seams are reinforced to preserve its original silhouette.', 4, 5004, '2025-12-04 13:09:42', 'active', 'new'),
(2082, 'Handwoven Silk Scarf (1970s)', 'A lightweight pure silk scarf from the 1970s featuring hand-dyed colour gradients. The edges are hand-rolled, showcasing delicate craftsmanship. The colours remain vibrant and free from major fading.', 4, 5004, '2025-12-04 13:11:34', 'active', 'used'),
(2083, 'Vintage Denim Workwear Jacket', 'A classic blue denim workwear jacket with reinforced stitching and brass buttons. The denim shows subtle fading that adds character without compromising structure. A timeless utilitarian piece that pairs well with modern outfits.', 10, 5004, '2025-12-04 13:21:54', 'inactive', 'used'),
(2084, ' Vintage Denim Workwear Jacket', 'A classic blue denim workwear jacket with reinforced stitching and brass buttons. The denim shows subtle fading that adds character without compromising structure. A timeless utilitarian piece that pairs well with modern outfits.', 10, 5004, '2025-12-04 13:34:19', 'active', 'used');

-- --------------------------------------------------------

--
-- 表的结构 `sellerRatings`
--

CREATE TABLE `sellerRatings` (
  `ratingId` int(11) NOT NULL,
  `sellerId` int(11) NOT NULL,
  `raterId` int(11) NOT NULL,
  `rating` tinyint(3) UNSIGNED NOT NULL,
  `comment` text DEFAULT NULL,
  `auctionId` int(11) NOT NULL,
  `createdAt` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 转存表中的数据 `sellerRatings`
--

INSERT INTO `sellerRatings` (`ratingId`, `sellerId`, `raterId`, `rating`, `comment`, `auctionId`, `createdAt`) VALUES
(1, 4, 9, 5, 'good seller!', 1030, '2025-12-02 00:37:38'),
(2, 4, 10, 5, 'Good seller', 1066, '2025-12-04 13:41:46');

-- --------------------------------------------------------

--
-- 表的结构 `users`
--

CREATE TABLE `users` (
  `userId` int(10) UNSIGNED NOT NULL,
  `userName` varchar(100) DEFAULT NULL,
  `userEmail` varchar(255) NOT NULL,
  `userPassword` varchar(255) NOT NULL,
  `createdAt` datetime DEFAULT current_timestamp(),
  `userRole` varchar(20) NOT NULL DEFAULT 'buyer',
  `userPhoneNumber` varchar(50) DEFAULT NULL,
  `userDob` date DEFAULT NULL,
  `userHouseNo` varchar(50) DEFAULT NULL,
  `userStreet` varchar(255) DEFAULT NULL,
  `userCity` varchar(100) DEFAULT NULL,
  `userPostcode` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 转存表中的数据 `users`
--

INSERT INTO `users` (`userId`, `userName`, `userEmail`, `userPassword`, `createdAt`, `userRole`, `userPhoneNumber`, `userDob`, `userHouseNo`, `userStreet`, `userCity`, `userPostcode`) VALUES
(4, 'xiaohua', '90750791197@qq.com', '$2y$10$jEXvufpdBeW8nIQksuROTuyqBamAa7mQAT1M0GhWeI3ysLYJsdxVa', '2025-11-25 12:29:23', 'buyer', '07858656880', '2006-03-18', '96', 'Look Lane', 'London', 'E1 6GU'),
(10, 'Irene S', 'c20030106@gamil.com', '$2y$10$r6itZWC.4YsyG6VraW//I.xoAtGm9pbFZb9ow6Mt8gxJqQQDGk9ji', '2025-12-04 13:20:22', 'buyer', '1234567', '2003-01-01', '35 XXX House', 'XXX Street', 'London', '1AB 2CD');

-- --------------------------------------------------------

--
-- 表的结构 `watchlist`
--

CREATE TABLE `watchlist` (
  `watchId` int(11) NOT NULL,
  `userId` int(10) UNSIGNED NOT NULL,
  `auctionId` int(11) UNSIGNED NOT NULL,
  `addedAt` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 转存表中的数据 `watchlist`
--

INSERT INTO `watchlist` (`watchId`, `userId`, `auctionId`, `addedAt`) VALUES
(24, 10, 1066, '2025-12-04 13:20:45');

--
-- 转储表的索引
--

--
-- 表的索引 `auctions`
--
ALTER TABLE `auctions`
  ADD PRIMARY KEY (`auctionId`),
  ADD KEY `idx_auctions_item` (`itemId`),
  ADD KEY `fk_auctions_winningBid` (`winningBidId`);

--
-- 表的索引 `bids`
--
ALTER TABLE `bids`
  ADD PRIMARY KEY (`bidId`),
  ADD KEY `idx_bids_auction` (`auctionId`),
  ADD KEY `idx_bids_buyer` (`buyerId`);

--
-- 表的索引 `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`categoryId`);

--
-- 表的索引 `images`
--
ALTER TABLE `images`
  ADD PRIMARY KEY (`imageId`),
  ADD KEY `idx_images_item` (`itemId`);

--
-- 表的索引 `items`
--
ALTER TABLE `items`
  ADD PRIMARY KEY (`itemId`),
  ADD KEY `idx_items_seller` (`sellerId`),
  ADD KEY `idx_items_category` (`categoryId`);

--
-- 表的索引 `sellerRatings`
--
ALTER TABLE `sellerRatings`
  ADD PRIMARY KEY (`ratingId`);

--
-- 表的索引 `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`userId`),
  ADD UNIQUE KEY `user_email` (`userEmail`);

--
-- 表的索引 `watchlist`
--
ALTER TABLE `watchlist`
  ADD PRIMARY KEY (`watchId`),
  ADD UNIQUE KEY `unique_watch` (`userId`,`auctionId`),
  ADD KEY `idx_watch_user` (`userId`),
  ADD KEY `idx_watch_auction` (`auctionId`);

--
-- 在导出的表使用AUTO_INCREMENT
--

--
-- 使用表AUTO_INCREMENT `auctions`
--
ALTER TABLE `auctions`
  MODIFY `auctionId` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1086;

--
-- 使用表AUTO_INCREMENT `bids`
--
ALTER TABLE `bids`
  MODIFY `bidId` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- 使用表AUTO_INCREMENT `categories`
--
ALTER TABLE `categories`
  MODIFY `categoryId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5005;

--
-- 使用表AUTO_INCREMENT `images`
--
ALTER TABLE `images`
  MODIFY `imageId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=142;

--
-- 使用表AUTO_INCREMENT `items`
--
ALTER TABLE `items`
  MODIFY `itemId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2085;

--
-- 使用表AUTO_INCREMENT `sellerRatings`
--
ALTER TABLE `sellerRatings`
  MODIFY `ratingId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- 使用表AUTO_INCREMENT `users`
--
ALTER TABLE `users`
  MODIFY `userId` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- 使用表AUTO_INCREMENT `watchlist`
--
ALTER TABLE `watchlist`
  MODIFY `watchId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- 限制导出的表
--

--
-- 限制表 `auctions`
--
ALTER TABLE `auctions`
  ADD CONSTRAINT `fk_auctions_item` FOREIGN KEY (`itemId`) REFERENCES `items` (`itemId`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_auctions_winningBid` FOREIGN KEY (`winningBidId`) REFERENCES `bids` (`bidId`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- 限制表 `bids`
--
ALTER TABLE `bids`
  ADD CONSTRAINT `fk_bids_auction` FOREIGN KEY (`auctionId`) REFERENCES `auctions` (`auctionId`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_bids_buyer` FOREIGN KEY (`buyerId`) REFERENCES `users` (`userId`) ON DELETE CASCADE;

--
-- 限制表 `images`
--
ALTER TABLE `images`
  ADD CONSTRAINT `fk_images_item` FOREIGN KEY (`itemId`) REFERENCES `items` (`itemId`) ON DELETE CASCADE;

--
-- 限制表 `items`
--
ALTER TABLE `items`
  ADD CONSTRAINT `fk_items_category` FOREIGN KEY (`categoryId`) REFERENCES `categories` (`categoryId`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_items_seller` FOREIGN KEY (`sellerId`) REFERENCES `users` (`userId`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- 限制表 `watchlist`
--
ALTER TABLE `watchlist`
  ADD CONSTRAINT `fk_watch_auction` FOREIGN KEY (`auctionId`) REFERENCES `auctions` (`auctionId`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_watch_user` FOREIGN KEY (`userId`) REFERENCES `users` (`userId`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
