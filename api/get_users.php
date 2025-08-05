<?php
// api/get_users.php
ini_set('display_errors', 0);
error_reporting(0);
header('Content-Type: application/json');
require 'db_connect.php';

// In a real application, you would add session authentication to ensure only admins can access this.
// session_start();
// if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
//     http_response_code(403);
//     echo json_encode(['success' => false, 'message' => 'Unauthorized']);
//     exit;
// }

try {
    // Fetch all users from the database, excluding their password hashes for security.
    $stmt = $pdo->query("SELECT user_id, username, full_name, role FROM users ORDER BY user_id ASC");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'users' => $users ?: []]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to fetch user list.']);
}
?>
