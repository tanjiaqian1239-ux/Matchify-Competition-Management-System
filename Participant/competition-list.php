<?php
session_start();
include "../config.php";

$search = "";
if (isset($_POST['search'])) {
    $keyword = $conn->real_escape_string($_POST['keyword']);
    $search = "WHERE title LIKE '%$keyword%'";
}

$sql = "SELECT * FROM competitions $search ORDER BY deadline ASC";
$result = $conn->query($sql);

$profile_image = "../images/profile.avif";

if (isset($_SESSION['user_id'])) {
    $user_id = (int) $_SESSION['user_id'];

    $user_query = $conn->query("SELECT profile_image FROM users WHERE id = $user_id");

    if ($user_query && $user_query->num_rows > 0) {
        $user = $user_query->fetch_assoc();

        if (!empty($user['profile_image']) && $user['profile_image'] != "default.png") {
            $profile_image = "../images/profile/" . $user['profile_image'];
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
<link rel="stylesheet" href="../Participant-css/competitionlist.css">
</head>

<body>

<div class="hero">

<nav class="main-nav">
    <img src="../images/logo.png" class="logo">

    <ul>
        <li><a href="../Participant/index(participant).php">Home</a></li>
        <li><a href="../Participant/competition-list.php" class="active">Competition List</a></li>
        <li><a href="../Participant/about.php">About</a></li>
        <li><a href="../Participant/contact.php">Contact</a></li>
    </ul>

    <div class="nav-right">

    <?php if (!isset($_SESSION['user_id'])): ?>

        <a href="login.php" class="btn">Login</a>

    <?php else: ?>

        <div class="profile-dropdown">
            <img src="<?php echo $profile_image; ?>" class="profile-icon" onclick="toggleMenu()" alt="profile">

            <div class="dropdown-menu" id="dropdownMenu">
                <a href="profile.php">My Profile</a>
                <a href="../login.php">Logout</a>
            </div>
        </div>

    <?php endif; ?>

    </div>
</nav>

<div class="container">

    <div class="top-bar">
        <form method="POST" class="search-box">
            <input type="text" name="keyword" placeholder="🔍 Search competitions...">
            <button type="submit" name="search">Search</button>
        </form>
    </div>

    <h2 class="title">
        <?php echo $result->num_rows; ?> Competitions Available
    </h2>

    <div class="grid">

    <?php if ($result->num_rows > 0): ?>

        <?php while($row = $result->fetch_assoc()): ?>

        <div class="card">
            <img src="../images/competition_banner.jpg" class="card-img">

            <div class="card-body">

                <h3><?php echo htmlspecialchars($row['title']); ?></h3>

                <p class="desc">
                    <?php echo htmlspecialchars(substr($row['description'], 0, 90)); ?>...
                </p>

                <p class="deadline">
                    📅 Deadline: <?php echo $row['deadline']; ?>
                </p>

                <div class="buttons">
                    <a href="competition_detail.php?id=<?php echo $row['id']; ?>" class="btn-view">
                        View Details
                    </a>

                    <a href="login.php" class="btn-join">
                        Join Now
                    </a>
                </div>

            </div>
        </div>

        <?php endwhile; ?>

    <?php else: ?>
        <p>No competitions found</p>
    <?php endif; ?>

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
        if (menu) {
            menu.style.display = "none";
        }
    }
});
</script>

</body>
</html>