<?php

include('../config/auth.php');
include('../config/db.php');

$error = '';

if(isset($_POST['create_timetable'])){

$class_id = intval($_POST['class_id']);

$section_id = intval($_POST['section_id']);

$template_id = intval($_POST['template_id']);



$academic_year = mysqli_real_escape_string(
$conn,
trim($_POST['academic_year'])
);

$effective_from = !empty($_POST['effective_from'])
? $_POST['effective_from']
: NULL;

$effective_to = !empty($_POST['effective_to'])
? $_POST['effective_to']
: NULL;

$status = mysqli_real_escape_string(
$conn,
$_POST['status']
);

$check = mysqli_query(

$conn,

"SELECT timetable_id

FROM timetables

WHERE

class_id='$class_id'

AND

section_id='$section_id'

AND

template_id='$template_id'"

);

if(mysqli_num_rows($check) > 0){

$error =
'A timetable of this type already exists for this class and section.';

}
else{

$effective_from_sql =
$effective_from
? "'$effective_from'"
: "NULL";

$effective_to_sql =
$effective_to
? "'$effective_to'"
: "NULL";

mysqli_query(

$conn,

"INSERT INTO timetables(

class_id,
section_id,
template_id,
academic_year,
status,
effective_from,
effective_to

)

VALUES(

'$class_id',
'$section_id',
'$template_id',
'$academic_year',
'$status',
$effective_from_sql,
$effective_to_sql

)"

);

$new_id = mysqli_insert_id($conn);

header(
"Location:entries.php?id=".$new_id
);

exit();

}

}

$class_query = mysqli_query(

$conn,

"SELECT *

FROM classes

WHERE status='active'

ORDER BY class_name ASC"

);
$sections = [];

$section_query = mysqli_query(

$conn,

"SELECT

section_id,
class_id,
section_name

FROM sections

WHERE status='active'

ORDER BY section_name ASC"

);

while($section = mysqli_fetch_assoc($section_query)){

$sections[] = $section;

}

$template_query = mysqli_query(

$conn,

"SELECT *

FROM period_templates

WHERE status='active'

ORDER BY template_name ASC"

);

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta
name="viewport"
content="width=device-width, initial-scale=1.0">

<title>
Create Timetable | MindMerge SmartCampus
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
Create Timetable
</h1>

<p>
Create and manage class schedules using predefined schedule templates.
</p>

</div>

<a
href="index.php"
class="btn">

<i class="fa-solid fa-arrow-left"></i>

Back

</a>

</div>

<?php if($error!=''){ ?>

<div
style="
background:#fee2e2;
color:#991b1b;
padding:14px 18px;
border-radius:14px;
margin-bottom:20px;
">

<?php echo $error; ?>

</div>

<?php } ?>

<div class="dashboard-section">

<form method="POST">

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

while($class = mysqli_fetch_assoc($class_query)){

?>

<option
value="<?php echo $class['class_id']; ?>">

<?php

echo htmlspecialchars(
$class['class_name']
);

?>

</option>

<?php

}

?>

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
required
disabled>

<option value="">
Select Class First
</option>

</select>

<?php

while($section = mysqli_fetch_assoc($section_query)){

?>

<option
value="<?php echo $section['section_id']; ?>">

<?php

echo htmlspecialchars(
$section['class_name']
);

?>

 -

<?php

echo htmlspecialchars(
$section['section_name']
);

?>

</option>

<?php

}

?>

</select>

</div>

</div>

<div class="form-grid">

<div class="form-group">

<label class="form-label">
Schedule Template
</label>

<select
name="template_id"
class="form-select"
required>

<option value="">
Select Template
</option>

<?php

while($template = mysqli_fetch_assoc($template_query)){

?>

<option
value="<?php echo $template['template_id']; ?>">

<?php

echo htmlspecialchars(
$template['template_code']
);

?>

 -

<?php

echo htmlspecialchars(
$template['template_name']
);

?>

</option>

<?php

}

?>

</select>

</div>

</div>

<div class="form-grid">

<div class="form-group">

<label class="form-label">
Academic Year
</label>

<input
type="text"
name="academic_year"
class="form-input"
placeholder="2026-27"
required>

</div>

<div class="form-group">

<label class="form-label">
Status
</label>

<select
name="status"
class="form-select">

<option value="active">
Active
</option>

<option value="inactive">
Inactive
</option>

</select>

</div>

</div>

<div class="form-grid">

<div class="form-group">

<label class="form-label">
Effective From
</label>

<input
type="date"
name="effective_from"
class="form-input">

</div>

<div class="form-group">

<label class="form-label">
Effective To
</label>

<input
type="date"
name="effective_to"
class="form-input">

</div>

</div>

<div
style="
display:flex;
gap:12px;
flex-wrap:wrap;
margin-top:20px;
">

<button
type="submit"
name="create_timetable"
class="btn btn-primary">

<i class="fa-solid fa-floppy-disk"></i>

Create Timetable

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

const sections = <?php echo json_encode($sections); ?>;

const classSelect =
document.getElementById('class_id');

const sectionSelect =
document.getElementById('section_id');

classSelect.addEventListener(

'change',

function(){

const classId = this.value;

sectionSelect.innerHTML = '';

if(classId === ''){

sectionSelect.disabled = true;

sectionSelect.innerHTML =

'<option value="">Select Class First</option>';

return;

}

sectionSelect.disabled = false;

sectionSelect.innerHTML =

'<option value="">Select Section</option>';

sections.forEach(function(section){

if(section.class_id == classId){

const option =
document.createElement('option');

option.value =
section.section_id;

option.textContent =
section.section_name;

sectionSelect.appendChild(option);

}

});

}

);

</script>
</body>

</html>