<?php
// core/helpers.php

// Check if a user is logged in
// Returns true if 'custome_id' exists in session, false otherwise
function isLoggedIn() {
    return isset($_SESSION['custome_id']);
}

// Redirect to a given URL
// Stops further execution after redirect
function redirect($url) {
    header("Location: $url"); // Send HTTP header to redirect
    exit;                     // Stop script execution immediately
}

// Retrieve old input value after form submission
// Helps to repopulate form fields if validation fails
function old($key) {
    // Check if the key exists in $_POST, sanitize with htmlspecialchars, else return empty string
    return isset($_POST[$key]) ? htmlspecialchars($_POST[$key]) : '';
}
