<?php
// api/patient_login.php

session_start();
header('Content-Type: application/json');
require 'db_connect.php';

$response = ['success' => false, 'message' => 'Invalid credentials provided.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $response['message'] = 'Username and password fields cannot be empty.';
        echo json_encode($response);
        exit;
    }

    try {
        $stmt = $pdo->prepare("SELECT user_id, full_name, password_hash, role FROM users WHERE username = ? AND role = 'patient'");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verify that a user was found AND that the password is correct
        if ($user && password_verify($password, $user['password_hash'])) {
            // Regenerate session ID for security
            session_regenerate_id(true);
            
            // Set session variables
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role'] = $user['role'];
            
            $response = ['success' => true, 'message' => 'Login successful. Redirecting...'];
        } else {
            // Keep the error message generic for security
            $response['message'] = 'Invalid username or password.';
        }
    } catch (PDOException $e) {
        // Catch database-specific errors
        $response['message'] = 'Database error. Please contact an administrator.';
        // Log the detailed error for the developer, not for the user.
        error_log("Login PDOException: " . $e->getMessage());
    } catch (Exception $e) {
        // Catch other general errors
        $response['message'] = 'A server error occurred. Please try again later.';
        error_log("Login Exception: " . $e->getMessage());
    }

} else {
    $response['message'] = 'Invalid request method.';
}

echo json_encode($response);
?>
