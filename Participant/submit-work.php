<?php
session_start();
include "../config.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id        = (int)$_SESSION['user_id'];
$competition_id = intval($_GET['id']);

/* =========================
   GET COMPETITION INFO
========================= */
$comp_stmt = $conn->prepare("SELECT title, submission_type FROM competition_applications WHERE id=?");
$comp_stmt->bind_param("i", $competition_id);
$comp_stmt->execute();
$comp = $comp_stmt->get_result()->fetch_assoc();

if (!$comp) {
    echo "<script>alert('Competition not found.'); window.location.href='my-competition.php';</script>";
    exit();
}

$submission_type = $comp['submission_type']; // 'file', 'link', 'both'

/* =========================
   CHECK JOINED
========================= */
$check = $conn->prepare("SELECT id FROM competition_participants WHERE competition_id=? AND user_id=?");
$check->bind_param("ii", $competition_id, $user_id);
$check->execute();
$joined = $check->get_result()->fetch_assoc();

if (!$joined) {
    echo "<script>alert('You have not joined this competition'); window.location.href='my-competition.php';</script>";
    exit();
}

/* =========================
   CHECK ALREADY SUBMITTED
========================= */
$check2 = $conn->prepare("SELECT id FROM competition_submissions WHERE competition_id=? AND user_id=?");
$check2->bind_param("ii", $competition_id, $user_id);
$check2->execute();
$submitted = $check2->get_result()->fetch_assoc();

