<?php

include('../../config/auth.php');
include('../../config/db.php');

$search = '';

$where = '';

if(isset($_GET['search']) && trim($_GET['search']) != ''){

    $search = mysqli_real_escape_string(
        $conn,
        trim($_GET['search'])
    );

    $where = "

WHERE

ts.full_name LIKE '%$search%'

OR

ts.phone LIKE '%$search%'

OR

ts.license_number LIKE '%$search%'

";

}

$query = mysqli_query(

$conn,

"SELECT

ts.*,

u.email,

u.profile_photo

FROM transport_staff ts

LEFT JOIN users u
ON ts.user_id = u.id

$where

ORDER BY ts.staff_id DESC"

);

/* Statistics */

$total_staff = mysqli_fetch_assoc(

    mysqli_query(

        $conn,

        "SELECT COUNT(*) total
         FROM transport_staff"

    )

)['total'];

$total_drivers = mysqli_fetch_assoc(

    mysqli_query(

        $conn,

        "SELECT COUNT(*) total
         FROM transport_staff
         WHERE staff_type='driver'"

    )

)['total'];

$total_helpers = mysqli_fetch_assoc(

    mysqli_query(

        $conn,

        "SELECT COUNT(*) total
         FROM transport_staff
         WHERE staff_type='helper'"

    )

)['total'];

$active_staff = mysqli_fetch_assoc(

    mysqli_query(

        $conn,

        "SELECT COUNT(*) total
         FROM transport_staff
         WHERE status='active'"

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
Transport Staff Management | MindMerge SmartCampus
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
Transport Staff Management
</h1>

<p>
Manage Drivers and Helpers for school transport.
</p>

</div>

<a
href="add.php"
class="btn btn-primary">

<i class="fa-solid fa-user-plus"></i>

Add Staff

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

echo "Staff member added successfully.";

}
elseif($_GET['success'] == 'updated'){

echo "Staff member updated successfully.";

}elseif($_GET['success'] == 'deleted'){

echo "Staff member deleted successfully.";

}elseif($_GET['success']=='driver_created'){

echo "Driver created successfully. Default password: driver123";

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

if($_GET['error'] == 'in_use'){

echo "Cannot delete staff because it is assigned to a bus.";

}

?>

</div>

<?php } ?>

<!-- Statistics -->

<div class="dashboard-grid">

<div class="dashboard-card">

<h3>Total Staff</h3>

<h2>
<?php echo $total_staff; ?>
</h2>

</div>

<div class="dashboard-card">

<h3>Drivers</h3>

<h2>
<?php echo $total_drivers; ?>
</h2>

</div>

<div class="dashboard-card">

<h3>Helpers</h3>

<h2>
<?php echo $total_helpers; ?>
</h2>

</div>

<div class="dashboard-card">

<h3>Active Staff</h3>

<h2>
<?php echo $active_staff; ?>
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

<i class="fa-solid fa-user-plus"></i>

Add Staff

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

placeholder="Search Name, Phone or License Number"

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
<!-- Staff Table -->

<div class="dashboard-section">

<div class="section-header">

<h2>
All Transport Staff
</h2>

</div>

<div class="table-responsive">

<table class="custom-table">

<thead>

<tr>

<th data-sort="true">
ID
</th>

<th data-sort="true">
Name
</th>

<th data-sort="true">
Type
</th>

<th data-sort="true">
Phone
</th>

<th data-sort="true">
License Number
</th>

<th data-sort="true">
Status
</th>

<th data-sort="true">
Created On
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
echo $row['staff_id'];
?>

</strong>

</td>

<td>

<?php
echo htmlspecialchars(
$row['full_name'] ?? '-'
);
?>

</td>

<td>

<?php if($row['staff_type'] == 'driver'){ ?>

<span
style="
background:#dbeafe;
color:#1d4ed8;
padding:6px 12px;
border-radius:20px;
font-size:12px;
font-weight:600;
">

Driver

</span>

<?php } else { ?>

<span
style="
background:#dcfce7;
color:#166534;
padding:6px 12px;
border-radius:20px;
font-size:12px;
font-weight:600;
">

Helper

</span>

<?php } ?>

</td>

<td>

<?php
echo htmlspecialchars(
$row['phone'] ?? '-'
);
?>

</td>

<td>

<?php

echo !empty($row['license_number'])

? htmlspecialchars($row['license_number'])

: '-';

?>

</td>

<td>

<span
class="status <?php echo ($row['status'] == 'active') ? 'success' : 'danger'; ?>">

<?php
echo ucfirst(
$row['status']
);
?>

</span>

</td>

<td>

<?php

echo date(

'd M Y',

strtotime(
$row['created_at']
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
href="edit.php?id=<?php echo $row['staff_id']; ?>"
class="btn btn-primary">

<i class="fa-solid fa-pen"></i>

Edit

</a>

<a
href="delete.php?id=<?php echo $row['staff_id']; ?>"
class="btn"
style="
background:#ef4444;
color:white;
"
onclick="return confirm('Are you sure you want to delete this staff member?');">

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
style="
text-align:center;
padding:40px;
">

<div
style="
display:flex;
flex-direction:column;
align-items:center;
gap:10px;
">

<i
class="fa-solid fa-user-tie"
style="
font-size:40px;
color:#94a3b8;
"> </i>

<p
style="
margin:0;
font-weight:500;
">

No transport staff found.

</p>

<a
href="add.php"
class="btn btn-primary">

<i class="fa-solid fa-user-plus"></i>

Add First Staff Member

</a>

</div>

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
