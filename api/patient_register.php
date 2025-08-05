<?php
// api/patient_register.php
ini_set('display_errors', 0);
error_reporting(0);
header('Content-Type: application/json');
require 'db_connect.php';

$response = ['success' => false, 'message' => 'An unknown error occurred.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // --- Validation ---
    if (empty($full_name) || empty($username) || empty($password)) {
        $response['message'] = 'All fields are required.';
    } elseif (strlen($password) < 8) {
        $response['message'] = 'Password must be at least 8 characters long.';
    } elseif ($password !== $confirm_password) {
        $response['message'] = 'Passwords do not match.';
    } else {
        try {
            // Check if username already exists
            $stmt = $pdo->prepare("SELECT user_id FROM users WHERE username = ?");
            $stmt->execute([$username]);
            if ($stmt->fetch()) {
                $response['message'] = 'This username is already taken. Please choose another.';
            } else {
                // Hash the password for secure storage
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                $role = 'patient'; // Hardcode the role for this registration form

                $sql = "INSERT INTO users (full_name, username, password_hash, role) VALUES (?, ?, ?, ?)";
                $insert_stmt = $pdo->prepare($sql);
                $insert_stmt->execute([$full_name, $username, $password_hash, $role]);

                $response = ['success' => true, 'message' => 'Registration successful! You can now log in.'];
            }
        } catch (Exception $e) {
            $response['message'] = 'A server error occurred. Please try again later.';
            // error_log('Patient Registration Error: ' . $e->getMessage());
        }
    }
} else {
    $response['message'] = 'Invalid request method.';
}

echo json_encode($response);
?>
