-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 20, 2025 at 03:17 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

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
-- Table structure for table `stock_transactions`
--

CREATE TABLE `stock_transactions` (
  `id` int(11) NOT NULL,
  `transaction_id` varchar(50) NOT NULL,
  `product_id` varchar(50) NOT NULL,
  `quantity` decimal(10,2) NOT NULL,
  `transaction_type` enum('stock_in','stock_out') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `stock_transactions`
--

INSERT INTO `stock_transactions` (`id`, `transaction_id`, `product_id`, `quantity`, `transaction_type`, `created_at`, `updated_at`) VALUES
(1, 'STKN682a914b557d09.81093663', '20251028', 1.00, 'stock_in', '2025-05-19 02:02:51', '2025-05-19 02:02:51'),
(2, '682a91854c353', '20251028', 1.00, 'stock_out', '2025-05-19 02:03:49', '2025-05-19 02:03:49'),
(3, '682a921eca943', '20251028', 20.00, 'stock_out', '2025-05-19 02:06:22', '2025-05-19 02:06:22'),
(4, 'STKN682a9a6092f151.89358722', '20251029', 10.00, 'stock_in', '2025-05-19 02:41:36', '2025-05-19 02:41:36'),
(5, 'STKN682a9de1e6dbb1.79360108', '20251030', 20.00, 'stock_in', '2025-05-19 02:56:33', '2025-05-19 02:56:33'),
(6, 'STKN682aa22ab83ab4.93974714', '20251031', 1.00, 'stock_in', '2025-05-19 03:14:50', '2025-05-19 03:14:50'),
(7, '682b3a86c10e8', '20251031', 1.00, 'stock_out', '2025-05-19 14:04:54', '2025-05-19 14:04:54'),
(8, 'STKN682b421dc7b228.99512229', '20251032', 120.00, 'stock_in', '2025-05-19 14:37:17', '2025-05-19 14:37:17'),
(9, '682b487074aae', '20251032', 1.00, 'stock_out', '2025-05-19 15:04:16', '2025-05-19 15:04:16'),
(10, 'STKN682c6bfb3ca2f1.81383889', '20251038', 1.00, 'stock_in', '2025-05-20 11:48:11', '2025-05-20 11:48:11'),
(11, '682c6c0e6dca7', '20251038', 1.00, 'stock_out', '2025-05-20 11:48:30', '2025-05-20 11:48:30');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `stock_transactions`
--
ALTER TABLE `stock_transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `transaction_id` (`transaction_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `stock_transactions`
--
ALTER TABLE `stock_transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `stock_transactions`
--
ALTER TABLE `stock_transactions`
  ADD CONSTRAINT `stock_transactions_ibfk_1` FOREIGN KEY (`transaction_id`) REFERENCES `transactions` (`transaction_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
