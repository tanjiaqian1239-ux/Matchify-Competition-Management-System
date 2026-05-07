<?php
session_start();
include "config.php";

/* =========================
   AUTO DISABLE EXPIRED JUDGES
   (1 MONTH AFTER COMPETITION END)
========================= */
$conn->query("
    UPDATE users u
    INNER JOIN competition_judges cj 
        ON cj.judge_id = u.id
    INNER JOIN competition_applications ca 
        ON ca.id = cj.competition_id
    SET u.status = 'inactive'
    WHERE u.role = 'judge'
    AND DATE_ADD(ca.end_date, INTERVAL 1 MONTH) < CURDATE()
    AND u.status = 'active'
");

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("
        SELECT * 
        FROM users 
        WHERE email = ?
    ");

    $stmt->bind_param("s", $email);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows == 1) {

        $user = $result->fetch_assoc();

        /* =========================
           VERIFY PASSWORD
        ========================= */
        if (password_verify($password, $user['password'])) {

            /* =========================
               CHECK ACCOUNT STATUS
            ========================= */
            if ($user['status'] == 'inactive') {

                echo "<script>
                        alert('❌ Your account has been disabled.');
                        window.location.href='login.php';
                      </script>";
                exit();
            }

            /* =========================
               SESSION
            ========================= */
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            /* =========================
               PARTICIPANT
            ========================= */
            if ($user['role'] == 'participant') {

                echo "<script>
                        alert('✅ Login successful (Participant)');
                        window.location.href='Participant/index(participant).php';
                      </script>";
                exit();
            }

            /* =========================
               ORGANISER
            ========================= */
            elseif ($user['role'] == 'organiser') {

                echo "<script>
                        alert('✅ Login successful (Organiser)');
                        window.location.href='Organiser/index-organiser.php';
                      </script>";
                exit();
            }

            /* =========================
               ADMIN
            ========================= */
            elseif ($user['role'] == 'admin') {

                echo "<script>
                        alert('✅ Login successful (Admin)');
                        window.location.href='Admin/dashboard.php';
                      </script>";
                exit();
            }

            /* =========================
               JUDGE
            ========================= */
            elseif ($user['role'] == 'judge') {

                echo "<script>
                        alert('✅ Login successful (Judge)');
                        window.location.href='Judge/dashboard.php';
                      </script>";
                exit();
            }

            /* =========================
               UNKNOWN ROLE
            ========================= */
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

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="icon" type="image/png" href="images/logo.png">

    <title>Login - Matchify</title>

    <link rel="stylesheet" href="css/login.css">

</head>

<body>

<div class="hero">

    <!-- NAVBAR -->
    <nav>

        <img src="images/logo.png" class="logo">

        <ul>
            <li><a href="index.php">Home</a></li>
            <li><a href="competition-list.php">Competition List</a></li>
            <li><a href="about.php">About</a></li>
            <li><a href="contact.php">Contact</a></li>
        </ul>

        <a href="login.php" class="btn">Login</a>

    </nav>

    <!-- LOGIN BOX -->
    <div class="container">

        <div class="title">Login</div>

        <form action="" method="POST">

            <div class="user-details">

                <!-- EMAIL -->
                <div class="input-box">

                    <span class="details">Email</span>

                    <input 
                        type="email"
                        name="email"
                        placeholder="Enter your email"
                        required
                    >

                </div>

                <!-- PASSWORD -->
                <div class="input-box">

                    <span class="details">Password</span>

                    <div class="password-wrapper">

                        <input 
                            type="password"
                            name="password"
                            id="password"
                            placeholder="Enter your password"
                            required
                        >

                        <span 
                            class="toggle-password"
                            data-target="password">
                        </span>

                    </div>

                    <div class="forgot-password-container">
                        <a href="forgetpassword.php">Forgot Password?</a>
                    </div>

                </div>

            </div>

            <!-- BUTTON -->
            <div class="button">
                <input type="submit" value="Login" class="btn">
            </div>

            <!-- REGISTER -->
            <div class="extra-links">
                <span>Don't have an account?</span>
                <a href="signup.php">Register</a>
            </div>

        </form>

    </div>

</div>

<script>

document.addEventListener("DOMContentLoaded", function () {

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