<?php
define('INCLUDED', true);
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';
$conn = getDBConnection();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Shopping Cart - Sellora</title>
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="css/cart.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<?php include '../includes/navbar.php'; ?>

<main class="main-content">
  <div class="container">
    <div class="breadcrumb">
      <a href="home.php">Home</a>
      <i class="fas fa-chevron-right"></i>
      <span>Shopping Cart</span>
    </div>

    <h1 class="page-title"><i class="fas fa-shopping-cart"></i> Shopping Cart</h1>

    <div class="cart-layout" id="cartLayout">
      <div class="spinner"></div>
    </div>
  </div>
</main>

<!-- CHECKOUT MODAL -->
<div class="modal-overlay" id="checkoutModal">
  <div class="modal" style="max-width:560px;">
    <div class="modal-header">
      <h3 class="modal-title"><i class="fas fa-credit-card"></i> Checkout</h3>
      <button class="modal-close" onclick="closeCheckout()"><i class="fas fa-times"></i></button>
    </div>
    <div class="modal-body">
      <form id="checkoutForm">
        <div class="form-group">
          <label class="form-label">Full Name</label>
          <input type="text" class="form-control" name="full_name" value="<?= htmlspecialchars($_SESSION['user_name'] ?? '') ?>" required>
        </div>
        <div class="form-group">
          <label class="form-label">Shipping Address *</label>
          <textarea class="form-control" name="shipping_address" rows="3" placeholder="Enter your full delivery address..." required></textarea>
        </div>
        <div class="form-group">
          <label class="form-label">Phone Number</label>
          <input type="tel" class="form-control" name="phone" placeholder="+254 7XX XXX XXX">
        </div>
        <div class="form-group">
          <label class="form-label">Payment Method</label>
          <select class="form-control form-select" name="payment_method">
            <option value="cash_on_delivery">Cash on Delivery</option>
            <option value="mpesa">M-Pesa</option>
            <option value="bank_transfer">Bank Transfer</option>
          </select>
        </div>
        <div class="order-summary-mini" id="orderSummaryMini"></div>
        <button type="submit" class="btn btn-primary btn-block btn-lg">
          <i class="fas fa-check-circle"></i> Place Order
        </button>
      </form>
    </div>
  </div>
</div>

<?php include '../includes/footer.php'; ?>

<script>
// Pass the PHP constant to JavaScript
const UPLOAD_URL = "<?php echo UPLOAD_URL; ?>";

let cartData = null;

