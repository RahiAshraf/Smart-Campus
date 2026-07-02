<?php

include('../config/auth.php');
include('../config/db.php');
require_once '../shared/services/ExamService.php';
require_once '../shared/services/StudentDashboardService.php';
require_once '../shared/services/ParentDashboardService.php';
require_once '../shared/services/TeacherScopeService.php';

$examService = new ExamService($conn);
$role = strtolower($_SESSION['user']['role'] ?? 'admin');
$scope = [];
$exams = [];

if ($role === 'student') {
    $studentService = new StudentDashboardService($conn, (int) ($_SESSION['user']['id'] ?? 0));
    $student = $studentService->getStudent();
    $scope = [
        'class_id' => (int) ($student['class_id'] ?? 0),
        'section_id' => (int) ($student['section_id'] ?? 0),
        'hide_drafts' => true,
    ];
    $exams = $examService->getExams($scope);
} elseif ($role === 'parent') {
    $parentService = new ParentDashboardService($conn, (int) ($_SESSION['user']['id'] ?? 0));
    $examMap = [];
    foreach ($parentService->getChildren() as $child) {
        foreach ($examService->getExams([
            'class_id' => (int) ($child['class_id'] ?? 0),
            'section_id' => (int) ($child['section_id'] ?? 0),
            'hide_drafts' => true,
        ]) as $exam) {
            $examMap[(int) $exam['exam_id']] = $exam;
        }
    }
    $exams = array_values($examMap);
    $scope = ['children' => $parentService->getChildren()];
} elseif ($role === 'teacher') {
    $teacherScope = new TeacherScopeService($conn, (int) ($_SESSION['user']['id'] ?? 0));
    $scope = [
        'teacher_id' => $teacherScope->getTeacherId(),
        'hide_drafts' => true,
    ];
    $exams = $examService->getExams($scope);
} else {
    $exams = $examService->getExams();
}

$examStats = $examService->getExamStatistics($scope);

$search = '';

if(isset($_GET['search']) && trim($_GET['search']) != ''){

    $search = mysqli_real_escape_string(
        $conn,
        trim($_GET['search'])
    );

    $needle = strtolower($search);
    $exams = array_values(array_filter($exams, function ($exam) use ($needle) {
        $haystack = strtolower(implode(' ', [
            $exam['exam_name'] ?? '',
            $exam['exam_type'] ?? '',
            $exam['class_name'] ?? '',
            $exam['section_name'] ?? '',
            $exam['subject_name'] ?? '',
            $exam['custom_subject'] ?? '',
            $exam['status'] ?? '',
        ]));

        return str_contains($haystack, $needle);
    }));
}

/* Statistics */

$total_exams = $examStats['total_exams'];
$upcoming_exams = $examStats['upcoming_exams'];
$ongoing_exams = $examStats['ongoing_exams'];
$completed_exams = $examStats['completed_exams'];

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<script>
(function(){
    if(localStorage.getItem("mindmerge-theme") === "dark"){
        document.documentElement.classList.add("dark-mode");
    }
})();
</script>


<meta
name="viewport"
content="width=device-width, initial-scale=1.0">

<title>
Exam Management | MindMerge SmartCampus
</title>

<link
rel="stylesheet"
href="../assets/css/global.css">

<link
rel="stylesheet"
href="../assets/css/layout.css">

<link
rel="stylesheet"
href="../assets/css/components.css">

<link
rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="../assets/css/material-dashboard.css">
<link rel="stylesheet" href="../assets/css/material-theme.css">

</head>

<body>

<div class="app-layout">

<?php include('../partials/sidebar.php'); ?>

<div class="main-content">

<?php include('../partials/topbar.php'); ?>

<div class="page-content">

<div class="page-header">

<div>

<h1>
Exam Management
</h1>

<p>
Create and manage examinations.
</p>

</div>

<?php if (canCreate('exams')) { ?><a
href="add.php"
class="btn btn-primary">

<i class="fa-solid fa-plus"></i>

Add Exam

</a><?php } ?>

</div>

<?php if(isset($_GET['success'])){ ?>

<div
style="
background:#dcfce7;
color:#166534;
padding:14px 18px;
border-radius:14px;
margin-bottom:20px;
font-weight:500;
">

<?php

if($_GET['success']=='added'){

echo "Exam created successfully.";

}
elseif($_GET['success']=='updated'){

echo "Exam updated successfully.";

}
elseif($_GET['success']=='deleted'){

echo "Exam deleted successfully.";

}

?>

</div>

<?php } ?>

<?php if(isset($_GET['error'])){ ?>

<div
style="
background:#fee2e2;
color:#991b1b;
padding:14px 18px;
border-radius:14px;
margin-bottom:20px;
font-weight:500;
">

<?php

if($_GET['error']=='not_found'){

echo "Exam not found.";

}
elseif($_GET['error']=='delete_failed'){

echo "Unable to delete exam.";

}

?>

</div>

<?php } ?>

<!-- Statistics -->

<div class="dashboard-grid">

    <div class="dashboard-card">
        <div>
            <h3>Total Exams</h3>
            <h2><?php echo $total_exams; ?></h2>
        </div>
        <div class="card-icon">
            <i class="fa-solid fa-file-lines"></i>
        </div>
    </div>

    <div class="dashboard-card">
        <div>
            <h3>Upcoming Exams</h3>
            <h2><?php echo $upcoming_exams; ?></h2>
        </div>
        <div class="card-icon">
            <i class="fa-solid fa-calendar-days"></i>
        </div>
    </div>

    <div class="dashboard-card">
        <div>
            <h3>Ongoing Exams</h3>
            <h2><?php echo $ongoing_exams; ?></h2>
        </div>
        <div class="card-icon">
            <i class="fa-solid fa-hourglass-half"></i>
        </div>
    </div>

    <div class="dashboard-card">
        <div>
            <h3>Completed Exams</h3>
            <h2><?php echo $completed_exams; ?></h2>
        </div>
        <div class="card-icon">
            <i class="fa-solid fa-circle-check"></i>
        </div>
    </div>

