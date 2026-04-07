<?php
require_once 'config/database.php'; 

// Call the function from your database.php to create the $pdo variable
$pdo = getPDOConnection(); 

if (!$pdo) {
    die("<h1>Failed to initialize PDO connection.</h1>");
}

try {
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM products");
    $row = $stmt->fetch();
    echo "<h1>Success! Total Products: " . $row['total'] . "</h1>";
    
    $stmt = $pdo->query("SELECT name, main_image FROM products LIMIT 5");
    while ($prod = $stmt->fetch()) {
        echo "Product: " . $prod['name'] . " | Image Filename: " . $prod['main_image'] . "<br>";
    }
} catch (Exception $e) {
    echo "<h1>Database Error: " . $e->getMessage() . "</h1>";
}