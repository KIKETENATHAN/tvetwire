<?php
// mpesa_callback.php (updated for receipts)

// Retrieve the callback data sent by Mpesa
$callbackJSONData = file_get_contents('php://input');
$callbackData = json_decode($callbackJSONData, true);

// Ensure that the callback data is properly received
if (!$callbackData) {
    // Log error or respond with error
    http_response_code(400);
    exit();
}

// Extract the relevant data from the callback
$resultCode = $callbackData['Body']['stkCallback']['ResultCode']; // Payment result (0 = success)
$checkoutRequestID = $callbackData['Body']['stkCallback']['CheckoutRequestID'];
$transactionID = isset($callbackData['Body']['stkCallback']['CallbackMetadata']['Item'][1]['Value']) ? $callbackData['Body']['stkCallback']['CallbackMetadata']['Item'][1]['Value'] : '';
$amount = isset($callbackData['Body']['stkCallback']['CallbackMetadata']['Item'][0]['Value']) ? $callbackData['Body']['stkCallback']['CallbackMetadata']['Item'][0]['Value'] : 0;
$transactionTime = date("Y-m-d H:i:s"); // Current timestamp

// Determine the payment status based on ResultCode
$paymentStatus = ($resultCode == 0) ? 'Success' : 'Failed';

// Database connection details
$servername = "localhost"; // Or your server name
$username = "your_db_username"; // Replace with your DB username
$password = "your_db_password"; // Replace with your DB password
$dbname = "tvetprime";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($paymentStatus === 'Success') {
    // Get transaction details from the database (optional if you already have them)
    $sqlTransaction = "SELECT * FROM transactions WHERE transaction_id='$checkoutRequestID'";
    $result = $conn->query($sqlTransaction);

    if ($result->num_rows > 0) {
        // Fetch transaction data
        $row = $result->fetch_assoc();
        $phoneNumber = $row['phone_number'];
        $note = $row['note'];
        $whatsappNumber = $row['whatsapp_number'];
        $emailAddress = $row['email_address'];

        // Generate unique receipt number (e.g., TVET123456)
        $receiptNumber = 'TVET' . rand(100000, 999999);

        // Insert receipt details into receipts table
        $sqlReceipt = "INSERT INTO receipts (transaction_id, phone_number, amount, receipt_time, note, whatsapp_number, email_address, receipt_number)
                       VALUES ('$transactionID', '$phoneNumber', '$amount', '$transactionTime', '$note', '$whatsappNumber', '$emailAddress', '$receiptNumber')";

        if ($conn->query($sqlReceipt) === TRUE) {
            echo json_encode(['status' => 'success', 'message' => 'Receipt generated and saved.', 'receipt_number' => $receiptNumber]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error generating receipt: ' . $conn->error]);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Transaction not found.']);
    }
}

// Close connection
$conn->close();
?>
