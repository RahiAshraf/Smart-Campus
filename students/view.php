
<?php

include('../config/auth.php');
include('../config/db.php');

$id = intval($_GET['id']);

$query = mysqli_query(

$conn,

"SELECT

s.*,

u.full_name,
u.email,
u.phone,

c.class_name,

sec.section_name

FROM students s

LEFT JOIN users u
ON s.user_id = u.id

LEFT JOIN classes c
ON s.class_id = c.class_id

LEFT JOIN sections sec
ON s.section_id = sec.section_id

WHERE s.id='$id'

LIMIT 1"

);

if(!$query){

die(mysqli_error($conn));

}

$row = mysqli_fetch_assoc($query);

if(!$row){

header("Location:index.php");
exit();

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
View Student | MindMerge SmartCampus
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
View Student
</h1>

<p>
Student profile information.
</p>

</div>

<div
style="
display:flex;
gap:12px;
flex-wrap:wrap;
">

<a
href="edit.php?id=<?php echo $row['id']; ?>"
class="btn btn-primary">

<i class="fa-solid fa-pen"></i>

Edit

</a>

<a
href="index.php"
class="btn">

<i class="fa-solid fa-arrow-left"></i>

Back

</a>

</div>

</div>

<div class="dashboard-section">

<div class="section-header">

<h2>
Student Details
</h2>

</div>

<div class="form-grid">

<div class="form-group">

<label class="form-label">
Student ID
</label>

<div class="form-input">

<?php echo htmlspecialchars($row['student_id']); ?>

</div>

</div>

<div class="form-group">

<label class="form-label">
Full Name
</label>

<div class="form-input">

<?php echo htmlspecialchars($row['full_name']); ?>

</div>

</div>

</div>

<div class="form-grid">

<div class="form-group">

<label class="form-label">
Email
</label>

<div class="form-input">

<?php echo htmlspecialchars($row['email']); ?>

</div>

</div>

<div class="form-group">

<label class="form-label">
Phone
</label>

<div class="form-input">

<?php echo htmlspecialchars($row['phone']); ?>

</div>

</div>

</div>

<div class="form-grid">

<div class="form-group">

<label class="form-label">
Class
</label>

<div class="form-input">

<?php echo htmlspecialchars($row['class_name']); ?>

</div>

</div>

<div class="form-group">

<label class="form-label">
Section
</label>

<div class="form-input">

<?php echo htmlspecialchars($row['section_name']); ?>

</div>

</div>

</div>

<div class="form-grid">

<div class="form-group">

<label class="form-label">
Date Of Birth
</label>

<div class="form-input">

<?php

if(!empty($row['dob'])){

echo date(
'd M Y',
strtotime($row['dob'])
);

}

?>

</div>

</div>

<div class="form-group">

<label class="form-label">
Gender
</label>

<div class="form-input">

<?php echo ucfirst($row['gender']); ?>

</div>

</div>

</div>

<div class="form-group">

<label class="form-label">
Parent Phone
</label>

<div class="form-input">

<?php echo htmlspecialchars($row['parent_phone']); ?>

</div>

</div>

<div class="form-group">

<label class="form-label">
Address
</label>

<div class="form-textarea">

<?php

echo nl2br(
htmlspecialchars(
$row['address']
)
);

?>

</div>

</div>

<div class="form-group">

<label class="form-label">
Created On
</label>

<div class="form-input">

<?php

echo date(
'd M Y h:i A',
strtotime(
$row['created_at']
)
);

?>

</div>

</div>

</div>

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
href="edit.php?id=<?php echo $row['id']; ?>"
class="btn btn-primary">

<i class="fa-solid fa-pen"></i>

Edit Student

</a>

<a
href="delete.php?id=<?php echo $row['id']; ?>"
class="btn"
style="
background:#ef4444;
color:white;
"
onclick="return confirm('Are you sure you want to delete this student?');">

<i class="fa-solid fa-trash"></i>

Delete Student

</a>

</div>

</div>

</div>

</div>

</div>

<script src="../assets/js/common.js"></script>

</body>
</html>