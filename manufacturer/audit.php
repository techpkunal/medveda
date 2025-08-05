<?php
require '../api/db_connect.php';

// Helper functions to format the details for display
function format_action_title($action) {
    return ucwords(str_replace('_', ' ', strtolower($action)));
}

function format_action_details($block) {
    // Safely decode the JSON details
    $details = json_decode($block['details'], true);
    if (is_null($details)) {
        return 'Details not available or invalid format.';
    }

    $actor = htmlspecialchars($block['actor_name']) ?: "System (ID: " . htmlspecialchars($block['actor_id']) . ")";

    switch ($block['action']) {
        case 'PRODUCT_REGISTERED':
            $brand_name = htmlspecialchars($details['brand_name'] ?? 'N/A');
            $quantity = htmlspecialchars($details['quantity'] ?? 'N/A');
            return "Registered '{$brand_name}'. Initial quantity: {$quantity}.";
        
        case 'PRODUCT_DISPENSED_TO_DISTRIBUTOR':
            $quantity = htmlspecialchars($details['dispensed_quantity'] ?? 'N/A');
            return "Dispensed {$quantity} units to a distributor.";

        case 'PRODUCT_PICKED_UP_BY_DISTRIBUTOR':
            return "Product consignment was picked up by the distributor: {$actor}.";

        case 'PRODUCT_SOLD_TO_PHARMACIST':
            $pharmacist_id = htmlspecialchars($details['sold_to_pharmacist_id'] ?? 'N/A');
            $quantity = htmlspecialchars($details['quantity'] ?? 'N/A');
            return "Sold {$quantity} units to Pharmacist (ID: {$pharmacist_id}).";

        case 'PRODUCT_DISPENSED_TO_PATIENT':
            $patient_name = htmlspecialchars($details['patient_name'] ?? 'N/A');
            $quantity = htmlspecialchars($details['quantity'] ?? 'N/A');
            return "Dispensed {$quantity} units to patient '{$patient_name}'.";

        default:
            // Fallback for any other actions, showing the raw details safely
            return 'Details: ' . htmlspecialchars($block['details']);
    }
}


