<?php
// Helper functions for GemCart

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Get current user data
function getCurrentUser() {
    global $conn;
    if (isLoggedIn()) {
        $user_id = $_SESSION['user_id'];
        $query = "SELECT * FROM users WHERE id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "i", $user_id);   
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        return mysqli_fetch_assoc($result);
    }
    return null;
}

// Get cart items
function getCartItems() {
    global $conn;
    $cart_items = [];
    
    if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
        $cart_ids = array_keys($_SESSION['cart']);
        if (!empty($cart_ids)) {
            $placeholders = str_repeat('?,', count($cart_ids) - 1) . '?';
            $query = "SELECT * FROM products WHERE id IN ($placeholders)";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, str_repeat('i', count($cart_ids)), ...$cart_ids);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            
            while ($product = mysqli_fetch_assoc($result)) {
                $product['quantity'] = $_SESSION['cart'][$product['id']];
                $cart_items[] = $product;
            }
        }
    }
    
    return $cart_items;
}

// Calculate cart total
function getCartTotal() {
    $cart_items = getCartItems();
    $total = 0;
    
    foreach ($cart_items as $item) {
        $total += $item['price'] * $item['quantity'];
    }
    
    return $total;
}

// Validate email format
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Sanitize input
function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

// Hash password
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

// Verify password
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

// ------------------ ADMIN HELPERS ------------------
// Check if admin is logged in
function isAdminLoggedIn() {
    return isset($_SESSION['admin_id']);
}

// Require admin authentication; redirects to admin_login.php if not logged in
function requireAdmin() {
    if (!isAdminLoggedIn()) {
        header('Location: admin_login.php');
        exit();
    }
}

// Sanitize admin username
function sanitizeUsername($username) {
    return preg_replace('/[^a-zA-Z0-9_]/', '', trim($username));
}

// Redirect with message
function redirect($url, $message = '', $type = 'success') {
    if ($message) {
        $_SESSION['message'] = $message;
        $_SESSION['message_type'] = $type;
    }
    header("Location: $url");
    exit();
}

// Display message
function displayMessage() {
    if (isset($_SESSION['message'])) {
        $type = $_SESSION['message_type'] ?? 'info';
        $message = $_SESSION['message'];
        unset($_SESSION['message'], $_SESSION['message_type']);
        
        return "<div class='alert alert-$type'>$message</div>";
    }
    return '';
}

// Get product by ID
function getProduct($id) {
    global $conn;
    $query = "SELECT p.*, c.name as category_name 
              FROM products p 
              LEFT JOIN categories c ON p.category_id = c.id 
              WHERE p.id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_assoc($result);
}

// Check if product is in favorites
function isInFavorites($product_id) {
    global $conn;
    if (!isLoggedIn()) return false;
    
    $user_id = $_SESSION['user_id'];
    $query = "SELECT * FROM favorites WHERE user_id = ? AND product_id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "ii", $user_id, $product_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    return mysqli_num_rows($result) > 0;
}
?> 