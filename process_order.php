<?php
session_start();
require_once 'includes/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'User not logged in']);
    exit();
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

// Set JSON response header
header('Content-Type: application/json');

try {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        throw new Exception('Invalid JSON data');
    }
    
    $user_id = $_SESSION['user_id'];
    $cart_items = $input['cart_items'] ?? [];
    $payment_method = $input['payment_method'] ?? '';
    $delivery_address = $input['delivery_address'] ?? '';
    
    // Validate input
    if (empty($cart_items)) {
        throw new Exception('Cart is empty');
    }
    
    if (empty($payment_method)) {
        throw new Exception('Payment method is required');
    }
    
    if (empty($delivery_address)) {
        throw new Exception('Delivery address is required');
    }
    
    // Calculate total
    $total_amount = 0;
    $validated_items = [];
    $invalid_items = [];
    
    // Validate each cart item against database
    foreach ($cart_items as $item) {
        $stmt = $pdo->prepare("SELECT id, name, price FROM products WHERE id = ?");
        $stmt->execute([$item['id']]);
        $product = $stmt->fetch();
        
        if (!$product) {
            // Collect invalid items but don't fail the entire order
            $invalid_items[] = $item['id'];
            continue;
        }
        
        // Verify price matches (security check)
        if (abs($product['price'] - $item['price']) > 0.01) {
            throw new Exception("Price mismatch for product {$product['name']}");
        }
        
        $quantity = max(1, intval($item['quantity']));
        $subtotal = $product['price'] * $quantity;
        $total_amount += $subtotal;
        
        $validated_items[] = [
            'product_id' => $product['id'],
            'quantity' => $quantity,
            'price' => $product['price']
        ];
    }
    
    // If all items are invalid, throw an error
    if (empty($validated_items) && !empty($invalid_items)) {
        throw new Exception('All items in your cart are invalid or no longer available');
    }
    
    // Start transaction
    $pdo->beginTransaction();
    
    try {
        // Insert order
        $stmt = $pdo->prepare("
            INSERT INTO orders (user_id, total_amount, payment_method, delivery_address, order_date) 
            VALUES (?, ?, ?, ?, NOW())
        ");
        $stmt->execute([$user_id, $total_amount, $payment_method, $delivery_address]);
        
        $order_id = $pdo->lastInsertId();
        
        // Insert order items
        $stmt = $pdo->prepare("
            INSERT INTO order_items (order_id, product_id, quantity, price) 
            VALUES (?, ?, ?, ?)
        ");
        
        foreach ($validated_items as $item) {
            $stmt->execute([
                $order_id,
                $item['product_id'],
                $item['quantity'],
                $item['price']
            ]);
        }
        
        // Clear user's cart after successful order
        $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");
        $stmt->execute([$user_id]);
        
        // Commit transaction
        $pdo->commit();
        
        // Return success response
        $response = [
            'success' => true,
            'message' => 'Order placed successfully!',
            'order_id' => $order_id,
            'total_amount' => $total_amount
        ];
        
        // Include warning about invalid items if any
        if (!empty($invalid_items)) {
            $response['warning'] = 'Some items in your cart were invalid or no longer available and were not included in your order: ' . implode(', ', $invalid_items);
        }
        
        echo json_encode($response);
        
    } catch (Exception $e) {
        // Rollback transaction on error
        $pdo->rollback();
        throw $e;
    }
    
} catch (Exception $e) {
    error_log("Order processing error: " . $e->getMessage());
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}
?>