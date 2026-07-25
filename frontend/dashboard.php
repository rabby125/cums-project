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
        <div class="card"><h4>Connected Devices</h4><p id="statDevices">--</p></div>
        <div class="card"><h4>Live Users</h4><p id="statUsers">--</p></div>
        <div class="card"><h4>Today's Operations</h4><p id="statOps">--</p></div>
        <div class="card"><h4>Supported Vendors</h4><p id="statVendors">--</p></div>
    </div>

    <div class="search-panel">
        <select id="zoneSelect"><option value="">Select Zone</option></select>
        <select id="deviceSelect"><option value="">Select Device</option></select>
        <input type="text" id="searchInput" placeholder="Enter Username / IP">
        <button id="searchBtn">Search</button>
    </div>

    <div class="action-panel"><a href="manage_devices.php" ><button id="addDeviceBtn" style="background:#1e2a4a;color:white;border:none;padding:10px 20px;border-radius:4px;cursor:pointer;">Manage Devices</button></a>
        <button id="createBtn">Create User</button>
        <button id="deleteBtn">Delete User</button>
        <button id="resetBtn">Reset Password</button>
        <button id="listUsersBtn" style="background:#0277bd;color:white;border:none;padding:8px 16px;border-radius:4px;cursor:pointer;">List All Users</button>
    </div>

    <table class="user-table">
        <thead>
            <tr><th>#</th><th>Username</th><th>Device</th><th>IP</th><th>Vendor</th><th>Zone</th><th>Status</th><th>Action</th></tr>
        </thead>
        <tbody id="userTableBody">
            <tr><td colspan="8">Loading...</td></tr>
        </tbody>
    </table>

    <div id="createModal" class="modal-overlay" style="display:none;">
        <div class="modal-box">
            <h3>Create New User</h3>
            <input type="text" id="createModalUsername" placeholder="Username" readonly>
            <input type="password" id="createModalPassword" placeholder="Password">
            <div style="display:flex; gap:10px; margin-top:10px;">
                <button id="createModalConfirm" style="background:#2e7d32;color:white;border:none;padding:8px 16px;border-radius:4px;cursor:pointer;">Create</button>
                <button id="createModalCancel" style="background:#888;color:white;border:none;padding:8px 16px;border-radius:4px;cursor:pointer;">Cancel</button>
            </div>
        </div>
    </div>

    <div id="resetModal" class="modal-overlay" style="display:none;">
        <div class="modal-box">
            <h3>Reset Password</h3>
            <input type="text" id="resetModalUsername" placeholder="Username" readonly>
            <input type="password" id="resetModalPassword" placeholder="New Password">
            <div style="display:flex; gap:10px; margin-top:10px;">
                <button id="resetModalConfirm" style="background:#ef6c00;color:white;border:none;padding:8px 16px;border-radius:4px;cursor:pointer;">Reset</button>
                <button id="resetModalCancel" style="background:#888;color:white;border:none;padding:8px 16px;border-radius:4px;cursor:pointer;">Cancel</button>
            </div>
        </div>
    </div>

    <div class="activity-section">
        <h3>Latest Activity Logs</h3>
        <div id="activityLogList" class="activity-list"><p>Loading...</p></div>
    </div>

    <script src="js/dashboard.js?v=3"></script>
</body>
</html>