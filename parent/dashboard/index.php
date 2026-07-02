<?php

require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/permissions.php';
require_once __DIR__ . '/../../config/finance.php';
require_once __DIR__ . '/../../shared/services/ParentDashboardService.php';
require_once __DIR__ . '/../../shared/helpers/portal.php';

portal_require_role(['parent']);
finance_ensure_tables($conn);

$service = new ParentDashboardService($conn, (int) $_SESSION['user']['id']);
$allChildren = $service->getChildren();
$selectedChildId = (int) ($_GET['child_id'] ?? 0);
if ($selectedChildId <= 0 && count($allChildren) === 1) {
    $selectedChildId = (int) ($allChildren[0]['id'] ?? 0);
}
if ($selectedChildId > 0) {
    $validChildIds = array_map(fn($child) => (int) ($child['id'] ?? 0), $allChildren);
    if (in_array($selectedChildId, $validChildIds, true)) {
        $service->setActiveChild($selectedChildId);
    } else {
        $selectedChildId = 0;
    }
}
$stats = $service->getStats();
$insights = $service->getInsights();
$children = $service->getChildrenOverview();
$fees = $service->getFeeSummary();
$feeDetails = $service->getFeeDetails();
$feePayments = $service->getRecentFeePayments();
$attendanceTrend = $service->getAttendanceTrend();
$performanceTrend = $service->getPerformanceTrend();
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
<title>Parent Dashboard | MindMerge</title>
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
<h1 class="hero-title">Parent Portal</h1>
<p class="hero-description">Welcome, <strong><?php echo e($user_name); ?></strong>. Follow your children's attendance, fees, results, notices, transport, and school communications.</p>
<div class="hero-meta">
<span class="hero-badge"><i class="fa-solid fa-children"></i><?php echo number_format($stats['children_count']); ?> children</span>
<span class="hero-badge"><i class="fa-solid fa-calendar-check"></i><?php echo number_format($stats['average_attendance'], 2); ?>% attendance</span>
<span class="hero-badge"><i class="fa-solid fa-wallet"></i><?php echo money($stats['fee_balance']); ?> balance</span>
</div>
</div>
<div class="hero-illustration"><i class="fa-solid fa-people-roof"></i></div>
</section>

<?php if (count($allChildren) > 1) { ?>
<section class="dashboard-widget child-selector-widget">
<div class="dashboard-widget-header"><div class="dashboard-widget-title"><i class="fa-solid fa-children"></i><div><h4>Child Selector</h4><span>Choose whose dashboard data to view</span></div></div></div>
<div class="dashboard-widget-body">
<div class="child-card-selector">
<?php foreach ($allChildren as $childOption) { $active = (int) ($childOption['id'] ?? 0) === $selectedChildId; ?>
<a href="?child_id=<?php echo (int) $childOption['id']; ?>" class="child-selector-card <?php echo $active ? 'active' : ''; ?>">
<span class="child-selector-avatar"><i class="fa-solid fa-user-graduate"></i></span>
<span><strong><?php echo e($childOption['full_name']); ?></strong><small><?php echo e(($childOption['class_name'] ?? '-') . ' ' . ($childOption['section_name'] ?? '')); ?></small></span>
</a>
<?php } ?>
</div>
<form class="child-dropdown-selector" method="get">
<select name="child_id" onchange="this.form.submit()">
<?php foreach ($allChildren as $childOption) { ?><option value="<?php echo (int) $childOption['id']; ?>" <?php echo (int) ($childOption['id'] ?? 0) === $selectedChildId ? 'selected' : ''; ?>><?php echo e($childOption['full_name']); ?> - <?php echo e(($childOption['class_name'] ?? '-') . ' ' . ($childOption['section_name'] ?? '')); ?></option><?php } ?>
</select>
</form>
</div>
</section>
<?php } ?>

