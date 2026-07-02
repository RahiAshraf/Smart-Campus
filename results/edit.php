<?php

include('../config/auth.php');
include('../config/db.php');

$error = '';

if(
!isset($_GET['id'])
||
!is_numeric($_GET['id'])
){

header('Location:index.php');
exit;

}

$result_id = (int)$_GET['id'];

$query = mysqli_query(

$conn,

"SELECT *

FROM results

WHERE result_id='$result_id'

LIMIT 1"

);

if(mysqli_num_rows($query) == 0){

header('Location:index.php?error=not_found');
exit;

}

$result = mysqli_fetch_assoc($query);

$exams = mysqli_query(

$conn,

"SELECT

exam_id,
exam_name

FROM exams

ORDER BY exam_name ASC"

);

if($_SERVER['REQUEST_METHOD'] == 'POST'){

$exam_id = (int)$_POST['exam_id'];

$status = mysqli_real_escape_string(
$conn,
$_POST['status']
);

$published_at = !empty($_POST['published_at'])

? "'".$_POST['published_at']."'"
: "NULL";

$examQuery = mysqli_query(

$conn,

"SELECT

class_id,
section_id

FROM exams

WHERE exam_id='$exam_id'

LIMIT 1"

);

if(mysqli_num_rows($examQuery) == 0){

$error = 'Selected exam not found.';

}
else{

$exam = mysqli_fetch_assoc($examQuery);

mysqli_query(

$conn,

"UPDATE results

SET

exam_id='$exam_id',
class_id='".$exam['class_id']."',
section_id='".$exam['section_id']."',
status='$status',
published_at=$published_at

WHERE result_id='$result_id'"

);

header(
'Location:index.php?success=updated'
);

exit;

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
Edit Result
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
Edit Result
</h1>

<p>
Update result session information.
</p>

</div>

<a
href="index.php"
class="btn">

<i class="fa-solid fa-arrow-left"></i>

Back

</a>

</div>

<?php if(!empty($error)){ ?>

<div
style="
background:#fee2e2;
color:#991b1b;
padding:14px 18px;
border-radius:14px;
margin-bottom:20px;
font-weight:500;
">

<?php echo $error; ?>

</div>

<?php } ?>

<div class="dashboard-section">

<form method="POST">

<div class="form-grid">

<div class="form-group">

<label>
Exam
</label>

<select
name="exam_id"
class="form-input"
required>

<?php

while($exam=mysqli_fetch_assoc($exams)){

?>

<option

value="<?php echo $exam['exam_id']; ?>"

<?php

echo ($exam['exam_id']==$result['exam_id'])

? 'selected'
: '';

?>

>

<?php

echo htmlspecialchars(
$exam['exam_name']
);

?>

</option>

<?php } ?>

</select>

</div>

<div class="form-group">

<label>
Status
</label>

<select
name="status"
class="form-input"
required>

<option
value="draft"

<?php
echo ($result['status']=='draft')
? 'selected'
: '';
?>

>

Draft

</option>

<option
value="published"

<?php
echo ($result['status']=='published')
? 'selected'
: '';
?>

>

Published

</option>

</select>

</div>

<div class="form-group">

<label>
Published Date
</label>

<input

type="datetime-local"

name="published_at"

class="form-input"

value="<?php

echo !empty($result['published_at'])

? date(
'Y-m-d\TH:i',
strtotime($result['published_at'])
)

: '';

?>">

</div>

</div>

<div
style="
margin-top:20px;
display:flex;
gap:12px;
flex-wrap:wrap;
">

<button
type="submit"
class="btn btn-primary">

<i class="fa-solid fa-floppy-disk"></i>

Update Result

</button>

<a
href="mark.php?id=<?php echo $result_id; ?>"
class="btn">

Edit Marks

</a>

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

</body>

</html>