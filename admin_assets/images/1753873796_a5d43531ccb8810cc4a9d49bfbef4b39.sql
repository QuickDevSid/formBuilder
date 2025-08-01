-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jul 30, 2025 at 11:52 AM
-- Server version: 8.0.42-cll-lve
-- PHP Version: 8.3.23

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `quickenl_trinity_new`
--

-- --------------------------------------------------------

--
-- Table structure for table `tbl_users`
--

CREATE TABLE `tbl_users` (
  `id` int NOT NULL,
  `department` varchar(191) DEFAULT NULL,
  `username` varchar(500) NOT NULL,
  `email` varchar(500) NOT NULL,
  `password` varchar(500) NOT NULL,
  `mobile_number` varchar(1000) NOT NULL,
  `type` enum('0','1','2') NOT NULL COMMENT '0 = admin,',
  `is_admin` enum('0','1') DEFAULT '1' COMMENT '0 = super_admin, 1 = admin',
  `created_on` datetime NOT NULL,
  `updated_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status` enum('0','1') NOT NULL DEFAULT '1',
  `is_deleted` enum('0','1') NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `tbl_users`
--

INSERT INTO `tbl_users` (`id`, `department`, `username`, `email`, `password`, `mobile_number`, `type`, `is_admin`, `created_on`, `updated_on`, `status`, `is_deleted`) VALUES
(1, '1,2,3', 'admin', 'admin@gmail.com', '123', '999999999', '0', '1', '2021-05-29 12:52:38', '2021-05-29 07:23:57', '1', '1'),
(2, '1,2,3', 'super_admin', 'super_admin@gmail.com', '1234', '2323243444', '2', '0', '2024-06-19 13:37:19', '2024-06-19 11:37:19', '1', '0'),
(3, '1,2,3', 'nayan', 'nayan@gmail.com', '123', '', '0', '1', '2024-06-20 15:22:33', '2024-06-20 09:52:33', '1', '1'),
(6, '1,2,3', 'dcds', 'sd@gmail.com', '1111', '', '0', '1', '2024-07-29 18:53:47', '2024-07-29 13:23:47', '1', '1'),
(7, '1,2', 'csd', 'dd@gmaki.com', '123', '', '0', '1', '2024-07-29 18:54:45', '2024-07-29 13:24:45', '1', '1'),
(8, '1', 'admin123', 'admin@12.gmail.com', '123', '', '0', '1', '2024-07-31 13:40:16', '2024-07-31 08:10:16', '1', '1'),
(9, '1,2,3', 'priyanka', 'priyanka@gmail.com', '123', '', '0', '1', '2024-08-05 17:14:45', '2024-08-05 11:44:45', '1', '1'),
(10, '1,2,3', 'Nitin D', 'nitin@gmail.com', '123', '', '0', '1', '2024-08-12 10:17:10', '2024-08-12 04:47:10', '1', '1'),
(11, '3', 'Abhay', 'abhay@gmail.com', '123', '', '0', '1', '2024-08-12 11:23:35', '2024-08-12 05:53:35', '1', '1'),
(12, '1', 'Rajan Sir', 'Fab@gmail.com', '123', '', '0', '1', '2024-10-13 15:17:49', '2024-10-13 09:47:49', '1', '0'),
(13, '2', 'Vikipawar', 'Paint@gmail.com', '123', '', '0', '1', '2024-10-13 15:18:22', '2024-10-13 09:48:22', '1', '0'),
(14, '3', 'Sagarjagtap', 'Sagar@gmail.com', '123', '', '0', '1', '2024-10-13 15:19:35', '2024-10-13 09:49:35', '1', '1'),
(15, '4', 'Material Preparation', 'Kitting@gmail.com', '123', '', '0', '1', '2025-01-20 20:44:57', '2025-01-20 15:14:57', '1', '0'),
(16, '3', 'Single Stand Workstation', 'SSW@Gmail.com', '123', '', '0', '1', '2025-06-14 12:11:02', '2025-06-14 06:41:02', '1', '1');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tbl_users`
--
ALTER TABLE `tbl_users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tbl_users`
--
ALTER TABLE `tbl_users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
