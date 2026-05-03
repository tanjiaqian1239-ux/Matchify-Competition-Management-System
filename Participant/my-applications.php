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
   CANCEL APPLICATION
========================= */
if (isset($_GET['cancel'])) {

    $cancel_id = (int) $_GET['cancel'];

    $conn->query("
        DELETE FROM competition_participants
        WHERE id='$cancel_id'
        AND user_id='$user_id'
        AND status='pending'
    ");

    header("Location: my-applications.php");
    exit();
}

/* =========================
   GET APPLICATIONS
========================= */
$sql = "
SELECT cp.*, ca.title, ca.category, ca.start_date, ca.end_date
FROM competition_participants cp
LEFT JOIN competition_applications ca
ON cp.competition_id = ca.id
WHERE cp.user_id = '$user_id'
ORDER BY cp.id DESC
";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>My Applications</title>
<link rel="icon" href="../images/logo.png">

<link rel="stylesheet" href="../Participant-css/index-participant.css">

<style>
.content{
    padding:60px 8%;
}

.page-title{
    text-align:center;
    font-size:32px;
    font-weight:700;
    margin-bottom:35px;
}

.grid{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(320px,1fr));
    gap:25px;
}

.card{
    background:#fff;
    border-radius:18px;
    padding:25px;
    box-shadow:0 10px 25px rgba(0,0,0,0.08);
}

.card h3{
    margin-bottom:10px;
    color:#7b2ff7;
}

.card p{
    margin:8px 0;
    color:#555;
    font-size:14px;
}

.status{
    display:inline-block;
    padding:7px 14px;
    border-radius:30px;
    font-size:13px;
    font-weight:600;
    margin-top:10px;
}

.pending{
    background:#fff4d6;
    color:#c98a00;
}

.approved{
    background:#d8ffe5;
    color:#14854c;
}

.rejected{
    background:#ffe0e0;
    color:#d12222;
}

.btn-cancel{
    display:inline-block;
    margin-top:15px;
    padding:10px 14px;
    background:#ff4d4d;
    color:#fff;
    text-decoration:none;
    border-radius:10px;
    font-size:14px;
}

.empty{
    text-align:center;
    font-size:18px;
    color:#888;
    margin-top:50px;
}
</style>

</head>

<body>

<div class="hero">

<!-- NAVBAR -->
<nav class="main-nav">

    <img src="../images/logo.png" class="logo">

    <ul>
        <li><a href="../Participant/index(participant).php">Home</a></li>
        <li><a href="../Participant/competition-list.php">Competition List</a></li>
        <li><a href="../Participant/my-applications.php" class="active">My Applications</a></li>
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

<!-- CONTENT -->
<div class="content">

<div class="page-title">
My Applications
</div>

<?php if ($result->num_rows > 0): ?>

<div class="grid">

<?php while($row = $result->fetch_assoc()): ?>

<div class="card">

<h3><?php echo htmlspecialchars($row['title']); ?></h3>

<p><strong>Category:</strong>
<?php echo htmlspecialchars($row['category']); ?>
</p>

<p><strong>Competition Date:</strong><br>
<?php echo $row['start_date']; ?>
→
<?php echo $row['end_date']; ?>
</p>

<p><strong>Name:</strong>
<?php echo htmlspecialchars($row['full_name']); ?>
</p>

<p><strong>Email:</strong>
<?php echo htmlspecialchars($row['email']); ?>
</p>

<p><strong>Status:</strong></p>

<span class="status <?php echo $row['status']; ?>">
<?php echo strtoupper($row['status']); ?>
</span>

<?php if ($row['status'] == "pending"): ?>

<br>

<a class="btn-cancel"
href="?cancel=<?php echo $row['id']; ?>"
onclick="return confirm('Cancel this application?')">
Cancel Application
</a>

<?php endif; ?>

</div>

<?php endwhile; ?>

</div>

<?php else: ?>

<div class="empty">
No applications yet.
</div>

<?php endif; ?>

</div>
</div>

<script>
function toggleMenu(){
    const menu = document.getElementById("dropdownMenu");
    menu.style.display =
    (menu.style.display === "flex") ? "none" : "flex";
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