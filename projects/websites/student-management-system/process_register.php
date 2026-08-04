<?php
// process_register.php
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first  = $conn->real_escape_string($_POST['first_name']);
    $last   = $conn->real_escape_string($_POST['last_name']);
    $email  = $conn->real_escape_string($_POST['email']);
    $phone  = $conn->real_escape_string($_POST['phone']);
    $course = $conn->real_escape_string($_POST['course']);
    $dept   = $conn->real_escape_string($_POST['department']);

    $sql = "INSERT INTO students (first_name, last_name, email, phone, course, department)
            VALUES ('$first', '$last', '$email', '$phone', '$course', '$dept')";

    if ($conn->query($sql) === TRUE) {
        header("Location: view.php?msg=Student+registered+successfully");
        exit;
    } else {
        echo "Error: " . $conn->error;
    }
} else {
    header("Location: register.php");
    exit;
}