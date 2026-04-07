<?php
require_once(__DIR__ . '/config/config.php');
$conn = getDBConnection();
$categories = getCategories($conn);

$trending_query = "SELECT p.id, p.name, p.price, p.discount_price, p.main_image, p.category_id 
                  FROM products p 
                  WHERE p.is_active = 1 
                  ORDER BY p.views DESC LIMIT 8";
$trending_result = $conn->query($trending_query);
$trending_products = $trending_result->fetch_all(MYSQLI_ASSOC);
?>
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sellora - Kenya's Premier Online Marketplace</title>
  <link rel="stylesheet" href="client/css/landing.css">
  <link rel="stylesheet" href="client/css/landing-v2.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
</head>
<body>

<!-- ============================================================
     NAVIGATION (same as original)
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
     HERO BANNER (QuickStore-style full-width slider look)
     ============================================================ -->
<section class="qs-hero">
  <div class="qs-hero-inner">
    <div class="qs-hero-text">
      <p class="qs-hero-sub">Kenya's #1 Marketplace — <span>50,000+ Products</span></p>
      <h1>Shop Smart.<br><em>Sell More.</em><br>Grow Together.</h1>
      <p class="qs-hero-desc">Discover thousands of products from verified vendors across Kenya. Electronics, fashion, beauty, and more — delivered to your door.</p>
      <div class="qs-hero-price">
        <span>From</span> <strong>KSh 199</strong>
        <small>or enjoy M-Pesa easy payment</small>
      </div>
      <div class="qs-hero-btns">
        <a href="client/home.php" class="qs-btn-primary"><i class="fas fa-shopping-bag"></i> Start Shopping</a>
        <a href="vendor/register.php" class="qs-btn-ghost"><i class="fas fa-store"></i> Become a Vendor</a>
      </div>
    </div>
    <div class="qs-hero-image">
      <a href="client/home.php?category=electronics" class="qs-hero-img-link">
        <img id="dynamic-hero-img" 
            src="client/images/hero-phone.png" 
            alt="Featured Product" />
        
        <div class="qs-hero-img-placeholder">
          <i class="fas fa-mobile-alt"></i>
          <span>Featured Products</span>
        </div>
      </a>
    </div>
    <div class="qs-hero-dots">
      <span class="dot active"></span>
      <span class="dot"></span>
      <span class="dot"></span>
    </div>
  </div>
</section>

<!-- ============================================================
     TRUST BADGES BAR
     ============================================================ -->
<section class="qs-trust-bar">
  <div class="qs-container">
    <div class="qs-trust-grid">
      <div class="qs-trust-item">
        <i class="fas fa-shield-alt"></i>
        <div><strong>100% Secure</strong><span>Encrypted transactions</span></div>
      </div>
      <div class="qs-trust-item">
        <i class="fas fa-truck-fast"></i>
        <div><strong>Fast Delivery</strong><span>Across County</span></div>
      </div>
      <div class="qs-trust-item">
        <i class="fas fa-rotate-left"></i>
        <div><strong>Easy Returns</strong><span>10-day return policy</span></div>
      </div>
      <div class="qs-trust-item">
        <i class="fas fa-headset"></i>
        <div><strong>24/7 Support</strong><span>Always here to help</span></div>
      </div>
      <div class="qs-trust-item">
        <i class="fas fa-tags"></i>
        <div><strong>Best Prices</strong><span>Guaranteed deals</span></div>
      </div>
    </div>
  </div>
</section>

<!-- ============================================================
     TOP CATEGORIES (QuickStore-style icon row)
     ============================================================ -->
