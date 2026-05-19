<?php
session_start();
include "../config.php";
requireRole(['admin','superadmin']);
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = (int)$_SESSION['user_id'];

/* =========================
   PROFILE IMAGE
========================= */
$profile_image = "../images/profile.avif";
$uq = $conn->query("SELECT profile_image FROM users WHERE id = $user_id");
if ($uq && $uq->num_rows > 0) {
    $u = $uq->fetch_assoc();
    if (!empty($u['profile_image'])) {
        $path = "/images/profile/" . $u['profile_image'];
        if (file_exists($_SERVER['DOCUMENT_ROOT'] . $path)) {
            $profile_image = $path;
        }
    }
}

/* =========================
   DELETE USER
========================= */
if (isset($_GET['delete'])) {
    $del_id = (int)$_GET['delete'];
    if ($del_id !== $user_id) {
        $conn->query("DELETE FROM users WHERE id = $del_id");
    }
    header("Location: manage-users.php?msg=deleted");
    exit();
}

/* =========================
   TOGGLE STATUS
========================= */
if (isset($_GET['toggle'])) {
    $tog_id  = (int)$_GET['toggle'];
    $tog_status = $_GET['status'] ?? '';
    $new_status = ($tog_status === 'active') ? 'inactive' : 'active';
    $upd = $conn->prepare("UPDATE users SET status=? WHERE id=?");
    $upd->bind_param("si", $new_status, $tog_id);
    $upd->execute();
    header("Location: manage-users.php?msg=updated");
    exit();
}

/* =========================
   EDIT ROLE
========================= */
if (isset($_POST['edit_role'])) {
    $edit_id   = (int)$_POST['edit_id'];
    $edit_role = $_POST['edit_role'];
    $allowed_roles = ['admin','organiser','participant','judge'];
    if (in_array($edit_role, $allowed_roles)) {
        $upd = $conn->prepare("UPDATE users SET role=? WHERE id=?");
        $upd->bind_param("si", $edit_role, $edit_id);
        $upd->execute();
    }
    header("Location: manage-users.php?msg=updated");
    exit();
}

/* =========================
   FILTER
========================= */
$filter_role   = $_GET['role']   ?? 'all';
$filter_status = $_GET['status'] ?? 'all';
$search        = $_GET['search'] ?? '';

$where = "WHERE 1=1";
$params = [];
$types  = '';

if ($filter_role !== 'all') {
    $where   .= " AND role = ?";
    $params[] = $filter_role;
    $types   .= 's';
}

if ($filter_status !== 'all') {
    $where   .= " AND status = ?";
    $params[] = $filter_status;
    $types   .= 's';
}

if (!empty($search)) {
    $like     = "%$search%";
    $where   .= " AND (fullname LIKE ? OR email LIKE ?)";
    $params[] = $like;
    $params[] = $like;
    $types   .= 'ss';
}

$sql  = "SELECT * FROM users $where ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$users = $stmt->get_result();

/* =========================
   COUNTS
========================= */
$total_users    = $conn->query("SELECT COUNT(*) as c FROM users")->fetch_assoc()['c'];
$active_users   = $conn->query("SELECT COUNT(*) as c FROM users WHERE status='active'")->fetch_assoc()['c'];
$inactive_users = $conn->query("SELECT COUNT(*) as c FROM users WHERE status='inactive'")->fetch_assoc()['c'];

