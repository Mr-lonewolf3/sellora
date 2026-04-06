<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';
requireVendorLogin();
$conn = getDBConnection();
$categories = getCategories($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Add Product - Sellora Vendor</title>
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
        <h1>Add New Product</h1>
        <p>Upload a product from your local drive</p>
      </div>
      <div class="topbar-right">
        <a href="products.php" class="topbar-btn topbar-btn-outline">
          <i class="fas fa-arrow-left"></i> Back to Products
        </a>
      </div>
    </div>

    <div class="vendor-content">
      <div id="alertBox"></div>

      <form id="addProductForm" enctype="multipart/form-data">
        <input type="hidden" name="action" value="add">

        <div style="display:grid; grid-template-columns: 1fr 360px; gap:24px; align-items:start;">

          <!-- MAIN FORM -->
          <div>
            <!-- Basic Info -->
            <div class="product-form-card" style="margin-bottom:20px;">
              <div class="product-form-header">
                <i class="fas fa-info-circle"></i> Basic Information
              </div>
              <div class="product-form-body">
                <div class="form-group">
                  <label class="form-label">Product Name *</label>
                  <input type="text" name="name" class="form-control" placeholder="Enter product name" required>
                </div>

                <div class="form-grid-2">
                  <div class="form-group">
                    <label class="form-label">Category *</label>
                    <select name="category_id" class="form-control form-select" required>
                      <option value="">Select Category</option>
                      <?php foreach ($categories as $cat): ?>
                      <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                  <div class="form-group">
                    <label class="form-label">Brand</label>
                    <input type="text" name="brand" class="form-control" placeholder="e.g. Samsung, Nike">
                  </div>
                </div>

                <div class="form-group">
                  <label class="form-label">Product Description</label>
                  <textarea name="description" class="form-control" rows="5" placeholder="Describe your product in detail..."></textarea>
                </div>
              </div>
            </div>

            <!-- Pricing & Stock -->
            <div class="product-form-card" style="margin-bottom:20px;">
              <div class="product-form-header">
                <i class="fas fa-tag"></i> Pricing & Inventory
              </div>
              <div class="product-form-body">
                <div class="form-grid-3">
                  <div class="form-group">
                    <label class="form-label">Regular Price (KSh) *</label>
                    <input type="number" name="price" class="form-control" placeholder="0.00" step="0.01" min="0" required>
                  </div>
                  <div class="form-group">
                    <label class="form-label">Discount Price (KSh)</label>
                    <input type="number" name="discount_price" class="form-control" placeholder="Optional" step="0.01" min="0">
                  </div>
                  <div class="form-group">
                    <label class="form-label">Stock Quantity *</label>
                    <input type="number" name="stock" class="form-control" placeholder="0" min="0" required>
                  </div>
                </div>
                <div class="form-group">
                  <label class="form-label">SKU (Stock Keeping Unit)</label>
                  <input type="text" name="sku" class="form-control" placeholder="e.g. TECH-001">
                </div>
              </div>
            </div>
          </div>

          <!-- IMAGES PANEL -->
          <div>
            <div class="product-form-card">
              <div class="product-form-header">
                <i class="fas fa-images"></i> Product Images
              </div>
              <div class="product-form-body">
                <div class="form-group">
                  <label class="form-label">Main Product Image *</label>
                  <div class="image-upload-area" id="mainImgArea">
                    <input type="file" name="main_image" id="mainImageInput" accept="image/*" onchange="previewMainImage(this)" required>
                    <i class="fas fa-cloud-upload-alt"></i>
                    <p>Click to upload main image</p>
                    <small>JPG, PNG, WEBP — Max 5MB</small>
                  </div>
                  <div id="mainImagePreview" style="margin-top:10px; display:none;">
                    <img id="mainImgPreviewImg" style="width:100%; border-radius:10px; object-fit:cover; max-height:200px;">
                    <button type="button" onclick="clearMainImage()" style="margin-top:6px; font-size:12px; color:var(--primary); background:none; border:none; cursor:pointer;">
                      <i class="fas fa-times"></i> Remove
                    </button>
                  </div>
                </div>

                <div class="form-group">
                  <label class="form-label">Additional Images (optional)</label>
                  <div class="image-upload-area">
                    <input type="file" name="extra_images[]" id="extraImagesInput" accept="image/*" multiple onchange="previewExtraImages(this)">
                    <i class="fas fa-images"></i>
                    <p>Upload more images</p>
                    <small>Up to 5 additional images</small>
                  </div>
                  <div class="image-preview-grid" id="extraImagesPreview"></div>
                </div>

                <div class="alert alert-info" style="font-size:13px; margin-top:12px;">
                  <i class="fas fa-info-circle"></i>
                  Use clear, high-quality images. Recommended size: 800×800px
                </div>
              </div>
            </div>

            <!-- SUBMIT -->
            <div style="margin-top:16px;">
              <button type="submit" class="btn btn-primary btn-block btn-lg" id="submitBtn">
                <i class="fas fa-plus-circle"></i> Add Product
              </button>
              <a href="products.php" class="btn btn-outline btn-block" style="margin-top:10px;">
                <i class="fas fa-times"></i> Cancel
              </a>
            </div>
          </div>

        </div>
      </form>
    </div>
  </div>
</div>

<div id="toast-container"></div>

<script>
function previewMainImage(input) {
  if (input.files && input.files[0]) {
    const reader = new FileReader();
    reader.onload = e => {
      document.getElementById('mainImgPreviewImg').src = e.target.result;
      document.getElementById('mainImagePreview').style.display = 'block';
      document.getElementById('mainImgArea').style.display = 'none';
    };
    reader.readAsDataURL(input.files[0]);
  }
}

function clearMainImage() {
  document.getElementById('mainImageInput').value = '';
  document.getElementById('mainImagePreview').style.display = 'none';
  document.getElementById('mainImgArea').style.display = 'block';
}

function previewExtraImages(input) {
  const preview = document.getElementById('extraImagesPreview');
  preview.innerHTML = '';
  Array.from(input.files).slice(0, 5).forEach(file => {
    const reader = new FileReader();
    reader.onload = e => {
      const div = document.createElement('div');
      div.className = 'image-preview-item';
      div.innerHTML = `<img src="${e.target.result}">`;
      preview.appendChild(div);
    };
    reader.readAsDataURL(file);
  });
}

document.getElementById('addProductForm').addEventListener('submit', async function(e) {
  e.preventDefault();
  const btn = document.getElementById('submitBtn');
  btn.disabled = true;
  btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Uploading product...';

  const formData = new FormData(this);
  try {
    const res = await fetch('../api/products.php', { method: 'POST', body: formData });
    const data = await res.json();
    const alertBox = document.getElementById('alertBox');

    if (data.success) {
      alertBox.innerHTML = `<div class="alert alert-success"><i class="fas fa-check-circle"></i> ${data.message}</div>`;
      showToast(data.message, 'success');
      setTimeout(() => window.location.href = 'products.php', 1500);
    } else {
      alertBox.innerHTML = `<div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> ${data.message}</div>`;
      btn.disabled = false;
      btn.innerHTML = '<i class="fas fa-plus-circle"></i> Add Product';
    }
  } catch (err) {
    document.getElementById('alertBox').innerHTML = `<div class="alert alert-danger">Upload failed. Please try again.</div>`;
    btn.disabled = false;
    btn.innerHTML = '<i class="fas fa-plus-circle"></i> Add Product';
  }
});

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
