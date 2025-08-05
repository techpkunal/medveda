<?php
/**
 * MedChain OTP Sending API
 *
 * This script handles sending OTPs for both new registrations and logins.
 * It's designed to work with the MedChain application structure.
 */

// --- PHPMailer Imports ---
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// --- Composer's Autoloader ---
require 'vendor/autoload.php';
// --- MedChain Configuration ---
require_once 'otp_config.php'; // Includes DB connection and helper functions

// --- Set Headers ---
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// --- Response Array ---
$response = ['status' => 'error', 'message' => 'An unknown error occurred.'];

try {
    // 1. Check Request Method
    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        throw new Exception("Invalid request method.");
    }

    // 2. Get and Validate JSON Input
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        throw new Exception("Invalid input data.");
    }

    $email = isset($input['email']) ? sanitizeInput($input['email']) : '';
    $role = isset($input['role']) ? sanitizeInput($input['role']) : '';
    $action = isset($input['action']) ? sanitizeInput($input['action']) : 'login'; // Default to 'login'

    // 3. Validate Required Fields
    if (empty($email) || empty($role)) {
        throw new Exception("Email and role are required.");
    }
    if (!validateEmail($email)) {
        throw new Exception("Invalid email format.");
    }

    // 4. Get Database Connection
    $conn = getDBConnection();

    // 5. Generate OTP and Expiry
    $otp = rand(100000, 999999);
    $otp_expiry = date("Y-m-d H:i:s", strtotime("+10 minutes"));

    // 6. Handle Logic Based on Action (Register vs. Login)
    $stmt = $conn->prepare("SELECT user_id, is_verified FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user_exists = $result->num_rows > 0;

    if ($action === 'register') {
        if ($user_exists) {
            $user = $result->fetch_assoc();
            if ($user['is_verified']) {
                throw new Exception("An account with this email already exists and is verified. Please login.");
            }
            // If user exists but is not verified, update their OTP to allow registration completion
            $stmt_update = $conn->prepare("UPDATE users SET otp = ?, otp_expiry = ? WHERE email = ?");
            $stmt_update->bind_param("sss", $otp, $otp_expiry, $email);
            $stmt_update->execute();
            $stmt_update->close();
        } else {
            // New user for registration, insert a placeholder record
            $stmt_insert = $conn->prepare("INSERT INTO users (email, role, otp, otp_expiry, is_verified) VALUES (?, ?, ?, ?, 0)");
            $stmt_insert->bind_param("ssss", $email, $role, $otp, $otp_expiry);
            $stmt_insert->execute();
            $stmt_insert->close();
        }
    } else { // action === 'login'
        if (!$user_exists) {
            throw new Exception("No account found with this email. Please register first.");
        }
        $user = $result->fetch_assoc();
        if (!$user['is_verified']) {
            throw new Exception("Your account is not verified. Please complete the registration process.");
        }
        // User exists and is verified, update OTP for login
        $stmt_update = $conn->prepare("UPDATE users SET otp = ?, otp_expiry = ? WHERE email = ?");
        $stmt_update->bind_param("sss", $otp, $otp_expiry, $email);
        $stmt_update->execute();
        $stmt_update->close();
    }
    $stmt->close();


    // 7. --- Send Email with PHPMailer ---
    $mail = new PHPMailer(true);

    // Server settings from otp_config.php
    $mail->isSMTP();
    $mail->Host       = SMTP_HOST;
    $mail->SMTPAuth   = SMTP_AUTH;
    $mail->Username   = SMTP_USERNAME;
    $mail->Password   = SMTP_PASSWORD;
    $mail->SMTPSecure = SMTP_SECURE;
    $mail->Port       = SMTP_PORT;

    // Recipients
    $mail->setFrom(FROM_EMAIL, 'MedChain Security');
    $mail->addAddress($email);

    // Content
    $mail->isHTML(true);
    $mail->Subject = 'Your MedChain Verification Code';
    $mail->Body    = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: auto; padding: 20px; border: 1px solid #ddd; border-radius: 10px;'>
            <h2 style='color: #2c5aa0; text-align: center;'>MedChain Verification</h2>
            <p>Hello,</p>
            <p>Your one-time verification code is:</p>
            <p style='text-align: center; font-size: 24px; font-weight: bold; letter-spacing: 5px; color: #1e3c72; background-color: #f0f4f8; padding: 15px; border-radius: 5px;'>
                $otp
            </p>
            <p>This code is valid for 10 minutes. Please do not share it with anyone.</p>
            <p>If you did not request this code, you can safely ignore this email.</p>
            <hr>
            <p style='color: #666; font-size: 12px; text-align: center;'>Medveda - Secure Pharmaceutical Supply Chain</p>
        </div>";
    $mail->AltBody = "Your MedChain verification code is: $otp. It is valid for 10 minutes.";

    $mail->send();

    // 8. --- Success Response ---
    $response['status'] = 'success';
    $response['message'] = 'A verification code has been sent to your email.';

} catch (Exception $e) {
    // Catch any exceptions and update the response message
    $response['message'] = "Error: " . $e->getMessage();
    error_log("Send OTP Error: " . $e->getMessage()); // Log error for debugging
} finally {
    if (isset($conn) && $conn instanceof mysqli) {
        $conn->close();
    }
}

// --- Echo Final JSON Response ---
echo json_encode($response);
?>
