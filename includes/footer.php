<footer class="footer">
    <div class="container">
        <div class="footer-content">
            <div class="footer-section">
                <h3>GemCart</h3>
                <p>Your trusted source for premium jewelry</p>
            </div>
            
            <div class="footer-section">
                <h4>Quick Links</h4>
                <ul>
                    <li><a href="products.php">All Products</a></li>
                    <li><a href="about.php">About Us</a></li>
                    <li><a href="authenticity.php">Authenticity</a></li>
                    <li><a href="contact.php">Contact</a></li>
                </ul>
            </div>
            
            <div class="footer-section">
                <h4>Customer Service</h4>
                <ul>
                    <li><a href="feedback.php">Send Feedback</a></li>
                    <li><a href="about.php">Store Information</a></li>
                </ul>
            </div>
        </div>
        
        <?php if (function_exists('isLoggedIn') && isLoggedIn()): ?>
            <div style="text-align:center; margin-top:1.5rem;">
                <a href="logout.php" class="footer-logout-btn">Logout</a>
            </div>
        <?php endif; ?>

        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> GemCart. All rights reserved.</p>
        </div>
    </div>
</footer> 