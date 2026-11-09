<?php

require_once '../config/auth.php';
require_once '../config/db.php';
require_once '../config/student_development.php';
require_once '../shared/helpers/portal.php';

student_development_ensure_schema($conn);
if (!canView('student_development')) {
    header('Location: ../settings/access-denied.php');
    exit;
}

$role = portal_get_role();
$userId = (int) ($_SESSION['user']['id'] ?? 0);
$isManager = $role === 'admin' || ($role === 'teacher' && canCreate('student_development'));

if ($role === 'admin') {
    header('Location: admin/index.php');
    exit;
}
if ($role === 'teacher') {
    header('Location: teacher/index.php');
    exit;
}
if ($role === 'student') {
    header('Location: student/index.php');
    exit;
}
if ($role === 'parent') {
    header('Location: parent/index.php');
    exit;
}
$csrf = $_SESSION['sd_csrf'] ?? bin2hex(random_bytes(24));
$_SESSION['sd_csrf'] = $csrf;

function sd_e($value): string
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
}

function sd_stmt(mysqli $conn, string $sql, string $types = '', array $values = []): mysqli_stmt
{
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        throw new RuntimeException('Database query preparation failed.');
    }
    if ($types !== '') {
        student_development_bind($stmt, $types, $values);
    }
    mysqli_stmt_execute($stmt);
    return $stmt;
}

function sd_student_for_user(mysqli $conn, int $userId): int
{
    $stmt = sd_stmt($conn, 'SELECT id FROM students WHERE user_id = ? LIMIT 1', 'i', [$userId]);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    return (int) ($row['id'] ?? 0);
}

function sd_parent_children(mysqli $conn, int $userId): array
{
    $stmt = sd_stmt($conn, 'SELECT s.id, s.student_id, u.full_name, c.class_name, sec.section_name FROM parents p JOIN students s ON s.student_id = p.student_id JOIN users u ON u.id = s.user_id LEFT JOIN classes c ON c.class_id = s.class_id LEFT JOIN sections sec ON sec.section_id = s.section_id WHERE p.user_id = ? ORDER BY u.full_name', 'i', [$userId]);
    $items = [];
    $result = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($result)) {
        $items[] = $row;
    }
    return $items;
}

function sd_require_csrf(string $expected): void
{
    if (!hash_equals($expected, $_POST['csrf'] ?? '')) {
        http_response_code(400);
        exit('Invalid request token.');
    }
}

$studentId = $role === 'student' ? sd_student_for_user($conn, $userId) : 0;
$children = $role === 'parent' ? sd_parent_children($conn, $userId) : [];
$childIds = array_map(static fn(array $child): int => (int) $child['id'], $children);
$selectedChild = $role === 'parent' && $childIds
    ? (int) ($_GET['child_id'] ?? $childIds[0])
    : 0;
if ($role === 'parent' && !in_array($selectedChild, $childIds, true)) {
    $selectedChild = $childIds[0] ?? 0;
}
$scopeStudentId = $role === 'student' ? $studentId : ($role === 'parent' ? $selectedChild : 0);

