<?php

require_once '../../config/auth.php';
require_once '../../config/db.php';
require_once '../../shared/services/DriverDashboardService.php';
require_once '../../shared/helpers/portal.php';

portal_require_role(['driver']);

$service = new DriverDashboardService($conn, (int) $_SESSION['user']['id']);
$driver = $service->getDriverProfile();
$bus = $service->getDriverBus();
$stats = $service->getStats();
$stops = $service->getRouteStops();
$students = $service->getAssignedStudents();
$location = $service->getLiveLocation();
$notifications = $service->getRecentNotifications();

function e($value): string
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
}

function short_time($value): string
{
    return $value ? date('g:i A', strtotime($value)) : 'Not set';
}

function short_datetime($value): string
{
    return $value ? date('M j, Y g:i A', strtotime($value)) : 'Not updated';
}

$status = $stats['status'] ?? 'not_started';
$statusLabels = [
    'not_started' => 'Not Started',
    'running' => 'Running',
    'completed' => 'Completed',
];
$statusClass = $status === 'running' ? 'badge-success' : ($status === 'completed' ? 'badge-info' : 'badge-warning');

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
<title>Driver Dashboard | MindMerge</title>
<link rel="stylesheet" href="../../assets/css/global.css">
<link rel="stylesheet" href="../../assets/css/layout.css">
<link rel="stylesheet" href="../../assets/css/components.css">
<link rel="stylesheet" href="../../assets/css/dashboard-components.css">
<link rel="stylesheet" href="../../assets/css/portals.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="../assets/css/material-theme.css">

</head>
<body>
<div class="app-layout">
<?php include('../../partials/sidebar.php'); ?>
<div class="main-content">
<?php include('../../partials/topbar.php'); ?>
<div class="page-content">

<div class="dashboard-page">

<section class="dashboard-hero">
<div class="hero-content">
<h1 class="hero-title">Driver Route Console</h1>
<p class="hero-description">Welcome, <strong><?php echo e($driver['full_name'] ?? 'Driver'); ?></strong>. Review your assigned bus, route, stops, student roster, live location, and transport notifications.</p>
<div class="hero-meta">
<span class="hero-badge"><i class="fa-solid fa-bus"></i><?php echo e($stats['assigned_bus']); ?></span>
<span class="hero-badge"><i class="fa-solid fa-route"></i><?php echo e($stats['route_name']); ?></span>
<span class="hero-badge"><i class="fa-solid fa-location-dot"></i><?php echo e($statusLabels[$status] ?? 'Unknown'); ?></span>
</div>
</div>
<div class="hero-illustration"><i class="fa-solid fa-bus-simple"></i></div>
</section>

<section class="stats-grid">
<div class="stat-card"><div class="stat-icon"><i class="fa-solid fa-bus"></i></div><div class="stat-content"><span class="stat-label">Assigned Bus</span><span class="stat-value"><?php echo e($stats['assigned_bus']); ?></span><span class="stat-description"><?php echo e($bus['bus_name'] ?? 'No bus assigned'); ?></span></div></div>
<div class="stat-card"><div class="stat-icon"><i class="fa-solid fa-route"></i></div><div class="stat-content"><span class="stat-label">Today's Route</span><span class="stat-value"><?php echo e($stats['route_name']); ?></span><span class="stat-description"><?php echo number_format($stats['stop_count']); ?> stops</span></div></div>
<div class="stat-card"><div class="stat-icon"><i class="fa-solid fa-users"></i></div><div class="stat-content"><span class="stat-label">Students</span><span class="stat-value"><?php echo number_format($stats['student_count']); ?></span><span class="stat-description">Assigned riders</span></div></div>
<div class="stat-card"><div class="stat-icon"><i class="fa-solid fa-location-dot"></i></div><div class="stat-content"><span class="stat-label">Route Status</span><span class="stat-value"><?php echo e($statusLabels[$status] ?? 'Unknown'); ?></span><span class="stat-description">Last update: <?php echo short_datetime($location['updated_at'] ?? null); ?></span></div></div>
<div class="stat-card"><div class="stat-icon"><i class="fa-solid fa-bell"></i></div><div class="stat-content"><span class="stat-label">Notifications</span><span class="stat-value"><?php echo number_format($stats['unread_notifications']); ?></span><span class="stat-description">Unread alerts</span></div></div>
</section>

<section class="dashboard-widget">
<div class="dashboard-widget-header"><div class="dashboard-widget-title"><i class="fa-solid fa-bolt"></i><div><h4>Quick Actions</h4><span>Transport route controls</span></div></div></div>
<div class="dashboard-widget-body">
<div class="quick-actions">
<a href="../../transport/mobile/location.php" class="quick-action-card"><span class="quick-action-icon"><i class="fa-solid fa-play"></i></span><span class="quick-action-title">Start Trip</span><span class="quick-action-desc">Update live location</span></a>
<a href="../../transport/tracking/index.php" class="quick-action-card"><span class="quick-action-icon"><i class="fa-solid fa-map-location-dot"></i></span><span class="quick-action-title">Live Map</span><span class="quick-action-desc">Tracking panel</span></a>
<a href="../../profile/index.php" class="quick-action-card"><span class="quick-action-icon"><i class="fa-solid fa-user"></i></span><span class="quick-action-title">My Profile</span><span class="quick-action-desc">Driver details</span></a>
<a href="../../notifications/index.php" class="quick-action-card"><span class="quick-action-icon"><i class="fa-solid fa-bell"></i></span><span class="quick-action-title">Notifications</span><span class="quick-action-desc">Transport notices</span></a>
</div>
</div>
</section>

