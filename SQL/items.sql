CREATE TABLE `items` (
  `itemId` int(11) NOT NULL,
  `itemName` varchar(50) DEFAULT NULL,
  `itemDescription` varchar(500) DEFAULT NULL,
  `sellerId` int(11) DEFAULT NULL,
  `categoryId` int(11) DEFAULT NULL,
  `itemUploadTime` datetime DEFAULT NULL,
  `itemStatus` varchar(50) DEFAULT NULL,
  `itemCondition` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`itemId`),
  KEY `idx_items_seller` (`sellerId`),
  KEY `idx_items_category` (`categoryId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `items` (`itemId`, `itemName`, `itemDescription`, `sellerId`, `categoryId`, `itemUploadTime`, `itemStatus`, `itemCondition`) VALUES
(2001, 'Item 2001', 'Sample item 2001', 1, 10, '2025-11-11 23:26:39', 'active', 'used'),
(2002, 'Item 2002', 'Sample item 2002', 1, 10, '2025-11-11 23:26:39', 'active', 'used'),
(2003, 'Item 2003', 'Sample item 2003', 1, 20, '2025-11-11 23:26:39', 'active', 'new');