<section class="qs-section" id="categories">
  <div class="qs-container">
    <div class="qs-section-header">
      <h2>Our Top Categories</h2>
      <a href="client/home.php" class="qs-view-all">View All <i class="fas fa-arrow-right"></i></a>
    </div>
    <div class="qs-categories-row">
      <?php if (!empty($categories)): ?>
        <?php foreach ($categories as $cat): ?>
        <a href="client/home.php?category=<?= $cat['slug'] ?>" class="qs-cat-pill">
          <div class="qs-cat-icon-wrap">
            <i class="<?= $cat['icon'] ?>"></i>
          </div>
          <span><?= htmlspecialchars($cat['name']) ?></span>
          <small><?= rand(2, 20) ?> items</small>
        </a>
        <?php endforeach; ?>
      <?php else: ?>
        <!-- Fallback static categories if DB has no data yet -->
        <a href="client/home.php?category=electronics" class="qs-cat-pill">
          <div class="qs-cat-icon-wrap"><i class="fas fa-tv"></i></div>
          <span>Electronics</span><small>9 items</small>
        </a>
        <a href="client/home.php?category=speaker" class="qs-cat-pill">
          <div class="qs-cat-icon-wrap"><i class="fas fa-volume-up"></i></div>
          <span>Speaker</span><small>3 items</small>
        </a>
        <a href="client/home.php?category=tablets" class="qs-cat-pill">
          <div class="qs-cat-icon-wrap"><i class="fas fa-tablet-alt"></i></div>
          <span>Tablets</span><small>4 items</small>
        </a>
        <a href="client/home.php?category=fashion" class="qs-cat-pill">
          <div class="qs-cat-icon-wrap"><i class="fas fa-tshirt"></i></div>
          <span>Fashion</span><small>12 items</small>
        </a>
        <a href="client/home.php?category=beauty" class="qs-cat-pill">
          <div class="qs-cat-icon-wrap"><i class="fas fa-spa"></i></div>
          <span>Beauty</span><small>7 items</small>
        </a>
        <a href="client/home.php?category=phones" class="qs-cat-pill">
          <div class="qs-cat-icon-wrap"><i class="fas fa-mobile-alt"></i></div>
          <span>Phones</span><small>10 items</small>
        </a>
        <a href="client/home.php?category=headphones" class="qs-cat-pill">
          <div class="qs-cat-icon-wrap"><i class="fas fa-headphones"></i></div>
          <span>Headphones</span><small>2 items</small>
        </a>
        <a href="client/home.php?category=laptops" class="qs-cat-pill">
          <div class="qs-cat-icon-wrap"><i class="fas fa-laptop"></i></div>
          <span>Laptops</span><small>6 items</small>
        </a>
        <a href="client/home.php?category=accessories" class="qs-cat-pill">
          <div class="qs-cat-icon-wrap"><i class="fas fa-plug"></i></div>
          <span>Accessories</span><small>5 items</small>
        </a>
      <?php endif; ?>
    </div>
  </div>
</section>

<!-- ============================================================
     PROMO BANNER GRID (3 featured products)
     ============================================================ -->