<section class="stats-grid">
<div class="stat-card"><div class="stat-icon"><i class="fa-solid fa-children"></i></div><div class="stat-content"><span class="stat-label">Children</span><span class="stat-value"><?php echo number_format($stats['children_count']); ?></span><span class="stat-description">Linked student profiles</span></div></div>
<div class="stat-card"><div class="stat-icon"><i class="fa-solid fa-calendar-check"></i></div><div class="stat-content"><span class="stat-label">Avg Attendance</span><span class="stat-value"><?php echo number_format($stats['average_attendance'], 2); ?>%</span><span class="stat-description">Across all children</span></div></div>
<div class="stat-card"><div class="stat-icon"><i class="fa-solid fa-chart-column"></i></div><div class="stat-content"><span class="stat-label">Avg Performance</span><span class="stat-value"><?php echo number_format($stats['average_performance'], 2); ?>%</span><span class="stat-description">Published results</span></div></div>
<div class="stat-card"><div class="stat-icon"><i class="fa-solid fa-file-lines"></i></div><div class="stat-content"><span class="stat-label">Upcoming Exams</span><span class="stat-value"><?php echo number_format($stats['upcoming_exams']); ?></span><span class="stat-description">For linked classes</span></div></div>
<div class="stat-card"><div class="stat-icon"><i class="fa-solid fa-bell"></i></div><div class="stat-content"><span class="stat-label">Notifications</span><span class="stat-value"><?php echo number_format($stats['unread_notifications']); ?></span><span class="stat-description">Unread alerts</span></div></div>
</section>

<section class="dashboard-widget">
<div class="dashboard-widget-header"><div class="dashboard-widget-title"><i class="fa-solid fa-bolt"></i><div><h4>Quick Actions</h4><span>Family portal shortcuts</span></div></div></div>
<div class="dashboard-widget-body">
<div class="quick-actions">
<a href="../children/index.php" class="quick-action-card"><span class="quick-action-icon"><i class="fa-solid fa-children"></i></span><span class="quick-action-title">Children</span><span class="quick-action-desc">Student profiles</span></a>
<a href="../attendance/index.php" class="quick-action-card"><span class="quick-action-icon"><i class="fa-solid fa-calendar-check"></i></span><span class="quick-action-title">Attendance</span><span class="quick-action-desc">Daily records</span></a>
<a href="../results/index.php" class="quick-action-card"><span class="quick-action-icon"><i class="fa-solid fa-chart-column"></i></span><span class="quick-action-title">Results</span><span class="quick-action-desc">Marks and progress</span></a>
<a href="../../exams/index.php" class="quick-action-card"><span class="quick-action-icon"><i class="fa-solid fa-file-lines"></i></span><span class="quick-action-title">Exams</span><span class="quick-action-desc">Upcoming schedule</span></a>
<a href="../transport/index.php" class="quick-action-card"><span class="quick-action-icon"><i class="fa-solid fa-bus"></i></span><span class="quick-action-title">Transport</span><span class="quick-action-desc">Bus details</span></a>
<a href="../../notifications/index.php" class="quick-action-card"><span class="quick-action-icon"><i class="fa-solid fa-bell"></i></span><span class="quick-action-title">Notices</span><span class="quick-action-desc">School updates</span></a>
</div>
</div>
</section>

<section class="dashboard-widget">
<div class="dashboard-widget-header"><div class="dashboard-widget-title"><i class="fa-solid fa-children"></i><div><h4>Children Overview</h4><span>Attendance, fees, and latest performance</span></div></div></div>
<div class="dashboard-widget-body dashboard-table-wrap">
<table class="dashboard-table"><thead><tr><th>Child</th><th>Class</th><th>Attendance</th><th>Fee Balance</th><th>Latest Result</th></tr></thead><tbody>
<?php if (empty($children)) { ?><tr><td colspan="5">No linked children found.</td></tr><?php } ?>
<?php foreach ($children as $item) { $child = $item['student']; ?><tr><td><strong><?php echo e($child['full_name']); ?></strong><br><span><?php echo e($child['student_id']); ?></span></td><td><?php echo e($child['class_name'] . ' ' . $child['section_name']); ?></td><td><?php echo number_format($item['attendance'], 2); ?>%</td><td><?php echo money($item['fee_balance']); ?></td><td><?php echo $item['performance'] !== null ? number_format($item['performance'], 2) . '% - ' . e($item['latest_exam']) : '-'; ?></td></tr><?php } ?>
</tbody></table>
</div>
</section>

<section class="dashboard-grid-equal">
<div class="chart-card"><div class="chart-header"><div class="chart-title"><h3>Attendance</h3><span>Family attendance trend</span></div></div><div class="chart-body"><canvas id="attendanceTrendChart"></canvas></div></div>
<div class="chart-card"><div class="chart-header"><div class="chart-title"><h3>Performance</h3><span>Average result percentage by exam</span></div></div><div class="chart-body"><canvas id="performanceTrendChart"></canvas></div></div>
</section>

