<?php

require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../shared/services/TeacherScopeService.php';
require_once __DIR__ . '/../../shared/helpers/portal.php';

portal_require_role(['teacher']);
requirePermission('my_teacher_attendance', 'view');

$scope = new TeacherScopeService($conn, (int) $_SESSION['user']['id']);
$teacher_id = $scope->getTeacherId();
$user_name = portal_greeting_name();

$date_from = trim($_GET['date_from'] ?? date('Y-m-01'));
$date_to = trim($_GET['date_to'] ?? date('Y-m-d'));

$records = [];
$stats = [
    'present' => 0,
    'absent' => 0,
    'leave' => 0,
    'late' => 0,
    'total' => 0,
];

if ($teacher_id > 0) {
    $from_sql = mysqli_real_escape_string($conn, $date_from);
    $to_sql = mysqli_real_escape_string($conn, $date_to);

    $query = mysqli_query(
        $conn,
        "SELECT ta.*, u.full_name AS marked_by_name
         FROM teacher_attendance ta
         LEFT JOIN users u ON u.id = ta.created_by
         WHERE ta.teacher_id = '$teacher_id'
           AND ta.attendance_date BETWEEN '$from_sql' AND '$to_sql'
         ORDER BY ta.attendance_date DESC, ta.attendance_id DESC"
    );

    while ($row = mysqli_fetch_assoc($query)) {
        $records[] = $row;
        $stats['total']++;
        $status = strtolower($row['status'] ?? '');

        if ($status === 'present') {
            $stats['present']++;
        } elseif ($status === 'absent') {
            $stats['absent']++;
        } elseif ($status === 'leave') {
            $stats['leave']++;
        } elseif ($status === 'late' || $status === 'half_day') {
            $stats['late']++;
        }
    }
}

$attendance_rate = $stats['total'] > 0
    ? round((($stats['present'] + $stats['late']) / $stats['total']) * 100, 2)
    : 0;

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

<div class="page-header">
<div>
<h1>My Attendance</h1>
<p class="portal-greeting">View-only attendance records for <strong><?php echo htmlspecialchars($user_name); ?></strong>.</p>
</div>
<?php if (canView('attendance')) { ?>
<a href="<?php echo BASE_URL; ?>attendance/report.php" class="btn">
<i class="fa-solid fa-chart-column"></i> Attendance Reports
</a>
<?php } ?>
</div>

<?php if ($teacher_id <= 0) { ?>
<div class="alert-banner warning">
<i class="fa-solid fa-circle-exclamation"></i>
Teacher profile not linked. Contact administration.
</div>
<?php } else { ?>

<div class="portal-stats-grid">
<div class="dashboard-card"><div class="card-icon green"><i class="fa-solid fa-circle-check"></i></div><h3>Present</h3><h1><?php echo $stats['present']; ?></h1></div>
<div class="dashboard-card"><div class="card-icon red"><i class="fa-solid fa-circle-xmark"></i></div><h3>Absent</h3><h1><?php echo $stats['absent']; ?></h1></div>
<div class="dashboard-card"><div class="card-icon orange"><i class="fa-solid fa-clock"></i></div><h3>Late / Half Day</h3><h1><?php echo $stats['late']; ?></h1></div>
<div class="dashboard-card"><div class="card-icon blue"><i class="fa-solid fa-percent"></i></div><h3>Attendance Rate</h3><h1><?php echo $attendance_rate; ?>%</h1></div>
</div>

<div class="dashboard-section">
<div class="section-header"><h2>Filter Records</h2></div>
<form method="GET" class="filter-row" style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end;">
<div class="form-group">
<label>From</label>
<input type="date" name="date_from" value="<?php echo htmlspecialchars($date_from); ?>" class="form-control">
</div>
<div class="form-group">
<label>To</label>
<input type="date" name="date_to" value="<?php echo htmlspecialchars($date_to); ?>" class="form-control">
</div>
<button type="submit" class="btn btn-primary"><i class="fa-solid fa-filter"></i> Apply</button>
</form>
</div>

<div class="dashboard-section">
<div class="section-header"><h2>Attendance History</h2></div>
<div class="table-responsive">
<table class="custom-table">
<thead>
<tr>
<th>Date</th>
<th>Day</th>
<th>Status</th>
<th>Remarks</th>
<th>Recorded By</th>
</tr>
</thead>
<tbody>
<?php if (empty($records)) { ?>
<tr>
<td colspan="5">
<div class="empty-state">
<i class="fa-solid fa-calendar-check"></i>
<h3>No attendance records</h3>
<p>No attendance has been recorded for the selected period.</p>
</div>
</td>
</tr>
<?php } else { foreach ($records as $row) {
    $status = strtolower($row['status'] ?? '');
    $status_class = match ($status) {
        'present' => 'success',
        'absent' => 'danger',
        'leave' => 'warning',
        'late', 'half_day' => 'warning',
        default => 'primary',
    };
?>
<tr>
<td><?php echo date('d M Y', strtotime($row['attendance_date'])); ?></td>
<td><?php echo date('l', strtotime($row['attendance_date'])); ?></td>
<td><span class="status <?php echo $status_class; ?>"><?php echo ucfirst(str_replace('_', ' ', $status)); ?></span></td>
<td><?php echo htmlspecialchars($row['remarks'] ?? '-'); ?></td>
<td><?php echo htmlspecialchars($row['marked_by_name'] ?? 'Admin'); ?></td>
</tr>
<?php } } ?>
</tbody>
</table>
</div>
</div>

<?php } ?>

</div>
</div>
</div>
<script src="../../assets/js/common.js"></script>
</body>
</html>
