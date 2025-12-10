<?php
// public/register.php

// Start PHP session to handle session variables for user login and messages
session_start();

// Include database connection file to interact with MySQL
require_once "../config/database.php";

// Include helper functions (e.g., redirect, old input value, etc.)
require_once "../core/helpers.php";

// Initialize error variable to store validation or registration errors
$error = '';

// Check if the form has been submitted via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Get form input values and trim spaces
    $name  = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';
    $pass2 = $_POST['password_confirm'] ?? '';

    // Validation: check required fields
    if ($name === '' || $email === '' || $pass === '') {
        $error = "All fields are required.";
    } 
    // Validation: check email format
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } 
    // Validation: check if passwords match
    elseif ($pass !== $pass2) {
        $error = "Passwords do not match.";
    } 
    else {
        // Check if the email already exists in the database
        $stmt = $pdo->prepare("SELECT id FROM custome WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);

        if ($stmt->fetch()) {
            // Email already exists
            $error = "Email already registered.";
        } 
        else {
            // Hash the password securely
            $hash = password_hash($pass, PASSWORD_DEFAULT);

            // Insert new user record into 'custome' table
            $stmt = $pdo->prepare(
                "INSERT INTO custome (name, email, password, status, created_by) VALUES (?, ?, ?, 1, NULL)"
            );
            $stmt->execute([$name, $email, $hash]);

            // Redirect to login page after successful registration
            header("Location: login.php");
            exit;
        }
    }
}
?>

<?php 
// Include the common header HTML
require_once "../includes/header.php"; 
?>

<h2>Register</h2>

<!-- Display error message if there is any -->
<?php if ($error): ?>
  <div class="error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<!-- Registration form -->
<form method="post" action="">
  <div class="form-group">
    <label>Name</label>
    <!-- Keep previous input value if form submission fails -->
    <input type="text" name="name" value="<?= old('name') ?>">
  </div>

  <div class="form-group">
    <label>Email</label>
    <input type="email" name="email" value="<?= old('email') ?>">
  </div>

  <div class="form-group">
    <label>Password</label>
    <input type="password" name="password">
  </div>

  <div class="form-group">
    <label>Confirm Password</label>
    <input type="password" name="password_confirm">
  </div>

  <button type="submit">Register</button>
</form>

<!-- Link to login page for existing users -->
<p class="link small">Already have an account? <a href="login.php">Login here</a></p>

<?php 
// Include the common footer HTML
require_once "../includes/footer.php"; 
?>
