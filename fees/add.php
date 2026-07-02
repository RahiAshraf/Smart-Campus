<?php

include('../config/auth.php');
include('../config/db.php');

$error = '';

$classes = mysqli_query(

$conn,

"SELECT

class_id,
class_name

FROM classes

WHERE status='active'

ORDER BY class_name ASC"

);

if($_SERVER['REQUEST_METHOD'] == 'POST'){

$class_id = (int)$_POST['class_id'];

$fee_name = mysqli_real_escape_string(
$conn,
trim($_POST['fee_name'])
);

$amount = (float)$_POST['amount'];

$due_date = !empty($_POST['due_date'])
? $_POST['due_date']
: NULL;

$academic_year = mysqli_real_escape_string(
$conn,
trim($_POST['academic_year'])
);

$description = mysqli_real_escape_string(
$conn,
trim($_POST['description'])
);

$status = mysqli_real_escape_string(
$conn,
$_POST['status']
);

if(
empty($class_id)
||
empty($fee_name)
||
$amount <= 0
){

$error =
'Please fill all required fields correctly.';

}
else{

$check = mysqli_query(

$conn,

"SELECT fee_structure_id

FROM fee_structures

WHERE

class_id='$class_id'

AND

fee_name='$fee_name'

AND

academic_year='$academic_year'

LIMIT 1"

);

if(mysqli_num_rows($check) > 0){

$error =
'Fee structure already exists for this class and academic year.';

}
else{

$dueDateValue =
$due_date
? "'$due_date'"
: "NULL";

$insert = mysqli_query(

$conn,

"INSERT INTO fee_structures
(
class_id,
fee_name,
amount,
due_date,
academic_year,
description,
status
)

VALUES
(
'$class_id',
'$fee_name',
'$amount',
$dueDateValue,
'$academic_year',
'$description',
'$status'
)"

);

if($insert){

header(
'Location:index.php?success=added'
);

exit;

}
else{

$error =
'Failed to create fee structure.';

}

}

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
Add Fee Structure
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

<h1>
Add Fee Structure
</h1>

<p>
Create a new fee structure for a class.
</p>

</div>

<a
href="index.php"
class="btn">

<i class="fa-solid fa-arrow-left"></i>

Back

</a>

</div>

<?php if(!empty($error)){ ?>

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

<div class="form-grid">

<div class="form-group">

<label>
Class *
</label>

<select
name="class_id"
class="form-input"
required>

<option value="">
Select Class
</option>

<?php

mysqli_data_seek($classes,0);

while($class =
mysqli_fetch_assoc($classes)){

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

<label>
Fee Name *
</label>

<input

type="text"

name="fee_name"

class="form-input"

placeholder="Tuition Fee"

required>

</div>

<div class="form-group">

<label>
Amount *
</label>

<input

type="number"

name="amount"

class="form-input"

step="0.01"

min="1"

required>

</div>

<div class="form-group">

<label>
Due Date
</label>

<input

type="date"

name="due_date"

class="form-input">

</div>

<div class="form-group">

<label>
Academic Year
</label>

<input

type="text"

name="academic_year"

class="form-input"

value="2026-27"

required>

</div>

<div class="form-group">

<label>
Status
</label>

<select
name="status"
class="form-input">

<option value="active">
Active
</option>

<option value="inactive">
Inactive
</option>

</select>

</div>

<div class="form-group"
style="grid-column:1/-1;">

<label>
Description
</label>

<textarea

name="description"

class="form-input"

rows="4"

placeholder="Optional description">

</textarea>

</div>

</div>

<div
style="
margin-top:20px;
display:flex;
gap:12px;
flex-wrap:wrap;
">

<button
type="submit"
class="btn btn-primary">

<i class="fa-solid fa-floppy-disk"></i>

Save Fee Structure

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
