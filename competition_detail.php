<?php
session_start();
include "config.php";

$id = intval($_GET['id']);

$stmt = $conn->prepare("SELECT * FROM competition_applications WHERE id=? AND organizer_id=?");
$stmt->bind_param("ii", $id, $_SESSION['user_id']);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();

if (!$row) {
    echo "<script>alert('Competition not found.'); window.location.href='competition-list.php';</script>";
    exit();
}

$image = !empty($row['competition_image'])
    ? "uploads/" . $row['competition_image']
    : "images/competition_banner.jpg";

$today     = date('Y-m-d');
$app_start = !empty($row['application_start']) ? trim($row['application_start']) : null;
$app_end   = !empty($row['application_end'])   ? trim($row['application_end'])   : null;

if (!$app_start || !$app_end) {
    $btn_status = "open";
} elseif ($today < $app_start) {
    $btn_status = "not_open";
} elseif ($today > $app_end) {
    $btn_status = "expired";
} else {
    $btn_status = "open";
}

$profile_image = "../images/profile.avif";
if (isset($_SESSION['user_id'])) {
    $user_id = (int) $_SESSION['user_id'];
    $user_query = $conn->query("SELECT profile_image FROM users WHERE id = $user_id");
    if ($user_query && $user_query->num_rows > 0) {
        $user = $user_query->fetch_assoc();
        if (!empty($user['profile_image'])) {
            $path = "/images/profile/" . $user['profile_image'];
            if (file_exists($_SERVER['DOCUMENT_ROOT'] . $path)) {
                $profile_image = $path;
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
    <title><?= htmlspecialchars($row['title']) ?> - Matchify</title>
    <link rel="icon" type="image/png" href="images/logo.png">
    <link rel="stylesheet" href="Participant-css/index-participant.css">
    <style>
        body { background: #f4f6fb; margin: 0; padding: 0; }

        .hero {
            min-height: 80px !important;
            background-image: url("images/back-image.jpg") !important;
            background-position: top center !important;
            background-size: cover !important;
            background-repeat: no-repeat !important;
            padding: 0 !important;
            margin: 0 !important;
        }

        nav.main-nav {
            background: transparent !important;
            box-shadow: none !important;
            padding: 10px 45px;
        }

        .banner-wrapper {
            width: 90%;
            max-width: 860px;
            height: 220px;
            overflow: hidden;
            margin: 20px auto;
            border-radius: 16px;
        }

        .banner-wrapper img {
            width: 100% !important;
            height: 100% !important;
            object-fit: cover !important;
            object-position: center !important;
            display: block !important;
        }

        .detail-wrapper {
            max-width: 900px;
            margin: 20px auto 60px;
            padding: 0 20px;
        }

        .detail-card {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.10);
            overflow: hidden;
        }

        .detail-header {
            background: linear-gradient(135deg, #6c63ff, #a855f7);
            padding: 35px 40px;
            color: #fff;
        }

        .detail-header h1 {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .detail-header .category-badge {
            display: inline-block;
            background: rgba(255,255,255,0.25);
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 13px;
        }

        .detail-header .status-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 700;
            margin-left: 10px;
        }

        .status-badge.pending  { background: #fef3c7; color: #d97706; }
        .status-badge.approved { background: #d1fae5; color: #065f46; }
        .status-badge.rejected { background: #fee2e2; color: #b91c1c; }

        .detail-body { padding: 35px 40px; }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }

        .info-box {
            background: #f8f7ff;
            border-left: 4px solid #6c63ff;
            border-radius: 10px;
            padding: 15px 20px;
        }

        .info-box .label {
            font-size: 11px;
            color: #888;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 5px;
        }

        .info-box .value {
            font-size: 15px;
            font-weight: 600;
            color: #333;
        }

        .section-title {
            font-size: 18px;
            font-weight: 600;
            color: #6c63ff;
            margin-bottom: 12px;
            padding-bottom: 8px;
            border-bottom: 2px solid #f0eeff;
        }

        .description {
            font-size: 14px;
            line-height: 1.8;
            color: #555;
            margin-bottom: 30px;
        }

        .organizer-box {
            display: flex;
            align-items: center;
            gap: 15px;
            background: #f8f7ff;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 30px;
        }

        .organizer-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #6c63ff, #a855f7);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            color: #fff;
            flex-shrink: 0;
        }

        .organizer-info .name { font-weight: 600; font-size: 15px; }
        .organizer-info .email { font-size: 13px; color: #888; }

        .reject-box {
            background: #fff0f0;
            border-left: 4px solid #ef4444;
            border-radius: 10px;
            padding: 15px 20px;
            margin-bottom: 30px;
            font-size: 14px;
            color: #b91c1c;
        }

        .action-buttons { display: flex; gap: 15px; }

        .btn-join {
            flex: 1;
            text-align: center;
            padding: 14px;
            background: linear-gradient(135deg, #6c63ff, #a855f7);
            color: #fff;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            font-size: 15px;
            transition: opacity 0.3s;
            border: none;
            cursor: pointer;
            display: block;
        }

        .btn-join:hover { opacity: 0.85; }
        .btn-join[disabled] { cursor: not-allowed; opacity: 0.85; }

        .btn-back {
            padding: 14px 25px;
            background: #f0eeff;
            color: #6c63ff;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            font-size: 15px;
            transition: background 0.3s;
        }

        .btn-back:hover { background: #e0d9ff; }

        @media (max-width: 600px) {
            .info-grid { grid-template-columns: 1fr; }
            .detail-header h1 { font-size: 20px; }
            .detail-body { padding: 20px; }
            .detail-header { padding: 20px; }
            .banner-wrapper { height: 160px; }
        }
    </style>
</head>
<body>

<div class="hero">
    <nav class="main-nav">
        <img src="images/logo.png" class="logo">
        <ul>
            <li><a href="index-organiser.php">Home</a></li>
            <li><a href="competition-list.php" class="active">Competition List</a></li>
            <li><a href="about.php">About</a></li>
            <li><a href="contact.php">Contact</a></li>
        </ul>
        <div class="nav-right">
            <?php if (!isset($_SESSION['user_id'])): ?>
                <a href="login.php" class="btn">Login</a>
            <?php else: ?>
                <div class="profile-dropdown">
                    <img src="<?php echo $profile_image; ?>" class="profile-icon" onclick="toggleMenu()">
                    <div class="dropdown-menu" id="dropdownMenu">
                        <a href="profile.php">My Profile</a>
                        <a href="login.php">Logout</a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </nav>
</div>

<div class="banner-wrapper">
    <img src="<?= $image ?>" alt="Competition Banner">
</div>

<div class="detail-wrapper">
    <div class="detail-card">

        <div class="detail-header">
            <h1><?= htmlspecialchars($row['title']) ?></h1>
            <span class="category-badge">📌 <?= htmlspecialchars($row['category']) ?></span>
            <span class="status-badge <?= $row['status'] ?>"><?= strtoupper($row['status']) ?></span>
        </div>

        <div class="detail-body">

            <?php if ($row['status'] == 'rejected' && !empty($row['reject_reason'])): ?>
            <div class="reject-box">
                ❌ Rejected Reason: <?= htmlspecialchars($row['reject_reason']) ?>
            </div>
            <?php endif; ?>

            <div class="info-grid">
                <div class="info-box">
                    <div class="label">📅 Competition Start</div>
                    <div class="value"><?= $row['start_date'] ?></div>
                </div>
                <div class="info-box">
                    <div class="label">🏁 Competition End</div>
                    <div class="value"><?= $row['end_date'] ?></div>
                </div>
                <div class="info-box">
                    <div class="label">📋 Application Start</div>
                    <div class="value"><?= $app_start ?? 'N/A' ?></div>
                </div>
                <div class="info-box">
                    <div class="label">⏰ Application End</div>
                    <div class="value"><?= $app_end ?? 'N/A' ?></div>
                </div>
                <div class="info-box">
                    <div class="label">📧 Contact Email</div>
                    <div class="value"><?= htmlspecialchars($row['email'] ?? 'N/A') ?></div>
                </div>
                <div class="info-box">
                    <div class="label">👥 Expected Participants</div>
                    <div class="value"><?= $row['participants'] ?? 'N/A' ?></div>
                </div>
            </div>

            <div class="section-title">About This Competition</div>
            <p class="description">
                <?= nl2br(htmlspecialchars($row['description'] ?? 'No description provided.')) ?>
            </p>

            <div class="section-title">Organizer</div>
            <div class="organizer-box">
                <div class="organizer-icon">👤</div>
                <div class="organizer-info">
                    <div class="name"><?= htmlspecialchars($row['organizer'] ?? 'N/A') ?></div>
                    <div class="email"><?= htmlspecialchars($row['email'] ?? 'N/A') ?></div>
                </div>
            </div>

            <div class="action-buttons">
                <a href="competition-list.php" class="btn-back">← Back</a>

                <?php if ($row['status'] === 'approved'): ?>

                    <?php if ($btn_status === 'open'): ?>
                        <a href="login.php" class="btn-join">Join Now 🚀</a>

                    <?php elseif ($btn_status === 'not_open'): ?>
                        <button class="btn-join" disabled style="background:#f0a500;">
                            Opens <?= date("d M Y", strtotime($app_start)) ?> 🕐
                        </button>

                    <?php else: ?>
                        <button class="btn-join" disabled style="background:#aaa;">
                            Application Closed ❌
                        </button>
                    <?php endif; ?>

                <?php elseif ($row['status'] === 'pending'): ?>
                    <button class="btn-join" disabled style="background:#f0a500;">
                        ⏳ Pending Admin Approval
                    </button>

                <?php else: ?>
                    <button class="btn-join" disabled style="background:#ccc;color:#888;">
                        Application Rejected
                    </button>
                <?php endif; ?>

            </div>

        </div>
    </div>
</div>

<script>
function toggleMenu(){
    const menu = document.getElementById("dropdownMenu");
    menu.style.display = (menu.style.display === "flex") ? "none" : "flex";
}

document.addEventListener("click", function(e){
    const dropdown = document.querySelector(".profile-dropdown");
    const menu = document.getElementById("dropdownMenu");
    if (dropdown && !dropdown.contains(e.target)) {
        if (menu) menu.style.display = "none";
    }
});
</script>

</body>
</html>