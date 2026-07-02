<?php

include('../../config/auth.php');
include('../../config/db.php');

$error = '';

/* =========================
   Students (Not Assigned)
========================= */

$students = mysqli_query(

$conn,

"SELECT

s.id,
s.student_id,

u.full_name

FROM students s

INNER JOIN users u
ON s.user_id=u.id

WHERE s.id NOT IN
(
SELECT student_id
FROM transport_student_assignments
)

ORDER BY u.full_name"

);

/* =========================
   Active Buses
========================= */

$buses = mysqli_query(

$conn,

"SELECT

bus_id,
bus_name,
bus_number

FROM transport_buses

WHERE status='active'

ORDER BY bus_name"

);

if($_SERVER['REQUEST_METHOD'] == 'POST'){

$student_id = (int)($_POST['student_id'] ?? 0);

$bus_id = (int)($_POST['bus_id'] ?? 0);

$stop_id = (int)($_POST['stop_id'] ?? 0);

if(
$student_id <= 0
||
$bus_id <= 0
||
$stop_id <= 0
){

$error =
'Please select Student, Bus and Stop.';

}
else{

$check = mysqli_query(

$conn,

"SELECT assignment_id

FROM transport_student_assignments

WHERE student_id='$student_id'

LIMIT 1"

);

if(mysqli_num_rows($check) > 0){

$error =
'Student already assigned to a bus.';

}
else{

mysqli_query(

$conn,

"INSERT INTO transport_student_assignments
(
student_id,
bus_id,
stop_id
)

VALUES
(
'$student_id',
'$bus_id',
'$stop_id'
)"

);

header(
'Location:index.php?success=added'
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
Assign Student Transport
</title>

<link
rel="stylesheet"
href="../../assets/css/global.css">

<link
rel="stylesheet"
href="../../assets/css/layout.css">

<link
rel="stylesheet"
href="../../assets/css/components.css">

<link
rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<link rel="stylesheet" href="../../assets/css/material-theme.css">
</head>

<body>

<div class="app-layout">

<?php include('../../partials/sidebar.php'); ?>

<div class="main-content">

<?php include('../../partials/topbar.php'); ?>

<div class="page-content">

<div class="page-header">

<div>

<h1>
Assign Student To Transport
</h1>

<p>
Assign a student to a bus and route stop.
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
Student
</label>

<select
name="student_id"
class="form-input"
required>

<option value="">
Select Student
</option>

<?php
while($student=mysqli_fetch_assoc($students)){
?>

<option
value="<?php echo $student['id']; ?>">

<?php

echo htmlspecialchars(
$student['full_name']
);

?>

(

<?php

echo htmlspecialchars(
$student['student_id']
);

?>

)

</option>

<?php } ?>

</select>

</div>

<div class="form-group">

<label>
Bus
</label>

<select
name="bus_id"
id="bus_id"
class="form-input"
required>

<option value="">
Select Bus
</option>

<?php
while($bus=mysqli_fetch_assoc($buses)){
?>

<option
value="<?php echo $bus['bus_id']; ?>">

<?php

echo htmlspecialchars(
$bus['bus_name']
);

?>

(

<?php

echo htmlspecialchars(
$bus['bus_number']
);

?>

)

</option>

<?php } ?>

</select>

</div>

<div class="form-group">

<label>
Route Stop
</label>

<select
name="stop_id"
id="stop_id"
class="form-input"
required>

<option value="">
Select Bus First
</option>

</select>

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

Save Assignment

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

<script>

document
.getElementById('bus_id')
.addEventListener(
'change',
function(){

let busId = this.value;

let stopDropdown =
document.getElementById(
'stop_id'
);

stopDropdown.innerHTML =
'<option value="">Loading Stops...</option>';

if(busId === ''){

stopDropdown.innerHTML =
'<option value="">Select Bus First</option>';

return;

}

fetch(

'get_stops.php?bus_id='
+
busId

)

.then(
response => response.json()
)

.then(data => {

let html =
'<option value="">Select Stop</option>';

if(
data.success
&&
data.data.length > 0
){

data.data.forEach(stop => {

html +=

'<option value="' +

stop.stop_id +

'">' +

stop.stop_name +

'</option>';

});

}
else{

html =
'<option value="">No Stops Available</option>';

}

stopDropdown.innerHTML =
html;

})

.catch(error => {

console.error(error);

stopDropdown.innerHTML =
'<option value="">Failed To Load Stops</option>';

});

}
);

</script>

<script src="../../assets/js/common.js"></script>

</body>

</html>