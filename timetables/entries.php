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
ON t.class_id=c.class_id

JOIN sections s
ON t.section_id=s.section_id

JOIN period_templates pt
ON t.template_id=pt.template_id

WHERE t.timetable_id='$timetable_id'"

);

$timetable = mysqli_fetch_assoc(
$timetable_query
);

if(!$timetable){

header("Location:index.php");
exit();

}

if(isset($_POST['save_entry'])){

$day_of_week = mysqli_real_escape_string(
$conn,
$_POST['day_of_week']
);

$period_id = intval(
$_POST['period_id']
);

$subject_id = intval(
$_POST['subject_id']
);

$teacher_assignment_id = intval(
$_POST['teacher_assignment_id']
);

$room_no = mysqli_real_escape_string(
$conn,
trim($_POST['room_no'])
);

$remarks = mysqli_real_escape_string(
$conn,
trim($_POST['remarks'])
);

$existing_query = mysqli_query(

$conn,

"SELECT entry_id

FROM timetable_entries

WHERE

timetable_id='$timetable_id'

AND

day_of_week='$day_of_week'

AND

period_id='$period_id'

LIMIT 1"

);

if(mysqli_num_rows($existing_query) > 0){

$existing = mysqli_fetch_assoc(
$existing_query
);

mysqli_query(

$conn,

"UPDATE timetable_entries

SET

subject_id='$subject_id',

teacher_assignment_id='$teacher_assignment_id',

room_no='$room_no',

remarks='$remarks'

WHERE entry_id='".$existing['entry_id']."'"

);

}
else{

mysqli_query(

$conn,

"INSERT INTO timetable_entries(

timetable_id,
day_of_week,
period_id,
subject_id,
teacher_assignment_id,
room_no,
remarks

)

VALUES(

'$timetable_id',
'$day_of_week',
'$period_id',
'$subject_id',
'$teacher_assignment_id',
'$room_no',
'$remarks'

)"

);

}

header(
"Location:entries.php?id=$timetable_id&success=saved"
);

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

WHERE template_id='".$timetable['template_id']."'

ORDER BY sort_order ASC"

);

$periods = [];

while($period = mysqli_fetch_assoc($period_query)){

$periods[] = $period;

}

$teacher_query = mysqli_query(

$conn,

"SELECT

ta.assignment_id,

ta.subject_id,

u.full_name,

sub.subject_name

FROM teacher_assignments ta

JOIN teachers t
ON ta.teacher_id=t.id

JOIN users u
ON t.user_id=u.id

JOIN subjects sub
ON ta.subject_id=sub.subject_id

WHERE

ta.class_id='".$timetable['class_id']."'

AND

ta.section_id='".$timetable['section_id']."'

ORDER BY

sub.subject_name ASC,
u.full_name ASC"

);

$teachers = [];

$subjects = [];

while($row = mysqli_fetch_assoc($teacher_query)){

$teachers[] = $row;

$subjects[$row['subject_id']] = [

'subject_id'   => $row['subject_id'],
'subject_name' => $row['subject_name']

];

}

$subjects = array_values($subjects);

$total_slots = 0;

foreach($periods as $period){

if($period['is_teaching_period'] == 'yes'){

$total_slots += count($days);

}

}

$assigned_slots = mysqli_fetch_assoc(

mysqli_query(

$conn,

"SELECT COUNT(*) total

FROM timetable_entries

WHERE timetable_id='$timetable_id'"

)

)['total'];

$unassigned_slots =
max(
0,
$total_slots - $assigned_slots
);

$completion = 0;

if($total_slots > 0){

$completion = round(

($assigned_slots / $total_slots) * 100

);

if($completion > 100){

$completion = 100;

}

}
?>
<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta
name="viewport"
content="width=device-width, initial-scale=1.0">

<title>
Timetable Entry Manager | MindMerge SmartCampus
</title>

<link rel="stylesheet" href="../assets/css/global.css">
<link rel="stylesheet" href="../assets/css/layout.css">
<link rel="stylesheet" href="../assets/css/components.css">

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="../../assets/css/material-theme.css">

<style>

.editor-table{

width:100%;
min-width:1400px;
border-collapse:collapse;

}

.editor-table th,
.editor-table td{

border:1px solid rgba(148,163,184,.15);

padding:12px;

vertical-align:top;

}

.editor-table th{

background:#f8fafc;
text-align:center;
font-weight:600;

}

body.dark-mode .editor-table th{

background:#081028;

}

.period-cell{

background:#f8fafc;
font-weight:600;
min-width:180px;

}

body.dark-mode .period-cell{

background:#081028;

}

.entry-card{

padding:10px;
border-radius:12px;
background:#eff6ff;
border:1px solid #bfdbfe;

}

body.dark-mode .entry-card{

background:#0f172a;
border-color:#29476d;

}

.non-teaching{

padding:24px;
text-align:center;
font-weight:700;
border-radius:12px;
background:#f3f4f6;
color:#6b7280;

}

