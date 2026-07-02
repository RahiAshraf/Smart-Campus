<?php

include('../config/auth.php');
include('../config/db.php');

$exam_id = isset($_GET['exam_id'])
? (int)$_GET['exam_id']
: 0;

$class_id = isset($_GET['class_id'])
? (int)$_GET['class_id']
: 0;

$section_id = isset($_GET['section_id'])
? (int)$_GET['section_id']
: 0;

$conditions = [];

if($exam_id > 0){
$conditions[] = "r.exam_id=$exam_id";
}

if($class_id > 0){
$conditions[] = "r.class_id=$class_id";
}

if($section_id > 0){
$conditions[] = "r.section_id=$section_id";
}

$where = '';

if(!empty($conditions)){

$where =
'WHERE ' .
implode(
' AND ',
$conditions
);

}

$reportQuery = mysqli_query(

$conn,

"SELECT

rm.*,

u.full_name,

st.student_id,

c.class_name,

sec.section_name,

e.exam_name,

e.total_marks

FROM result_marks rm

INNER JOIN results r
ON rm.result_id = r.result_id

INNER JOIN students st
ON rm.student_id = st.id

INNER JOIN users u
ON st.user_id = u.id

INNER JOIN exams e
ON r.exam_id = e.exam_id

LEFT JOIN classes c
ON r.class_id = c.class_id

LEFT JOIN sections sec
ON r.section_id = sec.section_id

$where

ORDER BY rm.marks_obtained DESC"

);

$exams = mysqli_query(
$conn,
"SELECT exam_id,exam_name
FROM exams
ORDER BY exam_name"
);

$classes = mysqli_query(
$conn,
"SELECT class_id,class_name
FROM classes
ORDER BY class_name"
);

$sections = mysqli_query(
$conn,
"SELECT section_id,section_name
FROM sections
ORDER BY section_name"
);

$summary = mysqli_fetch_assoc(

mysqli_query(

$conn,

"SELECT

COUNT(rm.mark_id) total_students,

AVG(rm.marks_obtained) average_marks,

MAX(rm.marks_obtained) highest_marks,

MIN(rm.marks_obtained) lowest_marks

FROM result_marks rm

INNER JOIN results r
ON rm.result_id=r.result_id

$where"

)

);

?><!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta
name="viewport"
content="width=device-width, initial-scale=1.0">

<title>
Result Reports
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

<link rel="stylesheet" href="../../assets/css/material-theme.css">
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
Result Reports
</h1>

<p>
Performance analytics and reports.
</p>

</div>

<div
style="
display:flex;
gap:10px;
flex-wrap:wrap;
">
<a
href="index.php"
class="btn">

<i class="fa-solid fa-arrow-left"></i>

Back

</a>
<button
onclick="window.print();"
class="btn">

<i class="fa-solid fa-print"></i>

Print

</button>

<a
href="export_csv.php?exam_id=<?php echo $exam_id; ?>&class_id=<?php echo $class_id; ?>&section_id=<?php echo $section_id; ?>"
class="btn btn-primary">

<i class="fa-solid fa-file-csv"></i>

Export CSV

</a>

<a href="export_excel.php?exam_id=<?php echo $exam_id; ?>&class_id=<?php echo $class_id; ?>&section_id=<?php echo $section_id; ?>" class="btn btn-primary">
<i class="fa-solid fa-file-excel"></i>
Excel
</a>
</div>

</div>

<!-- Filters -->

<div class="dashboard-section">

<form method="GET">

<div class="form-grid">

<div class="form-group">

<label>
Exam
</label>

<select
name="exam_id"
class="form-input">

<option value="">
All Exams
</option>

<?php while($exam=mysqli_fetch_assoc($exams)){ ?>

<option

value="<?php echo $exam['exam_id']; ?>"

<?php echo ($exam_id==$exam['exam_id']) ? 'selected' : ''; ?>

>

<?php echo htmlspecialchars($exam['exam_name']); ?>

</option>

<?php } ?>

</select>

</div>

<div class="form-group">

<label>
Class
</label>

<select
name="class_id"
class="form-input">

<option value="">
All Classes
</option>

<?php while($class=mysqli_fetch_assoc($classes)){ ?>

<option

value="<?php echo $class['class_id']; ?>"

<?php echo ($class_id==$class['class_id']) ? 'selected' : ''; ?>

>

<?php echo htmlspecialchars($class['class_name']); ?>

</option>

<?php } ?>

</select>

</div>

<div class="form-group">

<label>
Section
</label>

<select
name="section_id"
class="form-input">

<option value="">
All Sections
</option>

<?php while($section=mysqli_fetch_assoc($sections)){ ?>

<option

value="<?php echo $section['section_id']; ?>"

<?php echo ($section_id==$section['section_id']) ? 'selected' : ''; ?>

>

<?php echo htmlspecialchars($section['section_name']); ?>

</option>

<?php } ?>

</select>

</div>

</div>

<div style="margin-top:15px;">

<button
type="submit"
class="btn btn-primary">

Apply Filters

</button>

<a
href="report.php"
class="btn">

Reset

</a>

</div>

</form>

</div>

<!-- Summary -->

<div class="dashboard-grid">

<div class="dashboard-card">

<h3>Total Students</h3>

<h2>

<?php echo (int)$summary['total_students']; ?>

</h2>

</div>

<div class="dashboard-card">

<h3>Average Marks</h3>

<h2>

<?php

echo number_format(
(float)$summary['average_marks'],
2
);

?>

</h2>

</div>

<div class="dashboard-card">

<h3>Highest Marks</h3>

<h2>

<?php echo (float)$summary['highest_marks']; ?>

</h2>

</div>

<div class="dashboard-card">

<h3>Lowest Marks</h3>

<h2>

<?php echo (float)$summary['lowest_marks']; ?>

</h2>

</div>

</div>

<!-- Report Table -->

<div class="dashboard-section">

<div class="section-header">

<h2>
Student Performance Report
</h2>

</div>

<div class="table-responsive">

<table class="custom-table">

<thead>

<tr>

<th>Student</th>
<th>ID</th>
<th>Exam</th>
<th>Class</th>
<th>Section</th>
<th>Marks</th>
<th>Total</th>
<th>Percentage</th>

</tr>

</thead>

<tbody>

<?php

if(mysqli_num_rows($reportQuery)>0){

while($row=mysqli_fetch_assoc($reportQuery)){

$percentage = 0;

if($row['total_marks'] > 0){

$percentage =
($row['marks_obtained'] /
$row['total_marks']) * 100;

}

?>

<tr>

<td>

<?php echo htmlspecialchars($row['full_name']); ?>

</td>

<td>

<?php echo htmlspecialchars($row['student_id']); ?>

</td>

<td>

<?php echo htmlspecialchars($row['exam_name']); ?>

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

echo !empty($row['section_name'])
? htmlspecialchars($row['section_name'])
: 'All Sections';

?>

</td>

<td>

<?php echo $row['marks_obtained']; ?>

</td>

<td>

<?php echo $row['total_marks']; ?>

</td>

<td>

<?php

echo number_format(
$percentage,
2
);

?>%

</td>

</tr>

<?php

}

}
else{

?>

<tr>

<td colspan="8">

No report data found.

</td>

</tr>

<?php } ?>

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