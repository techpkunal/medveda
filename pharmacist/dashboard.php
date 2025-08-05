<?php
// Include authentication check
require_once 'auth_check.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pharmacist Dashboard - MedVeda</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Roboto+Mono:wght@400;500&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #28a745;
            --primary-hover: #218838;
            --secondary-color: #394264;
            --bg-color: #F0F4F8;
            --sidebar-bg: #FFFFFF;
            --card-bg: rgba(255, 255, 255, 0.65);
            --text-primary: #212529;
            --text-secondary: #6c757d;
            --border-color: #E2E8F0;
            --shadow: 0 7px 25px rgba(0, 0, 0, 0.07);
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

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
        
        .sidebar-nav a.active {
            background: linear-gradient(90deg, var(--primary-color), var(--primary-hover));
            color: #FFFFFF;
            font-weight: 600;
        }

        .main-content {
            margin-left: 260px;
            padding: 2.5rem;
            flex: 1;
            min-height: 100vh;
            animation: fadeInUp 0.6s ease-out forwards;
        }

        .dashboard-header { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            margin-bottom: 2rem; 
            padding-bottom: 1rem; 
            border-bottom: 2px solid var(--border-color); 
        }

        .dashboard-header h2 { 
            font-size: 2rem; 
            font-weight: 700; 
            color: var(--secondary-color); 
            margin: 0; 
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .user-name {
            color: var(--text-secondary);
            font-weight: 500;
        }

        .logout-btn {
            background: linear-gradient(135deg, #dc3545, #c82333);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .logout-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(220, 53, 69, 0.3);
        }

        .card {
            background: var(--card-bg);
            border-radius: 16px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: var(--shadow);
            border: 1px solid rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px); 
            -webkit-backdrop-filter: blur(10px); 
            transition: all 0.3s ease-in-out; 
            animation: fadeInUp 0.6s ease-out forwards;
        }

        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 28px rgba(0, 0, 0, 0.12);
        }

        .card-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 1.5rem;
        }

        .card-header .icon {
            width: 24px;
            height: 24px;
            color: var(--primary-color);
        }

        .card-header h3 {
            font-size: 1.3rem;
            font-weight: 600;
            color: var(--secondary-color);
            margin: 0;
        }

        .welcome-message {
            text-align: center;
            padding: 3rem;
            color: var(--text-secondary);
        }

        .welcome-message .icon {
            width: 64px;
            height: 64px;
            margin-bottom: 1rem;
            color: var(--primary-color);
        }

        .welcome-message h3 {
            font-size: 1.5rem;
            margin-bottom: 1rem;
            color: var(--secondary-color);
        }

        .welcome-message p {
            font-size: 1rem;
            line-height: 1.6;
            max-width: 600px;
            margin: 0 auto;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <svg class="logo-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M19.5 7A9 9 0 0 0 12 3a9 9 0 0 0-7.5 4"/>
                <path d="M12 3v18"/>
                <path d="M5 21h14"/>
            </svg>
            <h1>MedVeda</h1>
        </div>
        
        <nav class="sidebar-nav">
            <a href="#" class="active">
                <svg class="icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                    <polyline points="9,22 9,12 15,12 15,22"/>
                </svg>
                Dashboard
            </a>
            <a href="#">
                <svg class="icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M19.5 7A9 9 0 0 0 12 3a9 9 0 0 0-7.5 4"/>
                    <path d="M12 3v18"/>
                    <path d="M5 21h14"/>
                </svg>
                Pharmacy
            </a>
            <a href="#">
                <svg class="icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="3.27,6.96 12,12.01 20.73,6.96"/>
                    <line x1="12" y1="22.08" x2="12" y2="12"/>
                </svg>
                Blockchain
            </a>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="dashboard-header">
            <h2>Pharmacist Dashboard</h2>
            <div class="user-info">
                <span class="user-name">Welcome, <?php echo htmlspecialchars($current_user['full_name'] ?? 'Pharmacist'); ?></span>
                <button class="logout-btn" onclick="logout()">Logout</button>
            </div>
        </div>

        <!-- Welcome Section -->
        <div class="card">
            <div class="welcome-message">
                <svg class="icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M19.5 7A9 9 0 0 0 12 3a9 9 0 0 0-7.5 4"/>
                    <path d="M12 3v18"/>
                    <path d="M5 21h14"/>
                </svg>
                <h3>Welcome to Your Pharmacist Dashboard</h3>
                <p>
                    You have successfully logged in with secure OTP authentication. 
                    Your account is now protected with MedVeda's advanced security system. 
                    You can now access all pharmacist features and manage your pharmaceutical operations securely.
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
                <h3>Quick Actions</h3>
            </div>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                <div style="background: rgba(40, 167, 69, 0.1); padding: 1.5rem; border-radius: 12px; text-align: center;">
                    <svg style="width: 32px; height: 32px; color: var(--primary-color); margin-bottom: 0.5rem;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M19.5 7A9 9 0 0 0 12 3a9 9 0 0 0-7.5 4"/>
                        <path d="M12 3v18"/>
                        <path d="M5 21h14"/>
                    </svg>
                    <h4 style="margin: 0.5rem 0; color: var(--secondary-color);">Manage Inventory</h4>
                    <p style="margin: 0; color: var(--text-secondary); font-size: 0.9rem;">Track and manage your pharmaceutical inventory</p>
                </div>
                
                <div style="background: rgba(40, 167, 69, 0.1); padding: 1.5rem; border-radius: 12px; text-align: center;">
                    <svg style="width: 32px; height: 32px; color: var(--primary-color); margin-bottom: 0.5rem;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                        <circle cx="8.5" cy="7" r="4"/>
                        <line x1="20" y1="8" x2="20" y2="14"/>
                        <line x1="23" y1="11" x2="17" y2="11"/>
                    </svg>
                    <h4 style="margin: 0.5rem 0; color: var(--secondary-color);">Patient Records</h4>
                    <p style="margin: 0; color: var(--text-secondary); font-size: 0.9rem;">Access and manage patient medication records</p>
                </div>
                
                <div style="background: rgba(40, 167, 69, 0.1); padding: 1.5rem; border-radius: 12px; text-align: center;">
                    <svg style="width: 32px; height: 32px; color: var(--primary-color); margin-bottom: 0.5rem;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="3.27,6.96 12,12.01 20.73,6.96"/>
                        <line x1="12" y1="22.08" x2="12" y2="12"/>
                    </svg>
                    <h4 style="margin: 0.5rem 0; color: var(--secondary-color);">Blockchain Verify</h4>
                    <p style="margin: 0; color: var(--text-secondary); font-size: 0.9rem;">Verify product authenticity on blockchain</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        async function logout() {
            if (confirm('Are you sure you want to logout?')) {
                try {
                    const response = await fetch('../otp-login/session_manager.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            action: 'logout'
                        })
                    });

                    const data = await response.json();
                    
                    if (data.status === 'success') {
                        window.location.href = '../otp-login/login_medchain.html';
                    } else {
                        // Force redirect even if API call fails
                        window.location.href = '../otp-login/login_medchain.html';
                    }
                } catch (error) {
                    console.error('Logout error:', error);
                    // Force redirect even if there's an error
                    window.location.href = '../otp-login/login_medchain.html';
                }
            }
        }
    </script>
</body>
</html>
