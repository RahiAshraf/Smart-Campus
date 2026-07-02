
<?php

include('../config/auth.php');
include('../config/db.php');

$id = intval($_GET['id']);

$template = mysqli_query(

$conn,

"SELECT *

FROM period_templates

WHERE template_id='$id'"

);

$row = mysqli_fetch_assoc($template);

if(!$row){

header("Location:index.php");
exit();

}

$error = '';

if(isset($_POST['update_template'])){

$template_code = strtoupper(

mysqli_real_escape_string(
$conn,
trim($_POST['template_code'])
)

);

$template_name = mysqli_real_escape_string(
$conn,
trim($_POST['template_name'])
);

$template_type = mysqli_real_escape_string(
$conn,
$_POST['template_type']
);

$description = mysqli_real_escape_string(
$conn,
trim($_POST['description'])
);

$status = mysqli_real_escape_string(
$conn,
$_POST['status']
);

$check = mysqli_query(

$conn,

"SELECT *

FROM period_templates

WHERE

template_code='$template_code'

AND

template_id!='$id'"

);

if(mysqli_num_rows($check)>0){

$error =
'Template code already exists.';

}
else{

mysqli_query(

$conn,

"UPDATE period_templates

SET

template_code='$template_code',
template_name='$template_name',
template_type='$template_type',
description='$description',
status='$status'

WHERE template_id='$id'"

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
Edit Schedule Template | MindMerge SmartCampus
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
Edit Schedule Template
</h1>

<p>
Update schedule template information.
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
padding:14px;
border-radius:12px;
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
Template Code
</label>

<input
type="text"
name="template_code"
class="form-input"
value="<?php echo htmlspecialchars($row['template_code']); ?>"
required>

<small>
Examples:
REGULAR,
EXAM,
LAB,
HOSTEL,
SUMMER
</small>

</div>

<div class="form-group">

<label class="form-label">
Template Name
</label>

<input
type="text"
name="template_name"
class="form-input"
value="<?php echo htmlspecialchars($row['template_name']); ?>"
required>

</div>

</div>

<div class="form-grid">

<div class="form-group">

<label class="form-label">
Template Type
</label>

<select
name="template_type"
class="form-select"
required>

<option value="regular" <?php if($row['template_type']=='regular') echo 'selected'; ?>>
Regular
</option>

<option value="exam" <?php if($row['template_type']=='exam') echo 'selected'; ?>>
Exam
</option>

<option value="lab" <?php if($row['template_type']=='lab') echo 'selected'; ?>>
Lab
</option>

<option value="hostel" <?php if($row['template_type']=='hostel') echo 'selected'; ?>>
Hostel
</option>

<option value="custom" <?php if($row['template_type']=='custom') echo 'selected'; ?>>
Custom
</option>

</select>

</div>

<div class="form-group">

<label class="form-label">
Status
</label>

<select
name="status"
class="form-select">

<option value="active" <?php if($row['status']=='active') echo 'selected'; ?>>
Active
</option>

<option value="inactive" <?php if($row['status']=='inactive') echo 'selected'; ?>>
Inactive
</option>

</select>

</div>

</div>

<div class="form-group">

<label class="form-label">
Description
</label>

<textarea
name="description"
class="form-textarea"
rows="5"><?php echo htmlspecialchars($row['description']); ?></textarea>

</div>

<div
style="
display:flex;
gap:12px;
flex-wrap:wrap;
">

<button
type="submit"
name="update_template"
class="btn btn-primary">

<i class="fa-solid fa-floppy-disk"></i>

Update Template

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
