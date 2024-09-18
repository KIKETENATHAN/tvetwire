<?php
// send_notes.php

// Fetch input data
$data = json_decode(file_get_contents('php://input'), true);

// Log received data for debugging
error_log("Received data: " . print_r($data, true));

if (empty($data['note']) || empty($data['whatsappNumber']) || empty($data['emailAddress'])) {
    echo json_encode(['status' => 'error', 'message' => 'Incomplete data received.']);
    exit();
}

$note = $data['note'];
$whatsappNumber = $data['whatsappNumber'];
$emailAddress = $data['emailAddress'];

// Log that we are proceeding with sending
error_log("Sending notes to WhatsApp: $whatsappNumber and Email: $emailAddress");

// Load the Twilio PHP library (make sure to include your autoload file)
require 'vendor/autoload.php';
use Twilio\Rest\Client;

// Twilio credentials (replace with your credentials)
$twilioSid = 'your_twilio_sid';
$twilioToken = 'your_twilio_auth_token';
$twilioFrom = 'your_twilio_whatsapp_number'; // e.g., 'whatsapp:+14155238886';

// Send WhatsApp message
try {
    $twilio = new Client($twilioSid, $twilioToken);
    $twilio->messages->create(
        'whatsapp:' . $whatsappNumber,
        [
            'from' => $twilioFrom,
            'body' => "Here are your notes: $note"
        ]
    );
} catch (Exception $e) {
    error_log("WhatsApp sending failed: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Failed to send WhatsApp message: ' . $e->getMessage()]);
    exit();
}

// Send Email (using PHP mail function for simplicity)
$subject = "Your Notes from TvetPrime";
$message = "Here are your notes: $note";
$headers = "From: no-reply@yourdomain.com";

if (!mail($emailAddress, $subject, $message, $headers)) {
    error_log("Email sending failed");
    echo json_encode(['status' => 'error', 'message' => 'Failed to send email.']);
    exit();
}

// Return success response
echo json_encode(['status' => 'success', 'message' => 'Notes sent to user.']);
?>


