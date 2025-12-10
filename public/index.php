<?php
// public/index.php

// Start PHP session to track user login and session data
session_start();
?>

<?php 
// Include the header file which contains the opening HTML tags and CSS links
require_once __DIR__ . "/../includes/header.php"; 
?>

<h2>PHP Custom Authentication</h2>

<p>Use the links below:</p>

<!-- Navigation links for the user -->
<div class="nav">
    <a href="register.php">Register</a> <!-- Link to registration page -->
    <a href="login.php">Login</a>       <!-- Link to login page -->
    <a href="dashboard.php">Dashboard</a>  <!--  it can be enabled after login is implemented -->
</div>

<?php 
// Include the footer file which contains the closing HTML tags
require_once __DIR__ . "/../includes/footer.php"; 
?>
