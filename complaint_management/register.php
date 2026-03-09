<?php
// register.php
session_start();
if (isset($_SESSION['user_id'])) { header("Location: user/dashboard.php"); exit(); }

require_once 'config/db.php';
require_once 'includes/functions.php';

$error = $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = sanitize($conn, $_POST['name'] ?? '');
    $email    = sanitize($conn, $_POST['email'] ?? '');
    $phone    = sanitize($conn, $_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    if (empty($name) || empty($email) || empty($password)) {
        $error = "Name, email and password are required.";
    } elseif ($password !== $confirm) {
        $error = "Passwords do not match.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } else {
        // Check if email exists
        $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            $error = "Email already registered. Please login.";
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt   = $conn->prepare("INSERT INTO users (name, email, password, phone, role) VALUES (?, ?, ?, ?, 'user')");
            $stmt->bind_param("ssss", $name, $email, $hashed, $phone);
            if ($stmt->execute()) {
                $success = "Account created successfully! You can now login.";
            } else {
                $error = "Something went wrong. Please try again.";
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
<title>ComplainX — Register</title>
<link rel="stylesheet" href="assets/css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<div class="auth-page">
    <div class="auth-card" style="max-width:460px;">
        <div class="auth-logo">ComplainX</div>
        <div class="auth-sub">Create your student account</div>

        <?php if ($error):   ?><div class="alert-cx alert-danger"><i class="fas fa-exclamation-circle"></i> <?= $error ?></div><?php endif; ?>
        <?php if ($success): ?><div class="alert-cx alert-success"><i class="fas fa-check-circle"></i> <?= $success ?></div><?php endif; ?>

        <?php if (!$success): ?>
        <form method="POST" action="">
            <div class="form-group">
                <label class="form-label"><i class="fas fa-user"></i> Full Name</label>
                <input type="text" name="name" class="form-control-cx" placeholder="Enter your full name"
                    value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>
            </div>
            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label"><i class="fas fa-envelope"></i> Email</label>
                    <input type="email" name="email" class="form-control-cx" placeholder="your@email.com"
                        value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label"><i class="fas fa-phone"></i> Phone</label>
                    <input type="text" name="phone" class="form-control-cx" placeholder="10-digit number"
                        value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
                </div>
            </div>
            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label"><i class="fas fa-lock"></i> Password</label>
                    <input type="password" name="password" class="form-control-cx" placeholder="Min. 6 characters" required>
                </div>
                <div class="form-group">
                    <label class="form-label"><i class="fas fa-lock"></i> Confirm</label>
                    <input type="password" name="confirm_password" class="form-control-cx" placeholder="Repeat password" required>
                </div>
            </div>
            <button type="submit" class="btn-primary-cx" style="width:100%;margin-top:4px;">
                <i class="fas fa-user-plus"></i> Create Account
            </button>
        </form>
        <?php endif; ?>

        <div class="auth-footer">
            Already have an account? <a href="index.php">Login here</a>
        </div>
    </div>
</div>
<script src="assets/js/main.js"></script>
</body>
</html>
