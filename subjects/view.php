
<?php

include('../config/auth.php');
include('../config/db.php');

$id = intval($_GET['id']);

$query = mysqli_query(

$conn,

"SELECT *

FROM subjects

WHERE subject_id='$id'"

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
View Subject | MindMerge SmartCampus
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
View Subject
</h1>

<p>
Subject information and details.
</p>

</div>

<div
style="
display:flex;
gap:12px;
flex-wrap:wrap;
">

<a
href="edit.php?id=<?php echo $row['subject_id']; ?>"
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

<div class="form-grid">

<div class="form-group">

<label class="form-label">
Status
</label>

<div>

<span
class="status <?php echo ($row['status']=='active') ? 'success' : 'danger'; ?>">

<?php echo ucfirst($row['status']); ?>

</span>

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
href="edit.php?id=<?php echo $row['subject_id']; ?>"
class="btn btn-primary">

<i class="fa-solid fa-pen"></i>

Edit Subject

</a>

<a
href="delete.php?id=<?php echo $row['subject_id']; ?>"
class="btn"
style="
background:#ef4444;
color:white;
"
onclick="return confirm('Are you sure you want to delete this subject?');">

<i class="fa-solid fa-trash"></i>

Delete Subject

</a>

</div>

</div>

</div>

</div>

</div>

<script src="../assets/js/common.js"></script>

</body>
</html>
