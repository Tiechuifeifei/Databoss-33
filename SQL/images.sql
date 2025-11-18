CREATE TABLE `images` (
  `imageId` int(11) NOT NULL,
  `itemId` int(11) DEFAULT NULL,
  `imageUrl` varchar(200) DEFAULT NULL,
  `isPrimary` tinyint(1) DEFAULT NULL,
  `uploadedAt` datetime DEFAULT NULL,
  PRIMARY KEY (`imageId`),
  KEY `idx_image_item` (`itemId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
