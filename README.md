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
$host = "localhost";
$dbname = "php_auth";
$username = "root";
$password = "";

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8",
        $username,
        $password
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
```
Step 4: Core Helper Functions

File: core/helpers.php
```
<?php
function isLoggedIn() {
    return isset($_SESSION['custome_id']);
}

function redirect($url) {
    header("Location: $url");
    exit;
}

function old($key) {
    return isset($_POST[$key]) ? htmlspecialchars($_POST[$key]) : '';
}
```
Step 5: Create Includes (Header, Footer, Auth Check)

Header: includes/header.php
```
<?php
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
Footer: includes/footer.php
```
<?php
?>
  </div> <!-- /.container -->
</body>
</html>

```
Auth Check: includes/auth.php
```
<?php
session_start();

if (!isset($_SESSION['custome_id'])) {
    header("Location: login.php");
    exit;
}
```
Step 6: Authentication PHP Files

index.php: public/index.php
```
<?php
session_start();
require_once __DIR__ . "/../includes/header.php"; 
?>

<h2>PHP Custom Authentication</h2>

<p>Use the links below:</p>

<div class="nav">
    <a href="register.php">Register</a>
    <a href="login.php">Login</a>
    <a href="dashboard.php">Dashboard</a>
</div>

<?php 
require_once __DIR__ . "/../includes/footer.php"; 
?>

```
register.php: public/register.php
```
(Handles user registration, password hashing, and redirects to login)

<?php
session_start();
require_once "../config/database.php";
require_once "../core/helpers.php";

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';
    $pass2 = $_POST['password_confirm'] ?? '';

    if ($name === '' || $email === '' || $pass === '') {
        $error = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } elseif ($pass !== $pass2) {
        $error = "Passwords do not match.";
    } else {
        $stmt = $pdo->prepare("SELECT id FROM custome WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);

        if ($stmt->fetch()) {
            $error = "Email already registered.";
        } else {
            $hash = password_hash($pass, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare(
                "INSERT INTO custome (name, email, password, status, created_by) VALUES (?, ?, ?, 1, NULL)"
            );
            $stmt->execute([$name, $email, $hash]);
            header("Location: login.php");
            exit;
        }
    }
}
?>

<?php require_once "../includes/header.php"; ?>

<h2>Register</h2>
<?php if ($error): ?>
  <div class="error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<form method="post" action="">
  <div class="form-group">
    <label>Name</label>
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

<p class="link small">Already have an account? <a href="login.php">Login here</a></p>

<?php require_once "../includes/footer.php"; ?>

```
login.php: public/login.php
```
(Handles login, password verification, session start, session_regenerate_id, redirect to dashboard)

<?php
session_start();
require_once "../config/database.php";
require_once "../core/helpers.php";

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';

    if ($email === '' || $pass === '') {
        $error = "Both fields are required.";
    } else {
        $stmt = $pdo->prepare(
            "SELECT * FROM custome WHERE email = ? AND status = 1 AND deleted_at IS NULL LIMIT 1"
        );
        $stmt->execute([$email]);
        $custome = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($custome && password_verify($pass, $custome['password'])) {
            $_SESSION['custome_id']   = $custome['id'];
            $_SESSION['custome_name'] = $custome['name'];
            session_regenerate_id(true);
            header("Location: dashboard.php");
            exit;
        } else {
            $error = "Invalid credentials or inactive account.";
        }
    }
}
?>

<?php require_once "../includes/header.php"; ?>

<h2>Login</h2>
<?php if ($error): ?>
  <div class="error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<form method="post" action="">
  <div class="form-group">
    <label>Email</label>
    <input type="email" name="email" value="<?= old('email') ?>">
  </div>

  <div class="form-group">
    <label>Password</label>
    <input type="password" name="password">
  </div>

  <button type="submit">Login</button>
</form>

<p class="link small">Don't have an account? <a href="register.php">Register</a></p>

<?php require_once "../includes/footer.php"; ?>

```
dashboard.php: public/dashboard.php

(Protected dashboard page with welcome message and links)
```
<?php
require_once "../includes/auth.php";
require_once "../includes/header.php";
?>

<h2>Dashboard</h2>
<p>Welcome, <?= htmlspecialchars($_SESSION['custome_name']); ?>!</p>

<div>
  <a href="logout.php">Logout</a> &nbsp;|&nbsp;
  <a href="user.php">User list</a>
</div>

<?php require_once "../includes/footer.php"; ?>

```
user.php: public/user.php

(Displays user list and handles soft delete)
```
<?php
require_once "../includes/auth.php";
require_once "../config/database.php";
require_once "../core/helpers.php";

$action = $_GET['action'] ?? null;
$id = isset($_GET['id']) ? (int)$_GET['id'] : null;

if ($action && $id) {
  if ($action === 'softdelete') {
    $stmt = $pdo->prepare(
      "UPDATE custome SET status = 0, deleted_at = NOW(), updated_by = ? WHERE id = ? AND deleted_at IS NULL"
    );
    $stmt->execute([$_SESSION['custome_id'], $id]);
  }
  header("Location: user.php");
  exit;
}

$stmt = $pdo->query(
  "SELECT id, name, email, status, created_at, updated_at, deleted_at
   FROM custome
   WHERE status = 1 AND deleted_at IS NULL
   ORDER BY id ASC"
);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php require_once "../includes/header.php"; ?>

<h2>User List</h2>

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

<?php require_once "../includes/footer.php"; ?>

```
logout.php: public/logout.php
```
<?php
session_start();
$_SESSION = [];
session_destroy();
header("Location: login.php");
exit;
```
Step 7: Add CSS (Optional)

File: public/css/style.css
```
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

h2 { margin-top: 0; }

.form-group { margin-bottom: 12px; }

label { display: block; margin-bottom: 6px; font-weight: 600; }

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

.error { color: #b00020; margin-bottom: 10px; }

.link { margin-top: 10px; display: block; }

.nav { margin-bottom: 16px; }

.nav a { margin-right: 10px; color: #2b6cb0; text-decoration: none; }

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

.small { font-size: 0.9rem; color: #666; }
```
Step 8: Folder structure & files
```
PHP_Custome_Authentication/
├── config/
│   └── database.php
├── core/
│   └── helpers.php
├── includes/
│   ├── auth.php
│   ├── header.php
│   └── footer.php
├── public/
│   ├── css/
│   │   └── style.css
│   ├── index.php
│   ├── register.php
│   ├── login.php
│   ├── dashboard.php
│   ├── user.php
│   └── logout.php
├── sql/
│   └── custome.sql
└── README.md
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
