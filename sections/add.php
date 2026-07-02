<?php

include('../config/auth.php');
include('../config/db.php');

$error = '';

if(isset($_POST['add_section'])){

$class_id =
intval($_POST['class_id']);

$section_code =
mysqli_real_escape_string(
$conn,
trim($_POST['section_code'])
);

$section_name =
mysqli_real_escape_string(
$conn,
trim($_POST['section_name'])
);

$capacity =
intval($_POST['capacity']);

$status =
mysqli_real_escape_string(
$conn,
$_POST['status']
);

$check = mysqli_query(

$conn,

"SELECT *

FROM sections

WHERE

class_id='$class_id'

AND

section_code='$section_code'"

);

if(mysqli_num_rows($check) > 0){

$error =
"Section code already exists for this class.";

}
else{

mysqli_query(

$conn,

"INSERT INTO sections(

class_id,
section_code,
section_name,
capacity,
status

)

VALUES(

'$class_id',
'$section_code',
'$section_name',
'$capacity',
'$status'

)"

);

header(
"Location:index.php?success=added"
);

exit();

}

}

$classes = mysqli_query(
$conn,
"SELECT * FROM classes
WHERE status='active'
ORDER BY class_name ASC"
);

?>

<!DOCTYPE html>
<html>

<head>

<meta charset="UTF-8">

<meta
name="viewport"
content="width=device-width, initial-scale=1.0">

<title>Add Section</title>

<link rel="stylesheet"
href="../assets/css/global.css">

<link rel="stylesheet"
href="../assets/css/layout.css">

<link rel="stylesheet"
href="../assets/css/components.css">

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

<h1>Add Section</h1>

<p>
Create a new section.
</p>

</div>

</div>

<div class="dashboard-section">

<?php if($error!=''){ ?>

<p style="color:red;">
<?php echo $error; ?>
</p>

<?php } ?>

<form method="POST">

<div class="form-grid">

<div class="form-group">

<label class="form-label">
Class
</label>

<select
name="class_id"
class="form-select"
required>

<option value="">
Select Class
</option>

<?php while($class=mysqli_fetch_assoc($classes)){ ?>

<option
value="<?php echo $class['class_id']; ?>">

<?php echo $class['class_name']; ?>

</option>

<?php } ?>

</select>

</div>

<div class="form-group">

<label class="form-label">
Section Code
</label>

<input
type="text"
name="section_code"
class="form-input"
placeholder="A"
required>

</div>

</div>

<div class="form-group">

<label class="form-label">
Section Name
</label>

<input
type="text"
name="section_name"
class="form-input"
placeholder="Section A"
required>

</div>

<div class="form-grid">

<div class="form-group">

<label class="form-label">
Capacity
</label>

<input
type="number"
name="capacity"
class="form-input"
value="50"
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

<button
type="submit"
name="add_section"
class="btn btn-primary">

<i class="fa-solid fa-floppy-disk"></i>

Save Section

</button>

</form>

</div>

</div>

</div>

</div>

<script src="../assets/js/common.js"></script>

</body>
</html>