<?php
require '../api/db_connect.php';

header('Content-Type: application/json');

// Get pharmacist_id from the query string
if (!isset($_GET['pharmacist_id']) || !is_numeric($_GET['pharmacist_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'A valid pharmacist ID is required.']);
    exit;
}

$pharmacist_id = (int)$_GET['pharmacist_id'];

try {
    // MODIFIED: Added p.unique_identifier to the SELECT statement
    $stmt = $pdo->prepare("
        SELECT 
            p.product_id,
            p.brand_name,
            p.batch_number,
            p.expiry_date,
            pp.quantity,
            p.unique_identifier 
        FROM pharmacist_products pp
        JOIN products p ON pp.product_id = p.product_id
        WHERE pp.pharmacist_id = ? AND pp.quantity > 0
        ORDER BY p.brand_name ASC
    ");
    $stmt->execute([$pharmacist_id]);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'products' => $products
    ]);

} catch (Exception $e) {
    http_response_code(500);
    error_log("Error in get_pharmacist_products.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Failed to fetch pharmacist inventory: ' . $e->getMessage()
    ]);
}
?>
