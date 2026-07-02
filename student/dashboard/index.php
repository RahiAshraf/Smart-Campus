<?php

require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/permissions.php';
require_once __DIR__ . '/../../config/finance.php';
require_once __DIR__ . '/../../shared/services/StudentDashboardService.php';
require_once __DIR__ . '/../../shared/helpers/portal.php';

portal_require_role(['student']);
finance_ensure_tables($conn);

$service = new StudentDashboardService($conn, (int) $_SESSION['user']['id']);
$student = $service->getStudent();
$stats = $service->getStats();
$fees = $service->getFeeOverview();
$feeDetails = $service->getFeeDetails();
$feePayments = $service->getRecentFeePayments();
$attendanceTrend = $service->getAttendanceTrend();
$resultTrend = $service->getResultPercentageTrend();
$todaySchedule = $service->getTodayTimetable();
$attendanceStatus = $service->getAttendanceStatus();
$recentAttendance = $service->getRecentAttendance(6);
$upcomingExams = $service->getUpcomingExams();
$recentResults = $service->getRecentResults();
$notifications = $service->getRecentNotifications();
$transport = $service->getTransportSummary();
$user_name = portal_greeting_name();

function e($value): string
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
}

function money($value): string
{
    return finance_money($value);
}

function short_date($value): string
{
    return $value ? date('M j, Y', strtotime($value)) : 'Not set';
}

function short_time($value): string
{
    return $value ? date('g:i A', strtotime($value)) : 'All day';
}

$feePaidPct = $fees['assigned'] > 0 ? round(($fees['paid'] / $fees['assigned']) * 100, 2) : 0;

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Student Dashboard | MindMerge</title>
<link rel="stylesheet" href="../../assets/css/global.css">
<link rel="stylesheet" href="../../assets/css/layout.css">
<link rel="stylesheet" href="../../assets/css/components.css">
<link rel="stylesheet" href="../../assets/css/dashboard-components.css">
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

<div class="dashboard-page">

<section class="dashboard-hero">
<div class="hero-content">
<h1 class="hero-title">My Learning Portal</h1>
<p class="hero-description">Welcome, <strong><?php echo e($user_name); ?></strong>. Track attendance, fees, timetable, exams, results, transport, and campus updates from one place.</p>
<div class="hero-meta">
<span class="hero-badge"><i class="fa-solid fa-id-card"></i><?php echo e($student['student_id'] ?? 'Student'); ?></span>
<span class="hero-badge"><i class="fa-solid fa-school"></i><?php echo e(trim(($student['class_name'] ?? '') . ' ' . ($student['section_name'] ?? ''))); ?></span>
<span class="hero-badge"><i class="fa-solid fa-calendar-check"></i><?php echo number_format($stats['attendance_pct'], 2); ?>% attendance</span>
</div>
</div>
<div class="hero-illustration"><i class="fa-solid fa-user-graduate"></i></div>
</section>

