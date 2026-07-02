<?php

require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/permissions.php';
require_once __DIR__ . '/../../config/finance.php';
require_once __DIR__ . '/../../shared/services/AdminDashboardService.php';
require_once __DIR__ . '/../../shared/helpers/portal.php';

portal_require_role(['admin']);
finance_ensure_tables($conn);

$service = new AdminDashboardService($conn);
$stats = $service->getStats();
$insights = $service->getInsights();
$institution = $service->getInstitutionOverview();
$finance = $service->getFinanceOverview();
$salary = $service->getSalaryOverview();
$exams = $service->getExamOverview();
$transport = $service->getTransportOverview();
$alerts = $service->getDashboardAlerts();
$moduleHealth = $service->getModuleHealth();
$monthlyTrend = $service->getMonthlyAttendanceTrend();
$classComparison = $service->getClassAttendanceComparison();
$teacherTrend = $service->getTeacherAttendanceTrend();
$studentGrowth = $service->getStudentGrowthTrend();
$financeTrend = $service->getFinanceCollectionTrend();
$examTrend = $service->getExamPerformanceTrend();
$recentAdmissions = $service->getRecentAdmissions();
$recentPayments = $service->getRecentPayments();
$recentSalaryPayments = $service->getRecentSalaryPayments();
$recentResults = $service->getRecentResults();
$recentNotifications = $service->getRecentNotifications();
$upcomingEvents = $service->getUpcomingEvents();
$user_name = portal_greeting_name();

function e($value): string
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
}

function money($value): string
{
    return finance_money($value);
}

function pct($value): string
{
    return number_format((float) $value, 2) . '%';
}

function short_date($value): string
{
    return $value ? date('M j, Y', strtotime($value)) : 'Not set';
}

function short_time($value): string
{
    return $value ? date('g:i A', strtotime($value)) : 'All day';
}

$collectionRate = $finance['total_assigned'] > 0
    ? round(($finance['total_collected'] / $finance['total_assigned']) * 100, 2)
    : 0;
$transportCoverage = $stats['total_students'] > 0
    ? round(($transport['assigned_students'] / $stats['total_students']) * 100, 2)
    : 0;

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">


<script>
(() => {
    if (localStorage.getItem("mindmerge-theme") === "dark") {
        document.documentElement.classList.add("dark-mode");
    }
})();
</script>


<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Dashboard | MindMerge SmartCampus</title>
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
<h1 class="hero-title">Institution Command Center</h1>
<p class="hero-description"><?php echo portal_time_greeting(); ?>, <strong><?php echo e($user_name); ?></strong>. Monitor academics, attendance, fees, transport, notifications, and operating health from one enterprise dashboard.</p>
<div class="hero-meta">
<span class="hero-badge"><i class="fa-solid fa-calendar-day"></i><?php echo date('l, M j, Y'); ?></span>
<span class="hero-badge"><i class="fa-solid fa-user-shield"></i>Admin Workspace</span>
<span class="hero-badge"><i class="fa-solid fa-bell"></i><?php echo number_format($stats['unread_notifications']); ?> unread alerts</span>
</div>
</div>
<div class="hero-illustration"><i class="fa-solid fa-building-columns"></i></div>
</section>

