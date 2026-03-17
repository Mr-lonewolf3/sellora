<?php
// ============================================================
// Sellora - Database Configuration
// ============================================================

define('DB_HOST', 'mysql');       // Docker service name / localhost for XAMPP
define('DB_USER', 'sellora_user');
define('DB_PASS', 'sellora_pass');
define('DB_NAME', 'sellora_db');
define('DB_PORT', '3306');

// For XAMPP (local), change DB_HOST to 'localhost' and use root credentials:
// define('DB_HOST', 'localhost');
// define('DB_USER', 'root');
// define('DB_PASS', '');
// define('DB_NAME', 'sellora_db');

function getDBConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
    if ($conn->connect_error) {
        die(json_encode(['success' => false, 'message' => 'Database connection failed: ' . $conn->connect_error]));
    }
    $conn->set_charset('utf8mb4');
    return $conn;
}

// PDO connection (alternative)
function getPDOConnection() {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
        return $pdo;
    } catch (PDOException $e) {
        die(json_encode(['success' => false, 'message' => 'Connection failed: ' . $e->getMessage()]));
    }
}
?>
