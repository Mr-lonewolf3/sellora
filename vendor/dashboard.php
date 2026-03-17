<?php
// if (session_status() === PHP_SESSION_NONE) {
//     session_start();
// }
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';
requireVendorLogin();

$conn = getDBConnection();
$vendor_id = $_SESSION['vendor_id'];



// Stats
$stats_stmt = $conn->prepare("SELECT
    COUNT(*) as total_products,
    SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active_products,
    SUM(stock) as total_stock,
    SUM(views) as total_views
    FROM products WHERE vendor_id = ?");
$stats_stmt->bind_param("i", $vendor_id);
$stats_stmt->execute();
$stats = $stats_stmt->get_result()->fetch_assoc();

// Total orders & revenue
$order_stmt = $conn->prepare("SELECT COUNT(DISTINCT o.id) as total_orders, COALESCE(SUM(oi.price * oi.quantity), 0) as revenue
    FROM order_items oi JOIN orders o ON oi.order_id = o.id WHERE oi.vendor_id = ?");
$order_stmt->bind_param("i", $vendor_id);
$order_stmt->execute();
$order_stats = $order_stmt->get_result()->fetch_assoc();

// Recent products
$recent_stmt = $conn->prepare("SELECT p.*, c.name as category_name FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    WHERE p.vendor_id = ? ORDER BY p.created_at DESC LIMIT 8");
$recent_stmt->bind_param("i", $vendor_id);
$recent_stmt->execute();
$recent_products = $recent_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Recent orders
$recent_orders_stmt = $conn->prepare("SELECT o.order_number, o.order_status, o.total_amount, o.created_at, u.full_name
    FROM orders o JOIN order_items oi ON o.id = oi.order_id LEFT JOIN users u ON o.user_id = u.id
    WHERE oi.vendor_id = ? GROUP BY o.id ORDER BY o.created_at DESC LIMIT 5");
$recent_orders_stmt->bind_param("i", $vendor_id);
$recent_orders_stmt->execute();
$recent_orders = $recent_orders_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Vendor info
$vendor_stmt = $conn->prepare("SELECT * FROM vendors WHERE id = ?");
$vendor_stmt->bind_param("i", $vendor_id);
$vendor_stmt->execute();
$vendor = $vendor_stmt->get_result()->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard - Sellora Vendor</title>
  <link rel="stylesheet" href="../client/css/style.css">
  <link rel="stylesheet" href="css/vendor.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<div class="vendor-layout">
  <?php include 'includes/sidebar.php'; ?>

  <div class="vendor-main">
    <!-- TOP BAR -->
    <div class="vendor-topbar">
      <div class="topbar-left">
        <h1>Dashboard</h1>
        <p>Welcome back, <?= htmlspecialchars($vendor['company_name']) ?>!</p>
      </div>
      <div class="topbar-right">
        <a href="../client/home.php" target="_blank" class="topbar-btn topbar-btn-outline">
          <i class="fas fa-external-link-alt"></i> View Store
        </a>
        <a href="add-product.php" class="topbar-btn topbar-btn-primary">
          <i class="fas fa-plus"></i> Add Product
        </a>
      </div>
    </div>

    <div class="vendor-content">

      <!-- STATS CARDS -->
      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-icon" style="background:#fff3e0;">
            <i class="fas fa-box" style="color:#ff6d00;"></i>
          </div>
          <div class="stat-info">
            <strong><?= number_format($stats['total_products'] ?? 0) ?></strong>
            <span>Total Products</span>
          </div>
          <div class="stat-trend trend-up"><i class="fas fa-arrow-up"></i> Active: <?= $stats['active_products'] ?? 0 ?></div>
        </div>

        <div class="stat-card">
          <div class="stat-icon" style="background:#e8f5e9;">
            <i class="fas fa-shopping-bag" style="color:#2e7d32;"></i>
          </div>
          <div class="stat-info">
            <strong><?= number_format($order_stats['total_orders'] ?? 0) ?></strong>
            <span>Total Orders</span>
          </div>
          <div class="stat-trend trend-up"><i class="fas fa-arrow-up"></i> All time</div>
        </div>

        <div class="stat-card">
          <div class="stat-icon" style="background:#e3f2fd;">
            <i class="fas fa-coins" style="color:#0277bd;"></i>
          </div>
          <div class="stat-info">
            <strong><?= formatPrice($order_stats['revenue'] ?? 0) ?></strong>
            <span>Total Revenue</span>
          </div>
          <div class="stat-trend trend-up"><i class="fas fa-arrow-up"></i> All time</div>
        </div>

        <div class="stat-card">
          <div class="stat-icon" style="background:#fce4ec;">
            <i class="fas fa-eye" style="color:#c62828;"></i>
          </div>
          <div class="stat-info">
            <strong><?= number_format($stats['total_views'] ?? 0) ?></strong>
            <span>Product Views</span>
          </div>
          <div class="stat-trend trend-up"><i class="fas fa-arrow-up"></i> Total</div>
        </div>
      </div>

      <!-- RECENT PRODUCTS & ORDERS -->
      <div class="dashboard-grid">
        <!-- RECENT PRODUCTS -->
        <div class="vendor-table-card">
          <div class="vendor-table-header">
            <h3><i class="fas fa-box"></i> Recent Products</h3>
            <a href="products.php" class="topbar-btn topbar-btn-outline" style="font-size:12px; padding:6px 12px;">View All</a>
          </div>
          <table class="vendor-table">
            <thead>
              <tr>
                <th>Product</th>
                <th>Category</th>
                <th>Price</th>
                <th>Stock</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($recent_products)): ?>
              <tr><td colspan="6" style="text-align:center; padding:30px; color:#999;">No products yet. <a href="add-product.php" style="color:var(--primary);">Add your first product!</a></td></tr>
              <?php else: ?>
              <?php foreach ($recent_products as $p): ?>
              <?php $img = file_exists('../uploads/products/' . $p['main_image']) ? '../uploads/products/' . $p['main_image'] : 'https://via.placeholder.com/48x48/f5f5f5/999?text=P'; ?>
              <tr>
                <td>
                  <div style="display:flex; align-items:center; gap:10px;">
                    <img src="<?= $img ?>" class="product-table-img" alt="">
                    <div class="product-table-name">
                      <?= htmlspecialchars(substr($p['name'], 0, 30)) ?>
                      <small>SKU: <?= htmlspecialchars($p['sku'] ?: 'N/A') ?></small>
                    </div>
                  </div>
                </td>
                <td><?= htmlspecialchars($p['category_name']) ?></td>
                <td><strong><?= formatPrice($p['price']) ?></strong></td>
                <td>
                  <?php if ($p['stock'] <= 0): ?>
                  <span class="status-badge status-inactive">Out of Stock</span>
                  <?php elseif ($p['stock'] <= 5): ?>
                  <span class="status-badge status-low"><?= $p['stock'] ?> left</span>
                  <?php else: ?>
                  <span><?= $p['stock'] ?></span>
                  <?php endif; ?>
                </td>
                <td>
                  <span class="status-badge <?= $p['is_active'] ? 'status-active' : 'status-inactive' ?>">
                    <i class="fas fa-circle" style="font-size:8px;"></i>
                    <?= $p['is_active'] ? 'Active' : 'Inactive' ?>
                  </span>
                </td>
                <td>
                  <div class="action-btns">
                    <a href="edit-product.php?id=<?= $p['id'] ?>" class="action-btn action-btn-edit" title="Edit"><i class="fas fa-edit"></i></a>
                    <button class="action-btn action-btn-delete" onclick="deleteProduct(<?= $p['id'] ?>, '<?= htmlspecialchars($p['name']) ?>')" title="Delete"><i class="fas fa-trash"></i></button>
                    <a href="../client/product.php?id=<?= $p['id'] ?>" target="_blank" class="action-btn action-btn-view" title="View"><i class="fas fa-eye"></i></a>
                  </div>
                </td>
              </tr>
              <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>

        <!-- RECENT ORDERS -->
        <div class="vendor-table-card">
          <div class="vendor-table-header">
            <h3><i class="fas fa-receipt"></i> Recent Orders</h3>
          </div>
          <?php if (empty($recent_orders)): ?>
          <div style="padding:30px; text-align:center; color:#999;">
            <i class="fas fa-receipt" style="font-size:32px; opacity:0.3; margin-bottom:8px; display:block;"></i>
            <p>No orders yet</p>
          </div>
          <?php else: ?>
          <table class="vendor-table">
            <thead>
              <tr><th>Order #</th><th>Customer</th><th>Amount</th><th>Status</th></tr>
            </thead>
            <tbody>
              <?php foreach ($recent_orders as $order): ?>
              <tr>
                <td><strong><?= htmlspecialchars($order['order_number']) ?></strong></td>
                <td><?= htmlspecialchars($order['full_name'] ?? 'Guest') ?></td>
                <td><?= formatPrice($order['total_amount']) ?></td>
                <td>
                  <span class="status-badge <?= $order['order_status'] === 'delivered' ? 'status-active' : ($order['order_status'] === 'cancelled' ? 'status-inactive' : 'status-pending') ?>">
                    <?= ucfirst($order['order_status']) ?>
                  </span>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
          <?php endif; ?>
        </div>
      </div>

    </div>
  </div>
</div>

<div id="toast-container"></div>

<script>
function deleteProduct(id, name) {
  if (!confirm(`Delete "${name}"? This cannot be undone.`)) return;
  fetch('../api/products.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `action=delete&product_id=${id}`
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      showToast(data.message, 'success');
      setTimeout(() => location.reload(), 1000);
    } else {
      showToast(data.message, 'error');
    }
  });
}

function showToast(message, type = 'success') {
  const container = document.getElementById('toast-container');
  const toast = document.createElement('div');
  toast.className = `toast ${type}`;
  const icon = type === 'success' ? 'check-circle' : 'times-circle';
  toast.innerHTML = `<i class="fas fa-${icon}"></i> ${message}`;
  container.appendChild(toast);
  setTimeout(() => { toast.style.opacity = '0'; setTimeout(() => toast.remove(), 300); }, 3000);
}
</script>
</body>
</html>
