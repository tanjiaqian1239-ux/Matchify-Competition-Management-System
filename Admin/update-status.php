<?php
include "../config.php";
requireRole(['admin','superadmin']);
$id = $_GET['id'];
$action = $_GET['action'];

/* GET COMPETITION IMAGE */
$get = $conn->prepare("SELECT competition_image FROM competition_applications WHERE id=?");
$get->bind_param("i", $id);
$get->execute();
$result = $get->get_result();
$row = $result->fetch_assoc();

$image = !empty($row['competition_image'])
    ? "../images/competition/" . $row['competition_image']
    : "../images/no-image.png";

/* APPROVE / REJECT */
if ($action == "approve") {
    $status = "approved";
} elseif ($action == "reject") {
    $status = "rejected";
} else {
    header("Location: manage-competition-list.php");
    exit();
}

$stmt = $conn->prepare("UPDATE competition_applications SET status=? WHERE id=?");
$stmt->bind_param("si", $status, $id);
$stmt->execute();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Competition Updated</title>
<link rel="icon" type="image/png" href="../images/logo.png">
<style>
body{
    font-family:Arial, sans-serif;
    background:#f4f4f4;
    display:flex;
    justify-content:center;
    align-items:center;
    height:100vh;
}
.box{
    background:#fff;
    padding:30px;
    border-radius:15px;
    box-shadow:0 5px 20px rgba(0,0,0,0.08);
    text-align:center;
    width:420px;
}
.box img{
    width:100%;
    height:220px;
    object-fit:cover;
    border-radius:12px;
    margin-bottom:15px;
}
.box h2{
    margin-bottom:10px;
}
.box a{
    display:inline-block;
    margin-top:15px;
    padding:10px 20px;
    background:#6c63ff;
    color:#fff;
    text-decoration:none;
    border-radius:8px;
}
</style>
</head>
<body>

<div class="box">

    <img src="<?= $image ?>">

    <h2>Status Updated</h2>

    <p>This competition has been <b><?= strtoupper($status) ?></b>.</p>

    <a href="manage-competition-list.php">Back</a>

</div>

</body>
</html>