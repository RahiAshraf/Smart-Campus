<?php

include('../config/auth.php');
include('../config/db.php');

$error = '';
$success = '';

$student_fee_id = isset($_GET['id'])
? (int)$_GET['id']
: 0;

$feeData = null;

if($student_fee_id > 0){

$feeQuery = mysqli_query(

$conn,

"SELECT

sf.*,

fs.fee_name,
fs.due_date,
fs.academic_year,

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

WHERE sf.student_fee_id='$student_fee_id'

LIMIT 1"

);

if(mysqli_num_rows($feeQuery) > 0){

$feeData =
mysqli_fetch_assoc(
$feeQuery
);

}

}

if($_SERVER['REQUEST_METHOD']=='POST'){

$student_fee_id =
(int)$_POST['student_fee_id'];

$amount_paid =
(float)$_POST['amount_paid'];

$payment_date =
mysqli_real_escape_string(
$conn,
$_POST['payment_date']
);

$payment_method =
mysqli_real_escape_string(
$conn,
$_POST['payment_method']
);

$transaction_ref =
mysqli_real_escape_string(
$conn,
trim($_POST['transaction_ref'])
);

$remarks =
mysqli_real_escape_string(
$conn,
trim($_POST['remarks'])
);

$feeQuery = mysqli_query(

$conn,

"SELECT *

FROM student_fees

WHERE student_fee_id='$student_fee_id'

LIMIT 1"

);

$fee =
mysqli_fetch_assoc(
$feeQuery
);

if($amount_paid <= 0){

$error =
'Payment amount must be greater than zero.';

}
elseif($amount_paid > $fee['balance_amount']){

$error =
'Payment amount cannot exceed balance amount.';

}
else{

$receipt_no =
'MMF' .
date('Ymd') .
rand(1000,9999);

$created_by =
$_SESSION['user']['id'];

mysqli_query(

$conn,

"INSERT INTO fee_payments
(
receipt_no,
student_fee_id,
amount_paid,
payment_date,
payment_method,
transaction_ref,
remarks,
created_by
)

VALUES
(
'$receipt_no',
'$student_fee_id',
'$amount_paid',
'$payment_date',
'$payment_method',
'$transaction_ref',
'$remarks',
'$created_by'
)"

);

$new_paid =
$fee['paid_amount'] +
$amount_paid;

$new_balance =
$fee['amount'] -
$new_paid;

$status = 'unpaid';

if($new_balance <= 0){

$status = 'paid';
$new_balance = 0;

}
elseif($new_paid > 0){

$status = 'partial';

}

mysqli_query(

$conn,

"UPDATE student_fees

SET

paid_amount='$new_paid',
balance_amount='$new_balance',
payment_status='$status',
last_payment_date='$payment_date'

WHERE student_fee_id='$student_fee_id'"

);

header(
'Location:collect.php?id=' .
$student_fee_id .
'&success=paid'
);

exit;

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
Collect Fee Payment
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
Fee Collection
</h1>

<p>
Collect and record student payments.
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

Payment recorded successfully.

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

<?php if($feeData){ ?>

<!-- Summary -->

<div class="dashboard-grid">

<div class="dashboard-card">

<h3>
Total Fee
</h3>

<h2>

₹<?php echo number_format($feeData['amount'],2); ?>

</h2>

</div>

<div class="dashboard-card">

<h3>
Paid
</h3>

<h2>

₹<?php echo number_format($feeData['paid_amount'],2); ?>

</h2>

</div>

<div class="dashboard-card">

<h3>
Balance
</h3>

<h2>

₹<?php echo number_format($feeData['balance_amount'],2); ?>

</h2>

</div>

<div class="dashboard-card">

<h3>
Status
</h3>

<h2>

<?php echo ucfirst($feeData['payment_status']); ?>

</h2>

</div>

</div>

<!-- Student Info -->

<div class="dashboard-section">

<div class="section-header">

<h2>
Student Information
</h2>

</div>

<div class="form-grid">

<div class="form-group">

<label>Student</label>

<div class="form-input">

<?php echo htmlspecialchars($feeData['full_name']); ?>

</div>

</div>

<div class="form-group">

<label>Student ID</label>

<div class="form-input">

<?php echo htmlspecialchars($feeData['student_id']); ?>

</div>

</div>

<div class="form-group">

<label>Class</label>

<div class="form-input">

<?php echo htmlspecialchars($feeData['class_name']); ?>

</div>

</div>

<div class="form-group">

<label>Section</label>

<div class="form-input">

<?php echo htmlspecialchars($feeData['section_name']); ?>

</div>

</div>

<div class="form-group">

<label>Fee Name</label>

<div class="form-input">

<?php echo htmlspecialchars($feeData['fee_name']); ?>

</div>

</div>

<div class="form-group">

<label>Due Date</label>

<div class="form-input">

<?php echo date('d M Y',strtotime($feeData['due_date'])); ?>

</div>

</div>

</div>

</div>

<!-- Payment Form -->

<?php if($feeData['balance_amount'] > 0){ ?>

<div class="dashboard-section">

<div class="section-header">

<h2>
Record Payment
</h2>

</div>

<form method="POST">

<input
type="hidden"
name="student_fee_id"
value="<?php echo $feeData['student_fee_id']; ?>">

<div class="form-grid">

<div class="form-group">

<label>
Amount Paid
</label>

<input

type="number"

name="amount_paid"

class="form-input"

step="0.01"

min="0.01"

max="<?php echo $feeData['balance_amount']; ?>"

required>

</div>

<div class="form-group">

<label>
Payment Date
</label>

<input

type="date"

name="payment_date"

class="form-input"

value="<?php echo date('Y-m-d'); ?>"

required>

</div>

<div class="form-group">

<label>
Payment Method
</label>

<select
name="payment_method"
class="form-input">

<option value="cash">Cash</option>
<option value="upi">UPI</option>
<option value="card">Card</option>
<option value="bank_transfer">Bank Transfer</option>
<option value="cheque">Cheque</option>

</select>

</div>

<div class="form-group">

<label>
Transaction Ref
</label>

<input

type="text"

name="transaction_ref"

class="form-input">

</div>

</div>

<div class="form-group">

<label>
Remarks
</label>

<textarea

name="remarks"

class="form-input"

rows="3"></textarea>

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

<i class="fa-solid fa-money-bill-wave"></i>

Collect Payment

</button>

</div>

</form>

</div>

<?php } ?>

<?php } else { ?>

<div class="dashboard-section">

<div
style="
text-align:center;
padding:50px;
">

No fee record found.

</div>

</div>

<?php } ?>

</div>

</div>

</div>

<script src="../assets/js/common.js"></script>

</body>

</html>