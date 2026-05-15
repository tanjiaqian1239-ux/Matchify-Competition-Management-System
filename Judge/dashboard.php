<?php
session_start();
include "../config.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'judge') {
    header("Location: ../login.php");
    exit();
}

$judge_id = (int) $_SESSION['user_id'];

// Get judge info
$judge = $conn->query("SELECT * FROM users WHERE id = $judge_id")->fetch_assoc();

// Profile image
$profile_image = "../images/profile.avif";
if (!empty($judge['profile_image'])) {
    $path = "/images/profile/" . $judge['profile_image'];
    if (file_exists($_SERVER['DOCUMENT_ROOT'] . $path)) {
        $profile_image = $path;
    }
}

// Get assigned competitions
$competitions = $conn->query("
    SELECT ca.*, cj.id AS judge_record_id
    FROM competition_judges cj
    INNER JOIN competition_applications ca ON cj.competition_id = ca.id
    WHERE cj.judge_id = $judge_id AND cj.status = 'approved'
    ORDER BY ca.start_date ASC
");

// Count stats
$total_competitions = $conn->query("SELECT COUNT(*) total FROM competition_judges WHERE judge_id = $judge_id AND status = 'approved'")->fetch_assoc()['total'];
$total_scored = $conn->query("SELECT COUNT(DISTINCT competition_id) total FROM competition_scores WHERE judge_id = $judge_id")->fetch_assoc()['total'];
$pending_score = $total_competitions - $total_scored;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Judge Dashboard</title>
    <link rel="icon" type="image/png" href="../images/logo.png">
    <link rel="stylesheet" href="../Admin-css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<!-- SIDEBAR -->
<div class="sidebar">
    <div class="logo"></div>
    <ul class="menu">
        <li class="active">
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

    <!-- HEADER -->
    <div class="header--wrapper">
        <div class="header--title">
            <span>Judge</span>
            <h2>Judge Dashboard</h2>
        </div>
        <div class="user--info">
            <div class="search--box">
                <i class="fa-solid fa-search"></i>
                <input type="text" placeholder="Search">
            </div>
            <img src="<?= $profile_image ?>" alt="Profile">
        </div>
    </div>

    <!-- STATS CARDS -->
    <div class="card--container">
        <div class="card--wrapper">

            <div class="competition--card light-blue">
                <div class="card--header">
                    <div class="amount">
                        <span class="title">Assigned Competitions</span>
                        <span class="amount--value"><?= $total_competitions ?></span>
                    </div>
                    <i class="fas fa-trophy icon dark-blue"></i>
                </div>
                <span class="card--detail">Total competitions assigned</span>
            </div>

            <div class="competition--card light-purple">
                <div class="card--header">
                    <div class="amount">
                        <span class="title">Scored</span>
                        <span class="amount--value"><?= $total_scored ?></span>
                    </div>
                    <i class="fas fa-check-circle icon dark-purple"></i>
                </div>
                <span class="card--detail">Competitions scored</span>
            </div>

            <div class="competition--card light-red">
                <div class="card--header">
                    <div class="amount">
                        <span class="title">Pending Scoring</span>
                        <span class="amount--value"><?= $pending_score < 0 ? 0 : $pending_score ?></span>
                    </div>
                    <i class="fas fa-clock icon dark-red"></i>
                </div>
                <span class="card--detail">Awaiting your scores</span>
            </div>

        </div>
    </div>

    <!-- COMPETITION LIST -->
    <div class="card--container">
        <div class="section--header">
            <h3 class="main--title">My Assigned Competitions</h3>
            <span class="pending-label"><?= $total_competitions ?> Total</span>
        </div>

        <?php if ($competitions->num_rows === 0): ?>
            <p style="color:#aaa; text-align:center; padding:30px;">No competitions assigned yet.</p>
        <?php else: ?>

        <?php while ($comp = $competitions->fetch_assoc()):
            $today = date('Y-m-d');
            if ($today < $comp['start_date']) {
                $comp_status = "Not Started";
                $status_color = "pending";
            } elseif ($today > $comp['end_date']) {
                $comp_status = "Ended";
                $status_color = "rejected";
            } else {
                $comp_status = "Ongoing";
                $status_color = "approved";
            }

            $img = (!empty($comp['competition_image']) && $comp['competition_image'] != '0')
                ? '../uploads/' . $comp['competition_image']
                : '../images/default.png';

            // Count participants and submissions
            $p_count = $conn->query("SELECT COUNT(*) total FROM competition_participants WHERE competition_id = " . $comp['id'])->fetch_assoc()['total'];
            $s_count = $conn->query("SELECT COUNT(*) total FROM competition_submissions WHERE competition_id = " . $comp['id'])->fetch_assoc()['total'];
            $scored_count = $conn->query("SELECT COUNT(*) total FROM competition_scores WHERE competition_id = " . $comp['id'] . " AND judge_id = $judge_id")->fetch_assoc()['total'];
        ?>

        <div class="proposal--card" style="margin-bottom:16px; align-items:flex-start;">
            <div class="proposal--left" style="display:flex; gap:16px; flex:1;">

                <img src="<?= htmlspecialchars($img) ?>"
                     onerror="this.src='../images/default.png'"
                     style="width:100px; height:80px; object-fit:cover; border-radius:10px; flex-shrink:0;">

                <div>
                    <h4 style="margin-bottom:6px;"><?= htmlspecialchars($comp['title']) ?></h4>
                    <span class="tag"><?= htmlspecialchars($comp['category']) ?></span>
                    <span class="status <?= $status_color ?>" style="margin-left:6px;"><?= $comp_status ?></span>

                    <div class="meta" style="margin-top:8px;">
                        <span><i class="fa-solid fa-calendar"></i> <?= $comp['start_date'] ?> → <?= $comp['end_date'] ?></span>
                        <span><i class="fa-solid fa-users"></i> <?= $p_count ?> Participants</span>
                        <span><i class="fa-solid fa-file"></i> <?= $s_count ?> Submissions</span>
                        <span><i class="fa-solid fa-star"></i> <?= $scored_count ?> Scored</span>
                    </div>
                </div>
            </div>

            <div class="proposal--right">
                <a href="score-competition.php?id=<?= $comp['id'] ?>"
                   style="display:block; text-align:center; padding:10px 20px; background:linear-gradient(135deg,#7163ba,#a855f7); color:#fff; border-radius:8px; text-decoration:none; font-weight:600; font-size:13px;">
                    <i class="fas fa-gavel"></i> Score Now
                </a>
                <a href="view-submissions.php?id=<?= $comp['id'] ?>"
                   style="display:block; text-align:center; padding:10px 20px; background:#f0eeff; color:#7163ba; border-radius:8px; text-decoration:none; font-weight:600; font-size:13px; margin-top:8px;">
                    <i class="fas fa-eye"></i> View Submissions
                </a>
            </div>
        </div>

        <?php endwhile; ?>
        <?php endif; ?>

    </div>

</div>

</body>
</html>