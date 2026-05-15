<?php
session_start();
include "../config.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'judge') {
    header("Location: ../login.php");
    exit();
}

$judge_id       = (int) $_SESSION['user_id'];
$competition_id = intval($_GET['id'] ?? 0);

// Verify judge is assigned to this competition
$check = $conn->prepare("SELECT * FROM competition_judges WHERE competition_id=? AND judge_id=? AND status='approved'");
$check->bind_param("ii", $competition_id, $judge_id);
$check->execute();
if ($check->get_result()->num_rows === 0) {
    echo "<script>alert('Access denied.'); window.location.href='dashboard.php';</script>";
    exit();
}

// Get competition
$comp = $conn->query("SELECT * FROM competition_applications WHERE id = $competition_id")->fetch_assoc();

// Check if competition has ended (scoring opens next day after end_date)
$now          = new DateTime();
$end_date     = new DateTime($comp['end_date']);
$scoring_open = ($now >= (clone $end_date)->modify('+1 day'));

// Check if result is already published (lock scoring)
$result_published = isset($comp['result_status']) && $comp['result_status'] === 'published';

// Handle score submit
$success = "";
$error   = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($result_published) {
        $error = "❌ Results have been published. Scores are now locked and cannot be changed.";
    } elseif (!$scoring_open) {
        $error = "❌ Scoring is not open yet. Please wait until the competition ends on " . date("d M Y", strtotime($comp['end_date'])) . ".";
    } else {
        $participant_id = intval($_POST['participant_id']);
        $score          = floatval($_POST['score']);
        $comment        = trim($_POST['comment'] ?? '');

        // Check participant has a submission
        $sub_check = $conn->prepare("SELECT id FROM competition_submissions WHERE competition_id=? AND user_id=(SELECT user_id FROM competition_participants WHERE id=?)");
        $sub_check->bind_param("ii", $competition_id, $participant_id);
        $sub_check->execute();
        if ($sub_check->get_result()->num_rows === 0) {
            $error = "❌ This participant has not submitted their work yet.";
        } elseif ($score < 0 || $score > 100) {
            $error = "❌ Score must be between 0 and 100.";
        } else {
            // Check if already scored
            $existing = $conn->prepare("SELECT id FROM competition_scores WHERE competition_id=? AND participant_id=? AND judge_id=?");
            $existing->bind_param("iii", $competition_id, $participant_id, $judge_id);
            $existing->execute();
            $ex = $existing->get_result()->fetch_assoc();

            if ($ex) {
                $upd = $conn->prepare("UPDATE competition_scores SET score=?, comment=? WHERE id=?");
                $upd->bind_param("dsi", $score, $comment, $ex['id']);
                $upd->execute();
                $success = "✅ Score updated successfully!";
            } else {
                $ins = $conn->prepare("INSERT INTO competition_scores (competition_id, participant_id, judge_id, score, comment) VALUES (?,?,?,?,?)");
                $ins->bind_param("iiiis", $competition_id, $participant_id, $judge_id, $score, $comment);
                $ins->execute();
                $success = "✅ Score submitted successfully!";
            }
        }
    }
}

