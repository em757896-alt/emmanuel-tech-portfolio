-- ============================================================
-- PBO COMPLIANCE PLATFORM - DATABASE SCHEMA
-- MySQL DB Name: if0_42280606_if0_42280606_
-- MySQL User Name: if0_42280606
-- MySQL Password: AES256:4m0deNaMM0HA+yKw/HIgbYzFLvAjq8o1cD7cfheTaOSB8M/MqTc/Edx85mfbuzOL
-- MySQL Host Name: sql303.infinityfree.com
-- PHPMyAdmin: Available via vPanel
-- Created for: CRECO Kenya PBO Platform
-- ============================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+03:00"; -- East Africa Time

-- ============================================================
-- DATABASE: if0_42280606_if0_42280606_
-- ============================================================

-- ------------------------------------------------------------
-- Table: users
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `uuid` VARCHAR(36) NOT NULL UNIQUE,
  `full_name` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL UNIQUE,
  `phone` VARCHAR(20) DEFAULT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `role` ENUM('super_admin','admin','moderator','pbo_user','public') NOT NULL DEFAULT 'public',
  `organization_name` VARCHAR(255) DEFAULT NULL,
  `organization_type` VARCHAR(100) DEFAULT NULL,
  `county` VARCHAR(100) DEFAULT NULL,
  `is_verified` TINYINT(1) DEFAULT 0,
  `is_active` TINYINT(1) DEFAULT 1,
  `mfa_enabled` TINYINT(1) DEFAULT 0,
  `mfa_secret` VARCHAR(255) DEFAULT NULL,
  `mfa_backup_codes` TEXT DEFAULT NULL,
  `email_verified_at` TIMESTAMP NULL DEFAULT NULL,
  `last_login` TIMESTAMP NULL DEFAULT NULL,
  `login_attempts` INT(3) DEFAULT 0,
  `locked_until` TIMESTAMP NULL DEFAULT NULL,
  `password_reset_token` VARCHAR(255) DEFAULT NULL,
  `password_reset_expires` TIMESTAMP NULL DEFAULT NULL,
  `profile_image` VARCHAR(255) DEFAULT NULL,
  `consent_given` TINYINT(1) DEFAULT 0,
  `consent_date` TIMESTAMP NULL DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_email` (`email`),
  INDEX `idx_role` (`role`),
  INDEX `idx_uuid` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Table: sessions
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `sessions` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `session_token` VARCHAR(255) NOT NULL UNIQUE,
  `ip_address` VARCHAR(45) DEFAULT NULL,
  `user_agent` TEXT DEFAULT NULL,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `expires_at` TIMESTAMP NOT NULL,
  `last_activity` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  INDEX `idx_session_token` (`session_token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Table: audit_logs
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `audit_logs` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) DEFAULT NULL,
  `action` VARCHAR(255) NOT NULL,
  `module` VARCHAR(100) DEFAULT NULL,
  `record_id` INT(11) DEFAULT NULL,
  `old_values` JSON DEFAULT NULL,
  `new_values` JSON DEFAULT NULL,
  `ip_address` VARCHAR(45) DEFAULT NULL,
  `user_agent` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  INDEX `idx_action` (`action`),
  INDEX `idx_module` (`module`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Table: knowledge_articles
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `knowledge_articles` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `title_en` VARCHAR(500) NOT NULL,
  `title_sw` VARCHAR(500) DEFAULT NULL,
  `slug` VARCHAR(500) NOT NULL UNIQUE,
  `content_en` LONGTEXT NOT NULL,
  `content_sw` LONGTEXT DEFAULT NULL,
  `summary_en` TEXT DEFAULT NULL,
  `summary_sw` TEXT DEFAULT NULL,
  `category` ENUM('pbo_act','registration','compliance','governance','finance','advocacy','rights') NOT NULL,
  `subcategory` VARCHAR(100) DEFAULT NULL,
  `tags` TEXT DEFAULT NULL,
  `featured_image` VARCHAR(255) DEFAULT NULL,
  `is_plain_language` TINYINT(1) DEFAULT 1,
  `pbo_act_section` VARCHAR(100) DEFAULT NULL,
  `is_published` TINYINT(1) DEFAULT 0,
  `is_featured` TINYINT(1) DEFAULT 0,
  `view_count` INT(11) DEFAULT 0,
  `download_count` INT(11) DEFAULT 0,
  `author_id` INT(11) DEFAULT NULL,
  `published_at` TIMESTAMP NULL DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`author_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  FULLTEXT INDEX `ft_search` (`title_en`, `content_en`, `title_sw`, `content_sw`),
  INDEX `idx_category` (`category`),
  INDEX `idx_published` (`is_published`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Table: resources (Toolkits, Guides, Templates)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `resources` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(500) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `file_path` VARCHAR(500) NOT NULL,
  `file_name` VARCHAR(255) NOT NULL,
  `file_size` INT(11) DEFAULT NULL,
  `file_type` VARCHAR(50) DEFAULT NULL,
  `resource_type` ENUM('toolkit','guide','template','report','infographic','video','faq') NOT NULL,
  `category` VARCHAR(100) DEFAULT NULL,
  `language` ENUM('en','sw','both') DEFAULT 'en',
  `is_public` TINYINT(1) DEFAULT 1,
  `download_count` INT(11) DEFAULT 0,
  `uploaded_by` INT(11) DEFAULT NULL,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`uploaded_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  INDEX `idx_resource_type` (`resource_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Table: faqs
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `faqs` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `question_en` TEXT NOT NULL,
  `question_sw` TEXT DEFAULT NULL,
  `answer_en` LONGTEXT NOT NULL,
  `answer_sw` LONGTEXT DEFAULT NULL,
  `category` VARCHAR(100) DEFAULT NULL,
  `pbo_act_reference` VARCHAR(100) DEFAULT NULL,
  `sort_order` INT(5) DEFAULT 0,
  `is_published` TINYINT(1) DEFAULT 1,
  `view_count` INT(11) DEFAULT 0,
  `helpful_yes` INT(11) DEFAULT 0,
  `helpful_no` INT(11) DEFAULT 0,
  `created_by` INT(11) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  FULLTEXT INDEX `ft_faq_search` (`question_en`, `answer_en`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Table: compliance_checklists
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `compliance_checklists` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `category` ENUM('registration','governance','finance','reporting','advocacy','general') NOT NULL,
  `pbo_type` VARCHAR(100) DEFAULT NULL,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Table: checklist_items
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `checklist_items` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `checklist_id` INT(11) NOT NULL,
  `item_text` TEXT NOT NULL,
  `item_text_sw` TEXT DEFAULT NULL,
  `description` TEXT DEFAULT NULL,
  `pbo_act_reference` VARCHAR(100) DEFAULT NULL,
  `is_mandatory` TINYINT(1) DEFAULT 1,
  `weight` INT(3) DEFAULT 1,
  `category` VARCHAR(100) DEFAULT NULL,
  `sort_order` INT(5) DEFAULT 0,
  `guidance_notes` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`checklist_id`) REFERENCES `compliance_checklists`(`id`) ON DELETE CASCADE,
  INDEX `idx_checklist` (`checklist_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Table: user_compliance_assessments
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `user_compliance_assessments` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `checklist_id` INT(11) NOT NULL,
  `responses` JSON NOT NULL,
  `total_items` INT(5) DEFAULT 0,
  `completed_items` INT(5) DEFAULT 0,
  `score_percentage` DECIMAL(5,2) DEFAULT 0.00,
  `compliance_level` ENUM('low','medium','high','excellent') DEFAULT 'low',
  `recommendations` JSON DEFAULT NULL,
  `completed_at` TIMESTAMP NULL DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`checklist_id`) REFERENCES `compliance_checklists`(`id`) ON DELETE CASCADE,
  INDEX `idx_user_assessment` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Table: chatbot_knowledge_base
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `chatbot_knowledge_base` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `question_pattern` TEXT NOT NULL,
  `keywords` TEXT NOT NULL,
  `answer_en` LONGTEXT NOT NULL,
  `answer_sw` LONGTEXT DEFAULT NULL,
  `pbo_act_section` VARCHAR(100) DEFAULT NULL,
  `source_reference` TEXT DEFAULT NULL,
  `category` VARCHAR(100) DEFAULT NULL,
  `confidence_score` DECIMAL(3,2) DEFAULT 1.00,
  `is_active` TINYINT(1) DEFAULT 1,
  `usage_count` INT(11) DEFAULT 0,
  `last_used` TIMESTAMP NULL DEFAULT NULL,
  `created_by` INT(11) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  FULLTEXT INDEX `ft_kb_search` (`question_pattern`, `keywords`, `answer_en`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Table: chatbot_conversations
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `chatbot_conversations` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `session_id` VARCHAR(255) NOT NULL,
  `user_id` INT(11) DEFAULT NULL,
  `user_message` TEXT NOT NULL,
  `bot_response` LONGTEXT NOT NULL,
  `kb_item_id` INT(11) DEFAULT NULL,
  `confidence` DECIMAL(3,2) DEFAULT NULL,
  `language` ENUM('en','sw') DEFAULT 'en',
  `feedback` ENUM('helpful','not_helpful','flagged') DEFAULT NULL,
  `feedback_note` TEXT DEFAULT NULL,
  `flagged_for_review` TINYINT(1) DEFAULT 0,
  `reviewed_by` INT(11) DEFAULT NULL,
  `ip_address` VARCHAR(45) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`kb_item_id`) REFERENCES `chatbot_knowledge_base`(`id`) ON DELETE SET NULL,
  INDEX `idx_session` (`session_id`),
  INDEX `idx_flagged` (`flagged_for_review`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Table: monitoring_reports
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `monitoring_reports` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `report_number` VARCHAR(50) NOT NULL UNIQUE,
  `user_id` INT(11) NOT NULL,
  `organization_name` VARCHAR(255) NOT NULL,
  `organization_type` VARCHAR(100) DEFAULT NULL,
  `county` VARCHAR(100) NOT NULL,
  `sub_county` VARCHAR(100) DEFAULT NULL,
  `report_type` ENUM('registration','compliance','barrier','delay','violation','enabling_practice','other') NOT NULL,
  `title` VARCHAR(500) NOT NULL,
  `description` LONGTEXT NOT NULL,
  `incident_date` DATE DEFAULT NULL,
  `affected_parties` TEXT DEFAULT NULL,
  `government_agency` VARCHAR(255) DEFAULT NULL,
  `pbo_act_violations` TEXT DEFAULT NULL,
  `report_data` JSON DEFAULT NULL,
  `quantitative_data` JSON DEFAULT NULL,
  `qualitative_data` JSON DEFAULT NULL,
  `evidence_provided` TINYINT(1) DEFAULT 0,
  `supporting_docs` JSON DEFAULT NULL,
  `severity_level` ENUM('low','medium','high','critical') DEFAULT 'medium',
  `status` ENUM('submitted','under_review','verified','rejected','resolved') DEFAULT 'submitted',
  `is_anonymous` TINYINT(1) DEFAULT 0,
  `consent_to_publish` TINYINT(1) DEFAULT 0,
  `moderation_notes` TEXT DEFAULT NULL,
  `moderated_by` INT(11) DEFAULT NULL,
  `moderated_at` TIMESTAMP NULL DEFAULT NULL,
  `is_validated` TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`moderated_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  INDEX `idx_county` (`county`),
  INDEX `idx_status` (`status`),
  INDEX `idx_report_type` (`report_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Table: incident_reports (Civic Space Violations)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `incident_reports` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `incident_number` VARCHAR(50) NOT NULL UNIQUE,
  `reporter_id` INT(11) NOT NULL,
  `incident_type` ENUM('harassment','intimidation','arbitrary_arrest','deregistration','funding_block','assembly_denial','other') NOT NULL,
  `incident_date` DATE NOT NULL,
  `location` VARCHAR(255) NOT NULL,
  `county` VARCHAR(100) NOT NULL,
  `description` LONGTEXT NOT NULL,
  `perpetrator_type` ENUM('government_agency','police','county_government','private_actor','unknown') DEFAULT NULL,
  `perpetrator_details` TEXT DEFAULT NULL,
  `victims_affected` INT(11) DEFAULT NULL,
  `organizations_affected` TEXT DEFAULT NULL,
  `immediate_impact` TEXT DEFAULT NULL,
  `long_term_impact` TEXT DEFAULT NULL,
  `action_taken` TEXT DEFAULT NULL,
  `legal_action_initiated` TINYINT(1) DEFAULT 0,
  `supporting_documents` JSON DEFAULT NULL,
  `witness_information` JSON DEFAULT NULL,
  `is_confidential` TINYINT(1) DEFAULT 0,
  `status` ENUM('reported','investigating','resolved','unresolved','referred') DEFAULT 'reported',
  `urgency_level` ENUM('low','medium','high','urgent') DEFAULT 'medium',
  `moderated_by` INT(11) DEFAULT NULL,
  `moderation_notes` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`reporter_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`moderated_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  INDEX `idx_incident_county` (`county`),
  INDEX `idx_incident_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Table: analytics_events
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `analytics_events` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) DEFAULT NULL,
  `session_id` VARCHAR(255) DEFAULT NULL,
  `event_type` VARCHAR(100) NOT NULL,
  `event_category` VARCHAR(100) DEFAULT NULL,
  `event_label` VARCHAR(255) DEFAULT NULL,
  `event_value` TEXT DEFAULT NULL,
  `page_url` VARCHAR(500) DEFAULT NULL,
  `referrer` VARCHAR(500) DEFAULT NULL,
  `county` VARCHAR(100) DEFAULT NULL,
  `device_type` VARCHAR(50) DEFAULT NULL,
  `browser` VARCHAR(100) DEFAULT NULL,
  `ip_address` VARCHAR(45) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  INDEX `idx_event_type` (`event_type`),
  INDEX `idx_event_date` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Table: page_views
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `page_views` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `page_url` VARCHAR(500) NOT NULL,
  `page_title` VARCHAR(255) DEFAULT NULL,
  `user_id` INT(11) DEFAULT NULL,
  `session_id` VARCHAR(255) DEFAULT NULL,
  `ip_address` VARCHAR(45) DEFAULT NULL,
  `country` VARCHAR(100) DEFAULT 'Kenya',
  `county` VARCHAR(100) DEFAULT NULL,
  `device_type` ENUM('desktop','tablet','mobile') DEFAULT 'desktop',
  `duration_seconds` INT(11) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  INDEX `idx_page` (`page_url`(191)),
  INDEX `idx_view_date` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Table: counties (Kenya Counties Reference)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `counties` (
  `id` INT(3) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `code` VARCHAR(10) NOT NULL UNIQUE,
  `region` VARCHAR(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Table: notifications
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `notifications` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `message` TEXT NOT NULL,
  `type` ENUM('info','warning','success','danger') DEFAULT 'info',
  `link` VARCHAR(500) DEFAULT NULL,
  `is_read` TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  INDEX `idx_user_notify` (`user_id`, `is_read`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Table: monitoring_attachments
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `monitoring_attachments` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `report_id` INT(11) NOT NULL,
  `original_name` VARCHAR(255) NOT NULL,
  `stored_path` VARCHAR(500) NOT NULL,
  `mime_type` VARCHAR(100) DEFAULT NULL,
  `file_size` INT(11) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`report_id`) REFERENCES `monitoring_reports`(`id`) ON DELETE CASCADE,
  INDEX `idx_attachment_report` (`report_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- SEED DATA
-- ============================================================

-- Kenya Counties
INSERT INTO `counties` (`name`, `code`, `region`) VALUES
('Nairobi', 'NBI', 'Nairobi'),
('Mombasa', 'MSA', 'Coast'),
('Kwale', 'KWL', 'Coast'),
('Kilifi', 'KLF', 'Coast'),
('Tana River', 'TNR', 'Coast'),
('Lamu', 'LMU', 'Coast'),
('Taita Taveta', 'TTA', 'Coast'),
('Garissa', 'GRS', 'North Eastern'),
('Wajir', 'WJR', 'North Eastern'),
('Mandera', 'MND', 'North Eastern'),
('Marsabit', 'MRS', 'Rift Valley'),
('Isiolo', 'ISL', 'Eastern'),
('Meru', 'MRU', 'Eastern'),
('Tharaka Nithi', 'TRN', 'Eastern'),
('Embu', 'EMB', 'Eastern'),
('Kitui', 'KTU', 'Eastern'),
('Machakos', 'MKS', 'Eastern'),
('Makueni', 'MKN', 'Eastern'),
('Nyandarua', 'NYN', 'Central'),
('Nyeri', 'NYR', 'Central'),
('Kirinyaga', 'KRN', 'Central'),
('Murang\'a', 'MRG', 'Central'),
('Kiambu', 'KMB', 'Central'),
('Turkana', 'TRK', 'Rift Valley'),
('West Pokot', 'WPK', 'Rift Valley'),
('Samburu', 'SMB', 'Rift Valley'),
('Trans Nzoia', 'TNZ', 'Rift Valley'),
('Uasin Gishu', 'USG', 'Rift Valley'),
('Elgeyo Marakwet', 'ELG', 'Rift Valley'),
('Nandi', 'NND', 'Rift Valley'),
('Baringo', 'BRN', 'Rift Valley'),
('Laikipia', 'LKP', 'Rift Valley'),
('Nakuru', 'NKR', 'Rift Valley'),
('Narok', 'NRK', 'Rift Valley'),
('Kajiado', 'KJD', 'Rift Valley'),
('Kericho', 'KRC', 'Rift Valley'),
('Bomet', 'BMT', 'Rift Valley'),
('Kakamega', 'KKM', 'Western'),
('Vihiga', 'VHG', 'Western'),
('Bungoma', 'BNG', 'Western'),
('Busia', 'BSA', 'Western'),
('Siaya', 'SYA', 'Nyanza'),
('Kisumu', 'KSM', 'Nyanza'),
('Homa Bay', 'HMB', 'Nyanza'),
('Migori', 'MGR', 'Nyanza'),
('Kisii', 'KSI', 'Nyanza'),
('Nyamira', 'NYM', 'Nyanza');

-- Default Super Admin (password: AES256:2vQKcZlXZtu8dwKVVChzA8tyC1rMtk9XJxmoTMNBSbaVmjcuM4KBUCVstVgheVj6XrGsvYHCxxvQ9tjbDRKD2Q==)
INSERT INTO `users` (`uuid`, `full_name`, `email`, `password_hash`, `role`, `is_verified`, `is_active`, `consent_given`, `consent_date`) VALUES
(UUID(), 'CRECO Admin', 'admin@crecokenya.org', '$2y$12$LKJ8mN2pQ5rT9vW3xY7zA.eHgI4oP6sU1wE0dF2bC8nM5lO3kR7y', 'super_admin', 1, 1, 1, NOW());

-- Knowledge Base Articles (PBO Act Summaries)
INSERT INTO `chatbot_knowledge_base` (`question_pattern`, `keywords`, `answer_en`, `answer_sw`, `pbo_act_section`, `category`) VALUES
('What is a PBO', 'PBO definition public benefit organization', 'A Public Benefit Organization (PBO) is an organization established for purposes that benefit the public, not primarily for the private benefit of its members. Under the PBO Act 2013, PBOs must register with the PBO Regulatory Authority to operate legally in Kenya.', 'Shirika la Faida kwa Umma (PBO) ni shirika lililoundwa kwa madhumuni yanayofaidisha umma, si kimsingi kwa faida ya kibinafsi ya wanachama wake. Chini ya Sheria ya PBO ya 2013, PBO lazima zisajiliwe na Mamlaka ya Udhibiti wa PBO ili kufanya kazi kisheria nchini Kenya.', 'Section 2', 'definition'),
('How to register a PBO', 'registration process steps requirements', 'To register a PBO in Kenya: 1) Choose your organization type, 2) Prepare constitutional documents, 3) Complete Form PBO-1 (Application for Registration), 4) Pay the registration fee, 5) Submit to PBO Regulatory Authority, 6) Await verification and certificate issuance. The process typically takes 30-90 days.', 'Kusajili PBO nchini Kenya: 1) Chagua aina ya shirika lako, 2) Andaa hati za katiba, 3) Kamilisha Fomu PBO-1, 4) Lipa ada ya usajili, 5) Wasilisha kwa Mamlaka ya Udhibiti wa PBO, 6) Subiri uthibitisho na utoaji wa cheti.', 'Section 10-25', 'registration'),
('PBO compliance requirements', 'compliance annual reporting obligations', 'PBOs must comply with: 1) Annual reporting to PBO Regulatory Authority, 2) Financial auditing requirements, 3) Maintaining proper governance structures, 4) Filing annual returns within 6 months of financial year end, 5) Notifying authority of any changes in leadership or constitution, 6) Maintaining a register of members.', 'PBO lazima zifuate: 1) Kuripoti kila mwaka kwa Mamlaka ya Udhibiti wa PBO, 2) Mahitaji ya ukaguzi wa fedha, 3) Kudumisha miundo sahihi ya utawala, 4) Kuwasilisha ripoti za kila mwaka ndani ya miezi 6 ya mwisho wa mwaka wa fedha.', 'Section 30-45', 'compliance');

-- Compliance Checklist
INSERT INTO `compliance_checklists` (`title`, `description`, `category`) VALUES
('PBO Registration Checklist', 'Complete checklist for PBO registration requirements under the PBO Act 2013', 'registration'),
('Annual Compliance Checklist', 'Annual compliance requirements for registered PBOs', 'compliance'),
('Governance Checklist', 'Governance and management requirements for PBOs', 'governance');

INSERT INTO `checklist_items` (`checklist_id`, `item_text`, `pbo_act_reference`, `is_mandatory`, `weight`, `guidance_notes`) VALUES
(1, 'Organization has a written constitution/memorandum of association', 'Section 12', 1, 2, 'The constitution must include the organization name, objectives, governance structure, and dissolution clause'),
(1, 'Organization has a minimum of 3 founding members', 'Section 11', 1, 2, 'All founding members must be of legal age (18+) and provide valid identification'),
(1, 'Organization has a clear public benefit objective', 'Section 5', 1, 3, 'The objectives must clearly demonstrate public benefit, not private benefit'),
(1, 'Application Form PBO-1 completed and signed', 'Section 10', 1, 2, 'Form must be signed by authorized representative'),
(1, 'Registration fee paid', 'Section 14', 1, 1, 'Current registration fee structure applies'),
(1, 'List of board members provided with ID copies', 'Section 15', 1, 2, 'Include full names, ID numbers, and contact details'),
(2, 'Annual returns filed within 6 months of financial year end', 'Section 35', 1, 3, 'Late filing attracts penalties under the Act'),
(2, 'Audited financial statements prepared', 'Section 37', 1, 3, 'Must be audited by a certified public accountant'),
(2, 'Board meetings held at least 4 times per year', 'Section 28', 1, 2, 'Minutes must be recorded and maintained'),
(2, 'Annual General Meeting held', 'Section 29', 1, 2, 'All members must be notified of AGM at least 21 days in advance'),
(2, 'Changes in leadership reported to authority', 'Section 40', 1, 2, 'Must be reported within 30 days of change'),
(3, 'Board of directors/trustees has at least 3 members', 'Section 22', 1, 2, 'Board composition requirements'),
(3, 'Conflict of interest policy in place', 'Section 31', 1, 2, 'Policy must be documented and signed by board members'),
(3, 'Financial management policies documented', 'Section 36', 1, 3, 'Include procurement, expenditure authorization, and banking policies');

-- FAQs
INSERT INTO `faqs` (`question_en`, `answer_en`, `category`, `pbo_act_reference`) VALUES
('What is the PBO Act 2013?', 'The Public Benefit Organizations Act 2013 is a Kenyan law that governs the registration, operation, and regulation of organizations that exist for public benefit. It established the PBO Regulatory Authority and sets out rights, obligations, and compliance requirements for PBOs operating in Kenya.', 'General', 'PBO Act 2013'),
('Who must register under the PBO Act?', 'Any organization that: (1) is not established for profit, (2) has objectives that benefit the public, (3) operates in Kenya, and (4) receives or intends to receive funding from external sources must register under the PBO Act. This includes NGOs, community organizations, foundations, and similar entities.', 'Registration', 'Section 9'),
('What are the penalties for non-compliance?', 'Penalties for non-compliance include: fines ranging from KES 50,000 to KES 1,000,000, suspension of operations, deregistration, criminal prosecution of responsible officers. Specific penalties vary by the nature and severity of non-compliance.', 'Compliance', 'Section 55-60'),
('Can a foreign organization operate in Kenya as a PBO?', 'Yes, foreign organizations can operate in Kenya but must register with the PBO Regulatory Authority. They must have a local representative and comply with all requirements of the PBO Act, including financial reporting and governance standards.', 'Registration', 'Section 18');

COMMIT;