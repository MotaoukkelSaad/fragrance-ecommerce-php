-- =====================================================
-- db_fragrance - COMPLETE DATABASE SCHEMA
-- =====================================================
CREATE DATABASE db_fragrance;

USE db_fragrance;

-- Users table with authentication
CREATE TABLE `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) UNIQUE NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `phone` VARCHAR(20),
  `role` ENUM('user', 'admin') DEFAULT 'user',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Products table
CREATE TABLE `products` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(150) NOT NULL,
  `price` DECIMAL(10, 2) NOT NULL,
  `gender` ENUM('homme', 'femme', 'unisex') NOT NULL,
  `image` VARCHAR(255),
  `description` TEXT,
  `stock` INT DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_gender (gender),
  INDEX idx_price (price)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Orders table
CREATE TABLE `orders` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL,
  `phone` VARCHAR(20) NOT NULL,
  `address` TEXT NOT NULL,
  `city` VARCHAR(100) NOT NULL,
  `subtotal` DECIMAL(10, 2) NOT NULL,
  `shipping_type` ENUM('gratuite', 'express') DEFAULT 'gratuite',
  `shipping_cost` DECIMAL(10, 2) DEFAULT 0,
  `total` DECIMAL(10, 2) NOT NULL,
  `status` ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  INDEX idx_user_id (user_id),
  INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Order items table
CREATE TABLE `order_items` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `order_id` INT NOT NULL,
  `product_id` INT NOT NULL,
  `product_name` VARCHAR(150) NOT NULL,
  `quantity` INT NOT NULL,
  `price_at_purchase` DECIMAL(10, 2) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`),
  INDEX idx_order_id (order_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert sample products
INSERT INTO `products` (name, price, gender, image, description, stock) VALUES 
('Bleu de Chanel', 1200.00, 'homme', 'images/products/bleu-chanel.png', 'Elegant men perfume with fresh notes', 50),
('La Vie Est Belle', 950.00, 'femme', 'images/products/la-vie.png', 'Beautiful women perfume with floral notes', 40),
('Sauvage Dior', 1100.00, 'homme', 'images/products/sauvage.png', 'Fresh masculine scent', 35),
('Black Opium', 1050.00, 'femme', 'images/products/black-opium.png', 'Luxurious women fragrance', 45),
('One Million', 1000.00, 'homme', 'images/products/one-million.png', 'Sophisticated men cologne', 30),
('Good Girl', 1150.00, 'femme', 'images/products/good-girl.png', 'Intense and bold fragrance for women', 38),
('Acqua di Gio', 850.00, 'homme', 'images/products/acqua-gio.png', 'Fresh aquatic fragrance', 55),
('Libre', 1250.00, 'femme', 'images/products/libre.png', 'Exotic and sensual women perfume', 32),
('Aventus', 2000.00, 'homme', 'images/products/aventus.png', 'Premium men fragrance', 20),
('Baccarat Rouge', 2500.00, 'femme', 'images/products/baccarat-rouge.png', 'Luxury women perfume', 15),
('Invictus', 900.00, 'homme', 'images/products/invictus.png', 'Powerful men fragrance', 42),
('Miss Dior', 1000.00, 'femme', 'images/products/miss-dior.png', 'Elegant women perfume', 48);
