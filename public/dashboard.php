<?php
require_once "../includes/auth.php";
require_once "../includes/header.php";
?>

<h2>Dashboard</h2>

<p>Welcome, <?= htmlspecialchars($_SESSION['custome_name']); ?>!</p>

<div>
  <a href="profile.php">Profile</a>
  &nbsp;|&nbsp;
  <a href="user.php">User list</a>
  &nbsp;|&nbsp;
  <a href="logout.php">Logout</a>
</div>

<?php 
require_once "../includes/footer.php"; 
?>