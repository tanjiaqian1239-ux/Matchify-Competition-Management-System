<?php
session_start();
include "../config.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = (int)$_SESSION['user_id'];
$competition_id = intval($_GET['id']);

/* =========================
   CHECK JOINED
========================= */
$check = $conn->prepare("
    SELECT id 
    FROM competition_participants 
    WHERE competition_id=? AND user_id=?
");
$check->bind_param("ii", $competition_id, $user_id);
$check->execute();
$joined = $check->get_result()->fetch_assoc();

if (!$joined) {
    echo "<script>
        alert('You have not joined this competition');
        window.location.href='my-competition.php';
    </script>";
    exit();
}

/* =========================
   CHECK ALREADY SUBMITTED
========================= */
$check2 = $conn->prepare("
    SELECT id 
    FROM competition_submissions 
    WHERE competition_id=? AND user_id=?
");
$check2->bind_param("ii", $competition_id, $user_id);
$check2->execute();
$submitted = $check2->get_result()->fetch_assoc();

/* =========================
   SUBMIT HANDLER
========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$submitted) {

    $title = $_POST['title'];
    $desc  = $_POST['description'];

    $file_name = $_FILES['file']['name'];
    $tmp_name  = $_FILES['file']['tmp_name'];

    $ext = pathinfo($file_name, PATHINFO_EXTENSION);
    $new_name = "sub_" . time() . "." . $ext;

    $upload_dir = "../uploads/submissions/";

    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    move_uploaded_file($tmp_name, $upload_dir . $new_name);

    $stmt = $conn->prepare("
        INSERT INTO competition_submissions 
        (competition_id, user_id, title, description, file_path)
        VALUES (?, ?, ?, ?, ?)
    ");

    $stmt->bind_param("iisss", $competition_id, $user_id, $title, $desc, $new_name);
    $stmt->execute();

    echo "<script>
        alert('Submitted successfully!');
        window.location.href='my-competition.php';
    </script>";
    exit();
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
    padding:25px;
    border-radius:16px;
    box-shadow:0 10px 30px rgba(0,0,0,0.1);
}

h2{
    margin-bottom:10px;
}

input, textarea{
    width:100%;
    padding:10px;
    margin-bottom:12px;
    border:1px solid #ddd;
    border-radius:8px;
}

button{
    width:100%;
    padding:12px;
    background:#6c63ff;
    color:#fff;
    border:none;
    border-radius:8px;
    font-weight:600;
    cursor:pointer;
}

button:hover{
    opacity:0.85;
}

.back-btn{
    display:inline-block;
    margin-bottom:15px;
    padding:8px 14px;
    background:#f3f4f6;
    color:#333;
    text-decoration:none;
    border-radius:8px;
    font-size:13px;
}

.back-btn:hover{
    background:#e5e7eb;
}

.notice{
    color:green;
    font-weight:600;
}
</style>
</head>

<body>

<div class="container">

<h2>📤 Submit Work</h2>

<!-- GO BACK -->
<a href="my-competition.php" class="back-btn">← Go Back</a>

<?php if ($submitted): ?>

    <p class="notice">You already submitted this competition.</p>

<?php else: ?>

<form method="POST" enctype="multipart/form-data">

    <input type="text" name="title" placeholder="Submission Title" required>

    <textarea name="description" placeholder="Description" required></textarea>

    <input type="file" name="file" required>

    <button type="submit">Submit Work</button>

</form>

<?php endif; ?>

</div>

</body>
</html>