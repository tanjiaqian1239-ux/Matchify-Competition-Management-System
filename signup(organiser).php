<?php
include "config.php";

$error = ""; 

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
        $error = "❌ Email must be a valid format";
    }
    elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/', $password)) {
        $error = "❌ Password must be at least 8 characters and include uppercase, lowercase, a number, and a special character";
    }
    elseif ($password !== $confirmPassword) {
        $error = "❌ Passwords do not match";
    }
    else {
        $stmt = $conn->prepare("SELECT email, username FROM users WHERE email=? OR username=? LIMIT 1");
        $stmt->bind_param("ss", $email, $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            if ($row['email'] === $email) {
                $error = "❌ Email already exists";
            } else {
                $error = "❌ Username already exists";
            }
            $stmt->close();
        } else {
            $stmt->close();
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $role = 'organiser';

            $stmt = $conn->prepare("INSERT INTO users (fullname, username, email, password, country, phone, gender, role) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssssss", $fullname, $username, $email, $hashedPassword, $country, $phone, $gender, $role);

            if ($stmt->execute()) {
                echo "<script>alert('✅ Organiser Registration successful!'); window.location.href='login.php';</script>";
                exit();
            } else {
                $error = "❌ Registration failed";
            }
            $stmt->close();
        }
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
    <title>Matchify Competition Management System</title>
    <link rel="stylesheet" href="css/signup.css">
</head>
<body>

<div class="hero">
    <nav>
        <img src="images/logo.png" class="logo">
        <ul>
            <li><a href="home.php">Home</a></li>
            <li><a href="competition-list.php">Competition List</a></li>
            <li><a href="about.php">About</a></li>
            <li><a href="contact.php">Contact</a></li>
        </ul>
        <a href="login.php" class="btn">Login</a>
    </nav>

    <div class="container">
        <div class="title">Registration As Organiser</div>

        <div id="messageDisplay" style="text-align: center; margin-bottom: 15px; min-height: 25px;">
            <?php if (!empty($error)) : ?>
                <span style="color: red; font-weight: bold;"><?php echo $error; ?></span>
            <?php endif; ?>
        </div>

        <form action="" method="POST" id="registrationForm">
            <div class="user-details">
                <div class="input-box">
                    <span class="details">Full Name</span>
                    <input type="text" name="fullname" pattern="[A-Za-z\s]+" placeholder="Enter your name" required>
                </div>

                <div class="input-box">
                    <span class="details">Username</span>
                    <input type="text" name="username" placeholder="Enter your username" required>
                </div>

                <div class="input-box">
                    <span class="details">Email</span>
                    <input type="email" name="email" placeholder="Enter your email" required>
                </div>

                <div class="input-box">
                    <span class="details">Password</span>
                    <div class="password-wrapper">
                        <input type="password" name="password" id="password" 
                               placeholder="Enter your password" required
                               pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$"
                               title="Must be at least 8 characters, include uppercase, lowercase, number and special character">
                        <span class="toggle-password" data-target="password"></span>
                    </div>
                </div>

                <div class="input-box">
                    <span class="details">Confirm Password</span>
                    <div class="password-wrapper">
                        <input type="password" name="confirmPassword" id="confirmPassword" placeholder="Confirm your password" required>
                        <span class="toggle-password" data-target="confirmPassword"></span>
                    </div>
                </div>

                <div class="input-box">
                    <span class="details">Country</span>
                    <select name="country" required>
                        <option value="">Select Country</option>
                        <optgroup label="Asia">
                            <option value="Malaysia">Malaysia</option>
                            <option value="Singapore">Singapore</option>
                            <option value="Indonesia">Indonesia</option>
                            <option value="Thailand">Thailand</option>
                            <option value="Vietnam">Vietnam</option>
                            <option value="Philippines">Philippines</option>
                            <option value="China">China</option>
                            <option value="Japan">Japan</option>
                            <option value="South Korea">South Korea</option>
                            <option value="India">India</option>
                            <option value="Brunei">Brunei</option>
                            <option value="Cambodia">Cambodia</option>
                            <option value="Laos">Laos</option>
                            <option value="Myanmar">Myanmar</option>
                            <option value="Pakistan">Pakistan</option>
                            <option value="Bangladesh">Bangladesh</option>
                            <option value="Sri Lanka">Sri Lanka</option>
                        </optgroup>
                        <optgroup label="Europe">
                            <option value="United Kingdom">United Kingdom</option>
                            <option value="Germany">Germany</option>
                            <option value="France">France</option>
                            <option value="Italy">Italy</option>
                            <option value="Spain">Spain</option>
                            <option value="Netherlands">Netherlands</option>
                            <option value="Switzerland">Switzerland</option>
                            <option value="Sweden">Sweden</option>
                            <option value="Norway">Norway</option>
                            <option value="Denmark">Denmark</option>
                            <option value="Russia">Russia</option>
                        </optgroup>
                        <optgroup label="North America">
                            <option value="United States">United States</option>
                            <option value="Canada">Canada</option>
                            <option value="Mexico">Mexico</option>
                        </optgroup>
                        <optgroup label="South America">
                            <option value="Brazil">Brazil</option>
                            <option value="Argentina">Argentina</option>
                            <option value="Chile">Chile</option>
                            <option value="Colombia">Colombia</option>
                        </optgroup>
                        <optgroup label="Oceania">
                            <option value="Australia">Australia</option>
                            <option value="New Zealand">New Zealand</option>
                            <option value="Fiji">Fiji</option>
                        </optgroup>
                        <optgroup label="Middle East & Africa">
                            <option value="United Arab Emirates">United Arab Emirates</option>
                            <option value="Saudi Arabia">Saudi Arabia</option>
                            <option value="Qatar">Qatar</option>
                            <option value="Turkey">Turkey</option>
                            <option value="Egypt">Egypt</option>
                            <option value="South Africa">South Africa</option>
                            <option value="Nigeria">Nigeria</option>
                            <option value="Kenya">Kenya</option>
                        </optgroup>
                    </select>
                </div>

                <div class="input-box">
                    <span class="details">Phone Number</span>
                    <input type="text" name="phone" placeholder="Enter phone number" required pattern="[0-9]+">
                </div>
            </div>

            <div class="gender-details">
                <span class="gender-title">Gender</span>
                <input type="radio" name="gender" value="Male" id="dot-1" required>
                <input type="radio" name="gender" value="Female" id="dot-2" required>
                <input type="radio" name="gender" value="Other" id="dot-3" required>
                <div class="category">
                    <label for="dot-1"><span class="dot one"></span><span>Male</span></label>
                    <label for="dot-2"><span class="dot two"></span><span>Female</span></label>
                    <label for="dot-3"><span class="dot three"></span><span>Prefer not to say</span></label>
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
                <a href="signup.php">Participant</a>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const form = document.getElementById("registrationForm");
    const password = document.getElementById("password");
    const confirmPassword = document.getElementById("confirmPassword");
    const messageDisplay = document.getElementById("messageDisplay");

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

    function checkLogic() {
        const regex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/;
        
        if (!regex.test(password.value)) {
            password.setCustomValidity("Invalid format");
        } else {
            password.setCustomValidity("");
        }

        if (confirmPassword.value !== "" && password.value !== confirmPassword.value) {
            messageDisplay.innerHTML = '<span style="color: red; font-weight: bold;">❌ Passwords do not match</span>';
            confirmPassword.setCustomValidity("Match Error");
        } else if (confirmPassword.value !== "" && password.value === confirmPassword.value) {
            messageDisplay.innerHTML = '<span style="color: green; font-weight: bold;">✅ Passwords match</span>';
            confirmPassword.setCustomValidity("");
        } else {
            messageDisplay.innerHTML = "";
            confirmPassword.setCustomValidity("");
        }
    }

    password.addEventListener("input", checkLogic);
    confirmPassword.addEventListener("input", checkLogic);

    form.addEventListener("submit", function(e) {
        checkLogic();
        if (!form.checkValidity()) {
            form.reportValidity();
            e.preventDefault();
        }
    });
});
</script>

</body>
</html>