<?php
/**
 * Patient Inventory/Stock Check Page (V2)
 * New workflow: Select a pharmacy first, then search for medicine.
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
    <title>Check Pharmacy Stock - MedVeda</title>
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
            --green-stock: #34C759;
            --red-out-stock: #FF3B30;
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

        .card {
            background-color: #fff;
            padding: 2rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            border: 2px solid var(--border-color);
        }

        .form-step {
            margin-bottom: 2rem;
            border-bottom: 2px solid var(--border-color);
            padding-bottom: 2rem;
        }
        .form-step:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }

        .form-step label {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-weight: 700;
            margin-bottom: 1rem;
            font-size: 1.1rem;
        }

        .select-input, .search-input {
            width: 100%;
            padding: 0.8rem 1rem;
            font-size: 1rem;
            border-radius: 8px;
            border: 2px solid var(--border-color);
            transition: border-color 0.2s, box-shadow 0.2s;
            background-color: #fff;
            font-weight: 600;
            box-sizing: border-box;
        }
        .select-input:focus, .search-input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px var(--primary-glow);
        }
        .search-input:disabled {
            background-color: #f8f9fa;
            cursor: not-allowed;
        }

        #search-step {
            opacity: 0.5;
            transition: opacity 0.3s ease-in-out;
        }

        #search-step.active {
            opacity: 1;
        }

        .search-form { display: flex; gap: 1rem; }
        .search-button {
            background-color: var(--primary-color);
            color: #fff;
            padding: 0.8rem 1.5rem;
            border-radius: 8px;
            font-weight: 700;
            border: none;
            cursor: pointer;
            transition: background-color 0.2s, transform 0.2s;
        }
        .search-button:hover { background-color: var(--primary-hover); transform: translateY(-2px); }
        .search-button:disabled { background-color: #a5b4fc; cursor: not-allowed; transform: none; }

        .results-list .list-item {
            display: flex;
            align-items: center;
            padding: 1.5rem;
            border-bottom: 2px solid var(--border-color);
        }
        .results-list .list-item:last-child { border-bottom: none; }
        .results-list .med-icon {
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
        .results-list .med-details { flex-grow: 1; }
        .results-list .med-details h3 { margin: 0 0 0.25rem; font-size: 1.1rem; font-weight: 700; }
        .results-list .med-details p { margin: 0; color: var(--text-light); font-size: 0.9rem; font-weight: 600; }
        
        .results-list .availability.in-stock { font-weight: 700; color: var(--green-stock); }
        .results-list .availability.out-of-stock { font-weight: 700; color: var(--red-out-stock); }
        
        .skeleton { animation: pulse 1.5s cubic-bezier(0.4, 0, 0.6, 1) infinite; }
        @keyframes pulse { 50% { opacity: .5; } }
        .skeleton-box { background-color: #e2e8f0; border-radius: 8px; }

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
                    <a href="index.php"><i class="fas fa-file-medical"></i>Medical Records</a>
                    <a href="inventory.php" class="active"><i class="fas fa-boxes-stacked"></i>Check Stock</a>
                    <a href="doctor.html"><i class="fas fa-user-doctor"></i>AI Doctor</a>
             
                <a href="?logout=true" class="sidebar-nav logout-link"><i class="fas fa-sign-out-alt"></i>Logout</a>
               </nav>
            </aside>

            <main class="main-content">
                <header class="main-header">
                    <h2>Check Pharmacy Stock</h2>
                    <div class="profile-info">
                        <span class="username">Welcome, <?php echo htmlspecialchars($patient_name); ?></span>
                        <img src="https://i.pravatar.cc/150?u=<?php echo htmlspecialchars($patient_id); ?>" alt="User Profile Photo">
                    </div>
                </header>

                <div class="card">
                    <div class="form-step">
                        <label for="pharmacistSelector"><i class="fas fa-store"></i> Step 1: Select a Pharmacy</label>
                        <select id="pharmacistSelector" class="select-input">
                            <option value="">-- Please choose a medical store --</option>
                        </select>
                    </div>

                    <div id="search-step" class="form-step">
                        <label for="searchInput"><i class="fas fa-search"></i> Step 2: Search for Medicine</label>
                        <form id="searchForm" class="search-form">
                            <input type="search" id="searchInput" class="search-input" placeholder="Enter medicine name..." disabled>
                            <button type="submit" id="searchButton" class="search-button" disabled>Search</button>
                        </form>
                    </div>

                    <div class="results-list" id="results-body">
                        <p style="text-align:center; color: var(--text-light); padding: 2rem 0; font-weight: 600;">Please select a pharmacy to begin.</p>
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
        
        // --- All other JavaScript functions ---
        document.addEventListener('DOMContentLoaded', function() {
            const pharmacistSelector = document.getElementById('pharmacistSelector');
            const searchStep = document.getElementById('search-step');
            const searchInput = document.getElementById('searchInput');
            const searchButton = document.getElementById('searchButton');
            const searchForm = document.getElementById('searchForm');
            const resultsBody = document.getElementById('results-body');

            // Fetch and populate pharmacists
            fetch('../api/get_pharmacists.php')
                .then(res => res.json())
                .then(data => {
                    if (data.success && data.pharmacists) {
                        data.pharmacists.forEach(pharmacist => {
                            const option = document.createElement('option');
                            option.value = pharmacist.user_id;
                            option.textContent = pharmacist.full_name;
                            pharmacistSelector.appendChild(option);
                        });
                    }
                })
                .catch(error => {
                    resultsBody.innerHTML = '<p style="text-align:center; color: var(--red-out-stock);">Could not load pharmacy list.</p>';
                });

            // Event listener for pharmacy selection
            pharmacistSelector.addEventListener('change', function() {
                if (pharmacistSelector.value) {
                    // Enable search
                    searchStep.classList.add('active');
                    searchInput.disabled = false;
                    searchButton.disabled = false;
                    resultsBody.innerHTML = '<p style="text-align:center; color: var(--text-light); padding: 2rem 0; font-weight: 600;">Now, search for a medicine to see its availability.</p>';
                } else {
                    // Disable search
                    searchStep.classList.remove('active');
                    searchInput.disabled = true;
                    searchButton.disabled = true;
                    resultsBody.innerHTML = '<p style="text-align:center; color: var(--text-light); padding: 2rem 0; font-weight: 600;">Please select a pharmacy to begin.</p>';
                }
            });

            // Event listener for search form submission
            searchForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const searchTerm = searchInput.value.trim();
                const pharmacistId = pharmacistSelector.value;

                if (!searchTerm || !pharmacistId) {
                    return;
                }

                // Show skeleton loader
                let skeletonHTML = '';
                for(let i=0; i<3; i++) {
                    skeletonHTML += `
                        <div class="list-item skeleton">
                            <div class="skeleton-box" style="width: 48px; height: 48px; margin-right: 1.5rem;"></div>
                            <div style="flex-grow: 1;"><div class="skeleton-box" style="height: 20px; width: 40%; margin-bottom: 0.5rem;"></div><div class="skeleton-box" style="height: 16px; width: 60%;"></div></div>
                        </div>`;
                }
                resultsBody.innerHTML = skeletonHTML;

                // Fetch results for the selected pharmacy
                fetch(`../api/get_pharmacy_inventory.php?pharmacist_id=${pharmacistId}&search=${encodeURIComponent(searchTerm)}`)
                    .then(res => res.json())
                    .then(data => {
                        resultsBody.innerHTML = ''; // Clear loader
                        if (data.success && data.products.length > 0) {
                            data.products.forEach(product => {
                                const listItem = document.createElement('div');
                                listItem.className = 'list-item';
                                listItem.innerHTML = `
                                    <div class="med-icon"><i class="fas fa-prescription-bottle-medical"></i></div>
                                    <div class="med-details">
                                        <h3>${product.brand_name}</h3>
                                        <p>${product.generic_name} &bull; ${product.strength} &bull; by ${product.manufacturer_name}</p>
                                    </div>
                                    <div class="availability in-stock">
                                        <i class="fas fa-check-circle"></i> In Stock
                                    </div>
                                `;
                                resultsBody.appendChild(listItem);
                            });
                        } else if (data.success) {
                            resultsBody.innerHTML = `
                                <div class="list-item">
                                    <div class="med-icon" style="color: var(--red-out-stock);"><i class="fas fa-times-circle"></i></div>
                                    <div class="med-details">
                                        <h3>${searchTerm}</h3>
                                        <p>This medicine was not found at the selected pharmacy.</p>
                                    </div>
                                    <div class="availability out-of-stock">
                                        Out of Stock
                                    </div>
                                </div>`;
                        } else {
                            resultsBody.innerHTML = `<p style="text-align:center; color: var(--red-out-stock); padding: 2rem 0;">Error: ${data.message}</p>`;
                        }
                    })
                    .catch(error => {
                        resultsBody.innerHTML = '<p style="text-align:center; color: var(--red-out-stock); padding: 2rem 0;">Could not connect to the inventory service.</p>';
                    });
            });
        });
    </script>
</body>
</html>
