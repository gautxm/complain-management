<?php
// index.php — Login Page
session_start();

// If already logged in, redirect
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'user') {
        header("Location: user/dashboard.php"); exit();
    } else {
        header("Location: admin/dashboard.php"); exit();
    }
}

require_once 'config/db.php';
require_once 'includes/functions.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = sanitize($conn, $_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = "Please fill in all fields.";
    } else {
        $stmt = $conn->prepare("SELECT id, name, email, password, role FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user   = $result->fetch_assoc();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['name']    = $user['name'];
            $_SESSION['email']   = $user['email'];
            $_SESSION['role']    = $user['role'];

            if ($user['role'] === 'user') {
                header("Location: user/dashboard.php");
            } else {
                header("Location: admin/dashboard.php");
            }
            exit();
        } else {
            $error = "Invalid email or password.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>ComplainX — Login</title>
<link rel="stylesheet" href="assets/css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<div class="auth-page">
    <div class="auth-card">
        <div class="auth-logo">ComplainX</div>
        <div class="auth-sub">Sign in to your account</div>

        <?php if ($error): ?>
        <div class="alert-cx alert-danger"><i class="fas fa-exclamation-circle me-1"></i> <?= $error ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label class="form-label"><i class="fas fa-envelope"></i> Email Address</label>
                <input type="email" name="email" class="form-control-cx"
                    placeholder="Enter your email"
                    value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label class="form-label"><i class="fas fa-lock"></i> Password</label>
                <input type="password" name="password" class="form-control-cx"
                    placeholder="Enter your password" required>
            </div>
            <button type="submit" class="btn-primary-cx" style="width:100%;margin-top:6px;">
                <i class="fas fa-sign-in-alt"></i> Sign In
            </button>
        </form>

        <div class="auth-footer">
            Don't have an account? <a href="register.php">Register here</a>
        </div>

        <div style="margin-top:20px;padding-top:16px;border-top:1px solid #f3f4f6;">
            <p style="text-align:center;font-size:11px;color:#9ca3af;margin-bottom:8px;">Demo Credentials</p>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;font-size:12px;text-align:center;">
                <div style="background:#f8f7ff;border-radius:8px;padding:8px;">
                    <div style="font-weight:700;color:#6366f1;">Student</div>
                    <div style="color:#6b7280;">arjun@student.com</div>
                    <div style="color:#6b7280;">user123</div>
                </div>
                <div style="background:#fff9ec;border-radius:8px;padding:8px;">
                    <div style="font-weight:700;color:#f59e0b;">Admin</div>
                    <div style="color:#6b7280;">admin@complainx.com</div>
                    <div style="color:#6b7280;">password</div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="assets/js/main.js"></script>
</body>
</html>
