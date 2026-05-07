<?php
session_start();
include "../config.php";

/* =========================
   APPROVE / REJECT ACTION
========================= */
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id     = intval($_GET['id']);
    $action = $_GET['action'];

    if ($action === 'approve') {
        $get = $conn->prepare("SELECT * FROM competition_judges WHERE id=?");
        $get->bind_param("i", $id);
        $get->execute();
        $judge = $get->get_result()->fetch_assoc();

        if ($judge) {
            $temp_pass   = bin2hex(random_bytes(4));
            $hashed_pass = password_hash($temp_pass, PASSWORD_DEFAULT);
            $judge_email = $judge['judge_email'];
            $judge_name  = $judge['judge_name'];

            $check = $conn->prepare("SELECT id FROM users WHERE email=?");
            $check->bind_param("s", $judge_email);
            $check->execute();
            $existing = $check->get_result()->fetch_assoc();

            if ($existing) {
                $judge_user_id = $existing['id'];
            } else {
                $username = 'judge_' . time();
                $ins = $conn->prepare("INSERT INTO users (fullname, username, email, password, role, status) VALUES (?,?,?,?,'judge','active')");
                $ins->bind_param("ssss", $judge_name, $username, $judge_email, $hashed_pass);
                $ins->execute();
                $judge_user_id = $conn->insert_id;
            }

            $upd = $conn->prepare("UPDATE competition_judges SET status='approved', judge_id=?, temp_password=? WHERE id=?");
            $upd->bind_param("isi", $judge_user_id, $temp_pass, $id);
            $upd->execute();
        }

        header("Location: manage-judges.php?msg=approved");
        exit();
    }

    if ($action === 'reject' && isset($_POST['reason'])) {
        $reason = $_POST['reason'];
        $upd = $conn->prepare("UPDATE competition_judges SET status='rejected', reject_reason=? WHERE id=?");
        $upd->bind_param("si", $reason, $id);
        $upd->execute();
        header("Location: manage-judges.php?msg=rejected");
        exit();
    }
}

/* =========================
   COUNT
========================= */
$pendingCount  = $conn->query("SELECT COUNT(*) total FROM competition_judges WHERE status='pending'")->fetch_assoc()['total'];
$approvedCount = $conn->query("SELECT COUNT(*) total FROM competition_judges WHERE status='approved'")->fetch_assoc()['total'];
$rejectedCount = $conn->query("SELECT COUNT(*) total FROM competition_judges WHERE status='rejected'")->fetch_assoc()['total'];

/* =========================
   FILTER
========================= */
$status = $_GET['status'] ?? 'all';
$msg    = $_GET['msg'] ?? '';

