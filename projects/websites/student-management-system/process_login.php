<?php
// process_login.php
session_start();

// Simple hard-coded credentials for demo
$valid_user = "admin";
$valid_pass = "admin123";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = $_POST['username'] ?? "";
    $pass = $_POST['password'] ?? "";

    if ($user === $valid_user && $pass === $valid_pass) {
        $_SESSION['admin'] = $user;
        header("Location: view.php?msg=Logged+in+successfully");
        exit;
    } else {
        header("Location: login.php?msg=Invalid+credentials");
        exit;
    }
} else {
    header("Location: login.php");
    exit;
}