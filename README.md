# PHP_Custome_Authentication

## Introduction

This repository is a minimal, production-minded **Core PHP** project showing how to implement a full authentication flow without a framework. It focuses on clarity, secure database access with **PDO**, and practical patterns used in real web apps: hashed passwords, session protection, audit columns (`created_by`, `updated_by`), and *soft deletes* (`deleted_at`) so data can be restored if needed.

The app is intentionally small so learners can inspect and understand every line of code. It is suitable for personal learning, interviews, or as a starter boilerplate for small projects.

---

## Project overview

* **Language:** PHP (Core PHP, no framework)  
* **Database:** MySQL/MariaDB (via PDO)  
* **Server:** Apache (XAMPP / Laragon / MAMP / LAMP)  
* **Frontend:** Plain HTML + CSS (single stylesheet in `public/css/style.css`)  
* **Web root:** `public/`  

Key ideas implemented:

* Secure DB queries with prepared statements (PDO)  
* `password_hash()` / `password_verify()` for passwords  
* Session-based login with session regeneration  
* `custome` table with `status`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`  
* Soft delete updates `deleted_at` and sets `status = 0`  
* Simple user listing and admin-style delete action  

---

## Features

* Register & Login (email + password)  
* Protected dashboard (session checks)  
* User list showing only active users  
* Soft delete (sets `deleted_at` and `status = 0`, stores `updated_by`)  
* PDO-based DB handling  
* Clear folder separation: config, core helpers, includes, public files  

---

**Why `public/` as web root?**  
Keep sensitive PHP code (config, core, includes) outside the web root in production. For local dev we keep them adjacent but use `public/` as the served folder to show the correct structure.

---

## Installation & setup — step by step

Follow these steps exactly on your local machine.

### 1) Install local server

Use one of the common stacks:

* **Windows:** XAMPP or Laragon  
* **macOS:** MAMP or Homebrew (`php`, `mysql`)  
* **Linux:** setup LAMP (`apache2`, `php`, `mysql-server`)  

Start **Apache** and **MySQL**.

### 2) Place project in web root

* XAMPP: `C:\xampp\htdocs\PHP_Custome_Authentication`  
* Laragon: `C:\laragon\www\PHP_Custome_Authentication`  
* macOS/Linux: `/var/www/html/PHP_Custome_Authentication`  

Ensure the `public/` folder exists and contains `index.php`.

#### Step 1: Project Folder Setup

Open your terminal (Command Prompt) and run:

```bash
cd C:\xampp\htdocs
mkdir PHP_Custome_Authentication
cd PHP_Custome_Authentication
mkdir config core includes public public\css sql

```
After this you should have:
```

PHP_Custome_Authentication/
├── config/
├── core/
├── includes/
├── public/
│   └── css/
└── sql/

```
config/ → Database configuration

core/ → Helper functions and core logic

includes/ → Header, footer, auth checks, etc.

public/ → CSS, JS, images for front-end

sql/ → SQL scripts to create tables

---
Step 2: Create database and import schema

Open phpMyAdmin or MySQL CLI and run the SQL in sql/custome.sql. Example:
```
CREATE DATABASE php_auth;
USE php_auth;

CREATE TABLE custome (
    id INT AUTO_INCREMENT PRIMARY KEY,

    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,

    status TINYINT DEFAULT 1 COMMENT '1=Active, 0=Inactive',

    created_by INT DEFAULT NULL,
    updated_by INT DEFAULT NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    deleted_at TIMESTAMP NULL DEFAULT NULL
);

```

Step 3: Configure Database Connection

File: config/database.php
```
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

```
Step 4: Core Helper Functions

File: core/helpers.php
```
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

```
These helpers handle redirects, login checks, and input sanitization.
---

Step 5: Create Includes (Header, Footer, Auth Check)

1. Header: includes/header.php
```
<?php
// includes/header.php
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>PHP Custome Authentication</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
  <div class="container">

```
2. Footer: includes/footer.php
```
<?php
// includes/footer.php
?>
  </div> <!-- /.container -->
</body>
</html>


```
3. Auth Check: includes/auth.php
```
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

```
Step 6: Authentication PHP Files

---

6.1) index.php: public/index.php

This is the index page of the project. It provides navigation links for the user to go to the registration or login pages.
It also starts a session to handle user login status if needed in the future.

```
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

```
6.2) register.php: public/register.php

This file handles user registration. It allows a new user to register by providing their name, email, and password.
It includes validation for empty fields, valid email format, password matching, and checks if the email already exists in the database. After successful registration, the user is redirected to the login page.

```
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

```
6.3) login.php: public/login.php

This file handles user login. It validates the input fields, checks the database for a matching active user, verifies the password, and starts a session for the logged-in user.
It also includes session security measures like session_regenerate_id() to prevent session fixation. On successful login, the user is redirected to the dashboard.
```
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



```
6.4) dashboard.php: public/dashboard.php

This file is the dashboard page visible to logged-in users. It requires authentication and welcomes the user by displaying their name stored in the session.
It also provides links for logging out and viewing the user list.
```

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

```
6.5) user.php: public/user.php

This file displays a list of registered users in a table format.
It also allows the logged-in user to soft delete a user (mark as inactive without permanently removing them from the database).
Soft-deleted users have status = 0 and deleted_at timestamp.
```
<?php
// public/users.php

