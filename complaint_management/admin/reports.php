<?php
// admin/reports.php
session_start();
require_once '../config/db.php';
require_once '../includes/functions.php';
requireAdminOrAgent();

// Status distribution
$statusData = [];
$r = $conn->query("SELECT status, COUNT(*) as cnt FROM complaints GROUP BY status");
while ($row = $r->fetch_assoc()) $statusData[$row['status']] = (int)$row['cnt'];

// Category distribution
$catData = [];
$r = $conn->query("SELECT cat.name, COUNT(c.id) as cnt FROM complaints c JOIN categories cat ON c.category_id = cat.id GROUP BY cat.name ORDER BY cnt DESC");
while ($row = $r->fetch_assoc()) $catData[$row['name']] = (int)$row['cnt'];

// Monthly data (last 6 months)
$monthlyData = [];
$r = $conn->query("SELECT DATE_FORMAT(created_at, '%b %Y') as month, COUNT(*) as cnt FROM complaints WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH) GROUP BY DATE_FORMAT(created_at, '%Y-%m') ORDER BY created_at ASC");
while ($row = $r->fetch_assoc()) $monthlyData[$row['month']] = (int)$row['cnt'];

// Summary stats
$total    = array_sum($statusData);
$resolved = ($statusData['Resolved'] ?? 0) + ($statusData['Closed'] ?? 0);
$resRate  = $total > 0 ? round(($resolved / $total) * 100) : 0;
$agents   = $conn->query("SELECT COUNT(*) as c FROM users WHERE role='agent'")->fetch_assoc()['c'];

// Avg resolution days
$avgRes = $conn->query("SELECT AVG(DATEDIFF(updated_at, created_at)) as avg_days FROM complaints WHERE status IN ('Resolved','Closed')")->fetch_assoc()['avg_days'];
$avgRes = $avgRes ? round($avgRes, 1) : 'N/A';

