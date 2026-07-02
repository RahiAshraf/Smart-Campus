<?php

require_once '../../config/auth.php';
require_once '../../config/db.php';
require_once '../../shared/helpers/portal.php';

portal_require_role(['teacher']);

$user_id = (int)$_SESSION['user']['id'];

/*
Find teacher record
*/

$teacherQuery = mysqli_query(

$conn,

"SELECT id
FROM teachers
WHERE user_id=$user_id
LIMIT 1"

);

$teacher = mysqli_fetch_assoc($teacherQuery);

$teacher_id = (int)($teacher['id'] ?? 0);

$buses = mysqli_query(

$conn,

"SELECT DISTINCT

b.bus_id,
b.bus_name,
b.bus_number,

d.full_name AS driver_name,
d.phone AS driver_phone,

r.route_name,

l.latitude,
l.longitude,
l.status,
l.updated_at,

COUNT(DISTINCT s.id) AS students_count

FROM teacher_assignments ta

INNER JOIN students s
ON s.class_id=ta.class_id
AND s.section_id=ta.section_id

INNER JOIN transport_student_assignments tsa
ON tsa.student_id=s.id

INNER JOIN transport_buses b
ON b.bus_id=tsa.bus_id

LEFT JOIN transport_staff d
ON d.staff_id=b.driver_id

LEFT JOIN transport_routes r
ON r.bus_id=b.bus_id

LEFT JOIN transport_live_location l
ON l.bus_id=b.bus_id

WHERE ta.teacher_id=$teacher_id

GROUP BY b.bus_id

ORDER BY b.bus_name"

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
Teacher Transport Portal
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

<link
rel="stylesheet"
href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>

<link rel="stylesheet" href="../../assets/css/material-theme.css">
<style>

.transport-grid{

display:grid;
grid-template-columns:
repeat(auto-fit,minmax(340px,1fr));

gap:20px;

}

.bus-card{

background:var(--card);
border-radius:16px;
padding:20px;
box-shadow:var(--shadow);

}

.transport-map{

height:250px;
border-radius:12px;
overflow:hidden;
margin-top:15px;

}

.bus-card h3{

margin-bottom:12px;

}

.bus-meta{

display:grid;
grid-template-columns:
repeat(2,1fr);

gap:10px;
margin-top:15px;

}

.bus-meta div{

background:#f8fafc;
padding:10px;
border-radius:10px;

}

.status-running{

color:#16a34a;
font-weight:600;

}

.status-not_started{

color:#f59e0b;
font-weight:600;

}

.status-completed{

color:#64748b;
font-weight:600;

}

</style>

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
Transport Monitoring
</h1>

<p>
Track buses carrying students from your assigned classes.
</p>

</div>

</div>

<div class="transport-grid">

<?php

if(mysqli_num_rows($buses)>0){

$mapIndex = 0;

while($bus = mysqli_fetch_assoc($buses)){

$mapIndex++;

?>

<div class="bus-card">

<h3>

<?php

echo htmlspecialchars(
$bus['bus_name']
);

?>

</h3>

<p>

<strong>Bus Number:</strong>

<?php

echo htmlspecialchars(
$bus['bus_number']
);

?>

</p>

<p>

<strong>Route:</strong>

<?php

echo htmlspecialchars(
$bus['route_name'] ?? '-'
);

?>

</p>

<p>

<strong>Driver:</strong>

<?php

echo htmlspecialchars(
$bus['driver_name'] ?? 'Not Assigned'
);

?>

</p>

<p>

<strong>Phone:</strong>

<?php

echo htmlspecialchars(
$bus['driver_phone'] ?? '-'
);

?>

</p>

<p>

<strong>Status:</strong>

<span class="status-<?php echo $bus['status']; ?>">

<?php

echo ucfirst(
str_replace(
'_',
' ',
$bus['status']
)
);

?>

</span>

</p>

<div class="bus-meta">

<div>

<strong>Students</strong>

<br>

<?php

echo (int)$bus['students_count'];

?>

</div>

<div>

<strong>Updated</strong>

<br>

<?php

echo !empty($bus['updated_at'])

? date(
'd M h:i A',
strtotime($bus['updated_at'])
)

: '-';

?>

</div>

</div>

<div
id="map_<?php echo $mapIndex; ?>"
class="transport-map">

</div>

<script>

document.addEventListener(

'DOMContentLoaded',

function(){

const map =

L.map(
'map_<?php echo $mapIndex; ?>'
)

.setView(

[
<?php echo (float)($bus['latitude'] ?: 17.3850); ?>,
<?php echo (float)($bus['longitude'] ?: 78.4867); ?>
],

13

);

L.tileLayer(

'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',

{
maxZoom:19
}

).addTo(map);

L.marker(

[
<?php echo (float)($bus['latitude'] ?: 17.3850); ?>,
<?php echo (float)($bus['longitude'] ?: 78.4867); ?>
]

)

.addTo(map)

.bindPopup(

'<?php echo addslashes($bus['bus_name']); ?>'

);

}

);

</script>

</div>

<?php

}

}else{

?>

<div class="dashboard-section">

<div
style="
text-align:center;
padding:40px;
width:100%;
">

<h3>
No Transport Data Available
</h3>

<p>
No students from your assigned classes are currently assigned to transport.
</p>

</div>

</div>

<?php } ?>

</div>

</div>

</div>

</div>

<script
src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js">
</script>

<script src="../../assets/js/common.js"></script>

</body>

</html>