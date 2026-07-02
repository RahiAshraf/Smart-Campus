<?php

session_start();

include('../config/db.php');

$error = "";
$success = "";

if(
!isset($_SESSION['register_otp'])
||
!isset($_SESSION['register_data'])
){

header("Location: register.php");
exit();

}

/* ADMIN ID */

function generateAdminId($conn){

$result =
mysqli_query(
$conn,
"SELECT COUNT(*) as total
FROM users
WHERE role='admin'"
);

$row =
mysqli_fetch_assoc($result);

$next =
$row['total'] + 1;

return
"ADM" .
str_pad(
$next,
4,
"0",
STR_PAD_LEFT
);

}

/* STUDENT ID */

function generateStudentId($conn){

$result =
mysqli_query(
$conn,
"SELECT MAX(id) as max_id
FROM students"
);

$row =
mysqli_fetch_assoc($result);

$next =
($row['max_id'] ?? 0) + 1;

return
"STU" .
str_pad(
$next,
4,
"0",
STR_PAD_LEFT
);

}

/* TEACHER ID */

function generateTeacherId($conn){

$result =
mysqli_query(
$conn,
"SELECT MAX(id) as max_id
FROM teachers"
);

$row =
mysqli_fetch_assoc($result);

$next =
($row['max_id'] ?? 0) + 1;

return
"TCH" .
str_pad(
$next,
4,
"0",
STR_PAD_LEFT
);

}

/* PARENT ID */

function generateParentId($conn){

$result =
mysqli_query(
$conn,
"SELECT MAX(id) as max_id
FROM parents"
);

$row =
mysqli_fetch_assoc($result);

$next =
($row['max_id'] ?? 0) + 1;

return
"PAR" .
str_pad(
$next,
4,
"0",
STR_PAD_LEFT
);

}

