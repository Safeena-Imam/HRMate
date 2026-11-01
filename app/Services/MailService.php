<?php

namespace App\Services;

class MailService
{
    /**
     * Send an email with HTML and Plain Text fallback.
     * Supports both real internet SMTP (Gmail, Outlook, etc.) and fallback logging.
     */
    public static function send(string $to, string $subject, string $htmlBody, string $textBody = ''): bool
    {
        $appName = env('APP_NAME', 'HRMate');
        $fromEmail = env('MAIL_FROM_ADDRESS', env('MAIL_USERNAME', 'noreply@hrmate.local'));
        $fromName = env('MAIL_FROM_NAME', $appName);

        // Ensure logs directory exists for safety/audit
        $logDir = dirname(__DIR__, 2) . '/storage/logs';
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0755, true);
        }

        $logFile = $logDir . '/mail.log';
        $timestamp = date('Y-m-d H:i:s');
        $logContent = "=========================================================\n"
                    . "[{$timestamp}] OUTGOING EMAIL\n"
                    . "To: {$to}\n"
                    . "Subject: {$subject}\n"
                    . "---------------------------------------------------------\n"
                    . ($textBody ?: strip_tags($htmlBody)) . "\n"
                    . "=========================================================\n\n";

        @file_put_contents($logFile, $logContent, FILE_APPEND);

        // If SMTP credentials are configured, send real email over SMTP Socket
        $smtpHost = env('MAIL_HOST', '');
        $smtpUser = env('MAIL_USERNAME', '');
        $smtpPass = env('MAIL_PASSWORD', '');
        $smtpPort = (int)env('MAIL_PORT', 587);
        $smtpEnc  = strtolower(env('MAIL_ENCRYPTION', 'tls'));

        if (!empty($smtpHost) && !empty($smtpUser) && !empty($smtpPass)) {
            $sent = self::sendViaSmtp($smtpHost, $smtpPort, $smtpEnc, $smtpUser, $smtpPass, $fromEmail, $fromName, $to, $subject, $htmlBody, $textBody);
            if ($sent) {
                return true;
            }
        }

        // Fallback to PHP native mail() if available
        try {
            $headers = [
                'MIME-Version: 1.0',
                'Content-type: text/html; charset=utf-8',
                "From: {$fromName} <{$fromEmail}>",
                "Reply-To: {$fromEmail}",
                'X-Mailer: PHP/' . phpversion()
            ];
            if (function_exists('mail') && ini_get('sendmail_path')) {
                @mail($to, $subject, $htmlBody, implode("\r\n", $headers));
            }
        } catch (\Throwable $e) {
            // Logged in mail.log
        }

        return true;
    }

    /**
     * Native Lightweight SMTP Client (TLS / SSL / Plain)
     */
    private static function sendViaSmtp(
        string $host,
        int $port,
        string $encryption,
        string $username,
        string $password,
        string $fromEmail,
        string $fromName,
        string $toEmail,
        string $subject,
        string $htmlBody,
        string $textBody = ''
    ): bool {
        $socketHost = ($encryption === 'ssl') ? "ssl://{$host}" : $host;
        $timeout = 15;

        $socket = @fsockopen($socketHost, $port, $errno, $errstr, $timeout);
        if (!$socket) {
            @file_put_contents(dirname(__DIR__, 2) . '/storage/logs/mail.log', "[SMTP ERROR] Could not connect to {$host}:{$port} ({$errno}: {$errstr})\n", FILE_APPEND);
            return false;
        }

        stream_set_timeout($socket, $timeout);

        $read = function() use ($socket) {
            $response = '';
            while ($str = fgets($socket, 515)) {
                $response .= $str;
                if (substr($str, 3, 1) === ' ') {
                    break;
                }
            }
            return $response;
        };

        $send = function(string $cmd) use ($socket) {
            fputs($socket, $cmd . "\r\n");
        };

        $serverGreeting = $read();

        // EHLO
        $send("EHLO " . gethostname());
        $read();

        // STARTTLS if configured
        if ($encryption === 'tls') {
            $send("STARTTLS");
            $tlsRes = $read();
            if (strpos($tlsRes, '220') === false) {
                fclose($socket);
                return false;
            }

            $cryptoMethod = STREAM_CRYPTO_METHOD_TLS_CLIENT | STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT;
            if (defined('STREAM_CRYPTO_METHOD_TLSv1_3_CLIENT')) {
                $cryptoMethod |= STREAM_CRYPTO_METHOD_TLSv1_3_CLIENT;
            }

            if (!stream_socket_enable_crypto($socket, true, $cryptoMethod)) {
                fclose($socket);
                return false;
            }

            $send("EHLO " . gethostname());
            $read();
        }

        // AUTH LOGIN
        $send("AUTH LOGIN");
        $read();

        $send(base64_encode($username));
        $read();

        $send(base64_encode($password));
        $authRes = $read();
        if (strpos($authRes, '235') === false) {
            @file_put_contents(dirname(__DIR__, 2) . '/storage/logs/mail.log', "[SMTP AUTH FAILED] Response: {$authRes}\n", FILE_APPEND);
            fclose($socket);
            return false;
        }

        // MAIL FROM
        $send("MAIL FROM:<{$fromEmail}>");
        $read();

        // RCPT TO
        $send("RCPT TO:<{$toEmail}>");
        $read();

        // DATA
        $send("DATA");
        $read();

        $boundary = "----=_Part_" . md5(uniqid((string)time(), true));

        $emailHeaders = "From: =?UTF-8?B?" . base64_encode($fromName) . "?= <{$fromEmail}>\r\n"
                      . "To: <{$toEmail}>\r\n"
                      . "Subject: =?UTF-8?B?" . base64_encode($subject) . "?=\r\n"
                      . "Date: " . date('r') . "\r\n"
                      . "MIME-Version: 1.0\r\n"
                      . "Content-Type: multipart/alternative; boundary=\"{$boundary}\"\r\n\r\n";

        $emailBody = "--{$boundary}\r\n"
                   . "Content-Type: text/plain; charset=UTF-8\r\n"
                   . "Content-Transfer-Encoding: base64\r\n\r\n"
                   . chunk_split(base64_encode($textBody ?: strip_tags($htmlBody))) . "\r\n"
                   . "--{$boundary}\r\n"
                   . "Content-Type: text/html; charset=UTF-8\r\n"
                   . "Content-Transfer-Encoding: base64\r\n\r\n"
                   . chunk_split(base64_encode($htmlBody)) . "\r\n"
                   . "--{$boundary}--\r\n";

        $send($emailHeaders . $emailBody . "\r\n.");
        $dataRes = $read();

        $send("QUIT");
        fclose($socket);

        $success = strpos($dataRes, '250') !== false;
        if ($success) {
            @file_put_contents(dirname(__DIR__, 2) . '/storage/logs/mail.log', "[SMTP SUCCESS] Real email delivered to {$toEmail} via {$host}\n", FILE_APPEND);
        }
        return $success;
    }

    /**
     * Send Welcome Onboarding Email to newly created Employee
     */
    public static function sendWelcomeEmployee(array $employee, string $username, string $plainPassword): bool
    {
        $appName = env('APP_NAME', 'HRMate');
        $loginUrl = url('login');
        $firstName = htmlspecialchars($employee['first_name'] ?? 'Team Member', ENT_QUOTES, 'UTF-8');
        $to = $employee['email'];
        $subject = "Welcome to {$appName} – Your Account Credentials";

        $htmlBody = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f8fafc; margin: 0; padding: 24px; color: #101828; }
        .card { max-width: 560px; margin: 0 auto; background: #ffffff; border-radius: 12px; border: 1px solid #e2e8f0; overflow: hidden; box-shadow: 0 4px 12px rgba(16, 24, 40, 0.05); }
        .header { background: #101828; padding: 28px; text-align: center; border-bottom: 3px solid #9DE500; }
        .header h1 { margin: 0; color: #ffffff; font-size: 22px; font-weight: 700; letter-spacing: -0.5px; }
        .header p { margin: 6px 0 0 0; color: #94a3b8; font-size: 13px; }
        .content { padding: 32px 28px; line-height: 1.6; }
        .greeting { font-size: 17px; font-weight: 600; margin-bottom: 12px; }
        .cred-box { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 18px; margin: 20px 0; }
        .cred-row { display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 14px; }
        .cred-row:last-child { margin-bottom: 0; }
        .cred-label { color: #64748b; font-weight: 500; }
        .cred-val { font-family: monospace; font-weight: 700; color: #101828; }
        .btn-wrap { text-align: center; margin: 28px 0 20px 0; }
        .btn { display: inline-block; background: #9DE500; color: #101828; font-weight: 700; text-decoration: none; padding: 12px 28px; border-radius: 8px; font-size: 14px; box-shadow: 0 4px 12px rgba(157, 229, 0, 0.35); }
        .notice { font-size: 12px; color: #64748b; margin-top: 24px; padding-top: 16px; border-top: 1px solid #f1f5f9; }
        .footer { background: #f8fafc; padding: 16px; text-align: center; font-size: 12px; color: #94a3b8; border-top: 1px solid #e2e8f0; }
    </style>
</head>
<body>
    <div class="card">
        <div class="header">
            <h1>{$appName}</h1>
            <p>Enterprise Workforce &amp; HR Management Portal</p>
        </div>
        <div class="content">
            <div class="greeting">Hello {$firstName},</div>
            <p>Welcome to the team! Your employee profile has been officially provisioned on the <strong>{$appName}</strong> portal. You can now access your self-service dashboard to view attendance, leaves, and payroll records.</p>
            
            <div class="cred-box">
                <div class="cred-row">
                    <span class="cred-label">Login URL:</span>
                    <span class="cred-val">{$loginUrl}</span>
                </div>
                <div class="cred-row">
                    <span class="cred-label">Username / Email:</span>
                    <span class="cred-val">{$username}</span>
                </div>
                <div class="cred-row">
                    <span class="cred-label">Temporary Password:</span>
                    <span class="cred-val" style="color: #0b101b; background: #ecfcca; padding: 2px 6px; border-radius: 4px;">{$plainPassword}</span>
                </div>
            </div>

            <div class="btn-wrap">
                <a href="{$loginUrl}" class="btn" target="_blank">Sign In to Your Workspace &rarr;</a>
            </div>

            <p class="notice">
                <strong>Security Reminder:</strong> Please sign in and update your temporary password to a secure personal password. Never share your credentials with anyone.
            </p>
        </div>
        <div class="footer">
            &copy; {$appName} System. This is an automated notification.
        </div>
    </div>
</body>
</html>
HTML;

        $textBody = "Hello {$firstName},\n\n"
                  . "Welcome to {$appName}! Your account credentials are:\n"
                  . "Login URL: {$loginUrl}\n"
                  . "Username: {$username}\n"
                  . "Password: {$plainPassword}\n\n"
                  . "Please log in and update your password immediately.\n";

        return self::send($to, $subject, $htmlBody, $textBody);
    }

    /**
     * Send Password Reset Request Email
     */
    public static function sendPasswordReset(string $email, string $resetToken, string $name = 'User'): bool
    {
        $appName = env('APP_NAME', 'HRMate');
        $resetUrl = url('reset-password') . '?token=' . urlencode($resetToken);
        $subject = "{$appName} – Password Reset Instructions";
        $safeName = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');

        $htmlBody = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f8fafc; margin: 0; padding: 24px; color: #101828; }
        .card { max-width: 560px; margin: 0 auto; background: #ffffff; border-radius: 12px; border: 1px solid #e2e8f0; overflow: hidden; box-shadow: 0 4px 12px rgba(16, 24, 40, 0.05); }
        .header { background: #101828; padding: 28px; text-align: center; border-bottom: 3px solid #9DE500; }
        .header h1 { margin: 0; color: #ffffff; font-size: 22px; font-weight: 700; letter-spacing: -0.5px; }
        .content { padding: 32px 28px; line-height: 1.6; }
        .greeting { font-size: 17px; font-weight: 600; margin-bottom: 12px; }
        .btn-wrap { text-align: center; margin: 28px 0; }
        .btn { display: inline-block; background: #9DE500; color: #101828; font-weight: 700; text-decoration: none; padding: 12px 28px; border-radius: 8px; font-size: 14px; box-shadow: 0 4px 12px rgba(157, 229, 0, 0.35); }
        .link-alt { word-break: break-all; font-size: 12px; color: #64748b; background: #f8fafc; padding: 10px; border-radius: 6px; border: 1px solid #e2e8f0; }
        .notice { font-size: 12px; color: #64748b; margin-top: 24px; padding-top: 16px; border-top: 1px solid #f1f5f9; }
        .footer { background: #f8fafc; padding: 16px; text-align: center; font-size: 12px; color: #94a3b8; border-top: 1px solid #e2e8f0; }
    </style>
</head>
<body>
    <div class="card">
        <div class="header">
            <h1>{$appName}</h1>
        </div>
        <div class="content">
            <div class="greeting">Hello {$safeName},</div>
            <p>We received a request to reset the password associated with your account on <strong>{$appName}</strong>. Click the button below to choose a new password:</p>
            
            <div class="btn-wrap">
                <a href="{$resetUrl}" class="btn" target="_blank">Reset My Password &rarr;</a>
            </div>

            <p style="font-size: 13px; color: #64748b;">If the button above does not work, copy and paste this link into your web browser:</p>
            <div class="link-alt">{$resetUrl}</div>

            <p class="notice">
                <strong>Note:</strong> This password reset link will expire in <strong>60 minutes</strong>. If you did not request this password reset, please ignore this email; your account remains secure.
            </p>
        </div>
        <div class="footer">
            &copy; {$appName} Security System.
        </div>
    </div>
</body>
</html>
HTML;

        $textBody = "Hello {$safeName},\n\n"
                  . "We received a request to reset your password on {$appName}.\n\n"
                  . "Reset your password using this link:\n{$resetUrl}\n\n"
                  . "This link is valid for 60 minutes. If you did not make this request, you can safely ignore this email.\n";

        return self::send($email, $subject, $htmlBody, $textBody);
    }
}
