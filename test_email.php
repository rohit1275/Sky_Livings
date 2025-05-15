<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

// Create a new PHPMailer instance
$mail = new PHPMailer(true);

try {
    // Server settings
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'skylivings20@gmail.com';
    $mail->Password = 'olql aorl lini ecdt';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;
    
    // Enable debugging
    $mail->SMTPDebug = 2;
    $mail->Debugoutput = function($str, $level) {
        echo "SMTP Debug: $str\n";
    };

    // Recipients
    $mail->setFrom('skylivings20@gmail.com', 'Sky Livings Test');
    $mail->addAddress('skylivings20@gmail.com', 'Test Recipient');
    $mail->addAddress('info@skylivings.com', 'Info Recipient'); // Adding a second recipient

    // Content
    $mail->isHTML(true);
    $mail->Subject = 'PHPMailer Test - ' . date('Y-m-d H:i:s');
    $mail->Body = '
        <h1>PHPMailer Test Email</h1>
        <p>This is a test email to verify PHPMailer installation and configuration.</p>
        <p>Sent at: ' . date('Y-m-d H:i:s') . '</p>
        <p>If you receive this email, the system is working correctly.</p>
    ';

    // Send the email
    $mail->send();
    echo '<h2>Test Results:</h2>';
    echo '<p>Test email sent successfully to:</p>';
    echo '<ul>';
    echo '<li>skylivings20@gmail.com</li>';
    echo '<li>info@skylivings.com</li>';
    echo '</ul>';
    echo '<p>Please check both email accounts (including spam folders) for the test email.</p>';
    echo '<p>Email subject: PHPMailer Test - ' . date('Y-m-d H:i:s') . '</p>';
} catch (Exception $e) {
    echo '<h2>Error:</h2>';
    echo '<p>Error sending test email: ' . $mail->ErrorInfo . '</p>';
}
?> 