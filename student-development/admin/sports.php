<?php

require_once __DIR__ . '/_bootstrap.php';
require_once __DIR__ . '/_layout.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    sd_admin_require_csrf($sd_admin_csrf);
    $action = $_POST['action'] ?? '';
    if ($action === 'save_sport') {
        $id = (int) ($_POST['sport_id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $mode = $_POST['mode'] ?? 'indoor';
        $status = $_POST['status'] ?? 'active';
        if ($name === '' || !in_array($mode, ['indoor', 'outdoor'], true) || !in_array($status, ['active', 'inactive'], true)) {
            sd_admin_flash('Enter valid sport details.', 'error');
        } elseif ($id > 0) {
            sd_admin_stmt($conn, 'UPDATE sd_sports SET name = ?, mode = ?, practice_schedule = ?, status = ? WHERE sport_id = ?', 'ssssi', [$name, $mode, trim($_POST['practice_schedule'] ?? ''), $status, $id]);
            sd_admin_flash('Sport updated.');
        } else {
            sd_admin_stmt($conn, 'INSERT INTO sd_sports (name, mode, practice_schedule, status) VALUES (?, ?, ?, ?)', 'ssss', [$name, $mode, trim($_POST['practice_schedule'] ?? ''), $status]);
            sd_admin_flash('Sport created.');
        }
    } elseif ($action === 'delete_sport') {
        sd_admin_stmt($conn, 'DELETE FROM sd_sports WHERE sport_id = ?', 'i', [(int) $_POST['sport_id']]);
        sd_admin_flash('Sport deleted.');
    } elseif ($action === 'review_application') {
        $status = $_POST['status'] ?? '';
        if (in_array($status, ['trial', 'selected', 'rejected'], true)) {
            sd_admin_stmt($conn, 'UPDATE sd_sport_applications SET status = ?, trial_date = ?, trial_result = ?, reviewed_by = ?, reviewed_at = NOW() WHERE application_id = ?', 'sssii', [$status, $_POST['trial_date'] ?: null, trim($_POST['trial_result'] ?? ''), $sd_admin_user_id, (int) $_POST['application_id']]);
            sd_admin_flash('Sports registration updated.');
        }
    } elseif ($action === 'save_match') {
        $sportId = (int) $_POST['sport_id'];
        $date = str_replace('T', ' ', $_POST['match_date'] ?? '');
        if ($sportId > 0 && $date !== '') {
            sd_admin_stmt($conn, 'INSERT INTO sd_sports_matches (sport_id, match_date, venue, status) VALUES (?, ?, ?, "scheduled")', 'iss', [$sportId, $date, trim($_POST['venue'] ?? '')]);
            sd_admin_flash('Sports event created.');
        }
    } elseif ($action === 'delete_match') {
        sd_admin_stmt($conn, 'DELETE FROM sd_sports_matches WHERE match_id = ?', 'i', [(int) $_POST['match_id']]);
        sd_admin_flash('Sports event deleted.');
    }
    sd_admin_redirect('sports.php');
}

$search = trim($_GET['search'] ?? '');
$editId = (int) ($_GET['edit'] ?? 0);
$sportsSql = 'SELECT sp.*, u.full_name AS coach, COUNT(DISTINCT CASE WHEN sa.status = "selected" THEN sa.student_id END) AS player_count, COUNT(DISTINCT t.team_id) AS team_count FROM sd_sports sp LEFT JOIN teachers th ON th.id = sp.coach_teacher_id LEFT JOIN users u ON u.id = th.user_id LEFT JOIN sd_sport_applications sa ON sa.sport_id = sp.sport_id LEFT JOIN sd_sports_teams t ON t.sport_id = sp.sport_id';
$params = [];
$types = '';
if ($search !== '') {
    $sportsSql .= ' WHERE sp.name LIKE ? OR sp.mode LIKE ?';
    $term = '%' . $search . '%';
    $params = [$term, $term];
    $types = 'ss';
}
$sportsSql .= ' GROUP BY sp.sport_id ORDER BY sp.name';
$sports = sd_admin_rows(sd_admin_stmt($conn, $sportsSql, $types, $params));
$editSport = $editId ? (sd_admin_rows(sd_admin_stmt($conn, 'SELECT * FROM sd_sports WHERE sport_id = ?', 'i', [$editId]))[0] ?? null) : null;
$applications = sd_admin_rows(sd_admin_stmt($conn, 'SELECT a.*, sp.name AS sport_name, u.full_name AS student_name FROM sd_sport_applications a JOIN sd_sports sp ON sp.sport_id = a.sport_id JOIN students s ON s.id = a.student_id JOIN users u ON u.id = s.user_id ORDER BY a.applied_at DESC'));
$matches = sd_admin_rows(sd_admin_stmt($conn, 'SELECT m.*, sp.name AS sport_name FROM sd_sports_matches m JOIN sd_sports sp ON sp.sport_id = m.sport_id ORDER BY m.match_date DESC'));

sd_admin_header('Sports Management', 'Manage sports, teams, coaches, trials, players and sports events.', 'sports');
?>
<section class="sd-admin-section sd-admin-card">
    <div class="sd-admin-section-header"><h2>Sports catalogue</h2><span class="sd-admin-muted"><?php echo count($sports); ?> records</span></div>
    <form class="sd-admin-toolbar" method="get"><input name="search" value="<?php echo sd_admin_e($search); ?>" placeholder="Search sports"><button class="btn btn-primary">Search</button><a class="btn btn-outline-primary" href="sports.php">Reset</a></form>
    <form class="sd-admin-form" method="post"><input type="hidden" name="csrf" value="<?php echo sd_admin_e($sd_admin_csrf); ?>"><input type="hidden" name="action" value="save_sport"><input type="hidden" name="sport_id" value="<?php echo (int) ($editSport['sport_id'] ?? 0); ?>"><label>Sport name<input name="name" required value="<?php echo sd_admin_e($editSport['name'] ?? ''); ?>"></label><label>Mode<select name="mode"><option value="indoor" <?php echo ($editSport['mode'] ?? '') === 'indoor' ? 'selected' : ''; ?>>Indoor</option><option value="outdoor" <?php echo ($editSport['mode'] ?? '') === 'outdoor' ? 'selected' : ''; ?>>Outdoor</option></select></label><label>Practice schedule<input name="practice_schedule" value="<?php echo sd_admin_e($editSport['practice_schedule'] ?? ''); ?>"></label><label>Status<select name="status"><option value="active">Active</option><option value="inactive" <?php echo ($editSport['status'] ?? '') === 'inactive' ? 'selected' : ''; ?>>Inactive</option></select></label><button class="btn btn-primary"><?php echo $editSport ? 'Update sport' : 'Add sport'; ?></button><?php if ($editSport): ?><a class="btn btn-outline-primary" href="sports.php">Cancel</a><?php endif; ?></form>
    <div class="sd-admin-table-wrap"><table class="sd-admin-table"><thead><tr><th>Sport</th><th>Mode</th><th>Coach</th><th>Teams</th><th>Players</th><th>Status</th><th>Actions</th></tr></thead><tbody><?php foreach ($sports as $sport): ?><tr><td><?php echo sd_admin_e($sport['name']); ?></td><td><?php echo sd_admin_e(ucfirst($sport['mode'])); ?></td><td><?php echo sd_admin_e($sport['coach'] ?: 'Not assigned'); ?></td><td><?php echo (int) $sport['team_count']; ?></td><td><?php echo (int) $sport['player_count']; ?></td><td><?php echo sd_admin_e(ucfirst($sport['status'])); ?></td><td><div class="sd-admin-actions"><a class="btn btn-sm btn-outline-primary" href="?edit=<?php echo (int) $sport['sport_id']; ?>">Edit</a><form method="post" onsubmit="return confirm('Delete this sport and related registrations?');"><input type="hidden" name="csrf" value="<?php echo sd_admin_e($sd_admin_csrf); ?>"><input type="hidden" name="action" value="delete_sport"><input type="hidden" name="sport_id" value="<?php echo (int) $sport['sport_id']; ?>"><button class="btn btn-sm btn-outline-danger">Delete</button></form></div></td></tr><?php endforeach; ?><?php if (!$sports): ?><tr><td colspan="7">No sports found.</td></tr><?php endif; ?></tbody></table></div>
</section>

<section class="sd-admin-section sd-admin-card"><div class="sd-admin-section-header"><h2>Player registrations and trials</h2></div><div class="sd-admin-table-wrap"><table class="sd-admin-table"><thead><tr><th>Student</th><th>Sport</th><th>Applied</th><th>Status</th><th>Trial date</th><th>Action</th></tr></thead><tbody><?php foreach ($applications as $application): ?><tr><td><?php echo sd_admin_e($application['student_name']); ?></td><td><?php echo sd_admin_e($application['sport_name']); ?></td><td><?php echo sd_admin_e($application['applied_at']); ?></td><td><?php echo sd_admin_e(ucfirst($application['status'])); ?></td><td><?php echo sd_admin_e($application['trial_date']); ?></td><td><?php if (in_array($application['status'], ['pending','trial'], true)): ?><form class="sd-admin-actions" method="post"><input type="hidden" name="csrf" value="<?php echo sd_admin_e($sd_admin_csrf); ?>"><input type="hidden" name="action" value="review_application"><input type="hidden" name="application_id" value="<?php echo (int) $application['application_id']; ?>"><input type="date" name="trial_date" value="<?php echo sd_admin_e($application['trial_date']); ?>"><button class="btn btn-sm btn-outline-primary" name="status" value="trial">Trial</button><button class="btn btn-sm btn-primary" name="status" value="selected">Select</button><button class="btn btn-sm btn-outline-danger" name="status" value="rejected">Reject</button></form><?php else: ?>—<?php endif; ?></td></tr><?php endforeach; ?><?php if (!$applications): ?><tr><td colspan="6">No sports registrations found.</td></tr><?php endif; ?></tbody></table></div></section>

<section class="sd-admin-section sd-admin-card"><div class="sd-admin-section-header"><h2>Sports events</h2></div><form class="sd-admin-form" method="post"><input type="hidden" name="csrf" value="<?php echo sd_admin_e($sd_admin_csrf); ?>"><input type="hidden" name="action" value="save_match"><label>Sport<select name="sport_id" required><?php foreach ($sports as $sport): ?><option value="<?php echo (int) $sport['sport_id']; ?>"><?php echo sd_admin_e($sport['name']); ?></option><?php endforeach; ?></select></label><label>Date and time<input type="datetime-local" name="match_date" required></label><label>Venue<input name="venue"></label><button class="btn btn-primary">Add event</button></form><div class="sd-admin-table-wrap"><table class="sd-admin-table"><thead><tr><th>Sport</th><th>Date</th><th>Venue</th><th>Status</th><th>Action</th></tr></thead><tbody><?php foreach ($matches as $match): ?><tr><td><?php echo sd_admin_e($match['sport_name']); ?></td><td><?php echo sd_admin_e($match['match_date']); ?></td><td><?php echo sd_admin_e($match['venue']); ?></td><td><?php echo sd_admin_e(ucfirst($match['status'])); ?></td><td><form method="post" onsubmit="return confirm('Delete this event?');"><input type="hidden" name="csrf" value="<?php echo sd_admin_e($sd_admin_csrf); ?>"><input type="hidden" name="action" value="delete_match"><input type="hidden" name="match_id" value="<?php echo (int) $match['match_id']; ?>"><button class="btn btn-sm btn-outline-danger">Delete</button></form></td></tr><?php endforeach; ?><?php if (!$matches): ?><tr><td colspan="5">No sports events found.</td></tr><?php endif; ?></tbody></table></div></section>
<?php sd_admin_footer(); ?>
