<?php // includes/sidebar_user.php ?>
<div class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <div class="brand-logo">ComplainX</div>
        <div class="brand-sub">COMPLAINT MANAGEMENT</div>
    </div>
    <nav class="sidebar-nav">
        <a href="<?= BASE_URL ?>/user/dashboard.php"        class="nav-item <?= basename($_SERVER['PHP_SELF']) == 'dashboard.php'        ? 'active' : '' ?>"><i class="fas fa-th-large"></i> Dashboard</a>
        <a href="<?= BASE_URL ?>/user/submit_complaint.php" class="nav-item <?= basename($_SERVER['PHP_SELF']) == 'submit_complaint.php' ? 'active' : '' ?>"><i class="fas fa-plus-circle"></i> New Complaint</a>
        <a href="<?= BASE_URL ?>/user/my_complaints.php"    class="nav-item <?= basename($_SERVER['PHP_SELF']) == 'my_complaints.php'    ? 'active' : '' ?>"><i class="fas fa-list-ul"></i> My Complaints</a>
    </nav>
    <div class="sidebar-footer">
        <div class="user-info-sidebar">
            <div class="avatar-circle"><?= strtoupper(substr($_SESSION['name'], 0, 2)) ?></div>
            <div>
                <div class="user-name-sidebar"><?= htmlspecialchars($_SESSION['name']) ?></div>
                <div class="user-role-sidebar">Student / User</div>
            </div>
        </div>
        <a href="<?= BASE_URL ?>/logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
</div>
