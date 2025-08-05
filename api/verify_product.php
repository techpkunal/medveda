<?php
// api/verify_product.php
header('Content-Type: application/json');
require 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

$unique_identifier = trim($_POST['unique_identifier'] ?? '');

if (empty($unique_identifier)) {
    echo json_encode(['success' => false, 'message' => 'Unique identifier is required.']);
    exit;
}

try {
    // 1. Fetch the product details using the unique identifier
    $stmt = $pdo->prepare("SELECT * FROM products WHERE unique_identifier = ?");
    $stmt->execute([$unique_identifier]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        echo json_encode(['success' => false, 'message' => 'Product not found.']);
        exit;
    }

    // 2. Fetch the entire audit trail for this product, ordered chronologically
    // We join with the users table to get the full name and role of the actor
    $history_stmt = $pdo->prepare(
        "SELECT a.*, u.full_name as actor_name, u.role as actor_role
         FROM audit_trail a
         JOIN users u ON a.actor_id = u.user_id
         WHERE a.product_id = ?
         ORDER BY a.log_id ASC"
    );
    $history_stmt->execute([$product['product_id']]);
    $history = $history_stmt->fetchAll(PDO::FETCH_ASSOC);

    // 3. Perform blockchain integrity check
    $integrity_status = 'VERIFIED';
    $integrity_message = 'Product Authenticity Verified';
    $last_valid_hash = str_repeat('0', 64); // The genesis hash

    foreach ($history as $log) {
        if ($log['previous_hash'] !== $last_valid_hash) {
            $integrity_status = 'TAMPERED';
            $integrity_message = 'Data Tampering Detected! Chain is broken.';
            break; // Stop checking once a break is found
        }
        // Recalculate the hash of the current block to ensure its data wasn't changed
        $block_data_string = $log['product_id'] . $log['action'] . $log['actor_id'] . $log['details'] . $log['previous_hash'];
        $recalculated_hash = hash('sha256', $block_data_string);

        if ($recalculated_hash !== $log['current_hash']) {
            $integrity_status = 'TAMPERED';
            $integrity_message = 'Data Tampering Detected! Block hash mismatch.';
            break;
        }
        $last_valid_hash = $log['current_hash'];
    }

    // 4. Send the complete response
    echo json_encode([
        'success' => true,
        'product' => $product,
        'history' => $history,
        'integrity' => [
            'status' => $integrity_status,
            'message' => $integrity_message
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500); // Internal Server Error
    echo json_encode(['success' => false, 'message' => 'Server Error: ' . $e->getMessage()]);
}
?>
