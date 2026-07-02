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

fs.fee_name LIKE '%$search%'

OR

c.class_name LIKE '%$search%'

OR

fs.academic_year LIKE '%$search%'

OR

fs.status LIKE '%$search%'

";

}

/* =========================
   Fee Structures
========================= */

$query = mysqli_query(

$conn,

"SELECT

fs.*,

c.class_name,

(
SELECT COUNT(*)
FROM student_fees sf
WHERE sf.fee_structure_id = fs.fee_structure_id
) AS assigned_students

FROM fee_structures fs

INNER JOIN classes c
ON fs.class_id = c.class_id

$where

ORDER BY fs.fee_structure_id DESC"

);

/* =========================
   Statistics
========================= */

$total_structures = mysqli_fetch_assoc(

mysqli_query(

$conn,

"SELECT COUNT(*) total
FROM fee_structures"

)

)['total'];

$total_assigned_fees = mysqli_fetch_assoc(

mysqli_query(

$conn,

"SELECT COUNT(*) total
FROM student_fees"

)

)['total'];

$total_collected = mysqli_fetch_assoc(

mysqli_query(

$conn,

"SELECT

IFNULL(
SUM(amount_paid),
0
) total

FROM fee_payments"

)

)['total'];

$total_pending = mysqli_fetch_assoc(

mysqli_query(

$conn,

"SELECT

IFNULL(
SUM(balance_amount),
0
) total

FROM student_fees"

)

)['total'];

/* =========================
   Recent Payments
========================= */

$recentPayments = mysqli_query(

$conn,

"SELECT

fp.receipt_no,
fp.amount_paid,
fp.payment_date,

u.full_name,

st.student_id

FROM fee_payments fp

INNER JOIN student_fees sf
ON fp.student_fee_id = sf.student_fee_id

INNER JOIN students st
ON sf.student_id = st.id

INNER JOIN users u
ON st.user_id = u.id

ORDER BY fp.payment_id DESC

LIMIT 10"

);

$pendingFees = mysqli_query(

$conn,

"SELECT

sf.student_fee_id,
sf.balance_amount,
sf.payment_status,

u.full_name,
st.student_id,

fs.fee_name,

c.class_name,
sec.section_name

FROM student_fees sf

INNER JOIN students st
ON sf.student_id=st.id

INNER JOIN users u
ON st.user_id=u.id

INNER JOIN fee_structures fs
ON sf.fee_structure_id=fs.fee_structure_id

LEFT JOIN classes c
ON st.class_id=c.class_id

LEFT JOIN sections sec
ON st.section_id=sec.section_id

WHERE sf.balance_amount > 0

ORDER BY sf.balance_amount DESC

LIMIT 20"

);
$total_unpaid_students = mysqli_fetch_assoc(

mysqli_query(

$conn,

"SELECT COUNT(*) total

FROM student_fees

WHERE balance_amount > 0"

)

)['total'];

?>
<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<script>
(function(){
    if(localStorage.getItem("mindmerge-theme") === "dark"){
        document.documentElement.classList.add("dark-mode");
    }
})();
</script>


<meta
name="viewport"
content="width=device-width, initial-scale=1.0">

<title>
Fees Management | MindMerge SmartCampus
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

<link rel="stylesheet" href="../assets/css/material-theme.css">

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
Fees Management
</h1>

<p>
Manage fee structures, student fees and payments.
</p>

</div>

<a
href="add.php"
class="btn btn-primary">

<i class="fa-solid fa-plus"></i>

Add Fee Structure

</a>

</div>

<?php if(isset($_GET['success'])){ ?>

<div
style="
background:#dcfce7;
color:#166534;
padding:14px 18px;
border-radius:14px;
margin-bottom:20px;
font-weight:500;
">

<?php

if($_GET['success']=='added'){

echo "Fee structure created successfully.";

}
elseif($_GET['success']=='updated'){

echo "Fee structure updated successfully.";

}
elseif($_GET['success']=='deleted'){

echo "Fee structure deleted successfully.";

}
elseif($_GET['success']=='collected'){

echo "Fee payment collected successfully.";

}

?>

</div>

<?php } ?>