body.dark-mode .non-teaching{

background:#111827;
color:#9ca3af;

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

|

<?php

echo ucfirst(
$timetable['template_type']
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
href="view.php?id=<?php echo $timetable_id; ?>"
class="btn btn-primary">

<i class="fa-solid fa-table"></i>

View Timetable

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

Timetable entry saved successfully.

</div>

<?php } ?>

<div class="dashboard-grid">

<div class="dashboard-card stat-card">

<div class="stat-top">

<div class="card-icon">
<i class="fa-solid fa-table-cells"></i>
</div>

<h3>

<?php echo $total_slots; ?>

</h3>

</div>

<p>
Teaching Slots
</p>

</div>

<div class="dashboard-card stat-card">

<div class="stat-top">

<div class="card-icon">
<i class="fa-solid fa-circle-check"></i>
</div>

<h3>

<?php echo $assigned_slots; ?>

</h3>

</div>

<p>
Assigned Slots
</p>

</div>

<div class="dashboard-card stat-card">

<div class="stat-top">

<div class="card-icon">
<i class="fa-solid fa-clock"></i>
</div>

<h3>

<?php echo $unassigned_slots; ?>

</h3>

</div>

<p>
Pending Slots
</p>

</div>

<div class="dashboard-card stat-card">

<div class="stat-top">

<div class="card-icon">
<i class="fa-solid fa-chart-pie"></i>
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
Weekly Timetable Editor
</h2>

<p>
Assign subjects, faculty members and rooms for every teaching period.
</p>

</div>

<div class="table-responsive">

<table class="editor-table">

<thead>

<tr>

<th>
Period
</th>

<?php

foreach($days as $day){

?>

<th>

<?php

echo ucfirst($day);

?>

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

<div
style="
margin-top:8px;
">

<span class="status primary">

<?php

echo ucfirst(
$period['period_type']
);

?>

</span>

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

if($period['is_teaching_period'] == 'no'){

?>

<div class="non-teaching">

<?php

echo strtoupper(
$period['period_type']
);

?>

</div>

<?php

}
else{

?>

<div class="entry-card">

<?php

if($entry){

?>

<div
style="
font-weight:600;
margin-bottom:6px;
">

<?php

echo htmlspecialchars(
$entry['subject_name']
);

?>

</div>

<div
style="
font-size:12px;
color:#6b7280;
margin-bottom:8px;
">

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
style="
font-size:12px;
font-weight:600;
color:#2563eb;
margin-bottom:8px;
">

Room:
<?php echo htmlspecialchars($entry['room_no']); ?>

</div>

<?php

}

if(!empty($entry['remarks'])){

?>

<div
style="
font-size:12px;
color:var(--muted);
margin-bottom:10px;
">

<?php

echo htmlspecialchars(
$entry['remarks']
);

?>

</div>

<?php

}

}

?>

<form method="POST">

<input
type="hidden"
name="day_of_week"
value="<?php echo $day; ?>">

<input
type="hidden"
name="period_id"
value="<?php echo $period['period_id']; ?>">

<div style="margin-top:10px;">

<select
name="subject_id"
class="form-select subject-select"
required>

<option value="">
Select Subject
</option>

<?php

foreach($subjects as $subject){

?>

<option

value="<?php echo $subject['subject_id']; ?>"

<?php

if(
$entry
&&
$entry['subject_id'] == $subject['subject_id']
){

echo 'selected';

}

?>

>

<?php

echo htmlspecialchars(
$subject['subject_name']
);

?>

</option>

<?php

}

?>

</select>

</div>

<div style="margin-top:10px;">

<select
name="teacher_assignment_id"
class="form-select"
required>

<option value="">
Select Teacher
</option>

<?php

foreach($teachers as $teacher){

?>

<option

value="<?php echo $teacher['assignment_id']; ?>"

data-subject="<?php echo $teacher['subject_id']; ?>"

<?php

if(
$entry
&&
$entry['teacher_assignment_id']
==
$teacher['assignment_id']
){

echo 'selected';

}

?>

>

<?php

echo htmlspecialchars(
$teacher['full_name']
);

?>

-

<?php

echo htmlspecialchars(
$teacher['subject_name']
);

?>

</option>

<?php

}

?>

</select>

</div>

<?php

if($period['room_required'] == 'yes'){

?>

<div style="margin-top:10px;">

<input

type="text"

name="room_no"

class="form-input"

placeholder="Room / Lab"

value="<?php

if($entry){

echo htmlspecialchars(
$entry['room_no']
);

}

?>">

</div>

<?php

}

?>

<div style="margin-top:10px;">

<textarea

name="remarks"

class="form-textarea"

rows="2"

placeholder="Remarks (Optional)"><?php

if($entry){

echo htmlspecialchars(
$entry['remarks']
);

}

?></textarea>

</div>

<div style="margin-top:10px;">

<button
type="submit"
name="save_entry"
class="btn btn-primary"
style="width:100%;">

<i class="fa-solid fa-floppy-disk"></i>

<?php

if($entry){

echo 'Update';

}
else{

echo 'Assign';

}

?>

</button>

</div>

</form>

</div>

<?php

}

?>

</td>

<?php

}

?>

</tr>

<?php

}

?>

</tbody>

</table>

</div>

</div>

<div class="dashboard-section">

<div class="section-header">

<h2>
Editor Notes
</h2>

</div>

<div
style="
display:grid;
grid-template-columns:
repeat(auto-fit,minmax(280px,1fr));
gap:20px;
">

<div class="dashboard-card">

<h3>
Subject Assignment
</h3>

<p>
Only subjects assigned to this class and section are displayed.
</p>

</div>

<div class="dashboard-card">

<h3>
Teacher Assignment
</h3>

<p>
Teachers are loaded from teacher assignments linked to this class and section.
</p>

</div>

<div class="dashboard-card">

<h3>
Room Allocation
</h3>

<p>
Room field appears only when the selected period requires a room.
</p>

</div>

</div>

</div>

</div>

</div>

</div>

<script src="../assets/js/common.js"></script>

</body>

</html>