<?php
/**
 * API Endpoint: Get Recent Medications for Patient
 * Fetches the most recent medications dispensed to the currently logged-in patient.
 */

// Include required files
require_once '../config.php';
require_once '../otp-login/session_manager.php';

// Set content type to JSON
header('Content-Type: application/json');

// Ensure the user is logged in as a patient
$user = requireAuth('patient');

// Get the patient's ID from the session
$patient_id = $_SESSION['user_id'];

try {
    // Get database connection
    $pdo = getDatabaseConnection();
    
    // Query to get recent medications for the patient
    $query = "
        SELECT 
            p.brand_name,
            p.generic_name,
            dp.quantity,
            p.unit,
            dp.dispensed_at,
            DATE_ADD(dp.dispensed_at, INTERVAL 30 DAY) as next_refill,
            u.full_name as dispensed_by
        FROM 
            dispensations dp
        JOIN 
            products p ON dp.product_id = p.product_id
        JOIN 
            users u ON dp.pharmacist_id = u.user_id
        WHERE 
            dp.patient_id = :patient_id
            AND dp.status = 'dispensed'
        ORDER BY 
            dp.dispensed_at DESC
        LIMIT 5  -- Limit to 5 most recent medications
    ";
    
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':patient_id', $patient_id, PDO::PARAM_INT);
    $stmt->execute();
    
    $medications = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Return success response with medications
    echo json_encode([
        'success' => true,
        'medications' => $medications
    ]);
    
} catch (PDOException $e) {
    // Log the error and return error response
    error_log('Error in get_recent_medications.php: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to fetch recent medications. Please try again later.'
    ]);
} catch (Exception $e) {
    // Log the error and return error response
    error_log('Unexpected error in get_recent_medications.php: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'An unexpected error occurred.'
    ]);
}
?>