<section class="stats-grid">
<div class="stat-card"><div class="stat-icon"><i class="fa-solid fa-calendar-check"></i></div><div class="stat-content"><span class="stat-label">Attendance</span><span class="stat-value"><?php echo number_format($stats['attendance_pct'], 2); ?>%</span><span class="stat-description"><?php echo e($attendanceStatus); ?></span></div></div>
<div class="stat-card"><div class="stat-icon"><i class="fa-solid fa-wallet"></i></div><div class="stat-content"><span class="stat-label">Fee Balance</span><span class="stat-value"><?php echo money($fees['balance']); ?></span><span class="stat-description"><?php echo number_format($feePaidPct, 2); ?>% paid</span></div></div>
<div class="stat-card"><div class="stat-icon"><i class="fa-solid fa-file-lines"></i></div><div class="stat-content"><span class="stat-label">Upcoming Exams</span><span class="stat-value"><?php echo number_format($stats['upcoming_exams']); ?></span><span class="stat-description">For your class section</span></div></div>
<div class="stat-card"><div class="stat-icon"><i class="fa-solid fa-book"></i></div><div class="stat-content"><span class="stat-label">Subjects</span><span class="stat-value"><?php echo number_format($stats['subjects_count']); ?></span><span class="stat-description">In active timetable</span></div></div>
<div class="stat-card"><div class="stat-icon"><i class="fa-solid fa-square-poll-vertical"></i></div><div class="stat-content"><span class="stat-label">Latest Result</span><span class="stat-value"><?php echo $stats['latest_result_pct'] !== null ? number_format($stats['latest_result_pct'], 2) . '%' : '-'; ?></span><span class="stat-description">Published exam score</span></div></div>
<div class="stat-card"><div class="stat-icon"><i class="fa-solid fa-bell"></i></div><div class="stat-content"><span class="stat-label">Notifications</span><span class="stat-value"><?php echo number_format($stats['unread_notifications']); ?></span><span class="stat-description">Unread alerts</span></div></div>
</section>

<section class="dashboard-widget">
<div class="dashboard-widget-header"><div class="dashboard-widget-title"><i class="fa-solid fa-bolt"></i><div><h4>Quick Actions</h4><span>Student tools and shortcuts</span></div></div></div>
<div class="dashboard-widget-body">
<div class="quick-actions">
<a href="../timetable/index.php" class="quick-action-card"><span class="quick-action-icon"><i class="fa-solid fa-calendar-days"></i></span><span class="quick-action-title">Timetable</span><span class="quick-action-desc">Today and weekly classes</span></a>
<a href="../../exams/index.php" class="quick-action-card"><span class="quick-action-icon"><i class="fa-solid fa-file-lines"></i></span><span class="quick-action-title">Exams</span><span class="quick-action-desc">Upcoming assessments</span></a>
<a href="../results/index.php" class="quick-action-card"><span class="quick-action-icon"><i class="fa-solid fa-chart-column"></i></span><span class="quick-action-title">Results</span><span class="quick-action-desc">Published marks</span></a>
<a href="../attendance/index.php" class="quick-action-card"><span class="quick-action-icon"><i class="fa-solid fa-calendar-check"></i></span><span class="quick-action-title">Attendance</span><span class="quick-action-desc">Daily status</span></a>
<a href="../transport/index.php" class="quick-action-card"><span class="quick-action-icon"><i class="fa-solid fa-bus"></i></span><span class="quick-action-title">Transport</span><span class="quick-action-desc">Bus and route</span></a>
<a href="../digital-id/index.php" class="quick-action-card"><span class="quick-action-icon"><i class="fa-solid fa-id-card"></i></span><span class="quick-action-title">Digital ID</span><span class="quick-action-desc">Student identity</span></a>
</div>
</div>
</section>

