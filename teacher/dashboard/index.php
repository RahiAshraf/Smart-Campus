<?php

require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/permissions.php';
require_once __DIR__ . '/../../config/finance.php';
require_once __DIR__ . '/../../shared/services/TeacherScopeService.php';
require_once __DIR__ . '/../../shared/services/TeacherDashboardService.php';
require_once __DIR__ . '/../../shared/helpers/portal.php';

portal_require_role(['teacher']);
finance_ensure_tables($conn);

$scope = new TeacherScopeService($conn, (int) $_SESSION['user']['id']);
$service = new TeacherDashboardService($conn, $scope);
$stats = $service->getStats();
$insights = $service->getInsights();
$schedule = $service->getTodaySchedule();
$periods = $service->getCurrentAndNextPeriod();
$markingTrend = $service->getAttendanceMarkingTrend();
$studentTrend = $service->getStudentAttendanceTrend();
$performance = $service->getStudentPerformance();
$assignments = $service->getAssignmentOverview();
$salary = $service->getSalarySummary();
$upcomingExams = $service->getUpcomingExams();
$notifications = $service->getRecentNotifications();
$teacher = $scope->getTeacher();
$user_name = portal_greeting_name();

function e($value): string
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
}

function short_date($value): string
{
    return $value ? date('M j, Y', strtotime($value)) : 'Not set';
}

function short_time($value): string
{
    return $value ? date('g:i A', strtotime($value)) : 'All day';
}

