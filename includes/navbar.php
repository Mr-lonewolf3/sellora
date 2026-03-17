<?php
if (!defined('INCLUDED')) { require_once __DIR__ . '/../config/config.php'; }
$conn = getDBConnection();
$categories = getCategories($conn);
$cart_count = getCartCount($conn);
$current_category = $_GET['category'] ?? '';
?>
<nav class="navbar">
  <div class="navbar-top">
    <div class="container">
      <span><i class="fas fa-phone"></i> +254 700 000 000 &nbsp;|&nbsp; <i class="fas fa-envelope"></i> support@sellora.co.ke</span>
      <span>
        <?php if (isLoggedIn()): ?>
          Welcome, <?= htmlspecialchars($_SESSION['user_name']) ?>! &nbsp;|&nbsp;
          <a href="../api/user_auth.php?action=logout" style="color:#fff;">Logout</a>
        <?php else: ?>
          <a href="login.php" style="color:#fff;">Sign In</a> &nbsp;|&nbsp;
          <a href="register.php" style="color:#fff;">Register</a>
        <?php endif; ?>
      </span>
    </div>
  </div>
  <div class="navbar-main">
    <div class="container">
      <a href="../index.php" class="navbar-brand">Sell<span>ora</span></a>
      <div class="navbar-search">
        <input type="text" id="searchInput" placeholder="Search products, brands, vendors..." value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
        <select id="searchCategory">
          <option value="">All Categories</option>
          <?php foreach ($categories as $cat): ?>
          <option value="<?= $cat['slug'] ?>" <?= $current_category === $cat['slug'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($cat['name']) ?>
          </option>
          <?php endforeach; ?>
        </select>
        <button onclick="performSearch()"><i class="fas fa-search"></i></button>
      </div>
      <div class="navbar-actions">
        <?php if (isLoggedIn()): ?>
        <a href="account.php" class="nav-action-btn">
          <i class="fas fa-user"></i>
          <span>Account</span>
        </a>
        <?php else: ?>
        <a href="login.php" class="nav-action-btn">
          <i class="fas fa-user"></i>
          <span>Sign In</span>
        </a>
        <?php endif; ?>
        <a href="cart.php" class="nav-action-btn">
          <i class="fas fa-shopping-cart"></i>
          <span>Cart</span>
          <span class="cart-badge" id="cartBadge"><?= $cart_count ?></span>
        </a>
      </div>
    </div>
  </div>
  <div class="navbar-bottom">
    <div class="container">
      <a href="home.php" class="nav-cat-link <?= empty($current_category) ? 'active' : '' ?>">All</a>
      <?php foreach ($categories as $cat): ?>
      <a href="home.php?category=<?= $cat['slug'] ?>" class="nav-cat-link <?= $current_category === $cat['slug'] ? 'active' : '' ?>">
        <i class="<?= $cat['icon'] ?>"></i> <?= htmlspecialchars($cat['name']) ?>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</nav>
<div id="toast-container"></div>

<script>
function performSearch() {
  const q = document.getElementById('searchInput').value.trim();
  const cat = document.getElementById('searchCategory').value;
  let url = 'home.php?';
  if (q) url += 'q=' + encodeURIComponent(q) + '&';
  if (cat) url += 'category=' + encodeURIComponent(cat);
  window.location.href = url;
}

document.getElementById('searchInput').addEventListener('keypress', function(e) {
  if (e.key === 'Enter') performSearch();
});

function showToast(message, type = 'success') {
  const container = document.getElementById('toast-container');
  const toast = document.createElement('div');
  toast.className = `toast ${type}`;
  const icon = type === 'success' ? 'check-circle' : type === 'error' ? 'times-circle' : 'info-circle';
  toast.innerHTML = `<i class="fas fa-${icon}"></i> ${message}`;
  container.appendChild(toast);
  setTimeout(() => { toast.style.opacity = '0'; setTimeout(() => toast.remove(), 300); }, 3000);
}

function updateCartBadge(count) {
  const badge = document.getElementById('cartBadge');
  if (badge) badge.textContent = count;
}

function addToCart(productId, qty = 1) {
  fetch('../api/cart.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `action=add&product_id=${productId}&quantity=${qty}`
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      showToast(data.message, 'success');
      updateCartBadge(data.cart_count);
    } else {
      showToast(data.message, 'error');
    }
  });
}
</script>
