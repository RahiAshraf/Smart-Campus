<?php

include('../config/auth.php');
include('../config/db.php');

$search = '';

$where = '';

if(
isset($_GET['search'])
&&
trim($_GET['search']) != ''
){

$search = mysqli_real_escape_string(
$conn,trim($_GET['search'])
);

$where = "

WHERE

e.exam_name LIKE '%$search%'

OR

c.class_name LIKE '%$search%'

OR

s.section_name LIKE '%$search%'

OR

r.status LIKE '%$search%'

";

}

$query = mysqli_query(

$conn,

"SELECT

r.*,

e.exam_name,
e.exam_date,
e.total_marks,

COALESCE(c.class_name,'School Wide') AS class_name,

COALESCE(s.section_name,'All Sections') AS section_name,

COUNT(DISTINCT rm.mark_id) AS total_students,

AVG(rm.marks_obtained) AS average_marks

FROM results r

INNER JOIN exams e
ON r.exam_id = e.exam_id
LEFT JOIN classes c
ON r.class_id = c.class_id

LEFT JOIN sections s
ON r.section_id = s.section_id

LEFT JOIN result_marks rm
ON r.result_id = rm.result_id

$where

GROUP BY

r.result_id,
e.exam_name,
e.exam_date,
e.total_marks,
c.class_name,
s.section_name

ORDER BY r.result_id DESC"

);

$total_results = mysqli_fetch_assoc(

mysqli_query(

$conn,

"SELECT COUNT(*) total
FROM results"

)

)['total'];

$draft_results = mysqli_fetch_assoc(

mysqli_query(

$conn,

"SELECT COUNT(*) total
FROM results
WHERE status='draft'"

)

)['total'];

$published_results = mysqli_fetch_assoc(

mysqli_query(

$conn,

"SELECT COUNT(*) total
FROM results
WHERE status='published'"

)

)['total'];

$total_marks_entries = mysqli_fetch_assoc(

mysqli_query(

$conn,

"SELECT COUNT(*) total
FROM result_marks"

)

)['total'];

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
Results Management | MindMerge SmartCampus
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

<link rel="stylesheet" href="../assets/css/material-theme.css">
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
Results Management
</h1>

<p>
Manage examination results and student marks.
</p>

</div>

<a
href="add.php"
class="btn btn-primary">

<i class="fa-solid fa-plus"></i>

Create Result

</a>

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

if($_GET['success']=='created'){

echo "Result session created successfully.";

}
elseif($_GET['success']=='deleted'){

echo "Result deleted successfully.";

}
elseif($_GET['success']=='published'){

echo "Result published successfully.";

}

?>

</div>

<?php } ?>

<?php if(isset($_GET['error'])){ ?>

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

if($_GET['error']=='not_found'){

echo "Result not found.";

}
elseif($_GET['error']=='delete_failed'){

echo "Unable to delete result.";

}elseif($_GET['error']=='no_marks'){

echo "Cannot publish result because no marks have been entered.";

}

?>

</div>

<?php } ?>
<!-- Statistics -->

<div class="dashboard-grid">

    <div class="dashboard-card">
        <div>
            <h3>Total Results</h3>
            <h2><?php echo $total_results; ?></h2>
        </div>
        <div class="card-icon">
            <i class="fa-solid fa-square-poll-vertical"></i>
        </div>
    </div>

    <div class="dashboard-card">
        <div>
            <h3>Draft Results</h3>
            <h2><?php echo $draft_results; ?></h2>
        </div>
        <div class="card-icon">
            <i class="fa-solid fa-file-pen"></i>
        </div>
    </div>

    <div class="dashboard-card">
        <div>
            <h3>Published Results</h3>
            <h2><?php echo $published_results; ?></h2>
        </div>
        <div class="card-icon">
            <i class="fa-solid fa-circle-check"></i>
        </div>
    </div>

    <div class="dashboard-card">
        <div>
            <h3>Marks Entries</h3>
            <h2><?php echo $total_marks_entries; ?></h2>
        </div>
        <div class="card-icon">
            <i class="fa-solid fa-clipboard-list"></i>
        </div>
    </div>

