<?php

require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../shared/services/StudentDashboardService.php';
require_once __DIR__ . '/../../shared/services/TimetableViewService.php';
require_once __DIR__ . '/../../shared/helpers/portal.php';

portal_require_role(['student']);
requirePermission('timetables', 'view');

$service = new StudentDashboardService($conn, (int) $_SESSION['user']['id']);
$student = $service->getStudent();
$timetableService = new TimetableViewService($conn);
$grid = $timetableService->buildClassSectionGrid(
    (int) ($student['class_id'] ?? 0),
    (int) ($student['section_id'] ?? 0)
);

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Timetable | MindMerge</title>
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
<div class="page-header">
<div>
<h1>My Timetable</h1>
<p><?php echo htmlspecialchars(($student['class_name'] ?? '') . ' - ' . ($student['section_name'] ?? '')); ?></p>
<?php if ($grid) { ?><p class="text-muted"><?php echo htmlspecialchars($grid['subtitle']); ?></p><?php } ?>
</div>
</div>

<div class="dashboard-section">
<div class="section-header"><h2>Weekly Schedule</h2></div>
<?php if (!$grid) { ?>
<div class="empty-state">
<i class="fa-solid fa-calendar-days"></i>
<h3>No timetable available</h3>
<p>Your class timetable has not been published yet.</p>
</div>
<?php } else { ?>
<?php echo $timetableService->renderGrid($grid); ?>
<?php } ?>
</div>

</div></div></div>
<script src="../../assets/js/common.js"></script>
</body>
</html>
