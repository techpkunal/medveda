<?php
// Forcefully disable all error reporting to ensure a clean JSON output.
// This is a critical step to prevent warnings from breaking the API response.
ini_set('display_errors', 0);
error_reporting(0);

// Set the content type to JSON for the API response
header('Content-Type: application/json');

// The database connection script should be in the same 'api' directory.
require 'db_connect.php';

// In a real application, this would come from a user session.
$manufacturer_id = 1; 

try {
    // --- Step 1: Check for ANY products first with a simple query ---
    // This is more reliable than starting with complex aggregate queries.
    $stmt_check = $pdo->prepare("SELECT product_id FROM products WHERE manufacturer_id = ? LIMIT 1");
    $stmt_check->execute([$manufacturer_id]);
    $product_exists = $stmt_check->fetch();

    // --- Step 2: Decide whether to use real data or mock data ---
    if ($product_exists) {
        // --- REAL DATA PATH ---
        // Now that we know products exist, we can safely run the complex queries.
        
        // Query for the dashboard metrics cards (with NULL protection)
        $stmt_metrics = $pdo->prepare(
            "SELECT
                SUM(IFNULL(stock_quantity, 0)) as total_stock_quantity,
                SUM(CASE WHEN expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 90 DAY) THEN 1 ELSE 0 END) as expiring_soon,
                SUM(CASE WHEN IFNULL(stock_quantity, 0) < IFNULL(reorder_level, 0) THEN 1 ELSE 0 END) as low_stock_items
            FROM products WHERE manufacturer_id = ?"
        );
        $stmt_metrics->execute([$manufacturer_id]);
        $metrics = $stmt_metrics->fetch(PDO::FETCH_ASSOC);

        // Query for the 5 most recently registered products
        $stmt_recent = $pdo->prepare(
            "SELECT brand_name, generic_name, batch_number, registration_timestamp
            FROM products WHERE manufacturer_id = ? ORDER BY registration_timestamp DESC LIMIT 5"
        );
        $stmt_recent->execute([$manufacturer_id]);
        $recent_products = $stmt_recent->fetchAll(PDO::FETCH_ASSOC);

        // Query for the category distribution chart
        $stmt_chart = $pdo->prepare(
            "SELECT therapeutic_category, COUNT(*) as count
            FROM products WHERE manufacturer_id = ? AND therapeutic_category IS NOT NULL AND therapeutic_category != ''
            GROUP BY therapeutic_category"
        );
        $stmt_chart->execute([$manufacturer_id]);
        $category_distribution = $stmt_chart->fetchAll(PDO::FETCH_ASSOC);
        
        // Build the final response with real data
        $response = [
            'success' => true,
            'metrics' => $metrics,
            'recentProducts' => $recent_products,
            'categoryDistribution' => $category_distribution,
        ];

    } else {
        // --- MOCK DATA PATH ---
        // If no products exist in the database, return sample data so the dashboard isn't empty.
        $response = [
            'success' => true,
            'message' => 'Displaying sample data. No products found in the database.',
            'metrics' => [
                'total_stock_quantity' => 18550, 'expiring_soon' => 12, 'low_stock_items' => 5,
            ],
            'recentProducts' => [
                ['brand_name' => 'CardioGuard 10mg', 'generic_name' => 'Atorvastatin', 'batch_number' => 'CG2025A', 'registration_timestamp' => '2025-07-10 11:45:00'],
                ['brand_name' => 'PainAway Extra', 'generic_name' => 'Ibuprofen', 'batch_number' => 'PA2025B', 'registration_timestamp' => '2025-07-09 15:20:00'],
            ],
            'categoryDistribution' => [
                ['therapeutic_category' => 'Cardiovascular', 'count' => 4], ['therapeutic_category' => 'Analgesic', 'count' => 6],
                ['therapeutic_category' => 'Antibiotic', 'count' => 8], ['therapeutic_category' => 'Antihistamine', 'count' => 3],
            ],
        ];
    }

    // --- Step 3: Send the final JSON response ---
    echo json_encode($response);

} catch (PDOException $e) {
    // If a database error occurs, send a structured JSON error message.
    http_response_code(500); // Internal Server Error
    echo json_encode([
        'success' => false, 
        'message' => 'Database Error: ' . $e->getMessage(),
        // Providing the file and line can help with debugging.
        'error_details' => 'Error in ' . $e->getFile() . ' on line ' . $e->getLine()
    ]);
}
?>
