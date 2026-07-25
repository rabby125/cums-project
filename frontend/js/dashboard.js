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

        const zoneSelect = document.getElementById('zoneSelect');
        const zones = [...new Set(allDevices.map(d => d.zone))];

        zones.forEach(zone => {
            const opt = document.createElement('option');
            opt.value = zone;
            opt.textContent = zone;
            zoneSelect.appendChild(opt);
        });

        const deviceSelect = document.getElementById('deviceSelect');

        allDevices.forEach(d => {
            const opt = document.createElement('option');
            opt.value = d.id;
            opt.textContent = `${d.device_name} (${d.ip_address})`;
            deviceSelect.appendChild(opt);
        });

        zoneSelect.addEventListener('change', function () {
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

async function loadActivityLogs() {
    try {
        const res = await fetch('../backend/api/get_activity_logs.php');
        const data = await res.json();

        const container = document.getElementById('activityLogList');

        if (data.status !== 'success' || !data.logs || data.logs.length === 0) {
            container.innerHTML = '<p style="padding:15px;">No activity logs found.</p>';
            return;
        }

        container.innerHTML = data.logs.map(log => `
            <div class="activity-item">
                <span class="desc">
                    <strong>${log.admin_username || 'admin'}</strong> —
                    ${log.action} user <strong>${log.username_target}</strong>
                    on ${log.device_name || 'Unknown Device'}
                    (<span class="result-${log.result}">${log.result}</span>)
                </span>
                <span class="time">${log.created_at}</span>
            </div>
        `).join('');

    } catch (err) {
        document.getElementById('activityLogList').innerHTML =
            '<p style="padding:15px;">Failed to load activity logs.</p>';
    }
}

document.getElementById('searchBtn').addEventListener('click', async function () {

    const deviceId = document.getElementById('deviceSelect').value;
    const username = document.getElementById('searchInput').value.trim();
    const tableBody = document.getElementById('userTableBody');

    if (!deviceId || !username) {
        alert('Please select a device and enter a username.');
        return;
    }

    tableBody.innerHTML = '<tr><td colspan="8">Searching...</td></tr>';

    try {

        const res = await fetch('../backend/api/search_user.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                device_id: deviceId,
                username
            })
        });

        const result = await res.json();

        const device = allDevices.find(d => d.id == deviceId);

        let statusText =
            result.status === 'success'
                ? 'Active'
                : result.status === 'not_found'
                    ? 'Not Found'
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
            </tr>
        `;

        loadActivityLogs();

    } catch (err) {

        tableBody.innerHTML =
            `<tr><td colspan="8">Error: ${err.message}</td></tr>`;
    }

});

function getSearchContext() {
    return {
        deviceId: document.getElementById('deviceSelect').value,
        username: document.getElementById('searchInput').value.trim()
    };
}

document.getElementById('createBtn').addEventListener('click', function () {

    const { deviceId, username } = getSearchContext();

    if (!deviceId || !username) {
        return alert('Please select a device and enter a username.');
    }

    document.getElementById('createModalUsername').value = username;
    document.getElementById('createModalPassword').value = '';

    document.getElementById('createModal').style.display = 'flex';
});

document.getElementById('createModalCancel').addEventListener('click', () => {
    document.getElementById('createModal').style.display = 'none';
});

document.getElementById('createModalConfirm').addEventListener('click', async function () {

    const { deviceId } = getSearchContext();

    const username = document.getElementById('createModalUsername').value;
    const newPass = document.getElementById('createModalPassword').value;

    if (!newPass) {
        return alert('Please enter a password.');
    }

    const res = await fetch('../backend/api/create_user.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            device_id: deviceId,
            username,
            password: newPass
        })
    });

    const result = await res.json();

    alert(result.message || result.status);

    document.getElementById('createModal').style.display = 'none';

    loadActivityLogs();
});

document.getElementById('deleteBtn').addEventListener('click', async function () {

    const { deviceId, username } = getSearchContext();

    if (!deviceId || !username) {
        return alert('Please select a device and enter a username.');
    }

    if (!confirm(`Are you sure you want to delete ${username}?`)) return;

    const res = await fetch('../backend/api/delete_user.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            device_id: deviceId,
            username
        })
    });

    const result = await res.json();

    alert(result.message || result.status);

    loadActivityLogs();

});

document.getElementById('resetBtn').addEventListener('click', function () {

    const { deviceId, username } = getSearchContext();

    if (!deviceId || !username) {
        return alert('Please select a device and enter a username.');
    }

    document.getElementById('resetModalUsername').value = username;
    document.getElementById('resetModalPassword').value = '';

    document.getElementById('resetModal').style.display = 'flex';
});

document.getElementById('resetModalCancel').addEventListener('click', () => {
    document.getElementById('resetModal').style.display = 'none';
});

document.getElementById('resetModalConfirm').addEventListener('click', async function () {

    const { deviceId } = getSearchContext();

    const username = document.getElementById('resetModalUsername').value;
    const newPass = document.getElementById('resetModalPassword').value;

    if (!newPass) {
        return alert('Please enter a new password.');
    }

    const res = await fetch('../backend/api/edit_user.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            device_id: deviceId,
            username,
            new_password: newPass
        })
    });

    const result = await res.json();

    alert(result.message || result.status);

    document.getElementById('resetModal').style.display = 'none';

    loadActivityLogs();
});

loadDashboard();
loadDevices();
loadActivityLogs();

document.getElementById('listUsersBtn').addEventListener('click', async function () {

    const deviceId = document.getElementById('deviceSelect').value;
    const tableBody = document.getElementById('userTableBody');

    if (!deviceId) {
        return alert('Please select a device first.');
    }

    const device = allDevices.find(d => d.id == deviceId);

    tableBody.innerHTML =
        '<tr><td colspan="8">Loading user list...</td></tr>';

    try {

        const res = await fetch('../backend/api/list_users.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                device_id: deviceId
            })
        });

        const result = await res.json();

        if (
            result.status !== 'success' ||
            !result.users ||
            result.users.length === 0
        ) {
            tableBody.innerHTML =
                `<tr><td colspan="8">${result.message || 'No users found.'}</td></tr>`;
            return;
        }

        tableBody.innerHTML = result.users.map((u, i) => `
            <tr>
                <td>${i + 1}</td>
                <td>${u}</td>
                <td>${device.device_name}</td>
                <td>${device.ip_address}</td>
                <td>${device.vendor}</td>
                <td>${device.zone}</td>
                <td>Found</td>
                <td>—</td>
            </tr>
        `).join('');

    } catch (err) {

        tableBody.innerHTML =
            `<tr><td colspan="8">Error: ${err.message}</td></tr>`;
    }
});