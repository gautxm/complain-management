<?php
// user/my_complaints.php
session_start();
require_once '../config/db.php';
require_once '../includes/functions.php';
requireLogin();
if ($_SESSION['role'] !== 'user') { header("Location: ../admin/dashboard.php"); exit(); }

$uid    = $_SESSION['user_id'];
$filter = $_GET['status'] ?? 'All';
$search = sanitize($conn, $_GET['search'] ?? '');

$where = "WHERE c.user_id = $uid";
if ($filter !== 'All') {
    $f = $conn->real_escape_string($filter);
    $where .= " AND c.status = '$f'";
}
if (!empty($search)) {
    $where .= " AND (c.title LIKE '%$search%' OR c.complaint_no LIKE '%$search%')";
}

$complaints = $conn->query("
    SELECT c.*, cat.name as category_name, u.name as agent_name
    FROM complaints c
    LEFT JOIN categories cat ON c.category_id = cat.id
    LEFT JOIN users u ON c.agent_id = u.id
    $where
    ORDER BY c.created_at DESC
");

$statusColors = [
    'Pending'     => '#f5a623',
    'In Progress' => '#3b82f6',
    'Resolved'    => '#22c55e',
    'Closed'      => '#6b7280',
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Complaints — ComplainX</title>
<link rel="stylesheet" href="../assets/css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<button class="mobile-toggle" id="sidebarToggle" onclick="toggleSidebar()"><i class="fas fa-bars"></i></button>
<div class="app-wrapper">
<?php require_once '../includes/sidebar_user.php'; ?>
<div class="main-content">

    <div class="page-header">
        <h2>My Complaints</h2>
        <p>Track and manage all your submitted complaints</p>
    </div>

    <!-- SEARCH BAR -->
    <form method="GET" action="" style="margin-bottom:16px;display:flex;gap:10px;max-width:500px;">
        <input type="hidden" name="status" value="<?= htmlspecialchars($filter) ?>">
        <input type="text" name="search" class="form-control-cx" placeholder="Search by title or ID..."
            value="<?= htmlspecialchars($search) ?>" style="flex:1;">
        <button type="submit" class="btn-sm-cx btn-save" style="padding:10px 18px;"><i class="fas fa-search"></i></button>
        <?php if (!empty($search)): ?>
        <a href="my_complaints.php" class="btn-sm-cx btn-view" style="padding:10px 14px;"><i class="fas fa-times"></i></a>
        <?php endif; ?>
    </form>

    <!-- STATUS FILTER TABS -->
    <div class="filter-tabs">
        <?php foreach (['All','Pending','In Progress','Resolved','Closed'] as $tab): ?>
        <a href="my_complaints.php?status=<?= urlencode($tab) ?>&search=<?= urlencode($search) ?>"
           class="filter-tab <?= $filter === $tab ? 'active' : '' ?>"><?= $tab ?></a>
        <?php endforeach; ?>
        <a href="submit_complaint.php" class="filter-tab"
           style="background:linear-gradient(135deg,#6366f1,#818cf8);color:#fff;border-color:#6366f1;margin-left:auto;">
           <i class="fas fa-plus"></i> New Complaint
        </a>
    </div>

    <!-- COMPLAINT LIST -->
    <?php if ($complaints->num_rows > 0): ?>
    <?php while ($c = $complaints->fetch_assoc()): ?>
    <div class="complaint-card" style="border-left-color:<?= $statusColors[$c['status']] ?? '#6366f1' ?>;">
        <div style="flex:1;">
            <div class="complaint-card-meta">
                <span class="id-badge"><?= $c['complaint_no'] ?></span>
                <?= getPriorityBadge($c['priority']) ?>
                <span style="font-size:12px;color:#9ca3af;"><i class="fas fa-tag"></i> <?= $c['category_name'] ?></span>
            </div>
            <div class="complaint-card-title"><?= htmlspecialchars($c['title']) ?></div>
            <div class="complaint-card-sub">
                <i class="fas fa-calendar-alt"></i> <?= date('d M Y', strtotime($c['created_at'])) ?>
                &nbsp;·&nbsp;
                <i class="fas fa-user-cog"></i> Agent: <?= $c['agent_name'] ?? 'Unassigned' ?>
            </div>
        </div>
        <div class="complaint-card-actions">
            <?= getStatusBadge($c['status']) ?>
            <a href="view_complaint.php?id=<?= $c['id'] ?>" class="btn-sm-cx btn-view">
                <i class="fas fa-eye"></i> View
            </a>
        </div>
    </div>
    <?php endwhile; ?>
    <?php else: ?>
    <div class="card-box" style="text-align:center;padding:60px 20px;">
        <i class="fas fa-inbox" style="font-size:48px;color:#c7d2fe;display:block;margin-bottom:16px;"></i>
        <h3 style="font-family:'Playfair Display',serif;color:#1e1b4b;margin-bottom:8px;">No complaints found</h3>
        <p style="color:#6b7280;font-size:14px;">
            <?= $filter !== 'All' ? "No \"$filter\" complaints." : "You haven't submitted any complaints yet." ?>
        </p>
        <a href="submit_complaint.php" class="btn-primary-cx" style="display:inline-block;margin-top:16px;text-decoration:none;">
            <i class="fas fa-plus"></i> Submit a Complaint
        </a>
    </div>
    <?php endif; ?>

</div>
</div>
<script src="../assets/js/main.js"></script>
</body>
</html>
