<?php

include('../config/auth.php');
include('../config/db.php');

$template_id = intval($_GET['id'] ?? 0);

$template_query = mysqli_query(

$conn,

"SELECT *

FROM period_templates

WHERE template_id='$template_id'"

);

$template = mysqli_fetch_assoc(
$template_query
);

if(!$template){

header("Location:index.php");
exit();

}

$total_blocks = mysqli_fetch_assoc(

mysqli_query(

$conn,

"SELECT COUNT(*) total

FROM periods

WHERE template_id='$template_id'"

)

)['total'];

$teaching_blocks = mysqli_fetch_assoc(

mysqli_query(

$conn,

"SELECT COUNT(*) total

FROM periods

WHERE

template_id='$template_id'

AND

is_teaching_period='yes'"

)

)['total'];

$special_blocks = mysqli_fetch_assoc(

mysqli_query(

$conn,

"SELECT COUNT(*) total

FROM periods

WHERE

template_id='$template_id'

AND

period_type!='regular'"

)

)['total'];

$attendance_blocks = mysqli_fetch_assoc(

mysqli_query(

$conn,

"SELECT COUNT(*) total

FROM periods

WHERE

template_id='$template_id'

AND

attendance_allowed='yes'"

)

)['total'];

$query = mysqli_query(

$conn,

"SELECT *

FROM periods

WHERE template_id='$template_id'

ORDER BY sort_order ASC"

);

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

<?php

echo htmlspecialchars(
$template['template_name']
);

?>

| Schedule Builder

</title>

<link rel="stylesheet" href="../assets/css/global.css">
<link rel="stylesheet" href="../assets/css/layout.css">
<link rel="stylesheet" href="../assets/css/components.css">

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="../../assets/css/material-theme.css">

<style>

.template-summary{

display:grid;

grid-template-columns:
repeat(auto-fit,minmax(260px,1fr));

gap:12px;

}

.color-preview{

width:20px;
height:20px;

border-radius:5px;

display:inline-block;

border:1px solid #d1d5db;

vertical-align:middle;

margin-right:8px;

}
.info-box{

background:var(--card);

padding:18px 20px;

border-radius:12px;

border:1px solid rgba(148,163,184,.15);

display:flex;

flex-direction:column;

gap:8px;

min-height:95px;

justify-content:flex-start;

}
.info-box .status{

display:inline-flex;

width:auto;

align-self:flex-start;

}

.info-title{

font-size:14px;

font-weight:600;

color:var(--muted);

margin-bottom:6px;

}

.info-value{

font-size:16px;

font-weight:600;

color:var(--text);

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

<?php

echo htmlspecialchars(
$template['template_name']
);

?>

</h1>

<p>

<?php

echo htmlspecialchars(
$template['template_code']
);

?>

•

<?php

echo ucfirst(
$template['template_type']
);

?>

Schedule

</p>

</div>

<div
style="
display:flex;
gap:12px;
flex-wrap:wrap;
">

<a
href="../periods/add.php?template_id=<?php echo $template_id; ?>"
class="btn btn-primary">

<i class="fa-solid fa-plus"></i>

Add Period

</a>

<a
href="edit.php?id=<?php echo $template_id; ?>"
class="btn btn-primary">

<i class="fa-solid fa-pen"></i>

Edit Schedule

</a>

<a
href="../timetables/index.php"
class="btn btn-primary">

<i class="fa-solid fa-calendar-days"></i>

Open Timetables

</a>

<a
href="index.php"
class="btn">

<i class="fa-solid fa-arrow-left"></i>

Back

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

if($_GET['success'] == 'period_added'){

echo "Period added successfully.";

}
elseif($_GET['success'] == 'period_deleted'){

echo "Period deleted successfully.";

}
elseif($_GET['success'] == 'period_updated'){

echo "Period updated successfully.";

}

?>

</div>

<?php } ?>

<div class="dashboard-section">

<div class="section-header">

<h2>
Schedule Information
</h2>

</div>

<div class="template-summary">

<div
class="info-box">
<div class="info-title">
    Template Code
</div>

<div class="info-value">
    <?php echo htmlspecialchars($template['template_code']); ?>
</div>

</div>

<div
class="info-box">

<div class="info-title">
    Template Type
</div>

<div class="info-value">
    <?php echo ucfirst($template['template_type']); ?>
</div>

</div>

<div
class="info-box"
>
<div class="info-title">
    Status
</div>

<span
class="status <?php echo ($template['status']=='active') ? 'success' : 'danger'; ?>">
<?php echo ucfirst($template['status']); ?>
</span>

</div>

</div>

<?php if(!empty($template['description'])){ ?>

<div style="margin-top:20px;">

<div
class="info-box">

<strong>
Description
</strong>


<?php

echo nl2br(
htmlspecialchars(
$template['description']
)
);

?>

</div>

</div>

<?php } ?>

