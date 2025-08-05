<?php
require_once '../config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Only POST method allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['distributor_product_id']) || !isset($input['product_id'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit;
}

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $pdo->beginTransaction();

    // Check if product exists and is not already picked up
    $stmt = $pdo->prepare("
        SELECT dp.*, p.brand_name, p.batch_number 
        FROM distributor_products dp
        JOIN products p ON dp.product_id = p.product_id
        WHERE dp.id = ? AND dp.pickup_status = 'pending'
    ");
    $stmt->execute([$input['distributor_product_id']]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        throw new Exception('Product not found or already picked up');
    }

    // Update pickup status
    $stmt = $pdo->prepare("
        UPDATE distributor_products 
        SET pickup_status = 'picked_up', picked_up_at = NOW() 
        WHERE id = ?
    ");
    $stmt->execute([$input['distributor_product_id']]);

    // Add to blockchain audit trail
    $previous_hash = '';
    $stmt = $pdo->prepare("SELECT current_hash FROM audit_trail ORDER BY log_id DESC LIMIT 1");
    $stmt->execute();
    $last_record = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($last_record) {
        $previous_hash = $last_record['current_hash'];
    }

    // Create blockchain entry for pickup
    $action = 'PRODUCT_PICKED_UP';
    $actor_id = 3; // Distributor user ID
    $details = json_encode([
        'product_id' => $input['product_id'],
        'brand_name' => $product['brand_name'],
        'batch_number' => $product['batch_number'],
        'quantity' => $product['quantity'],
        'picked_up_at' => date('Y-m-d H:i:s')
    ]);

    // Generate hash for blockchain integrity
    $hash_input = $action . $actor_id . $input['product_id'] . $details . $previous_hash . date('Y-m-d H:i:s');
    $current_hash = hash('sha256', $hash_input);

    $stmt = $pdo->prepare("
        INSERT INTO audit_trail (product_id, action, actor_id, log_timestamp, details, current_hash, previous_hash) 
        VALUES (?, ?, ?, NOW(), ?, ?, ?)
    ");
    $stmt->execute([$input['product_id'], $action, $actor_id, $details, $current_hash, $previous_hash]);

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Product picked up successfully and recorded on blockchain',
        'blockchain_hash' => $current_hash
    ]);

} catch (PDOException $e) {
    $pdo->rollBack();
    error_log("Database error in pickup_product.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    if (isset($pdo)) {
        $pdo->rollBack();
    }
    error_log("General error in pickup_product.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>
