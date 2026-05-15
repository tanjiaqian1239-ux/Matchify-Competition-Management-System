<?php
session_start();
include "../config.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = (int)$_SESSION['user_id'];
$success = '';
$error   = '';

/* =========================
   GET USER DATA
========================= */
$stmt = $conn->prepare("SELECT * FROM users WHERE id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

/* =========================
   UPDATE PROFILE
========================= */
if (isset($_POST['update_profile'])) {

    $fullname = trim($_POST['fullname']);
    $phone    = trim($_POST['phone']);
    $gender   = $_POST['gender'];
    $country  = trim($_POST['country']);

    $profile_image = $user['profile_image'];

    if (!empty($_FILES['avatar']['name'])) {
        $ext     = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
        $allowed = ['jpg','jpeg','png','webp'];

        if (!in_array(strtolower($ext), $allowed)) {
            $error = 'Only JPG, PNG, WEBP images allowed.';
        } else {
            $new_name   = "profile_" . $user_id . "_" . time() . "." . $ext;
            $upload_dir = $_SERVER['DOCUMENT_ROOT'] . "/images/profile/";

            if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

            if (move_uploaded_file($_FILES['avatar']['tmp_name'], $upload_dir . $new_name)) {
                $profile_image = $new_name;
            } else {
                $error = 'Failed to upload image.';
            }
        }
    }

    if (!$error) {
        $upd = $conn->prepare("UPDATE users SET fullname=?, phone=?, gender=?, country=?, profile_image=? WHERE id=?");
        $upd->bind_param("sssssi", $fullname, $phone, $gender, $country, $profile_image, $user_id);
        $upd->execute();
        $success = 'Profile updated successfully!';

        $stmt = $conn->prepare("SELECT * FROM users WHERE id=?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
    }
}

/* =========================
   CHANGE PASSWORD
========================= */
if (isset($_POST['change_password'])) {

    $current  = $_POST['current_password'];
    $new_pass = $_POST['new_password'];
    $confirm  = $_POST['confirm_password'];

    $pattern = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/';

    if (!password_verify($current, $user['password'])) {
        $error = 'Current password is incorrect.';
    } elseif (!preg_match($pattern, $new_pass)) {
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

/* =========================
   COMPETITION STATS
========================= */
$stats_q = $conn->prepare("
    SELECT
        COUNT(*) as total,
        SUM(status='approved') as approved,
        SUM(status='pending')  as pending,
        SUM(status='rejected') as rejected
    FROM competition_applications
    WHERE organizer_id = ?
");
$stats_q->bind_param("i", $user_id);
$stats_q->execute();
$stats = $stats_q->get_result()->fetch_assoc();

/* =========================
   RECENT COMPETITIONS
========================= */
$recent_q = $conn->prepare("
    SELECT id, title, category, status, created_at
    FROM competition_applications
    WHERE organizer_id = ?
    ORDER BY created_at DESC
    LIMIT 5
");
$recent_q->bind_param("i", $user_id);
$recent_q->execute();
$recent = $recent_q->get_result();

/* =========================
   PROFILE IMAGE
========================= */
$avatar = "../images/profile.avif";
if (!empty($user['profile_image'])) {
    $path = "/images/profile/" . $user['profile_image'];
    if (file_exists($_SERVER['DOCUMENT_ROOT'] . $path)) {
        $avatar = $path;
    }
}

/* =========================
   ACTIVE TAB
========================= */
$active_tab = 'edit';
if (isset($_POST['change_password'])) $active_tab = 'password';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Profile – Matchify</title>
<link rel="icon" type="image/png" href="../images/logo.png">
<link rel="stylesheet" href="../Organiser-css/profile.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar">
    <img src="../images/logo.png" class="logo">
    <ul>
        <li><a href="index.php">Home</a></li>
        <li><a href="competition-list.php">Competition List</a></li>
        <li><a href="about.php">About</a></li>
        <li><a href="contact.php">Contact</a></li>
    </ul>
    <div class="nav-profile">
        <img src="<?= $avatar ?>" class="nav-avatar" onclick="toggleNavMenu()">
        <div class="nav-dropdown" id="navDropdown">
            <a href="profile.php"><i class="fas fa-user"></i> My Profile</a>
            <a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>
</nav>

<div class="page">

    <!-- FLASH -->
    <?php if ($success): ?>
        <div class="flash success">✅ <?= htmlspecialchars($success) ?></div>
    <?php elseif ($error): ?>
        <div class="flash error">❌ <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <!-- PROFILE HEADER -->
    <div class="card profile-header">
        <div class="avatar-wrap">
            <img src="<?= $avatar ?>" id="avatarPreview">
        </div>
        <div class="header-info">
            <h2><?= htmlspecialchars($user['fullname'] ?? '') ?></h2>
            <p><?= htmlspecialchars($user['email'] ?? '') ?></p>
            <span class="role-badge">Organiser</span>
        </div>

        <!-- STATS ROW -->
        <div class="stats-row">
            <div class="stat-box">
                <div class="stat-num"><?= $stats['total'] ?? 0 ?></div>
                <div class="stat-label">Total</div>
            </div>
            <div class="stat-box approved">
                <div class="stat-num"><?= $stats['approved'] ?? 0 ?></div>
                <div class="stat-label">Approved</div>
            </div>
            <div class="stat-box pending">
                <div class="stat-num"><?= $stats['pending'] ?? 0 ?></div>
                <div class="stat-label">Pending</div>
            </div>
            <div class="stat-box rejected">
                <div class="stat-num"><?= $stats['rejected'] ?? 0 ?></div>
                <div class="stat-label">Rejected</div>
            </div>
        </div>
    </div>

    <!-- TABS -->
    <div class="tabs">
        <button class="tab-btn <?= $active_tab=='edit'        ? 'active':'' ?>" onclick="switchTab('edit',        this)">✏️ Edit Profile</button>
        <button class="tab-btn <?= $active_tab=='password'    ? 'active':'' ?>" onclick="switchTab('password',    this)">🔒 Change Password</button>
        <button class="tab-btn <?= $active_tab=='competitions' ? 'active':'' ?>" onclick="switchTab('competitions', this)">🏆 My Competitions</button>
    </div>

    <!-- TAB: EDIT PROFILE -->
    <div class="tab-content <?= $active_tab=='edit' ? 'active':'' ?>" id="tab-edit">
        <div class="card">
            <div class="card-title">✏️ Edit Profile</div>
            <form method="POST" enctype="multipart/form-data">

                <div class="photo-section">
                    <div class="photo-wrap">
                        <img src="<?= $avatar ?>" id="avatarPreview2">
                        <label for="avatarInput" class="photo-overlay">
                            <i class="fas fa-camera"></i>
                            <span>Change Photo</span>
                        </label>
                    </div>
                    <input type="file" name="avatar" id="avatarInput" accept="image/*" style="display:none" onchange="previewAvatar(this)">
                    <p class="photo-hint">JPG, PNG, WEBP — max 2MB</p>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" name="fullname" value="<?= htmlspecialchars($user['fullname'] ?? '') ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>" readonly>
                    </div>
                    <div class="form-group">
                        <label>Phone</label>
                        <input type="text" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>Gender</label>
                        <select name="gender">
                            <option value="">-- Select --</option>
                            <option value="Male"   <?= ($user['gender'] ?? '') == 'Male'   ? 'selected' : '' ?>>Male</option>
                            <option value="Female" <?= ($user['gender'] ?? '') == 'Female' ? 'selected' : '' ?>>Female</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Country</label>
                        <input type="text" name="country" value="<?= htmlspecialchars($user['country'] ?? '') ?>">
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
    <div class="tab-content <?= $active_tab=='password' ? 'active':'' ?>" id="tab-password">
        <div class="card">
            <div class="card-title">🔒 Change Password</div>
            <form method="POST" id="passwordForm">
                <div class="form-grid">

                    <div class="form-group full">
                        <label>Current Password</label>
                        <div class="password-wrapper">
                            <input type="password" name="current_password" id="currentPassword" placeholder="Enter current password" required>
                            <span class="toggle-password" data-target="currentPassword"></span>
                        </div>
                    </div>

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

    <!-- TAB: MY COMPETITIONS -->
    <div class="tab-content <?= $active_tab=='competitions' ? 'active':'' ?>" id="tab-competitions">
        <div class="card">
            <div class="card-title">🏆 My Competitions</div>

            <?php
            $recent_rows = [];
            while ($r = $recent->fetch_assoc()) $recent_rows[] = $r;
            ?>

            <?php if (empty($recent_rows)): ?>
                <div class="empty-state">You have not created any competition yet.</div>
            <?php else: ?>
                <?php foreach ($recent_rows as $r): ?>
                <div class="history-item">
                    <div class="history-info">
                        <h4><?= htmlspecialchars($r['title']) ?></h4>
                        <p>📌 <?= htmlspecialchars($r['category']) ?> &nbsp;|&nbsp; Created: <?= date('d M Y', strtotime($r['created_at'])) ?></p>
                    </div>
                    <div class="history-right">
                        <span class="badge <?= $r['status'] ?>"><?= ucfirst($r['status']) ?></span>
                        <div style="margin-top:6px;">
                            <a href="review-result.php?id=<?= $r['id'] ?>" style="font-size:12px; color:#6c63ff; text-decoration:none; font-weight:600;">View →</a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>

                <div style="text-align:center; margin-top:16px;">
                    <a href="dashboard.php" style="font-size:13px; color:#6c63ff; font-weight:600; text-decoration:none;">View All in Dashboard →</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

</div>

<script>
function switchTab(tab, btn) {
    document.querySelectorAll('.tab-content').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.getElementById('tab-' + tab).classList.add('active');
    btn.classList.add('active');
}

function previewAvatar(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            document.getElementById('avatarPreview').src  = e.target.result;
            document.getElementById('avatarPreview2').src = e.target.result;
        };
        reader.readAsDataURL(input.files[0]);
    }
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

function toggleNavMenu() {
    const d = document.getElementById('navDropdown');
    d.style.display = d.style.display === 'block' ? 'none' : 'block';
}

document.addEventListener('click', function(e) {
    const nav = document.querySelector('.nav-profile');
    const d   = document.getElementById('navDropdown');
    if (nav && !nav.contains(e.target)) d.style.display = 'none';
});
</script>

</body>
</html>