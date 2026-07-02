<?php

include('../config/auth.php');
include('../config/db.php');

if(
!isset($_GET['id'])
||
!is_numeric($_GET['id'])
){

header('Location:index.php');
exit;

}

$result_id = (int)$_GET['id'];

$resultQuery = mysqli_query(

$conn,

"SELECT

r.*,

e.exam_name,
e.exam_type,
e.total_marks,
e.exam_date,
e.start_time,
e.end_time,

c.class_name,

s.section_name

FROM results r

INNER JOIN exams e
ON r.exam_id=e.exam_id

LEFT JOIN classes c
ON r.class_id=c.class_id

LEFT JOIN sections s
ON r.section_id=s.section_id

WHERE r.result_id='$result_id'

LIMIT 1"

);

if(mysqli_num_rows($resultQuery)==0){

header('Location:index.php?error=not_found');
exit;

}

$result =
mysqli_fetch_assoc(
$resultQuery
);
if(
isset($_GET['publish'])
&&
$_GET['publish']==1
){

$markCheck = mysqli_fetch_assoc(

mysqli_query(

$conn,

"SELECT COUNT(*) total

FROM result_marks

WHERE result_id='$result_id'"

)

);

if($markCheck['total'] > 0){

mysqli_query(

$conn,

"UPDATE results

SET

status='published',
published_at=NOW()

WHERE result_id='$result_id'"

);

}

header(
'Location:view.php?id=' .
$result_id
);

exit;

}

$marksQuery = mysqli_query(

$conn,

"SELECT

rm.*,

st.student_id,

u.full_name

FROM result_marks rm

INNER JOIN students st
ON rm.student_id=st.id

INNER JOIN users u
ON st.user_id=u.id

WHERE rm.result_id='$result_id'

ORDER BY u.full_name ASC"

);

$stats = mysqli_fetch_assoc(

mysqli_query(

$conn,

"SELECT

COUNT(*) total_students,

AVG(marks_obtained) avg_marks,

MAX(marks_obtained) highest_marks,

MIN(marks_obtained) lowest_marks

FROM result_marks

WHERE result_id='$result_id'"

)

);

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
View Result
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
Result Details
</h1>

<p>

<?php

echo htmlspecialchars(
$result['exam_name']
);

?>

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
<a
href="export_csv.php?id=<?php echo $result_id; ?>"
class="btn btn-primary">

CSV

</a>

<a
href="export_excel.php?id=<?php echo $result_id; ?>"
class="btn btn-primary">

Excel

</a>

<a
href="report.php?id=<?php echo $result_id; ?>"
class="btn btn-primary">

<i class="fa-solid fa-print"></i>

Print Report

</a>
<a
href="mark.php?id=<?php echo $result_id; ?>"
class="btn">

<i class="fa-solid fa-pen"></i>

Edit Marks

</a>
<?php if($result['status']!='published'){ ?>

<a
href="?id=<?php echo $result_id; ?>&publish=1"
class="btn btn-primary"
onclick="return confirm('Publish result?');">

Publish

</a>

<?php } ?>

</div>

</div>

<!-- Statistics -->

<div class="dashboard-grid">

<div class="dashboard-card">

<h3>
Students
</h3>

<h2>

<?php

echo (int)$stats['total_students'];

?>

</h2>

</div>

<div class="dashboard-card">

<h3>
Average
</h3>

<h2>

<?php

echo number_format(
(float)$stats['avg_marks'],
2
);

?>

</h2>

</div>

<div class="dashboard-card">

<h3>
Highest
</h3>

<h2>

<?php

echo number_format(
(float)$stats['highest_marks'],
2
);

?>

</h2>

</div>

<div class="dashboard-card">

<h3>
Lowest
</h3>

<h2>

<?php

echo number_format(
(float)$stats['lowest_marks'],
2
);

?>

</h2>

</div>

</div>

<!-- Exam Information -->

<div class="dashboard-section">

<div class="section-header">

<h2>
Exam Information
</h2>

</div>

<div class="form-grid">

<div class="form-group">

<label>
Exam
</label>

<div class="form-input">

<?php echo htmlspecialchars($result['exam_name']); ?>

</div>

</div>

<div class="form-group">

<label>
Type
</label>

<div class="form-input">

<?php

echo ucwords(
str_replace(
'_',
' ',
$result['exam_type']
)
);

?>

</div>

</div>

<div class="form-group">

<label>
Class
</label>

<div class="form-input">

<?php

echo !empty($result['class_name'])
? htmlspecialchars($result['class_name'])
: 'School Wide';

?>

</div>

</div>

<div class="form-group">

<label>
Section
</label>

<div class="form-input">

<?php

echo !empty($result['section_name'])
? htmlspecialchars($result['section_name'])
: 'All Sections';

?>

</div>

</div>

<div class="form-group">

<label>
Exam Date
</label>

<div class="form-input">

<?php

echo !empty($result['exam_date'])
? date(
'd M Y',
strtotime($result['exam_date'])
)
: '-';

?>

</div>

</div>

<div class="form-group">

<label>
Exam Time
</label>

<div class="form-input">

<?php

if(
!empty($result['start_time'])
&&
!empty($result['end_time'])
){

echo
date(
'h:i A',
strtotime($result['start_time'])
)
.
' - '
.
date(
'h:i A',
strtotime($result['end_time'])
);

}
else{

echo '-';

}

?>

</div>

</div>

<div class="form-group">

<label>
Total Marks
</label>

<div class="form-input">

<?php echo $result['total_marks']; ?>

</div>

</div>
<div class="form-group">

<label>
Result Status
</label>

<div class="form-input">

<?php

echo ucfirst(
$result['status']
);

?>

</div>

</div>

</div>

</div>

<!-- Student Marks -->

<div class="dashboard-section">

<div class="section-header">

<h2>
Student Results
</h2>

</div>

<div class="table-responsive">

<table class="custom-table">

<thead>

<tr>


<th>
Student ID
</th>

<th>
Student Name
</th>

<th>
Marks
</th>

<th>
Percentage
</th>

<th>
Remarks
</th>

</tr>

</thead>

<tbody>

<?php

if(mysqli_num_rows($marksQuery)>0){

while($row=mysqli_fetch_assoc($marksQuery)){

$percentage = 0;

if($result['total_marks'] > 0){

$percentage =
($row['marks_obtained'] /
$result['total_marks']) * 100;

}

?>

<tr>

<td>

<?php

echo htmlspecialchars(
$row['student_id']
);

?>

</td>

<td>

<?php

echo htmlspecialchars(
$row['full_name']
);

?>

</td>

<td>

<?php

echo $row['marks_obtained'];

?>

/

<?php

echo $result['total_marks'];

?>

</td>

<td>

<?php

echo number_format(
$percentage,
2
);

?>

%

</td>

<td>

<?php

echo htmlspecialchars(
$row['remarks']
?? '-'
);

?>

</td>

</tr>

<?php

}

}
else{

?>

<tr>

<td
colspan="5"
style="
text-align:center;
padding:40px;
">

No marks entered yet.

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