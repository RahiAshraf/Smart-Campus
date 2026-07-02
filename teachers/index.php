
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

"SELECT

t.id AS teacher_record_id,

t.teacher_id,
t.qualification,
t.created_at,

u.full_name,
u.email,
u.phone

FROM teachers t

LEFT JOIN users u
ON t.user_id = u.id

WHERE

t.teacher_id LIKE '%$search%'

OR

u.full_name LIKE '%$search%'

OR

u.email LIKE '%$search%'

OR

u.phone LIKE '%$search%'

OR

t.qualification LIKE '%$search%'

ORDER BY u.full_name ASC"

);

}
else{

$query = mysqli_query(

$conn,

"SELECT

t.id AS teacher_record_id,

t.teacher_id,
t.qualification,
t.created_at,

u.full_name,
u.email,
u.phone

FROM teachers t

LEFT JOIN users u
ON t.user_id = u.id

ORDER BY u.full_name ASC"

);

}

$total_teachers = mysqli_fetch_assoc(

mysqli_query(
$conn,
"SELECT COUNT(*) total
FROM teachers"
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
Teachers Management | MindMerge SmartCampus
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
Teachers Management
</h1>

<p>
Manage teaching staff profiles.
</p>

</div>

<div
style="
display:flex;
gap:12px;
flex-wrap:wrap;
">

<a
href="../subjects/index.php"
class="btn btn-primary">

<i class="fa-solid fa-book"></i>

Manage Subjects

</a>

<a
href="../teacher_assignments/index.php"
class="btn btn-primary">

<i class="fa-solid fa-link"></i>

Manage Assignments

</a>

<a
href="add.php"
class="btn btn-primary">

<i class="fa-solid fa-plus"></i>

Add Teacher

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
echo "Teacher added successfully.";
}
elseif($_GET['success'] == 'updated'){
echo "Teacher updated successfully.";
}
elseif($_GET['success'] == 'deleted'){
echo "Teacher deleted successfully.";
}

?>

</div>

<?php } ?>

<div class="dashboard-section">

<div class="section-header">

<h2>
Teacher Overview
</h2>

</div>

<div
style="
font-size:18px;
font-weight:600;
">

Total Teachers :
<?php echo $total_teachers; ?>

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

placeholder="Search Teacher ID, Name, Email, Phone"

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
All Teachers
</h2>

</div>

<div class="table-responsive">

<table class="custom-table">

<thead>

<tr>

<th data-sort="true">
Teacher ID
</th>

<th data-sort="true">
Teacher Name
</th>

<th data-sort="true">
Email
</th>

<th data-sort="true">
Phone
</th>

<th data-sort="true">
Qualification
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
<?php echo htmlspecialchars($row['teacher_id']); ?>
</td>

<td>
<?php echo htmlspecialchars($row['full_name']); ?>
</td>

<td>
<?php echo htmlspecialchars($row['email']); ?>
</td>

<td>
<?php echo htmlspecialchars($row['phone']); ?>
</td>

<td>
<?php echo htmlspecialchars($row['qualification']); ?>
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
href="view.php?id=<?php echo $row['teacher_record_id']; ?>"
class="btn"
style="
background:#10b981;
color:white;
">

View

</a>

<a
href="edit.php?id=<?php echo $row['teacher_record_id']; ?>"
class="btn btn-primary">

Edit

</a>

<a
href="delete.php?id=<?php echo $row['teacher_record_id']; ?>"
class="btn"
style="
background:#ef4444;
color:white;
"
onclick="return confirm('Delete this teacher?');">

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

<td colspan="7" style="text-align:center;">

No teachers found.

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
