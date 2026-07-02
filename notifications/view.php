<?php

include('../config/auth.php');
include('../config/db.php');
require_once('../config/notifications.php');

$page_title = 'View Notification';

$user_id = (int) ($_SESSION['user']['id'] ?? 0);
$user_role = $_SESSION['user']['role'] ?? '';
$notification_id = (int) ($_GET['id'] ?? 0);

$context = notification_user_context($conn, $user_id, $user_role);


if ($notification_id <= 0) {
    header('Location: index.php');
    exit();
}

if (!notification_user_can_see($conn, $notification_id, $context)) {
    header('Location: index.php?error=access_denied');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_read'])) {
    notification_mark_read($conn, $notification_id, $user_id);
    header('Location: view.php?id=' . $notification_id . '&success=read');
    exit();
}

$notification = mysqli_fetch_assoc(mysqli_query(
    $conn,
    "SELECT n.*, u.full_name AS created_by_name
     FROM notifications n
     LEFT JOIN users u ON n.created_by = u.id
     WHERE n.id = '$notification_id'
     LIMIT 1"
));

if (!$notification) {
    header('Location: index.php');
    exit();
}

$read_row = mysqli_fetch_assoc(mysqli_query(
    $conn,
    "SELECT read_id FROM notification_reads
     WHERE notification_id = '$notification_id' AND user_id = '$user_id'
     LIMIT 1"
));

$is_read = !empty($read_row);

if (!$is_read) {
    notification_mark_read($conn, $notification_id, $user_id);
    $is_read = true;
}

$target_labels = notification_resolve_target_labels($conn, $notification_id);
$notification_config = notification_type_config($notification['type']);

if (
    empty($notification_config)
    || !isset($notification_config['color'])
) {$notification_config = array_merge([
    'label' => ucfirst($notification['type']),
    'icon'  => 'fa-bell',
    'class' => 'info',
    'color' => '#2563eb',
    'bg'    => '#eff6ff'
], $notification_config);

}

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
<title>View Notification | MindMerge SmartCampus</title>
<link rel="stylesheet" href="../assets/css/global.css">
<link rel="stylesheet" href="../assets/css/layout.css">
<link rel="stylesheet" href="../assets/css/components.css">
<link rel="stylesheet" href="../assets/css/notifications.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="../../assets/css/material-theme.css">
<style>
.view-preview{
border-left:5px solid <?php echo $notification_config['color']; ?>;
background:<?php echo $notification_config['bg']; ?>;
}
.info-grid{
display:grid;
grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
gap:18px;
}
.info-item{
display:flex;
flex-direction:column;
gap:6px;
}
.info-label{
font-size:13px;
color:var(--muted);
font-weight:500;
}
.info-value{
font-size:15px;
font-weight:600;
}
</style>
</head>
<body>

<div class="app-layout">

<?php include('../partials/sidebar.php'); ?>

<div class="main-content">

<?php include('../partials/topbar.php'); ?>

<div class="page-content">

<div class="page-header">

<div>
<h1>Notification Details</h1>
<p>View announcement content, recipients, and delivery status.</p>
</div>

<div style="display:flex;gap:12px;flex-wrap:wrap;">
<a href="index.php" class="btn">
<i class="fa-solid fa-arrow-left"></i>
Back
</a>
</div>

</div>

<?php if (isset($_GET['success']) && $_GET['success'] === 'created') { ?>
<div class="alert-banner success">
<i class="fa-solid fa-circle-check"></i>
Notification created and sent successfully.
</div>
<?php } ?>

<?php if (isset($_GET['success']) && $_GET['success'] === 'read') { ?>
<div class="alert-banner success">
<i class="fa-solid fa-circle-check"></i>
Notification marked as read.
</div>
<?php } ?>

<div class="dashboard-section view-preview notif-preview">

<div class="notif-preview-header">
<span class="notif-preview-badge" style="background:<?php echo $notification_config['bg']; ?>;color:<?php echo $notification_config['color']; ?>;">
<i class="fa-solid <?php echo $notification_config['icon']; ?>"></i>
<?php echo htmlspecialchars($notification_config['label']); ?>
</span>
<span class="status <?php echo $is_read ? 'success' : 'warning'; ?>">
<?php echo $is_read ? 'Read' : 'Unread'; ?>
</span>
</div>

<h2 class="notif-preview-title" style="font-size:22px;margin-bottom:12px;">
<?php echo htmlspecialchars($notification['title']); ?>
</h2>

<div class="notif-preview-message" style="color:var(--text);font-size:15px;">
<?php echo nl2br(htmlspecialchars($notification['message'])); ?>
</div>

<div class="notif-preview-meta">
<i class="fa-regular fa-clock"></i>
<?php echo date('d M Y, h:i A', strtotime($notification['created_at'])); ?>
</div>

</div>

<div class="dashboard-section">

<div class="section-header">
<h2>Delivery Information</h2>
</div>

<div class="info-grid">

<div class="info-item">
<span class="info-label">Type</span>
<span class="info-value">
<span class="status <?php echo $notification_config['class']; ?>">
<?php echo htmlspecialchars($notification_config['label']); ?>
</span>
</span>
</div>

<div class="info-item">
<span class="info-label">Created By</span>
<span class="info-value"><?php echo htmlspecialchars($notification['created_by_name'] ?? 'System'); ?></span>
</div>

<div class="info-item">
<span class="info-label">Created At</span>
<span class="info-value"><?php echo date('d M Y, h:i A', strtotime($notification['created_at'])); ?></span>
</div>

<div class="info-item">
<span class="info-label">Read Status</span>
<span class="info-value">
<span class="status <?php echo $is_read ? 'success' : 'warning'; ?>">
<?php echo $is_read ? 'Read' : 'Unread'; ?>
</span>
</span>
</div>

</div>

</div>

<?php if($_SESSION['user']['role'] === 'admin'){ ?>

<div class="dashboard-section">

<div class="section-header">
<h2>Recipients</h2>
</div>

<?php if (!empty($target_labels)) { ?>

<div class="notif-target-list">

<?php foreach ($target_labels as $label) { ?>

<span class="notif-target-tag">

<i class="fa-solid fa-user-group"></i>

<?php echo htmlspecialchars($label); ?>

</span>

<?php } ?>

</div>

<?php } else { ?>

<div class="alert-banner info">
<i class="fa-solid fa-triangle-exclamation"></i>
No recipient information available.
</div>  
<?php } ?>

</div>

<?php } ?>

<?php if (!$is_read) { ?>
<form method="POST" style="margin-top:20px;">
<button type="submit" name="mark_read" value="1" class="btn btn-primary">
<i class="fa-solid fa-envelope-open"></i>
Mark as Read
</button>
</form>
<?php } ?>

</div>

</div>

</div>

<script src="../assets/js/common.js"></script>

</body>
</html>
