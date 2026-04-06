<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';
requireVendorLogin();
$conn = getDBConnection();
$vendor_id = $_SESSION['vendor_id'];

$stmt = $conn->prepare("SELECT * FROM vendors WHERE id = ?");
$stmt->bind_param("i", $vendor_id);
$stmt->execute();
$vendor = $stmt->get_result()->fetch_assoc();

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $company_name = sanitize($_POST['company_name'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $address = sanitize($_POST['address'] ?? '');
    $description = sanitize($_POST['description'] ?? '');

    $logo = $vendor['logo'];
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
        $uploaded = uploadImage($_FILES['logo'], 'vendor');
        if ($uploaded) {
            if ($logo && $logo !== 'default_vendor.png') @unlink(UPLOAD_DIR . $logo);
            $logo = $uploaded;
        }
    }

    $stmt = $conn->prepare("UPDATE vendors SET company_name=?, phone=?, address=?, description=?, logo=? WHERE id=?");
    $stmt->bind_param("sssssi", $company_name, $phone, $address, $description, $logo, $vendor_id);
    if ($stmt->execute()) {
        $_SESSION['vendor_name'] = $company_name;
        $success = 'Profile updated successfully!';
        $vendor['company_name'] = $company_name;
        $vendor['phone'] = $phone;
        $vendor['address'] = $address;
        $vendor['description'] = $description;
        $vendor['logo'] = $logo;
    } else {
        $error = 'Failed to update profile.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Profile Settings - Sellora Vendor</title>
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
        <h1>Profile Settings</h1>
        <p>Manage your vendor account information</p>
      </div>
    </div>

    <div class="vendor-content">
      <?php if ($success): ?>
      <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= $success ?></div>
      <?php endif; ?>
      <?php if ($error): ?>
      <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?= $error ?></div>
      <?php endif; ?>

      <div style="display:grid; grid-template-columns: 1fr 300px; gap:24px; align-items:start;">
        <div class="product-form-card">
          <div class="product-form-header">
            <i class="fas fa-user-cog"></i> Store Information
          </div>
          <div class="product-form-body">
            <form method="POST" enctype="multipart/form-data">
              <div class="form-grid-2">
                <div class="form-group">
                  <label class="form-label">Company/Store Name *</label>
                  <input type="text" name="company_name" class="form-control" value="<?= htmlspecialchars($vendor['company_name']) ?>" required>
                </div>
                <div class="form-group">
                  <label class="form-label">Business Phone</label>
                  <input type="tel" name="phone" class="form-control" value="<?= htmlspecialchars($vendor['phone'] ?? '') ?>">
                </div>
              </div>

              <div class="form-group">
                <label class="form-label">Email Address</label>
                <input type="email" class="form-control" value="<?= htmlspecialchars($vendor['email']) ?>" disabled>
                <small style="font-size:12px; color:#999;">Email cannot be changed</small>
              </div>

              <div class="form-group">
                <label class="form-label">Business Address</label>
                <input type="text" name="address" class="form-control" value="<?= htmlspecialchars($vendor['address'] ?? '') ?>">
              </div>

              <div class="form-group">
                <label class="form-label">Business Description</label>
                <textarea name="description" class="form-control" rows="4"><?= htmlspecialchars($vendor['description'] ?? '') ?></textarea>
              </div>

              <div class="form-group">
                <label class="form-label">Store Logo</label>
                <div class="file-upload-wrap">
                  <input type="file" name="logo" accept="image/*">
                  <label class="file-upload-label">
                    <i class="fas fa-cloud-upload-alt"></i>
                    <span>Upload new logo</span>
                    <small>Leave empty to keep current</small>
                  </label>
                </div>
              </div>

              <button type="submit" class="btn btn-primary btn-lg">
                <i class="fas fa-save"></i> Save Changes
              </button>
            </form>
          </div>
        </div>

        <!-- PROFILE CARD -->
        <div>
          <div class="product-form-card">
            <div class="product-form-header"><i class="fas fa-store"></i> Store Preview</div>
            <div class="product-form-body" style="text-align:center;">
              <div style="width:80px; height:80px; border-radius:50%; background:linear-gradient(135deg,var(--primary),var(--primary-dark)); display:flex; align-items:center; justify-content:center; font-size:32px; color:#fff; font-weight:800; margin:0 auto 12px;">
                <?= strtoupper(substr($vendor['company_name'], 0, 1)) ?>
              </div>
              <h3 style="font-size:16px; font-weight:700;"><?= htmlspecialchars($vendor['company_name']) ?></h3>
              <p style="font-size:13px; color:#666; margin-top:4px;"><?= htmlspecialchars($vendor['email']) ?></p>
              <?php if ($vendor['phone']): ?>
              <p style="font-size:13px; color:#666;"><?= htmlspecialchars($vendor['phone']) ?></p>
              <?php endif; ?>
              <div style="margin-top:12px; padding:8px; background:#f9f9f9; border-radius:8px; font-size:12px; color:#666;">
                Member since <?= date('M Y', strtotime($vendor['created_at'])) ?>
              </div>
              <?php if ($vendor['is_verified']): ?>
              <div style="margin-top:8px; display:inline-flex; align-items:center; gap:6px; background:#e8f5e9; color:#2e7d32; padding:4px 12px; border-radius:20px; font-size:12px; font-weight:600;">
                <i class="fas fa-check-circle"></i> Verified Vendor
              </div>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
</body>
</html>
