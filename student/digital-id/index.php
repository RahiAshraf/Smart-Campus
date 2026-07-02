<?php

require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../shared/services/StudentDashboardService.php';
require_once __DIR__ . '/../../shared/services/ProfileService.php';
require_once __DIR__ . '/../../shared/helpers/portal.php';

portal_require_role(['student']);

$service = new StudentDashboardService($conn, (int) $_SESSION['user']['id']);
$student = $service->getStudent();
$profileService = new ProfileService($conn);
$photoUrl = $profileService->getPhotoUrl($_SESSION['user']);
$studentCode = $student['student_id'] ?? '';
$signature = hash_hmac('sha256', $studentCode, 'mindmerge-smartcampus-qr-v1');
$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$verifyUrl = $scheme . '://' . $host . BASE_URL . 'verification/index.php?sid=' . urlencode($studentCode) . '&sig=' . urlencode($signature);
$qrData = urlencode($verifyUrl);

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Digital ID | MindMerge</title>
<link rel="stylesheet" href="../../assets/css/global.css">
<link rel="stylesheet" href="../../assets/css/layout.css">
<link rel="stylesheet" href="../../assets/css/components.css">
<link rel="stylesheet" href="../../assets/css/portals.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="../../assets/css/material-theme.css">
</head>
<body>
<div class="app-layout">
<?php include('../../partials/sidebar.php'); ?>
<div class="main-content">
<?php include('../../partials/topbar.php'); ?>
<div class="page-content">
<div class="page-header no-print">
<div><h1>Digital ID</h1><p>Your official student identification card.</p></div>
<div style="display:flex;gap:10px;">
<button onclick="window.print()" class="btn btn-primary"><i class="fa-solid fa-print"></i> Print</button>
</div>
</div>
<div class="digital-id-card" id="digitalIdCard">
<div class="digital-id-header">
<h2>MindMerge SmartCampus</h2>
<p>Student Identity Card</p>
</div>
<?php if ($photoUrl) { ?>
<img src="<?php echo htmlspecialchars($photoUrl); ?>" alt="Photo" class="digital-id-photo">
<?php } else { ?>
<div class="digital-id-letter"><?php echo strtoupper(substr($_SESSION['user']['full_name'] ?? 'S', 0, 1)); ?></div>
<?php } ?>
<h3 style="text-align:center;margin-bottom:16px;"><?php echo htmlspecialchars($_SESSION['user']['full_name'] ?? ''); ?></h3>
<div class="digital-id-details">
<p><strong>Student ID:</strong> <?php echo htmlspecialchars($student['student_id'] ?? '-'); ?></p>
<p><strong>Class:</strong> <?php echo htmlspecialchars($student['class_name'] ?? '-'); ?></p>
<p><strong>Section:</strong> <?php echo htmlspecialchars($student['section_name'] ?? '-'); ?></p>
<p><strong>Department:</strong> <?php echo htmlspecialchars($student['class_name'] ?? '-'); ?></p>
</div>
<div style="text-align:center;margin-top:20px;">
<div class="digital-id-qr">
<img src="https://api.qrserver.com/v1/create-qr-code/?size=120x120&data=<?php echo $qrData; ?>" alt="QR Code" width="120" height="120">
</div>
</div>
</div>
</div></div></div>
<script src="../../assets/js/common.js"></script>
</body>
</html>
