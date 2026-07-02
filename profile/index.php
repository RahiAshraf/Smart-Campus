<?php

require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../shared/services/ProfileService.php';
require_once __DIR__ . '/../shared/services/TeacherScopeService.php';

$user = $_SESSION['user'];

$user_id =
(int)$user['id'];

$role =
strtolower(
$user['role'] ?? ''
);

$edit_mode =
isset($_GET['edit'])
&&
canEdit('profile');

$error = '';
$success = '';

$profileService =
new ProfileService($conn);

$data =
$profileService->getProfileData(
$user_id,
$role
);

$photoUrl =
$profileService->getPhotoUrl(
$data['user']
);

$teacherScope =
$role === 'teacher'
? new TeacherScopeService(
$conn,
$user_id
)
: null;

/* =========================
   PROFILE UPDATE
========================= */

if(
$edit_mode
&&
isset($_POST['save_profile'])
){

$full_name =
mysqli_real_escape_string(
$conn,
trim($_POST['full_name'] ?? '')
);

$phone =
mysqli_real_escape_string(
$conn,
trim($_POST['phone'] ?? '')
);

$profile_photo =
$user['profile_photo'];

/* =========================
   PHOTO UPLOAD
========================= */

if(
isset($_FILES['profile_photo'])
&&
!empty($_FILES['profile_photo']['name'])
){

$allowed =
[
'jpg',
'jpeg',
'png',
'webp'
];

$ext =
strtolower(

pathinfo(

$_FILES['profile_photo']['name'],

PATHINFO_EXTENSION

)

);

if(

$_FILES['profile_photo']['size']
<=
2097152

&&

in_array(
$ext,
$allowed,
true
)

){

$file_name =

uniqid(
'profile_',
true
)

.

'.'

.

$ext;

$upload_dir =
__DIR__
.
'/../assets/uploads/profile/';

if(
!is_dir($upload_dir)
){

mkdir(
$upload_dir,
0777,
true
);

}

$upload_path =
$upload_dir
.
$file_name;

if(

move_uploaded_file(

$_FILES['profile_photo']['tmp_name'],

$upload_path

)

){

$profile_photo =
$file_name;

}
else{

$error =
'Failed to upload profile image. Please try again.';

}

}
else{

if(
$_FILES['profile_photo']['size']
>
2097152
){

$error =
'Profile image size must be less than 2 MB.';

}
else{

$error =
'Only JPG, JPEG, PNG and WEBP images are allowed.';

}

}

}

if(!empty($error)){

header(

'Location:index.php?edit=1&error='

.

urlencode($error)

);

exit;

}

/* =========================
   UPDATE USERS TABLE
========================= */

mysqli_query(

$conn,

"UPDATE users

SET

full_name='$full_name',
phone='$phone',
profile_photo='$profile_photo'

WHERE id='$user_id'"

);

/* =========================
   STUDENT
========================= */

if(
$role === 'student'
&&
!empty($data['role_data'])
){

$address =
mysqli_real_escape_string(

$conn,

$_POST['address']
?? ''

);

mysqli_query(

$conn,

"UPDATE students

SET

address='$address'

WHERE user_id='$user_id'"

);

}

/* =========================
   TEACHER
========================= */

if(
$role === 'teacher'
&&
!empty($data['role_data'])
){

$qualification =
mysqli_real_escape_string(

$conn,

$_POST['qualification']
?? ''

);

mysqli_query(

$conn,

"UPDATE teachers

SET

qualification='$qualification'

WHERE user_id='$user_id'"

);

}

/* =========================
   PARENT
========================= */

if(
$role === 'parent'
&&
!empty($data['role_data'])
){

$relationship_name =
mysqli_real_escape_string(

$conn,

$_POST['relationship_name']
?? ''

);

mysqli_query(

$conn,

"UPDATE parents

SET

relationship_name='$relationship_name'

WHERE user_id='$user_id'"

);

}

/* =========================
   DRIVER
========================= */

if(
$role === 'driver'
&&
!empty($data['role_data'])
){

$license_number =
mysqli_real_escape_string(

$conn,

$_POST['license_number']
?? ''

);

$emergency_contact =
mysqli_real_escape_string(

$conn,

$_POST['emergency_contact']
?? ''

);

$address =
mysqli_real_escape_string(

$conn,

$_POST['address']
?? ''

);

mysqli_query(

$conn,

"UPDATE transport_staff

SET

license_number='$license_number',
emergency_contact='$emergency_contact',
address='$address'

WHERE

user_id='$user_id'

AND

staff_type='driver'"

);

}

$updated =
mysqli_query(

$conn,

"SELECT *

FROM users

WHERE id='$user_id'

LIMIT 1"

);

$_SESSION['user'] =
mysqli_fetch_assoc(
$updated
);

header(
'Location:index.php?updated=1'
);

exit;

}

