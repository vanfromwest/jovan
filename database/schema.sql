-- CCSICT Faculty Monitoring System Database Schema
-- Database: ccsict_faculty_monitoring
-- Created: 2026-05-28

-- Create database if it doesn't exist
-- CREATE DATABASE IF NOT EXISTS ccsict_faculty_monitoring;
-- USE ccsict_faculty_monitoring;

-- ============================================
-- Table: departments
-- ============================================
CREATE TABLE IF NOT EXISTS `departments` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL UNIQUE,
  `code` VARCHAR(20) NOT NULL UNIQUE,
  `description` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table: users
-- ============================================
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `fullname` VARCHAR(150) NOT NULL,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('Admin', 'Faculty', 'Student') NOT NULL DEFAULT 'Student',
  `status` ENUM('PENDING', 'APPROVED', 'REJECTED') NOT NULL DEFAULT 'PENDING',
  `contact_number` VARCHAR(20),
  `department_id` INT,
  `profile_image` VARCHAR(255),
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `last_login` DATETIME,
  `is_active` BOOLEAN DEFAULT 1,
  FOREIGN KEY (`department_id`) REFERENCES `departments`(`id`) ON DELETE SET NULL,
  INDEX `idx_email` (`email`),
  INDEX `idx_username` (`username`),
  INDEX `idx_role` (`role`),
  INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table: faculty
-- ============================================
CREATE TABLE IF NOT EXISTS `faculty` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `user_id` INT NOT NULL UNIQUE,
  `qr_token` VARCHAR(100) NOT NULL UNIQUE,
  `position` VARCHAR(100),
  `office_room` VARCHAR(50),
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  INDEX `idx_qr_token` (`qr_token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table: qr_codes
-- ============================================
CREATE TABLE IF NOT EXISTS `qr_codes` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `faculty_id` INT NOT NULL UNIQUE,
  `qr_token` VARCHAR(100) NOT NULL UNIQUE,
  `qr_path` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`faculty_id`) REFERENCES `faculty`(`id`) ON DELETE CASCADE,
  INDEX `idx_qr_token` (`qr_token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table: faculty_status
-- ============================================
CREATE TABLE IF NOT EXISTS `faculty_status` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `faculty_id` INT NOT NULL UNIQUE,
  `status` ENUM('IN', 'OUT') NOT NULL DEFAULT 'OUT',
  `activity` VARCHAR(255),
  `location` VARCHAR(255),
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`faculty_id`) REFERENCES `faculty`(`id`) ON DELETE CASCADE,
  INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table: attendance
-- ============================================
CREATE TABLE IF NOT EXISTS `attendance` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `faculty_id` INT NOT NULL,
  `scan_date` DATE NOT NULL,
  `time_in` TIME,
  `time_out` TIME,
  `activity_out` VARCHAR(255),
  `location_out` VARCHAR(255),
  `remarks` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`faculty_id`) REFERENCES `faculty`(`id`) ON DELETE CASCADE,
  INDEX `idx_faculty_id` (`faculty_id`),
  INDEX `idx_scan_date` (`scan_date`),
  UNIQUE KEY `unique_faculty_date` (`faculty_id`, `scan_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table: announcements
-- ============================================
CREATE TABLE IF NOT EXISTS `announcements` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `title` VARCHAR(255) NOT NULL,
  `content` TEXT NOT NULL,
  `created_by` INT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_active` BOOLEAN DEFAULT 1,
  `is_pinned` BOOLEAN DEFAULT 0,
  FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table: activity_logs
-- ============================================
CREATE TABLE IF NOT EXISTS `activity_logs` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `user_id` INT NOT NULL,
  `action` VARCHAR(255) NOT NULL,
  `details` TEXT,
  `ip_address` VARCHAR(45),
  `user_agent` VARCHAR(255),
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  INDEX `idx_user_id` (`user_id`),
  INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table: scan_logs
-- ============================================
CREATE TABLE IF NOT EXISTS `scan_logs` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `faculty_id` INT NOT NULL,
  `qr_token` VARCHAR(100) NOT NULL,
  `scan_time` DATETIME NOT NULL,
  `scan_type` ENUM('IN', 'OUT') NOT NULL,
  `ip_address` VARCHAR(45),
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`faculty_id`) REFERENCES `faculty`(`id`) ON DELETE CASCADE,
  INDEX `idx_faculty_id` (`faculty_id`),
  INDEX `idx_scan_time` (`scan_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table: activities
-- ============================================
CREATE TABLE IF NOT EXISTS `activities` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL UNIQUE,
  `description` TEXT,
  `icon` VARCHAR(50),
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Insert Sample Data: Departments
-- ============================================
INSERT IGNORE INTO `departments` (`id`, `name`, `code`, `description`) VALUES
(1, 'Computer Science and Information Technology', 'CCSICT', 'Department of Computer Science and Information Technology'),
(2, 'General Education', 'GE', 'General Education Department'),
(3, 'Engineering', 'ENG', 'Engineering Department'),
(4, 'Business Administration', 'BA', 'Business Administration Department');

-- ============================================
-- Insert Sample Data: Activities
-- ============================================
INSERT IGNORE INTO `activities` (`id`, `name`, `description`, `icon`) VALUES
(1, 'Teaching', 'Conducting class/teaching students', 'chalkboard-user'),
(2, 'Laboratory Class', 'Conducting laboratory session', 'flask'),
(3, 'Faculty Meeting', 'Attending faculty meeting', 'people-group'),
(4, 'Student Consultation', 'Consulting with students', 'user-check'),
(5, 'Lunch Break', 'Taking lunch break', 'utensils'),
(6, 'Conference', 'Attending conference', 'video'),
(7, 'Training', 'Attending training/workshop', 'graduation-cap'),
(8, 'Administrative Work', 'Doing administrative tasks', 'file-pen'),
(9, 'Leave', 'On approved leave', 'calendar-xmark');

-- ============================================
-- Create Sample Admin User
-- ============================================
INSERT IGNORE INTO `users` 
(`id`, `fullname`, `username`, `email`, `password`, `role`, `status`, `contact_number`, `department_id`, `is_active`) 
VALUES 
(1, 'Admin User', 'adminsonic', 'adminsonic@ccsict.com', '$2y$10$a0b8Eaq.uuY/15Y5WklSrOOVtQdPLmW3B71YZhIH3UWoCIA3gud0a', 'Admin', 'APPROVED', '09123456789', 1, 1);

-- Note: Password for admin is 'sonic123' (hashed with bcrypt)
-- To generate new hashes, use: password_hash('password', PASSWORD_BCRYPT)

-- ============================================
-- Table: email_config
-- ============================================
CREATE TABLE IF NOT EXISTS `email_config` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `key` VARCHAR(50) NOT NULL UNIQUE,
  `value` TEXT,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_key` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default email config values
INSERT IGNORE INTO `email_config` (`key`, `value`) VALUES
('mail_host', 'smtp.gmail.com'),
('mail_port', '587'),
('mail_username', ''),
('mail_password', ''),
('mail_encryption', 'tls'),
('from_email', ''),
('from_name', 'CCSICT Faculty Monitoring System');
