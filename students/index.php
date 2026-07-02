
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

s.id AS student_record_id,

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

WHERE

s.student_id LIKE '%$search%'

OR

u.full_name LIKE '%$search%'

OR

u.email LIKE '%$search%'

OR

u.phone LIKE '%$search%'

OR

c.class_name LIKE '%$search%'

OR

sec.section_name LIKE '%$search%'

ORDER BY s.id DESC"

);

}
else{

$query = mysqli_query(

$conn,

"SELECT

s.id AS student_record_id,

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

ORDER BY s.id DESC"

);

}

$total_students = mysqli_fetch_assoc(

mysqli_query(
$conn,
"SELECT COUNT(*) total FROM students"
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
Students Management | MindMerge SmartCampus
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
Students Management
</h1>

<p>
Manage all students in the institution.
</p>

</div>

<a
href="add.php"
class="btn btn-primary">

<i class="fa-solid fa-plus"></i>

Add Student

</a>

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

echo "Student added successfully.";

}
elseif($_GET['success'] == 'updated'){

echo "Student updated successfully.";

}
elseif($_GET['success'] == 'deleted'){

echo "Student deleted successfully.";

}

?>

</div>

<?php } ?>

<div class="dashboard-section">

<div class="section-header">

<h2>
Student Overview
</h2>

</div>

<div
style="
font-size:18px;
font-weight:600;
">

Total Students :
<?php echo $total_students; ?>

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

placeholder="Search Student ID, Name, Email, Phone, Class"

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
All Students
</h2>

</div>

<div class="table-responsive">

<table class="custom-table">

<thead>

<tr>

<th data-sort="true">
Student ID
</th>

<th data-sort="true">
Student Name
</th>

<th data-sort="true">
Class
</th>

<th data-sort="true">
Section
</th>

<th data-sort="true">
Phone
</th>

<th data-sort="true">
Gender
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

<strong>

<?php
echo htmlspecialchars(
$row['student_id']
);
?>

</strong>

</td>

<td>

<?php
echo htmlspecialchars(
$row['full_name']
);
?>

</td>

<td>

<?php
echo htmlspecialchars(
$row['class_name']
);
?>

</td>

<td>

<?php
echo htmlspecialchars(
$row['section_name']
);
?>

</td>

<td>

<?php
echo htmlspecialchars(
$row['phone']
);
?>

</td>

<td>

<?php
echo ucfirst(
$row['gender']
);
?>

</td>

<td>

<?php

echo date(
'd M Y',
strtotime(
$row['created_at']
)
);

?>

</td>

<td>

<div
style="
display:flex;
gap:8px;
flex-wrap:wrap;
">

<a
href="view.php?id=<?php echo $row['student_record_id']; ?>"
class="btn"
style="
background:#10b981;
color:white;
">

<i class="fa-solid fa-eye"></i>

View

</a>

<a
href="edit.php?id=<?php echo $row['student_record_id']; ?>"
class="btn btn-primary">

<i class="fa-solid fa-pen"></i>

Edit

</a>

<a
href="delete.php?id=<?php echo $row['student_record_id']; ?>"
class="btn"
style="
background:#ef4444;
color:white;
"
onclick="return confirm('Are you sure you want to delete this student?');">

<i class="fa-solid fa-trash"></i>

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

<td
colspan="8"
style="text-align:center;">

No students found.

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