<section class="qs-promo-section">
  <div class="qs-container">
    <div class="qs-promo-grid">

      <!-- Promo Card 1 -->
      <a href="client/home.php?category=phones" class="qs-promo-card qs-promo-blue">
        <div class="qs-promo-text">
          <small>BIG SAVING</small>
          <h3>Galaxy S17 Refurbished<br><em>Love The Price.</em></h3>
          <p>From <strong>KSh 22,999</strong></p>
          <span class="qs-promo-btn">Buy Now</span>
        </div>
        <div class="qs-promo-img">
          <img src="client/images/promo-phone.png"
               onerror="this.style.display='none'; this.nextElementSibling.style.display='block';"
               alt="Galaxy S13 Lite" style="scale: 1.7;" />
          <i class="fas fa-mobile-alt qs-promo-fallback-icon" style="display:none;"></i>
        </div>
      </a>

      <!-- Promo Card 2 -->
      <a href="client/home.php?category=smartwatch" class="qs-promo-card qs-promo-pink">
        <div class="qs-promo-text">
          <small>15% OFF</small>
          <h3>Women's T-Shirt<br><em>Light On Price.</em></h3>
          <p>From <strong>KSh 149</strong></p>
          <span class="qs-promo-btn outline">Learn More</span>
        </div>
        <div class="qs-promo-img">
          <img src="client/images/promo-tshirt.png"
               onerror="this.style.display='none'; this.nextElementSibling.style.display='block';"
               alt="Women's T-shirt" style="scale: 1.4;"  />
          <i class="fas fa-clock qs-promo-fallback-icon" style="display:none;"></i>
        </div>
      </a>

      <!-- Promo Card 3 -->
      <a href="client/home.php?category=smarthome" class="qs-promo-card qs-promo-lavender">
        <div class="qs-promo-text">
          <small>Fruit Offer</small>
          <h3>Pinapple<br><em>KSh 69 Each.</em></h3>
          <p>From <strong>KSh 110</strong></p>
          <span class="qs-promo-btn">Buy Now</span>
        </div>
        <div class="qs-promo-img">
          <img src="client/images/promo-fruit.png"
               onerror="this.style.display='none'; this.nextElementSibling.style.display='block';"
               alt="Smart Speaker" style="scale: 1.6;" />
          <i class="fas fa-home qs-promo-fallback-icon" style="display:none;"></i>
        </div>
      </a>

      <!-- Promo Card 4 -->
      <a href="client/home.php?category=airpods" class="qs-promo-card qs-promo-white">
        <div class="qs-promo-text">
          <small>BEST PRICE</small>
          <h3>Sneakers<br><em>J4's</em></h3>
          <p>From <strong>KSh 2499</strong></p>
          <span class="qs-promo-btn ghost">Learn More</span>
        </div>
        <div class="qs-promo-img">
          <img src="client/images/promo-shoes.png"
               onerror="this.style.display='none'; this.nextElementSibling.style.display='block';"
               alt="AirPods" style="scale: 1.8;" />
          <i class="fas fa-headphones qs-promo-fallback-icon" style="display:none;"></i>
        </div>
      </a>

      <!-- Promo Card 5 -->
      <a href="client/home.php?category=headphones" class="qs-promo-card qs-promo-teal">
        <div class="qs-promo-text">
          <small>FLAT 25% OFF</small>
          <h3>Apple Smartwatch 4th<br><em>Generation.</em></h3>
          <p>From <strong>KSh 8,999</strong></p>
          <span class="qs-promo-btn">Buy Now</span>
        </div>
        <div class="qs-promo-img">
          <img src="client/images/promo-watch.png"
               onerror="this.style.display='none'; this.nextElementSibling.style.display='block';"
               alt="Apple Smartwatch" style="scale: 1.5;" />
          <i class="fas fa-headphones-alt qs-promo-fallback-icon" style="display:none;"></i>
        </div>
      </a>

      <!-- Promo Card 6 -->
      <a href="client/home.php?category=laptops" class="qs-promo-card qs-promo-lilac">
        <div class="qs-promo-text">
          <small>NEWLY ADDED</small>
          <h3>Mac Book Pro.<br><em>New Arrival.</em></h3>
          <p>From <strong>KSh 149,900</strong></p>
          <span class="qs-promo-btn ghost">Learn More</span>
        </div>
        <div class="qs-promo-img">
          <img src="client/images/promo-macbook.jpg"
               onerror="this.style.display='none'; this.nextElementSibling.style.display='block';"
               alt="MacBook Pro" />
          <i class="fas fa-laptop qs-promo-fallback-icon" style="display:none;"></i>
        </div>
      </a>

    </div>
  </div>
</section>

<!-- ============================================================
     TRENDING PRODUCTS (tab-style section)
     ============================================================ -->
