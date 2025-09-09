<?php
session_start();
require_once 'includes/db.php';

// Fetch categories for filter
$categories = [];
$cat_result = mysqli_query($conn, 'SELECT * FROM categories ORDER BY name');
while ($row = mysqli_fetch_assoc($cat_result)) {
    $categories[$row['id']] = $row['name'];
}

// Handle category filter from string or id
$category_map = [
    'rings' => 1,
    'necklaces' => 2,
    'earrings' => 3,
    'bracelets' => 4,
    'watches' => 5
];

$category_filter = 0;
if (isset($_GET['category'])) {
    $cat_param = strtolower(trim($_GET['category']));
    if (is_numeric($cat_param)) {
        $category_filter = (int)$cat_param;
    } elseif (isset($category_map[$cat_param])) {
        $category_filter = $category_map[$cat_param];
    }
}

// Handle gender filter
$gender_filter = '';
if (isset($_GET['gender'])) {
    $gender_filter = strtolower(trim($_GET['gender']));
}

$where = '';
$conditions = [];

// Apply category filter
if ($category_filter > 0) {
    $conditions[] = 'p.category_id = ' . $category_filter;
}

// Apply gender filter (simplified logic - in a real application, you might have a gender column in products)
if ($gender_filter === 'men') {
    // For men, show watches and bracelets primarily
    // This is a simplified approach - in a real application, you would have a gender column in the products table
} elseif ($gender_filter === 'women') {
    // For women, show all categories
    // This is a simplified approach - in a real application, you would have a gender column in the products table
}

if (!empty($conditions)) {
    $where = 'WHERE ' . implode(' AND ', $conditions);
}