/* =========================
   REFRESH DATA
========================= */

$user =
$_SESSION['user'];

$data =
$profileService->getProfileData(
$user_id,
$role
);

$photoUrl =
$profileService->getPhotoUrl(
$data['user']
);

?>
<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<script>
(() => {
    if (localStorage.getItem("mindmerge-theme") === "dark") {
        document.documentElement.classList.add("dark-mode");
    }
})();
</script>

<meta
name="viewport"
content="width=device-width, initial-scale=1.0">

<title>
Profile | MindMerge SmartCampus
</title>

<link rel="stylesheet" href="../../assets/css/material-theme.css">

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
href="../assets/css/portals.css">


<link
rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="../../assets/css/material-theme.css">

<style>

/* =========================
   PROFILE LAYOUT
========================= */

.user-profile-layout{
display:grid;
grid-template-columns:340px 1fr;
gap:24px;
align-items:start;
}

/* =========================
   LEFT CARD
========================= */

.user-profile-card{
background:var(--card);
padding:30px;
box-shadow:var(--shadow);
text-align:center;
position:sticky;
top:90px;
}

.profile-avatar{
width:150px;
height:150px;
border-radius:50%;
object-fit:cover;
display:block;
margin:auto;
border:5px solid rgba(59,130,246,.15);
}

.profile-avatar-fallback{
width:150px;
height:150px;
border-radius:50%;
display:flex;
align-items:center;
justify-content:center;
font-size:58px;
font-weight:700;
color:#fff;
background:var(--primary);
margin:auto;
}

.profile-name{
margin-top:20px;
margin-bottom:8px;
font-size:28px;
font-weight:700;
}

.profile-role{
display:inline-flex;
align-items:center;
gap:8px;
padding:10px 18px;
border-radius:999px;
background:rgba(59,130,246,.12);
color:var(--primary);
font-weight:600;
font-size:14px;
}

.profile-meta{
margin-top:30px;
display:flex;
flex-direction:column;
gap:14px;
text-align:left;
}

.profile-meta-item{
display:flex;
align-items:center;
gap:12px;
padding:14px;
border-radius:14px;
background:rgba(148,163,184,.06);
}

.profile-meta-item i{
width:20px;
text-align:center;
color:var(--primary);
}

/* =========================
   RIGHT AREA
========================= */

.user-profile-content{
display:flex;
flex-direction:column;
gap:24px;
}

.profile-section{
background:var(--card);
padding:28px;
box-shadow:var(--shadow);
}

.profile-section-header{
display:flex;
justify-content:space-between;
align-items:center;
margin-bottom:20px;
gap:15px;
flex-wrap:wrap;
}

.profile-section-header h2{
margin:0;
}

/* =========================
   STATS
========================= */

.profile-stats{
display:grid;
grid-template-columns:
repeat(
auto-fit,
minmax(220px,1fr)
);
gap:16px;
}

.profile-stat-card{
background:var(--card);
padding:22px;
box-shadow:var(--shadow);
}

.profile-stat-card h4{
font-size:13px;
color:var(--muted);
margin-bottom:8px;
}

.profile-stat-card p{
font-size:24px;
font-weight:700;
margin:0;
}

/* =========================
   FORM
========================= */

.profile-form-card{
background:var(--card);
border-radius:24px;
padding:30px;
box-shadow:var(--shadow);
}

