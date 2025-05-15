<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session if needed
session_start();

// Include PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

// Log incoming request
error_log("Received request: " . print_r($_POST, true));

// Database connection
$servername = "localhost";
$dbname = "u609938602_skylivings";
$username = "u609938602_SkyLivings";
$password = "Rohit@6378";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    error_log("Database connection successful");
} catch(PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}

// Function to sanitize input
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Function to send email
function send_email($to, $subject, $message) {
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: Sky Livings <noreply@skylivings.com>" . "\r\n";
    
    // Log the email attempt
    error_log("Attempting to send email to: $to");
    error_log("Subject: $subject");
    
    $result = mail($to, $subject, $message, $headers);
    
    if (!$result) {
        error_log("Failed to send email. Error: " . error_get_last()['message']);
    }
    
    return $result;
}

// Get form data and log it
$name = isset($_POST['name']) ? trim($_POST['name']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
$visit_date = isset($_POST['visit_date']) ? trim($_POST['visit_date']) : '';
$visit_time = isset($_POST['visit_time']) ? trim($_POST['visit_time']) : '';

error_log("Processed form data:");
error_log("Name: " . $name);
error_log("Email: " . $email);
error_log("Phone: " . $phone);
error_log("Visit Date: " . $visit_date);
error_log("Visit Time: " . $visit_time);

// Validate required fields
if (empty($name) || empty($email) || empty($phone) || empty($visit_date) || empty($visit_time)) {
    $missing = [];
    if (empty($name)) $missing[] = 'name';
    if (empty($email)) $missing[] = 'email';
    if (empty($phone)) $missing[] = 'phone';
    if (empty($visit_date)) $missing[] = 'visit date';
    if (empty($visit_time)) $missing[] = 'visit time';
    
    error_log("Missing fields: " . implode(', ', $missing));
    echo json_encode(['status' => 'error', 'message' => 'Please fill in all required fields: ' . implode(', ', $missing)]);
    exit;
}

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    error_log("Invalid email format: " . $email);
    echo json_encode(['status' => 'error', 'message' => 'Please enter a valid email address']);
    exit;
}

// Validate phone number (10 digits)
if (!preg_match('/^[0-9]{10}$/', $phone)) {
    error_log("Invalid phone format: " . $phone);
    echo json_encode(['status' => 'error', 'message' => 'Please enter a valid 10-digit phone number']);
    exit;
}

try {
    // First, check if the visits table exists
    $stmt = $conn->query("SHOW TABLES LIKE 'visits'");
    if ($stmt->rowCount() == 0) {
        // Create the visits table if it doesn't exist
        $sql = "CREATE TABLE IF NOT EXISTS visits (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            email VARCHAR(255) NOT NULL,
            phone VARCHAR(20) NOT NULL,
            visit_date DATE NOT NULL,
            visit_time TIME NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        $conn->exec($sql);
        error_log("Created visits table");
    }

    // Insert into database
    $stmt = $conn->prepare("INSERT INTO visits (name, email, phone, visit_date, visit_time) VALUES (?, ?, ?, ?, ?)");
    $result = $stmt->execute([$name, $email, $phone, $visit_date, $visit_time]);
    
    if (!$result) {
        error_log("Database insert failed: " . print_r($stmt->errorInfo(), true));
        throw new Exception("Failed to save visit details");
    }
    
    error_log("Successfully inserted visit into database");

    // Send email notification
    $mail = new PHPMailer(true);

    // Server settings
    $mail->SMTPDebug = 2; // Enable verbose debug output
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'skylivings20@gmail.com';
    $mail->Password = 'olql aorl lini ecdt';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    // Recipients
    $mail->setFrom('skylivings20@gmail.com', 'Sky Livings');
    $mail->addAddress('skylivings20@gmail.com');
    $mail->addAddress($email); // Send confirmation to visitor

    // Content
    $mail->isHTML(true);
    $mail->Subject = 'Visit Scheduled - Sky Livings';
    $mail->Body = "
        <h2>Visit Schedule Confirmation</h2>
        <p>Dear {$name},</p>
        <p>Your visit to Sky Livings has been scheduled successfully.</p>
        <p><strong>Visit Details:</strong></p>
        <ul>
            <li><strong>Date:</strong> {$visit_date}</li>
            <li><strong>Time:</strong> {$visit_time}</li>
        </ul>
        <p>If you need to reschedule or have any questions, please contact us.</p>
        <p>Thank you for choosing Sky Livings!</p>
    ";

    $mail->send();
    error_log("Email sent successfully");

    echo json_encode([
        'status' => 'success', 
        'message' => 'Your visit has been scheduled successfully. A confirmation email has been sent.'
    ]);

} catch (Exception $e) {
    error_log("Error occurred: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    // Return a more specific error message
    echo json_encode([
        'status' => 'error',
        'message' => 'An error occurred while processing your request. Please try again or contact support.'
    ]);
}
?> 