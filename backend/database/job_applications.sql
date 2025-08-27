-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Aug 23, 2025 at 11:15 PM
-- Server version: 10.11.10-MariaDB-log
-- PHP Version: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `u153805899_career_apply`
--

-- --------------------------------------------------------

--
-- Table structure for table `job_applications`
--

CREATE TABLE `job_applications` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `job_id` INT NOT NULL,
  `full_name` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `phone` VARCHAR(20) NOT NULL,
  `cover_letter` TEXT NOT NULL,
  `resume_url` VARCHAR(500) NOT NULL,
  `linkedin_url` VARCHAR(500) DEFAULT NULL,
  `portfolio_url` VARCHAR(500) DEFAULT NULL,
  `status` ENUM('Pending', 'Under Review', 'Shortlisted', 'Rejected', 'Hired') NOT NULL DEFAULT 'Pending',
  `submitted_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `reviewed_at` TIMESTAMP NULL,
  `notes` TEXT DEFAULT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`job_id`) REFERENCES `jobs`(`id`) ON DELETE CASCADE,
  INDEX `idx_job_id` (`job_id`),
  INDEX `idx_email` (`email`),
  INDEX `idx_status` (`status`),
  INDEX `idx_submitted_at` (`submitted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sample application data for testing
INSERT INTO `job_applications` (`job_id`, `full_name`, `email`, `phone`, `cover_letter`, `resume_url`, `linkedin_url`, `status`) VALUES
(1, 'John Doe', 'john.doe@example.com', '+1-555-0123', 'I am excited to apply for the Software Engineer position at SOFINDEX. With my background in web development and passion for creating innovative solutions, I believe I would be a great fit for your team.', 'https://example.com/resume/john-doe.pdf', 'https://linkedin.com/in/johndoe', 'Pending'),
(1, 'Jane Smith', 'jane.smith@example.com', '+1-555-0456', 'As a software developer with 4 years of experience, I am particularly drawn to SOFINDEX\'s mission of building technology that makes a positive impact. I would love to contribute to your innovative projects.', 'https://example.com/resume/jane-smith.pdf', 'https://linkedin.com/in/janesmith', 'Under Review'),
(2, 'Mike Johnson', 'mike.johnson@example.com', '+1-555-0789', 'I am a passionate UI/UX designer with a strong focus on user-centered design. I believe my creative approach and technical skills would be valuable to SOFINDEX\'s design team.', 'https://example.com/resume/mike-johnson.pdf', 'https://linkedin.com/in/mikejohnson', 'Pending');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `job_applications`
--
ALTER TABLE `job_applications`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `job_applications`
--
ALTER TABLE `job_applications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=179;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
