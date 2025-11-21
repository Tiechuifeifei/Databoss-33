CREATE TABLE `users` (
    `userId` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `userUsername` VARCHAR(30) UNIQUE NOT NULL,
    `userEmail` VARCHAR(50) UNIQUE NOT NULL,
    `userPpassword` VARCHAR(255) NOT NULL,
    `userRrole` ENUM('buyer', 'seller') NOT NULL DEFAULT 'buyer', 
    `userPhoneNumber` VARCHAR(15) NOT NULL,
    `userDob` DATE NOT NULL,

    -- Address
    `userHouseNo` VARCHAR(15) NOT NULL,
    `UserStreet` VARCHAR(50) NOT NULL, 
    `userCity` VARCHAR(50) NOT NULL,
    `userPostcode` VARCHAR(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
