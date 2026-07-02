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

b.bus_number LIKE '%$search%'

OR

b.bus_name LIKE '%$search%'

";

}

$query = mysqli_query(

$conn,

"SELECT

b.*,

d.full_name AS driver_name,

h.full_name AS helper_name

FROM transport_buses b

LEFT JOIN transport_staff d
ON b.driver_id = d.staff_id

LEFT JOIN transport_staff h
ON b.helper_id = h.staff_id

$where

ORDER BY b.bus_id DESC"

);

/* Statistics */

$total_buses = mysqli_fetch_assoc(

mysqli_query(

$conn,

"SELECT COUNT(*) total
FROM transport_buses"

)

)['total'];

$active_buses = mysqli_fetch_assoc(

mysqli_query(

$conn,

"SELECT COUNT(*) total
FROM transport_buses
WHERE status='active'"

)

)['total'];

$total_capacity = mysqli_fetch_assoc(

mysqli_query(

$conn,

"SELECT IFNULL(SUM(capacity),0) total
FROM transport_buses"

)

)['total'];

$total_assigned = mysqli_fetch_assoc(

mysqli_query(

$conn,

"SELECT COUNT(*) total
FROM transport_student_assignments"

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
Bus Management | MindMerge SmartCampus
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
Bus Management
</h1>

<p>
Manage school buses and staff assignments.
</p>

</div>

<a
href="add.php"
class="btn btn-primary">

<i class="fa-solid fa-bus"></i>

Add Bus

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

echo "Bus added successfully.";

}
elseif($_GET['success']=='updated'){

echo "Bus updated successfully.";

}
elseif($_GET['success']=='deleted'){

echo "Bus deleted successfully.";

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

if($_GET['error']=='in_use'){

echo "This bus is assigned to students and cannot be deleted.";

}
elseif($_GET['error']=='route_exists'){

echo "Delete the assigned route before deleting this bus.";

}
elseif($_GET['error']=='delete_failed'){

echo "Unable to delete bus.";

}

?>

</div>

<?php } ?>

<!-- Statistics -->
<div class="dashboard-grid">

<div class="dashboard-card">
    <div>
        <h3>Total Buses</h3>
        <h2><?php echo $total_buses; ?></h2>
    </div>
    <div class="card-icon"><i class="fa-solid fa-bus"></i></div>
</div>

<div class="dashboard-card">
    <div>
        <h3>Active Buses</h3>
        <h2><?php echo $active_buses; ?></h2>
    </div>
    <div class="card-icon"><i class="fa-solid fa-circle-check"></i></div>
</div>

<div class="dashboard-card">
    <div>
        <h3>Total Capacity</h3>
        <h2><?php echo $total_capacity; ?></h2>
    </div>
    <div class="card-icon"><i class="fa-solid fa-users"></i></div>
</div>

<div class="dashboard-card">
    <div>
        <h3>Assigned Students</h3>
        <h2><?php echo $total_assigned; ?></h2>
    </div>
    <div class="card-icon"><i class="fa-solid fa-user-graduate"></i></div>
</div>

</div>

<!-- Quick Actions -->
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

Add Bus

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
href="../student_assignments/index.php"
class="btn">

<i class="fa-solid fa-user-graduate"></i>

Student Assignments

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

placeholder="Search Bus Number or Bus Name"

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

<!-- Table -->

<div class="dashboard-section">

<div class="section-header">

<h2>
All Buses
</h2>

</div>

<div class="table-responsive">

<table class="custom-table">

<thead>

<tr>

<th data-sort="true">
Bus Number
</th>

<th data-sort="true">
Bus Name
</th>

<th data-sort="true">
Driver
</th>

<th data-sort="true">
Helper
</th>

<th data-sort="true">
Capacity
</th>

<th data-sort="true">
Timing
</th>

<th data-sort="true">
Status
</th>

<th>
Actions
</th>

</tr>

</thead>

<tbody>

<?php

if(mysqli_num_rows($query)>0){

while($row=mysqli_fetch_assoc($query)){

?>

<tr>

<td>
<strong>
<?php echo htmlspecialchars($row['bus_number']); ?>
</strong>
</td>

<td>
<?php echo htmlspecialchars($row['bus_name']); ?>
</td>

<td>
<?php echo htmlspecialchars($row['driver_name'] ?? 'Not Assigned'); ?>
</td>

<td>
<?php echo htmlspecialchars($row['helper_name'] ?? 'Not Assigned'); ?>
</td>

<td>
<?php echo (int)$row['capacity']; ?>
</td>

<td>

<?php

echo !empty($row['start_time'])
? date('h:i A', strtotime($row['start_time']))
: '-';

?>

-

<?php

echo !empty($row['end_time'])
? date('h:i A', strtotime($row['end_time']))
: '-';

?>

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
href="edit.php?id=<?php echo $row['bus_id']; ?>"
class="btn btn-primary">

<i class="fa-solid fa-pen"></i>

Edit

</a>

<a
href="delete.php?id=<?php echo $row['bus_id']; ?>"
class="btn"
style="
background:#ef4444;
color:white;
"
onclick="return confirm('Delete this bus?');">

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
colspan="8"
style="text-align:center;">

No buses found.

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

<script src="../../assets/js/common.js"></script>

</body>

</html>
