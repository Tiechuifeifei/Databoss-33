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
  `sellerId` int(11) DEFAULT NULL,
  PRIMARY KEY (`auctionId`),
  KEY `idx_auctions_item` (`itemId`),
  KEY `idx_auctions_seller` (`sellerId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `auctions` (`auctionId`, `itemId`, `auctionStartTime`, `auctionEndTime`, `auctionStatus`, `startPrice`, `soldPrice`, `winningBidId`, `reservedPrice`, `sellerId`) VALUES
(1001, 2001, '2025-11-11 23:26:39', '2025-11-18 23:26:39', 'running', 1.00, NULL, NULL, 0.00, 1),
(1002, 2002, '2025-11-11 23:26:39', '2025-11-18 23:26:39', 'running', 5.00, NULL, NULL, 0.00, 1),
(1003, 2003, '2025-11-11 23:26:39', '2025-11-18 23:26:39', 'running', 10.00, NULL, NULL, 0.00, 1);