<section class="stats-grid">
<div class="stat-card">
<div class="stat-icon"><i class="fa-solid fa-user-graduate"></i></div>
<div class="stat-content"><span class="stat-label">Students</span><span class="stat-value"><?php echo number_format($stats['total_students']); ?></span><span class="stat-description">Currently enrolled</span></div>
</div>
<div class="stat-card">
<div class="stat-icon"><i class="fa-solid fa-chalkboard-user"></i></div>
<div class="stat-content"><span class="stat-label">Teachers</span><span class="stat-value"><?php echo number_format($stats['total_teachers']); ?></span><span class="stat-description">Teaching staff</span></div>
</div>
<div class="stat-card">
<div class="stat-icon"><i class="fa-solid fa-school"></i></div>
<div class="stat-content"><span class="stat-label">Classes</span><span class="stat-value"><?php echo number_format($stats['total_classes']); ?></span><span class="stat-description"><?php echo number_format($stats['total_sections']); ?> active sections</span></div>
</div>
<div class="stat-card">
<div class="stat-icon"><i class="fa-solid fa-bus"></i></div>
<div class="stat-content"><span class="stat-label">Transport</span><span class="stat-value"><?php echo number_format($stats['total_transport']); ?></span><span class="stat-description"><?php echo number_format($transport['assigned_students']); ?> students assigned</span></div>
</div>
<div class="stat-card">
<div class="stat-icon"><i class="fa-solid fa-calendar-check"></i></div>
<div class="stat-content"><span class="stat-label">Student Attendance</span><span class="stat-value"><?php echo pct($stats['attendance_rate']); ?></span><span class="stat-description">Overall attendance rate</span></div>
</div>
<div class="stat-card">
<div class="stat-icon"><i class="fa-solid fa-wallet"></i></div>
<div class="stat-content"><span class="stat-label">Fee Pending</span><span class="stat-value"><?php echo money($stats['fee_pending']); ?></span><span class="stat-description"><?php echo pct($collectionRate); ?> collected</span></div>
</div>
<div class="stat-card">
<div class="stat-icon"><i class="fa-solid fa-user-tie"></i></div>
<div class="stat-content"><span class="stat-label">Active Payroll</span><span class="stat-value"><?php echo money($salary['active_payroll']); ?></span><span class="stat-description"><?php echo number_format($salary['active_records']); ?> active salaries</span></div>
</div>
<div class="stat-card">
<div class="stat-icon"><i class="fa-solid fa-scale-balanced"></i></div>
<div class="stat-content"><span class="stat-label">Billing Net</span><span class="stat-value"><?php echo money($finance['net_balance']); ?></span><span class="stat-description">Fees minus salaries</span></div>
</div>
</section>

<section class="dashboard-section">
<div class="dashboard-widget">
<div class="dashboard-widget-header">
<div class="dashboard-widget-title"><i class="fa-solid fa-bolt"></i><div><h4>Quick Actions</h4><span>Frequently used admin workflows</span></div></div>
</div>
<div class="dashboard-widget-body">
<div class="quick-actions">
<?php if (canCreate('students')) { ?><a href="../../students/add.php" class="quick-action-card"><span class="quick-action-icon"><i class="fa-solid fa-user-plus"></i></span><span class="quick-action-title">Add Student</span><span class="quick-action-desc">Register admission</span></a><?php } ?>
<?php if (canEdit('students')) { ?><a href="../../students/assign-parent.php" class="quick-action-card"><span class="quick-action-icon"><i class="fa-solid fa-people-roof"></i></span><span class="quick-action-title">Assign Parent</span><span class="quick-action-desc">Link family account</span></a><?php } ?>
<?php if (canCreate('teachers')) { ?><a href="../../teachers/add.php" class="quick-action-card"><span class="quick-action-icon"><i class="fa-solid fa-user-tie"></i></span><span class="quick-action-title">Add Teacher</span><span class="quick-action-desc">Create staff profile</span></a><?php } ?>
<?php if (canCreate('fees')) { ?><a href="../../fees/add.php" class="quick-action-card"><span class="quick-action-icon"><i class="fa-solid fa-money-bill-wave"></i></span><span class="quick-action-title">Add Fee</span><span class="quick-action-desc">Create fee structure</span></a><?php } ?>
<?php if (canEdit('fees')) { ?><a href="../../fees/collect.php" class="quick-action-card"><span class="quick-action-icon"><i class="fa-solid fa-receipt"></i></span><span class="quick-action-title">Collect Fees</span><span class="quick-action-desc">Post payment</span></a><?php } ?>
<?php if (canCreate('salaries')) { ?><a href="../../salaries/add.php" class="quick-action-card"><span class="quick-action-icon"><i class="fa-solid fa-hand-holding-dollar"></i></span><span class="quick-action-title">Add Salary</span><span class="quick-action-desc">Set teacher payroll</span></a><?php } ?>
<?php if (canView('billing')) { ?><a href="../../billing/index.php" class="quick-action-card"><span class="quick-action-icon"><i class="fa-solid fa-chart-line"></i></span><span class="quick-action-title">Billing</span><span class="quick-action-desc">Income and expense</span></a><?php } ?>
<?php if (canCreate('timetables')) { ?><a href="../../timetables/add.php" class="quick-action-card"><span class="quick-action-icon"><i class="fa-solid fa-calendar-days"></i></span><span class="quick-action-title">Create Timetable</span><span class="quick-action-desc">Build schedule</span></a><?php } ?>
<?php if (canCreate('notifications')) { ?><a href="../../notifications/create.php" class="quick-action-card"><span class="quick-action-icon"><i class="fa-solid fa-bell"></i></span><span class="quick-action-title">Send Notice</span><span class="quick-action-desc">Broadcast update</span></a><?php } ?>
<?php if (canCreate('exams')) { ?><a href="../../exams/add.php" class="quick-action-card"><span class="quick-action-icon"><i class="fa-solid fa-file-lines"></i></span><span class="quick-action-title">Add Exam</span><span class="quick-action-desc">Schedule assessment</span></a><?php } ?>
<?php if (canCreate('transport')) { ?><a href="../../transport/routes/add.php" class="quick-action-card"><span class="quick-action-icon"><i class="fa-solid fa-route"></i></span><span class="quick-action-title">Add Route</span><span class="quick-action-desc">Plan transport</span></a><?php } ?>
</div>
</div>
</div>
</section>

