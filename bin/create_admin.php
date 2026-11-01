<?php

/**
 * Apex HRMS - Safe Administrator Account Initialization Script
 *
 * This script MUST be executed exclusively via PHP Command Line Interface (CLI).
 * It cannot be triggered over HTTP/Web to prevent unauthorized external access.
 *
 * Usage:
 *   Interactive:
 *     php bin/create_admin.php
 *
 *   Non-interactive:
 *     php bin/create_admin.php <username> <email> <password> [first_name] [last_name]
 */

// Strict CLI enforcement
if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    echo "ERROR: This provisioning tool can only be executed via the command line (CLI).\n";
    exit(1);
}

// Bootstrap autoloader, environment, and services
require_once dirname(__DIR__) . '/app/bootstrap.php';

use Config\Database;
use App\Models\User;
use App\Models\Role;
use App\Models\Employee;
use App\Models\AuditLog;

echo "\n=======================================================\n";
echo "      APEX HRMS - SECURE ADMINISTRATOR PROVISIONING     \n";
echo "=======================================================\n\n";

try {
    $pdo = Database::getConnection();
    echo "[✓] Connected successfully to database.\n";
} catch (\Throwable $e) {
    echo "[✗] Database connection failed: " . $e->getMessage() . "\n";
    echo "    Please verify that MySQL is running in XAMPP and your database/schema.sql has been imported.\n";
    exit(1);
}

// Ensure Administrator role exists
$adminRole = Role::findBySlug('admin');
if (!$adminRole) {
    echo "[✗] Error: 'admin' role was not found in the roles table.\n";
    echo "    Please import database/schema.sql into your MySQL database before running this script.\n";
    exit(1);
}

// Check existing admin count
$hasAdmin = User::hasAnyAdmin();
if ($hasAdmin) {
    echo "[!] Notice: An active Administrator account already exists in this database.\n";
    echo "    Do you want to create an additional Administrator account? [y/N]: ";
    $confirm = strtolower(trim(fgets(STDIN) ?: 'n'));
    if ($confirm !== 'y' && $confirm !== 'yes') {
        echo "Aborted by user. Existing administrator credentials remain untouched.\n";
        exit(0);
    }
}

// Parse arguments or prompt interactively
$argvArgs = array_slice($argv, 1);

if (count($argvArgs) >= 3) {
    $username  = trim($argvArgs[0]);
    $email     = trim($argvArgs[1]);
    $password  = $argvArgs[2];
    $firstName = trim($argvArgs[3] ?? 'System');
    $lastName  = trim($argvArgs[4] ?? 'Administrator');
} else {
    echo "\nPlease enter the administrator details:\n";

    do {
        echo "Username [e.g. admin]: ";
        $username = trim(fgets(STDIN));
    } while ($username === '');

    do {
        echo "Email address [e.g. admin@company.org]: ";
        $email = trim(fgets(STDIN));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo " [!] Invalid email format. Please re-enter.\n";
            $email = '';
        }
    } while ($email === '');

    do {
        echo "Password (min 8 characters): ";
        $password = trim(fgets(STDIN));
        if (strlen($password) < 8) {
            echo " [!] Password too short (minimum 8 characters required).\n";
            $password = '';
        }
    } while ($password === '');

    echo "First Name [Default: System]: ";
    $firstName = trim(fgets(STDIN)) ?: 'System';

    echo "Last Name [Default: Administrator]: ";
    $lastName = trim(fgets(STDIN)) ?: 'Administrator';
}

// Check if username or email already exists
$existing = User::findByUsernameOrEmail($username);
if ($existing) {
    echo "[✗] Error: Username '{$username}' or email '{$email}' already exists.\n";
    exit(1);
}

$existingEmail = User::findByUsernameOrEmail($email);
if ($existingEmail) {
    echo "[✗] Error: Email '{$email}' is already registered.\n";
    exit(1);
}

// Create employee record for this administrator if not already present
$employeeCode = 'EMP-ADM-' . rand(100, 999);
$employeeId = null;

try {
    // Check if HR department (id 1) and HR Director (id 1) exist
    $employeeId = Employee::create([
        'employee_code'     => $employeeCode,
        'first_name'        => $firstName,
        'last_name'         => $lastName,
        'email'             => $email,
        'phone'             => '+1 (555) 000-0001',
        'hire_date'         => date('Y-m-d'),
        'department_id'     => 1, // Human Resources
        'designation_id'    => 1, // HR Director / Head
        'manager_id'        => null,
        'employment_status' => 'active'
    ]);
    echo "[✓] Created associated employee profile [{$employeeCode} - {$firstName} {$lastName}].\n";
} catch (\Throwable $e) {
    // If employee table already has email or fails, continue without linking employee
    echo "[!] Note: Could not attach new employee profile: " . $e->getMessage() . "\n";
}

// Hash password with strong native algorithm
$passwordHash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

// Create user account
try {
    $userId = User::create([
        'employee_id'   => $employeeId,
        'username'      => $username,
        'email'         => $email,
        'password_hash' => $passwordHash,
        'role_id'       => (int)$adminRole['id'],
        'status'        => 'active'
    ]);

    AuditLog::record($userId, 'ADMIN_INITIALIZED', 'User', $userId, "Initial administrator account '{$username}' created via CLI");

    echo "\n=======================================================\n";
    echo "  [SUCCESS] Administrator Account Successfully Created!\n";
    echo "=======================================================\n";
    echo "  Username: {$username}\n";
    echo "  Email:    {$email}\n";
    echo "  Role:     {$adminRole['name']} (Full Privileges)\n";
    echo "  Status:   Active\n";
    echo "-------------------------------------------------------\n";
    echo "  You can now log in at: " . env('APP_URL', 'http://localhost/HRM%20SYSTEM/public') . "/login\n\n";

} catch (\Throwable $e) {
    echo "[✗] Failed to create user account: " . $e->getMessage() . "\n";
    exit(1);
}
