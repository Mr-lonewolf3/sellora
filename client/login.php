<?php
define('INCLUDED', true);
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';
if (isLoggedIn()) redirect('home.php');
$redirect = sanitize($_GET['redirect'] ?? 'home.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sign In - Sellora</title>
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="css/auth.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body class="auth-body">

<div class="auth-wrapper">
  <div class="auth-left">
    <div class="auth-left-content">
      <a href="../index.php" class="auth-logo">Sell<span>ora</span></a>
      <h2>Welcome back!</h2>
      <p>Sign in to your account to continue shopping and track your orders.</p>
      <div class="auth-features">
        <div class="auth-feature"><i class="fas fa-shopping-bag"></i><span>Shop from thousands of products</span></div>
        <div class="auth-feature"><i class="fas fa-truck"></i><span>Fast delivery across Kenya</span></div>
        <div class="auth-feature"><i class="fas fa-shield-alt"></i><span>Secure & encrypted transactions</span></div>
      </div>
    </div>
  </div>

  <div class="auth-right">
    <div class="auth-card">
      <div class="auth-card-header">
        <h1>Sign In</h1>
        <p>New to Sellora? <a href="register.php">Create a free account</a></p>
      </div>

      <div id="alertBox"></div>

      <form id="loginForm" autocomplete="off">
        <input type="hidden" name="action" value="login">
        <input type="hidden" name="redirect" value="<?= htmlspecialchars($redirect) ?>">

        <div class="form-group">
          <label class="form-label"><i class="fas fa-envelope"></i> Email Address</label>
          <input type="email" name="email" class="form-control" placeholder="your@email.com" required autofocus>
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

        <div class="form-group form-check">
          <label><input type="checkbox" name="remember"> Remember me</label>
          <a href="#" class="forgot-link">Forgot password?</a>
        </div>

        <button type="submit" class="btn btn-primary btn-block btn-lg" id="loginBtn">
          <i class="fas fa-sign-in-alt"></i> Sign In
        </button>
      </form>

      <div class="auth-divider"><span>or</span></div>

      <div class="auth-alt-actions">
        <p>Are you a vendor? <a href="../vendor/login.php"><i class="fas fa-store"></i> Vendor Login</a></p>
      </div>

      <div class="auth-demo">
        <p><strong>Demo Credentials:</strong></p>
        <p>Email: user@sellora.com | Password: password</p>
      </div>
    </div>
  </div>
</div>

<div id="toast-container"></div>

<script>
function togglePassword() {
  const input = document.getElementById('passwordInput');
  const icon = document.getElementById('eyeIcon');
  if (input.type === 'password') {
    input.type = 'text';
    icon.className = 'fas fa-eye-slash';
  } else {
    input.type = 'password';
    icon.className = 'fas fa-eye';
  }
}

document.getElementById('loginForm').addEventListener('submit', async function(e) {
  e.preventDefault();
  const btn = document.getElementById('loginBtn');
  btn.disabled = true;
  btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Signing in...';

  const formData = new FormData(this);
  try {
    const res = await fetch('../api/user_auth.php', { method: 'POST', body: formData });
    const data = await res.json();
    const alertBox = document.getElementById('alertBox');

    if (data.success) {
      alertBox.innerHTML = `<div class="alert alert-success"><i class="fas fa-check-circle"></i> ${data.message}</div>`;
      setTimeout(() => window.location.href = data.redirect, 800);
    } else {
      alertBox.innerHTML = `<div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> ${data.message}</div>`;
      btn.disabled = false;
      btn.innerHTML = '<i class="fas fa-sign-in-alt"></i> Sign In';
    }
  } catch (err) {
    document.getElementById('alertBox').innerHTML = `<div class="alert alert-danger">Connection error. Please try again.</div>`;
    btn.disabled = false;
    btn.innerHTML = '<i class="fas fa-sign-in-alt"></i> Sign In';
  }
});

function showToast(msg, type) {
  const c = document.getElementById('toast-container');
  const t = document.createElement('div');
  t.className = `toast ${type}`;
  t.innerHTML = msg;
  c.appendChild(t);
  setTimeout(() => t.remove(), 3000);
}
</script>
</body>
</html>
