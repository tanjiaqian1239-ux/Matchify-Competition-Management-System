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
<title>Matchify Competition Management Platform</title>
<link rel="icon" type="image/png" href="../images/logo.png">
<link rel="stylesheet" href="../css/style.css">
<link rel="stylesheet" href="../Participant-css/about.css">
</head>

<body>

<div class="hero">

<nav class="main-nav">

    <img src="../images/logo.png" class="logo">

    <ul>
        <li><a href="index(participant).php">Home</a></li>
        <li><a href="competition-list.php">Competition List</a></li>
        <li><a href="about.php" class="active">About</a></li>
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

<div class="about-container">

    <h1>About Matchify</h1>

    <div class="section">
        <h2>📌 Project Overview</h2>
        <p>
            Matchify is a web-based Competition Management System designed to help users browse,
            join, and manage competitions easily. It provides a centralized platform for participants
            and administrators to interact efficiently.
        </p>
    </div>

    <div class="section">
        <h2>🎯 Mission</h2>
        <p>
            Our mission is to simplify competition management by providing a user-friendly platform
            where users can discover competitions, register, and track their participation seamlessly.
        </p>
    </div>

    <div class="section">
        <h2>⚙️ Key Features</h2>
        <ul>
            <li>User Registration & Login</li>
            <li>Competition Listing with Search</li>
            <li>Join Competitions</li>
            <li>Competition Details Page</li>
            <li>Admin Management</li>
        </ul>
    </div>

    <div class="section">
        <h2>👨‍💻 Developer</h2>
        <p>
            This system is developed as part of a Final Year Project (FYP).
        </p>
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