</div>

<!-- Quick Actions -->

<div class="dashboard-section">

<div class="section-header">

<h2>
Quick Actions
</h2>

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

Create Result

</a>

<a
href="report.php"
class="btn">

<i class="fa-solid fa-chart-column"></i>

Result Reports

</a>

<a
href="../exams/index.php"
class="btn">

<i class="fa-solid fa-file-signature"></i>

Exams

</a>

</div>

</div>

<!-- Search -->

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

placeholder="Search Exam, Class, Section or Status"

value="<?php echo htmlspecialchars($search); ?>"

style="flex:1;min-width:250px;">

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

<!-- Results Table -->

<div class="dashboard-section">

<div class="section-header">

<h2>
All Results
</h2>

</div>

<div class="table-responsive">

<table class="custom-table">

<thead>

<tr>

<th data-sort="true">
Exam
</th>

<th data-sort="true">
Class
</th>

<th data-sort="true">
Section
</th>

<th data-sort="true">
Students
</th>

<th data-sort="true">
Average
</th>

<th data-sort="true">
Status
</th>

<th data-sort="true">
Published
</th>

<th data-sort="false">
Actions
</th>

</tr>

</thead>

<tbody>

<?php

if(mysqli_num_rows($query) > 0){

while($row = mysqli_fetch_assoc($query)){

?>

<tr>

<td>

<strong>

<?php

echo htmlspecialchars(
$row['exam_name']
);

?>

</strong>

<br>

<small>

<?php

echo !empty($row['exam_date'])

? date(
'd M Y',
strtotime($row['exam_date'])
)

: '-';

?>

</small>

</td>

<td>

<?php

echo htmlspecialchars(
$row['class_name']
);

?>

</td>

<td>

<?php

echo htmlspecialchars(
$row['section_name']
);

?>

</td>

<td>

<?php

echo (int)$row['total_students'];

?>

</td>

<td>

<?php

echo $row['average_marks'] !== null

? number_format(
$row['average_marks'],
2
)

: '0.00';

?>

</td>

<td>

<span
class="status <?php echo ($row['status']=='published') ? 'success' : 'warning'; ?>">

<?php

echo ucfirst(
$row['status']
);

?>

</span>

</td>

<td>

<?php

echo !empty($row['published_at'])

? date(
'd M Y',
strtotime($row['published_at'])
)

: '-';

?>

</td>

<td>

<div
style="
display:flex;
gap:8px;
flex-wrap:wrap;
">

<a
href="mark.php?id=<?php echo $row['result_id']; ?>"
class="btn btn-primary">

<i class="fa-solid fa-pen"></i>

Marks

</a>

<a
href="view.php?id=<?php echo $row['result_id']; ?>"
class="btn"
style="
background:#10b981;
color:white;
">

<i class="fa-solid fa-eye"></i>

View

</a>

<a
href="delete.php?id=<?php echo $row['result_id']; ?>"
class="btn"
style="
background:#ef4444;
color:white;
"
onclick="return confirm('Delete this result?');">

<i class="fa-solid fa-trash"></i>

Delete

</a>
<?php if($row['status'] == 'draft'){ ?>

<a
href="publish.php?id=<?php echo $row['result_id']; ?>"
class="btn"
style="
background:#10b981;
color:white;
">

<i class="fa-solid fa-upload"></i>

Publish

</a>

<?php } ?>

</div>

</td>

</tr>

<?php

}

}
else{

?>

<tr>

<td
colspan="8"
style="
text-align:center;
padding:40px;
">

<div
style="
display:flex;
flex-direction:column;
align-items:center;
gap:10px;
">

<i
class="fa-solid fa-chart-column"
style="
font-size:40px;
color:#94a3b8;
">
</i>

<p
style="
margin:0;
font-weight:500;
">

No results found.

</p>

<a
href="add.php"
class="btn btn-primary">

<i class="fa-solid fa-plus"></i>

Create First Result

</a>

</div>

</td>

</tr>

<?php } ?>

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