<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
header('Content-Type: application/json');

$conn = getDBConnection();
$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'add':
        addToCart($conn);
        break;
    case 'update':
        updateCart($conn);
        break;
    case 'remove':
        removeFromCart($conn);
        break;
    case 'get':
        getCart($conn);
        break;
    case 'count':
        getCartCount2($conn);
        break;
    case 'clear':
        clearCart($conn);
        break;
    case 'checkout':
        checkout($conn);
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}

function getIdentifier() {
    if (isLoggedIn()) {
        return ['type' => 'user', 'value' => $_SESSION['user_id']];
    }
    return ['type' => 'session', 'value' => session_id()];
}

function addToCart($conn) {
    $product_id = (int)($_POST['product_id'] ?? 0);
    $quantity = (int)($_POST['quantity'] ?? 1);
    $id = getIdentifier();

    // Check product exists and has stock
    $stmt = $conn->prepare("SELECT id, stock FROM products WHERE id = ? AND is_active = 1");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $product = $stmt->get_result()->fetch_assoc();
    if (!$product) {
        echo json_encode(['success' => false, 'message' => 'Product not found']);
        return;
    }

    // Check if already in cart
    if ($id['type'] === 'user') {
        $stmt = $conn->prepare("SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?");
        $stmt->bind_param("ii", $id['value'], $product_id);
    } else {
        $stmt = $conn->prepare("SELECT id, quantity FROM cart WHERE session_id = ? AND product_id = ?");
        $stmt->bind_param("si", $id['value'], $product_id);
    }
    $stmt->execute();
    $existing = $stmt->get_result()->fetch_assoc();

    if ($existing) {
        $new_qty = $existing['quantity'] + $quantity;
        $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
        $stmt->bind_param("ii", $new_qty, $existing['id']);
        $stmt->execute();
    } else {
        if ($id['type'] === 'user') {
            $stmt = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
            $stmt->bind_param("iii", $id['value'], $product_id, $quantity);
        } else {
            $stmt = $conn->prepare("INSERT INTO cart (session_id, product_id, quantity) VALUES (?, ?, ?)");
            $stmt->bind_param("sii", $id['value'], $product_id, $quantity);
        }
        $stmt->execute();
    }

    $count = getCartCount($conn);
    echo json_encode(['success' => true, 'message' => 'Added to cart', 'cart_count' => $count]);
}

function updateCart($conn) {
    $cart_id = (int)($_POST['cart_id'] ?? 0);
    $quantity = (int)($_POST['quantity'] ?? 1);
    $id = getIdentifier();

    if ($quantity <= 0) {
        $stmt = $conn->prepare("DELETE FROM cart WHERE id = ?");
        $stmt->bind_param("i", $cart_id);
    } else {
        $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
        $stmt->bind_param("ii", $quantity, $cart_id);
    }
    $stmt->execute();
    $count = getCartCount($conn);
    echo json_encode(['success' => true, 'cart_count' => $count]);
}

function removeFromCart($conn) {
    $cart_id = (int)($_POST['cart_id'] ?? 0);
    $stmt = $conn->prepare("DELETE FROM cart WHERE id = ?");
    $stmt->bind_param("i", $cart_id);
    $stmt->execute();
    $count = getCartCount($conn);
    echo json_encode(['success' => true, 'message' => 'Item removed', 'cart_count' => $count]);
}

function getCart($conn) {
    $id = getIdentifier();
    if ($id['type'] === 'user') {
        $stmt = $conn->prepare("SELECT c.id as cart_id, c.quantity, p.id as product_id, p.name, p.price, p.discount_price, p.main_image, p.stock, v.company_name
            FROM cart c
            JOIN products p ON c.product_id = p.id
            JOIN vendors v ON p.vendor_id = v.id
            WHERE c.user_id = ?");
        $stmt->bind_param("i", $id['value']);
    } else {
        $stmt = $conn->prepare("SELECT c.id as cart_id, c.quantity, p.id as product_id, p.name, p.price, p.discount_price, p.main_image, p.stock, v.company_name
            FROM cart c
            JOIN products p ON c.product_id = p.id
            JOIN vendors v ON p.vendor_id = v.id
            WHERE c.session_id = ?");
        $stmt->bind_param("s", $id['value']);
    }
    $stmt->execute();
    $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    $total = 0;
    foreach ($items as &$item) {
        $effective_price = $item['discount_price'] ?? $item['price'];
        $item['effective_price'] = $effective_price;
        $item['subtotal'] = $effective_price * $item['quantity'];
        $total += $item['subtotal'];
    }

    echo json_encode(['success' => true, 'items' => $items, 'total' => $total]);
}

function getCartCount2($conn) {
    $count = getCartCount($conn);
    echo json_encode(['success' => true, 'count' => $count]);
}

function clearCart($conn) {
    $id = getIdentifier();
    if ($id['type'] === 'user') {
        $conn->query("DELETE FROM cart WHERE user_id = {$id['value']}");
    } else {
        $sid = $conn->real_escape_string($id['value']);
        $conn->query("DELETE FROM cart WHERE session_id = '$sid'");
    }
    echo json_encode(['success' => true]);
}

function checkout($conn) {
    if (!isLoggedIn()) {
        echo json_encode(['success' => false, 'message' => 'Please login to checkout', 'redirect' => '../client/login.php']);
        return;
    }

    $user_id = $_SESSION['user_id'];
    $shipping_address = sanitize($_POST['shipping_address'] ?? '');
    $payment_method = sanitize($_POST['payment_method'] ?? 'cash_on_delivery');

    if (empty($shipping_address)) {
        echo json_encode(['success' => false, 'message' => 'Shipping address is required']);
        return;
    }

    // Get cart items
    $stmt = $conn->prepare("SELECT c.quantity, p.id as product_id, p.vendor_id, p.price, p.discount_price, p.stock
        FROM cart c JOIN products p ON c.product_id = p.id WHERE c.user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    if (empty($items)) {
        echo json_encode(['success' => false, 'message' => 'Your cart is empty']);
        return;
    }

    $total = 0;
    foreach ($items as $item) {
        $price = $item['discount_price'] ?? $item['price'];
        $total += $price * $item['quantity'];
    }

    $order_number = generateOrderNumber();
    $stmt = $conn->prepare("INSERT INTO orders (user_id, order_number, total_amount, shipping_address, payment_method) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("isdss", $user_id, $order_number, $total, $shipping_address, $payment_method);
    $stmt->execute();
    $order_id = $conn->insert_id;

    foreach ($items as $item) {
        $price = $item['discount_price'] ?? $item['price'];
        $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, vendor_id, quantity, price) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("iiiid", $order_id, $item['product_id'], $item['vendor_id'], $item['quantity'], $price);
        $stmt->execute();
    }

    // Clear cart
    $conn->query("DELETE FROM cart WHERE user_id = $user_id");

    echo json_encode(['success' => true, 'message' => "Order placed successfully! Order #$order_number", 'order_number' => $order_number]);
}
?>
