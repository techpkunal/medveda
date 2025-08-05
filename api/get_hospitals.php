<?php
require_once 'db_connect.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

try {
    $stmt = $pdo->prepare("SELECT user_id, full_name FROM users WHERE role = 'hospital' AND is_verified = 1");
    $stmt->execute();
    $hospitals = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($hospitals) {
        $response['success'] = true;
        $response['hospitals'] = $hospitals;
    } else {
        $response['message'] = 'No verified hospitals found.';
    }
} catch (PDOException $e) {
    $response['message'] = 'Database error: ' . $e->getMessage();
}

echo json_encode($response);
?>