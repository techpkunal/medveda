<?php
/**
 * api/get_consignment_status.php
 *
 * This API endpoint determines a product's current logistics status.
 * It now correctly checks for a "delivered" status by looking for the 
 * 'PRODUCT_SOLD_TO_PHARMACIST' action in the audit trail.
 */

header('Content-Type: application/json');
require 'db_connect.php';

// --- Get Input ---
$unique_identifier = $_GET['unique_identifier'] ?? '';

if (empty($unique_identifier)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Product Unique Identifier is required.']);
    exit;
}

try {
    // --- Step 1: Find the Product ---
    $stmt_product = $pdo->prepare("SELECT product_id, brand_name, batch_number FROM products WHERE unique_identifier = ?");
    $stmt_product->execute([$unique_identifier]);
    $product = $stmt_product->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Product with this Unique ID was not found.']);
        exit;
    }
    $product_id = $product['product_id'];

    // --- Step 2: Check the Audit Trail for the latest relevant logistics action ---
    // We look for the most recent event among sale, pickup, or dispense.
    // The ORDER BY log_id DESC is crucial for getting the latest status correctly.
    $stmt_log = $pdo->prepare("
        SELECT a.action, a.actor_id, u.full_name
        FROM audit_trail a
        LEFT JOIN users u ON a.actor_id = u.user_id
        WHERE a.product_id = ? 
        AND a.action IN ('PRODUCT_SOLD_TO_PHARMACIST', 'PRODUCT_PICKED_UP_BY_DISTRIBUTOR', 'PRODUCT_DISPENSED_TO_DISTRIBUTOR')
        ORDER BY a.log_id DESC
        LIMIT 1
    ");
    $stmt_log->execute([$product_id]);
    $last_log = $stmt_log->fetch(PDO::FETCH_ASSOC);

    $response = [
        'success' => true,
        'product' => $product
    ];

    if (!$last_log) {
        // No relevant logistics event found, so it's still at the manufacturer.
        $response['status'] = 'at_manufacturer';
        $response['message'] = 'Product is at the manufacturer, not yet dispensed.';
    } elseif ($last_log['action'] === 'PRODUCT_SOLD_TO_PHARMACIST') {
        // The latest event is a sale to a pharmacist, meaning it's delivered.
        $response['status'] = 'delivered';
        $response['message'] = 'Product has been delivered to the final destination.';
        $response['distributor'] = [
            'user_id' => $last_log['actor_id'],
            'full_name' => $last_log['full_name']
        ];
    } elseif ($last_log['action'] === 'PRODUCT_PICKED_UP_BY_DISTRIBUTOR') {
        // The latest event is a pickup by the distributor.
        $response['status'] = 'picked_up';
        $response['message'] = 'Consignment is in transit.';
        $response['distributor'] = [
            'user_id' => $last_log['actor_id'],
            'full_name' => $last_log['full_name']
        ];
    } elseif ($last_log['action'] === 'PRODUCT_DISPENSED_TO_DISTRIBUTOR') {
        // The latest event is a dispense, but not yet picked up.
        $response['status'] = 'pending_pickup';
        $response['message'] = 'Consignment has been dispensed and is awaiting pickup.';
    }

    echo json_encode($response);

} catch (Exception $e) {
    http_response_code(500);
    error_log("Error in get_consignment_status.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Server Error: ' . $e->getMessage()]);
}
?>
