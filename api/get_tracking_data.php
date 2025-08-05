<?php
/**
 * api/get_tracking_data.php
 *
 * This API serves the live tracking page. It can:
 * 1. Get a list of all distributors.
 * 2. Get a list of all pharmacists (as potential destinations).
 * 3. Get all active consignments for a specific distributor.
 */

header('Content-Type: application/json');
require 'db_connect.php';

$get_param = $_GET['get'] ?? '';

try {
    if ($get_param === 'distributors') {
        $stmt = $pdo->query("SELECT user_id, full_name FROM users WHERE role = 'distributor' AND is_verified = 1 ORDER BY full_name ASC");
        $distributors = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'distributors' => $distributors]);
    } 
    elseif ($get_param === 'pharmacists') {
        // For destinations, we add their location data from user_profiles
        $stmt = $pdo->query("
            SELECT u.user_id, u.full_name, up.latitude, up.longitude 
            FROM users u
            JOIN user_profiles up ON u.user_id = up.user_id
            WHERE u.role = 'pharmacist' AND u.is_verified = 1 
            AND up.latitude IS NOT NULL AND up.longitude IS NOT NULL
            ORDER BY u.full_name ASC
        ");
        $pharmacists = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'pharmacists' => $pharmacists]);
    }
    elseif ($get_param === 'consignments' && isset($_GET['distributor_id'])) {
        $distributor_id = (int)$_GET['distributor_id'];
        
        // Find products assigned to this distributor that have been picked up and are ready to sell
        $stmt = $pdo->prepare("
            SELECT 
                dp.product_id,
                p.brand_name,
                p.batch_number,
                dp.quantity
            FROM distributor_products dp
            JOIN products p ON dp.product_id = p.product_id
            WHERE dp.distributor_product_id IN (
                SELECT distributor_product_id FROM audit_trail WHERE actor_id = ? AND action = 'PRODUCT_PICKED_UP_BY_DISTRIBUTOR'
            )
            AND dp.quantity > 0
            ORDER BY p.brand_name ASC
        ");
        
        $stmt->execute([$distributor_id]);
        $consignments = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'consignments' => $consignments]);
    }
    else {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid or missing "get" parameter.']);
    }

} catch (Exception $e) {
    http_response_code(500);
    error_log("Error in get_tracking_data.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Server Error: ' . $e->getMessage()]);
}
?>
