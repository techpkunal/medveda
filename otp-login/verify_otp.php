<?php
// --- Database Connection ---
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'otp_login_db');

// Create a response array to send back to the JavaScript
$response = ['status' => 'error', 'message' => 'An error occurred.'];
header('Content-Type: application/json');

// --- Main Logic ---
try {
    // 1. Create a new database connection
    $conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
    if ($conn->connect_error) {
        throw new Exception("Database connection failed: " . $conn->connect_error);
    }

    // 2. Check if the request method is POST and data is present
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['email']) && isset($_POST['otp'])) {
        $email = $_POST['email'];
        $otp_submitted = $_POST['otp'];

        // 3. Sanitize and Validate Email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email format provided.");
        }

        // 4. Prepare and Execute Query to fetch the user's stored OTP and its expiry time
        $stmt = $conn->prepare("SELECT otp, otp_expiry FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        // 5. Check if a user with that email exists in the database
        if ($stmt->num_rows > 0) {
            $stmt->bind_result($db_otp, $db_otp_expiry);
            $stmt->fetch();

            $current_time = date("Y-m-d H:i:s");

            // 6. Verify the submitted OTP against the one in the database
            if ($db_otp == $otp_submitted) {
                
                // 7. Check if the OTP has expired
                if ($current_time < $db_otp_expiry) {
                    // Success! OTP is correct and not expired.
                    $response['status'] = 'success';
                    $response['message'] = 'Login successful! Welcome.';

                    // Security Best Practice: Clear the OTP from the database after successful verification
                    // This prevents the same OTP from being used again.
                    $stmt_clear = $conn->prepare("UPDATE users SET otp = NULL, otp_expiry = NULL WHERE email = ?");
                    $stmt_clear->bind_param("s", $email);
                    $stmt_clear->execute();
                    $stmt_clear->close();

                } else {
                    // Error: OTP has expired
                    $response['message'] = 'OTP has expired. Please request a new one.';
                }
            } else {
                // Error: OTP is incorrect
                $response['message'] = 'Invalid OTP. Please try again.';
            }
        } else {
            // Error: No user found with that email
            $response['message'] = 'No user found with this email address.';
        }
        $stmt->close();
    } else {
        // Error: Invalid request (not POST or missing data)
        throw new Exception("Invalid request. Email or OTP not provided.");
    }

    // Close the database connection
    $conn->close();

} catch (Exception $e) {
    // If any error occurs anywhere in the script, update the message
    $response['message'] = $e->getMessage();
}

// Echo the final response back to the JavaScript as a JSON string
echo json_encode($response);
?>
