<?php
session_start();
include "../config.php";

/* PROFILE IMAGE */
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
<title>Apply Competition</title>
<link rel="icon" type="image/png" href="../images/logo.png">
<link rel="stylesheet" href="../css/style.css">
<link rel="stylesheet" href="../Organiser-css/apply-competition.css">
</head>

<body>

<div class="hero">

<!-- NAV -->
<nav class="main-nav">

    <img src="../images/logo.png" class="logo">

    <ul>
        <li><a href="index.php">Home</a></li>
        <li><a href="competition-list.php">Competition List</a></li>
        <li><a href="about.php">About</a></li>
        <li><a href="contact.php">Contact</a></li>
    </ul>

    <div class="nav-right">

        <?php if (!isset($_SESSION['user_id'])): ?>
            <a href="../login.php" class="btn">Login</a>
        <?php else: ?>
            <div class="profile-dropdown">
                <img src="<?php echo $profile_image; ?>" class="profile-icon" onclick="toggleMenu()">

                <div class="dropdown-menu" id="dropdownMenu">
                    <a href="profile.php">My Profile</a>
                    <a href="../login.php">Logout</a>
                </div>
            </div>
        <?php endif; ?>

    </div>

</nav>

<!-- FORM -->
<div class="form-wrapper">

<h1>Apply Online Competition</h1>

<form action="submit-competition.php" method="POST">

    <input type="text" name="title" placeholder="Competition Name" required>

   <select name="category" required>

        <option value="">-- Select Category --</option>

        <option value="esports">🎮 E-Sports</option>

        <option value="tech">💻 Technology / Programming</option>

        <option value="academic">🧠 Academic</option>

        <option value="creative">🎨 Creative & Design</option>

        <option value="business">📊 Business & Innovation</option>

        <option value="sports">🏃 Sports & Physical</option>

        <option value="entertainment">🎤 Entertainment</option>

        <option value="others">🌍 General / Others</option>

    </select>

    <textarea name="description" placeholder="Description"></textarea>

    <input type="date" name="start_date">
    <input type="date" name="end_date">

    <input type="number" name="participants" placeholder="Expected Participants">

    <input type="hidden" name="status" value="pending">

    <button type="submit">Submit Application</button>

</form>

</div>

</div>

<script>
function toggleMenu(){
    const menu = document.getElementById("dropdownMenu");
    menu.style.display = (menu.style.display === "flex") ? "none" : "flex";
}
</script>

</body>
</html>