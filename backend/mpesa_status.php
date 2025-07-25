<?php
// mpesa_status.php

header("Content-Type: application/json");

// Get CheckoutRequestID from query param
$checkoutId = $_GET['checkout_id'] ?? '';

if (empty($checkoutId)) {
    echo json_encode([
        "status" => "error",
        "message" => "Missing checkout_id"
    ]);
    exit;
}

// Helper: Search JSON lines in a given file
function findTransactionInFile($file, $checkoutId) {
    if (!file_exists($file)) return null;

    $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $data = json_decode($line, true);
        if (isset($data['CheckoutRequestID']) && $data['CheckoutRequestID'] === $checkoutId) {
            return $data;
        }
    }
    return null;
}

// Check in successful transactions first
$success = findTransactionInFile('payments_success.txt', $checkoutId);
if ($success) {
    echo json_encode([
        "status" => "success",
        "message" => "Payment received",
        "receipt" => $success['MpesaReceiptNumber'] ?? 'N/A',
        "amount" => $success['Amount'] ?? 'N/A',
        "phone" => $success['PhoneNumber'] ?? 'N/A',
        "time" => $success['Time'] ?? date("Y-m-d H:i:s")
    ]);

    exit;
}



// Then check in failed/cancelled transactions
$failed = findTransactionInFile('payments_failed.txt', $checkoutId);
if ($failed) {
    echo json_encode([
        "status" => "cancelled",
        "message" => $failed['ResultDesc'] ?? 'Payment failed or cancelled',
        "time" => $failed['Time'] ?? date("Y-m-d H:i:s")
    ]);
    exit;
}

// No match found yet – assume still pending
echo json_encode([
    "status" => "pending",
    "message" => "Payment not completed yet. Please wait..."
]);
