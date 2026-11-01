<?php

require_once __DIR__ . '/../app/bootstrap.php';

use Config\Database;
use App\Models\User;
use App\Models\Role;
use App\Models\Employee;

try {
    $pdo = Database::getConnection();

    // Check if Sophia exists in employees
    $empStmt = $pdo->prepare("SELECT id, first_name, last_name, email FROM employees WHERE employee_code = 'EMP-1003' OR email = 'sophia.chen@company.org' LIMIT 1");
    $empStmt->execute();
    $employee = $empStmt->fetch();

    $empId = $employee ? $employee['id'] : null;

    // Check if user already exists
    $userStmt = $pdo->prepare("SELECT id FROM users WHERE username = 'sophia' OR email = 'sophia.chen@company.org' LIMIT 1");
    $userStmt->execute();
    $existingUser = $userStmt->fetch();

    $password = 'EmployeePassword2026!';
    $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    $roleId = 4; // Employee role

    if ($existingUser) {
        // Update password and role
        $updateStmt = $pdo->prepare("UPDATE users SET password_hash = :hash, role_id = :role_id, status = 'active', employee_id = :emp_id WHERE id = :id");
        $updateStmt->execute([
            'hash'    => $hash,
            'role_id' => $roleId,
            'emp_id'  => $empId,
            'id'      => $existingUser['id']
        ]);
        echo "Updated existing user 'sophia' successfully.\n";
    } else {
        // Insert new user
        $insertStmt = $pdo->prepare("INSERT INTO users (employee_id, username, email, password_hash, role_id, status) VALUES (:emp_id, 'sophia', 'sophia.chen@company.org', :hash, :role_id, 'active')");
        $insertStmt->execute([
            'emp_id'  => $empId,
            'hash'    => $hash,
            'role_id' => $roleId
        ]);
        echo "Created new user 'sophia' successfully with ID: " . $pdo->lastInsertId() . "\n";
    }

    // Verify authentication test
    $auth = \App\Services\AuthService::attempt('sophia', $password);
    echo "Auth verification result: " . ($auth['success'] ? 'SUCCESS' : 'FAILED: ' . $auth['message']) . "\n";

} catch (\Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
