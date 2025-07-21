<?php
session_start();
require '../database/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $store_id = $_SESSION['store_id'];
    $product_name = trim($_POST['product_name']);
    $category = trim($_POST['category']);
    $description = trim($_POST['description']);
    $buying_price = floatval($_POST['buying_price']);
    $selling_price = floatval($_POST['selling_price']);
    $quantity = intval($_POST['quantity']);

    $sql = "INSERT INTO products (store_id, product_name, category, description, buy_price, sell_price, quantity) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssddi", $store_id, $product_name, $category, $description, $buying_price, $selling_price, $quantity);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Product added successfully.";
        header("Location: ../dashboard/index.php");
        exit();
    } else {
        $_SESSION['error'] = "Error adding product. Try again.";
        header("Location: ../dashboard/index.php");
        exit();
    }

    header("Location: ../dashboard/index.php");
    exit();
}
?>
