<?php

require_once __DIR__ . '/_bootstrap.php';
require_once __DIR__ . '/_layout.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    sd_admin_require_csrf($sd_admin_csrf);
    $action = $_POST['action'] ?? '';
    if ($action === 'save_certificate') {
        $id = (int) ($_POST['certificate_id'] ?? 0);
        $studentId = (int) ($_POST['student_id'] ?? 0);
        $category = $_POST['category'] ?? '';
        $title = trim($_POST['title'] ?? '');
        $date = $_POST['achievement_date'] ?: null;
        $status = $_POST['verification_status'] ?? 'pending';
        $categories = ['technical', 'academic', 'sports', 'cultural', 'extracurricular'];
        if ($studentId <= 0 || $title === '' || !in_array($category, $categories, true) || !in_array($status, ['pending', 'approved', 'rejected'], true)) {
            sd_admin_flash('Enter valid student, category, title and status.', 'error');
        } elseif ($id > 0) {
            sd_admin_stmt($conn, 'UPDATE sd_certificates SET student_id = ?, category = ?, title = ?, issuing_organization = ?, achievement_date = ?, verification_status = ?, verified_by = ?, verified_at = NOW(), reviewer_remarks = ? WHERE certificate_id = ?', 'isssssssi', [$studentId, $category, $title, trim($_POST['organization'] ?? ''), $date, $status, $sd_admin_user_id, trim($_POST['remarks'] ?? ''), $id]);
            sd_admin_flash('Certification updated.');
        } else {
            sd_admin_stmt($conn, 'INSERT INTO sd_certificates (student_id, category, title, issuing_organization, achievement_date, file_path, mime_type, verification_status, verified_by, verified_at, reviewer_remarks) VALUES (?, ?, ?, ?, ?, "admin-record", "text/plain", ?, ?, NOW(), ?)', 'isssssis', [$studentId, $category, $title, trim($_POST['organization'] ?? ''), $date, $status, $sd_admin_user_id, trim($_POST['remarks'] ?? '')]);
            sd_admin_flash('Certification added.');
        }
    } elseif ($action === 'delete_certificate') {
        sd_admin_stmt($conn, 'DELETE FROM sd_certificates WHERE certificate_id = ?', 'i', [(int) $_POST['certificate_id']]);
        sd_admin_flash('Certification deleted.');
    } elseif ($action === 'verify_certificate') {
        $status = $_POST['status'] ?? '';
        if (in_array($status, ['approved', 'rejected'], true)) {
            sd_admin_stmt($conn, 'UPDATE sd_certificates SET verification_status = ?, verified_by = ?, verified_at = NOW(), reviewer_remarks = ? WHERE certificate_id = ?', 'sisi', [$status, $sd_admin_user_id, trim($_POST['remarks'] ?? ''), (int) $_POST['certificate_id']]);
            sd_admin_flash('Certification verification updated.');
        }
    } elseif ($action === 'delete_achievement') {
        sd_admin_stmt($conn, 'DELETE FROM sd_sports_achievements WHERE achievement_id = ?', 'i', [(int) $_POST['achievement_id']]);
        sd_admin_flash('Achievement deleted.');
    }
    sd_admin_redirect('achievements.php');
}