</div>

<div class="dashboard-section">

<div class="section-header">

<h2>
Schedule Statistics
</h2>

</div>

<div
class="dashboard-grid"
style="
margin-top:12px;
">
<div
class="dashboard-card stat-card"
style="
padding:20px;
border:1px solid rgba(148,163,184,.15);
">

<div class="stat-top">

<div class="card-icon">

<i class="fa-solid fa-clock"></i>

</div>

<h3>

<?php echo $total_blocks; ?>

</h3>

</div>

<p>
Total Periods
</p>

</div>

<div class="dashboard-card stat-card">

<div class="stat-top">

<div class="card-icon">

<i class="fa-solid fa-book"></i>

</div>

<h3>

<?php echo $teaching_blocks; ?>

</h3>

</div>

<p>
Teaching Periods
</p>

</div>

<div class="dashboard-card stat-card">

<div class="stat-top">

<div class="card-icon">

<i class="fa-solid fa-star"></i>

</div>

<h3>

<?php echo $special_blocks; ?>

</h3>

</div>

<p>
Special Periods
</p>

</div>

<div class="dashboard-card stat-card">

<div class="stat-top">

<div class="card-icon">

<i class="fa-solid fa-user-check"></i>

</div>

<h3>

<?php echo $attendance_blocks; ?>

</h3>

</div>

<p>
Attendance Enabled
</p>

</div>

</div>

</div>

<div class="dashboard-section">

<div class="section-header">

<h2>
Schedule Periods
</h2>

</div>

<div class="table-responsive">

<table class="custom-table">

<thead>

<tr>

<th>Order</th>
<th>Period Name</th>
<th>Start Time</th>
<th>End Time</th>
<th>Type</th>
<th>Attendance</th>
<th>Teaching</th>
<th>Room</th>
<th>Color</th>
<th>Status</th>
<th>Actions</th>

</tr>

</thead>

<tbody>
    <?php

if(mysqli_num_rows($query) > 0){

while($row = mysqli_fetch_assoc($query)){

?>

<tr>

<td>

<?php echo $row['sort_order']; ?>

</td>

<td>

<strong>

<?php

echo htmlspecialchars(
$row['period_name']
);

?>

</strong>

<?php

if(!empty($row['notes'])){

?>

<br>

<small
style="
color:#6b7280;
">

<?php

echo htmlspecialchars(
$row['notes']
);

?>

</small>

<?php

}

?>

</td>

<td>

<?php

echo date(
'h:i A',
strtotime(
$row['start_time']
)
);

?>

</td>

<td>

<?php

echo date(
'h:i A',
strtotime(
$row['end_time']
)
);

?>

</td>

<td>

<span class="status primary">

<?php

echo ucfirst(
$row['period_type']
);

?>

</span>

</td>

<td>

<span
class="status <?php echo ($row['attendance_allowed']=='yes') ? 'success' : 'danger'; ?>">

<?php

echo ucfirst(
$row['attendance_allowed']
);

?>

</span>

</td>

<td>

<span
class="status <?php echo ($row['is_teaching_period']=='yes') ? 'success' : 'danger'; ?>">

<?php

echo ucfirst(
$row['is_teaching_period']
);

?>

</span>

</td>

<td>

<?php

echo ucfirst(
$row['room_required']
);

?>

</td>

<td>

<span
class="color-preview"

style="
background:
<?php echo htmlspecialchars($row['display_color']); ?>;
">

</span>

<?php

echo htmlspecialchars(
$row['display_color']
);

?>

</td>

<td>

<span
class="status <?php echo ($row['status']=='active') ? 'success' : 'danger'; ?>">

<?php

echo ucfirst(
$row['status']
);

?>

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
href="../periods/view.php?id=<?php echo $row['period_id']; ?>"
class="btn"
style="
background:#10b981;
color:white;
">

View

</a>

<a
href="../periods/edit.php?id=<?php echo $row['period_id']; ?>"
class="btn btn-primary">

Edit

</a>

<a
href="../periods/delete.php?id=<?php echo $row['period_id']; ?>"
class="btn"
style="
background:#ef4444;
color:white;
"
onclick="return confirm('Delete this period?');">

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

<td colspan="11" style="text-align:center;padding:40px;">

<div>

<h3 style="margin-bottom:10px;">

No Periods Added

</h3>

<p
style="
margin-bottom:20px;
color:#6b7280;
">

This schedule template does not contain any timing periods yet.

</p>

<a
href="../periods/add.php?template_id=<?php echo $template_id; ?>"
class="btn btn-primary">

<i class="fa-solid fa-plus"></i>

Add First Period

</a>

</div>

</td>

</tr>

<?php

}

?>

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