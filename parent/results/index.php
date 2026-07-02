<?php

require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../shared/services/ParentDashboardService.php';
require_once __DIR__ . '/../../shared/services/ResultsService.php';
require_once __DIR__ . '/../../shared/helpers/portal.php';

portal_require_role(['parent']);
requirePermission('results', 'view');

$parentService = new ParentDashboardService($conn, (int) $_SESSION['user']['id']);
$resultsService = new ResultsService($conn);
$rows = [];

foreach ($parentService->getChildren() as $child) {
    foreach ($resultsService->getStudentResults((int) $child['id']) as $result) {
        $result['child_name'] = $child['full_name'];
        $rows[] = $result;
    }
}

function e($value): string
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
}

function result_date($value, $fallback): string
{
    return $value ? date('M j, Y', strtotime($value)) : e($fallback ?: '-');
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Child Results | MindMerge</title>
<link rel="stylesheet" href="../../assets/css/global.css">
<link rel="stylesheet" href="../../assets/css/layout.css">
<link rel="stylesheet" href="../../assets/css/components.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="../../assets/css/material-theme.css">
</head>
<body>
<div class="app-layout">
<?php include('../../partials/sidebar.php'); ?>
<div class="main-content">
<?php include('../../partials/topbar.php'); ?>
<div class="page-content">
<div class="page-header"><div><h1>Child Results</h1><p>Published marksheets for your children.</p></div></div>
<div class="dashboard-section">
<div class="table-responsive">
<table class="custom-table">
<thead><tr><th>Child</th><th>Exam ID</th><th>Exam</th><th>Date</th><th>Subject</th><th>Marks</th><th>Percentage</th><th>Grade</th></tr></thead>
<tbody>
<?php if (empty($rows)) { ?>
<tr><td colspan="8"><div class="empty-state"><i class="fa-solid fa-chart-column"></i><h3>No published results</h3><p>Published child exam results will appear here.</p></div></td></tr>
<?php } else { foreach ($rows as $row) { ?>
<tr>
<td><?php echo e($row['child_name'] ?? 'Child'); ?></td>
<td><strong><?php echo e($row['exam_code'] ?? 'Legacy'); ?></strong></td>
<td><?php echo e($row['exam_name'] ?? 'Result Sheet'); ?></td>
<td><?php echo result_date($row['exam_date'] ?? null, $row['academic_year'] ?? ''); ?></td>
<td><?php echo e($row['subject_name'] ?? '-'); ?></td>
<td><?php echo e($row['marks_obtained'] ?? $row['total_marks'] ?? '0'); ?>/<?php echo e($row['max_marks'] ?? '-'); ?></td>
<td><?php echo isset($row['percentage']) ? number_format((float) $row['percentage'], 2) . '%' : '-'; ?></td>
<td><?php echo e($row['grade'] ?? '-'); ?></td>
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
