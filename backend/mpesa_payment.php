<?php
// Enable error reporting (disable in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Allow CORS if needed
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

// Autoload dependencies
require '../vendor/autoload.php';
use Dotenv\Dotenv;

// Load environment variables
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// Step 1: Receive and parse JSON input
$input = json_decode(file_get_contents("php://input"), true);

// Validate input
if (!isset($input['grand_total']) || !isset($input['mpesa_number'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Amount or phone number not provided'
    ]);
    exit;
}

$amount = $input['grand_total'];
$phone = $input['mpesa_number'];


// Format phone number
if (preg_match('/^0/', $phone)) {
    $phone = preg_replace('/^0/', '254', $phone);
} elseif (preg_match('/^\+254/', $phone)) {
    $phone = preg_replace('/^\+/', '', $phone);
}

// Step 2: Load credentials
$consumerKey        = $_ENV['MPESA_CONSUMER_KEY'];
$consumerSecret     = $_ENV['MPESA_CONSUMER_SECRET'];
$BusinessShortCode  = $_ENV['MPESA_BUSINESS_SHORTCODE'] ?? '174379';
$Passkey            = $_ENV['MPESA_PASSKEY'];
$PartyA             = $phone;
$AccountReference   = "TestAccount";
$TransactionDesc    = "Payment";
$CallbackURL        = "https://de46bf65f49b.ngrok-free.app/clients/sanya/backend/mpesa_callback.php";

// Step 3: Generate access token
$credentials = base64_encode("$consumerKey:$consumerSecret");
$tokenUrl = 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials';

$curl = curl_init($tokenUrl);
curl_setopt($curl, CURLOPT_HTTPHEADER, [
    "Authorization: Basic $credentials"
]);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($curl);
curl_close($curl);

$tokenData = json_decode($response, true);

if (!isset($tokenData['access_token'])) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to get access token',
        'raw' => $tokenData
    ]);
    exit;
}

$access_token = $tokenData['access_token'];

// Step 4: Prepare STK Push
$timestamp = date('YmdHis');
$password = base64_encode($BusinessShortCode . $Passkey . $timestamp);

$stkData = [
    "BusinessShortCode" => $BusinessShortCode,
    "Password" => $password,
    "Timestamp" => $timestamp,
    "TransactionType" => "CustomerPayBillOnline",
    "Amount" => $amount,
    "PartyA" => $PartyA,
    "PartyB" => $BusinessShortCode,
    "PhoneNumber" => $PartyA,
    "CallBackURL" => $CallbackURL,
    "AccountReference" => $AccountReference,
    "TransactionDesc" => $TransactionDesc
];

// Step 5: Send STK Push
$stkUrl = 'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest';
$curl = curl_init($stkUrl);
curl_setopt($curl, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
    "Authorization: Bearer $access_token"
]);
curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($stkData));
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

$stkResponse = curl_exec($curl);
curl_close($curl);

$stkResult = json_decode($stkResponse, true);

// Step 6: Handle STK Push Response
if (isset($stkResult['ResponseCode']) && $stkResult['ResponseCode'] === "0") {
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'STK push sent. Awaiting user confirmation.',
        'checkout_id' => $stkResult['CheckoutRequestID'],
        'response' => $stkResult
    ]);
} else {
    $errorMessage = $stkResult['errorMessage'] ?? ($stkResult['error'] ?? 'STK push failed or malformed response');
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'STK push failed.',
        'errorDetails' => $errorMessage,
        'rawResponse' => $stkResult
    ]);
}
