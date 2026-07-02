<?php

include('../../config/auth.php');
include('../../config/db.php');

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

s.student_id LIKE '%$search%'

OR

b.bus_name LIKE '%$search%'

OR

b.bus_number LIKE '%$search%'

OR

st.stop_name LIKE '%$search%'

";

}

/* =========================
   Assignment List
========================= */

$query = mysqli_query(

$conn,

"SELECT

tsa.assignment_id,
tsa.assigned_at,

s.id AS student_db_id,
s.student_id,
s.class_name,
s.section_name,

u.full_name,

b.bus_id,
b.bus_name,
b.bus_number,

st.stop_id,
st.stop_name

FROM transport_student_assignments tsa

INNER JOIN students s
ON tsa.student_id = s.id

INNER JOIN users u
ON s.user_id = u.id

LEFT JOIN transport_buses b
ON tsa.bus_id = b.bus_id

LEFT JOIN transport_stops st
ON tsa.stop_id = st.stop_id

$where

ORDER BY tsa.assignment_id DESC"

);

/* =========================
   Statistics
========================= */

$total_assignments = mysqli_fetch_assoc(

mysqli_query(

$conn,

"SELECT COUNT(*) total
FROM transport_student_assignments"

)

)['total'];

$assigned_students = mysqli_fetch_assoc(

mysqli_query(

$conn,

"SELECT COUNT(DISTINCT student_id) total
FROM transport_student_assignments"

)

)['total'];

$assigned_buses = mysqli_fetch_assoc(

mysqli_query(

$conn,

"SELECT COUNT(DISTINCT bus_id) total
FROM transport_student_assignments"

)

)['total'];

$available_students = mysqli_fetch_assoc(

mysqli_query(

$conn,

"SELECT COUNT(*) total
FROM students s
WHERE NOT EXISTS(
    SELECT 1
    FROM transport_student_assignments tsa
    WHERE tsa.student_id = s.id
)"

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
Student Transport Assignments
</title>

<link
rel="stylesheet"
href="../../assets/css/global.css">

<link
rel="stylesheet"
href="../../assets/css/layout.css">

<link
rel="stylesheet"
href="../../assets/css/components.css">

<link
rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<link rel="stylesheet" href="../../assets/css/material-theme.css">
</head>

<body>

<div class="app-layout">

<?php include('../../partials/sidebar.php'); ?>

<div class="main-content">

<?php include('../../partials/topbar.php'); ?>

<div class="page-content">

<div class="page-header">

<div>

<h1>
Student Transport Assignments
</h1>

<p>
Assign students to buses and route stops.
</p>

</div>

<a
href="add.php"
class="btn btn-primary">

<i class="fa-solid fa-plus"></i>

Assign Student

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

if($_GET['success'] == 'added'){

echo 'Student assigned successfully.';

}
elseif($_GET['success'] == 'updated'){

echo 'Assignment updated successfully.';

}
elseif($_GET['success'] == 'deleted'){

echo 'Assignment removed successfully.';

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
    echo "Assignment not found.";
}
elseif($_GET['error']=='delete_failed'){
    echo "Unable to remove assignment.";
}

?>

</div>

<?php } ?>

<!-- Statistics -->

<div class="dashboard-grid">

<div class="dashboard-card">

<h3>Total Assignments</h3>

<h2>

<?php echo $total_assignments; ?>

</h2>

</div>

<div class="dashboard-card">

<h3>Assigned Students</h3>

<h2>

<?php echo $assigned_students; ?>

</h2>

</div>

<div class="dashboard-card">

<h3>Assigned Buses</h3>

<h2>

<?php echo $assigned_buses; ?>

</h2>

</div>

<div class="dashboard-card">

<h3>Available Students</h3>

<h2>

<?php echo $available_students; ?>

</h2>

</div>

</div>

<!-- Quick Actions -->
<div class="dashboard-section">

<div class="section-header">
<h2>Quick Actions</h2>
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

Assign Student

</a>

<a
href="../buses/index.php"
class="btn">

<i class="fa-solid fa-bus"></i>

Manage Buses

</a>

<a
href="../routes/index.php"
class="btn">

<i class="fa-solid fa-route"></i>

Manage Routes

</a>

<a
href="../staff/index.php"
class="btn">

<i class="fa-solid fa-users"></i>

Transport Staff

</a>

<a
href="../tracking/index.php"
class="btn">

<i class="fa-solid fa-location-dot"></i>

Live Tracking

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

placeholder="Search Student, Bus, Stop"

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

<!-- Assignment Table -->

<div class="dashboard-section">

<div class="section-header">

<h2>
All Assignments
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
Student ID
</th>

<th data-sort="true">
Class
</th>

<th data-sort="true">
Bus
</th>

<th data-sort="true">
Stop
</th>

<th data-sort="true">
Assigned Date
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
$row['full_name']
);

?>

</strong>

</td>

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
trim(
($row['class_name'] ?? '')
.
' '
.
($row['section_name'] ?? '')
) ?: '-'
);

?>

</td>

<td>

<?php

echo htmlspecialchars(
$row['bus_name'] ?? '-'
);

?>

<br>

<small>

<?php

echo htmlspecialchars(
$row['bus_number'] ?? '-'
);

?>

</small>

</td>

<td>

<?php

echo htmlspecialchars(
$row['stop_name'] ?? '-'
);

?>

</td>

<td>

<?php

echo date(
'd M Y',
strtotime(
$row['assigned_at']
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
href="edit.php?id=<?php echo $row['assignment_id']; ?>"
class="btn btn-primary">

<i class="fa-solid fa-pen"></i>

Edit

</a>

<a
href="delete.php?id=<?php echo $row['assignment_id']; ?>"
class="btn"
style="
background:#ef4444;
color:white;
"
onclick="return confirm('Remove this assignment?');">

<i class="fa-solid fa-trash"></i>

Delete

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
colspan="7"
style="
text-align:center;
padding:40px;
">

No assignments found.

</td>

</tr>

<?php

}

?>

</tbody>

</table>

</div>

</div>

</div>

</div>

</div>

<script src="../../assets/js/common.js"></script>

</body>

</html>