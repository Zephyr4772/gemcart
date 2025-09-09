<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Get user details
try {
    $stmt = $pdo->prepare("SELECT id, name, email, created_at FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    
    if (!$user) {
        header('Location: logout.php');
        exit();
    }
} catch (PDOException $e) {
    $error = "Error fetching user details.";
}

// Get user's cart items
try {
    $stmt = $pdo->prepare("
        SELECT c.quantity, p.name, p.price, p.image, p.id as product_id
        FROM cart c 
        JOIN products p ON c.product_id = p.id 
        WHERE c.user_id = ?
    ");
    $stmt->execute([$user_id]);
    $cart_items = $stmt->fetchAll();
    
    $cart_total = 0;
    foreach ($cart_items as $item) {
        $cart_total += $item['price'] * $item['quantity'];
    }
} catch (PDOException $e) {
    $cart_items = [];
    $cart_total = 0;
    $error = "Error fetching cart items.";
}

// Get user's order history with order items
try {
    $stmt = $pdo->prepare("
        SELECT o.id, o.total_amount, o.payment_method, o.delivery_address, o.order_date,
               oi.product_id, oi.quantity, oi.price, p.name as product_name
        FROM orders o
        LEFT JOIN order_items oi ON o.id = oi.order_id
        LEFT JOIN products p ON oi.product_id = p.id
        WHERE o.user_id = ?
        ORDER BY o.order_date DESC, o.id DESC
    ");
    $stmt->execute([$user_id]);
    $order_results = $stmt->fetchAll();
    
    // Group orders by order ID
    $orders = [];
    foreach ($order_results as $row) {
        $order_id = $row['id'];
        if (!isset($orders[$order_id])) {
            $orders[$order_id] = [
                'id' => $row['id'],
                'total_amount' => $row['total_amount'],
                'payment_method' => $row['payment_method'],
                'delivery_address' => $row['delivery_address'],
                'order_date' => $row['order_date'],
                'items' => []
            ];
        }
        
        // Add item details if they exist
        if ($row['product_id']) {
            $orders[$order_id]['items'][] = [
                'product_id' => $row['product_id'],
                'product_name' => $row['product_name'],
                'quantity' => $row['quantity'],
                'price' => $row['price']
            ];
        }
    }
    
    // Re-index array to have sequential keys
    $orders = array_values($orders);
} catch (PDOException $e) {
    $orders = [];
    $error = "Error fetching order history.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Account | Cherry Charms</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;500;700&family=Playfair+Display:wght@400;700&display=swap" rel="stylesheet">
    <style>
        .account-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
        }
        .account-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .account-header h1 {
            font-family: 'Playfair Display', serif;
            font-size: 2.5rem;
            color: #003152;
            margin-bottom: 0.5rem;
        }
        .account-grid {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 2rem;
        }
        @media (max-width: 768px) {
            .account-grid {
                grid-template-columns: 1fr;
            }
        }
        .account-sidebar {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 1.5rem;
        }
        .account-sidebar h2 {
            font-family: 'Montserrat', sans-serif;
            font-weight: 700;
            margin-bottom: 1rem;
            color: #003152;
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 0.5rem;
        }
        .account-sidebar ul {
            list-style: none;
            padding: 0;
        }
        .account-sidebar ul li {
            margin-bottom: 0.5rem;
        }
        .account-sidebar ul li a {
            text-decoration: none;
            color: #333;
            display: block;
            padding: 0.5rem;
            border-radius: 5px;
            transition: all 0.3s ease;
        }
        .account-sidebar ul li a:hover,
        .account-sidebar ul li a.active {
            background: #f8f9fa;
            color: #d81b60;
        }
        .account-content {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 1.5rem;
        }
        .account-section {
            margin-bottom: 2rem;
        }
        .account-section h2 {
            font-family: 'Montserrat', sans-serif;
            font-weight: 700;
            margin-bottom: 1rem;
            color: #003152;
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 0.5rem;
        }
        .user-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
        .detail-item {
            margin-bottom: 1rem;
        }
        .detail-label {
            font-weight: 700;
            color: #003152;
        }
        .detail-value {
            color: #333;
        }
        .cart-item, .order-item {
            display: flex;
            align-items: center;
            padding: 1rem 0;
            border-bottom: 1px solid #f0f0f0;
        }
        .cart-item:last-child, .order-item:last-child {
            border-bottom: none;
        }
        .cart-item-img, .order-item-img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 5px;
            margin-right: 1rem;
        }
        .cart-item-details, .order-item-details {
            flex: 1;
        }
        .cart-item-name, .order-item-name {
            font-weight: 500;
            margin-bottom: 0.25rem;
        }
        .cart-item-price, .order-item-price {
            color: #666;
            font-size: 0.9rem;
        }
        .cart-item-qty, .order-item-qty {
            color: #666;
            font-size: 0.9rem;
        }
        .cart-item-total, .order-item-total {
            font-weight: 700;
            color: #d81b60;
        }
        .cart-summary {
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 2px solid #f0f0f0;
            text-align: right;
        }
        .cart-total {
            font-size: 1.2rem;
            font-weight: 700;
            color: #d81b60;
        }
        .btn {
            display: inline-block;
            padding: 0.5rem 1rem;
            background: #d81b60;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background 0.3s ease;
        }
        .btn:hover {
            background: #c2185b;
        }
        .btn-secondary {
            background: #f5f5f5;
            color: #333;
        }
        .btn-secondary:hover {
            background: #e0e0e0;
        }
        .order-details {
            background: #f8f9fa;
            border-radius: 5px;
            padding: 1rem;
            margin-top: 1rem;
        }
        .order-items-summary {
            margin-top: 0.5rem;
        }
        .order-item-row {
            display: flex;
            justify-content: space-between;
            padding: 0.25rem 0;
        }
        .order-total {
            font-weight: 700;
            color: #d81b60;
            border-top: 1px solid #ddd;
            margin-top: 0.5rem;
            padding-top: 0.5rem;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="account-container">
        <div class="account-header">
            <h1>My Account</h1>
            <p>Welcome back, <?php echo htmlspecialchars($user['name']); ?>!</p>
        </div>
        
        <div class="account-grid">
            <div class="account-sidebar">
                <h2>Account Menu</h2>
                <ul>
                    <li><a href="#" class="active">Dashboard</a></li>
                    <li><a href="cart_client.php">My Cart</a></li>
                    <li><a href="#orders">My Orders</a></li>
                    <li><a href="logout.php">Logout</a></li>
                </ul>
            </div>
            
            <div class="account-content">
                <?php if (isset($error)): ?>
                    <div class="error-message"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <div class="account-section">
                    <h2>Personal Information</h2>
                    <div class="user-details">
                        <div class="detail-item">
                            <div class="detail-label">Full Name</div>
                            <div class="detail-value"><?php echo htmlspecialchars($user['name']); ?></div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Email Address</div>
                            <div class="detail-value"><?php echo htmlspecialchars($user['email']); ?></div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Member Since</div>
                            <div class="detail-value"><?php echo date('F j, Y', strtotime($user['created_at'])); ?></div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Account ID</div>
                            <div class="detail-value"><?php echo $user['id']; ?></div>
                        </div>
                    </div>
                </div>
                
                <div class="account-section">
                    <h2>My Cart (<?php echo count($cart_items); ?> items)</h2>
                    <?php if (empty($cart_items)): ?>
                        <p>Your cart is empty. <a href="products.php">Browse products</a> to add items!</p>
                    <?php else: ?>
                        <?php foreach ($cart_items as $item): ?>
                            <div class="cart-item">
                                <img src="https://via.placeholder.com/60x60?text=<?php echo urlencode($item['name']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="cart-item-img">
                                <div class="cart-item-details">
                                    <div class="cart-item-name"><?php echo htmlspecialchars($item['name']); ?></div>
                                    <div class="cart-item-price">₹<?php echo number_format($item['price'] * 83, 2); ?></div>
                                    <div class="cart-item-qty">Quantity: <?php echo $item['quantity']; ?></div>
                                </div>
                                <div class="cart-item-total">₹<?php echo number_format(($item['price'] * $item['quantity']) * 83, 2); ?></div>
                            </div>
                        <?php endforeach; ?>
                        <div class="cart-summary">
                            <div class="cart-total">Total: ₹<?php echo number_format($cart_total * 83, 2); ?></div>
                            <div style="margin-top: 1rem;">
                                <a href="cart_client.php" class="btn btn-secondary">View Cart</a>
                                <a href="checkout.php" class="btn" style="margin-left: 0.5rem;">Checkout</a>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="account-section" id="orders">
                    <h2>Order History</h2>
                    <?php if (empty($orders)): ?>
                        <p>You haven't placed any orders yet.</p>
                    <?php else: ?>
                        <?php foreach ($orders as $order): ?>
                            <div class="order-item">
                                <div class="order-item-details">
                                    <div class="order-item-name">Order #<?php echo $order['id']; ?></div>
                                    <div class="order-item-price">₹<?php echo number_format($order['total_amount'] * 83, 2); ?></div>
                                    <div class="order-item-qty">Payment: <?php echo ucfirst($order['payment_method']); ?></div>
                                    <div class="order-item-qty">Date: <?php echo date('M j, Y g:i A', strtotime($order['order_date'])); ?></div>
                                    <?php if (!empty($order['items'])): ?>
                                        <div class="order-details">
                                            <div class="order-items-summary">
                                                <?php 
                                                $item_count = 0;
                                                foreach ($order['items'] as $item) {
                                                    $item_count += $item['quantity'];
                                                }
                                                echo $item_count . ' item' . ($item_count != 1 ? 's' : '');
                                                ?> - 
                                                <?php 
                                                $item_names = [];
                                                foreach ($order['items'] as $item) {
                                                    $item_names[] = $item['quantity'] . ' x ' . $item['product_name'];
                                                }
                                                echo implode(', ', array_slice($item_names, 0, 2));
                                                if (count($item_names) > 2) {
                                                    echo ' and ' . (count($item_names) - 2) . ' more';
                                                }
                                                ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="order-item-total">View Details</div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>