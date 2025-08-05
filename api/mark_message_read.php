<?php
require 'db_connect.php';
session_start();

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$message_id = $input['message_id'] ?? null;
$user_id = $input['user_id'] ?? ($_SESSION['user_id'] ?? null);

if (!$message_id || !$user_id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Message ID and User ID are required.']);
    exit;
}

try {
    // Ensure the user is the recipient before marking as read
    $stmt = $pdo->prepare(
        "UPDATE messages SET read_status = TRUE WHERE message_id = ? AND recipient_id = ?"
    );
    $stmt->execute([$message_id, $user_id]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Message marked as read.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Message not found or not authorized.']);
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
