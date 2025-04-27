-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 26, 2025 at 04:24 PM
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
(15, 'staff3', '$2y$10$SR62ZV5fnaFN15CVg16T7.QVRbFk5havXlMZ8ATOPIbKM8k3hLAgq', NULL),
(16, 'staff4', '$2y$10$RqksdWTkVq8ds8XUbPOn3uYQ9/mjBizC8Rr7Lm0ojr5tLRgrOExJO', NULL),
(17, 'eren', '$2y$10$0wsS3NTHk88BiUFx.dGUv.n0eOc1L/10wuJjoaQSTLrsHkKsiiRqC', NULL),
(18, 'tine', '$2y$10$bRTRd85Z/JOVHcfTd7PMPuRKMFlvlfd87IE.xB9rs5faVkbB3Froq', NULL),
(19, 'kent', '$2y$10$iiM3vvOvxbtnUKZHrrbeV..NhOGkqvy6eb1Hy9Xxyc1P0wTfrHfD.', NULL),
(21, 'Admin', '$2y$10$DuIyJW6iS3IzMWETo31H4ucN7m67Igbs9FdT9oXl379br/GOPEgmq', NULL),
(22, 'user20', '$2y$10$g6kHGvUeXFPa77D7LIuWHuiZzdIuHIeKA/EV6IQ3ae/G2eOJnGnki', NULL),
(23, 'Kent_ni', '$2y$10$t0dt3.GYKi78gK9m8mhm6O9lvIXXFv3HXidO1wB2FqkRWGjDRj1O.', NULL),
(24, 'enzo', '$2y$10$J6YLs78kOgdrDecoSVO7W.UezXGdDy4CRenLIIAqr7tWUuLinRA0O', NULL),
(25, 'wowowow', '$2y$10$66txXN7pz0Tofq84S/soj.0lg0PE2XzXlooOGxDh5B9CopdYNUfr2', NULL),
(26, 'eee', '$2y$10$MZP32m8cBGaewefOcbniEecHG6jLTzPPL5TYnLnOGZlf5BDk./nmm', 'admin'),
(27, 'wow', '$2y$10$PjXrW7GGLUZwPPJ5M58EC.eKjxnZLQT9z8Nq119BbwuUkbJWSNBeK', 'staff'),
(28, 'e', '$2y$10$WTcLu81l2C48obzxVefenenxRSD83GTmW171.oLFDvsAhakN35D6i', 'admin'),
(29, 'enzo23', '$2y$10$bzn3gop2ryHaWDBKC2Dv0.7TwBXqsZlG7VV25YFrS7H6BQ23J3lC6', 'staff'),
(30, 'mama', '$2y$10$mqISjoMVL0bPXNPTUl4BFebtvwp38J1t6GZhu1nFJ10A5t8hLFK/y', 'admin'),
(31, 'mama2', '$2y$10$cdTjaATarnYK3ouYzrZitupUD0tS0HRcRjpAac89LCtTLO8PbctCi', 'staff'),
(32, 'lol', '$2y$10$w3jnu.RJmDsqGS9KM1K3hudZ/zt2RugPl2IDO/K/AV94VFX4pPacO', 'admin'),
(33, 'tae', '$2y$10$.pgPLVq2GsjH9L6byMQSc.q/vxTypmhw1pYoNw8qp1k/emi5wKZxe', 'admin');

--
-- Indexes for dumped tables
--

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
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
