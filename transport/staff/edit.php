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

$staff_id = (int)$_GET['id'];

$staff_query = mysqli_query(

$conn,

"SELECT

ts.*,

u.email

FROM transport_staff ts

LEFT JOIN users u
ON ts.user_id=u.id

WHERE ts.staff_id='$staff_id'

LIMIT 1"

);

if(mysqli_num_rows($staff_query) == 0){

header('Location:index.php');
exit;

}

$staff = mysqli_fetch_assoc(
$staff_query
);

$error = '';

if($_SERVER['REQUEST_METHOD'] == 'POST'){

$staff_type = mysqli_real_escape_string(
$conn,
trim($_POST['staff_type'])
);

$full_name = mysqli_real_escape_string(
$conn,
trim($_POST['full_name'])
);

$phone = mysqli_real_escape_string(
$conn,
trim($_POST['phone'])
);
$email = mysqli_real_escape_string(
$conn,
trim($_POST['email'] ?? '')
);

$emergency_contact =
mysqli_real_escape_string(
$conn,
trim($_POST['emergency_contact'] ?? '')
);

$address =
mysqli_real_escape_string(
$conn,
trim($_POST['address'] ?? '')
);
$emergency_contact =
mysqli_real_escape_string(
$conn,
trim($_POST['emergency_contact'])
);

$address =
mysqli_real_escape_string(
$conn,
trim($_POST['address'])
);

$license_number = mysqli_real_escape_string(
$conn,
trim($_POST['license_number'])
);

$status = mysqli_real_escape_string(
$conn,
trim($_POST['status'])
);

if(
empty($staff_type)
||
empty($full_name)
){

$error =
"Staff Type and Full Name are required.";

}
elseif(
$staff_type == 'driver'
&&
empty($license_number)
){

$error =
"License Number is required for drivers.";

}elseif(
$staff_type == 'driver'
&&
empty($email)
){

$error =
"Driver email is required.";

}
else{
mysqli_query(

$conn,

"UPDATE transport_staff
SET

staff_type='$staff_type',

full_name='$full_name',

phone='$phone',

license_number='$license_number',

status='$status',

emergency_contact='$emergency_contact',

address='$address'

WHERE staff_id='$staff_id'"

);
if(!empty($staff['user_id'])){

mysqli_query(

$conn,

"UPDATE users
SET

full_name='$full_name',

phone='$phone',

email='$email'

WHERE id='".$staff['user_id']."'"

);

}

header(
'Location:index.php?success=updated'
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
Edit Staff | MindMerge SmartCampus
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
Edit Transport Staff
</h1>

<p>
Update driver or helper information.
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
Staff Type
</label>

<input
type="text"
class="form-input"
value="<?php echo ucfirst($staff['staff_type']); ?>"
readonly>

<input
type="hidden"
name="staff_type"
value="<?php echo $staff['staff_type']; ?>">


</select>

</div>

<div class="form-group">

<label>
Full Name
</label>

<input

type="text"

name="full_name"

class="form-input"

value="<?php echo htmlspecialchars($staff['full_name']); ?>"

required>

</div>

<div class="form-group">

<label>
Phone Number
</label>

<input

type="text"

name="phone"

class="form-input"

value="<?php echo htmlspecialchars($staff['phone']); ?>">

</div>
<div
id="driverEmailWrapper">

<label>
Driver Email
</label>

<input
type="email"
name="email"
id="driver_email"
class="form-input"
value="<?php echo htmlspecialchars($staff['email'] ?? ''); ?>">

<small
style="
display:block;
margin-top:6px;
color:#64748b;
">

Used for driver login.

</small>

</div>
<div class="form-group">

<label>
Emergency Contact
</label>

<input
type="text"
name="emergency_contact"
class="form-input"

value="<?php echo htmlspecialchars($staff['emergency_contact'] ?? ''); ?>">

</div>
<div class="form-group">

<label>
License Number
</label>

<input

type="text"

name="license_number"

id="license_number"

class="form-input"

value="<?php echo htmlspecialchars($staff['license_number']); ?>">

<small
style="
display:block;
margin-top:6px;
color:#64748b;
">

Required for Drivers

</small>

</div>
<div class="form-group">

<label>
Address
</label>

<textarea
name="address"
class="form-input"
rows="3"><?php

echo htmlspecialchars(
$staff['address'] ?? ''
);

?></textarea>

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
<?php echo ($staff['status']=='active') ? 'selected' : ''; ?>>

Active

</option>

<option
value="inactive"
<?php echo ($staff['status']=='inactive') ? 'selected' : ''; ?>>

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

Update Staff

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

<script>
function toggleFields(){

let type =
document.getElementById(
'staff_type'
).value;

let license =
document.getElementById(
'license_number'
);

let emailBox =
document.getElementById(
'driverEmailWrapper'
);

let emailField =
document.getElementById(
'driver_email'
);

if(type === 'driver'){

license.required = true;

emailField.required = true;

emailBox.style.display =
'block';

}
else{

license.required = false;

emailField.required = false;

emailBox.style.display =
'none';

}

}

document
.getElementById('staff_type')
.addEventListener(
'change',
toggleFields
);

toggleFields();
</script>

<script src="../../assets/js/common.js"></script>

</body>

</html>
