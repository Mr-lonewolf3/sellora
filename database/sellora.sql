-- ============================================================
-- Sellora E-Commerce Platform - Database Schema
-- ============================================================
CREATE DATABASE IF NOT EXISTS sellora_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE sellora_db;
-- ============================================================
-- USERS TABLE (Customers)
-- ============================================================
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    profile_image VARCHAR(255) DEFAULT 'default_user.png',
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
-- ============================================================
-- VENDORS TABLE
-- ============================================================
CREATE TABLE IF NOT EXISTS vendors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_name VARCHAR(200) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    description TEXT,
    logo VARCHAR(255) DEFAULT 'default_vendor.png',
    address TEXT,
    is_active TINYINT(1) DEFAULT 1,
    is_verified TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
-- ============================================================
-- CATEGORIES TABLE
-- ============================================================
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    icon VARCHAR(100),
    image VARCHAR(255),
    description TEXT,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
-- ============================================================
-- PRODUCTS TABLE
-- ============================================================
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    vendor_id INT NOT NULL,
    category_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    discount_price DECIMAL(10, 2) DEFAULT NULL,
    stock INT DEFAULT 0,
    main_image VARCHAR(255) NOT NULL,
    images TEXT COMMENT 'JSON array of additional images',
    brand VARCHAR(100),
    sku VARCHAR(100),
    is_active TINYINT(1) DEFAULT 1,
    is_featured TINYINT(1) DEFAULT 0,
    views INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (vendor_id) REFERENCES vendors(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
);
-- ============================================================
-- CART TABLE
-- ============================================================
CREATE TABLE cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    session_id VARCHAR(255),
    product_id INT NOT NULL,
    quantity INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);
-- ============================================================
-- ORDERS TABLE
-- ============================================================
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    order_number VARCHAR(50) NOT NULL UNIQUE,
    total_amount DECIMAL(10, 2) NOT NULL,
    shipping_address TEXT,
    payment_method VARCHAR(50) DEFAULT 'cash_on_delivery',
    payment_status ENUM('pending', 'paid', 'failed') DEFAULT 'pending',
    order_status ENUM(
        'pending',
        'processing',
        'shipped',
        'delivered',
        'cancelled'
    ) DEFAULT 'pending',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE
    SET NULL
);
-- ============================================================
-- ORDER ITEMS TABLE
-- ============================================================
CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    vendor_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);
-- ============================================================
-- REVIEWS TABLE
-- ============================================================
CREATE TABLE reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    user_id INT NOT NULL,
    rating INT NOT NULL CHECK (
        rating BETWEEN 1 AND 5
    ),
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
-- ============================================================
-- SEED CATEGORIES
-- ============================================================
INSERT INTO categories (name, slug, icon, description)
VALUES (
        'Electronics',
        'electronics',
        'fas fa-laptop',
        'Phones, Laptops, TVs, and more'
    ),
    (
        'Fashion',
        'fashion',
        'fas fa-tshirt',
        'Clothing, Shoes, and Accessories'
    ),
    (
        'Home & Garden',
        'home-garden',
        'fas fa-home',
        'Furniture, Decor, and Garden'
    ),
    (
        'Beauty & Health',
        'beauty-health',
        'fas fa-spa',
        'Skincare, Makeup, and Wellness'
    ),
    (
        'Sports & Outdoors',
        'sports-outdoors',
        'fas fa-running',
        'Fitness, Sports, and Outdoor gear'
    ),
    (
        'Babies & Kids',
        'babies-kids',
        'fas fa-baby',
        'Toys, Baby gear, and Kids clothing'
    ),
    (
        'Automotive',
        'automotive',
        'fas fa-car',
        'Car accessories and parts'
    ),
    (
        'Books & Stationery',
        'books-stationery',
        'fas fa-book',
        'Books, Pens, and Office supplies'
    ),
    (
        'Food & Grocery',
        'food-grocery',
        'fas fa-shopping-basket',
        'Fresh produce and packaged food'
    ),
    (
        'Computing',
        'computing',
        'fas fa-desktop',
        'Computers, Printers, and Accessories'
    );
-- ============================================================
-- SEED DEMO VENDOR
-- ============================================================
SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE products;
TRUNCATE TABLE vendors;
SET FOREIGN_KEY_CHECKS = 1;

INSERT INTO vendors (
        id,
        company_name,
        email,
        password,
        phone,
        description,
        is_active,
        is_verified
    )
VALUES -- Category: Electronics & Tech
(
 1,
 'TechHub Kenya',
 'sales@techhub.co.ke', 
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 
 '0711222333', 
 'Premium laptops and accessories in Nairobi.',
  1, 
  1
 ),
(
    2,
    'Nexus Electronics',
    'info@nexus.ke', 
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
     '0722333444',
      'Authorized dealer for smartphones and smart home devices.', 
      1, 
      1
      ),

-- Category: Fashion & Apparel
(
    3,
    'Fashion Palace', 
    'hello@fashionpalace.com', 
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    '0733444555',
    'Trendy outfits and designer footwear.',
      1, 
      1
      ),
(
    4,
    'Maji Fashion House', 
    'design@maji.co.ke',
     '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 
     '0744555666', 
     'Authentic African wear and custom tailoring.',
      1,
       1
      ),

-- Category: Home & Living
(
    5,
    'Decor Central', 
    'support@decorcentral.ke',
     '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
      '0755666777',
       'Modern furniture and minimalist home decor.',
        1,
         1
         ),
(
    6,
    'Kitchen Masters', 
    'orders@kitchenmasters.com',
     '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
      '0766777888', 'High-quality kitchenware and appliances.',
       1, 
       1
       ),

-- Category: Beauty & Personal Care
(
 7,
 'Glow Cosmetics',
 'admin@glow.ke', 
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 
 '0777888999', 
 'Organic skincare and professional makeup kits.', 
 1,
  1
  ),
(
    8,
    'The Scent Shop', 
    'scents@shop.com', 
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 
    '0788999000', 
    'Original designer perfumes and fragrances.',
     1, 
     1);
-- ============================================================
-- SEED DEMO USER (password: password)
-- ============================================================
INSERT INTO users (full_name, email, password, phone)
VALUES (
        'John Doe',
        'user@sellora.com',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        '+254700000000'
    );

INSERT INTO products (vendor_id, category_id, name, slug, price, main_image)
VALUES
(1, 1, 'Electronics Item', 'electronics-item', 45000, 'product_69b9897555108.jpg'),
(1, 1, 'Tech Gadget', 'tech-gadget', 12000, 'product_69b98a7b62446.jpg'),
(3, 2, 'Summer Dress', 'summer-dress', 3500, 'product_69b55754b52e1.jpg'),
(3, 2, 'Casual Sneakers', 'casual-sneakers', 5800, 'product_69b669749dd71.jpg');