<section class="qs-section" id="trending">
  <div class="qs-container">
    <div class="qs-trending-header">
      <h2>Our Trending Products</h2>
      <div class="qs-tabs">
        <button class="qs-tab active" onclick="switchTab(this,'new')">New Products</button>
        <button class="qs-tab" onclick="switchTab(this,'bestselling')">Best Selling</button>
        <button class="qs-tab" onclick="switchTab(this,'featured')">Featured</button>
      </div>
      <div class="qs-tab-arrows">
        <button class="qs-arrow" onclick="scrollProducts(-1)"><i class="fas fa-chevron-left"></i></button>
        <button class="qs-arrow" onclick="scrollProducts(1)"><i class="fas fa-chevron-right"></i></button>
      </div>
    </div>
    <div class="qs-products-row" id="productsRow">
      <!-- Product Card Template (replace with PHP loop from your products table) -->
      <div class="qs-products-row" id="productsRow">
          <?php if (!empty($trending_products)): ?>
              <?php foreach ($trending_products as $tp): 
                  $tp_price = $tp['discount_price'] ?? $tp['price'];
                  $has_tp_discount = !empty($tp['discount_price']) && $tp['discount_price'] < $tp['price'];
                  
                  // FIX: If UPLOAD_URL is a full http:// link, use it directly.
                  // If it is a relative path like 'uploads/products/', use 'client/' . UPLOAD_URL
                  $base_url = (strpos(UPLOAD_URL, 'http') === 0) ? UPLOAD_URL : 'client/' . UPLOAD_URL;
                  $tp_img = !empty($tp['main_image']) ? $base_url . $tp['main_image'] : 'https://via.placeholder.com/300';
              ?>
                  <a href="client/product.php?id=<?= $tp['id'] ?>" class="qs-product-card">
                      <?php if ($has_tp_discount): ?>
                          <div class="qs-product-badge sale">SALE</div>
                      <?php endif; ?>
                      
                      <div class="qs-product-img">
                          <img src="<?= $tp_img ?>" alt="<?= htmlspecialchars($tp['name']) ?>" 
                              onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                          <div class="qs-product-img-ph"><i class="fas fa-shopping-bag"></i></div>
                      </div>

                      <div class="qs-product-info">
                          <p class="qs-product-name"><?= htmlspecialchars(substr($tp['name'], 0, 25)) ?>...</p>
                          <div class="qs-product-stars">
                              <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                          </div>
                          <div class="qs-product-price">
                              <strong>KSh <?= number_format($tp_price) ?></strong>
                              <?php if ($has_tp_discount): ?>
                                  <del>KSh <?= number_format($tp['price']) ?></del>
                              <?php endif; ?>
                          </div>
                      </div>
                  </a>
              <?php endforeach; ?>
          <?php else: ?>
              <p>No products trending yet. Keep shopping!</p>
          <?php endif; ?>
    </div>
    <div class="qs-view-all-wrap">
      <a href="client/home.php" class="qs-btn-outline-red">View All Products <i class="fas fa-arrow-right"></i></a>
    </div>
  </div>
</section>

<!-- ============================================================
     STATS BAR
     ============================================================ -->
<section class="qs-stats-bar">
  <div class="qs-container">
    <div class="qs-stats-grid">
      <div class="qs-stat">
        <strong>50K+</strong>
        <span>Products Listed</span>
      </div>
      <div class="qs-stat-divider"></div>
      <div class="qs-stat">
        <strong>2K+</strong>
        <span>Verified Vendors</span>
      </div>
      <div class="qs-stat-divider"></div>
      <div class="qs-stat">
        <strong>100K+</strong>
        <span>Happy Customers</span>
      </div>
      <div class="qs-stat-divider"></div>
      <div class="qs-stat">
        <strong>47</strong>
        <span>Counties Served</span>
      </div>
    </div>
  </div>
</section>

<!-- ============================================================
     WHY CHOOSE SELLORA (Features)
     ============================================================ -->
<section class="qs-section qs-features-bg" id="features">
  <div class="qs-container">
    <div class="qs-section-center-header">
      <h2>Why Choose Sellora?</h2>
      <p>Built for Kenyan buyers and sellers — a platform that understands your needs</p>
    </div>
    <div class="qs-features-grid">
      <div class="feature-card">
        <div class="feature-icon" style="background:#fff3e0;">
          <i class="fas fa-store" style="color:#ff6d00;"></i>
        </div>
        <h3>Multi-Vendor Marketplace</h3>
        <p>Shop from hundreds of verified vendors all in one place. Compare prices and find the best deals.</p>
      </div>
      <div class="feature-card">
        <div class="feature-icon" style="background:#e8f5e9;">
          <i class="fas fa-upload" style="color:#2e7d32;"></i>
        </div>
        <h3>Easy Product Upload</h3>
        <p>Vendors can upload products directly from their local drive with images, descriptions, and pricing.</p>
      </div>
      <div class="feature-card">
        <div class="feature-icon" style="background:#e3f2fd;">
          <i class="fas fa-chart-line" style="color:#0277bd;"></i>
        </div>
        <h3>Vendor Dashboard</h3>
        <p>Monitor your products, track sales, and manage your store with a powerful vendor dashboard.</p>
      </div>
      <div class="feature-card">
        <div class="feature-icon" style="background:#fce4ec;">
          <i class="fas fa-search" style="color:#c62828;"></i>
        </div>
        <h3>Smart Search & Filter</h3>
        <p>Find products instantly with our intelligent search and category filtering system.</p>
      </div>
      <div class="feature-card">
        <div class="feature-icon" style="background:#f3e5f5;">
          <i class="fas fa-shopping-cart" style="color:#6a1b9a;"></i>
        </div>
        <h3>Seamless Shopping Cart</h3>
        <p>Add products to cart, manage quantities, and checkout with ease — even without an account.</p>
      </div>
      <div class="feature-card">
        <div class="feature-icon" style="background:#e0f7fa;">
          <i class="fas fa-mobile-alt" style="color:#006064;"></i>
        </div>
        <h3>Mobile Friendly</h3>
        <p>Shop on the go with our fully responsive design that works perfectly on any device.</p>
      </div>
    </div>
  </div>
