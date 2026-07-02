<?php

require_once '../../config/auth.php';
require_once '../../config/db.php';
require_once '../../shared/helpers/portal.php';
require_once '../../shared/services/TransportService.php';

portal_require_role(['parent']);

$service = new TransportService($conn);
$assignments = $service->getParentAssignmentsByUser((int) $_SESSION['user']['id']);
$selectedStudentId = (int) ($_GET['student_id'] ?? 0);
$selectedBusId = (int) ($_GET['bus_id'] ?? 0);
if ($selectedStudentId <= 0 && $selectedBusId <= 0 && !empty($assignments)) {
    $selectedStudentId = (int) $assignments[0]['student_db_id'];
    $selectedBusId = (int) $assignments[0]['bus_id'];
}
$selected = null;
foreach ($assignments as $assignment) {
    $studentMatches = $selectedStudentId > 0 && (int) ($assignment['student_db_id'] ?? 0) === $selectedStudentId;
    $busMatches = $selectedStudentId <= 0 && (int) $assignment['bus_id'] === $selectedBusId;
    if ($studentMatches || $busMatches) {
        $selected = $assignment;
        $selectedStudentId = (int) ($assignment['student_db_id'] ?? 0);
        $selectedBusId = (int) $assignment['bus_id'];
        break;
    }
}

function e($value): string
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
}

