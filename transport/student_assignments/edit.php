<?php

include('../../config/auth.php');
include('../../config/db.php');

$error = '';

$id = (int)($_GET['id'] ?? 0);

if($id <= 0){

header('Location:index.php?error=not_found');
exit;

}

/* =========================
   Assignment
========================= */

$assignmentQuery = mysqli_query(

$conn,

"SELECT

tsa.*,

u.full_name,

s.student_id AS student_code

FROM transport_student_assignments tsa

INNER JOIN students s
ON tsa.student_id=s.id

INNER JOIN users u
ON s.user_id=u.id

WHERE tsa.assignment_id='$id'

LIMIT 1"

);

if(mysqli_num_rows($assignmentQuery) == 0){

header('Location:index.php?error=not_found');
exit;

}

$assignment =
mysqli_fetch_assoc(
$assignmentQuery
);

/* =========================
   Buses
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

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta
name="viewport"
content="width=device-width, initial-scale=1.0">

<title>
Edit Student Assignment
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
Edit Student Assignment
</h1>

<p>
Modify bus and stop assignment.
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
">

<?php echo $error; ?>

</div>

<?php } ?>

<div class="dashboard-section">

<form
method="POST"
action="update.php">

<input
type="hidden"
name="assignment_id"
value="<?php echo $id; ?>">

<div class="form-grid">

<div class="form-group">

<label>
Student
</label>

<input

type="text"

class="form-input"

readonly

value="<?php

echo htmlspecialchars(
$assignment['full_name']
);

?> (<?php

echo htmlspecialchars(
$assignment['student_code']
);

?>)">

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
value="<?php echo $bus['bus_id']; ?>"

<?php

echo
$assignment['bus_id']
==
$bus['bus_id']
? 'selected'
: '';

?>>

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
Stop
</label>

<select
name="stop_id"
id="stop_id"
class="form-input"
required>

<option value="">
Loading Stops...
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

Update Assignment

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

const currentStopId =
<?php echo (int)$assignment['stop_id']; ?>;

function loadStops(busId, selectedStop = 0){

const stopDropdown =
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

'" ' +

(
parseInt(selectedStop)
===
parseInt(stop.stop_id)
? 'selected'
: ''
)

+

'>' +

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

document
.getElementById('bus_id')
.addEventListener(
'change',
function(){

loadStops(
this.value
);

}
);

/*
Load Existing Stops
*/

window.addEventListener(
'load',
function(){

loadStops(

document.getElementById(
'bus_id'
).value,

currentStopId

);

}
);

</script>

<script src="../../assets/js/common.js"></script>

</body>

</html>
