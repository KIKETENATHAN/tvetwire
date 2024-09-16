<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "tvetprime";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get POST data from frontend
$data = json_decode(file_get_contents("php://input"), true);
$note = $data['note'];
$contact = $data['contact'];
$contactMethod = $data['contactMethod'];

// STK Push Simulation (For actual STK Push, integrate with the Safaricom API)
$phoneNumber = ''; // Your phone number to initiate STK Push
$amount = 100; // Sample amount to charge

// Call STK Push API (This is a simulation. Replace with actual STK Push request)
$stkPushResponse = simulateStkPush($phoneNumber, $amount);

if ($stkPushResponse == "success") {
    // Fetch and send the notes after successful payment
    $notesFolder = "notes/"; // Folder where notes are stored
    $filePath = $notesFolder . $note;

    if ($contactMethod == "whatsapp") {
        sendWhatsAppMessage($contact, $filePath);
    } else {
        sendEmail($contact, $filePath);
    }

    // Store transaction details
    $stmt = $conn->prepare("INSERT INTO transactions (note, contact, contact_method, status) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $note, $contact, $contactMethod, $status);

    $status = 'success';
    $stmt->execute();
    $stmt->close();

    // Return response to frontend
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'failed']);
}

$conn->close();

// Functions for sending WhatsApp or Email
function sendWhatsAppMessage($contact, $filePath) {
    // Use WhatsApp API to send the note
    // (Implementation needed based on the WhatsApp API you are using)
}

function sendEmail($contact, $filePath) {
    // Email logic
    $to = $contact;
    $subject = "Your Notes from TvetPrime";
    $body = "Please find attached your notes.";
    $headers = "From: info@tvetprime.co.ke";

    // Send email with the attached note
    mail($to, $subject, $body, $headers);
}

// STK Push Simulation (replace with Safaricom STK API)
function simulateStkPush($phoneNumber, $amount) {
    // Simulate successful payment
    return "success";
}
?>
