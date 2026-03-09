<?php
// admin/dashboard.php
session_start();
require_once '../config/db.php';
require_once '../includes/functions.php';
requireAdminOrAgent();

// Stats
$stats = [];
$r = $conn->query("SELECT
    COUNT(*) as total,
    SUM(status='Pending') as pending,
    SUM(status='In Progress') as inprogress,
    SUM(status='Resolved') as resolved,
    SUM(status='Closed') as closed
FROM complaints");
$stats = $r->fetch_assoc();

// Total users
$ucount = $conn->query("SELECT COUNT(*) as cnt FROM users WHERE role='user'")->fetch_assoc()['cnt'];

// Recent complaints (all)
$recent = $conn->query("
    SELECT c.*, cat.name as category_name, u.name as user_name, ag.name as agent_name
    FROM complaints c
    LEFT JOIN categories cat ON c.category_id = cat.id
    LEFT JOIN users u ON c.user_id = u.id
    LEFT JOIN users ag ON c.agent_id = ag.id
    ORDER BY c.created_at DESC LIMIT 6
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Dashboard — ComplainX</title>
<link rel="stylesheet" href="../assets/css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<button class="mobile-toggle" id="sidebarToggle" onclick="toggleSidebar()"><i class="fas fa-bars"></i></button>
<div class="app-wrapper">
<?php require_once '../includes/sidebar_admin.php'; ?>
<div class="main-content">

    <div class="page-header">
        <h2>Admin Dashboard</h2>
        <p>Overview of all complaints in the system</p>
    </div>

    <!-- STAT CARDS -->
    <div class="stat-cards">
        <div class="stat-card" style="border-color:#6366f1;">
            <div class="stat-icon" style="background:#ede9fe;"><i class="fas fa-clipboard-list" style="color:#6366f1;"></i></div>
            <div><div class="stat-value" style="color:#6366f1;"><?= $stats['total'] ?></div><div class="stat-label">Total Complaints</div></div>
        </div>
        <div class="stat-card" style="border-color:#f59e0b;">
            <div class="stat-icon" style="background:#fef9c3;"><i class="fas fa-hourglass-half" style="color:#f59e0b;"></i></div>
            <div><div class="stat-value" style="color:#f59e0b;"><?= $stats['pending'] ?></div><div class="stat-label">Pending</div></div>
        </div>
        <div class="stat-card" style="border-color:#3b82f6;">
            <div class="stat-icon" style="background:#dbeafe;"><i class="fas fa-sync-alt" style="color:#3b82f6;"></i></div>
            <div><div class="stat-value" style="color:#3b82f6;"><?= $stats['inprogress'] ?></div><div class="stat-label">In Progress</div></div>
        </div>
        <div class="stat-card" style="border-color:#22c55e;">
            <div class="stat-icon" style="background:#dcfce7;"><i class="fas fa-check-circle" style="color:#22c55e;"></i></div>
            <div><div class="stat-value" style="color:#22c55e;"><?= (int)$stats['resolved'] + (int)$stats['closed'] ?></div><div class="stat-label">Resolved / Closed</div></div>
        </div>
    </div>

    <!-- QUICK STATS ROW -->
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:22px;">
        <div class="card-box" style="margin-bottom:0;text-align:center;padding:18px;">
            <i class="fas fa-users" style="font-size:28px;color:#6366f1;margin-bottom:8px;display:block;"></i>
            <div style="font-size:24px;font-weight:800;color:#1e1b4b;"><?= $ucount ?></div>
            <div style="font-size:12px;color:#6b7280;">Registered Students</div>
        </div>
        <div class="card-box" style="margin-bottom:0;text-align:center;padding:18px;">
            <i class="fas fa-user-tie" style="font-size:28px;color:#f59e0b;margin-bottom:8px;display:block;"></i>
            <div style="font-size:24px;font-weight:800;color:#1e1b4b;">
                <?= $conn->query("SELECT COUNT(*) as c FROM users WHERE role='agent'")->fetch_assoc()['c'] ?>
            </div>
            <div style="font-size:12px;color:#6b7280;">Active Agents</div>
        </div>
        <div class="card-box" style="margin-bottom:0;text-align:center;padding:18px;">
            <i class="fas fa-percentage" style="font-size:28px;color:#22c55e;margin-bottom:8px;display:block;"></i>
            <div style="font-size:24px;font-weight:800;color:#1e1b4b;">
                <?= $stats['total'] > 0 ? round((($stats['resolved'] + $stats['closed']) / $stats['total']) * 100) : 0 ?>%
            </div>
            <div style="font-size:12px;color:#6b7280;">Resolution Rate</div>
        </div>
    </div>

    <!-- RECENT COMPLAINTS TABLE -->
    <div class="card-box">
        <div class="card-header-row">
            <span class="card-title">Recent Complaints</span>
            <a href="manage_complaints.php" class="card-link">Manage All →</a>
        </div>
        <div style="overflow-x:auto;">
        <table class="table-modern table-admin">
            <thead><tr>
                <th>ID</th><th>Title</th><th>Student</th><th>Category</th><th>Priority</th><th>Status</th><th>Date</th><th>Action</th>
            </tr></thead>
            <tbody>
            <?php while ($row = $recent->fetch_assoc()): ?>
            <tr>
                <td><span class="id-badge"><?= $row['complaint_no'] ?></span></td>
                <td style="font-weight:600;max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= htmlspecialchars($row['title']) ?></td>
                <td style="color:#6b7280;font-size:12px;"><?= htmlspecialchars($row['user_name']) ?></td>
                <td style="color:#6b7280;"><?= $row['category_name'] ?></td>
                <td><?= getPriorityBadge($row['priority']) ?></td>
                <td><?= getStatusBadge($row['status']) ?></td>
                <td style="color:#9ca3af;font-size:12px;"><?= date('d M Y', strtotime($row['created_at'])) ?></td>
                <td><a href="view_complaint.php?id=<?= $row['id'] ?>" class="btn-sm-cx btn-view"><i class="fas fa-eye"></i> View</a></td>
            </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
        </div>
    </div>

</div>
</div>
<script src="../assets/js/main.js"></script>
</body>
</html>