/* =========================
   SUBMIT HANDLER
========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$submitted) {

    $title       = trim($_POST['title']);
    $desc        = trim($_POST['description']);
    $link        = trim($_POST['link'] ?? '');
    $new_name    = null;

    /* ---- FILE UPLOAD ---- */
    if (($submission_type === 'file' || $submission_type === 'both') && !empty($_FILES['file']['name'])) {

        $file_name  = $_FILES['file']['name'];
        $tmp_name   = $_FILES['file']['tmp_name'];
        $ext        = pathinfo($file_name, PATHINFO_EXTENSION);
        $new_name   = "sub_" . time() . "." . $ext;
        $upload_dir = "../uploads/submissions/";

        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        move_uploaded_file($tmp_name, $upload_dir . $new_name);
    }

    /* ---- VALIDATE ---- */
    $error = '';

    if ($submission_type === 'file' && empty($new_name)) {
        $error = 'Please upload a file.';
    } elseif ($submission_type === 'link' && empty($link)) {
        $error = 'Please enter a link.';
    } elseif ($submission_type === 'both' && empty($new_name) && empty($link)) {
        $error = 'Please upload a file or enter a link.';
    }

    if ($error) {
        $show_error = $error;
    } else {

        /* ---- INSERT ---- */
        $stmt = $conn->prepare("
            INSERT INTO competition_submissions 
            (competition_id, user_id, title, description, file_path, link)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("iissss", $competition_id, $user_id, $title, $desc, $new_name, $link);
        $stmt->execute();

        echo "<script>alert('Submitted successfully!'); window.location.href='my-competition.php';</script>";
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Submit Work</title>
<link rel="icon" type="image/png" href="../images/logo.png">
<style>
body{
    margin:0;
    font-family:Poppins,sans-serif;
    background:#f4f6fb;
}

.container{
    max-width:600px;
    margin:40px auto;
    background:#fff;
    padding:30px;
    border-radius:16px;
    box-shadow:0 10px 30px rgba(0,0,0,0.1);
}

h2{
    margin-bottom:6px;
}

.comp-title{
    font-size:13px;
    color:#888;
    margin-bottom:20px;
}

label{
    display:block;
    font-size:13px;
    font-weight:600;
    color:#374151;
    margin-bottom:5px;
}

input[type=text],
input[type=url],
textarea{
    width:100%;
    padding:10px 12px;
    margin-bottom:16px;
    border:1px solid #ddd;
    border-radius:8px;
    font-size:14px;
    font-family:Poppins,sans-serif;
    box-sizing:border-box;
    outline:none;
    transition:0.2s;
}

input:focus, textarea:focus{
    border-color:#6c63ff;
    box-shadow:0 0 0 3px rgba(108,99,255,0.12);
}

input[type=file]{
    width:100%;
    padding:10px;
    margin-bottom:16px;
    border:1px dashed #bbb;
    border-radius:8px;
    background:#fafafa;
    font-size:13px;
    box-sizing:border-box;
}

.divider{
    text-align:center;
    color:#aaa;
    font-size:13px;
    margin:4px 0 16px;
    position:relative;
}

.divider::before,
.divider::after{
    content:'';
    display:inline-block;
    width:40%;
    height:1px;
    background:#eee;
    vertical-align:middle;
    margin:0 8px;
}

button{
    width:100%;
    padding:12px;
    background:linear-gradient(135deg,#6c63ff,#a855f7);
    color:#fff;
    border:none;
    border-radius:10px;
    font-weight:600;
    font-size:15px;
    cursor:pointer;
    transition:0.2s;
    font-family:Poppins,sans-serif;
}

button:hover{
    opacity:0.9;
    transform:translateY(-1px);
}

.back-btn{
    display:inline-block;
    margin-bottom:18px;
    padding:8px 14px;
    background:#f3f4f6;
    color:#333;
    text-decoration:none;
    border-radius:8px;
    font-size:13px;
}

.back-btn:hover{ background:#e5e7eb; }

.notice{
    background:#dcfce7;
    color:#15803d;
    padding:12px 16px;
    border-radius:10px;
    font-weight:600;
    font-size:14px;
}

.error-box{
    background:#fee2e2;
    color:#b91c1c;
    padding:12px 16px;
    border-radius:10px;
    font-size:13px;
    margin-bottom:16px;
}

.type-badge{
    display:inline-block;
    background:#ede9fe;
    color:#6c63ff;
    font-size:12px;
    font-weight:600;
    padding:4px 12px;
    border-radius:20px;
    margin-bottom:20px;
}
</style>
</head>

<body>
<div class="container">

    <h2>📤 Submit Work</h2>
    <p class="comp-title"><?= htmlspecialchars($comp['title']) ?></p>

    <a href="my-competition.php" class="back-btn">← Go Back</a>

    <?php if ($submitted): ?>

        <div class="notice">✅ You already submitted this competition.</div>

    <?php else: ?>

        <!-- SUBMISSION TYPE BADGE -->
        <?php
        $badge = match($submission_type) {
            'file' => '📁 File Upload',
            'link' => '🔗 Link Submission',
            'both' => '📁 File Upload + 🔗 Link',
            default => $submission_type
        };
        ?>
        <div class="type-badge"><?= $badge ?></div>

        <!-- ERROR -->
        <?php if (!empty($show_error)): ?>
            <div class="error-box">❌ <?= htmlspecialchars($show_error) ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">

            <label>Submission Title</label>
            <input type="text" name="title" placeholder="Enter your submission title" required>

            <label>Description</label>
            <textarea name="description" rows="4" placeholder="Describe your submission" required></textarea>

            <!-- FILE -->
            <?php if ($submission_type === 'file' || $submission_type === 'both'): ?>
                <label>Upload File <?= $submission_type === 'both' ? '<span style="color:#aaa;font-weight:400;">(optional if link provided)</span>' : '' ?></label>
                <input type="file" name="file" <?= $submission_type === 'file' ? 'required' : '' ?>>
            <?php endif; ?>

            <!-- DIVIDER FOR BOTH -->
            <?php if ($submission_type === 'both'): ?>
                <div class="divider">or</div>
            <?php endif; ?>

            <!-- LINK -->
            <?php if ($submission_type === 'link' || $submission_type === 'both'): ?>
                <label>Submission Link <?= $submission_type === 'both' ? '<span style="color:#aaa;font-weight:400;">(optional if file uploaded)</span>' : '' ?></label>
                <input type="url" name="link" placeholder="https://drive.google.com/... or https://github.com/..." <?= $submission_type === 'link' ? 'required' : '' ?>>
            <?php endif; ?>

            <button type="submit">Submit Work 🚀</button>

        </form>

    <?php endif; ?>

</div>
</body>
</html>