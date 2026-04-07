<?php
define('INCLUDED', true);
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';
$conn = getDBConnection();

$product_id = (int)($_GET['id'] ?? 0);
if (!$product_id) { header('Location: home.php'); exit; }

$stmt = $conn->prepare("SELECT p.*, c.name as category_name, c.slug as category_slug,
    v.company_name, v.logo as vendor_logo, v.phone as vendor_phone, v.description as vendor_desc, v.id as vid
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    LEFT JOIN vendors v ON p.vendor_id = v.id
    WHERE p.id = ? AND p.is_active = 1");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) { header('Location: home.php'); exit; }

// Increment views
$conn->query("UPDATE products SET views = views + 1 WHERE id = $product_id");

// Related products
$rel_stmt = $conn->prepare("SELECT p.id, p.name, p.price, p.discount_price, p.main_image, v.company_name
    FROM products p JOIN vendors v ON p.vendor_id = v.id
    WHERE p.category_id = ? AND p.id != ? AND p.is_active = 1 LIMIT 6");
$rel_stmt->bind_param("ii", $product['category_id'], $product_id);
$rel_stmt->execute();
$related = $rel_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$extra_images = json_decode($product['images'] ?? '[]', true);
$effective_price = $product['discount_price'] ?? $product['price'];
$has_discount = !empty($product['discount_price']) && $product['discount_price'] < $product['price'];
$discount_pct = $has_discount ? round((1 - $product['discount_price'] / $product['price']) * 100) : 0;
// $main_img = getProductImage($product['main_image'], $product['name']);

$main_file = '/client/uploads/products/' . trim($product['main_image']);
if (file_exists($main_file) && is_file($main_file)) {
    $main_img = $main_file;
} elseif (!empty($extra_images)) {
    // Use first extra image as fallback
    foreach ($extra_images as $img) {
        $extra_path = '/client/uploads/products/' . trim($img);
        if (file_exists($extra_path) && is_file($extra_path)) {
            $main_img = $extra_path;
            break;
        }
    }
} else {
    // Absolute fallback
    $main_img = 'https://via.placeholder.com/500x500/f5f5f5/999?text=' . urlencode($product['name']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($product['name']) ?> - Sellora</title>
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="css/product.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<?php include '../includes/navbar.php'; ?>

<main class="main-content">
  <div class="container">
    <!-- Breadcrumb -->
    <div class="breadcrumb">
      <a href="home.php">Home</a>
      <i class="fas fa-chevron-right"></i>
      <a href="home.php?category=<?= $product['category_slug'] ?>"><?= htmlspecialchars($product['category_name']) ?></a>
      <i class="fas fa-chevron-right"></i>
      <span><?= htmlspecialchars(substr($product['name'], 0, 40)) ?>...</span>
    </div>

    <!-- PRODUCT DETAIL -->
    <div class="product-detail">
      <!-- Images -->
      <div class="product-images">
        <div class="main-image-wrap">
            <img src="<?php echo UPLOAD_URL . $product['main_image']; ?>" 
                    alt="<?php echo htmlspecialchars($product['name']); ?>" 
                    id="mainProductImg">
          <?php if ($has_discount): ?>
          <div class="img-badge">-<?= $discount_pct ?>% OFF</div>
          <?php endif; ?>
        </div>
        <?php if (!empty($extra_images)): ?>
        <div class="image-thumbs">
          <?php 
            // 1. Decode the JSON string from the database
            $extras = json_decode($product['images'], true); 
            
            // 2. Check if there are actually any extra images
            if (!empty($extras)): 
                foreach ($extras as $extra_img): 
            ?>
                <div class="thumb">
                    <img src="<?php echo UPLOAD_URL . $extra_img; ?>" 
                        onclick="document.getElementById('mainProductImg').src=this.src" 
                        style="cursor:pointer; width:80px; height:80px; object-fit:cover;">
                </div>
            <?php 
                endforeach; 
            endif; 
            ?>
        </div>
        <?php endif; ?>
      </div>

      <!-- Info -->
      <div class="product-info">
        <div class="product-info-vendor">
          <i class="fas fa-store"></i>
          <a href="home.php?vendor=<?= $product['vid'] ?>"><?= htmlspecialchars($product['company_name']) ?></a>
        </div>

        <h1 class="product-info-title"><?= htmlspecialchars($product['name']) ?></h1>

        <div class="product-info-rating">
          <span class="stars"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="far fa-star"></i></span>
          <span>4.0 (<?= rand(10, 200) ?> reviews)</span>
          <span class="dot">•</span>
          <span><?= number_format($product['views']) ?> views</span>
        </div>

        <div class="product-info-price">
          <span class="price-big"><?= formatPrice($effective_price) ?></span>
          <?php if ($has_discount): ?>
          <span class="price-old"><?= formatPrice($product['price']) ?></span>
          <span class="price-save">Save <?= formatPrice($product['price'] - $product['discount_price']) ?></span>
          <?php endif; ?>
        </div>

        <?php if (!empty($product['brand'])): ?>
        <div class="product-info-meta">
          <span><strong>Brand:</strong> <?= htmlspecialchars($product['brand']) ?></span>
        </div>
        <?php endif; ?>

        <div class="product-info-stock <?= $product['stock'] > 0 ? 'in-stock' : 'out-stock' ?>">
          <i class="fas fa-<?= $product['stock'] > 0 ? 'check-circle' : 'times-circle' ?>"></i>
          <?= $product['stock'] > 0 ? "In Stock ({$product['stock']} available)" : 'Out of Stock' ?>
        </div>

        <?php if ($product['stock'] > 0): ?>
        <div class="product-info-qty">
          <label>Quantity:</label>
          <div class="qty-control">
            <button onclick="changeQty(-1)">−</button>
            <input type="number" id="qty" value="1" min="1" max="<?= $product['stock'] ?>">
            <button onclick="changeQty(1)">+</button>
          </div>
        </div>

        <div class="product-info-actions">
          <button class="btn btn-primary btn-lg" onclick="addToCartDetail()">
            <i class="fas fa-cart-plus"></i> Add to Cart
          </button>
          <button class="btn btn-secondary btn-lg" onclick="buyNow()">
            <i class="fas fa-bolt"></i> Buy Now
          </button>
        </div>
        <?php else: ?>
        <div style="margin-top:20px;">
          <button class="btn btn-dark btn-lg" disabled>Out of Stock</button>
        </div>
        <?php endif; ?>

        <div class="product-info-trust">
          <div class="trust-item-sm"><i class="fas fa-shield-alt"></i><span>Secure Payment</span></div>
          <div class="trust-item-sm"><i class="fas fa-truck"></i><span>Fast Delivery</span></div>
          <div class="trust-item-sm"><i class="fas fa-rotate-left"></i><span>Easy Returns</span></div>
        </div>
      </div>
    </div>

    <!-- DESCRIPTION & VENDOR TABS -->
    <div class="product-tabs">
      <div class="tab-buttons">
        <button class="tab-btn active" onclick="switchTab('description', this)">Description</button>
        <button class="tab-btn" onclick="switchTab('vendor', this)">Vendor Info</button>
        <button class="tab-btn" onclick="switchTab('reviews', this)">Reviews</button>
      </div>

      <div class="tab-content active" id="tab-description">
        <div class="description-content">
          <?= nl2br(htmlspecialchars($product['description'] ?? 'No description available.')) ?>
        </div>
      </div>

      <div class="tab-content" id="tab-vendor">
        <div class="vendor-info-box">
          <div class="vendor-info-header">
            <div class="vendor-logo-wrap">
              <i class="fas fa-store"></i>
            </div>
            <div>
              <h3><?= htmlspecialchars($product['company_name']) ?></h3>
              <?php if ($product['vendor_phone']): ?>
              <p><i class="fas fa-phone"></i> <?= htmlspecialchars($product['vendor_phone']) ?></p>
              <?php endif; ?>
            </div>
          </div>
          <?php if ($product['vendor_desc']): ?>
          <p class="vendor-desc-text"><?= htmlspecialchars($product['vendor_desc']) ?></p>
          <?php endif; ?>
          <a href="home.php?vendor=<?= $product['vid'] ?>" class="btn btn-outline btn-sm">
            <i class="fas fa-store"></i> View All Products from this Vendor
          </a>
        </div>
      </div>

      <div class="tab-content" id="tab-reviews">
        <div class="reviews-placeholder">
          <i class="fas fa-star" style="font-size:40px; color:#f9a825; margin-bottom:12px;"></i>
          <p>Be the first to review this product!</p>
          <?php if (isLoggedIn()): ?>
          <button class="btn btn-primary" style="margin-top:12px;">Write a Review</button>
          <?php else: ?>
          <a href="login.php" class="btn btn-primary" style="margin-top:12px;">Login to Review</a>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- RELATED PRODUCTS -->
    <?php if (!empty($related)): ?>
    <section class="home-section" style="margin-top:40px;">
      <div class="section-header">
        <h2 class="section-title">Related Products</h2>
        <a href="home.php?category=<?= $product['category_slug'] ?>" class="view-all">View All <i class="fas fa-arrow-right"></i></a>
      </div>
      <div class="products-grid">
        <?php foreach ($related as $rp): ?>
        <?php
          $rp_price = $rp['discount_price'] ?? $rp['price'];
          $rp_has_disc = !empty($rp['discount_price']) && $rp['discount_price'] < $rp['price'];
          $rp_disc_pct = $rp_has_disc ? round((1 - $rp['discount_price'] / $rp['price']) * 100) : 0;
          // Use UPLOAD_URL for consistency. 
         // We check if the image name exists in the DB, otherwise use placeholder.
         $rp_img = !empty($rp['main_image']) 
              ? UPLOAD_URL . $rp['main_image'] 
              : 'https://via.placeholder.com/300x300/f5f5f5/999?text=' . urlencode($rp['name']);
        ?>
        <div class="product-card" onclick="window.location='product.php?id=<?= $rp['id'] ?>'">
          <div class="product-card-img">
            <img src="<?= $rp_img ?>" alt="<?= htmlspecialchars($rp['name']) ?>" loading="lazy">
            <?php if ($rp_has_disc): ?><span class="product-badge sale">-<?= $rp_disc_pct ?>%</span><?php endif; ?>
            <div class="product-card-actions">
              <button class="product-card-action-btn" onclick="event.stopPropagation(); addToCart(<?= $rp['id'] ?>)"><i class="fas fa-cart-plus"></i></button>
            </div>
          </div>
          <div class="product-card-body">
            <div class="product-vendor"><?= htmlspecialchars($rp['company_name']) ?></div>
            <div class="product-name"><?= htmlspecialchars($rp['name']) ?></div>
            <div class="product-price">
              <span class="price-current"><?= formatPrice($rp_price) ?></span>
              <?php if ($rp_has_disc): ?><span class="price-original"><?= formatPrice($rp['price']) ?></span><?php endif; ?>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </section>
    <?php endif; ?>

  </div>
</main>

<?php include '../includes/footer.php'; ?>

<script>
function switchImage(thumb, src) {
  document.getElementById('mainProductImg').src = src;
  document.querySelectorAll('.thumb').forEach(t => t.classList.remove('active'));
  thumb.classList.add('active');
}

function changeQty(delta) {
  const input = document.getElementById('qty');
  const max = parseInt(input.max);
  let val = parseInt(input.value) + delta;
  if (val < 1) val = 1;
  if (val > max) val = max;
  input.value = val;
}

function addToCartDetail() {
  const qty = parseInt(document.getElementById('qty').value);
  addToCart(<?= $product_id ?>, qty);
}

function buyNow() {
  const qty = parseInt(document.getElementById('qty').value);
  fetch('../api/cart.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `action=add&product_id=<?= $product_id ?>&quantity=${qty}`
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) window.location.href = 'cart.php';
  });
}

function switchTab(tab, btn) {
  document.querySelectorAll('.tab-content').forEach(t => t.classList.remove('active'));
  document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
  document.getElementById('tab-' + tab).classList.add('active');
  btn.classList.add('active');
}
</script>
</body>
</html>
