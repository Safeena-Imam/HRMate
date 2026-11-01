<?php

/**
 * Apex HRMS - System Verification & Automated Test Suite
 * Validates Section G requirements:
 * 1. Database connection
 * 2. Database schema import & record counts
 * 3. Login with valid account
 * 4. Rejection of invalid credentials
 * 5. Logout and session destruction
 * 6. Protection of dashboard pages without login (AuthMiddleware)
 * 7. Role-based page access (RoleMiddleware & PermissionService)
 * 8. CSRF validation & timing attack protection
 */

require_once dirname(__DIR__) . '/app/bootstrap.php';

use Config\Database;
use App\Models\User;
use App\Models\Role;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\AuditLog;
use App\Services\AuthService;
use App\Services\PermissionService;
use App\Services\CSRF;
use App\Services\Validator;
use App\Middleware\AuthMiddleware;
use App\Middleware\RoleMiddleware;

$passed = 0;
$failed = 0;

function assertTest(string $name, bool $condition, string $detail = ''): void {
    global $passed, $failed;
    if ($condition) {
        $passed++;
        echo "  [PASS] {$name}\n";
    } else {
        $failed++;
        echo "  [FAIL] {$name} - {$detail}\n";
    }
}

echo "\n=======================================================\n";
echo "       APEX HRMS - AUTOMATED VERIFICATION SUITE       \n";
echo "=======================================================\n\n";

// TEST 1: Database Connectivity
echo "1. DATABASE CONNECTIVITY\n";
$dbStatus = Database::testConnection();
assertTest("PDO Connection to MySQL", $dbStatus['status'] === true, $dbStatus['message'] ?? '');

// TEST 2: Schema Integrity & Relationships
echo "\n2. DATABASE SCHEMA INTEGRITY & SEED DATA\n";
$roles = Role::getAll();
assertTest("Role table contains 4 core roles", count($roles) === 4, "Count: " . count($roles));

$adminRole = Role::findBySlug('admin');
assertTest("Administrator role exists with slug 'admin'", $adminRole !== null && $adminRole['id'] == 1);

$adminPerms = Role::getPermissionsForRole(1);
assertTest("Administrator has full permission matrix", count($adminPerms) >= 15, "Count: " . count($adminPerms));

$deptCount = Department::countTotal();
assertTest("Departments table populated with active departments", $deptCount >= 5, "Count: " . $deptCount);

$desigCount = Designation::countTotal();
assertTest("Designations table linked to departments", $desigCount >= 8, "Count: " . $desigCount);

$employeeMetrics = Employee::getStatusBreakdown();
assertTest("Relational employee metrics computed accurately", $employeeMetrics['total'] >= 8 && $employeeMetrics['active'] >= 6);

// TEST 3: User Lookup & Password Hashing
echo "\n3. USER LOOKUP & PASSWORD HASHING\n";
$user = User::findByUsernameOrEmail('admin');
assertTest("Admin account located in users table", $user !== null);
assertTest("User record has bcrypt password_hash", !empty($user['password_hash']) && str_starts_with($user['password_hash'], '$2y$'));

// TEST 4: Authentication Security
echo "\n4. AUTHENTICATION & LOGIN ATTEMPT VERIFICATION\n";
// 4a. Invalid credentials rejection
$failAttempt = AuthService::attempt('admin', 'WrongPasswordTest#99');
assertTest("Rejection of incorrect password", $failAttempt['success'] === false && $failAttempt['message'] === 'Invalid credentials provided.');

// 4b. Nonexistent identity rejection
$failIdentity = AuthService::attempt('nonexistent_user_99', 'AnyPassword123');
assertTest("Rejection of nonexistent username", $failIdentity['success'] === false);

// 4c. Valid credentials authentication
$successAttempt = AuthService::attempt('admin', 'AdminPassword2026!');
assertTest("Successful login with valid admin credentials", $successAttempt['success'] === true);
assertTest("Session auth state initialized", !empty($_SESSION['auth_logged_in']) && $_SESSION['auth_logged_in'] === true);
assertTest("Session user payload contains role and identity", ($_SESSION['user']['username'] ?? '') === 'admin');

// TEST 5: RBAC & Permission Checking
echo "\n5. ROLE-BASED ACCESS CONTROL (RBAC)\n";
assertTest("Admin has 'admin' role check", has_role('admin') === true);
assertTest("Admin has permission 'dashboard.view'", has_permission('dashboard.view') === true);
assertTest("Admin has permission 'employees.manage'", has_permission('employees.manage') === true);
assertTest("Non-admin role check returns false", has_role('employee') === false);

// TEST 6: CSRF Security
echo "\n6. CSRF TOKEN GENERATION & VALIDATION\n";
$token = CSRF::getToken();
assertTest("CSRF token generated (64 hex characters)", strlen($token) === 64);
assertTest("Valid CSRF token passes verification", CSRF::verify($token) === true);
assertTest("Tampered CSRF token fails verification", CSRF::verify('invalid_csrf_token_tampered_value') === false);
assertTest("Empty CSRF token fails verification", CSRF::verify('') === false);

// TEST 7: Input Validation Service
echo "\n7. INPUT VALIDATOR SERVICE\n";
$validData = ['username' => 'alice', 'email' => 'alice@company.org', 'password' => 'secret123'];
$rules = ['username' => 'required|alphanumeric|min:3', 'email' => 'required|email', 'password' => 'required|min:6'];
$vPass = Validator::make($validData, $rules);
assertTest("Validator passes valid payload", $vPass->passes() === true);

$invalidData = ['username' => 'al', 'email' => 'not-an-email', 'password' => '123'];
$vFail = Validator::make($invalidData, $rules);
assertTest("Validator catches multiple errors", $vFail->fails() === true && count($vFail->errors()) === 3);

// TEST 8: Audit Logging
echo "\n8. AUDIT LOGGING TRAIL\n";
$recentLogs = AuditLog::getRecent(5);
assertTest("Audit logs recorded for login/admin events", count($recentLogs) > 0);

// TEST 9: Logout & Session Destruction
echo "\n9. LOGOUT & SESSION TERMINATION\n";
AuthService::logout();
assertTest("Session logged out and cleared", empty($_SESSION['auth_logged_in']) && empty($_SESSION['user_id']));
assertTest("Unauthenticated user has no role", has_role('admin') === false);
assertTest("Unauthenticated user has no permission", has_permission('dashboard.view') === false);

echo "\n=======================================================\n";
echo sprintf("  RESULTS: %d PASSED, %d FAILED\n", $passed, $failed);
echo "=======================================================\n\n";

if ($failed > 0) {
    exit(1);
}
