-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 23, 2025 at 12:14 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `fpbg_final`
--

-- --------------------------------------------------------

--
-- Table structure for table `inventory`
--

CREATE TABLE `inventory` (
  `product_id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `stock_quantity` int(11) NOT NULL,
  `unit_of_measure` varchar(50) NOT NULL,
  `category` varchar(100) NOT NULL,
  `cost_price` decimal(10,2) NOT NULL,
  `selling_price` decimal(10,2) NOT NULL,
  `stock_status` enum('Available','Low Stock','Out of Stock') NOT NULL DEFAULT 'Available',
  `expiration_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `product_id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `stock_quantity` int(11) NOT NULL,
  `unit_of_measure` varchar(50) NOT NULL,
  `category` varchar(100) NOT NULL,
  `cost_price` decimal(10,2) NOT NULL,
  `selling_price` decimal(10,2) NOT NULL,
  `stock_status` enum('in stock','out of stock','low stock') NOT NULL,
  `expiration_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`product_id`, `product_name`, `stock_quantity`, `unit_of_measure`, `category`, `cost_price`, `selling_price`, `stock_status`, `expiration_date`) VALUES
(4, 'Hotdog', 15, 'pcs', 'Chicken', 300.00, 450.00, '', '2025-12-31'),
(5, 'Hotdog', 15, 'pcs', 'Chicken', 300.00, 450.00, '', '2025-12-31'),
(6, 'Hotdog', 15, 'pcs', 'Chicken', 300.00, 450.00, '', '2025-12-31');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `password`) VALUES
(15, 'staff3', '$2y$10$SR62ZV5fnaFN15CVg16T7.QVRbFk5havXlMZ8ATOPIbKM8k3hLAgq'),
(16, 'staff4', '$2y$10$RqksdWTkVq8ds8XUbPOn3uYQ9/mjBizC8Rr7Lm0ojr5tLRgrOExJO'),
(17, 'eren', '$2y$10$0wsS3NTHk88BiUFx.dGUv.n0eOc1L/10wuJjoaQSTLrsHkKsiiRqC'),
(18, 'tine', '$2y$10$bRTRd85Z/JOVHcfTd7PMPuRKMFlvlfd87IE.xB9rs5faVkbB3Froq'),
(19, 'kent', '$2y$10$iiM3vvOvxbtnUKZHrrbeV..NhOGkqvy6eb1Hy9Xxyc1P0wTfrHfD.'),
(21, 'Admin', '$2y$10$DuIyJW6iS3IzMWETo31H4ucN7m67Igbs9FdT9oXl379br/GOPEgmq'),
(22, 'user20', '$2y$10$g6kHGvUeXFPa77D7LIuWHuiZzdIuHIeKA/EV6IQ3ae/G2eOJnGnki'),
(23, 'Kent_ni', '$2y$10$t0dt3.GYKi78gK9m8mhm6O9lvIXXFv3HXidO1wB2FqkRWGjDRj1O.');

-- --------------------------------------------------------

--
-- Table structure for table `user_final`
--

CREATE TABLE `user_final` (
  `user_id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('Admin','Staff') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_final`
--

INSERT INTO `user_final` (`user_id`, `username`, `password`, `role`, `created_at`) VALUES
(1, 'admin', ' $2y$10$k2Hg5gqLsVWaU5lVed0tJOT8Io/iKq71PvEIXV63k3qOvVg3f2Ox6', 'Admin', '2025-03-31 15:00:33'),
(6, 'gagni', 'gagni123', 'Admin', '2025-03-31 23:34:26'),
(13, 'sample_4', '$2y$10$X0De5/6shotLgAVYA5rsMu9t/NqiVkQAJYRyIvyPWCnlzpXNfBOiK', 'Staff', '2025-04-03 07:12:22'),
(14, 'sample_5', '$2y$10$9.fMOJ.zMEP1HJlwnjDOs.uik7gnHu78UBxs/2eyB8s1bC.v/QbnS', 'Admin', '2025-04-22 13:58:19'),
(15, 'sample_6', '$2y$10$CsaXDnn4dF/uxjHjWqSFcuaq73zl0hchnikfc4PxNZYuZ2PK3wje2', 'Admin', '2025-04-22 14:01:31');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `inventory`
--
ALTER TABLE `inventory`
  ADD PRIMARY KEY (`product_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `user_final`
--
ALTER TABLE `user_final`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `inventory`
--
ALTER TABLE `inventory`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `user_final`
--
ALTER TABLE `user_final`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
