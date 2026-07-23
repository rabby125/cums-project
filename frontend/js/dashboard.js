let allDevices = [];

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

async function loadDevices() {
    try {
        const res = await fetch('../backend/api/get_devices.php');
        const data = await res.json();

        if (data.status !== 'success') return;
        allDevices = data.devices;

        // Zone dropdown এ ইউনিক জোন বসানো
        const zoneSelect = document.getElementById('zoneSelect');
        const zones = [...new Set(allDevices.map(d => d.zone))];
        zones.forEach(zone => {
            const opt = document.createElement('option');
            opt.value = zone;
            opt.textContent = zone;
            zoneSelect.appendChild(opt);
        });

        // Zone বদলালে Device dropdown ফিল্টার হবে
        zoneSelect.addEventListener('change', function() {
            const deviceSelect = document.getElementById('deviceSelect');
            deviceSelect.innerHTML = '<option value="">Select Device</option>';

            const filtered = this.value
                ? allDevices.filter(d => d.zone === this.value)
                : allDevices;

            filtered.forEach(d => {
                const opt = document.createElement('option');
                opt.value = d.id;
                opt.textContent = `${d.device_name} (${d.ip_address})`;
                deviceSelect.appendChild(opt);
            });
        });

    } catch (err) {
        console.error('Device load failed:', err);
    }
}

loadDashboard();
loadDevices();
document.getElementById('searchBtn').addEventListener('click', async function() {
    const deviceId = document.getElementById('deviceSelect').value;
    const username = document.getElementById('searchInput').value.trim();
    const tableBody = document.getElementById('userTableBody');

    if (!deviceId || !username) {
        alert('Device এবং Username দুটোই দিতে হবে');
        return;
    }

    tableBody.innerHTML = '<tr><td colspan="8">সার্চ হচ্ছে...</td></tr>';

    try {
        const res = await fetch('../backend/api/search_user.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ device_id: deviceId, username: username })
        });
        const result = await res.json();

        const device = allDevices.find(d => d.id == deviceId);
        let statusText = result.status === 'success' ? 'Active'
                        : result.status === 'not_found' ? 'Not Found'
                        : 'Error';

        tableBody.innerHTML = `
            <tr>
                <td>1</td>
                <td>${username}</td>
                <td>${device.device_name}</td>
                <td>${device.ip_address}</td>
                <td>${device.vendor}</td>
                <td>${device.zone}</td>
                <td>${statusText}</td>
                <td>${result.message || ''}</td>
            </tr>`;

    } catch (err) {
        tableBody.innerHTML = `<tr><td colspan="8">এরর: ${err.message}</td></tr>`;
    }
});
function getSearchContext() {
    const deviceId = document.getElementById('deviceSelect').value;
    const username = document.getElementById('searchInput').value.trim();
    return { deviceId, username };
}

document.getElementById('createBtn').addEventListener('click', async function() {
    const { deviceId, username } = getSearchContext();
    if (!deviceId || !username) return alert('Device ও Username দিন');

    const newPass = prompt('নতুন ইউজারের পাসওয়ার্ড দিন:');
    if (!newPass) return;

    const res = await fetch('../backend/api/create_user.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ device_id: deviceId, username, password: newPass })
    });
    const result = await res.json();
    alert(result.message || result.status);
});

document.getElementById('deleteBtn').addEventListener('click', async function() {
    const { deviceId, username } = getSearchContext();
    if (!deviceId || !username) return alert('Device ও Username দিন');
    if (!confirm(`সত্যিই ${username} ডিলিট করতে চান?`)) return;

    const res = await fetch('../backend/api/delete_user.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ device_id: deviceId, username })
    });
    const result = await res.json();
    alert(result.message || result.status);
});

document.getElementById('resetBtn').addEventListener('click', async function() {
    const { deviceId, username } = getSearchContext();
    if (!deviceId || !username) return alert('Device ও Username দিন');

    const newPass = prompt('নতুন পাসওয়ার্ড দিন:');
    if (!newPass) return;

    const res = await fetch('../backend/api/edit_user.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ device_id: deviceId, username, new_password: newPass })
    });
    const result = await res.json();
    alert(result.message || result.status);
});