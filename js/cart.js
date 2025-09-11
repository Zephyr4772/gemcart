// Client-side cart management using localStorage (authenticated users only)
class CartManager {
    constructor() {
        this.cartKey = 'gemcart_cart';
        this.isLoggedIn = this.checkLoginStatus();
        this.userId = this.getUserId();
        this.init();
    }

    // Check if user is logged in
    checkLoginStatus() {
        // Primary method: check data attribute on body
        const bodyData = document.body.dataset.userLoggedIn;
        if (bodyData === 'true') {
            return true;
        }
        
        // Secondary method: check if userId is present in dataset
        const userId = document.body.dataset.userId;
        if (userId && userId !== '') {
            return true;
        }
        
        // Tertiary method: check if there's a cart link in the header (only shown to logged in users)
        const cartLink = document.querySelector('.cart-link');
        if (cartLink) {
            return true;
        }
        
        // Additional check: look for user dropdown with account links
        const userAccountLink = document.querySelector('.user-dropdown-content a[href="account.php"]');
        if (userAccountLink) {
            return true;
        }
        
        return false;
    }

    // Get user ID from session (only for logged in users)
    getUserId() {
        // Only allow cart operations for logged in users
        if (this.isLoggedIn) {
            const userId = document.body.dataset.userId;
            return userId ? `user_${userId}` : null;
        }
        return null;
    }

    // Initialize cart
    init() {
        // Always initialize for better user experience
        this.loadCart();
        this.syncWithServerOnLoad();
        this.updateCartDisplay();
    }

    // Load cart from localStorage
    loadCart() {
        const cartData = localStorage.getItem(this.cartKey);
        this.cart = cartData ? JSON.parse(cartData) : {};
        
        // Initialize user's cart if logged in
        if (this.isLoggedIn && this.userId) {
            if (!this.cart[this.userId]) {
                this.cart[this.userId] = [];
            }
        }
    }

    // Save cart to localStorage
    saveCart() {
        localStorage.setItem(this.cartKey, JSON.stringify(this.cart));
    }

