<?php

require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../shared/services/ResultsService.php';
require_once __DIR__ . '/../../shared/services/TeacherScopeService.php';
require_once __DIR__ . '/../../shared/helpers/portal.php';

portal_require_role(['teacher']);
requirePermission('results', 'view');

$scope = new TeacherScopeService($conn, (int) $_SESSION['user']['id']);
$service = new ResultsService($conn);
$results = $service->getResultsForTeacher($scope->getTeacherId());

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Result Sheets | MindMerge</title>
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
<div class="page-header"><div><h1>Result Sheets</h1><p>View and enter marks for your assigned classes.</p></div></div>
<div class="dashboard-section">
<div class="table-responsive">
<table class="custom-table">
<thead><tr><th>Exam ID</th><th>Exam</th><th>Class</th><th>Section</th><th>Date</th><th>Status</th><th>Actions</th></tr></thead>
<tbody>
<?php if (empty($results)) { ?>
<tr><td colspan="7"><div class="empty-state"><i class="fa-solid fa-square-poll-vertical"></i><h3>No result sheets</h3><p>Exam result sheets for your classes will appear here.</p></div></td></tr>
<?php } else { foreach ($results as $r) { ?>
<tr>
<td><strong><?php echo htmlspecialchars($r['exam_code'] ?? 'Legacy'); ?></strong></td>
<td><?php echo htmlspecialchars($r['exam_name'] ?? 'Result Sheet'); ?></td>
<td><?php echo htmlspecialchars($r['class_name']); ?></td>
<td><?php echo htmlspecialchars($r['section_name']); ?></td>
<td><?php echo !empty($r['exam_date']) ? date('M j, Y', strtotime($r['exam_date'])) : htmlspecialchars($r['academic_year']); ?></td>
<td><span class="status <?php echo $r['status'] === 'published' ? 'success' : 'warning'; ?>"><?php echo ucfirst($r['status']); ?></span></td>
<td>
<a href="../../results/view.php?id=<?php echo (int) $r['result_id']; ?>" class="btn btn-sm"><i class="fa-solid fa-eye"></i></a>
<?php if (canCreate('results') || canEdit('results')) { ?>
<a href="../../results/mark.php?id=<?php echo (int) $r['result_id']; ?>" class="btn btn-sm"><i class="fa-solid fa-pen"></i></a>
<?php } ?>
</td>
</tr>
<?php } } ?>
</tbody>
</table>
</div>
</div>
</div></div></div>
<script src="../../assets/js/common.js"></script>
</body>
</html>
