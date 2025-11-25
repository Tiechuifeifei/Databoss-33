-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- 主机： localhost
-- 生成日期： 2025-11-14 01:47:30
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
  `auctionStatus` enum('scheduled','running','ended','cancelled') DEFAULT 'scheduled',
  `startPrice` decimal(10,2) NOT NULL DEFAULT 0.00,
  `soldPrice` decimal(10,2) DEFAULT NULL,
  `winningBidId` int(10) UNSIGNED DEFAULT NULL,
  `reservedPrice` decimal(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 转存表中的数据 `auctions`
--

INSERT INTO `auctions` (`auctionId`, `itemId`, `auctionStartTime`, `auctionEndTime`, `auctionStatus`, `startPrice`, `soldPrice`, `winningBidId`, `reservedPrice`) VALUES
(1001, 2001, '2025-11-11 23:26:39', '2025-12-18 23:26:39', 'scheduled', 1.00, NULL, NULL, 0.00),
(1002, 2002, '2025-11-09 23:26:39', '2025-11-18 23:26:39', 'running', 5.00, NULL, NULL, 0.00),
(1003, 2003, '2025-11-11 23:26:39', '2025-11-18 23:26:39', 'running', 10.00, NULL, NULL, 0.00);

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
(1, 1001, 2, 5.00, '2025-11-12 12:00:00'),
(2, 1002, 3, 9.99, '2025-11-12 09:00:00');

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
(10, 'Bags'),
(20, 'Accessories');

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
(1, 2001, 'images/item_2001_main.jpg', 1, '2025-11-14 00:40:55'),
(2, 2001, 'images/item_2001_side.jpg', 0, '2025-11-14 00:40:55'),
(3, 2001, 'images/item_2001_detail.jpg', 0, '2025-11-14 00:40:55'),
(4, 2002, 'images/item_2002_main.jpg', 1, '2025-11-14 00:40:55'),
(5, 2002, 'images/item_2002_back.jpg', 0, '2025-11-14 00:40:55'),
(6, 2002, 'images/item_2002_close.jpg', 0, '2025-11-14 00:40:55'),
(7, 2003, 'images/item_2003_main.jpg', 1, '2025-11-14 00:40:55'),
(8, 2003, 'images/item_2003_angle.jpg', 0, '2025-11-14 00:40:55'),
(9, 2003, 'images/item_2003_box.jpg', 0, '2025-11-14 00:40:55');

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
  `itemStatus` enum('active','sold','removed') DEFAULT 'active',
  `itemCondition` enum('new','used','refurbished') DEFAULT 'used'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 转存表中的数据 `items`
--

INSERT INTO `items` (`itemId`, `itemName`, `itemDescription`, `sellerId`, `categoryId`, `itemUploadTime`, `itemStatus`, `itemCondition`) VALUES
(2001, 'Item 2001', 'Sample item 2001', 1, 10, '2025-11-11 23:26:39', 'active', 'used'),
(2002, 'Item 2002', 'Sample item 2002', 1, 10, '2025-11-11 23:26:39', 'active', 'used'),
(2003, 'Item 2003', 'Sample item 2003', 1, 20, '2025-11-11 23:26:39', 'active', 'new');

-- --------------------------------------------------------

--
-- 表的结构 `users`
--

CREATE TABLE `users` (
  `userId` int(10) UNSIGNED NOT NULL,
  `userName` varchar(100) DEFAULT NULL,
  `userEmail` varchar(255) NOT NULL,
  `userPassword` varchar(255) NOT NULL,
  `userPhone` varchar(50) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `createdAt` datetime DEFAULT current_timestamp(),
  `role` enum('buyer','seller') NOT NULL DEFAULT 'buyer'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 转存表中的数据 `users`
--

INSERT INTO `users` (`userId`, `userName`, `userEmail`, `userPassword`, `userPhone`, `city`, `country`, `createdAt`, `role`) VALUES
(1, 'Seller A', 'seller@email.com', '7e240de74fb1ed08fa08d38063f6a6a91462a815', '07111 111111', 'London', 'UK', '2025-01-01 10:00:00', 'seller'),
(2, 'Buyer B', 'buyer1@email.com', '5cb138284d431abd6a053a56625ec088bfb88912', '07222 222222', 'Manchester', 'UK', '2025-01-02 11:00:00', 'buyer'),
(3, 'Buyer C', 'buyer2@email.com', 'f36b4825e5db2cf7dd2d2593b3f5c24c0311d8b2', '07333 333333', 'Leeds', 'UK', '2025-01-03 12:00:00', 'buyer');

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
(1, 2, 1001, '2025-11-14 00:43:07'),
(2, 2, 1003, '2025-11-14 00:43:07'),
(3, 3, 1002, '2025-11-14 00:43:07');

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
  MODIFY `auctionId` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1004;

--
-- 使用表AUTO_INCREMENT `bids`
--
ALTER TABLE `bids`
  MODIFY `bidId` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- 使用表AUTO_INCREMENT `categories`
--
ALTER TABLE `categories`
  MODIFY `categoryId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- 使用表AUTO_INCREMENT `images`
--
ALTER TABLE `images`
  MODIFY `imageId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- 使用表AUTO_INCREMENT `items`
--
ALTER TABLE `items`
  MODIFY `itemId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2004;

--
-- 使用表AUTO_INCREMENT `users`
--
ALTER TABLE `users`
  MODIFY `userId` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- 使用表AUTO_INCREMENT `watchlist`
--
ALTER TABLE `watchlist`
  MODIFY `watchId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

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
