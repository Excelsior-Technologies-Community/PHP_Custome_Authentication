<?php
session_start();

// Clear session
$_SESSION = [];
session_destroy();

// ✅ Remove remember me cookie
setcookie("email", "", time() - 3600, "/");

// Redirect
header("Location: login.php");
exit;