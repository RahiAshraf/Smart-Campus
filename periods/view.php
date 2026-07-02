
<?php

include('../config/auth.php');
include('../config/db.php');

$id = intval($_GET['id']);

$period = mysqli_query(

$conn,

"SELECT

p.*,

pt.template_code,
pt.template_name,
pt.template_type

FROM periods p

LEFT JOIN period_templates pt

ON pt.template_id = p.template_id

WHERE p.period_id='$id'"

);

$row = mysqli_fetch_assoc($period);

if(!$row){

header("Location:index.php");
exit();

}

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
View Period | MindMerge SmartCampus
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
View Period
</h1>

<p>
Period information and schedule details.
</p>

</div>

<div
style="
display:flex;
gap:12px;
flex-wrap:wrap;
">

<a
href="edit.php?id=<?php echo $row['period_id']; ?>"
class="btn btn-primary">

<i class="fa-solid fa-pen"></i>

Edit

</a>

<a
href="../period_templates/view.php?id=<?php echo $row['template_id']; ?>"
class="btn">

<i class="fa-solid fa-arrow-left"></i>

Back To Schedule

</a>

</div>

</div>

<div class="dashboard-section">

<div class="section-header">

<h2>
Period Information
</h2>

</div>

<div class="form-grid">

<div class="form-group">

<label class="form-label">
Period Name
</label>

<div class="form-input">

<?php
echo htmlspecialchars(
$row['period_name']
);
?>

</div>

</div>

<div class="form-group">

<label class="form-label">
Schedule Template
</label>

<div class="form-input">

<?php

echo htmlspecialchars(
$row['template_code']
);

?>

 -

<?php

echo htmlspecialchars(
$row['template_name']
);

?>

</div>

</div>

</div>

<div class="form-grid">

<div class="form-group">

<label class="form-label">
Start Time
</label>

<div class="form-input">

<?php

echo date(
'h:i A',
strtotime(
$row['start_time']
)
);

?>

</div>

</div>

<div class="form-group">

<label class="form-label">
End Time
</label>

<div class="form-input">

<?php

echo date(
'h:i A',
strtotime(
$row['end_time']
)
);

?>

</div>

</div>

</div>

<div class="form-grid">

<div class="form-group">

<label class="form-label">
Period Type
</label>

<div>

<span class="status success">

<?php

echo ucfirst(
$row['period_type']
);

?>

</span>

</div>

</div>

<div class="form-group">

<label class="form-label">
Template Type
</label>

<div>

<span class="status success">

<?php

echo ucfirst(
$row['template_type']
);

?>

</span>

</div>

</div>

</div>

<div class="form-grid">

<div class="form-group">

<label class="form-label">
Sort Order
</label>

<div class="form-input">

<?php
echo $row['sort_order'];
?>

</div>

</div>

<div class="form-group">

<label class="form-label">
Room Required
</label>

<div class="form-input">

<?php

echo ucfirst(
$row['room_required']
);

?>

</div>

</div>
<div class="form-group">

<label class="form-label">
Attendance Allowed
</label>

<div class="form-input">

<?php

echo ucfirst(
$row['attendance_allowed']
);

?>

</div>

</div>

<div class="form-group">

<label class="form-label">
Teaching Period
</label>

<div class="form-input">

<?php

echo ucfirst(
$row['is_teaching_period']
);

?>

</div>

</div>

</div>

<div class="form-grid">

<div class="form-group">

<label class="form-label">
Status
</label>

<div>

<span
class="status <?php echo ($row['status']=='active') ? 'success' : 'danger'; ?>">

<?php

echo ucfirst(
$row['status']
);

?>

</span>

</div>

</div>

<div class="form-group">

<label class="form-label">
Created On
</label>

<div class="form-input">

<?php

echo date(
'd M Y h:i A',
strtotime(
$row['created_at']
)
);

?>

</div>

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
gap:10px;
">

<div
style="
width:30px;
height:30px;
border-radius:6px;
background:<?php echo htmlspecialchars($row['display_color']); ?>;
border:1px solid #d1d5db;
">

</div>

<div>

<?php

echo htmlspecialchars(
$row['display_color']
);

?>

</div>

</div>

</div>

<div class="form-group">

<label class="form-label">
Notes
</label>

<div class="form-textarea">

<?php

echo nl2br(
htmlspecialchars(
$row['notes']
)
);

?>

</div>

</div>

</div>

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
href="edit.php?id=<?php echo $row['period_id']; ?>"
class="btn btn-primary">

<i class="fa-solid fa-pen"></i>

Edit Period

</a>

<a
href="delete.php?id=<?php echo $row['period_id']; ?>"
class="btn"
style="
background:#ef4444;
color:white;
"
onclick="return confirm('Delete this period?');">

<i class="fa-solid fa-trash"></i>

Delete Period

</a>

<a
href="../period_templates/view.php?id=<?php echo $row['template_id']; ?>"
class="btn">

<i class="fa-solid fa-calendar-days"></i>

Open Schedule

</a>

</div>

</div>

</div>

</div>

</div>

<script src="../assets/js/common.js"></script>

</body>
</html>
