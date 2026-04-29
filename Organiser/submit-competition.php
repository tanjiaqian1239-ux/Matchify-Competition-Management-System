<?php
session_start();
include "../config.php";

$title = $_POST['title'];
$category = $_POST['category'];
$description = $_POST['description'];
$start_date = $_POST['start_date'];
$end_date = $_POST['end_date'];
$participants = $_POST['participants'];

$organizer_id = $_SESSION['user_id'];

/* =========================
   IMAGE UPLOAD (ADDED)
========================= */
$imageName = null;

if (!empty($_FILES['competition_image']['name'])) {

    $folder = "../uploads/";

    if (!is_dir($folder)) {
        mkdir($folder, 0777, true);
    }

    $ext = pathinfo($_FILES["competition_image"]["name"], PATHINFO_EXTENSION);
    $imageName = "comp_" . time() . "." . $ext;

    $target = $folder . $imageName;

    move_uploaded_file(
        $_FILES["competition_image"]["tmp_name"],
        $target
    );
}

/* =========================
   INSERT DATABASE (UPDATED)
========================= */
$stmt = $conn->prepare("
INSERT INTO competition_applications
(title, category, description, start_date, end_date, participants, organizer_id, competition_image)
VALUES (?, ?, ?, ?, ?, ?, ?, ?)
");

$stmt->bind_param(
    "ssssssis",
    $title,
    $category,
    $description,
    $start_date,
    $end_date,
    $participants,
    $organizer_id,
    $imageName
);

$stmt->execute();

header("Location: application-success.php");
exit;
?>