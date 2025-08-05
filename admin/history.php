<?php
// Set the manufacturer ID. In a real app, this would come from a login session.
$manufacturer_id = 1; 

// Include the database connection script
require '../api/db_connect.php';

try {
    // **UPDATED QUERY**: Select the new, more detailed columns for the history view.
    $stmt = $pdo->prepare(
        "SELECT 
            brand_name, 
            generic_name, 
            batch_number, 
            manufacturing_date, 
            expiry_date, 
            stock_quantity, 
            unique_identifier,
            therapeutic_category
        FROM products 
        WHERE manufacturer_id = ? 
        ORDER BY registration_timestamp DESC"
    );
    $stmt->execute([$manufacturer_id]);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    // If there's a database error, stop the script and show the error.
    die("Error fetching product history: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Full History - MedVeda</title>
    
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

        .page-header { margin-bottom: 2rem; }
        .page-header h2 { font-size: 2.25rem; font-weight: 800; margin: 0; color: var(--text-primary); }
        
        .card {
            background-color: var(--window-bg); 
            border-radius: 12px; 
            border: 2px solid var(--border-color);
            box-shadow: 0 2px 8px rgba(0,0,0,0.05); 
            overflow: hidden;
        }
        
        /* Table Styling */
        .history-table {
            width: 100%; 
            border-collapse: collapse;
        }
        .history-table th, .history-table td {
            padding: 14px 12px; 
            text-align: left; 
            border-bottom: 2px solid var(--border-color);
            font-size: 0.95rem; 
            white-space: nowrap;
            vertical-align: middle;
        }
        .history-table thead th {
            font-weight: 700; 
            color: var(--text-secondary); 
            background-color: #F8F9FA;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .history-table tbody tr:hover { 
            background-color: #F8F9FA; 
        }
        .history-table tr:last-child td {
            border-bottom: none;
        }
        
        .identifier-container {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
        }
        .unique-id {
            font-family: 'Roboto Mono', monospace;
            font-size: 0.9rem;
            background-color: #E2E8F0; 
            color: var(--text-secondary);
            padding: 4px 8px; 
            border-radius: 6px; 
            font-weight: 600;
        }
        .copy-btn {
            background: transparent;
            border: none;
            padding: 4px;
            border-radius: 6px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: background-color 0.2s ease;
            flex-shrink: 0;
        }
        .copy-btn:hover { background-color: #e9e9e9; }
        .copy-btn svg { width: 16px; height: 16px; color: var(--text-secondary); }
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
                    <h1>MedVeda</h1>
                </div>
               <nav class="sidebar-nav">
    <ul>
        <li><a href="index.html"><svg class="icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg><span>Dashboard</span></a></li>
        <li><a href="register.php"><svg class="icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg><span>Add Product</span></a></li>
        <li><a href="live_tracking.php"><svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg><span>Logistic & Tracking</span></a></li>
        <li><a href="history.php" class="active"><svg class="icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 4v6h6"/><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"/></svg><span>Full History</span></a></li>
        <li><a href="audit.php"><svg class="icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg><span>Audit Inspector</span></a></li>
        <li><a href="analysis.php"><svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg><span>Trace & Analysis</span></a></li>
        <li><a href="http://localhost/block/"><svg class="icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 22H5a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h7l5 5v2"/><path d="M15 18H9"/><path d="M15 22H9"/><path d="M12 14v8"/></svg><span>Home Page</span></a></li>
    </ul>
</nav>


            </aside>

            <main class="main-content">
                <header class="page-header">
                    <h2>Full Product History</h2>
                </header>

                <div class="card">
                    <div style="overflow-x:auto;">
                        <table class="history-table">
                            <thead>
                                <tr>
                                    <th>Brand Name</th>
                                    <th>Generic Name</th>
                                    <th>Category</th>
                                    <th>Batch No.</th>
                                    <th>MFG Date</th>
                                    <th>Expiry Date</th>
                                    <th>Stock Qty</th>
                                    <th>Unique Identifier</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($products)): ?>
                                    <tr>
                                        <td colspan="8" style="text-align:center; padding: 2rem;">No products have been registered yet.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($products as $product): ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($product['brand_name']); ?></strong></td>
                                            <td><?php echo htmlspecialchars($product['generic_name']); ?></td>
                                            <td><?php echo htmlspecialchars($product['therapeutic_category']); ?></td>
                                            <td><?php echo htmlspecialchars($product['batch_number']); ?></td>
                                            <td><?php echo date("d M Y", strtotime($product['manufacturing_date'])); ?></td>
                                            <td><?php echo date("d M Y", strtotime($product['expiry_date'])); ?></td>
                                            <td><?php echo htmlspecialchars($product['stock_quantity']); ?></td>
                                            <td>
                                                <div class="identifier-container">
                                                    <span class="unique-id"><?php echo htmlspecialchars($product['unique_identifier']); ?></span>
                                                    <button class="copy-btn" data-clipboard-text="<?php echo htmlspecialchars($product['unique_identifier']); ?>" title="Copy ID">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
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

        // --- Copy to Clipboard ---
        document.addEventListener('click', function(e) {
            const copyButton = e.target.closest('.copy-btn');
            if (copyButton) {
                const textToCopy = copyButton.dataset.clipboardText;
                if (textToCopy) {
                    copyTextToClipboard(textToCopy, copyButton);
                }
            }
        });

        function copyTextToClipboard(text, buttonEl) {
            const textArea = document.createElement('textarea');
            textArea.value = text;
            
            // Make the textarea out of sight
            textArea.style.position = 'fixed';
            textArea.style.top = '-9999px';
            textArea.style.left = '-9999px';

            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();

            try {
                document.execCommand('copy');
                // Provide feedback
                const originalIcon = buttonEl.innerHTML;
                buttonEl.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="green" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>';
                buttonEl.title = 'Copied!';
                setTimeout(() => {
                    buttonEl.innerHTML = originalIcon;
                    buttonEl.title = 'Copy ID';
                }, 2000);
            } catch (err) {
                console.error('Oops, unable to copy', err);
                buttonEl.title = 'Failed to copy';
            }

            document.body.removeChild(textArea);
        }
    </script>

</body>
</html>
