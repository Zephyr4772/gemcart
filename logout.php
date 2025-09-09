<?php
session_start();

// Clear cart from session
unset($_SESSION['cart']);

// Destroy all session data
session_destroy();

// Redirect to homepage
header("Location: index.php");
exit();
?> 