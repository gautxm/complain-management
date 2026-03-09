<?php
// admin/ajax_update.php — AJAX endpoint for inline status/agent updates
session_start();
require_once '../config/db.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn() || !in_array($_SESSION['role'], ['admin','agent'])) {
    echo json_encode(['success' => false, 'msg' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'msg' => 'Invalid method']);
    exit();
}

$action       = sanitize($conn, $_POST['action'] ?? '');
$complaint_id = (int)($_POST['complaint_id'] ?? 0);

if (!$complaint_id) {
    echo json_encode(['success' => false, 'msg' => 'Invalid complaint ID']);
    exit();
}

if ($action === 'status') {
    $status = sanitize($conn, $_POST['status'] ?? '');
    $allowed = ['Pending', 'In Progress', 'Resolved', 'Closed'];
    if (!in_array($status, $allowed)) {
        echo json_encode(['success' => false, 'msg' => 'Invalid status']);
        exit();
    }
    $conn->query("UPDATE complaints SET status='$status', updated_at=NOW() WHERE id=$complaint_id");
    echo json_encode(['success' => true, 'msg' => "Status updated to $status"]);

} elseif ($action === 'agent') {
    $agent_id = (int)($_POST['agent_id'] ?? 0);
    if ($agent_id > 0) {
        $conn->query("UPDATE complaints SET agent_id=$agent_id, updated_at=NOW() WHERE id=$complaint_id");
    } else {
        $conn->query("UPDATE complaints SET agent_id=NULL, updated_at=NOW() WHERE id=$complaint_id");
    }
    echo json_encode(['success' => true, 'msg' => 'Agent updated']);

} else {
    echo json_encode(['success' => false, 'msg' => 'Unknown action']);
}
?>
