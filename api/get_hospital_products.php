<?php
require_once 'db_connect.php';

$hospitalId = isset($_GET['hospital_id']) ? intval($_GET['hospital_id']) : 0;

if ($hospitalId === 0) {
    echo json_encode(['success' => false, 'message' => 'Hospital ID is required.']);
    exit();
}

try {
    $pdo = getDatabaseConnection();

    $sql = "SELECT hp.hospital_product_id, p.product_id, p.brand_name, p.batch_number, hp.quantity, p.expiry_date, p.unique_identifier
            FROM hospital_products hp
            JOIN products p ON hp.product_id = p.product_id
            WHERE hp.hospital_id = :hospital_id";

    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':hospital_id', $hospitalId, PDO::PARAM_INT);
    $stmt->execute();

    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'products' => $products]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}

?>