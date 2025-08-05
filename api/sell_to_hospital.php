<?php
require_once 'db_connect.php';

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

header('Content-Type: application/json');

$response = ['success' => false, 'message' => 'Invalid request.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    $distributor_product_id = $input['distributor_product_id'] ?? null;
    $product_id = $input['product_id'] ?? null;
    $hospital_id = $input['hospital_id'] ?? null;
    $quantity = $input['quantity'] ?? null;
    $actor_id = $input['actor_id'] ?? null; // The distributor selling the product

    // Input Validation
    $required_fields = ['distributor_product_id', 'product_id', 'hospital_id', 'quantity', 'actor_id'];
    foreach ($required_fields as $field) {
        if (empty($input[$field]) || !is_numeric($input[$field])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => "Missing or invalid parameter: $field"]);
            exit;
        }
    }

    $distributor_product_id = (int)$input['distributor_product_id'];
    $product_id = (int)$input['product_id'];
    $hospital_id = (int)$input['hospital_id'];
    $quantity = (int)$input['quantity'];
    $actor_id = (int)$input['actor_id']; // The distributor selling the product

    if ($quantity <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Quantity must be greater than zero.']);
        exit;
    }
    error_log("sell_to_hospital.php received: distributor_product_id=$distributor_product_id, product_id=$product_id, hospital_id=$hospital_id, quantity=$quantity, actor_id=$actor_id");
    try {
        $pdo->beginTransaction();

        // 1. Check current quantity in distributor_products
        $stmt = $pdo->prepare("SELECT quantity FROM distributor_products WHERE distributor_product_id = ? AND pickup_status = 'picked_up' FOR UPDATE");
        $stmt->execute([$distributor_product_id]);
        $current_quantity = $stmt->fetchColumn();

        if ($current_quantity === false) {
            throw new Exception("Product not found in your inventory or not yet picked up.");
        }
        if ($current_quantity < $quantity) {
            throw new Exception("Insufficient stock. You only have $current_quantity units available.");
        }

        // 2. Decrease distributor's stock
        $new_distributor_quantity = $current_quantity - $quantity;
        if ($new_distributor_quantity > 0) {
            $stmt = $pdo->prepare("UPDATE distributor_products SET quantity = ? WHERE distributor_product_id = ?");
            $stmt->execute([$new_distributor_quantity, $distributor_product_id]);
        } else {
            $stmt = $pdo->prepare("DELETE FROM distributor_products WHERE distributor_product_id = ?");
            $stmt->execute([$distributor_product_id]);
        }

        // 3. Add/Update quantity in hospital_products
        // Check if product already exists for this hospital
        $stmt = $pdo->prepare("SELECT hospital_product_id, quantity FROM hospital_products WHERE hospital_id = ? AND product_id = ?");
        $stmt->execute([$hospital_id, $product_id]);
        $hospital_product = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($hospital_product) {
            // Update existing entry
            $stmt = $pdo->prepare("UPDATE hospital_products SET quantity = quantity + ? WHERE hospital_product_id = ?");
            $stmt->execute([$quantity, $hospital_product['hospital_product_id']]);
            $current_hospital_product_id = $hospital_product['hospital_product_id'];
        } else {
            // Insert new entry
            $stmt = $pdo->prepare("INSERT INTO hospital_products (hospital_id, product_id, quantity) VALUES (?, ?, ?)");
            $stmt->execute([$hospital_id, $product_id, $quantity]);
            $current_hospital_product_id = $pdo->lastInsertId();
        }

        $stmt = $pdo->prepare("INSERT INTO hospital_received_history (hospital_product_id, product_id, quantity, received_from_actor_id, received_timestamp) VALUES (?, ?, ?, ?, NOW())");
        $stmt->execute([$current_hospital_product_id, $product_id, $quantity, $actor_id]);

        // 5. Add to blockchain audit trail
        $stmt_prev_hash = $pdo->prepare("SELECT current_hash FROM audit_trail WHERE product_id = ? ORDER BY log_id DESC LIMIT 1");
        $stmt_prev_hash->execute([$product_id]);
        $previous_hash = $stmt_prev_hash->fetchColumn() ?: str_repeat('0', 64);

        $action = 'PRODUCT_SOLD_TO_HOSPITAL';
        $details = json_encode([
            'product_id' => $product_id,
            'sold_by_distributor_id' => $actor_id,
            'sold_to_hospital_id' => $hospital_id,
            'quantity' => $quantity,
            'timestamp' => date('Y-m-d H:i:s')
        ]);

        $hash_input = $product_id . $action . $actor_id . $details . $previous_hash;
        $current_hash = hash('sha256', $hash_input);

        $stmt = $pdo->prepare("INSERT INTO audit_trail (product_id, action, actor_id, details, current_hash, previous_hash) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$product_id, $action, $actor_id, $details, $current_hash, $previous_hash]);

        $response['success'] = true;
        $pdo->commit();

    } catch (PDOException $e) {
        $pdo->rollBack();
        $response['message'] = 'Database error: ' . $e->getMessage();
        error_log('PDOException in sell_to_hospital.php: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        exit;
    } catch (Exception $e) {
        if ($pdo->inTransaction()) { $pdo->rollBack(); }
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        exit;
    }
}
else {
    http_response_code(405);
    $response = ['success' => false, 'message' => 'Invalid request method.'];
}

echo json_encode($response);
?>