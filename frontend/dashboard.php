<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: index.html");
    exit;
}
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <title>CUMS - Dashboard</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="topbar">
        <h2>Central User Management System (Real-Time)</h2>
        <div class="admin-info">
            <span><?php echo htmlspecialchars($_SESSION['username']); ?> (<?php echo htmlspecialchars($_SESSION['role']); ?>)</span>
            <a href="logout.php">Logout</a>
        </div>
    </div>

    <div class="stats-cards">
        <div class="card">
            <h4>Connected Devices</h4>
            <p id="statDevices">--</p>
        </div>
        <div class="card">
            <h4>Live Users</h4>
            <p id="statUsers">--</p>
        </div>
        <div class="card">
            <h4>Today's Operations</h4>
            <p id="statOps">--</p>
        </div>
        <div class="card">
            <h4>Supported Vendors</h4>
            <p id="statVendors">--</p>
        </div>
    </div>

    <div class="search-panel">
        <select id="zoneSelect"><option value="">Select Zone</option></select>
        <select id="deviceSelect"><option value="">Select Device</option></select>
        <input type="text" id="searchInput" placeholder="Enter Username / IP">
        <button id="searchBtn">Search</button>
    </div>

    <table class="user-table">
        <thead>
            <tr>
                <th>#</th><th>Username</th><th>Device</th><th>IP</th>
                <th>Vendor</th><th>Zone</th><th>Status</th><th>Action</th>
            </tr>
        </thead>
        <tbody id="userTableBody">
            <tr><td colspan="8">লোড হচ্ছে...</td></tr>
        </tbody>
    </table>

    <script src="js/dashboard.js"></script>
</body>
</html>