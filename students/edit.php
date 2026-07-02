
<?php

include('../config/auth.php');
include('../config/db.php');

$id = intval($_GET['id']);

$query = mysqli_query(

$conn,

"SELECT

s.*,

u.full_name,
u.email,
u.phone

FROM students s

LEFT JOIN users u
ON s.user_id = u.id

WHERE s.id='$id'"

);

$row = mysqli_fetch_assoc($query);

if(!$row){

header("Location:index.php");
exit();

}

$class_query = mysqli_query(
$conn,
"SELECT *
FROM classes
WHERE status='active'
ORDER BY class_name ASC"
);

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

$error = '';

if(isset($_POST['update_student'])){

$full_name = mysqli_real_escape_string(
$conn,
trim($_POST['full_name'])
);

$email = mysqli_real_escape_string(
$conn,
trim($_POST['email'])
);

$phone = mysqli_real_escape_string(
$conn,
trim($_POST['phone'])
);

$class_id = intval($_POST['class_id']);

$section_id = intval($_POST['section_id']);

$dob = mysqli_real_escape_string(
$conn,
$_POST['dob']
);

$gender = mysqli_real_escape_string(
$conn,
$_POST['gender']
);

$parent_phone = mysqli_real_escape_string(
$conn,
trim($_POST['parent_phone'])
);

$address = mysqli_real_escape_string(
$conn,
trim($_POST['address'])
);

$check_email = mysqli_query(

$conn,

"SELECT *

FROM users

WHERE email='$email'

AND id!='".$row['user_id']."'"
);

if(mysqli_num_rows($check_email) > 0){

$error = "Email already exists.";

}
else{

$class_data = mysqli_fetch_assoc(

mysqli_query(
$conn,
"SELECT class_name
FROM classes
WHERE class_id='$class_id'"
)

);

$section_data = mysqli_fetch_assoc(

mysqli_query(
$conn,
"SELECT section_name
FROM sections
WHERE section_id='$section_id'"
)

);

mysqli_query(

$conn,

"UPDATE users

SET

full_name='$full_name',
email='$email',
phone='$phone'

WHERE id='".$row['user_id']."'"
);

mysqli_query(

$conn,

"UPDATE students

SET

class_id='$class_id',
section_id='$section_id',
class_name='".$class_data['class_name']."',
section_name='".$section_data['section_name']."',
dob='$dob',
gender='$gender',
address='$address',
parent_phone='$parent_phone'

WHERE id='$id'"
);

header("Location:index.php?success=updated");
exit();

}

}

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>
Edit Student | MindMerge SmartCampus
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
Edit Student
</h1>

<p>
Update student information.
</p>

</div>

<a
href="index.php"
class="btn">

<i class="fa-solid fa-arrow-left"></i>

Back to Students

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

<div class="section-header">

<h2>
Account Information
</h2>

</div>

<div class="form-grid">

<div class="form-group">

<label class="form-label">
Student ID
</label>

<input
type="text"
class="form-input"
value="<?php echo htmlspecialchars($row['student_id']); ?>"
readonly>

</div>

<div class="form-group">

<label class="form-label">
Full Name
</label>

<input
type="text"
name="full_name"
class="form-input"
value="<?php echo htmlspecialchars($row['full_name']); ?>"
required>

</div>

</div>

<div class="form-grid">

<div class="form-group">

<label class="form-label">
Email Address
</label>

<input
type="email"
name="email"
class="form-input"
value="<?php echo htmlspecialchars($row['email']); ?>"
required>

</div>

<div class="form-group">

<label class="form-label">
Phone Number
</label>

<input
type="text"
name="phone"
class="form-input"
value="<?php echo htmlspecialchars($row['phone']); ?>"
required>

</div>

</div>

<br>

<div class="section-header">

<h2>
Student Information
</h2>

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

<?php while($class = mysqli_fetch_assoc($class_query)){ ?>

<option
value="<?php echo $class['class_id']; ?>"
<?php if($row['class_id'] == $class['class_id']) echo 'selected'; ?>>

<?php echo htmlspecialchars($class['class_name']); ?>

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

<div class="form-grid">

<div class="form-group">

<label class="form-label">
Date Of Birth
</label>

<input
type="date"
name="dob"
class="form-input"
value="<?php echo $row['dob']; ?>">

</div>

<div class="form-group">

<label class="form-label">
Gender
</label>

<select
name="gender"
class="form-select">

<option value="male" <?php if($row['gender']=='male') echo 'selected'; ?>>
Male
</option>

<option value="female" <?php if($row['gender']=='female') echo 'selected'; ?>>
Female
</option>

<option value="other" <?php if($row['gender']=='other') echo 'selected'; ?>>
Other
</option>

</select>

</div>

</div>

<div class="form-group">

<label class="form-label">
Parent Phone
</label>

<input
type="text"
name="parent_phone"
class="form-input"
value="<?php echo htmlspecialchars($row['parent_phone']); ?>">

</div>

<div class="form-group">

<label class="form-label">
Address
</label>

<textarea
name="address"
class="form-textarea"><?php echo htmlspecialchars($row['address']); ?></textarea>

</div>

<div
style="
display:flex;
gap:12px;
flex-wrap:wrap;
">

<button
type="submit"
name="update_student"
class="btn btn-primary">

<i class="fa-solid fa-floppy-disk"></i>

Update Student

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
$section_query,
0
);

while($section = mysqli_fetch_assoc($section_query)){

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
