<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$host = "localhost";
$user = "root";
$password = "";
$database = "mcmp";

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

/* =========================
   ROLE SYSTEM
========================= */
function requireRole($roles = []) {
    if (!isset($_SESSION['user_id'])) {
        header("Location: ../login.php");
        exit();
    }

    if (!in_array($_SESSION['role'], $roles)) {
        echo "<script>
                alert('❌ No permission');
                window.location.href='../Admin/dashboard.php';
              </script>";
        exit();
    }
}

return $conn;
?>