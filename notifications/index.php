<?php

include('../config/auth.php');
include('../config/db.php');
require_once('../config/notifications.php');

$page_title = 'Notifications';

$user_id = (int) ($_SESSION['user']['id'] ?? 0);
$user_role = $_SESSION['user']['role'] ?? '';
$context = notification_user_context($conn, $user_id, $user_role);
$visibility = notification_visibility_where($conn, $context);
$type_config = notification_type_config();

$search = trim($_GET['search'] ?? '');
$type_filter = trim($_GET['type'] ?? '');
$date_filter = trim($_GET['date'] ?? '');

$filter_where = " AND $visibility ";

if ($search !== '') {
    $search_sql = mysqli_real_escape_string($conn, $search);
    $filter_where .= " AND (n.title LIKE '%$search_sql%' OR n.message LIKE '%$search_sql%') ";
}

if ($type_filter !== '' && notification_is_valid_type($type_filter)) {
    $type_sql = mysqli_real_escape_string($conn, $type_filter);
    $filter_where .= " AND n.type = '$type_sql' ";
}

if ($date_filter !== '') {
    $date_sql = mysqli_real_escape_string($conn, $date_filter);
    $filter_where .= " AND DATE(n.created_at) = '$date_sql' ";
}

$total_notifications = (int) mysqli_fetch_assoc(mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total FROM notifications n WHERE 1=1 $filter_where"
))['total'];

$today_notifications = (int) mysqli_fetch_assoc(mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total FROM notifications n
     WHERE DATE(n.created_at) = CURDATE() $filter_where"
))['total'];

$read_notifications = (int) mysqli_fetch_assoc(mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM notifications n
     INNER JOIN notification_reads nr
       ON nr.notification_id = n.id AND nr.user_id = '$user_id'
     WHERE 1=1 $filter_where"
))['total'];

$unread_notifications = max(0, $total_notifications - $read_notifications);

$notifications_query = mysqli_query(
    $conn,
    "SELECT
        n.*,
        u.full_name AS created_by_name,
        CASE WHEN nr.read_id IS NULL THEN 0 ELSE 1 END AS is_read
     FROM notifications n
     LEFT JOIN users u ON n.created_by = u.id
     LEFT JOIN notification_reads nr
       ON nr.notification_id = n.id AND nr.user_id = '$user_id'
     WHERE 1=1 $filter_where
     ORDER BY n.created_at DESC"
);

$can_create = canCreate('notifications');

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">

<script>
(function(){
    if(localStorage.getItem("mindmerge-theme") === "dark"){
        document.documentElement.classList.add("dark-mode");
    }
})();
</script>


<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Notifications | MindMerge SmartCampus</title>
<link rel="stylesheet" href="../assets/css/global.css">
<link rel="stylesheet" href="../assets/css/layout.css">
<link rel="stylesheet" href="../assets/css/components.css">
<link rel="stylesheet" href="../assets/css/notifications.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="../../assets/css/material-theme.css">
</head>
<body>

<div class="app-layout">

<?php include('../partials/sidebar.php'); ?>

<div class="main-content">

<?php include('../partials/topbar.php'); ?>

<div class="page-content">

<div class="page-header">

<div>
<h1>Notifications</h1>
<p>Manage announcements, alerts, and campus communication.</p>
</div>

<?php if ($can_create) { ?>
<a href="create.php" class="btn btn-primary">
<i class="fa-solid fa-paper-plane"></i>
Create Notification
</a>
<?php } ?>
<?php if (canView('notifications')) { ?>
<a href="reports.php" class="btn">
<i class="fa-solid fa-chart-column"></i>
Reports
</a>
<?php } ?>

</div>

<?php if (isset($_GET['error']) && $_GET['error'] === 'access_denied') { ?>
<div class="alert-banner error">
<i class="fa-solid fa-circle-exclamation"></i>
You do not have access to that notification.
</div>
<?php } ?>

<div class="dashboard-grid">

<div class="dashboard-card stat-card">
<div class="stat-top">
<div class="card-icon"><i class="fa-solid fa-bell"></i></div>
<h3><?php echo $total_notifications; ?></h3>
</div>
<p>Total Notifications</p>
</div>

<div class="dashboard-card stat-card">
<div class="stat-top">
<div class="card-icon"><i class="fa-solid fa-envelope-open"></i></div>
<h3><?php echo $read_notifications; ?></h3>
</div>
<p>Read</p>
</div>

<div class="dashboard-card stat-card">
<div class="stat-top">
<div class="card-icon"><i class="fa-solid fa-envelope"></i></div>
<h3><?php echo $unread_notifications; ?></h3>
</div>
<p>Unread</p>
</div>

<div class="dashboard-card stat-card">
<div class="stat-top">
<div class="card-icon"><i class="fa-solid fa-calendar-day"></i></div>
<h3><?php echo $today_notifications; ?></h3>
</div>
<p>Today's Notifications</p>
</div>

</div>

<div class="dashboard-section">

<div class="section-header">
<h2>Filters</h2>
</div>