// Priority distribution
$priData = [];
$r = $conn->query("SELECT priority, COUNT(*) as cnt FROM complaints GROUP BY priority");
while ($row = $r->fetch_assoc()) $priData[$row['priority']] = (int)$row['cnt'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Reports — ComplainX</title>
<link rel="stylesheet" href="../assets/css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.min.js"></script>
</head>
<body>
<button class="mobile-toggle" id="sidebarToggle" onclick="toggleSidebar()"><i class="fas fa-bars"></i></button>
<div class="app-wrapper">
<?php require_once '../includes/sidebar_admin.php'; ?>
<div class="main-content">

    <div class="page-header">
        <h2>Reports & Analytics</h2>
        <p>Visual overview of complaint data</p>
    </div>

    <!-- PROGRESS BARS -->
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:22px;">
        <div class="card-box" style="margin-bottom:0;">
            <div class="card-title" style="margin-bottom:18px;">Complaints by Status</div>
            <?php
            $statusColors = ['Pending'=>'#f59e0b','In Progress'=>'#3b82f6','Resolved'=>'#22c55e','Closed'=>'#9ca3af'];
            foreach ($statusData as $s => $count): ?>
            <div style="margin-bottom:14px;">
                <div style="display:flex;justify-content:space-between;margin-bottom:6px;">
                    <span style="font-size:13px;color:#374151;font-weight:500;"><?= $s ?></span>
                    <span style="font-size:13px;font-weight:700;color:<?= $statusColors[$s] ?? '#6366f1' ?>"><?= $count ?></span>
                </div>
                <div class="progress-bar-custom">
                    <div class="progress-fill" style="width:<?= $total > 0 ? round($count/$total*100) : 0 ?>%;background:<?= $statusColors[$s] ?? '#6366f1' ?>;"></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="card-box" style="margin-bottom:0;">
            <div class="card-title" style="margin-bottom:18px;">Complaints by Priority</div>
            <?php
            $priColors = ['High'=>'#ef4444','Medium'=>'#f59e0b','Low'=>'#22c55e'];
            foreach ($priData as $p => $count): ?>
            <div style="margin-bottom:14px;">
                <div style="display:flex;justify-content:space-between;margin-bottom:6px;">
                    <span style="font-size:13px;color:#374151;font-weight:500;"><?= $p ?></span>
                    <span style="font-size:13px;font-weight:700;color:<?= $priColors[$p] ?? '#6366f1' ?>"><?= $count ?></span>
                </div>
                <div class="progress-bar-custom">
                    <div class="progress-fill" style="width:<?= $total > 0 ? round($count/$total*100) : 0 ?>%;background:<?= $priColors[$p] ?? '#6366f1' ?>;"></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- CHARTS ROW -->
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:22px;">
        <div class="card-box" style="margin-bottom:0;">
            <div class="card-title" style="margin-bottom:16px;">Status Distribution</div>
            <div style="height:240px;position:relative;">
                <canvas id="statusChart"></canvas>
            </div>
        </div>
        <div class="card-box" style="margin-bottom:0;">
            <div class="card-title" style="margin-bottom:16px;">Complaints by Category</div>
            <div style="height:240px;position:relative;">
                <canvas id="categoryChart"></canvas>
            </div>
        </div>
    </div>

    <!-- MONTHLY TREND CHART -->
    <?php if (!empty($monthlyData)): ?>
    <div class="card-box" style="margin-bottom:22px;">
        <div class="card-title" style="margin-bottom:16px;">Monthly Trend (Last 6 Months)</div>
        <div style="height:200px;position:relative;">
            <canvas id="monthlyChart"></canvas>
        </div>
    </div>
    <?php endif; ?>

    <!-- SUMMARY STATS -->
    <div class="summary-gradient">
        <h3>Summary Statistics</h3>
        <div class="report-stat-grid">
            <div class="report-stat-item">
                <div class="report-stat-icon">📋</div>
                <div class="report-stat-val"><?= $total ?></div>
                <div class="report-stat-label">Total Filed</div>
            </div>
            <div class="report-stat-item">
                <div class="report-stat-icon">⏱</div>
                <div class="report-stat-val"><?= $avgRes ?> <span style="font-size:14px;">days</span></div>
                <div class="report-stat-label">Avg Resolution</div>
            </div>
            <div class="report-stat-item">
                <div class="report-stat-icon">📈</div>
                <div class="report-stat-val"><?= $resRate ?>%</div>
                <div class="report-stat-label">Resolution Rate</div>
            </div>
            <div class="report-stat-item">
                <div class="report-stat-icon">👥</div>
                <div class="report-stat-val"><?= $agents ?></div>
                <div class="report-stat-label">Active Agents</div>
            </div>
        </div>
    </div>

    <!-- CATEGORY TABLE -->
    <div class="card-box" style="margin-top:22px;">
        <div class="card-title" style="margin-bottom:16px;">Category Breakdown</div>
        <table class="table-modern">
            <thead><tr><th>Category</th><th>Count</th><th>Share</th><th>Bar</th></tr></thead>
            <tbody>
            <?php
            $catColors = ['#6366f1','#f59e0b','#22c55e','#ec4899','#3b82f6','#a855f7'];
            $ci = 0;
            foreach ($catData as $cat => $count): ?>
            <tr>
                <td style="font-weight:600;"><?= $cat ?></td>
                <td><strong style="color:<?= $catColors[$ci % count($catColors)] ?>"><?= $count ?></strong></td>
                <td style="color:#6b7280;"><?= $total > 0 ? round($count/$total*100) : 0 ?>%</td>
                <td style="min-width:120px;">
                    <div class="progress-bar-custom">
                        <div class="progress-fill" style="width:<?= $total > 0 ? round($count/$total*100) : 0 ?>%;background:<?= $catColors[$ci % count($catColors)] ?>;"></div>
                    </div>
                </td>
            </tr>
            <?php $ci++; endforeach; ?>
            </tbody>
        </table>
    </div>

</div>
</div>

<script src="../assets/js/main.js"></script>
<script>
const statusData   = <?= json_encode($statusData) ?>;
const categoryData = <?= json_encode($catData) ?>;
const monthlyData  = <?= json_encode($monthlyData) ?>;

// Initialize status + category charts
initCharts(statusData, categoryData);

// Monthly trend chart
const mCtx = document.getElementById('monthlyChart');
if (mCtx && Object.keys(monthlyData).length > 0) {
    new Chart(mCtx, {
        type: 'line',
        data: {
            labels: Object.keys(monthlyData),
            datasets: [{
                label: 'Complaints',
                data: Object.values(monthlyData),
                borderColor: '#6366f1',
                backgroundColor: 'rgba(99,102,241,.1)',
                borderWidth: 2.5,
                fill: true,
                tension: .4,
                pointBackgroundColor: '#6366f1',
                pointRadius: 5,
                pointHoverRadius: 7
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
</script>
</body>
</html>
