-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- 主机： localhost
-- 生成日期： 2025-11-12 17:23:44
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
-- 表的结构 `Auctions`
--

CREATE TABLE `Auctions` (
  `auctionId` int(11) NOT NULL,
  `itemId` int(11) DEFAULT NULL,
  `auctionStartTime` datetime DEFAULT NULL,
  `auctionEndTime` datetime DEFAULT NULL,
  `auctionStatus` varchar(50) DEFAULT NULL,
  `startPrice` decimal(10,2) DEFAULT NULL,
  `currentPrice` decimal(10,2) DEFAULT NULL,
  `soldPrice` decimal(10,2) DEFAULT NULL,
  `reservedPrice` decimal(10,2) DEFAULT NULL,
  `userId` int(11) DEFAULT NULL,
  `winningBidId` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 转存表中的数据 `Auctions`
--

INSERT INTO `Auctions` (`auctionId`, `itemId`, `auctionStartTime`, `auctionEndTime`, `auctionStatus`, `startPrice`, `currentPrice`, `soldPrice`, `reservedPrice`, `userId`, `winningBidId`) VALUES
(1, 1, '2025-11-12 16:17:34', '2025-11-19 16:17:34', 'active', 800.00, 800.00, NULL, 900.00, 1, NULL);

-- --------------------------------------------------------

--
-- 表的结构 `Bids`
--

CREATE TABLE `Bids` (
  `bidId` int(11) NOT NULL,
  `auctionId` int(11) DEFAULT NULL,
  `bidderId` int(11) DEFAULT NULL,
  `bidAmount` decimal(10,2) DEFAULT NULL,
  `bidTime` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- 表的结构 `Categories`
--

CREATE TABLE `Categories` (
  `categoryId` int(11) NOT NULL,
  `categoryName` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 转存表中的数据 `Categories`
--

INSERT INTO `Categories` (`categoryId`, `categoryName`) VALUES
(1, 'Electronics'),
(2, 'Books');

-- --------------------------------------------------------

--
-- 表的结构 `Images`
--

CREATE TABLE `Images` (
  `imageId` int(11) NOT NULL,
  `itemId` int(11) DEFAULT NULL,
  `imageUrl` varchar(200) DEFAULT NULL,
  `isPrimary` tinyint(1) DEFAULT NULL,
  `uploadedAt` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- 表的结构 `Items`
--

CREATE TABLE `Items` (
  `itemId` int(11) NOT NULL,
  `itemName` varchar(50) DEFAULT NULL,
  `itemDescription` varchar(500) DEFAULT NULL,
  `userId` int(11) DEFAULT NULL,
  `categoryId` int(11) DEFAULT NULL,
  `itemUploadTime` datetime DEFAULT NULL,
  `itemStatus` varchar(50) DEFAULT NULL,
  `itemCondition` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 转存表中的数据 `Items`
--

INSERT INTO `Items` (`itemId`, `itemName`, `itemDescription`, `userId`, `categoryId`, `itemUploadTime`, `itemStatus`, `itemCondition`) VALUES
(1, 'MacBook Air', 'Apple M2 2022 version', 1, 1, '2025-11-12 16:17:34', 'available', 'new');

-- --------------------------------------------------------

--
-- 表的结构 `Users`
--

CREATE TABLE `Users` (
  `userId` int(11) NOT NULL,
  `userName` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 转存表中的数据 `Users`
--

INSERT INTO `Users` (`userId`, `userName`) VALUES
(1, 'Yufei'),
(2, 'Irene');

--
-- 转储表的索引
--

--
-- 表的索引 `Auctions`
--
ALTER TABLE `Auctions`
  ADD PRIMARY KEY (`auctionId`),
  ADD KEY `itemId` (`itemId`),
  ADD KEY `userId` (`userId`),
  ADD KEY `winningBidId` (`winningBidId`);

--
-- 表的索引 `Bids`
--
ALTER TABLE `Bids`
  ADD PRIMARY KEY (`bidId`),
  ADD KEY `auctionId` (`auctionId`),
  ADD KEY `bidderId` (`bidderId`);

--
-- 表的索引 `Categories`
--
ALTER TABLE `Categories`
  ADD PRIMARY KEY (`categoryId`);

--
-- 表的索引 `Images`
--
ALTER TABLE `Images`
  ADD PRIMARY KEY (`imageId`),
  ADD KEY `itemId` (`itemId`);

--
-- 表的索引 `Items`
--
ALTER TABLE `Items`
  ADD PRIMARY KEY (`itemId`),
  ADD KEY `userId` (`userId`),
  ADD KEY `categoryId` (`categoryId`);

--
-- 表的索引 `Users`
--
ALTER TABLE `Users`
  ADD PRIMARY KEY (`userId`);

--
-- 在导出的表使用AUTO_INCREMENT
--

--
-- 使用表AUTO_INCREMENT `Auctions`
--
ALTER TABLE `Auctions`
  MODIFY `auctionId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- 使用表AUTO_INCREMENT `Bids`
--
ALTER TABLE `Bids`
  MODIFY `bidId` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `Categories`
--
ALTER TABLE `Categories`
  MODIFY `categoryId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- 使用表AUTO_INCREMENT `Images`
--
ALTER TABLE `Images`
  MODIFY `imageId` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `Items`
--
ALTER TABLE `Items`
  MODIFY `itemId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- 使用表AUTO_INCREMENT `Users`
--
ALTER TABLE `Users`
  MODIFY `userId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- 限制导出的表
--

--
-- 限制表 `Auctions`
--
ALTER TABLE `Auctions`
  ADD CONSTRAINT `auctions_ibfk_1` FOREIGN KEY (`itemId`) REFERENCES `Items` (`itemId`),
  ADD CONSTRAINT `auctions_ibfk_2` FOREIGN KEY (`userId`) REFERENCES `Users` (`userId`),
  ADD CONSTRAINT `auctions_ibfk_3` FOREIGN KEY (`winningBidId`) REFERENCES `Bids` (`bidId`);

--
-- 限制表 `Bids`
--
ALTER TABLE `Bids`
  ADD CONSTRAINT `bids_ibfk_1` FOREIGN KEY (`auctionId`) REFERENCES `Auctions` (`auctionId`),
  ADD CONSTRAINT `bids_ibfk_2` FOREIGN KEY (`bidderId`) REFERENCES `Users` (`userId`);

--
-- 限制表 `Images`
--
ALTER TABLE `Images`
  ADD CONSTRAINT `images_ibfk_1` FOREIGN KEY (`itemId`) REFERENCES `Items` (`itemId`);

--
-- 限制表 `Items`
--
ALTER TABLE `Items`
  ADD CONSTRAINT `items_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `Users` (`userId`),
  ADD CONSTRAINT `items_ibfk_2` FOREIGN KEY (`categoryId`) REFERENCES `Categories` (`categoryId`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
