<?php
/**
 * api/trace_supply_chain.php
 * This script provides a detailed lifecycle trace for a specific product.
 * It receives a product's unique identifier and returns a comprehensive
 * summary of its journey through the supply chain, including quantities
 * at each stage and a detailed event timeline.
 */

// Set headers for JSON response
header('Content-Type: application/json');

// Include the database connection script
require 'db_connect.php';

// --- Get Input ---
// Decode the JSON payload from the request body
$input = json_decode(file_get_contents('php://input'), true);

// Validate that the unique identifier was provided
if (empty($input['unique_identifier'])) {
    http_response_code(400); // Bad Request
    echo json_encode(['success' => false, 'message' => 'Unique Identifier is required.']);
    exit;
}
$unique_identifier = $input['unique_identifier'];

try {
    // --- Step 1: Find the Product ---
    $stmt_product = $pdo->prepare("SELECT product_id, brand_name, batch_number FROM products WHERE unique_identifier = ?");
    $stmt_product->execute([$unique_identifier]);
    $product = $stmt_product->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        http_response_code(404); // Not Found
        echo json_encode(['success' => false, 'message' => 'Product with this Unique ID was not found.']);
        exit;
    }
    $product_id = $product['product_id'];

    // --- Step 2: Fetch the Full Audit Trail ---
    $stmt_logs = $pdo->prepare("
        SELECT a.log_timestamp, a.action, a.details, u.full_name as actor_name
        FROM audit_trail a
        LEFT JOIN users u ON a.actor_id = u.user_id
        WHERE a.product_id = ?
        ORDER BY a.log_id ASC
    ");
    $stmt_logs->execute([$product_id]);
    $logs = $stmt_logs->fetchAll(PDO::FETCH_ASSOC);

    // --- Step 3: Process Logs for Summary and Timeline ---
    
    // --- FIX: Pre-scan logs to get quantities for dispense events ---
    // This is a workaround because the "pickup" log event doesn't contain the quantity.
    // We assume dispense and pickup events occur in the same order.
    $dispense_quantities = [];
    foreach ($logs as $log) {
        if ($log['action'] === 'PRODUCT_DISPENSED_TO_DISTRIBUTOR') {
            $details = json_decode($log['details'], true) ?: [];
            $dispense_quantities[] = (int)($details['dispensed_quantity'] ?? 0);
        }
    }
    $pickup_event_counter = 0;

    // Initialize counters for the supply chain stages
    $total_produced = 0;
    $total_dispensed_from_manufacturer = 0;
    $with_distributor = 0;
    $with_pharmacist = 0;
    $consumed = 0;
    $timeline = [];

    // Iterate over each log entry to calculate quantities and build the timeline
    foreach ($logs as $log) {
        $details = json_decode($log['details'], true) ?: [];
        
        $quantity = (int)($details['quantity'] ?? $details['dispensed_quantity'] ?? 0);

        // Update the summary counters based on the action performed
        switch ($log['action']) {
            case 'PRODUCT_REGISTERED':
                $total_produced += $quantity;
                break;
            case 'PRODUCT_DISPENSED_TO_DISTRIBUTOR':
                $with_distributor += $quantity;
                $total_dispensed_from_manufacturer += $quantity;
                break;
            case 'PRODUCT_SOLD_TO_PHARMACIST':
                $with_distributor -= $quantity;
                $with_pharmacist += $quantity;
                break;
            case 'PRODUCT_DISPENSED_TO_PATIENT':
                $with_pharmacist -= $quantity;
                $consumed += $quantity;
                break;
        }

        // --- FIX: Correctly assign quantity to pickup events for timeline display ---
        $timeline_quantity = $quantity;
        if ($log['action'] === 'PRODUCT_PICKED_UP_BY_DISTRIBUTOR') {
            // Use the pre-scanned quantity for the corresponding pickup event
            if (isset($dispense_quantities[$pickup_event_counter])) {
                $timeline_quantity = $dispense_quantities[$pickup_event_counter];
            }
            $pickup_event_counter++;
        }

        // Build a formatted entry for the detailed timeline
        $timeline[] = [
            'timestamp' => date("d M Y, h:i A", strtotime($log['log_timestamp'])),
            'actor' => htmlspecialchars($log['actor_name'] ?: 'System'),
            'action' => ucwords(str_replace('_', ' ', strtolower($log['action']))),
            'details' => "Quantity: " . $timeline_quantity
        ];
    }
    
    // --- FIX: Calculate the quantity remaining with the manufacturer ---
    $at_manufacturer = $total_produced - $total_dispensed_from_manufacturer;

    // --- Step 4: Assemble and Send the Final Response ---
    $response = [
        'success' => true,
        'product_info' => [
            'brand_name' => htmlspecialchars($product['brand_name']),
            'batch_number' => htmlspecialchars($product['batch_number'])
        ],
        'supply_summary' => [
            'at_manufacturer' => $at_manufacturer, // Added this new metric
            'produced' => $total_produced,
            'with_distributor' => $with_distributor,
            'with_pharmacist' => $with_pharmacist,
            'consumed' => $consumed
        ],
        'timeline' => $timeline
    ];

    echo json_encode($response);

} catch (Exception $e) {
    // Catch any server errors and return a 500 status code
    http_response_code(500); // Internal Server Error
    echo json_encode(['success' => false, 'message' => 'Server Error: ' . $e->getMessage()]);
}
?>
