<?php
require '../api/db_connect.php';

header('Content-Type: application/json');

// Helper functions to make the output user-friendly
function format_action_title($action) {
    return ucwords(str_replace('_', ' ', strtolower($action)));
}

function format_action_details($block) {
    $details = json_decode($block['details'], true);
    if (is_null($details)) { return 'Details not available.'; }
    
    $actor = htmlspecialchars($block['actor_name']) ?: "System (ID: " . htmlspecialchars($block['actor_id']) . ")";

    switch ($block['action']) {
        case 'PRODUCT_REGISTERED':
            return "Registered by Manufacturer: {$actor}.";
        case 'PRODUCT_DISPENSED_TO_DISTRIBUTOR':
            $quantity = htmlspecialchars($details['dispensed_quantity'] ?? 'N/A');
            return "Dispensed {$quantity} units to Distributor by {$actor}.";
        case 'PRODUCT_PICKED_UP_BY_DISTRIBUTOR':
            return "Picked up from Manufacturer by Distributor: {$actor}.";
        case 'PRODUCT_SOLD_TO_PHARMACIST':
            $pharmacist_id = htmlspecialchars($details['sold_to_pharmacist_id'] ?? 'N/A');
            $quantity = htmlspecialchars($details['quantity'] ?? 'N/A');
            return "Sold {$quantity} units to Pharmacist (ID: {$pharmacist_id}) by Distributor: {$actor}.";
        case 'PRODUCT_DISPENSED_TO_PATIENT':
            $patient_name = htmlspecialchars($details['patient_name'] ?? 'N/A');
            $quantity = htmlspecialchars($details['quantity'] ?? 'N/A');
            return "Dispensed {$quantity} units to Patient '{$patient_name}' by Pharmacist: {$actor}.";
        case 'PRODUCT_SOLD_TO_HOSPITAL':
            $hospital_id = htmlspecialchars($details['sold_to_hospital_id'] ?? 'N/A');
            $quantity = htmlspecialchars($details['quantity'] ?? 'N/A');
            $hospital_name = 'N/A';
            if ($hospital_id !== 'N/A') {
                global $pdo;
                $stmt = $pdo->prepare("SELECT full_name FROM users WHERE user_id = ? AND role = 'hospital'");
                $stmt->execute([$hospital_id]);
                $hospital_name = $stmt->fetchColumn() ?: 'Unknown Hospital';
            }
            return "Sold {$quantity} units to Hospital '{$hospital_name}' (ID: {$hospital_id}) by {$actor}.";
        case 'PRODUCT_DISPENSED_FROM_HOSPITAL':
            $patient_id = htmlspecialchars($details['patient_id'] ?? 'N/A');
            $quantity = htmlspecialchars($details['quantity'] ?? 'N/A');
            $patient_name = htmlspecialchars($details['patient_name'] ?? 'N/A');
            $hospital_id_dispensed = htmlspecialchars($details['dispensed_by_hospital_id'] ?? 'N/A');
            $hospital_name_dispensed = 'N/A';
            if ($hospital_id_dispensed !== 'N/A') {
                global $pdo;
                $stmt = $pdo->prepare("SELECT full_name FROM users WHERE user_id = ? AND role = 'hospital'");
                $stmt->execute([$hospital_id_dispensed]);
                $hospital_name_dispensed = $stmt->fetchColumn() ?: 'Unknown Hospital';
            }
            return "Dispensed {$quantity} units from Hospital to Patient (ID: {$patient_id}) by {$hospital_name_dispensed}.";
        default:
            return "An unknown action was performed by Actor ID: " . htmlspecialchars($block['actor_id']);
    }
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (empty($input['unique_identifier'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Unique Identifier is required.']);
    exit;
}

$unique_identifier = $input['unique_identifier'];
// **MODIFIED**: Get the optional pharmacist_id from the JSON payload
$pharmacist_id = isset($input['pharmacist_id']) ? (int)$input['pharmacist_id'] : null;

try {
    // 1. Get main product details (including total stock as a fallback)
    $stmt = $pdo->prepare("SELECT * FROM products WHERE unique_identifier = ?");
    $stmt->execute([$unique_identifier]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        throw new Exception('Product with this Unique ID was not found.');
    }
    $product_id = $product['product_id'];

    // **MODIFIED**: If a pharmacist ID is provided, get THEIR specific stock and overwrite the main stock value.
    if ($pharmacist_id) {
        $stmt_pharma_stock = $pdo->prepare("SELECT quantity FROM pharmacist_products WHERE product_id = ? AND pharmacist_id = ?");
        $stmt_pharma_stock->execute([$product_id, $pharmacist_id]);
        $pharma_stock = $stmt_pharma_stock->fetchColumn();
        
        // Overwrite the stock_quantity with the pharmacist's actual stock.
        $product['stock_quantity'] = ($pharma_stock !== false) ? (int)$pharma_stock : 0;
    }

    // 3. Get the product's history
    $stmt_history = $pdo->prepare("
        SELECT a.*, u.full_name as actor_name
        FROM audit_trail a
        LEFT JOIN users u ON a.actor_id = u.user_id
        WHERE a.product_id = ?
        ORDER BY a.log_id ASC
    ");
    $stmt_history->execute([$product_id]);
    $logs = $stmt_history->fetchAll(PDO::FETCH_ASSOC);

    // 4. Verify the chain and format for display
    $history = [];
    $expected_previous_hash = str_repeat('0', 64);

    foreach ($logs as $block) {
        $recalculated_hash = hash('sha256', $block['product_id'] . $block['action'] . $block['actor_id'] . $block['details'] . $block['previous_hash']);
        $is_valid = ($recalculated_hash === $block['current_hash'] && $block['previous_hash'] === $expected_previous_hash);
        
        $history[] = [
            'title' => format_action_title($block['action']),
            'details' => format_action_details($block),
            'timestamp' => date("F j, Y, g:i a", strtotime($block['log_timestamp'])),
            'status' => $is_valid ? 'VERIFIED' : 'TAMPERED'
        ];

        if (!$is_valid) { break; }
        $expected_previous_hash = $block['current_hash'];
    }

    echo json_encode([
        'success' => true,
        'product' => $product,
        'history' => $history
    ]);

} catch (Exception $e) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
