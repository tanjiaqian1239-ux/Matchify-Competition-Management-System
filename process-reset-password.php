<?php
$token = $_POST["token"] ?? '';
if (!$token) die("Invalid token");

$token_hash = hash("sha256", $token);

$conn = require __DIR__ . "/config.php";

$sql = "SELECT * FROM users WHERE reset_token_hash = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $token_hash);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) die("Token not found");
if (strtotime($user["reset_token_expires_at"]) <= time()) die("Token has expired");

// Get passwords
$password = $_POST["password"];
$password_confirmation = $_POST["password_confirmation"];

// Validation
$pattern = "/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&]).{8,}$/";

if (!preg_match($pattern, $password)) {
    die("Password must be at least 8 characters and include uppercase, lowercase, number, and special character.");
}

if ($password !== $password_confirmation) {
    die("Passwords must match");
}

// Hash password
$password_hash = password_hash($password, PASSWORD_DEFAULT);

// Update database
$sql = "UPDATE users
        SET password = ?,
            reset_token_hash = NULL,
            reset_token_expires_at = NULL
        WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $password_hash, $user["id"]);
$stmt->execute();

echo "<script>
        alert('✅ Password updated. You can now login.');
        window.location.href='login.php';
      </script>";

$conn->close();