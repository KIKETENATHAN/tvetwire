<?php
// process_payment.php

// Safaricom Mpesa API Credentials
$consumerKey = 'KnrlUyqnuWM2MQGAVVpDnk1X7yKBTqF7D7kOPtuztprzVLGc';
$consumerSecret = 'SBKY7EUN1pHG6IUF0HdbKglwNtkqec1ac1tyqP3e8tLd45Ir919ig9AGQGzWj53W';
$shortcode = '174379';
$lipaNaMpesaOnlinePasskey = 'bfb279f9aa9bdbcf158e97dd71a467cd2e0c893059b10f78e6b72ada1ed2c919';
$callbackURL = 'https://mydomain.com/path'; // Replace with your callback URL

// Retrieve the data sent from the front-end
$data = json_decode(file_get_contents('php://input'), true);

$note = $data['note'];
$whatsappNumber = $data['whatsappNumber'];
$emailAddress = $data['emailAddress'];
$paymentPhone = $data['paymentPhone'];

// Validate input
if (empty($paymentPhone)) {
    echo json_encode(['status' => 'error', 'message' => 'Phone number for payment is required.']);
    exit();
}

// Prepare the Mpesa STK Push request
$amount = 1; // Set the amount to be charged
$timestamp = date("YmdHis");
$password = base64_encode($shortcode . $lipaNaMpesaOnlinePasskey . $timestamp);

// Generate the access token
$curl = curl_init();
curl_setopt($curl, CURLOPT_URL, 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials');
curl_setopt($curl, CURLOPT_HTTPHEADER, ['Authorization: Basic ' . base64_encode($consumerKey . ':' . $consumerSecret)]);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($curl);
curl_close($curl);

$accessToken = json_decode($response)->access_token;

// Trigger STK Push
$curl = curl_init();
curl_setopt($curl, CURLOPT_URL, 'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest');
curl_setopt($curl, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $accessToken,
    'Content-Type: application/json'
]);
$curl_post_data = [
    'BusinessShortCode' => $shortcode,
    'Password' => $password,
    'Timestamp' => $timestamp,
    'TransactionType' => 'CustomerPayBillOnline',
    'Amount' => $amount,
    'PartyA' => $paymentPhone, // The phone number making the payment
    'PartyB' => $shortcode,
    'PhoneNumber' => $paymentPhone,
    'CallBackURL' => $callbackURL,
    'AccountReference' => 'TvetPrime',
    'TransactionDesc' => 'Payment for ' . $note
];

$data_string = json_encode($curl_post_data);

curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($curl);
curl_close($curl);

// Handle the response from Mpesa
$responseData = json_decode($response, true);
if (isset($responseData['ResponseCode']) && $responseData['ResponseCode'] == "0") {
    // STK Push was successful, proceed to save the transaction

    // Database connection details
    $servername = "localhost"; // Or your server name
    $username = "root"; // Replace with your DB username
    $password = ""; // Replace with your DB password
    $dbname = "tvetprime";

    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Get transaction details from the STK push response
    $transactionID = $responseData['CheckoutRequestID']; // Assuming this is the transaction ID
    $transactionTime = date("Y-m-d H:i:s"); // Current time

    // Insert transaction details into the database
    $sql = "INSERT INTO transactions (transaction_id, phone_number, amount, transaction_time, note, whatsapp_number, email_address, status)
            VALUES ('$transactionID', '$paymentPhone', '$amount', '$transactionTime', '$note', '$whatsappNumber', '$emailAddress', 'Pending')";

    if ($conn->query($sql) === TRUE) {
        // Payment was successful, proceed to send the notes

        $noteData = [
            'note' => $note,
            'whatsappNumber' => $whatsappNumber,
            'emailAddress' => $emailAddress
        ];

        // Call send_notes.php to send the notes (you can include the code for sending notes here as well)
        // Log the data being sent to send_notes.php
        error_log("Sending data to send_notes.php: " . print_r($noteData, true));

        // Call send_notes.php to send the notes
        $ch = curl_init('http://localhost/tvetwire/send_notes.php'); // Replace with your actual URL
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($noteData));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // Log response from send_notes.php
        error_log("send_notes.php response: " . $result);
        error_log("HTTP status code: " . $httpCode);

        // Check result from send_notes.php
        $resultData = json_decode($result, true);
        if (isset($resultData['status']) && $resultData['status'] == 'success') {
            echo json_encode(['status' => 'success', 'message' => 'Payment successful! Notes sent to user.']);
        } else {
            echo json_encode(['status' => 'warning', 'message' => 'Payment successful, but notes not sent: ' . $resultData['message']]);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error saving transaction: ' . $conn->error]);
    }

    // Close connection
    $conn->close();
} else {
    // STK Push failed, return error message
    echo json_encode(['status' => 'error', 'message' => 'STK Push failed.']);
}
?>
