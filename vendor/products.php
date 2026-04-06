<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';
requireVendorLogin();
$conn = getDBConnection();
$vendor_id = $_SESSION['vendor_id'];

$stmt = $conn->prepare("SELECT p.*, c.name as category_name FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    WHERE p.vendor_id = ? ORDER BY p.created_at DESC");
$stmt->bind_param("i", $vendor_id);
$stmt->execute();
$products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$total = count($products);
$active = array_filter($products, fn($p) => $p['is_active']);
$inactive = $total - count($active);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Products - Sellora Vendor</title>
  <link rel="stylesheet" href="../client/css/style.css">
  <link rel="stylesheet" href="css/vendor.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<div class="vendor-layout">
  <?php include 'includes/sidebar.php'; ?>

  <div class="vendor-main">
    <div class="vendor-topbar">
      <div class="topbar-left">
        <h1>My Products</h1>
        <p><?= $total ?> total &nbsp;|&nbsp; <?= count($active) ?> active &nbsp;|&nbsp; <?= $inactive ?> inactive</p>
      </div>
      <div class="topbar-right">
        <a href="add-product.php" class="topbar-btn topbar-btn-primary">
          <i class="fas fa-plus"></i> Add New Product
        </a>
      </div>
    </div>

    <div class="vendor-content">

      <!-- QUICK STATS -->
      <div class="stats-grid" style="grid-template-columns: repeat(3,1fr); margin-bottom:24px;">
        <div class="stat-card">
          <div class="stat-icon" style="background:#e3f2fd;"><i class="fas fa-box" style="color:#0277bd;"></i></div>
          <div class="stat-info"><strong><?= $total ?></strong><span>Total Products</span></div>
        </div>
        <div class="stat-card">
          <div class="stat-icon" style="background:#e8f5e9;"><i class="fas fa-check-circle" style="color:#2e7d32;"></i></div>
          <div class="stat-info"><strong><?= count($active) ?></strong><span>Active</span></div>
        </div>
        <div class="stat-card">
          <div class="stat-icon" style="background:#ffebee;"><i class="fas fa-times-circle" style="color:var(--primary);"></i></div>
          <div class="stat-info"><strong><?= $inactive ?></strong><span>Inactive</span></div>
        </div>
      </div>

      <!-- SEARCH & FILTER BAR -->
      <div class="vendor-table-card">
        <div class="vendor-table-header">
          <h3><i class="fas fa-box"></i> Product List</h3>
          <div style="display:flex; gap:10px; align-items:center;">
            <input type="text" id="productSearch" class="form-control" placeholder="Search products..." style="width:220px;" oninput="filterProducts()">
            <select id="statusFilter" class="form-control form-select" style="width:140px;" onchange="filterProducts()">
              <option value="">All Status</option>
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
            </select>
          </div>
        </div>

        <?php if (empty($products)): ?>
        <div class="empty-state">
          <i class="fas fa-box-open"></i>
          <h3>No products yet</h3>
          <p>Start adding products to your store</p>
          <a href="add-product.php" class="btn btn-primary" style="margin-top:16px;"><i class="fas fa-plus"></i> Add First Product</a>
        </div>
        <?php else: ?>
        <div style="overflow-x:auto;">
          <table class="vendor-table" id="productsTable">
            <thead>
              <tr>
                <th>#</th>
                <th>Product</th>
                <th>Category</th>
                <th>Price</th>
                <th>Discount</th>
                <th>Stock</th>
                <th>Views</th>
                <th>Status</th>
                <th>Date Added</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody id="productsBody">
              <?php foreach ($products as $i => $p): ?>
              <?php $img = file_exists('../uploads/products/' . $p['main_image']) ? '../uploads/products/' . $p['main_image'] : 'https://via.placeholder.com/48x48/f5f5f5/999?text=P'; ?>
              <tr class="product-row" data-name="<?= strtolower(htmlspecialchars($p['name'])) ?>" data-status="<?= $p['is_active'] ? 'active' : 'inactive' ?>">
                <td><?= $i + 1 ?></td>
                <td>
                  <div style="display:flex; align-items:center; gap:10px;">
                    <img src="<?= $img ?>" class="product-table-img" alt="">
                    <div class="product-table-name">
                      <?= htmlspecialchars(substr($p['name'], 0, 35)) ?><?= strlen($p['name']) > 35 ? '...' : '' ?>
                      <small><?= htmlspecialchars($p['category_name']) ?></small>
                    </div>
                  </div>
                </td>
                <td><?= htmlspecialchars($p['category_name']) ?></td>
                <td><strong><?= formatPrice($p['price']) ?></strong></td>
                <td><?= $p['discount_price'] ? formatPrice($p['discount_price']) : '<span style="color:#ccc;">—</span>' ?></td>
                <td>
                  <?php if ($p['stock'] <= 0): ?>
                  <span class="status-badge status-inactive">0</span>
                  <?php elseif ($p['stock'] <= 5): ?>
                  <span class="status-badge status-low"><?= $p['stock'] ?></span>
                  <?php else: ?>
                  <span><?= $p['stock'] ?></span>
                  <?php endif; ?>
                </td>
                <td><?= number_format($p['views']) ?></td>
                <td>
                  <span class="status-badge <?= $p['is_active'] ? 'status-active' : 'status-inactive' ?>">
                    <i class="fas fa-circle" style="font-size:8px;"></i>
                    <?= $p['is_active'] ? 'Active' : 'Inactive' ?>
                  </span>
                </td>
                <td style="font-size:12px; color:#999;"><?= date('d M Y', strtotime($p['created_at'])) ?></td>
                <td>
                  <div class="action-btns">
                    <a href="edit-product.php?id=<?= $p['id'] ?>" class="action-btn action-btn-edit" title="Edit Product"><i class="fas fa-edit"></i></a>
                    <button class="action-btn action-btn-toggle" onclick="toggleStatus(<?= $p['id'] ?>)" title="Toggle Status"><i class="fas fa-power-off"></i></button>
                    <button class="action-btn action-btn-delete" onclick="deleteProduct(<?= $p['id'] ?>, '<?= htmlspecialchars(addslashes($p['name'])) ?>')" title="Delete Product"><i class="fas fa-trash"></i></button>
                    <a href="../client/product.php?id=<?= $p['id'] ?>" target="_blank" class="action-btn action-btn-view" title="View on Store"><i class="fas fa-eye"></i></a>
                  </div>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <?php endif; ?>
      </div>

    </div>
  </div>
</div>

<div id="toast-container"></div>

<script>
function filterProducts() {
  const search = document.getElementById('productSearch').value.toLowerCase();
  const status = document.getElementById('statusFilter').value;
  document.querySelectorAll('.product-row').forEach(row => {
    const name = row.dataset.name;
    const rowStatus = row.dataset.status;
    const matchSearch = !search || name.includes(search);
    const matchStatus = !status || rowStatus === status;
    row.style.display = matchSearch && matchStatus ? '' : 'none';
  });
}

function deleteProduct(id, name) {
  if (!confirm(`Delete "${name}"?\n\nThis action cannot be undone.`)) return;
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

function toggleStatus(id) {
  fetch('../api/products.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `action=toggle_status&product_id=${id}`
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      showToast(data.message, 'success');
      setTimeout(() => location.reload(), 800);
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