// Fetch products
$sql = "SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id $where ORDER BY p.id DESC";
$result = mysqli_query($conn, $sql);
$product_count = mysqli_num_rows($result);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products - GemCart</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;500;700&family=Playfair+Display:wght@400;700&display=swap" rel="stylesheet">
    <script src="js/cart.js"></script>
    <style>
        body {
            background: linear-gradient(120deg, #f8fafd 0%, #e6ecf5 100%);
        }
        .products-header {
            text-align: center;
            margin: 2rem 0 1rem 0;
        }
        .products-header h1 {
            font-family: 'Playfair Display', serif;
            font-size: 2.5rem;
            color: #003152;
            margin-bottom: 0.5rem;
        }
        .products-header .product-count {
            color: #5a7ca7;
            font-size: 1.1rem;
            margin-bottom: 1rem;
        }
        .filter-bar {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: center;
            gap: 1rem;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
            padding: 1.2rem 2rem;
            margin-bottom: 2rem;
            max-width: 900px;
            margin-left: auto;
            margin-right: auto;
        }
        .filter-bar label {
            font-weight: 600;
            color: #003152;
            margin-right: 0.5rem;
        }
        .filter-bar select {
            padding: 0.7rem 2.2rem 0.7rem 1rem;
            border-radius: 8px;
            border: 1.5px solid #5a7ca7;
            font-size: 1rem;
            background: #fff url('data:image/svg+xml;utf8,<svg fill="%235a7ca7" height="20" viewBox="0 0 24 24" width="20" xmlns="http://www.w3.org/2000/svg"><path d="M7 10l5 5 5-5z"/></svg>') no-repeat right 0.7rem center/1.2em;
            appearance: none;
            min-width: 160px;
            color: #003152;
        }
        .products-section-bg {
            background: rgba(255, 255, 255, 0.97);
            border-radius: 24px;
            box-shadow: 0 4px 32px rgba(90, 124, 167, 0.08);
            padding: 2.5rem 1rem;
            margin: 0 auto 2rem auto;
            max-width: 1300px;
        }
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 2.5rem;
            margin: 0 auto;
            max-width: 1300px;
        }
        .product-card {
            background: linear-gradient(120deg, #f8fafd 60%, #e6ecf5 100%);
            border-radius: 18px;
            box-shadow: 0 4px 24px rgba(90, 124, 167, 0.10);
            overflow: hidden;
            transition: transform 0.25s cubic-bezier(.4,2,.6,1), box-shadow 0.25s, border 0.25s;
            position: relative;
            border: 2px solid #5a7ca7;
        }
        .product-card:hover {
            transform: translateY(-10px) scale(1.04) rotate(-1deg);
            box-shadow: 0 12px 48px 0 rgba(90, 124, 167, 0.18);
            border-color: #003152;
            filter: brightness(1.04) drop-shadow(0 0 8px #5a7ca788);
        }
        .product-card img {
            width: 100%;
            height: 220px;
            object-fit: cover;
            border-radius: 18px 18px 0 0;
            box-shadow: 0 2px 12px #5a7ca744;
        }
        .product-card h3 {
            margin: 1.2rem 1.2rem 0.5rem 1.2rem;
            font-size: 1.25rem;
            color: #003152;
            font-family: 'Playfair Display', serif;
            font-weight: 700;
            letter-spacing: 1px;
            display: flex;
            align-items: center;
        }
        .product-card .badge {
            display: inline-block;
            background: linear-gradient(90deg, #5a7ca7 60%, #003152 100%);
            color: #fff;
            font-size: 0.85rem;
            font-weight: 700;
            border-radius: 8px;
            padding: 0.3rem 0.8rem;
            margin-left: 0.7rem;
            box-shadow: 0 2px 8px #5a7ca733;
            letter-spacing: 0.5px;
        }
        .product-card .category {
            margin: 0 1.2rem 0.5rem 1.2rem;
            color: #5a7ca7;
            font-size: 1.05rem;
            font-family: 'Montserrat', sans-serif;
            font-weight: 500;
        }
        .product-card .desc {
            margin: 0 1.2rem 1.2rem 1.2rem;
            color: #003152;
            font-size: 1.01rem;
            font-family: 'Montserrat', sans-serif;
            min-height: 48px;
        }
        .product-card .price {
            margin: 0 1.2rem 1.2rem 1.2rem;
            font-weight: bold;
            color: #003152;
            font-size: 1.25rem;
            font-family: 'Montserrat', sans-serif;
            letter-spacing: 1px;
            text-shadow: 0 1px 4px #e6ecf5;
        }
        .product-card .actions {
            padding: 0 1.2rem 1.2rem 1.2rem;
        }
        .product-card .btn {
            background: linear-gradient(90deg, #5a7ca7 60%, #003152 100%);
            color: #fff;
            border: none;
            padding: 0.7rem 1.2rem;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 700;
            font-family: 'Montserrat', sans-serif;
            box-shadow: 0 2px 8px #5a7ca755;
            transition: background 0.2s, color 0.2s, box-shadow 0.2s;
        }
        .product-card .btn:hover {
            background: linear-gradient(90deg, #003152 60%, #5a7ca7 100%);
            color: #5a7ca7;
            box-shadow: 0 4px 16px #5a7ca755;
        }
        .no-products {
            text-align: center;
            color: #5a7ca7;
            font-size: 1.2rem;
            margin: 2rem 0;
        }
        @media (max-width: 700px) {
            .products-header h1 { font-size: 1.6rem; }
            .products-section-bg { padding: 1.2rem 0.2rem; }
            .products-grid { gap: 1.2rem; }
            .product-card img { height: 140px; }
            .filter-bar { flex-direction: column; padding: 1rem 0.5rem; }
        }
    </style>
</head>
<body data-user-logged-in="<?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>" 
      data-user-id="<?php echo isset($_SESSION['user_id']) ? $_SESSION['user_id'] : ''; ?>">
    <?php include 'includes/header.php'; ?>
    <div class="products-header">
        <h1>Our Products</h1>
        <div class="product-count">
            Showing <?php echo $product_count; ?> product<?php if ($product_count != 1) echo 's'; ?>
            <?php if ($gender_filter): ?>
                - <?php echo ucfirst($gender_filter); ?>'s Collection
            <?php endif; ?>
        </div>
    </div>
    <form method="get" class="filter-bar">
        <?php if ($gender_filter): ?>
            <input type="hidden" name="gender" value="<?php echo htmlspecialchars($gender_filter); ?>">
        <?php endif; ?>
        <label for="category">Category:</label>
        <select name="category" id="category" onchange="this.form.submit()">
            <option value="0">All Categories</option>
            <?php foreach ($categories as $cat_id => $cat_name): ?>
                <option value="<?php echo $cat_id; ?>" <?php if ($category_filter == $cat_id) echo 'selected'; ?>><?php echo htmlspecialchars($cat_name); ?></option>
            <?php endforeach; ?>
        </select>
    </form>
    <div class="products-section-bg">
        <div class="products-grid">
            <?php if ($product_count > 0): ?>
                <?php $i = 0; while ($product = mysqli_fetch_assoc($result)): $i++; ?>
                    <div class="product-card">
                        <?php 
                            // Use the image path stored in the database
                            // If no image is set in database, try to find one based on product name
                            // Otherwise, use a placeholder
                            $imgSrc = '';
                            
                            // First, check if there's an image path in the database
                            if (!empty($product['image']) && $product['image'] !== 'default.jpg') {
                                // Check if it's a full URL or a relative path
                                if (filter_var($product['image'], FILTER_VALIDATE_URL)) {
                                    $imgSrc = $product['image'];
                                } else {
                                    // It's a relative path, check if file exists
                                    $fullPath = 'assets/' . $product['image'];
                                    if (file_exists($fullPath)) {
                                        $imgSrc = $fullPath;
                                    } else {
                                        // Try to find the image in the category folder
                                        $category_folder = strtolower($product['category_name']);
                                        $category_folder = str_replace(' ', '', $category_folder);
                                        $fullPath = 'assets/' . $category_folder . '/' . $product['image'];
                                        if (file_exists($fullPath)) {
                                            $imgSrc = $fullPath;
                                        } else {
                                            // Fallback to placeholder
                                            $imgSrc = 'https://via.placeholder.com/300x220?text=' . urlencode($product['name']);
                                        }
                                    }
                                }
                                // Set image_name for the addToCart function
                                $image_name = basename($product['image']);
                            } else {
                                // No image in database, try to find one based on product name
                                $category_folder = strtolower($product['category_name']);
                                $category_folder = str_replace(' ', '', $category_folder);
                                
                                // Create a clean filename from the product name
                                $image_name = strtolower($product['name']);
                                $image_name = preg_replace('/[^a-z0-9\s-]/', '', $image_name);
                                $image_name = preg_replace('/[\s-]+/', ' ', $image_name);
                                $image_name = str_replace(' ', '-', $image_name);
                                $image_name .= '.jpg';
                                
                                // Check if specific image exists
                                $fullPath = 'assets/' . $category_folder . '/' . $image_name;
                                if (file_exists($fullPath)) {
                                    $imgSrc = $fullPath;
                                } else {
                                    // Try to find any image in the category folder
                                    $category_images = glob('assets/' . $category_folder . '/*.{jpg,jpeg,png,gif}', GLOB_BRACE);
                                    if (!empty($category_images)) {
                                        $imgSrc = $category_images[0];
                                        $image_name = basename($category_images[0]);
                                    } else {
                                        // Fallback to placeholder
                                        $imgSrc = 'https://via.placeholder.com/300x220?text=' . urlencode($product['name']);
                                        $image_name = '';
                                    }
                                }
                            }
                        ?>
                        <img src="<?php echo htmlspecialchars($imgSrc); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                        <h3>
                            <?php echo htmlspecialchars($product['name']); ?>
                            <?php if ($i == 1): ?><span class="badge">Bestseller</span> <span style="color:#5a7ca7;">&#10024;</span><?php endif; ?>
                            <?php if ($i == 2): ?><span class="badge">New</span><?php endif; ?>
                            <?php if ($i == 3): ?><span class="badge">Limited</span><?php endif; ?>
                        </h3>
                        <div class="category">Category: <?php echo htmlspecialchars($product['category_name']); ?></div>
                        <div class="desc"><?php echo htmlspecialchars($product['description']); ?></div>
                        <div class="price">₹<?php echo number_format($product['price'] * 83, 2); ?></div>
                        <div class="actions">
                            <?php if (isset($_SESSION['user_id'])): ?>
                            <button type="button" class="btn" onclick="addToCart(<?php echo $product['id']; ?>, '<?php echo addslashes($product['name']); ?>', <?php echo $product['price']; ?>, '<?php echo addslashes($image_name); ?>', 1)">
                                <i class="fa fa-shopping-bag"></i> Add to Cart
                            </button>
                            <?php else: ?>
                            <a href="login.php" class="btn">
                                <i class="fa fa-user"></i> Login to Add
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="no-products">No products found for your filter/search.</div>
            <?php endif; ?>
        </div>
    </div>
    <?php include 'includes/footer.php'; ?>
    <script>
        function addToCart(productId, productName, productPrice, productImage, quantity) {
            if (window.cartManager) {
                window.cartManager.addToCart(productId, productName, productPrice, productImage, quantity || 1);
            }
        }
    </script>
<?php
?>
</body>
</html>