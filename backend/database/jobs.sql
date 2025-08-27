-- Table structure for table `jobs`
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

-- Sample data for testing
INSERT INTO `jobs` (`title`, `department`, `location`, `type`, `experience`, `description`, `responsibilities`, `requirements`, `benefits`, `posted`) VALUES
('Software Engineer', 'Engineering', 'Remote', 'Full-time', '2-5 years', 'We are looking for a talented Software Engineer to join our development team and help build innovative solutions.', 'Develop and maintain web applications, Collaborate with cross-functional teams, Write clean and maintainable code, Participate in code reviews', 'Bachelor\'s degree in Computer Science or related field, Proficiency in JavaScript, Python, or similar languages, Experience with modern web frameworks, Strong problem-solving skills', 'Competitive salary, Health insurance, Remote work options, Professional development opportunities, Flexible working hours'),
('UI/UX Designer', 'Design', 'Remote', 'Full-time', '3-6 years', 'Join our design team to create beautiful and intuitive user experiences that delight our customers.', 'Create user interface designs, Conduct user research and testing, Collaborate with product and engineering teams, Maintain design system', 'Bachelor\'s degree in Design or related field, Proficiency in Figma, Sketch, or similar tools, Experience with user research methods, Strong portfolio showcasing previous work', 'Competitive salary, Health insurance, Remote work options, Creative freedom, Latest design tools and software'),
('Marketing Specialist', 'Marketing', 'Remote', 'Full-time', '1-3 years', 'Help us grow our brand and reach new customers through strategic marketing initiatives.', 'Develop marketing campaigns, Manage social media presence, Analyze marketing performance, Coordinate with external partners', 'Bachelor\'s degree in Marketing or related field, Experience with digital marketing tools, Strong communication skills, Creative thinking abilities', 'Competitive salary, Health insurance, Remote work options, Performance bonuses, Professional growth opportunities');