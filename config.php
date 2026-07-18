<?php
// config.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }
$host = "localhost";
$username = "root";
$password = "";
$database = "word_bank"; // This database holds BOTH your 'words' and 'users' tables

$conn = mysqli_connect($host, $username, $password, $database);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>