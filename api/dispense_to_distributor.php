<?php
// Ensure this path is correct
require '../api/db_connect.php'; 

header('Content-Type: application/json');

// Don't allow direct access
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
    exit;
}

// Get the posted data
$input = json_decode(file_get_contents('php://input'), true);

// Validate input
if (!isset($input['product_id']) || !isset($input['quantity']) || !is_numeric($input['product_id']) || !is_numeric($input['quantity']) || $input['quantity'] <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Product ID and a valid, positive quantity are required.']);
    exit;
}

$product_id = (int)$input['product_id'];
$quantity = (int)$input['quantity'];
$actor_id = 1; // Assuming Admin (user_id = 1) is performing this action

// Initialize PDO connection if not already done by db_connect.php
if (!isset($pdo)) {
    // This is a fallback, db_connect.php should ideally create the $pdo object
    try {
        // You might need to define DB_HOST, DB_NAME, etc., if not included
        $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        http_response_code(500);
        error_log("Database Connection Error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Database connection failed.']);
        exit;
    }
}


try {
    $pdo->beginTransaction();

    // 1. Lock the product row to prevent race conditions and get current stock
    $stmt = $pdo->prepare("SELECT brand_name, batch_number, stock_quantity FROM products WHERE product_id = ? FOR UPDATE");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        throw new Exception('Product not found.');
    }

    if ($product['stock_quantity'] < $quantity) {
        throw new Exception('Insufficient stock. Available: ' . $product['stock_quantity'] . ', Requested: ' . $quantity);
    }

    // 2. Update product quantity
    $new_quantity = $product['stock_quantity'] - $quantity;
    $stmt = $pdo->prepare("UPDATE products SET stock_quantity = ? WHERE product_id = ?");
    $stmt->execute([$new_quantity, $product_id]);

    // 3. Add to distributor_products table
    $stmt = $pdo->prepare(
        "INSERT INTO distributor_products (product_id, quantity, dispensed_at, pickup_status) 
         VALUES (?, ?, NOW(), 'pending')"
    );
    $stmt->execute([$product_id, $quantity]);

    // 4. Add to blockchain audit trail
    // Get the hash of the last block for this specific product
    $stmt_prev_hash = $pdo->prepare("SELECT current_hash FROM audit_trail WHERE product_id = ? ORDER BY log_id DESC LIMIT 1");
    $stmt_prev_hash->execute([$product_id]);
    $last_record = $stmt_prev_hash->fetch(PDO::FETCH_ASSOC);
    // If it's the first entry for this product after registration, get that hash. If no hash exists, it's a genesis block.
    $previous_hash = $last_record ? $last_record['current_hash'] : str_repeat('0', 64);


    // Create blockchain entry for dispensing
    $action = 'PRODUCT_DISPENSED_TO_DISTRIBUTOR';
    $details = json_encode([
        'product_id' => $product_id,
        'brand_name' => $product['brand_name'],
        'batch_number' => $product['batch_number'],
        'dispensed_quantity' => $quantity,
        'remaining_quantity' => $new_quantity,
        'dispensed_at' => date('Y-m-d H:i:s')
    ]);

    // **FIXED**: Generate hash for blockchain integrity, matching the verification logic
    $hash_input = $product_id . $action . $actor_id . $details . $previous_hash;
    $current_hash = hash('sha256', $hash_input);

    $stmt = $pdo->prepare(
        "INSERT INTO audit_trail (product_id, action, actor_id, log_timestamp, details, current_hash, previous_hash) 
         VALUES (?, ?, ?, NOW(), ?, ?, ?)"
    );
    $stmt->execute([$product_id, $action, $actor_id, $details, $current_hash, $previous_hash]);

    // 5. Commit the transaction
    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Product dispensed to distributor successfully and recorded on blockchain.',
        'dispensed_quantity' => $quantity,
        'remaining_quantity' => $new_quantity,
        'blockchain_hash' => $current_hash
    ]);

} catch (Exception $e) {
    // If an error occurred, roll back the transaction
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    error_log("Error in dispense_to_distributor.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
