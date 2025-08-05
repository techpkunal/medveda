<?php
require_once 'db_connect.php';

$hospitalId = isset($_GET['hospital_id']) ? intval($_GET['hospital_id']) : 0;

if ($hospitalId === 0) {
    echo json_encode(['success' => false, 'message' => 'Hospital ID is required.']);
    exit();
}

try {
    $pdo = getDatabaseConnection();

    $sql = "SELECT hrh.received_timestamp, p.brand_name, p.batch_number, hrh.quantity, u.full_name as received_from
            FROM hospital_received_history hrh
            JOIN products p ON hrh.product_id = p.product_id
            JOIN users u ON hrh.received_from_actor_id = u.user_id
            JOIN hospital_products hp ON hrh.hospital_product_id = hp.hospital_product_id
            WHERE hp.hospital_id = :hospital_id
            ORDER BY hrh.received_timestamp DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':hospital_id', $hospitalId, PDO::PARAM_INT);
    $stmt->execute();

    $history = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'history' => $history]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}

?>