$search = trim($_GET['search'] ?? '');
$category = $_GET['category'] ?? '';
$editId = (int) ($_GET['edit'] ?? 0);
$students = sd_admin_rows(sd_admin_stmt($conn, 'SELECT s.id, s.student_id, u.full_name FROM students s JOIN users u ON u.id = s.user_id ORDER BY u.full_name'));
$certSql = 'SELECT c.*, u.full_name AS student_name, s.student_id AS admission_no FROM sd_certificates c JOIN students s ON s.id = c.student_id JOIN users u ON u.id = s.user_id WHERE 1 = 1';
$params = [];
$types = '';
if ($search !== '') {
    $certSql .= ' AND (u.full_name LIKE ? OR c.title LIKE ?)';
    $term = '%' . $search . '%';
    $params = [$term, $term];
    $types = 'ss';
}
if (in_array($category, ['technical', 'academic', 'sports', 'cultural', 'extracurricular'], true)) {
    $certSql .= ' AND c.category = ?';
    $params[] = $category;
    $types .= 's';
}
$certSql .= ' ORDER BY c.created_at DESC';
$certificates = sd_admin_rows(sd_admin_stmt($conn, $certSql, $types, $params));
$editCertificate = $editId ? (sd_admin_rows(sd_admin_stmt($conn, 'SELECT * FROM sd_certificates WHERE certificate_id = ?', 'i', [$editId]))[0] ?? null) : null;
$sportsAchievements = sd_admin_rows(sd_admin_stmt($conn, 'SELECT a.*, u.full_name AS student_name, sp.name AS sport_name FROM sd_sports_achievements a JOIN students s ON s.id = a.student_id JOIN users u ON u.id = s.user_id LEFT JOIN sd_sports sp ON sp.sport_id = a.sport_id ORDER BY a.achieved_on DESC'));
$categoryStats = sd_admin_rows(sd_admin_stmt($conn, 'SELECT category, COUNT(*) AS total FROM sd_certificates GROUP BY category ORDER BY category'));

sd_admin_header('Certifications & Achievements', 'Review student certifications, achievements and verification status.', 'achievements');
?>
<section class="sd-admin-grid"><?php foreach ($categoryStats as $stat): ?><div class="sd-admin-card"><h3><?php echo sd_admin_e(ucfirst($stat['category'])); ?></h3><div class="sd-admin-value"><?php echo (int) $stat['total']; ?></div><span class="sd-admin-muted">certifications</span></div><?php endforeach; ?><?php if (!$categoryStats): ?><div class="sd-admin-card"><h3>Certification statistics</h3><div class="sd-admin-value">0</div></div><?php endif; ?></section>
<section class="sd-admin-section sd-admin-card">
    <div class="sd-admin-section-header"><h2>Certifications</h2></div>
    <form class="sd-admin-toolbar" method="get"><input name="search" value="<?php echo sd_admin_e($search); ?>" placeholder="Search student or title"><select name="category"><option value="">All certificate types</option><?php foreach (['technical','academic','sports','cultural','extracurricular'] as $item): ?><option value="<?php echo $item; ?>" <?php echo $category === $item ? 'selected' : ''; ?>><?php echo ucfirst($item); ?></option><?php endforeach; ?></select><button class="btn btn-primary">Filter</button><a class="btn btn-outline-primary" href="achievements.php">Reset</a></form>
    <form class="sd-admin-form" method="post"><input type="hidden" name="csrf" value="<?php echo sd_admin_e($sd_admin_csrf); ?>"><input type="hidden" name="action" value="save_certificate"><input type="hidden" name="certificate_id" value="<?php echo (int) ($editCertificate['certificate_id'] ?? 0); ?>"><label>Student<select name="student_id" required><option value="">Select student</option><?php foreach ($students as $student): ?><option value="<?php echo (int) $student['id']; ?>" <?php echo (int) ($editCertificate['student_id'] ?? 0) === (int) $student['id'] ? 'selected' : ''; ?>><?php echo sd_admin_e($student['full_name'] . ' (' . $student['student_id'] . ')'); ?></option><?php endforeach; ?></select></label><label>Type<select name="category"><?php foreach (['technical','academic','sports','cultural','extracurricular'] as $item): ?><option value="<?php echo $item; ?>" <?php echo ($editCertificate['category'] ?? '') === $item ? 'selected' : ''; ?>><?php echo ucfirst($item); ?></option><?php endforeach; ?></select></label><label>Title<input name="title" required value="<?php echo sd_admin_e($editCertificate['title'] ?? ''); ?>"></label><label>Date<input type="date" name="achievement_date" value="<?php echo sd_admin_e($editCertificate['achievement_date'] ?? ''); ?>"></label><label>Organization<input name="organization" value="<?php echo sd_admin_e($editCertificate['issuing_organization'] ?? ''); ?>"></label><label>Status<select name="verification_status"><option value="pending">Pending</option><option value="approved" <?php echo ($editCertificate['verification_status'] ?? '') === 'approved' ? 'selected' : ''; ?>>Approved</option><option value="rejected" <?php echo ($editCertificate['verification_status'] ?? '') === 'rejected' ? 'selected' : ''; ?>>Rejected</option></select></label><button class="btn btn-primary"><?php echo $editCertificate ? 'Update record' : 'Add record'; ?></button><?php if ($editCertificate): ?><a class="btn btn-outline-primary" href="achievements.php">Cancel</a><?php endif; ?></form>
    <div class="sd-admin-table-wrap"><table class="sd-admin-table"><thead><tr><th>Student</th><th>Type</th><th>Achievement</th><th>Date</th><th>Status</th><th>Actions</th></tr></thead><tbody><?php foreach ($certificates as $certificate): ?><tr><td><?php echo sd_admin_e($certificate['student_name']); ?><br><span class="sd-admin-muted"><?php echo sd_admin_e($certificate['admission_no']); ?></span></td><td><?php echo sd_admin_e(ucfirst($certificate['category'])); ?></td><td><?php echo sd_admin_e($certificate['title']); ?><br><span class="sd-admin-muted"><?php echo sd_admin_e($certificate['issuing_organization']); ?></span></td><td><?php echo sd_admin_e($certificate['achievement_date']); ?></td><td><?php echo sd_admin_e(ucfirst($certificate['verification_status'])); ?></td><td><div class="sd-admin-actions"><a class="btn btn-sm btn-outline-primary" href="?edit=<?php echo (int) $certificate['certificate_id']; ?>">Edit</a><?php if ($certificate['verification_status'] === 'pending'): ?><form method="post"><input type="hidden" name="csrf" value="<?php echo sd_admin_e($sd_admin_csrf); ?>"><input type="hidden" name="action" value="verify_certificate"><input type="hidden" name="certificate_id" value="<?php echo (int) $certificate['certificate_id']; ?>"><button class="btn btn-sm btn-primary" name="status" value="approved">Verify</button><button class="btn btn-sm btn-outline-danger" name="status" value="rejected">Reject</button></form><?php endif; ?><form method="post" onsubmit="return confirm('Delete this certification?');"><input type="hidden" name="csrf" value="<?php echo sd_admin_e($sd_admin_csrf); ?>"><input type="hidden" name="action" value="delete_certificate"><input type="hidden" name="certificate_id" value="<?php echo (int) $certificate['certificate_id']; ?>"><button class="btn btn-sm btn-outline-danger">Delete</button></form></div></td></tr><?php endforeach; ?><?php if (!$certificates): ?><tr><td colspan="6">No certifications found.</td></tr><?php endif; ?></tbody></table></div>
