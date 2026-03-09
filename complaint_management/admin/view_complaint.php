<?php
// admin/view_complaint.php
session_start();
require_once '../config/db.php';
require_once '../includes/functions.php';
requireAdminOrAgent();

$id = (int)($_GET['id'] ?? 0);

$stmt = $conn->prepare("
    SELECT c.*, cat.name as category_name, u.name as user_name, u.email as user_email, u.phone as user_phone,
           ag.name as agent_name
    FROM complaints c
    LEFT JOIN categories cat ON c.category_id = cat.id
    LEFT JOIN users u ON c.user_id = u.id
    LEFT JOIN users ag ON c.agent_id = ag.id
    WHERE c.id = ?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$complaint = $stmt->get_result()->fetch_assoc();
if (!$complaint) { header("Location: manage_complaints.php"); exit(); }

$agents  = $conn->query("SELECT id, name FROM users WHERE role IN ('admin','agent') ORDER BY name");
$remarks = $conn->query("SELECT r.*, u.name as author_name, u.role as author_role FROM remarks r JOIN users u ON r.user_id = u.id WHERE r.complaint_id = $id ORDER BY r.created_at ASC");

// Handle form actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_status'])) {
        $new_status = sanitize($conn, $_POST['status']);
        $new_agent  = (int)($_POST['agent_id'] ?? 0);
        $conn->query("UPDATE complaints SET status='$new_status', agent_id=" . ($new_agent ?: 'NULL') . " WHERE id=$id");
        header("Location: view_complaint.php?id=$id&msg=updated"); exit();
    }
    if (isset($_POST['add_remark'])) {
        $comment = sanitize($conn, $_POST['comment'] ?? '');
        if (!empty($comment)) {
            $uid = $_SESSION['user_id'];
            $rs  = $conn->prepare("INSERT INTO remarks (complaint_id, user_id, comment) VALUES (?, ?, ?)");
            $rs->bind_param("iis", $id, $uid, $comment);
            $rs->execute();
        }
        header("Location: view_complaint.php?id=$id&msg=remarked"); exit();
    }
}

