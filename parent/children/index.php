<?php

require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../shared/services/ParentDashboardService.php';
require_once __DIR__ . '/../../shared/helpers/portal.php';

portal_require_role(['parent']);
requirePermission('students', 'view');

$service = new ParentDashboardService($conn, (int) $_SESSION['user']['id']);
$children = $service->getChildren();

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Children | MindMerge</title>
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
<div class="page-header"><div><h1>My Children</h1><p>Linked student profiles.</p></div></div>
<div class="table-responsive">
<table class="custom-table">
<thead><tr><th>Student ID</th><th>Name</th><th>Class</th><th>Section</th></tr></thead>
<tbody>
<?php if (empty($children)) { ?><tr><td colspan="4">No linked children found. Contact administration.</td></tr><?php } else { foreach ($children as $c) { ?>
<tr>
<td><?php echo htmlspecialchars($c['student_id']); ?></td>
<td><?php echo htmlspecialchars($c['full_name']); ?></td>
<td><?php echo htmlspecialchars($c['class_name']); ?></td>
<td><?php echo htmlspecialchars($c['section_name']); ?></td>
</tr>
<?php } } ?>
</tbody>
</table>
</div>
</div></div></div>
<script src="../../assets/js/common.js"></script>
</body>
</html>
