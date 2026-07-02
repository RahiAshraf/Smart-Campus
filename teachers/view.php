
<?php

include('../config/auth.php');
include('../config/db.php');

$id = intval($_GET['id']);

$query = mysqli_query(

$conn,

"SELECT

t.*,

u.full_name,
u.email,
u.phone

FROM teachers t

LEFT JOIN users u
ON t.user_id = u.id

WHERE t.id='$id'"

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
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>
View Teacher | MindMerge SmartCampus
</title>

<link rel="stylesheet" href="../assets/css/global.css">
<link rel="stylesheet" href="../assets/css/layout.css">
<link rel="stylesheet" href="../assets/css/components.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

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
View Teacher
</h1>

<p>
Teacher profile information.
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

Edit

</a>

<a
href="index.php"
class="btn">

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

</div>

</div>

</div>

<script src="../assets/js/common.js"></script>

</body>
</html>