<section class="analytics-grid">
<div class="analytics-card"><div class="analytics-header"><div><p class="analytics-title">Finance Collection</p><div class="analytics-value"><?php echo pct($collectionRate); ?></div><div class="analytics-subtitle"><?php echo money($finance['total_collected']); ?> collected of <?php echo money($finance['total_assigned']); ?></div></div><div class="analytics-icon"><i class="fa-solid fa-chart-pie"></i></div></div><div class="analytics-footer"><span class="analytics-trend up"><i class="fa-solid fa-indian-rupee-sign"></i><?php echo money($finance['today_collection']); ?> today</span><span><?php echo number_format($finance['pending_students']); ?> pending</span></div></div>
<div class="analytics-card"><div class="analytics-header"><div><p class="analytics-title">Salary Payroll</p><div class="analytics-value"><?php echo money($salary['paid_this_month']); ?></div><div class="analytics-subtitle"><?php echo money($salary['active_payroll']); ?> active monthly payroll</div></div><div class="analytics-icon"><i class="fa-solid fa-user-tie"></i></div></div><div class="analytics-footer"><span class="analytics-trend flat"><i class="fa-solid fa-receipt"></i><?php echo money($salary['total_paid']); ?> total paid</span><span>Salary expense</span></div></div>
<div class="analytics-card"><div class="analytics-header"><div><p class="analytics-title">Billing Balance</p><div class="analytics-value"><?php echo money($finance['net_balance']); ?></div><div class="analytics-subtitle"><?php echo money($finance['total_collected']); ?> in, <?php echo money($finance['salary_expense']); ?> out</div></div><div class="analytics-icon"><i class="fa-solid fa-scale-balanced"></i></div></div><div class="analytics-footer"><span class="analytics-trend <?php echo $finance['net_balance'] >= 0 ? 'up' : 'down'; ?>"><i class="fa-solid fa-chart-line"></i>Net position</span><span>Billing stats</span></div></div>
<div class="analytics-card"><div class="analytics-header"><div><p class="analytics-title">Exam Pipeline</p><div class="analytics-value"><?php echo number_format($exams['total_exams']); ?></div><div class="analytics-subtitle"><?php echo number_format($exams['published_results']); ?> published results, <?php echo number_format($exams['draft_results']); ?> drafts</div></div><div class="analytics-icon"><i class="fa-solid fa-clipboard-check"></i></div></div><div class="analytics-footer"><span class="analytics-trend flat"><i class="fa-solid fa-clock"></i><?php echo number_format($exams['upcoming_exams']); ?> upcoming</span><span>Assessment status</span></div></div>
<div class="analytics-card"><div class="analytics-header"><div><p class="analytics-title">Transport Coverage</p><div class="analytics-value"><?php echo pct($transportCoverage); ?></div><div class="analytics-subtitle"><?php echo number_format($transport['routes']); ?> routes, <?php echo number_format($transport['drivers']); ?> drivers</div></div><div class="analytics-icon"><i class="fa-solid fa-location-dot"></i></div></div><div class="analytics-footer"><span class="analytics-trend up"><i class="fa-solid fa-bus-simple"></i><?php echo number_format($transport['buses']); ?> buses</span><span><?php echo number_format($transport['assigned_students']); ?> riders</span></div></div>
</section>

