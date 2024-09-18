<?php
// view_receipt.php

// Get the receipt number from the query string
$receiptNumber = $_GET['receipt_number'];

// Database connection details
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "tvetprime";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Retrieve the receipt details from the database
$sql = "SELECT * FROM receipts WHERE receipt_number='$receiptNumber'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Output the receipt details
    $receipt = $result->fetch_assoc();
    echo "<h1>Receipt</h1>";
    echo "<p>Receipt Number: " . $receipt['receipt_number'] . "</p>";
    echo "<p>Transaction ID: " . $receipt['transaction_id'] . "</p>";
    echo "<p>Phone Number: " . $receipt['phone_number'] . "</p>";
    echo "<p>Amount Paid: " . $receipt['amount'] . "</p>";
    echo "<p>Receipt Time: " . $receipt['receipt_time'] . "</p>";
    echo "<p>Note: " . $receipt['note'] . "</p>";
    echo "<p>WhatsApp Number: " . $receipt['whatsapp_number'] . "</p>";
    echo "<p>Email Address: " . $receipt['email_address'] . "</p>";
} else {
    echo "Receipt not found.";
}

// Close connection
$conn->close();
?>
