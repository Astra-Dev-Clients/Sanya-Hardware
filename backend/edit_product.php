<?php
require '../Database/db.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Make sure all keys exist
    $required = ['sn', 'product_name', 'category', 'description', 'buy_price', 'sell_price', 'quantity'];
    foreach ($required as $key) {
        if (!isset($_POST[$key])) {
            die("Missing value for $key");
        }
    }

    $sn = $_POST['sn'];
    $product_name = $_POST['product_name'];
    $category = $_POST['category'];
    $description = $_POST['description'];
    $buy_price = floatval($_POST['buy_price']);
    $sell_price = floatval($_POST['sell_price']);
    $quantity = intval($_POST['quantity']);

    $stmt = $conn->prepare("UPDATE products SET product_name = ?, category = ?, description = ?, buy_price = ?, sell_price = ?, quantity = ? WHERE sn = ?");
    $stmt->bind_param("sssddii", $product_name, $category, $description, $buy_price, $sell_price, $quantity, $sn);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Product updated successfully.";
        header("Location: ../dashboard/index.php");
        exit();
    } else {
        $_SESSION['error'] = "Failed to update product. Try again.";
        header("Location: ../dashboard/index.php");
        exit();
    }

    $stmt->close();
    $conn->close();
}
?>