CREATE TABLE `users` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(30) UNIQUE NOT NULL,
    `email` VARCHAR(50) UNIQUE NOT NULL,
    `password_hash` VARCHAR(255) NOT NULL,
    `role` ENUM('buyer', 'seller') NOT NULL DEFAULT 'buyer', 
    `phone_number` VARCHAR(15) NOT NULL,
    `dob` DATE NOT NULL,

    -- Address
    `house_no` VARCHAR(15) NOT NULL,
    `street` VARCHAR(50) NOT NULL, 
    `city` VARCHAR(50) NOT NULL,
    `postcode` VARCHAR(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
