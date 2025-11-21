CREATE TABLE `users` (
    `userId` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `userName` VARCHAR(30) UNIQUE NOT NULL,
    `userEmail` VARCHAR(50) UNIQUE NOT NULL,
    `userPassword` VARCHAR(255) NOT NULL,
    `userRole` ENUM('buyer','seller') NOT NULL DEFAULT 'buyer',
    `userPhoneNumber` VARCHAR(15) NOT NULL,
    `userDob` DATE NOT NULL,

    -- Address
    `userHouseNo` VARCHAR(15) NOT NULL,
    `userStreet` VARCHAR(50) NOT NULL, 
    `userCity` VARCHAR(50) NOT NULL,
    `userPostcode` VARCHAR(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
