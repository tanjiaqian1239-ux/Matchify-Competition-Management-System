<?php
session_start();
include "../config.php";
requireRole(['admin','superadmin']);

$comp_id = intval($_GET['id'] ?? 0);

/* =========================
   APPROVE / REJECT
========================= */
if (isset($_GET['action']) && isset($_GET['judge_id'])) {

    $judge_id = intval($_GET['judge_id']);
    $action   = $_GET['action'];

    /* ================= APPROVE ================= */
    if ($action === 'approve') {

        $get = $conn->prepare("SELECT * FROM competition_judges WHERE id=?");
        $get->bind_param("i", $judge_id);
        $get->execute();
        $judge = $get->get_result()->fetch_assoc();

        if ($judge) {

            $email = $judge['judge_email'];
            $name  = $judge['judge_name'];

            // Get competition title
            $comp_stmt = $conn->prepare("SELECT title FROM competition_applications WHERE id=?");
            $comp_stmt->bind_param("i", $judge['competition_id']);
            $comp_stmt->execute();
            $comp_title = $comp_stmt->get_result()->fetch_assoc()['title'] ?? 'N/A';

            // Check if user already exists
            $check = $conn->prepare("SELECT id FROM users WHERE email=?");
            $check->bind_param("s", $email);
            $check->execute();
            $existing = $check->get_result()->fetch_assoc();

            $is_new_account = false;
            $temp_pass      = null;

            if ($existing) {
                // Use existing account — no new password
                $user_id = $existing['id'];
            } else {
                // Create new account
                $is_new_account = true;
                $temp_pass      = bin2hex(random_bytes(4));
                $hashed_pass    = password_hash($temp_pass, PASSWORD_DEFAULT);
                $username       = 'judge_' . time();

                $ins = $conn->prepare("INSERT INTO users (fullname, username, email, password, role, status) VALUES (?,?,?,?,'judge','active')");
                $ins->bind_param("ssss", $name, $username, $email, $hashed_pass);
                $ins->execute();
                $user_id = $conn->insert_id;
            }

            // Update judge record
            $upd = $conn->prepare("UPDATE competition_judges SET status='approved', judge_id=?, temp_password=? WHERE id=?");
            $upd->bind_param("isi", $user_id, $temp_pass, $judge_id);
            $upd->execute();

            // Send email
            $mail_sent  = false;
            $mail_error = '';

            try {
                $mail = require __DIR__ . "/../mailer.php";

                $mail->setFrom('tanjiaqian1239@gmail.com', 'Matchify Competition Management Platform');
                $mail->addAddress($email, $name);

                $login_url = "http://localhost/Matchify%20Competition%20Management%20Platform/login.php";

                if ($is_new_account) {
                    // New judge — send credentials
                    $mail->Subject = "You are approved as a Judge - Matchify";
                    $mail->Body = "
                    <!DOCTYPE html>
                    <html>
                    <body style='font-family:Arial,sans-serif; background:#f5f7fb; padding:30px;'>
                      <div style='max-width:520px; margin:auto; background:#fff; border-radius:16px; overflow:hidden; box-shadow:0 8px 30px rgba(0,0,0,0.08);'>
                        <div style='background:linear-gradient(135deg,#6d5dfc,#8a7dff); padding:30px; text-align:center;'>
                          <h1 style='color:#fff; margin:0; font-size:22px;'>Matchify</h1>
                          <p style='color:rgba(255,255,255,0.85); margin:6px 0 0; font-size:14px;'>Competition Management Platform</p>
                        </div>
                        <div style='padding:30px;'>
                          <h2 style='color:#111827; margin:0 0 10px;'>Hello, $name!</h2>
                          <p style='color:#4b5563; font-size:14px; line-height:1.7;'>
                            You have been approved as a <b>Judge</b> for:
                          </p>
                          <div style='background:#f5f3ff; border:1px solid #ddd6fe; border-radius:12px; padding:16px; margin:16px 0;'>
                            <b style='color:#4c1d95;'>🏆 $comp_title</b>
                          </div>
                          <div style='background:#f5f3ff; border:1px solid #ddd6fe; border-radius:12px; padding:20px; margin:20px 0;'>
                            <p style='margin:0 0 10px; font-size:13px; color:#6b7280; font-weight:600; text-transform:uppercase;'>Your Login Credentials</p>
                            <table style='width:100%; font-size:14px; color:#111827;'>
                              <tr>
                                <td style='padding:6px 0; width:120px; color:#6b7280;'>Email</td>
                                <td style='padding:6px 0;'><b>$email</b></td>
                              </tr>
                              <tr>
                                <td style='padding:6px 0; color:#6b7280;'>Password</td>
                                <td style='padding:6px 0;'><b style='color:#6d5dfc; font-size:18px; letter-spacing:2px;'>$temp_pass</b></td>
                              </tr>
                            </table>
                          </div>
                          <p style='color:#ef4444; font-size:13px;'>&#9888; Please change your password after your first login.</p>
                          <div style='text-align:center; margin:24px 0 10px;'>
                            <a href='$login_url' style='display:inline-block; padding:14px 36px; background:linear-gradient(135deg,#6d5dfc,#8a7dff); color:#fff; border-radius:30px; text-decoration:none; font-weight:600; font-size:14px;'>
                              Login to Matchify
                            </a>
                          </div>
                        </div>
                        <div style='background:#f9fafb; padding:16px; text-align:center; border-top:1px solid #f0f0f0;'>
                          <p style='margin:0; font-size:12px; color:#9ca3af;'>Matchify Competition Management Platform</p>
                        </div>
                      </div>
                    </body>
                    </html>
                    ";
                } else {
                    // Existing judge — notify new assignment only
                    $mail->Subject = "New Competition Assignment - Matchify";
                    $mail->Body = "
                    <!DOCTYPE html>
                    <html>
                    <body style='font-family:Arial,sans-serif; background:#f5f7fb; padding:30px;'>
                      <div style='max-width:520px; margin:auto; background:#fff; border-radius:16px; overflow:hidden; box-shadow:0 8px 30px rgba(0,0,0,0.08);'>
                        <div style='background:linear-gradient(135deg,#6d5dfc,#8a7dff); padding:30px; text-align:center;'>
                          <h1 style='color:#fff; margin:0; font-size:22px;'>Matchify</h1>
                          <p style='color:rgba(255,255,255,0.85); margin:6px 0 0; font-size:14px;'>Competition Management Platform</p>
                        </div>
                        <div style='padding:30px;'>
                          <h2 style='color:#111827; margin:0 0 10px;'>Hello, $name!</h2>
                          <p style='color:#4b5563; font-size:14px; line-height:1.7;'>
                            You have been assigned as a judge for a new competition:
                          </p>
                          <div style='background:#f5f3ff; border:1px solid #ddd6fe; border-radius:12px; padding:16px; margin:16px 0;'>
                            <b style='color:#4c1d95;'> $comp_title</b>
                          </div>
                          <p style='color:#4b5563; font-size:14px;'>Please login with your existing account to view and score this competition.</p>
                          <div style='text-align:center; margin:24px 0 10px;'>
                            <a href='$login_url' style='display:inline-block; padding:14px 36px; background:linear-gradient(135deg,#6d5dfc,#8a7dff); color:#fff; border-radius:30px; text-decoration:none; font-weight:600; font-size:14px;'>
                              Login to Matchify
                            </a>
                          </div>
                        </div>
                        <div style='background:#f9fafb; padding:16px; text-align:center; border-top:1px solid #f0f0f0;'>
                          <p style='margin:0; font-size:12px; color:#9ca3af;'>Matchify Competition Management Platform</p>
                        </div>
                      </div>
                    </body>
                    </html>
                    ";
                }

                $mail->AltBody = $is_new_account
                    ? "Hello $name, you are approved as judge.\nEmail: $email\nTemp Password: $temp_pass\nLogin: $login_url"
                    : "Hello $name, you have been assigned to a new competition: $comp_title.\nLogin: $login_url";

                $mail->send();
                $mail_sent = true;

            } catch (Exception $e) {
                $mail_error = $mail->ErrorInfo;
                error_log("[Matchify] Mail failed for $email - " . $mail->ErrorInfo);
            }

            if ($mail_sent) {
                header("Location: manage-judges-detail.php?id=$comp_id&msg=approved");
            } else {
                header("Location: manage-judges-detail.php?id=$comp_id&msg=mailfail&err=" . urlencode($mail_error));
            }
            exit();
        }
    }

    /* ================= REJECT ================= */
    if ($action === 'reject' && isset($_POST['reason'])) {
        $reason = $_POST['reason'];
        $upd = $conn->prepare("UPDATE competition_judges SET status='rejected', reject_reason=? WHERE id=?");
        $upd->bind_param("si", $reason, $judge_id);
        $upd->execute();
        header("Location: manage-judges-detail.php?id=$comp_id&msg=rejected");
        exit();
    }
}

