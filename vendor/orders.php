<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';
requireVendorLogin();
$conn = getDBConnection();
$vendor_id = $_SESSION['vendor_id'];

$stmt = $conn->prepare("SELECT o.id, o.order_number, o.order_status, o.payment_status, o.total_amount, o.created_at,
    u.full_name, u.email, u.phone,
    SUM(oi.quantity) as items_count, SUM(oi.price * oi.quantity) as vendor_total
    FROM orders o
    JOIN order_items oi ON o.id = oi.order_id
    LEFT JOIN users u ON o.user_id = u.id
    WHERE oi.vendor_id = ?
    GROUP BY o.id
    ORDER BY o.created_at DESC");
$stmt->bind_param("i", $vendor_id);
$stmt->execute();
$orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Orders - Sellora Vendor</title>
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
        <h1>Orders</h1>
        <p><?= count($orders) ?> total orders</p>
      </div>
    </div>

    <div class="vendor-content">
      <div class="vendor-table-card">
        <div class="vendor-table-header">
          <h3><i class="fas fa-receipt"></i> All Orders</h3>
          <input type="text" id="orderSearch" class="form-control" placeholder="Search orders..." style="width:220px;" oninput="filterOrders()">
        </div>

        <?php if (empty($orders)): ?>
        <div class="empty-state">
          <i class="fas fa-receipt"></i>
          <h3>No orders yet</h3>
          <p>Orders will appear here when customers purchase your products</p>
        </div>
        <?php else: ?>
        <div style="overflow-x:auto;">
          <table class="vendor-table">
            <thead>
              <tr>
                <th>Order #</th>
                <th>Customer</th>
                <th>Items</th>
                <th>Your Revenue</th>
                <th>Payment</th>
                <th>Status</th>
                <th>Date</th>
              </tr>
            </thead>
            <tbody id="ordersBody">
              <?php foreach ($orders as $order): ?>
              <tr class="order-row" data-search="<?= strtolower($order['order_number'] . ' ' . $order['full_name']) ?>">
                <td><strong><?= htmlspecialchars($order['order_number']) ?></strong></td>
                <td>
                  <div><?= htmlspecialchars($order['full_name'] ?? 'Guest') ?></div>
                  <small style="color:#999;"><?= htmlspecialchars($order['email'] ?? '') ?></small>
                </td>
                <td><?= $order['items_count'] ?> item(s)</td>
                <td><strong><?= formatPrice($order['vendor_total']) ?></strong></td>
                <td>
                  <span class="status-badge <?= $order['payment_status'] === 'paid' ? 'status-active' : 'status-pending' ?>">
                    <?= ucfirst($order['payment_status']) ?>
                  </span>
                </td>
                <td>
                  <?php
                  $status_class = match($order['order_status']) {
                    'delivered' => 'status-active',
                    'cancelled' => 'status-inactive',
                    'shipped' => 'status-pending',
                    default => 'status-pending'
                  };
                  ?>
                  <span class="status-badge <?= $status_class ?>">
                    <?= ucfirst($order['order_status']) ?>
                  </span>
                </td>
                <td style="font-size:12px; color:#999;"><?= date('d M Y, H:i', strtotime($order['created_at'])) ?></td>
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

<script>
function filterOrders() {
  const search = document.getElementById('orderSearch').value.toLowerCase();
  document.querySelectorAll('.order-row').forEach(row => {
    row.style.display = !search || row.dataset.search.includes(search) ? '' : 'none';
  });
}
</script>
</body>
</html>
