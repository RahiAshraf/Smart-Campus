<?php

include('../config/auth.php');
include('../config/db.php');
require_once '../shared/services/ExamService.php';
require_once '../shared/services/ResultsService.php';

$error = '';
$examService = new ExamService($conn);
$resultsService = new ResultsService($conn);

$exams = $examService->getExams();

if($_SERVER['REQUEST_METHOD'] == 'POST'){

$exam_id = (int)$_POST['exam_id'];
$exam = $examService->getExamById($exam_id);

if(!$exam){

$error =
'Selected exam not found.';

}
else{

$result_id = $resultsService->createResultFromExam($exam_id, (int) ($_SESSION['user']['id'] ?? 0));

if($result_id <= 0){

$error = 'Unable to create result session for the selected exam.';

}
else{

header(
'Location:mark.php?id=' .
$result_id
);

exit;

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
Create Result Session
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
Create Result Session
</h1>

<p>
Select an exam and begin entering marks.
</p>

</div>

<a
href="index.php"
class="btn">

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
">

<?php echo $error; ?>

</div>

<?php } ?>

<div class="dashboard-section">

<form method="POST">

<div class="form-group">

<label>
Select Exam
</label>

<select
name="exam_id"
class="form-input"
required>

<option value="">
Select Exam
</option>

<?php

foreach($exams as $exam){

?>
<option
value="<?php echo $exam['exam_id']; ?>">
<?php

$classLabel =
!empty($exam['class_name'])
? $exam['class_name']
: 'School Wide';

$sectionLabel =
!empty($exam['section_name'])
? $exam['section_name']
: 'All Sections';

echo htmlspecialchars(
$exam['exam_name']
);

echo ' | ';

echo !empty($exam['exam_date'])
? date(
'd M Y',
strtotime($exam['exam_date'])
)
: '-';

echo ' | ';

echo 'Class: ';

echo htmlspecialchars(
$classLabel
);

echo ' | Section: ';

echo htmlspecialchars(
$sectionLabel
);
?>
</option>

<?php } ?>

</select>

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

Create & Enter Marks

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

</body>

</html>
