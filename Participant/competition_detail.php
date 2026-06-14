<?php
session_start();
include "../config.php";

$id = intval($_GET['id']);

$stmt = $conn->prepare("
    SELECT * 
    FROM competition_applications 
    WHERE id=? AND status='approved'
");
$stmt->bind_param("i", $id);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();

if (!$row) {
    echo "<script>alert('Competition not found.'); window.location.href='competition-list.php';</script>";
    exit();
}

$image = !empty($row['competition_image'])
    ? "../uploads/" . $row['competition_image']
    : "../images/competition_banner.jpg";

$today     = date('Y-m-d');
$app_start = $row['application_start'] ?? null;
$app_end   = $row['application_end'] ?? null;

$start_date = $row['start_date'];
$end_date   = $row['end_date'];

if (!$app_start || !$app_end) {
    $btn_status = "open";
} elseif ($today < $app_start) {
    $btn_status = "not_open";
} elseif ($today > $app_end) {
    $btn_status = "expired";
} else {
    $btn_status = "open";
}

if ($today < $start_date) {
    $submission_status = "not_started";
} elseif ($today > $end_date) {
    $submission_status = "closed";
} else {
    $submission_status = "open";
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
<title><?= htmlspecialchars($row['title']) ?></title>
<link rel="icon" type="image/png" href="../images/logo.png">

<style>
body { background:#f4f6fb; margin:0; font-family:Poppins,sans-serif; }

.banner-wrapper{
    width:90%;
    max-width:860px;
    height:220px;
    margin:20px auto;
    border-radius:16px;
    overflow:hidden;
}
.banner-wrapper img{ width:100%; height:100%; object-fit:cover; }

.detail-wrapper{ max-width:900px; margin:auto; padding:20px; }

.detail-card{
    background:#fff;
    border-radius:20px;
    box-shadow:0 10px 40px rgba(0,0,0,0.1);
    overflow:hidden;
}

.detail-header{
    background:linear-gradient(135deg,#6c63ff,#a855f7);
    color:#fff;
    padding:30px;
}

.section-title{
    font-size:16px;
    margin-top:20px;
    color:#6c63ff;
    font-weight:600;
}

.description{
    font-size:14px;
    line-height:1.6;
    color:#444;
}

.info-grid{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:15px;
}

.info-box{
    background:#f8f7ff;
    padding:12px;
    border-radius:10px;
    font-size:14px;
    color:#444;
}

.info-box span{
    display:block;
    font-size:11px;
    color:#999;
    margin-bottom:4px;
    text-transform:uppercase;
    letter-spacing:0.5px;
}

.action-buttons{
    margin-top:20px;
    display:flex;
    gap:10px;
}

.btn{
    flex:1;
    display:block;
    text-align:center;
    padding:12px;
    border-radius:10px;
    text-decoration:none;
    font-weight:600;
}

.back-btn{
    background:#333;
    color:#fff;
}

.join-btn{
    background:linear-gradient(135deg,#6c63ff,#a855f7);
    color:#fff;
}

.disabled-btn{
    flex:1;
    padding:12px;
    border-radius:10px;
    background:#ccc;
    color:#666;
    font-weight:600;
    text-align:center;
}
</style>
</head>

<body>

<div class="banner-wrapper">
    <img src="<?= $image ?>">
</div>

<div class="detail-wrapper">
<div class="detail-card">

    <div class="detail-header">
        <h1><?= htmlspecialchars($row['title']) ?></h1>
        <p><?= strtoupper($row['category']) ?></p>
    </div>

    <div style="padding:25px;">

        <div class="info-grid">
            <div class="info-box">
                <span>Start Date</span>
                <?= $start_date ?>
            </div>
            <div class="info-box">
                <span>End Date</span>
                <?= $end_date ?>
            </div>
            <div class="info-box">
                <span>Participants</span>
                <?= $row['participants'] ?? 'N/A' ?>
            </div>
            <div class="info-box">
                <span>Email</span>
                <?= htmlspecialchars($row['email'] ?? 'N/A') ?>
            </div>
        </div>

        <div class="section-title">Description</div>
        <div class="description">
            <?= nl2br(htmlspecialchars($row['description'])) ?>
        </div>

        <div class="section-title">Rules</div>
        <div class="description">
            <?= nl2br(htmlspecialchars($row['rules'] ?? 'No rules provided')) ?>
        </div>

        <div class="section-title">Prizes</div>
        <div class="description">
            <?= nl2br(htmlspecialchars($row['prizes'] ?? 'No prizes information provided')) ?>
        </div>

        <div class="section-title">Submission Method</div>
        <div class="description">
            <?= strtoupper($row['submission_type'] ?? 'NOT SPECIFIED') ?>
        </div>

        <div class="action-buttons">

            <a href="competition-list.php" class="btn back-btn">
                ⬅ Go Back
            </a>

            <?php if ($btn_status == 'open'): ?>
                <a href="apply_competition.php?id=<?= $row['id'] ?>" class="btn join-btn">
                    🚀 Join Now
                </a>
            <?php elseif ($btn_status == 'not_open'): ?>
                <div class="disabled-btn">Not Open Yet</div>
            <?php else: ?>
                <div class="disabled-btn">🔒 Closed</div>
            <?php endif; ?>

        </div>

    </div>

</div>
</div>

</body>
</html>