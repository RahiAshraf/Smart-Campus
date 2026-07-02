<?php

include('../config/auth.php');
include('../config/db.php');

if(
!isset($_GET['id'])
||
!is_numeric($_GET['id'])
){

header('Location:payments.php');
exit;

}

$payment_id = (int)$_GET['id'];

$query = mysqli_query(

$conn,

"SELECT

fp.*,

sf.amount,
fp.amount_paid,
sf.balance_amount,

fs.fee_name,
fs.academic_year,

st.student_id,

u.full_name,

c.class_name,

sec.section_name

FROM fee_payments fp

INNER JOIN student_fees sf
ON fp.student_fee_id=sf.student_fee_id

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

WHERE fp.payment_id='$payment_id'

LIMIT 1"

);

if(mysqli_num_rows($query)==0){

header('Location:payments.php');
exit;

}

$receipt =
mysqli_fetch_assoc(
$query
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
Fee Receipt
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
<style>

.receipt-box{

max-width:900px;
width:100%;

background:var(--card);

color:var(--text);

padding:30px;

border-radius:16px;

box-shadow:var(--shadow);

border:1px solid rgba(148,163,184,0.15);

}

.receipt-header{

text-align:center;
margin-bottom:30px;

}

.receipt-header h1{

margin-bottom:5px;

}

.receipt-table{

width:100%;
border-collapse:collapse;

margin-top:20px;

}

.receipt-table th,
.receipt-table td{

border:1px solid rgba(148,163,184,0.2);

padding:12px;

text-align:left;

}

@media print{

.no-print{
display:none !important;
}

body{
background:white !important;
}

.receipt-box{

background:white !important;
color:black !important;

box-shadow:none;

border:none;

max-width:100%;

}

}

</style>

</head>

<body>

<div class="app-layout no-print">

<?php include('../partials/sidebar.php'); ?>

<div class="main-content">

<?php include('../partials/topbar.php'); ?>

<div class="page-content">

<div class="page-header">

<div>

<h1>
Fee Receipt
</h1>

<p>
Receipt No:
<?php echo htmlspecialchars($receipt['receipt_no']); ?>
</p>

</div>

<div
style="
display:flex;
gap:10px;
flex-wrap:wrap;
">

<a
href="payments.php"
class="btn">

<i class="fa-solid fa-arrow-left"></i>

Back

</a>

<button
onclick="window.print();"
class="btn btn-primary">

<i class="fa-solid fa-print"></i>

Print Receipt

</button>

</div>

</div>

<div
style="
max-width:1000px;
margin:30px auto;
padding:0 20px;
">

<div class="receipt-box">

<div class="receipt-header">

<h1>
MindMerge SmartCampus
</h1>

<p>
Official Fee Payment Receipt
</p>

</div>

<table class="receipt-table">

<tr>

<th>
Receipt No
</th>

<td>

<?php

echo htmlspecialchars(
$receipt['receipt_no']
);

?>

</td>

<th>
Payment Date
</th>

<td>

<?php

echo date(
'd M Y',
strtotime(
$receipt['payment_date']
)
);

?>

</td>

</tr>

<tr>

<th>
Student Name
</th>

<td>

<?php

echo htmlspecialchars(
$receipt['full_name']
);

?>

</td>

<th>
Student ID
</th>

<td>

<?php

echo htmlspecialchars(
$receipt['student_id']
);

?>

</td>

</tr>

<tr>

<th>
Class
</th>

<td>

<?php

echo htmlspecialchars(
$receipt['class_name']
);

?>

</td>

<th>
Section
</th>

<td>

<?php

echo htmlspecialchars(
$receipt['section_name']
);

?>

</td>

</tr>

<tr>

<th>
Fee Name
</th>

<td>

<?php

echo htmlspecialchars(
$receipt['fee_name']
);

?>

</td>

<th>
Academic Year
</th>

<td>

<?php

echo htmlspecialchars(
$receipt['academic_year']
);

?>

</td>

</tr>

</table>

<br>

<table class="receipt-table">

<tr>

<th>
Total Fee Amount
</th>

<td>

₹<?php

echo number_format(
$receipt['amount'],
2
);

?>

</td>

</tr>

<tr>

<th>
Amount Paid Now
</th>

<td>

₹<?php

echo number_format(
$receipt['amount_paid'],
2
);

?>

</td>

</tr>

<tr>

<th>
Remaining Balance
</th>

<td>

₹<?php

echo number_format(
$receipt['balance_amount'],
2
);

?>

</td>

</tr>

<tr>

<th>
Payment Method
</th>

<td>

<?php

echo ucwords(

str_replace(
'_',
' ',
$receipt['payment_method']
)

);

?>

</td>

</tr>

<tr>

<th>
Transaction Reference
</th>

<td>

<?php

echo !empty($receipt['transaction_ref'])

? htmlspecialchars(
$receipt['transaction_ref']
)

: '-';

?>

</td>

</tr>

<tr>

<th>
Remarks
</th>

<td>

<?php

echo !empty($receipt['remarks'])

? htmlspecialchars(
$receipt['remarks']
)

: '-';

?>

</td>

</tr>

</table>

<div
style="
margin-top:60px;
display:flex;
justify-content:space-between;
">

<div>

_____________________

<br>

Student Signature

</div>

<div>

_____________________

<br>

Authorized Signature

</div>

</div>

</div>

</div>


</div>

</div>

</div>

<script src="../assets/js/common.js"></script>

</body>

</html>