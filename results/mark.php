<?php

include('../config/auth.php');
include('../config/db.php');
require_once '../shared/services/ResultsService.php';

$service = new ResultsService($conn);

if(
!isset($_GET['id'])
||
!is_numeric($_GET['id'])
){

header('Location:index.php');
exit;

}

$result_id = (int)$_GET['id'];

$result = $service->getResultById($result_id);

if(!$result){

header('Location:index.php');
exit;

}

$students = $service->getStudentsForResult($result_id);
$existingEntries = [];
foreach($service->getEntries($result_id) as $entry){
    $existingEntries[(int)$entry['student_id']] = $entry;
}

if($_SERVER['REQUEST_METHOD']=='POST'){
if(
isset($_POST['marks'])
&&
is_array($_POST['marks'])
){

$rows = [];
foreach($_POST['marks'] as $student_id=>$marks){
    $rows[] = [
        'student_id' => (int)$student_id,
        'marks_obtained' => $marks,
        'remarks' => $_POST['remarks'][(int)$student_id] ?? '',
    ];
}

$service->bulkSaveEntries($result_id, $rows, null);
}
header(
'Location:view.php?id=' .
$result_id .
'&success=saved'
);

exit;

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
Enter Marks | MindMerge SmartCampus
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
Enter Student Marks
</h1>

<p>

<?php

echo htmlspecialchars(
$result['exam_name']
);

?>

</p>

</div>

<a
href="index.php"
class="btn">

<i class="fa-solid fa-arrow-left"></i>

Back

</a>

</div>

<div class="dashboard-grid">

<div class="dashboard-card">

<h3>
Exam
</h3>

<h2>

<?php

echo htmlspecialchars(
$result['exam_name']
);

?>

</h2>

</div>

<div class="dashboard-card">

<h3>
Maximum Marks
</h3>

<h2>

<?php

echo (int)$result['total_marks'];

?>

</h2>

</div>

<div class="dashboard-card">

<h3>
Result Status
</h3>

<h2>

<?php

echo ucfirst(
$result['status']
);

?>

</h2>

</div>
<div class="dashboard-card">

<h3>
Target Group
</h3>

<h2>

<?php

if(
!empty($result['class_name'])
&&
!empty($result['section_name'])
){

echo htmlspecialchars(
$result['class_name']
)
.
' - '
.
htmlspecialchars(
$result['section_name']
);

}
elseif(
!empty($result['class_name'])
){

echo htmlspecialchars(
$result['class_name']
);

}
else{

echo 'School Wide';

}

?>

</h2>

</div>  

</div>

<div class="dashboard-section">

<form method="POST">

<div class="table-responsive">

<table class="custom-table">

<thead>

<tr>

<th>
Student ID
</th>

<th>
Student Name
</th>

<th>
Marks
</th>

<th>
Remarks
</th>

</tr>

</thead>

<tbody>

<?php

if(count($students) > 0){

foreach($students as $student){

$existing = $existingEntries[(int)$student['id']] ?? null;

?>

<tr>

<td>

<?php

echo htmlspecialchars(
$student['student_id']
);

?>

</td>

<td>

<strong>

<?php

echo htmlspecialchars(
$student['full_name']
);

?>

</strong>

</td>

<td>

<input

type="number"

name="marks[<?php echo $student['id']; ?>]"

class="form-input"

min="0"

max="<?php echo (int)$result['total_marks']; ?>"

step="0.01"

value="<?php echo $existing['marks_obtained'] ?? ''; ?>"

required>

</td>

<td>

<input

type="text"

name="remarks[<?php echo $student['id']; ?>]"

class="form-input"

value="<?php echo htmlspecialchars($existing['remarks'] ?? ''); ?>"

placeholder="Optional">

</td>

</tr>

<?php

}

}
else{

?>

<tr>

<td
colspan="4"
style="
text-align:center;
padding:40px;
">

No students found for this result session.

</td>

</tr>

<?php

}

?>

</tbody>

</table>

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

Save Marks

</button>

<a
href="view.php?id=<?php echo $result_id; ?>"
class="btn">

View Result

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