<section class="dashboard-grid-equal">
<div class="chart-card">
<div class="chart-header"><div class="chart-title"><h3>Attendance Analytics</h3><span>Student attendance trend and class comparison</span></div></div>
<div class="chart-body"><canvas id="monthlyAttendanceChart"></canvas></div>
</div>
<div class="chart-card">
<div class="chart-header"><div class="chart-title"><h3>Class Attendance Comparison</h3><span>Top class attendance distribution</span></div></div>
<div class="chart-body"><canvas id="classComparisonChart"></canvas></div>
</div>
<div class="chart-card">
<div class="chart-header"><div class="chart-title"><h3>Finance Analytics</h3><span>Monthly fee collections</span></div></div>
<div class="chart-body"><canvas id="financeCollectionChart"></canvas></div>
</div>
<div class="chart-card">
<div class="chart-header"><div class="chart-title"><h3>Exam Analytics</h3><span>Average result percentage by exam</span></div></div>
<div class="chart-body"><canvas id="examPerformanceChart"></canvas></div>
</div>
</section>

<section class="dashboard-grid-3">
<div class="info-panel">
<h3 class="info-panel-title"><i class="fa-solid fa-chart-line"></i>Institution Summary</h3>
<div class="info-list">
<div class="info-list-item"><span>Parents</span><strong><?php echo number_format($institution['parents']); ?></strong></div>
<div class="info-list-item"><span>Subjects</span><strong><?php echo number_format($institution['subjects']); ?></strong></div>
<div class="info-list-item"><span>Teacher Attendance</span><strong><?php echo pct($stats['teacher_attendance_rate']); ?></strong></div>
</div>
</div>
<div class="info-panel">
<h3 class="info-panel-title"><i class="fa-solid fa-triangle-exclamation"></i>Priority Alerts</h3>
<div class="info-list">
<div class="info-list-item"><span>Pending attendance</span><strong><?php echo number_format($alerts['pending_attendance']); ?></strong></div>
<div class="info-list-item"><span>Critical students</span><strong><?php echo number_format($alerts['critical_students']); ?></strong></div>
<div class="info-list-item"><span>Fee follow-ups</span><strong><?php echo number_format($alerts['pending_fee_students']); ?></strong></div>
<div class="info-list-item"><span>Draft results</span><strong><?php echo number_format($alerts['draft_results']); ?></strong></div>
</div>
</div>
<div class="info-panel">
<h3 class="info-panel-title"><i class="fa-solid fa-server"></i>System Health</h3>
<div class="progress-group">
<?php foreach ($moduleHealth as $module => $count) { $width = min(100, max(8, (int) $count * 12)); ?>
<div class="progress-item"><div class="progress-header"><span class="progress-title"><?php echo e(ucfirst($module)); ?></span><span class="progress-value"><?php echo number_format($count); ?> records</span></div><div class="progress"><span class="progress-bar progress-success" style="width:<?php echo $width; ?>%"></span></div></div>
<?php } ?>
</div>
</div>
</section>

