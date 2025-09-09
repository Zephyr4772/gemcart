<?php
// PHP error reporting for development (remove/comment out in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// IMPORTANT: session_start() should typically be called once at the very top of the main PHP page
// that includes this header, not within the header itself, to prevent "headers already sent" errors.
// If this header is included in every page, ensure it's loaded AFTER session_start() in the main page.

// If isLoggedIn function is not defined elsewhere (e.g., functions.php), define it here.
// Best practice is to define it once in functions.php and include functions.php on every page.
if (!function_exists('isLoggedIn')) {
    function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }
}

// Get the current page filename to apply 'active' class
$current_page = basename($_SERVER['PHP_SELF']);
?>

<link href="https://fonts.googleapis.com/css?family=Pacifico&display=swap" rel="stylesheet">
<script src="js/cart.js"></script>

<header class="header">
    <div class="container header-modern">
        <nav class="main-nav">
            <ul class="nav-menu">
                <li><a href="index.php" class="<?php echo $current_page == 'index.php' ? 'active' : ''; ?>">HOME</a></li>
                <li><a href="products.php" class="<?php echo $current_page == 'products.php' ? 'active' : ''; ?>">PRODUCTS</a></li>
                <li><a href="about.php" class="<?php echo $current_page == 'about.php' ? 'active' : ''; ?>">ABOUT</a></li>
                <li><a href="contact.php" class="<?php echo $current_page == 'contact.php' ? 'active' : ''; ?>">CONTACT</a></li>
            </ul>
        </nav>
        <div class="logo logo-modern">
            <a href="index.php"><h1 style="font-family: 'Pacifico', cursive; color: #003152 !important; font-size: 2.8rem; letter-spacing: 7px; margin: 0; margin-left: 3.5rem; padding: 0 2.5rem; min-width: 340px; display: inline-block;">cherry charms</h1></a>
        </div>
        <div class="header-actions-modern">
            <a href="about.php" class="icon-link" title="About Us"><i class="fa fa-info-circle"></i></a>
            <a href="feedback.php" class="icon-link" title="Feedback"><i class="fa fa-commenting"></i></a>
            <div class="user-dropdown">
                <a href="#" class="icon-link" title="User"><i class="fa fa-user"></i></a>
                <div class="user-dropdown-content">
                <?php if (!isLoggedIn()): ?>
                    <a href="register.php">Register</a>
                    <a href="login.php">Login</a>
                <?php else: ?>
                    <a href="account.php">My Account</a>
                    <a href="logout.php">Logout</a>
                <?php endif; ?>
                </div>
            </div>
            <?php if (isLoggedIn()): ?>
            <a href="cart_client.php" class="icon-link cart-link" title="Cart">
                <i class="fa fa-shopping-bag"></i>
                <span id="cart-count" class="cart-count" style="display: none;">0</span>
            </a>
            <?php endif; ?>
        </div>
    </div>
</header>
<?php
// This displayMessage() function call should only be here if it's the standard way
// you display messages across all pages, otherwise it might be redundant or misplaced.
if (function_exists('displayMessage')) {
    echo displayMessage();
}
?>