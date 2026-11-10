-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 10, 2026 at 11:09 AM
-- Server version: 10.11.16-MariaDB
-- PHP Version: 8.3.36

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `asset_tracker`
--

-- --------------------------------------------------------

--
-- Table structure for table `assets`
--

CREATE TABLE `assets` (
  `id` int(11) NOT NULL,
  `asset_code` varchar(50) NOT NULL,
  `name` varchar(200) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `location_id` int(11) DEFAULT NULL,
  `custodian_id` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `purchase_date` date DEFAULT NULL,
  `service_date` date DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `status` enum('available','in_use','under_service','lost','disposed') DEFAULT 'available',
  `qr_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL,
  `purchase_price` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `assets`
--

INSERT INTO `assets` (`id`, `asset_code`, `name`, `category_id`, `location_id`, `custodian_id`, `description`, `purchase_date`, `service_date`, `expiry_date`, `status`, `qr_path`, `created_at`, `deleted_at`, `purchase_price`) VALUES
(1, 'AST-001', 'Dell Latitude 5420', 1, 1, 2, 'Core i5, 16GB RAM, 512GB SSD', '2023-01-15', '2023-07-15', '2026-01-15', 'in_use', NULL, '2026-11-10 03:45:33', NULL, 185000.00),
(2, 'AST-002', 'HP EliteBook 840', 1, 2, 3, 'Core i7, 16GB RAM', '2023-02-10', '2023-08-10', '2026-02-10', 'in_use', NULL, '2026-11-10 03:45:33', NULL, 220000.00),
(3, 'AST-003', 'MacBook Pro M2', 1, 4, 4, 'M2 Chip, 8GB RAM, 256GB SSD', '2023-03-05', '2023-09-05', '2026-03-05', 'available', NULL, '2026-11-10 03:45:33', NULL, 350000.00),
(4, 'AST-004', 'Samsung 24\" Monitor', 3, 1, 5, 'IPS Panel, HDMI/VGA', '2022-11-20', NULL, '2025-11-20', 'in_use', NULL, '2026-11-10 03:45:33', NULL, 45000.00),
(5, 'AST-005', 'LG 27\" 4K Monitor', 3, 4, 6, 'UltraFine Display', '2023-04-12', NULL, '2026-04-12', 'in_use', NULL, '2026-11-10 03:45:33', NULL, 85000.00),
(6, 'AST-006', 'HP LaserJet Pro', 4, 2, 7, 'Black & White Printer', '2021-05-15', '2023-05-15', '2024-05-15', 'under_service', NULL, '2026-11-10 03:45:33', NULL, 55000.00),
(7, 'AST-007', 'Cisco 24-Port Switch', 5, 5, 1, 'Gigabit Managed Switch', '2020-10-10', '2023-10-10', '2025-10-10', 'in_use', NULL, '2026-11-10 03:45:33', NULL, 75000.00),
(8, 'AST-008', 'Dell PowerEdge Server', 6, 5, 1, 'Xeon Processor, 32GB RAM', '2021-12-05', '2023-06-05', '2026-12-05', 'in_use', NULL, '2026-11-10 03:45:33', NULL, 850000.00),
(9, 'AST-009', 'Executive Office Chair', 8, 8, 9, 'Leather Ergonomic Chair', '2022-06-20', NULL, NULL, 'in_use', NULL, '2026-11-10 03:45:33', NULL, 25000.00),
(10, 'AST-010', 'iPhone 13', 9, 3, 10, 'Company Mobile Phone', '2022-11-01', NULL, '2025-11-01', 'in_use', NULL, '2026-11-10 03:45:33', NULL, 250000.00),
(11, 'AST-9BDA24', 'awdawdwad', 7, 3, 6, 'as', '2026-11-30', '2026-11-13', '2026-12-01', 'in_use', NULL, '2026-11-10 09:52:20', NULL, 0.00),
(12, 'AST-0F5EA1', 'awdawdwad', 7, 3, 6, 'as', '2026-11-30', '2026-11-13', '2026-12-01', 'in_use', NULL, '2026-11-10 09:55:03', NULL, 0.00);

-- --------------------------------------------------------

--
-- Table structure for table `asset_attachments`
--

CREATE TABLE `asset_attachments` (
  `id` int(11) NOT NULL,
  `asset_id` int(11) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `original_name` varchar(255) NOT NULL,
  `mime_type` varchar(100) DEFAULT NULL,
  `size` int(11) DEFAULT NULL,
  `uploaded_by` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `asset_attachments`
--

INSERT INTO `asset_attachments` (`id`, `asset_id`, `filename`, `original_name`, `mime_type`, `size`, `uploaded_by`, `created_at`) VALUES
(12, 12, '27388f8898b66e8282017d204fb50c92.jpeg', 'Gemini_Generated_Image.jpeg', 'image/jpeg', 203622, 1, '2026-11-10 09:55:33');

-- --------------------------------------------------------

--
-- Table structure for table `asset_history`
--

CREATE TABLE `asset_history` (
  `id` int(11) NOT NULL,
  `asset_id` int(11) NOT NULL,
  `action` varchar(50) NOT NULL,
  `from_user_id` int(11) DEFAULT NULL,
  `to_user_id` int(11) DEFAULT NULL,
  `from_location_id` int(11) DEFAULT NULL,
  `to_location_id` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `performed_by` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `asset_history`
--

INSERT INTO `asset_history` (`id`, `asset_id`, `action`, `from_user_id`, `to_user_id`, `from_location_id`, `to_location_id`, `notes`, `performed_by`, `created_at`) VALUES
(1, 1, 'created', NULL, 2, NULL, 1, 'Initial registration', 1, '2024-01-15 04:30:00'),
(2, 2, 'created', NULL, 3, NULL, 2, 'Initial registration', 1, '2024-01-16 06:00:00'),
(3, 3, 'created', NULL, 4, NULL, 4, 'Initial registration', 1, '2024-01-17 03:45:00'),
(4, 4, 'checked_out', NULL, 5, 1, 1, 'Assigned to user', 1, '2024-01-18 08:30:00'),
(5, 5, 'checked_out', NULL, 6, 4, 4, 'Assigned to user', 1, '2024-01-19 04:50:00'),
(6, 6, 'maintenance', 7, 7, 2, 2, 'Sent for repair', 1, '2024-01-20 02:30:00'),
(7, 7, 'transferred', 8, 9, 5, 7, 'Moved to conference room', 1, '2024-01-21 10:00:00'),
(8, 8, 'created', NULL, 1, NULL, 5, 'Initial registration', 1, '2024-01-22 03:30:00'),
(9, 9, 'transferred', 10, 11, 6, 7, 'Moved to meeting room', 1, '2024-01-23 04:30:00'),
(10, 10, 'checked_out', NULL, 10, 3, 3, 'Assigned to user', 1, '2024-01-24 04:00:00'),
(11, 11, 'created', NULL, NULL, NULL, NULL, 'Asset created', 1, '2026-11-10 09:52:20'),
(12, 12, 'created', NULL, NULL, NULL, NULL, 'Asset created', 1, '2026-11-10 09:55:03');

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(50) NOT NULL,
  `details` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `audit_logs`
--

INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `details`, `ip_address`, `created_at`) VALUES
(1, 1, 'Login', 'User logged in successfully', '127.0.0.1', '2024-02-01 02:30:00'),
(2, 1, 'Asset Create', 'Created asset AST-001', '127.0.0.1', '2024-02-01 02:35:00'),
(3, 2, 'Login', 'User logged in successfully', '192.168.1.10', '2024-02-01 03:00:00'),
(4, 2, 'Asset View', 'Viewed asset AST-001', '192.168.1.10', '2024-02-01 03:05:00'),
(5, 3, 'Login', 'User logged in successfully', '192.168.1.11', '2024-02-01 03:30:00'),
(6, 3, 'Asset Update', 'Updated asset AST-002', '192.168.1.11', '2024-02-01 03:45:00'),
(7, 1, 'Settings Update', 'Changed asset prefix to AST', '127.0.0.1', '2024-02-01 04:30:00'),
(8, 4, 'Login', 'User logged in successfully', '192.168.1.12', '2024-02-01 05:00:00'),
(9, 1, 'User Create', 'Created new user Tharindu', '127.0.0.1', '2024-02-01 05:30:00'),
(10, 5, 'Login', 'User logged in successfully', '192.168.1.13', '2024-02-01 06:00:00'),
(11, NULL, 'Login Failed', 'Failed login attempt for admin@example.com', '::1', '2026-11-10 09:16:02'),
(12, NULL, 'Login Failed', 'Failed login attempt for admin@example.com', '::1', '2026-11-10 09:16:38'),
(13, NULL, 'Login Failed', 'Failed login attempt for admin@gmail.com', '::1', '2026-11-10 09:17:52'),
(14, NULL, 'Login Failed', 'Failed login attempt for admin@gmail.com', '::1', '2026-11-10 09:18:00'),
(15, NULL, 'Login Failed', 'Failed login attempt for admin@example.com', '::1', '2026-11-10 09:18:42'),
(16, NULL, 'Login Failed', 'Failed login attempt for admin@example.com', '::1', '2026-11-10 09:19:14'),
(17, NULL, 'Login Failed', 'Failed login attempt for admin@example.com', '::1', '2026-11-10 09:19:18'),
(18, NULL, 'Login Failed', 'Failed login attempt for admin@gmail.com', '::1', '2026-11-10 09:19:36'),
(19, NULL, 'Login Failed', 'Failed login attempt for kasun@example.com', '::1', '2026-11-10 09:20:33'),
(20, NULL, 'Login Failed', 'Failed login attempt for kasun@example.com', '::1', '2026-11-10 09:20:50'),
(21, NULL, 'Login Failed', 'Failed login attempt for admin@gmail.com', '::1', '2026-11-10 09:25:44'),
(22, NULL, 'Login Failed', 'Failed login attempt for admin@gmail.com', '::1', '2026-11-10 09:25:49'),
(23, NULL, 'Login Failed', 'Failed login attempt for admin@gmail.com', '::1', '2026-11-10 09:28:49'),
(24, NULL, 'Login Failed', 'Failed login attempt for admin@gmail.com', '::1', '2026-11-10 09:29:04'),
(25, NULL, 'Login Failed', 'Failed login attempt for admin@gmail.com', '::1', '2026-11-10 09:29:11'),
(26, 1, 'Login', 'User logged in successfully', '::1', '2026-11-10 09:41:20'),
(27, 1, 'Asset Create', 'Created asset AST-9BDA24', '::1', '2026-11-10 09:52:20'),
(28, 1, 'Asset Create', 'Created asset AST-0F5EA1', '::1', '2026-11-10 09:55:03');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`) VALUES
(1, 'Laptops'),
(2, 'Desktop Computers'),
(3, 'Monitors'),
(4, 'Printers'),
(5, 'Networking'),
(6, 'Servers'),
(7, 'Computer Accessories'),
(8, 'Office Furniture'),
(9, 'Mobile Devices'),
(10, 'Software Licenses');

