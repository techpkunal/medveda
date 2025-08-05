<?php
require_once 'db_connect.php';

try {
    $pdo = getDatabaseConnection();

    $sql = "SELECT user_id, full_name, role FROM users WHERE role = 'admin' OR role = 'distributor'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();

    $recipients = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'recipients' => $recipients]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}

?>