async function loadCart() {
  const layout = document.getElementById('cartLayout');
  try {
    const res = await fetch('../api/cart.php?action=get');
    const data = await res.json();
    cartData = data;

    if (!data.success || data.items.length === 0) {
      layout.innerHTML = `
        <div class="empty-state" style="width:100%;">
          <i class="fas fa-shopping-cart"></i>
          <h3>Your cart is empty</h3>
          <p>Add some products to get started!</p>
          <a href="home.php" class="btn btn-primary" style="margin-top:16px;">
            <i class="fas fa-shopping-bag"></i> Continue Shopping
          </a>
        </div>`;
      return;
    }

    let itemsHtml = '';
    data.items.forEach(item => {
      const img = item.main_image
        ? `${UPLOAD_URL}${item.main_image}`
        : `https://via.placeholder.com/80x80/f5f5f5/999?text=P`;
      itemsHtml += `
        <div class="cart-item" id="cart-item-${item.cart_id}">
          <img src="${img}" alt="${item.name}" class="cart-item-img" onclick="window.location='product.php?id=${item.product_id}'">
          <div class="cart-item-info">
            <div class="cart-item-vendor">${item.company_name}</div>
            <div class="cart-item-name" onclick="window.location='product.php?id=${item.product_id}'">${item.name}</div>
            <div class="cart-item-price">KSh ${parseFloat(item.effective_price).toLocaleString('en-KE', {minimumFractionDigits:2})}</div>
          </div>
          <div class="cart-item-qty">
            <button onclick="updateQty(${item.cart_id}, ${item.quantity - 1})">−</button>
            <span>${item.quantity}</span>
            <button onclick="updateQty(${item.cart_id}, ${item.quantity + 1})">+</button>
          </div>
          <div class="cart-item-subtotal">
            KSh ${parseFloat(item.subtotal).toLocaleString('en-KE', {minimumFractionDigits:2})}
          </div>
          <button class="cart-item-remove" onclick="removeItem(${item.cart_id})" title="Remove">
            <i class="fas fa-trash"></i>
          </button>
        </div>`;
    });

    const total = parseFloat(data.total);
    const shipping = total > 5000 ? 0 : 300;
    const grandTotal = total + shipping;

    layout.innerHTML = `
      <div class="cart-items-wrap">
        <div class="cart-items-header">
          <span>${data.items.length} item(s) in your cart</span>
          <button class="btn btn-danger btn-sm" onclick="clearCart()">
            <i class="fas fa-trash"></i> Clear Cart
          </button>
        </div>
        <div class="cart-items" id="cartItems">${itemsHtml}</div>
        <a href="home.php" class="btn btn-outline" style="margin-top:16px;">
          <i class="fas fa-arrow-left"></i> Continue Shopping
        </a>
      </div>
      <div class="cart-summary">
        <div class="summary-card">
          <h3>Order Summary</h3>
          <div class="summary-row"><span>Subtotal</span><strong>KSh ${total.toLocaleString('en-KE', {minimumFractionDigits:2})}</strong></div>
          <div class="summary-row"><span>Shipping</span><strong>${shipping === 0 ? '<span style="color:green;">FREE</span>' : 'KSh ' + shipping.toFixed(2)}</strong></div>
          ${shipping > 0 ? '<div class="free-shipping-note"><i class="fas fa-info-circle"></i> Add KSh ' + (5000 - total).toFixed(2) + ' more for FREE shipping!</div>' : ''}
          <div class="summary-divider"></div>
          <div class="summary-row total-row"><span>Total</span><strong>KSh ${grandTotal.toLocaleString('en-KE', {minimumFractionDigits:2})}</strong></div>
          <button class="btn btn-primary btn-block btn-lg" onclick="openCheckout(${grandTotal})">
            <i class="fas fa-credit-card"></i> Proceed to Checkout
          </button>
          <div class="summary-trust">
            <i class="fas fa-lock"></i> Secure & Encrypted Checkout
          </div>
        </div>
      </div>`;
  } catch (e) {
    layout.innerHTML = '<div class="alert alert-danger">Failed to load cart. Please refresh.</div>';
  }
}

async function updateQty(cartId, qty) {
  const res = await fetch('../api/cart.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `action=update&cart_id=${cartId}&quantity=${qty}`
  });
  const data = await res.json();
  if (data.success) {
    updateCartBadge(data.cart_count);
    loadCart();
  }
}

async function removeItem(cartId) {
  const res = await fetch('../api/cart.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `action=remove&cart_id=${cartId}`
  });
  const data = await res.json();
  if (data.success) {
    showToast('Item removed from cart', 'info');
    updateCartBadge(data.cart_count);
    loadCart();
  }
}

async function clearCart() {
  if (!confirm('Clear all items from cart?')) return;
  const res = await fetch('../api/cart.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: 'action=clear'
  });
  const data = await res.json();
  if (data.success) {
    updateCartBadge(0);
    loadCart();
  }
}

function openCheckout(total) {
  <?php if (!isLoggedIn()): ?>
  window.location.href = 'login.php?redirect=cart.php';
  return;
  <?php endif; ?>
  document.getElementById('orderSummaryMini').innerHTML = `
    <div class="mini-summary">
      <div class="summary-row"><span>Order Total:</span><strong>KSh ${total.toLocaleString('en-KE', {minimumFractionDigits:2})}</strong></div>
    </div>`;
  document.getElementById('checkoutModal').classList.add('active');
}

function closeCheckout() {
  document.getElementById('checkoutModal').classList.remove('active');
}

document.getElementById('checkoutForm').addEventListener('submit', async function(e) {
  e.preventDefault();
  const formData = new FormData(this);
  formData.append('action', 'checkout');
  const res = await fetch('../api/cart.php', { method: 'POST', body: formData });
  const data = await res.json();
  if (data.success) {
    closeCheckout();
    showToast(data.message, 'success');
    updateCartBadge(0);
    setTimeout(() => loadCart(), 500);
  } else {
    showToast(data.message, 'error');
    if (data.redirect) window.location.href = data.redirect;
  }
});

loadCart();
</script>
</body>
</html>
