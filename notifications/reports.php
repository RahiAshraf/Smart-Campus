<?php

include('../config/auth.php');
include('../config/db.php');
require_once('../config/notifications.php');

requirePermission('notifications', 'view');

$page_title = 'Notification Reports';

$user_id = (int) ($_SESSION['user']['id'] ?? 0);
$user_role = $_SESSION['user']['role'] ?? '';
$context = notification_user_context($conn, $user_id, $user_role);
$visibility = notification_visibility_where($conn, $context);
$type_config = notification_type_config();

$date_from = trim($_GET['date_from'] ?? date('Y-m-01'));
$date_to = trim($_GET['date_to'] ?? date('Y-m-d'));
$type_filter = trim($_GET['type'] ?? '');

$filter_where = " AND $visibility ";

$from_sql = mysqli_real_escape_string($conn, $date_from);
$to_sql = mysqli_real_escape_string($conn, $date_to);
$filter_where .= " AND DATE(n.created_at) BETWEEN '$from_sql' AND '$to_sql' ";

if ($type_filter !== '' && notification_is_valid_type($type_filter)) {
    $type_sql = mysqli_real_escape_string($conn, $type_filter);
    $filter_where .= " AND n.type = '$type_sql' ";
}

$total = (int) mysqli_fetch_assoc(mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total FROM notifications n WHERE 1=1 $filter_where"
))['total'];

$read_count = (int) mysqli_fetch_assoc(mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM notifications n
     INNER JOIN notification_reads nr
       ON nr.notification_id = n.id AND nr.user_id = '$user_id'
     WHERE 1=1 $filter_where"
))['total'];

$unread_count = max(0, $total - $read_count);

$by_type = [];
$type_query = mysqli_query(
    $conn,
    "SELECT n.type, COUNT(*) AS total
     FROM notifications n
     WHERE 1=1 $filter_where
     GROUP BY n.type
     ORDER BY total DESC"
);

while ($row = mysqli_fetch_assoc($type_query)) {
    $by_type[] = $row;
}

$recent_query = mysqli_query(
    $conn,
    "SELECT n.*, u.full_name AS created_by_name,
            CASE WHEN nr.read_id IS NULL THEN 0 ELSE 1 END AS is_read
     FROM notifications n
     LEFT JOIN users u ON u.id = n.created_by
     LEFT JOIN notification_reads nr
       ON nr.notification_id = n.id AND nr.user_id = '$user_id'
     WHERE 1=1 $filter_where
     ORDER BY n.created_at DESC
     LIMIT 50"
);

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Notification Reports | MindMerge SmartCampus</title>
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
<h1>Notification Reports</h1>
<p>Delivery summary, read rates, and notification activity.</p>
</div>
<a href="index.php" class="btn"><i class="fa-solid fa-arrow-left"></i> Back to Notifications</a>
</div>

<div class="dashboard-grid">
<div class="dashboard-card stat-card"><div class="stat-top"><div class="card-icon"><i class="fa-solid fa-bell"></i></div><h3><?php echo $total; ?></h3></div><p>Total Notifications</p></div>
<div class="dashboard-card stat-card"><div class="stat-top"><div class="card-icon green"><i class="fa-solid fa-envelope-open"></i></div><h3><?php echo $read_count; ?></h3></div><p>Read</p></div>
<div class="dashboard-card stat-card"><div class="stat-top"><div class="card-icon orange"><i class="fa-solid fa-envelope"></i></div><h3><?php echo $unread_count; ?></h3></div><p>Unread</p></div>
<div class="dashboard-card stat-card"><div class="stat-top"><div class="card-icon blue"><i class="fa-solid fa-percent"></i></div><h3><?php echo $total > 0 ? round(($read_count / $total) * 100, 2) : 0; ?>%</h3></div><p>Read Rate</p></div>
</div>

<div class="dashboard-section">
<div class="section-header"><h2>Filters</h2></div>
<form method="GET" style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end;">
<div class="form-group">
<label>From</label>
<input type="date" name="date_from" value="<?php echo htmlspecialchars($date_from); ?>" class="form-control">
</div>
<div class="form-group">
<label>To</label>
<input type="date" name="date_to" value="<?php echo htmlspecialchars($date_to); ?>" class="form-control">
</div>
<div class="form-group">
<label>Type</label>
<select name="type" class="form-control">
<option value="">All Types</option>
<?php foreach ($type_config as $key => $cfg) { ?>
<option value="<?php echo htmlspecialchars($key); ?>" <?php echo $type_filter === $key ? 'selected' : ''; ?>><?php echo htmlspecialchars($cfg['label']); ?></option>
<?php } ?>
</select>
</div>
<button type="submit" class="btn btn-primary"><i class="fa-solid fa-filter"></i> Apply</button>
</form>
</div>

<div class="dashboard-section">
<div class="section-header"><h2>By Type</h2></div>
<div class="table-responsive">
<table class="custom-table">
<thead><tr><th>Type</th><th>Count</th><th>Share</th></tr></thead>
<tbody>
<?php if (empty($by_type)) { ?>
<tr><td colspan="3"><div class="empty-state"><i class="fa-solid fa-chart-pie"></i><h3>No data</h3><p>No notifications match the selected filters.</p></div></td></tr>
<?php } else { foreach ($by_type as $row) {
    $cfg = $type_config[$row['type']] ?? $type_config['general'];
    $share = $total > 0 ? round(((int) $row['total'] / $total) * 100, 2) : 0;
?>
<tr>
<td><span class="status <?php echo $cfg['class']; ?>"><i class="fa-solid <?php echo $cfg['icon']; ?>"></i> <?php echo htmlspecialchars($cfg['label']); ?></span></td>
<td><?php echo (int) $row['total']; ?></td>
<td><?php echo $share; ?>%</td>
</tr>
<?php } } ?>
</tbody>
</table>
</div>
</div>

<div class="dashboard-section">
<div class="section-header"><h2>Recent Activity</h2></div>
<div class="table-responsive">
<table class="custom-table">
<thead><tr><th>Title</th><th>Type</th><th>Status</th><th>Created</th><th></th></tr></thead>
<tbody>
<?php if (!$recent_query || mysqli_num_rows($recent_query) === 0) { ?>
<tr><td colspan="5"><div class="empty-state"><i class="fa-solid fa-bell"></i><h3>No notifications</h3></div></td></tr>
<?php } else { while ($row = mysqli_fetch_assoc($recent_query)) {
    $cfg = $type_config[$row['type']] ?? $type_config['general'];
?>
<tr>
<td><?php echo htmlspecialchars($row['title']); ?></td>
<td><span class="status <?php echo $cfg['class']; ?>"><?php echo htmlspecialchars($cfg['label']); ?></span></td>
<td><span class="status <?php echo $row['is_read'] ? 'success' : 'warning'; ?>"><?php echo $row['is_read'] ? 'Read' : 'Unread'; ?></span></td>
<td><?php echo date('d M Y, h:i A', strtotime($row['created_at'])); ?></td>
<td><a href="view.php?id=<?php echo (int) $row['id']; ?>" class="btn btn-sm btn-primary">View</a></td>
</tr>
<?php } } ?>
</tbody>
</table>
</div>
</div>

</div>
</div>
</div>
<script src="../assets/js/common.js"></script>
</body>
</html>