$msg = $_GET['msg'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage Users</title>
<link rel="icon" type="image/png" href="../images/logo.png">
<link rel="stylesheet" href="../Admin-css/dashboard.css">
<link rel="stylesheet" href="../Admin-css/manage-users.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<!-- SIDEBAR -->
<div class="sidebar">
    <div class="logo"></div>
    <ul class="menu">
        <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a></li>
        <li><a href="profile.php"><i class="fas fa-user"></i><span>Profile</span></a></li>
        <li><a href="manage-admin.php"><i class="fas fa-user-shield"></i><span>Manage Admin</span></a></li>
        <li class="active"><a href="manage-users.php"><i class="fas fa-users"></i><span>Manage Users</span></a></li>
        <li><a href="manage-competition-list.php"><i class="fas fa-list-check"></i><span>Manage Competition</span></a></li>
        <li><a href="manage-judges.php"><i class="fas fa-gavel"></i><span>Manage Judges</span></a></li>
        <li class="logout"><a href="../logout.php"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a></li>
    </ul>
</div>

<!-- MAIN -->
<div class="main--content">

    <div class="header--wrapper">
        <div class="header--title">
            <span>Admin</span>
            <h2>Manage Users</h2>
        </div>
        <div class="user--info">
            <img src="<?= $profile_image ?>" alt="">
        </div>
    </div>

    <!-- FLASH -->
    <?php if ($msg === 'deleted'): ?>
        <div class="msg-box msg-rejected">User deleted successfully.</div>
    <?php elseif ($msg === 'updated'): ?>
        <div class="msg-box msg-success">User updated successfully.</div>
    <?php endif; ?>

    <!-- STATS -->
    <div class="user-stats">
        <div class="stat-card">
            <div class="stat-num"><?= $total_users ?></div>
            <div class="stat-label">Total Users</div>
        </div>
        <div class="stat-card active">
            <div class="stat-num"><?= $active_users ?></div>
            <div class="stat-label">Active</div>
        </div>
        <div class="stat-card inactive">
            <div class="stat-num"><?= $inactive_users ?></div>
            <div class="stat-label">Inactive</div>
        </div>
    </div>

    <!-- FILTER BAR -->
    <div class="filter-bar">
        <form method="GET" class="filter-form">
            <input type="text" name="search" placeholder="Search name or email..."
                   value="<?= htmlspecialchars($search) ?>" class="search-input">

            <select name="role" class="filter-select">
                <option value="all"        <?= $filter_role=='all'         ? 'selected':'' ?>>All Roles</option>
                <option value="admin"      <?= $filter_role=='admin'       ? 'selected':'' ?>>Admin</option>
                <option value="organiser"  <?= $filter_role=='organiser'   ? 'selected':'' ?>>Organiser</option>
                <option value="participant"<?= $filter_role=='participant'  ? 'selected':'' ?>>Participant</option>
                <option value="judge"      <?= $filter_role=='judge'       ? 'selected':'' ?>>Judge</option>
            </select>

            <select name="status" class="filter-select">
                <option value="all"      <?= $filter_status=='all'      ? 'selected':'' ?>>All Status</option>
                <option value="active"   <?= $filter_status=='active'   ? 'selected':'' ?>>Active</option>
                <option value="inactive" <?= $filter_status=='inactive' ? 'selected':'' ?>>Inactive</option>
            </select>

            <button type="submit" class="btn-filter">
                <i class="fas fa-search"></i> Search
            </button>

            <a href="manage-users.php" class="btn-reset">Reset</a>
        </form>
    </div>

    <!-- TABLE -->
    <div class="table--container">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>User</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Joined</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php
            $i = 1;
            $rows = [];
            while ($row = $users->fetch_assoc()) $rows[] = $row;
            ?>

            <?php if (empty($rows)): ?>
                <tr>
                    <td colspan="7" style="text-align:center; color:#aaa; padding:30px;">
                        No users found.
                    </td>
                </tr>
            <?php endif; ?>

            <?php foreach ($rows as $row): ?>
            <tr>
                <td><?= $i++ ?></td>

                <!-- USER -->
                <td>
                    <div class="user-cell">
                        <?php
                        $av = "../images/profile.avif";
                        if (!empty($row['profile_image'])) {
                            $pp = "/images/profile/" . $row['profile_image'];
                            if (file_exists($_SERVER['DOCUMENT_ROOT'] . $pp)) $av = $pp;
                        }
                        ?>
                        <img src="<?= $av ?>" class="user-avatar">
                        <div>
                            <div class="user-name"><?= htmlspecialchars($row['fullname'] ?? '—') ?></div>
                            <div class="user-username">@<?= htmlspecialchars($row['username'] ?? '—') ?></div>
                        </div>
                    </div>
                </td>

                <!-- EMAIL -->
                <td><?= htmlspecialchars($row['email']) ?></td>

                <!-- ROLE -->
                <td><span class="role-tag <?= $row['role'] ?>"><?= ucfirst($row['role']) ?></span></td>

                <!-- STATUS -->
                <td>
                    <span class="status <?= $row['status'] === 'active' ? 'approved' : 'rejected' ?>">
                        <?= ucfirst($row['status']) ?>
                    </span>
                </td>

                <!-- JOINED -->
                <td><?= date('d M Y', strtotime($row['created_at'])) ?></td>

                <!-- ACTIONS -->
                <td>
                    <div class="action-btns">

                        <!-- EDIT ROLE -->
                        <button class="btn-action edit"
                                onclick="openEditPopup(<?= $row['id'] ?>, '<?= $row['role'] ?>')">
                            <i class="fas fa-edit"></i>
                        </button>

                        <!-- TOGGLE STATUS -->
                        <?php if ($row['id'] !== $user_id): ?>
                            <a href="manage-users.php?toggle=<?= $row['id'] ?>&status=<?= $row['status'] ?>"
                               class="btn-action <?= $row['status'] === 'active' ? 'disable' : 'enable' ?>"
                               onclick="return confirm('<?= $row['status'] === 'active' ? 'Disable' : 'Enable' ?> this user?')">
                                <i class="fas fa-<?= $row['status'] === 'active' ? 'ban' : 'check' ?>"></i>
                            </a>

                            <!-- DELETE -->
                            <a href="manage-users.php?delete=<?= $row['id'] ?>"
                               class="btn-action delete"
                               onclick="return confirm('Delete this user? This cannot be undone.')">
                                <i class="fas fa-trash"></i>
                            </a>
                        <?php else: ?>
                            <span style="font-size:12px; color:#aaa;">(You)</span>
                        <?php endif; ?>

                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</div>

<!-- EDIT ROLE POPUP -->
<div id="overlay" onclick="closePopup()"></div>

<div id="editPopup">
    <h3>Edit User Role</h3>
    <form method="POST" id="editForm">
        <input type="hidden" name="edit_id" id="editUserId">
        <div class="popup-field">
            <label>Role</label>
            <select name="edit_role" id="editRoleSelect" class="filter-select" style="width:100%;">
                <option value="admin">Admin</option>
                <option value="organiser">Organiser</option>
                <option value="participant">Participant</option>
                <option value="judge">Judge</option>
            </select>
        </div>
        <div class="popup-btns">
            <button type="button" onclick="closePopup()">Cancel</button>
            <button type="submit" name="edit_role">Save</button>
        </div>
    </form>
</div>

<script>
function openEditPopup(id, role) {
    document.getElementById('editUserId').value    = id;
    document.getElementById('editRoleSelect').value = role;
    document.getElementById('overlay').style.display   = 'block';
    document.getElementById('editPopup').style.display = 'block';
}

function closePopup() {
    document.getElementById('overlay').style.display   = 'none';
    document.getElementById('editPopup').style.display = 'none';
}
</script>

</body>
</html>