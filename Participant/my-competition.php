<?php
session_start();
include "../config.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = (int)$_SESSION['user_id'];

/* =========================
   GET MY COMPETITIONS
========================= */
$stmt = $conn->prepare("
    SELECT 
        cp.*,
        ca.title,
        ca.category,
        ca.start_date,
        ca.end_date
    FROM competition_participants cp
    JOIN competition_applications ca 
        ON cp.competition_id = ca.id
    WHERE cp.user_id = ?
    ORDER BY cp.created_at DESC
");

$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$today = date('Y-m-d');
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>My Competitions</title>
<link rel="icon" type="image/png" href="../images/logo.png">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
body{
    margin:0;
    font-family:Poppins,sans-serif;
    background:#f4f6fb;
}

.container{
    max-width:1000px;
    margin:30px auto;
    padding:20px;
}

/* HEADER */
.header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:20px;
}

.back-btn{
    text-decoration:none;
    background:#fff;
    padding:8px 14px;
    border-radius:10px;
    box-shadow:0 4px 12px rgba(0,0,0,0.08);
    font-size:13px;
    font-weight:600;
    color:#333;
}

.back-btn:hover{
    background:#eee;
}

/* CARD */
.card{
    background:#fff;
    padding:20px;
    border-radius:16px;
    margin-bottom:15px;
    box-shadow:0 6px 20px rgba(0,0,0,0.08);
    display:flex;
    justify-content:space-between;
    align-items:center;
}

.info h3{
    margin:0;
    font-size:18px;
}

.info p{
    margin:4px 0;
    font-size:13px;
    color:#666;
}

/* STATUS */
.badge{
    padding:5px 10px;
    border-radius:20px;
    font-size:12px;
    font-weight:600;
}

.ongoing{ background:#d1fae5; color:#065f46; }
.notyet{ background:#fef3c7; color:#92400e; }
.ended{ background:#fee2e2; color:#991b1b; }

/* ACTIONS */
.actions{
    display:flex;
    flex-direction:column;
    gap:8px;
    text-align:right;
}

.btn{
    padding:8px 14px;
    border-radius:8px;
    text-decoration:none;
    font-size:13px;
    font-weight:600;
    text-align:center;
}

.submit{
    background:#6c63ff;
    color:#fff;
}

.submit:hover{
    opacity:0.85;
}

.disabled{
    background:#ccc;
    color:#666;
}
</style>
</head>

<body>

<div class="container">

<!-- HEADER + GO BACK -->
<div class="header">
    <h2>🎯 My Competitions</h2>

    <a href="javascript:history.back()" class="back-btn">
        ← Go Back
    </a>
</div>

<?php while($row = $result->fetch_assoc()): ?>

<?php
$start = $row['start_date'];
$end   = $row['end_date'];

if ($today < $start) {
    $status = "notyet";
    $status_text = "Not Started";
} elseif ($today > $end) {
    $status = "ended";
    $status_text = "Ended";
} else {
    $status = "ongoing";
    $status_text = "Ongoing";
}

$can_submit = ($status == "ongoing");
?>

<div class="card">

    <div class="info">
        <h3><?= htmlspecialchars($row['title']) ?></h3>
        <p>Category: <?= htmlspecialchars($row['category']) ?></p>
        <p><?= $start ?> → <?= $end ?></p>

        <span class="badge <?= $status ?>">
            <?= $status_text ?>
        </span>
    </div>

    <div class="actions">

        <a class="btn" href="../Participant/competition-detail.php?id=<?= $row['competition_id'] ?>">
            View Detail
        </a>

        <?php if ($can_submit): ?>
            <a class="btn submit" href="../Participant/submit-work.php?id=<?= $row['competition_id'] ?>">
                Submit Work 📤
            </a>
        <?php else: ?>
            <div class="btn disabled">Submit Closed</div>
        <?php endif; ?>

    </div>

</div>

<?php endwhile; ?>

</div>

</body>
</html>