if (isset($_GET['download']) && $_GET['download'] === 'certificates' && ($role === 'admin' || $scopeStudentId > 0)) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="student-achievements.csv"');
    $output = fopen('php://output', 'w');
    fputcsv($output, ['Student', 'Title', 'Category', 'Organization', 'Date', 'Status']);
    $where = $scopeStudentId ? ' WHERE c.student_id = ' . (int) $scopeStudentId : '';
    $report = mysqli_query($conn, 'SELECT u.full_name, c.title, c.category, c.issuing_organization, c.achievement_date, c.verification_status FROM sd_certificates c JOIN students s ON s.id = c.student_id JOIN users u ON u.id = s.user_id' . $where . ' ORDER BY c.achievement_date DESC');
    while ($row = mysqli_fetch_assoc($report)) {
        fputcsv($output, $row);
    }
    fclose($output);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    sd_require_csrf($csrf);
    $action = $_POST['action'] ?? '';
    $targetStudent = $role === 'student' ? $studentId : (int) ($_POST['student_id'] ?? 0);

    if ($action === 'club_apply' && $targetStudent > 0 && $role === 'student') {
        $clubId = (int) ($_POST['club_id'] ?? 0);
        sd_stmt($conn, 'INSERT IGNORE INTO sd_club_applications (club_id, student_id) SELECT club_id, ? FROM sd_clubs WHERE club_id = ? AND registration_open = 1 AND published = 1 AND status = "active"', 'ii', [$targetStudent, $clubId]);
    } elseif ($action === 'sport_apply' && $targetStudent > 0 && $role === 'student') {
        $sportId = (int) ($_POST['sport_id'] ?? 0);
        sd_stmt($conn, 'INSERT IGNORE INTO sd_sport_applications (sport_id, student_id) SELECT sport_id, ? FROM sd_sports WHERE sport_id = ? AND registration_open = 1 AND published = 1 AND status = "active"', 'ii', [$targetStudent, $sportId]);
    } elseif ($action === 'programme_apply' && $targetStudent > 0 && $role === 'student') {
        $programme = in_array($_POST['programme_key'] ?? '', ['ncc', 'nss'], true) ? $_POST['programme_key'] : '';
        if ($programme !== '') {
            sd_stmt($conn, 'INSERT IGNORE INTO sd_programme_applications (programme_key, student_id) SELECT ?, ? FROM sd_programme_settings WHERE programme_key = ? AND registration_open = 1', 'sis', [$programme, $targetStudent, $programme]);
        }
    } elseif ($action === 'club_create' && $isManager) {
        $name = trim($_POST['name'] ?? '');
        $category = in_array($_POST['category'] ?? '', ['cultural', 'technical', 'literary', 'social', 'volunteering', 'other'], true) ? $_POST['category'] : 'other';
        if ($name !== '') {
            sd_stmt($conn, 'INSERT INTO sd_clubs (name, category, description, coordinator_teacher_id) VALUES (?, ?, ?, NULL)', 'sss', [$name, $category, trim($_POST['description'] ?? '')]);
        }
    } elseif ($action === 'sport_create' && $isManager) {
        $name = trim($_POST['name'] ?? '');
        $mode = in_array($_POST['mode'] ?? '', ['indoor', 'outdoor'], true) ? $_POST['mode'] : 'indoor';
        if ($name !== '') {
            sd_stmt($conn, 'INSERT INTO sd_sports (name, mode, practice_schedule) VALUES (?, ?, ?)', 'sss', [$name, $mode, trim($_POST['practice_schedule'] ?? '')]);
        }
    } elseif ($action === 'certificate_review' && $isManager) {
        $status = in_array($_POST['status'] ?? '', ['approved', 'rejected'], true) ? $_POST['status'] : 'rejected';
        sd_stmt($conn, 'UPDATE sd_certificates SET verification_status = ?, verified_by = ?, verified_at = NOW(), reviewer_remarks = ? WHERE certificate_id = ?', 'sisi', [$status, $userId, trim($_POST['remarks'] ?? ''), (int) $_POST['certificate_id']]);
    } elseif ($action === 'certificate_upload' && ($role === 'student' || $isManager)) {
        $file = $_FILES['certificate'] ?? null;
        $uploadStudent = $role === 'student' ? $studentId : (int) ($_POST['student_id'] ?? 0);
        $allowed = ['application/pdf', 'image/jpeg', 'image/png'];
        $mime = $file && $file['error'] === UPLOAD_ERR_OK ? (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']) : '';
        if ($uploadStudent > 0 && trim($_POST['title'] ?? '') !== '' && $file && $file['error'] === UPLOAD_ERR_OK && $file['size'] <= 5 * 1024 * 1024 && in_array($mime, $allowed, true)) {
            $dir = dirname(__DIR__) . '/assets/uploads/certificates';
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $filename = bin2hex(random_bytes(16)) . '.' . $extension;
            if (move_uploaded_file($file['tmp_name'], $dir . '/' . $filename)) {
                $category = in_array($_POST['category'] ?? '', ['technical', 'academic', 'sports', 'cultural', 'extracurricular'], true) ? $_POST['category'] : 'extracurricular';
                sd_stmt($conn, 'INSERT INTO sd_certificates (student_id, category, title, issuing_organization, achievement_date, file_path, mime_type) VALUES (?, ?, ?, ?, ?, ?, ?)', 'issssss', [$uploadStudent, $category, trim($_POST['title']), trim($_POST['organization'] ?? ''), $_POST['achievement_date'] ?: null, 'assets/uploads/certificates/' . $filename, $mime]);
            }
        }
    } elseif ($action === 'review_club' && $isManager) {
        $status = in_array($_POST['status'] ?? '', ['approved', 'rejected'], true) ? $_POST['status'] : 'rejected';
        $applicationId = (int) ($_POST['application_id'] ?? 0);
        $conn->begin_transaction();
        sd_stmt($conn, 'UPDATE sd_club_applications SET status = ?, reviewed_by = ?, reviewed_at = NOW(), review_remarks = ? WHERE application_id = ?', 'sisi', [$status, $userId, trim($_POST['remarks'] ?? ''), $applicationId]);
        if ($status === 'approved') {
            sd_stmt($conn, 'INSERT IGNORE INTO sd_club_members (club_id, student_id, joined_on) SELECT club_id, student_id, CURDATE() FROM sd_club_applications WHERE application_id = ?', 'i', [$applicationId]);
        }
        $conn->commit();
    } elseif ($action === 'review_programme' && $isManager) {
        $status = in_array($_POST['status'] ?? '', ['approved', 'rejected'], true) ? $_POST['status'] : 'rejected';
        $applicationId = (int) ($_POST['application_id'] ?? 0);
        sd_stmt($conn, 'UPDATE sd_programme_applications SET status = ?, reviewed_by = ?, reviewed_at = NOW(), review_remarks = ? WHERE application_id = ?', 'sisi', [$status, $userId, trim($_POST['remarks'] ?? ''), $applicationId]);
        if ($status === 'approved') {
            $rowStmt = sd_stmt($conn, 'SELECT programme_key, student_id FROM sd_programme_applications WHERE application_id = ?', 'i', [$applicationId]);
            $application = mysqli_fetch_assoc(mysqli_stmt_get_result($rowStmt));
            if ($application['programme_key'] === 'ncc') {
                sd_stmt($conn, 'INSERT IGNORE INTO sd_ncc_cadets (student_id, enrollment_no, enrollment_date) VALUES (?, CONCAT("NCC-", ?), CURDATE())', 'ii', [(int) $application['student_id'], $applicationId]);
            } else {
                sd_stmt($conn, 'INSERT IGNORE INTO sd_nss_volunteers (student_id, enrollment_no, enrollment_date) VALUES (?, CONCAT("NSS-", ?), CURDATE())', 'ii', [(int) $application['student_id'], $applicationId]);
            }
        }
    } elseif ($action === 'consent_response' && $role === 'parent') {
        $consentId = (int) ($_POST['consent_id'] ?? 0);
        $response = in_array($_POST['response'] ?? '', ['approved', 'rejected'], true) ? $_POST['response'] : 'rejected';
        $stmt = sd_stmt($conn, 'UPDATE sd_parent_consents pc JOIN students s ON s.id = pc.student_id JOIN parents p ON p.student_id = s.student_id SET pc.response = ?, pc.responded_by = ?, pc.responded_at = NOW() WHERE pc.consent_id = ? AND p.user_id = ?', 'siii', [$response, $userId, $consentId, $userId]);
    }
    header('Location: ' . $_SERVER['REQUEST_URI']);
    exit;
}

