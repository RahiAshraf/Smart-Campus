
<?php

include('../config/auth.php');
include('../config/db.php');

$id = intval($_GET['id']);

$period = mysqli_query(

$conn,

"SELECT *

FROM periods

WHERE period_id='$id'"

);

$row = mysqli_fetch_assoc($period);

if(!$row){

header("Location:index.php");
exit();

}

$error = '';

$templates = mysqli_query(

$conn,

"SELECT *

FROM period_templates

WHERE status='active'

ORDER BY template_name ASC"

);

if(isset($_POST['update_period'])){

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

sort_order='$sort_order'

AND

period_id!='$id'"

);

if(mysqli_num_rows($check)>0){

$error =
'Sort order already exists in this schedule.';

}
else{

mysqli_query(

$conn,

"UPDATE periods

SET

template_id='$template_id',
period_name='$period_name',
start_time='$start_time',
end_time='$end_time',
period_type='$period_type',
attendance_allowed='$attendance_allowed',
is_teaching_period='$is_teaching_period',
display_color='$display_color',
room_required='$room_required',
notes='$notes',
sort_order='$sort_order',
status='$status'

WHERE period_id='$id'"

);

header(
"Location:../period_templates/view.php?id=".$template_id."&success=period_updated"
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
Edit Period | MindMerge SmartCampus
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
Edit Period
</h1>

<p>
Update academic period information.
</p>

</div>

<a
href="../period_templates/view.php?id=<?php echo $row['template_id']; ?>"
class="btn">

<i class="fa-solid fa-arrow-left"></i>

Back To Schedule

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

<?php

while($template = mysqli_fetch_assoc($templates)){

?>

<option

value="<?php echo $template['template_id']; ?>"

<?php

if($row['template_id']==$template['template_id']){

echo 'selected';

}

?>>

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
value="<?php echo htmlspecialchars($row['period_name']); ?>"
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
value="<?php echo $row['start_time']; ?>"
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
value="<?php echo $row['end_time']; ?>"
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

<option value="regular" <?php if($row['period_type']=='regular') echo 'selected'; ?>>
Regular
</option>

<option value="break" <?php if($row['period_type']=='break') echo 'selected'; ?>>
Break
</option>

<option value="lunch" <?php if($row['period_type']=='lunch') echo 'selected'; ?>>
Lunch
</option>

<option value="lab" <?php if($row['period_type']=='lab') echo 'selected'; ?>>
Lab
</option>

<option value="exam" <?php if($row['period_type']=='exam') echo 'selected'; ?>>
Exam
</option>

<option value="activity" <?php if($row['period_type']=='activity') echo 'selected'; ?>>
Activity
</option>

</select>

</div>

<div class="form-group">

<div class="form-group">

<label class="form-label">
Attendance Allowed
</label>

<select
name="attendance_allowed"
class="form-select">

<option value="yes"
<?php
if($row['attendance_allowed']=='yes')
echo 'selected';
?>>

Yes

</option>

<option value="no"
<?php
if($row['attendance_allowed']=='no')
echo 'selected';
?>>

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

<option value="yes"
<?php
if($row['is_teaching_period']=='yes')
echo 'selected';
?>>

Yes

</option>

<option value="no"
<?php
if($row['is_teaching_period']=='no')
echo 'selected';
?>>

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
value="<?php echo htmlspecialchars($row['display_color']); ?>"
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
value="<?php echo htmlspecialchars($row['display_color']); ?>"
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

<option value="yes" <?php if($row['room_required']=='yes') echo 'selected'; ?>>
Yes
</option>

<option value="no" <?php if($row['room_required']=='no') echo 'selected'; ?>>
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
value="<?php echo $row['sort_order']; ?>"
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
rows="4"><?php echo htmlspecialchars($row['notes']); ?></textarea>

</div>

<div class="form-group">

<label class="form-label">
Status
</label>

<select
name="status"
class="form-select">

<option value="active" <?php if($row['status']=='active') echo 'selected'; ?>>
Active
</option>

<option value="inactive" <?php if($row['status']=='inactive') echo 'selected'; ?>>
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
name="update_period"
class="btn btn-primary">

<i class="fa-solid fa-floppy-disk"></i>

Update Period

</button>

<a
href="../period_templates/view.php?id=<?php echo $row['template_id']; ?>"
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

const colorPicker =
document.getElementById('display_color');

const colorValue =
document.getElementById('color_value');

if(colorPicker && colorValue){

colorPicker.addEventListener(

'input',

function(){

colorValue.value =
this.value;

}

);

}

</script>
</body>
</html>
