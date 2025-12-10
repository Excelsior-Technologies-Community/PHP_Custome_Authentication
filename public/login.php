<?php
// public/login.php

// Start PHP session to manage user login
session_start();

// Include database connection to query user data
require_once "../config/database.php";

// Include helper functions (e.g., old input, redirect)
require_once "../core/helpers.php";

// Initialize error message variable
$error = '';

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Get form inputs and trim spaces
    $email = trim($_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';

    // Validate that both fields are filled
    if ($email === '' || $pass === '') {
        $error = "Both fields are required.";
    } else {
        // Query database for an active user with the given email
        $stmt = $pdo->prepare(
            "SELECT * FROM custome WHERE email = ? AND status = 1 AND deleted_at IS NULL LIMIT 1"
        );
        $stmt->execute([$email]);
        $custome = $stmt->fetch(PDO::FETCH_ASSOC);

        // Check if user exists and password matches
        if ($custome && password_verify($pass, $custome['password'])) {
            // Set meaningful session keys
            $_SESSION['custome_id']   = $custome['id'];
            $_SESSION['custome_name'] = $custome['name'];

            // Protect against session fixation attacks
            session_regenerate_id(true);

            // Redirect to dashboard after successful login
            header("Location: dashboard.php");
            exit;
        } else {
            // Show error if credentials are invalid or account inactive
            $error = "Invalid credentials or inactive account.";
        }
    }
}
?>

<?php 
// Include common header HTML
require_once "../includes/header.php"; 
?>

<h2>Login</h2>

<!-- Display error message if exists -->
<?php if ($error): ?>
  <div class="error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<!-- Login form -->
<form method="post" action="">
  <div class="form-group">
    <label>Email</label>
    <!-- Retain old email input in case of error -->
    <input type="email" name="email" value="<?= old('email') ?>">
  </div>

  <div class="form-group">
    <label>Password</label>
    <input type="password" name="password">
  </div>

  <button type="submit">Login</button>
</form>

<!-- Link to registration page for new users -->
<p class="link small">Don't have an account? <a href="register.php">Register</a></p>

<?php 
// Include common footer HTML
require_once "../includes/footer.php"; 
?>
