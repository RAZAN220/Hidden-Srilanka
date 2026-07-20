<?php
// Database configuration
// Priority order:
// 1) If PostgreSQL env (Replit) is present, use it.
// 2) Otherwise use MySQL (XAMPP/local) with sensible defaults.

if (getenv('PGHOST')) {
    // PostgreSQL (existing Replit setup)
    $dsn = "pgsql:host=" . getenv('PGHOST') . ";port=" . getenv('PGPORT') . ";dbname=" . getenv('PGDATABASE');
    $dbUser = getenv('PGUSER');
    $dbPass = getenv('PGPASSWORD');
} else {
    // MySQL (XAMPP) — adjust DB_NAME/DB_USER/DB_PASS as needed
    $dbHost = getenv('DB_HOST') ?: '127.0.0.1';
    $dbPort = getenv('DB_PORT') ?: '3306';
    $dbName = getenv('DB_NAME') ?: 'hidden_srilanka';
    $dbUser = getenv('DB_USER') ?: 'root';
    $dbPass = getenv('DB_PASS') ?: '';
    $dsn = "mysql:host={$dbHost};port={$dbPort};dbname={$dbName};charset=utf8mb4";
}

try {
    $pdo = new PDO($dsn, $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // In production you might want to log instead of die()
    die("Database connection failed: " . $e->getMessage());
}

// Base URL helper (keeps previous behavior)
if (!defined('BASE_URL')) {
    define('BASE_URL', '/Hidden-Srilanka/');
}
?>
