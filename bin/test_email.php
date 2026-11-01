<?php

require dirname(__DIR__) . '/app/bootstrap.php';

use App\Services\MailService;

if ($argc < 2) {
    echo "Usage: php bin/test_email.php <recipient_email>\n";
    echo "Example: php bin/test_email.php test@example.com\n";
    exit(1);
}

$recipient = trim($argv[1]);
echo "Testing real email dispatch to: {$recipient} ...\n";

$appName = env('APP_NAME', 'HRMate');
$html = "<h3>Test Email from {$appName}</h3><p>If you see this, real SMTP email dispatch is working 100%!</p>";
$sent = MailService::send($recipient, "Test Delivery from {$appName}", $html, "Test Email from {$appName}. Real email is working!");

if ($sent) {
    echo "SUCCESS: Email process finished. Check recipient inbox (or spam) and storage/logs/mail.log!\n";
} else {
    echo "FAILED: Could not send email. See storage/logs/mail.log for error details.\n";
}