<section class="dashboard-grid-3">
<div class="info-panel">
<h3 class="info-panel-title"><i class="fa-solid fa-wallet"></i>Fees</h3>
<div class="progress-group"><div class="progress-item"><div class="progress-header"><span class="progress-title">Paid</span><span class="progress-value"><?php echo number_format($feePaidPct, 2); ?>%</span></div><div class="progress"><span class="progress-bar progress-success" style="width:<?php echo min(100, $feePaidPct); ?>%"></span></div></div></div>
<div class="info-list" style="margin-top:18px;"><div class="info-list-item"><span>Assigned</span><strong><?php echo money($fees['assigned']); ?></strong></div><div class="info-list-item"><span>Paid</span><strong><?php echo money($fees['paid']); ?></strong></div><div class="info-list-item"><span>Balance</span><strong><?php echo money($fees['balance']); ?></strong></div></div>
</div>
<div class="info-panel">
<h3 class="info-panel-title"><i class="fa-solid fa-triangle-exclamation"></i>Attendance Alerts</h3>
<div class="info-list">
<?php if (empty($insights['attendance_alerts'])) { ?><div class="info-list-item"><span>All children meet attendance thresholds</span><strong>Good</strong></div><?php } ?>
<?php foreach ($insights['attendance_alerts'] as $alert) { ?><div class="info-list-item"><span><?php echo e($alert['name']); ?></span><strong><?php echo number_format($alert['rate'], 2); ?>%</strong></div><?php } ?>
</div>
</div>
</section>

<section class="dashboard-grid-equal">
<div class="dashboard-widget">
<div class="dashboard-widget-header"><div class="dashboard-widget-title"><i class="fa-solid fa-file-invoice-dollar"></i><div><h4>Fee Details</h4><span>Children's assigned fees</span></div></div></div>
<div class="dashboard-widget-body dashboard-table-wrap">
<table class="dashboard-table"><thead><tr><th>Child</th><th>Fee</th><th>Balance</th><th>Status</th></tr></thead><tbody>
<?php if (empty($feeDetails)) { ?><tr><td colspan="4">No fees assigned.</td></tr><?php } ?>
<?php foreach ($feeDetails as $fee) { ?><tr><td><?php echo e($fee['full_name']); ?></td><td><strong><?php echo e($fee['fee_name']); ?></strong><br><span>Due <?php echo short_date($fee['due_date']); ?></span></td><td><?php echo money($fee['balance_amount']); ?><br><span>Paid <?php echo money($fee['paid_amount']); ?></span></td><td><span class="dashboard-badge <?php echo $fee['payment_status'] === 'paid' ? 'badge-success' : ($fee['payment_status'] === 'partial' ? 'badge-warning' : 'badge-danger'); ?>"><?php echo e(ucfirst($fee['payment_status'])); ?></span></td></tr><?php } ?>
</tbody></table>
</div>
</div>
<div class="dashboard-widget">
<div class="dashboard-widget-header"><div class="dashboard-widget-title"><i class="fa-solid fa-receipt"></i><div><h4>Fee Payments</h4><span>Recent receipts</span></div></div></div>
<div class="dashboard-widget-body dashboard-table-wrap">
<table class="dashboard-table"><thead><tr><th>Receipt</th><th>Child</th><th>Amount</th></tr></thead><tbody>
<?php if (empty($feePayments)) { ?><tr><td colspan="3">No fee payments yet.</td></tr><?php } ?>
<?php foreach ($feePayments as $payment) { ?><tr><td><strong><?php echo e($payment['receipt_no']); ?></strong><br><span><?php echo short_date($payment['payment_date']); ?></span></td><td><?php echo e($payment['full_name']); ?><br><span><?php echo e($payment['fee_name']); ?></span></td><td><?php echo money($payment['amount_paid']); ?></td></tr><?php } ?>
</tbody></table>
</div>
</div>
</section>

