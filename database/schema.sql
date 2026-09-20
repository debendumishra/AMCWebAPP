-- ====================================================================
-- COMPUTER AMC, FIELD SERVICE, INVENTORY & BILLING DATABASE SCHEMA
-- Target: MySQL 8.0+ / MariaDB 10.5+
-- ====================================================================

SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

-- 1. Document Number Sequences (Atomic generation)
CREATE TABLE IF NOT EXISTS `document_sequences` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `seq_type` VARCHAR(50) NOT NULL,
  `seq_year` VARCHAR(10) NOT NULL,
  `last_number` INT UNSIGNED NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_seq_type_year` (`seq_type`, `seq_year`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. User Roles
CREATE TABLE IF NOT EXISTS `roles` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(50) NOT NULL,
  `description` VARCHAR(255) DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_role_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Granular Permissions
CREATE TABLE IF NOT EXISTS `permissions` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `module` VARCHAR(50) NOT NULL,
  `action` VARCHAR(50) NOT NULL,
  `description` VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_perm_module_action` (`module`, `action`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Role Permissions Mapping
CREATE TABLE IF NOT EXISTS `role_permissions` (
  `role_id` INT UNSIGNED NOT NULL,
  `permission_id` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`role_id`, `permission_id`),
  CONSTRAINT `fk_rp_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_rp_perm` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. System Users
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `role_id` INT UNSIGNED NOT NULL,
  `customer_id` INT UNSIGNED DEFAULT NULL,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `mobile` VARCHAR(20) NOT NULL,
  `alternate_mobile` VARCHAR(20) DEFAULT NULL,
  `employee_code` VARCHAR(50) DEFAULT NULL,
  `avatar` VARCHAR(255) DEFAULT NULL,
  `skills` TEXT DEFAULT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `last_login` DATETIME DEFAULT NULL,
  `last_ip` VARCHAR(45) DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_user_email` (`email`),
  KEY `idx_user_role` (`role_id`),
  KEY `idx_user_mobile` (`mobile`),
  CONSTRAINT `fk_users_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Login History
CREATE TABLE IF NOT EXISTS `login_history` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED DEFAULT NULL,
  `email_attempted` VARCHAR(150) NOT NULL,
  `ip_address` VARCHAR(45) NOT NULL,
  `user_agent` TEXT DEFAULT NULL,
  `status` ENUM('SUCCESS', 'FAILED') NOT NULL DEFAULT 'SUCCESS',
  `attempted_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_lh_user` (`user_id`),
  KEY `idx_lh_time` (`attempted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. System Settings
CREATE TABLE IF NOT EXISTS `system_settings` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `setting_key` VARCHAR(100) NOT NULL,
  `setting_value` LONGTEXT DEFAULT NULL,
  `setting_group` VARCHAR(50) DEFAULT 'general',
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. Audit Logs
CREATE TABLE IF NOT EXISTS `audit_logs` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED DEFAULT NULL,
  `action` VARCHAR(50) NOT NULL,
  `module` VARCHAR(100) NOT NULL,
  `record_id` INT UNSIGNED NOT NULL,
  `old_values` JSON DEFAULT NULL,
  `new_values` JSON DEFAULT NULL,
  `ip_address` VARCHAR(45) DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_audit_module_record` (`module`, `record_id`),
  KEY `idx_audit_user` (`user_id`),
  KEY `idx_audit_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. Customers Master
CREATE TABLE IF NOT EXISTS `customers` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `customer_code` VARCHAR(50) NOT NULL,
  `company_name` VARCHAR(200) NOT NULL,
  `contact_person` VARCHAR(100) NOT NULL,
  `designation` VARCHAR(100) DEFAULT NULL,
  `mobile` VARCHAR(20) NOT NULL,
  `alternate_mobile` VARCHAR(20) DEFAULT NULL,
  `email` VARCHAR(150) NOT NULL,
  `gstin` VARCHAR(20) DEFAULT NULL,
  `pan` VARCHAR(20) DEFAULT NULL,
  `billing_address` TEXT NOT NULL,
  `city` VARCHAR(100) DEFAULT NULL,
  `state` VARCHAR(100) DEFAULT NULL,
  `pincode` VARCHAR(20) DEFAULT NULL,
  `customer_type` ENUM('AMC', 'PAID', 'BOTH') NOT NULL DEFAULT 'AMC',
  `status` ENUM('ACTIVE', 'INACTIVE', 'SUSPENDED') NOT NULL DEFAULT 'ACTIVE',
  `notes` TEXT DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_customer_code` (`customer_code`),
  KEY `idx_cust_mobile` (`mobile`),
  KEY `idx_cust_company` (`company_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 10. Customer Locations (Unlimited per customer)
CREATE TABLE IF NOT EXISTS `customer_locations` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `customer_id` INT UNSIGNED NOT NULL,
  `location_name` VARCHAR(150) NOT NULL,
  `address` TEXT NOT NULL,
  `city` VARCHAR(100) NOT NULL,
  `state` VARCHAR(100) NOT NULL,
  `pincode` VARCHAR(20) NOT NULL,
  `contact_person` VARCHAR(100) DEFAULT NULL,
  `mobile` VARCHAR(20) DEFAULT NULL,
  `email` VARCHAR(150) DEFAULT NULL,
  `latitude` DECIMAL(10, 8) DEFAULT NULL,
  `longitude` DECIMAL(11, 8) DEFAULT NULL,
  `working_hours` VARCHAR(100) DEFAULT '09:00 AM - 06:00 PM',
  `remarks` TEXT DEFAULT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_loc_customer` (`customer_id`),
  KEY `idx_loc_city` (`city`),
  CONSTRAINT `fk_loc_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 11. Customer Contacts
CREATE TABLE IF NOT EXISTS `customer_contacts` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `customer_id` INT UNSIGNED NOT NULL,
  `location_id` INT UNSIGNED DEFAULT NULL,
  `name` VARCHAR(100) NOT NULL,
  `designation` VARCHAR(100) DEFAULT NULL,
  `department` VARCHAR(100) DEFAULT NULL,
  `mobile` VARCHAR(20) NOT NULL,
  `email` VARCHAR(150) DEFAULT NULL,
  `is_primary` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_cc_customer` (`customer_id`),
  CONSTRAINT `fk_cc_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 12. Asset Types Master
CREATE TABLE IF NOT EXISTS `asset_types` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `category` VARCHAR(50) DEFAULT 'HARDWARE',
  `icon` VARCHAR(50) DEFAULT 'bi-pc-display',
  `description` VARCHAR(255) DEFAULT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_asset_type_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 13. Machines / IT Assets Master
CREATE TABLE IF NOT EXISTS `machines` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `asset_code` VARCHAR(50) NOT NULL,
  `asset_tag` VARCHAR(100) DEFAULT NULL,
  `customer_id` INT UNSIGNED NOT NULL,
  `location_id` INT UNSIGNED NOT NULL,
  `asset_type_id` INT UNSIGNED NOT NULL,
  `building` VARCHAR(100) DEFAULT NULL,
  `floor` VARCHAR(50) DEFAULT NULL,
  `department` VARCHAR(100) DEFAULT NULL,
  `assigned_employee` VARCHAR(100) DEFAULT NULL,
  `make` VARCHAR(100) NOT NULL,
  `model` VARCHAR(100) NOT NULL,
  `serial_number` VARCHAR(100) NOT NULL,
  `processor` VARCHAR(100) DEFAULT NULL,
  `ram` VARCHAR(50) DEFAULT NULL,
  `storage` VARCHAR(100) DEFAULT NULL,
  `operating_system` VARCHAR(100) DEFAULT NULL,
  `ip_address` VARCHAR(45) DEFAULT NULL,
  `mac_address` VARCHAR(45) DEFAULT NULL,
  `purchase_date` DATE DEFAULT NULL,
  `installation_date` DATE DEFAULT NULL,
  `warranty_start` DATE DEFAULT NULL,
  `warranty_expiry` DATE DEFAULT NULL,
  `amc_start` DATE DEFAULT NULL,
  `amc_expiry` DATE DEFAULT NULL,
  `status` ENUM('ACTIVE', 'UNDER_AMC', 'UNDER_REPAIR', 'STANDBY', 'RETIRED', 'DISPOSED') NOT NULL DEFAULT 'ACTIVE',
  `qr_code_token` VARCHAR(100) DEFAULT NULL,
  `photo` VARCHAR(255) DEFAULT NULL,
  `remarks` TEXT DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_machine_asset_code` (`asset_code`),
  UNIQUE KEY `idx_machine_qr` (`qr_code_token`),
  KEY `idx_machine_customer` (`customer_id`),
  KEY `idx_machine_location` (`location_id`),
  KEY `idx_machine_serial` (`serial_number`),
  KEY `idx_machine_type` (`asset_type_id`),
  CONSTRAINT `fk_mach_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`),
  CONSTRAINT `fk_mach_location` FOREIGN KEY (`location_id`) REFERENCES `customer_locations` (`id`),
  CONSTRAINT `fk_mach_type` FOREIGN KEY (`asset_type_id`) REFERENCES `asset_types` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 14. Machine History & Timeline
CREATE TABLE IF NOT EXISTS `machine_history` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `machine_id` INT UNSIGNED NOT NULL,
  `event_type` VARCHAR(50) NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `reference_id` VARCHAR(50) DEFAULT NULL,
  `performed_by` INT UNSIGNED DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_mh_machine` (`machine_id`),
  KEY `idx_mh_time` (`created_at`),
  CONSTRAINT `fk_mh_machine` FOREIGN KEY (`machine_id`) REFERENCES `machines` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 15. Contract Types
CREATE TABLE IF NOT EXISTS `contract_types` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `code` VARCHAR(50) NOT NULL,
  `is_labour_covered` TINYINT(1) NOT NULL DEFAULT 1,
  `is_spares_covered` TINYINT(1) NOT NULL DEFAULT 0,
  `is_visits_covered` TINYINT(1) NOT NULL DEFAULT 1,
  `pm_frequency` ENUM('NONE', 'MONTHLY', 'QUARTERLY', 'HALF_YEARLY', 'ANNUAL') NOT NULL DEFAULT 'QUARTERLY',
  `description` TEXT DEFAULT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_ct_code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 16. Contracts Master
CREATE TABLE IF NOT EXISTS `contracts` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `contract_number` VARCHAR(50) NOT NULL,
  `customer_id` INT UNSIGNED NOT NULL,
  `contract_type_id` INT UNSIGNED NOT NULL,
  `title` VARCHAR(200) NOT NULL,
  `start_date` DATE NOT NULL,
  `end_date` DATE NOT NULL,
  `contract_value` DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
  `billing_frequency` ENUM('ANNUAL', 'HALF_YEARLY', 'QUARTERLY', 'MONTHLY', 'ONE_TIME') NOT NULL DEFAULT 'ANNUAL',
  `payment_terms` VARCHAR(100) DEFAULT 'Advance',
  `gst_rate` DECIMAL(5, 2) NOT NULL DEFAULT 18.00,
  `response_time_hrs` INT NOT NULL DEFAULT 4,
  `resolution_time_hrs` INT NOT NULL DEFAULT 24,
  `renewal_reminder_days` INT NOT NULL DEFAULT 30,
  `status` ENUM('DRAFT', 'ACTIVE', 'EXPIRED', 'TERMINATED', 'RENEWED') NOT NULL DEFAULT 'ACTIVE',
  `document_file` VARCHAR(255) DEFAULT NULL,
  `remarks` TEXT DEFAULT NULL,
  `created_by` INT UNSIGNED DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_contract_no` (`contract_number`),
  KEY `idx_contract_cust` (`customer_id`),
  KEY `idx_contract_dates` (`start_date`, `end_date`),
  CONSTRAINT `fk_contract_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`),
  CONSTRAINT `fk_contract_type` FOREIGN KEY (`contract_type_id`) REFERENCES `contract_types` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 17. Contract Machines Mapping
CREATE TABLE IF NOT EXISTS `contract_machines` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `contract_id` INT UNSIGNED NOT NULL,
  `machine_id` INT UNSIGNED NOT NULL,
  `machine_rate` DECIMAL(10, 2) DEFAULT 0.00,
  `start_date` DATE NOT NULL,
  `end_date` DATE NOT NULL,
  `status` ENUM('ACTIVE', 'REMOVED', 'EXPIRED') NOT NULL DEFAULT 'ACTIVE',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_cm_contract_machine` (`contract_id`, `machine_id`),
  KEY `idx_cm_machine` (`machine_id`),
  CONSTRAINT `fk_cm_contract` FOREIGN KEY (`contract_id`) REFERENCES `contracts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_cm_machine` FOREIGN KEY (`machine_id`) REFERENCES `machines` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 18. SLA Master
CREATE TABLE IF NOT EXISTS `sla_master` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `priority` ENUM('CRITICAL', 'HIGH', 'MEDIUM', 'LOW') NOT NULL,
  `call_type` VARCHAR(50) NOT NULL DEFAULT 'ALL',
  `response_time_minutes` INT NOT NULL DEFAULT 120,
  `resolution_time_minutes` INT NOT NULL DEFAULT 480,
  `escalate_after_minutes` INT DEFAULT 360,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_sla_priority_type` (`priority`, `call_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 19. Problem Categories & Problems
CREATE TABLE IF NOT EXISTS `problem_categories` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `asset_type_id` INT UNSIGNED DEFAULT NULL,
  `description` VARCHAR(255) DEFAULT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `problems` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `category_id` INT UNSIGNED NOT NULL,
  `name` VARCHAR(200) NOT NULL,
  `default_priority` ENUM('CRITICAL', 'HIGH', 'MEDIUM', 'LOW') NOT NULL DEFAULT 'MEDIUM',
  `estimated_minutes` INT DEFAULT 60,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `idx_problem_cat` (`category_id`),
  CONSTRAINT `fk_problem_category` FOREIGN KEY (`category_id`) REFERENCES `problem_categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 20. Service Calls Master
CREATE TABLE IF NOT EXISTS `calls` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `call_number` VARCHAR(50) NOT NULL,
  `call_type` ENUM('AMC', 'PAID', 'WARRANTY', 'PM', 'INSTALLATION', 'EMERGENCY', 'BREAKDOWN') NOT NULL DEFAULT 'AMC',
  `priority` ENUM('CRITICAL', 'HIGH', 'MEDIUM', 'LOW') NOT NULL DEFAULT 'MEDIUM',
  `customer_id` INT UNSIGNED NOT NULL,
  `location_id` INT UNSIGNED NOT NULL,
  `machine_id` INT UNSIGNED DEFAULT NULL,
  `contract_id` INT UNSIGNED DEFAULT NULL,
  `problem_category_id` INT UNSIGNED DEFAULT NULL,
  `problem_id` INT UNSIGNED DEFAULT NULL,
  `caller_name` VARCHAR(100) NOT NULL,
  `caller_mobile` VARCHAR(20) NOT NULL,
  `caller_email` VARCHAR(150) DEFAULT NULL,
  `reported_issue` TEXT NOT NULL,
  `preferred_time` DATETIME DEFAULT NULL,
  `assigned_engineer_id` INT UNSIGNED DEFAULT NULL,
  `status` VARCHAR(50) NOT NULL DEFAULT 'NEW',
  `sla_response_deadline` DATETIME DEFAULT NULL,
  `sla_resolution_deadline` DATETIME DEFAULT NULL,
  `response_at` DATETIME DEFAULT NULL,
  `resolved_at` DATETIME DEFAULT NULL,
  `closed_at` DATETIME DEFAULT NULL,
  `is_sla_breached` TINYINT(1) NOT NULL DEFAULT 0,
  `is_reopened` TINYINT(1) NOT NULL DEFAULT 0,
  `reopen_reason` TEXT DEFAULT NULL,
  `created_by` INT UNSIGNED DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_call_number` (`call_number`),
  KEY `idx_calls_customer` (`customer_id`),
  KEY `idx_calls_machine` (`machine_id`),
  KEY `idx_calls_status` (`status`),
  KEY `idx_calls_engineer` (`assigned_engineer_id`),
  KEY `idx_calls_priority` (`priority`),
  KEY `idx_calls_created` (`created_at`),
  CONSTRAINT `fk_calls_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`),
  CONSTRAINT `fk_calls_location` FOREIGN KEY (`location_id`) REFERENCES `customer_locations` (`id`),
  CONSTRAINT `fk_calls_engineer` FOREIGN KEY (`assigned_engineer_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 21. Call Status History & Audit
CREATE TABLE IF NOT EXISTS `call_status_history` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `call_id` INT UNSIGNED NOT NULL,
  `old_status` VARCHAR(50) DEFAULT NULL,
  `new_status` VARCHAR(50) NOT NULL,
  `changed_by` INT UNSIGNED DEFAULT NULL,
  `remarks` TEXT DEFAULT NULL,
  `latitude` DECIMAL(10, 8) DEFAULT NULL,
  `longitude` DECIMAL(11, 8) DEFAULT NULL,
  `ip_address` VARCHAR(45) DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_csh_call` (`call_id`),
  CONSTRAINT `fk_csh_call` FOREIGN KEY (`call_id`) REFERENCES `calls` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 22. Call Communications & Timeline
CREATE TABLE IF NOT EXISTS `call_communications` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `call_id` INT UNSIGNED NOT NULL,
  `sender_id` INT UNSIGNED NOT NULL,
  `message` TEXT NOT NULL,
  `attachment` VARCHAR(255) DEFAULT NULL,
  `is_internal_only` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_ccom_call` (`call_id`),
  CONSTRAINT `fk_ccom_call` FOREIGN KEY (`call_id`) REFERENCES `calls` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 23. Engineer Skills & Location Check-ins
CREATE TABLE IF NOT EXISTS `engineer_locations` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `engineer_id` INT UNSIGNED NOT NULL,
  `call_id` INT UNSIGNED DEFAULT NULL,
  `event_type` VARCHAR(50) NOT NULL,
  `latitude` DECIMAL(10, 8) NOT NULL,
  `longitude` DECIMAL(11, 8) NOT NULL,
  `accuracy` DECIMAL(8, 2) DEFAULT NULL,
  `timestamp` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_eng_loc_user` (`engineer_id`),
  KEY `idx_eng_loc_call` (`call_id`),
  CONSTRAINT `fk_el_engineer` FOREIGN KEY (`engineer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 24. Spare Categories, Brands, Types, Capacities & Items
CREATE TABLE IF NOT EXISTS `spare_categories` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `code` VARCHAR(50) DEFAULT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_sc_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `spare_brands` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_sb_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `spare_types` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `category_id` INT UNSIGNED NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_st_category` (`category_id`),
  CONSTRAINT `fk_st_cat` FOREIGN KEY (`category_id`) REFERENCES `spare_categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `spare_items` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `category_id` INT UNSIGNED NOT NULL,
  `brand_id` INT UNSIGNED DEFAULT NULL,
  `type_id` INT UNSIGNED DEFAULT NULL,
  `sku` VARCHAR(100) NOT NULL,
  `name` VARCHAR(200) NOT NULL,
  `capacity_spec` VARCHAR(100) DEFAULT NULL,
  `unit` VARCHAR(20) DEFAULT 'PCS',
  `hsn_code` VARCHAR(20) DEFAULT '8473',
  `purchase_price` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
  `selling_price` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
  `gst_rate` DECIMAL(5, 2) NOT NULL DEFAULT 18.00,
  `min_stock_level` INT NOT NULL DEFAULT 5,
  `max_stock_level` INT NOT NULL DEFAULT 50,
  `warranty_months` INT DEFAULT 12,
  `status` ENUM('ACTIVE', 'INACTIVE') NOT NULL DEFAULT 'ACTIVE',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_spare_sku` (`sku`),
  KEY `idx_spare_category` (`category_id`),
  KEY `idx_spare_brand` (`brand_id`),
  CONSTRAINT `fk_spare_category` FOREIGN KEY (`category_id`) REFERENCES `spare_categories` (`id`),
  CONSTRAINT `fk_spare_brand` FOREIGN KEY (`brand_id`) REFERENCES `spare_brands` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 25. Inventory Stock Summary
CREATE TABLE IF NOT EXISTS `inventory` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `spare_item_id` INT UNSIGNED NOT NULL,
  `warehouse_name` VARCHAR(100) NOT NULL DEFAULT 'Main Store',
  `quantity` INT NOT NULL DEFAULT 0,
  `reserved_quantity` INT NOT NULL DEFAULT 0,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_inv_item_warehouse` (`spare_item_id`, `warehouse_name`),
  CONSTRAINT `fk_inv_item` FOREIGN KEY (`spare_item_id`) REFERENCES `spare_items` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 26. Immutable Stock Ledger (Double-Entry Inventory)
CREATE TABLE IF NOT EXISTS `inventory_transactions` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `spare_item_id` INT UNSIGNED NOT NULL,
  `warehouse_name` VARCHAR(100) NOT NULL DEFAULT 'Main Store',
  `transaction_type` ENUM('OPENING', 'PURCHASE', 'PURCHASE_RETURN', 'ISSUE', 'CONSUMPTION', 'RETURN', 'TRANSFER_IN', 'TRANSFER_OUT', 'DAMAGE', 'ADJUSTMENT') NOT NULL,
  `reference_type` VARCHAR(50) DEFAULT NULL,
  `reference_id` VARCHAR(50) DEFAULT NULL,
  `quantity_in` INT NOT NULL DEFAULT 0,
  `quantity_out` INT NOT NULL DEFAULT 0,
  `unit_price` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
  `balance_after` INT NOT NULL,
  `remarks` TEXT DEFAULT NULL,
  `created_by` INT UNSIGNED DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_it_item` (`spare_item_id`),
  KEY `idx_it_type` (`transaction_type`),
  KEY `idx_it_date` (`created_at`),
  CONSTRAINT `fk_it_item` FOREIGN KEY (`spare_item_id`) REFERENCES `spare_items` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 27. Spare Requisitions & Approval
CREATE TABLE IF NOT EXISTS `spare_requests` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `request_number` VARCHAR(50) NOT NULL,
  `call_id` INT UNSIGNED NOT NULL,
  `engineer_id` INT UNSIGNED NOT NULL,
  `machine_id` INT UNSIGNED DEFAULT NULL,
  `status` ENUM('REQUESTED', 'APPROVED', 'PARTIALLY_APPROVED', 'REJECTED', 'ISSUED', 'USED', 'RETURNED') NOT NULL DEFAULT 'REQUESTED',
  `approved_by` INT UNSIGNED DEFAULT NULL,
  `approval_remarks` TEXT DEFAULT NULL,
  `approved_at` DATETIME DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_srq_number` (`request_number`),
  KEY `idx_srq_call` (`call_id`),
  KEY `idx_srq_engineer` (`engineer_id`),
  CONSTRAINT `fk_srq_call` FOREIGN KEY (`call_id`) REFERENCES `calls` (`id`),
  CONSTRAINT `fk_srq_engineer` FOREIGN KEY (`engineer_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `spare_request_items` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `spare_request_id` INT UNSIGNED NOT NULL,
  `spare_item_id` INT UNSIGNED NOT NULL,
  `requested_qty` INT NOT NULL DEFAULT 1,
  `approved_qty` INT NOT NULL DEFAULT 0,
  `issued_qty` INT NOT NULL DEFAULT 0,
  `is_chargeable` TINYINT(1) NOT NULL DEFAULT 0,
  `unit_price` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
  `status` ENUM('REQUESTED', 'APPROVED', 'REJECTED', 'ISSUED') NOT NULL DEFAULT 'REQUESTED',
  PRIMARY KEY (`id`),
  KEY `idx_sri_req` (`spare_request_id`),
  CONSTRAINT `fk_sri_req` FOREIGN KEY (`spare_request_id`) REFERENCES `spare_requests` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_sri_item` FOREIGN KEY (`spare_item_id`) REFERENCES `spare_items` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 28. Digital Job Cards / Service Reports
CREATE TABLE IF NOT EXISTS `service_reports` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `report_number` VARCHAR(50) NOT NULL,
  `call_id` INT UNSIGNED NOT NULL,
  `engineer_id` INT UNSIGNED NOT NULL,
  `customer_id` INT UNSIGNED NOT NULL,
  `machine_id` INT UNSIGNED DEFAULT NULL,
  `complaint_summary` TEXT DEFAULT NULL,
  `diagnosis` TEXT NOT NULL,
  `action_taken` TEXT NOT NULL,
  `start_time` DATETIME DEFAULT NULL,
  `end_time` DATETIME DEFAULT NULL,
  `engineer_remarks` TEXT DEFAULT NULL,
  `customer_remarks` TEXT DEFAULT NULL,
  `customer_signed_name` VARCHAR(100) DEFAULT NULL,
  `customer_signature_data` LONGTEXT DEFAULT NULL,
  `signed_at` DATETIME DEFAULT NULL,
  `signature_latitude` DECIMAL(10, 8) DEFAULT NULL,
  `signature_longitude` DECIMAL(11, 8) DEFAULT NULL,
  `is_draft` TINYINT(1) NOT NULL DEFAULT 0,
  `pdf_path` VARCHAR(255) DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_sr_number` (`report_number`),
  KEY `idx_sr_call` (`call_id`),
  KEY `idx_sr_engineer` (`engineer_id`),
  CONSTRAINT `fk_sr_call` FOREIGN KEY (`call_id`) REFERENCES `calls` (`id`),
  CONSTRAINT `fk_sr_engineer` FOREIGN KEY (`engineer_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 29. Service Report Photos
CREATE TABLE IF NOT EXISTS `service_report_photos` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `service_report_id` INT UNSIGNED NOT NULL,
  `photo_type` ENUM('BEFORE', 'DURING', 'AFTER', 'DAMAGED_PART', 'SERIAL_TAG') NOT NULL DEFAULT 'AFTER',
  `photo_path` VARCHAR(255) NOT NULL,
  `caption` VARCHAR(255) DEFAULT NULL,
  `uploaded_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_srp_report` (`service_report_id`),
  CONSTRAINT `fk_srp_report` FOREIGN KEY (`service_report_id`) REFERENCES `service_reports` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 30. Suppliers Master
CREATE TABLE IF NOT EXISTS `suppliers` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(150) NOT NULL,
  `contact_person` VARCHAR(100) DEFAULT NULL,
  `mobile` VARCHAR(20) NOT NULL,
  `email` VARCHAR(150) DEFAULT NULL,
  `gstin` VARCHAR(20) DEFAULT NULL,
  `address` TEXT NOT NULL,
  `city` VARCHAR(100) DEFAULT NULL,
  `state` VARCHAR(100) DEFAULT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_sup_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 31. Purchases & Inward Stock
CREATE TABLE IF NOT EXISTS `purchases` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `purchase_number` VARCHAR(50) NOT NULL,
  `supplier_id` INT UNSIGNED NOT NULL,
  `supplier_invoice_no` VARCHAR(100) NOT NULL,
  `purchase_date` DATE NOT NULL,
  `sub_total` DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
  `tax_amount` DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
  `total_amount` DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
  `payment_status` ENUM('PAID', 'PARTIAL', 'UNPAID') NOT NULL DEFAULT 'UNPAID',
  `invoice_file` VARCHAR(255) DEFAULT NULL,
  `remarks` TEXT DEFAULT NULL,
  `created_by` INT UNSIGNED DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_pur_number` (`purchase_number`),
  KEY `idx_pur_supplier` (`supplier_id`),
  CONSTRAINT `fk_pur_supplier` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `purchase_items` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `purchase_id` INT UNSIGNED NOT NULL,
  `spare_item_id` INT UNSIGNED NOT NULL,
  `quantity` INT NOT NULL,
  `rate` DECIMAL(10, 2) NOT NULL,
  `discount_percent` DECIMAL(5, 2) DEFAULT 0.00,
  `gst_percent` DECIMAL(5, 2) DEFAULT 18.00,
  `total` DECIMAL(12, 2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_pi_purchase` (`purchase_id`),
  CONSTRAINT `fk_pi_purchase` FOREIGN KEY (`purchase_id`) REFERENCES `purchases` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_pi_item` FOREIGN KEY (`spare_item_id`) REFERENCES `spare_items` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 32. Invoices & Billing
CREATE TABLE IF NOT EXISTS `invoices` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `invoice_number` VARCHAR(50) NOT NULL,
  `customer_id` INT UNSIGNED NOT NULL,
  `contract_id` INT UNSIGNED DEFAULT NULL,
  `call_id` INT UNSIGNED DEFAULT NULL,
  `invoice_type` ENUM('AMC', 'PAID_SERVICE', 'SPARE_SALE', 'OTHER') NOT NULL DEFAULT 'AMC',
  `invoice_date` DATE NOT NULL,
  `due_date` DATE NOT NULL,
  `sub_total` DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
  `discount_amount` DECIMAL(10, 2) DEFAULT 0.00,
  `cgst_amount` DECIMAL(10, 2) DEFAULT 0.00,
  `sgst_amount` DECIMAL(10, 2) DEFAULT 0.00,
  `igst_amount` DECIMAL(10, 2) DEFAULT 0.00,
  `total_amount` DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
  `paid_amount` DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
  `balance_amount` DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
  `status` ENUM('DRAFT', 'SENT', 'PAID', 'PARTIALLY_PAID', 'OVERDUE', 'CANCELLED') NOT NULL DEFAULT 'SENT',
  `notes` TEXT DEFAULT NULL,
  `created_by` INT UNSIGNED DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_inv_number` (`invoice_number`),
  KEY `idx_inv_customer` (`customer_id`),
  KEY `idx_inv_status` (`status`),
  CONSTRAINT `fk_inv_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `invoice_items` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `invoice_id` INT UNSIGNED NOT NULL,
  `description` VARCHAR(255) NOT NULL,
  `hsn_sac` VARCHAR(20) DEFAULT '9987',
  `quantity` INT NOT NULL DEFAULT 1,
  `unit_price` DECIMAL(10, 2) NOT NULL,
  `discount` DECIMAL(10, 2) DEFAULT 0.00,
  `gst_rate` DECIMAL(5, 2) NOT NULL DEFAULT 18.00,
  `cgst` DECIMAL(10, 2) DEFAULT 0.00,
  `sgst` DECIMAL(10, 2) DEFAULT 0.00,
  `igst` DECIMAL(10, 2) DEFAULT 0.00,
  `total` DECIMAL(12, 2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_ii_invoice` (`invoice_id`),
  CONSTRAINT `fk_ii_invoice` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 33. Payments Received
CREATE TABLE IF NOT EXISTS `payments` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `payment_number` VARCHAR(50) NOT NULL,
  `invoice_id` INT UNSIGNED NOT NULL,
  `customer_id` INT UNSIGNED NOT NULL,
  `payment_date` DATE NOT NULL,
  `amount` DECIMAL(12, 2) NOT NULL,
  `payment_mode` ENUM('CASH', 'UPI', 'BANK_TRANSFER', 'CHEQUE', 'CARD', 'OTHER') NOT NULL DEFAULT 'UPI',
  `transaction_reference` VARCHAR(100) DEFAULT NULL,
  `bank_name` VARCHAR(100) DEFAULT NULL,
  `remarks` TEXT DEFAULT NULL,
  `received_by` INT UNSIGNED DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_pay_invoice` (`invoice_id`),
  KEY `idx_pay_customer` (`customer_id`),
  CONSTRAINT `fk_pay_invoice` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`),
  CONSTRAINT `fk_pay_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 34. Preventive Maintenance (PM) Schedules & Checklists
CREATE TABLE IF NOT EXISTS `pm_schedules` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `contract_id` INT UNSIGNED NOT NULL,
  `customer_id` INT UNSIGNED NOT NULL,
  `machine_id` INT UNSIGNED NOT NULL,
  `schedule_date` DATE NOT NULL,
  `assigned_engineer_id` INT UNSIGNED DEFAULT NULL,
  `call_id` INT UNSIGNED DEFAULT NULL,
  `status` ENUM('PENDING', 'GENERATED', 'COMPLETED', 'SKIPPED') NOT NULL DEFAULT 'PENDING',
  `remarks` TEXT DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_pm_contract` (`contract_id`),
  KEY `idx_pm_machine` (`machine_id`),
  KEY `idx_pm_date` (`schedule_date`),
  CONSTRAINT `fk_pm_contract` FOREIGN KEY (`contract_id`) REFERENCES `contracts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_pm_machine` FOREIGN KEY (`machine_id`) REFERENCES `machines` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `pm_checklists` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `asset_type_id` INT UNSIGNED NOT NULL,
  `title` VARCHAR(150) NOT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `idx_pmc_asset` (`asset_type_id`),
  CONSTRAINT `fk_pmc_asset` FOREIGN KEY (`asset_type_id`) REFERENCES `asset_types` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `pm_checklist_items` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `checklist_id` INT UNSIGNED NOT NULL,
  `task_description` VARCHAR(255) NOT NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  `is_mandatory` TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `idx_pci_checklist` (`checklist_id`),
  CONSTRAINT `fk_pci_checklist` FOREIGN KEY (`checklist_id`) REFERENCES `pm_checklists` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 35. Notifications & Web Push
CREATE TABLE IF NOT EXISTS `notifications` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL,
  `title` VARCHAR(200) NOT NULL,
  `message` TEXT NOT NULL,
  `type` VARCHAR(50) DEFAULT 'info',
  `link_url` VARCHAR(255) DEFAULT NULL,
  `is_read` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_notif_user` (`user_id`, `is_read`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `customer_feedback` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `call_id` INT UNSIGNED NOT NULL,
  `customer_id` INT UNSIGNED NOT NULL,
  `engineer_id` INT UNSIGNED NOT NULL,
  `rating` TINYINT UNSIGNED NOT NULL DEFAULT 5,
  `comments` TEXT DEFAULT NULL,
  `timeliness_rating` TINYINT UNSIGNED DEFAULT 5,
  `technical_competence_rating` TINYINT UNSIGNED DEFAULT 5,
  `behavior_rating` TINYINT UNSIGNED DEFAULT 5,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_cf_call` (`call_id`),
  CONSTRAINT `fk_cf_call` FOREIGN KEY (`call_id`) REFERENCES `calls` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
