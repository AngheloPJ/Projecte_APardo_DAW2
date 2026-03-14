<?php

 $envPath = __DIR__ . '/../.env';
 $envLocalPath = __DIR__ . '/../.env.local';

 $envFile = file_exists($envLocalPath) ? $envLocalPath : $envPath;

if (!file_exists($envFile)) {
    die('Archivo .env o .env.local no encontrado');
}

 $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

foreach ($lines as $line) {
    if (str_starts_with(trim($line), '#')) continue;
    if (strpos($line, '=') === false) continue;

    [$key, $value] = explode('=', $line, 2);
    $_ENV[$key] = trim($value);
}

// PHP Config
define('BASE_PATH', __DIR__ . '/../');
define('BASE_URL', $_ENV['BASE_URL'] ?? 'http://localhost/');
define('BASE_VIEW', BASE_PATH . '/app/view');

// BBDD
define('DB_HOST', $_ENV['DB_HOST'] ?? 'localhost');
define('DB_NAME', $_ENV['DB_NAME'] ?? '');
define('DB_USER', $_ENV['DB_USER'] ?? 'root');
define('DB_PASSWORD', $_ENV['DB_PASSWORD'] ?? '');

// BBDD Conexión
define(
    'DSN',
    'mysql:host=' . DB_HOST .
    ';dbname=' . DB_NAME .
    ';charset=utf8mb4'
);

// Email (PHPMailer)
define('MAIL_HOST', $_ENV['MAIL_HOST'] ?? 'localhost');
define('MAIL_USER', $_ENV['MAIL_USER'] ?? '');
define('MAIL_PASS', $_ENV['MAIL_PASS'] ?? '');
define('MAIL_PORT', (int)($_ENV['MAIL_PORT'] ?? 25));
define('MAIL_SECURE', $_ENV['MAIL_SECURE'] ?? '');
define('MAIL_FROM', $_ENV['MAIL_FROM'] ?? 'no-reply@backend.com');
define('MAIL_NAME', $_ENV['MAIL_NAME'] ?? 'Web');

// Discord (OAuth)
define('DISCORD_CLIENT_ID', $_ENV['DISCORD_CLIENT_ID'] ?? '');
define('DISCORD_CLIENT_SECRET', $_ENV['DISCORD_CLIENT_SECRET'] ?? '');

// Github (OAuth)
define('GITHUB_CLIENT_ID', $_ENV['GITHUB_CLIENT_ID'] ?? '');
define('GITHUB_CLIENT_SECRET', $_ENV['GITHUB_CLIENT_SECRET'] ?? '');

// ReCaptcha
define('RECAPTCHA_SITEKEY', $_ENV['RECAPTCHA_SITEKEY'] ?? '');
define('RECAPTCHA_SECRET', $_ENV['RECAPTCHA_SECRET'] ?? '');
