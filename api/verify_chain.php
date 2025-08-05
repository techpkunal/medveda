<?php
// api/verify_chain.php
header('Content-Type: application/json');
require 'db_connect.php';

try {
    // --- FIX: Fetch all logs, ordered by product_id first, then by log_id. ---
    // This is crucial for grouping logs into their respective, independent chains.
    $stmt = $pdo->query(
        "SELECT log_id, product_id, action, actor_id, details, current_hash, previous_hash
         FROM audit_trail
         ORDER BY product_id, log_id ASC"
    );
    $all_logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$all_logs) {
        // It's not an error if the trail is empty, just return an empty success response.
        echo json_encode(['success' => true, 'chain' => []]);
        exit;
    }

    $results = [];
    $product_chains = [];

    // --- FIX: Group all log entries by their product ID. ---
    foreach ($all_logs as $log) {
        $product_chains[$log['product_id']][] = $log;
    }

    // --- FIX: Iterate over each product's independent chain and validate it. ---
    foreach ($product_chains as $product_id => $chain) {
        // The first block of any product's chain must point to the "genesis block" hash (all zeros).
        // We reset this for every new product chain.
        $expected_previous_hash = str_repeat('0', 64);

        foreach ($chain as $block) {
            $block_result = ['log_id' => $block['log_id']];

            // Step 1: Verify the link to the previous block in THIS product's chain.
            if ($block['previous_hash'] !== $expected_previous_hash) {
                $block_result['status'] = 'BROKEN_LINK';
                $block_result['message'] = "Chain link is broken for Product ID {$product_id}. The hash does not match the previous block's hash.";
                $results[] = $block_result;
                
                // Mark the rest of this product's chain as untrustworthy by setting an impossible hash to check against.
                $expected_previous_hash = 'chain_is_broken_at_this_point'; 
                continue; // Move to the next block in this (now broken) chain.
            }

            // Step 2: Verify the block's internal integrity (check for data tampering).
            $block_data_string = $block['product_id'] . $block['action'] . $block['actor_id'] . $block['details'] . $block['previous_hash'];
            $recalculated_hash = hash('sha256', $block_data_string);

            if ($recalculated_hash !== $block['current_hash']) {
                $block_result['status'] = 'TAMPERED';
                $block_result['message'] = 'Block data has been tampered with. Recalculated hash does not match the stored hash.';
                $results[] = $block_result;
                
                // Mark the rest of this product's chain as untrustworthy.
                $expected_previous_hash = 'chain_is_broken_at_this_point';
                continue;
            }
            
            // If both checks pass, the block is verified.
            $block_result['status'] = 'VERIFIED';
            $block_result['message'] = 'Block is authentic and correctly linked.';
            $results[] = $block_result;

            // For the next loop, the hash we expect to see is the hash of the current, valid block.
            $expected_previous_hash = $block['current_hash'];
        }
    }

    echo json_encode(['success' => true, 'chain' => $results]);

} catch (Exception $e) {
    http_response_code(500); // Internal Server Error
    echo json_encode(['success' => false, 'message' => 'A server error occurred during verification: ' . $e->getMessage()]);
}
?>
