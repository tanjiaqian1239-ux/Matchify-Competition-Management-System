-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 15, 2026 at 06:02 AM
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
-- Database: `mcmp`
--

-- --------------------------------------------------------

--
-- Table structure for table `competition_applications`
--

CREATE TABLE `competition_applications` (
  `id` int(11) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `application_start` date DEFAULT NULL,
  `application_end` date DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `participants` int(11) DEFAULT NULL,
  `organizer_id` int(11) DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `reject_reason` varchar(255) DEFAULT NULL,
  `competition_image` varchar(255) DEFAULT NULL,
  `prize_1st` varchar(255) DEFAULT NULL,
  `prize_2nd` varchar(255) DEFAULT NULL,
  `prize_3rd` varchar(255) DEFAULT NULL,
  `prize_description` text DEFAULT NULL,
  `organizer` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `rules` text DEFAULT NULL,
  `submission_type` varchar(50) DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `rejected_at` datetime DEFAULT NULL,
  `result_status` enum('pending','judging','published') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `competition_applications`
--

INSERT INTO `competition_applications` (`id`, `title`, `description`, `application_start`, `application_end`, `category`, `start_date`, `end_date`, `participants`, `organizer_id`, `status`, `created_at`, `reject_reason`, `competition_image`, `prize_1st`, `prize_2nd`, `prize_3rd`, `prize_description`, `organizer`, `email`, `rules`, `submission_type`, `approved_at`, `rejected_at`, `result_status`) VALUES
(4, 'swsw', 'swsw', '2026-05-14', '2026-05-14', 'esports', '2026-05-15', '2026-05-16', 12, 17, 'approved', '2026-05-14 10:39:31', NULL, 'comp_1778755171.jpg', 'RM 500 + Candidates', 'RM 200 + Candidates', 'RM 100 + Candidates', 'swswsw', '0', 'thouzentan11523@gmail.com', 'swswsw', 'file', '2026-05-14 18:39:44', NULL, 'pending'),
(5, 'grf', 'frfrf', '2026-05-14', '2026-05-14', 'tech', '2026-05-14', '2026-05-14', 13, 17, 'approved', '2026-05-14 10:41:21', NULL, 'comp_1778755281.jpg', 'RM 500 + Candidates', 'RM 200 + Candidates', 'RM 100 + Candidates', 'eded', '0', 'thouzentan11523@gmail.com', 'dede', 'file', '2026-05-14 18:41:32', NULL, 'pending'),
(6, 'd\'w\'d\'w', 'dede', '2026-05-14', '2026-05-14', 'tech', '2026-05-14', '2026-05-14', 12, 17, 'approved', '2026-05-14 10:51:27', NULL, 'comp_1778755887.jpg', 'RM 500 + Candidates', 'RM 200 + Candidates', 'RM 100 + Candidates', 'dwdwd', '0', 'thouzentan11523@gmail.com', 'dwdwd', 'file', '2026-05-14 18:51:35', NULL, 'pending'),
(7, 's\'s', 'dds', '2026-05-14', '2026-05-14', 'tech', '2026-05-14', '2026-05-14', 123, 17, 'approved', '2026-05-14 12:00:20', NULL, 'comp_1778760020.jpg', 'RM 500 + Candidates', 'RM 200 + Candidates', 'RM 100 + Candidates', 'xsxsxxs', '0', 'thouzentan11523@gmail.com', 'xsxsxs', 'file', '2026-05-14 20:00:29', NULL, 'pending');

-- --------------------------------------------------------

--
-- Table structure for table `competition_judges`
--

CREATE TABLE `competition_judges` (
  `id` int(11) NOT NULL,
  `competition_id` int(11) NOT NULL,
  `judge_name` varchar(255) NOT NULL,
  `judge_email` varchar(255) NOT NULL,
  `judge_phone` varchar(50) DEFAULT NULL,
  `judge_ic` varchar(50) DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `temp_password` varchar(255) DEFAULT NULL,
  `reject_reason` varchar(255) DEFAULT NULL,
  `judge_id` int(11) DEFAULT NULL,
  `assigned_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `competition_judges`
--

INSERT INTO `competition_judges` (`id`, `competition_id`, `judge_name`, `judge_email`, `judge_phone`, `judge_ic`, `status`, `temp_password`, `reject_reason`, `judge_id`, `assigned_at`) VALUES
(2, 5, 'dwdw', 'nangongluxi123@gmail.com', '0123456789', '303030-14-4112', 'approved', '4c45e902', NULL, 18, '2026-05-14 11:12:09'),
(3, 6, 'junxin@gmail.com', 'nangongluxi123@gmail.com', '0123456789', '303030-14-4112', 'approved', 'b498b640', NULL, 18, '2026-05-14 11:43:20'),
(4, 4, 'junxin@gmail.com', 'nangongluxi123@gmail.com', '0123456789', '', 'approved', '6cb1f204', NULL, 18, '2026-05-14 11:44:33'),
(7, 7, 'junxin@gmail.com', 'nangongluxi123@gmail.com', '0123456789', '303030-14-4112', 'approved', 'c2173bbe', NULL, 18, '2026-05-14 12:06:11');

-- --------------------------------------------------------

--
-- Table structure for table `competition_participants`
--

CREATE TABLE `competition_participants` (
  `id` int(11) NOT NULL,
  `competition_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `full_name` varchar(255) DEFAULT NULL,
  `ic_number` varchar(50) DEFAULT NULL,
  `gender` varchar(10) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `competition_participants`
--

INSERT INTO `competition_participants` (`id`, `competition_id`, `user_id`, `full_name`, `ic_number`, `gender`, `email`, `phone`, `address`, `status`, `created_at`) VALUES
(3, 4, 17, 'Jia Qian', 'IC: 040102-14-1421', 'Male', 'thouzentan11523@gmail.com', '60123456788', 'swsw', 'approved', '2026-05-14 10:40:08'),
(4, 5, 17, 'Jia Qian', 'IC: 040102-14-1421', 'Female', 'thouzentan11523@gmail.com', '60123456788', 'dwdw', 'approved', '2026-05-14 11:11:28');

-- --------------------------------------------------------

--
-- Table structure for table `competition_scores`
--

CREATE TABLE `competition_scores` (
  `id` int(11) NOT NULL,
  `competition_id` int(11) NOT NULL,
  `participant_id` int(11) NOT NULL,
  `judge_id` int(11) NOT NULL,
  `score` decimal(5,2) NOT NULL,
  `comment` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `competition_scores`
--

INSERT INTO `competition_scores` (`id`, `competition_id`, `participant_id`, `judge_id`, `score`, `comment`, `created_at`) VALUES
(1, 5, 4, 18, 50.00, '123', '2026-05-15 03:33:42');

-- --------------------------------------------------------

--
-- Table structure for table `competition_settings`
--

CREATE TABLE `competition_settings` (
  `id` int(11) NOT NULL,
  `competition_id` int(11) NOT NULL,
  `submission_open` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `competition_settings`
--

INSERT INTO `competition_settings` (`id`, `competition_id`, `submission_open`, `created_at`) VALUES
(4, 4, 0, '2026-05-14 10:39:44'),
(5, 5, 0, '2026-05-14 10:41:32'),
(6, 6, 0, '2026-05-14 10:51:35'),
(7, 7, 0, '2026-05-14 12:00:29');

-- --------------------------------------------------------

--
-- Table structure for table `competition_submissions`
--

CREATE TABLE `competition_submissions` (
  `id` int(11) NOT NULL,
  `competition_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `competition_submissions`
--

INSERT INTO `competition_submissions` (`id`, `competition_id`, `user_id`, `title`, `description`, `file_path`, `submitted_at`) VALUES
(1, 5, 17, 'dcdc', 'cdcdc', 'sub_1778761149.pptx', '2026-05-14 12:19:09');

-- --------------------------------------------------------

--
-- Table structure for table `participants`
--

CREATE TABLE `participants` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `competition_id` int(11) NOT NULL,
  `registration_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `fullname` varchar(100) DEFAULT NULL,
  `username` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `country` varchar(10) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `gender` varchar(10) DEFAULT NULL,
  `role` enum('admin','organiser','participant','judge') DEFAULT 'participant',
  `status` enum('active','pending') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `reset_token_hash` varchar(64) DEFAULT NULL,
  `reset_token_expires_at` datetime DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT 'default.png'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `fullname`, `username`, `email`, `password`, `country`, `phone`, `gender`, `role`, `status`, `created_at`, `reset_token_hash`, `reset_token_expires_at`, `profile_image`) VALUES
(15, 'Starry', 'Starrytan', 'starrytan1314@gmail.com', '$2y$10$NXyBFjoUjxJcnPhmxNk8M.JtNRflupru5WtMj8.WeGWR4Kya0xqCW', 'Malaysia', '60123456788', 'Female', 'participant', 'active', '2026-05-14 10:36:50', NULL, NULL, 'default.png'),
(17, 'Jia Qian', 'jiaqian', 'thouzentan11523@gmail.com', '$2y$10$XknyhNUjAr6486YkORiho.dpxiHI7IJPWxMQN/fqNsgXtlxynofFy', 'Malaysia', '60123456788', 'Female', 'organiser', 'active', '2026-05-14 10:38:55', NULL, NULL, 'default.png'),
(18, 'dwdw', 'judge_1778758157', 'nangongluxi123@gmail.com', '$2y$10$T4gWex1FHXs0sAOIdrb4ZOC8bixIXFQvxeBZgdYNW3y/HovYu/5Nm', NULL, NULL, NULL, 'judge', 'active', '2026-05-14 11:29:17', NULL, NULL, 'default.png');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `competition_applications`
--
ALTER TABLE `competition_applications`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `competition_judges`
--
ALTER TABLE `competition_judges`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `competition_participants`
--
ALTER TABLE `competition_participants`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `competition_scores`
--
ALTER TABLE `competition_scores`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `competition_settings`
--
ALTER TABLE `competition_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `competition_submissions`
--
ALTER TABLE `competition_submissions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `participants`
--
ALTER TABLE `participants`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `competition_applications`
--
ALTER TABLE `competition_applications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `competition_judges`
--
ALTER TABLE `competition_judges`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `competition_participants`
--
ALTER TABLE `competition_participants`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `competition_scores`
--
ALTER TABLE `competition_scores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `competition_settings`
--
ALTER TABLE `competition_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `competition_submissions`
--
ALTER TABLE `competition_submissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `participants`
--
ALTER TABLE `participants`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
