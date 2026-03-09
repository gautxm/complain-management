<?php
// admin/manage_complaints.php
session_start();
require_once '../config/db.php';
require_once '../includes/functions.php';
requireAdminOrAgent();

// Filters
$filter   = $_GET['status']   ?? 'All';
$catFilter= $_GET['category'] ?? 'All';
$search   = sanitize($conn, $_GET['search'] ?? '');

$where = "WHERE 1=1";
if ($filter !== 'All')    { $f = $conn->real_escape_string($filter);    $where .= " AND c.status = '$f'"; }
if ($catFilter !== 'All') { $cf = $conn->real_escape_string($catFilter); $where .= " AND cat.name = '$cf'"; }
if (!empty($search))      { $where .= " AND (c.title LIKE '%$search%' OR c.complaint_no LIKE '%$search%' OR u.name LIKE '%$search%')"; }

$complaints = $conn->query("
    SELECT c.*, cat.name as category_name, u.name as user_name, ag.name as agent_name
    FROM complaints c
    LEFT JOIN categories cat ON c.category_id = cat.id
    LEFT JOIN users u ON c.user_id = u.id
    LEFT JOIN users ag ON c.agent_id = ag.id
    $where ORDER BY c.created_at DESC
");

$agents     = $conn->query("SELECT id, name FROM users WHERE role IN ('admin','agent') ORDER BY name");
$categories = $conn->query("SELECT DISTINCT name FROM categories ORDER BY name");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage Complaints — ComplainX</title>
<link rel="stylesheet" href="../assets/css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<button class="mobile-toggle" id="sidebarToggle" onclick="toggleSidebar()"><i class="fas fa-bars"></i></button>
<div class="app-wrapper">
<?php require_once '../includes/sidebar_admin.php'; ?>
<div class="main-content">

    <div class="page-header">
        <h2>Manage Complaints</h2>
        <p>Assign agents, update status, and add remarks</p>
    </div>

    <!-- SEARCH + FILTER BAR -->
    <div style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:16px;align-items:center;">
        <form method="GET" style="display:flex;gap:10px;flex-wrap:wrap;flex:1;">
            <input type="text" name="search" class="form-control-cx" placeholder="Search by title, ID, student..."
                value="<?= htmlspecialchars($search) ?>" style="max-width:260px;">
            <select name="category" class="form-select-cx" style="max-width:160px;">
                <option value="All">All Categories</option>
                <?php while ($cat = $categories->fetch_assoc()): ?>
                <option value="<?= $cat['name'] ?>" <?= $catFilter === $cat['name'] ? 'selected' : '' ?>><?= $cat['name'] ?></option>
                <?php endwhile; ?>
            </select>
            <input type="hidden" name="status" value="<?= htmlspecialchars($filter) ?>">
            <button type="submit" class="btn-sm-cx btn-save" style="padding:10px 18px;"><i class="fas fa-search"></i> Search</button>
            <a href="manage_complaints.php" class="btn-sm-cx btn-view" style="padding:10px 14px;"><i class="fas fa-times"></i> Clear</a>
        </form>
    </div>

    <!-- STATUS TABS -->
    <div class="filter-tabs">
        <?php foreach (['All','Pending','In Progress','Resolved','Closed'] as $tab): ?>
        <a href="manage_complaints.php?status=<?= urlencode($tab) ?>&category=<?= urlencode($catFilter) ?>&search=<?= urlencode($search) ?>"
           class="filter-tab <?= $filter === $tab ? 'active' : '' ?>"><?= $tab ?></a>
        <?php endforeach; ?>
    </div>

    <!-- TABLE -->
    <div class="card-box" style="overflow-x:auto;">
        <table class="table-modern table-admin">
            <thead><tr>
                <th>ID</th>
                <th>Title</th>
                <th>Student</th>
                <th>Category</th>
                <th>Priority</th>
                <th>Assign To</th>
                <th>Status</th>
                <th>Date</th>
                <th>Action</th>
            </tr></thead>
            <tbody>
            <?php if ($complaints->num_rows > 0): ?>
            <?php while ($c = $complaints->fetch_assoc()): ?>
            <tr>
                <td><span class="id-badge"><?= $c['complaint_no'] ?></span></td>
                <td style="font-weight:600;max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="<?= htmlspecialchars($c['title']) ?>"><?= htmlspecialchars($c['title']) ?></td>
                <td style="color:#6b7280;font-size:12px;"><?= htmlspecialchars($c['user_name']) ?></td>
                <td style="color:#6b7280;"><?= $c['category_name'] ?></td>
                <td><?= getPriorityBadge($c['priority']) ?></td>
                <td>
                    <select class="inline-select" onchange="updateAgent(<?= $c['id'] ?>, this.value)">
                        <option value="">Unassigned</option>
                        <?php $agents->data_seek(0); while ($ag = $agents->fetch_assoc()): ?>
                        <option value="<?= $ag['id'] ?>" <?= $c['agent_id'] == $ag['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($ag['name']) ?>
                        </option>
                        <?php endwhile; ?>
                    </select>
                </td>
                <td>
                    <select class="inline-select-action" onchange="updateStatus(<?= $c['id'] ?>, this.value)">
                        <?php foreach (['Pending','In Progress','Resolved','Closed'] as $s): ?>
                        <option value="<?= $s ?>" <?= $c['status'] === $s ? 'selected' : '' ?>><?= $s ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
                <td style="color:#9ca3af;font-size:12px;"><?= date('d M Y', strtotime($c['created_at'])) ?></td>
                <td><a href="view_complaint.php?id=<?= $c['id'] ?>" class="btn-sm-cx btn-view"><i class="fas fa-eye"></i></a></td>
            </tr>
            <?php endwhile; ?>
            <?php else: ?>
            <tr><td colspan="9" style="text-align:center;padding:40px;color:#9ca3af;">
                <i class="fas fa-inbox" style="font-size:36px;display:block;margin-bottom:12px;"></i> No complaints found.
            </td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>
</div>
<script>
const BASE_URL = '<?= BASE_URL ?>';
</script>
<script src="../assets/js/main.js"></script>
</body>
</html>