function dt($value): string
{
    return $value ? date('M j, Y g:i A', strtotime($value)) : '-';
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Parent Transport Portal | MindMerge SmartCampus</title>
<link rel="stylesheet" href="../../assets/css/global.css">
<link rel="stylesheet" href="../../assets/css/layout.css">
<link rel="stylesheet" href="../../assets/css/components.css">
<link rel="stylesheet" href="../../assets/css/dashboard-components.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<link rel="stylesheet" href="../../assets/css/material-theme.css">
<style>
.transport-map{height:560px;min-height:360px;width:100%;border-radius:16px;overflow:hidden;border:1px solid var(--border-color);}
@media(max-width:700px){.transport-map{height:430px;}}
</style>
</head>
<body>
<div class="app-layout">
<?php include('../../partials/sidebar.php'); ?>
<div class="main-content">
<?php include('../../partials/topbar.php'); ?>
<div class="page-content">
<div class="dashboard-page">

<section class="dashboard-hero">
<div class="hero-content">
<h1 class="hero-title">Parent Transport Portal</h1>
<p class="hero-description">Track each linked child's bus, driver shared location, route progress, stops, and ETA.</p>
<div class="hero-meta">
<span class="hero-badge"><i class="fa-solid fa-children"></i><?php echo number_format(count($assignments)); ?> transport assignments</span>
<span class="hero-badge"><i class="fa-solid fa-bus"></i><?php echo e($selected['bus_number'] ?? 'No bus'); ?></span>
<span class="hero-badge"><i class="fa-solid fa-route"></i><?php echo e($selected['route_name'] ?? 'No route'); ?></span>
</div>
</div>
<div class="hero-illustration"><i class="fa-solid fa-people-roof"></i></div>
</section>

<?php if (empty($assignments)) { ?>
<div class="dashboard-empty"><i class="fa-solid fa-bus"></i><h3>No Transport Assignment Found</h3><p>No transport information is available for your linked children.</p></div>
<?php } else { ?>

<?php if (count($assignments) > 1) { ?>
<section class="dashboard-widget">
<div class="dashboard-widget-body">
<form method="GET" style="max-width:420px;">
<label for="student_id">Select Child Bus</label>
<select id="student_id" name="student_id" class="form-input" onchange="this.form.submit()">
<?php foreach ($assignments as $assignment) { ?>
<option value="<?php echo (int) ($assignment['student_db_id'] ?? 0); ?>" <?php echo (int) ($assignment['student_db_id'] ?? 0) === $selectedStudentId ? 'selected' : ''; ?>>
<?php echo e(($assignment['student_name'] ?? 'Child') . ' - ' . ($assignment['bus_number'] ?? 'Bus')); ?>
</option>
<?php } ?>
</select>
</form>
</div>
</section>
<?php } ?>

<section class="stats-grid">
<div class="stat-card"><div class="stat-icon"><i class="fa-solid fa-user-graduate"></i></div><div class="stat-content"><span class="stat-label">Child</span><span class="stat-value"><?php echo e($selected['student_name'] ?? '-'); ?></span><span class="stat-description"><?php echo e(($selected['class_name'] ?? '') . ' ' . ($selected['section_name'] ?? '')); ?></span></div></div>
<div class="stat-card"><div class="stat-icon"><i class="fa-solid fa-bus"></i></div><div class="stat-content"><span class="stat-label">Bus</span><span class="stat-value"><?php echo e($selected['bus_number'] ?? '-'); ?></span><span class="stat-description"><?php echo e($selected['bus_name'] ?? '-'); ?></span></div></div>
<div class="stat-card"><div class="stat-icon"><i class="fa-solid fa-user-tie"></i></div><div class="stat-content"><span class="stat-label">Driver</span><span class="stat-value"><?php echo e($selected['driver_name'] ?? '-'); ?></span><span class="stat-description"><?php echo e($selected['driver_phone'] ?? '-'); ?></span></div></div>
<div class="stat-card"><div class="stat-icon"><i class="fa-solid fa-location-dot"></i></div><div class="stat-content"><span class="stat-label">Stop</span><span class="stat-value"><?php echo e($selected['assigned_stop_name'] ?? '-'); ?></span><span class="stat-description">Assigned pickup/drop point</span></div></div>
</section>

<section class="dashboard-grid-equal">
<div class="dashboard-widget">
<div class="dashboard-widget-header"><div class="dashboard-widget-title"><i class="fa-solid fa-map-location-dot"></i><div><h4>Live Bus Location</h4><span id="etaText">Loading route progress...</span></div></div></div>
<div class="dashboard-widget-body">
<div id="transportMap" class="transport-map"></div>
<div class="transport-summary" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(170px,1fr));gap:14px;margin-top:16px;">
<div class="info-panel"><h3 class="info-panel-title"><i class="fa-solid fa-location-crosshairs"></i>Current Stop</h3><div id="currentStopText">-</div></div>
<div class="info-panel"><h3 class="info-panel-title"><i class="fa-solid fa-flag-checkered"></i>Next Stop</h3><div id="nextStopText">-</div></div>
<div class="info-panel"><h3 class="info-panel-title"><i class="fa-solid fa-chart-line"></i>Progress</h3><div id="progressText">0%</div></div>
</div>
</div>
</div>
<div class="dashboard-widget">
<div class="dashboard-widget-header"><div class="dashboard-widget-title"><i class="fa-solid fa-list"></i><div><h4>All Children Transport</h4><span>Bus and driver assignments</span></div></div></div>
<div class="dashboard-widget-body dashboard-table-wrap">
<table class="dashboard-table"><thead><tr><th>Child</th><th>Bus</th><th>Route</th><th>Driver</th><th>Last Update</th></tr></thead><tbody>
<?php foreach ($assignments as $assignment) { ?>
<tr>
<td><strong><?php echo e($assignment['student_name']); ?></strong><br><span><?php echo e($assignment['student_id']); ?></span></td>
<td><?php echo e($assignment['bus_number']); ?><br><span><?php echo e($assignment['bus_name']); ?></span></td>
<td><?php echo e($assignment['route_name'] ?: '-'); ?></td>
<td><?php echo e($assignment['driver_name'] ?: '-'); ?><br><span><?php echo e($assignment['driver_phone'] ?: '-'); ?></span></td>
<td><?php echo dt($assignment['updated_at'] ?? null); ?></td>
</tr>
<?php } ?>
</tbody></table>
</div>
</div>
</section>

<?php } ?>
</div>
</div>
</div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="../../assets/js/common.js"></script>
<?php if ($selected) { ?>
<script>
const busId = <?php echo (int) $selectedBusId; ?>;
let map = L.map('transportMap').setView([13.6288, 79.4192], 13);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(map);
let busMarker = null, routeLine = null, stopMarkers = [], hasFit = false;
const busIcon = L.divIcon({ html:'<i class="fa-solid fa-bus" style="font-size:30px;color:#2563eb;"></i>', className:'', iconSize:[32,32] });
function resetRoute(){ stopMarkers.forEach(m=>map.removeLayer(m)); stopMarkers=[]; if(routeLine) map.removeLayer(routeLine); routeLine=null; }
function draw(bus){
    resetRoute();
    const coords=(bus.stops||[]).filter(s=>s.latitude&&s.longitude).map(s=>[s.latitude,s.longitude]);
    if(coords.length) routeLine=L.polyline(coords,{color:bus.route.route_color||'#2563eb',weight:5,opacity:.85}).addTo(map);
    (bus.stops||[]).forEach(stop=>{ if(!stop.latitude||!stop.longitude)return; const icon=L.divIcon({html:stop.is_start==1?'<i class="fa-solid fa-flag-checkered" style="color:#16a34a;font-size:20px;"></i>':(stop.is_end==1?'<i class="fa-solid fa-flag" style="color:#dc2626;font-size:20px;"></i>':'<i class="fa-solid fa-location-dot" style="color:#f59e0b;font-size:18px;"></i>'),className:'',iconSize:[24,24]}); const marker=L.marker([stop.latitude,stop.longitude],{icon}).addTo(map).bindTooltip(stop.stop_name||'Stop'); stopMarkers.push(marker); });
    if(bus.latitude&&bus.longitude){ const latLng=[bus.latitude,bus.longitude]; if(busMarker) busMarker.setLatLng(latLng); else busMarker=L.marker(latLng,{icon:busIcon}).addTo(map); busMarker.bindPopup(`<strong>${bus.bus_number||'-'}</strong><br>${bus.driver.name||'-'}`); if(!hasFit){ routeLine?map.fitBounds(routeLine.getBounds(),{padding:[35,35]}):map.setView(latLng,15); hasFit=true; } }
    else if(!hasFit&&routeLine){ map.fitBounds(routeLine.getBounds(),{padding:[35,35]}); hasFit=true; }
    const p=bus.progress||{}; document.getElementById('currentStopText').innerText=p.current_stop?.stop_name||'-'; document.getElementById('nextStopText').innerText=p.next_stop?.stop_name||'-'; document.getElementById('progressText').innerText=`${p.percent||0}%`; document.getElementById('etaText').innerText=p.eta_minutes!==null&&p.eta_minutes!==undefined?`ETA ${p.eta_minutes} min, ${p.distance_to_next_km} km`:'ETA unavailable';
}
function load(){ fetch(`../../transport/tracking/fetch_live_location.php?bus_id=${busId}`).then(r=>r.json()).then(p=>{ if(p.success&&p.data) draw(p.data); }).catch(console.error); }
load(); setInterval(load,15000);
</script>
<?php } ?>
</body>
</html>
