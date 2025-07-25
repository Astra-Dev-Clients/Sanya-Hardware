<?php
session_start();
require '../../database/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['id'])) {
        $_SESSION['error'] = "Missing user ID.";
        header("Location: ../../dashboard/users.php");
        exit();
    }

    $id = intval($_POST['id']);

    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        $_SESSION['success'] = "User deleted successfully.";
    } else {
        $_SESSION['error'] = "Failed to delete user. Try again.";
    }

    header("Location: ../../dashboard/admin.php");
    exit();
}
?>
