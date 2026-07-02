<?php

include('../config/auth.php');
include('../config/db.php');

$error = '';
$success = '';

$feeStructures = mysqli_query(

$conn,

"SELECT

fs.*,

c.class_name

FROM fee_structures fs

INNER JOIN classes c
ON fs.class_id = c.class_id

WHERE fs.status='active'

ORDER BY c.class_name,
fs.fee_name"

);

$selected_structure = 0;

$students = null;

$fee = null;

/*
Load Students
*/

if(
isset($_GET['fee_structure_id'])
&&
is_numeric($_GET['fee_structure_id'])
){

$selected_structure =
(int)$_GET['fee_structure_id'];

$feeQuery = mysqli_query(

$conn,

"SELECT

fs.*,

c.class_name

FROM fee_structures fs

INNER JOIN classes c
ON fs.class_id=c.class_id

WHERE fee_structure_id='$selected_structure'

LIMIT 1"

);

if(mysqli_num_rows($feeQuery) > 0){

$fee =
mysqli_fetch_assoc(
$feeQuery
);

$students = mysqli_query(

$conn,

"SELECT

s.id,
s.student_id,

u.full_name,

sec.section_name

FROM students s

INNER JOIN users u
ON s.user_id=u.id

LEFT JOIN sections sec
ON s.section_id=sec.section_id

WHERE s.class_id='".$fee['class_id']."'

ORDER BY

u.full_name ASC"

);

}

}

/*
Assign Fee
*/

if($_SERVER['REQUEST_METHOD'] == 'POST'){

$fee_structure_id =
(int)$_POST['fee_structure_id'];

if(
isset($_POST['students'])
&&
count($_POST['students']) > 0
){

foreach($_POST['students'] as $student_id){

$student_id =
(int)$student_id;

/*
Prevent Duplicate
*/

$exists = mysqli_query(

$conn,

"SELECT student_fee_id

FROM student_fees

WHERE

student_id='$student_id'

AND

fee_structure_id='$fee_structure_id'

LIMIT 1"

);

if(mysqli_num_rows($exists) > 0){

continue;

}

$feeInfo = mysqli_fetch_assoc(

mysqli_query(

$conn,

"SELECT amount

FROM fee_structures

WHERE fee_structure_id='$fee_structure_id'"

)

);

$amount =
(float)$feeInfo['amount'];

mysqli_query(

$conn,

"INSERT INTO student_fees
(
student_id,
fee_structure_id,
amount,
paid_amount,
balance_amount,
payment_status
)

VALUES
(
'$student_id',
'$fee_structure_id',
'$amount',
0,
'$amount',
'unpaid'
)"

);

}

header(
'Location:assign.php?success=assigned'
);

exit;

}
else{

$error =
'Please select at least one student.';

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
Assign Fees
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
Assign Fees
</h1>

<p>
Assign fee structures to students.
</p>

</div>

<a
href="index.php"
class="btn">

Back

</a>

</div>

<?php if(isset($_GET['success'])){ ?>

<div
style="
background:#dcfce7;
color:#166534;
padding:15px;
border-radius:12px;
margin-bottom:20px;
">

Fees assigned successfully.

</div>

<?php } ?>

<?php if(!empty($error)){ ?>

<div
style="
background:#fee2e2;
color:#991b1b;
padding:15px;
border-radius:12px;
margin-bottom:20px;
">

<?php echo $error; ?>

</div>

<?php } ?>

<div class="dashboard-section">

<form method="GET">

<div class="form-grid">

<div class="form-group">

<label>
Fee Structure
</label>

<select
name="fee_structure_id"
class="form-input"
required>

<option value="">
Select Fee Structure
</option>

<?php

mysqli_data_seek($feeStructures,0);

while($row =
mysqli_fetch_assoc($feeStructures)){

?>

<option

value="<?php echo $row['fee_structure_id']; ?>"

<?php

echo ($selected_structure ==
$row['fee_structure_id'])

? 'selected'
: '';

?>

>

<?php

echo htmlspecialchars(
$row['class_name']
);

?>

 -

<?php

echo htmlspecialchars(
$row['fee_name']
);

?>

 (

₹<?php echo number_format($row['amount'],2); ?>

)

</option>

<?php } ?>

</select>

</div>

</div>

<div style="margin-top:15px;">

<button
type="submit"
class="btn btn-primary">

Load Students

</button>

</div>

</form>

</div>

<?php if($students){ ?>

<div class="dashboard-section">

<div class="section-header">

<h2>

Students -
<?php echo htmlspecialchars($fee['class_name']); ?>

</h2>

</div>

<form method="POST">

<input
type="hidden"
name="fee_structure_id"
value="<?php echo $selected_structure; ?>">

<div style="margin-bottom:15px;">

<label>

<input
type="checkbox"
id="selectAll">

 Select All

</label>

</div>

<div class="table-responsive">

<table class="custom-table">

<thead>

<tr>

<th>Select</th>
<th>Student ID</th>
<th>Name</th>
<th>Section</th>

</tr>

</thead>

<tbody>

<?php

while($student =
mysqli_fetch_assoc($students)){

?>

<tr>

<td>

<input

type="checkbox"

name="students[]"

value="<?php echo $student['id']; ?>">

</td>

<td>

<?php

echo htmlspecialchars(
$student['student_id']
);

?>

</td>

<td>

<?php

echo htmlspecialchars(
$student['full_name']
);

?>

</td>

<td>

<?php

echo htmlspecialchars(
$student['section_name']
);

?>

</td>

</tr>

<?php } ?>

</tbody>

</table>

</div>

<div
style="
margin-top:20px;
">

<button
type="submit"
class="btn btn-primary">

Assign Fee

</button>

</div>

</form>

</div>

<?php } ?>

</div>

</div>

</div>

<script>

document.addEventListener(

'DOMContentLoaded',

function(){

const selectAll =
document.getElementById(
'selectAll'
);

if(selectAll){

selectAll.addEventListener(

'change',

function(){

document
.querySelectorAll(
'input[name="students[]"]'
)
.forEach(

checkbox => {

checkbox.checked =
this.checked;

}

);

}

);

}

}

);

</script>

<script src="../assets/js/common.js"></script>

</body>

</html>