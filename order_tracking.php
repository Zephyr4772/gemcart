<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Function to get category folder name from image name
function getCategoryFolderFromImage($image_name) {
    // Extract category abbreviation from image name (e.g., 1-rng-g.jpg -> rings)
    if (strpos($image_name, '-rng') !== false) return 'rings';
    if (strpos($image_name, '-nck') !== false) return 'necklaces';
    if (strpos($image_name, '-erg') !== false) return 'earrings';
    if (strpos($image_name, '-brl') !== false) return 'bracelets';
    if (strpos($image_name, '-wch') !== false) return 'watches';
    return 'products'; // default
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?redirect=order_tracking');
    exit();
}

$user_id = $_SESSION['user_id'];
$order_id = $_GET['order_id'] ?? '';

// Fetch order details from database
$order_details = null;
if ($order_id && is_numeric($order_id)) {
    try {
        // Get order information
        $stmt = $pdo->prepare("
            SELECT o.*, u.name as customer_name, u.email as customer_email
            FROM orders o
            JOIN users u ON o.user_id = u.id
            WHERE o.id = ? AND o.user_id = ?
        ");
        $stmt->execute([$order_id, $user_id]);
        $order = $stmt->fetch();
        
        if ($order) {
            // Get order items
            $stmt = $pdo->prepare("
                SELECT oi.*, p.name as product_name, p.image as product_image
                FROM placed_orders oi
                JOIN products p ON oi.product_id = p.id
                WHERE oi.order_id = ?
            ");
            $stmt->execute([$order_id]);
            $order_items = $stmt->fetchAll();
            
            // Format order details similar to the log format
            $order_details = [
                'order_id' => $order['id'],
                'user_id' => $order['user_id'],
                'total_amount' => $order['total_amount'],
                'payment_method' => $order['payment_method'],
                'delivery_address' => $order['delivery_address'],
                'order_date' => $order['order_date'],
                'customer_name' => $order['customer_name'],
                'customer_email' => $order['customer_email'],
                'cart_items' => [],
                'cart_total' => $order['total_amount']
            ];
            
            // Convert order items to cart items format
            foreach ($order_items as $item) {
                $order_details['cart_items'][] = [
                    'id' => $item['product_id'],
                    'name' => $item['product_name'],
                    'price' => floatval($item['price']),
                    'quantity' => intval($item['quantity']),
                    'image' => $item['product_image']
                ];
            }
        }
    } catch (PDOException $e) {
        error_log("Order tracking error: " . $e->getMessage());
    }
}

// Fallback to log file for backward compatibility
if (!$order_details && $order_id && file_exists('orders.log')) {
    $log_lines = file('orders.log', FILE_IGNORE_NEW_LINES);
    foreach ($log_lines as $line) {
        $order_data = json_decode($line, true);
        if ($order_data && $order_data['order_id'] === $order_id && $order_data['user_id'] == $user_id) {
            $order_details = $order_data;
            break;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Tracking | Cherry Charms</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;500;700&family=Playfair+Display:wght@400;700&display=swap" rel="stylesheet">
    <style>
        .tracking-main {
            padding: 2rem 0;
            min-height: 60vh;
            background: #f8f9fa;
        }
        .tracking-container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 0 1rem;
        }
        .tracking-title {
            font-family: 'Playfair Display', serif;
            font-size: 2.5rem;
            margin-bottom: 2rem;
            text-align: center;
            color: #003152;
        }
        .order-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
            margin-bottom: 2rem;
        }
        .order-header {
            background: linear-gradient(135deg, #d81b60, #c2185b);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        .order-id {
            font-size: 1.5rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }
        .order-date {
            opacity: 0.9;
        }
        .order-status {
            display: inline-block;
            background: rgba(255,255,255,0.2);
            padding: 0.5rem 1rem;
            border-radius: 20px;
            margin-top: 1rem;
            font-weight: 500;
        }
        .order-content {
            padding: 2rem;
        }
        .order-section {
            margin-bottom: 2rem;
        }
        .order-section h3 {
            font-family: 'Montserrat', sans-serif;
            font-weight: 700;
            margin-bottom: 1rem;
            color: #003152;
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 0.5rem;
        }
        .order-items {
            display: grid;
            gap: 1rem;
        }
        .order-item {
            display: flex;
            align-items: center;
            padding: 1rem;
            border: 1px solid #f0f0f0;
            border-radius: 5px;
            transition: all 0.3s ease;
        }
        .order-item:hover {
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .order-item-img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 5px;
            margin-right: 1rem;
        }
        .order-item-details {
            flex: 1;
        }
        .order-item-name {
            font-weight: 500;
            margin-bottom: 0.25rem;
            color: #003152;
        }
        .order-item-price {
            color: #666;
            font-size: 0.9rem;
        }
        .order-item-qty {
            color: #666;
            font-size: 0.9rem;
        }
        .order-item-total {
            font-weight: bold;
            color: #d81b60;
        }
        .order-summary {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 5px;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
        }
        .summary-row.final {
            font-weight: bold;
            font-size: 1.2rem;
            color: #d81b60;
            border-top: 1px solid #ddd;
            padding-top: 0.5rem;
            margin-top: 0.5rem;
        }
        .shipping-info, .payment-info {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 5px;
            margin-top: 1rem;
        }
        .info-row {
            display: flex;
            margin-bottom: 0.5rem;
        }
        .info-label {
            font-weight: 500;
            width: 120px;
            color: #666;
        }
        .info-value {
            flex: 1;
            color: #333;
        }
        .tracking-timeline {
            margin-top: 2rem;
        }
        .timeline-item {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
            padding: 1rem;
            background: white;
            border-radius: 5px;
            border-left: 4px solid #d81b60;
        }
        .timeline-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #d81b60;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
        }
        .timeline-content h4 {
            margin-bottom: 0.25rem;
            color: #003152;
        }
        .timeline-content p {
            color: #666;
            font-size: 0.9rem;
        }
        .back-btn {
            display: inline-block;
            padding: 0.8rem 1.5rem;
            background: #003152;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-bottom: 2rem;
            transition: background 0.3s ease;
        }
        .back-btn:hover {
            background: #001f3f;
        }
        .not-found {
            text-align: center;
            padding: 3rem;
        }
        .not-found-icon {
            font-size: 4rem;
            color: #ddd;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body data-user-logged-in="<?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>" 
      data-user-id="<?php echo isset($_SESSION['user_id']) ? $_SESSION['user_id'] : ''; ?>">
    
    <?php include 'includes/header.php'; ?>
    
    <main class="tracking-main">
        <div class="tracking-container">
            <a href="index.php" class="back-btn">
                <i class="fa fa-arrow-left"></i> Back to Home
            </a>
            
            <h1 class="tracking-title">Order Tracking</h1>
            
            <?php if ($order_details): ?>
                <div class="order-card">
                    <div class="order-header">
                        <div class="order-id"><?php echo htmlspecialchars($order_details['order_id']); ?></div>
                        <div class="order-date"><?php echo date('F j, Y \a\t g:i A', strtotime($order_details['order_date'] ?? $order_details['timestamp'] ?? 'now')); ?></div>
                        <div class="order-status">Order Confirmed</div>
                    </div>
                    
                    <div class="order-content">
                        <div class="order-section">
                            <h3>Order Items</h3>
                            <div class="order-items">
                                <?php foreach ($order_details['cart_items'] as $item): ?>
                                    <div class="order-item">
                                        <img src="assets/<?php echo getCategoryFolderFromImage($item['image']); ?>/<?php echo htmlspecialchars($item['image']); ?>" 
                                             alt="<?php echo htmlspecialchars($item['name']); ?>" 
                                             class="order-item-img"
                                             onerror="this.src='https://via.placeholder.com/80x80?text=Product'">
                                        <div class="order-item-details">
                                            <div class="order-item-name"><?php echo htmlspecialchars($item['name']); ?></div>
                                            <div class="order-item-price">₹<?php echo number_format($item['price'] * 83, 2); ?></div>
                                            <div class="order-item-qty">Quantity: <?php echo $item['quantity']; ?></div>
                                        </div>
                                        <div class="order-item-total">
                                            ₹<?php echo number_format(($item['price'] * $item['quantity']) * 83, 2); ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        
                        <div class="order-section">
                            <h3>Order Summary</h3>
                            <div class="order-summary">
                                <div class="summary-row">
                                    <span>Subtotal:</span>
                                    <span>₹<?php echo number_format($order_details['cart_total'] * 83, 2); ?></span>
                                </div>
                                <div class="summary-row">
                                    <span>Shipping:</span>
                                    <span>₹0.00</span>
                                </div>
                                <div class="summary-row">
                                    <span>Tax:</span>
                                    <span>₹<?php echo number_format(($order_details['cart_total'] * 0.08) * 83, 2); ?></span>
                                </div>
                                <div class="summary-row final">
                                    <span>Total:</span>
                                    <span>₹<?php echo number_format(($order_details['cart_total'] * 1.08) * 83, 2); ?></span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="order-section">
                            <h3>Shipping Information</h3>
                            <div class="shipping-info">
                                <div class="info-row">
                                    <div class="info-label">Customer:</div>
                                    <div class="info-value">
                                        <?php echo htmlspecialchars($order_details['customer_name'] ?? 'N/A'); ?>
                                    </div>
                                </div>
                                <div class="info-row">
                                    <div class="info-label">Email:</div>
                                    <div class="info-value"><?php echo htmlspecialchars($order_details['customer_email'] ?? $order_details['shipping_info']['email'] ?? 'N/A'); ?></div>
                                </div>
                                <?php if (isset($order_details['shipping_info']['phone'])): ?>
                                <div class="info-row">
                                    <div class="info-label">Phone:</div>
                                    <div class="info-value"><?php echo htmlspecialchars($order_details['shipping_info']['phone']); ?></div>
                                </div>
                                <?php endif; ?>
                                <div class="info-row">
                                    <div class="info-label">Address:</div>
                                    <div class="info-value">
                                        <?php 
                                        if (isset($order_details['delivery_address'])) {
                                            echo htmlspecialchars($order_details['delivery_address']);
                                        } elseif (isset($order_details['shipping_info']['address'])) {
                                            echo htmlspecialchars($order_details['shipping_info']['address']) . '<br>';
                                            echo htmlspecialchars($order_details['shipping_info']['city'] . ', ' . $order_details['shipping_info']['state'] . ' ' . $order_details['shipping_info']['zip']);
                                        } else {
                                            echo 'N/A';
                                        }
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="order-section">
                            <h3>Payment Information</h3>
                            <div class="payment-info">
                                <div class="info-row">
                                    <div class="info-label">Payment Method:</div>
                                    <div class="info-value"><?php echo htmlspecialchars(ucfirst($order_details['payment_method'] ?? 'N/A')); ?></div>
                                </div>
                                <?php if (isset($order_details['payment_info']['card_number'])): ?>
                                <div class="info-row">
                                    <div class="info-label">Card:</div>
                                    <div class="info-value"><?php echo htmlspecialchars($order_details['payment_info']['card_number']); ?></div>
                                </div>
                                <?php endif; ?>
                                <?php if (isset($order_details['payment_info']['card_name'])): ?>
                                <div class="info-row">
                                    <div class="info-label">Name on Card:</div>
                                    <div class="info-value"><?php echo htmlspecialchars($order_details['payment_info']['card_name']); ?></div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="order-section">
                            <h3>Order Timeline</h3>
                            <div class="tracking-timeline">
                                <div class="timeline-item">
                                    <div class="timeline-icon">
                                        <i class="fa fa-check"></i>
                                    </div>
                                    <div class="timeline-content">
                                        <h4>Order Confirmed</h4>
                                        <p><?php echo date('F j, Y \a\t g:i A', strtotime($order_details['order_date'] ?? $order_details['timestamp'] ?? 'now')); ?></p>
                                    </div>
                                </div>
                                <div class="timeline-item">
                                    <div class="timeline-icon">
                                        <i class="fa fa-cog"></i>
                                    </div>
                                    <div class="timeline-content">
                                        <h4>Processing</h4>
                                        <p>Your order is being prepared for shipment</p>
                                    </div>
                                </div>
                                <div class="timeline-item">
                                    <div class="timeline-icon">
                                        <i class="fa fa-truck"></i>
                                    </div>
                                    <div class="timeline-content">
                                        <h4>Shipped</h4>
                                        <p>Estimated delivery: 3-5 business days</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="not-found">
                    <div class="not-found-icon">
                        <i class="fa fa-search"></i>
                    </div>
                    <h2>Order Not Found</h2>
                    <p>The order you're looking for doesn't exist or you don't have permission to view it.</p>
                    <a href="index.php" class="back-btn">Continue Shopping</a>
                </div>
            <?php endif; ?>
        </div>
    </main>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html> 