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

$stmt = $conn->prepare("
INSERT INTO competition_applications
(title, category, description, start_date, end_date, participants, organizer_id)
VALUES (?, ?, ?, ?, ?, ?, ?)
");

$stmt->bind_param(
    "ssssssi",
    $title,
    $category,
    $description,
    $start_date,
    $end_date,
    $participants,
    $organizer_id
);

$stmt->execute();

header("Location: application-success.php");
exit;  
?>