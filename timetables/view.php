<?php

include('../config/auth.php');
include('../config/db.php');

$timetable_id = intval($_GET['id'] ?? 0);

$timetable_query = mysqli_query(

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

WHERE t.timetable_id='$timetable_id'"

);

$timetable = mysqli_fetch_assoc(
$timetable_query
);

if(!$timetable){

header("Location:index.php");
exit();

}

$days = [

'monday',
'tuesday',
'wednesday',
'thursday',
'friday',
'saturday'

];

$period_query = mysqli_query(

$conn,

"SELECT *

FROM periods

WHERE

template_id='".$timetable['template_id']."'

AND

status='active'

ORDER BY sort_order ASC"

);

$periods = [];

while($period = mysqli_fetch_assoc($period_query)){

$periods[] = $period;

}

$assigned_entries = mysqli_fetch_assoc(

mysqli_query(

$conn,

"SELECT COUNT(*) total

FROM timetable_entries

WHERE timetable_id='$timetable_id'"

)

)['total'];

$teaching_periods = 0;

foreach($periods as $period){

if($period['is_teaching_period']=='yes'){

$teaching_periods++;

}

}

$total_slots = $teaching_periods * count($days);

$completion = 0;

if($total_slots > 0){

$completion = round(
($assigned_entries / $total_slots) * 100
);

if($completion > 100){

$completion = 100;

}

}
?><!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta
name="viewport"
content="width=device-width, initial-scale=1.0">

<title>

<?php

echo htmlspecialchars(
$timetable['class_name']
);

?>

-

<?php

echo htmlspecialchars(
$timetable['section_name']
);

?>

| Schedule

</title>

<link rel="stylesheet" href="../assets/css/global.css">
<link rel="stylesheet" href="../assets/css/layout.css">
<link rel="stylesheet" href="../assets/css/components.css">

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="../../assets/css/material-theme.css">

<style>

.timetable-table{

width:100%;
min-width:1100px;

border-collapse:collapse;

background:var(--card);

}

.timetable-table th,
.timetable-table td{

border:1px solid rgba(148,163,184,.15);

padding:12px;

vertical-align:top;

text-align:center;

}

.timetable-table th{

background:#f8fafc;

font-weight:600;

}

body.dark-mode .timetable-table th{

background:#081028;

}

.period-cell{

background:#f8fafc;

font-weight:600;

min-width:140px;

}

body.dark-mode .period-cell{

background:#081028;

}

.subject-box{

padding:10px;

border-radius:12px;

background:#eff6ff;

border:1px solid #bfdbfe;

min-height:70px;

display:flex;

flex-direction:column;

justify-content:center;

gap:5px;

}

body.dark-mode .subject-box{

background:#0f172a;

border-color:#29476d;

}

.empty-box{

padding:10px;

border-radius:12px;

background:#f8fafc;

min-height:70px;

display:flex;

align-items:center;

justify-content:center;

color:#94a3b8;

}

body.dark-mode .empty-box{

background:#111827;

}

.non-teaching{

padding:16px;

border-radius:12px;

background:#f3f4f6;

font-weight:700;

color:#6b7280;

text-align:center;

}

body.dark-mode .non-teaching{

background:#111827;

color:#9ca3af;

}

.teacher-name{

font-size:12px;

color:var(--muted);

}

.room-name{

font-size:12px;

font-weight:600;

color:#2563eb;

}

.info-grid{

display:grid;

grid-template-columns:
repeat(auto-fit,minmax(220px,1fr));

gap:20px;

}

.info-card{

background:var(--card);

padding:20px;

border-radius:16px;

border:1px solid rgba(148,163,184,.15);

}

body.dark-mode .info-card{

background:#081028;

border-color:#29476d;

}

.info-card h4{

margin-bottom:10px;

font-size:14px;

color:var(--muted);

}

.status-alert{

padding:16px 18px;

border-radius:14px;

font-weight:600;

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
$timetable['class_name']
);

?>

-

<?php

