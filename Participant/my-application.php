<?php
session_start();
include "../config.php";

/* =========================
   LOGIN CHECK
========================= */
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = (int) $_SESSION['user_id'];

/* =========================
   PROFILE IMAGE
========================= */
$profile_image = "../images/profile.avif";

$user_query = $conn->query("SELECT profile_image FROM users WHERE id = $user_id");

if ($user_query && $user_query->num_rows > 0) {
    $user = $user_query->fetch_assoc();

    if (!empty($user['profile_image'])) {
        $path = "/images/profile/" . $user['profile_image'];

        if (file_exists($_SERVER['DOCUMENT_ROOT'] . $path)) {
            $profile_image = $path;
        }
    }
}

/* =========================
   GET APPLICATIONS
========================= */
$sql = "
SELECT cp.*, ca.title, ca.category, ca.start_date, ca.end_date, ca.competition_image
FROM competition_participants cp
JOIN competition_applications ca
ON cp.competition_id = ca.id
WHERE cp.user_id = $user_id
ORDER BY cp.created_at DESC
";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<link rel="icon" href="../images/logo.png">
<title>My Applications</title>

<link rel="stylesheet" href="../Participant-css/index-participant.css">

<style>
/* ================= CONTENT ================= */
.content{
    padding: 60px 10%;
}

.title{
    font-size: 26px;
    font-weight: 700;
    margin-bottom: 25px;
}

/* ================= CARD ================= */
.app-grid{
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
    gap: 20px;
}

.app-card{
    background: #fff;
    border-radius: 15px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.08);
    overflow: hidden;
    transition: 0.3s;
}

.app-card:hover{
    transform: translateY(-5px);
}

.app-img{
    width: 100%;
    height: 160px;
    object-fit: cover;
}

.app-body{
    padding: 18px;
}

.tag{
    display: inline-block;
    background: #f3dcff;
    color: #8e00c7;
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 12px;
    margin-bottom: 8px;
}

.status{
    margin-top: 10px;
    font-weight: 600;
}

/* STATUS COLORS */
.pending{ color: #f0a500; }
.approved{ color: #28a745; }
.rejected{ color: #dc3545; }

.btn{
    display: inline-block;
    margin-top: 12px;
    padding: 10px 14px;
    border-radius: 8px;
    text-decoration: none;
    background: #007bff;
    color: #fff;
    font-size: 13px;
}

.btn:hover{
    background: #0069d9;
}

/* EMPTY STATE */
.empty{
    text-align: center;
    color: #777;
    margin-top: 50px;
}
</style>

</head>

<body>

<div class="hero">

<!-- ================= NAVBAR (UNCHANGED) ================= -->
<nav class="main-nav">

    <img src="../images/logo.png" class="logo">

    <ul>
        <li><a href="../Participant/index(participant).php">Home</a></li>
        <li><a href="../Participant/competition-list.php">Competition List</a></li>
        <li><a href="../Participant/about.php">About</a></li>
        <li><a href="#">Contact</a></li>
    </ul>

    <div class="nav-right">

        <div class="profile-dropdown">

            <img src="<?php echo $profile_image; ?>" class="profile-icon" onclick="toggleMenu()">

            <div class="dropdown-menu" id="dropdownMenu">
                <a href="profile.php">My Profile</a>
                <a href="../login.php">Logout</a>
            </div>

        </div>

    </div>

</nav>

<!-- ================= CONTENT ================= -->
<div class="content">

<h2 class="title">My Applications</h2>

<?php if ($result->num_rows > 0): ?>

<div class="app-grid">

<?php while($row = $result->fetch_assoc()): ?>

<div class="app-card">

    <img class="app-img"
         src="<?= !empty($row['competition_image']) ? '../uploads/'.$row['competition_image'] : '../images/competition_banner.jpg' ?>">

    <div class="app-body">

        <span class="tag"><?= htmlspecialchars($row['category']) ?></span>

        <h3><?= htmlspecialchars($row['title']) ?></h3>

        <p>📅 <?= $row['start_date'] ?> → <?= $row['end_date'] ?></p>

        <p class="status <?= $row['status'] ?>">
            Status: <?= ucfirst($row['status']) ?>
        </p>

        <a href="view-application.php?id=<?= $row['id'] ?>" class="btn">
            View Details
        </a>

    </div>

</div>

<?php endwhile; ?>

</div>

<?php else: ?>

<div class="empty">
    <h3>No Applications Yet</h3>
    <p>You haven’t joined any competition yet.</p>
</div>

<?php endif; ?>

</div>

</div>

<!-- ================= SCRIPT ================= -->
<script>
function toggleMenu(){
    const menu = document.getElementById("dropdownMenu");
    menu.style.display = (menu.style.display === "flex") ? "none" : "flex";
}

document.addEventListener("click", function(e){
    const dropdown = document.querySelector(".profile-dropdown");
    const menu = document.getElementById("dropdownMenu");

    if (dropdown && !dropdown.contains(e.target)) {
        if (menu) menu.style.display = "none";
    }
});
</script>

</body>
</html>