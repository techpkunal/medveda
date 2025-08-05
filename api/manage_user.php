<?php
// api/manage_user.php
ini_set('display_errors', 0);
error_reporting(0);
header('Content-Type: application/json');
require 'db_connect.php';

$response = ['success' => false, 'message' => 'Invalid request.'];
$action = $_POST['action'] ?? '';

// Only authenticated admins should be able to perform this.
// In a real app, you would check a session variable, e.g., if ($_SESSION['role'] !== 'admin') { die(); }

try {
    $pdo->beginTransaction();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        switch ($action) {
            case 'create':
                $username = trim($_POST['username'] ?? '');
                $full_name = trim($_POST['full_name'] ?? '');
                $password = $_POST['password'] ?? '';
                $role = $_POST['role'] ?? '';

                if (empty($username) || empty($full_name) || empty($password) || empty($role)) {
                    throw new Exception("All fields are required to create a user.");
                }

                $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
                $stmt->execute([$username]);
                if ($stmt->fetchColumn() > 0) {
                    throw new Exception("This username is already taken.");
                }

                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                $sql = "INSERT INTO users (username, full_name, password_hash, role) VALUES (?, ?, ?, ?)";
                $pdo->prepare($sql)->execute([$username, $full_name, $password_hash, $role]);
                
                $response = ['success' => true, 'message' => 'User account created successfully.'];
                break;

            case 'update':
                $user_id = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);
                $full_name = trim($_POST['full_name'] ?? '');
                $role = $_POST['role'] ?? '';
                $password = $_POST['password'] ?? '';

                if (!$user_id || empty($full_name) || empty($role)) {
                    throw new Exception("Invalid data provided for update.");
                }
                if ($user_id === 1 && $role !== 'admin') {
                    throw new Exception("The primary administrator role cannot be changed.");
                }

                if (!empty($password)) {
                    $password_hash = password_hash($password, PASSWORD_DEFAULT);
                    $sql = "UPDATE users SET full_name = ?, password_hash = ?, role = ? WHERE user_id = ?";
                    $pdo->prepare($sql)->execute([$full_name, $password_hash, $role, $user_id]);
                } else {
                    $sql = "UPDATE users SET full_name = ?, role = ? WHERE user_id = ?";
                    $pdo->prepare($sql)->execute([$full_name, $role, $user_id]);
                }

                $response = ['success' => true, 'message' => 'User account updated successfully.'];
                break;

            case 'delete':
                $user_id = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);
                if (!$user_id) { throw new Exception("Invalid User ID."); }
                if ($user_id === 1) { throw new Exception("The primary administrator account cannot be deleted."); }

                $sql = "DELETE FROM users WHERE user_id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$user_id]);

                if ($stmt->rowCount() > 0) {
                    $response = ['success' => true, 'message' => 'User account has been permanently deleted.'];
                } else {
                    throw new Exception("User not found or could not be deleted.");
                }
                break;

            default:
                throw new Exception("Invalid action specified.");
        }
    }
    
    $pdo->commit();

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
?>