-- --------------------------------------------------------

--
-- Table structure for table `locations`
--

CREATE TABLE `locations` (
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `locations`
--

INSERT INTO `locations` (`id`, `name`) VALUES
(1, 'IT Department'),
(2, 'HR Department'),
(3, 'Finance Department'),
(4, 'Marketing Department'),
(5, 'Server Room'),
(6, 'Reception Area'),
(7, 'Meeting Room A'),
(8, 'Director Office'),
(9, 'Main Store Room'),
(10, 'Branch Office');

-- --------------------------------------------------------

--
-- Table structure for table `login_attempts`
--

CREATE TABLE `login_attempts` (
  `id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `success` tinyint(1) DEFAULT 0,
  `ip` varchar(45) DEFAULT NULL,
  `attempted_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `login_attempts`
--

INSERT INTO `login_attempts` (`id`, `email`, `success`, `ip`, `attempted_at`) VALUES
(26, 'admin@gmail.com', 1, '::1', '2026-11-10 09:41:20');

-- --------------------------------------------------------

--
-- Table structure for table `maintenance_logs`
--

CREATE TABLE `maintenance_logs` (
  `id` int(11) NOT NULL,
  `asset_id` int(11) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `cost` decimal(10,2) DEFAULT 0.00,
  `service_date` date DEFAULT NULL,
  `next_service_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `maintenance_logs`
--

INSERT INTO `maintenance_logs` (`id`, `asset_id`, `description`, `cost`, `service_date`, `next_service_date`, `created_at`) VALUES
(1, 1, 'Screen replacement', 35000.00, '2023-07-15', '2024-01-15', '2026-11-10 03:45:33'),
(2, 2, 'Keyboard replacement', 15000.00, '2023-08-10', '2024-02-10', '2026-11-10 03:45:33'),
(3, 6, 'Toner cartridge replaced', 8000.00, '2023-05-15', '2023-11-15', '2026-11-10 03:45:33'),
(4, 6, 'Paper jam sensor cleaned', 2500.00, '2023-02-20', '2023-08-20', '2026-11-10 03:45:33'),
(5, 7, 'Firmware update', 0.00, '2023-10-10', '2024-04-10', '2026-11-10 03:45:33'),
(6, 8, 'Hard drive replaced (RAID)', 45000.00, '2023-06-05', '2023-12-05', '2026-11-10 03:45:33'),
(7, 8, 'Battery replaced', 18000.00, '2023-09-15', '2024-09-15', '2026-11-10 03:45:33'),
(8, 3, 'Battery cycle check', 0.00, '2023-09-05', '2024-03-05', '2026-11-10 03:45:33'),
(9, 5, 'Dead pixel check', 0.00, '2023-04-12', '2024-04-12', '2026-11-10 03:45:33'),
(10, 10, 'Screen protector replaced', 2500.00, '2023-11-01', '2024-05-01', '2026-11-10 03:45:33');

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `setting_key` varchar(50) NOT NULL,
  `setting_value` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`setting_key`, `setting_value`) VALUES
('asset_code_prefix', 'AST'),
('company_address', '123 Main Street, Colombo'),
('company_email', 'info@mycompany.com'),
('company_name', 'My Company (Pvt) Ltd'),
('company_phone', '+94 11 234 5678'),
('currency_symbol', 'Rs.'),
('default_language', 'en'),
('items_per_page', '20'),
('maintenance_alert_days', '30'),
('system_version', '1.0.0');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','staff') DEFAULT 'staff',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `last_login` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `created_at`, `last_login`) VALUES
(1, 'Admin User', 'admin@gmail.com', '$2y$10$IPG66dFP8OvpvjWD3HKlNufvQ.7ngaMW4Es737D.iBdxIs2rE7EMy', 'admin', '2024-01-01 02:30:00', '2026-11-10 15:11:20'),
(2, 'Kasun Perera', 'kasun@example.com', '$2y$10$e0MYzXyjpJS7Pd0RVvHwHe1Hl6WyOAqEYCq1Y5.LQzK0lSf5HvCaO', 'staff', '2024-01-02 03:30:00', '2026-11-10 09:15:33'),
(3, 'Nimal Silva', 'nimal@example.com', '$2y$10$e0MYzXyjpJS7Pd0RVvHwHe1Hl6WyOAqEYCq1Y5.LQzK0lSf5HvCaO', 'staff', '2024-01-03 04:30:00', '2026-11-10 09:15:33'),
(4, 'Amali Fernando', 'amali@example.com', '$2y$10$e0MYzXyjpJS7Pd0RVvHwHe1Hl6WyOAqEYCq1Y5.LQzK0lSf5HvCaO', 'admin', '2024-01-04 05:30:00', '2026-11-10 09:15:33'),
(5, 'Sunil Perera', 'sunil@example.com', '$2y$10$e0MYzXyjpJS7Pd0RVvHwHe1Hl6WyOAqEYCq1Y5.LQzK0lSf5HvCaO', 'staff', '2024-01-05 06:30:00', NULL),
(6, 'Chamara Silva', 'chamara@example.com', '$2y$10$e0MYzXyjpJS7Pd0RVvHwHe1Hl6WyOAqEYCq1Y5.LQzK0lSf5HvCaO', 'staff', '2024-01-06 07:30:00', '2026-11-10 09:15:33'),
(7, 'Dilini Gamage', 'dilini@example.com', '$2y$10$e0MYzXyjpJS7Pd0RVvHwHe1Hl6WyOAqEYCq1Y5.LQzK0lSf5HvCaO', 'staff', '2024-01-07 08:30:00', NULL),
(8, 'Ruwan Kumara', 'ruwan@example.com', '$2y$10$e0MYzXyjpJS7Pd0RVvHwHe1Hl6WyOAqEYCq1Y5.LQzK0lSf5HvCaO', 'staff', '2024-01-08 09:30:00', '2026-11-10 09:15:33'),
(9, 'Sanduni Dias', 'sanduni@example.com', '$2y$10$e0MYzXyjpJS7Pd0RVvHwHe1Hl6WyOAqEYCq1Y5.LQzK0lSf5HvCaO', 'admin', '2024-01-09 10:30:00', '2026-11-10 09:15:33'),
(10, 'Tharindu Bandara', 'tharindu@example.com', '$2y$10$e0MYzXyjpJS7Pd0RVvHwHe1Hl6WyOAqEYCq1Y5.LQzK0lSf5HvCaO', 'staff', '2024-01-10 11:30:00', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `assets`
--
ALTER TABLE `assets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `asset_code` (`asset_code`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `location_id` (`location_id`),
  ADD KEY `custodian_id` (`custodian_id`);

--
-- Indexes for table `asset_attachments`
--
ALTER TABLE `asset_attachments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `asset_id` (`asset_id`);

--
-- Indexes for table `asset_history`
--
ALTER TABLE `asset_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `asset_id` (`asset_id`);

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `locations`
--
ALTER TABLE `locations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `login_attempts`
--
ALTER TABLE `login_attempts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_email_time` (`email`,`attempted_at`);

--
-- Indexes for table `maintenance_logs`
--
ALTER TABLE `maintenance_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `asset_id` (`asset_id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`setting_key`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `assets`
--
ALTER TABLE `assets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `asset_attachments`
--
ALTER TABLE `asset_attachments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `asset_history`
--
ALTER TABLE `asset_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `locations`
--
ALTER TABLE `locations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `login_attempts`
--
ALTER TABLE `login_attempts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `maintenance_logs`
--
ALTER TABLE `maintenance_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `assets`
--
ALTER TABLE `assets`
  ADD CONSTRAINT `assets_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `assets_ibfk_2` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `assets_ibfk_3` FOREIGN KEY (`custodian_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `asset_attachments`
--
ALTER TABLE `asset_attachments`
  ADD CONSTRAINT `asset_attachments_ibfk_1` FOREIGN KEY (`asset_id`) REFERENCES `assets` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `asset_history`
--
ALTER TABLE `asset_history`
  ADD CONSTRAINT `asset_history_ibfk_1` FOREIGN KEY (`asset_id`) REFERENCES `assets` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `audit_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `maintenance_logs`
--
ALTER TABLE `maintenance_logs`
  ADD CONSTRAINT `maintenance_logs_ibfk_1` FOREIGN KEY (`asset_id`) REFERENCES `assets` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
