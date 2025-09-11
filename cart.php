<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?redirect=cart');
    exit();
}

$user_id = $_SESSION['user_id'];

// Handle cart updates and removals
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['remove'])) {
        $product_id = intval($_POST['remove']);
        $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
        $stmt->bind_param("ii", $user_id, $product_id);
        $stmt->execute();
        $_SESSION['message'] = "Item removed from cart";
    }
    
    if (isset($_POST['update']) && isset($_POST['quantities'])) {
        foreach ($_POST['quantities'] as $product_id => $qty) {
            $product_id = intval($product_id);
            $qty = max(1, intval($qty));
            
            $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?");
            $stmt->bind_param("iii", $qty, $user_id, $product_id);
            $stmt->execute();
        }
        $_SESSION['message'] = "Cart updated successfully";
    }
    
    header('Location: cart.php');
    exit();
}

// Get cart products with details
$cart_products = [];
$cart_total = 0;

$query = "SELECT p.id, p.name, p.price, c.quantity, (p.price * c.quantity) as subtotal
          FROM cart c
          JOIN products p ON c.product_id = p.id
          WHERE c.user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $cart_products[] = $row;
    $cart_total += $row['subtotal'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cart | Cherry Charms</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;500;700&family=Playfair+Display:wght@400;700&display=swap" rel="stylesheet">
    <style>
        .cart-main {
            padding: 2rem 0;
            min-height: 60vh;
        }
        .cart-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1rem;
        }
        .cart-title {
            font-family: 'Playfair Display', serif;
            font-size: 2.5rem;
            margin-bottom: 2rem;
            text-align: center;
        }
        .cart-empty {
            text-align: center;
            font-size: 1.2rem;
            padding: 2rem;
        }
        .cart-empty a {
            color: #d81b60;
            text-decoration: none;
        }
        .cart-empty a:hover {
            text-decoration: underline;
        }
        .cart-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 2rem;
        }
        .cart-table th, .cart-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        .cart-table th {
            font-family: 'Montserrat', sans-serif;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.9rem;
        }
        .cart-qty-input {
            width: 60px;
            padding: 0.5rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            text-align: center;
        }
        .cart-remove-btn {
            background: none;
            border: none;
            color: #d81b60;
            font-size: 1.5rem;
            cursor: pointer;
            padding: 0 0.5rem;
        }
        .cart-summary {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 0;
            border-top: 2px solid #eee;
        }
        .cart-total {
            font-family: 'Montserrat', sans-serif;
            font-weight: 700;
            font-size: 1.2rem;
        }
        .cart-total span {
            color: #d81b60;
        }
        .cart-update-btn, .cart-checkout-btn {
            padding: 0.8rem 1.5rem;
            border: none;
            border-radius: 4px;
            font-family: 'Montserrat', sans-serif;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .cart-update-btn {
            background-color: #f5f5f5;
            color: #333;
            margin-right: 1rem;
        }
        .cart-update-btn:hover {
            background-color: #e0e0e0;
        }
        .cart-checkout-btn {
            background-color: #d81b60;
            color: white;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }
        .cart-checkout-btn:hover {
            background-color: #c2185b;
        }
        .message {
            padding: 1rem;
            margin-bottom: 1rem;
            background-color: #4caf50;
            color: white;
            border-radius: 4px;
            text-align: center;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
   
    
    <main class="cart-main">
        <div class="cart-container">
            <h1 class="cart-title">Your Cart</h1>
            
            <?php if (isset($_SESSION['message'])): ?>
                <div class="message"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></div>
            <?php endif; ?>
            
            <?php if (empty($cart_products)): ?>
                <div class="cart-empty">
                    Your cart is empty. <a href="products.php">Browse products</a> to add items!
                </div>
            <?php else: ?>
                <form action="cart.php" method="post">
                    <table class="cart-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Price</th>
                                <th>Quantity</th>
                                <th>Subtotal</th>
                                <th>Remove</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cart_products as $product): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($product['name']); ?></td>
                                <td>₹<?php echo number_format($product['price'] * 83, 2); ?></td>
                                <td>
                                    <input type="number" 
                                           name="quantities[<?php echo $product['id']; ?>]" 
                                           value="<?php echo $product['quantity']; ?>" 
                                           min="1" 
                                           class="cart-qty-input">
                                </td>
                                <td>₹<?php echo number_format($product['subtotal'] * 83, 2); ?></td>
                                <td>
                                    <button type="submit" 
                                            name="remove" 
                                            value="<?php echo $product['id']; ?>" 
                                            class="cart-remove-btn"
                                            onclick="return confirm('Are you sure you want to remove this item?')">
                                        &times;
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    
                    <div class="cart-summary">
                        <div class="cart-total">
                            Total: <span>₹<?php echo number_format($cart_total * 83, 2); ?></span>
                        </div>
                        <div>
                            <button type="submit" name="update" class="cart-update-btn">Update Cart</button>
                            <a href="checkout.php" class="cart-checkout-btn">Checkout</a>
                        </div>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </main>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>