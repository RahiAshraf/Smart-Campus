<?php

include('../config/auth.php');
include('../config/db.php');

$search = '';

if(isset($_GET['search'])){

$search =
mysqli_real_escape_string(
$conn,
trim($_GET['search'])
);

$query = mysqli_query(

$conn,

"SELECT sections.*,
classes.class_name

FROM sections

INNER JOIN classes
ON classes.class_id = sections.class_id

WHERE

classes.class_name LIKE '%$search%'

OR

sections.section_code LIKE '%$search%'

OR

sections.section_name LIKE '%$search%'

ORDER BY sections.section_id ASC"

);

}
else{

$query = mysqli_query(

$conn,

"SELECT sections.*,
classes.class_name

FROM sections

INNER JOIN classes
ON classes.class_id = sections.class_id

ORDER BY sections.section_id ASC"

);

}

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
Sections Management | MindMerge
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
Sections Management
</h1>

<p>
Manage class sections.
</p>

</div>

<a
href="add.php"
class="btn btn-primary">

<i class="fa-solid fa-plus"></i>

Add Section

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

if($_GET['success']=='added'){
echo "Section added successfully.";
}
elseif($_GET['success']=='updated'){
echo "Section updated successfully.";
}
elseif($_GET['success']=='deleted'){
echo "Section deleted successfully.";
}

?>

</div>

<?php } ?>
<?php if(isset($_GET['error'])){ ?>

<div
style="
background:#fee2e2;
color:#991b1b;
padding:14px 18px;
border-radius:14px;
margin-bottom:20px;
font-weight:500;
">

<?php

if($_GET['error']=='in_use'){

echo "Cannot delete section because students, timetable records or attendance records are linked to it.";

}

?>

</div>

<?php } ?>

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

placeholder="Search Section..."

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
All Sections
</h2>

</div>

<div class="table-responsive">

<table class="custom-table">

<thead>

<tr>

<th data-sort="true">
Section Code
</th>

<th data-sort="true">
Section Name
</th>

<th data-sort="true">
Class
</th>

<th data-sort="true">
Capacity
</th>

<th data-sort="true">
Status
</th>

<th data-sort="false">
Actions
</th>

</tr>

</thead>

<tbody>

<?php

if(mysqli_num_rows($query)>0){

while($row=mysqli_fetch_assoc($query)){

?>

<tr>

<td>
<?php echo htmlspecialchars($row['class_name']); ?>
</td>

<td>
<?php echo htmlspecialchars($row['section_code']); ?>
</td>

<td>
<?php echo htmlspecialchars($row['section_name']); ?>
</td>

<td>
<?php echo $row['capacity']; ?>
</td>

<td>

<span
class="status <?php echo ($row['status']=='active') ? 'success' : 'danger'; ?>">

<?php echo ucfirst($row['status']); ?>

</span>

</td>

<td>

<div
style="
display:flex;
gap:8px;
flex-wrap:wrap;
">

<a
href="edit.php?id=<?php echo $row['section_id']; ?>"
class="btn btn-primary">

<i class="fa-solid fa-pen"></i>

Edit

</a>

<a
href="delete.php?id=<?php echo $row['section_id']; ?>"
class="btn"
style="
background:#ef4444;
color:white;
"
onclick="return confirm('Delete this section?');">

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
colspan="6"
style="text-align:center;">

No sections found.

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