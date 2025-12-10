<?php
// public/logout.php

// Start session to access session variables
session_start();

// Clear all session data
$_SESSION = [];

// Destroy the session completely
session_destroy();

// Redirect user to login page after logout
header("Location: login.php");
exit;
