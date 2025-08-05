    <?php
    require '../api/db_connect.php';

    header('Content-Type: application/json');

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
        exit;
    }

    $input = json_decode(file_get_contents('php://input'), true);

    // Input Validation
    $required_fields = ['distributor_product_id', 'product_id', 'pharmacist_id', 'quantity', 'actor_id'];
    foreach ($required_fields as $field) {
        if (empty($input[$field]) || !is_numeric($input[$field])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => "Missing or invalid parameter: $field"]);
            exit;
        }
    }

    $distributor_product_id = (int)$input['distributor_product_id'];
    $product_id = (int)$input['product_id'];
    $pharmacist_id = (int)$input['pharmacist_id'];
    $quantity_to_sell = (int)$input['quantity'];
    $actor_id = (int)$input['actor_id']; // The distributor's user ID

    try {
        $pdo->beginTransaction();

        // 1. Check distributor's stock
        $stmt = $pdo->prepare("SELECT quantity FROM distributor_products WHERE distributor_product_id = ? AND pickup_status = 'picked_up' FOR UPDATE");
        $stmt->execute([$distributor_product_id]);
        $distributor_stock = $stmt->fetchColumn();

        if ($distributor_stock === false) {
            throw new Exception("Product not found in your inventory or not yet picked up.");
        }
        if ($distributor_stock < $quantity_to_sell) {
            throw new Exception("Insufficient stock. You only have $distributor_stock units available.");
        }

        // 2. Decrease distributor's stock
        $new_distributor_quantity = $distributor_stock - $quantity_to_sell;
        if ($new_distributor_quantity > 0) {
            $stmt = $pdo->prepare("UPDATE distributor_products SET quantity = ? WHERE distributor_product_id = ?");
            $stmt->execute([$new_distributor_quantity, $distributor_product_id]);
        } else {
            $stmt = $pdo->prepare("DELETE FROM distributor_products WHERE distributor_product_id = ?");
            $stmt->execute([$distributor_product_id]);
        }

        // 3. Add or update pharmacist's stock
        $stmt = $pdo->prepare("INSERT INTO pharmacist_products (product_id, pharmacist_id, quantity) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE quantity = quantity + VALUES(quantity)");
        $stmt->execute([$product_id, $pharmacist_id, $quantity_to_sell]);

        // 4. NEW: Record this transaction in the pharmacist_received_history table
        $stmt_history = $pdo->prepare("INSERT INTO pharmacist_received_history (pharmacist_id, product_id, quantity_received, received_from_distributor_id) VALUES (?, ?, ?, ?)");
        $stmt_history->execute([$pharmacist_id, $product_id, $quantity_to_sell, $actor_id]);

        // 5. Add to blockchain audit trail
        $stmt_prev_hash = $pdo->prepare("SELECT current_hash FROM audit_trail WHERE product_id = ? ORDER BY log_id DESC LIMIT 1");
        $stmt_prev_hash->execute([$product_id]);
        $previous_hash = $stmt_prev_hash->fetchColumn() ?: str_repeat('0', 64);

        $action = 'PRODUCT_SOLD_TO_PHARMACIST';
        $details = json_encode([
            'product_id' => $product_id,
            'sold_by_distributor_id' => $actor_id,
            'sold_to_pharmacist_id' => $pharmacist_id,
            'quantity' => $quantity_to_sell,
            'timestamp' => date('Y-m-d H:i:s')
        ]);

        $hash_input = $product_id . $action . $actor_id . $details . $previous_hash;
        $current_hash = hash('sha256', $hash_input);

        $stmt = $pdo->prepare("INSERT INTO audit_trail (product_id, action, actor_id, details, current_hash, previous_hash) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$product_id, $action, $actor_id, $details, $current_hash, $previous_hash]);

        $pdo->commit();

        echo json_encode(['success' => true, 'message' => 'Sale completed and recorded.']);

    } catch (Exception $e) {
        if ($pdo->inTransaction()) { $pdo->rollBack(); }
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    ?>
    