if(isset($_POST['verify'])){

$entered_otp =
$_POST['otp'];

if(
$entered_otp ==
$_SESSION['register_otp']
){

$data =
$_SESSION['register_data'];

$full_name =
mysqli_real_escape_string(
$conn,
$data['full_name']
);

$email =
mysqli_real_escape_string(
$conn,
$data['email']
);

$phone =
mysqli_real_escape_string(
$conn,
$data['phone']
);

$role =
$data['role'];

if(!in_array($role, ['teacher','student','parent'], true)){

$error =
"Invalid registration role.";

}
else{

$password =
password_hash(
$data['password'],
PASSWORD_DEFAULT
);

$admin_id = '';

$query = "

INSERT INTO users(

full_name,
email,
phone,
password,
role,
admin_id

)

VALUES(

'$full_name',
'$email',
'$phone',
'$password',
'$role',
'$admin_id'

)

";

if(mysqli_query($conn,$query)){

$user_id =
mysqli_insert_id($conn);



/* STUDENT */

if($role == 'student'){

$class_id =
intval($data['class_id'] ?? 0);

$section_id =
intval($data['section_id'] ?? 0);

$class_name = '';
$section_name = '';

$classQuery =
mysqli_query(
$conn,
"SELECT class_name
FROM classes
WHERE class_id='$class_id'"
);

if(mysqli_num_rows($classQuery) > 0){

$classRow =
mysqli_fetch_assoc(
$classQuery
);

$class_name =
$classRow['class_name'];

}

$sectionQuery =
mysqli_query(
$conn,
"SELECT section_name
FROM sections
WHERE section_id='$section_id'"
);

if(mysqli_num_rows($sectionQuery) > 0){

$sectionRow =
mysqli_fetch_assoc(
$sectionQuery
);

$section_name =
$sectionRow['section_name'];

}

$student_id =
generateStudentId($conn);

mysqli_query(

$conn,

"

INSERT INTO students(

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
'$class_name',
'$section_name',
'".mysqli_real_escape_string($conn, $data['dob'] ?? '')."',
'".mysqli_real_escape_string($conn, $data['gender'] ?? '')."',
'".mysqli_real_escape_string($conn, $data['address'] ?? '')."',
'".mysqli_real_escape_string($conn, $data['parent_phone'] ?? '')."'

)

"

);

}



/* TEACHER */

if($role == 'teacher'){

$teacher_id =
generateTeacherId($conn);

mysqli_query(

$conn,

"

INSERT INTO teachers(

user_id,
teacher_id,
qualification

)

VALUES(

'$user_id',
'$teacher_id',
'".mysqli_real_escape_string($conn, $data['qualification'] ?? '')."'

)

"

);

}



/* PARENT */

if($role == 'parent'){

$parent_id =
generateParentId($conn);

mysqli_query(

$conn,

"

INSERT INTO parents(

user_id,
parent_id,
student_id,
relationship_name

)

VALUES(

'$user_id',
'$parent_id',
'".mysqli_real_escape_string($conn, $data['student_id'] ?? '')."',
'".mysqli_real_escape_string($conn, $data['relationship_name'] ?? '')."'

)

"

);

}

unset($_SESSION['register_otp']);
unset($_SESSION['register_data']);

$success =
"Account Created Successfully";

}else{

$error =
"Failed to create account";

}

}

}else{

$error =
"Invalid OTP";

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
Verify OTP | MindMerge
</title>

<link
rel="stylesheet"
href="../assets/css/global.css">

<link
rel="stylesheet"
href="../assets/css/components.css">

<link
rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="../../assets/css/material-theme.css">


<style>

.auth-page{
min-height:100vh;
display:flex;
justify-content:center;
align-items:center;
padding:20px;
background:
linear-gradient(
135deg,
#081028,
#172554
);
}

.auth-card{
width:100%;
max-width:460px;
background:white;
padding:38px;
border-radius:24px;
box-shadow:
0 20px 60px rgba(0,0,0,0.25);
text-align:center;
}

.auth-icon{
width:80px;
height:80px;
border-radius:50%;
background:#dbeafe;
display:flex;
justify-content:center;
align-items:center;
margin:auto;
margin-bottom:20px;
font-size:32px;
color:#2563eb;
}

.error-box{
background:#fee2e2;
color:#991b1b;
padding:14px;
border-radius:12px;
margin-bottom:20px;
}

.success-box{
background:#dcfce7;
color:#166534;
padding:14px;
border-radius:12px;
margin-bottom:20px;
}

.auth-links{
margin-top:20px;
display:flex;
justify-content:center;
gap:20px;
}

.auth-links a{
text-decoration:none;
color:var(--primary);
font-weight:600;
}

</style>

</head>

<body class="auth-v2">

<div class="auth-page">

<div class="auth-card">

<div class="auth-icon">

<i class="fa-solid fa-shield-halved"></i>

</div>

<h1>OTP Verification</h1>

<p>
Enter the OTP sent to your email
</p>

<?php if($error != ""){ ?>

<div class="error-box">

<?php echo $error; ?>

</div>

<?php } ?>

<?php if($success != ""){ ?>

<div class="success-box">

<?php echo $success; ?>

<br><br>

You can now login using your credentials.

</div>

<a
href="login.php"
class="btn btn-primary"
style="width:100%;">

<i class="fa-solid fa-right-to-bracket"></i>

Go To Login

</a>

<?php }else{ ?>

<form method="POST">

<div class="form-group">

<label
class="form-label"
style="text-align:left;display:block;">

Enter OTP

</label>

<input
type="text"
name="otp"
class="form-input"
required>

</div>

<button
type="submit"
name="verify"
class="btn btn-primary"
style="width:100%;">

<i class="fa-solid fa-check"></i>

Verify OTP

</button>

</form>

<div class="auth-links">

<a href="register.php">
Back To Register
</a>

<a href="login.php">
Login
</a>

</div>

<?php } ?>

</div>

</div>

</body>
</html>