<form method="GET" action="">
<div class="form-grid">
<div class="form-group">
<label class="form-label">Search</label>
<input type="text" name="search" class="form-input" placeholder="Search title or message..." value="<?php echo htmlspecialchars($search); ?>">
</div>
<div class="form-group">
<label class="form-label">Type</label>
<select name="type" class="form-select">
<option value="">All Types</option>
<?php foreach ($type_config as $key => $type) { ?>
<option value="<?php echo $key; ?>" <?php echo $type_filter === $key ? 'selected' : ''; ?>>
<?php echo htmlspecialchars($type['label']); ?>
</option>
<?php } ?>
</select>
</div>
<div class="form-group">
<label class="form-label">Date</label>
<input type="date" name="date" class="form-input" value="<?php echo htmlspecialchars($date_filter); ?>">
</div>
<div class="form-group">
<label class="form-label">&nbsp;</label>
<div style="display:flex;gap:10px;flex-wrap:wrap;">
<button type="submit" class="btn btn-primary">
<i class="fa-solid fa-filter"></i>
Apply
</button>
<a href="index.php" class="btn">
<i class="fa-solid fa-rotate-left"></i>
Reset
</a>
</div>
</div>
</div>
</form>

</div>

<div class="dashboard-section">

<div class="section-header">
<h2>Quick Actions</h2>
</div>

<div class="quick-actions">

<?php if ($can_create) { ?>
<a href="create.php" class="action-card">
<i class="fa-solid fa-paper-plane"></i>
<h3>Create Notification</h3>
<p>Send a new campus announcement.</p>
</a>
<?php } ?>

<a href="reports.php" class="action-card">
<i class="fa-solid fa-chart-column"></i>
<h3>Reports</h3>
<p>View delivery and read-rate analytics.</p>
</a>

<a href="index.php" class="action-card">
<i class="fa-solid fa-arrows-rotate"></i>
<h3>Refresh</h3>
<p>Reload the notifications list.</p>
</a>

<?php if ($unread_notifications > 0) { ?>
<form method="POST" action="api/mark_all_read.php" id="markAllReadForm" style="display:contents;">
<button type="button" class="action-card" id="markAllReadCard" style="border:none;cursor:pointer;font:inherit;width:100%;">
<i class="fa-solid fa-envelope-open-text"></i>
<h3>Mark All Read</h3>
<p>Clear <?php echo (int) $unread_notifications; ?> unread notification(s).</p>
</button>
</form>
<?php } ?>

</div>

</div>

<div class="dashboard-section">

<div class="section-header">
<h2>Notifications List</h2>
</div>

<div class="table-responsive">

<table class="custom-table" id="notificationsTable">

<thead>
<tr>
<th data-sort="true">Title</th>
<th data-sort="true">Type</th>
<th data-sort="true">Created By</th>
<th data-sort="true">Date</th>
<th data-sort="true">Status</th>
<th>Actions</th>
</tr>
</thead>

<tbody>

<?php
$notification_found = false;

while ($notification = mysqli_fetch_assoc($notifications_query)) {
    $notification_found = true;
    $type = $notification['type'];
    $config = $type_config[$type] ?? $type_config['general'];
?>

<tr class="<?php echo $notification['is_read'] ? '' : 'unread-row'; ?>">

<td data-value="<?php echo htmlspecialchars(strtolower($notification['title'])); ?>">
<div style="display:flex;align-items:flex-start;gap:12px;">
<div style="width:36px;height:36px;border-radius:10px;display:flex;align-items:center;justify-content:center;background:<?php echo $config['bg']; ?>;color:<?php echo $config['color']; ?>;flex-shrink:0;">
<i class="fa-solid <?php echo $config['icon']; ?>"></i>
</div>
<div>
<strong><?php echo htmlspecialchars($notification['title']); ?></strong>
<br>
<small style="color:var(--muted);">
<?php echo htmlspecialchars(mb_strimwidth(strip_tags($notification['message']), 0, 80, '...')); ?>
</small>
</div>
</div>
</td>

<td data-value="<?php echo htmlspecialchars($type); ?>">
<span class="status <?php echo $config['class']; ?>">
<?php echo htmlspecialchars($config['label']); ?>
</span>
</td>

<td><?php echo htmlspecialchars($notification['created_by_name'] ?? 'System'); ?></td>

<td data-value="<?php echo strtotime($notification['created_at']); ?>">
<?php echo date('d M Y', strtotime($notification['created_at'])); ?>
</td>

<td data-value="<?php echo $notification['is_read'] ? '1' : '0'; ?>">
<?php if ($notification['is_read']) { ?>
<span class="status success">Read</span>
<?php } else { ?>
<span class="status warning">Unread</span>
<?php } ?>
</td>

<td>
<a href="view.php?id=<?php echo (int) $notification['id']; ?>" class="btn btn-primary" style="padding:8px 12px;font-size:13px;">
<i class="fa-solid fa-eye"></i>
View
</a>
</td>

</tr>

<?php } ?>

<?php if (!$notification_found) { ?>
<tr>
<td colspan="6" style="text-align:center;padding:60px 20px;">
<i class="fa-solid fa-bell-slash" style="font-size:48px;opacity:.4;display:block;margin-bottom:15px;"></i>
<h3>No Notifications Found</h3>
<p style="margin-top:10px;margin-bottom:20px;color:var(--muted);">
<?php echo ($search || $type_filter || $date_filter)
    ? 'No notifications match the selected filters.'
    : 'No notifications have been sent yet.'; ?>
</p>
<?php if ($can_create) { ?>
<a href="create.php" class="btn btn-primary">
<i class="fa-solid fa-plus"></i>
Create First Notification
</a>
<?php } ?>
</td>
</tr>
<?php } ?>

</tbody>

</table>

</div>

</div>

</div>

</div>

</div>

<script>
document.getElementById('markAllReadCard')?.addEventListener('click', function () {
    fetch('api/mark_all_read.php', {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'Accept': 'application/json' }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            window.location.href = 'index.php';
        }
    });
});
</script>

<script src="../assets/js/common.js"></script>

</body>
</html>