$tab = $_GET['tab'] ?? 'overview';
$validTabs = ['overview', 'clubs', 'sports', 'achievements', 'ncc', 'nss'];
if (!in_array($tab, $validTabs, true)) {
    $tab = 'overview';
}

$stats = [];
if ($role === 'admin') {
    $queries = [
        'clubs' => 'SELECT COUNT(*) total FROM sd_clubs WHERE status = "active"',
        'members' => 'SELECT COUNT(*) total FROM sd_club_members WHERE status = "active"',
        'sports' => 'SELECT COUNT(*) total FROM sd_sports WHERE status = "active"',
        'players' => 'SELECT COUNT(DISTINCT student_id) total FROM sd_sport_applications WHERE status = "selected"',
        'teams' => 'SELECT COUNT(*) total FROM sd_sports_teams',
        'certificates' => 'SELECT COUNT(*) total FROM sd_certificates',
        'ncc' => 'SELECT COUNT(*) total FROM sd_ncc_cadets WHERE status = "active"',
        'nss' => 'SELECT COUNT(*) total FROM sd_nss_volunteers WHERE status = "active"',
        'pending' => 'SELECT (SELECT COUNT(*) FROM sd_club_applications WHERE status = "pending") + (SELECT COUNT(*) FROM sd_sport_applications WHERE status = "pending") + (SELECT COUNT(*) FROM sd_programme_applications WHERE status = "pending") total',
    ];
    foreach ($queries as $key => $query) {
        $row = mysqli_fetch_assoc(mysqli_query($conn, $query));
        $stats[$key] = (int) ($row['total'] ?? 0);
    }
}

