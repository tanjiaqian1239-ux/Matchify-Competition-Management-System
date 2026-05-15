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
   ERROR HOLDER
========================= */
$error_msg = "";
$old = [
    'full_name' => $user_data['fullname'],
    'id_type'   => '',
    'id_number' => '',
    'gender'    => '',
    'email'     => $user_data['email'],
    'phone'     => $user_data['phone'],
    'address'   => '',
];

/* =========================
   SUBMIT APPLICATION
========================= */
if (isset($_POST['apply'])) {

    $full_name = $_POST['full_name'];
    $id_type   = $_POST['id_type'];
    $id_number = $_POST['id_number'];
    $gender    = $_POST['gender'];
    $email     = $_POST['email'];
    $phone     = $_POST['phone'];
    $address   = $_POST['address'];

    // Keep old values so form stays filled
    $old = compact('full_name','id_type','id_number','gender','email','phone','address');

    /* =========================
       VALIDATION
    ========================= */
    if ($id_type == "IC") {
        if (!preg_match("/^[0-9]{6}-[0-9]{2}-[0-9]{4}$/", $id_number)) {
            $error_msg = "IC format must be: 000000-00-0000 (e.g. 990101-14-1234)";
        }
    }

    if ($id_type == "Passport") {
        if (!preg_match("/^[A-Z0-9]{6,9}$/", $id_number)) {
            $error_msg = "Passport format invalid. Use 6–9 uppercase letters/numbers (e.g. A12345678)";
        }
    }

    if ($error_msg == "") {

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
                header("Location: ../Participant/my-applications.php");
                exit();
            }

        } else {
            $error_msg = "You have already applied for this competition.";
        }
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
    cursor: pointer;
}

/* ===== ERROR BOX ===== */
.error-box{
    background: #fff0f4;
    border: 1px solid #df4881;
    border-radius: 10px;
    padding: 12px 16px;
    margin-bottom: 15px;
    color: #c0184e;
    font-size: 13px;
    display: flex;
    align-items: center;
    gap: 8px;
}

/* Highlight the id_number field when error */
input.input-error{
    border-color: #df4881 !important;
    box-shadow: 0 0 8px rgba(223,72,129,0.25);
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
                    <a href="my-competition.php">My Competition</a>
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

<!-- ERROR MESSAGE -->
<?php if ($error_msg != ""): ?>
<div class="error-box">
    &#10060; <?= htmlspecialchars($error_msg) ?>
</div>
<?php endif; ?>

<form method="POST">

    <input type="text" name="full_name"
           value="<?= htmlspecialchars($old['full_name']) ?>"
           required placeholder="Full Name">

    <!-- ID TYPE -->
    <select name="id_type" required>
        <option value="">Select ID Type</option>
        <option value="IC"        <?= $old['id_type']=='IC'        ? 'selected':'' ?>>IC (Malaysia)</option>
        <option value="Passport"  <?= $old['id_type']=='Passport'  ? 'selected':'' ?>>Passport</option>
        <option value="Student ID"<?= $old['id_type']=='Student ID' ? 'selected':'' ?>>Student ID</option>
    </select>

    <div class="hint">
        IC format: 000000-00-0000<br>
        Passport format: 6–9 letters/numbers (uppercase)
    </div>

    <input type="text" name="id_number"
           value="<?= htmlspecialchars($old['id_number']) ?>"
           required placeholder="Enter ID Number"
           class="<?= $error_msg != '' ? 'input-error' : '' ?>">

    <select name="gender" required>
        <option value="">Gender</option>
        <option <?= $old['gender']=='Male'   ? 'selected':'' ?>>Male</option>
        <option <?= $old['gender']=='Female' ? 'selected':'' ?>>Female</option>
    </select>

    <input type="email" name="email"
           value="<?= htmlspecialchars($old['email']) ?>"
           required placeholder="Email">

    <input type="text" name="phone"
           value="<?= htmlspecialchars($old['phone']) ?>"
           required placeholder="Phone">

    <textarea name="address" required placeholder="Address"><?= htmlspecialchars($old['address']) ?></textarea>

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