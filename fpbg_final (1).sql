-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 28, 2025 at 09:26 AM
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
  `brand_name` varchar(100) DEFAULT NULL,
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
  `password` varchar(255) NOT NULL,
  `role` enum('admin','staff') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `password`, `role`) VALUES
(36, 'mama2', '$2y$10$e.o.BnF1fmO4xWU.37UqcuHaVTWgfbY4EpkXt6gRE3x.rBmFXUED.', 'admin'),
(37, 'iloveyou', '$2y$10$9/zDrQrtu2dCxnuf8pTCwOMxVaWi9L2pVY0kaydJBVY8D65a23EWC', 'admin'),
(38, 'eee', '$2y$10$vdz8DjkezGSuOAw1YIzSh.nOi5QBEP0cXCNxcaQMSOm.faumJOqLK', 'staff'),
(39, 'admin2', '$2y$10$oZvKiUYCj8wCUsDSoGwxGuK7UPQFUAaWty45ZNazOKABsBoy6Ky5K', 'admin');

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
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
