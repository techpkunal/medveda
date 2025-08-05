<?php
/**
 * Patient Dashboard - Medical Records
 * This file now uses the centralized session manager for robust authentication.
 */

// Use the centralized session manager to handle authentication
require_once '../otp-login/session_manager.php';

// This function will handle session start, validation, and redirection if not authenticated
// or does not have the 'patient' role.
$user = requireAuth('patient');

// Get patient info from the session
$patient_id = $_SESSION['user_id'];
$patient_name = isset($_SESSION['full_name']) ? $_SESSION['full_name'] : 'Patient';

// Handle logout request
if (isset($_GET['logout']) && $_GET['logout'] == 'true') {
    logout(); // Use the centralized logout function
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Medication History - MedVeda</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
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
        .main-header h2 { font-size: 1.8rem; font-weight: 800; margin: 0; }
        .profile-info { display: flex; align-items: center; gap: 1rem; }
        .profile-info img { width: 48px; height: 48px; border-radius: 50%; border: 2px solid var(--primary-color); }
        .profile-info .username { font-weight: 700; }

        .medication-list .list-item {
            background-color: #fff;
            padding: 1.5rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            border: 2px solid var(--border-color);
            opacity: 0;
            transform: translateY(20px);
            animation: slideUpIn 0.5s forwards;
        }
         @keyframes slideUpIn { to { opacity: 1; transform: translateY(0); } }
        
        .medication-list .med-header {
            display: flex;
            align-items: center;
            margin-bottom: 1.5rem;
        }
        .medication-list .med-icon {
            width: 48px;
            height: 48px;
            border-radius: 8px;
            background-color: #f3f2ff;
            color: var(--primary-color);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-right: 1.5rem;
        }
        .medication-list .med-details { flex-grow: 1; }
        .medication-list .med-details h3 { margin: 0 0 0.25rem; font-size: 1.1rem; font-weight: 700; }
        .medication-list .med-details p { margin: 0; color: var(--text-secondary); font-size: 0.9rem; font-weight: 600; }

        .verification-form {
            display: flex;
            gap: 0.75rem;
            align-items: center;
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 2px solid var(--border-color);
        }
        .verification-form .uid-input {
            flex-grow: 1;
            padding: 0.75rem 1rem;
            border: 2px solid var(--border-color);
            border-radius: 8px;
            font-family: 'Roboto Mono', monospace;
            font-size: 0.9rem;
            font-weight: 600;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .verification-form .uid-input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px var(--primary-glow);
        }
        .verification-form .verify-btn {
            background-color: var(--primary-color);
            color: #fff;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 700;
            border: none;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        .verification-form .verify-btn:hover { background-color: var(--primary-hover); }
        .verification-form .verify-btn:disabled { background-color: #a5b4fc; cursor: not-allowed; }
        .verification-message {
            margin-top: 0.75rem;
            font-size: 0.85rem;
            font-weight: 600;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            display: none;
        }
        .verification-message.error { color: #991b1b; background-color: #fee2e2; }
        .verification-message.success { color: #166534; background-color: #dcfce7; }

        .skeleton-item {
            height: 150px;
            background-color: #fff;
            border-radius: 12px;
            margin-bottom: 1rem;
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            border: 2px solid var(--border-color);
            animation: pulse 1.5s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
        @keyframes pulse { 50% { opacity: .5; } }
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
                <header class="main-header">
                    <h2>Medical Records</h2>
                    <div class="profile-info">
                        <span class="username">Welcome, <?php echo htmlspecialchars($patient_name); ?></span>
                        <img src="https://i.pravatar.cc/150?u=<?php echo htmlspecialchars($patient_id); ?>" alt="User Profile Photo">
                    </div>
                </header>

                <div class="medication-list" id="history-body">
                    <!-- History will be loaded here by JavaScript -->
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

        // --- All other JavaScript functions ---
        document.addEventListener('DOMContentLoaded', function() {
            const historyBody = document.getElementById('history-body');
            
            let skeletonHTML = '';
            for(let i=0; i<3; i++) {
                skeletonHTML += `<div class="skeleton-item"></div>`;
            }
            historyBody.innerHTML = skeletonHTML;

            fetch('../api/get_patient_history.php')
                .then(res => res.ok ? res.json() : Promise.reject(res))
                .then(data => {
                    historyBody.innerHTML = ''; 
                    if (data.success && data.history.length > 0) {
                        data.history.forEach((item, index) => {
                            const date = new Date(item.dispensation_timestamp).toLocaleString('en-GB', {
                                year: 'numeric', month: 'long', day: 'numeric'
                            });
                            
                            const listItem = document.createElement('div');
                            listItem.className = 'list-item';
                            listItem.id = `item-${item.unique_identifier}`;
                            listItem.style.animationDelay = `${index * 100}ms`;

                            listItem.innerHTML = `
                                <div class="med-header">
                                    <div class="med-icon"><i class="fas fa-pills"></i></div>
                                    <div class="med-details">
                                        <h3>${item.brand_name}</h3>
                                        <p>
                                            ${item.generic_name} &bull; ${item.strength || ''} &bull; Quantity: ${item.dispensed_quantity}
                                        </p>
                                        <p style="font-size: 0.8rem; margin-top: 4px;">Dispensed on: ${date}</p>
                                    </div>
                                </div>
                                <form class="verification-form" onsubmit="verifyProduct(event, '${item.unique_identifier}')">
                                    <input type="text" class="uid-input" placeholder="Enter Unique ID from Product" required>
                                    <button type="submit" class="verify-btn">Verify</button>
                                </form>
                                <div class="verification-message"></div>
                            `;
                            
                            historyBody.appendChild(listItem);
                        });
                    } else {
                        historyBody.innerHTML = '<p style="text-align:center; padding: 2rem; color: var(--text-light);">No medication history found.</p>';
                    }
                })
                .catch(error => {
                    console.error("Fetch error:", error);
                    historyBody.innerHTML = '<p style="color:red; text-align:center; padding: 2rem;">Could not load medication history.</p>';
                });
        });

        function verifyProduct(event, original_uid) {
            event.preventDefault();
            
            const form = event.target;
            const input = form.querySelector('.uid-input');
            const button = form.querySelector('.verify-btn');
            const messageDiv = form.nextElementSibling;
            
            const enteredUid = input.value.trim();

            if (!enteredUid) {
                messageDiv.textContent = 'Please enter the Unique ID.';
                messageDiv.className = 'verification-message error';
                messageDiv.style.display = 'block';
                return;
            }

            button.disabled = true;
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            messageDiv.style.display = 'none';

            const body = new URLSearchParams();
            body.append('unique_identifier', enteredUid);
            body.append('original_identifier', original_uid);

            fetch('../api/patient_verify.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: body
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    messageDiv.textContent = 'Verification successful! Redirecting...';
                    messageDiv.className = 'verification-message success';
                    messageDiv.style.display = 'block';
                    window.location.href = `view_product.php?uid=${encodeURIComponent(enteredUid)}`;
                } else {
                    messageDiv.textContent = data.message || 'Verification failed. Please check the ID and try again.';
                    messageDiv.className = 'verification-message error';
                    messageDiv.style.display = 'block';
                    button.disabled = false;
                    button.innerHTML = 'Verify';
                }
            })
            .catch(error => {
                console.error('Verification API error:', error);
                messageDiv.textContent = 'An error occurred. Please try again later.';
                messageDiv.className = 'verification-message error';
                messageDiv.style.display = 'block';
                button.disabled = false;
                button.innerHTML = 'Verify';
            });
        }
    </script>
</body>
</html>
