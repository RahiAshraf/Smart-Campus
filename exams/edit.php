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

$examQuery = mysqli_query(

$conn,

"SELECT *

FROM exams

WHERE exam_id='$exam_id'

LIMIT 1"

);

if(mysqli_num_rows($examQuery) == 0){

header('Location:index.php?error=not_found');
exit;

}

$exam =
mysqli_fetch_assoc(
$examQuery
);

$error = '';

$classes = mysqli_query(

$conn,

"SELECT *

FROM classes

WHERE status='active'

ORDER BY class_name"

);

$subjects = mysqli_query(

$conn,

"SELECT *

FROM subjects

WHERE status='active'

ORDER BY subject_name"

);

$selectedClass =
(int)($exam['class_id'] ?? 0);

$sections = mysqli_query(

$conn,

"SELECT *

FROM sections

WHERE class_id='$selectedClass'

ORDER BY section_name"

);

if($_SERVER['REQUEST_METHOD'] == 'POST'){

$exam_name = mysqli_real_escape_string(
$conn,
trim($_POST['exam_name'])
);

$exam_type = mysqli_real_escape_string(
$conn,
$_POST['exam_type']
);

$class_id =
!empty($_POST['class_id'])
?
(int)$_POST['class_id']
:
'NULL';

$section_id =
!empty($_POST['section_id'])
?
(int)$_POST['section_id']
:
'NULL';

$subject_id =
!empty($_POST['subject_id'])
?
(int)$_POST['subject_id']
:
'NULL';

$custom_subject = mysqli_real_escape_string(
$conn,
trim($_POST['custom_subject'])
);

$total_marks =
(int)$_POST['total_marks'];

$exam_date = mysqli_real_escape_string(
$conn,
$_POST['exam_date']
);

$start_time = mysqli_real_escape_string(
$conn,
$_POST['start_time']
);

$end_time = mysqli_real_escape_string(
$conn,
$_POST['end_time']
);

$description = mysqli_real_escape_string(
$conn,
trim($_POST['description'])
);

$status = mysqli_real_escape_string(
$conn,
$_POST['status']
);

if(empty($exam_name)){

$error =
'Exam Name is required.';

}
elseif(
empty($subject_id)
&&
empty($custom_subject)
){

$error =
'Select a subject or enter custom subject.';

}
else{

$custom_subject_sql =
!empty($custom_subject)
?
"'$custom_subject'"
:
"NULL";

mysqli_query(

$conn,

"UPDATE exams SET

exam_name='$exam_name',
exam_type='$exam_type',
class_id=$class_id,
section_id=$section_id,
subject_id=$subject_id,
custom_subject=$custom_subject_sql,
total_marks='$total_marks',
exam_date='$exam_date',
start_time='$start_time',
end_time='$end_time',
description='$description',
status='$status'

WHERE exam_id='$exam_id'"

);

header(
'Location:index.php?success=updated'
);

exit;

}

}
?>
<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta
name="viewport"
content="width=device-width, initial-scale=1.0">

<title>
Edit Exam | MindMerge SmartCampus
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
Edit Exam
</h1>

<p>
Update examination details.
</p>

</div>

<a
href="index.php"
class="btn">

<i class="fa-solid fa-arrow-left"></i>

Back

</a>

</div>

<?php if(!empty($error)){ ?>

<div
style="
background:#fee2e2;
color:#991b1b;
padding:14px 18px;
border-radius:14px;
margin-bottom:20px;
font-weight:500;
">

<?php echo $error; ?>

</div>

<?php } ?>

<div class="dashboard-section">

<form method="POST">

<div class="form-grid">

<div class="form-group">

<label>
Exam Name
</label>

<input
type="text"
name="exam_name"
class="form-input"
required
value="<?php echo htmlspecialchars($exam['exam_name']); ?>">

</div>

<div class="form-group">

<label>
Exam Type
</label>

<select
name="exam_type"
class="form-input">

<option value="unit_test" <?php echo ($exam['exam_type']=='unit_test')?'selected':''; ?>>
Unit Test
</option>

<option value="mid_exam" <?php echo ($exam['exam_type']=='mid_exam')?'selected':''; ?>>
Mid Exam
</option>