// Get participants with submissions
$participants = $conn->query("
    SELECT cp.*, u.fullname, u.email AS user_email,
           cs.title AS sub_title, cs.file_path, cs.submitted_at,
           cs.id AS submission_id,
           sc.score AS existing_score, sc.comment AS existing_comment, sc.id AS score_id
    FROM competition_participants cp
    INNER JOIN users u ON cp.user_id = u.id
    LEFT JOIN competition_submissions cs ON cs.competition_id = cp.competition_id AND cs.user_id = cp.user_id
    LEFT JOIN competition_scores sc ON sc.competition_id = cp.competition_id AND sc.participant_id = cp.id AND sc.judge_id = $judge_id
    WHERE cp.competition_id = $competition_id AND cp.status = 'approved'
    ORDER BY cp.id ASC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Score Competition</title>
    <link rel="icon" type="image/png" href="../images/logo.png">
    <link rel="stylesheet" href="../Admin-css/dashboard.css">
    <link rel="stylesheet" href="../Judge-css/score-competition.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<div class="sidebar">
    <div class="logo"></div>
    <ul class="menu">
        <li>
            <a href="dashboard.php">
                <i class="fas fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
        </li>
        <li class="active">
            <a href="my-competitions.php">
                <i class="fas fa-trophy"></i>
                <span>My Competitions</span>
            </a>
        </li>
        <li class="logout">
            <a href="../logout.php">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </li>
    </ul>
</div>

<div class="main--content">

    <div class="header--wrapper">
        <div class="header--title">
            <span>Judge</span>
            <h2>Score Competition</h2>
        </div>
    </div>

    <a href="dashboard.php" class="btn-back">← Back to Dashboard</a>

    <div class="comp-banner">
        <h2><?= htmlspecialchars($comp['title']) ?></h2>
        <p>📌 <?= htmlspecialchars($comp['category']) ?> &nbsp;|&nbsp; 📅 <?= $comp['start_date'] ?> → <?= $comp['end_date'] ?></p>
    </div>

    <?php if ($result_published): ?>
        <div class="published-locked">
            <i class="fas fa-lock"></i>
            Results have been published by the organizer. All scores are now <strong>locked</strong> and cannot be changed.
        </div>
    <?php elseif (!$scoring_open): ?>
        <div class="scoring-locked">
            <i class="fas fa-clock"></i>
            Scoring is locked until the day after the competition ends on <strong><?= date("d M Y", strtotime($comp['end_date'])) ?></strong>. Please check back after the deadline.
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="msg-success"><?= $success ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="msg-error"><?= $error ?></div>
    <?php endif; ?>

    <div class="card--container">
        <div class="section-title">👥 Participants & Submissions</div>

        <?php
        $rows = [];
        while ($r = $participants->fetch_assoc()) $rows[] = $r;
        ?>

        <?php if (empty($rows)): ?>
            <p style="color:#aaa; text-align:center; padding:30px;">No approved participants yet.</p>
        <?php else: ?>
            <?php foreach ($rows as $i => $p): ?>
            <div class="participant-card <?= $result_published ? 'locked' : '' ?>">

                <h4><?= ($i + 1) . '. ' . htmlspecialchars($p['fullname']) ?></h4>
                <div class="sub-info"><?= htmlspecialchars($p['user_email']) ?></div>

                <?php if ($result_published): ?>
                    <!-- PUBLISHED: show score as read-only, locked -->
                    <?php if ($p['existing_score'] !== null): ?>
                        <div class="score-badge-locked">🔒 Final Score: <?= $p['existing_score'] ?>/100</div><br>
                        <div class="score-readonly">
                            <div class="readonly-label">Your Score (Locked)</div>
                            <div class="readonly-value"><?= number_format($p['existing_score'], 1) ?> <span style="font-size:14px;font-weight:400;">/ 100</span></div>
                            <?php if (!empty($p['existing_comment'])): ?>
                                <div class="readonly-comment">"<?= htmlspecialchars($p['existing_comment']) ?>"</div>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="score-badge-locked">🔒 No score submitted</div>
                    <?php endif; ?>

                <?php else: ?>
                    <!-- NOT PUBLISHED: normal scoring flow -->
                    <?php if ($p['existing_score'] !== null): ?>
                        <div class="score-badge">⭐ Current Score: <?= $p['existing_score'] ?>/100</div><br>
                        <?php if (!empty($p['existing_comment'])): ?>
                            <div class="sub-info">Comment: <?= htmlspecialchars($p['existing_comment']) ?></div>
                        <?php endif; ?>
                    <?php endif; ?>

                    <?php if ($p['submission_id']): ?>
                        <div class="sub-info">📄 Submission: <?= htmlspecialchars($p['sub_title']) ?> — <?= date("d M Y", strtotime($p['submitted_at'])) ?></div>
                        <a href="../uploads/submissions/<?= htmlspecialchars($p['file_path']) ?>" target="_blank" class="file-link">
                            <i class="fas fa-download"></i> Download Submission
                        </a>
                    <?php else: ?>
                        <div class="no-submission">No submission yet.</div>
                    <?php endif; ?>

                    <?php if (!$scoring_open): ?>
                        <div class="waiting-score">
                            ⏳ Scoring opens after <?= date("d M Y", strtotime($comp['end_date'])) ?>
                        </div>
                    <?php elseif (!$p['submission_id']): ?>
                        <div class="no-submission-warning">
                            ❌ No submission — this participant cannot be scored.
                        </div>
                    <?php else: ?>
                        <form method="POST" class="score-form" style="margin-top:12px;">
                            <input type="hidden" name="participant_id" value="<?= $p['id'] ?>">

                            <div class="field">
                                <label>Score (0–100)</label>
                                <input type="number" name="score" min="0" max="100" step="0.5"
                                       value="<?= $p['existing_score'] ?? '' ?>" required>
                            </div>

                            <div class="field">
                                <label>Comment (optional)</label>
                                <textarea name="comment" placeholder="Leave a comment..."><?= htmlspecialchars($p['existing_comment'] ?? '') ?></textarea>
                            </div>

                            <button type="submit" class="btn-score">
                                <?= $p['existing_score'] !== null ? '✏️ Update Score' : '⭐ Submit Score' ?>
                            </button>
                        </form>
                    <?php endif; ?>
                <?php endif; ?>

            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

</div>

</body>
</html>