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

r.route_name LIKE '%$search%'

OR

b.bus_name LIKE '%$search%'

OR

b.bus_number LIKE '%$search%'

";

}

$query = mysqli_query(

$conn,

"SELECT

r.*,

b.bus_name,

b.bus_number,

COUNT(s.stop_id) AS total_stops

FROM transport_routes r

LEFT JOIN transport_buses b
ON r.bus_id = b.bus_id

LEFT JOIN transport_stops s
ON r.route_id = s.route_id

$where

GROUP BY r.route_id

ORDER BY r.route_id DESC"

);

/* Statistics */

$total_routes = mysqli_fetch_assoc(

mysqli_query(

$conn,

"SELECT COUNT(*) total
FROM transport_routes"

)

)['total'];

$active_routes = mysqli_fetch_assoc(

mysqli_query(

$conn,

"SELECT COUNT(*) total
FROM transport_routes
WHERE status='active'"

)

)['total'];

$total_stops = mysqli_fetch_assoc(

mysqli_query(

$conn,

"SELECT COUNT(*) total
FROM transport_stops"

)

)['total'];

$buses_with_routes = mysqli_fetch_assoc(

mysqli_query(

$conn,

"SELECT COUNT(DISTINCT bus_id) total
FROM transport_routes"

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
Route Management | MindMerge SmartCampus
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
Route Management
</h1>

<p>
Create and manage transport routes and stops.
</p>

</div>

<a
href="add.php"
class="btn btn-primary">

<i class="fa-solid fa-route"></i>

Add Route

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

echo "Route created successfully.";

}
elseif($_GET['success']=='updated'){

echo "Route updated successfully.";

}
elseif($_GET['success']=='deleted'){

echo "Route deleted successfully.";

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

echo "Route not found.";

}elseif($_GET['error']=='in_use'){

    echo "Students are assigned to this route.";

}
elseif($_GET['error']=='delete_failed'){

    echo "Unable to delete route.";

}

?>

</div>

<?php } ?>

<!-- Statistics -->

<div class="dashboard-grid">

<div class="dashboard-card">

<h3>Total Routes</h3>

<h2>

<?php
echo $total_routes;
?>

</h2>

</div>

<div class="dashboard-card">

<h3>Active Routes</h3>

<h2>

<?php
echo $active_routes;
?>

</h2>

</div>

<div class="dashboard-card">

<h3>Total Stops</h3>

<h2>

<?php
echo $total_stops;
?>

</h2>

</div>

<div class="dashboard-card">

<h3>Buses With Routes</h3>

<h2>

<?php
echo $buses_with_routes;
?>

</h2>

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

Add Route

</a>
<a
href="../buses/index.php"
class="btn">

<i class="fa-solid fa-bus"></i>

Manage Buses

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

placeholder="Search Route Name, Bus Name or Bus Number"

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

<!-- Routes Table -->

<div class="dashboard-section">

<div class="section-header">

<h2>
All Routes
</h2>

</div>

<div class="table-responsive">

<table class="custom-table">

<thead>

<tr>

<th data-sort="true">
Route Name
</th>

<th data-sort="true">
Bus
</th>

<th data-sort="true">
Stops
</th>

<th data-sort="true">
Start Time
</th>

<th data-sort="true">
End Time
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

<?php
echo htmlspecialchars(
$row['route_name']
);
?>

</strong>

</td>

<td>

<?php

echo htmlspecialchars(
$row['bus_name'] ?? 'No Bus Assigned'
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

echo (int)$row['total_stops'];

?>

Stops

</td>

<td>

<?php

echo (
    !empty($row['start_time'])
    &&
    $row['start_time'] != '00:00:00'
)
? date(
    'h:i A',
    strtotime($row['start_time'])
)
: '-';

?>

</td>

<td>

<?php

echo (
    !empty($row['end_time'])
    &&
    $row['end_time'] != '00:00:00'
)
? date(
    'h:i A',
    strtotime($row['end_time'])
)
: '-';

?>

</td>

<td>

<span
class="status <?php echo ($row['status']=='active') ? 'success' : 'danger'; ?>">

<?php

echo ucfirst(
$row['status']
);

?>

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
href="view.php?id=<?php echo $row['route_id']; ?>"
class="btn"
style="
background:#10b981;
color:white;
">

<i class="fa-solid fa-eye"></i>

View

</a>

<a
href="edit.php?id=<?php echo $row['route_id']; ?>"
class="btn btn-primary">

<i class="fa-solid fa-pen"></i>

Edit

</a>

<a
href="delete.php?id=<?php echo $row['route_id']; ?>"
class="btn"
style="
background:#ef4444;
color:white;
"
onclick="return confirm('Delete this route?');">

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

No routes found.

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
