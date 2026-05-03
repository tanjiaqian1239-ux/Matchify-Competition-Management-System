<?php
session_start();
include "../config.php";

date_default_timezone_set('Asia/Kuala_Lumpur');

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = (int) $_SESSION['user_id'];

if (!isset($_GET['id'])) {
    header("Location: dashboard.php");
    exit();
}

$id = (int) $_GET['id'];

$stmt = $conn->prepare("
    SELECT * FROM competition_applications
    WHERE id = ? AND organizer_id = ?
");
$stmt->bind_param("ii", $id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo "Competition not found";
    exit();
}

$data = $result->fetch_assoc();

$today = date('Y-m-d');

$application_end = $data['application_end'];
$start_date = $data['start_date'];

$lock_date = date('Y-m-d', strtotime($start_date . ' -1 day'));

$can_edit =
    ($data['status'] === 'pending') &&
    ($today <= $application_end) &&
    ($today < $lock_date);

if ($_SERVER["REQUEST_METHOD"] == "POST" && $can_edit) {

    if ($_POST['application_end'] < $_POST['application_start']) {
        die("Invalid application date range");
    }

    if ($_POST['start_date'] < $_POST['application_end']) {
        die("Competition must start after application ends");
    }

    if ($_POST['end_date'] < $_POST['start_date']) {
        die("Invalid competition end date");
    }

    $stmt = $conn->prepare("
        UPDATE competition_applications
        SET title=?, category=?, description=?, participants=?,
            application_start=?, application_end=?,
            start_date=?, end_date=?,
            prize_1st=?, prize_2nd=?, prize_3rd=?, prize_description=?
        WHERE id=? AND organizer_id=?
    ");

    $stmt->bind_param(
        "sssissssssssii",
        $_POST['title'],
        $_POST['category'],
        $_POST['description'],
        $_POST['participants'],
        $_POST['application_start'],
        $_POST['application_end'],
        $_POST['start_date'],
        $_POST['end_date'],
        $_POST['prize_1st'],
        $_POST['prize_2nd'],
        $_POST['prize_3rd'],
        $_POST['prize_description'],
        $id,
        $user_id
    );

    $stmt->execute();

    header("Location: manage-competition.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit Competition</title>
<link rel="icon" type="image/png" href="../images/logo.png">
<link rel="stylesheet" href="../Organiser-css/edit-competition.css">
</head>

<body>

<div class="form-wrapper">

<h1>Edit Competition</h1>

<?php if (!$can_edit): ?>
    <div class="lock-box">
        Editing Locked
    </div>
<?php endif; ?>

<form method="POST">

<h3>Basic Information</h3>

<label>Competition Name</label>
<input type="text" name="title"
    value="<?= htmlspecialchars($data['title']) ?>"
    <?= $can_edit ? '' : 'disabled' ?> required>

<label>Category</label>
<select name="category" <?= $can_edit ? '' : 'disabled' ?> required>
    <option value="esports" <?= $data['category']=='esports'?'selected':'' ?>>Esports</option>
    <option value="tech" <?= $data['category']=='tech'?'selected':'' ?>>Tech</option>
    <option value="academic" <?= $data['category']=='academic'?'selected':'' ?>>Academic</option>
    <option value="creative" <?= $data['category']=='creative'?'selected':'' ?>>Creative</option>
    <option value="business" <?= $data['category']=='business'?'selected':'' ?>>Business</option>
    <option value="sports" <?= $data['category']=='sports'?'selected':'' ?>>Sports</option>
    <option value="entertainment" <?= $data['category']=='entertainment'?'selected':'' ?>>Entertainment</option>
    <option value="others" <?= $data['category']=='others'?'selected':'' ?>>Others</option>
</select>

<label>Description</label>
<textarea name="description" <?= $can_edit ? '' : 'disabled' ?>>
<?= htmlspecialchars($data['description']) ?>
</textarea>

<label>Participants Limit</label>
<input type="number" name="participants"
    value="<?= $data['participants'] ?>"
    <?= $can_edit ? '' : 'disabled' ?>>

<h3>Application Period</h3>

<label>Application Start</label>
<input type="date" id="app_start" name="application_start"
    value="<?= $data['application_start'] ?>"
    min="<?= $today ?>"
    <?= $can_edit ? '' : 'disabled' ?>>

<label>Application End</label>
<input type="date" id="app_end" name="application_end"
    value="<?= $data['application_end'] ?>"
    min="<?= $today ?>"
    <?= $can_edit ? '' : 'disabled' ?>>

<h3>Competition Period</h3>

<label>Start Date</label>
<input type="date" id="start_date" name="start_date"
    value="<?= $data['start_date'] ?>"
    min="<?= $today ?>"
    <?= $can_edit ? '' : 'disabled' ?>>

<label>End Date</label>
<input type="date" id="end_date" name="end_date"
    value="<?= $data['end_date'] ?>"
    min="<?= $today ?>"
    <?= $can_edit ? '' : 'disabled' ?>>

<h3>Prizes</h3>

<label>1st Prize</label>
<input type="text" name="prize_1st"
    value="<?= htmlspecialchars($data['prize_1st']) ?>"
    <?= $can_edit ? '' : 'disabled' ?>>

<label>2nd Prize</label>
<input type="text" name="prize_2nd"
    value="<?= htmlspecialchars($data['prize_2nd']) ?>"
    <?= $can_edit ? '' : 'disabled' ?>>

<label>3rd Prize</label>
<input type="text" name="prize_3rd"
    value="<?= htmlspecialchars($data['prize_3rd']) ?>"
    <?= $can_edit ? '' : 'disabled' ?>>

<label>Prize Description</label>
<textarea name="prize_description"
    <?= $can_edit ? '' : 'disabled' ?>>
<?= htmlspecialchars($data['prize_description']) ?>
</textarea>

<?php if ($can_edit): ?>
    <button type="submit">Update Competition</button>
<?php endif; ?>

</form>

</div>

<script>
const today = new Date().toISOString().split('T')[0];

document.querySelectorAll('input[type="date"]').forEach(input => {
    input.min = today;
});
</script>

</body>
</html>