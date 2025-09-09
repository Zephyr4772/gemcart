<?php
session_start();
require 'includes/db.php';

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
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

try {
    // Get cart items with product details
    $stmt = $pdo->prepare("
        SELECT p.id, p.name, p.price, p.image, uc.quantity
        FROM cart uc
        JOIN products p ON uc.product_id = p.id
        WHERE uc.user_id = ?
    ");
    $stmt->execute([$user_id]);
    $cart_items = $stmt->fetchAll();

    $total = 0;
} catch (PDOException $e) {
    error_log("Cart retrieval error: " . $e->getMessage());
    die("Could not load cart. Please try again later.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Your Cart</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: center; }
        th { background-color: #f4f4f4; }
        img { width: 80px; height: auto; }
    </style>
</head>
<body>

<h1>Your Shopping Cart</h1>

<?php if (empty($cart_items)): ?>
    <p>Your cart is empty.</p>
<?php else: ?>
    <table>
        <thead>
            <tr>
                <th>Product</th>
                <th>Image</th>
                <th>Price</th>
                <th>Quantity</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($cart_items as $item): 
                $subtotal = $item['price'] * $item['quantity'];
                $total += $subtotal;
            ?>
            <tr>
                <td><?= htmlspecialchars($item['name']) ?></td>
                <td><img src="assets/<?php echo getCategoryFolderFromImage($item['image']); ?>/<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>"></td>
                <td>₹<?= number_format($item['price'] * 83, 2) ?></td>
                <td><?= $item['quantity'] ?></td>
                <td>₹<?= number_format($subtotal * 83, 2) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <h3>Total: ₹<?= number_format($total * 83, 2) ?></h3>
<?php endif; ?>

</body>
</html>
