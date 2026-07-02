<?php

require_once '../../config/auth.php';
require_once '../../config/db.php';
require_once '../../shared/helpers/portal.php';
require_once '../../shared/services/TransportService.php';

portal_require_role(['driver']);

$service = new TransportService($conn);
$assignment = $service->getDriverAssignmentByUser((int) $_SESSION['user']['id']);
$hasBus = !empty($assignment['bus_id']);
$busId = (int) ($assignment['bus_id'] ?? 0);
$status = $assignment['tracking_status'] ?? 'not_started';

function e($value): string
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Driver GPS Tracking | MindMerge</title>
<link rel="stylesheet" href="../../assets/css/global.css">
<link rel="stylesheet" href="../../assets/css/layout.css">
<link rel="stylesheet" href="../../assets/css/components.css">
<link rel="stylesheet" href="../../assets/css/dashboard-components.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<link rel="stylesheet" href="../../assets/css/material-theme.css">
<style>
.tracking-shell{display:flex;flex-direction:column;gap:20px;max-width:1100px;margin:0 auto;}
.tracking-map{height:560px;min-height:360px;width:100%;border-radius:16px;overflow:hidden;border:1px solid var(--border-color);}
.tracking-actions{display:flex;gap:12px;flex-wrap:wrap;}
.status-pill{display:inline-flex;align-items:center;gap:8px;padding:8px 14px;border-radius:999px;font-weight:700;font-size:13px;}
.status-pill.running{background:#dcfce7;color:#166534;}
.status-pill.not_started{background:#dbeafe;color:#1d4ed8;}
.status-pill.completed{background:#fee2e2;color:#991b1b;}
.gps-line{color:var(--text-secondary);font-size:14px;line-height:1.7;}
@media(max-width:700px){.tracking-map{height:420px}.tracking-actions .btn{width:100%;justify-content:center;}}
</style>
</head>
<body>
<div class="app-layout">
<?php include('../../partials/sidebar.php'); ?>
<div class="main-content">
<?php include('../../partials/topbar.php'); ?>
<div class="page-content">
<div class="tracking-shell">

<div class="dashboard-hero">
<div class="hero-content">
<h1 class="hero-title">Driver GPS Tracking</h1>
<p class="hero-description">Start the trip to broadcast your live bus location. GPS updates stop automatically when the trip is completed or reset.</p>
<div class="hero-meta">
<span class="hero-badge"><i class="fa-solid fa-bus"></i><?php echo e($assignment['bus_number'] ?? 'No bus'); ?></span>
<span class="hero-badge"><i class="fa-solid fa-route"></i><?php echo e($assignment['route_name'] ?? 'No route'); ?></span>
<span class="hero-badge"><i class="fa-solid fa-users"></i><?php echo number_format((int) ($assignment['student_count'] ?? 0)); ?> students</span>
</div>
</div>
<div class="hero-illustration"><i class="fa-solid fa-location-dot"></i></div>
</div>

<?php if (!$hasBus) { ?>
<div class="dashboard-empty"><i class="fa-solid fa-bus"></i><h3>No bus assigned</h3><p>Please contact the transport administrator.</p></div>
<?php } else { ?>

<section class="stats-grid">
<div class="stat-card"><div class="stat-icon"><i class="fa-solid fa-signal"></i></div><div class="stat-content"><span class="stat-label">Trip Status</span><span class="stat-value" id="statusText"><?php echo e(ucwords(str_replace('_', ' ', $status))); ?></span><span class="stat-description">Broadcasting only while running</span></div></div>
<div class="stat-card"><div class="stat-icon"><i class="fa-solid fa-location-crosshairs"></i></div><div class="stat-content"><span class="stat-label">Current Stop</span><span class="stat-value" id="currentStopText">-</span><span class="stat-description">Nearest completed stop</span></div></div>
<div class="stat-card"><div class="stat-icon"><i class="fa-solid fa-flag-checkered"></i></div><div class="stat-content"><span class="stat-label">Next Stop</span><span class="stat-value" id="nextStopText">-</span><span class="stat-description"><span id="etaText">ETA unavailable</span></span></div></div>
</section>

<section class="dashboard-widget">
<div class="dashboard-widget-header">
<div class="dashboard-widget-title"><i class="fa-solid fa-tower-broadcast"></i><div><h4>Broadcast Controls</h4><span id="gpsHelp">Waiting for GPS permission...</span></div></div>
<span id="statusBadge" class="status-pill <?php echo e($status); ?>"><?php echo e(ucwords(str_replace('_', ' ', $status))); ?></span>
</div>
<div class="dashboard-widget-body">
<div class="tracking-actions">
<button id="startJourneyBtn" class="btn btn-primary" type="button"><i class="fa-solid fa-play"></i> Start Trip</button>
<button id="endJourneyBtn" class="btn" type="button"><i class="fa-solid fa-stop"></i> Complete Trip</button>
<button id="resetJourneyBtn" class="btn btn-danger" type="button"><i class="fa-solid fa-rotate-left"></i> Reset</button>
</div>
<p class="gps-line">Latitude: <span id="latText">-</span> | Longitude: <span id="lngText">-</span> | Last sent: <span id="timeText">-</span></p>
<div class="progress" style="margin-top:14px;"><span id="routeProgressBar" class="progress-bar progress-success" style="width:0%"></span></div>
</div>
</section>

<div id="driverMap" class="tracking-map"></div>

<?php } ?>
</div>
</div>
</div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="../../assets/js/common.js"></script>
<?php if ($hasBus) { ?>
<script>
const busId = <?php echo $busId; ?>;
let trackingStatus = <?php echo json_encode($status); ?>;
let currentLat = null;
let currentLng = null;
let watchId = null;
let marker = null;
let routeLine = null;
let stopMarkers = [];
let sendTimer = null;
let hasFitRoute = false;

const map = L.map('driverMap').setView([13.6288, 79.4192], 13);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(map);

const busIcon = L.divIcon({
    html: '<i class="fa-solid fa-bus" style="font-size:30px;color:#2563eb;"></i>',
    className: '',
    iconSize: [32, 32],
});

function setStatus(status) {
    trackingStatus = status;
    const label = status === 'running' ? 'Running' : (status === 'completed' ? 'Completed' : 'Not Started');
    document.getElementById('statusText').innerText = label;
    const badge = document.getElementById('statusBadge');
    badge.className = 'status-pill ' + status;
    badge.innerText = label;
}

function drawRoute(bus) {
    stopMarkers.forEach(item => map.removeLayer(item));
    stopMarkers = [];
    if (routeLine) map.removeLayer(routeLine);
    routeLine = null;

    const stops = (bus && bus.stops) || [];
    const coords = stops.filter(stop => stop.latitude && stop.longitude).map(stop => [stop.latitude, stop.longitude]);
    if (coords.length) {
        routeLine = L.polyline(coords, { color: bus.route.route_color || '#2563eb', weight: 5, opacity: 0.8 }).addTo(map);
        if (!hasFitRoute) {
            map.fitBounds(routeLine.getBounds(), { padding: [35, 35] });
            hasFitRoute = true;
        }
    }

    stops.forEach(stop => {
        if (!stop.latitude || !stop.longitude) return;
        const icon = L.divIcon({
            html: stop.is_start == 1 ? '<i class="fa-solid fa-flag-checkered" style="color:#16a34a;font-size:20px;"></i>' : (stop.is_end == 1 ? '<i class="fa-solid fa-flag" style="color:#dc2626;font-size:20px;"></i>' : '<i class="fa-solid fa-location-dot" style="color:#f59e0b;font-size:18px;"></i>'),
            className: '',
            iconSize: [24, 24],
        });
        const stopMarker = L.marker([stop.latitude, stop.longitude], { icon }).addTo(map);
        stopMarker.bindTooltip(stop.stop_name || 'Stop', { direction: 'top' });
        stopMarkers.push(stopMarker);
    });
}

function updateProgress(progress) {
    if (!progress) return;
    document.getElementById('currentStopText').innerText = progress.current_stop?.stop_name || '-';
    document.getElementById('nextStopText').innerText = progress.next_stop?.stop_name || '-';
    document.getElementById('etaText').innerText = progress.eta_minutes !== null ? `ETA ${progress.eta_minutes} min, ${progress.distance_to_next_km} km` : 'ETA unavailable';
    document.getElementById('routeProgressBar').style.width = `${progress.percent || 0}%`;
}

function sendLocation(status = trackingStatus) {
    const body = new URLSearchParams({ status });
    if (status === 'running') {
        if (currentLat === null || currentLng === null) return Promise.resolve();
        body.set('latitude', currentLat);
        body.set('longitude', currentLng);
    }

    return fetch('../tracking/update_location.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: body.toString(),
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) document.getElementById('timeText').innerText = new Date().toLocaleTimeString();
        return data;
    });
}

function refreshBus() {
    fetch(`../tracking/fetch_live_location.php?bus_id=${busId}`)
        .then(response => response.json())
        .then(payload => {
            if (!payload.success || !payload.data) return;
            drawRoute(payload.data);
            updateProgress(payload.data.progress);
            setStatus(payload.data.status || trackingStatus);
        })
        .catch(console.error);
}

function handlePosition(position) {
    currentLat = position.coords.latitude;
    currentLng = position.coords.longitude;
    document.getElementById('latText').innerText = currentLat.toFixed(6);
    document.getElementById('lngText').innerText = currentLng.toFixed(6);
    document.getElementById('gpsHelp').innerText = trackingStatus === 'running' ? 'GPS active and broadcasting.' : 'GPS ready. Start trip to broadcast.';

    if (marker) marker.setLatLng([currentLat, currentLng]);
    else marker = L.marker([currentLat, currentLng], { icon: busIcon }).addTo(map).bindPopup('Your bus');

    if (trackingStatus === 'running') {
        sendLocation('running').then(refreshBus).catch(console.error);
    }
}

function startGPS() {
    if (!navigator.geolocation) {
        document.getElementById('gpsHelp').innerText = 'GPS is not supported by this browser.';
        return;
    }
    watchId = navigator.geolocation.watchPosition(handlePosition, error => {
        document.getElementById('gpsHelp').innerText = error.message || 'GPS permission is required.';
    }, { enableHighAccuracy: true, maximumAge: 0, timeout: 12000 });
}

document.getElementById('startJourneyBtn').addEventListener('click', () => {
    setStatus('running');
    sendLocation('running').then(refreshBus).catch(console.error);
    if (!sendTimer) sendTimer = setInterval(() => sendLocation('running').then(refreshBus).catch(console.error), 15000);
});

document.getElementById('endJourneyBtn').addEventListener('click', () => {
    setStatus('completed');
    if (sendTimer) clearInterval(sendTimer);
    sendTimer = null;
    sendLocation('completed').then(refreshBus).catch(console.error);
});

document.getElementById('resetJourneyBtn').addEventListener('click', () => {
    setStatus('not_started');
    if (sendTimer) clearInterval(sendTimer);
    sendTimer = null;
    sendLocation('not_started').then(refreshBus).catch(console.error);
});

refreshBus();
startGPS();
if (trackingStatus === 'running') {
    sendTimer = setInterval(() => sendLocation('running').then(refreshBus).catch(console.error), 15000);
}
</script>
<?php } ?>
</body>
</html>
