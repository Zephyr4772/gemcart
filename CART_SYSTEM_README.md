# Client-Side Cart System

This implementation provides a client-side cart system that stores cart data in the browser's localStorage, eliminating the need for database storage while maintaining user-specific cart functionality.

## Features

- **Client-side storage**: Cart data is stored in localStorage
- **User-specific carts**: Different carts for logged-in users and guests
- **Real-time updates**: Cart count updates immediately in the header
- **Persistent data**: Cart survives browser sessions
- **Server integration**: Optional API endpoints for checkout processing

## How It Works

### 1. Cart Management (`js/cart.js`)

The `CartManager` class handles all cart operations:

- **User Identification**: 
  - Logged-in users: `user_{user_id}`
  - Guest users: `guest_{timestamp}_{random_string}`

- **Storage Structure**:
  ```javascript
  {
    "user_123": [
      {
        id: 1,
        name: "Product Name",
        price: 99.99,
        image: "product.jpg",
        quantity: 2
      }
    ],
    "guest_1234567890_abc123": [...]
  }
  ```

### 2. Key Functions

- `addToCart(productId, name, price, image, quantity)`: Add items to cart
- `removeFromCart(productId)`: Remove items from cart
- `updateQuantity(productId, quantity)`: Update item quantities
- `getCartItems()`: Get all cart items
- `getCartTotal()`: Calculate cart total
- `getCartCount()`: Get total item count
- `clearCart()`: Clear entire cart

### 3. User Session Integration

The system detects user login status via data attributes:
```html
<body data-user-logged-in="true" data-user-id="123">
```

### 4. Cart Display

- **Header**: Shows cart count badge
- **Cart Page**: Full cart management interface
- **Notifications**: Toast notifications for cart actions

## Files Modified/Created

### New Files
- `js/cart.js` - Main cart management system
- `cart_client.php` - Client-side cart page
- `cart_api.php` - API endpoints for server integration
- `CART_SYSTEM_README.md` - This documentation

### Modified Files
- `includes/header.php` - Added cart script and count badge
- `products.php` - Updated to use client-side cart
- `index.php` - Added cart script and user data, replaced static jewelry pages with dynamic category shortcuts and enhanced UI with vibrant design, fixed JavaScript errors
- `css/style.css` - Removed JavaScript code and added proper CSS styling

### Removed Files
- `gold_jewelry.php` - Replaced with dynamic category shortcuts
- `silver_jewelry.php` - Replaced with dynamic category shortcuts
- `platinum_jewelry.php` - Replaced with dynamic category shortcuts

## Usage

### Adding Items to Cart
```javascript
// From products page
addToCart(productId, productName, productPrice, productImage);

// Direct API usage
window.cartManager.addToCart(productId, name, price, image, quantity);
```

### Cart Operations
```javascript
// Remove item
window.cartManager.removeFromCart(productId);

// Update quantity
window.cartManager.updateQuantity(productId, newQuantity);

// Get cart data
const items = window.cartManager.getCartItems();
const total = window.cartManager.getCartTotal();
const count = window.cartManager.getCartCount();
```

### Checkout Process
The checkout process sends cart data to `cart_api.php` for server-side processing:
```javascript
fetch('cart_api.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
        action: 'checkout',
        cart_items: cartData.items,
        cart_total: cartData.total
    })
});
```

## Homepage Category Shortcuts

The homepage now features dynamic category shortcuts that rotate daily:
- Three category cards are displayed based on the day of the year
- Each card links directly to the products page with the category filter pre-applied
- Categories rotate through all available product categories (Rings, Necklaces, Earrings, Bracelets, Watches)
- This provides fresh content without requiring manual updates
- Enhanced with vibrant design, category-specific icons, colors, and visual effects
- Added category carousel section for additional product discovery

## Benefits

1. **No Database Dependency**: Cart works without database tables
2. **Fast Performance**: No server requests for cart operations
3. **User-Friendly**: Immediate feedback and notifications
4. **Persistent**: Cart survives browser restarts
5. **Scalable**: Can handle multiple users simultaneously
6. **Flexible**: Easy to extend with additional features
7. **Dynamic Content**: Homepage category shortcuts provide rotating featured categories
8. **Enhanced UI**: Vibrant design with personality and interactive elements
9. **Fixed Code Structure**: JavaScript errors resolved by moving code to appropriate locations

## Browser Compatibility

- Modern browsers with localStorage support
- IE8+ (with polyfill for older browsers)
- Mobile browsers (iOS Safari, Chrome Mobile, etc.)

## Security Considerations

- Cart data is stored locally and not encrypted
- Server-side validation is recommended for checkout
- Consider implementing cart data expiration
- Validate product data on the server side

## Future Enhancements

- Cart data synchronization across devices
- Wishlist functionality
- Cart sharing features
- Advanced product options (size, color, etc.)
- Cart abandonment recovery
- Analytics integration