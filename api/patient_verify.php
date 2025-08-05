<?php
/**
 * API for patient product verification.
 * This script simulates the verification of a product's unique identifier
 * against the original identifier stored in the patient's history.
 *
 * In a real-world scenario, this would involve a more robust backend check,
 * potentially querying a database or a blockchain for the product's authenticity
 * and linkage to the patient.
 */

// Set content type to JSON
header('Content-Type: application/json');

// Allow only POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
    exit;
}

// Get the raw POST data
$input = file_get_contents('php://input');
$data = [];

// Parse the input based on content type
if (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/x-www-form-urlencoded') !== false) {
    // For x-www-form-urlencoded, parse directly from $_POST
    $data = $_POST;
} else {
    // For other content types like application/json, decode the raw input
    $data = json_decode($input, true);
}

// Validate required fields
if (!isset($data['unique_identifier']) || !isset($data['original_identifier'])) {
    http_response_code(400); // Bad Request
    echo json_encode(['success' => false, 'message' => 'Missing required parameters: unique_identifier or original_identifier.']);
    exit;
}

$enteredUid = trim($data['unique_identifier']);
$originalUid = trim($data['original_identifier']);

// Simulate verification logic:
// In a real application, you would connect to your database (e.g., using PDO)
// and verify the unique_identifier against your product records and audit trail.
// For this example, we'll simply check if the entered UID matches the original UID.

// Example of how a real check might look (pseudo-code):
/*
require_once 'db_connect.php'; // Assuming you have a db_connect.php
try {
    $pdo = getDBConnection(); // Get your PDO connection

    // 1. Check if the entered UID exists in the products table
    $stmt = $pdo->prepare("SELECT product_id FROM products WHERE unique_identifier = ?");
    $stmt->execute([$enteredUid]);
    $product_id = $stmt->fetchColumn();

    if (!$product_id) {
        echo json_encode(['success' => false, 'message' => 'Product with this ID does not exist.']);
        exit;
    }

    // 2. Further verify if this product was indeed dispensed to the current patient
    //    and if the original_identifier matches. This would involve checking the audit_trail.
    //    (This part is more complex and depends on your exact database schema and logic)
    //    For simplicity, we'll just check if enteredUid matches originalUid for this mock.

    if ($enteredUid === $originalUid) {
        echo json_encode(['success' => true, 'message' => 'Product verified successfully!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Verification failed. The entered ID does not match the expected product ID.']);
    }

} catch (Exception $e) {
    error_log("Patient verification API error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'An internal server error occurred during verification.']);
}
*/

// Simple mock verification logic:
if ($enteredUid === $originalUid) {
    echo json_encode(['success' => true, 'message' => 'Verification successful!']);
} else {
    echo json_encode(['success' => false, 'message' => 'Verification failed. The entered ID does not match the expected product ID.']);
}

exit;
?>
