<?php

require_once '../../config/auth.php';
require_once '../../config/db.php';
require_once '../../shared/helpers/portal.php';
require_once '../../shared/services/TransportService.php';

portal_require_role(['student']);

$service = new TransportService($conn);
$transport = $service->getStudentAssignmentByUser((int) $_SESSION['user']['id']);
$busId = (int) ($transport['bus_id'] ?? 0);

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
<title>Student Transport Portal | MindMerge SmartCampus</title>
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
<h1 class="hero-title">Transport Portal</h1>
<p class="hero-description">Track your assigned school bus, driver shared location, route progress, ETA, and optional personal location marker.</p>
<div class="hero-meta">
<span class="hero-badge"><i class="fa-solid fa-bus"></i><?php echo e($transport['bus_number'] ?? 'No bus'); ?></span>
<span class="hero-badge"><i class="fa-solid fa-route"></i><?php echo e($transport['route_name'] ?? 'No route'); ?></span>
<span class="hero-badge"><i class="fa-solid fa-location-dot"></i><?php echo e(ucwords(str_replace('_', ' ', $transport['tracking_status'] ?? 'not_started'))); ?></span>
</div>
</div>
<div class="hero-illustration"><i class="fa-solid fa-bus"></i></div>
</section>

<?php if (!$transport) { ?>
<div class="dashboard-empty"><i class="fa-solid fa-bus"></i><h3>No Transport Assigned</h3><p>You are currently not assigned to any transport service.</p></div>
<?php } else { ?>

<section class="stats-grid">
<div class="stat-card"><div class="stat-icon"><i class="fa-solid fa-id-card"></i></div><div class="stat-content"><span class="stat-label">Bus</span><span class="stat-value"><?php echo e($transport['bus_number']); ?></span><span class="stat-description"><?php echo e($transport['bus_name']); ?></span></div></div>
<div class="stat-card"><div class="stat-icon"><i class="fa-solid fa-user-tie"></i></div><div class="stat-content"><span class="stat-label">Driver</span><span class="stat-value"><?php echo e($transport['driver_name'] ?: '-'); ?></span><span class="stat-description"><?php echo e($transport['driver_phone'] ?: '-'); ?></span></div></div>
<div class="stat-card"><div class="stat-icon"><i class="fa-solid fa-location-dot"></i></div><div class="stat-content"><span class="stat-label">My Stop</span><span class="stat-value"><?php echo e($transport['assigned_stop_name'] ?: '-'); ?></span><span class="stat-description">Pickup/drop stop</span></div></div>
<div class="stat-card"><div class="stat-icon"><i class="fa-solid fa-clock"></i></div><div class="stat-content"><span class="stat-label">Last Update</span><span class="stat-value" id="lastUpdateText"><?php echo dt($transport['updated_at'] ?? null); ?></span><span class="stat-description">Driver shared location</span></div></div>
</section>

<section class="dashboard-widget">
<div class="dashboard-widget-header">
<div class="dashboard-widget-title"><i class="fa-solid fa-map-location-dot"></i><div><h4>Live Bus Location</h4><span id="etaText">Loading route progress...</span></div></div>
<button id="myLocationBtn" class="btn" type="button"><i class="fa-solid fa-location-crosshairs"></i> My Location: Off</button>
</div>
<div class="dashboard-widget-body">
<div id="transportMap" class="transport-map"></div>
<div class="transport-summary" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:14px;margin-top:16px;">
<div class="info-panel"><h3 class="info-panel-title"><i class="fa-solid fa-location-crosshairs"></i>Current Stop</h3><div id="currentStopText">-</div></div>
<div class="info-panel"><h3 class="info-panel-title"><i class="fa-solid fa-flag-checkered"></i>Next Stop</h3><div id="nextStopText">-</div></div>
<div class="info-panel"><h3 class="info-panel-title"><i class="fa-solid fa-chart-line"></i>Progress</h3><div id="progressText">0%</div></div>
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
<?php if ($transport) { ?>
<script>
const busId = <?php echo $busId; ?>;
let map = L.map('transportMap').setView([13.6288, 79.4192], 13);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(map);
let busMarker = null, myMarker = null, myWatch = null, routeLine = null, stopMarkers = [], hasFit = false;
const busIcon = L.divIcon({ html:'<i class="fa-solid fa-bus" style="font-size:30px;color:#2563eb;"></i>', className:'', iconSize:[32,32] });
const myIcon = L.divIcon({ html:'<i class="fa-solid fa-person" style="font-size:26px;color:#0f766e;"></i>', className:'', iconSize:[28,28] });
function resetRoute(){ stopMarkers.forEach(m=>map.removeLayer(m)); stopMarkers=[]; if(routeLine) map.removeLayer(routeLine); routeLine=null; }
function draw(bus){
    resetRoute();
    const coords = (bus.stops || []).filter(s=>s.latitude&&s.longitude).map(s=>[s.latitude,s.longitude]);
    if(coords.length) routeLine = L.polyline(coords,{color:bus.route.route_color||'#2563eb',weight:5,opacity:.85}).addTo(map);
    (bus.stops || []).forEach(stop=>{ if(!stop.latitude||!stop.longitude)return; const icon=L.divIcon({html:stop.is_start==1?'<i class="fa-solid fa-flag-checkered" style="color:#16a34a;font-size:20px;"></i>':(stop.is_end==1?'<i class="fa-solid fa-flag" style="color:#dc2626;font-size:20px;"></i>':'<i class="fa-solid fa-location-dot" style="color:#f59e0b;font-size:18px;"></i>'),className:'',iconSize:[24,24]}); const marker=L.marker([stop.latitude,stop.longitude],{icon}).addTo(map).bindTooltip(stop.stop_name||'Stop'); stopMarkers.push(marker); });
    if(bus.latitude&&bus.longitude){ const latLng=[bus.latitude,bus.longitude]; if(busMarker) busMarker.setLatLng(latLng); else busMarker=L.marker(latLng,{icon:busIcon}).addTo(map); busMarker.bindPopup(`<strong>${bus.bus_number||'-'}</strong><br>${bus.driver.name||'-'}`); if(!hasFit){ routeLine?map.fitBounds(routeLine.getBounds(),{padding:[35,35]}):map.setView(latLng,15); hasFit=true; } }
    else if(!hasFit&&routeLine){ map.fitBounds(routeLine.getBounds(),{padding:[35,35]}); hasFit=true; }
    const p=bus.progress||{}; document.getElementById('currentStopText').innerText=p.current_stop?.stop_name||'-'; document.getElementById('nextStopText').innerText=p.next_stop?.stop_name||'-'; document.getElementById('progressText').innerText=`${p.percent||0}%`; document.getElementById('etaText').innerText=p.eta_minutes!==null&&p.eta_minutes!==undefined?`ETA ${p.eta_minutes} min, ${p.distance_to_next_km} km`:'ETA unavailable'; document.getElementById('lastUpdateText').innerText=bus.updated_at?new Date(bus.updated_at).toLocaleString():'-';
}
function load(){ fetch(`../../transport/tracking/fetch_live_location.php?bus_id=${busId}`).then(r=>r.json()).then(p=>{ if(p.success&&p.data) draw(p.data); }).catch(console.error); }
document.getElementById('myLocationBtn').addEventListener('click',()=>{ const btn=document.getElementById('myLocationBtn'); if(myWatch){ navigator.geolocation.clearWatch(myWatch); myWatch=null; if(myMarker)map.removeLayer(myMarker); myMarker=null; btn.innerHTML='<i class="fa-solid fa-location-crosshairs"></i> My Location: Off'; return; } if(!navigator.geolocation)return; btn.innerHTML='<i class="fa-solid fa-location-crosshairs"></i> My Location: On'; myWatch=navigator.geolocation.watchPosition(pos=>{ const latLng=[pos.coords.latitude,pos.coords.longitude]; if(myMarker)myMarker.setLatLng(latLng); else myMarker=L.marker(latLng,{icon:myIcon}).addTo(map).bindPopup('My Location'); },console.error,{enableHighAccuracy:true,maximumAge:5000,timeout:12000}); });
load(); setInterval(load,15000);
</script>
<?php } ?>
</body>
</html>
