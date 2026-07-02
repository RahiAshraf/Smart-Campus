<?php

include('../config/auth.php');
include('../config/db.php');

if(
!isset($_GET['id'])
||
!is_numeric($_GET['id'])
){

header('Location:index.php?error=not_found');
exit;

}

$fee_structure_id = (int)$_GET['id'];

$feeQuery = mysqli_query(

$conn,

"SELECT

fs.*,

c.class_name

FROM fee_structures fs

INNER JOIN classes c
ON fs.class_id=c.class_id

WHERE fs.fee_structure_id='$fee_structure_id'

LIMIT 1"

);

if(mysqli_num_rows($feeQuery)==0){

header('Location:index.php?error=not_found');
exit;

}

$fee =
mysqli_fetch_assoc(
$feeQuery
);

$assignedStudents = mysqli_query(

$conn,

"SELECT

sf.*,

s.student_id,

u.full_name

FROM student_fees sf

INNER JOIN students s
ON sf.student_id=s.id

INNER JOIN users u
ON s.user_id=u.id

WHERE sf.fee_structure_id='$fee_structure_id'

ORDER BY u.full_name ASC"

);

$stats = mysqli_fetch_assoc(

mysqli_query(

$conn,

"SELECT

COUNT(*) total_students,

IFNULL(
SUM(amount),
0
) total_amount,

IFNULL(
SUM(paid_amount),
0
) total_paid,

IFNULL(
SUM(balance_amount),
0
) total_balance

FROM student_fees

WHERE fee_structure_id='$fee_structure_id'"

)

);

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
View Fee Structure
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
Fee Structure Details
</h1>

<p>

<?php

echo htmlspecialchars(
$fee['fee_name']
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
href="index.php"
class="btn">

<i class="fa-solid fa-arrow-left"></i>

Back

</a>

<a
href="edit.php?id=<?php echo $fee_structure_id; ?>"
class="btn btn-primary">

<i class="fa-solid fa-pen"></i>

Edit

</a>

</div>

</div>

<!-- Statistics -->

<div class="dashboard-grid">

<div class="dashboard-card">

<h3>
Assigned Students
</h3>

<h2>

<?php

echo (int)$stats['total_students'];

?>

</h2>

</div>


<div class="dashboard-card">

<h3>
Total Fee Amount
</h3>

<h2>

₹<?php

echo number_format(
$stats['total_amount'],
2
);

?>

</h2>

</div>

<div class="dashboard-card">

<h3>
Collected
</h3>

<h2>

₹<?php

echo number_format(
$stats['total_paid'],
2
);

?>

</h2>

</div>

<div class="dashboard-card">

<h3>
Pending
</h3>

<h2>

₹<?php

echo number_format(
$stats['total_balance'],
2
);

?>

</h2>

</div>

</div>

<!-- Fee Information -->

<div class="dashboard-section">

<div class="section-header">

<h2>
Fee Information
</h2>

</div>

<div class="form-grid">

<div class="form-group">

<label>
Fee Name
</label>

<div class="form-input">

<?php

echo htmlspecialchars(
$fee['fee_name']
);

?>

</div>

</div>

<div class="form-group">

<label>
Class
</label>

<div class="form-input">

<?php

echo htmlspecialchars(
$fee['class_name']
);

?>

</div>

</div>

<div class="form-group">

<label>
Amount
</label>

<div class="form-input">

₹<?php

echo number_format(
$fee['amount'],
2
);

?>

</div>

</div>

<div class="form-group">

<label>
Due Date
</label>

<div class="form-input">

<?php

echo !empty($fee['due_date'])

? date(
'd M Y',
strtotime($fee['due_date'])
)

: '-';

?>

</div>

</div>

<div class="form-group">

<label>
Academic Year
</label>

<div class="form-input">

<?php

echo htmlspecialchars(
$fee['academic_year']
);

?>

</div>

</div>

<div class="form-group">

<label>
Status
</label>

<div class="form-input">

<?php

echo ucfirst(
$fee['status']
);

?>

</div>

</div>

<div
class="form-group"
style="grid-column:1/-1;">

<label>
Description
</label>

<div class="form-input">

<?php

echo !empty($fee['description'])

? nl2br(
htmlspecialchars(
$fee['description']
)
)

: '-';

?>

</div>

</div>

</div>

</div>

<!-- Assigned Students -->

<div class="dashboard-section">

<div class="section-header">

<h2>
Assigned Students
</h2>

</div>

<div class="table-responsive">

<table class="custom-table">

<thead>

<tr>

<th>
Student ID
</th>

<th>
Student Name
</th>

<th>
Amount
</th>

<th>
Paid
</th>

<th>
Balance
</th>

<th>
Status
</th>
<th>
Action
</th>

</tr>

</thead>

<tbody>

<?php

if(mysqli_num_rows($assignedStudents) > 0){

while($row =
mysqli_fetch_assoc(
$assignedStudents
)){

?>

<tr>

<td>

<?php

echo htmlspecialchars(
$row['student_id']
);

?>

</td>

<td>

<?php

echo htmlspecialchars(
$row['full_name']
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

$statusClass = 'danger';

if($row['payment_status']=='partial'){
$statusClass='warning';
}
elseif($row['payment_status']=='paid'){
$statusClass='success';
}

?>

<span
class="status <?php echo $statusClass; ?>">

<?php

echo ucfirst(
$row['payment_status']
);

?>

</span>

</td>
<td>

<?php if($row['balance_amount'] > 0){ ?>

<a
href="collect.php?id=<?php echo $row['student_fee_id']; ?>"
class="btn btn-primary">

Collect

</a>

<?php } else { ?>

<span class="status success">

Completed

</span>

<?php } ?>

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

No students assigned yet.

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