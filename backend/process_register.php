<?php
session_start();
require '../database/db.php';

$store_id = trim($_POST['store_id']);
$store_name = trim($_POST['store_name']);
$email = trim($_POST['email']);
$password = password_hash($_POST['password'], PASSWORD_BCRYPT);

// Check if store_id already exists
$check = $conn->prepare("SELECT id FROM users WHERE store_id = ?");
$check->bind_param("s", $store_id);
$check->execute();
$check_result = $check->get_result();

if ($check_result->num_rows > 0) {
    $_SESSION['error'] = "Store ID already taken.";
    header("Location: ../auth/register.php");
    exit();
}

// Insert user
$stmt = $conn->prepare("INSERT INTO users (store_id, store_name, email, password) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $store_id, $store_name, $email, $password);

if ($stmt->execute()) {
    header("Location: ../auth/login.php");
    exit();
} else {
    $_SESSION['error'] = "Registration failed. Try again.";
    header("Location: ../auth/register.php");
    exit();
}