$steps = ['Pending' => 0, 'In Progress' => 1, 'Resolved' => 2, 'Closed' => 3];
$currentStep = $steps[$complaint['status']] ?? 0;
$stepLabels  = ['Submitted', 'Assigned', 'In Progress', 'Resolved'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $complaint['complaint_no'] ?> — Admin</title>
<link rel="stylesheet" href="../assets/css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<button class="mobile-toggle" id="sidebarToggle" onclick="toggleSidebar()"><i class="fas fa-bars"></i></button>
<div class="app-wrapper">
<?php require_once '../includes/sidebar_admin.php'; ?>
<div class="main-content">

    <div class="page-header">
        <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
            <a href="manage_complaints.php" style="color:#6366f1;font-size:14px;font-weight:600;text-decoration:none;">
                <i class="fas fa-arrow-left"></i> Back
            </a>
            <h2 style="margin-bottom:0;"><?= $complaint['complaint_no'] ?></h2>
            <?= getStatusBadge($complaint['status']) ?>
            <?= getPriorityBadge($complaint['priority']) ?>
        </div>
    </div>

    <?php if (isset($_GET['msg'])): ?>
    <div class="alert-cx alert-success"><i class="fas fa-check-circle"></i>
        <?= $_GET['msg'] === 'updated' ? 'Complaint updated successfully.' : 'Remark added.' ?>
    </div>
    <?php endif; ?>

    <div style="display:grid;grid-template-columns:2fr 1fr;gap:20px;">

        <!-- LEFT: Details + Remarks -->
        <div>
            <div class="card-box">
                <div class="card-title" style="margin-bottom:16px;"><?= htmlspecialchars($complaint['title']) ?></div>
                <p style="color:#374151;font-size:14px;line-height:1.7;margin-bottom:20px;"><?= nl2br(htmlspecialchars($complaint['description'])) ?></p>
                <?php if ($complaint['attachment']): ?>
                <a href="../uploads/<?= $complaint['attachment'] ?>" target="_blank"
                   style="display:inline-flex;align-items:center;gap:8px;background:#f8f7ff;border:1.5px solid #c7d2fe;border-radius:8px;padding:8px 14px;font-size:13px;color:#6366f1;text-decoration:none;">
                   <i class="fas fa-paperclip"></i> View Attachment
                </a>
                <?php endif; ?>

                <!-- PROGRESS -->
                <div class="tracker-banner" style="margin-top:22px;">
                    <div>
                        <div class="tracker-title">Complaint Progress</div>
                        <div class="tracker-steps" style="margin-top:14px;">
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
                    <div style="font-size:36px;">⚙️</div>
                </div>
            </div>

            <!-- REMARKS -->
            <div class="card-box">
                <div class="card-title" style="margin-bottom:20px;">💬 Remarks & Timeline</div>
                <?php if ($remarks->num_rows > 0): ?>
                <div class="remark-thread" style="margin-bottom:20px;">
                <?php while ($r = $remarks->fetch_assoc()): ?>
                    <div class="remark-item">
                        <div class="remark-avatar <?= in_array($r['author_role'],['admin','agent']) ? 'admin' : '' ?>">
                            <?= strtoupper(substr($r['author_name'], 0, 2)) ?>
                        </div>
                        <div class="remark-body">
                            <span class="remark-author"><?= htmlspecialchars($r['author_name']) ?></span>
                            <span class="remark-time"><?= timeAgo($r['created_at']) ?></span>
                            <?php if (in_array($r['author_role'],['admin','agent'])): ?>
                            <span style="font-size:10px;background:#fef3c7;color:#92400e;padding:1px 7px;border-radius:10px;font-weight:700;margin-left:4px;">Staff</span>
                            <?php endif; ?>
                            <div class="remark-text"><?= nl2br(htmlspecialchars($r['comment'])) ?></div>
                        </div>
                    </div>
                <?php endwhile; ?>
                </div>
                <?php else: ?>
                <p style="color:#9ca3af;font-size:14px;margin-bottom:20px;">No remarks yet.</p>
                <?php endif; ?>

                <form method="POST">
                    <label class="form-label">Add Staff Remark</label>
                    <textarea name="comment" class="form-control-cx" rows="3" placeholder="Add an update, internal note, or message to the student..."></textarea>
                    <button type="submit" name="add_remark" class="btn-sm-cx btn-save" style="margin-top:10px;padding:9px 20px;">
                        <i class="fas fa-paper-plane"></i> Add Remark
                    </button>
                </form>
            </div>
        </div>

        <!-- RIGHT: Manage + Student Info -->
        <div>
            <!-- UPDATE FORM -->
            <div class="card-box">
                <div class="card-title" style="margin-bottom:16px;">⚙️ Update Complaint</div>
                <form method="POST">
                    <div class="form-group">
                        <label class="form-label">Assign Agent</label>
                        <select name="agent_id" class="form-select-cx">
                            <option value="">Unassigned</option>
                            <?php $agents->data_seek(0); while ($ag = $agents->fetch_assoc()): ?>
                            <option value="<?= $ag['id'] ?>" <?= $complaint['agent_id'] == $ag['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($ag['name']) ?>
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Update Status</label>
                        <select name="status" class="form-select-cx">
                            <?php foreach (['Pending','In Progress','Resolved','Closed'] as $s): ?>
                            <option value="<?= $s ?>" <?= $complaint['status'] === $s ? 'selected' : '' ?>><?= $s ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" name="update_status" class="btn-primary-cx" style="width:100%;">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                </form>
            </div>

            <!-- STUDENT INFO -->
            <div class="card-box">
                <div class="card-title" style="margin-bottom:16px;">👤 Student Info</div>
                <div style="display:flex;flex-direction:column;gap:12px;">
                    <div class="detail-item"><label>Name</label><span><?= htmlspecialchars($complaint['user_name']) ?></span></div>
                    <div class="detail-item"><label>Email</label><span style="font-size:13px;"><?= htmlspecialchars($complaint['user_email']) ?></span></div>
                    <div class="detail-item"><label>Phone</label><span><?= htmlspecialchars($complaint['user_phone'] ?? 'N/A') ?></span></div>
                </div>
            </div>

            <!-- COMPLAINT META -->
            <div class="card-box">
                <div class="card-title" style="margin-bottom:16px;">📋 Details</div>
                <div style="display:flex;flex-direction:column;gap:12px;">
                    <div class="detail-item"><label>Category</label><span><?= $complaint['category_name'] ?></span></div>
                    <div class="detail-item"><label>Filed On</label><span><?= date('d M Y, h:i A', strtotime($complaint['created_at'])) ?></span></div>
                    <div class="detail-item"><label>Last Updated</label><span><?= timeAgo($complaint['updated_at']) ?></span></div>
                    <div class="detail-item"><label>Current Agent</label><span><?= $complaint['agent_name'] ?? '— Unassigned' ?></span></div>
                </div>
            </div>
        </div>

    </div>

</div>
</div>
<script src="../assets/js/main.js"></script>
</body>
</html>
