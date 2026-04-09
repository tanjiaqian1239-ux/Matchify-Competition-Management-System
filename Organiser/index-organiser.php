<?php
session_start();
include "../config.php";

$profile_image = "../images/profile.avif";

if (isset($_SESSION['user_id'])) {
    $user_id = (int) $_SESSION['user_id'];

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
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="icon" type="image/png" href="../images/logo.png">
<title>Matchify Competition Management System</title>
<link rel="stylesheet" href="../Organiser-css/index-organiser.css">
</head>

<body>

<div class="hero">

<nav class="main-nav">
    <img src="../images/logo.png" class="logo">

    <ul>
        <li><a href="../Organiser/index(participant).php">Home</a></li>
        <li><a href="../Organiser/competition-list.php">Competition List</a></li>
        <li><a href="#">About</a></li>
        <li><a href="#">Contact</a></li>
    </ul>

    <div class="nav-right">

        <?php if (!isset($_SESSION['user_id'])): ?>
            <a href="../login.php" class="btn">Login</a>
        <?php else: ?>
            <div class="profile-dropdown">
                <img src="<?php echo $profile_image; ?>" class="profile-icon" onclick="toggleMenu()">
                <div class="dropdown-menu" id="dropdownMenu">
                    <a href="profile.php">My Profile</a>
                    <a href="../logout.php">Logout</a>
                </div>
            </div>
        <?php endif; ?>

    </div>
</nav>

<div class="content">
    <div class="text-box">
        <h1>Matchify Competition Platform</h1>
        <p>Join & Compete with Ease</p>
        <div class="buttons">
            <a href="../Participant/competition-list.php" class="btn dark">View Competitions →</a>
            <a href="#" class="btn light">Get Started</a>
        </div>
    </div>
</div>

</div>

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