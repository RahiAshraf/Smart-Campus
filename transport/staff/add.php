<?php

include('../../config/auth.php');
include('../../config/db.php');

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
    $emergency_contact = mysqli_real_escape_string(
    $conn,
    trim($_POST['emergency_contact'] ?? '')
    );

    $address = mysqli_real_escape_string(
    $conn,
    trim($_POST['address'] ?? '')
    );
    $email = mysqli_real_escape_string(

    $conn,

    trim(
    $_POST['email'] ?? ''
    )

    );

    

    $license_number = mysqli_real_escape_string(
        $conn,
        trim($_POST['license_number'])
    );

    $status = mysqli_real_escape_string(
        $conn,
        trim($_POST['status'])
    );

    if(empty($staff_type) || empty($full_name)){

        $error = "Staff Type and Full Name are required.";

    }
    elseif(
        $staff_type == 'driver'
        &&
        empty($license_number)
    ){

        $error = "License Number is required for drivers.";

    }
   elseif(
    $staff_type == 'driver'
    &&
    empty($email)
    )
    {

    $error =
    "Email is required for driver login.";

    }
    else{

        $user_id = "NULL";

                
        if(
$staff_type == 'driver'
){

$emailCheck = mysqli_query(

$conn,

"SELECT id
FROM users
WHERE email='$email'
LIMIT 1"

);

if(mysqli_num_rows($emailCheck) > 0){

$error = "Email already exists.";

}
else{

$tempPassword = password_hash(

'driver123',

PASSWORD_DEFAULT

);

mysqli_query(

$conn,

"INSERT INTO users
(
full_name,
email,
phone,
password,
role
)

VALUES
(
'$full_name',
'$email',
'$phone',
'$tempPassword',
'driver'
)"

);

$user_id = mysqli_insert_id($conn);

}

}

    if(empty($error)){



        mysqli_query(

        $conn,

        "INSERT INTO transport_staff
        (
        user_id,
        staff_type,
        full_name,
        phone,
        license_number,
        emergency_contact,
        address,
        status
        )

        VALUES
        (
        " . ($user_id == "NULL" ? "NULL" : "'$user_id'") . ",
        '$staff_type',
        '$full_name',
        '$phone',
        '$license_number',
        '$emergency_contact',
        '$address',
        '$status'
        )"

        );
if($staff_type == 'driver'){

header(
"Location:index.php?success=driver_created"
);

}
else{

header(
"Location:index.php?success=added"
);

}

exit;

}

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
Add Staff | MindMerge SmartCampus
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
Add Transport Staff
</h1>

<p>
Create a new Driver or Helper.
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

<select
name="staff_type"
id="staff_type"
class="form-input"
required>

<option value="">
Select Type
</option>

<option value="driver">
Driver
</option>

<option value="helper">
Helper
</option>

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

required>

</div>

<div class="form-group">

<label>
Phone Number
</label>

<input

type="text"

name="phone"

class="form-input">

</div>
<div class="form-group">
<label>Emergency Contact</label>
<input
type="text"
name="emergency_contact"
class="form-input">
</div>
<div
id="driverEmailWrapper"
style="display:none;">

<label>
Driver Email
</label>

<input
type="email"
name="email"
id="driver_email"
class="form-input">


</div>
<div class="form-group">

<label>
License Number
</label>

<input

type="text"

name="license_number"

id="license_number"

class="form-input">

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
<label>Address</label>
<textarea
name="address"
class="form-input"
rows="3"></textarea>
</div>

<div class="form-group">

<label>
Status
</label>

<select
name="status"
class="form-input">

<option value="active">
Active
</option>

<option value="inactive">
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

Save Staff

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

document
.getElementById('staff_type')
.addEventListener(
'change',
toggleFields
);

function toggleFields(){

let type =
document.getElementById(
'staff_type'
).value;

let licenseField =
document.getElementById(
'license_number'
);

let emailBox =
document.getElementById(
'driverEmailWrapper'
);

if(type === 'driver'){
let emailField =
document.getElementById(
'driver_email'
);
emailField.required = true;
licenseField.required = true;

emailBox.style.display =
'block';

}
else{
emailField.required = false;
emailField.value = '';
licenseField.required = false;

emailBox.style.display =
'none';

}

}

toggleFields();
</script>

<script src="../../assets/js/common.js"></script>

</body>

</html>
