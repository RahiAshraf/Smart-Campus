<?php

require_once '../../config/auth.php';
require_once '../../config/db.php';
require_once '../../shared/helpers/portal.php';

portal_require_role(['driver']);

$user_id = (int)$_SESSION['user']['id'];

$query = mysqli_query(

$conn,

"SELECT

ts.*,

u.email,
u.profile_photo AS user_photo,

b.bus_id,
b.bus_name,
b.bus_number,

r.route_name

FROM transport_staff ts

LEFT JOIN users u
ON u.id = ts.user_id

LEFT JOIN transport_buses b
ON b.driver_id = ts.staff_id

LEFT JOIN transport_routes r
ON r.bus_id=b.bus_id

WHERE ts.user_id = $user_id

LIMIT 1"

);

$driver = mysqli_fetch_assoc($query);

if(!$driver){

die('Driver profile not found.');

}

$photo = !empty($driver['user_photo'])
? '../../uploads/profiles/' . $driver['user_photo']
: '';
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
Driver Profile
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

<link rel="stylesheet" href="../assets/css/material-theme.css">

<style>

.profile-grid{

display:grid;
grid-template-columns:
320px 1fr;
gap:20px;

}

.profile-card{

background:var(--card);
padding:24px;
border-radius:16px;
box-shadow:var(--shadow);

}

.profile-avatar{

width:130px;
height:130px;
border-radius:50%;
object-fit:cover;
display:block;
margin:auto;

}

.profile-placeholder{

width:130px;
height:130px;
border-radius:50%;

display:flex;
align-items:center;
justify-content:center;

font-size:48px;
font-weight:700;

background:#2563eb;
color:white;

margin:auto;

}

.profile-name{

text-align:center;
margin-top:15px;

}

.info-grid{

display:grid;
grid-template-columns:
repeat(auto-fit,minmax(250px,1fr));
gap:16px;

}

.info-box{

background:#f8fafc;
padding:16px;
border-radius:12px;

}

@media(max-width:768px){

.profile-grid{

grid-template-columns:1fr;

}

}

</style>

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
My Profile
</h1>

<p>
Driver account information.
</p>

</div>

</div>

<div class="profile-grid">

<div class="profile-card">

<?php if(!empty($driver['user_photo'])){ ?>

<img
src="<?php echo $photo; ?>"
class="profile-avatar">

<?php } else { ?>

<div class="profile-placeholder">

<?php echo strtoupper(substr($driver['full_name'],0,1)); ?>

</div>

<?php } ?>

<div class="profile-name">

<h2>

<?php
echo htmlspecialchars(
$driver['full_name']
);
?>

</h2>

<p>
Driver
</p>

</div>

</div>

<div class="profile-card">

<div class="info-grid">

<div class="info-box">

<strong>Email</strong>

<p>

<?php
echo htmlspecialchars(
$driver['email']
);
?>

</p>

</div>

<div class="info-box">

<strong>Phone</strong>

<p>

<?php
echo htmlspecialchars(
$driver['phone']
);
?>

</p>

</div>

<div class="info-box">

<strong>License Number</strong>

<p>

<?php
echo htmlspecialchars(
$driver['license_number']
);
?>

</p>

</div>

<div class="info-box">

<strong>Emergency Contact</strong>

<p>

<?php
echo htmlspecialchars(
$driver['emergency_contact']
);
?>

</p>

</div>

<div class="info-box">

<strong>Assigned Bus</strong>

<p>

<?php

echo !empty($driver['bus_name'])

? htmlspecialchars($driver['bus_name'])

: 'Not Assigned';

?>

</p>

</div>

<div class="info-box">

<strong>Bus Number</strong>

<p>

<?php

echo !empty($driver['bus_number'])

? htmlspecialchars($driver['bus_number'])

: '-';

?>

</p>

</div>

<div class="info-box">

<strong>Assigned Route</strong>

<p>

<?php

echo !empty($driver['route_name'])

? htmlspecialchars($driver['route_name'])

: 'Not Assigned';

?>

</p>

</div>

<div class="info-box">

<strong>Address</strong>

<p>

<?php

echo !empty($driver['address'])

? nl2br(htmlspecialchars($driver['address']))

: '-';

?>

</p>

</div>

</div>

</div>

</div>

</div>

</div>

</div>

<script src="../../assets/js/common.js"></script>

</body>
</html>