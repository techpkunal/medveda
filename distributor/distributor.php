<?php
/**
 * Distributor Dashboard
 * This file now uses the centralized session manager for robust authentication.
 */

// Use the centralized session manager to handle authentication
require_once '../otp-login/session_manager.php';

// This function will handle session start, validation, and redirection if the user is not authenticated
// or does not have the 'distributor' role.
$user = requireAuth('distributor');

// Get distributor info from the session (which is now guaranteed to be valid)
$distributor_id = $_SESSION['user_id'];
$distributor_name = isset($_SESSION['full_name']) ? $_SESSION['full_name'] : 'Distributor';

// Handle logout request
if (isset($_GET['logout']) && $_GET['logout'] == 'true') {
    logout(); // Use the centralized logout function from session_manager.php
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Distributor Dashboard - MedVeda</title>
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
            --shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            --urgent-color: #FF3B30;
            --success-color: #34C759;
            --pending-color: #FF9500;
            --picked-up-color: #5AC8FA;
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
        }
        .sidebar-header {
            padding: 1.5rem;
            display: flex;
            align-items: center;
            gap: 12px;
            border-bottom: 2px solid var(--border-color);
            flex-shrink: 0;
        }
        .sidebar-header .logo-icon { width: 40px; height: 40px; color: var(--primary-color); }
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
        
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }
        .page-header h2 { font-size: 2.25rem; font-weight: 800; margin: 0; }
        .page-header .user-info { text-align: right; font-weight: 600; }

        /* --- Card & Table Styles --- */
        .card {
            background-color: var(--window-bg);
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            border: 2px solid var(--border-color);
            overflow: hidden;
            margin-bottom: 2rem;
        }
        .card-header {
            font-size: 1.25rem;
            padding: 1.25rem 1.5rem;
            margin: 0;
            border-bottom: 2px solid var(--border-color);
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 700;
        }
        .card-header .icon { width: 24px; height: 24px; color: var(--primary-color); }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        .stat-card {
            background-color: var(--window-bg);
            border-radius: 12px;
            padding: 1.5rem;
            display: flex;
            align-items: center;
            gap: 1.5rem;
            border: 2px solid var(--border-color);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .stat-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1); }
        .stat-icon { width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .stat-icon svg { width: 24px; height: 24px; }
        .stat-info h4 { margin: 0 0 0.25rem 0; font-size: 1rem; color: var(--text-secondary); font-weight: 600; }
        .stat-info p { margin: 0; font-size: 2rem; font-weight: 800; }

        .products-table { width: 100%; border-collapse: collapse; }
        .products-table th, .products-table td { padding: 1rem 1.5rem; text-align: left; border-bottom: 2px solid var(--border-color); vertical-align: middle; }
        .products-table th { font-weight: 700; color: var(--text-secondary); font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px; }
        .products-table tr:last-child td { border-bottom: none; }
        
        .status-badge { padding: 0.3rem 0.8rem; border-radius: 20px; font-weight: 700; font-size: 0.8rem; color: #fff; text-transform: capitalize; white-space: nowrap; }
        .status-pending { background-color: var(--pending-color); }
        .status-picked_up { background-color: var(--picked-up-color); }
        .status-delivered { background-color: var(--success-color); }

        .btn { padding: 0.6rem 1.2rem; border-radius: 8px; font-weight: 600; border: 2px solid transparent; cursor: pointer; transition: all 0.2s ease; display: inline-flex; align-items: center; gap: 8px; }
        .btn-pickup { background-color: var(--primary-color); color: white; border-color: var(--primary-color); }
        .btn-pickup:hover { background-color: var(--primary-hover); border-color: var(--primary-hover); }
        .btn-sell { background-color: var(--success-color); color: white; border-color: var(--success-color); }
        .btn-sell:hover { background-color: #28a745; border-color: #28a745; }
        .btn:disabled { background-color: #e0e0e0; color: #9e9e9e; border-color: #e0e0e0; cursor: not-allowed; }

        /* --- Notification & Modal Styles --- */
        .notification { display: none; padding: 1rem; margin-bottom: 1rem; border-radius: 8px; font-weight: 600; }
        .notification.success { background-color: #E8F5E9; color: #2E7D32; border: 2px solid #A5D6A7; }
        .notification.error { background-color: #FFEBEE; color: #C62828; border: 2px solid #EF9A9A; }
        .modal-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); backdrop-filter: blur(5px); z-index: 2000; align-items: center; justify-content: center; }
        .modal-content { background: white; padding: 2rem; border-radius: 12px; width: 90%; max-width: 500px; box-shadow: 0 5px 15px rgba(0,0,0,0.3); border: 2px solid var(--border-color); }
        .modal-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid var(--border-color); padding-bottom: 1rem; margin-bottom: 1rem; }
        .modal-header h3 { font-weight: 800; }
        .modal-close { background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #999; }
        .form-group label { display: block; margin-bottom: .5rem; font-weight: 700; }
        .form-group input, .form-group select { width: 100%; padding: .75rem; border: 2px solid var(--border-color); border-radius: 8px; box-sizing: border-box; background-color: #fefefe; }
        .form-group input:focus, .form-group select:focus { border-color: var(--primary-color); outline: none; box-shadow: 0 0 0 3px var(--primary-glow); }

        /* --- Messaging Widget Styles --- */
        .message-list { max-height: 500px; overflow-y: auto; padding: 1rem; }
        .message-item { display: flex; gap: 1rem; padding: 1rem; margin-bottom: 1rem; background: #fff; border-radius: 12px; border: 2px solid transparent; box-shadow: 0 2px 4px rgba(0,0,0,0.04); transition: all 0.3s ease; opacity: 1; }
        .message-item:hover { transform: translateY(-3px); box-shadow: var(--shadow); border-color: var(--primary-glow); }
        .message-item.disappearing { opacity: 0; transform: scale(0.95); }
        .message-avatar { flex-shrink: 0; width: 40px; height: 40px; border-radius: 50%; background-color: var(--primary-color); color: white; display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 1rem; }
        .message-avatar.urgent { background-color: var(--urgent-color); }
        .message-content { flex-grow: 1; cursor: pointer; }
        .message-header { display: flex; justify-content: space-between; align-items: baseline; }
        .message-sender { font-weight: 700; color: var(--text-primary); }
        .message-time { font-size: 0.8rem; color: var(--text-secondary); }
        .message-subject { margin: 0.25rem 0; font-weight: 600; }
        .message-body { font-size: 0.9rem; color: var(--text-secondary); font-weight: 500; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 90%; transition: max-height 0.4s ease-in-out; max-height: 20px; }
        .message-body.expanded { white-space: normal; overflow: visible; max-height: 200px; }
        .message-actions { display: flex; align-items: center; }
        .mark-read-btn { background: #f0f0f0; border: none; color: var(--text-secondary); padding: 0.4rem 0.8rem; border-radius: 20px; cursor: pointer; font-weight: 600; font-size: 0.75rem; transition: all 0.2s ease; }
        .mark-read-btn:hover { background-color: #e0e0e0; color: var(--text-primary); }
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
                    <svg class="logo-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2L2 7l10 5 10-5-10-5z"></path><path d="M2 17l10 5 10-5"></path><path d="M2 12l10 5 10-5"></path></svg>
                    <h1>MedVeda</h1>
                </div>
                <nav class="sidebar-nav">
                    <ul>
                        <li><a href="#" class="active"><svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg><span>Dashboard</span></a></li>
                        <li><a href="#"><svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg><span>Inventory</span></a></li>
                        <li><a href="#"><svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg><span>Messages</span></a></li>
                        <li><a href="#"><svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg><span>Account</span></a></li>
                    </ul>
                </nav>
            </aside>

            <main class="main-content">
                <header class="page-header">
                    <h2>Distributor Overview</h2>
                    <div class="user-info" style="display: flex; align-items: center; gap: 15px;">
                        <div>
                            <strong>Welcome, <?php echo htmlspecialchars($distributor_name); ?></strong><br>
                            <span style="font-size: 0.85rem; color: var(--text-secondary);">Distributor Dashboard</span>
                        </div>
                        <a href="?logout=true" class="logout-btn" style="margin-left: 15px; padding: 8px 15px; background-color: #f44336; color: white; border-radius: 8px; text-decoration: none; font-size: 0.9rem; font-weight: 600; transition: background-color 0.3s;" onmouseover="this.style.backgroundColor='#d32f2f'" onmouseout="this.style.backgroundColor='#f44336'">
                            <svg style="width: 16px; height: 16px; vertical-align: middle; margin-right: 5px;" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                            </svg>
                            Logout
                        </a>
                    </div>
                </header>

                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon" style="background-color: #E3F2FD; color: #007AFF;"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path></svg></div>
                        <div class="stat-info"><h4>Total Batches</h4><p id="stat-total-batches">0</p></div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon" style="background-color: #FFF9E6; color: #FF9500;"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 8v4l3 3m6-3a9 9 0 1 1-18 0 9 9 0 0 1 18 0z"></path></svg></div>
                        <div class="stat-info"><h4>Pending Pickup</h4><p id="stat-pending">0</p></div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon" style="background-color: #E5F9FF; color: #5AC8FA;"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 18H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h3.19M15 6h2a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2h-3.19"></path><path d="M23 13V9"></path><path d="M1 11v4"></path><path d="M12 22a7 7 0 0 0 7-7h-4a3 3 0 0 1-3-3V8a3 3 0 0 0-3 3v4a7 7 0 0 0 7 7Z"></path></svg></div>
                        <div class="stat-info"><h4>Ready to Sell</h4><p id="stat-ready-sell">0</p></div>
                    </div>
                     <div class="stat-card">
                        <div class="stat-icon" style="background-color: #E5FEEF; color: #34C759;"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle><polyline points="17 11 19 13 23 9"></polyline></svg></div>
                        <div class="stat-info"><h4>Products Sold</h4><p id="stat-delivered">0</p></div>
                    </div>
                </div>

                <div id="notification-area" class="notification"></div>
                
                <!-- Secure Messaging Widget -->
                <div class="card">
                    <h3 class="card-header"><svg class="icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>Inbox</h3>
                    <div id="message-list-container" class="message-list">
                        <!-- Messages will be loaded here by JavaScript -->
                    </div>
                </div>

                <div class="card">
                    <h3 class="card-header"><svg class="icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>Product Inventory</h3>
                    <div style="overflow-x:auto;">
                        <table class="products-table">
                            <thead>
                                <tr>
                                    <th>Brand Name</th>
                                    <th>Batch Number</th>
                                    <th>Quantity</th>
                                    <th>Manufacturer</th>
                                    <th>Dispensed At</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="products-tbody"></tbody>
                        </table>
                    </div>
                </div>

                <div class="card">
                    <h3 class="card-header">
                        <svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10z"></path><path d="M12 6v6l4 2"></path></svg>
                        <span>Recent Sales History</span>
                    </h3>
                    <div style="overflow-x:auto;">
                        <table class="products-table">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Batch Number</th>
                                    <th>Quantity Sold</th>
                                    <th>Sold</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody id="sales-history-tbody"></tbody>
                        </table>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Sell to Pharmacist Modal -->
    <div id="sell-modal-overlay" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Sell Product</h3>
                <button class="modal-close" onclick="closeModal()">&times;</button>
            </div>
            <form id="sell-form">
                <input type="hidden" id="distributor-product-id">
                <input type="hidden" id="product-id">
                <div class="form-group">
                    <label for="pharmacist-select">Select Pharmacist</label>
                    <select id="pharmacist-select" required></select>
                </div>
                <div class="form-group">
                    <label for="sell-quantity">Quantity to Sell</label>
                    <input type="number" id="sell-quantity" min="1" required>
                </div>
                <button type="submit" class="btn btn-sell" style="width: 100%; padding: 0.8rem; font-size: 1rem;">Complete Sale</button>
            </form>
        </div>
    </div>

    <!-- Sell to Hospital Modal -->
    <div id="sell-to-hospital-modal-overlay" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Sell Product to Hospital</h3>
                <button class="modal-close" onclick="closeHospitalModal()">&times;</button>
            </div>
            <form id="sell-to-hospital-form">
                <input type="hidden" id="hospital-distributor-product-id">
                <input type="hidden" id="hospital-product-id">
                <div class="form-group">
                    <label for="hospital-select">Select Hospital</label>
                    <select id="hospital-select" required></select>
                </div>
                <div class="form-group">
                    <label for="hospital-sell-quantity">Quantity to Sell</label>
                    <input type="number" id="hospital-sell-quantity" min="1" required>
                </div>
                <button type="submit" class="btn btn-sell" style="width: 100%; padding: 0.8rem; font-size: 1rem;">Complete Sale to Hospital</button>
            </form>
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

        // --- All other JavaScript functions ---
        const distributorId = <?php echo $distributor_id; ?>;

        document.addEventListener('DOMContentLoaded', function() {
            loadDistributorProducts();
            loadPharmacists();
            loadHospitals();
            loadMessages();
            loadSalesHistory();

            setInterval(loadMessages, 30000); 
            setInterval(loadSalesHistory, 30000);
            
            closeModal();
            closeHospitalModal();
            document.getElementById('sell-form').addEventListener('submit', handleSellSubmit);
            document.getElementById('sell-to-hospital-form').addEventListener('submit', handleSellToHospitalSubmit);
        });

        async function loadMessages() {
            const container = document.getElementById('message-list-container');
            try {
                const response = await fetch(`../api/get_messages.php?user_id=${distributorId}`);
                const data = await response.json();
                if (data.success && data.messages) {
                    renderMessages(data.messages);
                } else {
                    if (!container.hasChildNodes()) {
                        container.innerHTML = `<p style="text-align:center; padding: 2rem; color: var(--text-secondary);">${data.message || 'No messages found.'}</p>`;
                    }
                }
            } catch (error) {
                console.error('Error fetching messages:', error);
                container.innerHTML = `<p style="text-align:center; padding: 2rem; color:red;">Failed to load messages.</p>`;
            }
        }

        function renderMessages(messages) {
            const container = document.getElementById('message-list-container');
            const unreadMessages = messages.filter(msg => !msg.read_status);

            if (unreadMessages.length === 0 && container.children.length === 0) {
                 container.innerHTML = `<p style="text-align:center; padding: 2rem; color: var(--text-secondary);">Your inbox is empty.</p>`;
                 return;
            }
            
            if (unreadMessages.length > 0 && container.querySelector('p')) {
                container.innerHTML = '';
            }

            const displayedMessageIds = new Set(Array.from(container.children).map(el => el.dataset.messageId));

            unreadMessages.forEach(msg => {
                if (displayedMessageIds.has(String(msg.message_id))) return;

                const initial = msg.sender_name.charAt(0).toUpperCase();
                const time = new Date(msg.timestamp).toLocaleString('en-US', { hour: 'numeric', minute: 'numeric', month: 'short', day: 'numeric' });
                const isUrgent = msg.priority === 'Urgent';

                const messageEl = document.createElement('div');
                messageEl.className = `message-item`;
                messageEl.dataset.messageId = msg.message_id;

                messageEl.innerHTML = `
                    <div class="message-avatar ${isUrgent ? 'urgent' : ''}">${initial}</div>
                    <div class="message-content">
                        <div class="message-header">
                            <span class="message-sender">${msg.sender_name} (${msg.sender_role})</span>
                            <span class="message-time">${time}</span>
                        </div>
                        <p class="message-subject">${isUrgent ? '<strong style="color:var(--urgent-color)">[URGENT]</strong> ' : ''}${msg.subject}</p>
                        <p class="message-body">${msg.message_content}</p>
                    </div>
                    <div class="message-actions">
                        <button class="mark-read-btn">✓ Mark as Read</button>
                    </div>
                `;
                
                messageEl.querySelector('.message-content').addEventListener('click', () => {
                    messageEl.querySelector('.message-body').classList.toggle('expanded');
                });

                const markReadBtn = messageEl.querySelector('.mark-read-btn');
                markReadBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    messageEl.classList.add('disappearing');
                    markMessageAsRead(msg.message_id);
                    messageEl.addEventListener('transitionend', () => {
                        messageEl.remove();
                        if (container.children.length === 0) {
                            container.innerHTML = `<p style="text-align:center; padding: 2rem; color: var(--text-secondary);">Your inbox is empty.</p>`;
                        }
                    });
                });

                container.appendChild(messageEl);
            });
        }

        async function markMessageAsRead(messageId) {
            try {
                const response = await fetch('../api/mark_message_read.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ message_id: messageId, user_id: distributorId })
                });
                const data = await response.json();
                if (!data.success) {
                   console.error('API failed to mark message as read:', data.message);
                }
            } catch (error) {
                console.error('Failed to mark message as read:', error);
            }
        }

        async function loadDistributorProducts() {
            const tbody = document.getElementById('products-tbody');
            tbody.innerHTML = '<tr><td colspan="7" style="text-align:center; padding: 2rem;">Loading products...</td></tr>';
            renderStats([]);

            try {
                const response = await fetch('../api/get_distributor_products.php');
                const data = await response.json();

                if (data.success && data.products) {
                    renderProducts(data.products);
                    renderStats(data.products);
                } else {
                    tbody.innerHTML = `<tr><td colspan="7" style="text-align:center; padding: 2rem;">${data.message || 'No products found.'}</td></tr>`;
                    renderStats([]);
                }
            } catch (error) {
                console.error('Error fetching distributor products:', error);
                tbody.innerHTML = '<tr><td colspan="7" style="text-align:center; padding: 2rem; color: red;">Failed to load products.</td></tr>';
                renderStats([]);
            }
        }
        
        function renderStats(products) {
            let totalBatches = 0;
            let pendingCount = 0;
            let readyToSellCount = 0;
           
            if (products && products.length > 0) {
                totalBatches = products.length;
                products.forEach(p => {
                    if (p.pickup_status === 'pending') pendingCount++;
                    if (p.pickup_status === 'picked_up') readyToSellCount++;
                });
            }

            document.getElementById('stat-total-batches').textContent = totalBatches;
            document.getElementById('stat-pending').textContent = pendingCount;
            document.getElementById('stat-ready-sell').textContent = readyToSellCount;
        }

        function renderProducts(products) {
            const tbody = document.getElementById('products-tbody');
            tbody.innerHTML = '';
            if (products.length === 0) {
                tbody.innerHTML = '<tr><td colspan="7" style="text-align:center; padding: 2rem;">No products assigned.</td></tr>';
                return;
            }
            products.forEach(product => {
                const dispensedDate = new Date(product.dispensed_at).toLocaleString();
                const isPending = product.pickup_status === 'pending';
                const isPickedUp = product.pickup_status === 'picked_up';
                // Escape single quotes in brand_name for safe JS string in HTML attribute
                const safeBrandName = String(product.brand_name).replace(/'/g, "\\'").replace(/\"/g, '&quot;');
                const row = `
                    <tr>
                        <td><strong>${product.brand_name}</strong></td>
                        <td><span style="font-family: 'Roboto Mono', monospace; font-size: 0.9rem;">${product.batch_number}</span></td>
                        <td>${product.quantity}</td>
                        <td>${product.manufacturer_name}</td>
                        <td>${dispensedDate}</td>
                        <td><span class="status-badge status-${product.pickup_status}">${product.pickup_status.replace('_', ' ')}</span></td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn btn-pickup" onclick="handlePickup(${product.distributor_product_id}, ${product.product_id})" ${!isPending ? 'disabled' : ''}>Pick Up</button>
                                <button class="btn btn-sell" onclick="openSellModal(${product.distributor_product_id}, ${product.product_id}, '${safeBrandName}', ${product.quantity})" ${!isPickedUp ? 'disabled' : ''}>Sell to Pharmacist</button>
                                <button class="btn btn-sell" onclick="openSellToHospitalModal(${product.distributor_product_id}, ${product.product_id}, '${safeBrandName}', ${product.quantity})" ${!isPickedUp ? 'disabled' : ''}>Sell to Hospital</button>
                            </div>
                        </td>
                    </tr>
                `;
                tbody.insertAdjacentHTML('beforeend', row);
            });
        }

        async function loadSalesHistory() {
            const tbody = document.getElementById('sales-history-tbody');
            try {
                const response = await fetch(`../api/get_sales_history.php`);
                if (!response.ok) {
                    const errorText = await response.text();
                    throw new Error(`Network response was not ok: ${response.status} ${response.statusText}. Server response: ${errorText}`);
                }
                const sales = await response.json();
                renderSalesHistory(sales);
                document.getElementById('stat-delivered').textContent = sales.length;
            } catch (error) {
                console.error('Error fetching sales history:', error);
                tbody.innerHTML = '<tr><td colspan="5" style="text-align:center; padding: 2rem; color: red;">Failed to load sales history.</td></tr>';
                document.getElementById('stat-delivered').textContent = 0;
            }
        }

        function renderSalesHistory(history) {
            const tbody = document.getElementById('sales-history-tbody');
            tbody.innerHTML = '';
            if (history.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5" style="text-align:center; padding: 2rem;">No sales have been recorded yet.</td></tr>';
                return;
            }
            history.forEach(sale => {
                const saleDate = new Date(sale.sale_date).toLocaleString();
                const row = `
                    <tr>
                        <td><strong>${sale.brand_name}</strong></td>
                        <td><span style="font-family: 'Roboto Mono', monospace; font-size: 0.9rem;">${sale.batch_number}</span></td>
                        <td>${sale.quantity_sold}</td>
                        <td>${sale.sold_to} (${sale.recipient_type})</td>
                        <td>${saleDate}</td>
                    </tr>
                `;
                tbody.insertAdjacentHTML('beforeend', row);
            });
        }

        async function loadPharmacists() {
            try {
                const response = await fetch('../api/get_pharmacists.php');
                const data = await response.json();
                const select = document.getElementById('pharmacist-select');
                select.innerHTML = '<option value="">Select a pharmacist...</option>';
                if (data.success) {
                    data.pharmacists.forEach(p => {
                        select.innerHTML += `<option value="${p.user_id}">${p.full_name}</option>`;
                    });
                }
            } catch (error) {
                console.error('Failed to load pharmacists:', error);
            }
        }

        async function loadHospitals() {
            try {
                const response = await fetch('../api/get_hospitals.php');
                const data = await response.json();
                const select = document.getElementById('hospital-select');
                select.innerHTML = '<option value="">Select a hospital...</option>';
                if (data.success) {
                    data.hospitals.forEach(h => {
                        select.innerHTML += `<option value="${h.user_id}">${h.full_name}</option>`;
                    });
                }
            } catch (error) {
                console.error('Failed to load hospitals:', error);
            }
        }

        function openSellModal(distributorProductId, productId, brandName, availableQty) {
            document.querySelector('#sell-modal-overlay h3').innerText = `Sell to Pharmacist: ${brandName}`;
            document.getElementById('distributor-product-id').value = distributorProductId;
            document.getElementById('product-id').value = productId;
            document.getElementById('sell-quantity').value = 1;
            document.getElementById('sell-quantity').max = availableQty;
            document.getElementById('sell-modal-overlay').style.display = 'flex';
        }

        function openSellToHospitalModal(distributorProductId, productId, brandName, availableQty) {
            document.querySelector('#sell-to-hospital-modal-overlay h3').innerText = `Sell to Hospital: ${brandName}`;
            document.getElementById('hospital-distributor-product-id').value = distributorProductId;
            document.getElementById('hospital-product-id').value = productId;
            document.getElementById('hospital-sell-quantity').value = 1;
            document.getElementById('hospital-sell-quantity').max = availableQty;
            document.getElementById('sell-to-hospital-modal-overlay').style.display = 'flex';
        }

        function closeModal() {
            document.getElementById('sell-modal-overlay').style.display = 'none';
        }

        function closeHospitalModal() {
            document.getElementById('sell-to-hospital-modal-overlay').style.display = 'none';
        }

        async function handleSellSubmit(event) {
            event.preventDefault();
            const distributorProductId = document.getElementById('distributor-product-id').value;
            const productId = document.getElementById('product-id').value;
            const pharmacistId = document.getElementById('pharmacist-select').value;
            const quantity = document.getElementById('sell-quantity').value;

            if (!pharmacistId || !quantity || quantity <= 0) {
                showNotification('Please select a pharmacist and enter a valid quantity.', 'error');
                return;
            }
            
            try {
                const response = await fetch('../api/sell_to_pharmacist.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        distributor_product_id: distributorProductId,
                        product_id: productId,
                        pharmacist_id: pharmacistId,
                        quantity: quantity,
                        actor_id: distributorId
                    })
                });
                const data = await response.json();
                if (data.success) {
                    showNotification('Sale successful! Refreshing lists...', 'success');
                    closeModal();
                    setTimeout(() => {
                        loadDistributorProducts();
                        loadSalesHistory();
                    }, 1500);
                } else {
                    showNotification('Error: ' + data.message, 'error');
                }
            } catch (error) {
                console.error('Sell error:', error);
                showNotification('A critical error occurred during the sale.', 'error');
            }
        }

        async function handleSellToHospitalSubmit(event) {
            event.preventDefault();
            const distributorProductId = document.getElementById('hospital-distributor-product-id').value;
            const productId = document.getElementById('hospital-product-id').value;
            const quantity = document.getElementById('hospital-sell-quantity').value;
            const hospitalId = document.getElementById('hospital-select').value;
            const actorId = distributorId;

            if (!hospitalId || !quantity || quantity <= 0) {
                showNotification('Please select a hospital and enter a valid quantity.', 'error');
                return;
            }

            console.log('Sending to sell_to_hospital.php:', {
                distributor_product_id: distributorProductId,
                product_id: productId,
                hospital_id: hospitalId,
                quantity: quantity,
                actor_id: actorId
            });

            try {
                const response = await fetch('../api/sell_to_hospital.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        distributor_product_id: distributorProductId,
                        product_id: productId,
                        hospital_id: hospitalId,
                        quantity: quantity,
                        actor_id: actorId
                    })
                });
                const data = await response.json();
                if (data.success) {
                    showNotification('Sale to Hospital successful! Refreshing lists...', 'success');
                    closeHospitalModal();
                    setTimeout(() => {
                        loadDistributorProducts();
                        loadSalesHistory();
                    }, 1500);
                } else {
                    showNotification('Error: ' + data.message, 'error');
                }
            } catch (error) {
                console.error('Sell to Hospital error:', error);
                showNotification('A critical error occurred during the sale to hospital.', 'error');
            }
        }
        
        async function handlePickup(distributorProductId, productId) {
            try {
                const response = await fetch('../api/update_pickup_status.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        distributor_product_id: distributorProductId,
                        product_id: productId,
                        new_status: 'picked_up',
                        actor_id: distributorId 
                    })
                });
                const data = await response.json();
                if (data.success) {
                    showNotification('Product status updated successfully! Refreshing list...', 'success');
                    setTimeout(loadDistributorProducts, 2000);
                } else {
                    showNotification('Error: ' + (data.message || 'Unknown error'), 'error');
                }
            } catch (error) {
                console.error('Error updating status:', error);
                showNotification('A critical error occurred. Please try again.', 'error');
            }
        }

        function showNotification(message, type) {
            const notificationArea = document.getElementById('notification-area');
            notificationArea.className = `notification ${type}`;
            notificationArea.textContent = message;
            notificationArea.style.display = 'block';
            setTimeout(() => {
                notificationArea.style.display = 'none';
            }, 4000);
        }
    </script>
</body>
</html>
