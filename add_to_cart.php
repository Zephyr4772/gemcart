<?php
session_start();
require 'includes/db.php'; // Include your database connection file

// Check if user is logged in (required for database cart)
if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI']; // Store current URL for redirect after login
    header('Location: login.php');
    exit();
}

// Validate product ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: products.php?error=invalid_product');
    exit();
}

$user_id = $_SESSION['user_id'];
$product_id = (int)$_GET['id'];

// Verify product exists in database
try {
    $stmt = $pdo->prepare("SELECT id FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    
    if (!$stmt->fetch()) {
        header('Location: products.php?error=product_not_found');
        exit();
    }
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    header('Location: products.php?error=database_error');
    exit();
}

// Check if product is already in user's cart
try {
    $stmt = $pdo->prepare("SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$user_id, $product_id]);
    $existing_item = $stmt->fetch();

    if ($existing_item) {
        // Update quantity if product exists in cart
        $new_quantity = $existing_item['quantity'] + 1;
        $stmt = $pdo->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
        $stmt->execute([$new_quantity, $existing_item['id']]);
    } else {
        // Add new item to cart
        $stmt = $pdo->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, 1)");
        $stmt->execute([$user_id, $product_id]);
    }
    
    // Success - redirect to client-side cart
    header('Location: cart_client.php?success=added_to_cart');
    exit();
    
} catch (PDOException $e) {
    error_log("Cart update error: " . $e->getMessage());
    header('Location: products.php?error=cart_update_failed');
    exit();
}
?>