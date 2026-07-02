<?php

require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../shared/services/StudentDashboardService.php';
require_once __DIR__ . '/../../shared/services/ResultsService.php';
require_once __DIR__ . '/../../shared/helpers/portal.php';

portal_require_role(['student']);
requirePermission('results', 'view');

$studentService = new StudentDashboardService($conn, (int) $_SESSION['user']['id']);
$resultsService = new ResultsService($conn);
$student = $studentService->getStudent();
$rows = $resultsService->getStudentResults($studentService->getStudentDbId());
$trend = $resultsService->getPerformanceTrend($studentService->getStudentDbId());
$latest = $resultsService->getLatestPerformance($studentService->getStudentDbId());

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
<title>My Results | MindMerge</title>
<link rel="stylesheet" href="../../assets/css/global.css">
<link rel="stylesheet" href="../../assets/css/layout.css">
<link rel="stylesheet" href="../../assets/css/components.css">
<link rel="stylesheet" href="../../assets/css/dashboard-components.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="../../assets/css/material-theme.css">
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
<h1 class="hero-title">My Results</h1>
<p class="hero-description">Published exam results for <?php echo e($student['full_name'] ?? 'your profile'); ?>.</p>
<div class="hero-meta">
<span class="hero-badge"><i class="fa-solid fa-id-card"></i><?php echo e($student['student_id'] ?? 'Student'); ?></span>
<span class="hero-badge"><i class="fa-solid fa-school"></i><?php echo e(trim(($student['class_name'] ?? '') . ' ' . ($student['section_name'] ?? ''))); ?></span>
<span class="hero-badge"><i class="fa-solid fa-chart-line"></i><?php echo $latest ? number_format((float) $latest['percentage'], 2) . '% latest' : 'No published result'; ?></span>
</div>
</div>
<div class="hero-illustration"><i class="fa-solid fa-square-poll-vertical"></i></div>
</section>

<section class="stats-grid">
<div class="stat-card"><div class="stat-icon"><i class="fa-solid fa-file-circle-check"></i></div><div class="stat-content"><span class="stat-label">Published Results</span><span class="stat-value"><?php echo number_format(count($rows)); ?></span><span class="stat-description">Visible to student portal</span></div></div>
<div class="stat-card"><div class="stat-icon"><i class="fa-solid fa-percent"></i></div><div class="stat-content"><span class="stat-label">Latest Percentage</span><span class="stat-value"><?php echo $latest ? number_format((float) $latest['percentage'], 2) . '%' : '-'; ?></span><span class="stat-description"><?php echo e($latest['grade'] ?? 'No grade yet'); ?></span></div></div>
<div class="stat-card"><div class="stat-icon"><i class="fa-solid fa-chart-column"></i></div><div class="stat-content"><span class="stat-label">Trend Points</span><span class="stat-value"><?php echo number_format(count($trend['values'])); ?></span><span class="stat-description">Published exams plotted</span></div></div>
</section>

<section class="dashboard-widget">
<div class="dashboard-widget-header"><div class="dashboard-widget-title"><i class="fa-solid fa-list-check"></i><div><h4>Published Marks</h4><span>Scores, percentages, grades, and remarks</span></div></div></div>
<div class="dashboard-widget-body dashboard-table-wrap">
<table class="dashboard-table">
<thead><tr><th>Exam ID</th><th>Exam</th><th>Date</th><th>Subject</th><th>Marks</th><th>Percentage</th><th>Grade</th><th>Remarks</th></tr></thead>
<tbody>
<?php if (empty($rows)) { ?>
<tr><td colspan="8"><div class="dashboard-empty"><i class="fa-solid fa-chart-column"></i><h3>No published results</h3><p>Your published exam results will appear here.</p></div></td></tr>
<?php } else { foreach ($rows as $row) { ?>
<tr>
<td><strong><?php echo e($row['exam_code'] ?? 'EXM'); ?></strong></td>
<td><?php echo e($row['exam_name'] ?? 'Result'); ?></td>
<td><?php echo result_date($row['exam_date'] ?? null, $row['academic_year'] ?? ''); ?></td>
<td><?php echo e($row['subject_name'] ?? '-'); ?></td>
<td><?php echo e($row['marks_obtained'] ?? '0'); ?>/<?php echo e($row['max_marks'] ?? '-'); ?></td>
<td><?php echo number_format((float) ($row['percentage'] ?? 0), 2); ?>%</td>
<td><?php echo e($row['grade'] ?? '-'); ?></td>
<td><?php echo e($row['remarks'] ?? '-'); ?></td>
</tr>
<?php } } ?>
</tbody>
</table>
</div>
</section>

</div>
</div>
</div>
</div>
<script src="../../assets/js/common.js"></script>
</body>
</html>