<section class="dashboard-grid-3">
<div class="profile-widget">
<div class="profile-avatar"><i class="fa-solid fa-user-graduate"></i></div>
<h3 class="profile-name"><?php echo e($user_name); ?></h3>
<div class="profile-role"><?php echo e($student['student_id'] ?? 'Student'); ?></div>
<div class="profile-meta">
<div class="profile-meta-item"><span>Class</span><strong><?php echo e($student['class_name'] ?? '-'); ?></strong></div>
<div class="profile-meta-item"><span>Section</span><strong><?php echo e($student['section_name'] ?? '-'); ?></strong></div>
<div class="profile-meta-item"><span>Guardian Phone</span><strong><?php echo e($student['parent_phone'] ?: '-'); ?></strong></div>
</div>
</div>
<div class="info-panel">
<h3 class="info-panel-title"><i class="fa-solid fa-wallet"></i>Fees</h3>
<div class="progress-group">
<div class="progress-item"><div class="progress-header"><span class="progress-title">Paid</span><span class="progress-value"><?php echo money($fees['paid']); ?></span></div><div class="progress"><span class="progress-bar progress-success" style="width:<?php echo min(100, $feePaidPct); ?>%"></span></div></div>
</div>
<div class="info-list" style="margin-top:18px;">
<div class="info-list-item"><span>Assigned</span><strong><?php echo money($fees['assigned']); ?></strong></div>
<div class="info-list-item"><span>Balance</span><strong><?php echo money($fees['balance']); ?></strong></div>
<div class="info-list-item"><span>Status</span><strong><?php echo e(ucfirst($fees['status'])); ?></strong></div>
</div>
</div>
<div class="info-panel">
<h3 class="info-panel-title"><i class="fa-solid fa-bus"></i>Transport</h3>
<div class="info-list">
<?php if (!$transport) { ?><div class="info-list-item"><span>Transport Not Assigned</span><strong>-</strong></div><?php } else { ?>
<div class="info-list-item"><span>Bus</span><strong><?php echo e($transport['bus_number']); ?></strong></div>
<div class="info-list-item"><span>Route</span><strong><?php echo e($transport['route_name'] ?: '-'); ?></strong></div>
<div class="info-list-item"><span>Stop</span><strong><?php echo e($transport['stop_name'] ?: '-'); ?></strong></div>
<div class="info-list-item"><span>Status</span><strong><?php echo e(ucfirst($transport['status'] ?? 'offline')); ?></strong></div>
<?php } ?>
</div>
</div>
</section>

<section class="dashboard-grid-equal">
<div class="dashboard-widget">
<div class="dashboard-widget-header"><div class="dashboard-widget-title"><i class="fa-solid fa-file-invoice-dollar"></i><div><h4>Fee Details</h4><span>Assigned fees and balances</span></div></div></div>
<div class="dashboard-widget-body dashboard-table-wrap">
<table class="dashboard-table"><thead><tr><th>Fee</th><th>Due</th><th>Balance</th><th>Status</th></tr></thead><tbody>
<?php if (empty($feeDetails)) { ?><tr><td colspan="4">No fees assigned.</td></tr><?php } ?>
<?php foreach ($feeDetails as $fee) { ?><tr><td><strong><?php echo e($fee['fee_name']); ?></strong><br><span><?php echo e($fee['academic_year']); ?></span></td><td><?php echo short_date($fee['due_date']); ?></td><td><?php echo money($fee['balance_amount']); ?><br><span>Paid <?php echo money($fee['paid_amount']); ?></span></td><td><span class="dashboard-badge <?php echo $fee['payment_status'] === 'paid' ? 'badge-success' : ($fee['payment_status'] === 'partial' ? 'badge-warning' : 'badge-danger'); ?>"><?php echo e(ucfirst($fee['payment_status'])); ?></span></td></tr><?php } ?>
</tbody></table>
</div>
</div>
<div class="dashboard-widget">
<div class="dashboard-widget-header"><div class="dashboard-widget-title"><i class="fa-solid fa-receipt"></i><div><h4>Fee Payments</h4><span>Recent receipts</span></div></div></div>
<div class="dashboard-widget-body dashboard-table-wrap">
<table class="dashboard-table"><thead><tr><th>Receipt</th><th>Fee</th><th>Amount</th></tr></thead><tbody>
<?php if (empty($feePayments)) { ?><tr><td colspan="3">No fee payments yet.</td></tr><?php } ?>
<?php foreach ($feePayments as $payment) { ?><tr><td><strong><?php echo e($payment['receipt_no']); ?></strong><br><span><?php echo short_date($payment['payment_date']); ?></span></td><td><?php echo e($payment['fee_name']); ?><br><span><?php echo e(ucwords(str_replace('_', ' ', $payment['payment_method']))); ?></span></td><td><?php echo money($payment['amount_paid']); ?></td></tr><?php } ?>
</tbody></table>
</div>
</div>
</section>