</section>
<section class="sd-admin-section sd-admin-card"><div class="sd-admin-section-header"><h2>Sports achievements</h2></div><div class="sd-admin-table-wrap"><table class="sd-admin-table"><thead><tr><th>Student</th><th>Sport</th><th>Title</th><th>Award level</th><th>Date</th><th>Action</th></tr></thead><tbody><?php foreach ($sportsAchievements as $achievement): ?><tr><td><?php echo sd_admin_e($achievement['student_name']); ?></td><td><?php echo sd_admin_e($achievement['sport_name'] ?: '—'); ?></td><td><?php echo sd_admin_e($achievement['title']); ?></td><td><?php echo sd_admin_e($achievement['award_level']); ?></td><td><?php echo sd_admin_e($achievement['achieved_on']); ?></td><td><form method="post" onsubmit="return confirm('Delete this achievement?');"><input type="hidden" name="csrf" value="<?php echo sd_admin_e($sd_admin_csrf); ?>"><input type="hidden" name="action" value="delete_achievement"><input type="hidden" name="achievement_id" value="<?php echo (int) $achievement['achievement_id']; ?>"><button class="btn btn-sm btn-outline-danger">Delete</button></form></td></tr><?php endforeach; ?><?php if (!$sportsAchievements): ?><tr><td colspan="6">No sports achievements found.</td></tr><?php endif; ?></tbody></table></div></section>
<?php sd_admin_footer(); ?>
