<?php
session_start();
include "../config.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$id = intval($_GET['id']);
$user_id = (int)$_SESSION['user_id'];

/* =========================
   GET COMPETITION
========================= */
$stmt = $conn->prepare("
    SELECT *
    FROM competition_applications
    WHERE id=?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();

if (!$row) {
    echo "<script>alert('Competition not found'); window.location.href='my-competitions.php';</script>";
    exit();
}

/* =========================
   CHECK USER JOINED
========================= */
$check = $conn->prepare("
    SELECT id 
    FROM competition_participants 
    WHERE competition_id=? AND user_id=?
");
$check->bind_param("ii", $id, $user_id);
$check->execute();
$joined = $check->get_result()->fetch_assoc();

/* =========================
   DATE LOGIC
========================= */
$today = date('Y-m-d');

$start = $row['start_date'];
$end   = $row['end_date'];

if ($today < $start) {
    $status = "Not Started";
} elseif ($today > $end) {
    $status = "Ended";
} else {
    $status = "Ongoing";
}

/* =========================
   IMAGE
========================= */
$image = !empty($row['competition_image'])
    ? "../uploads/" . $row['competition_image']
    : "../images/default.png";
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?= htmlspecialchars($row['title']) ?></title>
<link rel="icon" type="image/png" href="../images/logo.png">
<style>
body{
    margin:0;
    font-family:Poppins,sans-serif;
    background:#f4f6fb;
}

.container{
    max-width:900px;
    margin:30px auto;
    background:#fff;
    border-radius:16px;
    overflow:hidden;
    box-shadow:0 10px 30px rgba(0,0,0,0.1);
}

/* HEADER */
.header{
    padding:20px;
    background:linear-gradient(135deg,#6c63ff,#a855f7);
    color:#fff;
}

/* CONTENT */
.content{
    padding:25px;
}

.section{
    margin-bottom:20px;
}

.label{
    font-weight:600;
    color:#6c63ff;
    margin-bottom:6px;
}

.badge{
    display:inline-block;
    padding:5px 12px;
    border-radius:20px;
    font-size:12px;
    font-weight:600;
    background:#eee;
}

/* BUTTONS */
.actions{
    display:flex;
    gap:10px;
    margin-top:20px;
}

.btn{
    padding:10px 14px;
    border-radius:8px;
    text-decoration:none;
    font-weight:600;
    font-size:13px;
    text-align:center;
}

.join{
    background:#22c55e;
    color:#fff;
}

.submit{
    background:#6c63ff;
    color:#fff;
}

.back{
    background:#eee;
    color:#333;
}

/* DISABLED */
.disabled{
    background:#ccc;
    color:#666;
    pointer-events:none;
}
</style>
</head>

<body>

<div class="container">

    <!-- HEADER -->
    <div class="header">
        <h2><?= htmlspecialchars($row['title']) ?></h2>
        <p><?= strtoupper($row['category']) ?></p>
    </div>

    <div class="content">

        <!-- STATUS -->
        <div class="section">
            <div class="label">Status</div>
            <span class="badge"><?= $status ?></span>
        </div>

        <!-- DATE -->
        <div class="section">
            <div class="label">Competition Date</div>
            <?= $start ?> → <?= $end ?>
        </div>

        <!-- DESCRIPTION -->
        <div class="section">
            <div class="label">Description</div>
            <div><?= nl2br(htmlspecialchars($row['description'])) ?></div>
        </div>

        <!-- RULES -->
        <div class="section">
            <div class="label">Rules</div>
            <div><?= nl2br(htmlspecialchars($row['rules'] ?? 'No rules provided')) ?></div>
        </div>

        <!-- SUBMISSION -->
        <div class="section">
            <div class="label">Submission Type</div>
            <?= strtoupper($row['submission_type'] ?? 'NOT SET') ?>
        </div>

        <!-- ACTIONS -->
        <div class="actions">

            <!-- BACK -->
            <a href="my-competition.php" class="btn back">
                ← Back
            </a>

            <!-- JOIN -->
            <?php if (!$joined): ?>
                <a href="join-competition.php?id=<?= $id ?>" class="btn join">
                    Join Now 🚀
                </a>
            <?php endif; ?>

            <!-- SUBMIT -->
            <?php if ($joined && $status == "Ongoing"): ?>
                <a href="submit-work.php?id=<?= $id ?>" class="btn submit">
                    Submit Work 📤
                </a>
            <?php else: ?>
                <div class="btn disabled">Submit Closed</div>
            <?php endif; ?>

        </div>

    </div>

</div>

</body>
</html>