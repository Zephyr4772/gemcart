<?php
// Start the session at the absolute very beginning of the script.
session_start();

// Include necessary files BEFORE any HTML or other output.
require_once 'includes/db.php';
require_once 'includes/functions.php';

// IMPORTANT: Perform the login check and redirect BEFORE any HTML is sent to the browser.
if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_after_login'] = 'checkout.php'; // Store current page to redirect back after login
    header('Location: login.php'); // Redirect to your login page (adjust path if necessary)
    exit(); // Crucial: Stop script execution immediately after sending the redirect header.
}

// The rest of the PHP code for checkout logic (if any specific to this page) goes here.
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout | Cherry Charms</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;500;700&family=Playfair+Display:wght@400;700&display=swap" rel="stylesheet">
    <script src="js/cart.js"></script>
    <style>
        /* Your CSS styles for checkout (as provided previously) */
        .checkout-main {
            padding: 2rem 0;
            min-height: 60vh;
            background: #f8f9fa;
        }
        .checkout-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1rem;
        }
        .checkout-title {
            font-family: 'Playfair Display', serif;
            font-size: 2.5rem;
            margin-bottom: 2rem;
            text-align: center;
            color: #003152;
        }
        .checkout-steps {
            display: flex;
            justify-content: center;
            margin-bottom: 3rem;
            position: relative;
        }
        
        .checkout-steps::before {
            content: '';
            position: absolute;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            width: 60%;
            height: 2px;
            background: #ddd;
            z-index: -1;
        }
        .step {
            display: flex;
            align-items: center;
            margin: 0 1rem;
            position: relative;
        }
        .step-number {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #ddd;
            color: #666;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-right: 0.5rem;
            transition: all 0.3s ease;
        }
        .step.active .step-number {
            background: #d81b60;
            color: white;
        }
        .step.completed .step-number {
            background: #4CAF50;
            color: white;
        }
        .step-label {
            font-weight: 500;
            color: #666;
        }
        .step.active .step-label {
            color: #d81b60;
        }
        .step.completed .step-label {
            color: #4CAF50;
        }
        .checkout-content {
            display: flex;
            gap: 2rem;
            align-items: flex-start;
        }
        .checkout-form {
            flex: 2;
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .order-summary {
            flex: 1;
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: sticky;
            top: 2rem;
        }
        .form-section {
            margin-bottom: 2rem;
        }
        .form-section h3 {
            font-family: 'Montserrat', sans-serif;
            font-weight: 700;
            margin-bottom: 1rem;
            color: #003152;
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 0.5rem;
        }
        .form-row {
            display: flex;
            gap: 1rem;
            margin-bottom: 1rem;
        }
        .form-group {
            flex: 1;
        }
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #333;
        }
        .form-group input, .form-group select, .form-group textarea {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus {
            outline: none;
            border-color: #d81b60;
        }
        .card-input {
            position: relative;
        }
        .card-input input {
            padding-left: 2.5rem;
        }
        .card-icon {
            position: absolute;
            left: 0.8rem;
            top: 50%;
            transform: translateY(-50%);
            color: #666;
        }
        .order-items {
            margin-bottom: 1.5rem;
        }
        .order-item {
            display: flex;
            align-items: center;
            padding: 1rem 0;
            border-bottom: 1px solid #f0f0f0;
            transition: background-color 0.3s ease;
        }
        
        .order-item:hover {
            background-color: #f8f9fa;
            border-radius: 5px;
            padding: 1rem;
            margin: 0 -1rem;
        }
        .order-item:last-child {
            border-bottom: none;
        }
        .order-item-img {
            width: 60px;
            height: 60px;
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
        }
        .order-item-price {
            color: #666;
            font-size: 0.9rem;
        }
        .order-item-qty {
            color: #666;
            font-size: 0.9rem;
        }
        .order-totals {
            border-top: 2px solid #f0f0f0;
            padding-top: 1rem;
        }
        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
        }
        .total-row.final {
            font-weight: bold;
            font-size: 1.2rem;
            color: #d81b60;
            border-top: 1px solid #f0f0f0;
            padding-top: 0.5rem;
            margin-top: 0.5rem;
        }
        .checkout-buttons {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
        }
        .btn {
            padding: 1rem 2rem;
            border: none;
            border-radius: 5px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }
        .btn-primary {
            background: #d81b60;
            color: white;
            flex: 1;
        }
        .btn-primary:hover {
            background: #c2185b;
        }
        .btn-secondary {
            background: #f5f5f5;
            color: #333;
        }
        .btn-secondary:hover {
            background: #e0e0e0;
        }
        .step-content {
            display: none;
        }
        .step-content.active {
            display: block;
        }
        .success-message {
            text-align: center;
            padding: 3rem;
        }
        .success-icon {
            font-size: 4rem;
            color: #4CAF50;
            margin-bottom: 1rem;
        }
        .success-title {
            font-family: 'Playfair Display', serif;
            font-size: 2rem;
            margin-bottom: 1rem;
            color: #003152;
        }
        .success-details {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 10px;
            margin: 1.5rem 0;
        }
        .success-details h4 {
            margin-bottom: 1rem;
            color: #003152;
        }
        .success-details p {
            margin-bottom: 0.5rem;
        }
        .error-message {
            background: #f44336;
            color: white;
            padding: 1rem;
            border-radius: 5px;
            margin-bottom: 1rem;
        }
        .payment-toggle {
            position: relative;
        }
        .payment-toggle .btn {
            width: 100%;
            text-align: center;
            background: #f8f9fa;
            border: 1px solid #ddd;
            color: #333;
            padding: 0.8rem 1rem;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .payment-toggle .btn:hover {
            background: #e9ecef;
        }
        .payment-toggle .btn.active {
            background: #d81b60;
            color: white;
            border-color: #d81b60;
        }
        @media (max-width: 768px) {
            .checkout-content {
                flex-direction: column;
            }
            .form-row {
                flex-direction: column;
            }
            .checkout-steps {
                flex-direction: column;
                align-items: center;
            }
            .step {
                margin: 0.5rem 0;
            }
        }
    </style>
</head>
<body data-user-logged-in="<?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>" 
      data-user-id="<?php echo isset($_SESSION['user_id']) ? $_SESSION['user_id'] : ''; ?>">
    
    <?php include 'includes/header.php'; ?>
    
    <main class="checkout-main">
        <div class="checkout-container">
            <h1 class="checkout-title">Checkout</h1>
            
            <div class="checkout-steps">
                <div class="step active" id="step-1">
                    <div class="step-number">1</div>
                    <div class="step-label">Order Summary</div>
                </div>
                <div class="step" id="step-2">
                    <div class="step-number">2</div>
                    <div class="step-label">Order Placed</div>
                </div>
            </div>
            
            <div id="error-message" class="error-message" style="display: none;"></div>
            
            <div class="step-content active" id="step-1-content">
                <div class="checkout-content">
                    <div class="checkout-form">
                        <div class="form-section">
                            <h3>Delivery Information</h3>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="delivery-address">Delivery Address *</label>
                                    <textarea id="delivery-address" placeholder="Enter your full delivery address" rows="3" required></textarea>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="payment-method">Payment Method *</label>
                                    <div class="payment-toggle">
                                        <button type="button" id="payment-toggle" class="btn btn-secondary" onclick="togglePaymentMethod()">
                                            <span id="payment-method-text">Cash on Delivery</span>
                                        </button>
                                        <input type="hidden" id="payment-method" name="payment-method" value="cash">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-section">
                            <h3>Order Summary</h3>
                            <div id="order-summary-content">
                                </div>
                        </div>
                        <div class="checkout-buttons">
                            <a href="cart_client.php" class="btn btn-secondary">Back to Cart</a>
                            <button type="button" class="btn btn-primary" onclick="nextStep()">Place Order</button>
                        </div>
                    </div>
                    <div class="order-summary">
                        <h3>Order Summary</h3>
                        <div id="order-summary-sidebar">
                            </div>
                    </div>
                </div>
            </div>
            
            <div class="step-content" id="step-2-content">
                <div class="success-message">
                    <div class="success-icon">
                        <i class="fa fa-check-circle"></i>
                    </div>
                    <h2 class="success-title">Order Placed!</h2>
                    <p>Thank you for your purchase.</p>
                    <div class="checkout-buttons">
                        <a href="index.php" class="btn btn-primary">Continue Shopping</a>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <?php include 'includes/footer.php'; ?>
    
    <script>
        let currentStep = 1;
        let orderData = null;
        
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
        
        function loadOrderSummary() {
            const userLoggedIn = document.body.dataset.userLoggedIn === 'true';

            if (!userLoggedIn) {
                showError('You must be logged in to place an order.');
                document.querySelector('.btn-primary').disabled = true;
                return;
            }

            if (!window.cartManager) {
                showError('Cart manager not available');
                return;
            }
            
            const cartItems = window.cartManager.getCartItems();
            const cartTotal = window.cartManager.getCartTotal();
            
            if (cartItems.length === 0) {
                window.location.href = 'cart_client.php';
                return;
            }
            
            orderData = {
                items: cartItems,
                total: cartTotal,
                subtotal: cartTotal,
                shipping: 0,
                tax: cartTotal * 0.08, // 8% tax
                grandTotal: cartTotal * 1.08
            };
            
            renderOrderSummary();
        }
        
        function renderOrderSummary() {
            const mainContent = document.getElementById('order-summary-content');
            const sidebar1 = document.getElementById('order-summary-sidebar');
            
            let itemsHTML = '';
            orderData.items.forEach(item => {
                itemsHTML += `
                    <div class="order-item">
                        <img src="assets/${getCategoryFolder(item.image)}/${item.image}" alt="${item.name}" class="order-item-img" onerror="this.src='https://via.placeholder.com/60x60?text=Product'">
                        <div class="order-item-details">
                            <div class="order-item-name">${item.name}</div>
                            <div class="order-item-price">₹${(item.price * 83).toFixed(2)}</div>
                            <div class="order-item-qty">Quantity: ${item.quantity}</div>
                        </div>
                        <div class="order-item-total">
                            ₹${((item.price * item.quantity) * 83).toFixed(2)}
                        </div>
                    </div>
                `;
            });
            
            const summaryHTML = `
                <div class="order-items">
                    ${itemsHTML}
                </div>
                <div class="order-totals">
                    <div class="total-row">
                        <span>Subtotal:</span>
                        <span>₹${(orderData.subtotal * 83).toFixed(2)}</span>
                    </div>
                    <div class="total-row">
                        <span>Shipping:</span>
                        <span>₹${(orderData.shipping * 83).toFixed(2)}</span>
                    </div>
                    <div class="total-row">
                        <span>Tax:</span>
                        <span>₹${(orderData.tax * 83).toFixed(2)}</span>
                    </div>
                    <div class="total-row final">
                        <span>Total:</span>
                        <span>₹${(orderData.grandTotal * 83).toFixed(2)}</span>
                    </div>
                </div>
            `;
            
            mainContent.innerHTML = itemsHTML;
            sidebar1.innerHTML = summaryHTML;
        }
        
        function nextStep() {
            const userLoggedIn = document.body.dataset.userLoggedIn === 'true';
            if (!userLoggedIn) {
                showError('Please log in to proceed with your order.');
                return;
            }

            if (currentStep < 2) {
                // Process the order before moving to step 2
                processOrder();
            }
        }
        
        function processOrder() {
            const cartItems = window.cartManager.getCartItems();
            const cartTotal = window.cartManager.getCartTotal();
            const deliveryAddress = document.getElementById('delivery-address').value.trim();
            const paymentMethod = document.getElementById('payment-method').value;
            
            if (cartItems.length === 0) {
                showError('Your cart is empty.');
                return;
            }
            
            if (!deliveryAddress) {
                showError('Please enter a delivery address.');
                document.getElementById('delivery-address').focus();
                return;
            }
            
            if (!paymentMethod) {
                showError('Please select a payment method.');
                return;
            }
            
            // Show loading state
            const placeOrderBtn = document.querySelector('.btn-primary');
            const originalText = placeOrderBtn.textContent;
            placeOrderBtn.textContent = 'Processing...';
            placeOrderBtn.disabled = true;
            
            // Prepare order data
            const orderData = {
                cart_items: cartItems,
                payment_method: paymentMethod === 'cash' ? 'Cash on Delivery' : 'Online Payment',
                delivery_address: deliveryAddress
            };
            
            // Send order to server
            fetch('process_order.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(orderData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Clear cart on successful order
                    window.cartManager.clearCart();
                    
                    // Move to success step
                    document.getElementById(`step-${currentStep}-content`).classList.remove('active');
                    document.getElementById(`step-${currentStep}`).classList.remove('active');
                    document.getElementById(`step-${currentStep}`).classList.add('completed');
                    currentStep++;
                    document.getElementById(`step-${currentStep}-content`).classList.add('active');
                    document.getElementById(`step-${currentStep}`).classList.add('active');
                    
                    // Update success message with order details
                    const successMessage = document.querySelector('.success-message');
                    let successHTML = `
                        <div class="success-icon">
                            <i class="fa fa-check-circle"></i>
                        </div>
                        <h2 class="success-title">Order Placed Successfully!</h2>
                        <p>Thank you for your purchase.</p>
                        <div class="success-details">
                            <h4>Order Details:</h4>
                            <p><strong>Order ID:</strong> ${data.order_id}</p>
                            <p><strong>Total Amount:</strong> ₹${(data.total_amount * 83).toFixed(2)}</p>
                            <p><strong>Payment Method:</strong> ${orderData.payment_method}</p>
                    `;
                    
                    if (data.warning) {
                        successHTML += `<p><strong>Warning:</strong> ${data.warning}</p>`;
                    }
                    
                    successHTML += `
                            <p>You will receive a confirmation email shortly.</p>
                        </div>
                        <div class="checkout-buttons">
                            <a href="index.php" class="btn btn-primary">Continue Shopping</a>
                        </div>
                    `;
                    
                    successMessage.innerHTML = successHTML;
                } else {
                    showError(data.error || 'Failed to process order. Please try again.');
                    placeOrderBtn.textContent = originalText;
                    placeOrderBtn.disabled = false;
                }
            })
            .catch(error => {
                console.error('Order processing error:', error);
                showError('Failed to process order. Please try again.');
                placeOrderBtn.textContent = originalText;
                placeOrderBtn.disabled = false;
            });
        }
        
        function prevStep() {
            if (currentStep > 1) {
                document.getElementById(`step-${currentStep}-content`).classList.remove('active');
                document.getElementById(`step-${currentStep}`).classList.remove('active');
                
                currentStep--;
                
                document.getElementById(`step-${currentStep}-content`).classList.add('active');
                document.getElementById(`step-${currentStep}`).classList.add('active');
                document.getElementById(`step-${currentStep}`).classList.remove('completed');
            }
        }
        
        function showError(message) {
            const errorDiv = document.getElementById('error-message');
            errorDiv.textContent = message;
            errorDiv.style.display = 'block';
            
            setTimeout(() => {
                errorDiv.style.display = 'none';
            }, 5000);
        }
        
        function togglePaymentMethod() {
            const paymentToggle = document.getElementById('payment-toggle');
            const paymentMethodInput = document.getElementById('payment-method');
            const paymentMethodText = document.getElementById('payment-method-text');
            
            if (paymentMethodInput.value === 'cash') {
                // Switch to online payment
                paymentMethodInput.value = 'cashless';
                paymentMethodText.textContent = 'Online Payment';
                paymentToggle.classList.add('active');
            } else {
                // Switch to cash on delivery
                paymentMethodInput.value = 'cash';
                paymentMethodText.textContent = 'Cash on Delivery';
                paymentToggle.classList.remove('active');
            }
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(() => {
                if (window.cartManager) {
                    loadOrderSummary();
                } else {
                    console.error("cart.js or window.cartManager not loaded.");
                    showError("Error: Cart functionality not available. Please try refreshing.");
                }
            }, 100);
        });
        
        // Card number formatting (these inputs are no longer present in your HTML but keeping the functions just in case)
        document.addEventListener('DOMContentLoaded', function() {
            const cardNumber = document.getElementById('card-number');
            if (cardNumber) {
                cardNumber.addEventListener('input', function(e) {
                    let value = e.target.value.replace(/\s/g, '');
                    value = value.replace(/\D/g, '');
                    value = value.replace(/(\d{4})/g, '$1 ').trim();
                    e.target.value = value.substring(0, 19);
                });
            }
            
            const expiry = document.getElementById('expiry');
            if (expiry) {
                expiry.addEventListener('input', function(e) {
                    let value = e.target.value.replace(/\D/g, '');
                    if (value.length >= 2) {
                        value = value.substring(0, 2) + '/' + value.substring(2, 4);
                    }
                    e.target.value = value.substring(0, 5);
                });
            }
            
            const cvv = document.getElementById('cvv');
            if (cvv) {
                cvv.addEventListener('input', function(e) {
                    e.target.value = e.target.value.replace(/\D/g, '').substring(0, 4);
                });
            }
        });
    </script>
</body>
</html>