<?php
include "config.php";

$search = "";
if (isset($_POST['search'])) {
    $keyword = $conn->real_escape_string($_POST['keyword']);
    $search = "WHERE title LIKE '%$keyword%'";
}

$sql = "SELECT * FROM competitions $search ORDER BY deadline ASC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Matchify Competition Management Platform</title>
    <link rel="icon" type="image/png" href="images/logo.png">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/competition-list.css">
</head>
<body>

<div class="hero">

    <!-- SAME NAVBAR AS INDEX -->
    <nav>
        <img src="images/logo.png" class="logo">
        <ul>
            <li><a href="index.php">Home</a></li>
            <li><a href="competition-list.php" class="active">Competition List</a></li>
            <li><a href="about.php">About</a></li>
            <li><a href="contact.php">Contact</a></li>
        </ul>
        <a href="login.php" class="btn">Login</a>
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
                <img src="images/competition_banner.jpg" class="card-img">

                <div class="card-body">

                    <div class="card-header">
                        <h3><?php echo $row['title']; ?></h3>
                    </div>

                    <p class="desc">
                        <?php echo substr($row['description'], 0, 90); ?>...
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

</body>
</html>