.custom-upload-box{
display:flex;
align-items:center;
gap:15px;
padding:18px;
border-radius:18px;
border:2px dashed var(--primary);
cursor:pointer;
transition:.3s;
}

.custom-upload-box:hover{
background:rgba(59,130,246,.08);
}

.upload-icon{
width:55px;
height:55px;
border-radius:14px;
display:flex;
align-items:center;
justify-content:center;
background:rgba(59,130,246,.12);
font-size:22px;
color:var(--primary);
}

.upload-content h4{
margin:0;
font-size:15px;
}

.upload-content p{
margin:4px 0 0;
font-size:13px;
color:var(--muted);
}

.disabled-field{
background:rgba(148,163,184,.08) !important;
cursor:not-allowed;
opacity:.9;
}

/* =========================
   INFO BLOCKS
========================= */

.info-grid{
display:grid;
grid-template-columns:
repeat(
auto-fit,
minmax(250px,1fr)
);
gap:16px;
}

.info-card{
background:rgba(148,163,184,.06);
border-radius:16px;
padding:18px;
}

.info-card h4{
margin-bottom:8px;
font-size:14px;
color:var(--muted);
}

.info-card p{
margin:0;
font-weight:600;
word-break:break-word;
}

/* =========================
   SUCCESS
========================= */

.success-alert{
background:#dcfce7;
color:#166534;
padding:16px 18px;
border-radius:16px;
margin-bottom:20px;
font-weight:600;
display:flex;
align-items:center;
gap:10px;
}

.alert-danger{
background:#fee2e2;
color:#991b1b;
padding:16px 18px;
border-radius:16px;
margin-bottom:20px;
font-weight:600;
display:flex;
align-items:center;
gap:10px;
}

/* =========================
   DARK MODE
========================= */

body.dark-mode .profile-role{
background:#172554;
color:#93c5fd;
}

body.dark-mode .profile-meta-item{
background:#1e293b;
}

body.dark-mode .info-card{
background:#1e293b;
}

body.dark-mode .custom-upload-box:hover{
background:#172554;
}

body.dark-mode .disabled-field{
background:#1e293b !important;
}

/* =========================
   RESPONSIVE
========================= */

@media(max-width:1100px){

.user-profile-layout{
grid-template-columns:1fr;
}

.user-profile-card{
position:static;
}

}

@media(max-width:768px){

.profile-avatar,
.profile-avatar-fallback{
width:120px;
height:120px;
font-size:46px;
}

.profile-name{
font-size:24px;
}

.profile-form-card,
.profile-section,
.user-profile-card{
padding:22px;
}

}

</style>

</head>

<body>

<div class="app-layout">

<?php include __DIR__ . '/../partials/sidebar.php'; ?>

<div class="main-content">

<?php include __DIR__ . '/../partials/topbar.php'; ?>

<div class="page-content">
    <?php if(isset($_GET['updated'])){ ?>

<div class="success-alert">

<i class="fa-solid fa-circle-check"></i>

Profile updated successfully.

</div>

<?php } ?>

<?php if(isset($_GET['error'])){ ?>

<div class="alert-danger">

<i class="fa-solid fa-circle-exclamation"></i>

<?php
echo htmlspecialchars(
$_GET['error']
);
?>

</div>

<?php } ?>

<!-- =========================
     PAGE HEADER
========================= -->

<div class="page-header">

<div>

<h1>

<?php

echo $edit_mode
? 'Edit Profile'
: 'My Profile';

?>

</h1>

<p>

Manage your account information and personal details.

</p>

</div>

<div
style="
display:flex;
gap:10px;
flex-wrap:wrap;
">

<?php if(!$edit_mode){ ?>

<?php if(canEdit('profile')){ ?>

<a
href="index.php?edit=1"
class="btn btn-primary">

<i class="fa-solid fa-pen"></i>

Edit Profile

</a>

<?php } ?>

<a
href="print.php"
target="_blank"
class="btn">

<i class="fa-solid fa-print"></i>

Print Profile

</a>

<?php }else{ ?>

<a
href="index.php"
class="btn">

<i class="fa-solid fa-arrow-left"></i>

Back

</a>

<?php } ?>

