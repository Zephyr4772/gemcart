<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Watches category id (from database_setup.sql, it's 5)
$watches_category_id = 5;
$type = isset($_GET['type']) ? ucfirst(htmlspecialchars($_GET['type'])) : '';

// Fetch all watches
$sql = "SELECT * FROM products WHERE category_id = $watches_category_id ORDER BY id DESC";
$result = mysqli_query($conn, $sql);
$product_count = mysqli_num_rows($result);

?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Watches<?php if ($type) echo ' - ' . $type; ?> | Cherry Charms</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;500;700&family=Playfair+Display:wght@400;700&display=swap" rel="stylesheet">
    <script src="js/cart.js"></script>
    <style>
        body {
            background: linear-gradient(120deg, #f8fafd 0%, #e6ecf5 100%);
            position: relative;
        }
        .watches-bg-svg {
            position: absolute;
            top: 0; left: 0; width: 100vw; height: 100vh;
            z-index: 0;
            pointer-events: none;
        }
        .category-header {
            background: linear-gradient(135deg, #5a7ca7 60%, #003152 100%);
            color: #fff;
            padding: 3rem 0 2rem 0;
            text-align: center;
            margin-bottom: 2rem;
            border-radius: 0 0 40px 40px;
            box-shadow: 0 8px 32px rgba(90, 124, 167, 0.12);
            position: relative;
        }
        .category-title {
            font-family: 'Playfair Display', serif;
            font-size: 3.2rem;
            margin-bottom: 0.5rem;
            letter-spacing: 2px;
            color: #fff;
            text-shadow: 0 2px 8px #003152;
            position: relative;
        }
        .category-title .fa-clock-o {
            color: #fff;
            margin-left: 0.5rem;
            font-size: 2.2rem;
            vertical-align: middle;
            filter: drop-shadow(0 0 6px #003152);
        }
        .category-subtitle {
            font-size: 1.25rem;
            opacity: 0.95;
            color: #e6ecf5;
            font-family: 'Montserrat', sans-serif;
        }
        .products-section-bg {
            background: rgba(255, 255, 255, 0.90);
            border-radius: 24px;
            box-shadow: 0 4px 32px rgba(90, 124, 167, 0.08);
            padding: 2.5rem 1rem;
            margin: 0 auto 2rem auto;
            max-width: 1300px;
            position: relative;
            z-index: 1;
        }
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 2.5rem;
            margin: 0 auto;
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
            height: 260px;
            object-fit: cover;
            object-position: center;
            border-radius: 18px 18px 0 0;
            box-shadow: 0 2px 12px #5a7ca744;
        }
        .product-card h3 {
            margin: 1.2rem 1.2rem 0.5rem 1.2rem;
            font-size: 1.35rem;
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
            font-size: 1.35rem;
            font-family: 'Montserrat', sans-serif;
            letter-spacing: 1px;
            text-shadow: 0 1px 4px #e6ecf5;
        }
        .product-card .actions {
            margin: 0 1.2rem 1.2rem 1.2rem;
            display: flex;
            flex-direction: column;
            gap: 0.8rem;
        }
        .product-card .btn {
            background: linear-gradient(90deg, #5a7ca7 60%, #003152 100%);
            color: #fff;
            border: none;
            padding: 0.9rem 1.2rem;
            border-radius: 8px;
            cursor: pointer;
            width: 100%;
            font-size: 1.08rem;
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
        .product-card .sparkle {
            color: #5a7ca7;
            margin-left: 0.3rem;
            font-size: 1.1rem;
            vertical-align: middle;
            filter: drop-shadow(0 0 4px #fff);
        }
        .breadcrumb {
            padding: 1.2rem 0 0.5rem 0;
            max-width: 1300px;
            margin: 0 auto;
            font-size: 1.05rem;
            color: #5a7ca7;
            font-family: 'Montserrat', sans-serif;
        }
        .breadcrumb a {
            color: #5a7ca7;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.2s;
        }
        .breadcrumb a:hover {
            color: #003152;
        }
        .no-products {
            text-align: center;
            color: #5a7ca7;
            font-size: 1.2rem;
            margin: 2rem 0;
            grid-column: 1 / -1;
        }
        @media (max-width: 700px) {
            .products-section-bg {
                padding: 1.2rem 0.2rem;
            }
            .products-grid {
                gap: 1.2rem;
            }
            .product-card img {
                height: 180px;
            }
            .category-title {
                font-size: 2.2rem;
            }
        }
    </style>
</head>
<body data-user-logged-in="<?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>" 
      data-user-id="<?php echo isset($_SESSION['user_id']) ? $_SESSION['user_id'] : ''; ?>">
    <svg class="watches-bg-svg" viewBox="0 0 1440 320"><defs><radialGradient id="watchesGrad" cx="50%" cy="50%" r="80%"><stop offset="0%" stop-color="#5a7ca7" stop-opacity="0.18"/><stop offset="100%" stop-color="#f8fafd" stop-opacity="0"/></radialGradient></defs><ellipse cx="720" cy="160" rx="900" ry="180" fill="url(#watchesGrad)"/></svg>
    <?php include 'includes/header.php'; ?>
    <div class="breadcrumb">
        <a href="index.php">Home</a> &gt; Watches Collection
    </div>
    <div class="category-header">
        <h1 class="category-title">Watches Collection <i class="fa fa-clock-o"></i></h1>
        <p class="category-subtitle">Precision timepieces for the modern gentleman</p>
    </div>
    <main>
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
                                            // Try to find the image in the watches folder
                                            $fullPath = 'assets/watches/' . $product['image'];
                                            if (file_exists($fullPath)) {
                                                $imgSrc = $fullPath;
                                            } else {
                                                // Fallback to placeholder
                                                $imgSrc = 'https://via.placeholder.com/300x260?text=' . urlencode($product['name']);
                                            }
                                        }
                                    }
                                } else {
                                    // No image in database, try to find one based on product name
                                    // Create a clean filename from the product name
                                    $image_name = strtolower($product['name']);
                                    $image_name = preg_replace('/[^a-z0-9\s-]/', '', $image_name);
                                    $image_name = preg_replace('/[\s-]+/', ' ', $image_name);
                                    $image_name = str_replace(' ', '-', $image_name);
                                    $image_name .= '.jpg';
                                    
                                    // Check if specific image exists in watches folder
                                    $fullPath = 'assets/watches/' . $image_name;
                                    if (file_exists($fullPath)) {
                                        $imgSrc = $fullPath;
                                    } else {
                                        // Try to find any image in the watches folder
                                        $watches_images = glob('assets/watches/*.{jpg,jpeg,png,gif}', GLOB_BRACE);
                                        if (!empty($watches_images)) {
                                            $imgSrc = $watches_images[0];
                                        } else {
                                            // Fallback to placeholder
                                            $imgSrc = 'https://via.placeholder.com/300x260?text=' . urlencode($product['name']);
                                        }
                                    }
                                }
                            ?>
                            <img src="<?php echo htmlspecialchars($imgSrc); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                            <h3>
                                <?php echo htmlspecialchars($product['name']); ?>
                                <?php if ($i == 1): ?><span class="badge">Bestseller</span> <span class="sparkle">&#10024;</span><?php endif; ?>
                                <?php if ($i == 2): ?><span class="badge">New</span><?php endif; ?>
                            </h3>
                            <div class="category">Watches</div>
                            <div class="desc"><?php echo htmlspecialchars($product['description']); ?></div>
                            <div class="price">₹<?php echo number_format($product['price'] * 83, 2); ?></div>
                            <div class="actions">
                                <?php if (isset($_SESSION['user_id'])): ?>
                                <button type="button" class="btn" onclick="addToCart(<?php echo $product['id']; ?>, '<?php echo addslashes($product['name']); ?>', <?php echo $product['price']; ?>, '<?php echo addslashes($product['image']); ?>')">
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
                    <div class="no-products">No watches found.</div>
                <?php endif; ?>
            </div>
        </div>
    </main>
    <?php include 'includes/footer.php'; ?>
    <script>
        function addToCart(productId, productName, productPrice, productImage) {
            if (window.cartManager) {
                window.cartManager.addToCart(productId, productName, productPrice, productImage);
            }
        }
    </script>
</body>
</html>