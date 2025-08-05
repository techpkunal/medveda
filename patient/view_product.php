<?php
/**
 * Patient Product View Page
 * This file uses the centralized session manager for robust authentication.
 */

// Use the centralized session manager to handle authentication
require_once '../otp-login/session_manager.php';

// This function will handle session start, validation, and redirection if not authenticated
// or does not have the 'patient' role.
$user = requireAuth('patient');

// Get patient info from the session
$patient_id = $_SESSION['user_id'];
$patient_name = isset($_SESSION['full_name']) ? $_SESSION['full_name'] : 'Patient';

// Get the unique identifier from the URL
$unique_identifier = trim($_GET['uid'] ?? '');
if (empty($unique_identifier)) {
    // If no ID is provided, redirect back to the main history page
    header("Location: index.php");
    exit;
}

// Handle logout request
if (isset($_GET['logout']) && $_GET['logout'] == 'true') {
    logout(); // Use the centralized logout function
}

// Function to fetch product details from the database
function getProductDetails($uid, $patient_id) {
    $response = ['success' => false, 'message' => 'An error occurred.'];
    try {
        require_once '../otp-login/otp_config.php';
        $conn = getDBConnection();
        
        // Find the product_id from the unique_identifier
        $stmt_product = $conn->prepare("SELECT product_id FROM products WHERE unique_identifier = ?");
        $stmt_product->bind_param("s", $uid);
        $stmt_product->execute();
        $result_product = $stmt_product->get_result();
        $product_row = $result_product->fetch_assoc();
        $stmt_product->close();

        if (!$product_row) {
            throw new Exception('Product with this Unique ID was not found.');
        }
        $product_id = $product_row['product_id'];

        // Check if this product was dispensed to the logged-in patient
        $stmt_dispensation = $conn->prepare(
            "SELECT COUNT(*) as count
             FROM audit_trail
             WHERE product_id = ? 
               AND action = 'PRODUCT_DISPENSED_TO_PATIENT'
               AND JSON_UNQUOTE(JSON_EXTRACT(details, '$.patient_id')) = ?"
        );
        $stmt_dispensation->bind_param("is", $product_id, $patient_id);
        $stmt_dispensation->execute();
        $result_dispensation = $stmt_dispensation->get_result();
        $dispensation_row = $result_dispensation->fetch_assoc();
        $stmt_dispensation->close();

        if ($dispensation_row['count'] == 0) {
            throw new Exception('You do not have permission to view this product record.');
        }

        // If checks pass, fetch all product and history details
        $stmt_details = $conn->prepare("SELECT * FROM products WHERE product_id = ?");
        $stmt_details->bind_param("i", $product_id);
        $stmt_details->execute();
        $product = $stmt_details->get_result()->fetch_assoc();
        $stmt_details->close();

        $historyStmt = $conn->prepare("
            SELECT a.*, u.full_name as actor_name 
            FROM audit_trail a
            LEFT JOIN users u ON a.actor_id = u.user_id
            WHERE a.product_id = ?
            ORDER BY a.log_id ASC
        ");
        $historyStmt->bind_param("i", $product_id);
        $historyStmt->execute();
        $historyResult = $historyStmt->get_result();
        
        $history = [];
        while ($row = $historyResult->fetch_assoc()) {
            $history[] = $row;
        }
        $product['history'] = $history;
        $historyStmt->close();

        $conn->close();
        
        $response['success'] = true;
        $response['product'] = $product;
        return $response;
        
    } catch (Exception $e) {
        error_log("Error fetching product details: " . $e->getMessage());
        $response['message'] = $e->getMessage();
        return $response;
    }
}

// Get product details
$productData = getProductDetails($unique_identifier, $patient_id);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Provenance - MedVeda</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script src="https://unpkg.com/lenis@1.1.5/dist/lenis.min.js"></script>
    <style>
        /* --- macOS Window Theme --- */
        :root {
            --primary-color: #6c63ff; /* Patient Purple */
            --primary-hover: #5a52e0;
            --primary-glow: rgba(108, 99, 255, 0.3);
            --bg-color: #e9e9e9; /* Light gray background */
            --window-bg: #FFFFFF;
            --sidebar-bg: rgba(242, 242, 247, 0.95);
            --text-primary: #1D1D1F;
            --text-secondary: #6E6E73;
            --border-color: rgba(60, 60, 67, 0.29);
            --success-color: #34C759;
            --danger-color: #FF3B30;
            --chain-color: #AF52DE; /* Chain Purple */
        }

        /* --- Base & Layout --- */
        html, body {
            margin: 0;
            padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji";
            background-color: var(--bg-color);
            color: var(--text-primary);
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
        
        html.lenis { height: auto; }
        .lenis.lenis-smooth { scroll-behavior: auto !important; }

        /* --- macOS Window Container --- */
        .macos-browser-window {
            max-width: 1600px;
            height: 90vh;
            margin: 3rem auto;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            background-color: var(--window-bg);
            display: flex;
            flex-direction: column;
            border: 2px solid var(--border-color);
        }

        /* --- macOS Title Bar --- */
        .macos-title-bar {
            background-color: #f6f6f6;
            padding: 12px;
            display: flex;
            align-items: center;
            border-bottom: 2px solid var(--border-color);
            flex-shrink: 0;
        }
        .macos-buttons { display: flex; gap: 8px; }
        .dot { width: 12px; height: 12px; border-radius: 50%; }
        .dot-red { background-color: #ff5f56; }
        .dot-yellow { background-color: #ffbd2e; }
        .dot-green { background-color: #27c93f; }

        /* --- Main Dashboard Layout --- */
        .dashboard-body {
            display: flex;
            flex-grow: 1;
            position: relative;
            overflow: hidden;
        }
        
        .sidebar {
            width: 260px;
            background-color: var(--sidebar-bg);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-right: 2px solid var(--border-color);
            flex-shrink: 0;
            z-index: 1000;
            height: 100%;
            display: flex;
            flex-direction: column;
            padding: 1.5rem 0;
        }
        .sidebar .logo {
            display: flex;
            align-items: center;
            text-decoration: none;
            color: var(--text-primary);
            margin-bottom: 3rem;
            padding: 0 1.5rem;
        }
        .sidebar .logo img { height: 36px; margin-right: 12px; }
        .sidebar .logo h1 { font-size: 1.5rem; font-weight: 800; }
        .sidebar-nav { flex-grow: 1; }
        .sidebar-nav a {
            display: flex;
            align-items: center;
            padding: 0.8rem 1.5rem;
            margin: 0.5rem 1rem;
            border-radius: 8px;
            text-decoration: none;
            color: var(--text-secondary);
            font-weight: 600;
            transition: background-color 0.2s, color 0.2s;
        }
        .sidebar-nav a.active, .sidebar-nav a:hover {
            background-color: #f3f2ff;
            color: var(--primary-color);
        }
        .sidebar-nav a i { margin-right: 1rem; width: 20px; text-align: center; }
        .sidebar .logout-link { margin-top: auto; }

        .main-content {
            flex-grow: 1;
            padding: 2.5rem 3rem;
            overflow-y: auto;
            height: 100%;
            box-sizing: border-box;
        }
        .main-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2.5rem;
        }
        .main-header .title-section a {
            color: var(--text-secondary);
            text-decoration: none;
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 600;
        }
        .main-header .title-section a:hover { color: var(--primary-color); }
        .main-header h2 { font-size: 1.8rem; font-weight: 800; margin: 0; }
        .profile-info { display: flex; align-items: center; gap: 1rem; }
        .profile-info img { width: 48px; height: 48px; border-radius: 50%; border: 2px solid var(--primary-color); }
        .profile-info .username { font-weight: 700; }

        .card {
            background-color: #fff;
            padding: 2rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            border: 2px solid var(--border-color);
        }
        .verification-banner {
            display: flex;
            align-items: center;
            padding: 1rem 1.5rem;
            border-radius: 8px;
            margin-bottom: 2rem;
            font-weight: 700;
        }
        .verification-banner.verified { background-color: #dcfce7; color: #166534; border: 2px solid #166534; }
        .verification-banner.error { background-color: #fee2e2; color: #991b1b; border: 2px solid #991b1b; }
        .verification-banner i { font-size: 1.5rem; margin-right: 1rem; }
        .details-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
        }
        .detail-item .label { color: var(--text-secondary); font-size: 0.9rem; margin-bottom: 0.25rem; font-weight: 600; }
        .detail-item .value { font-weight: 700; }
        .detail-item .unique-id { font-family: 'Roboto Mono', monospace; font-size: 0.9rem; color: var(--primary-color); font-weight: 600; }
        
        .provenance-timeline { position: relative; padding-left: 30px; }
        .provenance-timeline::before {
            content: '';
            position: absolute;
            left: 9px;
            top: 5px;
            bottom: 5px;
            width: 4px;
            background-color: var(--border-color);
            border-radius: 2px;
        }
        .timeline-item { position: relative; margin-bottom: 1.5rem; }
        .timeline-item:last-child { margin-bottom: 0; }
        .timeline-item::before {
            content: '';
            position: absolute;
            left: -28px;
            top: 5px;
            width: 14px;
            height: 14px;
            border-radius: 50%;
            background-color: var(--chain-color);
            border: 4px solid var(--window-bg);
        }
        .timeline-item .time { font-size: 0.8rem; color: var(--text-secondary); margin-bottom: 0.25rem; font-weight: 600; }
        .timeline-item .action { font-weight: 700; }
        .timeline-item .actor { font-size: 0.9rem; font-weight: 600; }
        
        .error-message {
            text-align: center;
            padding: 2rem;
            color: var(--danger-color);
        }
        .btn {
            display: inline-flex;
            align-items: center;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            border: none;
            font-size: 0.9rem;
        }
        .btn-primary {
            background-color: var(--primary-color);
            color: white;
        }
        .view-cert-btn {
            background-color: var(--primary-color);
            color: #fff;
            padding: 0.8rem 1.5rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 700;
            border: none;
            cursor: pointer;
            transition: background-color 0.2s, transform 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            margin-top: 1rem;
        }
        .view-cert-btn:hover {
            background-color: var(--primary-hover);
            transform: translateY(-2px);
        }

        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(30, 41, 59, 0.8);
            backdrop-filter: blur(5px);
            z-index: 1000;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            box-sizing: border-box;
        }
        .modal-content {
            background: white;
            border-radius: 12px;
            width: 100%;
            max-width: 900px;
            max-height: 95vh;
            overflow-y: auto;
            position: relative;
            animation: slideInFromTop 0.5s cubic-bezier(0.25, 1, 0.5, 1);
            box-shadow: 0 25px 50px -12px rgb(0 0 0 / 0.25);
            border: 2px solid var(--border-color);
        }
        @keyframes slideInFromTop {
            from { transform: translateY(-50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        .modal-actions {
            position: absolute;
            top: 15px;
            right: 15px;
            z-index: 1011;
            display: flex;
            gap: 10px;
        }
        .modal-btn {
            background: #f1f5f9;
            border: none;
            border-radius: 50%;
            width: 36px;
            height: 36px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            color: var(--text-light);
        }
        .modal-btn:hover { background: #e2e8f0; color: var(--text-dark); }
        
        .invoice-wrapper {
            padding: 50px;
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #374151;
            position: relative;
            background-color: #fff;
        }
        .invoice-wrapper::after {
            content: '';
            background-image: url('../assets/verified_bg.png');
            background-repeat: no-repeat;
            background-position: center;
            background-size: 300px;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 100%;
            height: 100%;
            opacity: 0.10; 
            z-index: 0;
        }
        .invoice-content {
            position: relative;
            z-index: 1;
        }
        .invoice-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 40px;
        }
        .invoice-logo-section svg { height: 40px; }
        .invoice-logo-section h2 {
            font-size: 24px;
            color: #111827;
            margin: 10px 0 0;
            font-weight: 700;
        }
        .invoice-details { text-align: right; }
        .invoice-details h3 {
            font-size: 36px;
            margin: 0;
            color: #111827;
            font-weight: 700;
        }
        .invoice-details p { margin: 2px 0 0; color: #6b7280; font-size: 14px; }

        .invoice-billing-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 40px;
            background: #f9fafb;
            padding: 20px;
            border-radius: 8px;
            border: 1px solid var(--border-color);
        }
        .invoice-billing-info h4 {
            font-size: 12px;
            color: #6b7280;
            margin-bottom: 8px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .invoice-billing-info p { margin: 0; line-height: 1.6; font-weight: 500; }

        .invoice-table-wrapper { margin-bottom: 40px; }
        .invoice-table { width: 100%; border-collapse: collapse; }
        .invoice-table th, .invoice-table td { padding: 15px; text-align: left; }
        .invoice-table thead { background-color: #111827; color: white; }
        .invoice-table th { font-weight: 600; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; }
        .invoice-table tbody tr { border-bottom: 1px solid #e5e7eb; }
        .invoice-table tbody td { font-size: 14px; }
        .invoice-table .text-right { text-align: right; }
        
        .supply-chain-section { padding-top: 20px; }
        .supply-chain-section h4 {
            margin: 0 0 20px;
            font-size: 16px;
            text-transform: uppercase;
            color: #111827;
            display: flex;
            align-items: center;
            gap: 8px;
            letter-spacing: 1px;
        }
        .verification-steps {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .step { text-align: center; flex: 1; }
        .step-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: #dcfce7;
            color: var(--success-color);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin: 0 auto 10px;
            border: 4px solid #fff;
            box-shadow: 0 0 0 2px var(--success-color);
        }
        .step-name { font-weight: 600; font-size: 14px; }
        .step-actor { font-size: 12px; color: #6b7280; }
        .step-arrow {
            font-size: 24px;
            color: #d1d5db;
            flex: 0 1 50px;
        }

        .invoice-footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 2px solid #111827;
            text-align: center;
        }
        .invoice-footer .signature {
            font-family: 'Brush Script MT', cursive;
            font-size: 40px;
            color: #333;
            margin: 0;
        }
        .invoice-footer p { margin: 0; font-weight: 600; }
        .invoice-footer span { font-size: 0.8rem; color: var(--text-light); }
        
        @media print {
            body.printing * { visibility: hidden; }
            body.printing #invoice-modal, body.printing #invoice-modal * { visibility: visible; }
            body.printing #invoice-modal { position: absolute; left: 0; top: 0; width: 100%; height: 100%; padding: 0; margin: 0; background: none; backdrop-filter: none; display: block !important; }
            body.printing .modal-content { box-shadow: none; border: none; max-width: 100%; max-height: 100%; border-radius: 0; padding: 0; margin: 0; }
            body.printing .modal-actions { display: none; }
            .invoice-wrapper { -webkit-print-color-adjust: exact; print-color-adjust: exact; color-adjust: exact; }
        }
    </style>
</head>
<body>
    <div class="macos-browser-window">
        <div class="macos-title-bar">
            <div class="macos-buttons">
                <div class="dot dot-red"></div>
                <div class="dot dot-yellow"></div>
                <div class="dot dot-green"></div>
            </div>
        </div>
        <div class="dashboard-body">
            <aside class="sidebar">
                <a href="index.php" class="logo">
                     <img src="../assets/medchain_logo.svg" alt="MedChain Logo">
                    <h1>MedVeda</h1>
                </a>
                <nav class="sidebar-nav">
                    <a href="index.php" class="active"><i class="fas fa-file-medical"></i>Medical Records</a>
                    <a href="inventory.php"><i class="fas fa-boxes-stacked"></i>Check Stock</a>
                    <a href="doctor.html"><i class="fas fa-user-doctor"></i>AI Doctor</a>
            
                <a href="?logout=true" class="sidebar-nav logout-link"><i class="fas fa-sign-out-alt"></i>Logout</a>
                </nav>
            </aside>

            <main class="main-content">
                <?php if (!$productData['success']): ?>
                    <div class="error-message">
                        <i class="fas fa-exclamation-circle" style="font-size: 2rem; margin-bottom: 1rem;"></i>
                        <h2>Product Not Found</h2>
                        <p><?php echo htmlspecialchars($productData['message']); ?></p>
                        <a href="index.php" class="btn btn-primary" style="margin-top: 1rem;">
                            <i class="fas fa-arrow-left"></i> Back to Dashboard
                        </a>
                    </div>
                <?php else: 
                    $product = $productData['product'];
                    $history = $product['history'] ?? [];
                    
                    $isVerified = true;
                    $last_valid_hash = str_repeat('0', 64);
                    foreach ($history as $log) {
                        if ($log['previous_hash'] !== $last_valid_hash) {
                            $isVerified = false;
                            break;
                        }
                        $recalculated_hash = hash('sha256', $log['product_id'] . $log['action'] . $log['actor_id'] . $log['details'] . $log['previous_hash']);
                        if ($recalculated_hash !== $log['current_hash']) {
                            $isVerified = false;
                            break;
                        }
                        $last_valid_hash = $log['current_hash'];
                    }
                ?>
                    <div class="main-header">
                        <div class="title-section">
                            <a href="index.php"><i class="fas fa-arrow-left"></i> Back to Medical Records</a>
                            <h2>Product Provenance</h2>
                        </div>
                        <div class="profile-info">
                            <span class="username">Welcome, <?php echo htmlspecialchars($patient_name); ?></span>
                            <img src="https://i.pravatar.cc/150?u=<?php echo htmlspecialchars($patient_id); ?>" alt="User Profile Photo">
                        </div>
                    </div>

                    <div class="card">
                        <div class="verification-banner <?php echo $isVerified ? 'verified' : 'error'; ?>">
                            <i class="fas <?php echo $isVerified ? 'fa-check-circle' : 'fa-times-circle'; ?>"></i>
                            <strong>Verification Status: <?php echo $isVerified ? 'Chain Integrity Verified' : 'Chain Integrity Compromised'; ?></strong>
                        </div>
                        <h3><?php echo htmlspecialchars($product['brand_name']); ?> <span style="color: var(--text-light); font-weight: 500;">(<?php echo htmlspecialchars($product['generic_name']); ?>)</span></h3>
                        <div class="details-grid" style="margin-top: 1.5rem;">
                            <div class="detail-item"><div class="label">Batch Number</div><div class="value"><?php echo htmlspecialchars($product['batch_number']); ?></div></div>
                            <div class="detail-item"><div class="label">Expiry Date</div><div class="value"><?php echo date("F j, Y", strtotime($product['expiry_date'])); ?></div></div>
                            <div class="detail-item"><div class="label">Manufacturer</div><div class="value"><?php echo htmlspecialchars($product['manufacturer_name']); ?></div></div>
                            <div class="detail-item"><div class="label">Unique ID</div><div class="value"><span class="unique-id"><?php echo htmlspecialchars($product['unique_identifier']); ?></span></div></div>
                        </div>
                    </div>
                    <div class="card">
                        <h3>Provenance History</h3>
                        <div class="provenance-timeline">
                            <?php foreach ($history as $item): ?>
                                <div class="timeline-item">
                                    <div class="time"><?php echo date("F j, Y, g:i a", strtotime($item['log_timestamp'])); ?></div>
                                    <div class="action"><?php echo ucwords(str_replace('_', ' ', strtolower($item['action']))); ?></div>
                                    <div class="actor">By: <?php echo htmlspecialchars($item['actor_name'] ?? 'System'); ?></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <?php if ($isVerified): ?>
                        <div id="view-button-container">
                            <button onclick="openInvoiceModal()" class="view-cert-btn">
                                <i class="fas fa-receipt"></i> View Authenticity Certificate
                            </button>
                        </div>
                    <?php endif; ?>

                <?php endif; ?>
            </main>
        </div>
    </div>

    <div id="invoice-modal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-actions">
                 <button class="modal-btn" onclick="printCertificate()" title="Print Certificate">
                    <i class="fas fa-print"></i>
                </button>
                <button class="modal-btn" onclick="closeInvoiceModal()" title="Close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div id="invoice-content-area">
                <!-- Invoice content will be dynamically generated by JavaScript -->
            </div>
        </div>
    </div>

    <script>
        // --- Lenis Smooth Scroll Initialization ---
        const lenis = new Lenis({
            wrapper: document.querySelector('.main-content'),
        });
        function raf(time) {
            lenis.raf(time);
            requestAnimationFrame(raf);
        }
        requestAnimationFrame(raf);

        <?php if ($productData['success']): ?>
        
        function prepareInvoice() {
            const data = <?php echo json_encode($productData); ?>;
            const product = data.product;
            const history = data.product.history;
            const patientName = '<?php echo addslashes($patient_name); ?>';
            const invoiceContentArea = document.getElementById('invoice-content-area');
            
            const manufacturerEvent = history.find(h => h.action === 'PRODUCT_REGISTERED');
            const distributorEvent = history.find(h => h.action === 'PRODUCT_SOLD_TO_PHARMACIST');
            const pharmacistEvent = history.find(h => {
                if (h.action !== 'PRODUCT_DISPENSED_TO_PATIENT') return false;
                try {
                    const details = JSON.parse(h.details);
                    return details.patient_id == '<?php echo $patient_id; ?>';
                } catch(e) { return false; }
            });

            const manufacturerName = manufacturerEvent ? manufacturerEvent.actor_name : 'N/A';
            const distributorName = distributorEvent ? distributorEvent.actor_name : 'N/A';
            const pharmacistName = pharmacistEvent ? pharmacistEvent.actor_name : 'N/A';

            const dispensedDetails = pharmacistEvent ? JSON.parse(pharmacistEvent.details) : {};
            const dispensedQuantity = dispensedDetails.quantity || 'N/A';
            const dispensedDate = pharmacistEvent ? new Date(pharmacistEvent.log_timestamp).toLocaleDateString('en-GB') : new Date().toLocaleDateString('en-GB');

            const invoiceHtml = `
                <div class="invoice-wrapper">
                    <div class="invoice-content">
                        <header class="invoice-header">
                            <div class="invoice-logo-section">
                                <svg width="50" height="50" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M12 2L2 7l10 5 10-5-10-5z" fill="#6c63ff"/>
                                    <path d="M2 17l10 5 10-5" stroke="#6c63ff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M2 12l10 5 10-5" stroke="#6c63ff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                <h2>Medaveda</h2>
                            </div>
                            <div class="invoice-details">
                                <h3>CERTIFICATE</h3>
                                <p><strong>Certificate ID:</strong> ${product.unique_identifier}</p>
                                <p><strong>Issued On:</strong> ${new Date().toLocaleDateString('en-GB')}</p>
                            </div>
                        </header>

                        <section class="invoice-billing-info">
                            <div>
                                <h4>PATIENT INFORMATION</h4>
                                <p><strong>${patientName}</strong></p>
                            </div>
                            <div>
                                <h4>DISPENSING PHARMACY</h4>
                                <p><strong>${pharmacistName}</strong></p>
                                <p>Dispensed on ${dispensedDate}</p>
                            </div>
                        </section>

                        <div class="invoice-table-wrapper">
                            <h4 style="text-transform: uppercase; color: #6b7280; font-size: 12px; letter-spacing: 0.5px;">Authenticated Product Details</h4>
                            <table class="invoice-table">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Batch No.</th>
                                        <th>Mfg. & Exp. Dates</th>
                                        <th class="text-right">Quantity</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>
                                            <strong>${product.brand_name}</strong><br>
                                            <small>${product.generic_name} ${product.strength || ''}</small>
                                        </td>
                                        <td>${product.batch_number}</td>
                                        <td>
                                            Mfg: ${new Date(product.manufacturing_date).toLocaleDateString('en-GB')}<br>
                                            Exp: ${new Date(product.expiry_date).toLocaleDateString('en-GB')}
                                        </td>
                                        <td class="text-right">${dispensedQuantity}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="supply-chain-section">
                            <h4><i class="fas fa-shield-alt"></i> Supply Chain Verification</h4>
                            <div class="verification-steps">
                                <div class="step">
                                    <div class="step-icon"><i class="fas fa-industry"></i></div>
                                    <div class="step-name">Manufacturer</div>
                                    <div class="step-actor">Verified by ${manufacturerName}</div>
                                </div>
                                <div class="step-arrow"><i class="fas fa-long-arrow-alt-right"></i></div>
                                <div class="step">
                                    <div class="step-icon"><i class="fas fa-truck"></i></div>
                                    <div class="step-name">Distributor</div>
                                    <div class="step-actor">Verified by ${distributorName}</div>
                                </div>
                                <div class="step-arrow"><i class="fas fa-long-arrow-alt-right"></i></div>
                                <div class="step">
                                    <div class="step-icon"><i class="fas fa-clinic-medical"></i></div>
                                    <div class="step-name">Pharmacist</div>
                                    <div class="step-actor">Verified by ${pharmacistName}</div>
                                </div>
                            </div>
                        </div>

                        <footer class="invoice-footer">
                            <div class="signature-section">
                                <p class="signature" style="font-family: 'Brush Script MT', cursive;">Mr. Shrikant</p>
                                <p>CEO, Medaveda</p>
                                <span>This certificate confirms the authenticity and complete supply chain history of the specified product.</span>
                            </div>
                        </footer>
                    </div>
                </div>
            `;
            invoiceContentArea.innerHTML = invoiceHtml;
        }

        function openInvoiceModal() {
            prepareInvoice();
            document.getElementById('invoice-modal').style.display = 'flex';
        }

        function closeInvoiceModal() {
            document.getElementById('invoice-modal').style.display = 'none';
        }

        function printCertificate() {
            const body = document.body;
            body.classList.add('printing');
            window.print();
            body.classList.remove('printing');
        }

        function downloadCertificate() {
            const certificate = document.getElementById('invoice-content-area');
            const opt = {
                margin:       0,
                filename:     `Medaveda_Certificate_<?php echo addslashes($unique_identifier); ?>.pdf`,
                image:        { type: 'jpeg', quality: 1.0 },
                html2canvas:  { scale: 2, useCORS: true },
                jsPDF:        { unit: 'in', format: 'a4', orientation: 'portrait' }
            };
            
            const downloadBtn = document.querySelector('.modal-btn[onclick="downloadCertificate()"]');
            const originalContent = downloadBtn.innerHTML;
            downloadBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            downloadBtn.disabled = true;
            
            html2pdf().set(opt).from(certificate).save().then(() => {
                downloadBtn.innerHTML = originalContent;
                downloadBtn.disabled = false;
            }).catch((error) => {
                console.error('PDF generation error:', error);
                alert('Error generating PDF. Please try again.');
                downloadBtn.innerHTML = originalContent;
                downloadBtn.disabled = false;
            });
        }
        <?php endif; ?>
    </script>
</body>
</html>
