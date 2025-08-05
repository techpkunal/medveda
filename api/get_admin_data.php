<?php
// api/get_admin_data.php
ini_set('display_errors', 0);
error_reporting(0);
header('Content-Type: application/json');

require 'db_connect.php';

// Initialize a default response structure
$response = [
    'success' => false,
    'message' => 'An unknown error occurred.',
    'metrics' => [
        'total_users' => 0,
        'total_products' => 0,
        'total_logs' => 0,
        'chain_issues' => 0
    ],
    'recentActivity' => [],
    'expiringProducts' => [],
    'chainIssueDetails' => [],
    'alerts' => [
        'chain_integrity_issue' => false
    ]
];

try {
    // --- METRICS ---
    $response['metrics']['total_users'] = (int)($pdo->query("SELECT COUNT(*) FROM users")->fetchColumn() ?: 0);
    $response['metrics']['total_products'] = (int)($pdo->query("SELECT COUNT(*) FROM products")->fetchColumn() ?: 0);
    $response['metrics']['total_logs'] = (int)($pdo->query("SELECT COUNT(*) FROM audit_trail")->fetchColumn() ?: 0);

    // --- RECENT ACTIVITY ---
    $stmt_activity = $pdo->query(
        "SELECT a.log_timestamp, a.action, a.actor_id, a.details, a.product_id, u.full_name as actor_name
         FROM audit_trail a
         LEFT JOIN users u ON a.actor_id = u.user_id
         ORDER BY a.log_timestamp DESC
         LIMIT 5"
    );
    $response['recentActivity'] = $stmt_activity->fetchAll(PDO::FETCH_ASSOC) ?: [];

    // --- PRODUCTS NEARING EXPIRY ---
    $stmt_expiring = $pdo->query(
        "SELECT brand_name, batch_number, expiry_date
         FROM products
         WHERE expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 90 DAY)
         ORDER BY expiry_date ASC"
    );
    $response['expiringProducts'] = $stmt_expiring->fetchAll(PDO::FETCH_ASSOC) ?: [];

    // --- FIXED CHAIN VERIFICATION & ISSUES ---
    // This new logic correctly validates each product's history as a separate chain.
    $chain_stmt = $pdo->query("SELECT log_id, product_id, action, actor_id, details, current_hash, previous_hash FROM audit_trail ORDER BY product_id, log_id ASC");
    $all_logs = $chain_stmt->fetchAll(PDO::FETCH_ASSOC);

    $chain_issues_count = 0;
    $chain_issues_details = [];
    $product_chains = [];

    // Group all logs by their product_id
    foreach ($all_logs as $log) {
        $product_chains[$log['product_id']][] = $log;
    }

    // Iterate over each product's chain and validate it independently
    foreach ($product_chains as $product_id => $chain) {
        $expected_previous_hash = str_repeat('0', 64); // Genesis hash for each new product chain

        foreach ($chain as $block) {
            // Check 1: Is the chain link broken?
            if ($block['previous_hash'] !== $expected_previous_hash) {
                $chain_issues_count++;
                $chain_issues_details[] = [
                    'log_id' => $block['log_id'],
                    'status' => 'BROKEN LINK',
                    'message' => "Chain integrity compromised for Product ID: $product_id. Block #{$block['log_id']} has a mismatched previous hash."
                ];
                break; // Stop validating this product's chain as it's broken
            }

            // Check 2: Has the block data been tampered with?
            $block_data_string = $block['product_id'] . $block['action'] . $block['actor_id'] . $block['details'] . $block['previous_hash'];
            $recalculated_hash = hash('sha256', $block_data_string);
            
            if ($recalculated_hash !== $block['current_hash']) {
                $chain_issues_count++;
                $chain_issues_details[] = [
                    'log_id' => $block['log_id'],
                    'status' => 'TAMPERED',
                    'message' => "Data tampering detected in Block #{$block['log_id']} for Product ID: $product_id. The block's contents have been altered."
                ];
                break; // Stop validating this product's chain
            }
            
            // If the block is valid, set the expectation for the next block in this product's chain
            $expected_previous_hash = $block['current_hash'];
        }
    }

    $response['metrics']['chain_issues'] = $chain_issues_count;
    $response['chainIssueDetails'] = $chain_issues_details;
    $response['alerts']['chain_integrity_issue'] = $chain_issues_count > 0;
    // --- END OF FIX ---

    // --- Final Success ---
    $response['success'] = true;
    $response['message'] = 'Data fetched successfully.';

} catch (Exception $e) {
    $response['message'] = 'Server Error: ' . $e->getMessage();
    http_response_code(500);
}

// Use flags to ensure json_encode doesn't fail
echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PARTIAL_OUTPUT_ON_ERROR);
?>