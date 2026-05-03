<?php
session_start();
include "../config.php";

/* =========================
   APPROVE / REJECT ACTION
========================= */
if (isset($_GET['action']) && isset($_GET['id'])) {

    $id = intval($_GET['id']);
    $action = $_GET['action'];

    if ($action === "approve") {
        $stmt = $conn->prepare("UPDATE competition_applications SET status='approved' WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();

        header("Location: manage-competition-list.php?status=pending");
        exit();
    }

    if ($action === "reject" && isset($_POST['reason'])) {
        $reason = $_POST['reason'];

        $stmt = $conn->prepare("UPDATE competition_applications SET status='rejected', reject_reason=? WHERE id=?");
        $stmt->bind_param("si", $reason, $id);
        $stmt->execute();

        header("Location: manage-competition-list.php?status=pending");
        exit();
    }
}

/* =========================
   COUNT
========================= */
$pendingCount = $conn->query("SELECT COUNT(*) total FROM competition_applications WHERE status='pending'")->fetch_assoc()['total'];
$approvedCount = $conn->query("SELECT COUNT(*) total FROM competition_applications WHERE status='approved'")->fetch_assoc()['total'];
$rejectedCount = $conn->query("SELECT COUNT(*) total FROM competition_applications WHERE status='rejected'")->fetch_assoc()['total'];

/* =========================
   FILTER
========================= */
$status = $_GET['status'] ?? 'all';

if ($status == 'all') {
    $result = $conn->query("SELECT ca.*, u.fullname AS org_name, u.email AS org_email
                            FROM competition_applications ca
                            LEFT JOIN users u ON ca.organizer_id = u.id
                            ORDER BY ca.created_at DESC");
} else {
    $stmt = $conn->prepare("SELECT ca.*, u.fullname AS org_name, u.email AS org_email
                            FROM competition_applications ca
                            LEFT JOIN users u ON ca.organizer_id = u.id
                            WHERE ca.status=?
                            ORDER BY ca.created_at DESC");
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

<!-- SIDEBAR -->
<div class="sidebar">
        <div class="logo"></div>
        <ul class="menu">
            <li class="active">
                <a href="#" >
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
                <a href="../Admin/manage-competition-list.php">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Manage 
                        Competition 
                        List</span>
                </a>
            </li>
            <li>
                <a href="#">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li>
                <a href="#">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li>
                <a href="#">
                    <i class="fas fa-cog"></i>
                    <span>Settings</span>
                </a>
            </li>
            <li class="logout">
                <a href="#" >
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
$img       = (!empty($row['competition_image']) && $row['competition_image'] != '0')
             ? '../uploads/' . $row['competition_image']
             : '../images/default.png';
$org_name  = !empty($row['org_name'])  ? $row['org_name']  : ($row['organizer'] ?? 'N/A');
$org_email = !empty($row['org_email']) ? $row['org_email'] : ($row['email']     ?? 'N/A');
?>

<div class="proposal--card">

    <img class="comp-img"
         src="<?= htmlspecialchars($img) ?>"
         onerror="this.src='../images/default.png'"
         alt="Competition Image">

    <!-- INFO -->
    <div class="info">

        <!-- ROW 1: title + time -->
        <div class="row">
            <h3><?= htmlspecialchars($row['title']) ?></h3>
            <span class="time">Applied Time: <?= date("d M Y, h:i A", strtotime($row['created_at'])) ?></span>
        </div>

        <!-- ROW 2: category + participants -->
        <div class="row">
            <span>📌 Category: <?= htmlspecialchars($row['category']) ?></span>
            <span>👥 Participants: <?= $row['participants'] ?? 'N/A' ?></span>
        </div>

        <!-- ROW 3: application period -->
        <div class="row">
            <span>📋 Application: <?= $row['application_start'] ?? 'N/A' ?> → <?= $row['application_end'] ?? 'N/A' ?></span>
        </div>

        <!-- ROW 4: competition period -->
        <div class="row">
            <span>📅 Competition: <?= $row['start_date'] ?> → <?= $row['end_date'] ?></span>
        </div>

        <!-- ROW 5: organizer + email -->
        <div class="row">
            <span>👤 Organizer: <?= htmlspecialchars($org_name) ?></span>
            <span>📧 Email: <?= htmlspecialchars($org_email) ?></span>
        </div>

        <!-- ROW 6: prizes -->
        <?php if (!empty($row['prize_1st']) || !empty($row['prize_2nd']) || !empty($row['prize_3rd'])): ?>
        <div class="row">
            <?php if (!empty($row['prize_1st'])): ?>
                <span>🥇 <?= htmlspecialchars($row['prize_1st']) ?></span>
            <?php endif; ?>
            <?php if (!empty($row['prize_2nd'])): ?>
                <span>🥈 <?= htmlspecialchars($row['prize_2nd']) ?></span>
            <?php endif; ?>
            <?php if (!empty($row['prize_3rd'])): ?>
                <span>🥉 <?= htmlspecialchars($row['prize_3rd']) ?></span>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- ROW 7: prize description -->
        <?php if (!empty($row['prize_description'])): ?>
        <div class="row">
            <span>📝 <?= htmlspecialchars($row['prize_description']) ?></span>
        </div>
        <?php endif; ?>

        <!-- APPROVE / REJECT TIME -->
        <?php if($row['status'] == 'approved' && !empty($row['approved_at'])): ?>
            <div class="row">
                <span class="ok">Approved: <?= $row['approved_at'] ?></span>
            </div>
        <?php endif; ?>

        <?php if($row['status'] == 'rejected' && !empty($row['rejected_at'])): ?>
            <div class="row">
                <span class="bad">Rejected: <?= $row['rejected_at'] ?></span>
            </div>
        <?php endif; ?>

        <?php if($row['status'] == 'rejected' && !empty($row['reject_reason'])): ?>
            <div class="row">
                <span class="bad">Reason: <?= htmlspecialchars($row['reject_reason']) ?></span>
            </div>
        <?php endif; ?>

    </div>

    <!-- RIGHT -->
    <div class="right">

        <span class="status <?= $row['status'] ?>">
            <?= strtoupper($row['status']) ?>
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
        <textarea name="reason" required placeholder="Reason..."></textarea>

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