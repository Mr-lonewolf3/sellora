<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
header('Content-Type: application/json');

$conn = getDBConnection();
$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'register':
        registerVendor($conn);
        break;
    case 'login':
        loginVendor($conn);
        break;
    case 'logout':
        logoutVendor();
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}

function registerVendor($conn) {
    $company_name = sanitize($_POST['company_name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $phone = sanitize($_POST['phone'] ?? '');
    $description = sanitize($_POST['description'] ?? '');
    $address = sanitize($_POST['address'] ?? '');

    if (empty($company_name) || empty($email) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Company name, email, and password are required']);
        return;
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Invalid email address']);
        return;
    }
    if (strlen($password) < 6) {
        echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters']);
        return;
    }

    $stmt = $conn->prepare("SELECT id FROM vendors WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Email already registered as a vendor']);
        return;
    }

    // Handle logo upload
    $logo = 'default_vendor.png';
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
        $uploaded = uploadImage($_FILES['logo'], 'vendor');
        if ($uploaded) $logo = $uploaded;
    }

    $hashed = password_hash($password, PASSWORD_BCRYPT);
    $stmt = $conn->prepare("INSERT INTO vendors (company_name, email, password, phone, description, logo, address) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssss", $company_name, $email, $hashed, $phone, $description, $logo, $address);

    if ($stmt->execute()) {
        $vendor_id = $conn->insert_id;
        $_SESSION['vendor_id'] = $vendor_id;
        $_SESSION['vendor_name'] = $company_name;
        $_SESSION['vendor_email'] = $email;
        echo json_encode(['success' => true, 'message' => 'Vendor registration successful', 'redirect' => '../vendor/dashboard.php']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Registration failed. Please try again.']);
    }
}

function loginVendor($conn) {
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Email and password are required']);
        return;
    }

    $stmt = $conn->prepare("SELECT id, company_name, email, password, is_active FROM vendors WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $vendor = $stmt->get_result()->fetch_assoc();

    if (!$vendor || !password_verify($password, $vendor['password'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid email or password']);
        return;
    }
    if (!$vendor['is_active']) {
        echo json_encode(['success' => false, 'message' => 'Your vendor account has been deactivated']);
        return;
    }

    $_SESSION['vendor_id'] = $vendor['id'];
    $_SESSION['vendor_name'] = $vendor['company_name'];
    $_SESSION['vendor_email'] = $vendor['email'];

    echo json_encode(['success' => true, 'message' => 'Login successful', 'redirect' => '../vendor/dashboard.php']);
}

/*************  ✨ Windsurf Command ⭐  *************/
/*******  5e51d710-c6d9-4503-96c0-e3bcda216cc6  *******/function logoutVendor() {
    unset($_SESSION['vendor_id'], $_SESSION['vendor_name'], $_SESSION['vendor_email']);
    echo json_encode(['success' => true, 'redirect' => '../vendor/login.php']);
}
?>
