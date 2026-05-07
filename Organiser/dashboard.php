<?php
session_start();
include "../config.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = (int) $_SESSION['user_id'];

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

$total = $conn->query("SELECT COUNT(*) AS total FROM competition_applications WHERE organizer_id = $user_id")->fetch_assoc()['total'];
$approved = $conn->query("SELECT COUNT(*) AS total FROM competition_applications WHERE organizer_id = $user_id AND status = 'approved'")->fetch_assoc()['total'];
$pending = $conn->query("SELECT COUNT(*) AS total FROM competition_applications WHERE organizer_id = $user_id AND status = 'pending'")->fetch_assoc()['total'];
$rejected = $conn->query("SELECT COUNT(*) AS total FROM competition_applications WHERE organizer_id = $user_id AND status = 'rejected'")->fetch_assoc()['total'];
$participants = $conn->query("SELECT COUNT(*) AS total FROM competition_participants cp INNER JOIN competition_applications ca ON cp.competition_id = ca.id WHERE ca.organizer_id = $user_id")->fetch_assoc()['total'];

$list = $conn->query("SELECT * FROM competition_applications WHERE organizer_id = $user_id ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Organiser Dashboard</title>
<link rel="icon" type="image/png" href="../images/logo.png">
<link rel="stylesheet" href="../Organiser-css/dashboardd.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>

<div class="sidebar">
    <div class="logo"></div>
    <ul class="menu">
        <li class="active">
            <a href="dashboard.php">
                <i class="fas fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
        </li>
        <li>
            <a href="profile.php">
                <i class="fas fa-user"></i>
                <span>Profile</span>
            </a>
        </li>
        <li>
            <a href="manage-competition.php">
                <i class="fas fa-trophy"></i>
                <span>Manage Competitions</span>
            </a>
        </li>
        <li>
            <a href="create-competition.php">
                <i class="fas fa-plus-circle"></i>
                <span>Create Competition</span>
            </a>
        </li>
        <li class="logout">
            <a href="../logout.php">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </li>
    </ul>
</div>

<div class="main--content">

    <!-- HEADER -->
    <div class="header--wrapper">
        <div class="header--title">
            <span>Primary</span>
            <h2>Organiser Dashboard</h2>
        </div>
        <div class="user--info">
            <div class="search--box">
                <i class="fa-solid fa-search"></i>
                <input type="text" placeholder="Search">
            </div>
            <img src="<?php echo $profile_image; ?>" alt="Profile">
        </div>
    </div>

    <!-- CARDS -->
    <div class="card--container">
        <div class="card--wrapper">

            <div class="competition--card light-red">
                <div class="card--header">
                    <div class="amount">
                        <span class="title">Total Competition</span>
                        <span class="amount--value"><?php echo $total; ?></span>
                    </div>
                    <i class="fas fa-trophy icon"></i>
                </div>
                <span class="card--detail">All competitions</span>
            </div>

            <div class="competition--card light-purple">
                <div class="card--header">
                    <div class="amount">
                        <span class="title">Approved</span>
                        <span class="amount--value"><?php echo $approved; ?></span>
                    </div>
                    <i class="fa-solid fa-circle-check icon dark-purple"></i>
                </div>
                <span class="card--detail">Active competitions</span>
            </div>

            <div class="competition--card light-blue">
                <div class="card--header">
                    <div class="amount">
                        <span class="title">Pending Review</span>
                        <span class="amount--value"><?php echo $pending; ?></span>
                    </div>
                    <i class="fa-solid fa-clock icon dark-blue"></i>
                </div>
                <span class="card--detail">Awaiting approval</span>
            </div>

            <div class="competition--card light-red">
                <div class="card--header">
                    <div class="amount">
                        <span class="title">Rejected</span>
                        <span class="amount--value"><?php echo $rejected; ?></span>
                    </div>
                    <i class="fa-solid fa-xmark icon dark-red"></i>
                </div>
                <span class="card--detail">Not approved</span>
            </div>

            <div class="competition--card light-purple">
                <div class="card--header">
                    <div class="amount">
                        <span class="title">Total Participants</span>
                        <span class="amount--value"><?php echo $participants; ?></span>
                    </div>
                    <i class="fa-solid fa-user-group icon dark-purple"></i>
                </div>
                <span class="card--detail">Across all competitions</span>
            </div>

        </div>
    </div>

    <!-- TABLE -->
    <div class="card--container">
        <div class="section--header">
            <h3 class="main--title">Competition List</h3>
            <span class="pending-label"><?php echo $pending; ?> Pending</span>
        </div>

        <div class="table--container">
            <table>
                <thead>
                    <tr>
                        <th>Competition</th>
                        <th>Status</th>
                        <th>Category</th>
                        <th>Participants Limit</th>
                        <th>Application Date</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php while($row = $list->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['title']); ?></td>
                        <td>
                            <span class="status <?php echo $row['status']; ?>">
                                <?php echo ucfirst($row['status']); ?>
                            </span>
                        </td>
                        <td><?php echo htmlspecialchars($row['category']); ?></td>
                        <td><?php echo $row['participants']; ?></td>
                        <td><?php echo $row['application_start'] . " ~ " . $row['application_end']; ?></td>
                        <td><?php echo $row['start_date']; ?></td>
                        <td><?php echo $row['end_date']; ?></td>
                        <td>
                            <a href="view-competition-detail.php?id=<?php echo $row['id']; ?>" title="View">
                                <i class="fa fa-eye action-icon"></i>
                            </a>
                            <a href="edit-competition.php?id=<?php echo $row['id']; ?>" title="Edit">
                                <i class="fa fa-edit action-icon"></i>
                            </a>
                            <a href="view-participants.php?id=<?php echo $row['id']; ?>" title="View Participants">
                                <i class="fa fa-users action-icon"></i>
                            </a>
                            <a href="delete-competition.php?id=<?php echo $row['id']; ?>"
                               onclick="return confirm('Delete this competition?')" title="Delete">
                                <i class="fa fa-trash action-icon"></i>
                            </a>
                            <?php if ($row['status'] === 'approved'): ?>
                            <a href="assign-judge.php?id=<?php echo $row['id']; ?>" title="Assign Judge">
                                <i class="fa fa-gavel action-icon" style="color:#7163ba;"></i>
                            </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

</body>
</html>