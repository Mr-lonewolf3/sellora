<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<aside class="vendor-sidebar" id="vendorSidebar">
  <div class="sidebar-brand">
    <div>
      <div class="sidebar-logo">Sell<span>ora</span></div>
    </div>
    <span class="sidebar-badge">Vendor</span>
  </div>

  <div class="sidebar-vendor-info">
    <div class="vendor-avatar">
      <?= strtoupper(substr($_SESSION['vendor_name'] ?? 'V', 0, 1)) ?>
    </div>
    <div class="vendor-avatar-info">
      <strong><?= htmlspecialchars($_SESSION['vendor_name'] ?? 'Vendor') ?></strong>
      <span><?= htmlspecialchars($_SESSION['vendor_email'] ?? '') ?></span>
    </div>
  </div>

  <nav class="sidebar-nav">
    <div class="sidebar-nav-label">Main</div>
    <a href="dashboard.php" class="sidebar-nav-link <?= $current_page === 'dashboard.php' ? 'active' : '' ?>">
      <i class="fas fa-tachometer-alt"></i> Dashboard
    </a>
    <a href="products.php" class="sidebar-nav-link <?= in_array($current_page, ['products.php', 'add-product.php', 'edit-product.php']) ? 'active' : '' ?>">
      <i class="fas fa-box"></i> My Products
    </a>
    <a href="add-product.php" class="sidebar-nav-link <?= $current_page === 'add-product.php' ? 'active' : '' ?>">
      <i class="fas fa-plus-circle"></i> Add Product
    </a>
    <a href="orders.php" class="sidebar-nav-link <?= $current_page === 'orders.php' ? 'active' : '' ?>">
      <i class="fas fa-receipt"></i> Orders
    </a>

    <div class="sidebar-nav-label">Account</div>
    <a href="profile.php" class="sidebar-nav-link <?= $current_page === 'profile.php' ? 'active' : '' ?>">
      <i class="fas fa-user-cog"></i> Profile Settings
    </a>
    <a href="../client/home.php" target="_blank" class="sidebar-nav-link">
      <i class="fas fa-store"></i> View Storefront
    </a>
  </nav>

  <div class="sidebar-footer">
    <a href="../api/vendor_auth.php?action=logout" onclick="return confirm('Are you sure you want to logout?')">
      <i class="fas fa-sign-out-alt"></i> Logout
    </a>
  </div>
</aside>
