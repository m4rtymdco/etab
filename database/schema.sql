-- eTab Event Judging Tabulator
-- MySQL 5.7+ / MariaDB 10.3+ / utf8mb4

CREATE DATABASE IF NOT EXISTS `etab` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `etab`;

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `audit_logs`;
DROP TABLE IF EXISTS `score_drafts`;
DROP TABLE IF EXISTS `password_resets`;
DROP TABLE IF EXISTS `scores`;
DROP TABLE IF EXISTS `judge_assignments`;
DROP TABLE IF EXISTS `criteria`;
DROP TABLE IF EXISTS `criteria_template_items`;
DROP TABLE IF EXISTS `criteria_templates`;
DROP TABLE IF EXISTS `contestant_events`;
DROP TABLE IF EXISTS `contestants`;
DROP TABLE IF EXISTS `events`;
DROP TABLE IF EXISTS `users`;

SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE `users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(120) NOT NULL,
  `email` VARCHAR(190) NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `role` ENUM('admin','judge') NOT NULL DEFAULT 'judge',
  `phone` VARCHAR(40) NULL,
  `bio` TEXT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `events` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(180) NOT NULL,
  `description` TEXT NULL,
  `event_date` DATE NULL,
  `event_time` TIME NULL,
  `venue` VARCHAR(180) NULL,
  `status` ENUM('upcoming','ongoing','completed') NOT NULL DEFAULT 'upcoming',
  `results_published` TINYINT(1) NOT NULL DEFAULT 0,
  `drop_high_low` TINYINT(1) NOT NULL DEFAULT 0,
  `score_min` DECIMAL(6,2) NOT NULL DEFAULT 1.00,
  `score_max` DECIMAL(6,2) NOT NULL DEFAULT 100.00,
  `rounds` INT UNSIGNED NOT NULL DEFAULT 1,
  `created_by` INT UNSIGNED NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `events_status_idx` (`status`),
  KEY `events_created_by_fk` (`created_by`),
  CONSTRAINT `events_created_by_fk` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `contestants` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(180) NOT NULL,
  `category` VARCHAR(120) NULL,
  `photo_url` VARCHAR(255) NULL,
  `status` ENUM('active','archived') NOT NULL DEFAULT 'active',
  `notes` TEXT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `contestants_status_idx` (`status`),
  KEY `contestants_category_idx` (`category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `contestant_events` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `contestant_id` INT UNSIGNED NOT NULL,
  `event_id` INT UNSIGNED NOT NULL,
  `entry_number` VARCHAR(40) NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `contestant_event_unique` (`contestant_id`,`event_id`),
  KEY `ce_event_fk` (`event_id`),
  CONSTRAINT `ce_contestant_fk` FOREIGN KEY (`contestant_id`) REFERENCES `contestants` (`id`) ON DELETE CASCADE,
  CONSTRAINT `ce_event_fk` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `criteria` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `event_id` INT UNSIGNED NOT NULL,
  `name` VARCHAR(180) NOT NULL,
  `description` TEXT NULL,
  `section` VARCHAR(180) NULL,
  `max_score` DECIMAL(6,2) NOT NULL DEFAULT 100.00,
  `weight` DECIMAL(6,2) NOT NULL DEFAULT 0.00,
  `sort_order` INT NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `criteria_event_fk` (`event_id`),
  CONSTRAINT `criteria_event_fk` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `criteria_templates` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(180) NOT NULL,
  `description` TEXT NULL,
  `created_by` INT UNSIGNED NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `criteria_template_items` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `template_id` INT UNSIGNED NOT NULL,
  `name` VARCHAR(180) NOT NULL,
  `description` TEXT NULL,
  `section` VARCHAR(180) NULL,
  `max_score` DECIMAL(6,2) NOT NULL DEFAULT 100.00,
  `weight` DECIMAL(6,2) NOT NULL DEFAULT 0.00,
  `sort_order` INT NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `cti_template_fk` (`template_id`),
  CONSTRAINT `cti_template_fk` FOREIGN KEY (`template_id`) REFERENCES `criteria_templates` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `judge_assignments` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `judge_id` INT UNSIGNED NOT NULL,
  `event_id` INT UNSIGNED NOT NULL,
  `assigned_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `judge_event_unique` (`judge_id`,`event_id`),
  KEY `ja_event_fk` (`event_id`),
  CONSTRAINT `ja_judge_fk` FOREIGN KEY (`judge_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `ja_event_fk` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `scores` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `judge_id` INT UNSIGNED NOT NULL,
  `contestant_id` INT UNSIGNED NOT NULL,
  `criteria_id` INT UNSIGNED NOT NULL,
  `event_id` INT UNSIGNED NOT NULL,
  `round` INT UNSIGNED NOT NULL DEFAULT 1,
  `score_value` DECIMAL(6,2) NOT NULL,
  `comments` TEXT NULL,
  `is_override` TINYINT(1) NOT NULL DEFAULT 0,
  `override_by` INT UNSIGNED NULL,
  `submitted_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `score_once_per_criterion` (`judge_id`,`contestant_id`,`criteria_id`,`round`),
  KEY `scores_event_idx` (`event_id`,`round`),
  KEY `scores_contestant_idx` (`contestant_id`),
  KEY `scores_criteria_fk` (`criteria_id`),
  CONSTRAINT `scores_judge_fk` FOREIGN KEY (`judge_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `scores_contestant_fk` FOREIGN KEY (`contestant_id`) REFERENCES `contestants` (`id`) ON DELETE CASCADE,
  CONSTRAINT `scores_criteria_fk` FOREIGN KEY (`criteria_id`) REFERENCES `criteria` (`id`) ON DELETE CASCADE,
  CONSTRAINT `scores_event_fk` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `score_drafts` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `judge_id` INT UNSIGNED NOT NULL,
  `contestant_id` INT UNSIGNED NOT NULL,
  `event_id` INT UNSIGNED NOT NULL,
  `round` INT UNSIGNED NOT NULL DEFAULT 1,
  `payload` LONGTEXT NOT NULL,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `draft_unique` (`judge_id`,`contestant_id`,`event_id`,`round`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `password_resets` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL,
  `token_hash` VARCHAR(64) NOT NULL,
  `expires_at` DATETIME NOT NULL,
  `used_at` DATETIME NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `pr_user_fk` (`user_id`),
  KEY `pr_token_idx` (`token_hash`),
  CONSTRAINT `pr_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `audit_logs` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NULL,
  `action` VARCHAR(80) NOT NULL,
  `entity_type` VARCHAR(60) NULL,
  `entity_id` INT UNSIGNED NULL,
  `details` TEXT NULL,
  `ip_address` VARCHAR(45) NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `audit_user_idx` (`user_id`),
  KEY `audit_action_idx` (`action`),
  KEY `audit_created_idx` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
