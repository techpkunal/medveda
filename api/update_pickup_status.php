<?php
require '../api/db_connect.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

// --- Main Logic with a try-catch block for clean error handling ---
try {
    // --- FIXED: More specific and robust validation ---
    if (!isset($input['distributor_product_id']) || !is_numeric($input['distributor_product_id'])) {
        throw new Exception("Missing or invalid 'distributor_product_id'.");
    }
    if (!isset($input['product_id']) || !is_numeric($input['product_id'])) {
        throw new Exception("Missing or invalid 'product_id'.");
    }
    if (empty(trim($input['new_status']))) {
        throw new Exception("Missing 'new_status'.");
    }
    if (!isset($input['actor_id']) || !is_numeric($input['actor_id'])) {
        throw new Exception("Missing or invalid 'actor_id'.");
    }
    
    $distributor_product_id = (int)$input['distributor_product_id'];
    $product_id = (int)$input['product_id'];
    $new_status = $input['new_status'];
    $actor_id = (int)$input['actor_id']; // The distributor's user ID

    $pdo->beginTransaction();

    // 1. Check current status to prevent re-updating
    $stmt = $pdo->prepare("SELECT pickup_status FROM distributor_products WHERE distributor_product_id = ?");
    $stmt->execute([$distributor_product_id]);
    $current_status = $stmt->fetchColumn();

    if ($current_status === false) {
        throw new Exception("Product not found in your inventory.");
    }
    if ($current_status !== 'pending') {
        throw new Exception("This product has already been processed. Current status: $current_status");
    }

    // 2. Update the status in the distributor_products table
    $stmt = $pdo->prepare("UPDATE distributor_products SET pickup_status = ? WHERE distributor_product_id = ?");
    $stmt->execute([$new_status, $distributor_product_id]);

    // 3. Add the action to the blockchain audit trail
    $stmt_prev_hash = $pdo->prepare("SELECT current_hash FROM audit_trail WHERE product_id = ? ORDER BY log_id DESC LIMIT 1");
    $stmt_prev_hash->execute([$product_id]);
    $previous_hash = $stmt_prev_hash->fetchColumn() ?: str_repeat('0', 64);

    $action = 'PRODUCT_PICKED_UP_BY_DISTRIBUTOR';
    $details = json_encode([
        'product_id' => $product_id,
        'distributor_product_id' => $distributor_product_id,
        'picked_up_by_actor_id' => $actor_id,
        'timestamp' => date('Y-m-d H:i:s')
    ]);

    $hash_input = $product_id . $action . $actor_id . $details . $previous_hash;
    $current_hash = hash('sha256', $hash_input);

    $stmt = $pdo->prepare(
        "INSERT INTO audit_trail (product_id, action, actor_id, details, current_hash, previous_hash) 
         VALUES (?, ?, ?, ?, ?, ?)"
    );
    $stmt->execute([$product_id, $action, $actor_id, $details, $current_hash, $previous_hash]);

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Product status updated and recorded on the blockchain.',
        'new_status' => $new_status,
        'blockchain_hash' => $current_hash
    ]);

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(400); // Use 400 for client-side errors like bad input
    error_log("Error in update_pickup_status.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
