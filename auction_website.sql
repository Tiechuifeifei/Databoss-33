-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- 主机： localhost
-- 生成日期： 2025-11-26 13:22:01
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
(1001, 2001, '2025-11-18 23:26:39', '2025-12-18 23:26:39', 'running', 1.00, 8.00, 3, 0.00),
(1002, 2002, '2025-11-09 23:26:39', '2025-11-18 23:26:39', 'ended', 5.00, 9.99, 2, 0.00),
(1003, 2003, '2025-11-11 23:26:39', '2025-11-18 23:26:39', 'ended', 10.00, NULL, NULL, 0.00),
(1006, 2001, '2025-11-18 12:00:00', '2025-11-20 12:00:00', 'running', 10.00, NULL, NULL, 20.00),
(1007, 2001, '2025-11-18 12:00:00', '2025-11-20 12:00:00', 'running', 10.00, NULL, NULL, 20.00),
(1008, 2001, '2025-11-18 12:00:00', '2025-11-20 12:00:00', 'running', 10.00, NULL, NULL, 20.00),
(1009, 2001, '2025-01-01 10:00:00', '2025-01-01 12:00:00', 'ended', 10.00, NULL, NULL, 5.00),
(1010, 2001, '2025-01-01 10:00:00', '2025-01-01 12:00:00', 'ended', 10.00, NULL, NULL, 5.00),
(1012, 2016, '2025-11-21 11:40:17', '2025-11-21 11:40:27', 'scheduled', 10.00, NULL, NULL, 5.00),
(1013, 2017, '2025-11-21 11:41:42', '2025-11-21 11:41:52', 'scheduled', 10.00, NULL, NULL, 5.00),
(1014, 2018, '2025-11-21 11:41:51', '2025-11-21 11:42:01', 'scheduled', 10.00, NULL, NULL, 5.00),
(1015, 2019, '2025-11-21 11:43:01', '2025-11-21 11:43:11', 'scheduled', 10.00, 30.00, 4, 5.00),
(1016, 2001, '2025-01-01 10:00:00', '2025-01-01 12:00:00', 'scheduled', 18.00, NULL, NULL, 5.00),
(1017, 2020, '2025-11-21 11:49:24', '2025-11-21 11:49:39', 'running', 10.00, NULL, NULL, 5.00),
(1018, 2001, '2025-01-01 10:00:00', '2025-01-01 12:00:00', 'scheduled', 18.00, NULL, NULL, 5.00),
(1019, 2022, '2025-11-21 14:50:37', '2025-11-22 15:55:37', 'ended', 20.00, NULL, NULL, 40.00),
(1022, 2001, '2025-11-21 16:46:00', '2025-11-29 16:46:00', 'scheduled', 1.00, NULL, NULL, 2.00),
(1023, 2001, '2025-11-22 17:09:00', '2025-11-29 17:09:00', 'scheduled', 12.00, NULL, NULL, 13.00),
(1024, 2001, '2025-11-22 17:15:00', '2025-11-29 17:15:00', 'scheduled', 1.00, NULL, NULL, 2.00),
(1025, 2028, '2025-11-22 17:18:00', '2025-11-29 17:18:00', 'running', 1.00, NULL, NULL, 2.00),
(1026, 2030, '2025-11-22 17:45:00', '2025-11-29 17:45:00', 'running', 1.00, NULL, NULL, 2.00),
(1027, 2031, '2025-11-25 14:08:00', '2025-11-29 14:09:00', 'running', 1.00, NULL, NULL, 2.00),
(1028, 2033, '2025-11-20 14:49:00', '2025-11-30 14:49:00', 'running', 1.00, NULL, NULL, 1.00),
(1029, 2034, '2025-11-02 15:02:00', '2025-11-29 15:03:00', 'running', 1.00, NULL, NULL, 1.00),
(1030, 2035, '2025-11-25 15:42:00', '2025-12-18 15:42:00', 'running', 1.00, NULL, NULL, 1.00),
(1031, 2036, '2025-11-30 23:40:00', '2025-12-06 23:40:00', 'scheduled', 3.00, NULL, NULL, 4.00),
(1032, 2037, '2025-11-29 00:52:00', '2025-12-06 04:52:00', 'scheduled', 1.00, NULL, NULL, 19.00),
(1033, 2038, '2025-11-26 03:09:00', '2025-12-01 01:09:00', 'running', 1.00, NULL, NULL, 3.00),
(1034, 2040, '2025-11-24 11:21:00', '2025-11-30 11:21:00', 'scheduled', 10.00, NULL, NULL, 80.00);

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
(2, 1002, 3, 9.99, '2025-11-12 09:00:00'),
(3, 1001, 3, 8.00, '2025-11-19 13:25:52'),
(4, 1015, 2, 30.00, '2025-11-21 11:43:21'),
(5, 1025, 4, 1.00, '2025-11-25 23:35:10'),
(6, 1026, 4, 1.00, '2025-11-25 23:38:15'),
(7, 1026, 5, 8.00, '2025-11-25 23:42:00'),
(8, 1001, 5, 1000.00, '2025-11-26 00:21:18'),
(9, 1028, 5, 4.00, '2025-11-26 00:22:01'),
(10, 1027, 4, 100.00, '2025-11-26 00:51:08'),
(11, 1001, 4, 20000.00, '2025-11-26 01:06:26'),
(12, 1028, 4, 1000.00, '2025-11-26 11:19:08');

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
(1, 'Vintage Jewellery & Watches'),
(2, 'Furniture'),
(3, 'Classic Car & Automobilia'),
(4, 'Vintage Fashion');

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
(9, 2003, 'images/item_2003_box.jpg', 0, '2025-11-14 00:40:55'),
(10, 2005, 'uploads/item_1763589506_7114.png', 0, '2025-11-19 21:58:26'),
(11, 2008, 'uploads/item_1763590858_3053.png', 0, '2025-11-19 22:20:58'),
(12, 2008, 'uploads/item_1763590893_1107.png', 0, '2025-11-19 22:21:33'),
(13, 2008, 'uploads/item_1763590893_1637.png', 1, '2025-11-19 22:21:33'),
(14, 2009, 'uploads/item_1763591165_6425.png', 1, '2025-11-19 22:26:05'),
(15, 2009, 'uploads/item_1763591172_3413.png', 1, '2025-11-19 22:26:12'),
(16, 2009, 'uploads/item_1763591190_2680.png', 1, '2025-11-19 22:26:30'),
(17, 2010, 'uploads/item_1763591438_5498.png', 1, '2025-11-19 22:30:38'),
(18, 2011, 'uploads/item_1763591620_4685.png', 1, '2025-11-19 22:33:40'),
(19, 2012, 'uploads/item_1763591703_8299.png', 1, '2025-11-19 22:35:03'),
(20, 2012, 'uploads/item_1763591712_8692.png', 1, '2025-11-19 22:35:12'),
(21, 2013, 'uploads/item_1763591822_9433.png', 1, '2025-11-19 22:37:02'),
(22, 2013, 'uploads/item_1763591827_7339.png', 1, '2025-11-19 22:37:07'),
(23, 2013, 'uploads/item_1763591834_1115.png', 1, '2025-11-19 22:37:14'),
(24, 2014, 'uploads/item_1763591989_6786.png', 1, '2025-11-19 22:39:49'),
(25, 2014, 'uploads/item_1763591999_3764.png', 0, '2025-11-19 22:39:59'),
(26, 2015, 'uploads/item_1763638158_1582.png', 1, '2025-11-20 11:29:18'),
(27, 2021, 'uploads/item_1763733873_7083.png', 1, '2025-11-21 14:04:33'),
(30, 2022, 'images/sample1.jpg', 1, '2025-11-21 14:55:37'),
(31, 2022, 'images/sample2.jpg', 0, '2025-11-21 14:55:37'),
(32, 2022, 'images/sample3.jpg', 0, '2025-11-21 14:55:37'),
(33, 2025, 'uploads/69209c9b6cf0f.png', 1, '2025-11-21 17:08:43'),
(34, 2026, 'uploads/69209d641baf4.png', 1, '2025-11-21 17:12:04'),
(35, 2027, 'uploads/69209e1f977c0.png', 1, '2025-11-21 17:15:11'),
(36, 2027, 'uploads/69209e24ab7d3.png', 0, '2025-11-21 17:15:16'),
(37, 2028, 'uploads/69209ef0c8382.png', 1, '2025-11-21 17:18:40'),
(38, 2029, 'uploads/69209f6b88a54.png', 1, '2025-11-21 17:20:43'),
(39, 2030, 'uploads/6920a5068b6c6.png', 1, '2025-11-21 17:44:38'),
(41, 2031, 'uploads/6925b862a61e7.png', 1, '2025-11-25 14:08:34'),
(42, 2032, 'uploads/6925c1b516bc0.jpg', 1, '2025-11-25 14:48:21'),
(43, 2033, 'uploads/6925c1e2955a9.png', 1, '2025-11-25 14:49:06'),
(44, 2034, 'uploads/6925c51a920d4.png', 1, '2025-11-25 15:02:50'),
(45, 2035, 'uploads/6925ce5368255.png', 1, '2025-11-25 15:42:11'),
(46, 2036, 'uploads/69263e4f35260.png', 0, '2025-11-25 23:39:59'),
(47, 2036, 'uploads/69263e5c5f7a2.png', 1, '2025-11-25 23:40:12'),
(48, 2037, 'uploads/69264f26c938f.png', 1, '2025-11-26 00:51:50'),
(49, 2038, 'uploads/6926532da0e9b.png', 1, '2025-11-26 01:09:01'),
(50, 2038, 'uploads/6926533723890.png', 0, '2025-11-26 01:09:11'),
(51, 2040, 'uploads/6926e28040c33.png', 0, '2025-11-26 11:20:32'),
(52, 2040, 'uploads/6926e287524bc.png', 1, '2025-11-26 11:20:39');

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
(2001, 'Item 2001', 'Sample item 2001', 1, 1, '2025-11-11 23:26:39', 'active', 'used'),
(2002, 'Item 2002', 'Sample item 2002', 1, 1, '2025-11-11 23:26:39', 'active', 'used'),
(2003, 'Item 2003', 'Sample item 2003', 1, 2, '2025-11-11 23:26:39', 'active', 'new'),
(2004, 'car', 'really cool ', 1, 3, '2025-11-19 21:54:02', 'active', 'used'),
(2005, 'car', 'fancy', 1, 3, '2025-11-19 21:58:20', 'active', 'new'),
(2006, 'car', 'goodgood', 1, 3, '2025-11-19 22:13:21', 'active', 'new'),
(2007, 'car', 'good', 1, 3, '2025-11-19 22:17:42', 'active', 'new'),
(2008, 'car', 'verygood', 1, 3, '2025-11-19 22:20:48', 'active', 'new'),
(2009, 'car', 'good', 1, 3, '2025-11-19 22:25:57', 'active', 'used'),
(2010, 'car', 'very nice car', 1, 3, '2025-11-19 22:30:32', 'active', 'new'),
(2011, 'car', 'good', 1, 3, '2025-11-19 22:33:32', 'active', 'new'),
(2012, 'car', 'good', 1, 3, '2025-11-19 22:34:57', 'active', 'used'),
(2013, 'car', 'very good', 1, 3, '2025-11-19 22:36:55', 'active', 'used'),
(2014, 'car', 'nice car', 1, 3, '2025-11-19 22:39:43', 'active', 'refurbished'),
(2015, 'car', 'good', 1, 3, '2025-11-20 11:29:10', 'active', 'new'),
(2016, 'EndAuction Test Item', 'Testing endAuction function', 1, 1, '2025-11-21 11:40:37', 'inactive', 'new'),
(2017, 'EndAuction Test Item', 'Testing endAuction function', 1, 1, '2025-11-21 11:42:02', 'inactive', 'new'),
(2018, 'EndAuction Test Item', 'Testing endAuction function', 1, 1, '2025-11-21 11:42:11', 'inactive', 'new'),
(2019, 'EndAuction Test Item', 'Testing endAuction function', 1, 1, '2025-11-21 11:43:21', 'sold', 'new'),
(2020, 'Test Item for refresh', 'Testing status transitions', 1, 1, '2025-11-21 11:49:29', 'active', 'new'),
(2021, 'watch', 'a nice watch', 1, 1, '2025-11-21 14:02:01', 'active', 'new'),
(2022, 'Test Item From User', 'This is a test item created by an existing user.', 1, 2, '2025-11-21 14:55:37', 'active', 'new'),
(2023, 'car', 'good car', 1, 3, '2025-11-21 17:03:11', 'active', 'new'),
(2024, 'car', '1', 1, 3, '2025-11-21 17:06:31', 'active', 'new'),
(2025, 'watch', '111', 1, 1, '2025-11-21 17:08:43', 'active', 'new'),
(2026, 'nice watch', 'goooooooood watch', 1, 1, '2025-11-21 17:12:04', 'active', 'new'),
(2027, 'watch', '123', 1, 1, '2025-11-21 17:15:03', 'active', 'refurbished'),
(2028, 'watch', 'watcccchhhhhh', 1, 1, '2025-11-21 17:18:34', 'active', 'new'),
(2029, 'watch', '1234124', 1, 1, '2025-11-21 17:20:37', 'active', 'new'),
(2030, 'watch111', 'nice watch', 1, 1, '2025-11-21 17:44:30', 'active', 'new'),
(2031, 'car', '231', 5, 3, '2025-11-25 14:08:25', 'active', 'used'),
(2032, 'watch', '12', 6, 3, '2025-11-25 14:47:48', 'active', 'used'),
(2033, 'car', '123', 6, 1, '2025-11-25 14:48:57', 'active', 'new'),
(2034, 'car', '11', 4, 3, '2025-11-25 15:02:43', 'active', 'new'),
(2035, 'car', '11', 4, 3, '2025-11-25 15:42:04', 'active', 'new'),
(2036, 'another nice car', 'carcarcar', 4, 3, '2025-11-25 23:39:44', 'inactive', 'used'),
(2037, 'car', 'carrr', 4, 3, '2025-11-26 00:51:42', 'inactive', 'used'),
(2038, 'watch', 'nice watch', 4, 1, '2025-11-26 01:08:17', 'active', 'refurbished'),
(2039, 'newc', '', 4, 1, '2025-11-26 11:20:10', 'active', 'new'),
(2040, 'newcar', 'new carrrr', 4, 3, '2025-11-26 11:20:21', 'active', 'new');

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
(1, 'Seller A', 'seller@email.com', '7e240de74fb1ed08fa08d38063f6a6a91462a815', '2025-01-01 10:00:00', 'seller', 'london', '2025-11-26', '11', '111', 'london', 'w1 3tg'),
(2, 'Buyer B', 'buyer1@email.com', '5cb138284d431abd6a053a56625ec088bfb88912', '2025-01-02 11:00:00', 'buyer', NULL, NULL, NULL, NULL, NULL, NULL),
(3, 'Buyer C', 'buyer2@email.com', 'f36b4825e5db2cf7dd2d2593b3f5c24c0311d8b2', '2025-01-03 12:00:00', 'buyer', NULL, NULL, NULL, NULL, NULL, NULL),
(4, 'xiaohua', '90750791197@qq.com', '$2y$10$jEXvufpdBeW8nIQksuROTuyqBamAa7mQAT1M0GhWeI3ysLYJsdxVa', '2025-11-25 12:29:23', 'buyer', '07858656880', '2006-03-18', '96', 'Look Lane', 'London', 'E1 6GU'),
(5, 'xiaohua111', '9077@qq.com', '$2y$10$PLpuaPeKU1CpzNL7s2mWuO60P/xEoYYHgHOupXnt/AXU3Blb2SqLe', '2025-11-25 12:55:35', 'buyer', '020730', '2006-03-07', '15', 'West Park Walk', 'Arcadia', '91007'),
(6, 'yufei', '9077@gmail.com', '$2y$10$kOy3j1Ft4QYUUCfQAX0/2uMc/U67hdOOageW87.HwrQYhX5ShPLxa', '2025-11-25 14:47:28', 'buyer', '06480', '1980-12-29', '77', 'S 6TH AVE', 'Arcadia', '28449');

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
(3, 3, 1002, '2025-11-14 00:43:07'),
(5, 5, 1031, '2025-11-26 00:31:59'),
(7, 4, 1031, '2025-11-26 00:50:46'),
(10, 4, 1032, '2025-11-26 01:07:13'),
(11, 4, 1027, '2025-11-26 11:18:14');

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
  MODIFY `auctionId` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1035;

--
-- 使用表AUTO_INCREMENT `bids`
--
ALTER TABLE `bids`
  MODIFY `bidId` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- 使用表AUTO_INCREMENT `categories`
--
ALTER TABLE `categories`
  MODIFY `categoryId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- 使用表AUTO_INCREMENT `images`
--
ALTER TABLE `images`
  MODIFY `imageId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

--
-- 使用表AUTO_INCREMENT `items`
--
ALTER TABLE `items`
  MODIFY `itemId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2041;

--
-- 使用表AUTO_INCREMENT `users`
--
ALTER TABLE `users`
  MODIFY `userId` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- 使用表AUTO_INCREMENT `watchlist`
--
ALTER TABLE `watchlist`
  MODIFY `watchId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

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