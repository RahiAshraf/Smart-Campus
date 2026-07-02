<?php

include('../config/auth.php');
include('../config/db.php');

$search = '';

$where = '';

if(isset($_GET['search']) && trim($_GET['search']) != ''){

$search = mysqli_real_escape_string(
$conn,
trim($_GET['search'])
);

$where = "

WHERE

c.class_name LIKE '%$search%'

OR

s.section_name LIKE '%$search%'

OR

pt.template_name LIKE '%$search%'

OR

pt.template_type LIKE '%$search%'

OR

t.academic_year LIKE '%$search%'

";

}

$query = mysqli_query(

$conn,

"SELECT

t.*,

c.class_name,

s.section_name,

pt.template_name,

pt.template_code,

pt.template_type

FROM timetables t

JOIN classes c
ON t.class_id = c.class_id

JOIN sections s
ON t.section_id = s.section_id

JOIN period_templates pt
ON t.template_id = pt.template_id

$where

ORDER BY

c.class_name ASC,
s.section_name ASC"

);

$total_timetables = mysqli_fetch_assoc(

mysqli_query(

$conn,

"SELECT COUNT(*) total
FROM timetables"

)

)['total'];

$active_timetables = mysqli_fetch_assoc(

mysqli_query(

$conn,

"SELECT COUNT(*) total
FROM timetables
WHERE status='active'"

)

)['total'];

$total_classes = mysqli_fetch_assoc(

mysqli_query(

$conn,

"SELECT COUNT(DISTINCT class_id) total
FROM timetables"

)

)['total'];

$total_templates = mysqli_fetch_assoc(

mysqli_query(

$conn,

"SELECT COUNT(DISTINCT template_id) total
FROM timetables"

)

)['total'];

$regular_count = mysqli_fetch_assoc(

mysqli_query(

$conn,

"SELECT COUNT(*) total

FROM timetables t

JOIN period_templates pt
ON t.template_id=pt.template_id

WHERE pt.template_type='regular'"

)

)['total'];

