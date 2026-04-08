<?php
include "config.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $fullname = $_POST['fullname'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirmPassword'];
    $country = $_POST['country'];
    $phone = $_POST['phone'];
    $gender = $_POST['gender'];

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<script>
                alert('❌ Email must be a valid format');
                window.location.href='signup.php';
              </script>";
        exit();
    }

    if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/', $password)) {
        echo "<script>
                alert('❌ Password must be at least 8 characters and include uppercase, lowercase, and a number');
                window.location.href='signup.php';
              </script>";
        exit();
    }

    if ($password !== $confirmPassword) {
        echo "<script>
                alert('❌ Passwords do not match');
                window.location.href='signup.php';
              </script>";
        exit();
    }

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $check = "SELECT * FROM users WHERE email='$email' OR username='$username'";
    $result = $conn->query($check);

    if ($result->num_rows > 0) {
        echo "<script>
                alert('❌ Username or Email already exists');
                window.location.href='signup.php';
              </script>";
        exit();
    }

    $sql = "INSERT INTO users (fullname, username, email, password, country, phone, gender, role)
            VALUES ('$fullname','$username','$email','$hashedPassword','$country','$phone','$gender','participant')";

    if ($conn->query($sql) === TRUE) {
        echo "<script>
                alert('✅ Registration successful!');
                window.location.href='login.php';
              </script>";
        exit();
    } else {
        echo "<script>
                alert('❌ Registration failed: ".$conn->error."');
                window.location.href='signup.php';
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
    <title>Mathchify Competition Management System</title>
    <link rel="stylesheet" href="css/signup.css">
</head>
<body>

<div class="hero">
    <nav>
        <img src="images/logo.png" class="logo">
        <ul>
            <li><a href="#">Home</a></li>
            <li><a href="#">Competition List</a></li>
            <li><a href="#">About</a></li>
            <li><a href="#">Contact</a></li>
        </ul>
        <a href="#" class="btn">Login</a>
    </nav>

    <div class="container">
        <div class="title">Registration As Participant</div>

        <form action="" method="POST">

            <div class="user-details">

                <div class="input-box">
                    <span class="details">Full Name</span>
                    <input type="text" name="fullname"
                        pattern="[A-Za-z\s]+"
                        placeholder="Enter your name"
                        required>
                </div>

                <div class="input-box">
                    <span class="details">Username</span>
                    <input type="text" name="username"
                        placeholder="Enter your username"
                        required>
                </div>

                <div class="input-box">
                    <span class="details">Email</span>
                    <input type="email" name="email"
                        placeholder="Enter your email"
                        required
                        pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$"
                        title="Please enter a valid email address">
                </div>

                <div class="input-box">
                    <span class="details">Password</span>
                    <div class="password-wrapper">
                        <input type="password" name="password" id="password"
                            placeholder="Enter your password"
                            required
                            pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}"
                            title="At least 8 characters, include uppercase, lowercase and number">
                        <span class="toggle-password" data-target="password"></span>
                    </div>
                </div>

                <div class="input-box">
                    <span class="details">Confirm Password</span>
                    <div class="password-wrapper">
                        <input type="password" name="confirmPassword" id="confirmPassword"
                            placeholder="Confirm your password"
                            required>
                        <span class="toggle-password" data-target="confirmPassword"></span>
                    </div>
                </div>

                <small id="passwordError" style="color:red;"></small>

                <div class="input-box">
                    <span class="details">Country</span>
                    <select name="country" required>
                        <option value="">Select Country</option>
                        <option value="+60">Malaysia</option>
                        <option value="+65">Singapore</option>
                        <option value="+62">Indonesia</option>
                        <option value="+66">Thailand</option>
                        <option value="+84">Vietnam</option>
                        <option value="+63">Philippines</option>
                        <option value="+86">China</option>
                        <option value="+81">Japan</option>
                        <option value="+82">South Korea</option>
                        <option value="+91">India</option>
                        <option value="+1">United States</option>
                        <option value="+44">United Kingdom</option>
                        <option value="+61">Australia</option>
                        <option value="+49">Germany</option>
                    </select>
                </div>

                <div class="input-box">
                    <span class="details">Phone Number</span>
                    <input type="text" name="phone"
                        placeholder="Enter phone number"
                        required
                        pattern="[0-9]+">
                </div>

            </div>

            <div class="gender-details">
                <span class="gender-title">Gender</span>

                <input type="radio" name="gender" value="Male" id="dot-1" required>
                <input type="radio" name="gender" value="Female" id="dot-2" required>
                <input type="radio" name="gender" value="Other" id="dot-3" required>

                <div class="category">
                    <label for="dot-1">
                        <span class="dot one"></span>
                        <span>Male</span>
                    </label>

                    <label for="dot-2">
                        <span class="dot two"></span>
                        <span>Female</span>
                    </label>

                    <label for="dot-3">
                        <span class="dot three"></span>
                        <span>Prefer not to say</span>
                    </label>
                </div>
            </div>

            <div class="button">
                <input type="submit" value="Register" class="btn">
            </div>

            <div class="extra-links">
                <span>Already have an account?</span>
                <a href="login.php">Login</a>
            </div>

            <div class="extra-links">
                <span>Register as</span>
                <a href="organiser_signup.php">Organiser</a>
            </div>

        </form>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {

    const form = document.querySelector("form");
    const password = document.getElementById("password");
    const confirmPassword = document.getElementById("confirmPassword");
    const emailInput = document.querySelector("input[name='email']");
    const errorText = document.getElementById("passwordError");

    document.querySelectorAll(".toggle-password").forEach(btn => {
        btn.addEventListener("click", function () {
            const input = document.getElementById(this.dataset.target);
            input.type = input.type === "password" ? "text" : "password";
            this.classList.toggle("active");
        });
    });

    confirmPassword.addEventListener("input", () => {
        confirmPassword.setCustomValidity("");
        errorText.textContent = "";
    });

    password.addEventListener("input", () => {
        confirmPassword.setCustomValidity("");
        errorText.textContent = "";
    });

    emailInput.addEventListener("input", () => {
        const isValid = /^[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$/.test(emailInput.value);
        emailInput.setCustomValidity(isValid ? "" : "Please enter a valid email address");
    });

    form.addEventListener("submit", function(e) {
        if (password.value !== confirmPassword.value) {
            e.preventDefault();
            confirmPassword.setCustomValidity("Passwords do not match");
            errorText.textContent = "Passwords do not match";
            confirmPassword.reportValidity();
        }
    });

});
</script>

</body>
</html>