    // Sync cart with server when page loads
    async syncWithServerOnLoad() {
        // Only sync if logged in
        if (!this.isLoggedIn || !this.userId) {
            return;
        }
        
        try {
            // Get current cart items from localStorage
            const localCartItems = this.getCartItems();
            
            // Send cart items to server to sync
            const response = await fetch('cart_api.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=sync&items=${encodeURIComponent(JSON.stringify(localCartItems))}`
            });
            
            const result = await response.json();
            if (result.success) {
                console.log('Cart synchronized with server');
            }
        } catch (error) {
            console.error('Error synchronizing cart with server:', error);
        }
    }

    // Add item to cart
    addToCart(productId, productName, productPrice, productImage, quantity = 1) {
        // Check login status again in case it changed
        this.isLoggedIn = this.checkLoginStatus();
        this.userId = this.getUserId();
        
        if (!this.isLoggedIn) {
            this.showLoginPrompt('Please log in to add items to your cart.');
            return false;
        }

        // Initialize user cart if needed
        if (!this.cart[this.userId]) {
            this.cart[this.userId] = [];
        }

        const userCart = this.cart[this.userId];
        const existingItem = userCart.find(item => item.id == productId);

        if (existingItem) {
            existingItem.quantity += quantity;
        } else {
            userCart.push({
                id: productId,
                name: productName,
                price: parseFloat(productPrice),
                image: productImage,
                quantity: quantity
            });
        }

        this.saveCart();
        this.updateCartDisplay();
        this.showNotification('Item added to cart!');
        
        // Also add to server-side cart
        this.syncWithServer(productId, quantity, 'add');
        
        return true;
    }

    // Remove item from cart (requires authentication)
    removeFromCart(productId) {
        if (!this.isLoggedIn || !this.userId) {
            this.showLoginPrompt('Please log in to manage your cart.');
            return;
        }
        
        this.cart[this.userId] = this.cart[this.userId].filter(item => item.id != productId);
        this.saveCart();
        this.updateCartDisplay();
        this.showNotification('Item removed from cart!');
        
        // Also remove from server-side cart
        this.syncWithServer(productId, 0, 'remove');
    }

    // Update item quantity (requires authentication)
    updateQuantity(productId, quantity) {
        if (!this.isLoggedIn || !this.userId) {
            this.showLoginPrompt('Please log in to manage your cart.');
            return;
        }
        
        const item = this.cart[this.userId].find(item => item.id == productId);
        if (item) {
            if (quantity <= 0) {
                this.removeFromCart(productId);
            } else {
                item.quantity = parseInt(quantity);
                this.saveCart();
                this.updateCartDisplay();
                
                // Also update server-side cart
                this.syncWithServer(productId, quantity, 'update');
            }
        }
    }

    // Sync cart changes with server
    async syncWithServer(productId, quantity, action) {
        try {
            const formData = new FormData();
            formData.append('action', action);
            formData.append('product_id', productId);
            formData.append('quantity', quantity);
            
            const response = await fetch('cart_api.php', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            if (result.success) {
                console.log('Server sync successful:', result.message);
            } else {
                console.error('Server sync failed:', result.message);
            }
        } catch (error) {
            console.error('Error syncing with server:', error);
        }
    }

    // Get cart items (returns empty array for guests)
    getCartItems() {
        if (!this.isLoggedIn || !this.userId) {
            return [];
        }
        return this.cart[this.userId] || [];
    }

    // Get cart total (returns 0 for guests)
    getCartTotal() {
        if (!this.isLoggedIn || !this.userId) {
            return 0;
        }
        return this.cart[this.userId].reduce((total, item) => {
            return total + (item.price * item.quantity);
        }, 0) * 83; // Convert to Rupees
    }

    // Get cart count (returns 0 for guests)
    getCartCount() {
        if (!this.isLoggedIn || !this.userId) {
            return 0;
        }
        return this.cart[this.userId].reduce((count, item) => {
            return count + item.quantity;
        }, 0);
    }

    // Clear cart (requires authentication)
    clearCart() {
        if (!this.isLoggedIn || !this.userId) {
            return;
        }
        this.cart[this.userId] = [];
        this.saveCart();
        this.updateCartDisplay();
        
        // Also clear server-side cart
        this.syncWithServer(0, 0, 'clear');
    }

    // Remove invalid items from cart
    removeInvalidItems() {
        if (!this.isLoggedIn || !this.userId) {
            return;
        }
        
        // This would be called after syncing with server to remove any invalid items
        // For now, we'll handle this in the processOrder function
    }

    // Update cart display in header
    updateCartDisplay() {
        const cartCount = this.getCartCount();
        const cartCountElement = document.getElementById('cart-count');
        
        if (cartCountElement) {
            cartCountElement.textContent = cartCount;
            cartCountElement.style.display = cartCount > 0 ? 'block' : 'none';
        }
    }

    // Show notification
    showNotification(message) {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = 'cart-notification';
        notification.textContent = message;
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: #4CAF50;
            color: white;
            padding: 15px 20px;
            border-radius: 5px;
            z-index: 1000;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
            animation: slideIn 0.3s ease;
        `;

        // Add animation styles
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideIn {
                from { transform: translateX(100%); opacity: 0; }
                to { transform: translateX(0); opacity: 1; }
            }
        `;
        document.head.appendChild(style);

        document.body.appendChild(notification);

        // Remove notification after 3 seconds
        setTimeout(() => {
            notification.style.animation = 'slideOut 0.3s ease';
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 300);
        }, 3000);
    }

    // Show login prompt for guests trying to use cart features
    showLoginPrompt(message) {
        // First, double-check if the user is actually logged in
        // by checking for the presence of the cart link in the header
        const cartLink = document.querySelector('.cart-link');
        if (cartLink) {
            // User appears to be logged in, but our detection failed
            // This can happen with session issues or timing problems
            console.warn('CartManager: Login detection inconsistency detected. Forcing refresh.');
            // Try to add to cart directly without prompt
            return;
        }
        
        // Create modal overlay
        const overlay = document.createElement('div');
        overlay.className = 'login-prompt-overlay';
        overlay.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.7);
            z-index: 10000;
            display: flex;
            justify-content: center;
            align-items: center;
        `;

        // Create modal content
        const modal = document.createElement('div');
        modal.style.cssText = `
            background: white;
            padding: 2rem;
            border-radius: 10px;
            text-align: center;
            max-width: 400px;
            width: 90%;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        `;

        modal.innerHTML = `
            <h3 style="margin-top: 0; color: #003152;">Login Required</h3>
            <p>${message}</p>
            <div style="margin-top: 1.5rem;">
                <a href="login.php" class="btn" style="
                    display: inline-block;
                    padding: 0.8rem 1.5rem;
                    background: #d81b60;
                    color: white;
                    text-decoration: none;
                    border-radius: 5px;
                    margin: 0 0.5rem;
                ">Login</a>
                <button class="btn" style="
                    display: inline-block;
                    padding: 0.8rem 1.5rem;
                    background: #f5f5f5;
                    color: #333;
                    border: none;
                    border-radius: 5px;
                    margin: 0 0.5rem;
                    cursor: pointer;
                ">Cancel</button>
            </div>
        `;

        // Add event listeners
        modal.querySelector('button').addEventListener('click', () => {
            document.body.removeChild(overlay);
        });

        overlay.appendChild(modal);
        document.body.appendChild(overlay);
    }

    // Export cart data for server processing
    exportCartData() {
        return {
            userId: this.userId,
            items: this.getCartItems(),
            total: this.getCartTotal()
        };
    }
}

// Initialize cart when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    window.cartManager = new CartManager();
});