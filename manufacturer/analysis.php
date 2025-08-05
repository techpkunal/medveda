<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Analysis - MedChain</title>
    
    <style>
        /* --- macOS Window Theme --- */
        :root {
            --primary-color: #007AFF; /* macOS Accent Blue */
            --primary-hover: #0056b3;
            --primary-glow: rgba(0, 122, 255, 0.2);
            --bg-color: #e9e9e9; /* Light gray background */
            --window-bg: #FFFFFF;
            --sidebar-bg: rgba(242, 242, 247, 0.95); /* Translucent sidebar */
            --text-primary: #1D1D1F;
            --text-secondary: #6E6E73;
            --border-color: rgba(60, 60, 67, 0.29);
            --shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            --danger-color: #FF3B30;
            --warning-bg: #FFFBEA;
            --warning-text: #D97706;
            --success-bg: #F0FDF4;
            --success-text: #16A34A;
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
            scroll-behavior: smooth;
        }

        /* --- Card and Component Styling --- */
        .card { 
            background-color: var(--window-bg); 
            padding: 1.5rem; 
            border-radius: 12px; 
            border: 2px solid var(--border-color);
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            margin-bottom: 1.5rem;
        }
        .card-header { display: flex; align-items: center; gap: 12px; margin-bottom: 1rem; padding-bottom: 1rem; border-bottom: 2px solid var(--border-color); }
        .card-header .icon { width: 24px; height: 24px; color: var(--primary-color); }
        .card-header h3 { margin: 0; font-size: 1.2rem; font-weight: 700; }
        .form-group { margin-bottom: 1rem; }
        .form-group label { display: block; margin-bottom: .5rem; font-weight: 700; }
        .form-group input { width: 100%; padding: .75rem; border: 2px solid var(--border-color); border-radius: 8px; box-sizing: border-box; background-color: #fefefe; font-family: inherit; }
        .btn-primary { background-color: var(--primary-color); color: white; padding: 0.75rem 1.5rem; border-radius: 8px; font-weight: 600; border: none; cursor: pointer; transition: all 0.2s ease; }
        .btn-primary:hover { background-color: var(--primary-hover); }
        .notification { display: none; padding: 1rem; margin-bottom: 1rem; border-radius: 8px; font-weight: 500; border: 1px solid transparent; }
        .notification.success { background-color: var(--success-bg); color: var(--success-text); border-color: var(--success-text); }
        .notification.error { background-color: #f8d7da; color: #721c24; border-color: #f5c6cb; }
        .metric-card .label { font-size: 1rem; color: var(--text-secondary); margin: 0; font-weight: 600; }
        .metric-card .value { font-size: 2.5rem; font-weight: 800; color: var(--text-primary); margin: 0.5rem 0; }
        .dashboard-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; }
        .activity-table { width: 100%; border-collapse: collapse; }
        .activity-table th, .activity-table td { padding: 12px 10px; text-align: left; border-bottom: 2px solid var(--border-color); font-size: 0.9rem; vertical-align: middle; }
        .activity-table th { font-weight: 700; color: var(--text-secondary); background-color: #F9FAFB; text-transform: uppercase; letter-spacing: 0.5px;}
        .activity-table tr:last-child td { border-bottom: none; }

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
        <li><a href="audit.php"><svg class="icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg><span>Audit Inspector</span></a></li>
        <li><a href="analysis.php" class="active"><svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg><span>Analysis</span></a></li>
        <li><a href="http://localhost/block/"><svg class="icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 22H5a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h7l5 5v2"/><path d="M15 18H9"/><path d="M15 22H9"/><path d="M12 14v8"/></svg><span>Home Page</span></a></li>
    </ul>
</nav>

            </aside>

            <main class="main-content">
                <header>
                    <h2 style="font-size: 2.25rem; font-weight: 800; margin: 0 0 2rem 0; color: var(--text-primary);">Product Lifecycle Analysis</h2>
                </header>

                <div class="card">
                    <div class="card-header">
                        <svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>
                        <h3>Trace Product Lifecycle</h3>
                    </div>
                    <div style="padding: 1rem;">
                        <!-- Search Form -->
                        <form id="analysis-form" style="display: flex; gap: 1rem; max-width: 600px; margin-bottom: 2rem; align-items: flex-end;">
                            <div class="form-group" style="flex-grow: 1; margin-bottom: 0;">
                                <label for="unique-identifier">Enter Product Unique Identifier:</label>
                                <input type="text" id="unique-identifier" placeholder="e.g., med_687cf09b8e2802.81804718" required>
                            </div>
                            <button type="submit" class="btn-primary">Trace</button>
                        </form>
                        <!-- Notification Area -->
                        <div id="analysis-notification" class="notification" style="display: none;"></div>
                        
                        <!-- Results Area -->
                        <div id="analysis-results" style="display: none; margin-top: 2rem;">
                            <!-- Product Info -->
                            <div id="analysis-product-info" style="margin-bottom: 1.5rem; padding-bottom: 1rem; border-bottom: 2px solid var(--border-color);">
                                <!-- Populated by JS -->
                            </div>

                            <!-- Summary Metrics -->
                            <h4 style="font-size: 1.2rem; font-weight: 700; margin-bottom: 1rem;">Supply Chain Summary</h4>
                            <div id="analysis-summary-grid" class="dashboard-grid">
                                <!-- Populated by JS -->
                            </div>

                            <!-- Timeline -->
                            <h4 style="font-size: 1.2rem; font-weight: 700; margin-top: 2rem; margin-bottom: 1rem;">Detailed History</h4>
                            <div style="overflow-x:auto;">
                                <table class="activity-table">
                                    <thead>
                                        <tr>
                                            <th>Timestamp</th>
                                            <th>Actor</th>
                                            <th>Action</th>
                                            <th>Details</th>
                                        </tr>
                                    </thead>
                                    <tbody id="analysis-timeline-body">
                                        <!-- Populated by JS -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

            </main>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('analysis-form').addEventListener('submit', handleAnalysisSearch);
        });

        async function handleAnalysisSearch(event) {
            event.preventDefault();
            const identifier = document.getElementById('unique-identifier').value;
            const resultsContainer = document.getElementById('analysis-results');
            const notification = document.getElementById('analysis-notification');
            const submitButton = event.target.querySelector('button[type="submit"]');

            if (!identifier) {
                showNotification('analysis-notification', 'Please enter a Unique Identifier.', 'error');
                return;
            }

            resultsContainer.style.display = 'none';
            notification.style.display = 'none';
            submitButton.disabled = true;
            submitButton.textContent = 'Tracing...';

            try {
                const response = await fetch('../api/trace_supply_chain.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ unique_identifier: identifier })
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    displayAnalysisResults(data);
                    resultsContainer.style.display = 'block';
                } else {
                    showNotification('analysis-notification', `Error: ${data.message || 'Could not fetch data.'}`, 'error');
                }

            } catch (error) {
                console.error('Trace Submit Error:', error);
                showNotification('analysis-notification', 'A critical network error occurred.', 'error');
            } finally {
                submitButton.disabled = false;
                submitButton.textContent = 'Trace';
            }
        }

        function displayAnalysisResults(data) {
            const { product_info, supply_summary, timeline } = data;

            // Display Product Info
            document.getElementById('analysis-product-info').innerHTML = `
                <h3 style="margin: 0; font-size: 1.5rem; color: var(--text-primary);">${product_info.brand_name}</h3>
                <p style="margin: 0.25rem 0; color: var(--text-secondary); font-family: 'Roboto Mono', monospace;">Batch: ${product_info.batch_number}</p>
            `;

            // Display Summary Metrics
            const summaryContainer = document.getElementById('analysis-summary-grid');
            summaryContainer.innerHTML = `
                <div class="card metric-card" style="margin-bottom:0;"><p class="label">Total Produced</p><p class="value">${supply_summary.produced}</p></div>
                <div class="card metric-card" style="margin-bottom:0;"><p class="label">At Manufacturer</p><p class="value">${supply_summary.at_manufacturer}</p></div>
                <div class="card metric-card" style="margin-bottom:0;"><p class="label">With Distributor</p><p class="value">${supply_summary.with_distributor}</p></div>
                <div class="card metric-card" style="margin-bottom:0;"><p class="label">With Pharmacist</p><p class="value">${supply_summary.with_pharmacist}</p></div>
                <div class="card metric-card" style="margin-bottom:0;"><p class="label">Consumed by Patient</p><p class="value">${supply_summary.consumed}</p></div>
            `;

            // Display Detailed Timeline
            const timelineBody = document.getElementById('analysis-timeline-body');
            timelineBody.innerHTML = '';
            if (timeline && timeline.length > 0) {
                timeline.forEach(item => {
                    timelineBody.innerHTML += `
                        <tr>
                            <td>${item.timestamp}</td>
                            <td>${item.actor}</td>
                            <td>${item.action}</td>
                            <td>${item.details}</td>
                        </tr>
                    `;
                });
            } else {
                timelineBody.innerHTML = '<tr><td colspan="4" style="text-align:center; padding: 2rem;">No history found for this product.</td></tr>';
            }
        }

        function showNotification(elementId, message, type) {
            const el = document.getElementById(elementId);
            el.style.display = 'block';
            el.textContent = message;
            el.className = `notification ${type}`;
        }
    </script>

</body>
</html>