$clubsQuery = $role === 'admin'
    ? 'SELECT c.*, COUNT(DISTINCT cm.membership_id) member_count FROM sd_clubs c LEFT JOIN sd_club_members cm ON cm.club_id = c.club_id AND cm.status = "active" GROUP BY c.club_id ORDER BY c.name'
    : 'SELECT c.*, cm.status membership_status FROM sd_clubs c LEFT JOIN sd_club_members cm ON cm.club_id = c.club_id AND cm.student_id = ' . (int) $scopeStudentId . ' WHERE c.status = "active" AND c.published = 1 ORDER BY c.name';
$clubs = mysqli_query($conn, $clubsQuery);
$sports = mysqli_query($conn, $role === 'admin' ? 'SELECT * FROM sd_sports ORDER BY name' : 'SELECT sp.*, sa.status application_status FROM sd_sports sp LEFT JOIN sd_sport_applications sa ON sa.sport_id = sp.sport_id AND sa.student_id = ' . (int) $scopeStudentId . ' WHERE sp.status = "active" AND sp.published = 1 ORDER BY sp.name');
$certificates = mysqli_query($conn, 'SELECT c.*, u.full_name FROM sd_certificates c JOIN students s ON s.id = c.student_id JOIN users u ON u.id = s.user_id' . ($scopeStudentId ? ' WHERE c.student_id = ' . (int) $scopeStudentId : '') . ' ORDER BY c.created_at DESC');
$programmes = mysqli_query($conn, 'SELECT ps.*, pa.status application_status FROM sd_programme_settings ps LEFT JOIN sd_programme_applications pa ON pa.programme_key = ps.programme_key AND pa.student_id = ' . (int) $scopeStudentId . ' ORDER BY ps.programme_key');
$pendingClub = $isManager ? mysqli_query($conn, 'SELECT a.application_id, a.applied_at, c.name club_name, u.full_name student_name FROM sd_club_applications a JOIN sd_clubs c ON c.club_id = a.club_id JOIN students s ON s.id = a.student_id JOIN users u ON u.id = s.user_id WHERE a.status = "pending" ORDER BY a.applied_at') : false;
$pendingProgramme = $isManager ? mysqli_query($conn, 'SELECT a.application_id, a.programme_key, a.applied_at, u.full_name student_name FROM sd_programme_applications a JOIN students s ON s.id = a.student_id JOIN users u ON u.id = s.user_id WHERE a.status = "pending" ORDER BY a.applied_at') : false;
$consents = $role === 'parent' && $scopeStudentId ? mysqli_query($conn, 'SELECT * FROM sd_parent_consents WHERE student_id = ' . (int) $scopeStudentId . ' ORDER BY created_at DESC') : false;

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Student Development | MindMerge</title>
<link rel="stylesheet" href="../assets/css/global.css"><link rel="stylesheet" href="../assets/css/layout.css"><link rel="stylesheet" href="../assets/css/components.css"><link rel="stylesheet" href="../assets/css/dashboard-components.css"><link rel="stylesheet" href="../assets/css/portals.css"><link rel="stylesheet" href="../assets/css/material-theme.css"><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>.sd-nav{display:flex;gap:8px;flex-wrap:wrap;margin:20px 0}.sd-nav a{padding:10px 14px;border:1px solid var(--border-color);border-radius:10px;text-decoration:none;color:var(--text-primary)}.sd-nav a.active{background:#2563eb;color:#fff}.sd-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(190px,1fr));gap:15px}.sd-card{background:var(--card);border:1px solid var(--border-color);border-radius:16px;padding:18px;margin-bottom:18px}.sd-card h2,.sd-card h3{margin-top:0}.sd-value{font-size:28px;font-weight:800}.sd-list{display:grid;gap:10px}.sd-row{display:flex;justify-content:space-between;align-items:center;gap:14px;padding:13px;border:1px solid var(--border-color);border-radius:10px}.sd-muted{color:var(--text-secondary);font-size:13px}.sd-form{display:flex;gap:8px;flex-wrap:wrap;align-items:center}.sd-form input,.sd-form select,.sd-form textarea{padding:10px;border:1px solid var(--border-color);border-radius:8px;background:var(--surface);color:var(--text-primary)}.sd-status{font-size:12px;font-weight:700}.sd-empty{padding:25px;text-align:center;color:var(--text-secondary)}@media(max-width:700px){.sd-row{align-items:flex-start;flex-direction:column}.sd-form>*{width:100%}}</style>
</head>
<body>
<div class="app-layout"><?php include('../partials/sidebar.php'); ?><div class="main-content"><?php include('../partials/topbar.php'); ?><main class="page-content">
<div class="page-header"><div><h1><?php echo $role === 'admin' ? 'Student Development Management' : ($role === 'parent' ? "Child's Development" : 'My Development'); ?></h1><p><?php echo $role === 'admin' ? 'School-wide activities, sports, achievements, NCC and NSS workflows.' : 'Your authorized activities and official development records.'; ?></p></div></div>
<?php if ($role === 'parent' && $children) { ?><form class="sd-form" method="get"><input type="hidden" name="tab" value="<?php echo sd_e($tab); ?>"><label>Child <select name="child_id" onchange="this.form.submit()"><?php foreach ($children as $child) { ?><option value="<?php echo (int) $child['id']; ?>" <?php echo $selectedChild === (int) $child['id'] ? 'selected' : ''; ?>><?php echo sd_e($child['full_name']); ?></option><?php } ?></select></label></form><?php } ?>
<nav class="sd-nav"><?php foreach ($validTabs as $item) { ?><a class="<?php echo $tab === $item ? 'active' : ''; ?>" href="?tab=<?php echo sd_e($item); ?><?php echo $selectedChild ? '&child_id=' . $selectedChild : ''; ?>"><?php echo sd_e(ucwords($item)); ?></a><?php } ?></nav>

<?php if ($role === 'admin' && $tab === 'overview') { ?><div class="sd-grid"><?php foreach (['clubs'=>'Clubs','members'=>'Active Members','sports'=>'Sports','players'=>'Registered Players','teams'=>'Teams','certificates'=>'Certificates','ncc'=>'NCC Cadets','nss'=>'NSS Volunteers','pending'=>'Pending Applications'] as $key => $label) { ?><a class="sd-card" href="?tab=<?php echo $key === 'clubs' || $key === 'members' ? 'clubs' : ($key === 'sports' || $key === 'players' || $key === 'teams' ? 'sports' : ($key === 'certificates' ? 'achievements' : ($key === 'ncc' ? 'ncc' : ($key === 'nss' ? 'nss' : 'overview')))); ?>"><h3><?php echo sd_e($label); ?></h3><div class="sd-value"><?php echo number_format($stats[$key]); ?></div></a><?php } ?></div>
<?php } elseif ($tab === 'clubs') { ?><section class="sd-card"><h2><?php echo $role === 'admin' ? 'Clubs, members and applications' : 'Club opportunities and memberships'; ?></h2><?php if ($isManager) { ?><form method="post" class="sd-form"><input type="hidden" name="csrf" value="<?php echo sd_e($csrf); ?>"><input type="hidden" name="action" value="club_create"><input name="name" placeholder="Club name" required><select name="category"><option value="cultural">Cultural</option><option value="technical">Technical</option><option value="literary">Literary</option><option value="social">Social</option><option value="volunteering">Volunteering</option></select><input name="description" placeholder="Description"><button class="btn btn-primary">Create club</button></form><?php } ?><div class="sd-list"><?php while ($club = mysqli_fetch_assoc($clubs)) { ?><div class="sd-row"><div><strong><?php echo sd_e($club['name']); ?></strong><div class="sd-muted"><?php echo sd_e(ucfirst($club['category'])); ?> · <?php echo sd_e($club['description']); ?><?php if ($role === 'admin') { ?> · <?php echo (int) $club['member_count']; ?> active members<?php } ?></div></div><?php if ($role === 'student') { ?><span class="sd-status"><?php echo sd_e($club['membership_status'] ?? 'Not Registered'); ?></span><?php if (($club['membership_status'] ?? '') === '' && (int) $club['registration_open'] === 1) { ?><form method="post" class="sd-form"><input type="hidden" name="csrf" value="<?php echo sd_e($csrf); ?>"><input type="hidden" name="action" value="club_apply"><input type="hidden" name="club_id" value="<?php echo (int) $club['club_id']; ?>"><button class="btn btn-primary">Apply</button></form><?php } ?><?php } ?></div><?php } ?></div><?php if ($isManager && $pendingClub) { ?><h3>Pending club applications</h3><div class="sd-list"><?php while ($app = mysqli_fetch_assoc($pendingClub)) { ?><div class="sd-row"><span><?php echo sd_e($app['student_name'] . ' · ' . $app['club_name']); ?></span><form method="post" class="sd-form"><input type="hidden" name="csrf" value="<?php echo sd_e($csrf); ?>"><input type="hidden" name="action" value="review_club"><input type="hidden" name="application_id" value="<?php echo (int) $app['application_id']; ?>"><button name="status" value="approved" class="btn btn-primary">Approve</button><button name="status" value="rejected" class="btn">Reject</button></form></div><?php } ?></div><?php } ?></section>
<?php } elseif ($tab === 'sports') { ?><section class="sd-card"><h2>Sports and trial applications</h2><?php if ($isManager) { ?><form method="post" class="sd-form"><input type="hidden" name="csrf" value="<?php echo sd_e($csrf); ?>"><input type="hidden" name="action" value="sport_create"><input name="name" placeholder="Sport name" required><select name="mode"><option value="indoor">Indoor</option><option value="outdoor">Outdoor</option></select><input name="practice_schedule" placeholder="Practice schedule"><button class="btn btn-primary">Create sport</button></form><?php } ?><div class="sd-list"><?php while ($sport = mysqli_fetch_assoc($sports)) { ?><div class="sd-row"><div><strong><?php echo sd_e($sport['name']); ?></strong><div class="sd-muted"><?php echo sd_e(ucfirst($sport['mode'])); ?> · <?php echo sd_e($sport['practice_schedule']); ?></div></div><?php if ($role === 'student') { ?><span class="sd-status"><?php echo sd_e($sport['application_status'] ?? 'Not Registered'); ?></span><?php if (empty($sport['application_status']) && (int) $sport['registration_open'] === 1) { ?><form method="post" class="sd-form"><input type="hidden" name="csrf" value="<?php echo sd_e($csrf); ?>"><input type="hidden" name="action" value="sport_apply"><input type="hidden" name="sport_id" value="<?php echo (int) $sport['sport_id']; ?>"><button class="btn btn-primary">Apply for trial</button></form><?php } ?><?php } ?></div><?php } ?></div><p class="sd-muted">Trial applications never create team membership automatically; selection is recorded by an authorized coach or administrator.</p></section>
<?php } elseif ($tab === 'achievements') { ?><section class="sd-card"><h2>Certificates and achievements</h2><p><a href="?tab=achievements&amp;download=certificates<?php echo $scopeStudentId ? '&amp;child_id=' . $scopeStudentId : ''; ?>">Download authorized report (CSV)</a></p><form method="post" enctype="multipart/form-data" class="sd-form"><input type="hidden" name="csrf" value="<?php echo sd_e($csrf); ?>"><input type="hidden" name="action" value="certificate_upload"><?php if ($isManager) { ?><input name="student_id" type="number" placeholder="Student record ID" required><?php } ?><input name="title" placeholder="Certificate title" required><input name="organization" placeholder="Issuing organization"><input name="achievement_date" type="date"><select name="category"><option value="technical">Technical</option><option value="academic">Academic</option><option value="sports">Sports</option><option value="cultural">Cultural</option><option value="extracurricular">Extracurricular</option></select><input name="certificate" type="file" accept=".pdf,.jpg,.jpeg,.png" required><button class="btn btn-primary">Upload certificate</button></form><div class="sd-list"><?php while ($certificate = mysqli_fetch_assoc($certificates)) { ?><div class="sd-row"><div><strong><?php echo sd_e($certificate['title']); ?></strong><div class="sd-muted"><?php echo sd_e($certificate['full_name']); ?> · <?php echo sd_e(ucfirst($certificate['category'])); ?> · <?php echo sd_e($certificate['verification_status']); ?></div></div><div><a href="../<?php echo sd_e($certificate['file_path']); ?>" target="_blank" rel="noopener">View certificate</a><?php if ($isManager && $certificate['verification_status'] === 'pending') { ?><form method="post" class="sd-form"><input type="hidden" name="csrf" value="<?php echo sd_e($csrf); ?>"><input type="hidden" name="action" value="certificate_review"><input type="hidden" name="certificate_id" value="<?php echo (int) $certificate['certificate_id']; ?>"><button name="status" value="approved" class="btn btn-primary">Approve</button><button name="status" value="rejected" class="btn">Reject</button></form><?php } ?></div></div><?php } if (mysqli_num_rows($certificates) === 0) { ?><div class="sd-empty">No certificates are available in your authorized scope.</div><?php } ?></div></section>
<?php } elseif ($tab === 'ncc' || $tab === 'nss') { $programmeKey = $tab; ?><section class="sd-card"><h2><?php echo strtoupper($programmeKey); ?></h2><?php $programme = mysqli_fetch_assoc($programmes); ?><p><?php echo sd_e($programme['overview'] ?? 'Programme information is not available.'); ?></p><p class="sd-muted"><?php echo sd_e($programme['eligibility'] ?? 'Eligibility is reviewed by the authorized coordinator.'); ?></p><?php if ($role === 'student') { ?><p><strong>Status:</strong> <?php echo sd_e($programme['application_status'] ?? 'Not Enrolled'); ?></p><?php if (empty($programme['application_status']) && (int) ($programme['registration_open'] ?? 0) === 1) { ?><form method="post" class="sd-form"><input type="hidden" name="csrf" value="<?php echo sd_e($csrf); ?>"><input type="hidden" name="action" value="programme_apply"><input type="hidden" name="programme_key" value="<?php echo sd_e($programmeKey); ?>"><button class="btn btn-primary">Submit enrollment application</button></form><?php } ?><?php } ?><?php if ($isManager && $pendingProgramme) { ?><h3>Pending applications</h3><div class="sd-list"><?php while ($app = mysqli_fetch_assoc($pendingProgramme)) { if ($app['programme_key'] !== $programmeKey) continue; ?><div class="sd-row"><span><?php echo sd_e($app['student_name']); ?></span><form method="post" class="sd-form"><input type="hidden" name="csrf" value="<?php echo sd_e($csrf); ?>"><input type="hidden" name="action" value="review_programme"><input type="hidden" name="application_id" value="<?php echo (int) $app['application_id']; ?>"><button name="status" value="approved" class="btn btn-primary">Approve</button><button name="status" value="rejected" class="btn">Reject</button></form></div><?php } ?></div><?php } ?></section>
<?php } elseif ($tab === 'overview') { ?><div class="sd-grid"><div class="sd-card"><h3><?php echo $role === 'parent' ? "Child's clubs" : 'My clubs'; ?></h3><p>View memberships, participation and open opportunities.</p></div><div class="sd-card"><h3><?php echo $role === 'parent' ? "Child's sports" : 'My sports'; ?></h3><p>View trials, teams, coaches and schedules.</p></div><div class="sd-card"><h3><?php echo $role === 'parent' ? "Child's achievements" : 'My achievements'; ?></h3><p>Official certificates and verification status.</p></div><div class="sd-card"><h3>NCC / NSS</h3><p>Separate programme status, activities and attendance.</p></div></div><?php if ($role === 'parent' && $consents) { ?><section class="sd-card"><h2>Permission and consent requests</h2><div class="sd-list"><?php while ($consent = mysqli_fetch_assoc($consents)) { ?><div class="sd-row"><div><strong><?php echo sd_e($consent['request_type']); ?></strong><div class="sd-muted"><?php echo sd_e($consent['request_text']); ?> · <?php echo sd_e(ucfirst($consent['response'])); ?></div></div><?php if ($consent['response'] === 'pending') { ?><form method="post" class="sd-form"><input type="hidden" name="csrf" value="<?php echo sd_e($csrf); ?>"><input type="hidden" name="action" value="consent_response"><input type="hidden" name="consent_id" value="<?php echo (int) $consent['consent_id']; ?>"><button name="response" value="approved" class="btn btn-primary">Approve</button><button name="response" value="rejected" class="btn">Reject</button></form><?php } ?></div><?php } ?></div></section><?php } } ?>
</main></div></div>
<script src="../assets/js/common.js"></script>
</body>
</html>
