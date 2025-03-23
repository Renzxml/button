-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 23, 2025 at 04:18 PM
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
-- Database: `lockerv1_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `fingerprints`
--

CREATE TABLE `fingerprints` (
  `fingerprints_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `fingerprint_data` text NOT NULL,
  `assigned_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lockers`
--

CREATE TABLE `lockers` (
  `locker_id` int(11) NOT NULL,
  `locker_number` varchar(50) NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'available',
  `user_id` int(11) DEFAULT NULL,
  `assigned_at` timestamp NULL DEFAULT NULL,
  `lphw_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lockers`
--

INSERT INTO `lockers` (`locker_id`, `locker_number`, `status`, `user_id`, `assigned_at`, `lphw_id`) VALUES
(14, '1001', 'available', NULL, NULL, 2),
(15, '1002', 'available', NULL, NULL, 1),
(16, '1003', 'available', NULL, NULL, 3);

-- --------------------------------------------------------

--
-- Table structure for table `lockers_pin_hw`
--

CREATE TABLE `lockers_pin_hw` (
  `lphw_id` int(11) NOT NULL,
  `pin_number` int(11) NOT NULL,
  `status` varchar(10) NOT NULL DEFAULT 'available'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lockers_pin_hw`
--

INSERT INTO `lockers_pin_hw` (`lphw_id`, `pin_number`, `status`) VALUES
(1, 25, 'assigned'),
(2, 26, 'assigned'),
(3, 27, 'assigned');

-- --------------------------------------------------------

--
-- Table structure for table `registered_rfid`
--

CREATE TABLE `registered_rfid` (
  `rfid_id` int(11) NOT NULL,
  `rfid_tag` varchar(50) NOT NULL,
  `status` varchar(20) DEFAULT 'available',
  `registered_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `registered_rfid`
--

INSERT INTO `registered_rfid` (`rfid_id`, `rfid_tag`, `status`, `registered_at`, `user_id`) VALUES
(10, 'B3D342E4', 'available', '2025-03-23 10:47:02', 5),
(11, '72AB963F', 'available', '2025-03-23 10:50:00', 3);

-- --------------------------------------------------------

--
-- Table structure for table `subscriptions_tbl`
--

CREATE TABLE `subscriptions_tbl` (
  `sub_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `locker_no` int(11) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `status` enum('Active','Paused','Expired') DEFAULT 'Active',
  `paused_date` date DEFAULT NULL,
  `resume_date` date DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subscriptions_tbl`
--

INSERT INTO `subscriptions_tbl` (`sub_id`, `user_id`, `locker_no`, `start_date`, `end_date`, `status`, `paused_date`, `resume_date`, `updated_at`) VALUES
(1, 5, 18, '2025-03-02', '2025-03-20', 'Paused', NULL, NULL, '2025-02-28 20:58:11');

-- --------------------------------------------------------

--
-- Table structure for table `user_tbl`
--

CREATE TABLE `user_tbl` (
  `user_id` int(11) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `middle_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) NOT NULL,
  `gmail` varchar(150) NOT NULL,
  `mobile_no` varchar(20) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_tbl`
--

INSERT INTO `user_tbl` (`user_id`, `first_name`, `middle_name`, `last_name`, `gmail`, `mobile_no`, `password`, `role`) VALUES
(1, 'Renzel', '', 'Rodriguez', 'renzrodriguez23@gmail.com', '0', '$2y$10$lVPJUOFwq3DlL4A10NeGKuQDOz9v15dC/DaHILlHnHOHTMHb97TS6', 'user'),
(2, 'Renzel', '', 'Rodriguez', 'renz21@gmail.com', NULL, '$2y$10$kL2pahqq27j1Z6n0p4wKYOY696VSnf6FsAnzxASniRH2LMfkHNNsK', 'user'),
(3, 'Renzel', '', 'Rodriguez', 'renz@gmail.com', NULL, '$2y$10$WQj31W5kPjC/aTO2ZDQHTubaO8xl19lfinDYoxu88hYz8/hBCNA/.', 'user'),
(4, 'renz', 'ro', 'rodriguez', 'renzxml@gmail.com', '0', '$2y$10$PD5iJD6s0r2OJQdOi2UUj.1mx3dU6dxkxvg8N.TyHBreiGMoSkXmu', 'admin'),
(5, 'Vince', '', 'Baena', 'vincebaena17@gmail.com', NULL, '$2y$10$W5risERgAGZLiTAu3waQoepj3duENkfdGBDPu69zdASnDPZWZwFum', 'user'),
(6, 'Vince', 'Camacho', 'Baena', 'baenavinceiverson.bsit@gmail.com', '0', '$2y$10$JYs9KoVWkpaMEswYg/fis.k1I1D1LmIH7cxnrSDIy06xosx8sgOlu', 'admin'),
(7, 'Jan', '', 'Ureta', 'janermaine@gmail.com', NULL, '$2y$10$nTH14MSLyfZTNm9QXUwcweXE53p05XHLIDB9zAMm5y2viiqXeUpzq', 'user');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `fingerprints`
--
ALTER TABLE `fingerprints`
  ADD PRIMARY KEY (`fingerprints_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `lockers`
--
ALTER TABLE `lockers`
  ADD PRIMARY KEY (`locker_id`),
  ADD UNIQUE KEY `locker_number` (`locker_number`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `fk_pin_number` (`lphw_id`);

--
-- Indexes for table `lockers_pin_hw`
--
ALTER TABLE `lockers_pin_hw`
  ADD PRIMARY KEY (`lphw_id`),
  ADD UNIQUE KEY `pin_number` (`pin_number`);

--
-- Indexes for table `registered_rfid`
--
ALTER TABLE `registered_rfid`
  ADD PRIMARY KEY (`rfid_id`),
  ADD UNIQUE KEY `rfid_tag` (`rfid_tag`),
  ADD KEY `fk_user_id` (`user_id`);

--
-- Indexes for table `subscriptions_tbl`
--
ALTER TABLE `subscriptions_tbl`
  ADD PRIMARY KEY (`sub_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `user_tbl`
--
ALTER TABLE `user_tbl`
  ADD PRIMARY KEY (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `fingerprints`
--
ALTER TABLE `fingerprints`
  MODIFY `fingerprints_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lockers`
--
ALTER TABLE `lockers`
  MODIFY `locker_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `lockers_pin_hw`
--
ALTER TABLE `lockers_pin_hw`
  MODIFY `lphw_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `registered_rfid`
--
ALTER TABLE `registered_rfid`
  MODIFY `rfid_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `subscriptions_tbl`
--
ALTER TABLE `subscriptions_tbl`
  MODIFY `sub_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `user_tbl`
--
ALTER TABLE `user_tbl`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `fingerprints`
--
ALTER TABLE `fingerprints`
  ADD CONSTRAINT `fingerprints_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user_tbl` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `lockers`
--
ALTER TABLE `lockers`
  ADD CONSTRAINT `fk_pin_number` FOREIGN KEY (`lphw_id`) REFERENCES `lockers_pin_hw` (`lphw_id`),
  ADD CONSTRAINT `lockers_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user_tbl` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `registered_rfid`
--
ALTER TABLE `registered_rfid`
  ADD CONSTRAINT `fk_user_id` FOREIGN KEY (`user_id`) REFERENCES `user_tbl` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `subscriptions_tbl`
--
ALTER TABLE `subscriptions_tbl`
  ADD CONSTRAINT `subscriptions_tbl_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user_tbl` (`user_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
