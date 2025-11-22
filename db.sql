-- Create database for SmartCafe project
CREATE DATABASE IF NOT EXISTS smartcafe;
USE smartcafe;

-- Table for menu items
CREATE TABLE menu (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    price INT NOT NULL,
    category ENUM('makanan', 'minuman', 'dessert') NOT NULL,
    image VARCHAR(255) DEFAULT NULL,
    description TEXT DEFAULT NULL
);

-- Table for tables
    CREATE TABLE tables (
        id INT AUTO_INCREMENT PRIMARY KEY,
        table_number VARCHAR(10) NOT NULL UNIQUE
    );

-- Table for orders
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    total INT NOT NULL,
    table_id INT DEFAULT NULL,
    status ENUM('pending', 'completed', 'cancelled') DEFAULT 'pending',
    FOREIGN KEY (table_id) REFERENCES tables(id)
);

-- Table for order items (junction table for orders and menu)
CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    menu_id INT NOT NULL,
    quantity INT NOT NULL,
    price INT NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (menu_id) REFERENCES menu(id) ON DELETE CASCADE
);

-- Insert sample menu data
INSERT INTO menu (name, price, category, image, description) VALUES
('Latte', 18000, 'minuman', 'assets/food1.jpg', 'Kopi latte klasik'),
('Mocha', 22000, 'minuman', 'assets/food2.jpg', 'Kopi mocha dengan coklat'),
('Americano', 15000, 'minuman', 'assets/food3.jpg', 'Kopi americano hitam'),
('Nasi Goreng', 25000, 'makanan', 'assets/food1.jpg', 'Nasi goreng spesial'),
('Ayam Bakar', 30000, 'makanan', 'assets/food2.jpg', 'Ayam bakar dengan bumbu rempah'),
('Es Krim', 15000, 'dessert', 'assets/food3.jpg', 'Es krim vanilla');

-- Insert sample table data
INSERT INTO tables (table_number) VALUES
('T01'), ('T02'), ('T03'), ('T04'), ('T05');
