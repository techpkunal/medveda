<?php
// medchain/api/register_product.php
session_start();
require 'db_connect.php';

// In a real app, this would come from a session after login.
$manufacturer_id = 1;
$manufacturer_name = "Default Pharma Inc."; // Static for now

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $pdo->beginTransaction();

    try {
        $unique_identifier = uniqid('med_', true);

        $sql = "INSERT INTO products (
                    unique_identifier, manufacturer_id, brand_name, generic_name, batch_number, product_code_sku,
                    manufacturer_name, manufacturing_license_number, country_of_origin, manufacturing_date, expiry_date,
                    active_ingredients, excipients, formulation_type, strength, dosage_instructions, route_of_administration,
                    drug_license_number, approval_authority, approval_date, storage_conditions, shelf_life,
                    mrp, pack_size, packaging_type, therapeutic_category, indications, contraindications,
                    side_effects, precautions, interactions, stock_quantity, reorder_level, supplier_name
                ) VALUES (
                    :unique_identifier, :manufacturer_id, :brand_name, :generic_name, :batch_number, :product_code_sku,
                    :manufacturer_name, :manufacturing_license_number, :country_of_origin, :manufacturing_date, :expiry_date,
                    :active_ingredients, :excipients, :formulation_type, :strength, :dosage_instructions, :route_of_administration,
                    :drug_license_number, :approval_authority, :approval_date, :storage_conditions, :shelf_life,
                    :mrp, :pack_size, :packaging_type, :therapeutic_category, :indications, :contraindications,
                    :side_effects, :precautions, :interactions, :stock_quantity, :reorder_level, :supplier_name
                )";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':unique_identifier' => $unique_identifier,
            ':manufacturer_id' => $manufacturer_id,
            ':brand_name' => htmlspecialchars($_POST['brand_name']),
            ':generic_name' => htmlspecialchars($_POST['generic_name']),
            ':batch_number' => htmlspecialchars($_POST['batch_number']),
            ':product_code_sku' => htmlspecialchars($_POST['product_code_sku']),
            ':manufacturer_name' => $manufacturer_name,
            ':manufacturing_license_number' => htmlspecialchars($_POST['manufacturing_license_number']),
            ':country_of_origin' => htmlspecialchars($_POST['country_of_origin']),
            ':manufacturing_date' => $_POST['manufacturing_date'],
            ':expiry_date' => $_POST['expiry_date'],
            ':active_ingredients' => htmlspecialchars($_POST['active_ingredients']),
            ':excipients' => htmlspecialchars($_POST['excipients']),
            ':formulation_type' => htmlspecialchars($_POST['formulation_type']),
            ':strength' => htmlspecialchars($_POST['strength']),
            ':dosage_instructions' => htmlspecialchars($_POST['dosage_instructions']),
            ':route_of_administration' => htmlspecialchars($_POST['route_of_administration']),
            ':drug_license_number' => htmlspecialchars($_POST['drug_license_number']),
            ':approval_authority' => htmlspecialchars($_POST['approval_authority']),
            ':approval_date' => empty($_POST['approval_date']) ? null : $_POST['approval_date'],
            ':storage_conditions' => htmlspecialchars($_POST['storage_conditions']),
            ':shelf_life' => htmlspecialchars($_POST['shelf_life']),
            ':mrp' => empty($_POST['mrp']) ? null : $_POST['mrp'],
            ':pack_size' => htmlspecialchars($_POST['pack_size']),
            ':packaging_type' => htmlspecialchars($_POST['packaging_type']),
            ':therapeutic_category' => htmlspecialchars($_POST['therapeutic_category']),
            ':indications' => htmlspecialchars($_POST['indications']),
            ':contraindications' => htmlspecialchars($_POST['contraindications']),
            ':side_effects' => htmlspecialchars($_POST['side_effects']),
            ':precautions' => htmlspecialchars($_POST['precautions']),
            ':interactions' => htmlspecialchars($_POST['interactions']),
            ':stock_quantity' => (int)$_POST['stock_quantity'],
            ':reorder_level' => empty($_POST['reorder_level']) ? null : (int)$_POST['reorder_level'],
            ':supplier_name' => htmlspecialchars($_POST['supplier_name'])
        ]);

        $product_id = $pdo->lastInsertId();

        // --- CORRECTED "BLOCKCHAIN" LOGIC ---
        // For a new product, the previous hash is always the "genesis block" hash.
        $previous_hash = str_repeat('0', 64);

        $action = 'PRODUCT_REGISTERED';
        $log_details = json_encode([
            'brand_name' => $_POST['brand_name'],
            'batch' => $_POST['batch_number'],
            'quantity' => (int)$_POST['stock_quantity']
        ]);
        $block_data_string = $product_id . $action . $manufacturer_id . $log_details . $previous_hash;
        $current_hash = hash('sha256', $block_data_string);

        $audit_sql = "INSERT INTO audit_trail (product_id, action, actor_id, details, current_hash, previous_hash) VALUES (?, ?, ?, ?, ?, ?)";
        $audit_stmt = $pdo->prepare($audit_sql);
        $audit_stmt->execute([$product_id, $action, $manufacturer_id, $log_details, $current_hash, $previous_hash]);

        $pdo->commit();
        header("Location: ../manufacturer/register.php?status=success&uid=" . urlencode($unique_identifier));
        exit();

    } catch (Exception $e) {
        $pdo->rollBack();
        // For debugging: error_log("Registration Error: " . $e->getMessage());
        header("Location: ../manufacturer/register.php?status=error");
        exit();
    }
}
?>
