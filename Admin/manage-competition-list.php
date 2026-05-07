<?php
session_start();
include "../config.php";

/* =========================
   APPROVE / REJECT ACTION
========================= */
if (isset($_GET['action']) && isset($_GET['id'])) {

    $id = intval($_GET['id']);
    $action = $_GET['action'];

    /* ================= APPROVE ================= */
    if ($action === "approve") {

        $check = $conn->prepare("SELECT status FROM competition_applications WHERE id=?");
        $check->bind_param("i", $id);
        $check->execute();
        $res = $check->get_result()->fetch_assoc();

        if ($res && $res['status'] !== 'approved') {

            $stmt = $conn->prepare("
                UPDATE competition_applications 
                SET status='approved', approved_at=NOW()
                WHERE id=?
            ");
            $stmt->bind_param("i", $id);
            $stmt->execute();

            // optional init
            $conn->query("
                INSERT INTO competition_settings (competition_id, submission_open)
                VALUES ($id, 0)
            ");
        }

        header("Location: manage-competition-list.php?status=pending");
        exit();
    }

    /* ================= REJECT ================= */
    if ($action === "reject" && isset($_POST['reason'])) {

        $reason = trim($_POST['reason']);

        $stmt = $conn->prepare("
            UPDATE competition_applications 
            SET status='rejected', reject_reason=?, rejected_at=NOW()
            WHERE id=?
        ");
        $stmt->bind_param("si", $reason, $id);
        $stmt->execute();

        header("Location: manage-competition-list.php?status=pending");
        exit();
    }
}

/* =========================
   COUNT STATUS
========================= */
$pendingCount  = $conn->query("SELECT COUNT(*) AS total FROM competition_applications WHERE status='pending'")->fetch_assoc()['total'];
$approvedCount = $conn->query("SELECT COUNT(*) AS total FROM competition_applications WHERE status='approved'")->fetch_assoc()['total'];
$rejectedCount = $conn->query("SELECT COUNT(*) AS total FROM competition_applications WHERE status='rejected'")->fetch_assoc()['total'];

/* =========================
   FILTER
========================= */
$status = $_GET['status'] ?? 'all';

if ($status == 'all') {

    $result = $conn->query("
        SELECT ca.*, u.fullname AS org_name, u.email AS org_email
        FROM competition_applications ca
        LEFT JOIN users u ON ca.organizer_id = u.id
        ORDER BY ca.created_at DESC
    ");

} else {

    $stmt = $conn->prepare("
        SELECT ca.*, u.fullname AS org_name, u.email AS org_email
        FROM competition_applications ca
        LEFT JOIN users u ON ca.organizer_id = u.id
        WHERE ca.status=?
        ORDER BY ca.created_at DESC
    ");

    $stmt->bind_param("s", $status);
    $stmt->execute();
    $result = $stmt->get_result();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Competition</title>

<link rel="stylesheet" href="../Admin-css/manage-competition-list.css">
<link rel="icon" type="image/png" href="../images/logo.png">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>

<div class="sidebar">
    <div class="logo"></div>
    <ul class="menu">

        <li class="active">
            <a href="dashboard.php">
                <i class="fas fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
        </li>

        <li><a href="#"><i class="fas fa-user"></i><span>Profile</span></a></li>

        <li>
            <a href="manage-competition-list.php">
                <i class="fas fa-list-check"></i>
                <span>Manage Competition</span>
            </a>
        </li>

        <li>
            <a href="manage-judges.php">
                <i class="fas fa-gavel"></i>
                <span>Manage Judges</span>
            </a>
        </li>

        <li>
            <a href="#"><i class="fas fa-cog"></i><span>Settings</span></a>
        </li>

        <li class="logout">
            <a href="../login.php">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </li>

    </ul>
</div>

<div class="main--content">

<div class="header--wrapper">
    <div class="header--title">
        <span>Admin</span>
        <h2>Manage Competition</h2>
    </div>
</div>

<!-- TABS -->
<div class="tab--container">
    <a href="?status=all" class="tab <?=($status=='all')?'active':''?>">All</a>
    <a href="?status=pending" class="tab <?=($status=='pending')?'active':''?>">
        Pending <span class="tab-badge orange"><?=$pendingCount?></span>
    </a>
    <a href="?status=approved" class="tab <?=($status=='approved')?'active':''?>">
        Approved <span class="tab-badge purple"><?=$approvedCount?></span>
    </a>
    <a href="?status=rejected" class="tab <?=($status=='rejected')?'active':''?>">
        Rejected <span class="tab-badge" style="background:red"><?=$rejectedCount?></span>
    </a>
</div>

<!-- LIST -->
<div class="table--container">

<?php while($row = $result->fetch_assoc()): ?>

<?php
$img = (!empty($row['competition_image']))
    ? '../uploads/' . $row['competition_image']
    : '../images/default.png';

$org_name  = $row['org_name'] ?? $row['organizer'] ?? 'N/A';
$org_email = $row['org_email'] ?? $row['email'] ?? 'N/A';
?>

<div class="proposal--card">

    <img class="comp-img" src="<?=htmlspecialchars($img)?>" onerror="this.src='../images/default.png'">

    <div class="info">

        <div class="row">
            <h3><?=htmlspecialchars($row['title'])?></h3>
            <span class="time">Created: <?=date("d M Y, h:i A", strtotime($row['created_at']))?></span>
        </div>

        <div class="row">
            <span>📌 Category: <?=htmlspecialchars($row['category'])?></span>
            <span>👥 Participants: <?=$row['participants']?></span>
        </div>

        <!-- APPLICATION PERIOD -->
        <div class="row">
            <span>📋 Application: <?= $row['application_start'] ?> → <?= $row['application_end'] ?></span>
        </div>

        <!-- COMPETITION PERIOD -->
        <div class="row">
            <span>🏆 Competition: <?= $row['start_date'] ?> → <?= $row['end_date'] ?></span>
        </div>

        <!-- RULES (NEW) -->
        <?php if(!empty($row['rules'])): ?>
        <div class="row">
            <span>📜 Rules: <?= htmlspecialchars($row['rules']) ?></span>
        </div>
        <?php endif; ?>

        <div class="row">
            <span>👤 Organizer: <?=$org_name?></span>
            <span>📧 Email: <?=$org_email?></span>
        </div>

        <?php if($row['status']=='approved'): ?>
        <div class="row">
            <span class="ok">Approved: <?=$row['approved_at'] ?? 'N/A'?></span>
        </div>
        <?php endif; ?>

        <?php if($row['status']=='rejected'): ?>
        <div class="row">
            <span class="bad">Reason: <?=$row['reject_reason']?></span>
        </div>
        <?php endif; ?>

    </div>

    <div class="right">

        <span class="status <?=$row['status']?>">
            <?=strtoupper($row['status'])?>
        </span>

        <?php if($row['status']=="pending"): ?>
            <a class="approve" href="?id=<?=$row['id']?>&action=approve">Approve</a>
            <button class="reject" onclick="openPopup(<?=$row['id']?>)">Reject</button>
        <?php endif; ?>

    </div>

</div>

<?php endwhile; ?>

</div>
</div>

<!-- POPUP -->
<div class="overlay" id="overlay"></div>

<div class="popup" id="rejectPopup">
    <h3>Reject Competition</h3>

    <form method="POST" id="rejectForm">
        <textarea name="reason" required></textarea>

        <div class="popup--buttons">
            <button type="button" onclick="closePopup()">Cancel</button>
            <button type="submit">Reject</button>
        </div>
    </form>
</div>

<script>
function openPopup(id){
    document.getElementById("rejectForm").action =
        "?id="+id+"&action=reject";

    document.getElementById("overlay").classList.add("show");
    document.getElementById("rejectPopup").classList.add("show");
}

function closePopup(){
    document.getElementById("overlay").classList.remove("show");
    document.getElementById("rejectPopup").classList.remove("show");
}
</script>

</body>
</html>