-- Advanced Human Resource Management System (HRMS)
-- Database Schema Definition
-- Compatible with MySQL 8.0+ and MariaDB 10.4+

CREATE DATABASE IF NOT EXISTS `hrm_system` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `hrm_system`;

-- Disable foreign key checks during schema creation
SET FOREIGN_KEY_CHECKS = 0;

-- -------------------------------------------------------------
-- 1. Table: roles
-- -------------------------------------------------------------
DROP TABLE IF EXISTS `role_permissions`;
DROP TABLE IF EXISTS `audit_logs`;
DROP TABLE IF EXISTS `users`;
DROP TABLE IF EXISTS `employees`;
DROP TABLE IF EXISTS `designations`;
DROP TABLE IF EXISTS `departments`;
DROP TABLE IF EXISTS `permissions`;
DROP TABLE IF EXISTS `roles`;

CREATE TABLE `roles` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(50) NOT NULL UNIQUE,
    `slug` VARCHAR(50) NOT NULL UNIQUE,
    `description` VARCHAR(255) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- 2. Table: permissions
-- -------------------------------------------------------------
CREATE TABLE `permissions` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `slug` VARCHAR(100) NOT NULL UNIQUE,
    `module` VARCHAR(50) NOT NULL,
    `description` VARCHAR(255) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- 3. Table: role_permissions (Many-to-Many Pivot)
-- -------------------------------------------------------------
CREATE TABLE `role_permissions` (
    `role_id` INT NOT NULL,
    `permission_id` INT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`role_id`, `permission_id`),
    CONSTRAINT `fk_rp_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_rp_permission` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- 4. Table: departments
-- -------------------------------------------------------------
CREATE TABLE `departments` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `code` VARCHAR(20) NOT NULL UNIQUE,
    `name` VARCHAR(100) NOT NULL UNIQUE,
    `description` TEXT NULL,
    `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_dept_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- 5. Table: designations
-- -------------------------------------------------------------
CREATE TABLE `designations` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `department_id` INT NOT NULL,
    `code` VARCHAR(20) NOT NULL,
    `title` VARCHAR(100) NOT NULL,
    `description` TEXT NULL,
    `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_dept_designation` (`department_id`, `title`),
    INDEX `idx_desig_dept` (`department_id`),
    INDEX `idx_desig_status` (`status`),
    CONSTRAINT `fk_desig_dept` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- 6. Table: employees
-- -------------------------------------------------------------
CREATE TABLE `employees` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `employee_code` VARCHAR(30) NOT NULL UNIQUE,
    `first_name` VARCHAR(60) NOT NULL,
    `last_name` VARCHAR(60) NOT NULL,
    `email` VARCHAR(120) NOT NULL UNIQUE,
    `phone` VARCHAR(30) NULL,
    `hire_date` DATE NOT NULL,
    `department_id` INT NOT NULL,
    `designation_id` INT NOT NULL,
    `manager_id` INT NULL,
    `employment_status` ENUM('active', 'inactive', 'on_leave', 'terminated') NOT NULL DEFAULT 'active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_emp_code` (`employee_code`),
    INDEX `idx_emp_email` (`email`),
    INDEX `idx_emp_dept` (`department_id`),
    INDEX `idx_emp_desig` (`designation_id`),
    INDEX `idx_emp_manager` (`manager_id`),
    INDEX `idx_emp_status` (`employment_status`),
    CONSTRAINT `fk_emp_dept` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE RESTRICT,
    CONSTRAINT `fk_emp_desig` FOREIGN KEY (`designation_id`) REFERENCES `designations` (`id`) ON DELETE RESTRICT,
    CONSTRAINT `fk_emp_manager` FOREIGN KEY (`manager_id`) REFERENCES `employees` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- 7. Table: users
