<?php
$host = "localhost";
$user = "root";
$pass = "22092209";
$dbname = "sanya";

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
