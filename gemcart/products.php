<?php
require_once 'includes/db.php';

// Fetch categories for filter
$categories = [];
$cat_result = mysqli_query($conn, 'SELECT * FROM categories ORDER BY name');
while ($row = mysqli_fetch_assoc($cat_result)) {
    $categories[$row['id']] = $row['name'];
}

// Handle category filter
$category_filter = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$where = '';
if ($category_filter > 0) {
    $where = 'WHERE p.category_id = ' . $category_filter;
}

// Fetch products with proper JOIN to ensure correct category filtering
$sql = "SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id $where ORDER BY p.id DESC";
$result = mysqli_query($conn, $sql);

$images = [
    'pearl-necklace.jpg', 'silver-chain.jpg', 'diamond-studs.jpg', 'gold-hoops.jpg', 'tennis-bracelet.jpg',
    'charm-bracelet.jpg', 'luxury-watch.jpg', 'classic-watch.jpg', 'sapphire-ring.jpg', 'emerald-necklace.jpg',
    'gold-band.jpg', 'diamond-ring.jpg', 'all.jpg', 'plat.png', 'bride.jpg', 'main-girl.jpg'
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products - GemCart</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .products-grid { display: flex; flex-wrap: wrap; gap: 2rem; }
        .product-card { background: #fff; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); width: 300px; overflow: hidden; display: flex; flex-direction: column; }
        .product-card img { width: 100%; height: 220px; object-fit: cover; }
        .product-card h3 { margin: 1rem 1rem 0.5rem; font-size: 1.2rem; color: #003152; }
        .product-card .category { margin: 0 1rem 0.5rem; color: #666; font-size: 0.95rem; }
        .product-card .desc { margin: 0 1rem 1rem; color: #444; font-size: 0.98rem; }
        .product-card .price { margin: 0 1rem 1rem; font-weight: bold; color: #003152; font-size: 1.1rem; }
        .product-card .actions { margin: 0 1rem 1rem; }
        .product-card .btn { background: #003152; color: #fff; border: none; padding: 0.5rem 1rem; border-radius: 5px; cursor: pointer; width: 100%; font-size: 1rem; }
        .product-card .btn:hover { background: #001f3f; }
        .filter-bar { margin-bottom: 2rem; }
        .cart-notification { position: fixed; top: 30px; right: 30px; background: #003152; color: #fff; padding: 1rem 2rem; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.15); z-index: 9999; display: none; }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    <main>
        <div class="container">
            <h1>Our Products</h1>
            <form method="get" class="filter-bar">
                <label for="category">Filter by Category:</label>
                <select name="category" id="category" onchange="this.form.submit()">
                    <option value="0">All Categories</option>
                    <?php foreach ($categories as $cat_id => $cat_name): ?>
                        <option value="<?php echo $cat_id; ?>" <?php if ($category_filter == $cat_id) echo 'selected'; ?>><?php echo htmlspecialchars($cat_name); ?></option>
                    <?php endforeach; ?>
                </select>
            </form>
            <div class="products-grid">
                <?php if ($result && mysqli_num_rows($result) > 0): ?>
                    <?php while ($product = mysqli_fetch_assoc($result)): ?>
                        <?php 
                            $imgRaw = trim($product['image'] ?? '');
                            if ($imgRaw === '') {
                                // Fallback to a readable placeholder using name if image missing
                                $placeholderText = urlencode($product['name'] ?? 'Product');
                                $imgRaw = 'https://via.placeholder.com/300x220.png?text=' . $placeholderText;
                            }
                            $isAbsolute = preg_match('/^https?:\/\//i', $imgRaw) === 1;
                            $imgResolved = $isAbsolute ? $imgRaw : ('images/' . $imgRaw);
                        ?>
                        <div class="product-card">
                            <img src="<?php echo htmlspecialchars($imgResolved); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" onerror="this.src='https://via.placeholder.com/300x220?text=No+Image'">
                            <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                            <div class="category">Category: <?php echo htmlspecialchars($product['category_name']); ?></div>
                            <div class="desc"><?php echo htmlspecialchars($product['description']); ?></div>
                            <div class="price">$<?php echo number_format($product['price'], 2); ?></div>
                            <div class="actions">
                                <button class="btn add-to-cart-btn" 
                                    data-id="<?php echo $product['id']; ?>" 
                                    data-name="<?php echo htmlspecialchars($product['name']); ?>" 
                                    data-price="<?php echo $product['price']; ?>" 
                                    data-image="<?php echo htmlspecialchars($imgResolved); ?>">
                                    Add to Cart
                                </button>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p>No products found in the database.</p>
                <?php endif; ?>
            </div>
        </div>
        <div class="cart-notification" id="cartNotification">Added to cart!</div>
    </main>
    <?php include 'includes/footer.php'; ?>
    <script>
    // Add to Cart logic using localStorage
    document.querySelectorAll('.add-to-cart-btn').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const id = this.getAttribute('data-id');
            const name = this.getAttribute('data-name');
            const price = this.getAttribute('data-price');
            const image = this.getAttribute('data-image');
            let cart = JSON.parse(localStorage.getItem('cart') || '{}');
            if (cart[id]) {
                cart[id].qty += 1;
            } else {
                cart[id] = { id, name, price, image, qty: 1 };
            }
            localStorage.setItem('cart', JSON.stringify(cart));
            showCartNotification();
            updateCartIcon();
        });
    });
    function showCartNotification() {
        const notif = document.getElementById('cartNotification');
        notif.style.display = 'block';
        setTimeout(() => { notif.style.display = 'none'; }, 1200);
    }
    function updateCartIcon() {
        // Optional: update cart icon count in header if you want
        const cart = JSON.parse(localStorage.getItem('cart') || '{}');
        let count = 0;
        for (let k in cart) count += cart[k].qty;
        let cartLinks = document.querySelectorAll('.icon-link[title="Cart"]');
        cartLinks.forEach(link => {
            let badge = link.querySelector('.cart-count');
            if (!badge) {
                badge = document.createElement('span');
                badge.className = 'cart-count';
                link.appendChild(badge);
            }
            badge.textContent = count > 0 ? count : '';
        });
    }
    updateCartIcon();
    </script>
</body>
</html> 