<section class="dashboard-grid-3">
<div class="info-panel">
<h3 class="info-panel-title"><i class="fa-solid fa-id-card"></i>Assigned Bus</h3>
<div class="info-list">
<div class="info-list-item"><span>Bus Number</span><strong><?php echo e($bus['bus_number'] ?? '-'); ?></strong></div>
<div class="info-list-item"><span>Bus Name</span><strong><?php echo e($bus['bus_name'] ?? '-'); ?></strong></div>
<div class="info-list-item"><span>Capacity</span><strong><?php echo e($bus['capacity'] ?? '-'); ?></strong></div>
<div class="info-list-item"><span>Bus Status</span><strong><?php echo e(ucfirst($bus['bus_status'] ?? $bus['status'] ?? 'active')); ?></strong></div>
</div>
</div>
<div class="info-panel">
<h3 class="info-panel-title"><i class="fa-solid fa-location-dot"></i>Live Location</h3>
<div class="info-list">
<div class="info-list-item"><span>Status</span><strong><span class="dashboard-badge <?php echo $statusClass; ?>"><?php echo e($statusLabels[$status] ?? 'Unknown'); ?></span></strong></div>
<div class="info-list-item"><span>Latitude</span><strong><?php echo e($location['latitude'] ?? '-'); ?></strong></div>
<div class="info-list-item"><span>Longitude</span><strong><?php echo e($location['longitude'] ?? '-'); ?></strong></div>
<div class="info-list-item"><span>Updated</span><strong><?php echo short_datetime($location['updated_at'] ?? null); ?></strong></div>
</div>
</div>
<div class="info-panel">
<h3 class="info-panel-title"><i class="fa-solid fa-phone"></i>Driver Details</h3>
<div class="info-list">
<div class="info-list-item"><span>Phone</span><strong><?php echo e($driver['phone'] ?? '-'); ?></strong></div>
<div class="info-list-item"><span>License</span><strong><?php echo e($driver['license_number'] ?? '-'); ?></strong></div>
<div class="info-list-item"><span>Emergency</span><strong><?php echo e($driver['emergency_contact'] ?? '-'); ?></strong></div>
</div>
</div>
</section>

<section class="dashboard-grid-equal">
<div class="dashboard-widget">
<div class="dashboard-widget-header"><div class="dashboard-widget-title"><i class="fa-solid fa-route"></i><div><h4>Today's Route Stops</h4><span>Pickup/drop sequence</span></div></div></div>
<div class="dashboard-widget-body">
<div class="event-list">
<?php if (empty($stops)) { ?><div class="dashboard-empty"><i class="fa-solid fa-route"></i><p>No stops configured for this route.</p></div><?php } ?>
<?php foreach ($stops as $stop) { ?><div class="event-card"><div class="event-date"><span class="event-day"><?php echo e($stop['stop_order'] ?: '-'); ?></span><span class="event-month">Stop</span></div><div class="event-content"><h4 class="event-title"><?php echo e($stop['stop_name']); ?></h4><div class="event-description"><?php echo ((int) $stop['is_start'] === 1) ? 'Start point' : (((int) $stop['is_end'] === 1) ? 'End point' : 'Route stop'); ?></div><div class="event-meta"><span><?php echo short_time($stop['arrival_time']); ?></span></div></div></div><?php } ?>
</div>
</div>
</div>
<div class="dashboard-widget">
<div class="dashboard-widget-header"><div class="dashboard-widget-title"><i class="fa-solid fa-users"></i><div><h4>Assigned Students</h4><span>Student count and stops</span></div></div></div>
<div class="dashboard-widget-body dashboard-table-wrap">
<table class="dashboard-table"><thead><tr><th>Student</th><th>Class</th><th>Stop</th></tr></thead><tbody>
<?php if (empty($students)) { ?><tr><td colspan="3">No students assigned.</td></tr><?php } ?>
<?php foreach ($students as $student) { ?><tr><td><?php echo e($student['full_name']); ?></td><td><?php echo e($student['class_name'] . ' ' . $student['section_name']); ?></td><td><?php echo e($student['stop_name']); ?></td></tr><?php } ?>
</tbody></table>
</div>
</div>
</section>

<section class="dashboard-widget">
<div class="dashboard-widget-header"><div class="dashboard-widget-title"><i class="fa-solid fa-bell"></i><div><h4>Notifications</h4><span>Recent transport and school updates</span></div></div></div>
<div class="dashboard-widget-body">
<div class="notification-list">
<?php if (empty($notifications)) { ?><div class="dashboard-empty"><i class="fa-solid fa-bell-slash"></i><p>No notifications available.</p></div><?php } ?>
<?php foreach ($notifications as $n) { ?><div class="notification-item"><div class="notification-icon"><i class="fa-solid fa-bullhorn"></i></div><div class="notification-content"><h4 class="notification-title"><?php echo e($n['title']); ?></h4><div class="notification-message"><?php echo e($n['message']); ?></div><div class="notification-meta"><span><?php echo e(ucfirst($n['notification_type'] ?? 'general')); ?></span><span><?php echo short_datetime($n['created_at']); ?></span></div></div></div><?php } ?>
</div>
</div>
</section>

</div>
</div>
</div>
</div>

<script src="../../assets/js/common.js"></script>
</body>
</html>
