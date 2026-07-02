
<?php

include('../config/auth.php');
include('../config/db.php');

$error = '';

if(isset($_POST['add_subject'])){

$subject_code = mysqli_real_escape_string(
$conn,
trim($_POST['subject_code'])
);

$subject_name = mysqli_real_escape_string(
$conn,
trim($_POST['subject_name'])
);

$description = mysqli_real_escape_string(
$conn,
trim($_POST['description'])
);

$status = mysqli_real_escape_string(
$conn,
$_POST['status']
);

$check_subject = mysqli_query(

$conn,

"SELECT *

FROM subjects

WHERE subject_code='$subject_code'"

);

if(mysqli_num_rows($check_subject) > 0){

$error = "Subject code already exists.";

}
else{

mysqli_query(

$conn,

"INSERT INTO subjects(

subject_code,
subject_name,
description,
status

)

VALUES(

'$subject_code',
'$subject_name',
'$description',
'$status'

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
Add Subject | MindMerge SmartCampus
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
Add Subject
</h1>

<p>
Create a new subject.
</p>

</div>

<a
href="index.php"
class="btn">

<i class="fa-solid fa-arrow-left"></i>

Back to Subjects

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
Subject Code
</label>

<input
type="text"
name="subject_code"
class="form-input"
required>

</div>

<div class="form-group">

<label class="form-label">
Subject Name
</label>

<input
type="text"
name="subject_name"
class="form-input"
required>

</div>

<div class="form-group">

<label class="form-label">
Description
</label>

<textarea
name="description"
class="form-textarea"
rows="4"></textarea>

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

<div
style="
display:flex;
gap:12px;
flex-wrap:wrap;
">

<button
type="submit"
name="add_subject"
class="btn btn-primary">

<i class="fa-solid fa-floppy-disk"></i>

Save Subject

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
