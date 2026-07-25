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
    <title>CUMS - Manage Devices</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="topbar">
        <h2>Manage Devices</h2>
        <div class="admin-info">
            <a href="dashboard.php" style="color:#ffb703; margin-right:15px; text-decoration:none;">← Dashboard</a>
            <span><?php echo htmlspecialchars($_SESSION['username']); ?></span>
            <a href="logout.php">Logout</a>
        </div>
    </div>

    <div style="padding:20px;">
        <button id="addDeviceBtn" style="background:#1e2a4a;color:white;border:none;padding:10px 20px;border-radius:4px;cursor:pointer;">+ Add New Device</button>
    </div>

    <table class="user-table" style="width:calc(100% - 40px); margin:0 20px;">
        <thead>
            <tr><th>Name</th><th>IP</th><th>Vendor</th><th>Zone</th><th>Status</th><th>Action</th></tr>
        </thead>
        <tbody id="deviceTableBody"><tr><td colspan="6">Loading...</td></tr></tbody>
    </table>

    <!-- Add Device Modal -->
    <div id="addDeviceModal" class="modal-overlay" style="display:none;">
        <div class="modal-box">
            <h3>Add New Device</h3>
            <input type="text" id="newDeviceName" placeholder="Device Name">
            <input type="text" id="newDeviceIp" placeholder="IP Address">
            <select id="newDeviceVendor">
                <option value="">--Select Vendor--</option>
                <option value="huawei">Huawei</option>
                <option value="cisco">Cisco</option>
                <option value="mikrotik">MikroTik</option>
                <option value="juniper">Juniper</option>
                <option value="arista">Arista</option>
            </select>
            <input type="text" id="newDeviceModel" placeholder="Model (optional)">
            <select id="newDeviceZone">
                <option value="">--Select Zone--</option>
                <option value="dhaka">Dhaka</option>
                <option value="chittagong">Chittagong</option>
                <option value="khulna">Khulna</option>
                <option value="rajshahi">Rajshahi</option>
                <option value="arista">Arista</option>
            </select>
            <input type="text" id="newDeviceSshUser" placeholder="SSH Username">
            <input type="number" min="1"  id="newDeviceSshPort" placeholder="SSH Port (Default: 22)" value="22">
            <input type="password" id="newDeviceSshPass" placeholder="SSH Password">
            <p id="addDeviceError" style="color:red; font-size:13px;"></p>
            <div style="display:flex; gap:10px; margin-top:10px;">
                <button id="saveDeviceBtn" style="background:#2e7d32;color:white;border:none;padding:8px 16px;border-radius:4px;cursor:pointer;">Save</button>
                <button id="cancelDeviceBtn" style="background:#888;color:white;border:none;padding:8px 16px;border-radius:4px;cursor:pointer;">Cancel</button>
            </div>
        </div>
    </div>

    <script src="js/manage_devices.js?v=1"></script>
</body>
</html>