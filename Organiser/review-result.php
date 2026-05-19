<?php
session_start();
include "../config.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id        = (int) $_SESSION['user_id'];
$competition_id = intval($_GET['id'] ?? 0);

$stmt = $conn->prepare("SELECT * FROM competition_applications WHERE id=? AND organizer_id=? AND status='approved'");
$stmt->bind_param("ii", $competition_id, $user_id);
$stmt->execute();
$comp = $stmt->get_result()->fetch_assoc();

if (!$comp) {
    echo "<script>alert('Competition not found.'); window.location.href='dashboard.php';</script>";
    exit();
}

if (isset($_POST['publish_result'])) {

    $publish = $conn->prepare("UPDATE competition_applications SET result_status='published' WHERE id=?");
    $publish->bind_param("i", $competition_id);
    $publish->execute();

    $ranking_query = $conn->prepare("
        SELECT p.id, u.fullname, u.email,
               ROUND(AVG(s.score), 2) as average_score
        FROM competition_participants p
        JOIN users u ON p.user_id = u.id
        LEFT JOIN competition_scores s ON p.id = s.participant_id AND s.competition_id = ?
        WHERE p.competition_id = ? AND p.status = 'approved'
        GROUP BY p.id
        ORDER BY average_score DESC
    ");
    $ranking_query->bind_param("ii", $competition_id, $competition_id);
    $ranking_query->execute();
    $ranking_result = $ranking_query->get_result();

    $all_participants = [];
    $rank = 1;
    while ($r = $ranking_result->fetch_assoc()) {
        $r['rank'] = $rank++;
        $all_participants[] = $r;
    }

    $ranking_html = "";
    foreach ($all_participants as $p) {
        $medal = $p['rank'] == 1 ? '🥇' : ($p['rank'] == 2 ? '🥈' : ($p['rank'] == 3 ? '🥉' : '#' . $p['rank']));
        $ranking_html .= "
        <tr>
            <td style='padding:10px; text-align:center;'>{$medal}</td>
            <td style='padding:10px;'>" . htmlspecialchars($p['fullname']) . "</td>
            <td style='padding:10px; text-align:center; font-weight:700;'>" . ($p['average_score'] ?? '—') . "</td>
        </tr>";
    }

    foreach ($all_participants as $p) {
        $medal = $p['rank'] == 1 ? '🥇' : ($p['rank'] == 2 ? '🥈' : ($p['rank'] == 3 ? '🥉' : '#' . $p['rank']));

        try {
            $mail = require __DIR__ . "/../mailer.php";

            $mail->setFrom('tanjiaqian1239@gmail.com', 'Matchify Competition Management Platform');
            $mail->addAddress($p['email'], $p['fullname']);
            $mail->isHTML(true);
            $mail->Subject = 'Competition Results Published - ' . $comp['title'];

            $login_url = "http://localhost/Matchify%20Competition%20Management%20Platform/Participant/my-competition.php";

            $mail->Body = "
            <div style='font-family:Arial,sans-serif; max-width:600px; margin:auto; padding:30px; background:#f4f6fb; border-radius:16px;'>

                <div style='background:linear-gradient(135deg,#6c63ff,#a855f7); padding:30px; border-radius:12px; text-align:center; margin-bottom:24px;'>
                    <h1 style='color:#fff; margin:0; font-size:22px;'>Results Published!</h1>
                    <p style='color:rgba(255,255,255,0.85); margin:8px 0 0; font-size:14px;'>" . htmlspecialchars($comp['title']) . "</p>
                </div>

                <div style='background:#fff; padding:24px; border-radius:12px; margin-bottom:16px;'>
                    <p style='color:#333; font-size:15px;'>Hello <b>" . htmlspecialchars($p['fullname']) . "</b>,</p>
                    <p style='color:#555; font-size:14px; margin-top:10px;'>
                        The results for <b>" . htmlspecialchars($comp['title']) . "</b> have been published!
                    </p>

                    <div style='background:#f0eeff; padding:16px 20px; border-radius:10px; margin:16px 0; text-align:center;'>
                        <p style='margin:0; color:#555; font-size:13px;'>Your Ranking</p>
                        <p style='margin:8px 0 0; font-size:32px;'>{$medal}</p>
                        <p style='margin:4px 0 0; color:#333; font-size:16px; font-weight:700;'>
                            Score: " . ($p['average_score'] ?? '—') . " / 100
                        </p>
                    </div>

                    <p style='color:#555; font-size:14px; margin-top:16px;'>Full Rankings:</p>
                    <table style='width:100%; border-collapse:collapse; font-size:13px;'>
                        <thead>
                            <tr style='background:#f4f4f4;'>
                                <th style='padding:10px; text-align:center;'>Rank</th>
                                <th style='padding:10px; text-align:left;'>Participant</th>
                                <th style='padding:10px; text-align:center;'>Score</th>
                            </tr>
                        </thead>
                        <tbody>
                            {$ranking_html}
                        </tbody>
                    </table>

                    <div style='text-align:center; margin-top:20px;'>
                        <a href='{$login_url}'
                           style='display:inline-block; padding:12px 28px; background:linear-gradient(135deg,#6c63ff,#a855f7); color:#fff; border-radius:10px; text-decoration:none; font-weight:600; font-size:14px;'>
                           View My Results
                        </a>
                    </div>
                </div>

                <p style='text-align:center; color:#aaa; font-size:12px;'>Matchify Competition Management Platform</p>
            </div>
            ";

            $mail->AltBody = "Hello {$p['fullname']}, results for {$comp['title']} have been published. Your rank: {$medal}, Score: " . ($p['average_score'] ?? '—') . ". View: {$login_url}";

            $mail->send();

        } catch (Exception $e) {
            error_log("[Matchify] Result email failed for {$p['email']} - " . $e->getMessage());
        }
    }

    header("Location: review-result.php?id=$competition_id&msg=published");
    exit();
}

$msg = $_GET['msg'] ?? '';

$total_query = $conn->prepare("SELECT COUNT(*) as total FROM competition_participants WHERE competition_id=? AND status='approved'");
$total_query->bind_param("i", $competition_id);
$total_query->execute();
$total_participants = $total_query->get_result()->fetch_assoc()['total'];

$judge_query = $conn->prepare("SELECT * FROM competition_judges WHERE competition_id=? AND status='approved'");
$judge_query->bind_param("i", $competition_id);
$judge_query->execute();
$judges = $judge_query->get_result();

$judge_progress = [];
while ($judge = $judges->fetch_assoc()) {
    $judge_user_id = $judge['judge_id'];
    $score_query = $conn->prepare("SELECT COUNT(DISTINCT participant_id) as scored FROM competition_scores WHERE competition_id=? AND judge_id=?");
    $score_query->bind_param("ii", $competition_id, $judge_user_id);
    $score_query->execute();
    $scored = $score_query->get_result()->fetch_assoc()['scored'];
    $judge_progress[] = [
        'judge'  => $judge,
        'scored' => $scored,
        'done'   => ($scored >= $total_participants && $total_participants > 0)
    ];
}

$all_completed = !empty($judge_progress);
foreach ($judge_progress as $jp) {
    if (!$jp['done']) { $all_completed = false; break; }
}

$participant_query = $conn->prepare("
    SELECT p.id, u.fullname,
           ROUND(AVG(s.score), 2) as average_score,
           COUNT(s.id) as total_scores
    FROM competition_participants p
    JOIN users u ON p.user_id = u.id
    LEFT JOIN competition_scores s ON p.id = s.participant_id AND s.competition_id = ?
    WHERE p.competition_id=? AND p.status='approved'
    GROUP BY p.id
    ORDER BY average_score DESC
");
$participant_query->bind_param("ii", $competition_id, $competition_id);
$participant_query->execute();
$participants = $participant_query->get_result();

$profile_image = "../images/profile.avif";
$uq = $conn->query("SELECT profile_image FROM users WHERE id = $user_id");
if ($uq && $uq->num_rows > 0) {
    $u = $uq->fetch_assoc();
    if (!empty($u['profile_image'])) {
        $path = "/images/profile/" . $u['profile_image'];
        if (file_exists($_SERVER['DOCUMENT_ROOT'] . $path)) { $profile_image = $path; }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Review Result</title>
<link rel="icon" type="image/png" href="../images/logo.png">
<link rel="stylesheet" href="../Admin-css/dashboard.css">
<link rel="stylesheet" href="../Organiser-css/review-result.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<div class="sidebar">
    <div class="logo"></div>
    <ul class="menu">
        <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a></li>
        <li><a href="profile.php"><i class="fas fa-user"></i><span>Profile</span></a></li>
        <li class="active"><a href="apply-competition.php"><i class="fas fa-trophy"></i><span>Apply Competition</span></a></li>
        <li class="logout"><a href="../logout.php"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a></li>
    </ul>
</div>

<div class="main--content">

    <div class="header--wrapper">
        <div class="header--title">
            <span>Organiser</span>
            <h2>Review Result</h2>
        </div>
        <div class="user--info">
            <img src="<?= $profile_image ?>" alt="">
        </div>
    </div>

    <div class="review-wrapper">

        <a href="dashboard.php" class="btn-back">← Go Back</a>

        <div class="comp-info">
            <h2><?= htmlspecialchars($comp['title']) ?></h2>
            <p>📌 <?= htmlspecialchars($comp['category']) ?> &nbsp;|&nbsp; 🏆 <?= $comp['start_date'] ?> → <?= $comp['end_date'] ?></p>
        </div>

        <?php if ($msg === 'published'): ?>
            <div class="msg-box success">✅ Result published and email sent to all participants!</div>
        <?php endif; ?>

        <div class="card--container">
            <div class="section-title">👨‍⚖️ Judge Progress</div>
            <?php if (empty($judge_progress)): ?>
                <p style="color:#aaa; text-align:center; padding:20px;">No judges assigned yet.</p>
            <?php else: ?>
            <table class="result-table">
                <thead>
                    <tr><th>#</th><th>Judge Name</th><th>Email</th><th>Progress</th><th>Status</th></tr>
                </thead>
                <tbody>
                <?php $i = 1; foreach ($judge_progress as $jp): $j = $jp['judge']; ?>
                    <tr>
                        <td><?= $i++ ?></td>
                        <td><?= htmlspecialchars($j['judge_name']) ?></td>
                        <td><?= htmlspecialchars($j['judge_email']) ?></td>
                        <td><?= $jp['scored'] ?> / <?= $total_participants ?></td>
                        <td>
                            <?php if ($jp['done']): ?>
                                <span class="status completed">✅ Completed</span>
                            <?php else: ?>
                                <span class="status pending">⏳ In Progress</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>

        <div class="card--container">
            <div class="section-title">🏆 Participant Ranking</div>
            <table class="result-table">
                <thead>
                    <tr><th>Rank</th><th>Participant</th><th>Average Score</th><th>Total Scores Given</th></tr>
                </thead>
                <tbody>
                <?php $rank = 1; while ($p = $participants->fetch_assoc()): ?>
                    <tr>
                        <td><?= $rank == 1 ? '🥇' : ($rank == 2 ? '🥈' : ($rank == 3 ? '🥉' : '#'.$rank)) ?></td>
                        <td><?= htmlspecialchars($p['fullname']) ?></td>
                        <td><?= $p['average_score'] ?? '—' ?></td>
                        <td><?= $p['total_scores'] ?></td>
                    </tr>
                <?php $rank++; endwhile; ?>
                </tbody>
            </table>
        </div>

        <div class="publish-box">
            <?php if ($all_completed): ?>
                <?php if (($comp['result_status'] ?? '') === 'published'): ?>
                    <div class="published-label">✅ Result Already Published</div>
                <?php else: ?>
                    <form method="POST">
                        <button type="submit" name="publish_result" class="btn-publish"
                                onclick="return confirm('Publish result and notify all participants by email?')">
                            🚀 Publish Result & Notify Participants
                        </button>
                    </form>
                <?php endif; ?>
            <?php else: ?>
                <div class="publish-warning">⚠️ All judges must complete scoring before publishing result.</div>
            <?php endif; ?>
        </div>

    </div>
</div>

</body>
</html>