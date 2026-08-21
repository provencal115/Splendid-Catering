<?php

declare(strict_types=1);

session_start();

define('ROOT_PATH', dirname(__DIR__));
define('PUBLIC_PATH', ROOT_PATH . '/public');

if (!function_exists('detectBaseUrl')) {
    function detectBaseUrl(): string
    {
        if ($env = getenv('APP_URL')) {
            return rtrim($env, '/');
        }

        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '/index.php');
        $basePath = dirname($script);

        if ($basePath === '/' || $basePath === '.') {
            $basePath = '';
        }

        // Normalize when entry point lives in /public
        if (str_ends_with($basePath, '/public')) {
            $basePath = dirname($basePath);
        }

        return rtrim($scheme . '://' . $host . $basePath, '/');
    }
}

define('BASE_URL', detectBaseUrl());

$config = require ROOT_PATH . '/config/app.php';
$dbConfig = require ROOT_PATH . '/config/database.php';

require_once ROOT_PATH . '/includes/functions.php';
require_once ROOT_PATH . '/includes/auth.php';

try {
    $dsn = sprintf(
        'mysql:host=%s;port=%s;dbname=%s;charset=%s',
        $dbConfig['host'],
        $dbConfig['port'],
        $dbConfig['database'],
        $dbConfig['charset']
    );
    $pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    $requestUri = $_SERVER['REQUEST_URI'] ?? '';
    if (str_contains($requestUri, 'setup')) {
        $pdo = null;
    } else {
        http_response_code(503);
        include ROOT_PATH . '/pages/error-db.php';
        exit;
    }
}

date_default_timezone_set($config['timezone']);
