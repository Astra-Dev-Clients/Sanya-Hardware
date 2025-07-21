<?php
session_start();
require '../database/db.php'; // adjust the path if needed

$store_id = $_POST['store_id'];
$password = $_POST['password'];

$sql = "SELECT * FROM users WHERE store_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $store_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();

    if (password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['store_id'] = $user['store_id'];
        $_SESSION['store_name'] = $user['store_name'];
        header("Location: ../dashboard/index.php");
        exit();
    } else {
        $_SESSION['error'] = "Invalid password.";
    }
} else {
    $_SESSION['error'] = "Store ID not found.";
}

header("Location: ../auth/login.php");
exit();
?>
