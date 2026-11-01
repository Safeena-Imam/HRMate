<?php

namespace App\Services;

class Env
{
    private static bool $loaded = false;

    /**
     * Load environment variables from .env file into $_ENV and putenv.
     */
    public static function load(string $filePath): void
    {
        if (self::$loaded) {
            return;
        }

        if (!file_exists($filePath)) {
            // Fall back to .env.example if .env does not exist yet
            $examplePath = dirname($filePath) . '/.env.example';
            if (file_exists($examplePath)) {
                $filePath = $examplePath;
            } else {
                return;
            }
        }

        $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) {
            return;
        }

        foreach ($lines as $line) {
            $line = trim($line);

            // Skip comments and empty lines
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            // Parse key=value
            $parts = explode('=', $line, 2);
            if (count($parts) !== 2) {
                continue;
            }

            $key = trim($parts[0]);
            $val = trim($parts[1]);

            // Strip enclosing quotes
            if (
                (str_starts_with($val, '"') && str_ends_with($val, '"')) ||
                (str_starts_with($val, "'") && str_ends_with($val, "'"))
            ) {
                $val = substr($val, 1, -1);
            }

            // Convert common literal representations
            $val = match (strtolower($val)) {
                'true', '(true)' => true,
                'false', '(false)' => false,
                'null', '(null)' => null,
                'empty', '(empty)' => '',
                default => $val,
            };

            if (!array_key_exists($key, $_SERVER) && !array_key_exists($key, $_ENV)) {
                putenv("{$key}=" . (is_bool($val) ? ($val ? '1' : '0') : (string)$val));
                $_ENV[$key] = $val;
                $_SERVER[$key] = $val;
            }
        }

        self::$loaded = true;
    }

    /**
     * Retrieve an environment variable value with default fallback.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        if (array_key_exists($key, $_ENV)) {
            return $_ENV[$key];
        }

        if (array_key_exists($key, $_SERVER)) {
            return $_SERVER[$key];
        }

        $envVal = getenv($key);
        if ($envVal !== false) {
            return $envVal;
        }

        return $default;
    }
}
