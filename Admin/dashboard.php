<?php
session_start();
include "../config.php";

/* =========================
   ROLE CHECK
========================= */
requireRole(['admin','superadmin']);

$user_id = $_SESSION['user_id'] ?? 0;

/* =========================
   PROFILE IMAGE
========================= */
$profile_image = "../images/profile.avif";

$uq = $conn->query("SELECT profile_image FROM users WHERE id = $user_id");
if ($uq && $uq->num_rows > 0) {
    $u = $uq->fetch_assoc();
    if (!empty($u['profile_image'])) {
        $path = "/images/profile/" . $u['profile_image'];
        if (file_exists($_SERVER['DOCUMENT_ROOT'] . $path)) {
            $profile_image = $path;
        }
    }
}

/* =========================
   DASHBOARD STATS
========================= */
$total_users = $conn->query("SELECT COUNT(*) AS c FROM users")->fetch_assoc()['c'];

$total_admins = $conn->query("
    SELECT COUNT(*) AS c 
    FROM users 
    WHERE role IN ('admin','superadmin')
")->fetch_assoc()['c'];

$total_participants = $conn->query("
    SELECT COUNT(*) AS c 
    FROM users 
    WHERE role='participant'
")->fetch_assoc()['c'];

$pending_competitions = $conn->query("
    SELECT COUNT(*) AS c 
    FROM competition_applications 
    WHERE status='pending'
")->fetch_assoc()['c'];

$pending_users = $conn->query("
    SELECT COUNT(*) AS c 
    FROM users 
    WHERE status='pending'
")->fetch_assoc()['c'];

/* =========================
   RECENT DATA
========================= */
$recent_users = $conn->query("
    SELECT fullname, email, role, created_at 
    FROM users 
    ORDER BY created_at DESC 
    LIMIT 5
");

$recent_comp = $conn->query("
    SELECT title, category, organizer, start_date, end_date, created_at
    FROM competition_applications 
    ORDER BY created_at DESC 
    LIMIT 3
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Dashboard</title>

<link rel="icon" href="../images/logo.png">
<link rel="stylesheet" href="../Admin-css/dashboard.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>

<!-- SIDEBAR (UNCHANGED) -->
<div class="sidebar">
    <div class="logo"></div>
    <ul class="menu">
        <li class="active"><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a></li>
        <li><a href="profile.php"><i class="fas fa-user"></i><span>Profile</span></a></li>
        <li><a href="manage-admin.php"><i class="fas fa-user-shield"></i><span>Manage Admin</span></a></li>
        <li><a href="manage-users.php"><i class="fas fa-users"></i><span>Manage Users</span></a></li>
        <li><a href="manage-competition-list.php"><i class="fas fa-list-check"></i><span>Competition</span></a></li>
        <li><a href="manage-judges.php"><i class="fas fa-gavel"></i><span>Judges</span></a></li>
        <li class="logout"><a href="../login.php"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a></li>
    </ul>
</div>

<!-- MAIN -->
<div class="main--content">

<!-- HEADER -->
<div class="header--wrapper">
    <div class="header--title">
        <span>Admin Panel</span>
        <h2>Dashboard Overview</h2>
    </div>

    <div class="user--info">
        <img src="<?= $profile_image ?>" alt="">
    </div>
</div>

<!-- STATS CARDS -->
<div class="card--container">
    <div class="card--wrapper">

        <div class="competition--card light-red">
            <div class="card--header">
                <div class="amount">
                    <span class="title">Total Users</span>
                    <span class="amount--value"><?= $total_users ?></span>
                </div>
                <i class="fa-solid fa-users icon"></i>
            </div>
            <span class="card--detail">All registered users</span>
        </div>

        <div class="competition--card light-purple">
            <div class="card--header">
                <div class="amount">
                    <span class="title">Admins</span>
                    <span class="amount--value"><?= $total_admins ?></span>
                </div>
                <i class="fa-solid fa-user-shield icon dark-purple"></i>
            </div>
            <span class="card--detail">Admin & Superadmin</span>
        </div>

        <div class="competition--card light-blue">
            <div class="card--header">
                <div class="amount">
                    <span class="title">Pending Approvals</span>
                    <span class="amount--value"><?= $pending_competitions + $pending_users ?></span>
                </div>
                <i class="fa-solid fa-clock icon dark-blue"></i>
            </div>
            <span class="card--detail">System pending tasks</span>
        </div>

        <div class="competition--card light-purple">
            <div class="card--header">
                <div class="amount">
                    <span class="title">Participants</span>
                    <span class="amount--value"><?= $total_participants ?></span>
                </div>
                <i class="fa-solid fa-user icon dark-purple"></i>
            </div>
            <span class="card--detail">Active participants</span>
        </div>

    </div>
</div>

<!-- RECENT USERS -->
<div class="card--container">
    <div class="section--header">
        <h3 class="main--title">Recent Users</h3>
    </div>

    <table class="table--container">
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Created</th>
            </tr>
        </thead>
        <tbody>
        <?php while($u = $recent_users->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($u['fullname']) ?></td>
                <td><?= htmlspecialchars($u['email']) ?></td>
                <td><?= htmlspecialchars($u['role']) ?></td>
                <td><?= $u['created_at'] ?></td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>

<!-- RECENT COMPETITIONS -->
<div class="card--container">
    <div class="section--header">
        <h3 class="main--title">Recent Competitions</h3>
    </div>

    <?php while($c = $recent_comp->fetch_assoc()): ?>
    <div class="proposal--card">

        <div class="proposal--left">
            <h4><?= htmlspecialchars($c['title']) ?></h4>

            <span class="tag"><?= htmlspecialchars($c['category']) ?></span>

            <p class="organizer"><?= htmlspecialchars($c['organizer']) ?></p>

            <div class="meta">
                <span><i class="fa-solid fa-calendar"></i> <?= $c['start_date'] ?> - <?= $c['end_date'] ?></span>
            </div>
        </div>

        <div class="proposal--right">
            <button class="approve">View</button>
        </div>

    </div>
    <?php endwhile; ?>

</div>

</div>

</body>
</html>