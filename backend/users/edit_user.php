<?php
session_start();
require '../../database/db.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Validate required fields
    $required = ['id', 'store_name', 'store_id', 'till_number', 'phone', 'email'];
    foreach ($required as $key) {
        if (!isset($_POST[$key])) {
            die("Missing value for $key");
        }
    }

    $id          = intval($_POST['id']);
    $store_name  = trim($_POST['store_name']);
    $store_id    = trim($_POST['store_id']);
    $till_number = trim($_POST['till_number']);
    $phone       = trim($_POST['phone']);
    $email       = trim($_POST['email']);

    // Optional: Check for duplicate store_id or till_number for other users
    $check = $conn->prepare("SELECT id FROM users WHERE (store_id = ? OR till_number = ?) AND id != ?");
    $check->bind_param("ssi", $store_id, $till_number, $id);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $_SESSION['error'] = "Store ID or Till Number already exists.";
        header("Location: ../../dashboard/users.php");
        exit();
    }

    // Update the user
    $stmt = $conn->prepare("UPDATE users SET store_name = ?, store_id = ?, till_number = ?, phone = ?, email = ? WHERE id = ?");
    $stmt->bind_param("sssssi", $store_name, $store_id, $till_number, $phone, $email, $id);

    if ($stmt->execute()) {
        $_SESSION['success'] = "User updated successfully.";
    } else {
        $_SESSION['error'] = "Failed to update user. Try again.";
    }

    header("Location: ../../dashboard/admin.php");
    exit();
}
?>
