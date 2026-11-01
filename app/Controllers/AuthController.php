<?php

namespace App\Controllers;

use App\Services\AuthService;
use App\Services\Validator;
use App\Services\MailService;
use App\Models\User;
use App\Models\PasswordReset;
use App\Models\AuditLog;

class AuthController extends BaseController
{
    /**
     * Display login view
     */
    public function showLogin(): void
    {
        $hasAdmin = false;
        $dbError = null;

        try {
            $hasAdmin = User::hasAnyAdmin();
        } catch (\Throwable $e) {
            $dbError = $e->getMessage();
        }

        $this->view('auth.login', [
            'pageTitle' => 'Sign In - HRMate',
            'hasAdmin'  => $hasAdmin,
            'dbError'   => $dbError
        ], 'auth');
    }

    /**
     * Process login form submission
     */
    public function login(): void
    {
        if (!$this->validateCSRF()) {
            redirect('login');
            return;
        }

        $validator = Validator::make($_POST, [
            'login'    => 'required|min:3',
            'password' => 'required|min:4'
        ], [
            'login.required'    => 'Please enter your username or email address.',
            'password.required' => 'Please enter your account password.'
        ]);

        if ($validator->fails()) {
            set_flash('danger', $validator->first('login') ?? $validator->first('password') ?? 'Validation failed.');
            redirect('login');
            return;
        }

        $login = (string)($_POST['login'] ?? '');
        $password = (string)($_POST['password'] ?? '');

        $result = AuthService::attempt($login, $password);

        if (!$result['success']) {
            set_flash('danger', $result['message']);
            redirect('login');
            return;
        }

        $validator->clearOldInput();

        // Check for intended destination redirect
        $target = $_SESSION['intended_url'] ?? 'dashboard';
        unset($_SESSION['intended_url']);

        $fullName = $result['user']['full_name'] ?? $result['user']['username'];
        set_flash('success', "Welcome back, {$fullName}! You have securely logged into HRMate.");

        redirect($target);
    }

    /**
     * Process logout
     */
    public function logout(): void
    {
        // Support POST with CSRF or GET with simple confirmation
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$this->validateCSRF()) {
                redirect('dashboard');
                return;
            }
        }

        AuthService::logout();
        set_flash('success', 'You have been safely signed out. Thank you for using HRMate.');
        redirect('login');
    }

    /**
     * Display Forgot Password Request View
     */
    public function showForgotPassword(): void
    {
        $this->view('auth.forgot_password', [
            'pageTitle' => 'Reset Password - HRMate'
        ], 'auth');
    }

    /**
     * Handle Forgot Password Email Submission
     */
    public function sendResetLink(): void
    {
        if (!$this->validateCSRF()) {
            redirect('forgot-password');
            return;
        }

        $email = trim((string)($_POST['email'] ?? ''));

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            set_flash('danger', 'Please enter a valid corporate email address.');
            redirect('forgot-password');
            return;
        }

        $user = User::findByEmail($email);
        if ($user) {
            $token = PasswordReset::createToken($email);
            MailService::sendPasswordReset($email, $token, $user['username']);

            AuditLog::record(
                $user['id'],
                'PASSWORD_RESET_REQUEST',
                'users',
                $user['id'],
                "Password reset link requested for '{$email}'"
            );

            set_flash('success', "A password reset link has been dispatched to {$email}. Please check your inbox (valid for 60 minutes).");
        } else {
            // For security, don't expose if email does not exist
            set_flash('success', "If an account exists with {$email}, instructions to reset your password have been sent.");
        }

        redirect('login');
    }

    /**
     * Display Reset Password Form with Token
     */
    public function showResetPassword(): void
    {
        $token = trim((string)($_GET['token'] ?? ''));

        if (!$token) {
            set_flash('danger', 'Invalid or missing password reset token.');
            redirect('login');
            return;
        }

        $record = PasswordReset::verifyToken($token);
        if (!$record) {
            set_flash('danger', 'This password reset link has expired or is invalid. Please request a new one.');
            redirect('forgot-password');
            return;
        }

        $this->view('auth.reset_password', [
            'pageTitle' => 'Set New Password - HRMate',
            'token'     => $token,
            'email'     => $record['email']
        ], 'auth');
    }

    /**
     * Handle Password Update Submission
     */
    public function resetPassword(): void
    {
        if (!$this->validateCSRF()) {
            redirect('login');
            return;
        }

        $token = trim((string)($_POST['token'] ?? ''));
        $password = (string)($_POST['password'] ?? '');
        $passwordConfirm = (string)($_POST['password_confirmation'] ?? '');

        if (!$token) {
            set_flash('danger', 'Password reset session token is missing.');
            redirect('login');
            return;
        }

        $record = PasswordReset::verifyToken($token);
        if (!$record) {
            set_flash('danger', 'This reset link has expired or is no longer valid. Please submit a new request.');
            redirect('forgot-password');
            return;
        }

        if (strlen($password) < 6) {
            set_flash('danger', 'Your new password must be at least 6 characters long.');
            redirect("reset-password?token=" . urlencode($token));
            return;
        }

        if ($password !== $passwordConfirm) {
            set_flash('danger', 'The password confirmation does not match. Please re-enter your new password.');
            redirect("reset-password?token=" . urlencode($token));
            return;
        }

        $user = User::findByEmail($record['email']);
        if (!$user) {
            set_flash('danger', 'User account associated with this token was not found.');
            redirect('login');
            return;
        }

        // Update password hash
        $newHash = password_hash($password, PASSWORD_BCRYPT);
        User::updatePassword((int)$user['id'], $newHash);

        // Invalidate used reset token
        PasswordReset::deleteForEmail($record['email']);

        AuditLog::record(
            (int)$user['id'],
            'PASSWORD_RESET_SUCCESS',
            'users',
            (int)$user['id'],
            "Password successfully updated via reset token"
        );

        set_flash('success', 'Your password has been successfully updated! You can now log in with your new password.');
        redirect('login');
    }
}
