<?php
// You can add session validation here if needed for security
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Live Consignment Tracking - MedVeda</title>
    
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.css" />

    <style>
        :root {
            --primary-color: #007AFF; --primary-hover: #0056b3; --primary-glow: rgba(0, 122, 255, 0.2);
            --bg-color: #f0f2f5; --window-bg: #FFFFFF; --sidebar-bg: rgba(242, 242, 247, 0.95);
            --text-primary: #1D1D1F; --text-secondary: #6E6E73; --border-color: rgba(60, 60, 67, 0.22);
            --shadow: 0 10px 30px rgba(0, 0, 0, 0.1); --danger-color: #FF3B30;
            --success-bg: #F0FDF4; --success-text: #16A34A;
        }
        html, body { margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; background-color: var(--bg-color); color: var(--text-primary); }
        .macos-browser-window { max-width: 1600px; height: 95vh; margin: 1.5rem auto; border-radius: 12px; overflow: hidden; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); background-color: var(--window-bg); display: flex; flex-direction: column; border: 1px solid var(--border-color); }
        .macos-title-bar { background-color: #e8e8e8; padding: 12px; display: flex; align-items: center; border-bottom: 1px solid var(--border-color); flex-shrink: 0; }
        .macos-buttons { display: flex; gap: 8px; }
        .dot { width: 12px; height: 12px; border-radius: 50%; }
        .dot-red { background-color: #ff5f56; } .dot-yellow { background-color: #ffbd2e; } .dot-green { background-color: #27c93f; }
        .dashboard-body { display: flex; flex-grow: 1; position: relative; overflow: hidden; }
        .sidebar { width: 260px; background-color: var(--sidebar-bg); backdrop-filter: blur(20px); border-right: 1px solid var(--border-color); flex-shrink: 0; z-index: 1000; height: 100%; display: flex; flex-direction: column; }
        .sidebar-header { padding: 1.5rem; display: flex; align-items: center; gap: 12px; border-bottom: 1px solid var(--border-color); flex-shrink: 0; }
        .sidebar-header .logo-icon { width: 40px; height: 40px; }
        .sidebar-header h1 { font-size: 1.5rem; margin: 0; font-weight: 800; }
        .sidebar-nav { list-style: none; padding: 1.5rem 0; margin: 0; flex-grow: 1; overflow-y: auto; }
        .sidebar-nav ul { list-style: none; padding: 0; margin: 0; }
        .sidebar-nav a { display: flex; align-items: center; gap: 15px; padding: 0.8rem 1.5rem; margin: 0.25rem 1rem; color: var(--text-secondary); text-decoration: none; font-weight: 600; border-radius: 8px; transition: all 0.2s ease; }
        .sidebar-nav a:hover { background-color: rgba(128, 128, 128, 0.1); color: var(--primary-color); }
        .sidebar-nav a.active { background: var(--primary-color); color: #FFFFFF; font-weight: 700; box-shadow: 0 4px 12px var(--primary-glow); }
        .sidebar-nav a .icon { width: 22px; height: 22px; }
        .main-content { flex-grow: 1; padding: 2.5rem; box-sizing: border-box; height: 100%; overflow-y: auto; display: flex; flex-direction: column; }
        .card { background-color: var(--window-bg); padding: 1.5rem; border-radius: 12px; border: 1px solid var(--border-color); box-shadow: 0 2px 8px rgba(0,0,0,0.05); margin-bottom: 1.5rem; }
        .form-group label { display: block; margin-bottom: .5rem; font-weight: 700; }
        .form-group select, .form-group input { width: 100%; padding: .75rem; border: 1px solid var(--border-color); border-radius: 8px; box-sizing: border-box; background-color: #fefefe; font-family: inherit; }
        .form-group input:disabled { background-color: #f5f5f5; color: var(--text-secondary); }
        .btn-primary { background-color: var(--primary-color); color: white; padding: 0.75rem 1.5rem; border-radius: 8px; font-weight: 600; border: none; cursor: pointer; transition: all 0.2s ease; }
        .btn-primary:hover { background-color: var(--primary-hover); }
        .btn-primary:disabled { background-color: #ccc; cursor: not-allowed; }
        #map-container { flex-grow: 1; display: flex; flex-direction: column; border-radius: 12px; overflow: hidden; border: 1px solid var(--border-color); background-color: #f5f5f5; display: none; min-height: 60vh; }
        #map { flex-grow: 1; width: 100%; }
        .map-controls { padding: 1rem; background-color: #fff; border-top: 1px solid var(--border-color); display: flex; gap: 1rem; align-items: center; flex-shrink: 0; }
        .siren-indicator { display: none; padding: 0.5rem 1rem; background-color: var(--danger-color); color: white; font-weight: bold; border-radius: 8px; animation: pulse 1s infinite; }
        @keyframes pulse { 0% { transform: scale(1); } 70% { transform: scale(1.05); } 100% { transform: scale(1); } }
        .leaflet-routing-container { display: none; }
        .notification { display: none; padding: 1rem; margin-bottom: 1rem; border-radius: 8px; font-weight: 500; border: 1px solid transparent; }
        .notification.success { background-color: var(--success-bg); color: var(--success-text); border-color: var(--success-text); }
        .notification.error { background-color: #f8d7da; color: #721c24; border-color: #f5c6cb; }
        .notification.info { background-color: #e2e3e5; color: #383d41; border-color: #d6d8db; }
        #map-container.fullscreen { position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; z-index: 9999; border-radius: 0; margin: 0; min-height: 100vh; }
    </style>
</head>
<body>
    <div class="macos-browser-window">
        <div class="macos-title-bar">
            <div class="macos-buttons"><div class="dot dot-red"></div><div class="dot dot-yellow"></div><div class="dot dot-green"></div></div>
        </div>
        <div class="dashboard-body">
            <aside class="sidebar">
                <div class="sidebar-header"><img src="../assets/medchain_logo.svg" alt="MedChain Logo" class="logo-icon"><h1>MedVeda</h1></div>
                <nav class="sidebar-nav">
    <ul>
        <li><a href="index.html"><svg class="icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg><span>Dashboard</span></a></li>
        <li><a href="register.php"><svg class="icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg><span>Add Product</span></a></li>
        <li><a href="live_tracking.php" class="active"><svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg><span>Logistic & Tracking</span></a></li>
        <li><a href="history.php"><svg class="icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 4v6h6"/><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"/></svg><span>Full History</span></a></li>
        <li><a href="audit.php"><svg class="icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg><span>Audit Inspector</span></a></li>
        <li><a href="analysis.php"><svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg><span>Trace & Analysis</span></a></li>
        <li><a href="http://localhost/block/"><svg class="icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 22H5a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h7l5 5v2"/><path d="M15 18H9"/><path d="M15 22H9"/><path d="M12 14v8"/></svg><span>Home Page</span></a></li>
    </ul>
</nav>

            </aside>

            <main class="main-content">
                <header><h2 style="font-size: 2.25rem; font-weight: 800; margin: 0 0 2rem 0;">Logistics & Tracking</h2></header>
                
                <div class="card">
                    <h3>Dispense New Consignment</h3>
                    <div id="dispense-notification" class="notification"></div>
                    <form id="dispense-form" style="display: grid; grid-template-columns: 1fr 1fr auto; gap: 1rem; align-items: flex-end;">
                        <div class="form-group" style="margin:0;"><label for="product-select">Select Product:</label><select id="product-select" required></select></div>
                        <div class="form-group" style="margin:0;"><label for="dispense-quantity">Quantity:</label><input type="number" id="dispense-quantity" min="1" required></div>
                        <button type="submit" class="btn-primary">Dispense</button>
                    </form>
                </div>

                <div class="card">
                    <form id="tracking-form" style="display: flex; gap: 1rem; align-items: flex-end;">
                        <div class="form-group" style="flex-grow: 1; margin-bottom: 0;">
                            <label for="unique-identifier">Track Existing Consignment</label>
                            <input type="text" id="unique-identifier" placeholder="Enter Product Unique ID..." required>
                        </div>
                        <button type="submit" id="track-btn" class="btn-primary">Find</button>
                    </form>
                    <div id="tracking-notification" class="notification" style="margin-top: 1rem;"></div>
                </div>

                <div id="tracking-details-card" class="card" style="display: none;">
                    <h3 id="consignment-status-heading">Consignment Status</h3>
                    <div id="consignment-info"></div>
                    <form id="destination-form" style="display: none; margin-top: 1rem; gap: 1rem; align-items: flex-end;">
                         <div class="form-group" style="flex-grow: 1; margin-bottom: 0;">
                            <label for="destination-input">Enter Destination City</label>
                            <input type="text" id="destination-input" required placeholder="e.g., Belagavi">
                        </div>
                        <button type="submit" class="btn-primary">Track Route</button>
                    </form>
                </div>

                <div id="map-container">
                    <div id="map"></div>
                    <div class="map-controls">
                        <button id="start-sim-btn" class="btn-primary">Start Simulation</button>
                        <button id="wrong-turn-btn" class="btn-secondary">Simulate Wrong Turn</button>
                        <div id="siren-indicator" class="siren-indicator">ALERT: OFF ROUTE!</div>
                        <button id="fullscreen-btn" class="btn-secondary" style="margin-left: auto;">Maximize</button>
                        <button id="minimize-btn" class="btn-secondary" style="margin-left: auto; display: none;">Minimize</button>
                    </div>
                </div>

            </main>
        </div>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.js"></script>
    <script>
        let map = null, simulationInterval = null, audioContext = null, sirenOscillator = null, routingControl = null;
        let currentDistributor = null;
        const sourceLocation = { name: 'Medveda Pharmatical, Hubli', lat: 15.3647, lng: 75.1240 };

        document.addEventListener('DOMContentLoaded', function() {
            loadProductsForDispense();
            document.getElementById('tracking-form').addEventListener('submit', handleFindConsignment);
            document.getElementById('destination-form').addEventListener('submit', handleTrackRoute);
            document.getElementById('dispense-form').addEventListener('submit', handleDispenseSubmit);
            document.getElementById('fullscreen-btn').addEventListener('click', toggleFullscreen);
            document.getElementById('minimize-btn').addEventListener('click', toggleFullscreen);
        });

        async function handleFindConsignment(event) {
            event.preventDefault();
            const identifier = document.getElementById('unique-identifier').value;
            const trackBtn = document.getElementById('track-btn');
            trackBtn.disabled = true;
            trackBtn.textContent = 'Searching...';

            document.getElementById('tracking-details-card').style.display = 'none';
            document.getElementById('destination-form').style.display = 'none';
            document.getElementById('map-container').style.display = 'none';
            showNotification('tracking-notification', '', 'info', true);

            try {
                const response = await fetch(`../api/get_consignment_status.php?unique_identifier=${identifier}`);
                const data = await response.json();

                if (data.success) {
                    document.getElementById('tracking-details-card').style.display = 'block';
                    const statusHeading = document.getElementById('consignment-status-heading');
                    const infoDiv = document.getElementById('consignment-info');

                    if (data.status === 'delivered') {
                        statusHeading.textContent = `Status: Delivered`;
                        infoDiv.innerHTML = `<p>Product dispensed to your destination place by distributor <strong>${data.distributor.full_name}</strong>.</p>`;
                        document.getElementById('destination-form').style.display = 'none';
                    } else if (data.status === 'picked_up') {
                        statusHeading.textContent = `Status: In Transit`;
                        infoDiv.innerHTML = `<p>Product <strong>${data.product.brand_name} (Batch: ${data.product.batch_number})</strong> was picked up by <strong>${data.distributor.full_name}</strong>.</p>`;
                        currentDistributor = data.distributor;
                        document.getElementById('destination-form').style.display = 'flex';
                    } else if (data.status === 'pending_pickup') {
                        statusHeading.textContent = `Status: Pending Pickup`;
                        infoDiv.innerHTML = `<p>Product <strong>${data.product.brand_name} (Batch: ${data.product.batch_number})</strong> has been dispensed and is waiting for a distributor to pick it up.</p>`;
                    } else {
                         statusHeading.textContent = `Status: At Manufacturer`;
                         infoDiv.innerHTML = `<p>This product has not been dispensed to a distributor yet.</p>`;
                    }
                } else {
                    showNotification('tracking-notification', data.message || 'Could not find consignment.', 'error');
                }
            } catch (error) {
                showNotification('tracking-notification', 'A network error occurred.', 'error');
            } finally {
                trackBtn.disabled = false;
                trackBtn.textContent = 'Find';
            }
        }

        async function handleTrackRoute(event) {
            event.preventDefault();
            const destinationCity = document.getElementById('destination-input').value;
            const trackRouteBtn = event.target.querySelector('button[type="submit"]');
            
            if (!destinationCity) {
                alert('Please enter a destination city.');
                return;
            }

            trackRouteBtn.disabled = true;
            trackRouteBtn.textContent = 'Calculating...';

            try {
                const response = await fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(destinationCity)}`);
                const data = await response.json();
                if (data && data.length > 0) {
                    const destinationLocation = { name: data[0].display_name, lat: parseFloat(data[0].lat), lng: parseFloat(data[0].lon) };
                    await sendMessageToDistributor(destinationLocation.name);
                    await initializeMapWithRoute(sourceLocation, destinationLocation);
                } else {
                    alert('Could not find location for the entered city. Please be more specific.');
                }
            } catch (error) {
                alert('Geocoding service failed.');
            } finally {
                trackRouteBtn.disabled = false;
                trackRouteBtn.textContent = 'Track Route';
            }
        }
        
        async function sendMessageToDistributor(destinationName) {
            if (!currentDistributor || !currentDistributor.user_id) return;
            const uniqueId = document.getElementById('unique-identifier').value;
            const messageData = {
                sender_id: 1, 
                recipient_id: currentDistributor.user_id,
                subject: 'URGENT: New Delivery Instruction',
                message_content: `You have to deliver the consignment with Product ID: ${uniqueId} to the following destination: ${destinationName}.`,
                priority: 'Urgent'
            };
            try {
                const response = await fetch('../api/send_message.php', {
                    method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(messageData)
                });
                const result = await response.json();
                if (result.success) {
                    showNotification('tracking-notification', 'Delivery instruction sent to distributor!', 'success');
                } else { throw new Error(result.message); }
            } catch (error) {
                console.error("Messaging API error:", error);
                showNotification('tracking-notification', 'Could not send instruction to distributor.', 'error');
            }
        }

        async function initializeMapWithRoute(start, end) {
            const mapContainer = document.getElementById('map-container');
            mapContainer.style.display = 'flex';
            if (!map) {
                map = L.map('map');
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
            }
            if (routingControl) map.removeControl(routingControl);
            setTimeout(() => {
                map.invalidateSize();
                routingControl = L.routing.control({
                    waypoints: [L.latLng(start.lat, start.lng), L.latLng(end.lat, end.lng)],
                    routeWhileDragging: false, addWaypoints: false,
                    createMarker: (i, wp) => L.marker(wp.latLng, { icon: L.icon({ iconUrl: i === 0 ? 'https://img.icons8.com/office/40/000000/factory.png' : 'https://img.icons8.com/office/40/000000/clinic.png', iconSize: [40, 40] }) }).bindPopup(i === 0 ? `<b>Source:</b><br>${start.name}` : `<b>Destination:</b><br>${end.name}`),
                    lineOptions: { styles: [{color: 'blue', opacity: 0.6, weight: 6}] }
                }).addTo(map);
                routingControl.on('routesfound', e => {
                    map.fitBounds(L.latLngBounds(e.routes[0].coordinates));
                    initializeMapSimulation(e.routes[0].coordinates);
                });
            }, 200);
        }
        
        function toggleFullscreen() {
            const mapContainer = document.getElementById('map-container');
            const isFullscreen = mapContainer.classList.toggle('fullscreen');
            document.getElementById('fullscreen-btn').style.display = isFullscreen ? 'none' : 'block';
            document.getElementById('minimize-btn').style.display = isFullscreen ? 'block' : 'none';
            setTimeout(() => { if (map) map.invalidateSize(); }, 300);
        }

        async function loadProductsForDispense() {
            try {
                const response = await fetch('../api/get_products.php'); 
                const data = await response.json();
                const select = document.getElementById('product-select');
                select.innerHTML = '<option value="">Select a product...</option>';
                if (data.success && data.products) {
                    data.products.forEach(p => {
                        if (p.stock_quantity > 0) {
                            select.innerHTML += `<option value="${p.product_id}" data-max="${p.stock_quantity}">${p.brand_name} (Batch: ${p.batch_number}) - Avail: ${p.stock_quantity}</option>`;
                        }
                    });
                }
            } catch (error) { console.error('Error loading products:', error); }
        }

        document.getElementById('product-select').addEventListener('change', (e) => {
            const selected = e.target.options[e.target.selectedIndex];
            document.getElementById('dispense-quantity').max = selected.dataset.max || '';
        });

        async function handleDispenseSubmit(event) {
            event.preventDefault();
            const btn = event.target.querySelector('button[type="submit"]');
            btn.disabled = true;
            btn.textContent = '...';
            const productId = document.getElementById('product-select').value;
            const quantity = document.getElementById('dispense-quantity').value;
            try {
                const response = await fetch('../api/dispense_to_distributor.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ product_id: productId, quantity: quantity })
                });
                const data = await response.json();
                if (data.success) {
                    showNotification('dispense-notification', `Success! ${data.dispensed_quantity} units dispensed.`, 'success');
                    loadProductsForDispense();
                } else {
                    showNotification('dispense-notification', 'Error: ' + (data.message || 'Unknown error'), 'error');
                }
            } catch (error) {
                showNotification('dispense-notification', 'A critical network error occurred.', 'error');
            } finally {
                btn.disabled = false;
                btn.textContent = 'Dispense';
            }
        }
        
        function showNotification(elementId, message, type, clear = false) {
            const el = document.getElementById(elementId);
            if (clear) { el.style.display = 'none'; return; }
            el.style.display = 'block';
            el.textContent = message;
            el.className = `notification ${type}`;
        }

        function initializeMapSimulation(routeCoordinates) {
            if (simulationInterval) clearInterval(simulationInterval);
            const distributorIcon = L.divIcon({ className: 'leaflet-div-icon', html: `<img src="https://img.icons8.com/office/40/000000/truck.png">`, iconSize: [40, 40], iconAnchor: [20, 40] });
            const distributorMarker = L.marker(routeCoordinates[0], {icon: distributorIcon}).addTo(map);
            let step = 0;
            const totalSteps = routeCoordinates.length - 1;
            let isOffRoute = false;
            const startSimBtn = document.getElementById('start-sim-btn');
            const wrongTurnBtn = document.getElementById('wrong-turn-btn');
            startSimBtn.disabled = false;
            wrongTurnBtn.disabled = true;
            const startSimulation = () => {
                if(simulationInterval) clearInterval(simulationInterval);
                startSimBtn.disabled = true;
                wrongTurnBtn.disabled = false;
                simulationInterval = setInterval(() => {
                    if (step >= totalSteps) {
                        clearInterval(simulationInterval);
                        distributorMarker.setLatLng(routeCoordinates[totalSteps]);
                        stopSiren();
                        startSimBtn.disabled = false;
                        wrongTurnBtn.disabled = true;
                        return;
                    }
                    let currentPos = routeCoordinates[step];
                    if (isOffRoute) {
                        currentPos = L.latLng(currentPos.lat + 0.001, currentPos.lng + 0.001);
                        playSiren();
                    } else { stopSiren(); }
                    distributorMarker.setLatLng(currentPos);
                    map.panTo(currentPos, {animate: true, duration: 0.5});
                    step++;
                }, 100);
            };
            startSimBtn.onclick = startSimulation;
            wrongTurnBtn.onclick = () => { isOffRoute = !isOffRoute; };
        }
        function playSiren() {
            document.getElementById('siren-indicator').style.display = 'block';
            if (!audioContext) audioContext = new (window.AudioContext || window.webkitAudioContext)();
            if (sirenOscillator) return;
            sirenOscillator = audioContext.createOscillator();
            const gainNode = audioContext.createGain();
            sirenOscillator.connect(gainNode);
            gainNode.connect(audioContext.destination);
            sirenOscillator.type = 'sine';
            gainNode.gain.value = 0.1;
            sirenOscillator.frequency.setValueAtTime(800, audioContext.currentTime);
            sirenOscillator.frequency.linearRampToValueAtTime(1200, audioContext.currentTime + 0.5);
            sirenOscillator.start();
            sirenOscillator.loop = true;
        }
        function stopSiren() {
            document.getElementById('siren-indicator').style.display = 'none';
            if (sirenOscillator) {
                sirenOscillator.stop();
                sirenOscillator.disconnect();
                sirenOscillator = null;
            }
        }
    </script>
</body>
</html>
