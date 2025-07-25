<?php
session_start();
header('Content-Type: application/json'); // Ensure JSON response

include '../database/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $store_id = $_SESSION['store_id'];
    $assistant_id = $_SESSION['assistant_id'] ?? null;
    $sales_data = json_decode($_POST['sales_data'], true);
    $grand_total = floatval($_POST['grand_total']);
    $payment_method = $_POST['payment_method'] ?? 'Cash';
    $amount_paid = floatval($_POST['amount_paid'] ?? 0);
    $change_given = floatval($_POST['change_given'] ?? 0);
    $mpesa_number = $_POST['mpesa_number'] ?? null;
    $transaction_id = $_POST['transaction_id'] ?? null;
    $transaction_status = $_POST['transaction_status'] ?? 'Failed';

    // ✅ ENUM validation
    $allowed_methods = ['Cash', 'Mpesa', 'Card', 'Other'];
    if (!in_array($payment_method, $allowed_methods)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Invalid payment method selected.'
        ]);
        exit;
    }

    // ✅ ONLY record Mpesa if confirmed successful
    if ($payment_method === 'Mpesa' && $transaction_status !== 'Success') {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Mpesa transaction failed or not confirmed. Sale not recorded.'
        ]);
        exit;
    }

    $conn->begin_transaction();

    try {
        // Insert into sales table
        $stmt = $conn->prepare("INSERT INTO sales (store_id, assistant_id, total_amount, payment_method, amount_paid, change_given, mpesa_number, transaction_id) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sidsssss", $store_id, $assistant_id, $grand_total, $payment_method, $amount_paid, $change_given, $mpesa_number, $transaction_id);
        $stmt->execute();
        $sale_id = $stmt->insert_id;

        // Insert each sale item
        $stmt_item = $conn->prepare("INSERT INTO sale_items (sale_id, product_id, quantity, unit_price, total_price) VALUES (?, ?, ?, ?, ?)");
        foreach ($sales_data as $item) {
            $stmt_item->bind_param("iiidd", $sale_id, $item['product_id'], $item['quantity'], $item['price'], $item['total']);
            $stmt_item->execute();

            // Update stock
            $update_stock = $conn->prepare("UPDATE products SET quantity = quantity - ? WHERE sn = ?");
            $update_stock->bind_param("ii", $item['quantity'], $item['product_id']);
            $update_stock->execute();
        }

        $conn->commit();
        echo json_encode([
            'success' => true,
            'message' => 'Sale recorded successfully.'
        ]);
    } catch (Exception $e) {
        $conn->rollback();
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Transaction failed: ' . $e->getMessage()
        ]);
    }

    
} else {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed.'
    ]);
}
?>
