<?php

include('../config/auth.php');
include('../config/db.php');

$search = '';

$where = '';

if(
isset($_GET['search'])
&&
trim($_GET['search']) != ''
){

$search = mysqli_real_escape_string(
$conn,
trim($_GET['search'])
);

$where = "

WHERE

u.full_name LIKE '%$search%'

OR

st.student_id LIKE '%$search%'

OR

fp.receipt_no LIKE '%$search%'

OR

fs.fee_name LIKE '%$search%'

OR

fp.payment_method LIKE '%$search%'

";

}

$query = mysqli_query(

$conn,

"SELECT

fp.*,

sf.amount,
sf.paid_amount,
sf.balance_amount,

fs.fee_name,

st.student_id,

u.full_name,

c.class_name,

sec.section_name

FROM fee_payments fp

INNER JOIN student_fees sf
ON fp.student_fee_id = sf.student_fee_id

INNER JOIN fee_structures fs
ON sf.fee_structure_id = fs.fee_structure_id

INNER JOIN students st
ON sf.student_id = st.id

INNER JOIN users u
ON st.user_id = u.id

LEFT JOIN classes c
ON st.class_id = c.class_id

LEFT JOIN sections sec
ON st.section_id = sec.section_id

$where

ORDER BY fp.payment_id DESC"

);

/* Dashboard Stats */

$total_payments = mysqli_fetch_assoc(

mysqli_query(

$conn,

"SELECT COUNT(*) total
FROM fee_payments"

)

)['total'];

$total_collection = mysqli_fetch_assoc(

mysqli_query(

$conn,

"SELECT
IFNULL(SUM(amount_paid),0) total
FROM fee_payments"

)

)['total'];

$today_collection = mysqli_fetch_assoc(

mysqli_query(

$conn,

"SELECT
IFNULL(SUM(amount_paid),0) total

FROM fee_payments

WHERE payment_date = CURDATE()"

)

)['total'];

$this_month = mysqli_fetch_assoc(

mysqli_query(

$conn,

"SELECT
IFNULL(SUM(amount_paid),0) total

FROM fee_payments

WHERE MONTH(payment_date)=MONTH(CURDATE())

AND YEAR(payment_date)=YEAR(CURDATE())"

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
Fee Payments
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
Fee Payments
</h1>

<p>
Payment history and collections.
</p>

</div>

<a
href="index.php"
class="btn">

<i class="fa-solid fa-arrow-left"></i>

Back

</a>

</div>

<!-- Stats -->

<div class="dashboard-grid">

<div class="dashboard-card">

<h3>Total Payments</h3>

<h2>

<?php echo $total_payments; ?>

</h2>

</div>

<div class="dashboard-card">

<h3>Total Collection</h3>

<h2>

₹<?php echo number_format($total_collection,2); ?>

</h2>

</div>

<div class="dashboard-card">

<h3>Today Collection</h3>

<h2>

₹<?php echo number_format($today_collection,2); ?>

</h2>

</div>

<div class="dashboard-card">

<h3>This Month</h3>

<h2>

₹<?php echo number_format($this_month,2); ?>

</h2>

</div>

</div>

<!-- Search -->

<div class="dashboard-section">

<form method="GET">

<div
style="
display:flex;
gap:12px;
flex-wrap:wrap;
">

<input

type="text"

name="search"

class="form-input"

placeholder="Student, Receipt, Fee Name..."

value="<?php echo htmlspecialchars($search); ?>"

style="flex:1;min-width:250px;">

<button
type="submit"
class="btn btn-primary">

<i class="fa-solid fa-magnifying-glass"></i>

Search

</button>

<a
href="payments.php"
class="btn">

Reset

</a>

</div>

</form>

</div>

<!-- Payments Table -->

<div class="dashboard-section">

<div class="section-header">

<h2>
Payment History
</h2>

</div>

<div class="table-responsive">

<table class="custom-table">

<thead>

<tr>

<th data-sort="true">
Receipt
</th>

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
Date
</th>

<th data-sort="true">
Method
</th>

<th data-sort="false">
Actions
</th>

</tr>

</thead>

<tbody>

<?php

if(mysqli_num_rows($query) > 0){

while($row = mysqli_fetch_assoc($query)){

?>

<tr>

<td>

<strong>

<?php

echo htmlspecialchars(
$row['receipt_no']
);

?>

</strong>

</td>

<td>

<?php

echo htmlspecialchars(
$row['full_name']
);

?>

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
$row['amount_paid'],
2
);

?>

</td>

<td>

<?php

echo date(
'd M Y',
strtotime(
$row['payment_date']
)
);

?>

</td>

<td>

<?php

echo ucwords(
str_replace(
'_',
' ',
$row['payment_method']
)
);

?>

</td>

<td>

<div
style="
display:flex;
gap:8px;
flex-wrap:wrap;
">

<a
href="receipts.php?id=<?php echo $row['payment_id']; ?>"
class="btn btn-primary">

<i class="fa-solid fa-receipt"></i>

Receipt

</a>

</div>

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

No payment records found.

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