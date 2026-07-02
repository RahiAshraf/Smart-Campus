<?php

require_once '../../config/auth.php';
require_once '../../config/db.php';
require_once '../../shared/helpers/portal.php';
require_once '../../shared/services/TransportService.php';

$rawRole = strtolower(trim($_SESSION['user']['role'] ?? ''));
$permissionRole = strtolower(trim($_SESSION['permission_role'] ?? ''));
$role = $rawRole;
if (in_array($role, ['admin', 'administrator', 'super_admin', 'superadmin'], true) || $permissionRole === 'admin') {
    $role = 'admin';
}
$isFleetManager = $role === 'admin';
$userId = (int) ($_SESSION['user']['id'] ?? 0);

if ($role === 'student') {
    header('Location: ../../student/transport/index.php');
    exit;
}

if ($role === 'parent') {
    header('Location: ../../parent/transport/index.php');
    exit;
}

$service = new TransportService($conn);
$selectedBusId = (int) ($_GET['bus_id'] ?? 0);
$transportMessage = '';
$busOptions = [];

if ($isFleetManager) {

    $liveBuses = $service->getLiveBuses();

    foreach ($liveBuses as $bus) {

        if ($bus['status'] === 'running') {
            $status = 'Running';
        } elseif ($bus['status'] === 'completed') {
            $status = 'Completed';
        } else {
            $status = 'Trip Not Started';
        }

        $driver =
            !empty($bus['driver']['name'])
            ? $bus['driver']['name']
            : 'No Driver';

        $route =
            !empty($bus['route']['route_name'])
            ? $bus['route']['route_name']
            : 'No Route';

        $busOptions[] = [

            'bus_id' => (int)$bus['bus_id'],

            'label' =>
                $bus['bus_number']
                .' | '.$driver
                .' | '.$route
                .' | '.$status,

            'student_name' => null

        ];

    }

    if ($selectedBusId <= 0 && !empty($busOptions)) {

        $selectedBusId = (int)$busOptions[0]['bus_id'];

    }

} elseif ($role === 'driver') {
    $assignment = $service->getDriverAssignmentByUser($userId);
    $selectedBusId = (int) ($assignment['bus_id'] ?? 0);
} elseif ($role === 'student') {
    $assignment = $service->getStudentAssignmentByUser($userId);
    $selectedBusId = (int) ($assignment['bus_id'] ?? 0);
    if ($selectedBusId <= 0) {
        $transportMessage = 'No transport bus assigned to your account.';
    }
} elseif ($role === 'parent') {
    foreach ($service->getParentAssignmentsByUser($userId) as $assignment) {
        $busOptions[] = [
            'bus_id' => (int) $assignment['bus_id'],
            'label' => trim(($assignment['student_name'] ?? 'Child') . ' - ' . ($assignment['bus_number'] ?? 'Bus')),
            'student_name' => $assignment['student_name'] ?? null,
        ];
    }
    if ($selectedBusId <= 0 && !empty($busOptions)) {
        $selectedBusId = (int) $busOptions[0]['bus_id'];
    }
    if ($selectedBusId <= 0) {
        $transportMessage = 'No transport bus assigned for your linked children.';
    }
}

if (!$isFleetManager && !in_array($role, ['student', 'parent', 'driver'], true)) {
    $isFleetManager = true;
    $busOptions = [];
    foreach ($service->getLiveBuses() as $bus) {
        if ($bus['status'] === 'running') {
            $status = 'Running';
        } elseif ($bus['status'] === 'completed') {
            $status = 'Completed';
        } else {
            $status = 'Trip Not Started';
        }

        $busOptions[] = [
            'bus_id' => (int) $bus['bus_id'],
            'label' => ($bus['bus_number'] ?? 'Bus')
                . ' | ' . (!empty($bus['driver']['name']) ? $bus['driver']['name'] : 'No Driver')
                . ' | ' . (!empty($bus['route']['route_name']) ? $bus['route']['route_name'] : 'No Route')
                . ' | ' . $status,
            'student_name' => null,
        ];
    }

    if ($selectedBusId <= 0 && !empty($busOptions)) {
        $selectedBusId = (int) $busOptions[0]['bus_id'];
    }
}

