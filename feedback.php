<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/functions.php';

$user = getCurrentUser();
$errors = [];
$success = '';

// Issue types for the dropdown
$issue_types = ['Product', 'Order', 'Payment', 'Website', 'Other'];

// Handle form submission logic ONLY if logged in and it's a POST request
if (isLoggedIn() && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $issue_type = isset($_POST['issue_type']) ? sanitizeInput($_POST['issue_type']) : '';
    $message = sanitizeInput($_POST['message'] ?? '');
    if (empty($issue_type) || !in_array($issue_type, $issue_types)) {
        $errors[] = 'Please select a valid issue type.';
    }
    if (empty($message)) {
        $errors[] = 'Feedback message cannot be empty.';
    }
    if (empty($errors)) {
        // Check if 'issue_type' column exists in feedback table
        $col_check = mysqli_query($conn, "SHOW COLUMNS FROM feedback LIKE 'issue_type'");
        if (mysqli_num_rows($col_check) == 0) {
            $errors[] = 'Database missing column: issue_type. Please run: ALTER TABLE feedback ADD COLUMN issue_type VARCHAR(50) DEFAULT NULL;';
        } else {
            $query = "INSERT INTO feedback (user_id, name, email, message, issue_type) VALUES (?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, 'issss', $user['id'], $user['name'], $user['email'], $message, $issue_type);
            if (mysqli_stmt_execute($stmt)) {
                $success = 'Thank you for your feedback!';
                // Clear POST data after successful submission to prevent resubmission on refresh
                $_POST = array();
            } else {
                $errors[] = 'Failed to submit feedback. Please try again.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feedback - Cherry Charms</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php
    // Include the header BEFORE the main content starts.
    // This ensures your header is always loaded with its styles and scripts.
    include 'includes/header.php';
?>

<main>
    <div class="form-container">
        <h2>Send Feedback</h2>
        <?php if (!isLoggedIn()): // If user is NOT logged in, show the message ?>
            <div class="alert alert-error">You must be logged in to submit feedback. <a href="login.php">Login here</a>.</div>
        <?php else: // If user IS logged in, show the feedback form ?>
            <?php if (!empty($success)): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <?php foreach ($errors as $error): ?>
                        <p><?php echo htmlspecialchars($error); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="issue_type">Type of Issue</label>
                    <select id="issue_type" name="issue_type" required>
                        <option value="">-- Select Issue Type --</option>
                        <?php foreach ($issue_types as $type): ?>
                            <option value="<?php echo $type; ?>" <?php if (isset($_POST['issue_type']) && $_POST['issue_type'] === $type) echo 'selected'; ?>><?php echo $type; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="message">Your Feedback</label>
                    <textarea id="message" name="message" rows="5" required><?php echo isset($_POST['message']) ? htmlspecialchars($_POST['message']) : ''; ?></textarea>
                </div>
                <button type="submit" class="btn">Submit Feedback</button>
            </form>
        <?php endif; ?>
    </div>
</main>

<?php
    // Include the footer AFTER the main content.
    include 'includes/footer.php';
?>
</body>
</html>