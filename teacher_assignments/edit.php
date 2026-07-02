
<?php

include('../config/auth.php');
include('../config/db.php');

$id = intval($_GET['id']);

$query = mysqli_query(

$conn,

"SELECT *

FROM teacher_assignments

WHERE assignment_id='$id'"

);

$row = mysqli_fetch_assoc($query);

if(!$row){

header("Location:index.php");
exit();

}

$error = '';

$teachers = mysqli_query(

$conn,

"SELECT

t.id,
t.teacher_id,
t.qualification,

u.full_name

FROM teachers t

LEFT JOIN users u
ON t.user_id = u.id

ORDER BY u.full_name ASC"

);

$subjects = mysqli_query(

$conn,

"SELECT *

FROM subjects

WHERE status='active'

ORDER BY subject_name ASC"

);

$classes = mysqli_query(

$conn,

"SELECT *

FROM classes

WHERE status='active'

ORDER BY class_name ASC"

);

$sections = mysqli_query(

$conn,

"SELECT

section_id,
class_id,
section_name

FROM sections

WHERE status='active'

ORDER BY section_name ASC"

);

if(isset($_POST['update_assignment'])){

$teacher_id = intval($_POST['teacher_id']);

$subject_id = intval($_POST['subject_id']);

$assignment_role =
mysqli_real_escape_string(
$conn,
$_POST['assignment_role']
);

$class_id = intval($_POST['class_id']);

$section_id = intval($_POST['section_id']);

$check_duplicate = mysqli_query(

$conn,

"SELECT *

FROM teacher_assignments

WHERE

teacher_id='$teacher_id'

AND

subject_id='$subject_id'

AND

class_id='$class_id'

AND

section_id='$section_id'

AND

assignment_id!='$id'"

);

if(mysqli_num_rows($check_duplicate) > 0){

$error =
"This assignment already exists.";

}
else{

mysqli_query(

$conn,

"UPDATE teacher_assignments

SET

teacher_id='$teacher_id',
subject_id='$subject_id',
assignment_role='$assignment_role',
class_id='$class_id',
section_id='$section_id'

WHERE assignment_id='$id'"

);

header(
"Location:index.php?success=updated"
);

exit();

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
Edit Assignment | MindMerge SmartCampus
</title>

<link rel="stylesheet" href="../assets/css/global.css">
<link rel="stylesheet" href="../assets/css/layout.css">
<link rel="stylesheet" href="../assets/css/components.css">

<link rel="stylesheet"
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
Edit Assignment
</h1>

<p>
Update teacher assignment information.
</p>

</div>

<a
href="index.php"
class="btn">

<i class="fa-solid fa-arrow-left"></i>

Back to Assignments

</a>

</div>

<?php if($error != ''){ ?>

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

<div class="form-group">

<label class="form-label">
Teacher
</label>

<select
name="teacher_id"
class="form-select"
required>

<?php

while($teacher = mysqli_fetch_assoc($teachers)){

?>

<option

value="<?php echo $teacher['id']; ?>"

<?php

if($row['teacher_id'] == $teacher['id']){

echo 'selected';

}

?>>

<?php

echo
$teacher['teacher_id']
.
' - '
.
$teacher['full_name']
.
' ('
.
$teacher['qualification']
.
')';

?>

</option>

<?php } ?>

</select>

</div>

<div class="form-group">

<label class="form-label">
Subject
</label>

<select
name="subject_id"
class="form-select"
required>

<?php

while($subject = mysqli_fetch_assoc($subjects)){

?>

<option

value="<?php echo $subject['subject_id']; ?>"

<?php

if($row['subject_id'] == $subject['subject_id']){

echo 'selected';

}

?>>

<?php

echo
$subject['subject_code']
.
' - '
.
$subject['subject_name'];

?>

</option>

<?php } ?>

</select>

</div>
<div class="form-group">

<label class="form-label">

Assignment Role

</label>

<select
name="assignment_role"
class="form-select"
required>

<option
value="primary"
<?php if($row['assignment_role']=='primary') echo 'selected'; ?>>

Primary

</option>

<option
value="co_primary"
<?php if($row['assignment_role']=='co_primary') echo 'selected'; ?>>

Co Primary

</option>

<option
value="lab_incharge"
<?php if($row['assignment_role']=='lab_incharge') echo 'selected'; ?>>

Lab Incharge

</option>

<option
value="lab_faculty"
<?php if($row['assignment_role']=='lab_faculty') echo 'selected'; ?>>

Lab Faculty

</option>

<option
value="lab_assistant"
<?php if($row['assignment_role']=='lab_assistant') echo 'selected'; ?>>

Lab Assistant

</option>

</select>

</div>
<div class="form-grid">

<div class="form-group">

<label class="form-label">
Class
</label>

<select
name="class_id"
id="class_id"
class="form-select"
required>

<?php

while($class = mysqli_fetch_assoc($classes)){

?>

<option

value="<?php echo $class['class_id']; ?>"

<?php

if($row['class_id'] == $class['class_id']){

echo 'selected';

}

?>>

<?php echo $class['class_name']; ?>

</option>

<?php } ?>

</select>

</div>

<div class="form-group">

<label class="form-label">
Section
</label>
<select
name="section_id"
id="section_id"
class="form-select"
required>

<option value="">
Select Section
</option>

</select>
</div>

</div>

<div
style="
display:flex;
gap:12px;
flex-wrap:wrap;
">

<button
type="submit"
name="update_assignment"
class="btn btn-primary">

<i class="fa-solid fa-floppy-disk"></i>

Update Assignment

</button>

<a
href="index.php"
class="btn">

Cancel

</a>

</div>

</form>
<input
type="hidden"
id="current_class_id"
value="<?php echo $row['class_id']; ?>">

<input
type="hidden"
id="current_section_id"
value="<?php echo $row['section_id']; ?>">
</div>

</div>

</div>

</div>

<script src="../assets/js/common.js"></script>
<script>

const sections = [

<?php

mysqli_data_seek(
$sections,
0
);

while($section = mysqli_fetch_assoc($sections)){

?>

{
id:'<?php echo $section['section_id']; ?>',
class_id:'<?php echo $section['class_id']; ?>',
name:'<?php echo htmlspecialchars($section['section_name'],ENT_QUOTES); ?>'
},

<?php

}

?>

];

const classSelect =
document.getElementById('class_id');

const sectionSelect =
document.getElementById('section_id');

const currentClass =
document.getElementById('current_class_id').value;

const currentSection =
document.getElementById('current_section_id').value;

function loadSections(classId, selectedSection=''){

sectionSelect.innerHTML =
'<option value="">Select Section</option>';

sections.forEach(function(section){

if(section.class_id === classId){

const option =
document.createElement('option');

option.value = section.id;

option.textContent = section.name;

if(section.id === selectedSection){

option.selected = true;

}

sectionSelect.appendChild(option);

}

});

}

loadSections(
currentClass,
currentSection
);

classSelect.addEventListener(
'change',
function(){

loadSections(this.value);

}
);

</script>
</body>
</html>