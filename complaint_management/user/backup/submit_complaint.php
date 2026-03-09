<?php
// user/submit_complaint.php
session_start();
require_once '../config/db.php';
require_once '../includes/functions.php';
requireLogin();
if ($_SESSION['role'] !== 'user') { header("Location: ../admin/dashboard.php"); exit(); }

$error = '';
$success_no = '';

// Load categories
$categories = $conn->query("SELECT * FROM categories ORDER BY name");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title       = sanitize($conn, $_POST['title'] ?? '');
    $category_id = (int)($_POST['category_id'] ?? 0);
    $priority    = sanitize($conn, $_POST['priority'] ?? 'Medium');
    $description = sanitize($conn, $_POST['description'] ?? '');
    $uid         = $_SESSION['user_id'];

    if (empty($title) || empty($description) || $category_id === 0) {
        $error = "Title, category and description are required.";
    } else {
        // Handle file upload
        $attachment = null;
        if (!empty($_FILES['attachment']['name'])) {
            $allowed = ['jpg','jpeg','png','gif','pdf','doc','docx','txt'];
            $ext = strtolower(pathinfo($_FILES['attachment']['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, $allowed)) {
                $error = "File type not allowed. Use: jpg, png, pdf, doc, txt.";
            } elseif ($_FILES['attachment']['size'] > 2 * 1024 * 1024) {
                $error = "File size must be under 2MB.";
            } else {
                $filename   = uniqid('att_') . '.' . $ext;
                $uploadPath = '../uploads/' . $filename;
                if (move_uploaded_file($_FILES['attachment']['tmp_name'], $uploadPath)) {
                    $attachment = $filename;
                } else {
                    $error = "File upload failed. Check uploads/ folder permissions.";
                }
            }
        }

        if (empty($error)) {
            $complaint_no = generateComplaintNo($conn);
            $stmt = $conn->prepare("INSERT INTO complaints
                (complaint_no, user_id, title, category_id, description, attachment, priority, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, 'Pending')");
            $stmt->bind_param("sisissss", $complaint_no, $uid, $title, $category_id, $description, $attachment, $priority);
            if ($stmt->execute()) {
                $success_no = $complaint_no;
            } else {
                $error = "Database error. Please try again.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Submit Complaint — ComplainX</title>
<link rel="stylesheet" href="../assets/css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<button class="mobile-toggle" id="sidebarToggle" onclick="toggleSidebar()"><i class="fas fa-bars"></i></button>
<div class="app-wrapper">
<?php require_once '../includes/sidebar_user.php'; ?>
<div class="main-content">

<?php if ($success_no): ?>
<div class="success-state">
    <div class="success-icon">🎉</div>
    <h2 style="font-family:'Playfair Display',serif;font-size:24px;color:#1e1b4b;margin-bottom:8px;">Complaint Submitted!</h2>
    <p style="color:#6b7280;margin-bottom:8px;">Your complaint ID is <span class="success-id"><?= $success_no ?></span></p>
    <p style="color:#6b7280;font-size:14px;margin-bottom:28px;">You will be notified as the status is updated.</p>
    <div style="display:flex;gap:12px;flex-wrap:wrap;justify-content:center;">
        <a href="my_complaints.php" class="btn-sm-cx btn-save" style="padding:10px 22px;font-size:14px;"><i class="fas fa-list-ul"></i> View My Complaints</a>
        <a href="submit_complaint.php" class="btn-sm-cx btn-view" style="padding:10px 22px;font-size:14px;"><i class="fas fa-plus"></i> Submit Another</a>
    </div>
</div>
<?php else: ?>

<div class="page-header">
    <h2>Submit a Complaint</h2>
    <p>Fill in the details below and we'll look into it promptly</p>
</div>

<?php if ($error): ?>
<div class="alert-cx alert-danger"><i class="fas fa-exclamation-circle"></i> <?= $error ?></div>
<?php endif; ?>

<div class="card-box" style="max-width:640px;">
<form method="POST" action="" enctype="multipart/form-data">

    <div class="form-group">
        <label class="form-label"><i class="fas fa-heading"></i> Complaint Title *</label>
        <input type="text" name="title" class="form-control-cx"
            placeholder="Brief title describing your issue"
            value="<?= htmlspecialchars($_POST['title'] ?? '') ?>" required maxlength="200">
    </div>

    <div class="form-grid-2">
        <div class="form-group">
            <label class="form-label"><i class="fas fa-tag"></i> Category *</label>
            <select name="category_id" class="form-select-cx" required>
                <option value="">-- Select Category --</option>
                <?php $categories->data_seek(0); while ($cat = $categories->fetch_assoc()): ?>
                <option value="<?= $cat['id'] ?>"
                    <?= (isset($_POST['category_id']) && $_POST['category_id'] == $cat['id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($cat['name']) ?>
                </option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="form-group">
            <label class="form-label"><i class="fas fa-flag"></i> Priority *</label>
            <select name="priority" class="form-select-cx">
                <option value="Low"    <?= (($_POST['priority'] ?? '') === 'Low')    ? 'selected' : '' ?>>🟢 Low</option>
                <option value="Medium" <?= (($_POST['priority'] ?? 'Medium') === 'Medium') ? 'selected' : '' ?>>🟡 Medium</option>
                <option value="High"   <?= (($_POST['priority'] ?? '') === 'High')   ? 'selected' : '' ?>>🔴 High</option>
            </select>
        </div>
    </div>

    <div class="form-group">
        <label class="form-label"><i class="fas fa-align-left"></i> Description *</label>
        <textarea name="description" class="form-control-cx" rows="5"
            placeholder="Describe your issue in detail. Include when it happened, what was affected, etc."
            required><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
    </div>

    <div class="form-group">
        <label class="form-label"><i class="fas fa-paperclip"></i> Attachment (optional)</label>
        <div class="upload-zone" onclick="document.getElementById('attachmentInput').click()">
            <input type="file" name="attachment" id="attachmentInput" accept=".jpg,.jpeg,.png,.gif,.pdf,.doc,.docx,.txt">
            <div id="uploadLabel">
                <i class="fas fa-cloud-upload-alt" style="font-size:28px;margin-bottom:8px;display:block;"></i>
                <span>Click to attach screenshot or file</span><br>
                <small style="opacity:.7;">JPG, PNG, PDF, DOC — max 2MB</small>
            </div>
        </div>
    </div>

    <button type="submit" class="btn-primary-cx" style="width:100%;">
        <i class="fas fa-paper-plane"></i> Submit Complaint →
    </button>

</form>
</div>
<?php endif; ?>

</div>
</div>
<script src="../assets/js/main.js"></script>
</body>
</html>
