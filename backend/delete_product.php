<?php
require '../Database/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sn = $_POST['sn'];

    $stmt = $conn->prepare("DELETE FROM products WHERE sn = ?");
    $stmt->bind_param("i", $sn);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Product deleted successfully.";
       header("Location: ../dashboard/index.php");
        exit();
    } else {
        $_SESSION['error'] = "Failed to delete product. Try again.";
        header("Location: ../dashboard/index.php");
        exit();
    }

    $stmt->close();
    $conn->close();
}
?>
