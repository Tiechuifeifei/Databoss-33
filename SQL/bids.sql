CREATE TABLE `bids` (
  `bidId` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `auctionId` int(10) UNSIGNED NOT NULL,
  `buyerId` int(10) UNSIGNED NOT NULL,
  `bidPrice` decimal(10,2) NOT NULL,
  `bidTime` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`bidId`),
  KEY `idx_bids_auction` (`auctionId`),
  KEY `idx_bids_buyer` (`buyerId`),

  CONSTRAINT `fk_bids_buyer`
    FOREIGN KEY (`buyerId`)
    REFERENCES `users` (`userId`)
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