-- -------------------------------------------------------------
CREATE TABLE `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `employee_id` INT NULL UNIQUE,
    `username` VARCHAR(60) NOT NULL UNIQUE,
    `email` VARCHAR(120) NOT NULL UNIQUE,
    `password_hash` VARCHAR(255) NOT NULL,
    `role_id` INT NOT NULL,
    `status` ENUM('active', 'inactive', 'suspended') NOT NULL DEFAULT 'active',
    `last_login_at` DATETIME NULL,
    `last_login_ip` VARCHAR(45) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_user_login` (`username`),
    INDEX `idx_user_email` (`email`),
    INDEX `idx_user_role` (`role_id`),
    INDEX `idx_user_status` (`status`),
    CONSTRAINT `fk_user_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_user_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- 8. Table: audit_logs
-- -------------------------------------------------------------
CREATE TABLE `audit_logs` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NULL,
    `action` VARCHAR(100) NOT NULL,
    `entity_type` VARCHAR(60) NULL,
    `entity_id` INT NULL,
    `description` TEXT NULL,
    `ip_address` VARCHAR(45) NULL,
    `user_agent` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_audit_user` (`user_id`),
    INDEX `idx_audit_action` (`action`),
    INDEX `idx_audit_created` (`created_at`),
    CONSTRAINT `fk_audit_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1;

-- =============================================================
-- SEED DATA: Roles & Permissions
-- =============================================================

INSERT INTO `roles` (`id`, `name`, `slug`, `description`) VALUES
(1, 'Administrator', 'admin', 'Full system access and configuration privileges'),
(2, 'HR Officer', 'hr_officer', 'Manages personnel, onboarding, attendance, and records'),
(3, 'Department Manager', 'dept_manager', 'Oversees team members, leave requests, and performance'),
(4, 'Employee', 'employee', 'Standard self-service access to own profile and records');

INSERT INTO `permissions` (`id`, `name`, `slug`, `module`, `description`) VALUES
(1, 'View Dashboard', 'dashboard.view', 'dashboard', 'Access main organization metrics and summaries'),
(2, 'Manage Employees', 'employees.manage', 'employees', 'Create, update, and manage employee records'),
(3, 'View Employees', 'employees.view', 'employees', 'View organizational directory and employee details'),
(4, 'Manage Departments', 'departments.manage', 'departments', 'Create and modify organizational departments'),
(5, 'View Departments', 'departments.view', 'departments', 'Browse company department listings'),
(6, 'Manage Designations', 'designations.manage', 'designations', 'Create and manage position titles and tiers'),
(7, 'View Designations', 'designations.view', 'designations', 'Browse company designations'),
(8, 'Manage Users', 'users.manage', 'users', 'Create and manage user authentication accounts'),
(9, 'View Users', 'users.view', 'users', 'View user accounts list'),
(10, 'Manage Roles & Permissions', 'roles.manage', 'roles', 'Configure RBAC permissions matrices'),
(11, 'View Audit Logs', 'audit.view', 'audit', 'Examine security trails and administrative events'),
(12, 'View Attendance', 'attendance.view', 'attendance', 'View employee attendance logs and timesheets'),
(13, 'Manage Attendance', 'attendance.manage', 'attendance', 'Approve and modify attendance entries'),
(14, 'View Payroll', 'payroll.view', 'payroll', 'Inspect salary structures and pay slips'),
(15, 'Manage Payroll', 'payroll.manage', 'payroll', 'Process payroll and salary adjustments');

-- Administrator Permissions (All 1-15)
INSERT INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 1, `id` FROM `permissions`;

-- HR Officer Permissions
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES
(2, 1),  -- dashboard.view
(2, 2),  -- employees.manage
(2, 3),  -- employees.view
(2, 4),  -- departments.manage
(2, 5),  -- departments.view
(2, 6),  -- designations.manage
(2, 7),  -- designations.view
(2, 9),  -- users.view
(2, 11), -- audit.view
(2, 12), -- attendance.view
(2, 13), -- attendance.manage
(2, 14), -- payroll.view
(2, 15); -- payroll.manage

-- Department Manager Permissions
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES
(3, 1),  -- dashboard.view
(3, 3),  -- employees.view
(3, 5),  -- departments.view
(3, 7),  -- designations.view
(3, 12); -- attendance.view

-- Employee Permissions
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES
(4, 1),  -- dashboard.view
(4, 12); -- attendance.view

-- =============================================================
-- SEED DATA: Departments & Designations
-- =============================================================

INSERT INTO `departments` (`id`, `code`, `name`, `description`, `status`) VALUES
(1, 'DEP-HR', 'Human Resources', 'People operations, talent acquisition, and organizational development', 'active'),
(2, 'DEP-ENG', 'Engineering & IT', 'Software engineering, infrastructure, DevOps, and technical support', 'active'),
(3, 'DEP-FIN', 'Finance & Accounts', 'Financial planning, accounting, tax, and budget management', 'active'),
(4, 'DEP-MKT', 'Marketing & Sales', 'Brand strategy, client acquisition, and public relations', 'active'),
(5, 'DEP-OPS', 'Operations & Logistics', 'Supply chain, vendor management, and internal operational facilities', 'active');

INSERT INTO `designations` (`id`, `department_id`, `code`, `title`, `description`, `status`) VALUES
(1, 1, 'DES-HRD', 'HR Director', 'Head of human capital strategy and compliance', 'active'),
(2, 1, 'DES-HRS', 'Senior HR Specialist', 'Oversees recruitment and employee relations', 'active'),
(3, 2, 'DES-CTO', 'Chief Technology Officer', 'Leads technological roadmap and architecture', 'active'),
(4, 2, 'DES-SWE', 'Senior Software Engineer', 'Full-stack application and systems engineer', 'active'),
(5, 2, 'DES-QA', 'QA Automation Engineer', 'Quality assurance and test automation engineer', 'active'),
(6, 3, 'DES-FM', 'Finance Manager', 'Manages corporate financial ledgers and audits', 'active'),
(7, 3, 'DES-ACC', 'Senior Accountant', 'Handles general ledger and accounts payable', 'active'),
(8, 4, 'DES-ML', 'Marketing Lead', 'Drives campaign growth and digital reach', 'active'),
(9, 5, 'DES-OM', 'Operations Manager', 'Supervises workflow throughput and facilities', 'active');

-- =============================================================
-- SEED DATA: Initial Sample Employees
-- (Real relational hierarchy: Jane Doe manages John Smith and Alice Johnson)
-- =============================================================

INSERT INTO `employees` (`id`, `employee_code`, `first_name`, `last_name`, `email`, `phone`, `hire_date`, `department_id`, `designation_id`, `manager_id`, `employment_status`) VALUES
(1, 'EMP-1001', 'Eleanor', 'Vance', 'eleanor.vance@company.org', '+1 (555) 019-2831', '2021-03-15', 1, 1, NULL, 'active'),
(2, 'EMP-1002', 'Marcus', 'Sterling', 'marcus.sterling@company.org', '+1 (555) 019-4729', '2021-06-01', 2, 3, NULL, 'active'),
(3, 'EMP-1003', 'Sophia', 'Chen', 'sophia.chen@company.org', '+1 (555) 019-8392', '2022-01-10', 2, 4, 2, 'active'),
(4, 'EMP-1004', 'David', 'Miller', 'david.miller@company.org', '+1 (555) 019-5820', '2022-04-18', 3, 6, NULL, 'active'),
(5, 'EMP-1005', 'Amara', 'Okonkwo', 'amara.okonkwo@company.org', '+1 (555) 019-3914', '2022-09-05', 4, 8, NULL, 'active'),
(6, 'EMP-1006', 'Lucas', 'Bennett', 'lucas.bennett@company.org', '+1 (555) 019-9021', '2023-02-20', 2, 5, 2, 'active'),
(7, 'EMP-1007', 'Clara', 'Oswald', 'clara.oswald@company.org', '+1 (555) 019-1123', '2020-11-01', 5, 9, NULL, 'inactive'),
(8, 'EMP-1008', 'Julian', 'Ross', 'julian.ross@company.org', '+1 (555) 019-7744', '2023-08-14', 1, 2, 1, 'on_leave');
