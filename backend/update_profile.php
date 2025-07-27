<?php
session_start();
require '../database/db.php';

if (!isset($_POST['id'])) {
    $_SESSION['error'] = "Invalid request.";
    header("Location: profile.php");
    exit();
}

$id           = intval($_POST['id']);
$store_name   = trim($_POST['store_name']);
$till_number  = trim($_POST['till_number']);
$phone        = trim($_POST['phone']);
$email        = trim($_POST['email']);
$password     = $_POST['password'];

// Validate fields
if ($store_name === "" || $till_number === "" || $phone === "") {
    $_SESSION['error'] = "Please fill in all required fields.";
    header("Location: profile.php");
    exit();
}

// Check if password needs to be updated
if (!empty($password)) {
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE users SET store_name=?, till_number=?, phone=?, email=?, password=? WHERE id=?");
    $stmt->bind_param("sssssi", $store_name, $till_number, $phone, $email, $hashedPassword, $id);
} else {
    $stmt = $conn->prepare("UPDATE users SET store_name=?, till_number=?, phone=?, email=? WHERE id=?");
    $stmt->bind_param("ssssi", $store_name, $till_number, $phone, $email, $id);
}

if ($stmt->execute()) {
    $_SESSION['success'] = "Profile updated successfully.";
} else {
    $_SESSION['error'] = "Failed to update profile. Try again.";
}

$stmt->close();
header("Location: ../dashboard/settings.php");
exit();
