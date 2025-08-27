-- Complete Database Setup for SOFINDEX Career Center
-- Run this script to create the database and tables

-- Create database if it doesn't exist
CREATE DATABASE IF NOT EXISTS `u153805899_career_apply` 
CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE `u153805899_career_apply`;

-- Drop existing tables if they exist
DROP TABLE IF EXISTS `job_applications`;
DROP TABLE IF EXISTS `jobs`;

-- Create jobs table
CREATE TABLE `jobs` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) NOT NULL,
  `department` VARCHAR(255) NOT NULL,
  `location` VARCHAR(255) NOT NULL,
  `type` ENUM('Full-time', 'Part-time', 'Contract', 'Internship') NOT NULL,
  `experience` VARCHAR(100) NOT NULL,
  `description` TEXT NOT NULL,
  `responsibilities` TEXT NOT NULL,
  `requirements` TEXT NOT NULL,
  `benefits` TEXT NOT NULL,
  `posted` DATE NOT NULL DEFAULT CURRENT_DATE,
  `status` ENUM('Active', 'Closed', 'Draft') NOT NULL DEFAULT 'Active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_department` (`department`),
  INDEX `idx_location` (`location`),
  INDEX `idx_type` (`type`),
  INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create job_applications table with foreign key relationship
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

-- Insert sample job data
INSERT INTO `jobs` (`title`, `department`, `location`, `type`, `experience`, `description`, `responsibilities`, `requirements`, `benefits`, `posted`) VALUES
('Software Engineer', 'Engineering', 'Remote', 'Full-time', '2-5 years', 'We are looking for a talented Software Engineer to join our development team and help build innovative solutions.', 'Develop and maintain web applications, Collaborate with cross-functional teams, Write clean and maintainable code, Participate in code reviews', 'Bachelor\'s degree in Computer Science or related field, Proficiency in JavaScript, Python, or similar languages, Experience with modern web frameworks, Strong problem-solving skills', 'Competitive salary, Health insurance, Remote work options, Professional development opportunities, Flexible working hours'),
('UI/UX Designer', 'Design', 'Remote', 'Full-time', '3-6 years', 'Join our design team to create beautiful and intuitive user experiences that delight our customers.', 'Create user interface designs, Conduct user research and testing, Collaborate with product and engineering teams, Maintain design system', 'Bachelor\'s degree in Design or related field, Proficiency in Figma, Sketch, or similar tools, Experience with user research methods, Strong portfolio showcasing previous work', 'Competitive salary, Health insurance, Remote work options, Creative freedom, Latest design tools and software'),
('Marketing Specialist', 'Marketing', 'Remote', 'Full-time', '1-3 years', 'Help us grow our brand and reach new customers through strategic marketing initiatives.', 'Develop marketing campaigns, Manage social media presence, Analyze marketing performance, Coordinate with external partners', 'Bachelor\'s degree in Marketing or related field, Experience with digital marketing tools, Strong communication skills, Creative thinking abilities', 'Competitive salary, Health insurance, Remote work options, Performance bonuses, Professional growth opportunities'),
('Data Scientist', 'Engineering', 'Remote', 'Full-time', '3-7 years', 'Join our data team to extract insights and build machine learning models that drive business decisions.', 'Analyze large datasets, Build predictive models, Create data visualizations, Collaborate with business teams', 'Master\'s degree in Data Science, Statistics, or related field, Proficiency in Python/R, Experience with SQL and big data tools, Strong statistical background', 'Competitive salary, Health insurance, Remote work options, Cutting-edge tools and technologies, Research opportunities'),
('Product Manager', 'Product', 'Remote', 'Full-time', '4-8 years', 'Lead product strategy and execution to deliver exceptional user experiences and business value.', 'Define product vision and roadmap, Gather and prioritize requirements, Work with cross-functional teams, Analyze market trends', 'Bachelor\'s degree in Business, Engineering, or related field, Experience in product management, Strong analytical and communication skills, Technical background preferred', 'Competitive salary, Health insurance, Remote work options, Leadership opportunities, Professional development');

-- Insert sample application data
INSERT INTO `job_applications` (`job_id`, `full_name`, `email`, `phone`, `cover_letter`, `resume_url`, `linkedin_url`, `status`) VALUES
(1, 'John Doe', 'john.doe@example.com', '+1-555-0123', 'I am excited to apply for the Software Engineer position at SOFINDEX. With my background in web development and passion for creating innovative solutions, I believe I would be a great fit for your team.', 'https://example.com/resume/john-doe.pdf', 'https://linkedin.com/in/johndoe', 'Pending'),
(1, 'Jane Smith', 'jane.smith@example.com', '+1-555-0456', 'As a software developer with 4 years of experience, I am particularly drawn to SOFINDEX\'s mission of building technology that makes a positive impact. I would love to contribute to your innovative projects.', 'https://example.com/resume/jane-smith.pdf', 'https://linkedin.com/in/janesmith', 'Under Review'),
(2, 'Mike Johnson', 'mike.johnson@example.com', '+1-555-0789', 'I am a passionate UI/UX designer with a strong focus on user-centered design. I believe my creative approach and technical skills would be valuable to SOFINDEX\'s design team.', 'https://example.com/resume/mike-johnson.pdf', 'https://linkedin.com/in/mikejohnson', 'Pending');

-- Verify the setup
SELECT 'Database setup completed successfully!' AS status;
SELECT COUNT(*) AS total_jobs FROM jobs;
SELECT COUNT(*) AS total_applications FROM job_applications;

