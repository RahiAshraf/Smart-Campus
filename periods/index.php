<?php

include('../config/auth.php');
include('../config/db.php');
header("Location:../period_templates/index.php");
die;
$template_query = mysqli_query(

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

$total_templates = mysqli_fetch_assoc(

mysqli_query(

$conn,

"SELECT COUNT(*) total
FROM period_templates"

)

)['total'];

$total_blocks = mysqli_fetch_assoc(

mysqli_query(

$conn,

"SELECT COUNT(*) total
FROM periods"

)

)['total'];

$active_templates = mysqli_fetch_assoc(

mysqli_query(

$conn,

"SELECT COUNT(*) total
FROM period_templates
WHERE status='active'"

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
Schedule Management | MindMerge SmartCampus
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

padding:24px;

}

.template-card h3{

margin:16px 0 8px;

font-size:20px;

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

margin-top:20px;

}

.workflow-card{

margin-top:24px;

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
Schedule Management
</h1>

<p>
Manage school schedules, timing blocks and timetable structures.
</p>

</div>

<div
style="
display:flex;
gap:12px;
flex-wrap:wrap;
">

<a
href="../period_templates/add.php"
class="btn btn-primary">

<i class="fa-solid fa-plus"></i>

Create Schedule

</a>

<a
href="../timetables/index.php"
class="btn">

<i class="fa-solid fa-calendar-days"></i>

Timetables

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
echo "Schedule created successfully.";
}
elseif($_GET['success']=='updated'){
echo "Schedule updated successfully.";
}
elseif($_GET['success']=='deleted'){
echo "Schedule deleted successfully.";
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
<i class="fa-solid fa-clock"></i>
</div>

<h3>

<?php echo $total_blocks; ?>

</h3>

</div>

<p>
Schedule Blocks
</p>

</div>

<div class="dashboard-card stat-card">

<div class="stat-top">

<div class="card-icon">
<i class="fa-solid fa-graduation-cap"></i>
</div>

<h3>

<?php echo $active_templates; ?>

</h3>

</div>

<p>
Active Schedules
</p>

</div>

<div class="dashboard-card stat-card">

<div class="stat-top">

<div class="card-icon">
<i class="fa-solid fa-file-lines"></i>
</div>

<h3>

<?php echo $exam_templates; ?>

</h3>

</div>

<p>
Exam Schedules
</p>

</div>

</div>

<div class="dashboard-card workflow-card">

<div class="card-icon">

<i class="fa-solid fa-calendar-days"></i>

</div>

<h3 style="margin-top:16px;">
Timetable Engine
</h3>

<p style="margin-top:10px;">
Create class schedules using your timing templates and assign subjects to weekly timetable grids.
</p>

<div style="margin-top:20px;">

<a
href="../timetables/index.php"
class="btn btn-primary">

<i class="fa-solid fa-calendar-days"></i>

Open Timetables

</a>

</div>

</div>

<div class="dashboard-section">

<div class="section-header">

<h2>
Schedule Templates
</h2>

<p style="margin-top:6px;color:#6b7280;">
Choose a schedule template and manage its timing blocks.
</p>

</div>

<div class="template-grid">

<?php

if(mysqli_num_rows($template_query) > 0){

while($row = mysqli_fetch_assoc($template_query)){

?>

<div class="dashboard-card template-card">

<div class="card-icon">

<?php

if($row['template_type']=='exam'){

?>

<i class="fa-solid fa-file-pen"></i>

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

<div class="template-meta">

<span class="status primary">

<?php

echo ucfirst(
$row['template_type']
);

?>

</span>

<span class="status <?php echo ($row['status']=='active') ? 'success' : 'danger'; ?>">

<?php

echo ucfirst(
$row['status']
);

?>

</span>

</div>

<div class="template-actions">

<a
href="../period_templates/view.php?id=<?php echo $row['template_id']; ?>"
class="btn btn-primary">

<i class="fa-solid fa-table"></i>

Open Schedule

</a>

<a
href="../period_templates/edit.php?id=<?php echo $row['template_id']; ?>"
class="btn">

<i class="fa-solid fa-pen"></i>

Edit

</a>

</div>

</div>

<?php

}

}
else{

?>

<div class="dashboard-card template-card">

<h3>
No Schedule Templates Found
</h3>

<p>
Create your first schedule template to begin timetable planning.
</p>

<div style="margin-top:16px;">

<a
href="../period_templates/add.php"
class="btn btn-primary">

Create Schedule

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