<section class="dashboard-grid-equal">
<div class="dashboard-widget">
<div class="dashboard-widget-header"><div class="dashboard-widget-title"><i class="fa-solid fa-clock"></i><div><h4>Timetable</h4><span>Today's classes</span></div></div></div>
<div class="dashboard-widget-body dashboard-table-wrap">
<table class="dashboard-table"><thead><tr><th>Subject</th><th>Teacher</th><th>Time</th><th>Room</th></tr></thead><tbody>
<?php if (empty($todaySchedule)) { ?><tr><td colspan="4">No classes today.</td></tr><?php } ?>
<?php foreach ($todaySchedule as $item) { ?><tr><td><strong><?php echo e($item['subject_name']); ?></strong><br><span><?php echo e($item['period_name']); ?></span></td><td><?php echo e($item['teacher_name'] ?: '-'); ?></td><td><?php echo short_time($item['start_time']); ?> - <?php echo short_time($item['end_time']); ?></td><td><?php echo e($item['room_no'] ?: '-'); ?></td></tr><?php } ?>
</tbody></table>
</div>
</div>
</section>

<section class="dashboard-grid-equal">
<div class="chart-card"><div class="chart-header"><div class="chart-title"><h3>Attendance</h3><span>Daily attendance trend</span></div></div><div class="chart-body"><canvas id="attendanceTrendChart"></canvas></div></div>
<div class="chart-card"><div class="chart-header"><div class="chart-title"><h3>Performance</h3><span>Published result percentage</span></div></div><div class="chart-body"><canvas id="resultsTrendChart"></canvas></div></div>
</section>

<section class="dashboard-grid-3">
<div class="dashboard-widget">
<div class="dashboard-widget-header"><div class="dashboard-widget-title"><i class="fa-solid fa-calendar-check"></i><div><h4>Recent Attendance</h4><span>Latest attendance records</span></div></div></div>
<div class="dashboard-widget-body dashboard-table-wrap">
<table class="dashboard-table"><thead><tr><th>Date</th><th>Period</th><th>Status</th></tr></thead><tbody>
<?php if (empty($recentAttendance)) { ?><tr><td colspan="3">No attendance records yet.</td></tr><?php } ?>
<?php foreach ($recentAttendance as $item) { $present = ($item['status'] ?? '') !== 'absent'; ?><tr><td><?php echo short_date($item['attendance_date']); ?></td><td><?php echo e($item['subject_name'] ?: $item['period_name'] ?: 'Daily'); ?></td><td><span class="dashboard-badge <?php echo $present ? 'badge-success' : 'badge-danger'; ?>"><?php echo e(ucfirst(str_replace('_', ' ', $item['status']))); ?></span></td></tr><?php } ?>
</tbody></table>
</div>
</div>
<div class="dashboard-widget">
<div class="dashboard-widget-header"><div class="dashboard-widget-title"><i class="fa-solid fa-file-lines"></i><div><h4>Upcoming Exams</h4><span>Exam schedule</span></div></div></div>
<div class="dashboard-widget-body">
<div class="event-list">
<?php if (empty($upcomingExams)) { ?><div class="dashboard-empty"><i class="fa-solid fa-calendar-xmark"></i><p>No upcoming exams scheduled.</p></div><?php } ?>
<?php foreach ($upcomingExams as $exam) { ?><div class="event-card"><div class="event-date"><span class="event-day"><?php echo date('j', strtotime($exam['exam_date'])); ?></span><span class="event-month"><?php echo date('M', strtotime($exam['exam_date'])); ?></span></div><div class="event-content"><h4 class="event-title"><?php echo e($exam['exam_name']); ?></h4><div class="event-description"><?php echo e($exam['subject_name'] ?: ucwords(str_replace('_', ' ', $exam['exam_type']))); ?></div><div class="event-meta"><span><?php echo short_time($exam['start_time']); ?></span></div></div></div><?php } ?>
</div>
</div>
</div>
<div class="dashboard-widget">
<div class="dashboard-widget-header"><div class="dashboard-widget-title"><i class="fa-solid fa-square-poll-vertical"></i><div><h4>Results</h4><span>Recent published marks</span></div></div></div>
<div class="dashboard-widget-body dashboard-table-wrap">
<table class="dashboard-table"><thead><tr><th>Exam</th><th>Marks</th><th>Status</th></tr></thead><tbody>
<?php if (empty($recentResults)) { ?><tr><td colspan="3">No Results Available</td></tr><?php } ?>
<?php foreach ($recentResults as $result) { $pct = ((float) $result['total_marks'] > 0) ? round(((float) $result['marks_obtained'] / (float) $result['total_marks']) * 100, 2) : 0; ?><tr><td><strong><?php echo e($result['exam_name']); ?></strong><br><span><?php echo short_date($result['exam_date']); ?></span></td><td><?php echo e($result['marks_obtained']); ?>/<?php echo e($result['total_marks']); ?><br><span><?php echo number_format($pct, 2); ?>%</span></td><td><span class="dashboard-badge <?php echo ($result['status'] ?? '') === 'published' ? 'badge-success' : 'badge-warning'; ?>"><?php echo e(ucfirst($result['status'])); ?></span></td></tr><?php } ?>
</tbody></table>
</div>
</div>
</section>