</div>

</div>

<!-- =========================
     PROFILE LAYOUT
========================= -->

<div class="user-profile-layout">

<!-- =========================
     LEFT PROFILE CARD
========================= -->

<div class="user-profile-card">

<?php if($photoUrl){ ?>

<img
src="<?php echo htmlspecialchars($photoUrl); ?>"
alt="Profile"
class="profile-avatar">

<?php }else{ ?>

<div class="profile-avatar-fallback">

<?php

echo strtoupper(

substr(
$user['full_name'],
0,
1
)

);

?>

</div>

<?php } ?>

<h2 class="profile-name">

<?php

echo htmlspecialchars(
$user['full_name']
);

?>

</h2>

<div class="profile-role">

<i class="fa-solid fa-user"></i>

<?php

echo ucfirst($role);

?>

</div>

<div class="profile-meta">

<div class="profile-meta-item">

<i class="fa-solid fa-envelope"></i>

<span>

<?php

echo htmlspecialchars(
$user['email']
);

?>

</span>

</div>

<div class="profile-meta-item">

<i class="fa-solid fa-phone"></i>

<span>

<?php

echo !empty($user['phone'])
? htmlspecialchars($user['phone'])
: 'Not Available';

?>

</span>

</div>

<div class="profile-meta-item">

<i class="fa-solid fa-user-shield"></i>

<span>

<?php

echo ucfirst($role);

?>

Account

</span>

</div>

<?php if($role === 'admin'){ ?>

<div class="profile-meta-item">

<i class="fa-solid fa-id-card"></i>

<span>

<?php

echo htmlspecialchars(
$user['admin_id']
?? '-'
);

?>

</span>

</div>

<?php } ?>

<?php if(
$role === 'student'
&&
!empty($data['role_data'])
){ ?>

<div class="profile-meta-item">

<i class="fa-solid fa-graduation-cap"></i>

<span>

<?php

echo htmlspecialchars(
$data['role_data']['student_id']
?? '-'
);

?>

</span>

</div>

<?php } ?>

<?php if(
$role === 'teacher'
&&
!empty($data['role_data'])
){ ?>

<div class="profile-meta-item">

<i class="fa-solid fa-chalkboard-user"></i>

<span>

<?php

echo htmlspecialchars(
$data['role_data']['teacher_id']
?? '-'
);

?>

</span>

</div>

<?php } ?>

<?php if(
$role === 'driver'
&&
!empty($data['role_data'])
){ ?>

<div class="profile-meta-item">

<i class="fa-solid fa-id-card"></i>

<span>

<?php

echo htmlspecialchars(
$data['role_data']['license_number']
?? '-'
);

?>

</span>

</div>

<?php } ?>

</div>

</div>

<!-- =========================
     RIGHT CONTENT
========================= -->

<div class="user-profile-content">

<!-- =========================
     QUICK STATS
========================= -->

<div class="profile-stats">

<div class="profile-stat-card">

<h4>Account Role</h4>

<p>

<?php

echo ucfirst($role);

?>

</p>

</div>

<div class="profile-stat-card">

<h4>Profile Status</h4>

<p>

Active

</p>

</div>

<div class="profile-stat-card">

<h4>Email Verified</h4>

<p>

Yes

</p>

</div>

<div class="profile-stat-card">

<h4>Portal Access</h4>

<p>

Enabled

</p>

</div>

</div>