/* ================= COMPETITION ================= */
$comp_q = $conn->prepare("SELECT * FROM competition_applications WHERE id=?");
$comp_q->bind_param("i", $comp_id);
$comp_q->execute();
$comp = $comp_q->get_result()->fetch_assoc();

if (!$comp) {
    header("Location: manage-judges.php");
    exit();
}

/* ================= JUDGES ================= */
$status = $_GET['status'] ?? 'all';

if ($status == 'all') {
    $stmt = $conn->prepare("SELECT * FROM competition_judges WHERE competition_id=? ORDER BY assigned_at DESC");
    $stmt->bind_param("i", $comp_id);
} else {
    $stmt = $conn->prepare("SELECT * FROM competition_judges WHERE competition_id=? AND status=? ORDER BY assigned_at DESC");
    $stmt->bind_param("is", $comp_id, $status);
}

$stmt->execute();
$judges = $stmt->get_result();

$msg = $_GET['msg'] ?? '';
$err = $_GET['err'] ?? '';
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Judges - <?= htmlspecialchars($comp['title']) ?></title>
<link rel="stylesheet" href="../Admin-css/manage-judges-details.css">
<link rel="icon" type="image/png" href="../images/logo.png">
</head>
<body>

<div class="container">

    <div class="header">
        <div class="header-left">
            <span class="page-label">Judges Management</span>
            <h2><?= htmlspecialchars($comp['title']) ?></h2>
        </div>
        <a href="manage-judges.php" class="back-btn">&#8592; Back</a>
    </div>

    <?php if ($msg === 'approved'): ?>
        <div class="flash flash-success">&#10003; Judge approved and email sent successfully.</div>
    <?php elseif ($msg === 'mailfail'): ?>
        <div class="flash flash-warning">&#9888; Judge approved but email failed. Error: <?= htmlspecialchars($err) ?></div>
    <?php elseif ($msg === 'rejected'): ?>
        <div class="flash flash-error">&#10007; Judge has been rejected.</div>
    <?php endif; ?>

    <div class="filter-tabs">
        <a href="?id=<?= $comp_id ?>&status=all"      class="tab <?= $status=='all'      ? 'active':'' ?>">All</a>
        <a href="?id=<?= $comp_id ?>&status=pending"  class="tab <?= $status=='pending'  ? 'active':'' ?>">Pending</a>
        <a href="?id=<?= $comp_id ?>&status=approved" class="tab <?= $status=='approved' ? 'active':'' ?>">Approved</a>
        <a href="?id=<?= $comp_id ?>&status=rejected" class="tab <?= $status=='rejected' ? 'active':'' ?>">Rejected</a>
    </div>

    <div class="card-container">
        <?php
        $count = 0;
        while ($row = $judges->fetch_assoc()):
            $count++;
        ?>
        <div class="card">
            <div class="left">
                <div class="avatar"><?= strtoupper(substr($row['judge_name'], 0, 1)) ?></div>
                <div class="info">
                    <h3><?= htmlspecialchars($row['judge_name']) ?></h3>
                    <p class="email">&#9993; <?= htmlspecialchars($row['judge_email']) ?></p>
                    <span class="status <?= $row['status'] ?>"><?= ucfirst($row['status']) ?></span>

                    <?php if ($row['status'] === 'approved' && !empty($row['temp_password'])): ?>
                        <p style="margin:6px 0 0; font-size:12px; color:#065f46;">
                            🔑 Temp Password: <b><?= htmlspecialchars($row['temp_password']) ?></b>
                        </p>
                    <?php elseif ($row['status'] === 'approved' && empty($row['temp_password'])): ?>
                        <p style="margin:6px 0 0; font-size:12px; color:#2563eb;">
                            ♻️ Using existing account
                        </p>
                    <?php endif; ?>

                    <?php if (!empty($row['reject_reason'])): ?>
                        <p class="reason">&#9432; <?= htmlspecialchars($row['reject_reason']) ?></p>
                    <?php endif; ?>
                </div>
            </div>
            <div class="right">
                <?php if ($row['status'] == 'pending'): ?>
                    <a href="?id=<?= $comp_id ?>&judge_id=<?= $row['id'] ?>&action=approve" class="approve">&#10003; Approve</a>
                    <button class="reject" onclick="openPopup(<?= $row['id'] ?>)">&#10007; Reject</button>
                <?php endif; ?>
            </div>
        </div>
        <?php endwhile; ?>

        <?php if ($count === 0): ?>
            <div class="empty-state"><p>No judges found.</p></div>
        <?php endif; ?>
    </div>

</div>

<div id="overlay" onclick="closePopup()"></div>

<div id="popup">
    <h3>Reject Judge</h3>
    <p class="popup-sub">Please provide a reason for rejection.</p>
    <form method="POST" id="rejectForm">
        <textarea name="reason" required placeholder="Enter reason here..."></textarea>
        <div class="popup-btn">
            <button type="button" onclick="closePopup()">Cancel</button>
            <button type="submit">Confirm Reject</button>
        </div>
    </form>
</div>

<script>
function openPopup(id) {
    document.getElementById("rejectForm").action =
        "?id=<?= $comp_id ?>&judge_id=" + id + "&action=reject";
    document.getElementById("overlay").style.display = "block";
    document.getElementById("popup").style.display   = "block";
}

function closePopup() {
    document.getElementById("overlay").style.display = "none";
    document.getElementById("popup").style.display   = "none";
}
</script>

</body>
</html>