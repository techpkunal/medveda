<?php
// --- PHPMailer Imports ---
// These paths are relative to your `vendor` directory
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// --- Composer's Autoloader ---
// This line loads the PHPMailer library
require 'vendor/autoload.php';

// --- Database Connection ---
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'otp_login_db'); // Use the new database name

// Create a response array
$response = ['status' => 'error', 'message' => 'An error occurred.'];
header('Content-Type: application/json');

// --- Main Logic ---
try {
    // 1. Database Connection
    $conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
    if ($conn->connect_error) {
        throw new Exception("Database connection failed: " . $conn->connect_error);
    }

    // 2. Check for POST request and email
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['email'])) {
        $email = $_POST['email'];

        // 3. Sanitize and Validate Email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email format.");
        }

        // 4. Generate OTP and Expiry Time
        $otp = rand(100000, 999999); // Generate a 6-digit OTP
        $otp_expiry = date("Y-m-d H:i:s", strtotime("+10 minutes")); // OTP valid for 10 minutes

        // 5. Store OTP in the database (Insert or Update)
        // Check if user already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            // User exists, update their OTP and expiry
            $stmt_update = $conn->prepare("UPDATE users SET otp = ?, otp_expiry = ? WHERE email = ?");
            $stmt_update->bind_param("sss", $otp, $otp_expiry, $email);
            $stmt_update->execute();
            $stmt_update->close();
        } else {
            // New user, insert their record
            $stmt_insert = $conn->prepare("INSERT INTO users (email, otp, otp_expiry) VALUES (?, ?, ?)");
            $stmt_insert->bind_param("sss", $email, $otp, $otp_expiry);
            $stmt_insert->execute();
            $stmt_insert->close();
        }
        $stmt->close();

        // 6. --- Send Email with PHPMailer ---
        $mail = new PHPMailer(true); // Passing `true` enables exceptions

        // Server settings - IMPORTANT!
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com'; // Set the SMTP server to send through
        $mail->SMTPAuth   = true;
        $mail->Username   = 'shrikantchavan0003@gmail.com'; // **REPLACE** with your Gmail address
        $mail->Password   = 'szgtvcvdxsbftxrr';     // Your NEW App Password is now here
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;

        // Recipients
        $mail->setFrom('shrikantchavan0003@gmail.com', 'Secure Login System'); // **REPLACE**
        $mail->addAddress($email); // Add a recipient

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Your One-Time Password (OTP)';
        $mail->Body    = "Your OTP is: <b>$otp</b>. It is valid for 10 minutes.";
        $mail->AltBody = "Your OTP is: $otp. It is valid for 10 minutes.";

        $mail->send();

        // If email is sent successfully
        $response['status'] = 'success';
        $response['message'] = 'OTP has been sent to your email.';
        
    } else {
        throw new Exception("Invalid request.");
    }

    $conn->close();

} catch (Exception $e) {
    // Catch any exceptions and update the response message
    $response['message'] = "Message could not be sent. Mailer Error: {$e->getMessage()}";
}

// Echo the JSON response
echo json_encode($response);
?>
