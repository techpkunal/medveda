<?php
// api/dispense_product.php

// Disable error display to prevent invalid JSON responses
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Start output buffering to ensure clean JSON response
ob_start();

header('Content-Type: application/json');
require 'db_connect.php';

// In a real app, pharmacist_id would come from a session.
// Using static ID for 'pharmacist_y' from the users table.
$pharmacist_id = 4;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // --- FIX: Added specific HTTP status code for wrong method ---
    http_response_code(405); // Method Not Allowed
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

$product_id = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
$patient_id = filter_input(INPUT_POST, 'patient_id', FILTER_VALIDATE_INT);
$quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT);
$unique_identifier = filter_input(INPUT_POST, 'unique_identifier', FILTER_SANITIZE_STRING);

// --- FIX: Improved validation ---
if (!$product_id || !$patient_id || !$quantity || $quantity <= 0 || !$unique_identifier) {
    // --- FIX: Added specific HTTP status code for bad data ---
    http_response_code(400); // Bad Request
    echo json_encode(['success' => false, 'message' => 'Invalid data. Product, Patient, Quantity, and Identifier are required.']);
    exit;
}

try {
    $pdo->beginTransaction();

    // 1. Lock product row and check stock
    $stmt = $pdo->prepare("SELECT stock_quantity, brand_name FROM products WHERE product_id = ? FOR UPDATE");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        throw new Exception("Product not found.");
    }
    if ($product['stock_quantity'] < $quantity) {
        // --- FIX: More descriptive error message ---
        throw new Exception("Insufficient stock. Only " . $product['stock_quantity'] . " units available.");
    }

    // 2. Update stock quantity
    $new_quantity = $product['stock_quantity'] - $quantity;
    $pdo->prepare("UPDATE products SET stock_quantity = ? WHERE product_id = ?")->execute([$new_quantity, $product_id]);

    // 3. Create the audit trail log (blockchain entry)
    $stmt_last_hash = $pdo->prepare("SELECT current_hash FROM audit_trail WHERE product_id = ? ORDER BY log_id DESC LIMIT 1");
    $stmt_last_hash->execute([$product_id]);
    $last_hash = $stmt_last_hash->fetchColumn();

    if ($last_hash === false) { // Check for false explicitly, as a hash could be '0'
        throw new Exception("CRITICAL: Cannot dispense product with no registration history.");
    }
    
    $action = 'PRODUCT_DISPENSED';
    $log_details = json_encode([
        'brand_name' => $product['brand_name'],
        'dispensed_quantity' => (int)$quantity,
        'remaining_stock' => $new_quantity,
        'patient_id' => (int)$patient_id
    ]);
    
    $block_data_string = $product_id . $action . $pharmacist_id . $log_details . $last_hash;
    $current_hash = hash('sha256', $block_data_string);

    $audit_sql = "INSERT INTO audit_trail (product_id, action, actor_id, details, current_hash, previous_hash) VALUES (?, ?, ?, ?, ?, ?)";
    $pdo->prepare($audit_sql)->execute([$product_id, $action, $pharmacist_id, $log_details, $current_hash, $last_hash]);
    $audit_log_id = $pdo->lastInsertId();

    // 4. Dispensation tracking is handled via audit_trail table above
    // No separate dispensations table needed - all data is in audit_trail

    $pdo->commit();
    
    // Clean output buffer and send JSON response
    ob_clean();
    echo json_encode([
        'success' => true,
        'message' => 'Product dispensed and recorded successfully.',
        'unique_identifier' => $unique_identifier // Pass identifier back for refresh
    ]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    // Clean output buffer and send error JSON response
    ob_clean();
    http_response_code(500); // Internal Server Error
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
// --- FIX: Removed invalid character from end of file ---
?>
