<?php
require '../api/db_connect.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

// Input Validation
$required_fields = ['product_id', 'hospital_id', 'patient_id', 'quantity'];
foreach ($required_fields as $field) {
    if (empty($input[$field]) || !is_numeric($input[$field])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => "Missing or invalid parameter: $field"]);
        exit;
    }
}

$product_id = (int)$input['product_id'];
$hospital_id = (int)$input['hospital_id'];
$patient_id = (int)$input['patient_id'];
$quantity_to_dispense = (int)$input['quantity'];
$patient_name = $input['patient_name'] ?? 'Patient'; // Use patient name if provided

try {
    $pdo->beginTransaction();

    // 1. Lock and check hospital's stock
    $stmt = $pdo->prepare("SELECT quantity FROM hospital_products WHERE product_id = ? AND hospital_id = ? FOR UPDATE");
    $stmt->execute([$product_id, $hospital_id]);
    $hospital_stock = $stmt->fetchColumn();

    if ($hospital_stock === false) { throw new Exception("Product not found in hospital inventory."); }
    if ($hospital_stock < $quantity_to_dispense) { throw new Exception("Insufficient stock. Hospital only has $hospital_stock units."); }

    // 2. Decrease hospital's stock
    $new_hospital_quantity = $hospital_stock - $quantity_to_dispense;
    if ($new_hospital_quantity > 0) {
        $stmt = $pdo->prepare("UPDATE hospital_products SET quantity = ? WHERE product_id = ? AND hospital_id = ?");
        $stmt->execute([$new_hospital_quantity, $product_id, $hospital_id]);
    } else {
        $stmt = $pdo->prepare("DELETE FROM hospital_products WHERE product_id = ? AND hospital_id = ?");
        $stmt->execute([$product_id, $hospital_id]);
    }

    // 3. Add to blockchain audit trail
    $stmt_prev_hash = $pdo->prepare("SELECT current_hash FROM audit_trail WHERE product_id = ? ORDER BY log_id DESC LIMIT 1");
    $stmt_prev_hash->execute([$product_id]);
    $previous_hash = $stmt_prev_hash->fetchColumn() ?: str_repeat('0', 64);

    $action = 'PRODUCT_DISPENSED_FROM_HOSPITAL';
    $details = json_encode([
        'product_id' => $product_id,
        'dispensed_by_hospital_id' => $hospital_id,
        'patient_id' => $patient_id,
        'patient_name' => $patient_name,
        'quantity' => $quantity_to_dispense,
        'timestamp' => date('Y-m-d H:i:s')
    ]);

    $hash_input = $product_id . $action . $hospital_id . $details . $previous_hash;
    $current_hash = hash('sha256', $hash_input);

    $stmt = $pdo->prepare("INSERT INTO audit_trail (product_id, action, actor_id, details, current_hash, previous_hash) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$product_id, $action, $hospital_id, $details, $current_hash, $previous_hash]);

    $pdo->commit();

    echo json_encode(['success' => true, 'message' => 'Product dispensed successfully from hospital inventory.']);

} catch (Exception $e) {
    if ($pdo->inTransaction()) { $pdo->rollBack(); }
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>