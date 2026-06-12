<?php
// delete_student.php
session_start();
require 'db.php';

// Optional: restrict deletion to logged-in admin
// if (!isset($_SESSION['admin'])) {
//     header("Location: login.php?msg=Please+login+first");
//     exit;
// }

if (isset($_GET['id'])) {
    $id = (int) $_GET['id'];
    $sql = "DELETE FROM students WHERE id = $id";
    if ($conn->query($sql) === TRUE) {
        header("Location: view.php?msg=Student+deleted+successfully");
        exit;
    } else {
        echo "Error deleting record: " . $conn->error;
    }
} else {
    header("Location: view.php");
    exit;
}