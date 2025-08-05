<?php
/**
 * Distributor Profile Page
 * This file uses the centralized session manager for robust authentication.
 */

// Use the centralized session manager to handle authentication
require_once '../otp-login/session_manager.php';

// This function will handle session start, validation, and redirection if the user is not authenticated
// or does not have the 'distributor' role.
$user = requireAuth('distributor');

// Get distributor info from the session (which is now guaranteed to be valid)
$user_data = getCurrentUser();

$distributor_id = $user_data['user_id'] ?? 'N/A';
$distributor_name = $user_data['full_name'] ?? 'Distributor';
$distributor_email = $user_data['email'] ?? 'N/A';

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
    <title>Profile - MedChain</title>
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
        
        .products-table { width: 100%; border-collapse: collapse; }
        .products-table th, .products-table td { padding: 1rem 1.5rem; text-align: left; border-bottom: 2px solid var(--border-color); vertical-align: middle; }
        .products-table th { font-weight: 700; color: var(--text-secondary); font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px; }
        .products-table tr:last-child td { border-bottom: none; }
        
        .btn { 
            padding: 0.6rem 1.2rem; 
            border-radius: 8px; 
            font-weight: 600; 
            border: none; 
            cursor: pointer; 
            transition: all 0.2s ease; 
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-align: center;
        }
        .btn-primary { background-color: var(--primary-color); color: white; }
        .btn-primary:hover { background-color: var(--primary-hover); }

        /* --- Professional Messaging Form Styles --- */
        .card-content { padding: 1.5rem; }
        .form-group { margin-bottom: 1.25rem; }
        .form-group label { margin-bottom: 0.5rem; font-weight: 700; color: var(--text-secondary); display: block; }
        .form-group select, .form-group input, .form-group textarea {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid var(--border-color);
            border-radius: 8px;
            font-family: inherit;
            font-size: 1rem;
            box-sizing: border-box;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .form-group select:focus, .form-group input:focus, .form-group textarea:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px var(--primary-glow);
        }
        .form-group textarea { resize: vertical; min-height: 120px; }
        .notification { display: none; padding: 1rem; margin-bottom: 1rem; border-radius: 8px; font-weight: 600; }
        .notification.success { background-color: #E8F5E9; color: #2E7D32; border: 2px solid #A5D6A7; }
        .notification.error { background-color: #FFEBEE; color: #C62828; border: 2px solid #EF9A9A; }
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
                    <h1>MedChain</h1>
                </div>
                <nav class="sidebar-nav">
<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
                    <ul>
                        <li><a href="distributor.php" class="<?php echo ($current_page == 'distributor.php' || $current_page == 'index.php') ? 'active' : ''; ?>"><svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg><span>Dashboard</span></a></li>
                        <!-- <li><a href="inventory.php" class="<?php echo ($current_page == 'inventory.php') ? 'active' : ''; ?>"><svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg><span>Inventory</span></a></li> -->
                        <li><a href="messages.php" class="<?php echo ($current_page == 'messages.php') ? 'active' : ''; ?>"><svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg><span>Messages</span></a></li>
                        <li><a href="profile.php" class="<?php echo ($current_page == 'profile.php') ? 'active' : ''; ?>"><svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg><span>Account</span></a></li>
                    </ul>
                </nav>
            </aside>

            <main class="main-content">
                <header class="page-header">
                    <h2>Profile</h2>
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

            <div class="card">
                <h3 class="card-header"><svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg> Your Profile</h3>
                <div class="card-content">
                    <p><strong>Name:</strong> <?php echo htmlspecialchars($distributor_name); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($distributor_email); ?></p>
                    <p><strong>Distributor ID:</strong> <?php echo htmlspecialchars($distributor_id); ?></p>
                </div>
            </div>

            <div class="card">
                <h3 class="card-header"><svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"></path><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path></svg> Update Profile</h3>
                <div class="card-content">
                    <form id="updateProfileForm">
                        <div class="form-group">
                            <label for="fullName">Full Name</label>
                            <input type="text" id="fullName" name="fullName" value="<?php echo htmlspecialchars($distributor_name); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($distributor_email); ?>" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Update Profile</button>
                    </form>
                    <div id="profileNotification" class="notification-container"></div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Smooth scrolling initialization
        const lenis = new Lenis({
            duration: 1.2,
            easing: (t) => Math.min(1, 1.001 - Math.pow(2, -10 * t)),
            direction: 'vertical',
            gestureDirection: 'vertical',
            smooth: true,
            mouseMultiplier: 1,
            smoothTouch: true,
            touchMultiplier: 2,
            infinite: false,
        });

        function raf(time) {
            lenis.raf(time);
            requestAnimationFrame(raf);
        }

        requestAnimationFrame(raf);

        // Function to show notifications
        function showNotification(message, type, elementId = 'profileNotification') {
            const notificationContainer = document.getElementById(elementId);
            if (!notificationContainer) return;

            const notification = document.createElement('div');
            notification.className = `notification ${type}`;
            notification.textContent = message;
            notificationContainer.appendChild(notification);

            setTimeout(() => {
                notification.classList.add('hide');
                notification.addEventListener('transitionend', () => {
                    notification.remove();
                });
            }, 3000);
        }

        // Handle profile update form submission
        document.getElementById('updateProfileForm').addEventListener('submit', async (event) => {
            event.preventDefault();

            const fullName = document.getElementById('fullName').value;
            const email = document.getElementById('email').value;

            if (!fullName || !email) {
                showNotification('Please fill in all fields.', 'error');
                return;
            }

            try {
                const response = await fetch('../api/update_profile.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ fullName, email })
                });

                const result = await response.json();

                if (result.success) {
                    showNotification(result.message, 'success');
                    // Optionally update the displayed name/email without page reload
                    document.querySelector('.user-info p').textContent = `Welcome, ${fullName}!`;
                    document.querySelector('.card-content p:first-child strong').nextSibling.textContent = ` ${fullName}`;
                    document.querySelector('.card-content p:nth-child(2) strong').nextSibling.textContent = ` ${email}`;
                } else {
                    showNotification(result.message, 'error');
                }
            } catch (error) {
                console.error('Error updating profile:', error);
                showNotification('An error occurred while updating your profile.', 'error');
            }
        });
    </script>
</body>
</html>