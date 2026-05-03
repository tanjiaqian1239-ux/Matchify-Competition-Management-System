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
   CHECK ID
========================= */
if (!isset($_GET['id'])) {
    header("Location: dashboard.php");
    exit();
}

$id = (int) $_GET['id'];

/* =========================
   GET COMPETITION
========================= */
$query = $conn->query("
    SELECT *
    FROM competition_applications
    WHERE id = $id
    AND organizer_id = $user_id
");

if ($query->num_rows == 0) {
    header("Location: dashboard.php");
    exit();
}

$row = $query->fetch_assoc();

/* =========================
   IMAGE
========================= */
$competition_image = "../images/default-competition.png";

if (!empty($row['competition_image']) && $row['competition_image'] != "0") {
    $competition_image = "../images/competition/" . $row['competition_image'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Competition Details</title>
<link rel="icon" type="image/png" href="../images/logo.png">
<link rel="icon" href="../images/logo.png">
<link rel="stylesheet" href="../Organiser-css/view-competition-detail.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

</head>
<body>

<div class="main-container">

    <!-- TOP -->
    <div class="top-bar">
        <h2>Competition Details</h2>

        <a href="dashboard.php" class="back-btn">
            <i class="fa fa-arrow-left"></i> Back
        </a>
    </div>

    <!-- CARD -->
    <div class="detail-card">

        <!-- LEFT -->
        <div class="left-side">
            <img src="<?php echo $competition_image; ?>">
        </div>

        <!-- RIGHT -->
        <div class="right-side">

            <h1><?php echo htmlspecialchars($row['title']); ?></h1>

            <span class="status <?php echo $row['status']; ?>">
                <?php echo ucfirst($row['status']); ?>
            </span>

            <div class="info-grid">

                <div class="info-box">
                    <label>Category</label>
                    <p><?php echo $row['category']; ?></p>
                </div>

                <div class="info-box">
                    <label>Participants</label>
                    <p><?php echo $row['participants']; ?></p>
                </div>

                <div class="info-box">
                    <label>Application Start</label>
                    <p><?php echo $row['application_start']; ?></p>
                </div>

                <div class="info-box">
                    <label>Application End</label>
                    <p><?php echo $row['application_end']; ?></p>
                </div>

                <div class="info-box">
                    <label>Competition Start</label>
                    <p><?php echo $row['start_date']; ?></p>
                </div>

                <div class="info-box">
                    <label>Competition End</label>
                    <p><?php echo $row['end_date']; ?></p>
                </div>

                <div class="info-box">
                    <label>Organizer</label>
                    <p><?php echo $row['organizer']; ?></p>
                </div>

                <div class="info-box">
                    <label>Email</label>
                    <p><?php echo $row['email']; ?></p>
                </div>

            </div>

        </div>

    </div>

    <!-- PRIZE -->
    <div class="section-card">

        <h3>Prize Details</h3>

        <div class="prize-grid">

            <div class="prize-box gold">
                🥇 1st Prize <br>
                <strong><?php echo $row['prize_1st']; ?></strong>
            </div>

            <div class="prize-box silver">
                🥈 2nd Prize <br>
                <strong><?php echo $row['prize_2nd']; ?></strong>
            </div>

            <div class="prize-box bronze">
                🥉 3rd Prize <br>
                <strong><?php echo $row['prize_3rd']; ?></strong>
            </div>

        </div>

        <p class="prize-desc">
            <?php echo $row['prize_description']; ?>
        </p>

    </div>

    <!-- DESCRIPTION -->
    <div class="section-card">

        <h3>Description</h3>

        <p class="description">
            <?php echo nl2br(htmlspecialchars($row['description'])); ?>
        </p>

    </div>

    <!-- BUTTONS -->
    <div class="bottom-btns">

        <a href="edit-competition.php?id=<?php echo $row['id']; ?>" class="edit-btn">
            <i class="fa fa-edit"></i> Edit
        </a>

        <a href="manage-competition.php?delete=<?php echo $row['id']; ?>"
           onclick="return confirm('Delete this competition?')"
           class="delete-btn">
           <i class="fa fa-trash"></i> Delete
        </a>

    </div>

</div>

</body>
</html>