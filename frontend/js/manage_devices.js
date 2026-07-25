async function loadDeviceTable() {
    const tbody = document.getElementById('deviceTableBody');
    try {
        const res = await fetch('../backend/api/get_all_devices.php');
        const data = await res.json();
        if (data.status !== 'success') { tbody.innerHTML = '<tr><td colspan="6">লোড ব্যর্থ</td></tr>'; return; }

        tbody.innerHTML = data.devices.map(d => `
            <tr>
                <td>${d.device_name}</td>
                <td>${d.ip_address}</td>
                <td>${d.vendor}</td>
                <td>${d.zone}</td>
                <td>${d.status}</td>
                <td><button class="del-device-btn" data-id="${d.id}" style="background:#c62828;color:white;border:none;padding:5px 10px;border-radius:4px;cursor:pointer;">Delete</button></td>
            </tr>
        `).join('');

        document.querySelectorAll('.del-device-btn').forEach(btn => {
            btn.addEventListener('click', async function() {
                if (!confirm('সত্যিই এই ডিভাইসটি নিষ্ক্রিয় করতে চান?')) return;
                const res = await fetch('../backend/api/delete_device.php', {
                    method: 'POST', headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ device_id: this.dataset.id })
                });
                const result = await res.json();
                alert(result.message);
                loadDeviceTable();
            });
        });
    } catch (err) {
        tbody.innerHTML = '<tr><td colspan="6">এরর হয়েছে</td></tr>';
    }
}

document.getElementById('addDeviceBtn').addEventListener('click', () => {
    document.getElementById('addDeviceModal').style.display = 'flex';
});
document.getElementById('cancelDeviceBtn').addEventListener('click', () => {
    document.getElementById('addDeviceModal').style.display = 'none';
});

document.getElementById('saveDeviceBtn').addEventListener('click', async function() {
    const payload = {
        device_name: document.getElementById('newDeviceName').value.trim(),
        ip_address: document.getElementById('newDeviceIp').value.trim(),
        vendor: document.getElementById('newDeviceVendor').value,
        model: document.getElementById('newDeviceModel').value.trim(),
        zone: document.getElementById('newDeviceZone').value.trim(),
        ssh_username: document.getElementById('newDeviceSshUser').value.trim(),
        ssh_password: document.getElementById('newDeviceSshPass').value,
        ssh_port: document.getElementById('newDeviceSshPort').value.trim() || '22'
    };
    const errorEl = document.getElementById('addDeviceError');
    errorEl.textContent = '';

    const res = await fetch('../backend/api/add_device.php', {
        method: 'POST', headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    });
    const result = await res.json();
    if (result.status === 'success') {
        document.getElementById('addDeviceModal').style.display = 'none';
        ['newDeviceName','newDeviceIp','newDeviceVendor','newDeviceModel','newDeviceZone','newDeviceSshUser','newDeviceSshPass','newDeviceSshPort'].forEach(id => document.getElementById(id).value = '');
        loadDeviceTable();
    } else {
        errorEl.textContent = result.message;
    }
});

loadDeviceTable();