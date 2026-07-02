
<?php

include('../config/auth.php');
include('../config/db.php');

$id = intval($_GET['id']);

$query = mysqli_query(

$conn,

"SELECT

t.*,

u.full_name,
u.email,
u.phone

FROM teachers t

LEFT JOIN users u
ON t.user_id = u.id

WHERE t.id='$id'"

);

$row = mysqli_fetch_assoc($query);

if(!$row){

header("Location:index.php");
exit();

}

$error = '';

if(isset($_POST['update_teacher'])){

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

$qualification = mysqli_real_escape_string(
$conn,
trim($_POST['qualification'])
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

"UPDATE teachers

SET

qualification='$qualification'

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

<meta
name="viewport"
content="width=device-width, initial-scale=1.0">

<title>
Edit Teacher | MindMerge SmartCampus
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
Edit Teacher
</h1>

<p>
Update teacher information.
</p>

</div>

<a
href="index.php"
class="btn">

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

<h2>
Teacher Profile
</h2>

</div>

<div class="form-group">

<label class="form-label">
Teacher ID
</label>

<input
type="text"
class="form-input"
value="<?php echo htmlspecialchars($row['teacher_id']); ?>"
readonly>

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
value="<?php echo htmlspecialchars($row['full_name']); ?>"
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
value="<?php echo htmlspecialchars($row['email']); ?>"
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
value="<?php echo htmlspecialchars($row['phone']); ?>"
required>

</div>

<div class="form-group">

<label class="form-label">
Qualification
</label>

<input
type="text"
name="qualification"
class="form-input"
value="<?php echo htmlspecialchars($row['qualification']); ?>"
required>

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
name="update_teacher"
class="btn btn-primary">

<i class="fa-solid fa-floppy-disk"></i>

Update Teacher

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
