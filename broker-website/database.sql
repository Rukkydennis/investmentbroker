-- Database Schema for Futura Crypto Brokerage Platform

-- 1. Users Table
CREATE TABLE `users` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `full_name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `password_hash` VARCHAR(255) NOT NULL,
    `balance` DECIMAL(15, 2) DEFAULT 0.00,
    `role` ENUM('user', 'admin') DEFAULT 'user',
    `status` ENUM('active', 'banned') DEFAULT 'active',
    `referrer_id` INT DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`referrer_id`) REFERENCES `users`(`id`)
);

-- 2. Investment Plans Table
CREATE TABLE `plans` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `name` VARCHAR(50) NOT NULL,
    `roi_percent` DECIMAL(5, 2) NOT NULL, -- Daily ROI
    `duration_days` INT NOT NULL,
    `min_amount` DECIMAL(15, 2) NOT NULL,
    `max_amount` DECIMAL(15, 2) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 3. User Investments Table
CREATE TABLE `investments` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `user_id` INT NOT NULL,
    `plan_id` INT NOT NULL,
    `amount` DECIMAL(15, 2) NOT NULL,
    `total_profit` DECIMAL(15, 2) DEFAULT 0.00,
    `daily_profit` DECIMAL(15, 2) NOT NULL,
    `start_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `end_date` TIMESTAMP NULL,
    `next_payout` TIMESTAMP NULL,
    `status` ENUM('running', 'completed', 'cancelled') DEFAULT 'running',
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`),
    FOREIGN KEY (`plan_id`) REFERENCES `plans`(`id`)
);

-- 4. Deposits Table
CREATE TABLE `deposits` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `transaction_id` VARCHAR(50) UNIQUE NULL, -- Generated or User Provided
    `user_id` INT NOT NULL,
    `amount` DECIMAL(15, 2) NOT NULL,
    `method` VARCHAR(50) NOT NULL, -- e.g., 'Bitcoin', 'USDT'
    `status` ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    `proof_image` VARCHAR(255) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`)
);

-- 5. Withdrawals Table
CREATE TABLE `withdrawals` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `user_id` INT NOT NULL,
    `amount` DECIMAL(15, 2) NOT NULL,
    `method` VARCHAR(50) NOT NULL,
    `wallet_address` VARCHAR(255) NOT NULL,
    `status` ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`)
);

-- 6. Support Tickets Table
CREATE TABLE `tickets` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `user_id` INT NOT NULL,
    `subject` VARCHAR(255) NOT NULL,
    `priority` ENUM('low', 'medium', 'high') DEFAULT 'low',
    `status` ENUM('open', 'answered', 'closed') DEFAULT 'open',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`)
);

-- 7. Ticket Messages Table (Chat)
CREATE TABLE `ticket_messages` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `ticket_id` INT NOT NULL,
    `sender_type` ENUM('user', 'admin') NOT NULL,
    `message` TEXT NOT NULL,
    `attachment` VARCHAR(255) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`ticket_id`) REFERENCES `tickets`(`id`)
);

-- SEED DATA (Dummy Data for Testing)

-- Admin User (Password: admin123)
INSERT INTO `users` (`full_name`, `email`, `password_hash`, `role`, `balance`) 
VALUES ('Super Admin', 'admin@futura.io', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 0.00);

-- User (Password: password)
INSERT INTO `users` (`full_name`, `email`, `password_hash`, `role`, `balance`) 
VALUES ('Tony Stark', 'tony@stark.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', 15000.00);

-- Default Plans
INSERT INTO `plans` (`name`, `roi_percent`, `duration_days`, `min_amount`, `max_amount`) VALUES 
('Starter Plan', 3.00, 30, 100.00, 1000.00),
('Pro Plan', 5.00, 45, 1000.00, 10000.00),
('Elite Plan', 8.00, 60, 10000.00, 1000000.00);

-- Sample Investment
INSERT INTO `investments` (`user_id`, `plan_id`, `amount`, `daily_profit`, `next_payout`) 
VALUES (2, 2, 5000.00, 250.00, DATE_ADD(NOW(), INTERVAL 1 DAY));

-- 8. Admin Wallets Table (Dynamic Deposit Addresses)
CREATE TABLE `admin_wallets` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `network` VARCHAR(50) NOT NULL, -- e.g., 'Bitcoin', 'USDT (TRC20)'
    `address` VARCHAR(255) NOT NULL,
    `qr_code` VARCHAR(255) NULL, -- Path to image
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Seed Wallets
INSERT INTO `admin_wallets` (`network`, `address`) VALUES 
('Bitcoin (BTC)', 'bc1qxy2kgdygjrsqtzq2n0yrf2493p83kkfjhx0wlh'),
('Ethereum (ETH)', '0x71C7656EC7ab88b098defB751B7401B5f6d8976F'),
('USDT (TRC20)', 'TXjp3Z5s234234sdfsdf234234');
