<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - MedChain</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Roboto+Mono&display=swap" rel="stylesheet">
    <style>
        /* --- THEME APPLIED FROM INDEX.HTML --- */
        :root {
            --primary-color: #6a11cb;
            --primary-gradient: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            --primary-hover: #2575fc;
            --secondary-color: #1a2035;
            --bg-color: #f4f7fc;
            --card-bg: rgba(255, 255, 255, 0.75);
            --text-primary: #1a2035;
            --text-secondary: #6c757d;
            --border-color: #e2e8f0;
            --shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            --danger-color: #e63946;
            /* NEW COLOR FOR DISTRIBUTOR */
            --distributor-color: #009688; 
        }
        html, body {
            margin: 0; padding: 0;
            font-family: 'Poppins', sans-serif;
            background-color: var(--bg-color);
        }
        
        /* --- Main Content Area (Updated for full width) --- */
        .main-content {
            max-width: 1200px; /* Added for better readability on large screens */
            margin: 0 auto; /* Center the content */
            padding: 2.5rem; 
            box-sizing: border-box;
        }
        .card { 
            background-color: var(--card-bg); 
            padding: 2rem; 
            border-radius: 20px; 
            box-shadow: var(--shadow); 
            border: 1px solid var(--border-color);
            backdrop-filter: blur(10px);
        }
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2.5rem;
        }
        .page-header h2 { font-size: 2.25rem; font-weight: 700; margin: 0; color: var(--secondary-color); }
        
        /* --- NEW: Back Button Style --- */
        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            background: var(--primary-gradient);
            color: white;
            text-decoration: none;
            border-radius: 10px;
            font-weight: 500;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .btn-back:hover {
            transform: translateY(-2px);
            box-shadow: 0 7px 20px rgba(37, 117, 252, 0.2);
        }
        .btn-back svg {
            width: 20px;
            height: 20px;
        }
        
        /* --- User Table Styles --- */
        .user-table { width: 100%; border-collapse: collapse; }
        .user-table th, .user-table td {
            padding: 14px 15px;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
            vertical-align: middle;
        }
        .user-table th { background-color: #f8f9fa; font-weight: 600; color: var(--text-secondary); }
        .user-table tbody tr { transition: background-color 0.2s ease; }
        .user-table tbody tr:hover { background-color: #f5faff; }
        .user-table .role-badge {
            text-transform: capitalize;
            font-weight: 600;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            color: white;
            display: inline-block;
        }
        .role-admin { background-color: var(--danger-color); }
        .role-manufacturer { background-color: #522e8a; }
        /* ADDED: Style for Distributor Role */
        .role-distributor { background-color: var(--distributor-color); }
        .role-pharmacist { background-color: var(--primary-hover); }
        .role-patient { background-color: var(--text-secondary); }
    </style>
</head>
<body>
    <!-- REMOVED: Sidebar navigation has been removed -->

    <main class="main-content">
        <header class="page-header">
            <h2>User Management</h2>
            <!-- NEW: "Back to Dashboard" button added -->
            <a href="index.html" class="btn-back">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                </svg>
                <span>Back to Dashboard</span>
            </a>
        </header>

        <div class="card">
            <div style="overflow-x:auto;">
                <table class="user-table">
                    <thead>
                        <tr>
                            <th>User ID</th>
                            <th>Full Name</th>
                            <th>Username</th>
                            <th>Role</th>
                        </tr>
                    </thead>
                    <tbody id="users-tbody">
                        <!-- User data will be loaded here by JavaScript -->
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const tbody = document.getElementById('users-tbody');
            tbody.innerHTML = '<tr><td colspan="4" style="text-align:center; padding: 2rem;">Loading user data...</td></tr>';

            fetch('../api/get_users.php')
                .then(res => res.json())
                .then(data => {
                    tbody.innerHTML = ''; // Clear loading message
                    if (data.success && data.users.length > 0) {
                        data.users.forEach(user => {
                            const row = `
                                <tr>
                                    <td>${user.user_id}</td>
                                    <td>${user.full_name}</td>
                                    <td>${user.username}</td>
                                    <td><span class="role-badge role-${user.role}">${user.role}</span></td>
                                </tr>
                            `;
                            tbody.insertAdjacentHTML('beforeend', row);
                        });
                    } else {
                        tbody.innerHTML = '<tr><td colspan="4" style="text-align:center; padding: 2rem;">No users found.</td></tr>';
                    }
                })
                .catch(error => {
                    console.error('Fetch Error:', error);
                    tbody.innerHTML = '<tr><td colspan="4" style="text-align:center; color:red; padding: 2rem;">Failed to load user data.</td></tr>';
                });
        });
    </script>
</body>
</html>
