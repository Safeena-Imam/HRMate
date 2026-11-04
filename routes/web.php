<?php

use App\Services\Router;
use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Middleware\AuthMiddleware;
use App\Middleware\GuestMiddleware;
use Config\Database;

// Root URL redirects to dashboard
Router::get('/', function() {
    redirect('dashboard');
});

// Authentication Routes (Guarded by GuestMiddleware)
Router::get('/login', [AuthController::class, 'showLogin'], [GuestMiddleware::class]);
Router::post('/login', [AuthController::class, 'login'], [GuestMiddleware::class]);
Router::get('/forgot-password', [AuthController::class, 'showForgotPassword'], [GuestMiddleware::class]);
Router::post('/forgot-password', [AuthController::class, 'sendResetLink'], [GuestMiddleware::class]);
Router::get('/reset-password', [AuthController::class, 'showResetPassword'], [GuestMiddleware::class]);
Router::post('/reset-password', [AuthController::class, 'resetPassword'], [GuestMiddleware::class]);

// Logout (Supports both POST and GET)
Router::get('/logout', [AuthController::class, 'logout']);
Router::post('/logout', [AuthController::class, 'logout']);

// Dedicated Application Pages (Guarded by AuthMiddleware)
Router::get('/dashboard', [DashboardController::class, 'index'], [AuthMiddleware::class]);
Router::get('/employees', [DashboardController::class, 'employees'], [AuthMiddleware::class]);
Router::post('/employees/create', [DashboardController::class, 'createEmployee'], [AuthMiddleware::class]);
Router::get('/departments', [DashboardController::class, 'departments'], [AuthMiddleware::class]);
Router::get('/designations', function() { redirect('departments'); }, [AuthMiddleware::class]);

// Attendance & Export Routes
Router::get('/attendance', [DashboardController::class, 'attendance'], [AuthMiddleware::class]);
Router::post('/attendance/save', [DashboardController::class, 'saveAttendance'], [AuthMiddleware::class]);
Router::post('/attendance/save-daily', [DashboardController::class, 'saveDailyAttendance'], [AuthMiddleware::class]);
Router::get('/attendance/export', [DashboardController::class, 'exportAttendance'], [AuthMiddleware::class]);

// Leave Management Routes
Router::get('/leaves', [DashboardController::class, 'leaves'], [AuthMiddleware::class]);
Router::post('/leaves/apply', [DashboardController::class, 'applyLeave'], [AuthMiddleware::class]);
Router::post('/leaves/action', [DashboardController::class, 'actionLeave'], [AuthMiddleware::class]);

// Announcements Routes
Router::post('/announcements/create', [DashboardController::class, 'createAnnouncement'], [AuthMiddleware::class]);
Router::post('/announcements/delete', [DashboardController::class, 'deleteAnnouncement'], [AuthMiddleware::class]);

// Payroll & Payslip Routes
Router::get('/payroll', [DashboardController::class, 'payroll'], [AuthMiddleware::class]);
Router::post('/payroll/generate', [DashboardController::class, 'generatePayroll'], [AuthMiddleware::class]);
Router::post('/payroll/update-status', [DashboardController::class, 'updatePayrollStatus'], [AuthMiddleware::class]);
Router::get('/payroll/payslip', [DashboardController::class, 'viewPayslip'], [AuthMiddleware::class]);
Router::get('/payroll/export', [DashboardController::class, 'exportPayroll'], [AuthMiddleware::class]);

Router::get('/audit-logs', [DashboardController::class, 'auditLogs'], [AuthMiddleware::class]);

// System Health & Verification Endpoint
Router::get('/health', function() {
    $dbStatus = Database::testConnection();
    header('Content-Type: application/json');
    echo json_encode([
        'status'    => $dbStatus['status'] ? 'healthy' : 'degraded',
        'app'       => env('APP_NAME', 'Apex HRMS'),
        'env'       => env('APP_ENV', 'development'),
        'database'  => $dbStatus,
        'timestamp' => date('Y-m-d H:i:s'),
        'php'       => PHP_VERSION
    ], JSON_PRETTY_PRINT);
    exit;
});
