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

function set_flash($msg, $type = 'success') {
    $_SESSION['flash'] = ['msg' => $msg, 'type' => $type];
}

function display_flash() {
    if (isset($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        echo "<div style='padding: 10px; margin-bottom: 15px; border-radius: 4px; color: white; background: " . ($f['type'] == 'success' ? '#28a745' : '#dc3545') . ";'>{$f['msg']}</div>";
        unset($_SESSION['flash']);
    }
}