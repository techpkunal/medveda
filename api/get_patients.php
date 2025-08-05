<?php
// api/get_patients.php
ini_set('display_errors', 0);
error_reporting(0);
header('Content-Type: application/json');

require 'db_connect.php';

try {
    // Fetch all users who have the 'patient' role.
    // In a real app, you might filter this further.
    $stmt = $pdo->query("SELECT user_id, full_name, username FROM users WHERE role = 'patient' ORDER BY full_name ASC");
    $patients = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($patients) {
        echo json_encode(['success' => true, 'patients' => $patients]);
    } else {
        // Still return success, but with an empty array.
        echo json_encode(['success' => true, 'patients' => []]);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database query failed.']);
}
?>
