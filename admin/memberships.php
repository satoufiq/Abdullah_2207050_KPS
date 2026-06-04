<?php
require_once '../config.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
    header('Location: ../login.php');
    exit;
}

$page_title = 'Membership Applications';
include '../header.php';
include '../navbar.php';
?>

    <section class="admin-section">
        <div class="section-heading">
            <h2>Membership Applications</h2>
            <p>Review and approve or reject pending membership applications.</p>
        </div>

        <div class="admin-content">
            <div id="apps-response"></div>
            <table class="admin-table" id="apps-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Experience</th>
                        <th>Interests</th>
                        <th>Message</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </section>

    <script>
    async function loadApps() {
        const res = await fetch('../api/admin/get_membership_applications.php');
        const data = await res.json();
        const tbody = document.querySelector('#apps-table tbody');
        tbody.innerHTML = '';
        if (!data.success) {
            document.getElementById('apps-response').innerText = data.error || 'Failed to load';
            return;
        }
        data.applications.forEach((app, idx) => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${idx+1}</td>
                <td>${escapeHtml(app.name)}</td>
                <td>${escapeHtml(app.email)}</td>
                <td>${escapeHtml(app.experience || '')}</td>
                <td>${(app.interests || []).map(i=>escapeHtml(i)).join(', ')}</td>
                <td>${escapeHtml(app.message || '')}</td>
                <td>${escapeHtml(app.status)}</td>
                <td>
                    <input placeholder="Position (optional)" class="pos-input" data-id="${app.id}">
                    <button onclick="decide(${app.id}, 'approve')">Approve</button>
                    <button onclick="decide(${app.id}, 'reject')">Reject</button>
                </td>
            `;
            tbody.appendChild(tr);
        });
    }

    function escapeHtml(str) {
        if (!str) return '';
        return String(str).replace(/[&<>"'`]/g, function (s) { return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;', '`':'&#96;'})[s]; });
    }

    async function decide(id, action) {
        const input = document.querySelector('.pos-input[data-id="'+id+'"]');
        const position = input ? input.value.trim() : '';
        const res = await fetch('../api/admin/decide_membership.php', {
            method: 'POST',
            headers: {'Content-Type':'application/json'},
            body: JSON.stringify({application_id: id, action: action, position: position})
        });
        const data = await res.json();
        if (data.success) {
            loadApps();
        } else {
            alert(data.error || 'Action failed');
        }
    }

    loadApps();
    </script>

<?php include '../footer.php'; ?>