</div>

<!-- Quick Actions -->

<div class="dashboard-section">

<div class="section-header">

<h2>
Quick Actions
</h2>

</div>

<div
style="
display:flex;
gap:12px;
flex-wrap:wrap;
">

<?php if (canCreate('exams')) { ?><a
href="add.php"
class="btn btn-primary">

<i class="fa-solid fa-plus"></i>

Add Exam

</a><?php } ?>

<a
href="../results/index.php"
class="btn">

<i class="fa-solid fa-chart-column"></i>

Results

</a>

<a
href="../reports/report.php"
class="btn">

<i class="fa-solid fa-file-lines"></i>

Reports

</a>

</div>

</div>

<!-- Search -->

<div class="dashboard-section">

<form method="GET">

<div
style="
display:flex;
gap:12px;
flex-wrap:wrap;
">

<input

type="text"

name="search"

class="form-input"

placeholder="Search Exam, Type, Class, Section, Subject or Status"

value="<?php echo htmlspecialchars($search); ?>"

style="flex:1;min-width:250px;">

<button
type="submit"
class="btn btn-primary">

<i class="fa-solid fa-magnifying-glass"></i>

Search

</button>

<a
href="index.php"
class="btn">

Reset

</a>

</div>

</form>

</div>

<!-- Exams Table -->

<div class="dashboard-section">

<div class="section-header">

<h2>
All Exams
</h2>

</div>

<div class="table-responsive">

<table class="custom-table">

<thead>

<tr>

<th data-sort="true">
Exam Name
</th>
<th data-sort="true">
Type
</th>

<th data-sort="true">
Class
</th>

<th data-sort="true">
Section
</th>

<th data-sort="true">
Subject
</th>

<th data-sort="true">
Total Marks
</th>

<th data-sort="true">
Exam Date
</th>

<th data-sort="true">
Time
</th>

<th data-sort="true">
Status
</th>

<th>
Actions
</th>

</tr>

</thead>

<tbody>
    <?php

if(count($exams) > 0){

foreach($exams as $row){

?>

<tr>

<td>

<strong>
<?php echo htmlspecialchars($row['exam_name']); ?>
</strong>

<br>


</td>
<td>

<?php

echo ucwords(
str_replace(
'_',
' ',
$row['exam_type']
)
);

?>

</td>
<td>

<?php

echo !empty($row['class_name'])
? htmlspecialchars($row['class_name'])
: 'School Wide';

?>

</td>

<td>

<?php

echo !empty($row['section_name']) ? htmlspecialchars($row['section_name']) : 'All Sections';

?>

</td>

<td>

<?php

echo htmlspecialchars(

!empty($row['custom_subject'])
? $row['custom_subject']
: ($row['subject_name'] ?? '-')

);

?>

</td>

<td>

<?php

echo (int)$row['total_marks'];

?>

</td>

<td>

<?php

echo !empty($row['exam_date'])

? date(
'd M Y',
strtotime($row['exam_date'])
)

: '-';

?>

</td>

<td>

<?php

$start =
!empty($row['start_time'])
? date('h:i A',strtotime($row['start_time']))
: '';

$end =
!empty($row['end_time'])
? date('h:i A',strtotime($row['end_time']))
: '';

echo ($start || $end)
? $start . ' - ' . $end
: '-';

?>

</td>

<td>

<?php

$statusClass = 'danger';

if($row['status'] == 'ongoing'){
    $statusClass = 'info';
}
elseif($row['status'] == 'completed'){
    $statusClass = 'success';
}

?>

<span class="status <?php echo $statusClass; ?>">

<?php

echo ucfirst(
$row['status']
);

?>

</span>

</td>


<td>

<div
style="
display:flex;
gap:8px;
flex-wrap:wrap;
">

<a
href="view.php?id=<?php echo $row['exam_id']; ?>"
class="btn"
style="
background:#10b981;
color:white;
">

<i class="fa-solid fa-eye"></i>

View

</a>

<?php if (canEdit('exams')) { ?><a
href="edit.php?id=<?php echo $row['exam_id']; ?>"
class="btn btn-primary">

<i class="fa-solid fa-pen"></i>

Edit

</a><?php } ?>

<?php if (canDelete('exams')) { ?><a
href="delete.php?id=<?php echo $row['exam_id']; ?>"
class="btn"
style="
background:#ef4444;
color:white;
"
onclick="return confirm('Delete this exam?');">

<i class="fa-solid fa-trash"></i>

Delete

</a><?php } ?>

</div>

</td>

</tr>

<?php

}

}
else{

?>

<tr>

<td
colspan="10"
style="
text-align:center;
padding:40px;
">

<div
style="
display:flex;
flex-direction:column;
align-items:center;
gap:10px;
">

<i
class="fa-solid fa-file-signature"
style="
font-size:40px;
color:#94a3b8;
">
</i>

<p
style="
margin:0;
font-weight:500;
">

No exams found.

</p>

<a
href="add.php"
class="btn btn-primary">

<i class="fa-solid fa-plus"></i>

Create First Exam

</a>

</div>

</td>

</tr>

<?php

}

?>

</tbody>

</table>

</div>

</div>

</div>

</div>

</div>

<script src="../assets/js/common.js"></script>

</body>

</html>
