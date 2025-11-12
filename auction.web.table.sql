-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Nov 12, 2025 at 03:35 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `auction.web.table`
--

-- --------------------------------------------------------

--
-- Table structure for table `auctions`
--

CREATE TABLE `auctions` (
  `auctionId` int(11) NOT NULL,
  `itemId` int(11) DEFAULT NULL,
  `auctionStartTime` datetime DEFAULT NULL,
  `auctionEndTime` datetime DEFAULT NULL,
  `auctionStatus` varchar(50) DEFAULT NULL,
  `startPrice` decimal(10,2) DEFAULT NULL,
  `soldPrice` decimal(10,2) DEFAULT NULL,
  `winningBidId` int(11) DEFAULT NULL,
  `reservedPrice` decimal(10,2) DEFAULT NULL,
  `sellerId` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `auctions`
--

INSERT INTO `auctions` (`auctionId`, `itemId`, `auctionStartTime`, `auctionEndTime`, `auctionStatus`, `startPrice`, `soldPrice`, `winningBidId`, `reservedPrice`, `sellerId`) VALUES
(1001, 2001, '2025-11-11 23:26:39', '2025-11-18 23:26:39', 'running', 1.00, NULL, NULL, 0.00, 1),
(1002, 2002, '2025-11-11 23:26:39', '2025-11-18 23:26:39', 'running', 5.00, NULL, NULL, 0.00, 1),
(1003, 2003, '2025-11-11 23:26:39', '2025-11-18 23:26:39', 'running', 10.00, NULL, NULL, 0.00, 1);

-- --------------------------------------------------------

--
-- Table structure for table `bids`
--

CREATE TABLE `bids` (
  `bidId` int(10) UNSIGNED NOT NULL,
  `auctionId` int(10) UNSIGNED NOT NULL,
  `buyerId` int(10) UNSIGNED NOT NULL,
  `bidPrice` decimal(10,2) NOT NULL,
  `bidTime` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bids`
--

INSERT INTO `bids` (`bidId`, `auctionId`, `buyerId`, `bidPrice`, `bidTime`) VALUES
(1, 1001, 1, 5.00, '2025-01-05 12:00:00'),
(2, 1002, 2, 9.99, '2025-01-06 09:00:00'),
(3, 1003, 3, 15.00, '2025-01-07 17:00:00'),
(4, 1001, 2, 6.50, '2025-11-12 00:59:44');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `categoryId` int(11) NOT NULL,
  `categoryName` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`categoryId`, `categoryName`) VALUES
(10, 'Bags'),
(20, 'Accessories');

-- --------------------------------------------------------

--
-- Table structure for table `images`
--

CREATE TABLE `images` (
  `imageId` int(11) NOT NULL,
  `itemId` int(11) DEFAULT NULL,
  `imageUrl` varchar(200) DEFAULT NULL,
  `isPrimary` tinyint(1) DEFAULT NULL,
  `uploadedAt` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `items`
--

CREATE TABLE `items` (
  `itemId` int(11) NOT NULL,
  `itemName` varchar(50) DEFAULT NULL,
  `itemDescription` varchar(500) DEFAULT NULL,
  `sellerId` int(11) DEFAULT NULL,
  `categoryId` int(11) DEFAULT NULL,
  `itemUploadTime` datetime DEFAULT NULL,
  `itemStatus` varchar(50) DEFAULT NULL,
  `itemCondition` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `items`
--

INSERT INTO `items` (`itemId`, `itemName`, `itemDescription`, `sellerId`, `categoryId`, `itemUploadTime`, `itemStatus`, `itemCondition`) VALUES
(2001, 'Item 2001', 'Sample item 2001', 1, 10, '2025-11-11 23:26:39', 'active', 'used'),
(2002, 'Item 2002', 'Sample item 2002', 1, 10, '2025-11-11 23:26:39', 'active', 'used'),
(2003, 'Item 2003', 'Sample item 2003', 1, 20, '2025-11-11 23:26:39', 'active', 'new');

-- --------------------------------------------------------

--
-- Stand-in structure for view `sellers`
-- (See below for the actual view)
--
CREATE TABLE `sellers` (
`sellerId` int(10) unsigned
);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `userId` int(10) UNSIGNED NOT NULL,
  `userName` varchar(100) DEFAULT NULL,
  `userEmail` varchar(255) DEFAULT NULL,
  `userPassword` varchar(255) DEFAULT NULL,
  `userPhone` varchar(50) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `createdAt` datetime DEFAULT NULL,
  `role` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`userId`, `userName`, `userEmail`, `userPassword`, `userPhone`, `city`, `country`, `createdAt`, `role`) VALUES
(1, 'A', 'a@email.com', '7e240de74fb1ed08fa08d38063f6a6a91462a815', '07111 111111', 'London', 'UK', '2025-01-01 10:00:00', 'seller'),
(2, 'B', 'b@email.com', '5cb138284d431abd6a053a56625ec088bfb88912', '07222 222222', 'Manchester', 'UK', '2025-01-02 11:00:00', 'buyer'),
(3, 'C', 'c@email.com', 'f36b4825e5db2cf7dd2d2593b3f5c24c0311d8b2', '07333 333333', 'Leeds', 'UK', '2025-01-03 12:00:00', 'buyer');

-- --------------------------------------------------------

--
-- Structure for view `sellers`
--
DROP TABLE IF EXISTS `sellers`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `sellers`  AS SELECT `users`.`userId` AS `sellerId` FROM `users` WHERE `users`.`role` = 'seller' ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `auctions`
--
ALTER TABLE `auctions`
  ADD PRIMARY KEY (`auctionId`),
  ADD KEY `idx_auctions_item` (`itemId`),
  ADD KEY `idx_auctions_seller` (`sellerId`);

--
-- Indexes for table `bids`
--
ALTER TABLE `bids`
  ADD PRIMARY KEY (`bidId`),
  ADD KEY `idx_bids_buyer` (`buyerId`),
  ADD KEY `idx_bids_auction` (`auctionId`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`categoryId`);

--
-- Indexes for table `images`
--
ALTER TABLE `images`
  ADD PRIMARY KEY (`imageId`),
  ADD KEY `idx_image_item` (`itemId`);

--
-- Indexes for table `items`
--
ALTER TABLE `items`
  ADD PRIMARY KEY (`itemId`),
  ADD KEY `idx_items_seller` (`sellerId`),
  ADD KEY `idx_items_category` (`categoryId`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`userId`),
  ADD UNIQUE KEY `user_email` (`userEmail`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bids`
--
ALTER TABLE `bids`
  MODIFY `bidId` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `userId` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bids`
--
ALTER TABLE `bids`
  ADD CONSTRAINT `fk_bids_buyer` FOREIGN KEY (`buyerId`) REFERENCES `users` (`userId`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
