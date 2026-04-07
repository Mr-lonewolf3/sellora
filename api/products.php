<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
header('Content-Type: application/json');

$conn = getDBConnection();
$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'add':
        addProduct($conn);
        break;
    case 'edit':
        editProduct($conn);
        break;
    case 'delete':
        deleteProduct($conn);
        break;
    case 'toggle_status':
        toggleStatus($conn);
        break;
    case 'get':
        getProduct($conn);
        break;
    case 'list':
        listProducts($conn);
        break;
    case 'vendor_products':
        vendorProducts($conn);
        break;
    case 'search':
        searchProducts($conn);
        break;
    case 'increment_view':
        incrementView($conn);
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}

function addProduct($conn) {
    if (!isVendorLoggedIn()) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        return;
    }

    $vendor_id = $_SESSION['vendor_id'];
    $name = sanitize($_POST['name'] ?? '');
    $category_id = (int)($_POST['category_id'] ?? 0);
    $description = sanitize($_POST['description'] ?? '');
    $price = (float)($_POST['price'] ?? 0);
    $discount_price = !empty($_POST['discount_price']) ? (float)$_POST['discount_price'] : null;
    $stock = (int)($_POST['stock'] ?? 0);
    $brand = sanitize($_POST['brand'] ?? '');
    $sku = sanitize($_POST['sku'] ?? '');

    if (empty($name) || $category_id === 0 || $price <= 0) {
        echo json_encode(['success' => false, 'message' => 'Name, category, and price are required']);
        return;
    }

    if (!isset($_FILES['main_image']) || $_FILES['main_image']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['success' => false, 'message' => 'Product main image is required']);
        return;
    }

    $main_image = uploadImage($_FILES['main_image'], 'product');
    if (!$main_image) {
        echo json_encode(['success' => false, 'message' => 'Failed to upload image. Check file type and size (max 5MB).']);
        return;
    }

    // Handle multiple images
    $extra_images = [];
    if (isset($_FILES['extra_images'])) {
        foreach ($_FILES['extra_images']['tmp_name'] as $key => $tmp) {
            if ($_FILES['extra_images']['error'][$key] === UPLOAD_ERR_OK) {
                $file = [
                    'name' => $_FILES['extra_images']['name'][$key],
                    'tmp_name' => $tmp,
                    'error' => $_FILES['extra_images']['error'][$key],
                    'size' => $_FILES['extra_images']['size'][$key],
                ];
                $img = uploadImage($file, 'product_extra');
                if ($img) $extra_images[] = $img;
            }
        }
    }
    $images_json = json_encode($extra_images);

    $slug = generateSlug($name) . '-' . uniqid();

    $stmt = $conn->prepare("INSERT INTO products (vendor_id, category_id, name, slug, description, price, discount_price, stock, main_image, images, brand, sku) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iisssddissss", $vendor_id, $category_id, $name, $slug, $description, $price, $discount_price, $stock, $main_image, $images_json, $brand, $sku);
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Product added successfully', 'product_id' => $conn->insert_id]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to add product']);
    }
}

function editProduct($conn) {
    if (!isVendorLoggedIn()) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        return;
    }

    $vendor_id = $_SESSION['vendor_id'];
    $product_id = (int)($_POST['product_id'] ?? 0);
    $name = sanitize($_POST['name'] ?? '');
    $category_id = (int)($_POST['category_id'] ?? 0);
    $description = sanitize($_POST['description'] ?? '');
    $price = (float)($_POST['price'] ?? 0);
    $discount_price = !empty($_POST['discount_price']) ? (float)$_POST['discount_price'] : null;
    $stock = (int)($_POST['stock'] ?? 0);
    $brand = sanitize($_POST['brand'] ?? '');

    // Verify ownership
    $stmt = $conn->prepare("SELECT id, main_image FROM products WHERE id = ? AND vendor_id = ?");
    $stmt->bind_param("ii", $product_id, $vendor_id);
    $stmt->execute();
    $product = $stmt->get_result()->fetch_assoc();
    if (!$product) {
        echo json_encode(['success' => false, 'message' => 'Product not found or unauthorized']);
        return;
    }

    $main_image = $product['main_image'];
    if (isset($_FILES['main_image']) && $_FILES['main_image']['error'] === UPLOAD_ERR_OK) {
        $new_image = uploadImage($_FILES['main_image'], 'product');
        if ($new_image) {
            // Delete old image
            if ($main_image !== 'default_product.png') {
                @unlink(UPLOAD_DIR . $main_image);
            }
            $main_image = $new_image;
        }
    }

    $slug = generateSlug($name) . '-' . $product_id;
    $stmt = $conn->prepare("UPDATE products SET name=?, slug=?, category_id=?, description=?, price=?, discount_price=?, stock=?, main_image=?, brand=? WHERE id=? AND vendor_id=?");
    $stmt->bind_param("ssissddisii", $name, $slug, $category_id, $description, $price, $discount_price, $stock, $main_image, $brand, $product_id, $vendor_id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Product updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update product']);
    }
}