<?php if($edit_mode){ ?>

<!-- =========================
     EDIT FORM STARTS
========================= -->

<form
method="POST"
enctype="multipart/form-data">

<div class="profile-form-card">

<div class="profile-section-header">

<h2>

<i class="fa-solid fa-user-pen"></i>

Edit Profile

</h2>

</div>

<div class="form-grid">
    <!-- =========================
     COMMON FIELDS
========================= -->

<div class="form-group">

<label class="form-label">

Full Name

</label>

<input
type="text"
name="full_name"
class="form-input"
value="<?php echo htmlspecialchars($user['full_name']); ?>"
required>

</div>

<div class="form-group">

<label class="form-label">

Email

</label>

<input
type="email"
class="form-input disabled-field"
value="<?php echo htmlspecialchars($user['email']); ?>"
disabled>

</div>

<div class="form-group">

<label class="form-label">

Phone Number

</label>

<input
type="text"
name="phone"
class="form-input"
value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>"
required>

</div>

<div class="form-group">

<label class="form-label">

Role

</label>

<input
type="text"
class="form-input disabled-field"
value="<?php echo ucfirst($role); ?>"
disabled>

</div>

<!-- =========================
     PROFILE PHOTO
========================= -->

<div class="form-group">

<label class="form-label">

Profile Photo

</label>

<label class="custom-upload-box">

<div class="upload-icon">

<i class="fa-solid fa-cloud-arrow-up"></i>

</div>

<div class="upload-content">

<h4 id="uploadTitle">

Choose Profile Image

</h4>

<p id="uploadSubtext">

PNG, JPG, JPEG, WEBP (Max 2 MB)

</p>

</div>

<input
type="file"
name="profile_photo"
id="profilePhotoInput"
accept=".png,.jpg,.jpeg,.webp"
style="display:none;">

</label>

</div>

<!-- =========================
     ADMIN
========================= -->

<?php if($role === 'admin'){ ?>

<div class="form-group">

<label class="form-label">

Admin ID

</label>

<input
type="text"
class="form-input disabled-field"
value="<?php echo htmlspecialchars($user['admin_id'] ?? '-'); ?>"
disabled>

</div>

<?php } ?>

<!-- =========================
     STUDENT
========================= -->

<?php if(
$role === 'student'
&&
!empty($data['role_data'])
){ ?>

<div class="form-group">

<label class="form-label">

Student ID

</label>

<input
type="text"
class="form-input disabled-field"
value="<?php echo htmlspecialchars($data['role_data']['student_id'] ?? ''); ?>"
disabled>

</div>

<div class="form-group">

<label class="form-label">

Class

</label>

<input
type="text"
class="form-input disabled-field"
value="<?php echo htmlspecialchars($data['role_data']['class_name'] ?? ''); ?>"
disabled>

</div>

<div class="form-group">

<label class="form-label">

Section

</label>

<input
type="text"
class="form-input disabled-field"
value="<?php echo htmlspecialchars($data['role_data']['section_name'] ?? ''); ?>"
disabled>

</div>

<div class="form-group">

<label class="form-label">

Address

</label>

<textarea
name="address"
class="form-textarea"
rows="4"><?php echo htmlspecialchars($data['role_data']['address'] ?? ''); ?></textarea>

</div>

<?php } ?>

<!-- =========================
     TEACHER
========================= -->

<?php if(
$role === 'teacher'
&&
!empty($data['role_data'])
){ ?>

<div class="form-group">

<label class="form-label">

Teacher ID

</label>

<input
type="text"
class="form-input disabled-field"
value="<?php echo htmlspecialchars($data['role_data']['teacher_id'] ?? ''); ?>"
disabled>

</div>

<div class="form-group">

<label class="form-label">

Qualification

</label>

<input
type="text"
name="qualification"
class="form-input"
value="<?php echo htmlspecialchars($data['role_data']['qualification'] ?? ''); ?>">

</div>

<?php } ?>

<!-- =========================
     PARENT
========================= -->

<?php if(
$role === 'parent'
&&
!empty($data['role_data'])
){ ?>

<div class="form-group">

<label class="form-label">

Relationship

</label>

<input
type="text"
name="relationship_name"
class="form-input"
value="<?php echo htmlspecialchars($data['role_data']['relationship_name'] ?? ''); ?>">

</div>

<?php } ?>

<!-- =========================
     DRIVER
========================= -->

<?php if(
$role === 'driver'
&&
!empty($data['role_data'])
){ ?>

<div class="form-group">

<label class="form-label">

License Number

</label>

<input
type="text"
name="license_number"
class="form-input"
value="<?php echo htmlspecialchars($data['role_data']['license_number'] ?? ''); ?>">

</div>

<div class="form-group">

<label class="form-label">

Emergency Contact

</label>

<input
type="text"
name="emergency_contact"
class="form-input"
value="<?php echo htmlspecialchars($data['role_data']['emergency_contact'] ?? ''); ?>">

</div>

<div class="form-group">

<label class="form-label">

Address

</label>

<textarea
name="address"
class="form-textarea"
rows="4"><?php echo htmlspecialchars($data['role_data']['address'] ?? ''); ?></textarea>

</div>

<div class="form-group">

<label class="form-label">

Status

</label>

<input
type="text"
class="form-input disabled-field"
value="<?php echo ucfirst($data['role_data']['status'] ?? '-'); ?>"
disabled>

</div>

<?php } ?>

</div>

<div
style="
margin-top:24px;
display:flex;
gap:12px;
flex-wrap:wrap;
">

<button
type="submit"
name="save_profile"
class="btn btn-primary">

<i class="fa-solid fa-floppy-disk"></i>

Save Changes

</button>

<a
href="index.php"
class="btn">

Cancel

</a>

</div>

</div>

</form>

<?php } else { ?>

<!-- =========================
     VIEW MODE STARTS
========================= -->
<div class="profile-section">

<div class="profile-section-header">

<h2>

<i class="fa-solid fa-address-card"></i>

Profile Information

</h2>

</div>

<div class="info-grid">

<div class="info-card">

<h4>Email</h4>

<p>

<?php echo htmlspecialchars($user['email']); ?>

</p>

</div>

<div class="info-card">

<h4>Phone</h4>

<p>

<?php

echo !empty($user['phone'])
? htmlspecialchars($user['phone'])
: '-';

?>

</p>

</div>

<div class="info-card">

<h4>Role</h4>

<p>

<?php echo ucfirst($role); ?>

</p>

</div>

</div>

</div>

<!-- =========================
     STUDENT VIEW
========================= -->

<?php if(
$role === 'student'
&&
!empty($data['role_data'])
){ ?>

<div class="profile-section">

<div class="profile-section-header">

<h2>

<i class="fa-solid fa-graduation-cap"></i>

Student Information

</h2>

</div>

<div class="info-grid">

<div class="info-card">

<h4>Student ID</h4>

<p>

<?php

echo htmlspecialchars(
$data['role_data']['student_id']
?? '-'
);

?>

</p>

</div>

<div class="info-card">

<h4>Class</h4>

<p>

<?php

echo htmlspecialchars(
$data['role_data']['class_name']
?? '-'
);

?>

</p>

</div>

<div class="info-card">

<h4>Section</h4>

<p>

<?php

echo htmlspecialchars(
$data['role_data']['section_name']
?? '-'
);

?>

</p>

</div>

<div class="info-card">

<h4>Address</h4>

<p>

<?php

echo !empty(
$data['role_data']['address']
)

? htmlspecialchars(
$data['role_data']['address']
)

: '-';

?>

</p>

</div>

</div>

</div>

<?php } ?>

<!-- =========================
     TEACHER VIEW
========================= -->

<?php if(
$role === 'teacher'
&&
!empty($data['role_data'])
){ ?>

<div class="profile-section">

<div class="profile-section-header">

<h2>

<i class="fa-solid fa-chalkboard-user"></i>

Teacher Information

</h2>

</div>

<div class="info-grid">

<div class="info-card">

<h4>Teacher ID</h4>

<p>

<?php

echo htmlspecialchars(
$data['role_data']['teacher_id']
?? '-'
);

?>

</p>

</div>

<div class="info-card">

<h4>Qualification</h4>

<p>

<?php

echo htmlspecialchars(
$data['role_data']['qualification']
?? '-'
);

?>

</p>

</div>

</div>

</div>

<div class="profile-section">

<h2>

<i class="fa-solid fa-book"></i>

Assigned Subjects

</h2>

<br>

<?php

if(
!empty(
$data['extra']['subjects']
)
){

echo '<ul>';

foreach(
$data['extra']['subjects']
as $subject
){

echo '<li>'
.
htmlspecialchars($subject)
.
'</li>';

}

echo '</ul>';

}
else{

echo '<p>No subjects assigned.</p>';

}

?>

</div>

<div class="profile-section">

<h2>

<i class="fa-solid fa-school"></i>

Assigned Classes

</h2>

<br>

<?php

$pairs =
$teacherScope?->getAssignedClassSectionPairs()
?? [];

if(!empty($pairs)){

echo '<ul>';

foreach($pairs as $pair){

echo '<li>'
.
htmlspecialchars(
$pair['class_name']
.
' - '
.
$pair['section_name']
)
.
'</li>';

}

echo '</ul>';

}
else{

echo '<p>No class assignments.</p>';

}

?>

</div>

<?php } ?>

<!-- =========================
     PARENT VIEW
========================= -->

<?php if($role === 'parent'){ ?>

<div class="profile-section">

<h2>

<i class="fa-solid fa-children"></i>

Children Information

</h2>

<br>

<?php

$children =
$data['extra']['children']
?? [];

if(!empty($children)){ ?>

<div class="table-responsive">

<table class="custom-table">

<thead>

<tr>

<th>Name</th>
<th>Student ID</th>
<th>Class</th>

</tr>

</thead>

<tbody>

<?php foreach($children as $child){ ?>

<tr>

<td>

<?php

echo htmlspecialchars(
$child['full_name']
);

?>

</td>

<td>

<?php

echo htmlspecialchars(
$child['student_id']
);

?>

</td>

<td>

<?php

echo htmlspecialchars(

($child['class_name'] ?? '')

.

' '

.

($child['section_name'] ?? '')

);

?>

</td>

</tr>

<?php } ?>

</tbody>

</table>

</div>

<?php }else{ ?>

<p>No linked children found.</p>

<?php } ?>

</div>

<?php } ?>

<!-- =========================
     DRIVER VIEW
========================= -->

<?php if(
$role === 'driver'
&&
!empty($data['role_data'])
){ ?>

<div class="profile-section">

<h2>

<i class="fa-solid fa-bus"></i>

Driver Information

</h2>

<br>

<div class="info-grid">

<div class="info-card">

<h4>License Number</h4>

<p>

<?php

echo htmlspecialchars(
$data['role_data']['license_number']
?? '-'
);

?>

</p>

</div>

<div class="info-card">

<h4>Emergency Contact</h4>

<p>

<?php

echo htmlspecialchars(
$data['role_data']['emergency_contact']
?? '-'
);

?>

</p>

</div>

<div class="info-card">

<h4>Status</h4>

<p>

<?php

echo ucfirst(
$data['role_data']['status']
?? '-'
);

?>

</p>

</div>

<div class="info-card">

<h4>Address</h4>

<p>

<?php

echo htmlspecialchars(
$data['role_data']['address']
?? '-'
);

?>

</p>

</div>

</div>

</div>

<?php } ?>

<!-- =========================
     ADMIN VIEW
========================= -->

<?php if($role === 'admin'){ ?>

<div class="profile-section">

<h2>

<i class="fa-solid fa-user-shield"></i>

Administrator Information

</h2>

<br>

<div class="info-grid">

<div class="info-card">

<h4>Admin ID</h4>

<p>

<?php

echo htmlspecialchars(
$user['admin_id']
?? '-'
);

?>

</p>

</div>

<div class="info-card">

<h4>Role</h4>

<p>

System Administrator

</p>

</div>

</div>

</div>

<?php } ?>

</div>

</div>

<?php } ?>

</div>

</div>

</div>

<script>

const profileInput =
document.getElementById(
'profilePhotoInput'
);

if(profileInput){

profileInput.addEventListener(
'change',
function(){

const file =
this.files[0];

if(file){

const title =
document.getElementById(
'uploadTitle'
);

const subtitle =
document.getElementById(
'uploadSubtext'
);

if(title){
title.innerText =
file.name;
}

if(subtitle){
subtitle.innerText =
'File selected successfully';
}

}

}
);

}

</script>

<script src="../assets/js/common.js"></script>

</body>

</html>