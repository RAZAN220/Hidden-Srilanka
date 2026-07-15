<?php
// Database configuration — uses Replit's built-in PostgreSQL
$dsn = "pgsql:host=" . getenv('PGHOST') . ";port=" . getenv('PGPORT') . ";dbname=" . getenv('PGDATABASE');
$dbUser = getenv('PGUSER');
$dbPass = getenv('PGPASSWORD');

try {
    $pdo = new PDO($dsn, $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Use root-relative base URL so assets and redirects work correctly behind
// Replit's HTTPS proxy (PHP sees plain HTTP internally, so building an
// absolute http:// URL causes mixed-content blocks in the browser).
if (!defined('BASE_URL')) {
    define('BASE_URL', '/');
}
?>