function money($value): string
{
    return finance_money($value);
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Teacher Dashboard | MindMerge</title>
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
<h1 class="hero-title">Teacher Workspace</h1>
<p class="hero-description"><?php echo portal_time_greeting(); ?>, <strong><?php echo e($user_name); ?></strong>. Your timetable, attendance work, assigned sections, exams, and student performance are ready for today.</p>
<div class="hero-meta">
<span class="hero-badge"><i class="fa-solid fa-id-badge"></i><?php echo e($teacher['teacher_id'] ?? 'Teacher'); ?></span>
<span class="hero-badge"><i class="fa-solid fa-book-open"></i><?php echo number_format($stats['assigned_subjects']); ?> subjects</span>
<span class="hero-badge"><i class="fa-solid fa-users"></i><?php echo number_format($stats['assigned_students']); ?> students</span>
</div>
</div>
<div class="hero-illustration"><i class="fa-solid fa-chalkboard-user"></i></div>
</section>

<section class="stats-grid">
<div class="stat-card"><div class="stat-icon"><i class="fa-solid fa-calendar-day"></i></div><div class="stat-content"><span class="stat-label">Today's Classes</span><span class="stat-value"><?php echo number_format($stats['today_classes']); ?></span><span class="stat-description">Scheduled periods today</span></div></div>
<div class="stat-card"><div class="stat-icon"><i class="fa-solid fa-hourglass-half"></i></div><div class="stat-content"><span class="stat-label">Pending Attendance</span><span class="stat-value"><?php echo number_format($stats['pending_attendance']); ?></span><span class="stat-description">Class sections to mark</span></div></div>
<div class="stat-card"><div class="stat-icon"><i class="fa-solid fa-circle-check"></i></div><div class="stat-content"><span class="stat-label">Completed</span><span class="stat-value"><?php echo number_format($stats['completed_attendance']); ?></span><span class="stat-description"><?php echo number_format($insights['attendance_completion_pct'], 2); ?>% completion</span></div></div>
<div class="stat-card"><div class="stat-icon"><i class="fa-solid fa-file-lines"></i></div><div class="stat-content"><span class="stat-label">Upcoming Exams</span><span class="stat-value"><?php echo number_format($stats['upcoming_exams']); ?></span><span class="stat-description">Assigned classes</span></div></div>
<div class="stat-card"><div class="stat-icon"><i class="fa-solid fa-wallet"></i></div><div class="stat-content"><span class="stat-label">Salary</span><span class="stat-value"><?php echo money($salary['monthly_salary']); ?></span><span class="stat-description"><?php echo e(ucfirst($salary['status'])); ?> payroll</span></div></div>
<div class="stat-card"><div class="stat-icon"><i class="fa-solid fa-bell"></i></div><div class="stat-content"><span class="stat-label">Notifications</span><span class="stat-value"><?php echo number_format($stats['unread_notifications']); ?></span><span class="stat-description">Unread alerts</span></div></div>
</section>

<section class="dashboard-widget">
<div class="dashboard-widget-header">
<div class="dashboard-widget-title"><i class="fa-solid fa-bolt"></i><div><h4>Quick Actions</h4><span>Teaching tools and daily workflows</span></div></div>
</div>
<div class="dashboard-widget-body">
<div class="quick-actions">
<?php if (canCreate('attendance')) { ?><a href="../attendance/index.php" class="quick-action-card"><span class="quick-action-icon"><i class="fa-solid fa-clipboard-check"></i></span><span class="quick-action-title">Take Attendance</span><span class="quick-action-desc">Mark class presence</span></a><?php } ?>
<?php if (canView('timetables')) { ?><a href="../timetable/index.php" class="quick-action-card"><span class="quick-action-icon"><i class="fa-solid fa-calendar-days"></i></span><span class="quick-action-title">Timetable</span><span class="quick-action-desc">View schedule</span></a><?php } ?>
<?php if (canView('students')) { ?><a href="../students/index.php" class="quick-action-card"><span class="quick-action-icon"><i class="fa-solid fa-user-graduate"></i></span><span class="quick-action-title">Students</span><span class="quick-action-desc">Assigned learners</span></a><?php } ?>
<?php if (canView('exams')) { ?><a href="../../exams/index.php" class="quick-action-card"><span class="quick-action-icon"><i class="fa-solid fa-file-lines"></i></span><span class="quick-action-title">Exams</span><span class="quick-action-desc">Assessment plans</span></a><?php } ?>
<?php if (canView('results')) { ?><a href="../results/index.php" class="quick-action-card"><span class="quick-action-icon"><i class="fa-solid fa-square-poll-vertical"></i></span><span class="quick-action-title">Results</span><span class="quick-action-desc">Marks and reports</span></a><?php } ?>
<a href="../../notifications/index.php" class="quick-action-card"><span class="quick-action-icon"><i class="fa-solid fa-bell"></i></span><span class="quick-action-title">Notifications</span><span class="quick-action-desc">School updates</span></a>
</div>
</div>
</section>

<section class="dashboard-grid-lg">
<div class="dashboard-widget">
<div class="dashboard-widget-header"><div class="dashboard-widget-title"><i class="fa-solid fa-clock"></i><div><h4>Today's Timetable</h4><span>Current and upcoming teaching periods</span></div></div></div>
<div class="dashboard-widget-body">
<?php if ($periods['current']) { ?><div class="notification-item notification-unread"><div class="notification-icon"><i class="fa-solid fa-play"></i></div><div class="notification-content"><h4 class="notification-title">Current: <?php echo e($periods['current']['subject_name']); ?></h4><div class="notification-meta"><span><?php echo e($periods['current']['class_name'] . ' ' . $periods['current']['section_name']); ?></span><span><?php echo short_time($periods['current']['start_time']); ?> - <?php echo short_time($periods['current']['end_time']); ?></span></div></div></div><?php } ?>
<?php if ($periods['next']) { ?><div class="notification-item"><div class="notification-icon"><i class="fa-solid fa-forward-step"></i></div><div class="notification-content"><h4 class="notification-title">Next: <?php echo e($periods['next']['subject_name']); ?></h4><div class="notification-meta"><span><?php echo e($periods['next']['class_name'] . ' ' . $periods['next']['section_name']); ?></span><span><?php echo short_time($periods['next']['start_time']); ?></span></div></div></div><?php } ?>
<div class="dashboard-table-wrap" style="margin-top:18px;">
<table class="dashboard-table"><thead><tr><th>Period</th><th>Class</th><th>Time</th><th>Room</th></tr></thead><tbody>
<?php if (empty($schedule)) { ?><tr><td colspan="4">No classes scheduled for today.</td></tr><?php } ?>
<?php foreach ($schedule as $item) { ?><tr><td><strong><?php echo e($item['subject_name']); ?></strong><br><span><?php echo e($item['period_name']); ?></span></td><td><?php echo e($item['class_name'] . ' ' . $item['section_name']); ?></td><td><?php echo short_time($item['start_time']); ?> - <?php echo short_time($item['end_time']); ?></td><td><?php echo e($item['room_no'] ?: 'Not set'); ?></td></tr><?php } ?>
</tbody></table>
</div>
</div>
</div>
</section>

<section class="dashboard-grid-equal">
<div class="chart-card"><div class="chart-header"><div class="chart-title"><h3>Attendance</h3><span>Daily marking volume</span></div></div><div class="chart-body"><canvas id="markingTrendChart"></canvas></div></div>
<div class="chart-card"><div class="chart-header"><div class="chart-title"><h3>Student Attendance</h3><span>Attendance rate across assigned classes</span></div></div><div class="chart-body"><canvas id="studentTrendChart"></canvas></div></div>
<div class="chart-card"><div class="chart-header"><div class="chart-title"><h3>Student Performance</h3><span>Average result percentage by assigned section</span></div></div><div class="chart-body"><canvas id="performanceChart"></canvas></div></div>
<div class="dashboard-widget">
<div class="dashboard-widget-header"><div class="dashboard-widget-title"><i class="fa-solid fa-triangle-exclamation"></i><div><h4>Attendance Focus</h4><span>Classes requiring attention</span></div></div></div>
<div class="dashboard-widget-body">
<div class="info-list">
<?php if (empty($insights['classes_requiring_attention'])) { ?><div class="info-list-item"><span>All assigned classes marked today</span><strong>Done</strong></div><?php } ?>
<?php foreach ($insights['classes_requiring_attention'] as $className) { ?><div class="info-list-item"><span><?php echo e($className); ?></span><strong>Pending</strong></div><?php } ?>
</div>
</div>
</div>
</section>

<section class="dashboard-grid-3">
<div class="info-panel">
<h3 class="info-panel-title"><i class="fa-solid fa-money-check-dollar"></i>Salary Details</h3>
<div class="info-list">
<div class="info-list-item"><span>Monthly Salary</span><strong><?php echo money($salary['monthly_salary']); ?></strong></div>
<div class="info-list-item"><span>Paid This Month</span><strong><?php echo money($salary['paid_this_month']); ?></strong></div>
<div class="info-list-item"><span>Status</span><strong><?php echo e(ucfirst($salary['status'])); ?></strong></div>
<?php if (!empty($salary['last_payment'])) { ?>
<div class="info-list-item"><span>Last Payment</span><strong><?php echo money($salary['last_payment']['amount_paid']); ?></strong></div>
<div class="info-list-item"><span>Last Receipt</span><strong><?php echo e($salary['last_payment']['receipt_no']); ?></strong></div>
<?php } ?>
</div>
</div>
<div class="dashboard-widget">
<div class="dashboard-widget-header"><div class="dashboard-widget-title"><i class="fa-solid fa-book"></i><div><h4>Assignments</h4><span>Assigned subjects and sections</span></div></div></div>
<div class="dashboard-widget-body dashboard-table-wrap">
<table class="dashboard-table"><thead><tr><th>Subject</th><th>Class</th><th>Role</th></tr></thead><tbody>
<?php if (empty($assignments)) { ?><tr><td colspan="3">No teaching assignments found.</td></tr><?php } ?>
<?php foreach ($assignments as $item) { ?><tr><td><?php echo e($item['subject_name']); ?></td><td><?php echo e($item['class_name'] . ' ' . $item['section_name']); ?></td><td><span class="dashboard-badge badge-info"><?php echo e(ucwords(str_replace('_', ' ', $item['assignment_role']))); ?></span></td></tr><?php } ?>
</tbody></table>
</div>
</div>
<div class="dashboard-widget">
<div class="dashboard-widget-header"><div class="dashboard-widget-title"><i class="fa-solid fa-file-lines"></i><div><h4>Exams</h4><span>Upcoming assessments</span></div></div></div>
<div class="dashboard-widget-body">
<div class="event-list">
<?php if (empty($upcomingExams)) { ?><div class="dashboard-empty"><i class="fa-solid fa-calendar-xmark"></i><p>No upcoming exams for assigned classes.</p></div><?php } ?>
<?php foreach ($upcomingExams as $exam) { ?><div class="event-card"><div class="event-date"><span class="event-day"><?php echo date('j', strtotime($exam['exam_date'])); ?></span><span class="event-month"><?php echo date('M', strtotime($exam['exam_date'])); ?></span></div><div class="event-content"><h4 class="event-title"><?php echo e($exam['exam_name']); ?></h4><div class="event-description"><?php echo e($exam['class_name'] . ' ' . $exam['section_name']); ?></div><div class="event-meta"><span><?php echo short_time($exam['start_time']); ?></span><span><?php echo e($exam['subject_name'] ?: ucwords(str_replace('_', ' ', $exam['exam_type']))); ?></span></div></div></div><?php } ?>
</div>
</div>
</div>
<div class="dashboard-widget">
<div class="dashboard-widget-header"><div class="dashboard-widget-title"><i class="fa-solid fa-bell"></i><div><h4>Notifications</h4><span>Latest school updates</span></div></div></div>
<div class="dashboard-widget-body">
<div class="notification-list">
<?php if (empty($notifications)) { ?><div class="dashboard-empty"><i class="fa-solid fa-bell-slash"></i><p>No notifications available.</p></div><?php } ?>
<?php foreach ($notifications as $n) { ?><div class="notification-item"><div class="notification-icon"><i class="fa-solid fa-bullhorn"></i></div><div class="notification-content"><h4 class="notification-title"><?php echo e($n['title']); ?></h4><div class="notification-message"><?php echo e($n['message']); ?></div><div class="notification-meta"><span><?php echo e(ucfirst($n['notification_type'] ?? 'general')); ?></span><span><?php echo short_date($n['created_at']); ?></span></div></div></div><?php } ?>
</div>
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
new Chart(document.getElementById('markingTrendChart'), {
    type: 'line',
    data: { labels: <?php echo json_encode($markingTrend['labels']); ?>, datasets: [{ label: 'Marked sessions', data: <?php echo json_encode($markingTrend['values']); ?>, borderColor: '#2563eb', backgroundColor: 'rgba(37,99,235,0.12)', fill: true, tension: 0.35 }] },
    options: chartDefaults
});
new Chart(document.getElementById('studentTrendChart'), {
    type: 'line',
    data: { labels: <?php echo json_encode($studentTrend['labels']); ?>, datasets: [{ label: 'Attendance %', data: <?php echo json_encode($studentTrend['values']); ?>, borderColor: '#22c55e', backgroundColor: 'rgba(34,197,94,0.12)', fill: true, tension: 0.35 }] },
    options: { ...chartDefaults, scales: { ...chartDefaults.scales, y: { ...chartDefaults.scales.y, max: 100 } } }
});
new Chart(document.getElementById('performanceChart'), {
    type: 'bar',
    data: { labels: <?php echo json_encode($performance['labels']); ?>, datasets: [{ label: 'Average %', data: <?php echo json_encode($performance['values']); ?>, backgroundColor: '#f59e0b', borderRadius: 8 }] },
    options: { ...chartDefaults, scales: { ...chartDefaults.scales, y: { ...chartDefaults.scales.y, max: 100 } } }
});
</script>
</body>
</html>
