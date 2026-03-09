<?php
// includes/functions.php

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: " . BASE_URL . "/index.php");
        exit();
    }
}

function requireAdmin() {
    requireLogin();
    if ($_SESSION['role'] !== 'admin') {
        header("Location: " . BASE_URL . "/user/dashboard.php");
        exit();
    }
}

function requireAdminOrAgent() {
    requireLogin();
    if (!in_array($_SESSION['role'], ['admin', 'agent'])) {
        header("Location: " . BASE_URL . "/user/dashboard.php");
        exit();
    }
}

function sanitize($conn, $data) {
    return $conn->real_escape_string(trim(htmlspecialchars($data)));
}

function getStatusBadge($status) {
    $map = [
        'Pending'     => ['bg' => '#fff3cd', 'color' => '#856404', 'dot' => '#f5a623'],
        'In Progress' => ['bg' => '#cfe2ff', 'color' => '#084298', 'dot' => '#3b82f6'],
        'Resolved'    => ['bg' => '#d1e7dd', 'color' => '#0a3622', 'dot' => '#22c55e'],
        'Closed'      => ['bg' => '#e2e3e5', 'color' => '#41464b', 'dot' => '#6b7280'],
    ];
    $s = $map[$status] ?? $map['Pending'];
    return "<span class='status-badge' style='background:{$s['bg']};color:{$s['color']};'>
                <span style='background:{$s['dot']};'></span>{$status}
            </span>";
}

function getPriorityBadge($priority) {
    $map = [
        'High'   => ['bg' => '#fee2e2', 'color' => '#991b1b'],
        'Medium' => ['bg' => '#fef3c7', 'color' => '#92400e'],
        'Low'    => ['bg' => '#f0fdf4', 'color' => '#166534'],
    ];
    $p = $map[$priority] ?? $map['Medium'];
    return "<span class='priority-badge' style='background:{$p['bg']};color:{$p['color']};'>{$priority}</span>";
}

function generateComplaintNo($conn) {
    $result = $conn->query("SELECT COUNT(*) as cnt FROM complaints");
    $row = $result->fetch_assoc();
    $next = $row['cnt'] + 1;
    return 'CMP-' . str_pad($next, 3, '0', STR_PAD_LEFT);
}

function timeAgo($datetime) {
    $now  = new DateTime();
    $ago  = new DateTime($datetime);
    $diff = $now->diff($ago);
    if ($diff->d == 0) {
        if ($diff->h == 0) return $diff->i . " min ago";
        return $diff->h . " hr ago";
    }
    if ($diff->d < 30) return $diff->d . " day" . ($diff->d > 1 ? "s" : "") . " ago";
    return date('d M Y', strtotime($datetime));
}

// Base URL helper — detects automatically
define('BASE_URL', (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . "://" . $_SERVER['HTTP_HOST'] . rtrim(dirname(dirname($_SERVER['SCRIPT_NAME'])), '/\\'));
?>
