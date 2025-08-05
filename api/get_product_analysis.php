<?php
require '../api/db_connect.php';

header('Content-Type: application/json');

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

try {
    // 1. Find the product and its initial stock
    $stmt = $pdo->prepare("SELECT product_id, brand_name, batch_number, stock_quantity FROM products WHERE unique_identifier = ?");
    $stmt->execute([$unique_identifier]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        throw new Exception('Product with this Unique ID was not found.');
    }
    $product_id = $product['product_id'];

    // 2. Get all dispensation records for this product from the audit trail
    $stmt_dispensations = $pdo->prepare("
        SELECT 
            a.details,
            a.log_timestamp,
            u.full_name as pharmacist_name
        FROM audit_trail a
        JOIN users u ON a.actor_id = u.user_id
        WHERE a.product_id = ? 
        AND a.action = 'PRODUCT_DISPENSED_TO_PATIENT'
        ORDER BY a.log_timestamp DESC
    ");
    $stmt_dispensations->execute([$product_id]);
    $dispensations = $stmt_dispensations->fetchAll(PDO::FETCH_ASSOC);

    // 3. Process the data for the frontend
    $purchase_history = [];
    $total_consumed = 0;
    foreach ($dispensations as $dispensation) {
        $details = json_decode($dispensation['details'], true);
        $quantity = (int)($details['quantity'] ?? 0);
        $total_consumed += $quantity;
        
        $purchase_history[] = [
            'patient_name' => htmlspecialchars($details['patient_name'] ?? 'N/A'),
            'pharmacist_name' => htmlspecialchars($dispensation['pharmacist_name'] ?? 'N/A'),
            'quantity' => $quantity,
            'timestamp' => date("d M Y, H:i", strtotime($dispensation['log_timestamp']))
        ];
    }

    // 4. Calculate total supply (initial stock + remaining stock)
    // This logic assumes stock_quantity in 'products' is the REMAINING stock.
    // Total supply is the sum of what's left and what's been consumed.
    $total_supply = $product['stock_quantity'] + $total_consumed;


    echo json_encode([
        'success' => true,
        'product_info' => [
            'brand_name' => $product['brand_name'],
            'batch_number' => $product['batch_number']
        ],
        'analysis' => [
            'total_supply' => $total_supply,
            'total_consumed' => $total_consumed,
            'remaining_stock' => (int)$product['stock_quantity']
        ],
        'purchase_history' => $purchase_history
    ]);

} catch (Exception $e) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