echo htmlspecialchars(
$timetable['section_name']
);

?>

</h1>

<p>

<?php

echo htmlspecialchars(
$timetable['template_code']
);

?>

-

<?php

echo htmlspecialchars(
$timetable['template_name']
);

?>

•

<?php

echo ucfirst(
$timetable['template_type']
);

?>

Schedule

•

AY:

<?php

echo htmlspecialchars(
$timetable['academic_year']
);

?>

</p>

</div>

<div
style="
display:flex;
gap:12px;
flex-wrap:wrap;
">

<a
href="entries.php?id=<?php echo $timetable_id; ?>"
class="btn btn-primary">

<i class="fa-solid fa-pen-to-square"></i>

Manage Entries

</a>

<a
href="edit.php?id=<?php echo $timetable_id; ?>"
class="btn">

<i class="fa-solid fa-gear"></i>

Edit Schedule

</a>

<a
href="index.php"
class="btn">

<i class="fa-solid fa-arrow-left"></i>

Back

</a>

</div>

</div>

<div class="dashboard-grid">

<div class="dashboard-card stat-card">

<div class="stat-top">

<div class="card-icon">

<i class="fa-solid fa-calendar-days"></i>

</div>

<h3>

<?php echo count($periods); ?>

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

<?php echo $assigned_entries; ?>

</h3>

</div>

<p>
Assigned Entries
</p>

</div>

<div class="dashboard-card stat-card">

<div class="stat-top">

<div class="card-icon">

<i class="fa-solid fa-layer-group"></i>

</div>

<h3>

<?php echo count($days); ?>

</h3>

</div>

<p>
Working Days
</p>

</div>

<div class="dashboard-card stat-card">

<div class="stat-top">

<div class="card-icon">

<i class="fa-solid fa-circle-check"></i>

</div>

<h3>

<?php echo $completion; ?>%

</h3>

</div>

<p>
Completion
</p>

</div>

</div>

<div class="dashboard-section">

<div class="section-header">

<h2>
Schedule Information
</h2>

</div>

<div class="info-grid">

<div class="info-card">

<h4>
Schedule Type
</h4>

<span class="status primary">

<?php

echo ucfirst(
$timetable['template_type']
);

?>

</span>

</div>

<div class="info-card">

<h4>
Template
</h4>

<strong>

<?php

echo htmlspecialchars(
$timetable['template_name']
);

?>

</strong>

<br>

<small>

<?php

echo htmlspecialchars(
$timetable['template_code']
);

?>

</small>

</div>

<div class="info-card">

<h4>
Academic Year
</h4>

<strong>

<?php

echo htmlspecialchars(
$timetable['academic_year']
);

?>

</strong>

</div>

<div class="info-card">

<h4>
Effective Period
</h4>

<strong>

<?php

echo !empty($timetable['effective_from'])

? date(
'd M Y',
strtotime(
$timetable['effective_from']
)
)

: 'Immediate';

?>

</strong>

<br>

<small>

To

<?php

echo !empty($timetable['effective_to'])

? date(
'd M Y',
strtotime(
$timetable['effective_to']
)
)

: 'Until Changed';

?>

</small>

</div>

<div class="info-card">

<h4>
Status
</h4>

<span
class="status <?php echo ($timetable['status']=='active') ? 'success' : 'danger'; ?>">

<?php

echo ucfirst(
$timetable['status']
);

?>

</span>

</div>

</div>

</div>

<div class="dashboard-section">

<div class="section-header">

<h2>
Weekly Timetable
</h2>

</div>

<div class="table-responsive">

<table class="timetable-table">

<thead>

<tr>

<th>
Period
</th>

<?php

foreach($days as $day){

?>

<th>

<?php echo ucfirst($day); ?>

</th>

<?php

}

?>

</tr>

</thead>

<tbody>
    <?php

