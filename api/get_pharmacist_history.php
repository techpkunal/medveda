    <?php
    require '../api/db_connect.php';
    header('Content-Type: application/json');

    if (!isset($_GET['pharmacist_id']) || !is_numeric($_GET['pharmacist_id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'A valid pharmacist ID is required.']);
        exit;
    }
    $pharmacist_id = (int)$_GET['pharmacist_id'];

    try {
        $stmt = $pdo->prepare("
            SELECT 
                h.quantity_received,
                h.received_timestamp,
                p.brand_name,
                p.batch_number,
                u.full_name as distributor_name
            FROM pharmacist_received_history h
            JOIN products p ON h.product_id = p.product_id
            JOIN users u ON h.received_from_distributor_id = u.user_id
            WHERE h.pharmacist_id = ?
            ORDER BY h.received_timestamp DESC
        ");
        $stmt->execute([$pharmacist_id]);
        $history = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['success' => true, 'history' => $history]);

    } catch (Exception $e) {
        http_response_code(500);
        error_log("Error in get_pharmacist_history.php: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Failed to fetch history: ' . $e->getMessage()]);
    }
    ?>
    