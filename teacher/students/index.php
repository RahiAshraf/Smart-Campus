<?php

require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../shared/services/TeacherScopeService.php';
require_once __DIR__ . '/../../shared/helpers/portal.php';

portal_require_role(['teacher']);
requirePermission('students', 'view');

$scope = new TeacherScopeService($conn, (int) $_SESSION['user']['id']);
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$students = $scope->getAssignedStudents($search);

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Students | MindMerge</title>
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
<div><h1>My Students</h1><p>Students in your assigned classes and sections.</p></div>
</div>
<form method="GET" style="margin-bottom:20px;display:flex;gap:10px;flex-wrap:wrap;">
<input type="text" name="search" class="form-input" placeholder="Search students..." value="<?php echo htmlspecialchars($search); ?>" style="max-width:320px;">
<button type="submit" class="btn btn-primary"><i class="fa-solid fa-search"></i> Search</button>
</form>
<div class="table-responsive">
<table class="custom-table">
<thead><tr><th>Student ID</th><th>Name</th><th>Class</th><th>Section</th><th>Email</th></tr></thead>
<tbody>
<?php if (empty($students)) { ?>
<tr><td colspan="5">No assigned students found.</td></tr>
<?php } else { foreach ($students as $st) { ?>
<tr>
<td><?php echo htmlspecialchars($st['student_id']); ?></td>
<td><?php echo htmlspecialchars($st['full_name']); ?></td>
<td><?php echo htmlspecialchars($st['class_name']); ?></td>
<td><?php echo htmlspecialchars($st['section_name']); ?></td>
<td><?php echo htmlspecialchars($st['email']); ?></td>
</tr>
<?php } } ?>
</tbody>
</table>
</div>
</div></div></div>
<script src="../../assets/js/common.js"></script>
</body>
</html>
