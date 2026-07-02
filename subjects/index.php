
<?php

include('../config/auth.php');
include('../config/db.php');

$search = '';

if(isset($_GET['search'])){

$search = mysqli_real_escape_string(
$conn,
trim($_GET['search'])
);

$query = mysqli_query(

$conn,

"SELECT *

FROM subjects

WHERE

subject_code LIKE '%$search%'

OR

subject_name LIKE '%$search%'

OR

description LIKE '%$search%'

ORDER BY subject_name ASC"

);

}
else{

$query = mysqli_query(

$conn,

"SELECT *

FROM subjects

ORDER BY subject_name ASC"

);

}

$total_subjects = mysqli_fetch_assoc(

mysqli_query(
$conn,
"SELECT COUNT(*) total FROM subjects"
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
Subjects Management | MindMerge SmartCampus
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
Subjects Management
</h1>

<p>
Manage academic subjects.
</p>

</div>

<div
style="
display:flex;
gap:12px;
flex-wrap:wrap;
">

<a
href="../teachers/index.php"
class="btn">

<i class="fa-solid fa-arrow-left"></i>

Back to Teachers

</a>

<a
href="add.php"
class="btn btn-primary">

<i class="fa-solid fa-plus"></i>

Add Subject

</a>

</div>

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
echo "Subject added successfully.";
}
elseif($_GET['success'] == 'updated'){
echo "Subject updated successfully.";
}
elseif($_GET['success'] == 'deleted'){
echo "Subject deleted successfully.";
}

?>

</div>

<?php } ?>

<div class="dashboard-section">

<div class="section-header">

<h2>
Subject Overview
</h2>

</div>

<div
style="
font-size:18px;
font-weight:600;
">

Total Subjects :
<?php echo $total_subjects; ?>

</div>

</div>

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

placeholder="Search Subject Code or Name"

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

<div class="dashboard-section">

<div class="section-header">

<h2>
All Subjects
</h2>

</div>

<div class="table-responsive">

<table class="custom-table">

<thead>

<tr>

<th data-sort="true">
Subject Code
</th>

<th data-sort="true">
Subject Name
</th>

<th data-sort="true">
Description
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
<?php echo htmlspecialchars($row['subject_code']); ?>
</td>

<td>
<?php echo htmlspecialchars($row['subject_name']); ?>
</td>

<td>
<?php echo htmlspecialchars($row['description']); ?>
</td>

<td>
<?php echo ucfirst($row['status']); ?>
</td>

<td>
<?php echo date('d M Y',strtotime($row['created_at'])); ?>
</td>

<td>

<div
style="
display:flex;
gap:8px;
flex-wrap:wrap;
">

<a
href="view.php?id=<?php echo $row['subject_id']; ?>"
class="btn"
style="
background:#10b981;
color:white;
">

View

</a>

<a
href="edit.php?id=<?php echo $row['subject_id']; ?>"
class="btn btn-primary">

Edit

</a>

<a
href="delete.php?id=<?php echo $row['subject_id']; ?>"
class="btn"
style="
background:#ef4444;
color:white;
"
onclick="return confirm('Delete this subject?');">

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

<td colspan="6" style="text-align:center;">

No subjects found.

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

<script src="../assets/js/common.js"></script>

</body>
</html>
