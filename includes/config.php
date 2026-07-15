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

// Site base URL — auto-detected so it works on Replit's proxy
if (!defined('BASE_URL')) {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
    // Detect the script's depth from the project root to build the base path
    $docRoot = realpath($_SERVER['DOCUMENT_ROOT']);
    $scriptDir = dirname(realpath($_SERVER['SCRIPT_FILENAME']));
    $basePath = '/';
    if ($docRoot && $scriptDir && strpos($scriptDir, $docRoot) === 0) {
        $rel = str_replace($docRoot, '', $scriptDir);
        // Walk back to project root (config.php is always in includes/, one level down)
        $depth = substr_count(trim($rel, '/'), '/');
        $basePath = '/';
    }
    define('BASE_URL', $scheme . '://' . $host . $basePath);
}
?>
