<?php
session_start();
include "../config.php";

$msg = $_GET['msg'] ?? '';

/* =========================
   GET COMPETITIONS
========================= */
$comps_result = $conn->query("
    SELECT 
        ca.id,
        ca.title,
        ca.start_date,
        ca.end_date,
        ca.category,
        ca.competition_image,

        COUNT(CASE WHEN cj.status='pending' THEN 1 END) AS pending_count,
        COUNT(CASE WHEN cj.status='approved' THEN 1 END) AS approved_count,
        COUNT(CASE WHEN cj.status='rejected' THEN 1 END) AS rejected_count,
        COUNT(cj.id) AS total_count

    FROM competition_applications ca

    LEFT JOIN competition_judges cj 
        ON cj.competition_id = ca.id

    WHERE ca.status = 'approved'

    GROUP BY ca.id

    ORDER BY ca.created_at DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>

<meta charset="UTF-8">
<title>Manage Judges</title>

<link rel="icon" type="image/png" href="../images/logo.png">

<link rel="stylesheet" href="../Admin-css/dashboard.css">
<link rel="stylesheet" href="../Admin-css/manage_judges.css">

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

</head>

<body>

<!-- SIDEBAR -->
<div class="sidebar">

    <div class="logo"></div>

    <ul class="menu">

        <li>
            <a href="dashboard.php">
                <i class="fas fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
        </li>

        <li>
            <a href="#">
                <i class="fas fa-user"></i>
                <span>Profile</span>
            </a>
        </li>

        <li>
            <a href="manage-competition-list.php">
                <i class="fas fa-list-check"></i>
                <span>Manage Competition</span>
            </a>
        </li>

        <li class="active">
            <a href="manage-judges.php">
                <i class="fas fa-gavel"></i>
                <span>Manage Judges</span>
            </a>
        </li>

        <li>
            <a href="#">
                <i class="fas fa-cog"></i>
                <span>Settings</span>
            </a>
        </li>

        <li class="logout">
            <a href="../login.php">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </li>

    </ul>

</div>

<!-- MAIN -->
<div class="main--content">

    <!-- HEADER -->
    <div class="header--wrapper">

        <div class="header--title">
            <span>Admin</span>
            <h2>Manage Judges</h2>
        </div>

        <div class="user--info">
            <img src="../images/profile.avif" alt="">
        </div>

    </div>

    <!-- MESSAGE -->
    <?php if ($msg === 'approved'): ?>

        <div class="msg-box msg-success">
            Judge approved and email sent successfully!
        </div>

    <?php elseif ($msg === 'approved_no_email'): ?>

        <div class="msg-box msg-warning">
            Judge approved but email failed.
        </div>

    <?php elseif ($msg === 'rejected'): ?>

        <div class="msg-box msg-rejected">
            Judge rejected successfully.
        </div>

    <?php endif; ?>

    <!-- GRID -->
    <div class="judges-grid">

        <?php if ($comps_result->num_rows == 0): ?>

            <p style="color:#999;text-align:center;padding:40px;">
                No approved competitions found.
            </p>

        <?php endif; ?>

        <?php while ($comp = $comps_result->fetch_assoc()): ?>

        <?php
        /* =========================
           IMAGE FIX
        ========================= */

        $img = (!empty($comp['competition_image']) 
                && $comp['competition_image'] != '0')
            ? "../uploads/" . $comp['competition_image']
            : "../images/competition_banner.jpg";
        ?>

        <!-- CARD -->
        <a href="manage-judges-detail.php?id=<?= $comp['id'] ?>" 
           class="comp-judge-card">

            <!-- IMAGE -->
            <div class="comp-img-wrapper">

                <img src="<?= $img ?>" alt="banner">

                <?php if ($comp['pending_count'] > 0): ?>

                    <span class="pending-badge">
                        <?= $comp['pending_count'] ?>
                    </span>

                <?php endif; ?>

            </div>

            <!-- INFO -->
            <div class="comp-judge-info">

                <h3>
                    <?= htmlspecialchars($comp['title']) ?>
                </h3>

                <p class="comp-cat">
                    Category:
                    <?= htmlspecialchars($comp['category']) ?>
                </p>

                <p class="comp-date">
                    Date:
                    <?= $comp['start_date'] ?>
                    →
                    <?= $comp['end_date'] ?>
                </p>

                <!-- STATS -->
                <div class="judge-stats">

                    <span class="stat pending">
                        Pending:
                        <?= $comp['pending_count'] ?>
                    </span>

                    <span class="stat approved">
                        Approved:
                        <?= $comp['approved_count'] ?>
                    </span>

                    <span class="stat rejected">
                        Rejected:
                        <?= $comp['rejected_count'] ?>
                    </span>

                </div>

            </div>

            <!-- ARROW -->
            <div class="comp-arrow">
                <i class="fas fa-chevron-right"></i>
            </div>

        </a>

        <?php endwhile; ?>

    </div>

</div>

</body>
</html>