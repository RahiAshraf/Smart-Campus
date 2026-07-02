
<?php

include('../config/auth.php');
include('../config/db.php');

$error = '';

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

if(isset($_POST['add_student'])){

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

$password = $_POST['password'];

$last_student = mysqli_fetch_assoc(

mysqli_query(

$conn,

"SELECT student_id

FROM students

ORDER BY id DESC

LIMIT 1"

)

);

if($last_student){

$last_number = intval(
substr(
$last_student['student_id'],
3
)
);

$student_id =
'STU' .
str_pad(
$last_number + 1,
5,
'0',
STR_PAD_LEFT
);

}
else{

$student_id = 'STU00001';

}
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
WHERE email='$email'"
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

$hashed_password = password_hash(
$password,
PASSWORD_DEFAULT
);

mysqli_query(

$conn,

"INSERT INTO users(

full_name,
email,
phone,
password,
role

)

VALUES(

'$full_name',
'$email',
'$phone',
'$hashed_password',
'student'

)"

);

$user_id = mysqli_insert_id($conn);

mysqli_query(

$conn,

"INSERT INTO students(

user_id,
student_id,
class_id,
section_id,
class_name,
section_name,
dob,
gender,
address,
parent_phone

)

VALUES(

'$user_id',
'$student_id',
'$class_id',
'$section_id',
'".$class_data['class_name']."',
'".$section_data['section_name']."',
'$dob',
'$gender',
'$address',
'$parent_phone'

)"

);

header("Location:index.php?success=added");
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
Add Student | MindMerge SmartCampus
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

<h1>Add Student</h1>

<p>Create a new student account.</p>

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
Full Name
</label>

<input
type="text"
name="full_name"
class="form-input"
required>

</div>

<div class="form-group">

<label class="form-label">
Email Address
</label>

<input
type="email"
name="email"
class="form-input"
required>

</div>

</div>

<div class="form-grid">

<div class="form-group">

<label class="form-label">
Phone Number
</label>

<input
type="text"
name="phone"
class="form-input"
required>

</div>

<div class="form-group">

<label class="form-label">
Password
</label>

<input
type="password"
name="password"
class="form-input"
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
Date Of Birth
</label>

<input
type="date"
name="dob"
class="form-input">

</div>

</div>

<div class="form-grid">

<div class="form-group">

<label class="form-label">
Gender
</label>

<select
name="gender"
class="form-select"
required>

<option value="">
Select Gender
</option>

<option value="male">
Male
</option>

<option value="female">
Female
</option>

<option value="other">
Other
</option>

</select>

</div>

<div class="form-group">

<label class="form-label">
Parent Phone
</label>

<input
type="text"
name="parent_phone"
class="form-input">

</div>

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

mysqli_data_seek($class_query,0);

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

<div class="form-group">

<label class="form-label">
Address
</label>

<textarea
name="address"
class="form-textarea"
rows="4"></textarea>

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
name="add_student"
class="btn btn-primary">

<i class="fa-solid fa-floppy-disk"></i>

Save Student

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

classSelect.addEventListener(
'change',
function(){

const classId = this.value;

sectionSelect.innerHTML =
'<option value="">Select Section</option>';

if(classId===''){
sectionSelect.innerHTML =
'<option value="">Select Class First</option>';
return;
}

sections.forEach(function(section){

if(section.class_id===classId){

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
