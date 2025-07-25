<?php
session_start();
require '../../database/db.php';

if (!isset($_SESSION['astra_id'])) {
    header("Location: ../../auth/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $store_name   = trim($_POST['store_name']);
    $store_id     = trim($_POST['store_id']);
    $till_number  = trim($_POST['till_number']);
    $phone        = trim($_POST['phone']);
    $email        = trim($_POST['email']);
    $password     = trim($_POST['password']);
    $created_at   = date('Y-m-d H:i:s');

    // Check if store_id or till_number already exists
    $checkQuery = "SELECT id FROM users WHERE store_id = ? OR till_number = ?";
    $checkStmt  = $conn->prepare($checkQuery);
    $checkStmt->bind_param("ss", $store_id, $till_number);
    $checkStmt->execute();
    $checkStmt->store_result();

    if ($checkStmt->num_rows > 0) {
        $_SESSION['error'] = "Store ID or Till Number already exists.";
        header("Location: ../../dashboard/admin.php");
        exit();
    }

    // Insert new user
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $insertQuery = "INSERT INTO users (store_name, store_id, till_number, phone, email, password, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($insertQuery);
    $stmt->bind_param("sssssss", $store_name, $store_id, $till_number, $phone, $email, $hashedPassword, $created_at);

    if ($stmt->execute()) {
        $_SESSION['success'] = "User added successfully.";
    } else {
        $_SESSION['error'] = "Error adding user. Try again.";
    }

    header("Location: ../../dashboard/admin.php");
    exit();
}
?>