<?php if(isset($_GET['error'])){ ?>

<div
style="
background:#fee2e2;
color:#991b1b;
padding:14px 18px;
border-radius:14px;
margin-bottom:20px;
font-weight:500;
">

<?php

if($_GET['error']=='not_found'){

echo "Record not found.";

}
elseif($_GET['error']=='delete_failed'){

echo "Unable to delete record.";

}
elseif($_GET['error']=='in_use'){

echo "Cannot delete fee structure because it has already been assigned to students.";

}

?>

</div>

<?php } ?>

<!-- Statistics -->

<!-- Statistics -->

<div class="dashboard-grid">

    <div class="dashboard-card">
        <div>
            <h3>Fee Structures</h3>
            <h2><?php echo $total_structures; ?></h2>
        </div>
        <div class="card-icon">
            <i class="fa-solid fa-file-invoice-dollar"></i>
        </div>
    </div>

    <div class="dashboard-card">
        <div>
            <h3>Pending Students</h3>
            <h2><?php echo $total_unpaid_students; ?></h2>
        </div>
        <div class="card-icon">
            <i class="fa-solid fa-user-clock"></i>
        </div>
    </div>

    <div class="dashboard-card">
        <div>
            <h3>Assigned Fees</h3>
            <h2><?php echo $total_assigned_fees; ?></h2>
        </div>
        <div class="card-icon">
            <i class="fa-solid fa-users"></i>
        </div>
    </div>

    <div class="dashboard-card">
        <div>
            <h3>Collected Amount</h3>
            <h2>₹<?php echo number_format($total_collected,2); ?></h2>
        </div>
        <div class="card-icon">
            <i class="fa-solid fa-money-bill-wave"></i>
        </div>
    </div>

    <div class="dashboard-card">
        <div>
            <h3>Pending Amount</h3>
            <h2>₹<?php echo number_format($total_pending,2); ?></h2>
        </div>
        <div class="card-icon">
            <i class="fa-solid fa-wallet"></i>
        </div>
    </div>

</div>

<!-- Quick Actions -->

<div class="dashboard-section">

<div class="section-header">

<h2>
Quick Actions
</h2>

</div>

<div
style="
display:flex;
gap:12px;
flex-wrap:wrap;
">

<a
href="add.php"
class="btn btn-primary">

<i class="fa-solid fa-plus"></i>

Add Fee Structure

</a>

<a
href="assign.php"
class="btn">

<i class="fa-solid fa-users"></i>

Assign Fees

</a>

<a
href="receipts.php"
class="btn">

<i class="fa-solid fa-money-bill-wave"></i>

Receipts

</a>

<a
href="report.php"
class="btn">

<i class="fa-solid fa-chart-column"></i>

Reports

</a>

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

placeholder="Search Fee Name, Class or Academic Year"

value="<?php echo htmlspecialchars($search); ?>"

style="flex:1;min-width:250px;">

<button
type="submit"
class="btn btn-primary">

<i class="fa-solid fa-magnifying-glass"></i>

Search

</button>

<a
href="index.php"
class="btn">

Reset

</a>

</div>

</form>

</div>

<!-- Fee Structures -->

<div class="dashboard-section">

<div class="section-header">

<h2>
Fee Structures
</h2>

</div>

<div class="table-responsive">

<table class="custom-table">

<thead>

<tr>

<th data-sort="true">
Fee Name
</th>

<th data-sort="true">
Class
</th>

<th data-sort="true">
Amount
</th>

<th data-sort="true">
Due Date
</th>

<th data-sort="true">
Academic Year
</th>

<th data-sort="true">
Assigned
</th>

<th data-sort="true">
Status
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

<?php echo htmlspecialchars($row['fee_name']); ?>

</strong>

</td>

<td>

<?php echo htmlspecialchars($row['class_name']); ?>

</td>

<td>

₹<?php echo number_format($row['amount'],2); ?>

</td>

<td>

<?php

echo !empty($row['due_date'])

? date(
'd M Y',
strtotime($row['due_date'])
)

: '-';

?>

</td>

<td>

<?php echo htmlspecialchars($row['academic_year']); ?>

</td>

<td>

