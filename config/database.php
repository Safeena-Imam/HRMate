<?php

namespace Config;

use PDO;
use PDOException;
use App\Services\Logger;

class Database
{
    private static ?PDO $instance = null;
    private static ?array $config = null;

    /**
     * Set custom configuration array if needed
     */
    public static function setConfig(array $config): void
    {
        self::$config = $config;
        self::$instance = null; // Reset connection if config changes
    }

    /**
     * Return PDO connection singleton
     */
    public static function getConnection(): PDO
    {
        if (self::$instance !== null) {
            return self::$instance;
        }

        $host = self::$config['host'] ?? env('DB_HOST', '127.0.0.1');
        $port = self::$config['port'] ?? env('DB_PORT', '3306');
        $database = self::$config['database'] ?? env('DB_DATABASE', 'hrm_system');
        $username = self::$config['username'] ?? env('DB_USERNAME', 'root');
        $password = self::$config['password'] ?? env('DB_PASSWORD', '');
        $charset = self::$config['charset'] ?? env('DB_CHARSET', 'utf8mb4');

        $dsn = "mysql:host={$host};port={$port};dbname={$database};charset={$charset}";

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::ATTR_PERSISTENT         => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$charset} COLLATE {$charset}_unicode_ci"
        ];

        try {
            self::$instance = new PDO($dsn, $username, $password, $options);
            return self::$instance;
        } catch (PDOException $e) {
            Logger::error("Database connection failure: " . $e->getMessage(), [
                'host' => $host,
                'port' => $port,
                'database' => $database,
                'user' => $username
            ]);

            // Re-throw a cleaner exception so sensitive credentials are never leaked to output
            $isDebug = filter_var(env('APP_DEBUG', true), FILTER_VALIDATE_BOOLEAN);
            if ($isDebug) {
                throw new PDOException("Database Connection Error: " . $e->getMessage(), (int)$e->getCode());
            } else {
                throw new PDOException("Database service is currently unavailable. Please check system configuration.", 500);
            }
        }
    }

    /**
     * Test connection without throwing uncaught fatal exceptions
     */
    public static function testConnection(): array
    {
        try {
            $pdo = self::getConnection();
            $stmt = $pdo->query("SELECT 1 AS alive");
            $res = $stmt->fetch();
            return ['status' => true, 'message' => 'Connected successfully to ' . env('DB_DATABASE', 'hrm_system')];
        } catch (\Throwable $e) {
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }
}