<section class="dashboard-grid-3">
<div class="dashboard-widget">
<div class="dashboard-widget-header"><div class="dashboard-widget-title"><i class="fa-solid fa-square-poll-vertical"></i><div><h4>Results</h4><span>Recent marks</span></div></div></div>
<div class="dashboard-widget-body dashboard-table-wrap">
<table class="dashboard-table"><thead><tr><th>Child</th><th>Exam</th><th>Score</th></tr></thead><tbody>
<?php if (empty($recentResults)) { ?><tr><td colspan="3">No results yet.</td></tr><?php } ?>
<?php foreach ($recentResults as $result) { $pct = ((float) $result['total_marks'] > 0) ? round(((float) $result['marks_obtained'] / (float) $result['total_marks']) * 100, 2) : 0; ?><tr><td><?php echo e($result['full_name']); ?></td><td><?php echo e($result['exam_name']); ?><br><span><?php echo short_date($result['exam_date']); ?></span></td><td><?php echo number_format($pct, 2); ?>%</td></tr><?php } ?>
</tbody></table>
</div>
</div>
<div class="dashboard-widget">
<div class="dashboard-widget-header"><div class="dashboard-widget-title"><i class="fa-solid fa-file-lines"></i><div><h4>Upcoming Exams</h4><span>Linked class schedule</span></div></div></div>
<div class="dashboard-widget-body"><div class="event-list">
<?php if (empty($upcomingExams)) { ?><div class="dashboard-empty"><i class="fa-solid fa-calendar-xmark"></i><p>No upcoming exams scheduled.</p></div><?php } ?>
<?php foreach ($upcomingExams as $exam) { ?><div class="event-card"><div class="event-date"><span class="event-day"><?php echo date('j', strtotime($exam['exam_date'])); ?></span><span class="event-month"><?php echo date('M', strtotime($exam['exam_date'])); ?></span></div><div class="event-content"><h4 class="event-title"><?php echo e($exam['exam_name']); ?></h4><div class="event-description"><?php echo e($exam['class_name'] . ' ' . $exam['section_name']); ?></div><div class="event-meta"><span><?php echo short_time($exam['start_time']); ?></span><span><?php echo e($exam['subject_name'] ?: ucwords(str_replace('_', ' ', $exam['exam_type']))); ?></span></div></div></div><?php } ?>
</div></div>
</div>
<div class="dashboard-widget">
<div class="dashboard-widget-header"><div class="dashboard-widget-title"><i class="fa-solid fa-bus"></i><div><h4>Transport</h4><span>Assigned buses</span></div></div></div>
<div class="dashboard-widget-body dashboard-table-wrap">
<table class="dashboard-table"><thead><tr><th>Child</th><th>Bus</th><th>Route</th></tr></thead><tbody>
<?php if (empty($transport)) { ?><tr><td colspan="3">No transport assignments found.</td></tr><?php } ?>
<?php foreach ($transport as $item) { ?><tr><td><?php echo e($item['full_name']); ?></td><td><?php echo e($item['bus_number']); ?><br><span><?php echo e($item['driver_name'] ?: '-'); ?></span></td><td><?php echo e($item['route_name'] ?: '-'); ?><br><span><?php echo e($item['stop_name'] ?: '-'); ?></span></td></tr><?php } ?>
</tbody></table>
</div>
</div>
</section>

<section class="dashboard-grid-equal">
<div class="dashboard-widget">
<div class="dashboard-widget-header"><div class="dashboard-widget-title"><i class="fa-solid fa-bell"></i><div><h4>Notices</h4><span>Recent school updates</span></div></div></div>
<div class="dashboard-widget-body"><div class="notification-list">
<?php foreach ($notifications as $n) { ?><div class="notification-item"><div class="notification-icon"><i class="fa-solid fa-bullhorn"></i></div><div class="notification-content"><h4 class="notification-title"><?php echo e($n['title']); ?></h4><div class="notification-message"><?php echo e($n['message']); ?></div><div class="notification-meta"><span><?php echo e(ucfirst($n['notification_type'] ?? 'general')); ?></span><span><?php echo short_date($n['created_at']); ?></span></div></div></div><?php } ?>
</div></div>
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
new Chart(document.getElementById('performanceTrendChart'), {
    type: 'bar',
    data: { labels: <?php echo json_encode($performanceTrend['labels']); ?>, datasets: [{ label: 'Average %', data: <?php echo json_encode($performanceTrend['values']); ?>, backgroundColor: '#0f766e', borderRadius: 8 }] },
    options: { ...chartDefaults, scales: { ...chartDefaults.scales, y: { ...chartDefaults.scales.y, max: 100 } } }
});
</script>
</body>
</html>
