<?php
session_start();
include "../config.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = (int)$_SESSION['user_id'];

$stmt = $conn->prepare("
    SELECT cp.*, ca.title, ca.category, ca.start_date, ca.end_date, ca.result_status
    FROM competition_participants cp
    JOIN competition_applications ca ON cp.competition_id = ca.id
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
body { margin:0; font-family:Poppins,sans-serif; background:#f4f6fb; }

.container { max-width:1000px; margin:30px auto; padding:20px; }

.header { display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; }

.back-btn {
    text-decoration:none; background:#fff; padding:8px 14px;
    border-radius:10px; box-shadow:0 4px 12px rgba(0,0,0,0.08);
    font-size:13px; font-weight:600; color:#333;
}
.back-btn:hover { background:#eee; }

.card {
    background:#fff; padding:20px; border-radius:16px;
    margin-bottom:15px; box-shadow:0 6px 20px rgba(0,0,0,0.08);
}

.card-top {
    display:flex; justify-content:space-between; align-items:center;
}

.info h3 { margin:0; font-size:18px; }
.info p { margin:4px 0; font-size:13px; color:#666; }

.badge { padding:5px 10px; border-radius:20px; font-size:12px; font-weight:600; }
.ongoing  { background:#d1fae5; color:#065f46; }
.notyet   { background:#fef3c7; color:#92400e; }
.ended    { background:#fee2e2; color:#991b1b; }

.actions { display:flex; flex-direction:column; gap:8px; text-align:right; }

.btn { padding:8px 14px; border-radius:8px; text-decoration:none; font-size:13px; font-weight:600; text-align:center; display:block; }
.submit   { background:#6c63ff; color:#fff; }
.submit:hover { opacity:0.85; }
.disabled { background:#ccc; color:#666; cursor:not-allowed; }

.result-section {
    margin-top:16px;
    padding-top:16px;
    border-top:1px solid #f0eeff;
}

.result-title {
    font-size:14px; font-weight:700; color:#6c63ff; margin-bottom:12px;
}

.result-box {
    display:flex; gap:16px; flex-wrap:wrap; align-items:center;
}

.my-score-box {
    background: linear-gradient(135deg,#6c63ff,#a855f7);
    color:#fff; border-radius:12px; padding:14px 20px;
    text-align:center; min-width:130px;
}
.my-score-box .score-num { font-size:28px; font-weight:800; }
.my-score-box .score-label { font-size:11px; opacity:0.85; }

.my-rank-box {
    background:#f0eeff; border-radius:12px;
    padding:14px 20px; text-align:center; min-width:100px;
}
.my-rank-box .rank-num { font-size:28px; font-weight:800; color:#6c63ff; }
.my-rank-box .rank-label { font-size:11px; color:#888; }

.ranking-table {
    width:100%; border-collapse:collapse; margin-top:12px; font-size:13px;
}
.ranking-table th {
    background:#f4f4f4; padding:10px; text-align:left; color:#555;
}
.ranking-table td { padding:10px; border-bottom:1px solid #eee; }
.ranking-table tr.highlight td { background:#f0eeff; font-weight:700; }

.result-pending {
    background:#fef3c7; color:#92400e; padding:10px 16px;
    border-radius:10px; font-size:13px; font-weight:600;
    display:inline-block; margin-top:8px;
}
</style>
</head>

<body>
<div class="container">

<div class="header">
    <h2>🎯 My Competitions</h2>
    <a href="javascript:history.back()" class="back-btn">← Go Back</a>
</div>

<?php while($row = $result->fetch_assoc()): ?>

<?php
$start   = $row['start_date'];
$end     = $row['end_date'];
$comp_id = $row['competition_id'];

if ($today < $start) {
    $status = "notyet"; $status_text = "Not Started";
} elseif ($today > $end) {
    $status = "ended"; $status_text = "Ended";
} else {
    $status = "ongoing"; $status_text = "Ongoing";
}

/* Submit 只要 end_date 还没到就可以 */
$can_submit = ($today <= $end);

$my_score = null;
$my_rank  = null;
$all_ranks = [];

if ($row['result_status'] === 'published') {
    $rank_query = $conn->prepare("
        SELECT p.id, u.fullname, ROUND(AVG(s.score),2) as avg_score
        FROM competition_participants p
        JOIN users u ON p.user_id = u.id
        LEFT JOIN competition_scores s ON p.id = s.participant_id AND s.competition_id = ?
        WHERE p.competition_id = ? AND p.status = 'approved'
        GROUP BY p.id
        ORDER BY avg_score DESC
    ");
    $rank_query->bind_param("ii", $comp_id, $comp_id);
    $rank_query->execute();
    $rank_result = $rank_query->get_result();

    $rank_num = 1;
    while ($r = $rank_result->fetch_assoc()) {
        $r['rank'] = $rank_num++;
        $all_ranks[] = $r;
        if ($r['id'] == $row['id']) {
            $my_score = $r['avg_score'];
            $my_rank  = $r['rank'];
        }
    }
}
?>

<div class="card">
    <div class="card-top">
        <div class="info">
            <h3><?= htmlspecialchars($row['title']) ?></h3>
            <p>Category: <?= htmlspecialchars($row['category']) ?></p>
            <p><?= $start ?> → <?= $end ?></p>
            <span class="badge <?= $status ?>"><?= $status_text ?></span>
        </div>

        <div class="actions">
            <!-- FIXED: 去掉 ../Participant/ 前缀，文件在同一文件夹 -->
            <a class="btn" href="competition_detail.php?id=<?= $comp_id ?>">View Detail</a>

            <?php if ($can_submit): ?>
                <a class="btn submit" href="submit-work.php?id=<?= $comp_id ?>">Submit Work 📤</a>
            <?php else: ?>
                <div class="btn disabled">Submit Closed</div>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($row['result_status'] === 'published'): ?>
    <div class="result-section">
        <div class="result-title">🏆 Competition Result</div>
        <div class="result-box">
            <div class="my-score-box">
                <div class="score-num"><?= $my_score ?? '—' ?></div>
                <div class="score-label">My Score / 100</div>
            </div>
            <div class="my-rank-box">
                <div class="rank-num">
                    <?php
                    if ($my_rank == 1) echo '🥇';
                    elseif ($my_rank == 2) echo '🥈';
                    elseif ($my_rank == 3) echo '🥉';
                    else echo '#' . $my_rank;
                    ?>
                </div>
                <div class="rank-label">My Rank</div>
            </div>
        </div>

        <table class="ranking-table">
            <thead>
                <tr><th>Rank</th><th>Participant</th><th>Score</th></tr>
            </thead>
            <tbody>
            <?php foreach ($all_ranks as $r): ?>
                <tr <?= ($r['id'] == $row['id']) ? 'class="highlight"' : '' ?>>
                    <td><?= $r['rank'] == 1 ? '🥇' : ($r['rank'] == 2 ? '🥈' : ($r['rank'] == 3 ? '🥉' : '#'.$r['rank'])) ?></td>
                    <td><?= htmlspecialchars($r['fullname']) ?> <?= ($r['id'] == $row['id']) ? '<b>(You)</b>' : '' ?></td>
                    <td><?= $r['avg_score'] ?? '—' ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php elseif ($status === 'ended'): ?>
    <div class="result-section">
        <div class="result-pending">⏳ Result pending — waiting for organiser to publish.</div>
    </div>
    <?php endif; ?>

</div>

<?php endwhile; ?>

</div>
</body>
</html>