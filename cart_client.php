<?php
// Start the session at the absolute very beginning of the script.
// Ensure there is NO whitespace or other content before this tag.
session_start();

// Include necessary files BEFORE any HTML output.
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Require user to be logged in to view cart
if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_after_login'] = 'cart_client.php';
    header('Location: login.php');
    exit();
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
    <script src="js/cart.js"></script>
    <style>
        /* Your cart-specific CSS styles */
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
        .cart-img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 4px;
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
        .cart-remove-btn:hover {
            text-decoration: underline; /* Added subtle underline on hover */
        }
        .cart-summary {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 0;
            border-top: 2px solid #eee;
            flex-wrap: wrap; /* Added for responsiveness */
        }
        .cart-total {
            font-family: 'Montserrat', sans-serif;
            font-weight: 700;
            font-size: 1.2rem;
        }
        .cart-total span {
            color: #d81b60;
        }
        .cart-update-btn, .cart-checkout-btn, .cart-clear-btn { /* Combined styles */
            padding: 0.8rem 1.5rem;
            border: none;
            border-radius: 4px;
            font-family: 'Montserrat', sans-serif;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none; /* Ensure no underline */
            display: inline-block; /* Ensure block-level padding/margin */
            text-align: center;
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
            /* margin-right: 1rem; Removed as it's the last button */
        }
        .cart-checkout-btn:hover {
            background-color: #c2185b;
        }
        .cart-clear-btn {
            background-color: #f44336;
            color: white;
            margin-right: 1rem;
        }
        .cart-clear-btn:hover {
            background-color: #d32f2f;
        }
        .message { /* This style is for alerts, usually handled by displayMessage() */
            padding: 1rem;
            margin-bottom: 1rem;
            background-color: #4caf50;
            color: white;
            border-radius: 4px;
            text-align: center;
        }
        /* Cart count styles in header.php, but re-confirming for this page if standalone */
        .cart-count {
            position: absolute;
            top: -5px; /* Adjusted slightly from original -8px */
            right: -5px; /* Adjusted slightly from original -8px */
            background: #d81b60;
            color: white;
            border-radius: 50%;
            width: 18px; /* Adjusted from 20px */
            height: 18px; /* Adjusted from 20px */
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem; /* Adjusted from 12px for consistency */
            font-weight: bold;
        }
        .cart-link {
            position: relative;
        }

        /* Responsive adjustments for cart page */
        @media (max-width: 768px) {
            .cart-table th, .cart-table td {
                padding: 0.7rem 0.5rem;
                font-size: 0.9rem;
            }
            .cart-img {
                width: 60px;
                height: 60px;
            }
            .cart-qty-input {
                width: 50px;
                padding: 0.3rem;
                font-size: 0.9rem;
            }
            .cart-remove-btn {
                font-size: 1.2rem;
                padding: 0 0.3rem;
            }
            .cart-summary {
                flex-direction: column;
                align-items: stretch; /* Stretch items to fill width */
                gap: 1rem;
            }
            .cart-update-btn, .cart-checkout-btn, .cart-clear-btn {
                width: 100%; /* Make buttons full width */
                margin-right: 0;
                margin-bottom: 0.5rem; /* Add space between stacked buttons */
            }
            .cart-update-btn:last-child, .cart-checkout-btn:last-child, .cart-clear-btn:last-child {
                margin-bottom: 0;
            }
        }
    </style>
