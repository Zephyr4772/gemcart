<?php
session_start();
require_once 'includes/db.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in'], JSON_PRETTY_PRINT);
    exit();
}

$user_id = $_SESSION['user_id'];
$action = $_POST['action'] ?? '';
$product_id = intval($_POST['product_id'] ?? 0);
$quantity = intval($_POST['quantity'] ?? 0);

try {
    switch ($action) {
        case 'add':
            // Check if product exists
            $stmt = $pdo->prepare("SELECT id, name, price FROM products WHERE id = ?");
            $stmt->execute([$product_id]);
            if (!$stmt->fetch()) {
                echo json_encode(['success' => false, 'message' => 'Product not found'], JSON_PRETTY_PRINT);
                exit();
            }
            
            // Check if product is already in user's cart
            $stmt = $pdo->prepare("SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?");
            $stmt->execute([$user_id, $product_id]);
            $existing_item = $stmt->fetch();

            if ($existing_item) {
                // Update quantity if product exists in cart
                $new_quantity = $existing_item['quantity'] + $quantity;
                $stmt = $pdo->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
                $stmt->execute([$new_quantity, $existing_item['id']]);
            } else {
                // Add new item to cart
                $stmt = $pdo->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
                $stmt->execute([$user_id, $product_id, $quantity]);
            }
            
            echo json_encode(['success' => true, 'message' => 'Item added to cart'], JSON_PRETTY_PRINT);
            break;
            
        case 'update':
            if ($quantity <= 0) {
                // Remove item if quantity is 0 or less
                $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
                $stmt->execute([$user_id, $product_id]);
                echo json_encode(['success' => true, 'message' => 'Item removed from cart'], JSON_PRETTY_PRINT);
            } else {
                // Update item quantity
                $stmt = $pdo->prepare("UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?");
                $stmt->execute([$quantity, $user_id, $product_id]);
                echo json_encode(['success' => true, 'message' => 'Cart updated'], JSON_PRETTY_PRINT);
            }
            break;
            
        case 'remove':
            $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
            $stmt->execute([$user_id, $product_id]);
            echo json_encode(['success' => true, 'message' => 'Item removed from cart'], JSON_PRETTY_PRINT);
            break;
            
        case 'clear':
            $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");
            $stmt->execute([$user_id]);
            echo json_encode(['success' => true, 'message' => 'Cart cleared'], JSON_PRETTY_PRINT);
            break;
            
        case 'sync':
            // Sync cart items from localStorage with database
            $items_json = $_POST['items'] ?? '[]';
            $items = json_decode($items_json, true);
            
            if (is_array($items)) {
                foreach ($items as $item) {
                    $item_id = intval($item['id']);
                    $item_quantity = intval($item['quantity']);
                    
                    // Check if product exists
                    $stmt = $pdo->prepare("SELECT id, name, price FROM products WHERE id = ?");
                    $stmt->execute([$item_id]);
                    if (!$stmt->fetch()) {
                        continue; // Skip invalid products
                    }
                    
                    // Check if product is already in user's cart
                    $stmt = $pdo->prepare("SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?");
                    $stmt->execute([$user_id, $item_id]);
                    $existing_item = $stmt->fetch();

                    if ($existing_item) {
                        // Update quantity if product exists in cart
                        $new_quantity = max($existing_item['quantity'], $item_quantity);
                        $stmt = $pdo->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
                        $stmt->execute([$new_quantity, $existing_item['id']]);
                    } else {
                        // Add new item to cart
                        $stmt = $pdo->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
                        $stmt->execute([$user_id, $item_id, $item_quantity]);
                    }
                }
                echo json_encode(['success' => true, 'message' => 'Cart synchronized'], JSON_PRETTY_PRINT);
            } else {
                echo json_encode(['success' => false, 'message' => 'Invalid cart data'], JSON_PRETTY_PRINT);
            }
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action'], JSON_PRETTY_PRINT);
            break;
    }
} catch (PDOException $e) {
    error_log("Cart API error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error occurred'], JSON_PRETTY_PRINT);
}
?>