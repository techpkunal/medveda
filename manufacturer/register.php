<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Product - MedChain</title>
    
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
            --success-bg: #F0FDF4;
            --success-text: #16A34A;
            --info-bg: #EFF6FF;
            --info-text: #2563EB;
            --error-bg: #FEF2F2;
            --error-text: #DC2626;
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
            padding: 2.5rem; 
            border-radius: 12px; 
            border: 2px solid var(--border-color);
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        
        /* Form Styling */
        .form-section { border: none; padding: 0; margin: 0 0 2.5rem 0; }
        .form-section legend {
            font-size: 1.2rem; font-weight: 700; color: var(--text-primary);
            padding-bottom: 0.75rem; margin-bottom: 1.5rem;
            width: 100%; border-bottom: 2px solid var(--border-color);
        }
        .form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem; }
        .form-group { display: flex; flex-direction: column; }
        .form-group label { font-weight: 700; margin-bottom: 0.5rem; color: var(--text-secondary); }
        .form-group input, .form-group select, .form-group textarea {
            padding: 12px; border: 2px solid var(--border-color);
            border-radius: 8px; font-family: inherit; font-size: 1rem;
            background-color: #fefefe;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .form-group textarea { resize: vertical; min-height: 80px; }
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus {
            outline: none; border-color: var(--primary-color);
            box-shadow: 0 0 0 3px var(--primary-glow);
        }
        
        .btn-primary {
            background-color: var(--primary-color); color: white;
            padding: 14px 28px; text-decoration: none; border-radius: 8px;
            font-weight: 700; transition: all 0.2s ease;
            border: none; cursor: pointer;
            box-shadow: 0 4px 12px var(--primary-glow);
            width: 100%; font-size: 1.1rem; margin-top: 1rem;
        }
        .btn-primary:hover { background-color: var(--primary-hover); transform: translateY(-2px); }
        
        .alert { padding: 1rem 1.5rem; margin-bottom: 1.5rem; border-radius: 8px; font-weight: 600; border: 1px solid transparent; }
        .alert-success { color: var(--success-text); background-color: var(--success-bg); border-color: var(--success-text); }
        .alert-info { color: var(--info-text); background-color: var(--info-bg); border-color: var(--info-text); }
        .alert-error { color: var(--error-text); background-color: var(--error-bg); border-color: var(--error-text); }
        .alert .unique-id {
             font-family: 'Roboto Mono', monospace; font-weight: bold;
             display: block; margin-top: 0.5rem; word-break: break-all;
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
                <div class="sidebar-header">
                    <img src="../assets/medchain_logo.svg" alt="MedChain Logo" class="logo-icon">
                    <h1>MedChain</h1>
                </div>
                <nav class="sidebar-nav">
                    <ul>
                        <li><a href="index.html"><svg class="icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg><span>Dashboard</span></a></li>
                        <li><a href="register.php" class="active"><svg class="icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg><span>Add Product</span></a></li>
                        <li><a href="history.php"><svg class="icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 4v6h6"/><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"/></svg><span>Full History</span></a></li>
                        <li><a href="audit.php"><svg class="icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg><span>Audit Inspector</span></a></li>
                    </ul>
                </nav>
            </aside>

            <main class="main-content">
                <header class="page-header">
                    <h2>Register New Pharmaceutical Product</h2>
                </header>

                <div class="card">
                    <?php
                    if (isset($_GET['status'])) {
                        if ($_GET['status'] == 'success') {
                            echo '<div class="alert alert-success"><strong>Success!</strong> The product has been registered on the blockchain.</div>';
                            if (isset($_GET['uid'])) {
                                echo '<div class="alert alert-info"><strong>Product Unique Identifier:</strong><span class="unique-id">' . htmlspecialchars($_GET['uid']) . '</span></div>';
                            }
                        } elseif ($_GET['status'] == 'error') {
                             echo '<div class="alert alert-error"><strong>Error!</strong> Could not register the product. Please check the details and try again.</div>';
                        }
                    }
                    ?>
                
                    <form action="../api/register_product.php" method="POST">
                        
                        <fieldset class="form-section">
                            <legend>1. Basic Identification</legend>
                            <div class="form-grid">
                                <div class="form-group"><label for="brand_name">Brand Name</label><input type="text" id="brand_name" name="brand_name" required></div>
                                <div class="form-group"><label for="generic_name">Generic Name</label><input type="text" id="generic_name" name="generic_name" required></div>
                                <div class="form-group"><label for="batch_number">Batch Number</label><input type="text" id="batch_number" name="batch_number" required></div>
                                <div class="form-group"><label for="product_code_sku">Product Code / SKU</label><input type="text" id="product_code_sku" name="product_code_sku"></div>
                            </div>
                        </fieldset>

                        <fieldset class="form-section">
                            <legend>2. Manufacturer Details</legend>
                            <div class="form-grid">
                                <div class="form-group"><label for="manufacturing_license_number">Manufacturing License No.</label><input type="text" id="manufacturing_license_number" name="manufacturing_license_number"></div>
                                <div class="form-group"><label for="country_of_origin">Country of Origin</label><input type="text" id="country_of_origin" name="country_of_origin"></div>
                                <div class="form-group"><label for="manufacturing_date">Manufacturing Date (MFG)</label><input type="date" id="manufacturing_date" name="manufacturing_date" required></div>
                                <div class="form-group"><label for="expiry_date">Expiry Date (EXP)</label><input type="date" id="expiry_date" name="expiry_date" required></div>
                            </div>
                        </fieldset>

                        <fieldset class="form-section">
                            <legend>3. Composition</legend>
                            <div class="form-grid">
                                 <div class="form-group"><label for="active_ingredients">Active Ingredients (e.g., Paracetamol 500mg)</label><textarea id="active_ingredients" name="active_ingredients"></textarea></div>
                                 <div class="form-group"><label for="excipients">Excipients (Inactive Ingredients)</label><textarea id="excipients" name="excipients"></textarea></div>
                            </div>
                        </fieldset>

                        <fieldset class="form-section">
                            <legend>4. Dosage Information</legend>
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="formulation_type">Formulation Type</label>
                                    <select id="formulation_type" name="formulation_type">
                                        <option value="Tablet">Tablet</option>
                                        <option value="Capsule">Capsule</option>
                                        <option value="Syrup">Syrup</option>
                                        <option value="Injection">Injection</option>
                                        <option value="Ointment">Ointment</option>
                                        <option value="Drops">Drops</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>
                                <div class="form-group"><label for="strength">Strength (e.g., 500 mg)</label><input type="text" id="strength" name="strength"></div>
                                <div class="form-group"><label for="route_of_administration">Route of Administration</label><input type="text" id="route_of_administration" name="route_of_administration" placeholder="e.g., Oral, Intravenous"></div>
                                <div class="form-group"><label for="dosage_instructions">Dosage Instructions</label><textarea id="dosage_instructions" name="dosage_instructions"></textarea></div>
                            </div>
                        </fieldset>
                        
                        <fieldset class="form-section">
                            <legend>5. Regulatory & Pricing</legend>
                            <div class="form-grid">
                                <div class="form-group"><label for="drug_license_number">Drug License No.</label><input type="text" id="drug_license_number" name="drug_license_number"></div>
                                <div class="form-group"><label for="approval_authority">Approval Authority (e.g., FDA)</label><input type="text" id="approval_authority" name="approval_authority"></div>
                                <div class="form-group"><label for="approval_date">Approval Date</label><input type="date" id="approval_date" name="approval_date"></div>
                                <div class="form-group"><label for="mrp">MRP (Maximum Retail Price)</label><input type="number" step="0.01" id="mrp" name="mrp"></div>
                            </div>
                        </fieldset>

                        <fieldset class="form-section">
                            <legend>6. Packaging & Storage</legend>
                            <div class="form-grid">
                                <div class="form-group"><label for="pack_size">Pack Size (e.g., Strip of 10)</label><input type="text" id="pack_size" name="pack_size"></div>
                                <div class="form-group"><label for="packaging_type">Packaging Type (e.g., Blister Pack)</label><input type="text" id="packaging_type" name="packaging_type"></div>
                                <div class="form-group"><label for="shelf_life">Shelf Life (e.g., 24 Months)</label><input type="text" id="shelf_life" name="shelf_life"></div>
                                <div class="form-group"><label for="storage_conditions">Storage Conditions</label><input type="text" id="storage_conditions" name="storage_conditions" placeholder="e.g., Store below 25°C"></div>
                            </div>
                        </fieldset>

                        <fieldset class="form-section">
                            <legend>7. Usage & Warnings</legend>
                            <div class="form-grid">
                                <div class="form-group"><label for="therapeutic_category">Therapeutic Category</label><input type="text" id="therapeutic_category" name="therapeutic_category" placeholder="e.g., Analgesic, Antibiotic"></div>
                                <div class="form-group"><label for="indications">Indications</label><textarea id="indications" name="indications"></textarea></div>
                                <div class="form-group"><label for="contraindications">Contraindications</label><textarea id="contraindications" name="contraindications"></textarea></div>
                                <div class="form-group"><label for="side_effects">Side Effects</label><textarea id="side_effects" name="side_effects"></textarea></div>
                                <div class="form-group"><label for="precautions">Precautions</label><textarea id="precautions" name="precautions"></textarea></div>
                                <div class="form-group"><label for="interactions">Interactions</label><textarea id="interactions" name="interactions"></textarea></div>
                            </div>
                        </fieldset>

                        <fieldset class="form-section">
                            <legend>8. Logistics & Stock</legend>
                            <div class="form-grid">
                                <div class="form-group"><label for="stock_quantity">Stock Quantity Available</label><input type="number" id="stock_quantity" name="stock_quantity" required></div>
                                <div class="form-group"><label for="reorder_level">Reorder Level</label><input type="number" id="reorder_level" name="reorder_level"></div>
                                <div class="form-group"><label for="supplier_name">Supplier / Distributor Name</label><input type="text" id="supplier_name" name="supplier_name"></div>
                            </div>
                        </fieldset>

                        <button type="submit" class="btn-primary">Register Product on Chain</button>
                    </form>
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
    </script>

</body>
</html>