<section class="dashboard-widget">
<div class="dashboard-widget-header"><div class="dashboard-widget-title"><i class="fa-solid fa-bell"></i><div><h4>Notifications</h4><span>Recent campus updates</span></div></div></div>
<div class="dashboard-widget-body">
<div class="notification-list">
<?php if (empty($notifications)) { ?><div class="dashboard-empty"><i class="fa-solid fa-bell-slash"></i><p>No notifications available.</p></div><?php } ?>
<?php foreach ($notifications as $n) { ?><div class="notification-item"><div class="notification-icon"><i class="fa-solid fa-bullhorn"></i></div><div class="notification-content"><h4 class="notification-title"><?php echo e($n['title']); ?></h4><div class="notification-message"><?php echo e($n['message']); ?></div><div class="notification-meta"><span><?php echo e(ucfirst($n['notification_type'] ?? 'general')); ?></span><span><?php echo short_date($n['created_at']); ?></span></div></div></div><?php } ?>
</div>
</div>
</section>

</div>
</div>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="../../assets/js/common.js"></script>
<script>
Chart.defaults.font.family = "'Inter', 'Segoe UI', sans-serif";
Chart.defaults.color = '#64748b';
const chartDefaults = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: { legend: { labels: { usePointStyle: true, boxWidth: 8 } } },
    scales: {
        x: { grid: { display: false } },
        y: { beginAtZero: true, grid: { color: 'rgba(148, 163, 184, 0.18)' } }
    }
};
new Chart(document.getElementById('attendanceTrendChart'), {
    type: 'line',
    data: { labels: <?php echo json_encode($attendanceTrend['labels']); ?>, datasets: [{ label: 'Attendance %', data: <?php echo json_encode($attendanceTrend['values']); ?>, borderColor: '#2563eb', backgroundColor: 'rgba(37,99,235,0.12)', fill: true, spanGaps: true, tension: 0.35 }] },
    options: { ...chartDefaults, scales: { ...chartDefaults.scales, y: { ...chartDefaults.scales.y, max: 100 } } }
});
new Chart(document.getElementById('resultsTrendChart'), {
    type: 'bar',
    data: { labels: <?php echo json_encode($resultTrend['labels']); ?>, datasets: [{ label: 'Score %', data: <?php echo json_encode($resultTrend['values']); ?>, backgroundColor: '#0f766e', borderRadius: 8 }] },
    options: { ...chartDefaults, scales: { ...chartDefaults.scales, y: { ...chartDefaults.scales.y, max: 100 } } }
});
</script>
</body>
</html>
