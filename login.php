<?php
session_start();
include "config.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = $_POST['email'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE email='$email'";
    $result = $conn->query($sql);

    if ($result->num_rows == 1) {

        $user = $result->fetch_assoc();

        // verify password
        if (password_verify($password, $user['password'])) {

            // store session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            // redirect based on role
            if ($user['role'] == 'participant') {
                echo "<script>
                        alert('✅ Login successful (Participant)');
                        window.location.href='participant_dashboard.php';
                      </script>";
                exit();
            } 
            elseif ($user['role'] == 'organiser') {
                echo "<script>
                        alert('✅ Login successful (Organiser)');
                        window.location.href='organiser_dashboard.php';
                      </script>";
                exit();
            } 
            else {
                echo "<script>
                        alert('❌ Unknown role');
                        window.location.href='login.php';
                      </script>";
                exit();
            }

        } else {
            echo "<script>
                    alert('❌ Wrong password');
                    window.location.href='login.php';
                  </script>";
            exit();
        }

    } else {
        echo "<script>
                alert('❌ Email not found');
                window.location.href='login.php';
              </script>";
        exit();
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="images/logo.png">
    <title>Login - Mathchify</title>
    <link rel="stylesheet" href="css/login.css">
</head>
<body>

<div class="hero">
    <nav>
        <img src="images/logo.png" class="logo">
        <ul>
            <li><a href="index.php">Home</a></li>
            <li><a href="#">Competition List</a></li>
            <li><a href="#">About</a></li>
            <li><a href="#">Contact</a></li>
        </ul>
        <a href="login.php" class="btn">Login</a>
    </nav>

    <div class="container">
        <div class="title">Login</div>

        <form action="" method="POST">

            <div class="user-details">

                <div class="input-box">
                    <span class="details">Email</span>
                    <input type="email" name="email"
                        placeholder="Enter your email"
                        required>
                </div>

                <div class="input-box">
                    <span class="details">Password</span>
                    <div class="password-wrapper">
                        <input type="password" name="password" id="password" placeholder="Enter your password" required>
                        <span class="toggle-password" data-target="password"></span>
                    </div>
                    <div class="forgot-password-container">
                        <a href="forgetpassword.php">Forgot Password?</a>
                    </div>
                </div>

            <div class="button">
                <input type="submit" value="Login" class="btn">
            </div>

            <div class="extra-links">
                <span>Don't have an account?</span>
                <a href="signup.php">Register</a>
            </div>

            

        </form>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {

    const password = document.getElementById("password");

    document.querySelectorAll(".toggle-password").forEach(btn => {
        btn.addEventListener("click", function () {
            const input = document.getElementById(this.dataset.target);

            if (input.type === "password") {
                input.type = "text";
                this.classList.add("active");
            } else {
                input.type = "password";
                this.classList.remove("active");
            }
        });
    });

});
</script>

</body>
</html>