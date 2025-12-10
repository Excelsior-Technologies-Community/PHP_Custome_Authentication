<?php
// public/dashboard.php

// Include authentication check to ensure only logged-in users can access
require_once "../includes/auth.php";

// Include header HTML (opening tags, CSS, etc.)
require_once "../includes/header.php";
?>

<h2>Dashboard</h2>

<!-- Display welcome message with the logged-in user's name -->
<p>Welcome, <?= htmlspecialchars($_SESSION['custome_name']); ?>!</p>

<!-- Optional: Display session ID for debugging purposes (commented out) -->
<!-- <p class="small">Session ID: <?= session_id() ?></p> -->

<!-- Navigation links for logged-in user -->
<div>
  <a href="logout.php">Logout</a> <!-- Link to logout and destroy session -->
  &nbsp;|&nbsp;
  <a href="user.php">User list</a> <!-- Link to view all users (protected page) -->
</div>

<?php 
// Include footer HTML (closing tags)
require_once "../includes/footer.php"; 
?>
