<?php

$token = $_GET["token"] ?? '';

if (!$token) {
    die("Invalid token");
}

$token_hash = hash("sha256", $token);

require __DIR__ . "/config.php";

$sql = "SELECT * FROM users WHERE reset_token_hash = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $token_hash);
$stmt->execute();

$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    die("Token not found");
}

if (strtotime($user["reset_token_expires_at"]) <= time()) {
    die("Token has expired");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="images/logo.png">
    <title>Reset Password - Matchify Competition Management System</title>
    <link rel="stylesheet" href="css/reset-password.css">
</head>
<body>

<div class="hero">

    <!-- NAVBAR -->
    <nav>
        <img src="images/logo.png" class="logo">
        <ul>
            <li><a href="home.php">Home</a></li>
            <li><a href="#">Competition List</a></li>
            <li><a href="#">About</a></li>
            <li><a href="#">Contact</a></li>
        </ul>
        <a href="login.php" class="btn">Login</a>
    </nav>

    <!-- FORM -->
    <div class="container">
        <div class="title">Reset Password</div>

        <form method="POST" action="process-reset-password.php">

            <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">

            <div class="user-details">

                <div class="input-box">
                    <span class="details">New Password</span>
                    <div class="password-wrapper">
                        <input type="password" name="password" id="password"
                            placeholder="Enter new password"
                            required>
                        <span class="toggle-password" data-target="password"></span>
                    </div>
                    <small>Password must include: 8+ characters, uppercase, lowercase, number, special character</small>
                </div>

                <div class="input-box">
                    <span class="details">Confirm Password</span>
                    <div class="password-wrapper">
                        <input type="password" name="password_confirmation" id="confirmPassword"
                            placeholder="Confirm password"
                            required>
                        <span class="toggle-password" data-target="confirmPassword"></span>
                    </div>
                </div>

                <small id="passwordError" style="color:red;"></small>

            </div>

            <div class="button">
                <input type="submit" value="Reset Password" class="btn">
            </div>

        </form>
    </div>

</div>

<script>
document.addEventListener("DOMContentLoaded", function () {

    const form = document.querySelector("form");
    const password = document.getElementById("password");
    const confirmPassword = document.getElementById("confirmPassword");
    const errorText = document.getElementById("passwordError");

    // Toggle password visibility
    document.querySelectorAll(".toggle-password").forEach(btn => {
        btn.addEventListener("click", function () {
            const input = document.getElementById(this.dataset.target);
            input.type = input.type === "password" ? "text" : "password";
            this.classList.toggle("active");
        });
    });

    form.addEventListener("submit", function(e) {

        const pwd = password.value;
        const confirmPwd = confirmPassword.value;

        // Check password complexity
        const pattern = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;

        if (!pattern.test(pwd)) {
            e.preventDefault();
            errorText.textContent = "Password must be at least 8 characters and include uppercase, lowercase, number, and special character.";
            return;
        }

        if (pwd !== confirmPwd) {
            e.preventDefault();
            errorText.textContent = "Passwords do not match.";
            return;
        }

        // Clear error
        errorText.textContent = "";
    });

});
</script>

</body>
</html>