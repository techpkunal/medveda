<?php
require '../api/db_connect.php';

header('Content-Type: application/json');

try {
    // Fetches all users who have the 'pharmacist' role
    $stmt = $pdo->query("SELECT user_id, full_name FROM users WHERE role = 'pharmacist' ORDER BY full_name ASC");
    $pharmacists = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'pharmacists' => $pharmacists
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to fetch pharmacists: ' . $e->getMessage()
    ]);
}
?>
