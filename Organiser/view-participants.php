<?php
session_start();
include "../config.php";

date_default_timezone_set('Asia/Kuala_Lumpur');

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = (int) $_SESSION['user_id'];

if (!isset($_GET['id'])) {
    header("Location: manage-competition.php");
    exit();
}

$competition_id = (int) $_GET['id'];

/* =========================
   HANDLE ACTION
========================= */
if (isset($_GET['action'], $_GET['pid'])) {

    $pid = (int) $_GET['pid'];
    $action = $_GET['action'];

    if (in_array($action, ['approve', 'reject'])) {

        $status = ($action === 'approve') ? 'approved' : 'rejected';

        $stmt = $conn->prepare("
            UPDATE competition_participants
            SET status = ?
            WHERE id = ? AND competition_id = ?
        ");
        $stmt->bind_param("sii", $status, $pid, $competition_id);
        $stmt->execute();
    }

    header("Location: view-participants.php?id=" . $competition_id . "&tab=all");
    exit();
}

/* =========================
   CHECK OWNER
========================= */
$check = $conn->prepare("
    SELECT title 
    FROM competition_applications 
    WHERE id = ? AND organizer_id = ?
");
$check->bind_param("ii", $competition_id, $user_id);
$check->execute();
$competition = $check->get_result()->fetch_assoc();

if (!$competition) {
    echo "Competition not found";
    exit();
}

/* =========================
   TAB FILTER
========================= */
$tab = $_GET['tab'] ?? 'all';

$where = "competition_id = $competition_id";

if ($tab === 'pending') {
    $where .= " AND status='pending'";
} elseif ($tab === 'approved') {
    $where .= " AND status='approved'";
} elseif ($tab === 'rejected') {
    $where .= " AND status='rejected'";
}

$result = $conn->query("SELECT * FROM competition_participants WHERE $where ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Participants</title>
<link rel="icon" type="image/png" href="../images/logo.png">
<link rel="stylesheet" href="../Organiser-css/view-participants.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>

<div class="container">

    <h2><?= htmlspecialchars($competition['title']) ?> - Participants</h2>

    <!-- BACK BUTTON -->
    <a href="dashboard.php" class="back-btn">← Back to Dashboard</a>

    <!-- TABS -->
    <div class="tabs">
        <a href="?id=<?= $competition_id ?>&tab=all" class="<?= $tab=='all'?'active':'' ?>">All</a>
        <a href="?id=<?= $competition_id ?>&tab=pending" class="<?= $tab=='pending'?'active':'' ?>">Pending</a>
        <a href="?id=<?= $competition_id ?>&tab=approved" class="<?= $tab=='approved'?'active':'' ?>">Approved</a>
        <a href="?id=<?= $competition_id ?>&tab=rejected" class="<?= $tab=='rejected'?'active':'' ?>">Rejected</a>
    </div>

    <table>
        <thead>
        <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Status</th>
            <th>Joined</th>
            <th>Action</th>
        </tr>
        </thead>

        <tbody>

        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>

                    <td><?= htmlspecialchars($row['full_name']) ?></td>
                    <td><?= htmlspecialchars($row['email']) ?></td>
                    <td><?= htmlspecialchars($row['phone']) ?></td>

                    <td>
                        <span class="status <?= $row['status'] ?>">
                            <?= ucfirst($row['status']) ?>
                        </span>
                    </td>

                    <td><?= $row['created_at'] ?></td>

                    <td>

                        <?php if ($row['status'] === 'pending'): ?>

                            <a href="?id=<?= $competition_id ?>&pid=<?= $row['id'] ?>&action=approve&tab=<?= $tab ?>">
                                <button class="approve">Approve</button>
                            </a>

                            <a href="?id=<?= $competition_id ?>&pid=<?= $row['id'] ?>&action=reject&tab=<?= $tab ?>">
                                <button class="reject">Reject</button>
                            </a>

                        <?php else: ?>
                            <span class="done">Done</span>
                        <?php endif; ?>

                    </td>

                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="6" style="text-align:center;">No participants</td></tr>
        <?php endif; ?>

        </tbody>
    </table>

</div>

</body>
</html>