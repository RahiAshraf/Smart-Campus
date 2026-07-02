<?php

include('../config/auth.php');
include('../config/db.php');
$id = intval($_GET['id'] ?? 0);

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

WHERE t.timetable_id='$id'"

);

$row = mysqli_fetch_assoc(
$timetable_query
);

if(!$row){

header("Location:index.php");
exit();

}

$error = '';

if(isset($_POST['update_timetable'])){

$template_id = intval(
$_POST['template_id']
);

$academic_year = mysqli_real_escape_string(
$conn,
trim($_POST['academic_year'])
);

$effective_from =
!empty($_POST['effective_from'])
? $_POST['effective_from']
: NULL;

$effective_to =
!empty($_POST['effective_to'])
? $_POST['effective_to']
: NULL;

$status = mysqli_real_escape_string(
$conn,
$_POST['status']
);

if(

!empty($effective_from)

&&

!empty($effective_to)

&&

strtotime($effective_from)
>
strtotime($effective_to)

){

$error =
'Effective To date must be greater than Effective From date.';

}
else{

$duplicate_check = mysqli_query(

$conn,

"SELECT timetable_id

FROM timetables

WHERE

class_id='".$row['class_id']."'

AND

section_id='".$row['section_id']."'

AND

template_id='$template_id'

AND

timetable_id!='$id'"

);

if(mysqli_num_rows($duplicate_check) > 0){

$error =
'A timetable using this template already exists for this class and section.';

}
else{

$effective_from_sql =
$effective_from
? "'$effective_from'"
: "NULL";

$effective_to_sql =
$effective_to
? "'$effective_to'"
: "NULL";

mysqli_query(

$conn,

"UPDATE timetables

SET

template_id='$template_id',

academic_year='$academic_year',

status='$status',

effective_from=$effective_from_sql,

effective_to=$effective_to_sql

WHERE timetable_id='$id'"

);

header(
"Location:view.php?id=".$id."&success=updated"
);

exit();

}

}

}

$template_query = mysqli_query(

$conn,

"SELECT *

FROM period_templates

WHERE status='active'

ORDER BY template_name ASC"

);

?>
<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta
name="viewport"
content="width=device-width, initial-scale=1.0">

<title>
Edit Timetable | MindMerge SmartCampus
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
Edit Timetable
</h1>

<p>

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

|

<?php

echo htmlspecialchars(
$row['template_name']
);

?>

</p>

</div>

<a
href="view.php?id=<?php echo $id; ?>"
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
padding:14px 18px;
border-radius:14px;
margin-bottom:20px;
">

<?php echo $error; ?>

</div>

<?php } ?>

<div class="dashboard-section">

<div class="section-header">

<h2>
Timetable Details
</h2>

</div>

<form method="POST">

<div class="form-grid">

<div class="form-group">

<label class="form-label">
Class
</label>

<input
type="text"
class="form-input"
value="<?php echo htmlspecialchars($row['class_name']); ?>"
readonly>

</div>

<div class="form-group">

<label class="form-label">
Section
</label>

<input
type="text"
class="form-input"
value="<?php echo htmlspecialchars($row['section_name']); ?>"
readonly>

</div>

</div>

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

while($template = mysqli_fetch_assoc($template_query)){

?>

<option

value="<?php echo $template['template_id']; ?>"

<?php

if(
$template['template_id']
==
$row['template_id']
){

echo 'selected';

}

?>

>

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

 (

<?php

echo ucfirst(
$template['template_type']
);

?>

)

</option>

<?php

}

?>

</select>

</div>

<div class="form-group">

<label class="form-label">
Academic Year
</label>

<input
type="text"
name="academic_year"
class="form-input"
value="<?php echo htmlspecialchars($row['academic_year']); ?>"
required>

</div>

</div>

<div class="form-grid">

<div class="form-group">

<label class="form-label">
Effective From
</label>

<input
type="date"
name="effective_from"
class="form-input"
value="<?php echo $row['effective_from']; ?>">

</div>

<div class="form-group">

<label class="form-label">
Effective To
</label>

<input
type="date"
name="effective_to"
class="form-input"
value="<?php echo $row['effective_to']; ?>">

</div>

</div>

<div class="form-group">

<label class="form-label">
Status
</label>

<select
name="status"
class="form-select">

<option
value="active"
<?php if($row['status']=='active') echo 'selected'; ?>>
Active
</option>

<option
value="inactive"
<?php if($row['status']=='inactive') echo 'selected'; ?>>
Inactive
</option>

</select>

</div>
<div
style="
display:flex;
gap:12px;
flex-wrap:wrap;
margin-top:24px;
">

<button
type="submit"
name="update_timetable"
class="btn btn-primary">

<i class="fa-solid fa-floppy-disk"></i>

Update Timetable

</button>

<a
href="view.php?id=<?php echo $id; ?>"
class="btn">

Cancel

</a>

</div>

</form>

</div>

</div>

</div>

<script src="../assets/js/common.js"></script>

</body>

</html>