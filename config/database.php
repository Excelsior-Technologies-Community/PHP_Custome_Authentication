<?php
// config/database.php

// Database connection settings
$host = "localhost";      // Database host (usually localhost)
$dbname = "php_auth";     // Name of the database
$username = "root";       // Database username
$password = "";           // Database password (set if any)

// Try to establish a connection to the database using PDO
try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8", // DSN: Data Source Name
        $username,                                     // DB username
        $password                                      // DB password
    );

    // Set error mode to exception, so errors throw exceptions
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Optional: Set default fetch mode to associative array
    // This means $stmt->fetch() will return an array with column names as keys
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    // If connection fails, show an error message and stop execution
    // In production, avoid showing detailed errors for security reasons
    die("Database connection failed: " . $e->getMessage());
}