// Include authentication check to ensure only logged-in users can access
require_once "../includes/auth.php";

// Include database connection to fetch user data
require_once "../config/database.php";

// Include helper functions (optional for future use)
require_once "../core/helpers.php";

// Get action from URL query (e.g., softdelete)
$action = $_GET['action'] ?? null;

// Get user ID from URL query and cast to integer
$id = isset($_GET['id']) ? (int)$_GET['id'] : null;

// Handle action: soft delete a user
if ($action && $id) {
  if ($action === 'softdelete') {
    // Update user record to mark as inactive and set deleted_at timestamp
    $stmt = $pdo->prepare(
      "UPDATE custome 
         SET status = 0,
             deleted_at = NOW(),
             updated_by = ?
         WHERE id = ?
           AND deleted_at IS NULL"
    );
    // Execute query with current logged-in user ID and target user ID
    $stmt->execute([$_SESSION['custome_id'], $id]);
  }

  // Redirect back to user list after action
  header("Location: user.php");
  exit;
}

// Fetch list of active users (excluding soft-deleted users)
$stmt = $pdo->query(
  "SELECT id, name, email, status, created_at, updated_at, deleted_at
     FROM custome
     WHERE status = 1 AND deleted_at IS NULL
     ORDER BY id ASC"
);

// Fetch all users as associative array
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php 
// Include header HTML (opening tags, CSS, etc.)
require_once "../includes/header.php"; 
?>

<h2>User List</h2>

<!-- Display users in a table -->
<table class="table">
  <thead>
    <tr>
      <th>ID</th>
      <th>Name</th>
      <th>Email</th>
      <th>Status</th>
      <th>Created</th>
      <th>Updated</th>
      <th>Actions</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($users as $u): ?>
      <tr>
        <td><?= $u['id'] ?></td>
        <td><?= htmlspecialchars($u['name']) ?></td>
        <td><?= htmlspecialchars($u['email']) ?></td>
        <td><?= ($u['status'] ? 'Active' : 'Inactive') ?></td>
        <td><?= $u['created_at'] ?></td>
        <td><?= $u['updated_at'] ?></td>
        <td>
          <!-- Soft delete button -->
          <a class="btn btn-danger"
            href="user.php?action=softdelete&id=<?= $u['id'] ?>"
            onclick="return confirm('Are you sure you want to delete this user?')">
            Delete
          </a>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>

<?php 
// Include footer HTML (closing tags)
require_once "../includes/footer.php"; 
?>

```
6.6) logout.php: public/logout.php

This file handles user logout.
It destroys the current session, clearing all stored session data, and then redirects the user to the login page.

```
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

```
Step 7: Add CSS (Optional)

File: public/css/style.css
```
/* public/css/style.css */
body {
    font-family: Arial, sans-serif;
    background: #f6f8fa;
    margin: 0;
    padding: 0;
}

.container {
    max-width: 800px;
    margin: 40px auto;
    background: #fff;
    padding: 24px;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
}

h2 {
    margin-top: 0;
}

.form-group {
    margin-bottom: 12px;
}

label {
    display: block;
    margin-bottom: 6px;
    font-weight: 600;
}

input[type="text"],
input[type="email"],
input[type="password"] {
    width: 100%;
    padding: 10px;
    box-sizing: border-box;
    border: 1px solid #ddd;
    border-radius: 4px;
}

button {
    padding: 10px 16px;
    border: 0;
    background: #2b6cb0;
    color: #fff;
    border-radius: 4px;
    cursor: pointer;
}

.error {
    color: #b00020;
    margin-bottom: 10px;
}

.link {
    margin-top: 10px;
    display: block;
}

.nav {
    margin-bottom: 16px;
}

.nav a {
    margin-right: 10px;
    color: #2b6cb0;
    text-decoration: none;
}

.table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 12px;
}

.table th,
.table td {
    border: 1px solid #eee;
    padding: 8px;
    text-align: left;
}

.small {
    font-size: 0.9rem;
    color: #666;
}

```
Step 8: Folder structure & files
```
PHP_Custome_Authentication/
├── config/
│   └── database.php        # PDO connection (single point of truth)
├── core/
│   └── helpers.php         # small helper functions (old(), isLoggedIn(), etc.)
├── includes/
│   ├── auth.php            # session-based guard used on protected pages
│   ├── header.php          # shared header, navigation (starts HTML)
│   └── footer.php          # shared footer (closes HTML)
├── public/                 # web root (serve this folder with Apache)
│   ├── css/
│   │   └── style.css       # styling for pages
│   ├── index.php           # landing page
│   ├── register.php        # registration (public)
│   ├── login.php           # login (public)
│   ├── dashboard.php       # protected dashboard (requires auth)
│   ├── user.php            # admin-style users list and soft-delete action
│   └── logout.php          # logout logic
├── sql/
│   └── custome.sql         # SQL dump / schema for the custome table / this file not show 
└── README.md               # this file

```
Step 9: Run the Project

Start XAMPP/Laragon/MAMP Apache & MySQL.

Navigate to:
```
http://localhost/PHP_Custome_Authentication/index.php
http://localhost/PHP_Custome_Authentication/register.php

```
Test registration, login, and dashboard access.

✅ Your PHP_Custom_Authentication Project is ready!
