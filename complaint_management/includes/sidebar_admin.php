<?php // includes/sidebar_admin.php ?>
<div class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <div class="brand-logo">ComplainX</div>
        <div class="brand-sub">ADMIN PANEL</div>
    </div>
    <nav class="sidebar-nav">
        <a href="<?= BASE_URL ?>/admin/dashboard.php"           class="nav-item <?= basename($_SERVER['PHP_SELF']) == 'dashboard.php'           ? 'active' : '' ?>"><i class="fas fa-th-large"></i> Dashboard</a>
        <a href="<?= BASE_URL ?>/admin/manage_complaints.php"   class="nav-item <?= basename($_SERVER['PHP_SELF']) == 'manage_complaints.php'   ? 'active' : '' ?>"><i class="fas fa-tasks"></i> Manage Complaints</a>
        <a href="<?= BASE_URL ?>/admin/reports.php"             class="nav-item <?= basename($_SERVER['PHP_SELF']) == 'reports.php'             ? 'active' : '' ?>"><i class="fas fa-chart-bar"></i> Reports</a>
    </nav>
    <div class="sidebar-footer">
        <div class="user-info-sidebar">
            <div class="avatar-circle" style="background:linear-gradient(135deg,#f59e0b,#d97706);"><?= strtoupper(substr($_SESSION['name'], 0, 2)) ?></div>
            <div>
                <div class="user-name-sidebar"><?= htmlspecialchars($_SESSION['name']) ?></div>
                <div class="user-role-sidebar"><?= ucfirst($_SESSION['role']) ?></div>
            </div>
        </div>
        <a href="<?= BASE_URL ?>/logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
</div>