try {
    // MODIFIED QUERY: Join with the users table to get the actor's name and role
    $stmt = $pdo->query("
        SELECT 
            a.log_id, 
            a.product_id, 
            a.action, 
            a.actor_id, 
            a.log_timestamp, 
            a.current_hash, 
            a.previous_hash,
            a.details,
            u.full_name as actor_name,
            u.role as actor_role
        FROM audit_trail a
        LEFT JOIN users u ON a.actor_id = u.user_id
        ORDER BY a.log_id DESC
    ");
    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    die("Error fetching audit trail: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Audit Inspector - MedChain</title>
    
    <script src="https://unpkg.com/lenis@1.1.5/dist/lenis.min.js"></script>

    <style>
        /* --- macOS Window Theme --- */
        :root {
            --primary-color: #007AFF; /* macOS Accent Blue */
            --primary-hover: #0056b3;
            --primary-glow: rgba(0, 122, 255, 0.2);
            --bg-color: #e9e9e9; /* Light gray background */
            --window-bg: #FFFFFF;
            --sidebar-bg: rgba(242, 242, 247, 0.95);
            --text-primary: #1D1D1F;
            --text-secondary: #6E6E73;
            --border-color: rgba(60, 60, 67, 0.29);
            --danger-color: #FF3B30;
            --success-color: #34C759;
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

        /* --- Main Dashboard Layout (Sidebar + Content) --- */
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
        }
        .sidebar-header {
            padding: 1.5rem;
            display: flex;
            align-items: center;
            gap: 12px;
            border-bottom: 2px solid var(--border-color);
            flex-shrink: 0;
        }
        .sidebar-header .logo-icon { width: 40px; height: 40px; }
        .sidebar-header h1 { font-size: 1.5rem; margin: 0; font-weight: 800; }
        .sidebar-nav { list-style: none; padding: 1.5rem 0; margin: 0; flex-grow: 1; overflow-y: auto; }
        .sidebar-nav ul { list-style: none; padding: 0; margin: 0; }
        .sidebar-nav a {
            display: flex; align-items: center; gap: 15px;
            padding: 0.8rem 1.5rem; margin: 0.25rem 1rem;
            color: var(--text-secondary); text-decoration: none;
            font-weight: 600; border-radius: 8px; transition: all 0.2s ease;
        }
        .sidebar-nav a:hover { background-color: rgba(128, 128, 128, 0.1); color: var(--primary-color); }
        .sidebar-nav a.active {
            background: var(--primary-color); color: #FFFFFF; font-weight: 700;
            box-shadow: 0 4px 12px var(--primary-glow);
        }
        .sidebar-nav a .icon { width: 22px; height: 22px; }
        
        .main-content {
            flex-grow: 1;
            padding: 2.5rem;
            box-sizing: border-box;
            height: 100%;
            overflow-y: auto;
        }

        .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; }
        .page-header h2 { font-size: 2.25rem; font-weight: 800; margin: 0; color: var(--text-primary); }
        
        .btn { padding: 10px 20px; border-radius: 8px; font-weight: 700; border: none; cursor: pointer; transition: all 0.2s ease; display: inline-flex; align-items: center; gap: 8px; }
        .btn-primary { background-color: var(--primary-color); color: white; }
        .btn-primary:hover { background-color: var(--primary-hover); }

        .card {
            background-color: var(--window-bg); 
            border-radius: 12px; 
            border: 2px solid var(--border-color);
            box-shadow: 0 2px 8px rgba(0,0,0,0.05); 
            overflow: hidden;
        }
        
        /* Table Styling */
        .audit-table { width: 100%; border-collapse: collapse; }
        .audit-table th, .audit-table td { padding: 12px 15px; text-align: left; border-bottom: 2px solid var(--border-color); font-size: 0.9rem; }
        .audit-table th { font-weight: 700; background-color: #f8f9fa; text-transform: uppercase; letter-spacing: 0.5px; }
        .audit-table tr:last-child td { border-bottom: none; }
        .hash { font-family: 'Roboto Mono', monospace; font-size: 0.85rem; word-break: break-all; }
        .status { font-weight: 700; padding: 5px 10px; border-radius: 20px; text-align: center; display: inline-block; font-size: 0.8rem; }
        .status-verified { color: var(--success-color); background-color: #e8f5e9; }
        .status-tampered { color: var(--danger-color); background-color: #fbe9e7; }
        .status-pending { color: var(--text-secondary); background-color: #eceff1; }
        .actor-info { font-weight: 600; }
        .actor-info .role { font-size: 0.8em; color: var(--text-secondary); text-transform: capitalize; font-weight: 600; }
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
                <div class="sidebar-header">
                    <img src="../assets/medchain_logo.svg" alt="MedChain Logo" class="logo-icon">
                    <h1>MedChain</h1>
                </div>
                <nav class="sidebar-nav">
                    <ul>
                        <li><a href="index.html"><svg class="icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg><span>Dashboard</span></a></li>
                        <li><a href="register.php"><svg class="icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg><span>Add Product</span></a></li>
                        <li><a href="history.php"><svg class="icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 4v6h6"/><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"/></svg><span>Full History</span></a></li> 
                        <li><a href="audit.php" class="active"><svg class="icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg><span>Audit Inspector</span></a></li>
                    </ul>
                </nav>
            </aside>

            <main class="main-content">
                <header class="page-header">
                    <h2>Blockchain Audit Inspector</h2>
                    <button class="btn btn-primary" id="verifyAllBtn">Verify Entire Chain</button>
                </header>

                <div class="card">
                    <div style="overflow-x:auto;">
                        <table class="audit-table">
                            <thead>
                                <tr>
                                    <th>Block ID</th>
                                    <th>Timestamp</th>
                                    <th>Action</th>
                                    <th>Actor</th>
                                    <th>Details</th>
                                    <th>Current Hash</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($logs as $log): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($log['log_id']); ?></td>
                                        <td><?php echo date("d M Y, H:i:s", strtotime($log['log_timestamp'])); ?></td>
                                        <td><?php echo format_action_title($log['action']); ?></td>
                                        <td class="actor-info">
                                            <?php echo htmlspecialchars($log['actor_name'] ?: 'System'); ?><br>
                                            <span class="role">(<?php echo htmlspecialchars($log['actor_role'] ?: 'N/A'); ?>)</span>
                                        </td>
                                        <td><?php echo format_action_details($log); ?></td>
                                        <td class="hash"><?php echo htmlspecialchars($log['current_hash']); ?></td>
                                        <td>
                                            <div class="status status-pending" id="status-<?php echo $log['log_id']; ?>">Pending</div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </main>
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

        // --- Chain Verification JavaScript ---
        document.getElementById('verifyAllBtn').addEventListener('click', verifyEntireChain);

        async function verifyEntireChain() {
            const btn = document.getElementById('verifyAllBtn');
            btn.disabled = true;
            btn.innerHTML = 'Verifying...';

            try {
                const response = await fetch('../api/verify_chain.php');
                const results = await response.json();

                if(results.success) {
                    results.chain.forEach(block => {
                        updateStatus(block.log_id, block.status, block.message);
                    });
                } else {
                    alert('Error during verification: ' + (results.message || 'Unknown error'));
                }
            } catch (error) {
                console.error('Verification API call failed:', error);
                alert('Could not connect to the verification service.');
            }

            btn.disabled = false;
            btn.innerHTML = 'Verify Entire Chain';
        }

        function updateStatus(logId, status, message = '') {
            const statusEl = document.getElementById(`status-${logId}`);
            if (!statusEl) return;
            
            statusEl.className = 'status'; // Reset classes
            
            switch(status) {
                case 'VERIFIED':
                    statusEl.classList.add('status-verified');
                    statusEl.textContent = 'Verified';
                    break;
                case 'TAMPERED':
                case 'BROKEN_LINK':
                    statusEl.classList.add('status-tampered');
                    statusEl.textContent = status === 'TAMPERED' ? 'Tampered' : 'Broken Link';
                    break;
                default:
                    statusEl.classList.add('status-pending');
                    statusEl.textContent = 'Pending';
            }
            if(message) statusEl.title = message;
        }
    </script>
</body>
</html>
