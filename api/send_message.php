<?php
require_once 'db_connect.php';

$input = json_decode(file_get_contents('php://input'), true);

$senderId = isset($input['sender_id']) ? intval($input['sender_id']) : 0;
$recipientId = isset($input['recipient_id']) ? intval($input['recipient_id']) : 0;
$messageContent = isset($input['message_content']) ? $input['message_content'] : '';

if ($senderId === 0 || $recipientId === 0 || empty($messageContent)) {
    echo json_encode(['success' => false, 'message' => 'Invalid input. Sender ID, Recipient ID, and Message Content are required.']);
    exit();
}

try {
    $pdo = getDatabaseConnection();

    $sql = "INSERT INTO messages (sender_id, recipient_id, message_content) VALUES (:sender_id, :recipient_id, :message_content)";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':sender_id', $senderId, PDO::PARAM_INT);
    $stmt->bindParam(':recipient_id', $recipientId, PDO::PARAM_INT);
    $stmt->bindParam(':message_content', $messageContent, PDO::PARAM_STR);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Message sent successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to send message.']);
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}

?>
