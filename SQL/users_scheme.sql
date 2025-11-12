CREATE TABLE users(
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(30) UNIQUE NOT NULL,
    email VARCHAR(50) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('buyer', 'seller') NOT NULL DEFAULT 'buyer', 
    phone_number VARCHAR(15) NOT NULL,
    dob DATE NOT NULL,

    --Address
    house_no VARCHAR(15) NOT NULL,
    street VARCHAR(50) NOT NULL, 
    city VARCHAR(50) NOT NULL,
    postcode VARCHAR(10) NOT NULL
)

INSERT INTO users(username, email, password_hash, role, phone_number, dob, house_no, street, city, postcode)
VALUES('admin', 'admin@example.com', 'admin', 'buyer', '07505030708', '2002/09/22', '3', 'Princes Park Lane', 'London', 'UB3 1JS');