$hasTransportAccess = $selectedBusId > 0 && ($isFleetManager || $service->userCanViewBus($userId, $role, $selectedBusId));

function e($value): string
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Fleet Management | MindMerge</title>
<link rel="stylesheet" href="../../assets/css/global.css">
<link rel="stylesheet" href="../../assets/css/layout.css">
<link rel="stylesheet" href="../../assets/css/components.css">
<link rel="stylesheet" href="../../assets/css/dashboard-components.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<link rel="stylesheet" href="../../assets/css/material-theme.css">
<style>
.tracking-map{height:650px;min-height:380px;width:100%;border-radius:16px;overflow:hidden;border:1px solid var(--border-color);}
.tracking-toolbar{display:flex;gap:14px;align-items:flex-end;justify-content:space-between;flex-wrap:wrap;}
.tracking-toolbar form{min-width:min(100%,360px);}
.status-dot{display:inline-flex;align-items:center;gap:8px;font-weight:700;}
.status-dot::before{content:"";width:10px;height:10px;border-radius:50%;background:#f59e0b;}
.status-dot.running::before{background:#16a34a;}
.status-dot.completed::before{background:#2563eb;}
.transport-summary{display:grid;grid-template-columns:repeat(auto-fit,minmax(190px,1fr));gap:14px;margin-top:16px;}
.summary-pill{padding:14px;border:1px solid var(--border-color);border-radius:12px;background:var(--card);}
.summary-pill span{display:block;color:var(--text-secondary);font-size:12px;margin-bottom:5px;}
.summary-pill strong{font-size:16px;color:var(--text-primary);}
.trip-state{display:flex;align-items:center;gap:10px;margin-bottom:14px;padding:12px 14px;border:1px solid var(--border-color);border-radius:12px;background:var(--surface);}
.trip-state i{color:#f59e0b;}
.trip-state.running i{color:#16a34a;}
.trip-state.completed i{color:#2563eb;}
.route-stop-list{display:grid;gap:10px;margin-top:14px;}
.route-stop-item{display:grid;grid-template-columns:44px 1fr auto;gap:12px;align-items:center;padding:12px;border:1px solid var(--border-color);border-radius:12px;background:var(--card);}
.route-stop-order{width:34px;height:34px;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;background:var(--surface);font-weight:800;color:var(--text-primary);}
.route-stop-name{font-weight:800;color:var(--text-primary);}
.route-stop-meta{font-size:12px;color:var(--text-secondary);margin-top:2px;}
@media(max-width:700px){.tracking-map{height:460px}.tracking-toolbar{align-items:stretch}.tracking-toolbar form,.tracking-toolbar a{width:100%;}}
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
<h1 class="hero-title">Live Transport Tracking</h1>
<p class="hero-description">
<?php
if ($role === 'admin') {
    echo 'Monitor transport operations, driver location, ETA, stops, and route progress.';
} elseif ($role === 'driver') {
    echo 'View your assigned bus and route progress.';
} elseif ($role === 'parent') {
    echo 'Track each child bus and driver shared location.';
} else {
    echo 'Track your assigned school bus in real time.';
}
?>
</p>
<div class="hero-meta">
<span class="hero-badge"><i class="fa-solid fa-bus"></i><span id="heroBusText">Bus</span></span>
<span class="hero-badge"><i class="fa-solid fa-route"></i><span id="heroRouteText">Route</span></span>
<span class="hero-badge"><i class="fa-solid fa-clock"></i><span id="heroEtaText">ETA unavailable</span></span>
</div>
</div>
<div class="hero-illustration"><i class="fa-solid fa-map-location-dot"></i></div>
</section>

<?php if ($transportMessage !== '') { ?>
<div class="alert-banner warning"><?php echo e($transportMessage); ?></div>
<?php } ?>

<?php if ($isFleetManager || !empty($busOptions)) { ?>
<section class="dashboard-widget">
<?php if ($isFleetManager) { ?>
<div class="dashboard-widget-header">
<div class="dashboard-widget-title"><i class="fa-solid fa-bus-simple"></i><div><h4>Admin Fleet Bus Selection</h4><span>Choose any available bus to load its route, stops, and trip status.</span></div></div>
</div>
<?php } ?>
<div class="dashboard-widget-body">
<div class="tracking-toolbar">
<form method="GET" id="busSelectorForm">
<label for="bus_id">Select <?php echo $role === 'parent' ? 'Child Bus' : 'Bus / Driver'; ?></label>

<select id="bus_id" name="bus_id" class="form-input" <?php echo empty($busOptions) ? 'disabled' : ''; ?>>
<?php if (empty($busOptions)) { ?>
<option value="">No assigned buses found</option>
<?php } ?>
<?php foreach ($busOptions as $option) { ?>
<option value="<?php echo (int) $option['bus_id']; ?>" <?php echo $selectedBusId === (int) $option['bus_id'] ? 'selected' : ''; ?>>
<?php echo e($option['label']); ?>
</option>
<?php } ?>
</select>
</form>
<?php if ($role === 'driver') { ?><a href="../mobile/location.php" class="btn btn-primary"><i class="fa-solid fa-tower-broadcast"></i> Broadcast Location</a><?php } ?>
</div>
</div>
</section>
<?php } elseif ($role === 'driver') { ?>
<section class="dashboard-widget"><div class="dashboard-widget-body"><a href="../mobile/location.php" class="btn btn-primary"><i class="fa-solid fa-tower-broadcast"></i> Broadcast Location</a></div></section>
<?php } ?>

<?php if ($hasTransportAccess) { ?>
<section class="stats-grid">
<div class="stat-card"><div class="stat-icon"><i class="fa-solid fa-signal"></i></div><div class="stat-content"><span class="stat-label">Status</span><span class="stat-value" id="statusText">Loading</span><span class="stat-description">Driver shared trip state</span></div></div>
<div class="stat-card"><div class="stat-icon"><i class="fa-solid fa-location-crosshairs"></i></div><div class="stat-content"><span class="stat-label">Current Stop</span><span class="stat-value" id="currentStopText">-</span><span class="stat-description">Route progress</span></div></div>
<div class="stat-card"><div class="stat-icon"><i class="fa-solid fa-flag-checkered"></i></div><div class="stat-content"><span class="stat-label">Next Stop</span><span class="stat-value" id="nextStopText">-</span><span class="stat-description" id="etaDescription">ETA unavailable</span></div></div>
<div class="stat-card"><div class="stat-icon"><i class="fa-solid fa-users"></i></div><div class="stat-content"><span class="stat-label">Students</span><span class="stat-value" id="studentCountText">0</span><span class="stat-description">Assigned to bus</span></div></div>
</section>

<section class="dashboard-widget">
<div class="dashboard-widget-header">
<div class="dashboard-widget-title"><i class="fa-solid fa-map"></i><div><h4>Live Map</h4><span id="lastUpdateText">Waiting for location...</span></div></div>
<?php if ($role === 'student') { ?>
<button id="shareMyLocationBtn" class="btn" type="button"><i class="fa-solid fa-location-crosshairs"></i> My Location: Off</button>
<?php } ?>
</div>
<div class="dashboard-widget-body">
<div id="tripStateBox" class="trip-state"><i class="fa-solid fa-circle-info"></i><strong id="tripStateText">Loading trip details...</strong></div>
<div id="trackingMap" class="tracking-map"></div>
<div class="transport-summary">
<div class="summary-pill"><span>Driver</span><strong id="driverText">-</strong></div>
<div class="summary-pill"><span>Phone</span><strong id="phoneText">-</strong></div>
<div class="summary-pill"><span>Route</span><strong id="routeText">-</strong></div>
<div class="summary-pill"><span>Trip Time</span><strong id="tripTimeText">-</strong></div>
<div class="summary-pill"><span>Progress</span><strong id="progressText">0%</strong></div>
<div class="summary-pill"><span>Coordinates</span><strong id="coordinateText">-</strong></div>
</div>
</div>
</section>

<section class="dashboard-widget">
<div class="dashboard-widget-header">
<div class="dashboard-widget-title"><i class="fa-solid fa-route"></i><div><h4>Route Stops</h4><span id="routeStopSummaryText">Loading stops...</span></div></div>
</div>
<div class="dashboard-widget-body">
<div id="routeStopList" class="route-stop-list"></div>
</div>
</section>
<?php } else { ?>
<div class="dashboard-empty"><i class="fa-solid fa-bus"></i><h3>No live transport access</h3><p>Select an assigned bus or contact the administrator.</p></div>
<?php } ?>

<?php if ($isFleetManager) { ?>
<section class="dashboard-widget">
<div class="dashboard-widget-header"><div class="dashboard-widget-title"><i class="fa-solid fa-list"></i><div><h4>Fleet Status</h4><span>Live status for all buses</span></div></div></div>
<div class="dashboard-widget-body dashboard-table-wrap">
<table class="dashboard-table"><thead><tr><th>Bus</th><th>Driver</th><th>Route</th><th>Status</th><th>Last Update</th><th>Progress</th></tr></thead><tbody id="busTableBody"><tr><td colspan="6">Loading...</td></tr></tbody></table>
</div>
</section>
<?php } ?>

</div>
</div>
</div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="../../assets/js/common.js"></script>
<?php if ($hasTransportAccess) { ?>
<script>
let selectedBusId = <?php echo (int) $selectedBusId; ?>;
const isAdmin = <?php echo $isFleetManager ? 'true' : 'false'; ?>;
const canShareMyLocation = <?php echo $role === 'student' ? 'true' : 'false'; ?>;
let map = L.map('trackingMap').setView([13.6288, 79.4192], 11);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(map);

let busMarker = null;
let myMarker = null;
let myWatch = null;
let routeLine = null;
let stopMarkers = [];
let hasFit = false;

const busIcon = L.divIcon({ html: '<i class="fa-solid fa-bus" style="font-size:30px;color:#2563eb;"></i>', className: '', iconSize: [32,32] });
const myIcon = L.divIcon({ html: '<i class="fa-solid fa-person" style="font-size:26px;color:#0f766e;"></i>', className: '', iconSize: [28,28] });

function escapeHtml(value) {
    return String(value ?? '').replace(/[&<>"']/g, character => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    }[character]));
}

function clearRoute() {
    stopMarkers.forEach(marker => map.removeLayer(marker));
    stopMarkers = [];
    if (routeLine) map.removeLayer(routeLine);
    routeLine = null;
    if (busMarker) {
        map.removeLayer(busMarker);
        busMarker = null;
    }
}

function drawBus(bus) {
    clearRoute();
    const stops = bus.stops || [];
    const coords = stops.filter(stop => stop.latitude && stop.longitude).map(stop => [stop.latitude, stop.longitude]);
    if (coords.length) {
        routeLine = L.polyline(coords, { color: bus.route.route_color || '#2563eb', weight: 5, opacity: 0.85 }).addTo(map);
    }
    stops.forEach(stop => {
        if (!stop.latitude || !stop.longitude) return;
        const icon = L.divIcon({
            html: stop.is_start == 1 ? '<i class="fa-solid fa-flag-checkered" style="color:#16a34a;font-size:20px;"></i>' : (stop.is_end == 1 ? '<i class="fa-solid fa-flag" style="color:#dc2626;font-size:20px;"></i>' : '<i class="fa-solid fa-location-dot" style="color:#f59e0b;font-size:18px;"></i>'),
            className: '',
            iconSize: [24, 24],
        });
        const marker = L.marker([stop.latitude, stop.longitude], { icon }).addTo(map);
        marker.bindTooltip(stop.stop_name || 'Stop', { direction: 'top' });
        stopMarkers.push(marker);
    });

    if (bus.latitude && bus.longitude) {
        const latLng = [bus.latitude, bus.longitude];
        busMarker = L.marker(latLng, { icon: busIcon }).addTo(map);
        busMarker.bindPopup(`<strong>${bus.bus_number || '-'}</strong><br>${bus.route.route_name || '-'}<br>${bus.driver.name || '-'}`);
        if (!hasFit) {
            if (routeLine) map.fitBounds(routeLine.getBounds(), { padding: [40, 40] });
            else map.setView(latLng, 15);
            hasFit = true;
        }
    } else if (!hasFit && routeLine) {
        map.fitBounds(routeLine.getBounds(), { padding: [40, 40] });
        hasFit = true;
    }
}

function statusLabel(status) {
    if (status === 'running') return 'Running';
    if (status === 'completed') return 'Completed';
    return 'Trip Not Started';
}

function formatTripTime(route) {
    const start = route?.start_time || '-';
    const end = route?.end_time || '-';
    return `${start} - ${end}`;
}

function updateTripState(bus) {
    const stateBox = document.getElementById('tripStateBox');
    const stateText = document.getElementById('tripStateText');
    const status = bus.status || 'not_started';
    stateBox.className = `trip-state ${status}`;
    if (status === 'running') {
        stateText.innerText = 'Trip Running - live location and ETA are active.';
    } else if (status === 'completed') {
        stateText.innerText = 'Trip Completed - showing last shared route position.';
    } else {
        stateText.innerText = 'Trip Not Started - complete route and stops are shown below.';
    }
}

function updateStopList(bus) {
    const stops = bus.stops || [];
    document.getElementById('routeStopSummaryText').innerText = stops.length ? `${stops.length} stops on this route` : 'No stops configured for this route';
    document.getElementById('routeStopList').innerHTML = stops.length ? stops.map((stop, index) => {
        const stopType = stop.is_start == 1 ? 'Start' : (stop.is_end == 1 ? 'End' : 'Stop');
        const time = stop.arrival_time || '-';
        const coords = stop.latitude && stop.longitude ? `${Number(stop.latitude).toFixed(5)}, ${Number(stop.longitude).toFixed(5)}` : 'Coordinates not set';
        return `<div class="route-stop-item">
            <span class="route-stop-order">${index + 1}</span>
            <div><div class="route-stop-name">${escapeHtml(stop.stop_name || 'Unnamed Stop')}</div><div class="route-stop-meta">${escapeHtml(stopType)} | Arrival ${escapeHtml(time)} | ${escapeHtml(coords)}</div></div>
            <span class="dashboard-badge badge-info">${stopType}</span>
        </div>`;
    }).join('') : '<div class="dashboard-empty"><i class="fa-solid fa-route"></i><h3>No Stops Configured</h3><p>Add route stops to display the complete trip path.</p></div>';
}

function updateSummary(bus) {
    const status = statusLabel(bus.status);
    const progress = bus.progress || {};
    updateTripState(bus);
    updateStopList(bus);
    document.getElementById('statusText').innerText = status;
    document.getElementById('currentStopText').innerText = progress.current_stop?.stop_name || '-';
    document.getElementById('nextStopText').innerText = progress.next_stop?.stop_name || '-';
    document.getElementById('etaDescription').innerText = progress.eta_minutes !== null && progress.eta_minutes !== undefined ? `ETA ${progress.eta_minutes} min, ${progress.distance_to_next_km} km` : 'ETA unavailable';
    document.getElementById('studentCountText').innerText = bus.student_count || 0;
    document.getElementById('lastUpdateText').innerText = bus.updated_at ? `Last update ${new Date(bus.updated_at).toLocaleString()}` : 'Driver has not shared a location yet.';
    document.getElementById('driverText').innerText = bus.driver.name || '-';
    document.getElementById('phoneText').innerText = bus.driver.phone || '-';
    document.getElementById('routeText').innerText = bus.route.route_name || '-';
    document.getElementById('tripTimeText').innerText = formatTripTime(bus.route);
    document.getElementById('progressText').innerText = `${progress.percent || 0}%`;
    document.getElementById('coordinateText').innerText = bus.latitude && bus.longitude ? `${Number(bus.latitude).toFixed(5)}, ${Number(bus.longitude).toFixed(5)}` : '-';
    document.getElementById('heroBusText').innerText = bus.bus_number || 'Bus';
    document.getElementById('heroRouteText').innerText = bus.route.route_name || 'Route';
    document.getElementById('heroEtaText').innerText = bus.status === 'running' && progress.eta_minutes !== null && progress.eta_minutes !== undefined ? `ETA ${progress.eta_minutes} min` : status;
}

function loadSelectedBus() {
    if (!selectedBusId) return;
    fetch(`fetch_live_location.php?bus_id=${selectedBusId}`)
        .then(response => response.json())
        .then(payload => {
            if (!payload.success || !payload.data) return;
            drawBus(payload.data);
            updateSummary(payload.data);
        })
        .catch(console.error);
}

function loadFleet() {
    if (!isAdmin) return;
    fetch('fetch_live_location.php')
        .then(response => response.json())
        .then(payload => {
            const rows = (payload.data || []).map(bus => {
                const status = statusLabel(bus.status);
                return `<tr>
                    <td><strong>${escapeHtml(bus.bus_number || '-')}</strong><br>${escapeHtml(bus.bus_name || '-')}</td>
                    <td>${escapeHtml(bus.driver.name || '-')}</td>
                    <td>${escapeHtml(bus.route.route_name || '-')}</td>
                    <td><span class="dashboard-badge ${bus.status === 'running' ? 'badge-success' : (bus.status === 'completed' ? 'badge-info' : 'badge-warning')}">${status}</span></td>
                    <td>${escapeHtml(bus.updated_at ? new Date(bus.updated_at).toLocaleString() : '-')}</td>
                    <td>${bus.progress?.percent || 0}%</td>
                </tr>`;
            }).join('');
            document.getElementById('busTableBody').innerHTML = rows || '<tr><td colspan="6">No buses found.</td></tr>';
        })
        .catch(console.error);
}

function toggleMyLocation() {
    const btn = document.getElementById('shareMyLocationBtn');
    if (!btn) return;
    if (myWatch) {
        navigator.geolocation.clearWatch(myWatch);
        myWatch = null;
        if (myMarker) map.removeLayer(myMarker);
        myMarker = null;
        btn.innerHTML = '<i class="fa-solid fa-location-crosshairs"></i> My Location: Off';
        return;
    }
    if (!navigator.geolocation) return;
    btn.innerHTML = '<i class="fa-solid fa-location-crosshairs"></i> My Location: On';
    myWatch = navigator.geolocation.watchPosition(position => {
        const latLng = [position.coords.latitude, position.coords.longitude];
        if (myMarker) myMarker.setLatLng(latLng);
        else myMarker = L.marker(latLng, { icon: myIcon }).addTo(map).bindPopup('My Location');
    }, console.error, { enableHighAccuracy: true, maximumAge: 5000, timeout: 12000 });
}

if (canShareMyLocation) {
    document.getElementById('shareMyLocationBtn')?.addEventListener('click', toggleMyLocation);
}

document.getElementById('bus_id')?.addEventListener('change', event => {
    selectedBusId = Number(event.target.value || 0);
    if (!selectedBusId) return;
    const url = new URL(window.location.href);
    url.searchParams.set('bus_id', selectedBusId);
    window.history.replaceState({}, '', url);
    hasFit = false;
    loadSelectedBus();
});

loadSelectedBus();
loadFleet();
setInterval(() => { loadSelectedBus(); loadFleet(); }, 15000);
</script>
<?php } ?>
</body>
</html>