if ($status == 'all') {
    $result = $conn->query("
        SELECT cj.*, ca.title AS comp_title, ca.start_date, ca.end_date,
               u.fullname AS org_name
        FROM competition_judges cj
        LEFT JOIN competition_applications ca ON cj.competition_id = ca.id
        LEFT JOIN users u ON ca.organizer_id = u.id
        ORDER BY cj.assigned_at DESC
    ");
} else {
    $stmt = $conn->prepare("
        SELECT cj.*, ca.title AS comp_title, ca.start_date, ca.end_date,
               u.fullname AS org_name
        FROM competition_judges cj
        LEFT JOIN competition_applications ca ON cj.competition_id = ca.id
        LEFT JOIN users u ON ca.organizer_id = u.id
        WHERE cj.status=?
        ORDER BY cj.assigned_at DESC
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Judges</title>
    <link rel="icon" type="image/png" href="../images/logo.png">
    <link rel="stylesheet" href="../Admin-css/dashboard.css">
    <link rel="stylesheet" href="../Admin-css/manage-judges.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
        <div class="msg-box msg-success">✅ Judge approved successfully!</div>
    <?php elseif ($msg === 'rejected'): ?>
        <div class="msg-box msg-rejected">❌ Judge rejected.</div>
    <?php endif; ?>

    <!-- TABS -->
    <div class="tab--container">
        <a href="?status=all" class="tab <?= ($status=='all') ? 'active' : '' ?>">All</a>
        <a href="?status=pending" class="tab <?= ($status=='pending') ? 'active' : '' ?>">
            Pending <span class="tab-badge orange"><?= $pendingCount ?></span>
        </a>
        <a href="?status=approved" class="tab <?= ($status=='approved') ? 'active' : '' ?>">
            Approved <span class="tab-badge" style="background:#22c55e"><?= $approvedCount ?></span>
        </a>
        <a href="?status=rejected" class="tab <?= ($status=='rejected') ? 'active' : '' ?>">
            Rejected <span class="tab-badge" style="background:red"><?= $rejectedCount ?></span>
        </a>
    </div>

    <!-- LIST -->
    <div class="card--container">

        <?php if ($result->num_rows == 0): ?>
            <p style="text-align:center;color:#aaa;padding:30px;">No judges found.</p>
        <?php endif; ?>

        <?php while ($row = $result->fetch_assoc()): ?>

        <div class="proposal--card">

            <!-- LEFT -->
            <div class="proposal--left">

                <div class="judge-top">
                    <div class="judge-avatar">
                        <i class="fas fa-user-tie"></i>
                    </div>
                    <div>
                        <h4><?= htmlspecialchars($row['judge_name']) ?></h4>
                        <span class="status <?= $row['status'] ?>"><?= strtoupper($row['status']) ?></span>
                    </div>
                </div>

                <div class="meta">
                    <span>📧 <?= htmlspecialchars($row['judge_email']) ?></span>
                    <?php if (!empty($row['judge_phone'])): ?>
                        <span>📞 <?= htmlspecialchars($row['judge_phone']) ?></span>
                    <?php endif; ?>
                </div>

                <?php if (!empty($row['judge_ic'])): ?>
                <div class="meta">
                    <span>🪪 IC: <?= htmlspecialchars($row['judge_ic']) ?></span>
                </div>
                <?php endif; ?>

                <div class="meta">
                    <span>🏆 <b><?= htmlspecialchars($row['comp_title'] ?? 'N/A') ?></b></span>
                    <span>📅 <?= $row['start_date'] ?> → <?= $row['end_date'] ?></span>
                </div>

                <div class="meta">
                    <span>👤 Organizer: <b><?= htmlspecialchars($row['org_name'] ?? 'N/A') ?></b></span>
                    <span class="submitted-time">Submitted: <?= date("d M Y, h:i A", strtotime($row['assigned_at'])) ?></span>
                </div>

                <?php if ($row['status'] === 'rejected' && !empty($row['reject_reason'])): ?>
                <div class="meta">
                    <span style="color:#b91c1c;">❌ Reason: <?= htmlspecialchars($row['reject_reason']) ?></span>
                </div>
                <?php endif; ?>

                <?php if ($row['status'] === 'approved' && !empty($row['temp_password'])): ?>
                <div class="meta">
                    <span style="color:#065f46;">🔑 Temp Password: <b><?= htmlspecialchars($row['temp_password']) ?></b></span>
                </div>
                <?php endif; ?>

            </div>

            <!-- RIGHT -->
            <div class="proposal--right">
                <?php if ($row['status'] === 'pending'): ?>
                    <a class="approve" href="?id=<?= $row['id'] ?>&action=approve">
                        <i class="fa-solid fa-check"></i> Approve
                    </a>
                    <button class="reject" onclick="openPopup(<?= $row['id'] ?>)">
                        <i class="fa-solid fa-xmark"></i> Reject
                    </button>
                <?php endif; ?>
            </div>

        </div>

        <?php endwhile; ?>

    </div>
</div>

<!-- POPUP -->
<div class="overlay" id="overlay"></div>
<div class="popup" id="rejectPopup">
    <h3>Reject Judge</h3>
    <form method="POST" id="rejectForm">
        <textarea name="reason" required placeholder="Enter reason..."></textarea>
        <div class="popup--buttons">
            <button type="button" onclick="closePopup()">Cancel</button>
            <button type="submit">Reject</button>
        </div>
    </form>
</div>

<script>
function openPopup(id) {
    document.getElementById("rejectForm").action = "?id=" + id + "&action=reject";
    document.getElementById("overlay").classList.add("show");
    document.getElementById("rejectPopup").classList.add("show");
}
function closePopup() {
    document.getElementById("overlay").classList.remove("show");
    document.getElementById("rejectPopup").classList.remove("show");
}
</script>

</body>
</html>