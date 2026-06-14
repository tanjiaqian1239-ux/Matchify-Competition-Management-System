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

    // Build ranking table HTML
    $ranking_html = "";
    foreach ($all_participants as $p) {
        $rank_label = $p['rank'] == 1 ? '1st' : ($p['rank'] == 2 ? '2nd' : ($p['rank'] == 3 ? '3rd' : '#' . $p['rank']));
        $ranking_html .= "
        <tr>
            <td style='padding:10px; text-align:center;'>{$rank_label}</td>
            <td style='padding:10px;'>" . htmlspecialchars($p['fullname']) . "</td>
            <td style='padding:10px; text-align:center; font-weight:700;'>" . ($p['average_score'] ?? '-') . "</td>
        </tr>";
    }

    $organiser_name  = $comp['organizer'] ?? 'The Organiser';
    $organiser_email = $comp['email'] ?? '';
    $prize_1st = $comp['prize_1st'] ?? null;
    $prize_2nd = $comp['prize_2nd'] ?? null;
    $prize_3rd = $comp['prize_3rd'] ?? null;
    $login_url = "http://localhost/Matchify%20Competition%20Management%20Platform/Participant/my-competition.php";

    foreach ($all_participants as $p) {
        $rank_label = $p['rank'] == 1 ? '1st Place' : ($p['rank'] == 2 ? '2nd Place' : ($p['rank'] == 3 ? '3rd Place' : 'Rank #' . $p['rank']));
        $is_winner = ($p['rank'] <= 3);

        $prize = null;
        if ($p['rank'] == 1 && $prize_1st) $prize = $prize_1st;
        elseif ($p['rank'] == 2 && $prize_2nd) $prize = $prize_2nd;
        elseif ($p['rank'] == 3 && $prize_3rd) $prize = $prize_3rd;

        try {
            $mail = require __DIR__ . "/../mailer.php";
            $mail->setFrom('tanjiaqian1239@gmail.com', 'Matchify Competition Management Platform');
            $mail->addAddress($p['email'], $p['fullname']);
            $mail->isHTML(true);
            $mail->Subject = 'Competition Results - ' . $comp['title'];

            if ($is_winner) {

                $prize_section = "";
                if ($prize) {
                    $prize_section = "
                    <table width='100%' cellpadding='0' cellspacing='0' style='background:#fffbeb; border:1px solid #fde68a; border-radius:10px; margin:16px 0;'>
                        <tr><td style='padding:16px 20px;'>
                            <p style='margin:0 0 6px; font-size:12px; color:#92400e; font-weight:700; text-transform:uppercase; letter-spacing:0.5px;'>Your Prize</p>
                            <p style='margin:0; font-size:18px; font-weight:800; color:#b45309;'>{$prize}</p>
                        </td></tr>
                    </table>
                    <table width='100%' cellpadding='0' cellspacing='0' style='background:#f0fdf4; border:1px solid #bbf7d0; border-radius:10px; margin:16px 0;'>
                        <tr><td style='padding:16px 20px;'>
                            <p style='margin:0 0 8px; font-size:13px; color:#166534; font-weight:700;'>How to Claim Your Prize</p>
                            <p style='margin:0; font-size:14px; color:#15803d; line-height:1.7;'>Please contact the organiser to arrange prize collection:</p>
                            <p style='margin:8px 0 0; font-size:14px; color:#166534;'>
                                <b>Organiser:</b> {$organiser_name}<br>
                                <b>Email:</b> <a href='mailto:{$organiser_email}' style='color:#16a34a;'>{$organiser_email}</a>
                            </p>
                        </td></tr>
                    </table>";
                }

                $mail->Body = "
                <!DOCTYPE html>
                <html>
                <body style='margin:0; padding:0; background:#f4f6f9; font-family:Arial, sans-serif;'>
                  <table width='100%' cellpadding='0' cellspacing='0' style='padding:40px 0;'>
                    <tr><td align='center'>
                      <table width='560' cellpadding='0' cellspacing='0' style='background:#fff; border-radius:12px; overflow:hidden; box-shadow:0 4px 20px rgba(0,0,0,0.08);'>

                        <tr>
                          <td style='background:linear-gradient(135deg,#6c63ff,#a855f7); padding:32px 40px; text-align:center;'>
                            <h1 style='margin:0; color:#fff; font-size:24px; font-weight:800;'>Congratulations!</h1>
                            <p style='margin:8px 0 0; color:rgba(255,255,255,0.85); font-size:14px;'>" . htmlspecialchars($comp['title']) . "</p>
                          </td>
                        </tr>

                        <tr>
                          <td style='padding:32px 40px;'>
                            <p style='margin:0 0 6px; font-size:17px; font-weight:700; color:#111827;'>Dear " . htmlspecialchars($p['fullname']) . ",</p>
                            <p style='margin:12px 0; font-size:14px; color:#4b5563; line-height:1.8;'>
                                We are thrilled to announce that you have achieved <strong>{$rank_label}</strong> in <strong>" . htmlspecialchars($comp['title']) . "</strong>. Congratulations on your outstanding performance!
                            </p>

                            <table width='100%' cellpadding='0' cellspacing='0' style='background:#f0eeff; border-radius:12px; margin:20px 0;'>
                                <tr><td style='padding:20px; text-align:center;'>
                                    <p style='margin:0; font-size:12px; color:#7c3aed; font-weight:700; text-transform:uppercase; letter-spacing:0.5px;'>Your Result</p>
                                    <p style='margin:10px 0 4px; font-size:28px; font-weight:800; color:#1e1b4b;'>{$rank_label}</p>
                                    <p style='margin:6px 0 0; font-size:16px; color:#6c63ff; font-weight:700;'>Score: " . ($p['average_score'] ?? '-') . " / 100</p>
                                </td></tr>
                            </table>

                            {$prize_section}

                            <p style='margin:16px 0 8px; font-size:14px; color:#555; font-weight:700;'>Full Rankings:</p>
                            <table width='100%' cellpadding='0' cellspacing='0' style='border-collapse:collapse; font-size:13px;'>
                                <thead>
                                    <tr style='background:#f4f4f4;'>
                                        <th style='padding:10px; text-align:center;'>Rank</th>
                                        <th style='padding:10px; text-align:left;'>Participant</th>
                                        <th style='padding:10px; text-align:center;'>Score</th>
                                    </tr>
                                </thead>
                                <tbody>{$ranking_html}</tbody>
                            </table>

                            <table width='100%' cellpadding='0' cellspacing='0' style='margin-top:24px;'>
                                <tr><td align='center'>
                                    <a href='{$login_url}' style='display:inline-block; padding:14px 36px; background:linear-gradient(135deg,#6c63ff,#a855f7); color:#fff; border-radius:8px; text-decoration:none; font-size:14px; font-weight:700;'>
                                        View My Results
                                    </a>
                                </td></tr>
                            </table>
                          </td>
                        </tr>

                        <tr>
                          <td style='background:#f9fafb; border-top:1px solid #f0f0f0; padding:16px 40px; text-align:center;'>
                            <p style='margin:0; font-size:12px; color:#9ca3af;'>Matchify Competition Management Platform</p>
                          </td>
                        </tr>

                      </table>
                    </td></tr>
                  </table>
                </body>
                </html>";

            } else {

                $mail->Body = "
                <!DOCTYPE html>
                <html>
                <body style='margin:0; padding:0; background:#f4f6f9; font-family:Arial, sans-serif;'>
                  <table width='100%' cellpadding='0' cellspacing='0' style='padding:40px 0;'>
                    <tr><td align='center'>
                      <table width='560' cellpadding='0' cellspacing='0' style='background:#fff; border-radius:12px; overflow:hidden; box-shadow:0 4px 20px rgba(0,0,0,0.08);'>

                        <tr>
                          <td style='background:linear-gradient(135deg,#6c63ff,#a855f7); padding:32px 40px; text-align:center;'>
                            <h1 style='margin:0; color:#fff; font-size:22px; font-weight:800;'>Competition Results</h1>
                            <p style='margin:8px 0 0; color:rgba(255,255,255,0.85); font-size:14px;'>" . htmlspecialchars($comp['title']) . "</p>
                          </td>
                        </tr>

                        <tr>
                          <td style='padding:32px 40px;'>
                            <p style='margin:0 0 6px; font-size:17px; font-weight:700; color:#111827;'>Dear " . htmlspecialchars($p['fullname']) . ",</p>
                            <p style='margin:12px 0; font-size:14px; color:#4b5563; line-height:1.8;'>
                                Thank you for participating in <strong>" . htmlspecialchars($comp['title']) . "</strong>. We sincerely appreciate your effort and dedication throughout the competition.
                            </p>
                            <p style='margin:0 0 20px; font-size:14px; color:#4b5563; line-height:1.8;'>
                                Unfortunately, you did not place in the top 3 this time. We hope this experience has been valuable and encourages you to keep pushing forward. We look forward to seeing you in future competitions.
                            </p>

                            <table width='100%' cellpadding='0' cellspacing='0' style='background:#f0eeff; border-radius:12px; margin:20px 0;'>
                                <tr><td style='padding:20px; text-align:center;'>
                                    <p style='margin:0; font-size:12px; color:#7c3aed; font-weight:700; text-transform:uppercase; letter-spacing:0.5px;'>Your Result</p>
                                    <p style='margin:10px 0 4px; font-size:26px; font-weight:800; color:#1e1b4b;'>{$rank_label}</p>
                                    <p style='margin:6px 0 0; font-size:16px; color:#6c63ff; font-weight:700;'>Score: " . ($p['average_score'] ?? '-') . " / 100</p>
                                </td></tr>
                            </table>

                            <p style='margin:16px 0 8px; font-size:14px; color:#555; font-weight:700;'>Full Rankings:</p>
                            <table width='100%' cellpadding='0' cellspacing='0' style='border-collapse:collapse; font-size:13px;'>
                                <thead>
                                    <tr style='background:#f4f4f4;'>
                                        <th style='padding:10px; text-align:center;'>Rank</th>
                                        <th style='padding:10px; text-align:left;'>Participant</th>
                                        <th style='padding:10px; text-align:center;'>Score</th>
                                    </tr>
                                </thead>
                                <tbody>{$ranking_html}</tbody>
                            </table>

                            <table width='100%' cellpadding='0' cellspacing='0' style='margin-top:24px;'>
                                <tr><td align='center'>
                                    <a href='{$login_url}' style='display:inline-block; padding:14px 36px; background:linear-gradient(135deg,#6c63ff,#a855f7); color:#fff; border-radius:8px; text-decoration:none; font-size:14px; font-weight:700;'>
                                        View My Results
                                    </a>
                                </td></tr>
                            </table>
                          </td>
                        </tr>

                        <tr>
                          <td style='background:#f9fafb; border-top:1px solid #f0f0f0; padding:16px 40px; text-align:center;'>
                            <p style='margin:0; font-size:12px; color:#9ca3af;'>Matchify Competition Management Platform</p>
                          </td>
                        </tr>

                      </table>
                    </td></tr>
                  </table>
                </body>
                </html>";
            }

            $mail->AltBody = $is_winner
                ? "Congratulations {$p['fullname']}! You achieved {$rank_label} in {$comp['title']}. Score: " . ($p['average_score'] ?? '-') . ". Prize: {$prize}. Contact organiser at {$organiser_email} to claim your prize."
                : "Dear {$p['fullname']}, thank you for participating in {$comp['title']}. Unfortunately you did not place in the top 3. Your result: {$rank_label}, Score: " . ($p['average_score'] ?? '-') . ".";

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
        <li class="logout"><a href="../login.php"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a></li>
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
            <p><?= htmlspecialchars($comp['category']) ?> | <?= $comp['start_date'] ?> → <?= $comp['end_date'] ?></p>
        </div>

        <?php if ($msg === 'published'): ?>
            <div class="msg-box success">Result published and email sent to all participants!</div>
        <?php endif; ?>

        <div class="card--container">
            <div class="section-title">Judge Progress</div>
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
                                <span class="status completed">Completed</span>
                            <?php else: ?>
                                <span class="status pending">In Progress</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>

        <div class="card--container">
            <div class="section-title">Participant Ranking</div>
            <table class="result-table">
                <thead>
                    <tr><th>Rank</th><th>Participant</th><th>Average Score</th><th>Total Scores Given</th></tr>
                </thead>
                <tbody>
                <?php $rank = 1; while ($p = $participants->fetch_assoc()): ?>
                    <tr>
                        <td><?= $rank == 1 ? '1st' : ($rank == 2 ? '2nd' : ($rank == 3 ? '3rd' : '#'.$rank)) ?></td>
                        <td><?= htmlspecialchars($p['fullname']) ?></td>
                        <td><?= $p['average_score'] ?? '-' ?></td>
                        <td><?= $p['total_scores'] ?></td>
                    </tr>
                <?php $rank++; endwhile; ?>
                </tbody>
            </table>
        </div>

        <div class="publish-box">
            <?php if ($all_completed): ?>
                <?php if (($comp['result_status'] ?? '') === 'published'): ?>
                    <div class="published-label">Result Already Published</div>
                <?php else: ?>
                    <form method="POST">
                        <button type="submit" name="publish_result" class="btn-publish"
                                onclick="return confirm('Publish result and notify all participants by email?')">
                            Publish Result and Notify Participants
                        </button>
                    </form>
                <?php endif; ?>
            <?php else: ?>
                <div class="publish-warning">All judges must complete scoring before publishing result.</div>
            <?php endif; ?>
        </div>

    </div>
</div>

</body>
</html>