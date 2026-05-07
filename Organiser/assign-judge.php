<?php
session_start();
include "../config.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id        = (int) $_SESSION['user_id'];
$competition_id = intval($_GET['id'] ?? 0);

// Verify competition belongs to this organiser and is approved
$stmt = $conn->prepare("SELECT * FROM competition_applications WHERE id=? AND organizer_id=? AND status='approved'");
$stmt->bind_param("ii", $competition_id, $user_id);
$stmt->execute();
$comp = $stmt->get_result()->fetch_assoc();

if (!$comp) {
    echo "<script>alert('Competition not found or not approved.'); window.location.href='competition-list.php';</script>";
    exit();
}

// Handle submit
$msg = $_GET['msg'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judge_name  = trim($_POST['judge_name']);
    $judge_email = trim($_POST['judge_email']);
    $judge_phone = trim($_POST['judge_phone'] ?? '');
    $judge_ic    = trim($_POST['judge_ic'] ?? '');

    // Check if same email already assigned to this competition
    $check = $conn->prepare("SELECT id FROM competition_judges WHERE competition_id=? AND judge_email=?");
    $check->bind_param("is", $competition_id, $judge_email);
    $check->execute();

    if ($check->get_result()->num_rows > 0) {
        header("Location: assign-judge.php?id=$competition_id&msg=already");
        exit();
    }

    $ins = $conn->prepare("INSERT INTO competition_judges (competition_id, judge_name, judge_email, judge_phone, judge_ic, status) VALUES (?,?,?,?,?,'pending')");
    $ins->bind_param("issss", $competition_id, $judge_name, $judge_email, $judge_phone, $judge_ic);
    $ins->execute();

    header("Location: assign-judge.php?id=$competition_id&msg=success");
    exit();
}

// Handle remove
if (isset($_GET['remove'])) {
    $remove_id = intval($_GET['remove']);
    $del = $conn->prepare("DELETE FROM competition_judges WHERE id=? AND competition_id=?");
    $del->bind_param("ii", $remove_id, $competition_id);
    $del->execute();
    header("Location: assign-judge.php?id=$competition_id&msg=removed");
    exit();
}

// Get assigned judges for this competition
$assigned_result = $conn->prepare("SELECT * FROM competition_judges WHERE competition_id=? ORDER BY assigned_at DESC");
$assigned_result->bind_param("i", $competition_id);
$assigned_result->execute();
$judges_list = $assigned_result->get_result();

// Profile image
$profile_image = "../images/profile.avif";
$uq = $conn->query("SELECT profile_image FROM users WHERE id = $user_id");
if ($uq && $uq->num_rows > 0) {
    $u = $uq->fetch_assoc();
    if (!empty($u['profile_image'])) {
        $path = "/images/profile/" . $u['profile_image'];
        if (file_exists($_SERVER['DOCUMENT_ROOT'] . $path)) {
            $profile_image = $path;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assign Judge - <?= htmlspecialchars($comp['title']) ?></title>
    <link rel="icon" type="image/png" href="../images/logo.png">
    <link rel="stylesheet" href="../Admin-css/dashboard.css">
    <link rel="stylesheet" href="../Organiser-css/assign-judge.css">
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
            <a href="profile.php">
                <i class="fas fa-user"></i>
                <span>Profile</span>
            </a>
        </li>
        <li class="active">
            <a href="apply-competition.php">
                <i class="fas fa-trophy"></i>
                <span>Apply Competition</span>
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
            <span>Organiser</span>
            <h2>Assign Judge</h2>
        </div>
        <div class="user--info">
            <img src="<?= $profile_image ?>" alt="Profile">
        </div>
    </div>

    <div class="assign-wrapper">

        <a href="dashboard.php" class="btn-back">← Go Back</a>

        <!-- Competition Info -->
        <div class="comp-info">
            <h2><?= htmlspecialchars($comp['title']) ?></h2>
            <p>
                📌 <?= htmlspecialchars($comp['category']) ?>
                &nbsp;|&nbsp;
                🏆 <?= $comp['start_date'] ?> → <?= $comp['end_date'] ?>
                &nbsp;|&nbsp;
                📋 Application: <?= $comp['application_start'] ?? 'N/A' ?> → <?= $comp['application_end'] ?? 'N/A' ?>
            </p>
        </div>

        <!-- Message -->
        <?php if ($msg === 'success'): ?>
            <div class="msg-box msg-success">✅ Judge submitted successfully! Waiting for Admin approval.</div>
        <?php elseif ($msg === 'already'): ?>
            <div class="msg-box msg-already">⚠️ This email is already assigned to this competition.</div>
        <?php elseif ($msg === 'removed'): ?>
            <div class="msg-box msg-removed">🗑️ Judge removed successfully.</div>
        <?php endif; ?>

        <!-- Assign Form -->
        <div class="card--container">
            <div class="section-title">➕ Add New Judge</div>

            <form method="POST" class="assign-form">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Full Name <span class="required">*</span></label>
                        <input type="text" name="judge_name" placeholder="e.g. Ahmad bin Ali" required>
                    </div>
                    <div class="form-group">
                        <label>Email <span class="required">*</span></label>
                        <input type="email" name="judge_email" placeholder="e.g. ahmad@gmail.com" required>
                    </div>
                    <div class="form-group">
                        <label>Phone Number</label>
                        <input type="text" name="judge_phone" placeholder="e.g. 0123456789">
                    </div>
                    <div class="form-group">
                        <label>IC Number</label>
                        <input type="text" name="judge_ic" placeholder="e.g. 900101-01-1234">
                    </div>
                </div>
                <button type="submit" class="btn-assign">
                    <i class="fa fa-gavel"></i> Submit Judge
                </button>
            </form>
        </div>

        <!-- Judges List -->
        <div class="card--container">
            <div class="section-title">👨‍⚖️ Judge List</div>

            <?php
            $judges_list->data_seek(0);
            if ($judges_list->num_rows == 0):
            ?>
                <div class="empty-msg">No judges assigned yet.</div>
            <?php else: ?>
                <table class="judge-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>IC</th>
                            <th>Status</th>
                            <th>Submitted</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $i = 1; while ($j = $judges_list->fetch_assoc()): ?>
                        <tr>
                            <td><?= $i++ ?></td>
                            <td><?= htmlspecialchars($j['judge_name']) ?></td>
                            <td><?= htmlspecialchars($j['judge_email']) ?></td>
                            <td><?= htmlspecialchars($j['judge_phone'] ?? 'N/A') ?></td>
                            <td><?= htmlspecialchars($j['judge_ic'] ?? 'N/A') ?></td>
                            <td>
                                <span class="status-badge <?= $j['status'] ?>">
                                    <?= strtoupper($j['status']) ?>
                                </span>
                            </td>
                            <td><?= date("d M Y", strtotime($j['assigned_at'])) ?></td>
                            <td>
                                <?php if ($j['status'] == 'pending'): ?>
                                <a href="?id=<?= $competition_id ?>&remove=<?= $j['id'] ?>"
                                   class="btn-remove"
                                   onclick="return confirm('Remove this judge?')">
                                    Remove
                                </a>
                                <?php else: ?>
                                <span style="color:#aaa;font-size:12px;">—</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

    </div>
</div>

</body>
</html>