<?php
session_start();

if (isset($_POST['captcha'])) {
    $input = strtolower(trim($_POST['captcha']));
    $stored = strtolower($_SESSION['captcha_code'] ?? '');

    if ($input === $stored) {
        echo "valid";
    } else {
        echo "invalid";
    }
}
?>
