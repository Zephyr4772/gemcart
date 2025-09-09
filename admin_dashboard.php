<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/functions.php';
requireAdmin();

// Default page
$page = $_GET['page'] ?? 'home';

// Get user ID for user-specific views
$user_id_filter = $_GET['user_id'] ?? null;

// =================================================================================
//  HANDLE ALL FORM SUBMISSIONS (POST REQUESTS)
// =================================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // --- Product Actions ---
    if ($action === 'add_product' || $action === 'edit_product') {
        $product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : null;
        $name = trim($_POST['name']);
        $description = trim($_POST['description']);
        $price = (float)$_POST['price'];
        $category_id = (int)$_POST['category_id'];

        if (empty($name) || empty($price) || empty($category_id)) {
            $_SESSION['flash_message_error'] = "Name, price, and category are required.";
        } else {
            if ($action === 'edit_product') {
                $stmt = mysqli_prepare($conn, "UPDATE products SET name = ?, description = ?, price = ?, category_id = ? WHERE id = ?");
                mysqli_stmt_bind_param($stmt, "ssdii", $name, $description, $price, $category_id, $product_id);
                $_SESSION['flash_message'] = "Product updated successfully.";
            } else {
                $stmt = mysqli_prepare($conn, "INSERT INTO products (name, description, price, category_id) VALUES (?, ?, ?, ?)");
                mysqli_stmt_bind_param($stmt, "ssdi", $name, $description, $price, $category_id);
                $_SESSION['flash_message'] = "Product added successfully.";
            }
            if (!mysqli_stmt_execute($stmt)) {
                 $_SESSION['flash_message_error'] = "Database Error: " . mysqli_stmt_error($stmt);
            }
        }
    } elseif ($action === 'delete_product') {
        $product_id = (int)$_POST['product_id'];
        $stmt = mysqli_prepare($conn, "DELETE FROM products WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $product_id);
        mysqli_stmt_execute($stmt);
        $_SESSION['flash_message'] = "Product deleted.";
    }

    // --- Category Actions ---
    elseif ($action === 'add_category' || $action === 'edit_category') {
        $category_id = isset($_POST['category_id']) ? (int)$_POST['category_id'] : null;
        $name = trim($_POST['name']);
        if (empty($name)) {
            $_SESSION['flash_message_error'] = "Category name is required.";
        } else {
            if ($action === 'edit_category') {
                $stmt = mysqli_prepare($conn, "UPDATE categories SET name = ? WHERE id = ?");
                mysqli_stmt_bind_param($stmt, "si", $name, $category_id);
                $_SESSION['flash_message'] = "Category updated.";
            } else {
                $stmt = mysqli_prepare($conn, "INSERT INTO categories (name) VALUES (?)");
                mysqli_stmt_bind_param($stmt, "s", $name);
                $_SESSION['flash_message'] = "Category added.";
            }
             if (!mysqli_stmt_execute($stmt)) {
                 $_SESSION['flash_message_error'] = "Database Error: " . mysqli_stmt_error($stmt);
            }
        }
    } elseif ($action === 'delete_category') {
        $category_id = (int)$_POST['category_id'];
        $stmt = mysqli_prepare($conn, "DELETE FROM categories WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $category_id);
        if(!mysqli_stmt_execute($stmt)) {
            $_SESSION['flash_message_error'] = "Cannot delete category with products assigned to it.";
        } else {
            $_SESSION['flash_message'] = "Category deleted.";
        }
    }
    
    // --- User Actions ---
    elseif ($action === 'add_user' || $action === 'edit_user') {
        $user_id = isset($_POST['user_id']) ? (int)$_POST['user_id'] : null;
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        $password = $_POST['password'];

        if (empty($name) || empty($email) || ($action === 'add_user' && empty($password))) {
             $_SESSION['flash_message_error'] = "All fields are required for new users.";
        } else {
            if ($action === 'edit_user') {
                if (!empty($password)) {
                    $hash = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = mysqli_prepare($conn, "UPDATE users SET name = ?, email = ?, password = ? WHERE id = ?");
                    mysqli_stmt_bind_param($stmt, "sssi", $name, $email, $hash, $user_id);
                } else {
                    $stmt = mysqli_prepare($conn, "UPDATE users SET name = ?, email = ? WHERE id = ?");
                    mysqli_stmt_bind_param($stmt, "ssi", $name, $email, $user_id);
                }
                 $_SESSION['flash_message'] = "User updated.";
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = mysqli_prepare($conn, "INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
                mysqli_stmt_bind_param($stmt, "sss", $name, $email, $hash);
                $_SESSION['flash_message'] = "User added.";
            }
            if (!mysqli_stmt_execute($stmt)) {
                 $_SESSION['flash_message_error'] = "Database Error: " . mysqli_stmt_error($stmt);
            }
        }
    } elseif ($action === 'delete_user') {
        $user_id = (int)$_POST['user_id'];
        $stmt = mysqli_prepare($conn, "DELETE FROM users WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        $_SESSION['flash_message'] = "User deleted.";
    }

    // --- Feedback Actions ---
    elseif ($action === 'delete_feedback') {
        $feedback_id = (int)$_POST['feedback_id'];
        $stmt = mysqli_prepare($conn, "DELETE FROM feedback WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $feedback_id);
        mysqli_stmt_execute($stmt);
        $_SESSION['flash_message'] = "Feedback entry deleted.";
    }
    
    // --- Cart Actions ---
    elseif ($action === 'remove_cart_item') {
        $cart_id = (int)$_POST['cart_id'];
        $stmt = mysqli_prepare($conn, "DELETE FROM cart WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $cart_id);
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['flash_message'] = "Item removed from cart.";
        } else {
            $_SESSION['flash_message_error'] = "Error removing item from cart.";
        }
    }

    // Redirect to the same page to prevent form resubmission on refresh
    header("Location: admin_dashboard.php?page=$page" . ($user_id_filter ? "&user_id=$user_id_filter" : ""));
    exit();
}

// =================================================================================
//  HTML & DISPLAY LOGIC STARTS HERE
// =================================================================================
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GemCart Admin - <?= ucfirst($page) ?></title>
    <style>
        /* General Admin Styles */
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; background-color: #f8f9fa; margin: 0; color: #333; }
        .admin-container { display: flex; }
        .admin-sidebar { width: 240px; background-color: #003152; color: #fff; height: 100vh; position: fixed; top: 0; left: 0; padding: 20px 0; display: flex; flex-direction: column; }
        .admin-sidebar .logo { text-align: center; font-size: 1.8rem; margin-bottom: 2rem; color: #fff; }
        .admin-sidebar nav { flex-grow: 1; }
        .admin-sidebar nav ul { list-style-type: none; padding: 0; margin: 0; }
        .admin-sidebar nav ul li a { display: block; color: #e9ecef; padding: 15px 25px; text-decoration: none; transition: background-color 0.3s; }
        .admin-sidebar nav ul li a:hover, .admin-sidebar nav ul li.active a { background-color: #004a7c; color: #fff; }
        .admin-main-content { margin-left: 240px; padding: 30px; width: calc(100% - 300px); }
        .admin-header { border-bottom: 1px solid #dee2e6; padding-bottom: 1rem; margin-bottom: 2rem; }
        .admin-header h1 { margin: 0; }
        .alert { padding: 15px; margin-bottom: 20px; border-radius: 5px; border: 1px solid transparent; }
        .alert-error { background-color: #f8d7da; color: #721c24; border-color: #f5c6cb; }
        .alert-success { background-color: #d4edda; color: #155724; border-color: #c3e6cb; }
        .table-container { background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); margin-top: 2rem; overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #dee2e6; white-space: nowrap; }
        thead th { background-color: #f1f3f5; }
        tbody tr:hover { background-color: #f8f9fa; }
        td.actions { display: flex; gap: 10px; }
        .form-container { background: #fff; padding: 25px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .form-group { margin-bottom: 1rem; }
        .form-group label { display: block; margin-bottom: 0.5rem; font-weight: 600; }
        input[type=text], input[type=password], input[type=number], input[type=email], select, textarea { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        textarea { resize: vertical; min-height: 80px; }
        .btn { padding: 10px 20px; border: none; border-radius: 4px; background-color: #003152; color: white; cursor: pointer; text-decoration: none; display: inline-block; font-size: 1rem; }
        .btn:hover { background-color: #001d33; }
        .btn-secondary { background-color: #6c757d; }
        .btn-secondary:hover { background-color: #5a6268; }
        .btn-danger { background-color: #c82333; }
        .btn-danger:hover { background-color: #bd2130; }
        .btn-sm { padding: 5px 10px; font-size: 0.875rem; }
        .dashboard-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; }
        .stat-card { background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .stat-card h3 { margin-top: 0; } .stat-card p { font-size: 2.5rem; font-weight: bold; margin: 10px 0; }
        .stat-card a { text-decoration: none; color: #003152; font-weight: bold; }
        .order-header { background-color: #f1f3f5; font-weight: bold; }
        .order-items-row td { padding: 0; border-bottom: 2px solid #003152; }
        .order-items-container { padding: 15px; background-color: #fff; }
        .order-items-container h4 { margin-top: 0; margin-bottom: 10px; } .order-items-container ul { list-style-type: none; padding-left: 0; margin: 0; }
        .user-filter { margin-bottom: 20px; }
        .user-filter select { width: auto; display: inline-block; margin-right: 10px; }
    </style>
</head>
<body>
<div class="admin-container">
    <aside class="admin-sidebar">
        <div>
            <h1 class="logo">GemCart</h1>
            <nav>
                <ul>
                    <li class="<?= ($page == 'home') ? 'active' : '' ?>"><a href="admin_dashboard.php?page=home">Dashboard</a></li>
                    <li class="<?= ($page == 'products') ? 'active' : '' ?>"><a href="admin_dashboard.php?page=products">Products</a></li>
                    <li class="<?= ($page == 'categories') ? 'active' : '' ?>"><a href="admin_dashboard.php?page=categories">Categories</a></li>
                    <li class="<?= ($page == 'users') ? 'active' : '' ?>"><a href="admin_dashboard.php?page=users">Users</a></li>
                    <li class="<?= ($page == 'carts') ? 'active' : '' ?>"><a href="admin_dashboard.php?page=carts">User Carts</a></li>
                    <li class="<?= ($page == 'orders') ? 'active' : '' ?>"><a href="admin_dashboard.php?page=orders">Orders</a></li>
                    <li class="<?= ($page == 'feedback') ? 'active' : '' ?>"><a href="admin_dashboard.php?page=feedback">Feedback</a></li>
                </ul>
            </nav>
        </div>
        <div style="padding: 0 25px 20px 25px;">
            <form action="admin_logout.php" method="POST">
                <button type="submit" class="btn btn-danger" style="width: 100%;">Logout</button>
            </form>
        </div>
    </aside>

    <main class="admin-main-content">
        <?php
            // Display flash messages
            if (isset($_SESSION['flash_message'])) {
                echo '<div class="alert alert-success">' . htmlspecialchars($_SESSION['flash_message']) . '</div>';
                unset($_SESSION['flash_message']);
            }
            if (isset($_SESSION['flash_message_error'])) {
                echo '<div class="alert alert-error">' . htmlspecialchars($_SESSION['flash_message_error']) . '</div>';
                unset($_SESSION['flash_message_error']);
            }

            // Page content switcher
            switch ($page) {
                // ================== PRODUCTS PAGE ==================
                case 'products':
                    $edit_product = null;
                    if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
                        $stmt = mysqli_prepare($conn, "SELECT * FROM products WHERE id = ?");
                        mysqli_stmt_bind_param($stmt, "i", $_GET['id']);
                        mysqli_stmt_execute($stmt);
                        $edit_product = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
                    }
                    $categories_result = mysqli_query($conn, "SELECT id, name FROM categories ORDER BY name");
                    ?>
                    <div class="admin-header"><h1>Manage Products</h1></div>
                    <div class="form-container">
                        <h2><?= $edit_product ? 'Edit Product' : 'Add New Product' ?></h2>
                        <form action="admin_dashboard.php?page=products" method="POST">
                            <input type="hidden" name="action" value="<?= $edit_product ? 'edit_product' : 'add_product' ?>">
                            <?php if ($edit_product): ?><input type="hidden" name="product_id" value="<?= (int)$edit_product['id'] ?>"><?php endif; ?>
                            <div class="form-group"><label for="name">Product Name</label><input type="text" name="name" value="<?= htmlspecialchars($edit_product['name'] ?? '') ?>" required></div>
                            <div class="form-group"><label for="description">Description</label><textarea name="description"><?= htmlspecialchars($edit_product['description'] ?? '') ?></textarea></div>
                            <div class="form-group"><label for="price">Price</label><input type="number" name="price" step="0.01" value="<?= htmlspecialchars($edit_product['price'] ?? '') ?>" required></div>
                            <div class="form-group">
                                <label for="category_id">Category</label>
                                <select name="category_id" required>
                                    <option value="">Select Category</option>
                                    <?php while ($cat = mysqli_fetch_assoc($categories_result)): ?>
                                        <option value="<?= (int)$cat['id'] ?>" <?= isset($edit_product['category_id']) && $edit_product['category_id'] == $cat['id'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['name']) ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <button type="submit" class="btn"><?= $edit_product ? 'Update Product' : 'Add Product' ?></button>
                            <?php if ($edit_product): ?><a href="admin_dashboard.php?page=products" class="btn btn-secondary">Cancel Edit</a><?php endif; ?>
                        </form>
                    </div>
                    <div class="table-container">
                        <h2>Existing Products</h2>
                        <table>
                            <thead><tr><th>ID</th><th>Name</th><th>Price</th><th>Category</th><th>Actions</th></tr></thead>
                            <tbody>
                                <?php
                                $products_result = mysqli_query($conn, "SELECT p.id, p.name, p.price, c.name AS category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.id DESC");
                                while ($product = mysqli_fetch_assoc($products_result)): ?>
                                <tr>
                                    <td><?= (int)$product['id'] ?></td>
                                    <td><?= htmlspecialchars($product['name']) ?></td>
                                    <td>₹<?= htmlspecialchars(number_format($product['price'] * 83, 2)) ?></td>
                                    <td><?= htmlspecialchars($product['category_name'] ?? 'N/A') ?></td>
                                    <td class="actions">
                                        <a href="admin_dashboard.php?page=products&action=edit&id=<?= (int)$product['id'] ?>" class="btn btn-sm">Edit</a>
                                        <form action="admin_dashboard.php?page=products" method="POST" onsubmit="return confirm('Delete this product?');" style="display:inline;"><input type="hidden" name="action" value="delete_product"><input type="hidden" name="product_id" value="<?= (int)$product['id'] ?>"><button type="submit" class="btn btn-sm btn-danger">Delete</button></form>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php
                    break;

                // ================== CATEGORIES PAGE ==================
                case 'categories':
                    $edit_category = null;
                    if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
                        $stmt = mysqli_prepare($conn, "SELECT * FROM categories WHERE id = ?");
                        mysqli_stmt_bind_param($stmt, "i", $_GET['id']);
                        mysqli_stmt_execute($stmt);
                        $edit_category = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
                    }
                    ?>
                    <div class="admin-header"><h1>Manage Categories</h1></div>
                    <div class="form-container">
                        <h2><?= $edit_category ? 'Edit Category' : 'Add New Category' ?></h2>
                        <form action="admin_dashboard.php?page=categories" method="POST">
                            <input type="hidden" name="action" value="<?= $edit_category ? 'edit_category' : 'add_category' ?>">
                            <?php if ($edit_category): ?><input type="hidden" name="category_id" value="<?= (int)$edit_category['id'] ?>"><?php endif; ?>
                            <div class="form-group"><label for="name">Category Name</label><input type="text" name="name" value="<?= htmlspecialchars($edit_category['name'] ?? '') ?>" required></div>
                            <button type="submit" class="btn"><?= $edit_category ? 'Update Category' : 'Add Category' ?></button>
                            <?php if ($edit_category): ?><a href="admin_dashboard.php?page=categories" class="btn btn-secondary">Cancel Edit</a><?php endif; ?>
                        </form>
                    </div>
                     <div class="table-container">
                        <h2>Existing Categories</h2>
                        <table>
                            <thead><tr><th>ID</th><th>Name</th><th>Actions</th></tr></thead>
                            <tbody>
                                <?php
                                $cats_result = mysqli_query($conn, "SELECT * FROM categories ORDER BY name");
                                while ($cat = mysqli_fetch_assoc($cats_result)): ?>
                                <tr>
                                    <td><?= (int)$cat['id'] ?></td>
                                    <td><?= htmlspecialchars($cat['name']) ?></td>
                                    <td class="actions">
                                        <a href="admin_dashboard.php?page=categories&action=edit&id=<?= (int)$cat['id'] ?>" class="btn btn-sm">Edit</a>
                                        <form action="admin_dashboard.php?page=categories" method="POST" onsubmit="return confirm('Delete this category?');" style="display:inline;"><input type="hidden" name="action" value="delete_category"><input type="hidden" name="category_id" value="<?= (int)$cat['id'] ?>"><button type="submit" class="btn btn-sm btn-danger">Delete</button></form>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php
                    break;

                // ================== USERS PAGE ==================
                case 'users':
                    $edit_user = null;
                    if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
                        $stmt = mysqli_prepare($conn, "SELECT id, name, email FROM users WHERE id = ?");
                        mysqli_stmt_bind_param($stmt, "i", $_GET['id']);
                        mysqli_stmt_execute($stmt);
                        $edit_user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
                    }
                    ?>
                    <div class="admin-header"><h1>Manage Users</h1></div>
                    <div class="form-container">
                        <h2><?= $edit_user ? 'Edit User' : 'Add New User' ?></h2>
                        <form action="admin_dashboard.php?page=users" method="POST">
                             <input type="hidden" name="action" value="<?= $edit_user ? 'edit_user' : 'add_user' ?>">
                            <?php if ($edit_user): ?><input type="hidden" name="user_id" value="<?= (int)$edit_user['id'] ?>"><?php endif; ?>
                            <div class="form-group"><label for="name">Full Name</label><input type="text" name="name" value="<?= htmlspecialchars($edit_user['name'] ?? '') ?>" required></div>
                            <div class="form-group"><label for="email">Email</label><input type="email" name="email" value="<?= htmlspecialchars($edit_user['email'] ?? '') ?>" required></div>
                            <div class="form-group"><label for="password">Password</label><input type="password" name="password" <?= !$edit_user ? 'required' : '' ?>><?php if ($edit_user): ?><small>Leave blank to keep current password.</small><?php endif; ?></div>
                            <button type="submit" class="btn"><?= $edit_user ? 'Update User' : 'Add User' ?></button>
                            <?php if ($edit_user): ?><a href="admin_dashboard.php?page=users" class="btn btn-secondary">Cancel Edit</a><?php endif; ?>
                        </form>
                    </div>
                     <div class="table-container">
                        <h2>Existing Users</h2>
                        <table>
                            <thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Joined</th><th>Actions</th></tr></thead>
                            <tbody>
                                <?php
                                $users_result = mysqli_query($conn, "SELECT * FROM users ORDER BY id DESC");
                                while ($user = mysqli_fetch_assoc($users_result)): ?>
                                <tr>
                                    <td><?= (int)$user['id'] ?></td>
                                    <td><?= htmlspecialchars($user['name']) ?></td>
                                    <td><?= htmlspecialchars($user['email']) ?></td>
                                    <td><?= date('M j, Y', strtotime($user['created_at'])) ?></td>
                                    <td class="actions">
                                        <a href="admin_dashboard.php?page=users&action=edit&id=<?= (int)$user['id'] ?>" class="btn btn-sm">Edit</a>
                                        <form action="admin_dashboard.php?page=users" method="POST" onsubmit="return confirm('Delete this user?');" style="display:inline;"><input type="hidden" name="action" value="delete_user"><input type="hidden" name="user_id" value="<?= (int)$user['id'] ?>"><button type="submit" class="btn btn-sm btn-danger">Delete</button></form>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php
                    break;

                // ================== USER CARTS PAGE ==================
                case 'carts':
                    ?>
                    <div class="admin-header"><h1>Track User Carts</h1></div>
                    <div class="table-container">
                        <h2>User Carts</h2>
                        <?php
                        // Get all users for filter
                        $users_result = mysqli_query($conn, "SELECT id, name FROM users ORDER BY name");
                        $users = [];
                        while ($user = mysqli_fetch_assoc($users_result)) {
                            $users[$user['id']] = $user['name'];
                        }
                        ?>
                        <div class="user-filter">
                            <form method="GET">
                                <input type="hidden" name="page" value="carts">
                                <label for="user_id">Filter by User:</label>
                                <select name="user_id" id="user_id">
                                    <option value="">All Users</option>
                                    <?php foreach ($users as $id => $name): ?>
                                        <option value="<?= $id ?>" <?= ($user_id_filter == $id) ? 'selected' : '' ?>><?= htmlspecialchars($name) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="submit" class="btn">Filter</button>
                                <?php if ($user_id_filter): ?>
                                    <a href="admin_dashboard.php?page=carts" class="btn btn-secondary">Clear Filter</a>
                                <?php endif; ?>
                            </form>
                        </div>
                        <table>
                            <thead><tr><th>User</th><th>Product</th><th>Quantity</th><th>Added At</th><th>Actions</th></tr></thead>
                            <tbody>
                                <?php
                                $query = "SELECT c.*, u.name AS user_name, p.name AS product_name FROM cart c JOIN users u ON c.user_id = u.id JOIN products p ON c.product_id = p.id";
                                if ($user_id_filter) {
                                    $query .= " WHERE c.user_id = " . (int)$user_id_filter;
                                }
                                $query .= " ORDER BY c.user_id, c.added_at DESC";
                                $carts_result = mysqli_query($conn, $query);
                                
                                if (mysqli_num_rows($carts_result) > 0) {
                                    while ($cart_item = mysqli_fetch_assoc($carts_result)): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($cart_item['user_name']) ?></td>
                                        <td><?= htmlspecialchars($cart_item['product_name']) ?></td>
                                        <td><?= (int)$cart_item['quantity'] ?></td>
                                        <td><?= date('M j, Y, g:i A', strtotime($cart_item['added_at'])) ?></td>
                                        <td class="actions">
                                            <form action="admin_dashboard.php?page=carts<?= $user_id_filter ? "&user_id=$user_id_filter" : "" ?>" method="POST" onsubmit="return confirm('Remove this item from cart?');" style="display:inline;">
                                                <input type="hidden" name="action" value="remove_cart_item">
                                                <input type="hidden" name="cart_id" value="<?= (int)$cart_item['id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-danger">Remove</button>
                                            </form>
                                        </td>
                                    </tr>
                                    <?php endwhile;
                                } else {
                                    echo '<tr><td colspan="5">No items in carts.</td></tr>';
                                } ?>
                            </tbody>
                        </table>
                    </div>
                    <?php
                    break;

                // ================== ORDERS PAGE ==================
                case 'orders':
                     ?>
                    <div class="admin-header"><h1>Review Orders</h1></div>
                    <div class="table-container">
                        <h2>All Customer Orders</h2>
                        <?php
                        // Get all users for filter
                        $users_result = mysqli_query($conn, "SELECT id, name FROM users ORDER BY name");
                        $users = [];
                        while ($user = mysqli_fetch_assoc($users_result)) {
                            $users[$user['id']] = $user['name'];
                        }
                        ?>
                        <div class="user-filter">
                            <form method="GET">
                                <input type="hidden" name="page" value="orders">
                                <label for="user_id">Filter by User:</label>
                                <select name="user_id" id="user_id">
                                    <option value="">All Users</option>
                                    <?php foreach ($users as $id => $name): ?>
                                        <option value="<?= $id ?>" <?= ($user_id_filter == $id) ? 'selected' : '' ?>><?= htmlspecialchars($name) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="submit" class="btn">Filter</button>
                                <?php if ($user_id_filter): ?>
                                    <a href="admin_dashboard.php?page=orders" class="btn btn-secondary">Clear Filter</a>
                                <?php endif; ?>
                            </form>
                        </div>
                        <table>
                            <thead><tr><th>Order ID</th><th>Customer</th><th>Total</th><th>Payment</th><th>Address</th><th>Date</th></tr></thead>
                            <tbody>
                                <?php
                                $query = "SELECT o.*, u.name AS user_name FROM orders o JOIN users u ON o.user_id = u.id";
                                if ($user_id_filter) {
                                    $query .= " WHERE o.user_id = " . (int)$user_id_filter;
                                }
                                $query .= " ORDER BY o.order_date DESC";
                                $orders_result = mysqli_query($conn, $query);
                                
                                if (mysqli_num_rows($orders_result) > 0) {
                                    while ($order = mysqli_fetch_assoc($orders_result)): ?>
                                    <tr class="order-header">
                                        <td><?= (int)$order['id'] ?></td>
                                        <td><?= htmlspecialchars($order['user_name']) ?></td>
                                        <td>₹<?= htmlspecialchars(number_format($order['total_amount'] * 83, 2)) ?></td>
                                        <td><?= htmlspecialchars($order['payment_method']) ?></td>
                                        <td style="white-space:normal;"><?= htmlspecialchars($order['delivery_address']) ?></td>
                                        <td><?= date('M j, Y, g:i A', strtotime($order['order_date'])) ?></td>
                                    </tr>
                                    <tr class="order-items-row">
                                        <td colspan="6">
                                            <div class="order-items-container">
                                                <h4>Items in Order #<?= (int)$order['id'] ?>:</h4>
                                                <ul>
                                                    <?php
                                                    $items_stmt = mysqli_prepare($conn, "SELECT oi.quantity, oi.price, p.name AS product_name FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
                                                    mysqli_stmt_bind_param($items_stmt, "i", $order['id']);
                                                    mysqli_stmt_execute($items_stmt);
                                                    $items_result = mysqli_stmt_get_result($items_stmt);
                                                    while ($item = mysqli_fetch_assoc($items_result)): ?>
                                                        <li><?= (int)$item['quantity'] ?> x <strong><?= htmlspecialchars($item['product_name']) ?></strong> (@ ₹<?= htmlspecialchars(number_format($item['price'] * 83, 2)) ?> each)</li>
                                                    <?php endwhile; ?>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endwhile;
                                } else {
                                    echo '<tr><td colspan="6">No orders found.</td></tr>';
                                } ?>
                            </tbody>
                        </table>
                    </div>
                    <?php
                    break;

                // ================== FEEDBACK PAGE ==================
                case 'feedback':
                    ?>
                    <div class="admin-header"><h1>Review Feedback</h1></div>
                    <div class="table-container">
                        <h2>All Feedback Submissions</h2>
                        <table>
                            <thead><tr><th>ID</th><th>From</th><th>Email</th><th style="width: 40%;">Message</th><th>Date</th><th>Action</th></tr></thead>
                            <tbody>
                                <?php
                                $feedback_result = mysqli_query($conn, "SELECT f.*, u.name AS user_name FROM feedback f LEFT JOIN users u ON f.user_id = u.id ORDER BY f.date_submitted DESC");
                                if (mysqli_num_rows($feedback_result) > 0) {
                                while ($fb = mysqli_fetch_assoc($feedback_result)): ?>
                                <tr>
                                    <td><?= (int)$fb['id'] ?></td>
                                    <td><?= htmlspecialchars($fb['user_name'] ?? $fb['name']) ?></td>
                                    <td><?= htmlspecialchars($fb['email']) ?></td>
                                    <td style="white-space:normal;"><?= htmlspecialchars($fb['message']) ?></td>
                                    <td><?= date('M j, Y', strtotime($fb['date_submitted'])) ?></td>
                                    <td class="actions">
                                        <form action="admin_dashboard.php?page=feedback" method="POST" onsubmit="return confirm('Delete this feedback?');" style="display:inline;"><input type="hidden" name="action" value="delete_feedback"><input type="hidden" name="feedback_id" value="<?= (int)$fb['id'] ?>"><button type="submit" class="btn btn-sm btn-danger">Delete</button></form>
                                    </td>
                                </tr>
                                <?php endwhile;
                                } else {
                                    echo '<tr><td colspan="6">No feedback found.</td></tr>';
                                } ?>
                            </tbody>
                        </table>
                    </div>
                    <?php
                    break;
                    
                // ================== HOME PAGE (DEFAULT) ==================
                case 'home':
                default:
                    $user_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM users"))['c'];
                    $product_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM products"))['c'];
                    $order_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM orders"))['c'];
                    $cart_items_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM cart"))['c'];
                    ?>
                    <div class="admin-header">
                        <h1>Admin Dashboard</h1>
                        <p>Welcome, <?= htmlspecialchars($_SESSION['admin_username']); ?>!</p>
                    </div>
                    <div class="dashboard-stats">
                        <div class="stat-card"><h3>Total Users</h3><p><?= $user_count ?></p><a href="admin_dashboard.php?page=users">Manage Users</a></div>
                        <div class="stat-card"><h3>Total Products</h3><p><?= $product_count ?></p><a href="admin_dashboard.php?page=products">Manage Products</a></div>
                        <div class="stat-card"><h3>Total Orders</h3><p><?= $order_count ?></p><a href="admin_dashboard.php?page=orders">View Orders</a></div>
                        <div class="stat-card"><h3>Items in Carts</h3><p><?= $cart_items_count ?></p><a href="admin_dashboard.php?page=carts">View Carts</a></div>
                    </div>
                    <?php
                    break;
            }
        ?>
    </main>
</div>
</body>
</html>