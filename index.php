<?php
require_once __DIR__ . '/../config/config.php';
$conn = getDBConnection();
$categories = getCategories($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sellora - Kenya's Premier Online Marketplace</title>
  <link rel="stylesheet" href="client/css/landing.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
</head>
<body>

<!-- ============================================================
     NAVIGATION
     ============================================================ -->
<nav class="land-nav">
  <div class="container">
    <a href="index.php" class="land-logo">Sell<span>ora</span></a>
    <div class="land-nav-links">
      <a href="client/home.php">Shop</a>
      <a href="#features">Features</a>
      <a href="#categories">Categories</a>
      <a href="#vendor-cta">Sell on Sellora</a>
    </div>
    <div class="land-nav-actions">
      <a href="client/login.php" class="btn-nav-outline">Sign In</a>
      <a href="client/register.php" class="btn-nav-solid">Get Started</a>
    </div>
    <button class="land-menu-toggle" onclick="toggleMobileMenu()">
      <i class="fas fa-bars"></i>
    </button>
  </div>
  <div class="land-mobile-menu" id="mobileMenu">
    <a href="client/home.php">Shop</a>
    <a href="#features">Features</a>
    <a href="#categories">Categories</a>
    <a href="#vendor-cta">Sell on Sellora</a>
    <a href="client/login.php">Sign In</a>
    <a href="client/register.php" class="btn-nav-solid">Get Started Free</a>
  </div>
</nav>

<!-- ============================================================
     HERO SECTION
     ============================================================ -->
<section class="hero">
  <div class="hero-bg">
    <div class="hero-shape hero-shape-1"></div>
    <div class="hero-shape hero-shape-2"></div>
    <div class="hero-shape hero-shape-3"></div>
  </div>
  <div class="container">
    <div class="hero-content">
      <div class="hero-text">
        <div class="hero-badge">
          <i class="fas fa-bolt"></i> Kenya's #1 Marketplace
        </div>
        <h1>Shop Smart.<br><span>Sell More.</span><br>Grow Together.</h1>
        <p>Discover thousands of products from verified vendors across Kenya. From electronics to fashion, everything you need is just a click away.</p>
        <div class="hero-actions">
          <a href="client/home.php" class="btn-hero-primary">
            <i class="fas fa-shopping-bag"></i> Start Shopping
          </a>
          <a href="vendor/register.php" class="btn-hero-secondary">
            <i class="fas fa-store"></i> Become a Vendor
          </a>
        </div>
        <div class="hero-stats">
          <div class="hero-stat">
            <strong>50K+</strong>
            <span>Products</span>
          </div>
          <div class="hero-stat-divider"></div>
          <div class="hero-stat">
            <strong>2K+</strong>
            <span>Vendors</span>
          </div>
          <div class="hero-stat-divider"></div>
          <div class="hero-stat">
            <strong>100K+</strong>
            <span>Customers</span>
          </div>
        </div>
      </div>
      <div class="hero-visual">
        <div class="hero-phone">
          <div class="phone-screen">
            <div class="phone-header">
              <span class="phone-logo">Sell<span>ora</span></span>
              <i class="fas fa-shopping-cart"></i>
            </div>
            <div class="phone-banner">
              <div class="phone-banner-text">
                <small>Flash Sale</small>
                <strong>Up to 60% OFF</strong>
              </div>
              <i class="fas fa-tag phone-banner-icon"></i>
            </div>
            <div class="phone-products">
              <div class="phone-product">
                <div class="phone-product-img"><i class="fas fa-laptop"></i></div>
                <div class="phone-product-info">
                  <span>Laptop Pro</span>
                  <strong>KSh 45,000</strong>
                </div>
              </div>
              <div class="phone-product">
                <div class="phone-product-img fashion"><i class="fas fa-tshirt"></i></div>
                <div class="phone-product-info">
                  <span>Casual Wear</span>
                  <strong>KSh 1,200</strong>
                </div>
              </div>
              <div class="phone-product">
                <div class="phone-product-img beauty"><i class="fas fa-spa"></i></div>
                <div class="phone-product-info">
                  <span>Skincare Kit</span>
                  <strong>KSh 2,500</strong>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="floating-card card-1">
          <i class="fas fa-shield-alt"></i>
          <span>Secure Payments</span>
        </div>
        <div class="floating-card card-2">
          <i class="fas fa-truck"></i>
          <span>Fast Delivery</span>
        </div>
        <div class="floating-card card-3">
          <i class="fas fa-star"></i>
          <span>Top Rated</span>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ============================================================
     TRUST BADGES
     ============================================================ -->
<section class="trust-bar">
  <div class="container">
    <div class="trust-grid">
      <div class="trust-item">
        <i class="fas fa-shield-alt"></i>
        <div>
          <strong>100% Secure</strong>
          <span>Encrypted transactions</span>
        </div>
      </div>
      <div class="trust-item">
        <i class="fas fa-truck-fast"></i>
        <div>
          <strong>Fast Delivery</strong>
          <span>Across Kenya</span>
        </div>
      </div>
      <div class="trust-item">
        <i class="fas fa-rotate-left"></i>
        <div>
          <strong>Easy Returns</strong>
          <span>30-day return policy</span>
        </div>
      </div>
      <div class="trust-item">
        <i class="fas fa-headset"></i>
        <div>
          <strong>24/7 Support</strong>
          <span>Always here to help</span>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ============================================================
     CATEGORIES
     ============================================================ -->
<section class="land-section" id="categories">
  <div class="container">
    <div class="land-section-header">
      <h2>Shop by Category</h2>
      <p>Find exactly what you're looking for across our wide range of product categories</p>
    </div>
    <div class="land-categories-grid">
      <?php foreach ($categories as $cat): ?>
      <a href="client/home.php?category=<?= $cat['slug'] ?>" class="land-cat-card">
        <div class="land-cat-icon">
          <i class="<?= $cat['icon'] ?>"></i>
        </div>
        <span><?= htmlspecialchars($cat['name']) ?></span>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ============================================================
     FEATURES
     ============================================================ -->
<section class="land-section features-section" id="features">
  <div class="container">
    <div class="land-section-header">
      <h2>Why Choose Sellora?</h2>
      <p>Built for Kenyan buyers and sellers — a platform that understands your needs</p>
    </div>
    <div class="features-grid">
      <div class="feature-card">
        <div class="feature-icon" style="background: #fff3e0;">
          <i class="fas fa-store" style="color: #ff6d00;"></i>
        </div>
        <h3>Multi-Vendor Marketplace</h3>
        <p>Shop from hundreds of verified vendors all in one place. Compare prices and find the best deals.</p>
      </div>
      <div class="feature-card">
        <div class="feature-icon" style="background: #e8f5e9;">
          <i class="fas fa-upload" style="color: #2e7d32;"></i>
        </div>
        <h3>Easy Product Upload</h3>
        <p>Vendors can upload products directly from their local drive with images, descriptions, and pricing.</p>
      </div>
      <div class="feature-card">
        <div class="feature-icon" style="background: #e3f2fd;">
          <i class="fas fa-chart-line" style="color: #0277bd;"></i>
        </div>
        <h3>Vendor Dashboard</h3>
        <p>Monitor your products, track sales, and manage your store with a powerful vendor dashboard.</p>
      </div>
      <div class="feature-card">
        <div class="feature-icon" style="background: #fce4ec;">
          <i class="fas fa-search" style="color: #c62828;"></i>
        </div>
        <h3>Smart Search & Filter</h3>
        <p>Find products instantly with our intelligent search and category filtering system.</p>
      </div>
      <div class="feature-card">
        <div class="feature-icon" style="background: #f3e5f5;">
          <i class="fas fa-shopping-cart" style="color: #6a1b9a;"></i>
        </div>
        <h3>Seamless Shopping Cart</h3>
        <p>Add products to cart, manage quantities, and checkout with ease — even without an account.</p>
      </div>
      <div class="feature-card">
        <div class="feature-icon" style="background: #e0f7fa;">
          <i class="fas fa-mobile-alt" style="color: #006064;"></i>
        </div>
        <h3>Mobile Friendly</h3>
        <p>Shop on the go with our fully responsive design that works perfectly on any device.</p>
      </div>
    </div>
  </div>
</section>

<!-- ============================================================
     CUSTOMER REGISTRATION CTA
     ============================================================ -->
<section class="cta-section" id="customer-cta">
  <div class="container">
    <div class="cta-card customer-cta">
      <div class="cta-content">
        <div class="cta-badge">For Shoppers</div>
        <h2>Join Thousands of Happy Shoppers</h2>
        <p>Create your free account today and enjoy exclusive deals, order tracking, and a personalized shopping experience.</p>
        <ul class="cta-benefits">
          <li><i class="fas fa-check-circle"></i> Free account — no credit card required</li>
          <li><i class="fas fa-check-circle"></i> Exclusive member discounts</li>
          <li><i class="fas fa-check-circle"></i> Order history and tracking</li>
          <li><i class="fas fa-check-circle"></i> Save your favourite products</li>
        </ul>
        <div class="cta-actions">
          <a href="client/register.php" class="btn-cta-primary">
            <i class="fas fa-user-plus"></i> Create Free Account
          </a>
          <a href="client/login.php" class="btn-cta-link">
            Already have an account? Sign in →
          </a>
        </div>
      </div>
      <div class="cta-visual">
        <div class="cta-illustration">
          <i class="fas fa-shopping-bag"></i>
          <div class="cta-circles">
            <div class="cta-circle c1"></div>
            <div class="cta-circle c2"></div>
            <div class="cta-circle c3"></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ============================================================
     VENDOR REGISTRATION CTA
     ============================================================ -->
<section class="cta-section" id="vendor-cta">
  <div class="container">
    <div class="cta-card vendor-cta">
      <div class="cta-visual">
        <div class="cta-illustration vendor-ill">
          <i class="fas fa-store"></i>
          <div class="cta-circles">
            <div class="cta-circle c1"></div>
            <div class="cta-circle c2"></div>
            <div class="cta-circle c3"></div>
          </div>
        </div>
      </div>
      <div class="cta-content">
        <div class="cta-badge vendor-badge">For Vendors</div>
        <h2>Start Selling on Sellora Today</h2>
        <p>Join our growing community of vendors and reach thousands of customers across Kenya. Set up your store in minutes.</p>
        <ul class="cta-benefits">
          <li><i class="fas fa-check-circle"></i> Easy product upload from your local drive</li>
          <li><i class="fas fa-check-circle"></i> Powerful vendor dashboard</li>
          <li><i class="fas fa-check-circle"></i> Real-time sales monitoring</li>
          <li><i class="fas fa-check-circle"></i> Manage your inventory with ease</li>
        </ul>
        <div class="cta-actions">
          <a href="vendor/register.php" class="btn-cta-primary vendor-btn">
            <i class="fas fa-store"></i> Open Your Store
          </a>
          <a href="vendor/login.php" class="btn-cta-link">
            Already a vendor? Sign in →
          </a>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ============================================================
     FOOTER
     ============================================================ -->
<footer class="land-footer">
  <div class="container">
    <div class="land-footer-grid">
      <div class="land-footer-brand">
        <div class="footer-logo">Sell<span>ora</span></div>
        <p>Kenya's premier online marketplace connecting buyers and sellers across the country.</p>
        <div class="footer-social">
          <a href="#"><i class="fab fa-facebook-f"></i></a>
          <a href="#"><i class="fab fa-twitter"></i></a>
          <a href="#"><i class="fab fa-instagram"></i></a>
          <a href="#"><i class="fab fa-whatsapp"></i></a>
        </div>
      </div>
      <div>
        <h4>Quick Links</h4>
        <ul>
          <li><a href="client/home.php">Shop Now</a></li>
          <li><a href="#categories">Categories</a></li>
          <li><a href="client/register.php">Register</a></li>
          <li><a href="client/login.php">Login</a></li>
        </ul>
      </div>
      <div>
        <h4>For Vendors</h4>
        <ul>
          <li><a href="vendor/register.php">Become a Vendor</a></li>
          <li><a href="vendor/login.php">Vendor Login</a></li>
          <li><a href="vendor/dashboard.php">Dashboard</a></li>
        </ul>
      </div>
      <div>
        <h4>Contact Us</h4>
        <ul>
          <li><a href="#"><i class="fas fa-envelope"></i> support@sellora.co.ke</a></li>
          <li><a href="#"><i class="fas fa-phone"></i> +254 700 000 000</a></li>
          <li><a href="#"><i class="fas fa-map-marker-alt"></i> Nairobi, Kenya</a></li>
        </ul>
      </div>
    </div>
    <div class="land-footer-bottom">
      <p>&copy; <?= date('Y') ?> Sellora. All rights reserved. | Built for Kenya 🇰🇪</p>
    </div>
  </div>
</footer>

<script>
function toggleMobileMenu() {
  const menu = document.getElementById('mobileMenu');
  menu.classList.toggle('open');
}

// Smooth scroll for anchor links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
  anchor.addEventListener('click', function(e) {
    e.preventDefault();
    const target = document.querySelector(this.getAttribute('href'));
    if (target) target.scrollIntoView({ behavior: 'smooth', block: 'start' });
  });
});

// Animate on scroll
const observer = new IntersectionObserver((entries) => {
  entries.forEach(entry => {
    if (entry.isIntersecting) {
      entry.target.classList.add('animate-in');
    }
  });
}, { threshold: 0.1 });

document.querySelectorAll('.feature-card, .land-cat-card, .trust-item').forEach(el => {
  observer.observe(el);
});
</script>
</body>
</html>