</head>
<body data-user-logged-in="<?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>" 
      data-user-id="<?php echo isset($_SESSION['user_id']) ? $_SESSION['user_id'] : ''; ?>">
    
    <?php include 'includes/header.php'; ?>
    
    <main class="cart-main">
        <div class="cart-container">
            <h1 class="cart-title">Your Cart</h1>
            
            <div id="cart-content">
                </div>
        </div>
    </main>
    
    <?php include 'includes/footer.php'; ?>
    
    <script>
        // Function to determine category folder based on image name
        function getCategoryFolder(imageName) {
            // Extract category abbreviation from image name (e.g., 1-rng-g.jpg -> rings)
            if (imageName.includes('-rng')) return 'rings';
            if (imageName.includes('-nck')) return 'necklaces';
            if (imageName.includes('-erg')) return 'earrings';
            if (imageName.includes('-brl')) return 'bracelets';
            if (imageName.includes('-wch')) return 'watches';
            return 'products'; // default
        }
        
        // Render cart contents
        function renderCart() {
            if (!window.cartManager) {
                console.error("Cart Manager not available");
                return;
            }
            
            const cartItems = window.cartManager.getCartItems();
            const cartTotal = window.cartManager.getCartTotal();
            const cartContent = document.getElementById('cart-content');
            
            if (cartItems.length === 0) {
                cartContent.innerHTML = `
                    <div class="cart-empty">
                        Your cart is empty. <a href="products.php">Browse products</a> to add items!
                    </div>
                `;
                return;
            }
            
            let cartHTML = `
                <table class="cart-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Name</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Subtotal</th>
                            <th>Remove</th>
                        </tr>
                    </thead>
                    <tbody>
            `;
            
            cartItems.forEach(item => {
                const subtotal = item.price * item.quantity * 83; // Convert to Rupees
                const price = item.price * 83; // Convert to Rupees
                const imageSrc = item.image ? 
                    `https://via.placeholder.com/300x220?text=${encodeURIComponent(item.name)}` : 
                    'https://via.placeholder.com/80x80?text=No+Image';
                
                cartHTML += `
                    <tr>
                        <td>
                            <img src="${imageSrc}" alt="${item.name}" class="cart-img">
                        </td>
                        <td>${item.name}</td>
                        <td>₹${price.toFixed(2)}</td>
                        <td>
                            <input type="number" 
                                   class="cart-qty-input" 
                                   value="${item.quantity}" 
                                   min="1" 
                                   onchange="updateQuantity(${item.id}, this.value)">
                        </td>
                        <td>₹${subtotal.toFixed(2)}</td>
                        <td>
                            <button class="cart-remove-btn" onclick="removeFromCart(${item.id})">&times;</button>
                        </td>
                    </tr>
                `;
            });
            
            cartHTML += `
                    </tbody>
                </table>
                <div class="cart-summary">
                    <div class="cart-total">
                        Total: <span>₹${cartTotal.toFixed(2)}</span>
                    </div>
                    <div>
                        <button class="cart-clear-btn" onclick="clearCart()">Clear Cart</button>
                        <a href="checkout.php" class="cart-checkout-btn">Checkout</a>
                    </div>
                </div>
            `;
            
            cartContent.innerHTML = cartHTML;
        }
        
        function updateQuantity(productId, quantity) {
            // Ensure quantity is a number and at least 1
            quantity = parseInt(quantity);
            if (isNaN(quantity) || quantity < 1) {
                quantity = 1;
            }
            window.cartManager.updateQuantity(productId, quantity);
            renderCart();
        }
        
        function removeFromCart(productId) {
            if (confirm('Are you sure you want to remove this item?')) {
                window.cartManager.removeFromCart(productId);
                renderCart();
            }
        }
        
        function clearCart() {
            if (confirm('Are you sure you want to clear your entire cart?')) {
                window.cartManager.clearCart();
                renderCart();
            }
        }
        
        // Initialize cart display when page loads
        document.addEventListener('DOMContentLoaded', function() {
            // Wait for cartManager to be initialized by cart.js
            // A short timeout can help ensure cart.js has loaded and initialized window.cartManager
            setTimeout(() => {
                if (window.cartManager) {
                    renderCart();
                } else {
                    console.error("Cart Manager (from cart.js) is not available.");
                    // Display an error message to the user if cartManager isn't found
                    document.getElementById('cart-content').innerHTML = `
                        <div class="alert alert-error">
                            Error loading cart. Please ensure JavaScript is enabled and try refreshing the page.
                        </div>
                    `;
                }
            }, 100); // 100ms delay
        });
    </script>
</body>
</html>