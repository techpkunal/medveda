<?php
require 'db_connect.php';
session_start();

header('Content-Type: application/json');

$user_id = $_GET['user_id'] ?? ($_SESSION['user_id'] ?? null);

if (!$user_id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'User ID is required.']);
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT 
            m.message_id,
            m.subject,
            m.message_content,
            m.timestamp,
            m.read_status,
            m.priority,
            u.full_name as sender_name,
            u.role as sender_role
        FROM messages m
        JOIN users u ON m.sender_id = u.user_id
        WHERE m.recipient_id = ?
        ORDER BY m.timestamp DESC
    ");
    $stmt->execute([$user_id]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'messages' => $messages]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
