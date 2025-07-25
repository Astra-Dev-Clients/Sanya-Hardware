<?php
session_start();
require '../database/db.php';

$astra_id = $_POST['astra_id'] ?? '';
$password = $_POST['password'] ?? '';

$sql = "SELECT * FROM astra WHERE astra_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $astra_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();

    if (password_verify($password, $user['pass'])) {
        $_SESSION['astra_id'] = $user['astra_id'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['phone'] = $user['phone'];
        header("Location: ../dashboard/admin.php");
        exit();
    } else {
        $_SESSION['error'] = "Invalid password.";
    }
} else {
    $_SESSION['error'] = "Astra ID not found.";
}

header("Location: ../auth/astra/index.php");
exit();
?>
