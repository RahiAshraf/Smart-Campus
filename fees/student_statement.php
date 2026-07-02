<?php

include('../config/auth.php');
include('../config/db.php');

if(
!isset($_GET['student_id'])
||
!is_numeric($_GET['student_id'])
){

header('Location:report.php');
exit;

}

$student_id = (int)$_GET['student_id'];

$studentQuery = mysqli_query(

$conn,

"SELECT

s.*,

u.full_name,

c.class_name,

sec.section_name

FROM students s

INNER JOIN users u
ON s.user_id=u.id

LEFT JOIN classes c
ON s.class_id=c.class_id

LEFT JOIN sections sec
ON s.section_id=sec.section_id

WHERE s.id='$student_id'

LIMIT 1"

);

if(mysqli_num_rows($studentQuery)==0){

header('Location:report.php');
exit;

}

$student =
mysqli_fetch_assoc(
$studentQuery
);

$fees = mysqli_query(

$conn,

"SELECT

sf.*,

fs.fee_name,

fs.due_date,

fs.academic_year

FROM student_fees sf

INNER JOIN fee_structures fs
ON sf.fee_structure_id=fs.fee_structure_id

WHERE sf.student_id='$student_id'

ORDER BY fs.due_date ASC"

);

$summary = mysqli_fetch_assoc(

mysqli_query(

$conn,

"SELECT

SUM(amount) total_fee,

SUM(paid_amount) total_paid,

SUM(balance_amount) total_balance

FROM student_fees

WHERE student_id='$student_id'"

)

);

?>
<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta
name="viewport"
content="width=device-width, initial-scale=1.0">

<title>
Student Fee Statement
</title>

<link rel="stylesheet" href="../assets/css/global.css">
<link rel="stylesheet" href="../assets/css/layout.css">
<link rel="stylesheet" href="../assets/css/components.css">

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
Student Fee Statement
</h1>

<p>

<?php

echo htmlspecialchars(
$student['full_name']
);

?>

</p>

</div>

<div
style="
display:flex;
gap:10px;
flex-wrap:wrap;
">

<a
href="report.php"
class="btn">

Back

</a>

<button
onclick="window.print();"
class="btn btn-primary">

Print

</button>

</div>

</div>

<div class="dashboard-grid">

<div class="dashboard-card">

<h3>Total Fee</h3>

<h2>

₹<?php

echo number_format(
(float)$summary['total_fee'],
2
);

?>

</h2>

</div>

<div class="dashboard-card">

<h3>Total Paid</h3>

<h2>

₹<?php

echo number_format(
(float)$summary['total_paid'],
2
);

?>

</h2>

</div>

<div class="dashboard-card">

<h3>Balance</h3>

<h2>

₹<?php

echo number_format(
(float)$summary['total_balance'],
2
);

?>

</h2>

</div>

</div>

<div class="dashboard-section">

<div class="section-header">

<h2>
Fee Details
</h2>

</div>

<div class="table-responsive">

<table class="custom-table">

<thead>

<tr>

<th>Fee Name</th>

<th>Due Date</th>

<th>Total</th>

<th>Paid</th>

<th>Balance</th>

<th>Status</th>

</tr>

</thead>

<tbody>

<?php

if(mysqli_num_rows($fees)>0){

while($row=mysqli_fetch_assoc($fees)){

?>

<tr>

<td>

<?php

echo htmlspecialchars(
$row['fee_name']
);

?>

</td>

<td>

<?php

echo !empty($row['due_date'])

? date(
'd M Y',
strtotime(
$row['due_date']
)
)

: '-';

?>

</td>

<td>

₹<?php echo number_format($row['amount'],2); ?>

</td>

<td>

₹<?php echo number_format($row['paid_amount'],2); ?>

</td>

<td>

₹<?php echo number_format($row['balance_amount'],2); ?>

</td>

<td>

<span
class="status

<?php

echo

$row['payment_status']=='paid'
? 'success'
:
(
$row['payment_status']=='partial'
? 'warning'
: 'danger'
);

?>

">

<?php

echo ucfirst(
$row['payment_status']
);

?>

</span>

</td>

</tr>

<?php

}

}
else{

?>

<tr>

<td
colspan="6"
style="
text-align:center;
padding:40px;
">

No fee records found.

</td>

</tr>

<?php } ?>

</tbody>

</table>

</div>

</div>

</div>

</div>

</div>

<script src="../assets/js/common.js"></script>

</body>

</html>