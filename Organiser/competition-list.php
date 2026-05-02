<?php
session_start();
include "../config.php";

/* =========================
   SEARCH + CATEGORY FILTER
========================= */
$search = "";
$category_filter = "";

$selected_category = "All";

if (isset($_GET['category']) && $_GET['category'] != "") {
    $selected_category = $_GET['category'];

    if ($selected_category != "All") {
        $safe_category = $conn->real_escape_string($selected_category);
        $category_filter = "AND category='$safe_category'";
    }
}

if (isset($_POST['search'])) {
    $keyword = $conn->real_escape_string($_POST['keyword']);
    $search = "AND title LIKE '%$keyword%'";
}

/* =========================
   GET CATEGORY LIST
========================= */
$category_sql = "SELECT DISTINCT category FROM competition_applications 
                 WHERE status='approved' AND category IS NOT NULL AND category != ''
                 ORDER BY category ASC";
$category_result = $conn->query($category_sql);

/* =========================
   GET COMPETITIONS
========================= */
$sql = "SELECT * FROM competition_applications 
        WHERE status='approved' 
        $search 
        $category_filter
        ORDER BY start_date ASC";

$result = $conn->query($sql);

/* =========================
   PROFILE IMAGE
========================= */
$profile_image = "../images/profile.avif";

if (isset($_SESSION['user_id'])) {
    $user_id = (int) $_SESSION['user_id'];

    $user_query = $conn->query("SELECT profile_image FROM users WHERE id = $user_id");

    if ($user_query && $user_query->num_rows > 0) {
        $user = $user_query->fetch_assoc();

        if (!empty($user['profile_image']) && $user['profile_image'] != "default.png") {
            $profile_image = "../login.php" . $user['profile_image'];
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
<link rel="stylesheet" href="../Organiser-css/competitionlist.css">
</head>

<body>

<div class="hero competition-page">

<nav class="main-nav">

    <img src="../images/logo.png" class="logo">

    <ul>
        <li><a href="../Organiser/index(participant).php">Home</a></li>
        <li><a href="../Organiser/competition-list.php" class="active">Competition List</a></li>
        <li><a href="../Organiser/about.php">About</a></li>
        <li><a href="../Organiser/contact.php">Contact</a></li>
    </ul>

    <div class="nav-right">

    <?php if (!isset($_SESSION['user_id'])): ?>

        <a href="../login.php" class="btn">Login</a>

    <?php else: ?>

        <div class="profile-dropdown">
            <img src="<?php echo $profile_image; ?>" class="profile-icon" onclick="toggleMenu()" alt="profile">

            <div class="dropdown-menu" id="dropdownMenu">
                <a href="profile.php">My Profile</a>
                <a href="logout.php">Logout</a>
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

        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="../Organiser/apply-competition.php" class="btn-apply">
                Apply Competition
            </a>
        <?php endif; ?>

    </div>

    <!-- CATEGORY FILTER -->
    <div class="category-bar">

        <a href="competition-list.php?category=All"
           class="cat-btn <?php if($selected_category=="All") echo 'active'; ?>">
           All
        </a>

        <?php while($cat = $category_result->fetch_assoc()): ?>
            <a href="competition-list.php?category=<?php echo urlencode($cat['category']); ?>"
               class="cat-btn <?php if($selected_category==$cat['category']) echo 'active'; ?>">
               <?php echo htmlspecialchars($cat['category']); ?>
            </a>
        <?php endwhile; ?>

    </div>

    <h2 class="title">
        <?php echo $result->num_rows; ?> Competitions Available
    </h2>

    <div class="grid">

    <?php if ($result->num_rows > 0): ?>

        <?php while($row = $result->fetch_assoc()): ?>

        <div class="card">

            <img src="<?= !empty($row['competition_image']) ? '../uploads/'.$row['competition_image'] : '../images/competition_banner.jpg' ?>" class="card-img">

            <div class="card-body">

                <span class="tag">
                    <?php echo htmlspecialchars($row['category']); ?>
                </span>

                <h3><?php echo htmlspecialchars($row['title']); ?></h3>

                <p class="desc">
                    <?php echo htmlspecialchars(substr($row['description'],0,90)); ?>...
                </p>

                <p class="deadline">
                    📅 Start: <?php echo $row['start_date']; ?> → End: <?php echo $row['end_date']; ?>
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
        menu.style.display = "none";
    }
});
</script>

</body>
</html>