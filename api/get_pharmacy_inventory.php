<?php
/**
 * API to search for available medicine stock in a SPECIFIC pharmacy.
 * This file uses the centralized session manager for robust authentication.
 */

// Use the centralized session manager to handle authentication
require_once '../otp-login/session_manager.php';
// Use the centralized config for the database connection
require_once '../otp-login/otp_config.php';

header('Content-Type: application/json');
$response = ['success' => false, 'message' => 'An unknown error occurred.'];

try {
    // 1. Ensure a user is logged in as a patient.
    $user = requireAuth('patient');

    // 2. Get the search term and pharmacist ID from the URL query string
    $searchTerm = trim($_GET['search'] ?? '');
    $pharmacistId = filter_input(INPUT_GET, 'pharmacist_id', FILTER_VALIDATE_INT);

    if (!$pharmacistId) {
        throw new Exception("A valid pharmacy must be selected.");
    }
    
    if (empty($searchTerm)) {
        // Return success with an empty array if the search is empty
        echo json_encode(['success' => true, 'products' => []]);
        exit;
    }

    // 3. Connect to the database
    $conn = getDBConnection();

    // 4. Prepare the search query to look for products in a specific pharmacist's inventory
    $likeTerm = "%" . $searchTerm . "%";
    $stmt = $conn->prepare("
        SELECT 
            p.brand_name,
            p.generic_name,
            p.strength,
            p.manufacturer_name
        FROM 
            pharmacist_products pp
        JOIN 
            products p ON pp.product_id = p.product_id
        WHERE 
            pp.pharmacist_id = ? 
            AND (p.brand_name LIKE ? OR p.generic_name LIKE ?) 
            AND pp.quantity > 0
        ORDER BY
            p.brand_name ASC
    ");
    $stmt->bind_param("iss", $pharmacistId, $likeTerm, $likeTerm);
    $stmt->execute();
    $result = $stmt->get_result();

    $products = [];
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }

    $stmt->close();
    $conn->close();

    $response['success'] = true;
    $response['products'] = $products;
    $response['message'] = 'Inventory search successful.';

} catch (Exception $e) {
    http_response_code(500); // Internal Server Error
    $response['message'] = $e->getMessage();
    error_log("Error in get_pharmacy_inventory.php: " . $e->getMessage());
}

echo json_encode($response);
?>
