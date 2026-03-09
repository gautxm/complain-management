// assets/js/main.js

// ── Mobile Sidebar Toggle ──────────────────────
function toggleSidebar() {
    const sb = document.getElementById('sidebar');
    if (sb) sb.classList.toggle('open');
}
document.addEventListener('click', function(e) {
    const sb = document.getElementById('sidebar');
    const toggle = document.getElementById('sidebarToggle');
    if (sb && sb.classList.contains('open') && !sb.contains(e.target) && e.target !== toggle) {
        sb.classList.remove('open');
    }
});

// ── File Upload Label ──────────────────────────
function initFileUpload() {
    const input = document.getElementById('attachmentInput');
    const label = document.getElementById('uploadLabel');
    if (input && label) {
        input.addEventListener('change', function() {
            if (this.files.length > 0) {
                label.textContent = '📎 ' + this.files[0].name;
                label.style.borderColor = '#22c55e';
                label.style.background = '#f0fdf4';
                label.style.color = '#166534';
            }
        });
    }
}

// ── Auto-dismiss alerts ────────────────────────
function initAlerts() {
    const alerts = document.querySelectorAll('.alert-cx');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity .5s, max-height .5s';
            alert.style.opacity = '0';
            alert.style.maxHeight = '0';
            alert.style.overflow = 'hidden';
            alert.style.padding = '0';
            alert.style.marginBottom = '0';
        }, 4000);
    });
}

// ── Admin: Update Status via AJAX ─────────────
function updateStatus(complaintId, newStatus) {
    fetch(BASE_URL + '/admin/ajax_update.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'complaint_id=' + complaintId + '&status=' + encodeURIComponent(newStatus) + '&action=status'
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) showToast('Status updated to: ' + newStatus);
        else showToast('Update failed', 'error');
    })
    .catch(() => showToast('Network error', 'error'));
}

function updateAgent(complaintId, agentId) {
    fetch(BASE_URL + '/admin/ajax_update.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'complaint_id=' + complaintId + '&agent_id=' + agentId + '&action=agent'
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) showToast('Agent assigned successfully');
        else showToast('Assignment failed', 'error');
    });
}

// ── Toast Notification ─────────────────────────
function showToast(msg, type = 'success') {
    const existing = document.getElementById('cx-toast');
    if (existing) existing.remove();
    const toast = document.createElement('div');
    toast.id = 'cx-toast';
    toast.style.cssText = `
        position:fixed; bottom:24px; right:24px; z-index:9999;
        background: ${type === 'success' ? '#22c55e' : '#ef4444'};
        color:#fff; padding:12px 20px; border-radius:10px;
        font-family:'DM Sans',sans-serif; font-weight:600; font-size:14px;
        box-shadow:0 4px 14px rgba(0,0,0,.15); animation: fadeIn .3s ease;
    `;
    toast.textContent = (type === 'success' ? '✓ ' : '✗ ') + msg;
    document.body.appendChild(toast);
    setTimeout(() => { if (toast.parentNode) toast.remove(); }, 3000);
}

// ── Charts (Reports Page) ─────────────────────
function initCharts(statusData, categoryData) {
    if (typeof Chart === 'undefined') return;

    // Status Doughnut Chart
    const statusCtx = document.getElementById('statusChart');
    if (statusCtx) {
        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: Object.keys(statusData),
                datasets: [{
                    data: Object.values(statusData),
                    backgroundColor: ['#f59e0b','#3b82f6','#22c55e','#9ca3af'],
                    borderWidth: 3, borderColor: '#fff',
                    hoverOffset: 6
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { font: { family:'DM Sans', size: 12 }, padding: 14 } },
                    tooltip: {
                        bodyFont: { family: 'DM Sans' },
                        titleFont: { family: 'DM Sans' }
                    }
                },
                cutout: '68%'
            }
        });
    }

    // Category Bar Chart
    const catCtx = document.getElementById('categoryChart');
    if (catCtx) {
        new Chart(catCtx, {
            type: 'bar',
            data: {
                labels: Object.keys(categoryData),
                datasets: [{
                    label: 'Complaints',
                    data: Object.values(categoryData),
                    backgroundColor: ['#6366f1','#f59e0b','#22c55e','#ec4899','#3b82f6','#a855f7'],
                    borderRadius: 8, borderSkipped: false
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { display: false }, ticks: { font: { family: 'DM Sans', size: 12 } } },
                    y: {
                        beginAtZero: true, ticks: { stepSize: 1, font: { family: 'DM Sans', size: 12 } },
                        grid: { color: '#f3f4f6' }
                    }
                }
            }
        });
    }
}

// ── DOMContentLoaded ───────────────────────────
document.addEventListener('DOMContentLoaded', function() {
    initAlerts();
    initFileUpload();
});