foreach($periods as $period){

?>

<tr>

<td class="period-cell">
<div>

<strong>

<?php

echo htmlspecialchars(
$period['period_name']
);

?>

</strong>

</div>

<div
style="
font-size:12px;
margin-top:6px;
color:#6b7280;
">

<?php

echo date(
'h:i A',
strtotime(
$period['start_time']
)
);

?>

-

<?php

echo date(
'h:i A',
strtotime(
$period['end_time']
)
);

?>

</div>

</td>

<?php

foreach($days as $day){

$entry_query = mysqli_query(

$conn,

"SELECT

te.*,

sub.subject_name,

u.full_name

FROM timetable_entries te

JOIN subjects sub
ON te.subject_id=sub.subject_id

JOIN teacher_assignments ta
ON te.teacher_assignment_id=ta.assignment_id

JOIN teachers t
ON ta.teacher_id=t.id

JOIN users u
ON t.user_id=u.id

WHERE

te.timetable_id='$timetable_id'

AND te.day_of_week='$day'

AND te.period_id='".$period['period_id']."'

LIMIT 1"

);

$entry = mysqli_fetch_assoc(
$entry_query
);

?>

<td>

<?php

if(

$period['period_type']=='lunch'
||
$period['period_type']=='break'
||
$period['period_type']=='exam'

){

?>

<div
style="
background:<?php echo htmlspecialchars($period['display_color']); ?>20;
border:1px solid <?php echo htmlspecialchars($period['display_color']); ?>55;
color:<?php echo htmlspecialchars($period['display_color']); ?>;
font-weight:700;
padding:14px;
border-radius:12px;
text-align:center;
min-height:90px;
display:flex;
align-items:center;
justify-content:center;
">

<?php

echo htmlspecialchars(
$period['period_name']
);

?>

</div>

<?php

}
else if($period['is_teaching_period'] == 'no'){

?>

<div class="non-teaching">

<?php

echo htmlspecialchars(
$period['period_name']
);

?>

</div>

<?php

}
else if($entry){

?>

<div
class="subject-box"
style="
background:<?php echo htmlspecialchars($period['display_color']); ?>20;
border:1px solid <?php echo htmlspecialchars($period['display_color']); ?>55;
">

<div
style="
font-weight:700;
color:<?php echo htmlspecialchars($period['display_color']); ?>;
">

<?php

echo htmlspecialchars(
$entry['subject_name']
);

?>

</div>

<div class="teacher-name">

<?php

echo htmlspecialchars(
$entry['full_name']
);

?>

</div>

<?php

if(!empty($entry['room_no'])){

?>

<div
class="room-name"
style="
color:<?php echo htmlspecialchars($period['display_color']); ?>;
">

Room:

<?php

echo htmlspecialchars(
$entry['room_no']
);

?>

</div>

<?php

}

if(!empty($entry['remarks'])){

?>

<div
style="
font-size:11px;
margin-top:6px;
color:var(--muted);
">

<?php

echo htmlspecialchars(
$entry['remarks']
);

?>

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
class="empty-box"
style="
background:<?php echo htmlspecialchars($period['display_color']); ?>15;
color:<?php echo htmlspecialchars($period['display_color']); ?>;
border:1px solid <?php echo htmlspecialchars($period['display_color']); ?>25;
">

Unassigned

</div>

<?php

}

?>

</td>

<?php

} // foreach($days as $day)

?>

</tr>

<?php

} // foreach($periods as $period)

?>

</tbody>

</table>

</div>

</div>

<div class="dashboard-section">

<div class="section-header">

<h2>
Timetable Status
</h2>

</div>

<?php

if($completion < 100){

?>

<div
class="status-alert"
style="
background:#fef3c7;
color:#92400e;
">

<i class="fa-solid fa-triangle-exclamation"></i>

This timetable is incomplete.

<?php

echo max(
0,
$total_slots - $assigned_entries
);

?>

teaching slot(s) still need assignment.

</div>

<?php

}
else{

?>

<div
class="status-alert"
style="
background:#dcfce7;
color:#166534;
">

<i class="fa-solid fa-circle-check"></i>

Timetable is fully configured and ready for attendance usage.

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