<section class="dashboard-grid-3">
<div class="dashboard-widget">
<div class="dashboard-widget-header"><div class="dashboard-widget-title"><i class="fa-solid fa-user-plus"></i><div><h4>Recent Admissions</h4><span>Newest student records</span></div></div></div>
<div class="dashboard-widget-body dashboard-table-wrap">
<table class="dashboard-table"><thead><tr><th>Student</th><th>Class</th><th>Joined</th></tr></thead><tbody>
<?php if (empty($recentAdmissions)) { ?><tr><td colspan="3">No admissions yet.</td></tr><?php } ?>
<?php foreach ($recentAdmissions as $item) { ?><tr><td><strong><?php echo e($item['full_name'] ?: $item['student_id']); ?></strong><br><span><?php echo e($item['student_id']); ?></span></td><td><?php echo e(trim(($item['class_name'] ?? '') . ' ' . ($item['section_name'] ?? ''))); ?></td><td><?php echo short_date($item['created_at']); ?></td></tr><?php } ?>
</tbody></table>
</div>
</div>
<div class="dashboard-widget">
<div class="dashboard-widget-header"><div class="dashboard-widget-title"><i class="fa-solid fa-receipt"></i><div><h4>Recent Payments</h4><span>Latest fee transactions</span></div></div></div>
<div class="dashboard-widget-body dashboard-table-wrap">
<table class="dashboard-table"><thead><tr><th>Receipt</th><th>Student</th><th>Amount</th></tr></thead><tbody>
<?php if (empty($recentPayments)) { ?><tr><td colspan="3">No payments yet.</td></tr><?php } ?>
<?php foreach ($recentPayments as $item) { ?><tr><td><strong><?php echo e($item['receipt_no']); ?></strong><br><span><?php echo short_date($item['payment_date']); ?></span></td><td><?php echo e($item['full_name']); ?></td><td><?php echo money($item['amount_paid']); ?></td></tr><?php } ?>
</tbody></table>
</div>
</div>
<div class="dashboard-widget">
<div class="dashboard-widget-header"><div class="dashboard-widget-title"><i class="fa-solid fa-money-check-dollar"></i><div><h4>Salary Payments</h4><span>Latest payroll transactions</span></div></div></div>
<div class="dashboard-widget-body dashboard-table-wrap">
<table class="dashboard-table"><thead><tr><th>Receipt</th><th>Teacher</th><th>Amount</th></tr></thead><tbody>
<?php if (empty($recentSalaryPayments)) { ?><tr><td colspan="3">No salary payments yet.</td></tr><?php } ?>
<?php foreach ($recentSalaryPayments as $item) { ?><tr><td><strong><?php echo e($item['receipt_no']); ?></strong><br><span><?php echo short_date($item['payment_date']); ?> · <?php echo e($item['pay_period']); ?></span></td><td><?php echo e($item['full_name']); ?></td><td><?php echo money($item['amount_paid']); ?></td></tr><?php } ?>
</tbody></table>
</div>
</div>
<div class="dashboard-widget">
<div class="dashboard-widget-header"><div class="dashboard-widget-title"><i class="fa-solid fa-square-poll-vertical"></i><div><h4>Recent Results</h4><span>Exam publication activity</span></div></div></div>
<div class="dashboard-widget-body dashboard-table-wrap">
<table class="dashboard-table"><thead><tr><th>Exam</th><th>Class</th><th>Status</th></tr></thead><tbody>
<?php if (empty($recentResults)) { ?><tr><td colspan="3">No results yet.</td></tr><?php } ?>
<?php foreach ($recentResults as $item) { $published = ($item['status'] ?? '') === 'published'; ?><tr><td><strong><?php echo e($item['exam_name']); ?></strong><br><span><?php echo short_date($item['published_at']); ?></span></td><td><?php echo e(trim(($item['class_name'] ?? '') . ' ' . ($item['section_name'] ?? ''))); ?></td><td><span class="dashboard-badge <?php echo $published ? 'badge-success' : 'badge-warning'; ?>"><?php echo e(ucfirst($item['status'])); ?></span></td></tr><?php } ?>
</tbody></table>
</div>
</div>
</section>