<a
href="view.php?id=<?php echo $row['fee_structure_id']; ?>">

<?php echo (int)$row['assigned_students']; ?>

</a>

</td>

<td>

<span
class="status <?php echo ($row['status']=='active') ? 'success' : 'danger'; ?>">

<?php echo ucfirst($row['status']); ?>

</span>

</td>

<td>

<div
style="
display:flex;
gap:8px;
flex-wrap:wrap;
">

<a
href="view.php?id=<?php echo $row['fee_structure_id']; ?>"
class="btn"
style="
background:#10b981;
color:white;
">

<i class="fa-solid fa-eye"></i>

View

</a>

<a
href="edit.php?id=<?php echo $row['fee_structure_id']; ?>"
class="btn btn-primary">

<i class="fa-solid fa-pen"></i>

Edit

</a>

<a
href="delete.php?id=<?php echo $row['fee_structure_id']; ?>"
class="btn"
style="
background:#ef4444;
color:white;
"
onclick="return confirm('Delete this fee structure?');">

<i class="fa-solid fa-trash"></i>

Delete

</a>

</div>

</td>

</tr>

<?php

}

}else{

?>

<tr>

<td
colspan="8"
style="
text-align:center;
padding:40px;
">

No fee structures found.

</td>

</tr>

<?php } ?>

</tbody>

</table>

</div>

</div>

<!-- Recent Payments -->

<div class="dashboard-section">

<div class="section-header">

<h2>
Recent Payments
</h2>

</div>

<div class="table-responsive">

<table class="custom-table">

<thead>

<tr>

<th>
Receipt No
</th>

<th>
Student
</th>

<th>
Student ID
</th>

<th>
Amount
</th>

<th>
Payment Date
</th>

</tr>

</thead>

<tbody>

<?php

if(mysqli_num_rows($recentPayments)>0){

while($payment=mysqli_fetch_assoc($recentPayments)){

?>

<tr>

<td>

<?php echo htmlspecialchars($payment['receipt_no'] ?? '-'); ?>

</td>

<td>

<?php echo htmlspecialchars($payment['full_name']); ?>

</td>

<td>

<?php echo htmlspecialchars($payment['student_id']); ?>

</td>

<td>

₹<?php echo number_format($payment['amount_paid'],2); ?>

</td>

<td>

<?php

echo date(
'd M Y',
strtotime($payment['payment_date'])
);

?>

</td>

</tr>

<?php

}

}else{

?>

<tr>

<td
colspan="5"
style="
text-align:center;
padding:30px;
">

No payments found.

</td>

</tr>

<?php } ?>

</tbody>

</table>

</div>

</div>
<!-- Pending Collections -->

<div class="dashboard-section">

<div class="section-header">

<h2>
Pending Collections
</h2>

</div>

<div class="table-responsive">

<table class="custom-table">

<thead>

<tr>

<th>Student</th>
<th>ID</th>
<th>Class</th>
<th>Section</th>
<th>Fee</th>
<th>Balance</th>
<th>Status</th>
<th>Action</th>

</tr>

</thead>

<tbody>

<?php

if(mysqli_num_rows($pendingFees) > 0){

while($row = mysqli_fetch_assoc($pendingFees)){

?>

<tr>

<td>

<?php echo htmlspecialchars($row['full_name']); ?>

</td>

<td>

<?php echo htmlspecialchars($row['student_id']); ?>

</td>

<td>

<?php echo htmlspecialchars($row['class_name'] ?? '-'); ?>

</td>

<td>

<?php echo htmlspecialchars($row['section_name'] ?? '-'); ?>

</td>

<td>

<?php echo htmlspecialchars($row['fee_name']); ?>

</td>

<td>

₹<?php echo number_format($row['balance_amount'],2); ?>

</td>

<td>

<span class="status warning">

<?php echo ucfirst($row['payment_status']); ?>

</span>

</td>

<td>

<a
href="collect.php?id=<?php echo $row['student_fee_id']; ?>"
class="btn btn-primary">

Collect

</a>

</td>

</tr>

<?php

}

}else{

?>

<tr>

<td colspan="8" style="text-align:center;padding:30px;">

No pending fee collections.

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