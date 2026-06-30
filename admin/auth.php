<?php
session_start();

function require_login() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: ../index.php");
        exit;
    }
}

function require_admin() {
    if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
        header("Location: ../index.php");
        exit;
    }
}
