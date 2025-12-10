<?php
// includes/auth.php

// Start PHP session to access session variables
session_start();

// Check if the user is logged in by looking for 'custome_id' in session
if (!isset($_SESSION['custome_id'])) {
    // If not logged in, redirect the user to the login page
    header("Location: login.php");
    exit; // Stop further script execution
}
