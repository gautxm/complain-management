<?php
// config/db.php — Database Connection

define('DB_HOST', 'localhost');
define('DB_USER', 'root');       // Change to your MySQL username
define('DB_PASS', '');           // Change to your MySQL password
define('DB_NAME', 'complaint_db');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die("
    <div style='font-family:sans-serif;text-align:center;margin-top:100px;'>
        <h2 style='color:#dc2626;'>⚠️ Database Connection Failed</h2>
        <p style='color:#6b7280;'>Please make sure XAMPP is running and the database is imported.</p>
        <p style='color:#9ca3af;font-size:13px;'>Error: " . $conn->connect_error . "</p>
    </div>");
}

$conn->set_charset("utf8");
?>
