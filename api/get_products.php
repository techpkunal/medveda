<?php
// api/get_products.php
// This is an improved version that combines the user's detailed query
// with the correct field names expected by the frontend.

require '../api/db_connect.php'; // Using the consistent db_connect file

header('Content-Type: application/json');

try {
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Get all products with available stock, using a detailed query
    // but ensuring 'stock_quantity' is NOT aliased to maintain compatibility with index.html
    $stmt = $pdo->prepare("
        SELECT 
            product_id,
            brand_name,
            generic_name,
            batch_number,
            manufacturer_name,
            manufacturing_date,
            expiry_date,
            stock_quantity, -- Correct field name for the frontend
            mrp,
            formulation_type,
            strength,
            therapeutic_category,
            registration_timestamp
        FROM products 
        WHERE stock_quantity > 0
        ORDER BY brand_name ASC
    ");
    
    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // The date formatting is a good addition, so it has been kept.
    foreach ($products as &$product) {
        if (!empty($product['manufacturing_date'])) {
            $product['manufacturing_date'] = date('Y-m-d', strtotime($product['manufacturing_date']));
        }
        if (!empty($product['expiry_date'])) {
            $product['expiry_date'] = date('Y-m-d', strtotime($product['expiry_date']));
        }
        if (!empty($product['registration_timestamp'])) {
            $product['registration_timestamp'] = date('Y-m-d H:i:s', strtotime($product['registration_timestamp']));
        }
    }

    echo json_encode([
        'success' => true,
        'products' => $products
    ]);

} catch (Exception $e) {
    http_response_code(500);
    error_log("Error in get_products.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'A server error occurred: ' . $e->getMessage(),
        'products' => []
    ]);
}
?>