function deleteProduct($conn) {
    if (!isVendorLoggedIn()) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        return;
    }

    $vendor_id = $_SESSION['vendor_id'];
    $product_id = (int)($_POST['product_id'] ?? $_GET['product_id'] ?? 0);

    $stmt = $conn->prepare("SELECT main_image FROM products WHERE id = ? AND vendor_id = ?");
    $stmt->bind_param("ii", $product_id, $vendor_id);
    $stmt->execute();
    $product = $stmt->get_result()->fetch_assoc();

    if (!$product) {
        echo json_encode(['success' => false, 'message' => 'Product not found or unauthorized']);
        return;
    }

    // Delete image file
    if ($product['main_image'] && $product['main_image'] !== 'default_product.png') {
        @unlink(UPLOAD_DIR . $product['main_image']);
    }

    $stmt = $conn->prepare("DELETE FROM products WHERE id = ? AND vendor_id = ?");
    $stmt->bind_param("ii", $product_id, $vendor_id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Product deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete product']);
    }
}

function toggleStatus($conn) {
    if (!isVendorLoggedIn()) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        return;
    }
    $vendor_id = $_SESSION['vendor_id'];
    $product_id = (int)($_POST['product_id'] ?? 0);

    $stmt = $conn->prepare("UPDATE products SET is_active = NOT is_active WHERE id = ? AND vendor_id = ?");
    $stmt->bind_param("ii", $product_id, $vendor_id);
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Product status updated']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update status']);
    }
}

function getProduct($conn) {
    $product_id = (int)($_GET['id'] ?? 0);
    $stmt = $conn->prepare("SELECT p.*, c.name as category_name, v.company_name, v.logo as vendor_logo, v.phone as vendor_phone
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        LEFT JOIN vendors v ON p.vendor_id = v.id
        WHERE p.id = ? AND p.is_active = 1");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $product = $stmt->get_result()->fetch_assoc();
    if ($product) {
        echo json_encode(['success' => true, 'product' => $product]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Product not found']);
    }
}

function listProducts($conn) {
    $category = sanitize($_GET['category'] ?? '');
    $limit = (int)($_GET['limit'] ?? 20);
    $offset = (int)($_GET['offset'] ?? 0);
    $sort = sanitize($_GET['sort'] ?? 'newest');
    $min_price = (float)($_GET['min_price'] ?? 0);
    $max_price = (float)($_GET['max_price'] ?? 9999999);

    $where = "WHERE p.is_active = 1";
    $params = [];
    $types = "";

    if (!empty($category)) {
        $where .= " AND c.slug = ?";
        $params[] = $category;
        $types .= "s";
    }
    if ($min_price > 0) {
        $where .= " AND p.price >= ?";
        $params[] = $min_price;
        $types .= "d";
    }
    if ($max_price < 9999999) {
        $where .= " AND p.price <= ?";
        $params[] = $max_price;
        $types .= "d";
    }

    $order = match($sort) {
        'price_asc' => 'p.price ASC',
        'price_desc' => 'p.price DESC',
        'popular' => 'p.views DESC',
        default => 'p.created_at DESC'
    };

    $sql = "SELECT p.id, p.name, p.price, p.discount_price, p.main_image, p.stock, p.views,
                   c.name as category_name, v.company_name
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            LEFT JOIN vendors v ON p.vendor_id = v.id
            $where ORDER BY $order LIMIT ? OFFSET ?";

    $params[] = $limit;
    $params[] = $offset;
    $types .= "ii";

    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    echo json_encode(['success' => true, 'products' => $products]);
}

function vendorProducts($conn) {
    if (!isVendorLoggedIn()) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        return;
    }
    $vendor_id = $_SESSION['vendor_id'];
    $stmt = $conn->prepare("SELECT p.*, c.name as category_name FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.vendor_id = ? ORDER BY p.created_at DESC");
    $stmt->bind_param("i", $vendor_id);
    $stmt->execute();
    $products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    echo json_encode(['success' => true, 'products' => $products]);
}

function searchProducts($conn) {
    $query = sanitize($_GET['q'] ?? '');
    $category = sanitize($_GET['category'] ?? '');
    $limit = (int)($_GET['limit'] ?? 20);

    if (empty($query)) {
        echo json_encode(['success' => true, 'products' => []]);
        return;
    }

    $search = "%$query%";
    $sql = "SELECT p.id, p.name, p.price, p.discount_price, p.main_image, c.name as category_name, v.company_name
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            LEFT JOIN vendors v ON p.vendor_id = v.id
            WHERE p.is_active = 1 AND (p.name LIKE ? OR p.description LIKE ? OR v.company_name LIKE ?)";

    $params = [$search, $search, $search];
    $types = "sss";

    if (!empty($category)) {
        $sql .= " AND c.slug = ?";
        $params[] = $category;
        $types .= "s";
    }

    $sql .= " ORDER BY p.views DESC LIMIT ?";
    $params[] = $limit;
    $types .= "i";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    echo json_encode(['success' => true, 'products' => $products]);
}

function incrementView($conn) {
    $product_id = (int)($_POST['product_id'] ?? 0);
    $conn->query("UPDATE products SET views = views + 1 WHERE id = $product_id");
    echo json_encode(['success' => true]);
}
?>