<option value="semester" <?php echo ($exam['exam_type']=='semester')?'selected':''; ?>>
Semester
</option>

<option value="annual" <?php echo ($exam['exam_type']=='annual')?'selected':''; ?>>
Annual
</option>

<option value="custom" <?php echo ($exam['exam_type']=='custom')?'selected':''; ?>>
Custom
</option>

</select>

</div>

<div class="form-group">

<label>
Class
</label>

<select
name="class_id"
id="class_id"
class="form-input">

<option value="">
School Wide
</option>

<?php
while($class=mysqli_fetch_assoc($classes)){
?>

<option
value="<?php echo $class['class_id']; ?>"
<?php echo ($exam['class_id']==$class['class_id']) ? 'selected' : ''; ?>>

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
id="section_id"
class="form-input">

<option value="">
All Sections
</option>

<?php
while($section=mysqli_fetch_assoc($sections)){
?>

<option
value="<?php echo $section['section_id']; ?>"
<?php echo ($exam['section_id']==$section['section_id']) ? 'selected' : ''; ?>>

<?php echo htmlspecialchars($section['section_name']); ?>

</option>

<?php } ?>

</select>

</div>

<div class="form-group">

<label>
Existing Subject
</label>

<select
name="subject_id"
class="form-input">

<option value="">
Select Subject
</option>

<?php
while($subject=mysqli_fetch_assoc($subjects)){
?>

<option
value="<?php echo $subject['subject_id']; ?>"
<?php echo ($exam['subject_id']==$subject['subject_id']) ? 'selected' : ''; ?>>

<?php echo htmlspecialchars($subject['subject_name']); ?>

</option>

<?php } ?>

</select>

</div>

<div class="form-group">

<label>
Custom Subject
</label>

<input
type="text"
name="custom_subject"
class="form-input"
value="<?php echo htmlspecialchars($exam['custom_subject']); ?>">

</div>

<div class="form-group">

<label>
Total Marks
</label>

<input
type="number"
name="total_marks"
class="form-input"
min="1"
required
value="<?php echo $exam['total_marks']; ?>">

</div>

<div class="form-group">

<label>
Exam Date
</label>

<input
type="date"
name="exam_date"
class="form-input"
required
value="<?php echo $exam['exam_date']; ?>">

</div>

<div class="form-group">

<label>
Start Time
</label>

<input
type="time"
name="start_time"
class="form-input"
value="<?php echo $exam['start_time']; ?>">

</div>

<div class="form-group">

<label>
End Time
</label>

<input
type="time"
name="end_time"
class="form-input"
value="<?php echo $exam['end_time']; ?>">

</div>

<div class="form-group">

<label>
Status
</label>

<select
name="status"
class="form-input">

<option value="upcoming"
<?php echo ($exam['status']=='upcoming')?'selected':''; ?>>
Upcoming
</option>

<option value="ongoing"
<?php echo ($exam['status']=='ongoing')?'selected':''; ?>>
Ongoing
</option>

<option value="completed"
<?php echo ($exam['status']=='completed')?'selected':''; ?>>
Completed
</option>

</select>

</div>

<div
class="form-group"
style="grid-column:1/-1;">

<label>
Description
</label>

<textarea
name="description"
class="form-input"
rows="5"><?php echo htmlspecialchars($exam['description']); ?></textarea>

</div>

</div>

<div
style="
margin-top:20px;
display:flex;
gap:12px;
flex-wrap:wrap;
">

<button
type="submit"
class="btn btn-primary">

<i class="fa-solid fa-floppy-disk"></i>

Update Exam

</button>

<a
href="index.php"
class="btn">

Cancel

</a>

</div>

</form>

</div>

</div>

</div>

</div>

<script>

document
.getElementById('class_id')
.addEventListener(
'change',
function(){

let classId = this.value;

let sectionSelect =
document.getElementById(
'section_id'
);

if(classId === ''){

sectionSelect.innerHTML =
'<option value="">Select Class First</option>';

return;

}

fetch(
'get_sections.php?class_id=' + classId
)

.then(response => response.text())

.then(data => {

sectionSelect.innerHTML = data;

})

.catch(error => {

console.error(error);

});

}
);

</script>

<script src="../assets/js/common.js"></script>

</body>

</html>