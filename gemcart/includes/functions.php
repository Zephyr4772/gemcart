function displayMessage() {
    if (isset($_SESSION['message'])) {
        echo '<div class="alert">' . htmlspecialchars($_SESSION['message']) . '</div>';
        unset($_SESSION['message']);
    }
} 