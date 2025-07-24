<?php
// Setup error logging
ini_set('display_errors', 1);
error_reporting(E_ALL);


// Return JSON
header("Content-Type: application/json");

// Read callback data
$callbackJSON = file_get_contents('php://input');
file_put_contents('mpesa_callback_log.txt', date("Y-m-d H:i:s") . "\n" . $callbackJSON . "\n\n", FILE_APPEND);

$data = json_decode($callbackJSON, true);

if (isset($data['Body']['stkCallback'])) {
    $stkCallback = $data['Body']['stkCallback'];
    $CheckoutRequestID = $stkCallback['CheckoutRequestID'] ?? '';
    $ResultCode = $stkCallback['ResultCode'] ?? -1;
    $ResultDesc = $stkCallback['ResultDesc'] ?? 'Unknown';

    $Amount = 0;
    $MpesaReceiptNumber = '';
    $PhoneNumber = '';

    if ($ResultCode == 0 && isset($stkCallback['CallbackMetadata']['Item'])) {
        foreach ($stkCallback['CallbackMetadata']['Item'] as $item) {
            switch ($item['Name']) {
                case 'Amount': $Amount = $item['Value']; break;
                case 'MpesaReceiptNumber': $MpesaReceiptNumber = $item['Value']; break;
                case 'PhoneNumber': $PhoneNumber = $item['Value']; break;
            }
        }

        // Log successful transaction
        file_put_contents('payments_success.txt', json_encode([
            'CheckoutRequestID' => $CheckoutRequestID,
            'MpesaReceiptNumber' => $MpesaReceiptNumber,
            'Amount' => $Amount,
            'PhoneNumber' => $PhoneNumber,
            'Time' => date("Y-m-d H:i:s")
        ]) . "\n", FILE_APPEND);
    } else {
        // Log failed transaction
        file_put_contents('payments_failed.txt', json_encode([
            'CheckoutRequestID' => $CheckoutRequestID,
            'ResultCode' => $ResultCode,
            'ResultDesc' => $ResultDesc,
            'Time' => date("Y-m-d H:i:s")
        ]) . "\n", FILE_APPEND);
    }
} else {
    // Invalid callback format
    file_put_contents('invalid_callback.txt', date("Y-m-d H:i:s") . "\nInvalid structure\n" . $callbackJSON . "\n\n", FILE_APPEND);
}

// Safaricom expects this response
echo json_encode([
    "ResultCode" => 0,
    "ResultDesc" => "Accepted"
]);
?>
