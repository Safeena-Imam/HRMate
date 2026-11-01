<?php

namespace App\Models;

use Config\Database;

class PasswordReset
{
    /**
     * Create a secure password reset token valid for 60 minutes
     */
    public static function createToken(string $email): string
    {
        $pdo = Database::getConnection();
        $email = trim(strtolower($email));

        // Invalidate any older tokens for this email
        self::deleteForEmail($email);

        // Generate cryptographically secure token
        $token = bin2hex(random_bytes(32));

        $stmt = $pdo->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (:email, :token, DATE_ADD(NOW(), INTERVAL 60 MINUTE))");
        $stmt->execute([
            'email' => $email,
            'token' => $token
        ]);

        return $token;
    }

    /**
     * Verify if token is valid and not expired
     */
    public static function verifyToken(string $token): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM password_resets WHERE token = :token AND expires_at > NOW() LIMIT 1");
        $stmt->execute(['token' => trim($token)]);
        $record = $stmt->fetch();
        return $record ?: null;
    }

    /**
     * Delete tokens for a specific email address
     */
    public static function deleteForEmail(string $email): void
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("DELETE FROM password_resets WHERE email = :email");
        $stmt->execute(['email' => trim(strtolower($email))]);
    }
}
