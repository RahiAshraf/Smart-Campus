<?php

require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../shared/services/ParentDashboardService.php';
require_once __DIR__ . '/../../shared/helpers/portal.php';

portal_require_role(['parent']);
requirePermission('attendance', 'view');

$service = new ParentDashboardService($conn, (int) $_SESSION['user']['id']);
$children = $service->getChildren();

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Child Attendance | MindMerge</title>
<link rel="stylesheet" href="../../assets/css/global.css">
<link rel="stylesheet" href="../../assets/css/layout.css">
<link rel="stylesheet" href="../../assets/css/components.css">
<link rel="stylesheet" href="../../assets/css/portals.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="../../assets/css/material-theme.css">
</head>
<body>
<div class="app-layout">
<?php include('../../partials/sidebar.php'); ?>
<div class="main-content">
<?php include('../../partials/topbar.php'); ?>
<div class="page-content">
<div class="page-header"><div><h1>Child Attendance</h1><p>Recent attendance records for your children.</p></div></div>
<?php foreach ($children as $child) {
    $records = $service->getChildAttendance((int) $child['id']);
?>
<div class="dashboard-section">
<div class="section-header"><h2><?php echo htmlspecialchars($child['full_name']); ?> (<?php echo htmlspecialchars($child['student_id']); ?>)</h2></div>
<div class="table-responsive">
<table class="custom-table">
<thead><tr><th>Date</th><th>Subject</th><th>Status</th></tr></thead>
<tbody>
<?php if (empty($records)) { ?><tr><td colspan="3">No records.</td></tr><?php } else { foreach ($records as $r) { ?>
<tr>
<td><?php echo date('M j, Y', strtotime($r['attendance_date'])); ?></td>
<td><?php echo htmlspecialchars($r['subject_name'] ?? 'Daily'); ?></td>
<td><span class="status <?php echo $r['status'] === 'absent' ? 'danger' : 'success'; ?>"><?php echo ucfirst($r['status']); ?></span></td>
</tr>
<?php } } ?>
</tbody>
</table>
</div>
</div>
<?php } ?>
<?php if (empty($children)) { ?><p>No linked children found.</p><?php } ?>
</div></div></div>
<script src="../../assets/js/common.js"></script>
</body>
</html>
