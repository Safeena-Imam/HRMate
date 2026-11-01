<?php

namespace App\Services;

class Logger
{
    private static ?string $logFile = null;

    public static function init(?string $filePath = null): void
    {
        if ($filePath !== null) {
            self::$logFile = $filePath;
        } else {
            self::$logFile = dirname(__DIR__, 2) . '/storage/logs/app.log';
        }

        $logDir = dirname(self::$logFile);
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0755, true);
        }
    }

    public static function log(string $level, string $message, array $context = []): void
    {
        if (self::$logFile === null) {
            self::init();
        }

        $timestamp = date('Y-m-d H:i:s');
        $contextStr = !empty($context) ? ' ' . json_encode($context, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : '';
        $formatted = sprintf("[%s] [%s] %s%s%s", $timestamp, strtoupper($level), $message, $contextStr, PHP_EOL);

        @file_put_contents(self::$logFile, $formatted, FILE_APPEND | LOCK_EX);
    }

    public static function info(string $message, array $context = []): void
    {
        self::log('INFO', $message, $context);
    }

    public static function warning(string $message, array $context = []): void
    {
        self::log('WARNING', $message, $context);
    }

    public static function error(string $message, array $context = []): void
    {
        self::log('ERROR', $message, $context);
    }

    public static function security(string $message, array $context = []): void
    {
        self::log('SECURITY', $message, $context);
    }

    public static function exception(\Throwable $e, string $extra = ''): void
    {
        $message = ($extra !== '' ? $extra . ': ' : '') . $e->getMessage();
        $context = [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'code' => $e->getCode(),
            'trace' => array_slice(explode("\n", $e->getTraceAsString()), 0, 5)
        ];
        self::log('ERROR', $message, $context);
    }
}
