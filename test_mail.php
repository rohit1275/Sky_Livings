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
    // Enable SMTP debugging
    $mail->SMTPDebug = 2; // Enable verbose debug output
    $mail->Debugoutput = 'html'; // Format debug output as HTML
    
    // Server settings
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'uniquechoudhary1@gmail.com'; // Your Gmail address
    $mail->Password = 'ezsi nigk tbvh dmeq'; // Your Gmail App Password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;
    
    // Additional settings
    $mail->Timeout = 60; // Increase timeout to 60 seconds
    $mail->SMTPKeepAlive = true;

    // Recipients
    $mail->setFrom('uniquechoudhary1@gmail.com', 'Sky Livings');
    $mail->addAddress('uniquechoudhary1@gmail.com');

    // Content
    $mail->isHTML(true);
    $mail->Subject = 'Test Email';
    $mail->Body = 'This is a test email from your server.';

    $mail->send();
    echo 'Email sent successfully!';
} catch (Exception $e) {
    echo "Failed to send email. Error: {$mail->ErrorInfo}";
    echo "<br><br>Troubleshooting steps:";
    echo "<br>1. Verify that 2-Step Verification is enabled in your Google Account";
    echo "<br>2. Check if the App Password is correct and properly formatted";
    echo "<br>3. Make sure your Google Account allows 'Less secure app access'";
    echo "<br>4. Try generating a new App Password if the current one doesn't work";
}
?> 