-- phpMyAdmin SQL Dump
-- version 5.0.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: May 13, 2020 at 12:59 PM
-- Server version: 10.3.22-MariaDB-1:10.3.22+maria~bionic
-- PHP Version: 7.3.17-1+ubuntu18.04.1+deb.sury.org+1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

--
-- Database: `GrowBox`
--

-- --------------------------------------------------------

--
-- Table structure for table `system_attempts`
--

CREATE TABLE `system_attempts` (
  `session_id` varchar(255) DEFAULT NULL,
  `type` varchar(250) DEFAULT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `agent` varchar(300) DEFAULT NULL,
  `payload` text DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `system_audit`
--

CREATE TABLE `system_audit` (
  `id` int(11) NOT NULL,
  `action` varchar(100) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `pk` varchar(100) DEFAULT NULL,
  `source` varchar(250) DEFAULT NULL,
  `data` text DEFAULT NULL,
  `datetime` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `system_errors`
--

CREATE TABLE `system_errors` (
  `id` int(11) NOT NULL,
  `version` varchar(100) NOT NULL,
  `error_key` varchar(50) NOT NULL,
  `url` text DEFAULT NULL,
  `code` varchar(100) DEFAULT NULL,
  `message` mediumtext DEFAULT NULL,
  `trace` mediumtext DEFAULT NULL,
  `count` int(3) DEFAULT 0,
  `datetime_added` datetime NOT NULL DEFAULT current_timestamp(),
  `datetime_last` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `system_login_codes`
--

CREATE TABLE `system_login_codes` (
  `session_id` varchar(255) DEFAULT NULL,
  `user_key` varchar(100) DEFAULT NULL,
  `code` varchar(50) DEFAULT NULL,
  `used` tinyint(1) DEFAULT 0,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `system_roles`
--

CREATE TABLE `system_roles` (
  `id` int(11) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `role` varchar(250) DEFAULT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `system_roles_categories`
--

CREATE TABLE `system_roles_categories` (
  `id` int(11) NOT NULL,
  `category` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `system_roles_permissions`
--

CREATE TABLE `system_roles_permissions` (
  `id` int(11) NOT NULL,
  `role_id` int(11) DEFAULT NULL,
  `permission` varchar(250) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `system_sessions`
--

CREATE TABLE `system_sessions` (
  `session_id` varchar(255) NOT NULL,
  `user_key` varchar(250) DEFAULT NULL,
  `data` text DEFAULT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `agent` varchar(300) DEFAULT NULL,
  `stamp` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `system_users`
--

CREATE TABLE `system_users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `salt` varchar(50) DEFAULT NULL,
  `settings` text DEFAULT NULL,
  `last_active` datetime DEFAULT NULL,
  `active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `system_users_permissions`
--

CREATE TABLE `system_users_permissions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `permission` varchar(250) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `system_users_roles`
--

CREATE TABLE `system_users_roles` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `role_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `system_attempts`
--
ALTER TABLE `system_attempts`
  ADD KEY `session_id` (`session_id`),
  ADD KEY `ip` (`ip`);

--
-- Indexes for table `system_audit`
--
ALTER TABLE `system_audit`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `system_errors`
--
ALTER TABLE `system_errors`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unk_key` (`error_key`,`version`) USING BTREE,
  ADD KEY `version` (`version`);

--
-- Indexes for table `system_login_codes`
--
ALTER TABLE `system_login_codes`
  ADD UNIQUE KEY `code` (`code`),
  ADD KEY `user_key` (`user_key`) USING BTREE;

--
-- Indexes for table `system_roles`
--
ALTER TABLE `system_roles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `system_roles_categories`
--
ALTER TABLE `system_roles_categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `system_roles_permissions`
--
ALTER TABLE `system_roles_permissions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `role_id` (`role_id`);

--
-- Indexes for table `system_sessions`
--
ALTER TABLE `system_sessions`
  ADD PRIMARY KEY (`session_id`),
  ADD KEY `user_key` (`user_key`);

--
-- Indexes for table `system_users`
--
ALTER TABLE `system_users`
  ADD PRIMARY KEY (`id`),
  ADD KEY `active` (`active`) USING BTREE;

--
-- Indexes for table `system_users_permissions`
--
ALTER TABLE `system_users_permissions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`) USING BTREE;

--
-- Indexes for table `system_users_roles`
--
ALTER TABLE `system_users_roles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `role_id` (`role_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `system_audit`
--
ALTER TABLE `system_audit`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `system_errors`
--
ALTER TABLE `system_errors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `system_roles`
--
ALTER TABLE `system_roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `system_roles_categories`
--
ALTER TABLE `system_roles_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `system_roles_permissions`
--
ALTER TABLE `system_roles_permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `system_users`
--
ALTER TABLE `system_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `system_users_permissions`
--
ALTER TABLE `system_users_permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `system_users_roles`
--
ALTER TABLE `system_users_roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;
