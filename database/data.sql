drop database IF EXISTS sanya;


create database IF NOT EXISTS sanya_store;
USE sanya_store;



Create table astra(
    id INT AUTO_INCREMENT PRIMARY KEY,
    astra_id VARCHAR(50) UNIQUE NOT NULL,
    phone VARCHAR(20) UNIQUE NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    pass VARCHAR(255) UNIQUE NOT NULL,
    reg_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)


CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    store_id VARCHAR(100) UNIQUE NOT NULL,
    till_number VARCHAR(20) UNIQUE NOT NULL,
    phone VARCHAR(20) NOT NULL,
    email VARCHAR(255),
    password VARCHAR(255) NOT NULL,
    store_name VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);



CREATE TABLE assistants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    store_id VARCHAR(100) NOT NULL,
    fname VARCHAR(100) NOT NULL,
    lname VARCHAR(100) NOT NULL,
    role ENUM('Manager', 'Cashier', 'Salesperson') NOT NULL,
    email VARCHAR(255),
    phone VARCHAR(20),
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (store_id) REFERENCES users(store_id) ON DELETE CASCADE
);




CREATE TABLE products (
    sn INT AUTO_INCREMENT PRIMARY KEY,
    store_id VARCHAR(50) NOT NULL,
    product_name VARCHAR(100) NOT NULL,
    category VARCHAR(50) NOT NULL,
    description TEXT,
    buy_price DECIMAL(10,2) NOT NULL,
    sell_price DECIMAL(10,2) NOT NULL,
    quantity INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (store_id) REFERENCES users(store_id) ON DELETE CASCADE
);



-- 
CREATE TABLE sales (
    id INT AUTO_INCREMENT PRIMARY KEY,
    store_id VARCHAR(100) NOT NULL,
    assistant_id INT,
    total_amount DECIMAL(10,2) NOT NULL,
    amount_paid DECIMAL(10,2) NOT NULL,
    change_given DECIMAL(10,2) NOT NULL,
    payment_method ENUM('Cash', 'Mpesa', 'Card') DEFAULT 'Cash',
    mpesa_number VARCHAR(20) DEFAULT NULL,
    transaction_id VARCHAR(100) DEFAULT NULL,
    sale_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (store_id) REFERENCES users(store_id) ON DELETE CASCADE,
    FOREIGN KEY (assistant_id) REFERENCES assistants(id) ON DELETE SET NULL
);


-- 
CREATE TABLE sale_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sale_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (sale_id) REFERENCES sales(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(sn) ON DELETE CASCADE
);


ALTER TABLE sales 
MODIFY amount_paid DECIMAL(10,2) NULL,
MODIFY change_given DECIMAL(10,2) NULL;
