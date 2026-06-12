<?php
// db.example.php - SAMPLE CONFIG FOR GITHUB (NO REAL PASSWORD)

$host   = "sql313.infinityfree.com";   // or localhost for local dev
$user   = "YOUR_DB_USERNAME";          // e.g., if0_42168024
$pass   = "YOUR_DB_PASSWORD";          // DO NOT PUT REAL PASSWORD IN GITHUB
$dbname = "YOUR_DB_NAME";              // e.g., if0_42168024_if0_42168024

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>