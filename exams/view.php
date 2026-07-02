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

$exam_id = (int)$_GET['id'];

$query = mysqli_query(

$conn,

"SELECT

e.*,

c.class_name,

s.section_name,

sub.subject_name

FROM exams e

LEFT JOIN classes c
ON e.class_id=c.class_id

LEFT JOIN sections s
ON e.section_id=s.section_id

LEFT JOIN subjects sub
ON e.subject_id=sub.subject_id

WHERE e.exam_id='$exam_id'

LIMIT 1"

);

if(mysqli_num_rows($query) == 0){

header('Location:index.php?error=not_found');
exit;

}

$exam = mysqli_fetch_assoc($query);

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
View Exam | MindMerge SmartCampus
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
Exam Details
</h1>

<p>
View examination information.
</p>

</div>

<a
href="index.php"
class="btn">

<i class="fa-solid fa-arrow-left"></i>

Back

</a>

</div>

<div class="dashboard-section">

<div class="form-grid">

<div class="form-group">

<label>
Exam Name
</label>

<div class="form-input">

<?php echo htmlspecialchars($exam['exam_name']); ?>

</div>

</div>

<div class="form-group">

<label>
Exam Type
</label>

<div class="form-input">

<?php

echo ucwords(
str_replace(
'_',
' ',
$exam['exam_type']
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

echo htmlspecialchars(
$exam['class_name'] ?? 'School Wide'
);

?>

</div>

</div>

<div class="form-group">

<label>
Section
</label>

<div class="form-input">

<?php

echo htmlspecialchars(
$exam['section_name'] ?? '-'
);

?>

</div>

</div>

<div class="form-group">

<label>
Subject
</label>

<div class="form-input">

<?php

echo htmlspecialchars(

!empty($exam['custom_subject'])
? $exam['custom_subject']
: ($exam['subject_name'] ?? '-')

);

?>

</div>

</div>

<div class="form-group">

<label>
Total Marks
</label>

<div class="form-input">

<?php echo (int)$exam['total_marks']; ?>

</div>

</div>

<div class="form-group">

<label>
Exam Date
</label>

<div class="form-input">

<?php

echo !empty($exam['exam_date'])
? date(
'd M Y',
strtotime($exam['exam_date'])
)
: '-';

?>

</div>

</div>

<div class="form-group">

<label>
Time
</label>

<div class="form-input">

<?php

$start =
!empty($exam['start_time'])
? date(
'h:i A',
strtotime($exam['start_time'])
)
: '';

$end =
!empty($exam['end_time'])
? date(
'h:i A',
strtotime($exam['end_time'])
)
: '';

echo ($start || $end)
? $start . ' - ' . $end
: '-';

?>

</div>

</div>

<div class="form-group">

<label>
Status
</label>

<div class="form-input">

<?php echo ucfirst($exam['status']); ?>

</div>

</div>

<div class="form-group">

<label>
Created On
</label>

<div class="form-input">

<?php

echo date(
'd M Y h:i A',
strtotime($exam['created_at'])
);

?>

</div>

</div>

<div
class="form-group"
style="grid-column:1/-1;">

<label>
Description
</label>

<div
class="form-input"
style="min-height:120px;">

<?php

echo !empty($exam['description'])
? nl2br(htmlspecialchars($exam['description']))
: 'No description available.';

?>

</div>

</div>

</div>

<div
style="
margin-top:20px;
display:flex;
gap:12px;
flex-wrap:wrap;
">

<a
href="edit.php?id=<?php echo $exam['exam_id']; ?>"
class="btn btn-primary">

<i class="fa-solid fa-pen"></i>

Edit Exam

</a>

<a
href="index.php"
class="btn">

Close

</a>

</div>

</div>

</div>

</div>

</div>

<script src="../assets/js/common.js"></script>

</body>

</html>