async function loadDashboard() {
    try {
        const res = await fetch('../backend/api/get_dashboard_data.php');
        const data = await res.json();

        document.getElementById('statDevices').textContent = data.total_devices ?? 0;
        document.getElementById('statUsers').textContent = data.total_users ?? 0;
        document.getElementById('statOps').textContent = data.total_ops ?? 0;
        document.getElementById('statVendors').textContent = data.total_vendors ?? 0;
    } catch (err) {
        console.error('Dashboard load failed:', err);
    }
}

loadDashboard();