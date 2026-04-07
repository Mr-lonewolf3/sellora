<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
if (isVendorLoggedIn()) redirect('dashboard.php');
require_once __DIR__ . '/../config/helpers.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Become a Vendor - Sellora</title>
  <link rel="stylesheet" href="../client/css/style.css">
  <link rel="stylesheet" href="../client/css/auth.css">
  <link rel="stylesheet" href="css/vendor.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body class="auth-body">

<div class="auth-wrapper">
  <div class="auth-left vendor-auth-left">
    <div class="auth-left-content">
      <a href="../index.php" class="auth-logo">Sell<span>ora</span></a>
      <h2>Start Selling Today!</h2>
      <p>Join thousands of vendors on Sellora and reach customers across Kenya. Set up your store in minutes.</p>
      <div class="auth-features">
        <div class="auth-feature"><i class="fas fa-store"></i><span>Free store setup</span></div>
        <div class="auth-feature"><i class="fas fa-upload"></i><span>Upload products from your local drive</span></div>
        <div class="auth-feature"><i class="fas fa-chart-bar"></i><span>Powerful sales analytics</span></div>
        <div class="auth-feature"><i class="fas fa-headset"></i><span>Dedicated vendor support</span></div>
      </div>
    </div>
  </div>

  <div class="auth-right">
    <div class="auth-card">
      <div class="auth-card-header">
        <div class="vendor-auth-badge"><i class="fas fa-store"></i> Vendor Registration</div>
        <h1>Open Your Store</h1>
        <p>Already a vendor? <a href="login.php">Sign in to your account</a></p>
      </div>

      <div id="alertBox"></div>

      <form id="vendorRegForm" enctype="multipart/form-data">
        <input type="hidden" name="action" value="register">

        <div class="form-row">
          <div class="form-group">
            <label class="form-label"><i class="fas fa-building"></i> Company/Store Name *</label>
            <input type="text" name="company_name" class="form-control" placeholder="Your Business Name" required autofocus>
          </div>
          <div class="form-group">
            <label class="form-label"><i class="fas fa-phone"></i> Business Phone *</label>
            <input type="tel" name="phone" class="form-control" placeholder="+254 7XX XXX XXX" required>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label"><i class="fas fa-envelope"></i> Business Email *</label>
          <input type="email" name="email" class="form-control" placeholder="business@company.com" required>
        </div>

        <div class="form-group">
          <label class="form-label"><i class="fas fa-map-marker-alt"></i> Business Address</label>
          <input type="text" name="address" class="form-control" placeholder="Nairobi, Kenya">
        </div>

        <div class="form-group">
          <label class="form-label"><i class="fas fa-align-left"></i> Business Description</label>
          <textarea name="description" class="form-control" rows="3" placeholder="Tell customers about your business..."></textarea>
        </div>

        <div class="form-group">
          <label class="form-label"><i class="fas fa-image"></i> Store Logo (optional)</label>
          <div class="file-upload-wrap">
            <input type="file" name="logo" id="logoInput" accept="image/*" onchange="previewLogo(this)">
            <label for="logoInput" class="file-upload-label">
              <i class="fas fa-cloud-upload-alt"></i>
              <span>Click to upload logo</span>
              <small>JPG, PNG, WEBP — Max 5MB</small>
            </label>
            <img id="logoPreview" class="logo-preview" style="display:none;">
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label class="form-label"><i class="fas fa-lock"></i> Password *</label>
            <div class="password-wrap">
              <input type="password" name="password" id="passwordInput" class="form-control" placeholder="Min. 6 characters" required>
              <button type="button" class="toggle-password" onclick="togglePassword()">
                <i class="fas fa-eye" id="eyeIcon"></i>
              </button>
            </div>
          </div>
          <div class="form-group">
            <label class="form-label"><i class="fas fa-lock"></i> Confirm Password *</label>
            <input type="password" id="confirmPassword" class="form-control" placeholder="Repeat password" required>
          </div>
        </div>

        <div class="form-group form-check">
          <label>
            <input type="checkbox" required> I agree to Sellora's <a href="vendor-terms.php">Vendor Terms</a> and <a href="vendor-privacy.php">Policies</a>
          </label>
        </div>

        <button type="submit" class="btn btn-primary btn-block btn-lg" id="registerBtn">
          <i class="fas fa-store"></i> Create Vendor Account
        </button>
      </form>
    </div>
  </div>
</div>

<div id="toast-container"></div>

<script>
function togglePassword() {
  const input = document.getElementById('passwordInput');
  const icon = document.getElementById('eyeIcon');
  input.type = input.type === 'password' ? 'text' : 'password';
  icon.className = input.type === 'password' ? 'fas fa-eye' : 'fas fa-eye-slash';
}

function previewLogo(input) {
  if (input.files && input.files[0]) {
    const reader = new FileReader();
    reader.onload = e => {
      const preview = document.getElementById('logoPreview');
      preview.src = e.target.result;
      preview.style.display = 'block';
    };
    reader.readAsDataURL(input.files[0]);
  }
}

document.getElementById('vendorRegForm').addEventListener('submit', async function(e) {
  e.preventDefault();
  const password = document.getElementById('passwordInput').value;
  const confirm = document.getElementById('confirmPassword').value;
  const alertBox = document.getElementById('alertBox');

  if (password !== confirm) {
    alertBox.innerHTML = `<div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> Passwords do not match!</div>`;
    return;
  }

  const btn = document.getElementById('registerBtn');
  btn.disabled = true;
  btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating your store...';

  const formData = new FormData(this);
  const res = await fetch('../api/vendor_auth.php', { method: 'POST', body: formData });
  const data = await res.json();

  if (data.success) {
    alertBox.innerHTML = `<div class="alert alert-success"><i class="fas fa-check-circle"></i> ${data.message}</div>`;
    setTimeout(() => window.location.href = data.redirect, 800);
  } else {
    alertBox.innerHTML = `<div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> ${data.message}</div>`;
    btn.disabled = false;
    btn.innerHTML = '<i class="fas fa-store"></i> Create Vendor Account';
  }
});
</script>
</body>
</html>
