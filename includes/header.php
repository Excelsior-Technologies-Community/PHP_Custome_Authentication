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
        <?php
        if (isset($_SESSION['flash'])) {
            $flash = $_SESSION['flash'];
            $bg_color = ($flash['type'] === 'success') ? '#28a745' : '#dc3545';
            echo "<div style='padding: 12px; margin-bottom: 20px; border-radius: 4px; color: #fff; background: $bg_color; text-align: center; font-weight: bold;'>
                    {$flash['msg']}
                  </div>";
            unset($_SESSION['flash']);
        }
        ?>