</section>

<!-- ============================================================
     CUSTOMER REGISTRATION CTA (Join Thousands of Happy Shoppers)
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
          <img id="cta-sliding-img" 
              src="client/images/happy-shopper1.png" 
              alt="Sellora Shopper" 
              style="width: 100%; height: auto; z-index: 5; position: relative; transition: all 0.5s ease; scale: 1.8;">
          
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
     VENDOR REGISTRATION CTA (Start Selling on Sellora Today)
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
     FOOTER (same as original)
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
// 1. Standalone Utility Functions
function toggleMobileMenu() {
  const menu = document.getElementById('mobileMenu');
  if (menu) menu.classList.toggle('open');
}

function switchTab(btn, tab) {
  document.querySelectorAll('.qs-tab').forEach(t => t.classList.remove('active'));
  btn.classList.add('active');
}

function scrollProducts(dir) {
  const row = document.getElementById('productsRow');
  if (row) row.scrollBy({ left: dir * 280, behavior: 'smooth' });
}

// 2. Main Logic (Runs after DOM is ready)
document.addEventListener('DOMContentLoaded', function() {
    /* --- HERO SLIDER --- */
    const heroSection = document.querySelector('.qs-hero');
    const heroImage = document.getElementById('dynamic-hero-img');
    const heroDots = document.querySelectorAll('.qs-hero-dots .dot');

    const slides = [
        { image: 'client/images/hero-phone.png', bg: '#ec72e2' },
        { image: 'client/images/hero-phone2.png', bg: '#c24646' },
        { image: 'client/images/hero-electronic.png', bg: '#00c3ff' }
    ];

    let currentSlide = 0;

    function updateSlide(index) {
        if (!heroImage || !heroSection) return;
        heroImage.style.opacity = '0';
        heroImage.style.transform = 'translateX(20px)';

        setTimeout(() => {
            heroImage.src = slides[index].image;
            heroSection.style.backgroundColor = slides[index].bg;
            heroDots.forEach(dot => dot.classList.remove('active'));
            if(heroDots[index]) heroDots[index].classList.add('active');
            heroImage.style.opacity = '1';
            heroImage.style.transform = 'translateX(0)';
        }, 400); 
    }

    if (heroImage && slides.length > 0) {
        setInterval(() => {
            currentSlide = (currentSlide + 1) % slides.length;
            updateSlide(currentSlide);
        }, 5000);

        heroDots.forEach((dot, idx) => {
            dot.addEventListener('click', () => {
                currentSlide = idx;
                updateSlide(currentSlide);
            });
        });
    }

    /* --- SMOOTH SCROLL --- */
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    });

    /* --- ANIMATE ON SCROLL (OBSERVER) --- */
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate-in');
            }
        });
    }, { threshold: 0.1 });

    // This is the part that makes the categories visible
    const animElements = document.querySelectorAll('.feature-card, .land-cat-card, .trust-item, .qs-cat-pill, .qs-promo-card, .qs-product-card');
    animElements.forEach(el => observer.observe(el));
});
</script>
</body>
</html>
