<?php

include('../../config/auth.php');
include('../../config/db.php');

if(
!isset($_GET['id'])
||
!is_numeric($_GET['id'])
){

header('Location:index.php');
exit;

}

$bus_id = (int)$_GET['id'];

$bus_query = mysqli_query(

$conn,

"SELECT *

FROM transport_buses

WHERE bus_id = $bus_id

LIMIT 1"

);

if(mysqli_num_rows($bus_query) == 0){

header('Location:index.php');
exit;

}

$bus = mysqli_fetch_assoc(
$bus_query
);

$error = '';

if($_SERVER['REQUEST_METHOD'] == 'POST'){

$bus_number = mysqli_real_escape_string(
$conn,
trim($_POST['bus_number'])
);

$bus_name = mysqli_real_escape_string(
$conn,
trim($_POST['bus_name'])
);

$capacity = (int)$_POST['capacity'];

$driver_id = !empty($_POST['driver_id'])
? (int)$_POST['driver_id']
: 'NULL';

$helper_id = !empty($_POST['helper_id'])
? (int)$_POST['helper_id']
: 'NULL';

$start_time = mysqli_real_escape_string(
$conn,
$_POST['start_time']
);

$end_time = mysqli_real_escape_string(
$conn,
$_POST['end_time']
);

$status = mysqli_real_escape_string(
$conn,
$_POST['status']
);

if(
empty($bus_number)
||
empty($bus_name)
){

$error =
"Bus Number and Bus Name are required.";

}
else{

$duplicate = mysqli_query(

$conn,

"SELECT bus_id

FROM transport_buses

WHERE

bus_number='$bus_number'

AND

bus_id != $bus_id

LIMIT 1"

);

if(mysqli_num_rows($duplicate) > 0){

$error =
"Bus Number already exists.";

}
else{

mysqli_query(

$conn,

"UPDATE transport_buses

SET

bus_number='$bus_number',

bus_name='$bus_name',

capacity=$capacity,

driver_id=$driver_id,

helper_id=$helper_id,

start_time='$start_time',

end_time='$end_time',

status='$status'

WHERE bus_id=$bus_id"

);

header(
'Location:index.php?success=updated'
);

exit;

}

}

}
$drivers = mysqli_query(

$conn,

"SELECT
ts.staff_id,
ts.full_name

FROM transport_staff ts

WHERE
ts.staff_type='driver'

AND
ts.status='active'

AND
(
ts.staff_id = ".$bus['driver_id']."
OR
ts.staff_id NOT IN
(
SELECT driver_id
FROM transport_buses
WHERE driver_id IS NOT NULL
)
)

ORDER BY ts.full_name"

);

$helpers = mysqli_query(

$conn,

"SELECT *

FROM transport_staff

WHERE

staff_type='helper'

AND

status='active'

ORDER BY full_name"

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
Edit Bus | MindMerge SmartCampus
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
Edit Bus
</h1>

<p>
Update bus details and staff assignments.
</p>

</div>

<a
href="index.php"
class="btn">

<i class="fa-solid fa-arrow-left"></i>

Back

</a>

</div>

<?php if(!empty($error)){ ?>

<div
style="
background:#fee2e2;
color:#991b1b;
padding:14px 18px;
border-radius:14px;
margin-bottom:20px;
font-weight:500;
">

<?php echo $error; ?>

</div>

<?php } ?>

<div class="dashboard-section">

<form method="POST">

<div class="form-grid">

<div class="form-group">

<label>
Bus Number
</label>

<input

type="text"

name="bus_number"

class="form-input"

value="<?php echo htmlspecialchars($bus['bus_number']); ?>"

required>

</div>

<div class="form-group">

<label>
Bus Name
</label>

<input

type="text"

name="bus_name"

class="form-input"

value="<?php echo htmlspecialchars($bus['bus_name']); ?>"

required>

</div>

<div class="form-group">

<label>
Capacity
</label>

<input

type="number"

name="capacity"

class="form-input"

value="<?php echo (int)$bus['capacity']; ?>"

min="1"

required>

</div>

<div class="form-group">

<label>
Driver
</label>

<select
name="driver_id"
class="form-input">

<option value="">
Select Driver
</option>

<?php
while($driver=mysqli_fetch_assoc($drivers)){
?>

<option
value="<?php echo $driver['staff_id']; ?>"

<?php
echo ($bus['driver_id']==$driver['staff_id'])
? 'selected'
: '';
?>>

<?php echo htmlspecialchars($driver['full_name']); ?>

</option>

<?php } ?>

</select>

</div>

<div class="form-group">

<label>
Helper
</label>

<select
name="helper_id"
class="form-input">

<option value="">
Select Helper
</option>

<?php
while($helper=mysqli_fetch_assoc($helpers)){
?>

<option
value="<?php echo $helper['staff_id']; ?>"

<?php
echo ($bus['helper_id']==$helper['staff_id'])
? 'selected'
: '';
?>>

<?php echo htmlspecialchars($helper['full_name']); ?>

</option>

<?php } ?>

</select>

</div>

<div class="form-group">

<label>
Start Time
</label>

<input

type="time"

name="start_time"

class="form-input"

value="<?php echo $bus['start_time']; ?>">

</div>

<div class="form-group">

<label>
End Time
</label>

<input

type="time"

name="end_time"

class="form-input"

value="<?php echo $bus['end_time']; ?>">

</div>

<div class="form-group">

<label>
Status
</label>

<select
name="status"
class="form-input">

<option
value="active"
<?php echo ($bus['status']=='active') ? 'selected' : ''; ?>>

Active

</option>

<option
value="inactive"
<?php echo ($bus['status']=='inactive') ? 'selected' : ''; ?>>

Inactive

</option>

</select>

</div>

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

<i class="fa-solid fa-floppy-disk"></i>

Update Bus

</button>

<a
href="index.php"
class="btn">

Cancel

</a>

</div>

</form>

</div>

</div>

</div>

</div>

<script src="../../assets/js/common.js"></script>

</body>

</html>
