<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/functions.php';

$errors = [];
$success = '';

// If already logged in, redirect to dashboard
if (isset($_SESSION['admin_id'])) {
    header('Location: admin_dashboard.php');
    exit();
}

// -------------------- HANDLE LOGIN --------------------
if (isset($_POST['action']) && $_POST['action'] === 'login') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $errors[] = 'Username and password are required.';
    }

    if (empty($errors)) {
        $stmt = mysqli_prepare($conn, 'SELECT * FROM admins WHERE username = ? LIMIT 1');
        mysqli_stmt_bind_param($stmt, 's', $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($admin = mysqli_fetch_assoc($result)) {
            // Check hashed password OR plaintext password for fallback
            if (password_verify($password, $admin['password']) || ($password === $admin['password'])) {
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_username'] = $admin['username'];
                $_SESSION['flash_message'] = 'Welcome back, ' . htmlspecialchars($admin['username']) . '!';
                header('Location: admin_dashboard.php');
                exit();
            }
        }
        $errors[] = 'Invalid username or password.';
    }
}

// -------------------- HANDLE ADD NEW ADMIN --------------------
if (isset($_POST['action']) && $_POST['action'] === 'add_admin') {
    $username      = trim($_POST['username'] ?? '');
    $password      = $_POST['password'] ?? '';
    $confirm       = $_POST['confirm_password'] ?? '';
    $special_code  = trim($_POST['special_code'] ?? '');

    // Note: The 'special_code' is hardcoded here.
    if ($special_code !== '1a2b3c4d') {
        $errors[] = 'Invalid special code.';
    }
    if (empty($username) || empty($password)) {
        $errors[] = 'Username and password are required.';
    } elseif ($password !== $confirm) {
        $errors[] = 'Passwords do not match.';
    }

    // Check username uniqueness
    if (empty($errors)) {
        $stmt = mysqli_prepare($conn, 'SELECT id FROM admins WHERE username = ?');
        mysqli_stmt_bind_param($stmt, 's', $username);
        mysqli_stmt_execute($stmt);
        mysqli_store_result($stmt);
        if (mysqli_stmt_num_rows($stmt) > 0) {
            $errors[] = 'Username already exists.';
        }
    }

    // Insert new admin
    if (empty($errors)) {
        $hash = password_hash($password, PASSWORD_DEFAULT); // Always hash new passwords
        $stmt = mysqli_prepare($conn, 'INSERT INTO admins (username, password) VALUES (?, ?)');
        mysqli_stmt_bind_param($stmt, 'ss', $username, $hash);
        if (mysqli_stmt_execute($stmt)) {
            $success = 'New admin created successfully! You can now log in.';
        } else {
            $errors[] = 'Failed to create new admin.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - GemCart</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; }
        .admin-login-container { max-width: 400px; margin: 60px auto; background: #fff; padding: 20px 30px; border-radius: 6px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        h2 { text-align: center; margin-bottom: 1rem; color: #333; }
        .form-group { margin-bottom: 1rem; }
        label { display: block; margin-bottom: 0.5rem; font-weight: 600; }
        input[type=text], input[type=password], input[type=email] { width: 100%; padding: 8px 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        .btn { width: 100%; padding: 10px; background: #003152; color: #fff; border: none; border-radius: 4px; cursor: pointer; margin-top: 0.5rem; }
        .btn:hover { background: #001d33; }
        .alert { padding: 12px 15px; border-radius: 4px; margin-bottom: 1rem; border: 1px solid transparent; }
        .alert-error { background: #f8d7da; color: #721c24; border-color: #f5c6cb; }
        .alert-success { background: #d4edda; color: #155724; border-color: #c3e6cb; }
        .toggle-link { text-align: center; margin-top: 1rem; }
        .toggle-link a { color: #003152; text-decoration: none; cursor: pointer; }
    </style>
    <script>
        function toggleForm(target) {
            document.getElementById('login-form').style.display = target === 'login' ? 'block' : 'none';
            document.getElementById('add-form').style.display   = target === 'add'   ? 'block' : 'none';
        }
    </script>
</head>
<body>
    <div class="admin-login-container">
        <h2>Admin Login</h2>
        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <?php foreach ($errors as $e): echo '<p style="margin:0;">'.htmlspecialchars($e).'</p>'; endforeach; ?>
            </div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <form id="login-form" method="POST" style="display: <?= isset($_POST['action']) && $_POST['action'] === 'add_admin' ? 'none' : 'block'; ?>;">
            <input type="hidden" name="action" value="login">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button class="btn" type="submit">Login</button>
            <p class="toggle-link"><a onclick="toggleForm('add')">Need to add another admin?</a></p>
        </form>

        <form id="add-form" method="POST" style="display: <?= isset($_POST['action']) && $_POST['action'] === 'add_admin' ? 'block' : 'none'; ?>;">
            <input type="hidden" name="action" value="add_admin">
            <div class="form-group">
                <label for="new_username">Username</label>
                <input type="text" id="new_username" name="username" required>
            </div>
            <div class="form-group">
                <label for="new_password">Password</label>
                <input type="password" id="new_password" name="password" required>
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>
            <div class="form-group">
                <label for="special_code">Special Code</label>
                <input type="text" id="special_code" name="special_code" placeholder="Enter special code" required>
            </div>
            <button class="btn" type="submit">Add Admin</button>
            <p class="toggle-link"><a onclick="toggleForm('login')">Back to Login</a></p>
        </form>
    </div>
</body>
</html>