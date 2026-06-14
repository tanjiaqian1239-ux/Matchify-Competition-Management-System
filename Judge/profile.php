<?php
session_start();
include "../config.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'judge') {
    header("Location: ../login.php");
    exit();
}

$user_id = (int)$_SESSION['user_id'];
$success = '';
$error   = '';

$stmt = $conn->prepare("SELECT * FROM users WHERE id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (isset($_POST['update_profile'])) {
    $fullname = trim($_POST['fullname']);
    $phone    = trim($_POST['phone']);

    $upd = $conn->prepare("UPDATE users SET fullname=?, phone=? WHERE id=?");
    $upd->bind_param("ssi", $fullname, $phone, $user_id);
    $upd->execute();
    $success = 'Profile updated successfully!';

    $stmt = $conn->prepare("SELECT * FROM users WHERE id=?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
}

if (isset($_POST['change_password'])) {
    $new_pass = $_POST['new_password'];
    $confirm  = $_POST['confirm_password'];

    $pattern = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/';

    if (!preg_match($pattern, $new_pass)) {
        $error = 'Password must be at least 8 characters and include uppercase, lowercase, number, and special character.';
    } elseif ($new_pass !== $confirm) {
        $error = 'New passwords do not match.';
    } else {
        $hashed = password_hash($new_pass, PASSWORD_DEFAULT);
        $upd = $conn->prepare("UPDATE users SET password=? WHERE id=?");
        $upd->bind_param("si", $hashed, $user_id);
        $upd->execute();
        $success = 'Password changed successfully!';
    }
}

$active_tab = 'edit';
if (isset($_POST['change_password'])) $active_tab = 'password';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Profile – Judge</title>
<link rel="icon" type="image/png" href="../images/logo.png">
<link rel="stylesheet" href="../Admin-css/dashboard.css">
<link rel="stylesheet" href="../Judge-css/profile.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<!-- SIDEBAR -->
<div class="sidebar">
    <div class="logo"></div>
    <ul class="menu">
        <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a></li>
        <li class="active"><a href="profile.php"><i class="fas fa-user"></i><span>Profile</span></a></li>
        <li class="logout"><a href="../login.php"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a></li>
    </ul>
</div>

<!-- MAIN CONTENT -->
<div class="main--content">

    <div class="header--wrapper">
        <div class="header--title">
            <span>Judge</span>
            <h2>My Profile</h2>
        </div>
    </div>

    <div class="profile-page">

        <?php if ($success): ?>
            <div class="flash success">✅ <?= htmlspecialchars($success) ?></div>
        <?php elseif ($error): ?>
            <div class="flash error">❌ <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="profile-header-card">
            <div class="avatar-wrap">
                <i class="fas fa-user-tie avatar-icon"></i>
            </div>
            <div class="header-info">
                <h2><?= htmlspecialchars($user['fullname'] ?? '') ?></h2>
                <p><?= htmlspecialchars($user['email'] ?? '') ?></p>
                <span class="role-badge">Judge</span>
            </div>
        </div>

        <div class="profile-tabs">
            <button class="ptab-btn <?= $active_tab=='edit'     ? 'active':'' ?>" onclick="switchTab('edit',     this)">✏️ Edit Profile</button>
            <button class="ptab-btn <?= $active_tab=='password' ? 'active':'' ?>" onclick="switchTab('password', this)">🔒 Change Password</button>
        </div>

        <!-- TAB: EDIT PROFILE -->
        <div class="ptab-content <?= $active_tab=='edit' ? 'active':'' ?>" id="tab-edit">
            <div class="profile-card">
                <div class="card-title">✏️ Edit Profile</div>
                <form method="POST">
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Full Name</label>
                            <input type="text" name="fullname" value="<?= htmlspecialchars($user['fullname'] ?? '') ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Phone</label>
                            <input type="text" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>" readonly>
                        </div>
                        <div class="form-group">
                            <label>Username</label>
                            <input type="text" value="<?= htmlspecialchars($user['username'] ?? '') ?>" readonly>
                        </div>
                    </div>
                    <button type="submit" name="update_profile" class="btn-save">Save Changes</button>
                </form>
            </div>
        </div>

        <!-- TAB: CHANGE PASSWORD -->
        <div class="ptab-content <?= $active_tab=='password' ? 'active':'' ?>" id="tab-password">
            <div class="profile-card">
                <div class="card-title">🔒 Change Password</div>
                <p class="pw-note">💡 Set a new password for your account.</p>
                <form method="POST" id="passwordForm">
                    <div class="form-grid">

                        <div class="form-group">
                            <label>New Password</label>
                            <div class="password-wrapper">
                                <input type="password" name="new_password" id="newPassword" placeholder="Enter new password" required>
                                <span class="toggle-password" data-target="newPassword"></span>
                            </div>
                            <small>8+ characters, uppercase, lowercase, number, special character</small>
                        </div>

                        <div class="form-group">
                            <label>Confirm New Password</label>
                            <div class="password-wrapper">
                                <input type="password" name="confirm_password" id="confirmPassword" placeholder="Confirm new password" required>
                                <span class="toggle-password" data-target="confirmPassword"></span>
                            </div>
                        </div>

                    </div>

                    <small id="passwordError" class="pw-error"></small>
                    <button type="submit" name="change_password" class="btn-save">Change Password</button>
                </form>
            </div>
        </div>

    </div>
</div>

<script>
function switchTab(tab, btn) {
    document.querySelectorAll('.ptab-content').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.ptab-btn').forEach(b => b.classList.remove('active'));
    document.getElementById('tab-' + tab).classList.add('active');
    btn.classList.add('active');
}

document.querySelectorAll('.toggle-password').forEach(btn => {
    btn.addEventListener('click', function () {
        const input = document.getElementById(this.dataset.target);
        input.type = input.type === 'password' ? 'text' : 'password';
        this.classList.toggle('active');
    });
});

document.getElementById('passwordForm').addEventListener('submit', function(e) {
    const pwd     = document.getElementById('newPassword').value;
    const confirm = document.getElementById('confirmPassword').value;
    const errEl   = document.getElementById('passwordError');
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