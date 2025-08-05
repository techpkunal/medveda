<?php
/**
 * MedChain OTP Verification API
 * Enhanced OTP verification with role-based authentication and session management
 */

require_once 'otp_config.php';
require 'vendor/autoload.php'; // Add PHPMailer autoloader

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Set response headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

$response = ['status' => 'error', 'message' => 'An error occurred.'];

try {
    // Check request method
    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        throw new Exception("Invalid request method.");
    }

    // Get and validate input
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        $input = $_POST;
    }

    $email = isset($input['email']) ? sanitizeInput($input['email']) : '';
    $otp_submitted = isset($input['otp']) ? sanitizeInput($input['otp']) : '';
    $role = isset($input['role']) ? sanitizeInput($input['role']) : '';
    $action = isset($input['action']) ? sanitizeInput($input['action']) : 'login';
    $registration_data = isset($input['registration_data']) ? $input['registration_data'] : null;

    // Validate required fields
    if (empty($email) || empty($otp_submitted) || empty($role)) {
        throw new Exception("Email, OTP, and role are required.");
    }

    if (!validateEmail($email)) {
        throw new Exception("Invalid email format.");
    }

    // Check IP lockout
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    if (isIPLockedOut($ip_address)) {
        throw new Exception("Too many failed attempts. Please try again later.");
    }

    // Get database connection
    $conn = getDBConnection();

    // Fetch user and OTP data
    $stmt = $conn->prepare("SELECT user_id, username, role, full_name, otp, otp_expiry, is_verified FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $stmt->close();
        $conn->close();
        logLoginAttempt($email, $ip_address, false, "User not found");
        throw new Exception("No account found with this email address.");
    }

    $user = $result->fetch_assoc();
    $stmt->close();

    // This variable will hold the correct full name for the session
    $final_full_name = $user['full_name'];
    $final_username = $user['username'];

    // Verify role matches
    if ($user['role'] !== $role) {
        $conn->close();
        logLoginAttempt($email, $ip_address, false, "Role mismatch");
        throw new Exception("Invalid role for this account.");
    }

    // Check if OTP exists
    if (empty($user['otp'])) {
        $conn->close();
        logLoginAttempt($email, $ip_address, false, "No OTP found");
        throw new Exception("No OTP found. Please request a new code.");
    }

    // Verify OTP
    if ($user['otp'] !== $otp_submitted) {
        $conn->close();
        logLoginAttempt($email, $ip_address, false, "Invalid OTP");
        throw new Exception("Invalid OTP. Please try again.");
    }

    // Check OTP expiry
    $current_time = date("Y-m-d H:i:s");
    if ($current_time > $user['otp_expiry']) {
        $conn->close();
        logLoginAttempt($email, $ip_address, false, "OTP expired");
        throw new Exception("OTP has expired. Please request a new code.");
    }

    // Handle registration completion
    if ($action === 'register') {
        if (!$registration_data) {
            throw new Exception("Registration data is required for account completion.");
        }

        // Validate registration data
        $username = isset($registration_data['username']) ? sanitizeInput($registration_data['username']) : '';
        $full_name = isset($registration_data['full_name']) ? sanitizeInput($registration_data['full_name']) : '';
        $phone = isset($registration_data['phone']) ? sanitizeInput($registration_data['phone']) : '';
        $password = isset($registration_data['password']) ? $registration_data['password'] : '';

        if (empty($username) || empty($full_name) || empty($password)) {
            throw new Exception("Username, full name, and password are required.");
        }

        // Check if username is already taken
        $stmt = $conn->prepare("SELECT user_id FROM users WHERE username = ? AND user_id != ?");
        $stmt->bind_param("si", $username, $user['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $stmt->close();
            $conn->close();
            throw new Exception("Username is already taken. Please choose another.");
        }
        $stmt->close();

        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Update user record with complete registration data
        $stmt = $conn->prepare("UPDATE users SET username = ?, password_hash = ?, full_name = ?, phone = ?, is_verified = 1, otp = NULL, otp_expiry = NULL, last_login = NOW() WHERE user_id = ?");
        $stmt->bind_param("ssssi", $username, $hashed_password, $full_name, $phone, $user['user_id']);
        $stmt->execute();
        $stmt->close();

        // Update final names for session and response
        $final_username = $username;
        $final_full_name = $full_name;

        // Create user profile
        $stmt = $conn->prepare("INSERT INTO user_profiles (user_id, organization_name, status) VALUES (?, ?, 'active')");
        $organization = isset($registration_data['organization']) ? sanitizeInput($registration_data['organization']) : ucfirst($role) . ' Organization';
        $stmt->bind_param("is", $user['user_id'], $organization);
        $stmt->execute();
        $stmt->close();

        $response['message'] = 'Registration completed successfully! You are now logged in.';
        
        // *** Send Role-Based Welcome Email ONLY on Registration ***
        try {
            $mail = new PHPMailer(true);
            
            // SMTP Configuration
            $mail->isSMTP();
            $mail->Host = SMTP_HOST;
            $mail->SMTPAuth = true;
            $mail->Username = SMTP_USERNAME;
            $mail->Password = SMTP_PASSWORD;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port = SMTP_PORT;

            // Email settings
            $mail->setFrom(FROM_EMAIL, 'Mr. Shrikant, Medveda CEO');
            $mail->addAddress($email, $final_full_name);
            $mail->isHTML(true);
            
            // Email Content based on Role
            $subject = '';
            $body = '';

            switch ($user['role']) {
                case 'pharmacist':
                    $subject = 'A Warm Welcome to Medveda, Our Pharmacy Partner';
                    $body = "
                    <p>As a pharmacist, you are at the forefront of patient care. We are honored to have you in our network and are confident that Medveda will empower you to dispense authentic medications with unparalleled efficiency and trust.</p>
                    <p>Thank you for your commitment to health and safety.</p>";
                    break;
                case 'distributor':
                    $subject = 'Welcome to the Medveda Network, Our Valued Distributor';
                    $body = "
                    <p>As a distributor, you are a critical link in the pharmaceutical supply chain. We are excited to partner with you to enhance transparency, security, and efficiency in medication distribution through Medveda.</p>
                    <p>We look forward to a successful partnership.</p>";
                    break;
                case 'patient':
                    $subject = 'Your Health is Our Priority - Welcome to Medveda';
                    $body = "
                    <p>Your health and well-being are of the utmost importance. With Medveda, you can be assured that you are receiving genuine and safe medication every time. We are here to bring you peace of mind.</p>
                    <p>Welcome to a new standard of healthcare.</p>";
                    break;
                case 'hospital':
                    $subject = 'Welcome to Medveda, Our Esteemed Hospital Partner';
                    $body = "
                    <p>As a hospital, you play a vital role in patient care and public health. Medveda is designed to support your mission by ensuring the authenticity and traceability of pharmaceutical products, enhancing patient safety and operational efficiency.</p>
                    <p>We are excited to collaborate with you in building a more secure and transparent healthcare ecosystem.</p>";
                    break;
                default:
                    $subject = 'Welcome to Medveda';
                    $body = "<p>We are thrilled to have you on board. Thank you for joining our platform. We are committed to providing a secure and reliable experience for all our users.</p>";
                    break;
            }

            $mail->Subject = $subject;
            $mail->Body = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                <h2 style='color: #2c5aa0;'>Dear {$final_full_name},</h2>
                {$body}
                <br>
                <p>Best Regards,</p>
                <p><b>Mr. Shrikant</b><br>CEO, Medveda</p>
                <hr>
                <p style='color: #666; font-size: 12px;'>Medveda - Secure Pharmaceutical Supply Chain</p>
            </div>";
            $mail->AltBody = "Welcome to Medveda, {$final_full_name}!";

            $mail->send();
        } catch (Exception $e) {
            // Log email error but don't block the registration process
            error_log("Welcome email could not be sent. Mailer Error: {$e->getMessage()}");
        }
        // *** END: Send Welcome Email ***

    } else {
        // Handle login
        if (!$user['is_verified']) {
            $conn->close();
            logLoginAttempt($email, $ip_address, false, "Account not verified");
            throw new Exception("Account not verified. Please complete registration first.");
        }

        // Clear OTP after successful verification
        $stmt = $conn->prepare("UPDATE users SET otp = NULL, otp_expiry = NULL, last_login = NOW() WHERE user_id = ?");
        $stmt->bind_param("i", $user['user_id']);
        $stmt->execute();
        $stmt->close();

        $response['message'] = 'Login successful! Welcome back.';
    }

    // Create user session, now with the user's full name
    $session_id = createUserSession($user['user_id'], $user['role'], $final_full_name);
    if (!$session_id) {
        throw new Exception("Failed to create session. Please try again.");
    }

    // Log successful attempt
    logLoginAttempt($email, $ip_address, true);

    // Close database connection
    $conn->close();

    // Success response
    $response['status'] = 'success';
    $response['user'] = [
        'user_id' => $user['user_id'],
        'username' => $final_username,
        'email' => $email,
        'role' => $user['role'],
        'full_name' => $final_full_name
    ];
    $response['session_id'] = $session_id;
    $response['redirect_url'] = getRoleRedirectURL($user['role']);
    $response['action'] = $action;

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
    error_log("OTP Verification Error: " . $e->getMessage());
}

echo json_encode($response);
?>
