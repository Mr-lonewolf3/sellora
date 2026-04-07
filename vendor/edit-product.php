<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';
requireVendorLogin();
$conn = getDBConnection();
$vendor_id = $_SESSION['vendor_id'];
$product_id = (int)($_GET['id'] ?? 0);

if (!$product_id) { redirect('products.php'); }

$stmt = $conn->prepare("SELECT * FROM products WHERE id = ? AND vendor_id = ?");
$stmt->bind_param("ii", $product_id, $vendor_id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();
if (!$product) { redirect('products.php'); }

$categories = getCategories($conn);
$main_img_url = file_exists('../uploads/products/' . $product['main_image'])
    ? '../uploads/products/' . $product['main_image']
    : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Product - Sellora Vendor</title>
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
        <h1>Edit Product</h1>
        <p><?= htmlspecialchars(substr($product['name'], 0, 50)) ?></p>
      </div>
      <div class="topbar-right">
        <a href="../client/product.php?id=<?= $product_id ?>" target="_blank" class="topbar-btn topbar-btn-outline">
          <i class="fas fa-eye"></i> Preview
        </a>
        <a href="products.php" class="topbar-btn topbar-btn-outline">
          <i class="fas fa-arrow-left"></i> Back
        </a>
      </div>
    </div>

    <div class="vendor-content">
      <div id="alertBox"></div>

      <form id="editProductForm" enctype="multipart/form-data">
        <input type="hidden" name="action" value="edit">
        <input type="hidden" name="product_id" value="<?= $product_id ?>">

        <div style="display:grid; grid-template-columns: 1fr 360px; gap:24px; align-items:start;">

          <div>
            <div class="product-form-card" style="margin-bottom:20px;">
              <div class="product-form-header">
                <i class="fas fa-info-circle"></i> Basic Information
              </div>
              <div class="product-form-body">
                <div class="form-group">
                  <label class="form-label">Product Name *</label>
                  <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($product['name']) ?>" required>
                </div>

                <div class="form-grid-2">
                  <div class="form-group">
                    <label class="form-label">Category *</label>
                    <select name="category_id" class="form-control form-select" required>
                      <?php foreach ($categories as $cat): ?>
                      <option value="<?= $cat['id'] ?>" <?= $cat['id'] == $product['category_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat['name']) ?>
                      </option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                  <div class="form-group">
                    <label class="form-label">Brand</label>
                    <input type="text" name="brand" class="form-control" value="<?= htmlspecialchars($product['brand'] ?? '') ?>">
                  </div>
                </div>

                <div class="form-group">
                  <label class="form-label">Product Description</label>
                  <textarea name="description" class="form-control" rows="5"><?= htmlspecialchars($product['description'] ?? '') ?></textarea>
                </div>
              </div>
            </div>

            <div class="product-form-card">
              <div class="product-form-header">
                <i class="fas fa-tag"></i> Pricing & Inventory
              </div>
              <div class="product-form-body">
                <div class="form-grid-3">
                  <div class="form-group">
                    <label class="form-label">Regular Price (KSh) *</label>
                    <input type="number" name="price" class="form-control" value="<?= $product['price'] ?>" step="0.01" min="0" required>
                  </div>
                  <div class="form-group">
                    <label class="form-label">Discount Price (KSh)</label>
                    <input type="number" name="discount_price" class="form-control" value="<?= $product['discount_price'] ?? '' ?>" step="0.01" min="0">
                  </div>
                  <div class="form-group">
                    <label class="form-label">Stock Quantity *</label>
                    <input type="number" name="stock" class="form-control" value="<?= $product['stock'] ?>" min="0" required>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div>
            <div class="product-form-card">
              <div class="product-form-header">
                <i class="fas fa-image"></i> Product Image
              </div>
              <div class="product-form-body">
                <?php if ($main_img_url): ?>
                <div style="margin-bottom:12px;">
                  <p style="font-size:12px; color:#666; margin-bottom:6px;">Current Image:</p>
                  <img src="<?= $main_img_url ?>" style="width:100%; border-radius:10px; object-fit:cover; max-height:200px;" id="currentImg">
                </div>
                <?php endif; ?>

                <div class="form-group">
                  <label class="form-label">Replace Image (optional)</label>
                  <div class="image-upload-area">
                    <input type="file" name="main_image" accept="image/*" onchange="previewNewImage(this)">
                    <i class="fas fa-cloud-upload-alt"></i>
                    <p>Upload new image</p>
                    <small>Leave empty to keep current</small>
                  </div>
                  <img id="newImgPreview" style="display:none; width:100%; border-radius:10px; margin-top:8px; max-height:200px; object-fit:cover;">
                </div>
              </div>
            </div>

            <div style="margin-top:16px;">
              <button type="submit" class="btn btn-primary btn-block btn-lg" id="submitBtn">
                <i class="fas fa-save"></i> Save Changes
              </button>
              <button type="button" class="btn btn-danger btn-block" style="margin-top:10px;"
                onclick="deleteProduct(<?= $product_id ?>, '<?= htmlspecialchars(addslashes($product['name'])) ?>')">
                <i class="fas fa-trash"></i> Delete This Product
              </button>
            </div>
          </div>

        </div>
      </form>
    </div>
  </div>
</div>

<div id="toast-container"></div>

<script>
function previewNewImage(input) {
  if (input.files && input.files[0]) {
    const reader = new FileReader();
    reader.onload = e => {
      const preview = document.getElementById('newImgPreview');
      preview.src = e.target.result;
      preview.style.display = 'block';
    };
    reader.readAsDataURL(input.files[0]);
  }
}

document.getElementById('editProductForm').addEventListener('submit', async function(e) {
  e.preventDefault();
  const btn = document.getElementById('submitBtn');
  btn.disabled = true;
  btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';

  const formData = new FormData(this);
  const res = await fetch('../api/products.php', { method: 'POST', body: formData });
  const data = await res.json();
  const alertBox = document.getElementById('alertBox');

  if (data.success) {
    alertBox.innerHTML = `<div class="alert alert-success"><i class="fas fa-check-circle"></i> ${data.message}</div>`;
    setTimeout(() => window.location.href = 'products.php', 1200);
  } else {
    alertBox.innerHTML = `<div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> ${data.message}</div>`;
    btn.disabled = false;
    btn.innerHTML = '<i class="fas fa-save"></i> Save Changes';
  }
});

function deleteProduct(id, name) {
  if (!confirm(`Delete "${name}"?\n\nThis cannot be undone.`)) return;
  fetch('../api/products.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `action=delete&product_id=${id}`
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      showToast(data.message, 'success');
      setTimeout(() => window.location.href = 'products.php', 1000);
    } else {
      showToast(data.message, 'error');
    }
  });
}

function showToast(message, type = 'success') {
  const container = document.getElementById('toast-container');
  const toast = document.createElement('div');
  toast.className = `toast ${type}`;
  toast.innerHTML = `<i class="fas fa-check-circle"></i> ${message}`;
  container.appendChild(toast);
  setTimeout(() => toast.remove(), 3000);
}
</script>
</body>
</html>
