-- Drop the database if it exists and create a new one
DROP DATABASE IF EXISTS `panchayat_db`;
CREATE DATABASE IF NOT EXISTS `panchayat_db`;
USE `panchayat_db`;

-- Users table to store login details
CREATE TABLE IF NOT EXISTS `users` (
    `user_id` INT AUTO_INCREMENT PRIMARY KEY,
    `email` VARCHAR(100) UNIQUE NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `role` ENUM('admin', 'member', 'general') NOT NULL DEFAULT 'general',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- User details table to store personal information for users, connected to the users table
CREATE TABLE IF NOT EXISTS `user_details` (
    `user_id` INT PRIMARY KEY,
    `first_name` VARCHAR(200) NOT NULL,
    `middle_name` VARCHAR(200),
    `last_name` VARCHAR(200) NOT NULL,
    `dob` DATE NOT NULL,
    `phone_no` VARCHAR(15) UNIQUE NOT NULL,
    `gender` ENUM('male', 'female', 'other') NOT NULL,
    `address` TEXT NOT NULL,
    `profile_picture` TEXT,  -- For storing profile picture (optional)
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE
);

-- Village representatives table to store village committee members (only one village)
CREATE TABLE IF NOT EXISTS `village_representatives` (
    `rep_id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `position` ENUM(
        'headman',
        'secretary',
        'treasurer',
        'health coordinator',
        'education officer'
    ) NOT NULL,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `unique_position` UNIQUE (`position`)  -- Ensure that each position is unique
);


-- Population table to track the total population and houses of the village
CREATE TABLE IF NOT EXISTS `population` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `total_population` INT NOT NULL,
    `population_above_18` INT NOT NULL,
    `population_below_18` INT NOT NULL,
    `number_of_houses` INT NOT NULL,
    `last_updated` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Houses table to store household details (only for one village)
CREATE TABLE IF NOT EXISTS `houses` (
    `house_id` INT AUTO_INCREMENT PRIMARY KEY,
    `house_no` VARCHAR(50) UNIQUE NOT NULL,
    `owner_name` VARCHAR(255) NOT NULL,
    `number_of_members` INT NOT NULL
);

-- Government schemes table to store available schemes
-- CREATE TABLE IF NOT EXISTS `government_schemes` (
--     `scheme_id` INT AUTO_INCREMENT PRIMARY KEY,
--     `scheme_name` VARCHAR(255) NOT NULL,
--     `description` TEXT NOT NULL,
--     `eligibility` TEXT NOT NULL,
--     `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
--     `last_updated` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
-- );

-- News articles table to store village-related news
-- CREATE TABLE IF NOT EXISTS `news_articles` (
--     `article_id` INT AUTO_INCREMENT PRIMARY KEY,
--     `title` VARCHAR(255) NOT NULL,
--     `content` TEXT NOT NULL,
--     `author` VARCHAR(255) NOT NULL,
--     `published_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
-- );

-- Notices table to store village-related announcements
CREATE TABLE IF NOT EXISTS `notices` (
    `notice_id` INT AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(255) NOT NULL,
    `content` TEXT NOT NULL,
    `category` ENUM('meeting','announcement','safety','health') NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Health-related notices table
-- CREATE TABLE IF NOT EXISTS `health_notices` (
--     `health_id` INT AUTO_INCREMENT PRIMARY KEY,
--     `title` VARCHAR(255) NOT NULL,
--     `description` TEXT NOT NULL,
--     `event_date` DATE NOT NULL,
--     `last_updated` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
-- );

-- Work orders table for village projects and sanitation
CREATE TABLE IF NOT EXISTS `work_orders` (
    `order_id` INT AUTO_INCREMENT PRIMARY KEY,
    `project_name` VARCHAR(255) NOT NULL,
    `description` TEXT NOT NULL,
    `status` ENUM('pending','in_progress','completed') NOT NULL DEFAULT 'pending',
    `assigned_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `completion_date` DATE NULL
);

-- Complaint management table to track complaints
CREATE TABLE IF NOT EXISTS `complaints` (
    `complaint_id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `category` ENUM('road_repair','sanitation','electric_issue') NOT NULL,
    `description` TEXT NOT NULL,
    `status` ENUM('pending','resolved') NOT NULL DEFAULT 'pending',
    `submitted_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE
);

-- Added New Table
CREATE TABLE IF NOT EXISTS `schemes` (
    `scheme_id` INT AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(255) NOT NULL,
    `description` TEXT NOT NULL,
    `department` VARCHAR(100) NOT NULL,
    `eligibility_criteria` TEXT NOT NULL,
    `benefits` TEXT NOT NULL,
    `application_process` TEXT NOT NULL,
    `start_date` DATE NOT NULL,
    `end_date` DATE NULL,
    `status` ENUM('active','inactive') NOT NULL DEFAULT 'active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS `people` (
    `person_id` INT AUTO_INCREMENT PRIMARY KEY,
    `first_name` VARCHAR(100) NOT NULL,
    `last_name` VARCHAR(100) NOT NULL,
    `age` INT NOT NULL,
    `gender` ENUM('male', 'female', 'other') NOT NULL,
    `house_number` VARCHAR(50) NOT NULL,
    `occupation` VARCHAR(100),
    `education_level` VARCHAR(100),
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX (`house_number`),
    INDEX (`gender`),
    INDEX (`age`)
);