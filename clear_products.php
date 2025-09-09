<?php
// clear_images.php - Script to clear only the image data from products
require_once 'includes/db.php';

try {
    // Start transaction
    $pdo->beginTransaction();
    
    // Clear all image data from products (set to default.jpg)
    echo "Clearing image data from all products...\n";
    $pdo->exec("UPDATE products SET image = 'default.jpg'");
    
    // Commit transaction
    $pdo->commit();
    
    echo "✅ Successfully cleared all product images!\n";
    echo "All products now have 'default.jpg' as their image.\n";
    
    // Show updated products
    $stmt = $pdo->query("SELECT id, name, image FROM products ORDER BY id");
    $products = $stmt->fetchAll();
    
    echo "\nUpdated products:\n";
    echo "ID | Name | Image\n";
    echo "---|------|-------\n";
    foreach ($products as $product) {
        echo sprintf("%2d | %-30s | %s\n", 
            $product['id'], 
            substr($product['name'], 0, 30), 
            $product['image']
        );
    }
    
} catch (Exception $e) {
    // Rollback transaction on error
    $pdo->rollback();
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Transaction rolled back.\n";
}
?>
