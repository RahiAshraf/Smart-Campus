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

pt.*,

(
SELECT COUNT(*)
FROM periods p
WHERE p.template_id = pt.template_id
) AS total_blocks

FROM period_templates pt

WHERE

pt.template_code LIKE '%$search%'

OR

pt.template_name LIKE '%$search%'

OR

pt.template_type LIKE '%$search%'

OR

pt.description LIKE '%$search%'

ORDER BY pt.template_name ASC"

);

}
else{

$query = mysqli_query(

$conn,

"SELECT

pt.*,

(
SELECT COUNT(*)
FROM periods p
WHERE p.template_id = pt.template_id
) AS total_blocks

FROM period_templates pt

ORDER BY pt.template_name ASC"

);

}

$total_templates = mysqli_fetch_assoc(

mysqli_query(

$conn,

"SELECT COUNT(*) total
FROM period_templates"

)

)['total'];

$regular_templates = mysqli_fetch_assoc(

mysqli_query(

$conn,

"SELECT COUNT(*) total
FROM period_templates
WHERE template_type='regular'"

)

)['total'];

$exam_templates = mysqli_fetch_assoc(

mysqli_query(

$conn,

"SELECT COUNT(*) total
FROM period_templates
WHERE template_type='exam'"

)

)['total'];

$lab_templates = mysqli_fetch_assoc(

mysqli_query(

$conn,

"SELECT COUNT(*) total
FROM period_templates
WHERE template_type='lab'"

)

)['total'];

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
Schedule Templates | MindMerge SmartCampus
</title>

<link rel="stylesheet" href="../assets/css/global.css">
<link rel="stylesheet" href="../assets/css/layout.css">
<link rel="stylesheet" href="../assets/css/components.css">

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="../../assets/css/material-theme.css">

<style>

.template-grid{

display:grid;

grid-template-columns:
repeat(auto-fit,minmax(320px,1fr));

gap:20px;

}

.template-card{

background:var(--card);

border:1px solid rgba(148,163,184,.18);

padding:24px;

border-radius:18px;

height:100%;

display:flex;

flex-direction:column;

gap:0;

transition:.25s;

}


.template-card:hover{

border-color:var(--primary);

transform:translateY(-2px);

}

.template-card h3{

margin:16px 0 8px;

font-size:20px;

}

.template-actions{

display:flex;

gap:12px;

flex-wrap:wrap;

margin-top:auto;

padding-top:10px;

}
.template-meta{

display:flex;
flex-wrap:wrap;
gap:10px;

margin-top:12px;

}

.template-actions{

display:flex;
gap:10px;
flex-wrap:wrap;

margin-top:18px;

}
body.dark-mode .template-card{

background:#111c35;

border:1px solid #29476d;

}
</style>

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
Schedule Templates
</h1>

<p>
Create reusable schedule structures for classes, exams and labs.
</p>

</div>

<div
style="
display:flex;
gap:12px;
flex-wrap:wrap;
">

<a
href="add.php"
class="btn btn-primary">

<i class="fa-solid fa-plus"></i>

Create Template

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
echo "Template created successfully.";
}
elseif($_GET['success']=='updated'){
echo "Template updated successfully.";
}
elseif($_GET['success']=='deleted'){
echo "Template deleted successfully.";
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

if($_GET['error'] == 'template_has_periods'){

echo "Cannot delete this schedule because it still contains periods. Remove all periods first.";

}
elseif($_GET['error'] == 'template_in_use'){

echo "Cannot delete this schedule because it is already used by a timetable.";

}
else{

echo "An error occurred.";

}

?>

</div>

<?php } ?>

<div class="dashboard-grid">

<div class="dashboard-card stat-card">

<div class="stat-top">

<div class="card-icon">
<i class="fa-solid fa-calendar-days"></i>
</div>

<h3>
<?php echo $total_templates; ?>
</h3>

</div>

<p>
Schedule Templates
</p>

</div>

<div class="dashboard-card stat-card">

<div class="stat-top">

<div class="card-icon">
<i class="fa-solid fa-book-open"></i>
</div>

<h3>
<?php echo $regular_templates; ?>
</h3>

</div>

<p>
Regular Schedules
</p>

</div>

<div class="dashboard-card stat-card">

<div class="stat-top">

<div class="card-icon">
<i class="fa-solid fa-file-signature"></i>
</div>

<h3>
<?php echo $exam_templates; ?>
</h3>

</div>

<p>
Exam Schedules
</p>

</div>

<div class="dashboard-card stat-card">

<div class="stat-top">

<div class="card-icon">
<i class="fa-solid fa-flask"></i>
</div>

<h3>
<?php echo $lab_templates; ?>
</h3>

</div>

<p>
Lab Schedules
</p>

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

placeholder="Search Template Code, Name or Type"

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
Available Schedule Templates
</h2>

</div>

<div class="template-grid">
    <?php

if(mysqli_num_rows($query) > 0){

while($row = mysqli_fetch_assoc($query)){

?>

<div class="dashboard-card template-card">

<div class="card-icon">

<?php

if($row['template_type']=='exam'){

?>

<i class="fa-solid fa-file-signature"></i>

<?php

}
elseif($row['template_type']=='lab'){

?>

<i class="fa-solid fa-flask"></i>

<?php

}
elseif($row['template_type']=='hostel'){

?>

<i class="fa-solid fa-bed"></i>

<?php

}
else{

?>

<i class="fa-solid fa-clock"></i>

<?php

}

?>

</div>

<h3>

<?php

echo htmlspecialchars(
$row['template_name']
);

?>

</h3>

<p>

Code :
<strong>

<?php

echo htmlspecialchars(
$row['template_code']
);

?>

</strong>

</p>

<p style="margin-top:8px;">

<?php

echo $row['total_blocks'];

?>

 Blocks

</p>

<?php

if(!empty($row['description'])){

?>

<p
style="
margin-top:10px;
color:#6b7280;
line-height:1.6;
">

<?php

echo htmlspecialchars(
$row['description']
);

?>

</p>

<?php

}

?>

<div class="template-meta">

<span class="status primary">

<?php

echo ucfirst(
$row['template_type']
);

?>

</span>

<span
class="status <?php echo ($row['status']=='active') ? 'success' : 'danger'; ?>">

<?php

echo ucfirst(
$row['status']
);

?>

</span>

</div>

<div class="template-actions">

<a
href="view.php?id=<?php echo $row['template_id']; ?>"
class="btn btn-primary">

<i class="fa-solid fa-table"></i>

Open Schedule

</a>

<a
href="edit.php?id=<?php echo $row['template_id']; ?>"
class="btn">

<i class="fa-solid fa-pen"></i>

Edit

</a>

<a
href="delete.php?id=<?php echo $row['template_id']; ?>"
class="btn"
style="
background:#ef4444;
color:white;
"
onclick="return confirm('Delete this template?');">

<i class="fa-solid fa-trash"></i>

Delete

</a>

</div>

</div>

<?php

}

}
else{

?>

<div class="dashboard-card template-card">

<div class="card-icon">

<i class="fa-solid fa-calendar-xmark"></i>

</div>

<h3>
No Schedule Templates Found
</h3>

<p style="margin-top:12px;">

Create your first schedule template to begin timetable planning.

</p>

<div style="margin-top:20px;">

<a
href="add.php"
class="btn btn-primary">

<i class="fa-solid fa-plus"></i>

Create Template

</a>

</div>

</div>

<?php

}

?>

</div>

</div>

</div>

</div>

</div>

<script src="../assets/js/common.js"></script>

</body>

</html>