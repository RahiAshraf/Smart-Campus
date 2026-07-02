<?php

include('../config/auth.php');
include('../config/db.php');

$id = intval($_GET['id']);

$sectionQuery = mysqli_query(
$conn,
"SELECT * FROM sections
WHERE section_id='$id'"
);

$section =
mysqli_fetch_assoc(
$sectionQuery
);

if(!$section){

header("Location:index.php");
exit();

}

if(isset($_POST['update_section'])){

$class_id =
intval($_POST['class_id']);

$section_code =
mysqli_real_escape_string(
$conn,
$_POST['section_code']
);

$section_name =
mysqli_real_escape_string(
$conn,
$_POST['section_name']
);

$capacity =
intval($_POST['capacity']);

$status =
mysqli_real_escape_string(
$conn,
$_POST['status']
);

mysqli_query(

$conn,

"UPDATE sections

SET

class_id='$class_id',
section_code='$section_code',
section_name='$section_name',
capacity='$capacity',
status='$status'

WHERE section_id='$id'"

);

header(
"Location:index.php?success=updated"
);

exit();

}

$classes = mysqli_query(
$conn,
"SELECT * FROM classes
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

<title>Edit Section</title>

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

<h1>Edit Section</h1>

<div class="dashboard-section">

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

<?php while($class=mysqli_fetch_assoc($classes)){ ?>

<option
value="<?php echo $class['class_id']; ?>"
<?php if($class['class_id']==$section['class_id']) echo 'selected'; ?>>

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
value="<?php echo $section['section_code']; ?>"
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
value="<?php echo $section['section_name']; ?>"
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
value="<?php echo $section['capacity']; ?>"
required>

</div>

<div class="form-group">

<label class="form-label">
Status
</label>

<select
name="status"
class="form-select">

<option
value="active"
<?php if($section['status']=='active') echo 'selected'; ?>>

Active

</option>

<option
value="inactive"
<?php if($section['status']=='inactive') echo 'selected'; ?>>

Inactive

</option>

</select>

</div>

</div>

<button
type="submit"
name="update_section"
class="btn btn-primary">

Update Section

</button>

</form>

</div>

</div>

</div>

</div>

<script src="../assets/js/common.js"></script>

</body>
</html>