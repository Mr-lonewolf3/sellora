<?php
define('INCLUDED', true);
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';
if (isLoggedIn()) redirect('home.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Create Account - Sellora</title>
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="css/auth.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body class="auth-body">

<div class="auth-wrapper">
  <div class="auth-left">
    <div class="auth-left-content">
      <a href="../index.php" class="auth-logo">Sell<span>ora</span></a>
      <h2>Join Sellora Today!</h2>
      <p>Create your free account and start shopping from thousands of products across Kenya.</p>
      <div class="auth-features">
        <div class="auth-feature"><i class="fas fa-user-check"></i><span>Free account, no credit card needed</span></div>
        <div class="auth-feature"><i class="fas fa-tag"></i><span>Exclusive member discounts</span></div>
        <div class="auth-feature"><i class="fas fa-history"></i><span>Order history & tracking</span></div>
        <div class="auth-feature"><i class="fas fa-heart"></i><span>Save your favourite products</span></div>
      </div>
    </div>
  </div>

  <div class="auth-right">
    <div class="auth-card">
      <div class="auth-card-header">
        <h1>Create Account</h1>
        <p>Already have an account? <a href="login.php">Sign in</a></p>
      </div>

      <div id="alertBox"></div>

      <form id="registerForm" autocomplete="off">
        <input type="hidden" name="action" value="register">

        <div class="form-row">
          <div class="form-group">
            <label class="form-label"><i class="fas fa-user"></i> Full Name *</label>
            <input type="text" name="full_name" class="form-control" placeholder="John Doe" required autofocus>
          </div>
          <div class="form-group">
            <label class="form-label"><i class="fas fa-phone"></i> Phone Number</label>
            <input type="tel" name="phone" class="form-control" placeholder="+254 7XX XXX XXX">
          </div>
        </div>

        <div class="form-group">
          <label class="form-label"><i class="fas fa-envelope"></i> Email Address *</label>
          <input type="email" name="email" class="form-control" placeholder="your@email.com" required>
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
            <input type="checkbox" required> I agree to the <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a>
          </label>
        </div>

        <button type="submit" class="btn btn-primary btn-block btn-lg" id="registerBtn">
          <i class="fas fa-user-plus"></i> Create Free Account
        </button>
      </form>

      <div class="auth-divider"><span>or</span></div>

      <div class="auth-alt-actions">
        <p>Want to sell on Sellora? <a href="../vendor/register.php"><i class="fas fa-store"></i> Register as Vendor</a></p>
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

document.getElementById('registerForm').addEventListener('submit', async function(e) {
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
  btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating account...';

  const formData = new FormData(this);
  try {
    const res = await fetch('../api/user_auth.php', { method: 'POST', body: formData });
    const data = await res.json();

    if (data.success) {
      alertBox.innerHTML = `<div class="alert alert-success"><i class="fas fa-check-circle"></i> ${data.message}</div>`;
      setTimeout(() => window.location.href = data.redirect, 800);
    } else {
      alertBox.innerHTML = `<div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> ${data.message}</div>`;
      btn.disabled = false;
      btn.innerHTML = '<i class="fas fa-user-plus"></i> Create Free Account';
    }
  } catch (err) {
    alertBox.innerHTML = `<div class="alert alert-danger">Connection error. Please try again.</div>`;
    btn.disabled = false;
    btn.innerHTML = '<i class="fas fa-user-plus"></i> Create Free Account';
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
