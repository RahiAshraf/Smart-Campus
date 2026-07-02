
<?php

include('../config/auth.php');
include('../config/db.php');

$id = intval($_GET['id']);

$query = mysqli_query(

$conn,

"SELECT

ta.*,

t.teacher_id,

t.qualification,

u.full_name,
u.email,
u.phone,

sub.subject_code,
sub.subject_name,
sub.description,
sub.status AS subject_status,

c.class_code,
c.class_name,

sec.section_code,
sec.section_name

FROM teacher_assignments ta

LEFT JOIN teachers t
ON ta.teacher_id = t.id

LEFT JOIN users u
ON t.user_id = u.id

LEFT JOIN subjects sub
ON ta.subject_id = sub.subject_id

LEFT JOIN classes c
ON ta.class_id = c.class_id

LEFT JOIN sections sec
ON ta.section_id = sec.section_id

WHERE ta.assignment_id='$id'"

);

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
View Assignment | MindMerge SmartCampus
</title>

<link rel="stylesheet" href="../assets/css/global.css">
<link rel="stylesheet" href="../assets/css/layout.css">
<link rel="stylesheet" href="../assets/css/components.css">

<link rel="stylesheet"
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
View Assignment
</h1>

<p>
Teacher assignment information and details.
</p>

</div>

<div
style="
display:flex;
gap:12px;
flex-wrap:wrap;
">

<a
href="edit.php?id=<?php echo $row['assignment_id']; ?>"
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
Teacher Information
</h2>

</div>

<div class="form-grid">

<div class="form-group">

<label class="form-label">
Teacher ID
</label>

<div class="form-input">

<?php echo htmlspecialchars($row['teacher_id']); ?>

</div>

</div>

<div class="form-group">

<label class="form-label">
Teacher Name
</label>

<div class="form-input">

<?php echo htmlspecialchars($row['full_name']); ?>

</div>

</div>

</div>

<div class="form-grid">

<div class="form-group">

<label class="form-label">
Qualification
</label>

<div class="form-input">

<?php echo htmlspecialchars($row['qualification']); ?>

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

<div class="form-group">

<label class="form-label">
Email
</label>

<div class="form-input">

<?php echo htmlspecialchars($row['email']); ?>

</div>

</div>

</div>

<div class="dashboard-section">

<div class="section-header">

<h2>
Subject Information
</h2>

</div>

<div class="form-grid">

<div class="form-group">

<label class="form-label">
Subject Code
</label>

<div class="form-input">

<?php echo htmlspecialchars($row['subject_code']); ?>

</div>

</div>

<div class="form-group">

<label class="form-label">
Subject Name
</label>

<div class="form-input">

<?php echo htmlspecialchars($row['subject_name']); ?>

</div>

</div>

</div>

<div class="form-group">

<label class="form-label">
Description
</label>

<div class="form-textarea">

<?php

echo nl2br(
htmlspecialchars(
$row['description']
)
);

?>

</div>

</div>

<div class="form-group">

<label class="form-label">
Status
</label>

<div>

<span
class="status <?php echo ($row['subject_status']=='active') ? 'success' : 'danger'; ?>">

<?php echo ucfirst($row['subject_status']); ?>

</span>

</div>

</div>

</div>

<div class="dashboard-section">

<div class="section-header">

<h2>
Assignment Information
</h2>

</div>
<div class="form-group">

<label class="form-label">
Assignment Role
</label>

<div>

<span class="status success">

<?php

echo ucwords(
str_replace(
'_',
' ',
$row['assignment_role']
)
);

?>

</span>

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
Class Code
</label>

<div class="form-input">

<?php echo htmlspecialchars($row['class_code']); ?>

</div>

</div>

<div class="form-group">

<label class="form-label">
Section Code
</label>

<div class="form-input">

<?php echo htmlspecialchars($row['section_code']); ?>

</div>

</div>

</div>

<div class="form-group">

<label class="form-label">
Assigned On
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
href="edit.php?id=<?php echo $row['assignment_id']; ?>"
class="btn btn-primary">

<i class="fa-solid fa-pen"></i>

Edit Assignment

</a>

<a
href="delete.php?id=<?php echo $row['assignment_id']; ?>"
class="btn"
style="
background:#ef4444;
color:white;
"
onclick="return confirm('Delete this assignment?');">

<i class="fa-solid fa-trash"></i>

Delete Assignment

</a>

</div>

</div>

</div>

</div>

</div>

<script src="../assets/js/common.js"></script>

</body>
</html>