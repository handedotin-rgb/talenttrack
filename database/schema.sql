-- database/schema.sql
-- Production MySQL / MariaDB Schema for TalentTrack

CREATE DATABASE IF NOT EXISTS `talenttrack` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `talenttrack`;

-- Users Table
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(120) NOT NULL,
    `email` VARCHAR(150) NOT NULL UNIQUE,
    `password_hash` VARCHAR(255) NOT NULL,
    `role` ENUM('admin', 'recruiter', 'candidate') NOT NULL DEFAULT 'candidate',
    `company_name` VARCHAR(150) NULL,
    `phone` VARCHAR(30) NULL,
    `location` VARCHAR(120) NULL,
    `headline` VARCHAR(180) NULL,
    `bio` TEXT NULL,
    `skills` TEXT NULL,
    `experience_years` INT DEFAULT 0,
    `resume_path` VARCHAR(255) NULL,
    `avatar` VARCHAR(255) NULL,
    `status` ENUM('active', 'inactive', 'pending') NOT NULL DEFAULT 'active',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Job Categories Table
CREATE TABLE IF NOT EXISTS `categories` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL UNIQUE,
    `slug` VARCHAR(120) NOT NULL UNIQUE,
    `icon` VARCHAR(50) DEFAULT 'briefcase',
    `description` VARCHAR(255) NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Job Postings Table
CREATE TABLE IF NOT EXISTS `jobs` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `recruiter_id` INT NOT NULL,
    `category_id` INT NOT NULL,
    `title` VARCHAR(150) NOT NULL,
    `slug` VARCHAR(180) NOT NULL,
    `job_type` ENUM('full-time', 'part-time', 'contract', 'remote', 'internship') NOT NULL DEFAULT 'full-time',
    `experience_level` ENUM('entry', 'mid', 'senior', 'lead') NOT NULL DEFAULT 'mid',
    `location` VARCHAR(120) NOT NULL,
    `salary_min` INT NULL,
    `salary_max` INT NULL,
    `salary_currency` VARCHAR(10) DEFAULT 'USD',
    `description` MEDIUMTEXT NOT NULL,
    `requirements` MEDIUMTEXT NOT NULL,
    `benefits` TEXT NULL,
    `deadline` DATE NULL,
    `is_featured` TINYINT(1) DEFAULT 0,
    `status` ENUM('active', 'closed', 'draft', 'archived') NOT NULL DEFAULT 'active',
    `views_count` INT DEFAULT 0,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`recruiter_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- Job Applications Table
CREATE TABLE IF NOT EXISTS `applications` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `job_id` INT NOT NULL,
    `candidate_id` INT NOT NULL,
    `resume_path` VARCHAR(255) NOT NULL,
    `cover_letter` TEXT NULL,
    `current_stage` ENUM('applied', 'screening', 'interview', 'assessment', 'offer', 'hired', 'rejected') NOT NULL DEFAULT 'applied',
    `recruiter_rating` TINYINT DEFAULT NULL,
    `recruiter_notes` TEXT NULL,
    `status` ENUM('in_progress', 'hired', 'rejected', 'withdrawn') NOT NULL DEFAULT 'in_progress',
    `applied_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `unique_job_candidate` (`job_id`, `candidate_id`),
    FOREIGN KEY (`job_id`) REFERENCES `jobs`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`candidate_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Application Stage History (Audit Trail for Pipeline Progress)
CREATE TABLE IF NOT EXISTS `application_history` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `application_id` INT NOT NULL,
    `from_stage` VARCHAR(50) NULL,
    `to_stage` VARCHAR(50) NOT NULL,
    `note` TEXT NULL,
    `changed_by_user_id` INT NOT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`application_id`) REFERENCES `applications`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`changed_by_user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Interviews Table
CREATE TABLE IF NOT EXISTS `interviews` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `application_id` INT NOT NULL,
    `recruiter_id` INT NOT NULL,
    `candidate_id` INT NOT NULL,
    `title` VARCHAR(150) NOT NULL,
    `interview_type` ENUM('screening', 'technical', 'cultural_fit', 'hr', 'final') NOT NULL DEFAULT 'technical',
    `scheduled_at` DATETIME NOT NULL,
    `duration_minutes` INT DEFAULT 45,
    `meeting_link` VARCHAR(255) NULL,
    `location` VARCHAR(150) NULL,
    `status` ENUM('scheduled', 'completed', 'cancelled', 'rescheduled') NOT NULL DEFAULT 'scheduled',
    `feedback` TEXT NULL,
    `rating` TINYINT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`application_id`) REFERENCES `applications`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`recruiter_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`candidate_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Audit Logs Table
CREATE TABLE IF NOT EXISTS `audit_logs` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NULL,
    `action` VARCHAR(100) NOT NULL,
    `entity_type` VARCHAR(50) NOT NULL,
    `entity_id` INT NULL,
    `details` TEXT NULL,
    `ip_address` VARCHAR(45) NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB;
