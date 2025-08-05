<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../otp-login/otp_config.php';
require_once '../otp-login/session_manager.php';

$session = validateUserSession();

if (!$session || $session['role'] !== 'distributor') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized: Invalid session or role.']);
    exit();
}

$distributor_id = $session['user_id'];
$conn = getDBConnection(); // Get DB connection from session manager's config

$sql = "
    (SELECT 
        p.brand_name, 
        p.batch_number, 
        prh.quantity_received AS quantity_sold, 
        u.full_name AS sold_to,
        'pharmacist' AS recipient_type,
        prh.received_timestamp AS sale_date
    FROM pharmacist_received_history prh
    JOIN products p ON prh.product_id = p.product_id
    JOIN users u ON prh.pharmacist_id = u.user_id
    WHERE prh.received_from_distributor_id = ?)
    UNION ALL
    (SELECT 
        p.brand_name, 
        p.batch_number, 
        hrh.quantity AS quantity_sold, 
        u.full_name AS sold_to,
        'hospital' AS recipient_type,
        hrh.received_timestamp AS sale_date
    FROM hospital_received_history hrh
    JOIN products p ON hrh.product_id = p.product_id
    JOIN hospital_products hp ON hrh.hospital_product_id = hp.hospital_product_id
    JOIN users u ON hp.hospital_id = u.user_id
    WHERE hrh.received_from_actor_id = ?)
    ORDER BY sale_date DESC
";

try {
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        throw new Exception('Prepare failed: ' . $conn->error);
    }

    $stmt->bind_param("ii", $distributor_id, $distributor_id);

    if (!$stmt->execute()) {
        throw new Exception('Execute failed: ' . $stmt->error);
    }

    $result = $stmt->get_result();
    $sales_history = [];
    while ($row = $result->fetch_assoc()) {
        $sales_history[] = $row;
    }

    $stmt->close();
    $conn->close();

    header('Content-Type: application/json');
    $json_response = json_encode($sales_history);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('JSON encoding error: ' . json_last_error_msg());
    }
    echo $json_response;

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
    // Optionally log the error to a file
    // error_log($e->getMessage(), 3, '/path/to/your/error.log');
}
?>
