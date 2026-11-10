<?php

require_once __DIR__ . '/_bootstrap.php';
require_once __DIR__ . '/_layout.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    sd_admin_require_csrf($sd_admin_csrf);
    $action = $_POST['action'] ?? '';

    if ($action === 'save_club') {
        $id = (int) ($_POST['club_id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $category = $_POST['category'] ?? 'other';
        $description = trim($_POST['description'] ?? '');
        $status = $_POST['status'] ?? 'active';
        $allowedCategories = ['cultural', 'technical', 'literary', 'social', 'volunteering', 'other'];
        if ($name === '' || !in_array($category, $allowedCategories, true) || !in_array($status, ['active', 'inactive'], true)) {
            sd_admin_flash('Enter a valid club name, category and status.', 'error');
        } elseif ($id > 0) {
            sd_admin_stmt($conn, 'UPDATE sd_clubs SET name = ?, category = ?, description = ?, status = ? WHERE club_id = ?', 'ssssi', [$name, $category, $description, $status, $id]);
            sd_admin_flash('Club updated.');
        } else {
            sd_admin_stmt($conn, 'INSERT INTO sd_clubs (name, category, description, status) VALUES (?, ?, ?, ?)', 'ssss', [$name, $category, $description, $status]);
            sd_admin_publish($conn, $sd_admin_user_id, 'New student club available', $name . ' is now available in Student Development.', ['role:student', 'role:parent', 'role:teacher'], 'club:' . (int) $conn->insert_id);
            sd_admin_flash('Club created.');
        }
    } elseif ($action === 'delete_club') {
        sd_admin_stmt($conn, 'DELETE FROM sd_clubs WHERE club_id = ?', 'i', [(int) $_POST['club_id']]);
        sd_admin_flash('Club deleted.');
    } elseif ($action === 'review_application') {
        $status = $_POST['status'] ?? 'rejected';
        if (in_array($status, ['approved', 'rejected'], true)) {
            $applicationId = (int) $_POST['application_id'];
            $conn->begin_transaction();
            try {
                sd_admin_stmt($conn, 'UPDATE sd_club_applications SET status = ?, reviewed_by = ?, reviewed_at = NOW(), review_remarks = ? WHERE application_id = ?', 'sisi', [$status, $sd_admin_user_id, trim($_POST['remarks'] ?? ''), $applicationId]);
                if ($status === 'approved') {
                    sd_admin_stmt($conn, 'INSERT IGNORE INTO sd_club_members (club_id, student_id, joined_on) SELECT club_id, student_id, CURDATE() FROM sd_club_applications WHERE application_id = ?', 'i', [$applicationId]);
                }
                $conn->commit();
                $application = sd_admin_rows(sd_admin_stmt($conn, 'SELECT a.student_id, c.name FROM sd_club_applications a JOIN sd_clubs c ON c.club_id = a.club_id WHERE a.application_id = ?', 'i', [$applicationId]))[0] ?? null;
                if ($application) {
                    sd_admin_publish($conn, $sd_admin_user_id, 'Club application ' . $status, 'Your application for ' . $application['name'] . ' was ' . $status . '.', ['student:' . $application['student_id']], 'club-application-status:' . $applicationId . ':' . $status);
                }
                sd_admin_flash('Club application updated.');
            } catch (Throwable $exception) {
                $conn->rollback();
                sd_admin_flash('Unable to update the club application.', 'error');
            }
        }
    } elseif ($action === 'save_event') {
        $title = trim($_POST['title'] ?? '');
        $eventDate = $_POST['event_date'] ?? '';
        $eventType = $_POST['event_type'] ?? 'club';
        if ($title === '' || $eventDate === '' || !in_array($eventType, ['club', 'cultural', 'competition', 'volunteering', 'other'], true)) {
            sd_admin_flash('Enter a valid event title, date and type.', 'error');
        } else {
            sd_admin_stmt($conn, 'INSERT INTO sd_events (title, event_type, description, event_date, venue, organizer_user_id) VALUES (?, ?, ?, ?, ?, ?)', 'sssssi', [$title, $eventType, trim($_POST['description'] ?? ''), $eventDate, trim($_POST['venue'] ?? ''), $sd_admin_user_id]);
            sd_admin_publish($conn, $sd_admin_user_id, 'New student development event', $title . ' is scheduled for ' . $eventDate . '.', ['role:student', 'role:parent', 'role:teacher'], 'event:' . (int) $conn->insert_id);
            sd_admin_flash('Event created.');
        }
    } elseif ($action === 'delete_event') {
        sd_admin_stmt($conn, 'DELETE FROM sd_events WHERE event_id = ?', 'i', [(int) $_POST['event_id']]);
        sd_admin_flash('Event deleted.');
    }

    sd_admin_redirect('clubs.php');
}

$search = trim($_GET['search'] ?? '');
$editId = (int) ($_GET['edit'] ?? 0);
$viewId = (int) ($_GET['view'] ?? 0);
$clubsSql = 'SELECT c.club_id, c.name, c.category, c.description, c.status, c.registration_open, u.full_name AS coordinator, COUNT(DISTINCT cm.membership_id) AS member_count FROM sd_clubs c LEFT JOIN teachers t ON t.id = c.coordinator_teacher_id LEFT JOIN users u ON u.id = t.user_id LEFT JOIN sd_club_members cm ON cm.club_id = c.club_id AND cm.status = "active"';
$clubParams = [];
$clubTypes = '';
if ($search !== '') {
    $clubsSql .= ' WHERE c.name LIKE ? OR c.category LIKE ?';
    $term = '%' . $search . '%';
    $clubParams = [$term, $term];
    $clubTypes = 'ss';
}
$clubsSql .= ' GROUP BY c.club_id ORDER BY c.name';
$clubs = sd_admin_rows(sd_admin_stmt($conn, $clubsSql, $clubTypes, $clubParams));
$editClub = $editId ? (sd_admin_rows(sd_admin_stmt($conn, 'SELECT * FROM sd_clubs WHERE club_id = ?', 'i', [$editId]))[0] ?? null) : null;
$members = $viewId ? sd_admin_rows(sd_admin_stmt($conn, 'SELECT u.full_name, s.student_id, cm.role, cm.joined_on, cm.status FROM sd_club_members cm JOIN students s ON s.id = cm.student_id JOIN users u ON u.id = s.user_id WHERE cm.club_id = ? ORDER BY u.full_name', 'i', [$viewId])) : [];
$applications = sd_admin_rows(sd_admin_stmt($conn, 'SELECT a.application_id, a.status, a.applied_at, c.name AS club_name, u.full_name AS student_name FROM sd_club_applications a JOIN sd_clubs c ON c.club_id = a.club_id JOIN students s ON s.id = a.student_id JOIN users u ON u.id = s.user_id ORDER BY a.applied_at DESC'));
$events = sd_admin_rows(sd_admin_stmt($conn, 'SELECT e.*, COUNT(ep.participation_id) AS participant_count FROM sd_events e LEFT JOIN sd_event_participants ep ON ep.event_id = e.event_id WHERE e.event_type IN ("club","cultural","competition","volunteering","other") GROUP BY e.event_id ORDER BY e.event_date DESC'));

sd_admin_header('Student Activities & Clubs', 'Manage clubs, memberships, registrations, events and participation.', 'clubs');
?>
<section class="sd-admin-section">
    <div class="sd-admin-section-header"><h2>Clubs</h2><span class="sd-admin-muted"><?php echo count($clubs); ?> records</span></div>
    <form class="sd-admin-toolbar" method="get">
        <input name="search" value="<?php echo sd_admin_e($search); ?>" placeholder="Search clubs or categories">
        <button class="btn btn-primary"><i class="fa-solid fa-search"></i> Search</button>
        <a class="btn btn-outline-primary" href="clubs.php">Reset</a>
    </form>
    <form class="sd-admin-form" method="post">
        <input type="hidden" name="csrf" value="<?php echo sd_admin_e($sd_admin_csrf); ?>">
        <input type="hidden" name="action" value="save_club">
        <input type="hidden" name="club_id" value="<?php echo (int) ($editClub['club_id'] ?? 0); ?>">
        <label>Club name<input name="name" required value="<?php echo sd_admin_e($editClub['name'] ?? ''); ?>"></label>
        <label>Category<select name="category"><?php foreach (['cultural','technical','literary','social','volunteering','other'] as $category): ?><option value="<?php echo $category; ?>" <?php echo ($editClub['category'] ?? '') === $category ? 'selected' : ''; ?>><?php echo ucfirst($category); ?></option><?php endforeach; ?></select></label>
        <label>Status<select name="status"><option value="active" <?php echo ($editClub['status'] ?? 'active') === 'active' ? 'selected' : ''; ?>>Active</option><option value="inactive" <?php echo ($editClub['status'] ?? '') === 'inactive' ? 'selected' : ''; ?>>Inactive</option></select></label>
        <label>Description<input name="description" value="<?php echo sd_admin_e($editClub['description'] ?? ''); ?>"></label>
        <button class="btn btn-primary"><?php echo $editClub ? 'Update club' : 'Add club'; ?></button>
        <?php if ($editClub): ?><a class="btn btn-outline-primary" href="clubs.php">Cancel</a><?php endif; ?>
    </form>
    <div class="sd-admin-table-wrap"><table class="sd-admin-table"><thead><tr><th>Club</th><th>Category</th><th>Coordinator</th><th>Members</th><th>Status</th><th>Actions</th></tr></thead><tbody>
    <?php foreach ($clubs as $club): ?><tr><td><strong><?php echo sd_admin_e($club['name']); ?></strong><br><span class="sd-admin-muted"><?php echo sd_admin_e($club['description']); ?></span></td><td><?php echo sd_admin_e(ucfirst($club['category'])); ?></td><td><?php echo sd_admin_e($club['coordinator'] ?: 'Not assigned'); ?></td><td><?php echo number_format((int) $club['member_count']); ?></td><td><span class="sd-admin-badge"><?php echo sd_admin_e(ucfirst($club['status'])); ?></span></td><td><div class="sd-admin-actions"><a class="btn btn-sm btn-outline-primary" href="?view=<?php echo (int) $club['club_id']; ?>">View members</a><a class="btn btn-sm btn-outline-primary" href="?edit=<?php echo (int) $club['club_id']; ?>">Edit</a><form method="post" onsubmit="return confirm('Delete this club and its related registrations?');"><input type="hidden" name="csrf" value="<?php echo sd_admin_e($sd_admin_csrf); ?>"><input type="hidden" name="action" value="delete_club"><input type="hidden" name="club_id" value="<?php echo (int) $club['club_id']; ?>"><button class="btn btn-sm btn-outline-danger">Delete</button></form></div></td></tr><?php endforeach; ?>
    <?php if (!$clubs): ?><tr><td colspan="6">No clubs found.</td></tr><?php endif; ?></tbody></table></div>
</section>

<?php if ($viewId): ?><section class="sd-admin-section sd-admin-card"><div class="sd-admin-section-header"><h2>Members</h2><a href="clubs.php">Close</a></div><div class="sd-admin-table-wrap"><table class="sd-admin-table"><thead><tr><th>Student</th><th>Student ID</th><th>Role</th><th>Joined</th><th>Status</th></tr></thead><tbody><?php foreach ($members as $member): ?><tr><td><?php echo sd_admin_e($member['full_name']); ?></td><td><?php echo sd_admin_e($member['student_id']); ?></td><td><?php echo sd_admin_e(ucfirst($member['role'])); ?></td><td><?php echo sd_admin_e($member['joined_on']); ?></td><td><?php echo sd_admin_e(ucfirst($member['status'])); ?></td></tr><?php endforeach; ?><?php if (!$members): ?><tr><td colspan="5">No members found.</td></tr><?php endif; ?></tbody></table></div></section><?php endif; ?>

<section class="sd-admin-section sd-admin-card"><div class="sd-admin-section-header"><h2>Registrations</h2><span class="sd-admin-muted">Review student applications</span></div><div class="sd-admin-table-wrap"><table class="sd-admin-table"><thead><tr><th>Student</th><th>Club</th><th>Applied</th><th>Status</th><th>Action</th></tr></thead><tbody><?php foreach ($applications as $application): ?><tr><td><?php echo sd_admin_e($application['student_name']); ?></td><td><?php echo sd_admin_e($application['club_name']); ?></td><td><?php echo sd_admin_e($application['applied_at']); ?></td><td><?php echo sd_admin_e(ucfirst($application['status'])); ?></td><td><?php if ($application['status'] === 'pending'): ?><form class="sd-admin-actions" method="post"><input type="hidden" name="csrf" value="<?php echo sd_admin_e($sd_admin_csrf); ?>"><input type="hidden" name="action" value="review_application"><input type="hidden" name="application_id" value="<?php echo (int) $application['application_id']; ?>"><button class="btn btn-sm btn-primary" name="status" value="approved">Approve</button><button class="btn btn-sm btn-outline-danger" name="status" value="rejected">Reject</button></form><?php else: ?>—<?php endif; ?></td></tr><?php endforeach; ?><?php if (!$applications): ?><tr><td colspan="5">No registrations found.</td></tr><?php endif; ?></tbody></table></div></section>

<section class="sd-admin-section sd-admin-card"><div class="sd-admin-section-header"><h2>Club events</h2><span class="sd-admin-muted">Create and monitor activities</span></div><form class="sd-admin-form" method="post"><input type="hidden" name="csrf" value="<?php echo sd_admin_e($sd_admin_csrf); ?>"><input type="hidden" name="action" value="save_event"><label>Title<input name="title" required></label><label>Type<select name="event_type"><option value="club">Club</option><option value="cultural">Cultural</option><option value="competition">Competition</option><option value="volunteering">Volunteering</option><option value="other">Other</option></select></label><label>Date<input type="date" name="event_date" required></label><label>Venue<input name="venue"></label><button class="btn btn-primary">Add event</button></form><div class="sd-admin-table-wrap"><table class="sd-admin-table"><thead><tr><th>Event</th><th>Type</th><th>Date</th><th>Venue</th><th>Participants</th><th>Action</th></tr></thead><tbody><?php foreach ($events as $event): ?><tr><td><?php echo sd_admin_e($event['title']); ?></td><td><?php echo sd_admin_e(ucfirst($event['event_type'])); ?></td><td><?php echo sd_admin_e($event['event_date']); ?></td><td><?php echo sd_admin_e($event['venue']); ?></td><td><?php echo (int) $event['participant_count']; ?></td><td><form method="post" onsubmit="return confirm('Delete this event?');"><input type="hidden" name="csrf" value="<?php echo sd_admin_e($sd_admin_csrf); ?>"><input type="hidden" name="action" value="delete_event"><input type="hidden" name="event_id" value="<?php echo (int) $event['event_id']; ?>"><button class="btn btn-sm btn-outline-danger">Delete</button></form></td></tr><?php endforeach; ?><?php if (!$events): ?><tr><td colspan="6">No events found.</td></tr><?php endif; ?></tbody></table></div></section>
<?php sd_admin_footer(); ?>