$exam_count = mysqli_fetch_assoc(

mysqli_query(

$conn,

"SELECT COUNT(*) total

FROM timetables t

JOIN period_templates pt
ON t.template_id=pt.template_id

WHERE pt.template_type='exam'"

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
Timetables | MindMerge SmartCampus
</title>

<link rel="stylesheet" href="../assets/css/global.css">
<link rel="stylesheet" href="../assets/css/layout.css">
<link rel="stylesheet" href="../assets/css/components.css">

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="../../assets/css/material-theme.css">

<style>

.timetable-card{

background:var(--card);

border-radius:18px;

padding:24px;

box-shadow:var(--shadow);

border:1px solid rgba(148,163,184,.15);

transition:.3s;

}

.timetable-card:hover{

transform:translateY(-4px);

}

.timetable-meta{

display:flex;

gap:10px;

flex-wrap:wrap;

margin-top:12px;

margin-bottom:18px;

}

.meta-badge{

padding:6px 12px;

border-radius:30px;

font-size:13px;

font-weight:600;

background:#eef2ff;

color:#3730a3;

}

body.dark-mode .meta-badge{

background:#1e293b;

color:#cbd5e1;

}

body.dark-mode .timetable-card{

background:#111c35;

border-color:#29476d;

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
Timetables
</h1>

<p>
Manage class and section schedules.
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

Create Timetable

</a>

</div>

</div>
<?php

if(isset($_GET['success'])){

?>

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

if($_GET['success']=='created'){

echo "Timetable created successfully.";

}
elseif($_GET['success']=='updated'){

echo "Timetable updated successfully.";

}
elseif($_GET['success']=='deleted'){

echo "Timetable deleted successfully.";

}

?>

</div>

<?php

}

elseif(isset($_GET['error'])){

?>

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

if($_GET['error']=='delete_failed'){

echo "Unable to delete timetable.";

}

?>

</div>

<?php

}

?>
<div class="dashboard-grid">

<div class="dashboard-card stat-card">

<div class="stat-top">

<div class="card-icon">
<i class="fa-solid fa-calendar-days"></i>
</div>

<h3>
<?php echo $total_timetables; ?>
</h3>

</div>

<p>
Total Timetables
</p>

</div>

<div class="dashboard-card stat-card">

<div class="stat-top">

<div class="card-icon">
<i class="fa-solid fa-circle-check"></i>
</div>

<h3>
<?php echo $active_timetables; ?>
</h3>

</div>

<p>
Active Timetables
</p>

</div>

<div class="dashboard-card stat-card">

<div class="stat-top">

<div class="card-icon">
<i class="fa-solid fa-school"></i>
</div>

<h3>
<?php echo $total_classes; ?>
</h3>

</div>

<p>
Classes Covered
</p>

</div>

<div class="dashboard-card stat-card">

<div class="stat-top">

<div class="card-icon">
<i class="fa-solid fa-layer-group"></i>
</div>

<h3>
<?php echo $total_templates; ?>
</h3>

</div>

<p>
Schedules Used
</p>

</div>

<div class="dashboard-card stat-card">

<div class="stat-top">

<div class="card-icon">
<i class="fa-solid fa-calendar-week"></i>
</div>

<h3>
<?php echo $regular_count; ?>
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
<?php echo $exam_count; ?>
</h3>

</div>

<p>
Exam Schedules
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

placeholder="Search Class, Section, Schedule, Academic Year"

value="<?php echo htmlspecialchars($search); ?>"

style="
flex:1;
min-width:260px;
">

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
Available Timetables
</h2>

</div>
<!-- this -->
 <?php

if(mysqli_num_rows($query) > 0){

?>

<div
style="
display:grid;
grid-template-columns:
repeat(auto-fill,minmax(360px,1fr));
gap:20px;
">

<?php

while($row = mysqli_fetch_assoc($query)){

?>

<div class="timetable-card">

<div
style="
display:flex;
justify-content:space-between;
align-items:flex-start;
gap:12px;
">

<div>

<h3>

<?php

echo htmlspecialchars(
$row['class_name']
);

?>

-

<?php

echo htmlspecialchars(
$row['section_name']
);

?>

</h3>

<div class="timetable-meta">

<span class="meta-badge">

<?php

echo ucfirst(
$row['template_type']
);

?>

Schedule

</span>

<span class="meta-badge">

<?php

echo htmlspecialchars(
$row['template_name']
);

?>

</span>

</div>

<p
style="
margin-top:4px;
">

Academic Year:

<strong>

<?php

echo htmlspecialchars(
$row['academic_year']
);

?>

</strong>

</p>

</div>

<span
class="status <?php echo ($row['status']=='active') ? 'success' : 'danger'; ?>">

<?php

echo ucfirst(
$row['status']
);

?>

</span>

</div>

<div
style="
margin-top:14px;
padding-top:14px;
border-top:1px solid rgba(148,163,184,.15);
font-size:13px;
color:var(--muted);
line-height:1.8;
">

<div>

<strong>
Schedule:
</strong>

<?php

echo htmlspecialchars(
$row['template_code']
);

?>

-

<?php

echo htmlspecialchars(
$row['template_name']
);

?>

</div>

<div>

<strong>
Valid From:
</strong>

<?php

echo !empty($row['effective_from'])

? date(
'd M Y',
strtotime(
$row['effective_from']
)
)

: 'Immediate';

?>

</div>

<div>

<strong>
Valid To:
</strong>

<?php

echo !empty($row['effective_to'])

? date(
'd M Y',
strtotime(
$row['effective_to']
)
)

: 'Until Changed';

?>

</div>

</div>

<div
style="
display:flex;
gap:10px;
flex-wrap:wrap;
margin-top:20px;
">

<a
href="view.php?id=<?php echo $row['timetable_id']; ?>"
class="btn"
style="
background:#10b981;
color:white;
">

<i class="fa-solid fa-table"></i>

View

</a>

<a
href="entries.php?id=<?php echo $row['timetable_id']; ?>"
class="btn btn-primary">

<i class="fa-solid fa-pen-to-square"></i>

Entries

</a>

<a
href="edit.php?id=<?php echo $row['timetable_id']; ?>"
class="btn">

<i class="fa-solid fa-gear"></i>

Edit

</a>

<a
href="delete.php?id=<?php echo $row['timetable_id']; ?>"
class="btn"
style="
background:#ef4444;
color:white;
"
onclick="return confirm('Delete this timetable?');">

<i class="fa-solid fa-trash"></i>

Delete

</a>

</div>

</div>

<?php

}

?>

</div>
<?php

}
else{

?>

<div
style="
text-align:center;
padding:60px 20px;
">

<i
class="fa-solid fa-calendar-xmark"
style="
font-size:60px;
color:#94a3b8;
margin-bottom:20px;
"></i>

<h3>

No Timetables Found

</h3>

<p
style="
margin-top:10px;
margin-bottom:24px;
color:var(--muted);
">

No timetable records match your search criteria.

</p>

<a
href="add.php"
class="btn btn-primary">

<i class="fa-solid fa-plus"></i>

Create Timetable

</a>

</div>

<?php

}

?>

</div>

</div>

</div>

</div>

<script src="../assets/js/common.js"></script>

</body>

</html>