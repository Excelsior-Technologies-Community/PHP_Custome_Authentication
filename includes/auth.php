<?php
session_start();

if (!isset($_SESSION['custome_id'])) {
    header("Location: /PHP_Custome_Authentication/public/login.php");
    exit;
}