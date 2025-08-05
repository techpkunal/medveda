<?php
/**
 * API to get a patient's medication history.
 * Securely fetches dispensation records for the logged-in patient.
 */

// Use the centralized session manager to handle authentication
require_once '../otp-login/session_manager.php';
require_once '../otp-login/otp_config.php'; // For getDBConnection

// Set response headers
header('Content-Type: application/json');

// This function will handle session start, validation, and redirection if not authenticated
// or does not have the 'patient' role. It ensures only logged-in patients can access this API.
$user = requireAuth('patient');

$response = ['success' => false, 'message' => 'An error occurred.'];

try {
    // Get the patient's user ID from the validated session
    $patient_id = $_SESSION['user_id'];

    $conn = getDBConnection();

    // CORRECTED: The query now uses the correct table name 'audit_trail' instead of 'blockchain_log'.
    $stmt = $conn->prepare("
        SELECT 
            bl.log_timestamp AS dispensation_timestamp,
            p.brand_name,
            p.generic_name,
            p.strength,
            p.unique_identifier,
            JSON_UNQUOTE(JSON_EXTRACT(bl.details, '$.quantity')) AS dispensed_quantity
        FROM 
            audit_trail bl
        JOIN 
            products p ON bl.product_id = p.product_id
        WHERE 
            bl.action = 'PRODUCT_DISPENSED_TO_PATIENT'
            AND JSON_UNQUOTE(JSON_EXTRACT(bl.details, '$.patient_id')) = ?
        ORDER BY 
            bl.log_timestamp DESC
    ");

    // Bind the patient_id as a string ('s') to match the JSON text value.
    $stmt->bind_param("s", $patient_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $history = [];
    while ($row = $result->fetch_assoc()) {
        $history[] = $row;
    }

    $stmt->close();
    $conn->close();

    $response['success'] = true;
    $response['history'] = $history;
    $response['message'] = 'History retrieved successfully.';

} catch (Exception $e) {
    $response['message'] = "API Error: " . $e->getMessage();
    error_log("Patient History API Error: " . $e->getMessage());
}

echo json_encode($response);
?>
