<?php
session_start();
include "../config.php";

$id = intval($_GET['id'] ?? 0);
$status = 'pending';

if ($id) {
    $stmt = $conn->prepare("SELECT status FROM competition_applications WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    if ($row) {
        $status = $row['status'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Application Submitted</title>
<link rel="icon" type="image/png" href="../images/logo.png">
<link rel="stylesheet" href="../css/style.css">
<style>
body {
    margin: 0;
    padding: 0;
    background: #f4f6fb;
    font-family: 'Poppins', sans-serif;
}

.success-box {
    max-width: 600px;
    margin: 80px auto;
    padding: 50px 40px;
    background: white;
    border-radius: 20px;
    text-align: center;
    box-shadow: 0 10px 40px rgba(0,0,0,0.10);
}

.icon { font-size: 70px; margin-bottom: 20px; }

.success-box h1 {
    color: #22c55e;
    margin-bottom: 16px;
    font-size: 26px;
}

.success-box p {
    color: #555;
    font-size: 15px;
    line-height: 1.8;
    margin-bottom: 24px;
}

.badge-submitted {
    display: inline-block;
    margin: 10px 0 20px;
    padding: 10px 24px;
    background: #d1fae5;
    color: #065f46;
    border-radius: 20px;
    font-size: 14px;
    font-weight: 700;
    border: 1px solid #6ee7b7;
}

.badge-pending {
    display: inline-block;
    margin: 0 0 30px;
    padding: 10px 24px;
    background: #fef3c7;
    color: #d97706;
    border-radius: 20px;
    font-size: 14px;
    font-weight: 700;
    border: 1px solid #fcd34d;
}

.badge-approved {
    display: inline-block;
    margin: 0 0 30px;
    padding: 10px 24px;
    background: #d1fae5;
    color: #065f46;
    border-radius: 20px;
    font-size: 14px;
    font-weight: 700;
    border: 1px solid #6ee7b7;
}

.badge-rejected {
    display: inline-block;
    margin: 0 0 30px;
    padding: 10px 24px;
    background: #fee2e2;
    color: #b91c1c;
    border-radius: 20px;
    font-size: 14px;
    font-weight: 700;
    border: 1px solid #fca5a5;
}

.divider {
    border: none;
    border-top: 1px solid #f0f0f0;
    margin: 20px 0;
}

.btn-back {
    display: inline-block;
    padding: 13px 35px;
    background: linear-gradient(135deg, #7163ba, #a855f7);
    color: #fff;
    border-radius: 30px;
    text-decoration: none;
    font-size: 15px;
    font-weight: 600;
    transition: opacity 0.2s;
}

.btn-back:hover { opacity: 0.85; }
</style>
</head>

<body>

<div class="success-box">

    <div class="icon">🎉</div>

    <h1>Application Submitted!</h1>

    <p>Your competition application has been submitted successfully.</p>

    <div class="badge-submitted">✅ Submitted Successfully</div>

    <hr class="divider">

    <?php if ($status === 'pending'): ?>
        <p>📌 Please wait for admin review and approval.<br>
        📅 Estimated processing time: <b>3 - 5 working days</b></p>
        <div class="badge-pending">⏳ Status: Pending Approval</div>

    <?php elseif ($status === 'approved'): ?>
        <p>🎊 Your competition has been approved by admin!</p>
        <div class="badge-approved">✅ Status: Approved</div>

    <?php elseif ($status === 'rejected'): ?>
        <p>❌ Your competition has been rejected by admin.</p>
        <div class="badge-rejected">❌ Status: Rejected</div>
    <?php endif; ?>

    <br>

    <a href="competition-list.php" class="btn-back">Back to Competition List</a>

</div>

</body>
</html>