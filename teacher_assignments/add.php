
<?php

include('../config/auth.php');
include('../config/db.php');

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

if(isset($_POST['assign_teacher'])){

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

section_id='$section_id'"

);

if(mysqli_num_rows($check_duplicate) > 0){

$error =
"This assignment already exists.";

}
else{

$insert = mysqli_query(

$conn,

"INSERT INTO teacher_assignments(

teacher_id,
subject_id,
class_id,
section_id,
assignment_role

)

VALUES(

'$teacher_id',
'$subject_id',
'$class_id',
'$section_id',
'$assignment_role'

)"

);

if(!$insert){

$error = mysqli_error($conn);

}
else{

header(
"Location:index.php?success=added"
);

exit();

}

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
Assign Teacher | MindMerge SmartCampus
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
Assign Teacher
</h1>

<p>
Assign teachers to subjects, classes and sections.
</p>

</div>

<div
style="
display:flex;
gap:12px;
flex-wrap:wrap;
">

<a
href="index.php"
class="btn">

<i class="fa-solid fa-arrow-left"></i>

Back to Assignments

</a>

</div>

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

<option value="">
Select Teacher
</option>

<?php

while($teacher = mysqli_fetch_assoc($teachers)){

?>

<option
value="<?php echo $teacher['id']; ?>">

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

<option value="">
Select Subject
</option>

<?php

while($subject = mysqli_fetch_assoc($subjects)){

?>

<option
value="<?php echo $subject['subject_id']; ?>">

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

<option value="primary">
Primary
</option>

<option value="co_primary">
Co Primary
</option>

<option value="lab_incharge">
Lab Incharge
</option>

<option value="lab_faculty">
Lab Faculty
</option>

<option value="lab_assistant">
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

<option value="">
Select Class
</option>

<?php

while($class = mysqli_fetch_assoc($classes)){

?>

<option
value="<?php echo $class['class_id']; ?>">

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
Select Class First
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
name="assign_teacher"
class="btn btn-primary">

<i class="fa-solid fa-link"></i>

Create Assignment

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

classSelect.addEventListener(
'change',
function(){

const classId = this.value;

sectionSelect.innerHTML =
'<option value="">Select Section</option>';

if(classId === ''){

sectionSelect.innerHTML =
'<option value="">Select Class First</option>';

return;

}

sections.forEach(function(section){

if(section.class_id === classId){

sectionSelect.innerHTML +=

'<option value="' +
section.id +
'">' +
section.name +
'</option>';

}

});

}
);

</script>

</body>
</html>