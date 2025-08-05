<?php
/**
 * Patient Dashboard
 * This file now uses the centralized session manager for robust authentication.
 */

// CORRECTED: Point to the correct path for the session manager
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
    <title>Patient Dashboard - MedVeda</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Roboto+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/patient-dashboard.css">
    <style>
        /* Base styles moved to patient-dashboard.css */

        /* Animations moved to patient-dashboard.css */

        html, body {
            margin: 0;
            padding: 0;
            height: 100%;
            font-family: 'Poppins', sans-serif;
            background-color: var(--bg-color);
        }
        
        body {
            display: flex;
            overflow-y: auto; 
        }
        
        .sidebar {
            width: 260px;
            background-color: var(--sidebar-bg);
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            border-right: 1px solid var(--border-color);
            flex-shrink: 0;
            z-index: 1000;
            overflow-y: auto; 
        }
        
        .sidebar-header {
            padding: 1.5rem;
            display: flex;
            align-items: center;
            gap: 12px;
            border-bottom: 1px solid var(--border-color);
        }
        
        .sidebar-header .logo-icon { 
            width: 40px; 
            height: 40px;
            color: var(--primary-color);
        }

        .sidebar-header h1 { 
            font-size: 1.5rem; 
            margin: 0; 
            font-weight: 700; 
            color: var(--secondary-color); 
        }
        
        .sidebar-nav { 
            list-style: none; 
            padding: 1.5rem 0; 
            margin: 0; 
        }
        
        .sidebar-nav a {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 1rem 1.5rem;
            color: var(--text-secondary);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease-in-out;
            position: relative;
        }
        
        .sidebar-nav a:hover { 
            background-color: var(--bg-color); 
            color: var(--primary-color); 
            transform: translateX(5px);
        }
        
        .stat-card h4 {
            margin: 0.5rem 0;
            color: var(--secondary-color);
        }

        .stat-card p {
            margin: 0;
            color: var(--text-secondary);
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <svg class="logo-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                <circle cx="8.5" cy="7" r="4"/>
                <line x1="20" y1="8" x2="20" y2="14"/>
                <line x1="23" y1="11" x2="17" y2="11"/>
            </svg>
            <h1>MedVeda</h1>
        </div>
        
        <nav class="sidebar-nav">
            <a href="dashboard.php" class="active">
                <svg class="icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                    <polyline points="9,22 9,12 15,12 15,22"/>
                </svg>
                Dashboard
            </a>
            <a href="index.php">
                <svg class="icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                    <polyline points="14,2 14,8 20,8"/>
                    <line x1="16" y1="13" x2="8" y2="13"/>
                    <line x1="16" y1="17" x2="8" y2="17"/>
                </svg>
                My Medications
            </a>
            <a href="inventory.php">
                <svg class="icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                   <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
                </svg>
                Verify Products
            </a>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="dashboard-grid">
            <!-- Medical History Section -->
            <div class="card">
                <div class="card-header">
                    <h3>Medical History</h3>
                    <button class="btn btn-primary" onclick="fetchMedicalHistory()">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </button>
                </div>
                <div class="card-body">
                    <div id="medicalHistory" class="history-container">
                        <div class="loading-spinner">
                            <div class="spinner"></div>
                            <p>Loading your medical history...</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Medications -->
            <div class="card">
                <div class="card-header">
                    <h3>Current Medications</h3>
                </div>
                <div class="card-body">
                    <div id="recentMeds" class="medications-list">
                        <div class="loading-spinner">
                            <div class="spinner"></div>
                            <p>Loading your medications...</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Medication Usage Chart -->
            <div class="card">
                <div class="card-header">
                    <h3>Medication Usage</h3>
                </div>
                <div class="card-body">
                    <canvas id="medicationChart" height="200"></canvas>
                </div>
            </div>

            <!-- Product Verification -->
            <div class="card">
                <div class="card-header">
                    <h3>Verify Medication</h3>
                </div>
                <div class="card-body">
                    <div class="verification-form">
                        <input type="text" id="productId" placeholder="Enter Product ID" class="form-input">
                        <button onclick="verifyProduct()" class="btn btn-primary">Verify</button>
                    </div>
                    <div id="verificationResult" class="verification-result"></div>
                </div>
            </div>
        </div>

        <div class="dashboard-header">
            <h2>Patient Dashboard</h2>
            <div class="user-info">
                <span class="user-name">Welcome, <?php echo htmlspecialchars($patient_name); ?></span>
                <span class="user-id">ID: <?php echo htmlspecialchars($patient_id); ?></span>
                <a href="?logout=true" class="logout-btn">Logout</a>
            </div>
        </div>

        <!-- Stats Overview -->
        <div class="stats-grid">
            <div class="stat-card">
                <svg class="icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M19.5 7A9 9 0 0 0 12 3a9 9 0 0 0-7.5 4"/>
                    <path d="M12 3v18"/>
                    <path d="M5 21h14"/>
                </svg>
                <h4>Active Medications</h4>
                <p>Track your current prescriptions</p>
            </div>
            
            <div class="stat-card">
                <svg class="icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M9 11H5a2 2 0 0 0-2 2v3c0 1.1.9 2 2 2h4m6-6h4a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2h-4m-6 0a2 2 0 0 0-2-2v-3a2 2 0 0 0 2-2m6 0a2 2 0 0 1 2-2v-3a2 2 0 0 1-2-2"/>
                </svg>
                <h4>Verified Products</h4>
                <p>Blockchain-verified medications</p>
            </div>
            
            <div class="stat-card">
                <svg class="icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                    <polyline points="14,2 14,8 20,8"/>
                    <line x1="16" y1="13" x2="8" y2="13"/>
                    <line x1="16" y1="17" x2="8" y2="17"/>
                    <polyline points="10,9 9,9 8,9"/>
                </svg>
                <h4>Medical History</h4>
                <p>Your complete medication records</p>
            </div>
        </div>

        <!-- Welcome Section -->
        <div class="card">
            <div class="welcome-message">
                <svg class="icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                    <circle cx="8.5" cy="7" r="4"/>
                    <line x1="20" y1="8" x2="20" y2="14"/>
                    <line x1="23" y1="11" x2="17" y2="11"/>
                </svg>
                <h3>Welcome to Your Patient Portal</h3>
                <p>
                    You have successfully logged in with secure OTP authentication. 
                    Your medical information is now protected with MedVeda's advanced security system. 
                    You can safely access your medication history, verify product authenticity, and manage your healthcare records.
                </p>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card">
            <div class="card-header">
                <svg class="icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="3"/>
                    <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1 1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/>
                </svg>
                <h3>Patient Services</h3>
            </div>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                <div style="background: rgba(0, 123, 255, 0.1); padding: 1.5rem; border-radius: 12px; text-align: center;">
                    <svg style="width: 32px; height: 32px; color: var(--primary-color); margin-bottom: 0.5rem;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M19.5 7A9 9 0 0 0 12 3a9 9 0 0 0-7.5 4"/>
                        <path d="M12 3v18"/>
                        <path d="M5 21h14"/>
                    </svg>
                    <h4 style="margin: 0.5rem 0; color: var(--secondary-color);">View Medications</h4>
                    <p style="margin: 0; color: var(--text-secondary); font-size: 0.9rem;">Access your current and past medications</p>
                </div>
                
                <div style="background: rgba(0, 123, 255, 0.1); padding: 1.5rem; border-radius: 12px; text-align: center;">
                    <svg style="width: 32px; height: 32px; color: var(--primary-color); margin-bottom: 0.5rem;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="9,11 12,14 22,4"/>
                        <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
                    </svg>
                    <h4 style="margin: 0.5rem 0; color: var(--secondary-color);">Verify Products</h4>
                    <p style="margin: 0; color: var(--text-secondary); font-size: 0.9rem;">Check medication authenticity via blockchain</p>
                </div>
                
                <div style="background: rgba(0, 123, 255, 0.1); padding: 1.5rem; border-radius: 12px; text-align: center;">
                    <svg style="width: 32px; height: 32px; color: var(--primary-color); margin-bottom: 0.5rem;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                        <polyline points="14,2 14,8 20,8"/>
                        <line x1="16" y1="13" x2="8" y2="13"/>
                        <line x1="16" y1="17" x2="8" y2="17"/>
                        <polyline points="10,9 9,9 8,9"/>
                    </svg>
                    <h4 style="margin: 0.5rem 0; color: var(--secondary-color);">Medical Records</h4>
                    <p style="margin: 0; color: var(--text-secondary); font-size: 0.9rem;">View your complete medical history</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Chart.js for data visualization -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <script>
        // Wait for the DOM to be fully loaded
        document.addEventListener('DOMContentLoaded', function() {
            // Fetch and display patient's medical history
            fetchMedicalHistory();
            
            // Fetch and display recent medications
            fetchRecentMedications();
            
            // Initialize charts
            initializeMedicationChart();
        });
        
        // Function to fetch and display medical history
        async function fetchMedicalHistory() {
            try {
                const response = await fetch('../api/get_patient_history.php');
                const data = await response.json();
                
                if (data.success) {
                    const historyContainer = document.getElementById('medicalHistory');
                    if (data.history && data.history.length > 0) {
                        const historyHTML = data.history.map(item => `
                            <div class="history-item">
                                <div class="history-date">${new Date(item.dispensed_at).toLocaleDateString()}</div>
                                <div class="history-details">
                                    <h4>${item.brand_name || 'Medication'}</h4>
                                    <p>Dosage: ${item.quantity} ${item.unit || 'units'}</p>
                                    <p>Batch: ${item.batch_number || 'N/A'}</p>
                                    <p>Dispensed by: ${item.dispensed_by || 'Pharmacist'}</p>
                                </div>
                            </div>
                        `).join('');
                        historyContainer.innerHTML = historyHTML;
                    } else {
                        historyContainer.innerHTML = '<p class="no-data">No medical history found.</p>';
                    }
                } else {
                    console.error('Error fetching medical history:', data.message);
                    document.getElementById('medicalHistory').innerHTML = 
                        '<p class="error">Error loading medical history. Please try again later.</p>';
                }
            } catch (error) {
                console.error('Error:', error);
                document.getElementById('medicalHistory').innerHTML = 
                    '<p class="error">Failed to load medical history. Please check your connection.</p>';
            }
        }
        
        // Function to fetch and display recent medications
        async function fetchRecentMedications() {
            try {
                const response = await fetch('../api/get_recent_medications.php');
                const data = await response.json();
                
                const medsContainer = document.getElementById('recentMeds');
                if (data.success && data.medications && data.medications.length > 0) {
                    const medsHTML = data.medications.map(med => `
                        <div class="medication-item">
                            <div class="med-name">${med.brand_name}</div>
                            <div class="med-details">
                                <span>Dosage: ${med.dosage || 'As prescribed'}</span>
                                <span>Next refill: ${med.next_refill ? new Date(med.next_refill).toLocaleDateString() : 'N/A'}</span>
                            </div>
                        </div>
                    `).join('');
                    medsContainer.innerHTML = medsHTML;
                } else {
                    medsContainer.innerHTML = '<p class="no-data">No recent medications found.</p>';
                }
            } catch (error) {
                console.error('Error fetching recent medications:', error);
            }
        }
        
        // Function to initialize medication usage chart
        function initializeMedicationChart() {
            const ctx = document.getElementById('medicationChart').getContext('2d');
            // Sample data - replace with actual data from API
            const data = {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                datasets: [{
                    label: 'Medication Usage',
                    data: [12, 19, 3, 5, 2, 3],
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            };
            
            new Chart(ctx, {
                type: 'bar',
                data: data,
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Number of Medications'
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Month'
                            }
                        }
                    }
                }
            });
        }
        
        // Function to verify a product
        async function verifyProduct() {
            const productId = document.getElementById('productId').value.trim();
            if (!productId) {
                alert('Please enter a product ID');
                return;
            }
            
            try {
                const response = await fetch(`../api/patient_verify.php?product_id=${encodeURIComponent(productId)}`);
                const data = await response.json();
                
                const resultDiv = document.getElementById('verificationResult');
                if (data.valid) {
                    resultDiv.innerHTML = `
                        <div class="verification-success">
                            <h4>✓ Product Verified</h4>
                            <p>Product: ${data.product_name || 'N/A'}</p>
                            <p>Batch: ${data.batch_number || 'N/A'}</p>
                            <p>Manufacturer: ${data.manufacturer || 'N/A'}</p>
                            <p>Expiry: ${data.expiry_date || 'N/A'}</p>
                        </div>
                    `;
                } else {
                    resultDiv.innerHTML = `
                        <div class="verification-failure">
                            <h4>✗ Product Not Found</h4>
                            <p>This product could not be verified in our system.</p>
                        </div>
                    `;
                }
            } catch (error) {
                console.error('Error verifying product:', error);
                document.getElementById('verificationResult').innerHTML = 
                    '<p class="error">Error verifying product. Please try again later.</p>';
            }
        }
    </script>
</body>
</html>
