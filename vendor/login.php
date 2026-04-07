<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';
if (isVendorLoggedIn()) redirect('dashboard.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Vendor Login - Sellora</title>
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
      <h2>Vendor Portal</h2>
      <p>Sign in to your vendor account to manage your products, monitor sales, and grow your business.</p>
      <div class="auth-features">
        <div class="auth-feature"><i class="fas fa-chart-line"></i><span>Real-time sales dashboard</span></div>
        <div class="auth-feature"><i class="fas fa-upload"></i><span>Easy product upload from local drive</span></div>
        <div class="auth-feature"><i class="fas fa-boxes"></i><span>Full inventory management</span></div>
        <div class="auth-feature"><i class="fas fa-users"></i><span>Reach thousands of customers</span></div>
      </div>
    </div>
  </div>

  <div class="auth-right">
    <div class="auth-card">
      <div class="auth-card-header">
        <div class="vendor-auth-badge"><i class="fas fa-store"></i> Vendor Account</div>
        <h1>Vendor Sign In</h1>
        <p>New vendor? <a href="register.php">Register your store</a></p>
      </div>

      <div id="alertBox"></div>

      <form id="vendorLoginForm">
        <input type="hidden" name="action" value="login">

        <div class="form-group">
          <label class="form-label"><i class="fas fa-envelope"></i> Business Email</label>
          <input type="email" name="email" class="form-control" placeholder="business@company.com" required autofocus>
        </div>

        <div class="form-group">
          <label class="form-label"><i class="fas fa-lock"></i> Password</label>
          <div class="password-wrap">
            <input type="password" name="password" id="passwordInput" class="form-control" placeholder="Enter your password" required>
            <button type="button" class="toggle-password" onclick="togglePassword()">
              <i class="fas fa-eye" id="eyeIcon"></i>
            </button>
          </div>
        </div>

        <button type="submit" class="btn btn-primary btn-block btn-lg" id="loginBtn">
          <i class="fas fa-sign-in-alt"></i> Sign In to Dashboard
        </button>
      </form>

      <div class="auth-divider"><span>or</span></div>

      <div class="auth-alt-actions">
        <p>Shopping on Sellora? <a href="../client/login.php"><i class="fas fa-shopping-bag"></i> Customer Login</a></p>
      </div>

      <div class="auth-demo">
        <p><strong>Demo Vendor:</strong> vendor@sellora.com | Password: password</p>
      </div>
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

document.getElementById('vendorLoginForm').addEventListener('submit', async function(e) {
  e.preventDefault();
  const btn = document.getElementById('loginBtn');
  btn.disabled = true;
  btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Signing in...';

  const formData = new FormData(this);
  const res = await fetch('../api/vendor_auth.php', { method: 'POST', body: formData });
  const data = await res.json();
  const alertBox = document.getElementById('alertBox');

  if (data.success) {
    alertBox.innerHTML = `<div class="alert alert-success"><i class="fas fa-check-circle"></i> ${data.message}</div>`;
    setTimeout(() => window.location.href = data.redirect, 800);
  } else {
    alertBox.innerHTML = `<div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> ${data.message}</div>`;
    btn.disabled = false;
    btn.innerHTML = '<i class="fas fa-sign-in-alt"></i> Sign In to Dashboard';
  }
});
</script>
</body>
</html>
