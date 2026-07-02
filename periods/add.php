
<?php

include('../config/auth.php');
include('../config/db.php');

$error = '';

$templates = mysqli_query(

$conn,

"SELECT *

FROM period_templates

WHERE status='active'

ORDER BY template_name ASC"

);

if(isset($_POST['add_period'])){

$template_id = intval($_POST['template_id']);

$period_name = mysqli_real_escape_string(
$conn,
trim($_POST['period_name'])
);

$start_time = mysqli_real_escape_string(
$conn,
$_POST['start_time']
);

$end_time = mysqli_real_escape_string(
$conn,
$_POST['end_time']
);

$period_type = mysqli_real_escape_string(
$conn,
$_POST['period_type']
);

$attendance_allowed =
mysqli_real_escape_string(
$conn,
$_POST['attendance_allowed']
);


$is_teaching_period =
mysqli_real_escape_string(
$conn,
$_POST['is_teaching_period']
);

$display_color = mysqli_real_escape_string(
$conn,
$_POST['display_color']
);

$room_required = mysqli_real_escape_string(
$conn,
$_POST['room_required']
);

$notes = mysqli_real_escape_string(
$conn,
trim($_POST['notes'])
);

$sort_order = intval($_POST['sort_order']);

$status = mysqli_real_escape_string(
$conn,
$_POST['status']
);

if(strtotime($start_time) >= strtotime($end_time)){

$error =
'End time must be greater than start time.';

}
else{

$check = mysqli_query(

$conn,

"SELECT *

FROM periods

WHERE

template_id='$template_id'

AND

sort_order='$sort_order'"

);

if(mysqli_num_rows($check)>0){

$error =
'Sort order already exists in this schedule.';

}
else{

mysqli_query(

$conn,

"INSERT INTO periods(

template_id,
period_name,
start_time,
end_time,
period_type,
attendance_allowed,
is_teaching_period,
display_color,
room_required,
notes,
sort_order,
status

)

VALUES(

'$template_id',
'$period_name',
'$start_time',
'$end_time',
'$period_type',
'$attendance_allowed',
'$is_teaching_period',
'$display_color',
'$room_required',
'$notes',
'$sort_order',
'$status'

)"

);

header(
"Location:index.php?success=added"
);

exit();

}

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
Add Period | MindMerge SmartCampus
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
Add Period
</h1>

<p>
Create a new academic period.
</p>

</div>

<a
href="index.php"
class="btn">

<i class="fa-solid fa-arrow-left"></i>

Back

</a>

</div>

<?php if($error!=''){ ?>

<div
style="
background:#fee2e2;
color:#991b1b;
padding:14px;
border-radius:12px;
margin-bottom:20px;
">

<?php echo $error; ?>

</div>

<?php } ?>

<div class="dashboard-section">

<form method="POST">

<div class="form-grid">

<div class="form-group">

<label class="form-label">
Schedule Template
</label>

<select
name="template_id"
class="form-select"
required>

<option value="">
Select Schedule
</option>

<?php

mysqli_data_seek($templates,0);

while($template = mysqli_fetch_assoc($templates)){

?>

<option
value="<?php echo $template['template_id']; ?>">

<?php

echo htmlspecialchars(
$template['template_code']
);

?>

-

<?php

echo htmlspecialchars(
$template['template_name']
);

?>

</option>

<?php } ?>

</select>

</div>

<div class="form-group">

<label class="form-label">
Period Name
</label>

<input
type="text"
name="period_name"
class="form-input"
placeholder="P1"
required>

</div>

</div>

<div class="form-grid">

<div class="form-group">

<label class="form-label">
Start Time
</label>

<input
type="time"
name="start_time"
class="form-input"
required>

</div>

<div class="form-group">

<label class="form-label">
End Time
</label>

<input
type="time"
name="end_time"
class="form-input"
required>

</div>

</div>

<div class="form-grid">

<div class="form-group">

<label class="form-label">
Period Type
</label>

<select
name="period_type"
class="form-select">

<option value="regular">
Regular
</option>

<option value="break">
Break
</option>

<option value="lunch">
Lunch
</option>

<option value="lab">
Lab
</option>

<option value="exam">
Exam
</option>

<option value="activity">
Activity
</option>

</select>

</div>
<div class="form-group">

<label class="form-label">
Attendance Allowed
</label>

<select
name="attendance_allowed"
class="form-select">

<option value="yes">
Yes
</option>

<option value="no">
No
</option>

</select>

</div>
<div class="form-group">

<label class="form-label">
Teaching Period
</label>

<select
name="is_teaching_period"
class="form-select">

<option value="yes">
Yes
</option>

<option value="no">
No
</option>

</select>

</div>

</div>
<div class="form-group">

<label class="form-label">
Display Color
</label>

<div
style="
display:flex;
align-items:center;
gap:12px;
">

<input
type="color"
name="display_color"
id="display_color"
value="#3b82f6"
style="
width:60px;
height:45px;
padding:0;
border:none;
background:none;
cursor:pointer;
">

<input
type="text"
id="color_value"
value="#3b82f6"
readonly
class="form-input">

</div>

</div>

</div>

<div class="form-grid">

<div class="form-group">

<label class="form-label">
Room Required
</label>

<select
name="room_required"
class="form-select">

<option value="yes">
Yes
</option>

<option value="no">
No
</option>

</select>

</div>

<div class="form-group">

<label class="form-label">
Sort Order
</label>

<input
type="number"
name="sort_order"
class="form-input"
min="1"
required>

</div>

</div>

<div class="form-group">

<label class="form-label">
Notes
</label>

<textarea
name="notes"
class="form-textarea"
rows="4"></textarea>

</div>

<div class="form-group">

<label class="form-label">
Status
</label>

<select
name="status"
class="form-select">

<option value="active">
Active
</option>

<option value="inactive">
Inactive
</option>

</select>

</div>

<div
style="
display:flex;
gap:12px;
flex-wrap:wrap;
">

<button
type="submit"
name="add_period"
class="btn btn-primary">

<i class="fa-solid fa-floppy-disk"></i>

Save Period

</button>

<a
href="index.php"
class="btn">

Cancel

</a>

</div>

</form>

</div>

</div>

</div>

</div>

<script src="../assets/js/common.js"></script>
<script>

document
.getElementById('display_color')
.addEventListener(

'input',

function(){

document
.getElementById('color_value')
.value = this.value;

}

);

</script>
</body>
</html>
