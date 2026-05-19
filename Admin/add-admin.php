<?php
session_start();
include "../config.php";

requireRole(['superadmin']);

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = (int)$_SESSION['user_id'];

/* =========================
   PROFILE IMAGE
========================= */
$profile_image = "../images/profile.avif";
$uq = $conn->query("SELECT profile_image FROM users WHERE id = $user_id");
if ($uq && $uq->num_rows > 0) {
    $u = $uq->fetch_assoc();
    if (!empty($u['profile_image']) && $u['profile_image'] !== 'default.png') {
        $path = "/images/profile/" . $u['profile_image'];
        if (file_exists($_SERVER['DOCUMENT_ROOT'] . $path)) {
            $profile_image = $path;
        }
    }
}

/* =========================
   HANDLE FORM SUBMIT
========================= */
$success = '';
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = trim($_POST['fullname']);
    $email    = trim($_POST['email']);
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm  = $_POST['confirm_password'];
    $role     = $_POST['role'];

    $pattern  = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/';
    $allowed_roles = ['admin', 'superadmin'];

    // Validate
    if (empty($fullname) || empty($email) || empty($username) || empty($password)) {
        $error = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format.';
    } elseif (!in_array($role, $allowed_roles)) {
        $error = 'Invalid role selected.';
    } elseif (!preg_match($pattern, $password)) {
        $error = 'Password must be at least 8 characters and include uppercase, lowercase, number, and special character.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        // Check email duplicate
        $chk = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $chk->bind_param("s", $email);
        $chk->execute();
        if ($chk->get_result()->num_rows > 0) {
            $error = 'This email is already registered.';
        } else {
            // Check username duplicate
            $chk2 = $conn->prepare("SELECT id FROM users WHERE username = ?");
            $chk2->bind_param("s", $username);
            $chk2->execute();
            if ($chk2->get_result()->num_rows > 0) {
                $error = 'This username is already taken.';
            } else {
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                $ins = $conn->prepare("INSERT INTO users (fullname, email, username, password, role, status) VALUES (?,?,?,?,?,'active')");
                $ins->bind_param("sssss", $fullname, $email, $username, $hashed, $role);
                $ins->execute();
                $success = 'Admin account created successfully!';
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
<title>Add Admin</title>
<link rel="icon" type="image/png" href="../images/logo.png">
<link rel="stylesheet" href="../Admin-css/dashboard.css">
<link rel="stylesheet" href="../Admin-css/add-admin.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<!-- SIDEBAR -->
<div class="sidebar">
    <div class="logo"></div>
    <ul class="menu">
        <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a></li>
        <li><a href="profile.php"><i class="fas fa-user"></i><span>Profile</span></a></li>
        <li class="active"><a href="manage-admin.php"><i class="fas fa-user-shield"></i><span>Manage Admin</span></a></li>
        <li><a href="manage-users.php"><i class="fas fa-users"></i><span>Manage Users</span></a></li>
        <li><a href="manage-competition-list.php"><i class="fas fa-list-check"></i><span>Manage Competition</span></a></li>
        <li><a href="manage-judges.php"><i class="fas fa-gavel"></i><span>Manage Judges</span></a></li>
        <li class="logout"><a href="../login.php"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a></li>
    </ul>
</div>

<!-- MAIN -->
<div class="main--content">

    <div class="header--wrapper">
        <div class="header--title">
            <span>Admin Panel</span>
            <h2>Add Admin</h2>
        </div>
        <div class="user--info">
            <img src="<?= $profile_image ?>" alt="">
        </div>
    </div>

    <!-- BACK BUTTON -->
    <a href="manage-admin.php" class="btn-back">
        <i class="fas fa-arrow-left"></i> Back to Manage Admin
    </a>

    <!-- FLASH -->
    <?php if ($success): ?>
        <div class="msg-box msg-success">✅ <?= htmlspecialchars($success) ?></div>
    <?php elseif ($error): ?>
        <div class="msg-box msg-error">❌ <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <!-- FORM CARD -->
    <div class="form-card">
        <div class="form-card-title">
            <i class="fas fa-user-plus"></i> New Admin Account
        </div>

        <form method="POST" id="addAdminForm">
            <div class="form-grid">

                <div class="form-group">
                    <label>Full Name <span class="req">*</span></label>
                    <input type="text" name="fullname"
                           value="<?= htmlspecialchars($_POST['fullname'] ?? '') ?>"
                           placeholder="Enter full name" required>
                </div>

                <div class="form-group">
                    <label>Email <span class="req">*</span></label>
                    <input type="email" name="email"
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                           placeholder="Enter email address" required>
                </div>

                <div class="form-group">
                    <label>Username <span class="req">*</span></label>
                    <input type="text" name="username"
                           value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                           placeholder="Enter username" required>
                </div>

                <div class="form-group">
                    <label>Role <span class="req">*</span></label>
                    <select name="role">
                        <option value="admin"      <?= (($_POST['role'] ?? '') == 'admin')      ? 'selected' : '' ?>>Admin</option>
                        <option value="superadmin" <?= (($_POST['role'] ?? '') == 'superadmin') ? 'selected' : '' ?>>Superadmin</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Password <span class="req">*</span></label>
                    <div class="password-wrapper">
                        <input type="password" name="password" id="password"
                               placeholder="Enter password" required>
                        <span class="toggle-password" data-target="password"></span>
                    </div>
                    <small>8+ characters, uppercase, lowercase, number, special character</small>
                </div>

                <div class="form-group">
                    <label>Confirm Password <span class="req">*</span></label>
                    <div class="password-wrapper">
                        <input type="password" name="confirm_password" id="confirm_password"
                               placeholder="Confirm password" required>
                        <span class="toggle-password" data-target="confirm_password"></span>
                    </div>
                </div>

            </div>

            <small id="formError" class="form-error"></small>

            <div class="form-actions">
                <a href="manage-admin.php" class="btn-cancel">Cancel</a>
                <button type="submit" class="btn-submit">
                    <i class="fas fa-user-plus"></i> Create Admin
                </button>
            </div>
        </form>
    </div>

</div>

<script>
// Toggle password visibility
document.querySelectorAll('.toggle-password').forEach(btn => {
    btn.addEventListener('click', function () {
        const input = document.getElementById(this.dataset.target);
        input.type = input.type === 'password' ? 'text' : 'password';
        this.classList.toggle('active');
    });
});

// Client-side password validation
document.getElementById('addAdminForm').addEventListener('submit', function(e) {
    const pwd     = document.getElementById('password').value;
    const confirm = document.getElementById('confirm_password').value;
    const errEl   = document.getElementById('formError');
    const pattern = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;

    if (!pattern.test(pwd)) {
        e.preventDefault();
        errEl.textContent = 'Password must be at least 8 characters and include uppercase, lowercase, number, and special character.';
        return;
    }
    if (pwd !== confirm) {
        e.preventDefault();
        errEl.textContent = 'Passwords do not match.';
        return;
    }
    errEl.textContent = '';
});
</script>

</body>
</html>