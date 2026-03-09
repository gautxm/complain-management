<?php
// user/dashboard.php
session_start();
require_once '../config/db.php';
require_once '../includes/functions.php';
requireLogin();
if ($_SESSION['role'] !== 'user') { header("Location: ../admin/dashboard.php"); exit(); }

$uid = $_SESSION['user_id'];

// Stats
$stats = [];
foreach (['total' => '', 'pending' => "AND status='Pending'", 'inprogress' => "AND status='In Progress'", 'resolved' => "AND status='Resolved'"] as $key => $where) {
    $r = $conn->query("SELECT COUNT(*) as cnt FROM complaints WHERE user_id=$uid $where");
    $stats[$key] = $r->fetch_assoc()['cnt'];
}

// Recent complaints (latest 4)
$recent = $conn->query("SELECT c.*, cat.name as category_name, u.name as agent_name
    FROM complaints c
    LEFT JOIN categories cat ON c.category_id = cat.id
    LEFT JOIN users u ON c.agent_id = u.id
    WHERE c.user_id = $uid
    ORDER BY c.created_at DESC LIMIT 4");

// Last active complaint for tracker
$tracker = $conn->query("SELECT * FROM complaints WHERE user_id=$uid AND status NOT IN ('Closed','Resolved') ORDER BY created_at DESC LIMIT 1")->fetch_assoc();
if (!$tracker) {
    $tracker = $conn->query("SELECT * FROM complaints WHERE user_id=$uid ORDER BY created_at DESC LIMIT 1")->fetch_assoc();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard — ComplainX</title>
<link rel="stylesheet" href="../assets/css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<button class="mobile-toggle" id="sidebarToggle" onclick="toggleSidebar()">
    <i class="fas fa-bars"></i>
</button>
<div class="app-wrapper">
<?php require_once '../includes/sidebar_user.php'; ?>
<div class="main-content">

    <div class="page-header">
        <h2>Welcome back, <?= htmlspecialchars($_SESSION['name']) ?> 👋</h2>
        <p>Here's an overview of your complaint activity</p>
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
            <div><div class="stat-value" style="color:#22c55e;"><?= $stats['resolved'] ?></div><div class="stat-label">Resolved</div></div>
        </div>
    </div>

    <!-- RECENT COMPLAINTS TABLE -->
    <div class="card-box">
        <div class="card-header-row">
            <span class="card-title">Recent Complaints</span>
            <a href="my_complaints.php" class="card-link">View All →</a>
        </div>
        <?php if ($recent->num_rows > 0): ?>
        <div style="overflow-x:auto;">
        <table class="table-modern">
            <thead><tr>
                <th>ID</th><th>Title</th><th>Category</th><th>Priority</th><th>Status</th><th>Date</th><th>Action</th>
            </tr></thead>
            <tbody>
            <?php while ($row = $recent->fetch_assoc()): ?>
            <tr>
                <td><span class="id-badge"><?= $row['complaint_no'] ?></span></td>
                <td style="font-weight:600;color:#1e1b4b;max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= htmlspecialchars($row['title']) ?></td>
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
        <?php else: ?>
        <div style="text-align:center;padding:40px;color:#9ca3af;">
            <i class="fas fa-inbox" style="font-size:40px;margin-bottom:12px;display:block;"></i>
            No complaints yet. <a href="submit_complaint.php" style="color:#6366f1;font-weight:600;">Submit your first one!</a>
        </div>
        <?php endif; ?>
    </div>

    <!-- TRACKER BANNER -->
    <?php if ($tracker): ?>
    <?php
        $steps = ['Pending' => 0, 'In Progress' => 1, 'Resolved' => 2, 'Closed' => 3];
        $currentStep = $steps[$tracker['status']] ?? 0;
        $stepLabels = ['Submitted', 'Assigned', 'In Progress', 'Resolved'];
    ?>
    <div class="tracker-banner">
        <div>
            <div class="tracker-title">Live Complaint Tracker</div>
            <div class="tracker-id"><?= $tracker['complaint_no'] ?> — <?= htmlspecialchars($tracker['title']) ?></div>
            <div class="tracker-steps">
            <?php foreach ($stepLabels as $i => $label): ?>
                <div class="tracker-step">
                    <div class="step-circle <?= $i <= $currentStep ? 'done' : 'todo' ?>">
                        <?= $i <= $currentStep ? '✓' : ($i + 1) ?>
                    </div>
                    <span class="step-label" style="opacity:<?= $i <= $currentStep ? '1' : '.6' ?>;"><?= $label ?></span>
                </div>
                <?php if ($i < 3): ?>
                <span class="step-line <?= $i < $currentStep ? 'done-line' : 'todo-line' ?>"></span>
                <?php endif; ?>
            <?php endforeach; ?>
            </div>
        </div>
        <div style="font-size:48px;">🔍</div>
    </div>
    <?php endif; ?>

</div>
</div>
<script src="../assets/js/main.js"></script>
</body>
</html>
