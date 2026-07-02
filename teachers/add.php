
<?php

include('../config/auth.php');
include('../config/db.php');

$error = '';

if(isset($_POST['add_teacher'])){

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

$qualification = mysqli_real_escape_string(
$conn,
trim($_POST['qualification'])
);

$last_teacher = mysqli_fetch_assoc(

mysqli_query(

$conn,

"SELECT teacher_id

FROM teachers

ORDER BY id DESC

LIMIT 1"

)

);

if($last_teacher){

$last_number = intval(
substr(
$last_teacher['teacher_id'],
3
)
);

$teacher_id =
'TCH' .
str_pad(
$last_number + 1,
5,
'0',
STR_PAD_LEFT
);

}
else{

$teacher_id = 'TCH00001';

}

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
'teacher'

)"

);

$user_id = mysqli_insert_id($conn);

mysqli_query(

$conn,

"INSERT INTO teachers(

user_id,
teacher_id,
qualification

)

VALUES(

'$user_id',
'$teacher_id',
'$qualification'

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
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>
Add Teacher | MindMerge SmartCampus
</title>

<link rel="stylesheet" href="../assets/css/global.css">
<link rel="stylesheet" href="../assets/css/layout.css">
<link rel="stylesheet" href="../assets/css/components.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

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

<h1>Add Teacher</h1>

<p>Create a new teacher profile.</p>

</div>

<a href="index.php" class="btn">

<i class="fa-solid fa-arrow-left"></i>

Back to Teachers

</a>

</div>

<?php if($error != ''){ ?>

<div
style="
background:#fee2e2;
color:#991b1b;
padding:14px;
border-radius:12px;
margin-bottom:20px;
">

<?php echo $error; ?>

</div>

<?php } ?>

<div class="dashboard-section">

<form method="POST">

<div class="section-header">
<h2>Account Information</h2>
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
Email
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
Phone
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
<h2>Teacher Information</h2>
</div>

<div class="form-group">

<label class="form-label">
Qualification
</label>

<input
type="text"
name="qualification"
class="form-input"
placeholder="M.Tech, MCA, PhD..."
required>

</div>

<div
style="
display:flex;
gap:12px;
flex-wrap:wrap;
">

<button
type="submit"
name="add_teacher"
class="btn btn-primary">

<i class="fa-solid fa-floppy-disk"></i>

Save Teacher

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

</body>
</html>
