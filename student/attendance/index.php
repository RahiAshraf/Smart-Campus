<?php

require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../shared/services/StudentDashboardService.php';
require_once __DIR__ . '/../../shared/helpers/portal.php';

portal_require_role(['student']);
requirePermission('attendance', 'view');

$service = new StudentDashboardService($conn, (int) $_SESSION['user']['id']);
$records = $service->getRecentAttendance(30);
$stats = $service->getStats();

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Attendance | MindMerge</title>
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
<div class="page-header"><div><h1>My Attendance</h1><p>Overall attendance: <strong><?php echo $stats['attendance_pct']; ?>%</strong></p></div></div>
<div class="table-responsive">
<table class="custom-table">
<thead><tr><th>Date</th><th>Subject</th><th>Period</th><th>Status</th></tr></thead>
<tbody>
<?php if (empty($records)) { ?><tr><td colspan="4">No attendance records yet.</td></tr><?php } else { foreach ($records as $r) { ?>
<tr>
<td><?php echo date('M j, Y', strtotime($r['attendance_date'])); ?></td>
<td><?php echo htmlspecialchars($r['subject_name'] ?? 'Daily'); ?></td>
<td><?php echo htmlspecialchars($r['period_name'] ?? '-'); ?></td>
<td><span class="status <?php echo $r['status'] === 'absent' ? 'danger' : 'success'; ?>"><?php echo ucfirst($r['status']); ?></span></td>
</tr>
<?php } } ?>
</tbody>
</table>
</div>
</div></div></div>
<script src="../../assets/js/common.js"></script>
</body>
</html>
