
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

ta.assignment_id,

ta.assignment_role,

ta.created_at,

t.teacher_id,

u.full_name,

t.qualification,

sub.subject_code,
sub.subject_name,

c.class_name,

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

WHERE

u.full_name LIKE '%$search%'

OR

t.teacher_id LIKE '%$search%'

OR

sub.subject_name LIKE '%$search%'

OR

sub.subject_code LIKE '%$search%'

OR

c.class_name LIKE '%$search%'

OR

sec.section_name LIKE '%$search%'

ORDER BY

c.class_name ASC,
sec.section_name ASC,
sub.subject_name ASC"

);

}
else{

$query = mysqli_query(

$conn,

"SELECT

ta.assignment_id,

ta.assignment_role,

ta.created_at,

t.teacher_id,

u.full_name,

t.qualification,

sub.subject_code,
sub.subject_name,

c.class_name,

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

ORDER BY

c.class_name ASC,
sec.section_name ASC,
sub.subject_name ASC"

);

}

$total_assignments = mysqli_fetch_assoc(

mysqli_query(
$conn,
"SELECT COUNT(*) total
FROM teacher_assignments"
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
Teacher Assignments | MindMerge SmartCampus
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
Teacher Assignments
</h1>

<p>
Manage teacher, subject, class and section mappings.
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

Assign Teacher

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

if($_GET['success']=='added'){
echo "Assignment created successfully.";
}
elseif($_GET['success']=='updated'){
echo "Assignment updated successfully.";
}
elseif($_GET['success']=='deleted'){
echo "Assignment deleted successfully.";
}

?>

</div>

<?php } ?>

<div class="dashboard-section">

<div class="section-header">

<h2>
Assignment Overview
</h2>

</div>

<div
style="
font-size:18px;
font-weight:600;
">

Total Assignments :
<?php echo $total_assignments; ?>

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

placeholder="Search teacher, subject, class or section"

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
All Assignments
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
Teacher
</th>

<th data-sort="true">
Role
</th>

<th data-sort="true">
Qualification
</th>

<th data-sort="true">
Subject Code
</th>

<th data-sort="true">
Subject Name
</th>

<th data-sort="true">
Class
</th>

<th data-sort="true">
Section
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

<?php

$role = $row['assignment_role'];

$badge_class = 'success';

if($role == 'co_primary'){
    $badge_class = 'info';
}
elseif($role == 'lab_incharge'){
    $badge_class = 'warning';
}
elseif($role == 'lab_faculty'){
    $badge_class = 'primary';
}
elseif($role == 'lab_assistant'){
    $badge_class = 'secondary';
}

?>

<span class="status <?php echo $badge_class; ?>">

<?php

echo ucwords(
str_replace(
'_',
' ',
$role
)
);

?>

</span>

</td>

<td>
<?php echo htmlspecialchars($row['qualification']); ?>
</td>

<td>
<?php echo htmlspecialchars($row['subject_code']); ?>
</td>

<td>
<?php echo htmlspecialchars($row['subject_name']); ?>
</td>

<td>
<?php echo htmlspecialchars($row['class_name']); ?>
</td>

<td>
<?php echo htmlspecialchars($row['section_name']); ?>
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
href="view.php?id=<?php echo $row['assignment_id']; ?>"
class="btn"
style="
background:#10b981;
color:white;
">

View

</a>

<a
href="edit.php?id=<?php echo $row['assignment_id']; ?>"
class="btn btn-primary">

Edit

</a>

<a
href="delete.php?id=<?php echo $row['assignment_id']; ?>"
class="btn"
style="
background:#ef4444;
color:white;
"
onclick="return confirm('Delete this assignment?');">

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

<td colspan="10" style="text-align:center;">

No assignments found.

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