<?php

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/constants.php';

function verification_signature(string $student_code): string
{
    return hash_hmac('sha256', $student_code, 'mindmerge-smartcampus-qr-v1');
}

$student_code = trim($_GET['sid'] ?? '');
$signature = trim($_GET['sig'] ?? '');
$valid = $student_code !== '' && hash_equals(verification_signature($student_code), $signature);
$student = null;

if ($valid) {
    $student_code_esc = mysqli_real_escape_string($conn, $student_code);
    $student = mysqli_fetch_assoc(mysqli_query(
        $conn,
        "SELECT st.student_id, st.class_name, st.section_name, u.full_name, u.profile_photo
         FROM students st
         INNER JOIN users u ON u.id = st.user_id
         WHERE st.student_id = '$student_code_esc'
         LIMIT 1"
    ));
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Student Verification | MindMerge</title>
<link rel="stylesheet" href="../assets/css/global.css">
<link rel="stylesheet" href="../assets/css/components.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="../../assets/css/material-theme.css">
<style>
.verify-page{min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px;background:var(--bg);}
.verify-card{max-width:480px;width:100%;background:var(--card);border-radius:18px;padding:30px;box-shadow:var(--shadow);text-align:center;border:1px solid rgba(148,163,184,.15);}
.verify-photo,.verify-letter{width:112px;height:112px;border-radius:50%;margin:0 auto 18px;object-fit:cover;}
.verify-letter{display:flex;align-items:center;justify-content:center;background:var(--primary);color:white;font-size:42px;font-weight:700;}
.verify-status{display:inline-flex;align-items:center;gap:8px;margin:14px 0 22px;padding:9px 14px;border-radius:999px;font-weight:700;background:#dcfce7;color:#166534;}
.verify-status.invalid{background:#fee2e2;color:#991b1b;}
.verify-details{text-align:left;margin-top:20px;border-top:1px solid rgba(148,163,184,.2);padding-top:18px;}
.verify-details p{display:flex;justify-content:space-between;gap:16px;margin-bottom:10px;}
</style>
</head>
<body>
<div class="verify-page">
<div class="verify-card">
<i class="fa-solid fa-shield-check" style="font-size:42px;color:var(--primary);margin-bottom:14px;"></i>
<h1>Verification</h1>
<?php if (!$valid || !$student) { ?>
<div class="verify-status invalid"><i class="fa-solid fa-circle-xmark"></i> Invalid ID</div>
<p>This verification link is invalid or the student record is unavailable.</p>
<?php } else { ?>
<?php
$photo = $student['profile_photo'] ?? 'default.png';
$photoPath = __DIR__ . '/../assets/uploads/profile/' . $photo;
?>
<?php if ($photo !== 'default.png' && $photo !== 'default.svg' && file_exists($photoPath)) { ?>
<img class="verify-photo" src="<?php echo BASE_URL; ?>assets/uploads/profile/<?php echo htmlspecialchars($photo); ?>" alt="Student photo">
<?php } else { ?>
<div class="verify-letter"><?php echo strtoupper(substr($student['full_name'], 0, 1)); ?></div>
<?php } ?>
<h2><?php echo htmlspecialchars($student['full_name']); ?></h2>
<div class="verify-status"><i class="fa-solid fa-circle-check"></i> Verified Student</div>
<div class="verify-details">
<p><strong>Student ID</strong><span><?php echo htmlspecialchars($student['student_id']); ?></span></p>
<p><strong>Class</strong><span><?php echo htmlspecialchars($student['class_name'] ?? '-'); ?></span></p>
<p><strong>Section</strong><span><?php echo htmlspecialchars($student['section_name'] ?? '-'); ?></span></p>
<p><strong>Status</strong><span>Active</span></p>
</div>
<?php } ?>
</div>
</div>
</body>
</html>
