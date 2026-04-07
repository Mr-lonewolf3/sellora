<?php
define('INCLUDED', true);
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../config/database.php';
$conn = getDBConnection();
$pdo = getPDOConnection();

$category = sanitize($_GET['category'] ?? '');
$search_query = sanitize($_GET['q'] ?? '');
$sort = sanitize($_GET['sort'] ?? 'newest');
$min_price = (float)($_GET['min_price'] ?? 0);
$max_price = (float)($_GET['max_price'] ?? 0);
$page = max(1, (int)($_GET['page'] ?? 1));
$per_page = 20;
$offset = ($page - 1) * $per_page;

// Build product query
$where = "WHERE p.is_active = 1";
$params = [];
$types = "";

if (!empty($search_query)) {
    $search = "%$search_query%";
    $where .= " AND (p.name LIKE ? OR p.description LIKE ? OR v.company_name LIKE ?)";
    $params = array_merge($params, [$search, $search, $search]);
    $types .= "sss";
}

if (!empty($category)) {
    $where .= " AND c.slug = ?";
    $params[] = $category;
    $types .= "s";
}

if ($min_price > 0) {
    $where .= " AND p.price >= ?";
    $params[] = $min_price;
    $types .= "d";
}

if ($max_price > 0) {
    $where .= " AND p.price <= ?";
    $params[] = $max_price;
    $types .= "d";
}

$order = match($sort) {
    'price_asc' => 'p.price ASC',
    'price_desc' => 'p.price DESC',
    'popular' => 'p.views DESC',
    default => 'p.created_at DESC'
};

// Count total
$count_sql = "SELECT COUNT(*) as total FROM products p LEFT JOIN categories c ON p.category_id = c.id LEFT JOIN vendors v ON p.vendor_id = v.id $where";
$stmt = $conn->prepare($count_sql);
if (!empty($params)) $stmt->bind_param($types, ...$params);
$stmt->execute();
$total_products = $stmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_products / $per_page);

// Fetch products
$sql = "SELECT p.id, p.name, p.price, p.discount_price, p.main_image, p.stock, p.views,
               c.name as category_name, c.slug as category_slug, v.company_name
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        LEFT JOIN vendors v ON p.vendor_id = v.id
        $where ORDER BY $order LIMIT ? OFFSET ?";

$params[] = $per_page;
$params[] = $offset;
$types .= "ii";

$stmt = $conn->prepare($sql);
if (!empty($params)) $stmt->bind_param($types, ...$params);
$stmt->execute();
$products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Fetch categories
$categories = getCategories($conn);

// Active category info
$active_category = null;
if (!empty($category)) {
    $stmt = $conn->prepare("SELECT * FROM categories WHERE slug = ?");
    $stmt->bind_param("s", $category);
    $stmt->execute();
    $active_category = $stmt->get_result()->fetch_assoc();
}