<section class="dashboard-grid-lg">
<div class="dashboard-widget">
<div class="dashboard-widget-header"><div class="dashboard-widget-title"><i class="fa-solid fa-bell"></i><div><h4>Notifications</h4><span>Latest broadcasts and alerts</span></div></div></div>
<div class="dashboard-widget-body">
<div class="notification-list">
<?php if (empty($recentNotifications)) { ?><div class="dashboard-empty"><i class="fa-solid fa-bell-slash"></i><p>No notifications available.</p></div><?php } ?>
<?php foreach ($recentNotifications as $n) { ?>


<?php
$icon = 'fa-bullhorn';
$color = '#3b82f6';

switch($n['notification_type']){
    case 'announcement':
        $icon = 'fa-bullhorn';
        $color = '#7c3aed';
        break;

    case 'exam':
        $icon = 'fa-file-lines';
        $color = '#ef4444';
        break;

    case 'attendance':
        $icon = 'fa-calendar-check';
        $color = '#06b6d4';
        break;

    case 'result':
        $icon = 'fa-square-poll-vertical';
        $color = '#10b981';
        break;

    case 'fee':
        $icon = 'fa-money-bill-wave';
        $color = '#f59e0b';
        break;

    case 'transport':
        $icon = 'fa-bus';
        $color = '#ff5722';
        break;
}
?>
<div class="notification-item">


    <div class="notification-icon" style="background:<?php echo $color; ?>;">
    <i class="fa-solid <?php echo $icon; ?>"></i>
</div>

    <div class="notification-content">
        <h4 class="notification-title"><?php echo e($n['title']); ?></h4>

        <div class="notification-meta">
            <span><?php echo e(ucfirst($n['notification_type'] ?? 'general')); ?></span>
            <span><?php echo short_date($n['created_at']); ?></span>
        </div>
    </div>
</div>
<?php } ?>
</div>
</div>
</div>
<div class="dashboard-widget">
<div class="dashboard-widget-header"><div class="dashboard-widget-title"><i class="fa-solid fa-calendar-days"></i><div><h4>Upcoming Events</h4><span>Scheduled exams and milestones</span></div></div></div>
<div class="dashboard-widget-body">
<div class="event-list">
<?php if (empty($upcomingEvents)) { ?><div class="dashboard-empty"><i class="fa-solid fa-calendar-xmark"></i><p>No upcoming events scheduled.</p></div><?php } ?>
<?php foreach ($upcomingEvents as $event) { ?>
<div class="event-card">
<div class="event-date"><span class="event-day"><?php echo date('j', strtotime($event['date'])); ?></span><span class="event-month"><?php echo date('M', strtotime($event['date'])); ?></span></div>
<div class="event-content"><h4 class="event-title"><?php echo e($event['title']); ?></h4><div class="event-description"><?php echo e($event['type']); ?> <?php echo $event['scope'] ? 'for ' . e($event['scope']) : ''; ?></div><div class="event-meta"><span><i class="fa-solid fa-clock"></i> <?php echo short_time($event['time']); ?></span></div></div>
</div>
<?php } ?>
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

new Chart(document.getElementById('monthlyAttendanceChart'), {
    type: 'line',
    data: {
        labels: <?php echo json_encode($monthlyTrend['labels']); ?>,
        datasets: [{ label: 'Attendance %', data: <?php echo json_encode($monthlyTrend['values']); ?>, borderColor: '#2563eb', backgroundColor: 'rgba(37,99,235,0.12)', fill: true, tension: 0.35, pointRadius: 4 }]
    },
    options: { ...chartDefaults, scales: { ...chartDefaults.scales, y: { ...chartDefaults.scales.y, max: 100 } } }
});

new Chart(document.getElementById('classComparisonChart'), {
    type: 'bar',
    data: {
        labels: <?php echo json_encode($classComparison['labels']); ?>,
        datasets: [{ label: 'Attendance %', data: <?php echo json_encode($classComparison['values']); ?>, backgroundColor: '#22c55e', borderRadius: 8 }]
    },
    options: { ...chartDefaults, scales: { ...chartDefaults.scales, y: { ...chartDefaults.scales.y, max: 100 } } }
});

new Chart(document.getElementById('financeCollectionChart'), {
    type: 'bar',
    data: {
        labels: <?php echo json_encode($financeTrend['labels']); ?>,
        datasets: [{ label: 'Collections', data: <?php echo json_encode($financeTrend['values']); ?>, backgroundColor: '#0f766e', borderRadius: 8 }]
    },
    options: chartDefaults
});

new Chart(document.getElementById('examPerformanceChart'), {
    type: 'line',
    data: {
        labels: <?php echo json_encode($examTrend['labels']); ?>,
        datasets: [{ label: 'Average %', data: <?php echo json_encode($examTrend['values']); ?>, borderColor: '#f59e0b', backgroundColor: 'rgba(245,158,11,0.14)', fill: true, tension: 0.35, pointRadius: 4 }]
    },
    options: { ...chartDefaults, scales: { ...chartDefaults.scales, y: { ...chartDefaults.scales.y, max: 100 } } }
});
</script>
</body>
</html>
