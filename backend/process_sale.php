<?php
session_start();
include '../database/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $store_id = $_SESSION['store_id'];
    $assistant_id = $_SESSION['assistant_id'] ?? null;
    $sales_data = json_decode($_POST['sales_data'], true);
    $grand_total = $_POST['grand_total'];
    $payment_method = $_POST['payment_method'] ?? 'Cash';

    // Validate ENUM values for payment_method
    $allowed_methods = ['Cash', 'Mpesa', 'Card', 'Other'];
    if (!in_array($payment_method, $allowed_methods)) {
        die("Error: Invalid payment method selected.");
    }

    // Begin transaction
    $conn->begin_transaction();

    try {
        // 1. Insert sale (without amount_paid for now)
        $stmt = $conn->prepare("INSERT INTO sales (store_id, assistant_id, total_amount, payment_method) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sids", $store_id, $assistant_id, $grand_total, $payment_method);
        $stmt->execute();
        $sale_id = $conn->insert_id;

        // 2. Insert sale_items
        $stmt_item = $conn->prepare("INSERT INTO sale_items (sale_id, product_id, quantity, unit_price, total_price) VALUES (?, ?, ?, ?, ?)");

        foreach ($sales_data as $item) {
            $stmt_item->bind_param("iiidd", $sale_id, $item['product_id'], $item['quantity'], $item['price'], $item['total']);
            $stmt_item->execute();

            // 3. Update stock
            $update_stock = $conn->prepare("UPDATE products SET quantity = quantity - ? WHERE sn = ?");
            $update_stock->bind_param("ii", $item['quantity'], $item['product_id']);
            $update_stock->execute();
        }

        $conn->commit();
        echo "<script>alert('Sale recorded successfully.'); window.location.href='../dashboard/sell.php';</script>";
    } catch (Exception $e) {
        $conn->rollback();
        echo "Transaction Failed: " . $e->getMessage();
    }
}
?>
