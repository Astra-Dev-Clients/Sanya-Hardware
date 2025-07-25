<?php
session_start();
header('Content-Type: application/json');
include '../database/db.php';

$store_id = $_SESSION['store_id'] ?? null;

if (!$store_id) {
    echo json_encode(['data' => [], 'error' => 'Store ID missing']);
    exit;
}

// Prepare and execute the main query
$query = "
    SELECT 
        s.id AS sale_id,
        s.total_amount,
        s.amount_paid,
        s.change_given,
        s.payment_method,
        s.mpesa_number,
        s.transaction_id,
        s.sale_time,
        CONCAT(IFNULL(a.fname, ''), ' ', IFNULL(a.lname, '')) AS assistant_name
    FROM sales s
    LEFT JOIN assistants a ON s.assistant_id = a.id
    WHERE s.store_id = ?
    ORDER BY s.sale_time DESC
";

$stmt = $conn->prepare($query);
$stmt->bind_param("s", $store_id);
$stmt->execute();
$result = $stmt->get_result();

$sales = [];
while ($row = $result->fetch_assoc()) {
    // Gracefully handle missing assistant name
    $row['assistant_name'] = trim($row['assistant_name']) ?: 'N/A';

    // Fetch related sale items
    $item_stmt = $conn->prepare("
        SELECT si.*, p.product_name 
        FROM sale_items si 
        JOIN products p ON si.product_id = p.sn
        WHERE si.sale_id = ?
    ");
    $item_stmt->bind_param("i", $row['sale_id']);
    $item_stmt->execute();
    $items = $item_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    $row['items'] = $items;
    $sales[] = $row;
}

echo json_encode(['data' => $sales]);
