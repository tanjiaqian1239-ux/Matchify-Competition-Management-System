<?php
session_start();
include "../config.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'judge') {
    header("Location: ../login.php");
    exit();
}

$judge_id = (int) $_SESSION['user_id'];
$comp_id  = intval($_GET['id'] ?? 0);

// Get competition info
$comp = $conn->query("SELECT * FROM competition_applications WHERE id = $comp_id")->fetch_assoc();

if (!$comp) {
    echo "<script>alert('Competition not found.'); window.location.href='dashboard.php';</script>";
    exit();
}

// Get all submissions for this competition
$submissions = $conn->query("
    SELECT cs.*, u.fullname, u.email
    FROM competition_submissions cs
    JOIN users u ON cs.user_id = u.id
    WHERE cs.competition_id = $comp_id
    ORDER BY cs.submitted_at DESC
");

// Profile image
$profile_image = "../images/profile.avif";
$judge = $conn->query("SELECT profile_image FROM users WHERE id = $judge_id")->fetch_assoc();
if (!empty($judge['profile_image'])) {
    $path = "/images/profile/" . $judge['profile_image'];
    if (file_exists($_SERVER['DOCUMENT_ROOT'] . $path)) {
        $profile_image = $path;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>View Submissions - <?= htmlspecialchars($comp['title']) ?></title>
<link rel="icon" type="image/png" href="../images/logo.png">
<link rel="stylesheet" href="../Admin-css/dashboard.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
.page-wrapper {
    max-width: 1000px;
    margin: 0 auto;
    padding: 20px;
}

.back-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    text-decoration: none;
    color: #7163ba;
    font-weight: 600;
    font-size: 14px;
    margin-bottom: 20px;
}

.back-btn:hover { opacity: 0.75; }

.comp-info-bar {
    background: #f3f1ff;
    border: 1px solid #ebe8ff;
    border-radius: 14px;
    padding: 16px 20px;
    margin-bottom: 24px;
    font-size: 13px;
    color: #555;
    display: flex;
    flex-wrap: wrap;
    gap: 16px;
}

.comp-info-bar strong {
    color: #1e1b4b;
    font-size: 16px;
    display: block;
    margin-bottom: 6px;
}

.submission-card {
    background: #fff;
    border-radius: 16px;
    padding: 20px;
    margin-bottom: 16px;
    box-shadow: 0 4px 18px rgba(0,0,0,0.06);
    border: 1px solid #f0eef8;
}

.sub-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 12px;
}

.sub-title {
    font-size: 16px;
    font-weight: 700;
    color: #1e1b4b;
    margin-bottom: 4px;
}

.sub-participant {
    font-size: 13px;
    color: #666;
}

.sub-date {
    font-size: 12px;
    color: #999;
    white-space: nowrap;
}

.sub-desc {
    font-size: 13px;
    color: #555;
    line-height: 1.6;
    margin-bottom: 14px;
    background: #fafafa;
    padding: 10px 14px;
    border-radius: 10px;
}

.sub-actions {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.btn-download {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 16px;
    border-radius: 8px;
    text-decoration: none;
    font-size: 13px;
    font-weight: 600;
    background: #6c63ff;
    color: #fff;
    transition: 0.2s;
}

.btn-download:hover { opacity: 0.85; }

.btn-link {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 16px;
    border-radius: 8px;
    text-decoration: none;
    font-size: 13px;
    font-weight: 600;
    background: #f0eeff;
    color: #6c63ff;
    transition: 0.2s;
}

.btn-link:hover { background: #e5e0ff; }

.empty-state {
    text-align: center;
    padding: 50px 20px;
    color: #aaa;
    font-size: 15px;
}

.empty-state i {
    font-size: 40px;
    margin-bottom: 12px;
    display: block;
    color: #ddd;
}

.count-badge {
    background: #7163ba;
    color: #fff;
    padding: 3px 10px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 700;
    margin-left: 8px;
}
</style>
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
            <a href="profile.php">
                <i class="fas fa-user"></i>
                <span>Profile</span>
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

    <div class="header--wrapper">
        <div class="header--title">
            <span>Judge</span>
            <h2>View Submissions</h2>
        </div>
        <div class="user--info">
            <img src="<?= $profile_image ?>" alt="Profile">
        </div>
    </div>

    <div class="page-wrapper">

        <a href="dashboard.php" class="back-btn">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>

        <!-- Competition Info -->
        <div class="comp-info-bar">
            <div>
                <strong><?= htmlspecialchars($comp['title']) ?></strong>
                <span>📌 <?= htmlspecialchars($comp['category']) ?></span>
            </div>
            <div>
                <span>📅 <?= $comp['start_date'] ?> → <?= $comp['end_date'] ?></span>
            </div>
            <div>
                <span>Total Submissions <span class="count-badge"><?= $submissions->num_rows ?></span></span>
            </div>
        </div>

        <!-- Submissions -->
        <?php if ($submissions->num_rows === 0): ?>
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                No submissions yet for this competition.
            </div>
        <?php else: ?>

            <?php while ($sub = $submissions->fetch_assoc()): ?>
            <div class="submission-card">

                <div class="sub-header">
                    <div>
                        <div class="sub-title"><?= htmlspecialchars($sub['title'] ?? 'Untitled Submission') ?></div>
                        <div class="sub-participant">
                            <i class="fas fa-user"></i>
                            <?= htmlspecialchars($sub['fullname']) ?>
                            &nbsp;·&nbsp;
                            <?= htmlspecialchars($sub['email']) ?>
                        </div>
                    </div>
                    <div class="sub-date">
                        <i class="fas fa-clock"></i>
                        <?= date("d M Y, h:i A", strtotime($sub['submitted_at'])) ?>
                    </div>
                </div>

                <?php if (!empty($sub['description'])): ?>
                <div class="sub-desc">
                    <?= nl2br(htmlspecialchars($sub['description'])) ?>
                </div>
                <?php endif; ?>

                <div class="sub-actions">
                    <?php if (!empty($sub['file_path'])): ?>
                        <a href="../uploads/<?= htmlspecialchars($sub['file_path']) ?>" 
                           target="_blank" class="btn-download">
                            <i class="fas fa-download"></i> Download File
                        </a>
                    <?php endif; ?>

                    <?php if (!empty($sub['link'])): ?>
                        <a href="<?= htmlspecialchars($sub['link']) ?>" 
                           target="_blank" class="btn-link">
                            <i class="fas fa-external-link-alt"></i> View Link
                        </a>
                    <?php endif; ?>
                </div>

            </div>
            <?php endwhile; ?>

        <?php endif; ?>

    </div>
</div>

</body>
</html>