// Featured products (for homepage banner)
$featured_stmt = $conn->query("SELECT p.id, p.name, p.price, p.discount_price, p.main_image, v.company_name
    FROM products p JOIN vendors v ON p.vendor_id = v.id WHERE p.is_active = 1 ORDER BY p.views DESC LIMIT 4");
$featured = $featured_stmt->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $active_category ? htmlspecialchars($active_category['name']) . ' - ' : '' ?><?= !empty($search_query) ? "\"$search_query\" - " : '' ?>Sellora</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/home.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<?php include '../includes/navbar.php'; ?>

<main class="main-content">
<div class="container">

    <?php if (empty($category) && empty($search_query)): ?>
    <div class="home-banner">
        <div class="home-banner-content">
            <div class="home-banner-text">
                <span class="banner-tag">🔥 Flash Sale</span>
                <h1>Discover Amazing Deals<br>Across Kenya</h1>
                <p>Shop from thousands of products across all categories. Fast delivery, secure payments.</p>
                <div class="banner-search">
                    <input type="text" id="bannerSearch" placeholder="What are you looking for?">
                    <button onclick="doBannerSearch()"><i class="fas fa-search"></i> Search</button>
                </div>
            </div>
            <div class="home-banner-visual">
                <div class="banner-cards">
                    <?php foreach (array_slice($featured, 0, 3) as $fp): ?>
                    <div class="banner-product-card" onclick="window.location='product.php?id=<?= $fp['id'] ?>'">
                        <img src="<?php echo UPLOAD_URL . $product['main_image']; ?>" 
                            alt="<?php echo htmlspecialchars($product['name']); ?>" 
                            class="product-img">
                        <span><?= htmlspecialchars(substr($fp['name'], 0, 20)) ?>...</span>
                            <strong><?= formatPrice($fp['discount_price'] ?? $fp['price']) ?></strong>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <section class="home-section">
        <div class="section-header">
            <h2 class="section-title">Shop by Category</h2>
        </div>
        <div class="categories-grid">
            <?php foreach ($categories as $cat): ?>
            <a href="home.php?category=<?= $cat['slug'] ?>" class="category-card">
                <i class="<?= $cat['icon'] ?>"></i>
                <span><?= htmlspecialchars($cat['name']) ?></span>
            </a>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <div class="products-layout">
        <aside class="filter-sidebar">
            <div class="filter-card">
                <h3 class="filter-title"><i class="fas fa-sliders-h"></i> Filters</h3>
                <div class="filter-section">
                    <h4>Categories</h4>
                    <ul class="filter-list">
                        <li>
                            <a href="home.php<?= !empty($search_query) ? '?q=' . urlencode($search_query) : '' ?>" class="<?= empty($category) ? 'active' : '' ?>">
                                All Categories
                            </a>
                        </li>
                        <?php foreach ($categories as $cat): ?>
                        <li>
                            <a href="home.php?category=<?= $cat['slug'] ?><?= !empty($search_query) ? '&q=' . urlencode($search_query) : '' ?>"
                               class="<?= $category === $cat['slug'] ? 'active' : '' ?>">
                                <i class="<?= $cat['icon'] ?>"></i> <?= htmlspecialchars($cat['name']) ?>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <div class="filter-section">
                    <h4>Price Range (KSh)</h4>
                    <div class="price-range">
                        <input type="number" id="minPrice" placeholder="Min" value="<?= $min_price > 0 ? $min_price : '' ?>" class="form-control">
                        <span>—</span>
                        <input type="number" id="maxPrice" placeholder="Max" value="<?= $max_price > 0 ? $max_price : '' ?>" class="form-control">
                    </div>
                    <button class="btn btn-primary btn-sm btn-block" onclick="applyPriceFilter()" style="margin-top:10px;">Apply</button>
                </div>

                <div class="filter-section">
                    <h4>Sort By</h4>
                    <select class="form-control form-select" onchange="applySortFilter(this.value)">
                        <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Newest First</option>
                        <option value="popular" <?= $sort === 'popular' ? 'selected' : '' ?>>Most Popular</option>
                        <option value="price_asc" <?= $sort === 'price_asc' ? 'selected' : '' ?>>Price: Low to High</option>
                        <option value="price_desc" <?= $sort === 'price_desc' ? 'selected' : '' ?>>Price: High to Low</option>
                    </select>
                </div>
            </div>
        </aside>

        <div class="products-main">
            <div class="results-header">
                <div class="results-info">
                    <?php if (!empty($search_query)): ?>
                        <h2>Results for "<strong><?= htmlspecialchars($search_query) ?></strong>"</h2>
                    <?php elseif ($active_category): ?>
                        <h2><i class="<?= $active_category['icon'] ?>"></i> <?= htmlspecialchars($active_category['name']) ?></h2>
                    <?php else: ?>
                        <h2>All Products</h2>
                    <?php endif; ?>
                    <span><?= number_format($total_products) ?> products found</span>
                </div>
                <div class="results-sort-mobile">
                    <select class="form-control form-select" onchange="applySortFilter(this.value)">
                        <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Newest</option>
                        <option value="popular" <?= $sort === 'popular' ? 'selected' : '' ?>>Popular</option>
                        <option value="price_asc" <?= $sort === 'price_asc' ? 'selected' : '' ?>>Price ↑</option>
                        <option value="price_desc" <?= $sort === 'price_desc' ? 'selected' : '' ?>>Price ↓</option>
                    </select>
                </div>
            </div>

            <?php if (empty($products)): ?>
                <div class="empty-state">
                    <i class="fas fa-search"></i>
                    <h3>No products found</h3>
                    <p>Try adjusting your search or filters</p>
                    <a href="home.php" class="btn btn-primary" style="margin-top:16px;">Browse All Products</a>
                </div>
            <?php else: ?>
                <div class="products-grid">
                    <?php foreach ($products as $product): ?>
                    <?php
                        $effective_price = $product['discount_price'] ?? $product['price'];
                        $has_discount = !empty($product['discount_price']) && $product['discount_price'] < $product['price'];
                        $discount_pct = $has_discount ? round((1 - $product['discount_price'] / $product['price']) * 100) : 0;
                    ?>
                    <div class="product-card" onclick="window.location='product.php?id=<?= $product['id'] ?>'">
                        <div class="product-card-img">
                           <img src="<?php echo UPLOAD_URL . $product['main_image']; ?>" 
                                alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                class="product-img">
                            <?php if ($has_discount): ?>
                                <span class="product-badge sale">-<?= $discount_pct ?>%</span>
                            <?php endif; ?>
                            <?php if ($product['stock'] <= 0): ?>
                                <span class="product-badge" style="background:#666;">Out of Stock</span>
                            <?php endif; ?>
                        </div>
                        <div class="product-card-body">
                            <div class="product-vendor"><?= htmlspecialchars($product['company_name']) ?></div>
                            <div class="product-name"><?= htmlspecialchars($product['name']) ?></div>
                            <div class="product-price">
                                <span class="price-current"><?= formatPrice($effective_price) ?></span>
                                <?php if ($has_discount): ?>
                                    <span class="price-original"><?= formatPrice($product['price']) ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php
                    $base_params = array_filter([
                        'category' => $category, 'q' => $search_query, 'sort' => $sort,
                        'min_price' => $min_price ?: null, 'max_price' => $max_price ?: null
                    ]);
                    $base_url = 'home.php?' . http_build_query($base_params);
                    ?>
                    <?php if ($page > 1): ?>
                        <a href="<?= $base_url ?>&page=<?= $page - 1 ?>" class="page-btn"><i class="fas fa-chevron-left"></i></a>
                    <?php endif; ?>
                    <?php for ($i = max(1, $page-2); $i <= min($total_pages, $page+2); $i++): ?>
                        <a href="<?= $base_url ?>&page=<?= $i ?>" class="page-btn <?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
                    <?php endfor; ?>
                    <?php if ($page < $total_pages): ?>
                        <a href="<?= $base_url ?>&page=<?= $page + 1 ?>" class="page-btn"><i class="fas fa-chevron-right"></i></a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
</main>

<?php include '../includes/footer.php'; ?>

<script>
function doBannerSearch() {
    const q = document.getElementById('bannerSearch').value.trim();
    if (q) window.location.href = 'home.php?q=' + encodeURIComponent(q);
}

document.getElementById('bannerSearch')?.addEventListener('keypress', function(e) {
    if (e.key === 'Enter') doBannerSearch();
});

function applyPriceFilter() {
    const min = document.getElementById('minPrice').value;
    const max = document.getElementById('maxPrice').value;
    const params = new URLSearchParams(window.location.search);
    if (min) params.set('min_price', min); else params.delete('min_price');
    if (max) params.set('max_price', max); else params.delete('max_price');
    params.delete('page');
    window.location.href = 'home.php?' + params.toString();
}

function applySortFilter(sort) {
    const params = new URLSearchParams(window.location.search);
    params.set('sort', sort);
    params.delete('page');
    window.location.href = 'home.php?' + params.toString();
}
</script>
</body>
</html>