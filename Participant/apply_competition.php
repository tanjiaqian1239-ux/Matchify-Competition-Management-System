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
$competition_id = $_GET['id'] ?? 0;

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
   USER DATA (AUTO FILL)
========================= */
$user_data = $conn->query("SELECT * FROM users WHERE id=$user_id")->fetch_assoc();

/* =========================
   SUBMIT APPLICATION
========================= */
if (isset($_POST['apply'])) {

    $full_name = $_POST['full_name'];
    $id_type = $_POST['id_type'];
    $id_number = $_POST['id_number'];
    $gender = $_POST['gender'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];

    /* =========================
       BASIC VALIDATION
    ========================= */
    if ($id_type == "IC") {
        if (!preg_match("/^[0-9]{6}-[0-9]{2}-[0-9]{4}$/", $id_number)) {
            echo "<script>alert('IC format must be 000000-00-0000');</script>";
            exit();
        }
    }

    if ($id_type == "Passport") {
        if (!preg_match("/^[A-Z0-9]{6,9}$/", $id_number)) {
            echo "<script>alert('Passport format invalid (6-9 alphanumeric characters)');</script>";
            exit();
        }
    }

    /* =========================
       CHECK DUPLICATE
    ========================= */
    $check = $conn->query("
        SELECT * FROM competition_participants 
        WHERE user_id=$user_id AND competition_id=$competition_id
    ");

    if ($check->num_rows == 0) {

        $sql = "INSERT INTO competition_participants
        (competition_id, user_id, full_name, ic_number, gender, email, phone, address, status)
        VALUES
        ('$competition_id', '$user_id', '$full_name', '$id_type: $id_number', '$gender', '$email', '$phone', '$address', 'pending')";

        if ($conn->query($sql)) {
            header("Location: my-applications.php");
            exit();
        }

    } else {
        echo "<script>alert('You already applied this competition');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<link rel="icon" href="../images/logo.png">
<title>Apply Competition</title>

<link rel="stylesheet" href="../Participant-css/index-participant.css">

<style>
.content{
    padding: 60px 10%;
    display: flex;
    justify-content: center;
}

.form-box{
    background: #fff;
    width: 100%;
    max-width: 600px;
    padding: 30px;
    border-radius: 18px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.08);
}

.form-box h2{
    margin-bottom: 20px;
}

.form-box input,
.form-box select,
.form-box textarea{
    width: 100%;
    padding: 12px;
    margin-bottom: 10px;
    border-radius: 10px;
    border: 1px solid #ddd;
}

.hint{
    font-size: 12px;
    color: #888;
    margin-bottom: 10px;
}

button{
    width: 100%;
    padding: 12px;
    border: none;
    border-radius: 10px;
    background: linear-gradient(135deg,#c430d7,#7b2ff7);
    color: #fff;
    font-weight: 600;
}
</style>

</head>

<body>

<div class="hero">

<!-- NAV (UNCHANGED) -->
<nav class="main-nav">

    <img src="../images/logo.png" class="logo">

    <ul>
        <li><a href="../Participant/index(participant).php">Home</a></li>
        <li><a href="../Participant/competition-list.php">Competition List</a></li>
        <li><a href="../Participant/about.php">About</a></li>
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
                    <a href="../login.php">Logout</a>
                </div>
            </div>
        <?php endif; ?>

    </div>

</nav>

<!-- CONTENT -->
<div class="content">

<div class="form-box">

<h2>Apply Competition</h2>

<form method="POST">

    <input type="text" name="full_name"
           value="<?= htmlspecialchars($user_data['fullname']) ?>"
           required placeholder="Full Name">

    <!-- ID TYPE -->
    <select name="id_type" required>
        <option value="">Select ID Type</option>
        <option value="IC">IC (Malaysia)</option>
        <option value="Passport">Passport</option>
        <option value="Student ID">Student ID</option>
    </select>

    <div class="hint">
        IC format: 000000-00-0000<br>
        Passport format: 6–9 letters/numbers
    </div>

    <input type="text" name="id_number" required placeholder="Enter ID Number">

    <select name="gender" required>
        <option value="">Gender</option>
        <option>Male</option>
        <option>Female</option>
    </select>

    <input type="email" name="email"
           value="<?= htmlspecialchars($user_data['email']) ?>"
           required placeholder="Email">

    <input type="text" name="phone"
           value="<?= htmlspecialchars($user_data['phone']) ?>"
           required placeholder="Phone">

    <textarea name="address" required placeholder="Address"></textarea>

    <button type="submit" name="apply">Submit Application</button>

</form>

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