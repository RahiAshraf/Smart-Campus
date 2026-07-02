<?php

include('../config/auth.php');
include('../config/db.php');

$class_id = isset($_GET['class_id'])
? (int)$_GET['class_id']
: 0;

$status = isset($_GET['status'])
? mysqli_real_escape_string(
$conn,
$_GET['status']
)
: '';

$conditions = [];

if($class_id > 0){

$conditions[] =
"c.class_id='$class_id'";

}

if($status != ''){

$conditions[] =
"sf.payment_status='$status'";

}

$where = '';

if(!empty($conditions)){

$where =
'WHERE ' .
implode(
' AND ',
$conditions
);

}

/* Classes */

$classes = mysqli_query(

$conn,

"SELECT

class_id,
class_name

FROM classes

ORDER BY class_name"

);

/* Main Report */

$reportQuery = mysqli_query(

$conn,

"SELECT

sf.*,

fs.fee_name,
fs.academic_year,
fs.due_date,

st.student_id,

u.full_name,

c.class_name,

sec.section_name

FROM student_fees sf

INNER JOIN fee_structures fs
ON sf.fee_structure_id=fs.fee_structure_id

INNER JOIN students st
ON sf.student_id=st.id

INNER JOIN users u
ON st.user_id=u.id

LEFT JOIN classes c
ON st.class_id=c.class_id

LEFT JOIN sections sec
ON st.section_id=sec.section_id

$where

ORDER BY

c.class_name,
sec.section_name,
u.full_name"

);

/* Dashboard Stats */

$total_assigned = mysqli_fetch_assoc(

mysqli_query(

$conn,

"SELECT
COUNT(*) total

FROM student_fees"

)

)['total'];

$total_fee = mysqli_fetch_assoc(

mysqli_query(

$conn,

"SELECT
IFNULL(SUM(amount),0) total

FROM student_fees"

)

)['total'];

$total_collected = mysqli_fetch_assoc(

mysqli_query(

$conn,

"SELECT
IFNULL(SUM(paid_amount),0) total

FROM student_fees"

)

)['total'];

$total_pending = mysqli_fetch_assoc(

mysqli_query(

$conn,

"SELECT
IFNULL(SUM(balance_amount),0) total

FROM student_fees"

)

)['total'];

$paid_students = mysqli_fetch_assoc(

mysqli_query(

$conn,

"SELECT
COUNT(*) total

FROM student_fees

WHERE payment_status='paid'"

)

)['total'];

$partial_students = mysqli_fetch_assoc(

mysqli_query(

$conn,

"SELECT
COUNT(*) total

FROM student_fees

WHERE payment_status='partial'"

)

)['total'];

$unpaid_students = mysqli_fetch_assoc(

mysqli_query(

$conn,

"SELECT
COUNT(*) total

FROM student_fees

WHERE payment_status='unpaid'"

)

)['total'];

?>
<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta
name="viewport"
content="width=device-width, initial-scale=1.0">

<title>
Fee Reports
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
Fee Reports
</h1>

<p>
Fee collection analytics and reports.
</p>

</div>

<div
style="
display:flex;
gap:10px;
flex-wrap:wrap;
">

<a
href="index.php"
class="btn">

<i class="fa-solid fa-arrow-left"></i>

Back

</a>

<button
onclick="window.print();"
class="btn">

<i class="fa-solid fa-print"></i>

Print

</button>

<a
href="export_csv.php"
class="btn btn-primary">

CSV

</a>

<a
href="export_excel.php"
class="btn btn-primary">

Excel

</a>

</div>

</div>

<!-- Summary -->

<div class="dashboard-grid">

<div class="dashboard-card">

<h3>Total Assignments</h3>

<h2>

<?php echo $total_assigned; ?>

</h2>

</div>

<div class="dashboard-card">

<h3>Total Fee</h3>

<h2>

₹<?php echo number_format($total_fee,2); ?>

</h2>

</div>

<div class="dashboard-card">

<h3>Collected</h3>

<h2>

₹<?php echo number_format($total_collected,2); ?>

</h2>

</div>

<div class="dashboard-card">

<h3>Pending</h3>

<h2>

₹<?php echo number_format($total_pending,2); ?>

</h2>

</div>

</div>

<div class="dashboard-grid" style="margin-top:10px">

<div class="dashboard-card">

<h3>Paid</h3>

<h2>

<?php echo $paid_students; ?>

</h2>

</div>

<div class="dashboard-card">

<h3>Partial</h3>

<h2>

<?php echo $partial_students; ?>

</h2>

</div>

<div class="dashboard-card">

<h3>Unpaid</h3>

<h2>

<?php echo $unpaid_students; ?>

</h2>

</div>

</div>

<!-- Filters -->

<div class="dashboard-section">

<form method="GET">

<div class="form-grid">

<div class="form-group">

<label>
Class
</label>

<select
name="class_id"
class="form-input">

<option value="">
All Classes
</option>

<?php

while($class =
mysqli_fetch_assoc($classes)){

?>

<option

value="<?php echo $class['class_id']; ?>"

<?php

echo ($class_id ==
$class['class_id'])

? 'selected'
: '';

?>

>

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
Status
</label>

<select
name="status"
class="form-input">

<option value="">
All Status
</option>

<option
value="paid"
<?php echo ($status=='paid')?'selected':''; ?>>

Paid

</option>

<option
value="partial"
<?php echo ($status=='partial')?'selected':''; ?>>

Partial

</option>

<option
value="unpaid"
<?php echo ($status=='unpaid')?'selected':''; ?>>

Unpaid

</option>

</select>

</div>

</div>

<div style="margin-top:15px;">

<button
type="submit"
class="btn btn-primary">

Apply Filters

</button>

<a
href="report.php"
class="btn">

Reset

</a>

</div>

</form>

</div>

<!-- Report Table -->

<div class="dashboard-section">

<div class="section-header">

<h2>
Student Fee Report
</h2>

</div>

<div class="table-responsive">

<table class="custom-table">

<thead>

<tr>

<th data-sort="true">
Student
</th>

<th data-sort="true">
Class
</th>

<th data-sort="true">
Fee
</th>

<th data-sort="true">
Amount
</th>

<th data-sort="true">
Paid
</th>

<th data-sort="true">
Balance
</th>

<th data-sort="true">
Due Date
</th>

<th data-sort="true">
Status
</th>

</tr>

</thead>

<tbody>

<?php

if(mysqli_num_rows($reportQuery) > 0){

while($row =
mysqli_fetch_assoc($reportQuery)){

?>

<tr>

<td>

<strong>

<?php

echo htmlspecialchars(
$row['full_name']
);

?>

</strong>

<br>

<small>

<?php

echo htmlspecialchars(
$row['student_id']
);

?>

</small>

</td>

<td>

<?php

echo htmlspecialchars(
$row['class_name']
);

?>

<br>

<small>

<?php

echo htmlspecialchars(
$row['section_name']
);

?>

</small>

</td>

<td>

<?php

echo htmlspecialchars(
$row['fee_name']
);

?>

</td>

<td>

₹<?php

echo number_format(
$row['amount'],
2
);

?>

</td>

<td>

₹<?php

echo number_format(
$row['paid_amount'],
2
);

?>

</td>

<td>

₹<?php

echo number_format(
$row['balance_amount'],
2
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
colspan="8"
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