<?php
session_start();
include "../config.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

if (empty($_POST['title'])) {
    header("Location: apply-competition.php");
    exit;
}

$title             = trim($_POST['title']);
$category          = trim($_POST['category']);
$description       = trim($_POST['description'] ?? '');
$application_start = $_POST['application_start'] ?? null;
$application_end   = $_POST['application_end'] ?? null;
$start_date        = $_POST['start_date'] ?? null;
$end_date          = $_POST['end_date'] ?? null;
$participants      = intval($_POST['participants'] ?? 0);
$prize_1st         = trim($_POST['prize_1st'] ?? '');
$prize_2nd         = trim($_POST['prize_2nd'] ?? '');
$prize_3rd         = trim($_POST['prize_3rd'] ?? '');
$prize_description = trim($_POST['prize_description'] ?? '');

$organizer_id = intval($_SESSION['user_id']);

$user_stmt = $conn->prepare("SELECT fullname, email FROM users WHERE id = ?");
$user_stmt->bind_param("i", $organizer_id);
$user_stmt->execute();
$user_data = $user_stmt->get_result()->fetch_assoc();

$organizer = $user_data['fullname'] ?? '';
$email     = $user_data['email'] ?? '';

$imageName = null;

if (!empty($_FILES['competition_image']['name'])) {
    $folder = "../uploads/";
    if (!is_dir($folder)) {
        mkdir($folder, 0777, true);
    }
    $ext       = pathinfo($_FILES["competition_image"]["name"], PATHINFO_EXTENSION);
    $imageName = "comp_" . time() . "." . $ext;
    $target    = $folder . $imageName;
    $uploaded  = move_uploaded_file($_FILES["competition_image"]["tmp_name"], $target);

    if (!$uploaded) {
        die("❌ Image upload failed. Check uploads/ folder exists and has write permission.");
    }
}

$stmt = $conn->prepare("
    INSERT INTO competition_applications
    (title, category, description, application_start, application_end,
     start_date, end_date, participants, organizer_id,
     organizer, email, competition_image,
     prize_1st, prize_2nd, prize_3rd, prize_description)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");

$stmt->bind_param(
    "ssssssssiissssss",
    $title,              // s
    $category,           // s
    $description,        // s
    $application_start,  // s
    $application_end,    // s
    $start_date,         // s
    $end_date,           // s
    $participants,       // i
    $organizer_id,       // i
    $organizer,          // s
    $email,              // s
    $imageName,          // s
    $prize_1st,          // s
    $prize_2nd,          // s
    $prize_3rd,          // s
    $prize_description   // s
);

if ($stmt->execute()) {
    header("Location: application-success.php");
    exit;
} else {
    echo "❌ Database error: " . $stmt->error;
}
?>