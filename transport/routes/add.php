<?php

include('../../config/auth.php');
include('../../config/db.php');
$buses = mysqli_query(

$conn,

"SELECT

b.bus_id,
b.bus_name,
b.bus_number

FROM transport_buses b

WHERE b.status='active'

AND b.bus_id NOT IN
(
SELECT bus_id
FROM transport_routes
WHERE bus_id IS NOT NULL
)

ORDER BY b.bus_name ASC"

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
Create Route | MindMerge SmartCampus
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

.route-map{

height:550px;
width:100%;
border-radius:16px;
overflow:hidden;
border:1px solid #e5e7eb;

}

.stop-table{

margin-top:20px;

}

.stop-badge{

padding:5px 10px;
border-radius:20px;
font-size:12px;
font-weight:600;

}

.start-stop{

background:#dcfce7;
color:#166534;

}

.end-stop{

background:#fee2e2;
color:#991b1b;

}

.normal-stop{

background:#dbeafe;
color:#1d4ed8;

}

.route-actions{

display:flex;
gap:12px;
flex-wrap:wrap;
margin-top:20px;

}

.route-info{

display:grid;
grid-template-columns:
repeat(auto-fit,minmax(250px,1fr));
gap:16px;

}

.stop-count{

font-size:14px;
font-weight:600;
color:#64748b;

}
.route-modal{

display:none;

position:fixed;

top:0;
left:0;

width:100%;
height:100%;

background:rgba(0,0,0,.6);

z-index:99999;

justify-content:center;

align-items:center;

}

.route-modal-content{

background:var(--card);

padding:24px;

border-radius:16px;

width:420px;

max-width:95%;

box-shadow:var(--shadow);

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
Create Route
</h1>

<p>
Build route visually on map and assign to a bus.
</p>

</div>

<a
href="index.php"
class="btn">

<i class="fa-solid fa-arrow-left"></i>

Back

</a>

</div>

<!-- Route Information -->

<div class="dashboard-section">

<div class="section-header">

<h2>
Route Information
</h2>

</div>

<div class="route-info">

<div class="form-group">

<label>
Bus
</label>

<select
id="bus_id"
class="form-input">

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
Route Name
</label>

<input

type="text"

id="route_name"

class="form-input"

placeholder="Morning Route">

</div>

<div class="form-group">

<label>
Route Color
</label>

<input
type="color"
id="route_color"
value="#2563eb"
style="
width:100%;
height:50px;
border:none;
border-radius:12px;
cursor:pointer;
background:none;
padding:0;
">

</div>

<div class="form-group">

<label>
Start Time
</label>

<input
type="time"
id="start_time"
class="form-input"
readonly>

</div>

<div class="form-group">

<label>
End Time
</label>

<input
type="time"
id="end_time"
class="form-input"
readonly>

</div>

<div class="form-group">

<label>
Status
</label>

<select
id="status"
class="form-input">

<option value="active">
Active
</option>

<option value="inactive">
Inactive
</option>

</select>

</div>

</div>

<div
style="margin-top:16px;">

<label>
Route Description
</label>

<textarea

id="route_description"

class="form-input"

rows="4"

placeholder="Morning pickup route covering east zone stops...">

</textarea>

</div>

</div>

<!-- Map -->

<div class="dashboard-section">

<div class="section-header">

<h2>
Route Builder Map
</h2>

<div class="stop-count">

Stops Added: <span id="stopCount">
0 </span>

</div>

</div>

<div
style="
margin-bottom:12px;
display:flex;
gap:10px;
flex-wrap:wrap;
">



<button
type="button"
id="markEndBtn"
class="btn">

🔴 Next Click = End Point

</button>

<button
type="button"
id="clearRouteBtn"
class="btn">

<i class="fa-solid fa-trash"></i>

Clear Route

</button>

</div>

<div
id="routeMap"
class="route-map">
</div>

</div>

<!-- Stops Table -->

<div class="dashboard-section">

<div class="section-header">

<h2>
Route Stops
</h2>

</div>

<div class="table-responsive">

<table class="custom-table">

<thead>

<tr>

<th>
#
</th>

<th>
Stop Name
</th>

<th>
Arrival Time
</th>

<th>
Type
</th>

<th>
Latitude
</th>

<th>
Longitude
</th>

<th>
Action
</th>

</tr>

</thead>

<tbody id="stopsTableBody">

<tr id="emptyStopsRow">

<td
colspan="7"
style="text-align:center;">

No stops added yet.

</td>

</tr>

</tbody>

</table>

</div>

</div>

<!-- Save Section -->

<div class="dashboard-section">

<div class="section-header">

<h2>
Save Route
</h2>

</div>

<p
style="
margin-bottom:15px;
color:#64748b;
">

Select bus, add route information,
click map to create stops,
mark one start point and one end point,
then save.

</p>

<div class="route-actions">

<button
type="button"
id="saveRouteBtn"
class="btn btn-primary">

<i class="fa-solid fa-floppy-disk"></i>

Save Route

</button>

<a
href="index.php"
class="btn">

Cancel

</a>

</div>

</div>

</div>

</div>

</div>

<div id="stopModal" class="route-modal">

    <div class="route-modal-content">

        <h3>Add Route Stop</h3>

        <div class="form-group">
            <label>Stop Name</label>

            <input
            type="text"
            id="modalStopName"
            class="form-input">
        </div>

        <div class="form-group">
            <label>Arrival Time</label>

            <input
            type="time"
            id="modalArrivalTime"
            class="form-input">
        </div>

        <div
        style="
        display:flex;
        gap:10px;
        margin-top:15px;
        ">

            <button
            type="button"
            id="saveStopBtn"
            class="btn btn-primary">

            Save Stop

            </button>

            <button
            type="button"
            id="cancelStopBtn"
            class="btn">

            Cancel

            </button>

        </div>

    </div>

</div>
<!-- Route Builder Variables -->

<script>

const SAVE_ROUTE_URL =
'save_route.php';

const routeStops = [];

let map;

let markers = [];

let routeLine = null;

let pendingStopType =
'normal';

</script>

<script
src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js">
</script>

<script
src="route-builder.js">
</script>

<script src="../../assets/js/common.js"></script>

</body>

</html>
