# Sellora - E-Commerce Platform

Sellora is a full-featured, multi-vendor e-commerce platform built for a final year project. It allows vendors to register, upload products from their local drive, and monitor sales. Customers can browse categories, search for products, add items to their cart, and checkout.

## Features

### Client Side (Shoppers)
- **Landing Page:** Beautiful hero section, category grid, and registration call-to-action.
- **Home/Shop Page:** Browse products, filter by category, price range, and sort by popularity or price.
- **Product Details:** View product images, descriptions, vendor info, and add to cart.
- **Shopping Cart:** Manage quantities, view subtotal, and proceed to checkout.
- **Authentication:** User registration and login system.

### Vendor Side (Sellers)
- **Vendor Dashboard:** Real-time statistics on products, views, and recent orders.
- **Product Management:** Add new products (with image upload from local drive), edit details, and delete products.
- **Order Monitoring:** View orders placed by customers for their products.
- **Authentication:** Dedicated vendor registration and login portal.

## Technology Stack
- **Frontend:** HTML5, CSS3 (Custom styling, no heavy frameworks), Vanilla JavaScript
- **Backend:** PHP 8.2 (Procedural & OOP mix)
- **Database:** MySQL 8.0
- **Environment:** Docker (XAMPP equivalent)

## Setup (Using Github)

**bash**
- git clone https://github.com/yourusername/sellora.git
- cd sellora
- docker compose up --build 
 

 or


## Setup Instructions (Using XAMPP)

If you are using XAMPP on your local machine for your final year 
project presentation, follow these steps:


1. **Extract the Files:**
   Extract the `sellora.zip` archive into your XAMPP `htdocs` directory (e.g., `C:\xampp\htdocs\sellora`).

2. **Database Setup:**
   - Open XAMPP Control Panel and start **Apache** and **MySQL**.
   - Go to phpMyAdmin (`http://localhost/phpmyadmin`).
   - Create a new database named `sellora_db`.
   - Import the `database/sellora.sql` file into the `sellora_db` database. This will create all tables and insert demo data.

3. **Configuration:**
   - Open `config/database.php` in a text editor.
   - Comment out the Docker configuration and uncomment the XAMPP configuration:
     ```php
     // For XAMPP (local)
     define('DB_HOST', 'localhost');
     define('DB_USER', 'root');
     define('DB_PASS', '');
     define('DB_NAME', 'sellora_db');
     ```
   - Open `config/config.php` and ensure `SITE_URL` matches your local setup (e.g., `http://localhost/sellora`).

4. **Run the Project:**
   - Open your browser and navigate to `http://localhost/sellora`.

## Setup Instructions (Using Docker)

If you prefer to run the project using the provided Docker configuration:

1. Ensure Docker and Docker Compose are installed on your machine.
2. Open a terminal in the `sellora` directory.
3. Run the following command:
   ```bash
   docker-compose up -d
   ```
4. The application will be available at `http://localhost:8080`.
5. phpMyAdmin will be available at `http://localhost:8081` (Server: `mysql`, User: `root`, Password: `sellora_root`).

## Demo Accounts

**Customer Account:**
- Email: `user@sellora.com`
- Password: `password`

**Vendor Account:**
- Email: `vendor@sellora.com`
- Password: `password`

## Project Structure
- `/api/` - PHP backend endpoints for AJAX requests
- `/client/` - Customer-facing pages (home, product, cart, auth)
- `/vendor/` - Vendor dashboard and management pages
- `/config/` - Database and global configuration
- `/database/` - SQL schema and seed data
- `/includes/` - Shared UI components (navbar, footer)
- `/uploads/products/` - Directory for uploaded product images
