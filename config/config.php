<?php
// ============================================================
// Sellora - Global Configuration
// ============================================================



// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('BASE_URL', '/');
define('SITE_NAME', 'Sellora');
define('SITE_URL', 'http://localhost:8080');
define('SITE_DESCRIPTION', 'Kenya\'s Premier Online Marketplace');
define('CURRENCY', 'KES');
if (!defined('CURRENCY_SYMBOL')) {define('CURRENCY_SYMBOL', 'KSh');}
define('UPLOAD_DIR', dirname(__DIR__) . '/client/uploads/products/');
define('UPLOAD_URL', SITE_URL . '/client/uploads/products/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'webp', 'gif']);

require_once __DIR__ . '/database.php';

// ============================================================
// Helper Functions
// ============================================================

function formatPrice($price) {
    return 'KSh ' . number_format($price, 2); // concat string properly
}

function sanitize($input) {
    return htmlspecialchars(strip_tags(trim($input)));
}

function redirect($url) {
    header("Location: $url");
    exit();
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isVendorLoggedIn() {
    return isset($_SESSION['vendor_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        redirect(SITE_URL . '/client/login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    }
}

function requireVendorLogin() {
    if (!isVendorLoggedIn()) {
        redirect(SITE_URL . '/vendor/login.php');
    }
}

function generateSlug($text) {
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
    $text = preg_replace('/[\s-]+/', '-', $text);
    return $text;
}

function generateOrderNumber() {
    return 'SEL-' . strtoupper(substr(uniqid(), -8)) . '-' . date('Ymd');
}

function getCartCount($conn) {
    if (isLoggedIn()) {
        $user_id = $_SESSION['user_id'];
        $stmt = $conn->prepare("SELECT SUM(quantity) as total FROM cart WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
    } else {
        $session_id = session_id();
        $stmt = $conn->prepare("SELECT SUM(quantity) as total FROM cart WHERE session_id = ?");
        $stmt->bind_param("s", $session_id);
    }
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    return $result['total'] ?? 0;
}

function uploadImage($file, $prefix = 'product') {
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ALLOWED_EXTENSIONS)) {
        return false;
    }
    if ($file['size'] > MAX_FILE_SIZE) {
        return false;
    }
    $filename = $prefix . '_' . uniqid() . '.' . $ext;
    $destination = UPLOAD_DIR . $filename;
    if (move_uploaded_file($file['tmp_name'], $destination)) {
        return $filename;
    }
    return false;
}

function getCategories($conn) {
    $result = $conn->query("SELECT * FROM categories WHERE is_active = 1 ORDER BY name ASC");
    return $result->fetch_all(MYSQLI_ASSOC);
}
?>
