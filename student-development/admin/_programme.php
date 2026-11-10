<?php

require_once __DIR__ . '/_bootstrap.php';
require_once __DIR__ . '/_layout.php';

$programme = $sd_programme;
$isNcc = $programme === 'ncc';
$title = $isNcc ? 'NCC Management' : 'NSS Management';
$description = $isNcc
    ? 'Manage NCC cadet registrations, activities and attendance.'
    : 'Manage NSS volunteer registrations, service activities and attendance.';
$memberTable = $isNcc ? 'sd_ncc_cadets' : 'sd_nss_volunteers';
$memberId = $isNcc ? 'cadet_id' : 'volunteer_id';
$memberLabel = $isNcc ? 'Cadet' : 'Volunteer';
$activityTable = $isNcc ? 'sd_ncc_activities' : 'sd_nss_activities';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    sd_admin_require_csrf($sd_admin_csrf);
    $action = $_POST['action'] ?? '';
    if ($action === 'review_application') {
        $status = $_POST['status'] ?? '';
        if (in_array($status, ['approved', 'rejected'], true)) {
            $applicationId = (int) $_POST['application_id'];
            $conn->begin_transaction();
            try {
                sd_admin_stmt($conn, 'UPDATE sd_programme_applications SET status = ?, reviewed_by = ?, reviewed_at = NOW(), review_remarks = ? WHERE application_id = ? AND programme_key = ?', 'sisis', [$status, $sd_admin_user_id, trim($_POST['remarks'] ?? ''), $applicationId, $programme]);
                if ($status === 'approved') {
                    if ($isNcc) {
                        sd_admin_stmt($conn, 'INSERT IGNORE INTO sd_ncc_cadets (student_id, enrollment_no, enrollment_date) SELECT student_id, CONCAT("NCC-", application_id), CURDATE() FROM sd_programme_applications WHERE application_id = ? AND programme_key = ?', 'is', [$applicationId, $programme]);
                    } else {
                        sd_admin_stmt($conn, 'INSERT IGNORE INTO sd_nss_volunteers (student_id, enrollment_no, enrollment_date) SELECT student_id, CONCAT("NSS-", application_id), CURDATE() FROM sd_programme_applications WHERE application_id = ? AND programme_key = ?', 'is', [$applicationId, $programme]);
                    }
                }
                $conn->commit();
                $application = sd_admin_rows(sd_admin_stmt($conn, 'SELECT student_id FROM sd_programme_applications WHERE application_id = ?', 'i', [$applicationId]))[0] ?? null;
                if ($application) {
                    sd_admin_publish($conn, $sd_admin_user_id, strtoupper($programme) . ' application ' . $status, 'Your ' . strtoupper($programme) . ' application was ' . $status . '.', ['student:' . $application['student_id']], 'programme-application-status:' . $applicationId . ':' . $status);
                }
                sd_admin_flash($memberLabel . ' application updated.');
            } catch (Throwable $exception) {
                $conn->rollback();
                sd_admin_flash('Unable to update the application.', 'error');
            }
        }
    } elseif ($action === 'save_activity') {
        $titleValue = trim($_POST['title'] ?? '');
        $date = $_POST['activity_date'] ?? '';
        if ($titleValue === '' || $date === '') {
            sd_admin_flash('Enter an activity title and date.', 'error');
        } elseif ($isNcc) {
            sd_admin_stmt($conn, 'INSERT INTO sd_ncc_activities (title, activity_type, activity_date, venue, hours) VALUES (?, ?, ?, ?, ?)', 'ssssd', [$titleValue, $_POST['activity_type'] ?? 'other', $date, trim($_POST['venue'] ?? ''), (float) ($_POST['hours'] ?? 0)]);
            sd_admin_publish($conn, $sd_admin_user_id, 'New NCC activity', $titleValue . ' is scheduled for ' . $date . '.', ['role:student', 'role:parent', 'role:teacher'], 'ncc-activity:' . (int) $conn->insert_id);
            sd_admin_flash('NCC activity created.');
        } else {
            sd_admin_stmt($conn, 'INSERT INTO sd_nss_activities (title, activity_type, activity_date, venue, service_hours) VALUES (?, ?, ?, ?, ?)', 'ssssd', [$titleValue, $_POST['activity_type'] ?? 'other', $date, trim($_POST['venue'] ?? ''), (float) ($_POST['hours'] ?? 0)]);
            sd_admin_publish($conn, $sd_admin_user_id, 'New NSS activity', $titleValue . ' is scheduled for ' . $date . '.', ['role:student', 'role:parent', 'role:teacher'], 'nss-activity:' . (int) $conn->insert_id);
            sd_admin_flash('NSS activity created.');
        }
    } elseif ($action === 'delete_activity') {
        sd_admin_stmt($conn, 'DELETE FROM ' . $activityTable . ' WHERE activity_id = ?', 'i', [(int) $_POST['activity_id']]);
        sd_admin_flash('Activity deleted.');
    } elseif ($action === 'save_attendance') {
        $member = (int) $_POST['member_id'];
        $activity = (int) $_POST['activity_id'];
        $status = $_POST['attendance_status'] ?? 'present';
        if ($member > 0 && $activity > 0 && in_array($status, ['present', 'absent', 'excused'], true)) {
            if ($isNcc) {
                sd_admin_stmt($conn, 'INSERT INTO sd_ncc_attendance (activity_id, cadet_id, status) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE status = VALUES(status)', 'iis', [$activity, $member, $status]);
            } else {
                sd_admin_stmt($conn, 'INSERT INTO sd_nss_attendance (activity_id, volunteer_id, status) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE status = VALUES(status)', 'iis', [$activity, $member, $status]);
            }
            sd_admin_flash('Attendance saved.');
        }
    }
    sd_admin_redirect($isNcc ? 'ncc.php' : 'nss.php');
}

$search = trim($_GET['search'] ?? '');
$members = sd_admin_rows(sd_admin_stmt($conn, 'SELECT m.' . $memberId . ' AS member_id, m.enrollment_no, m.enrollment_date, m.status, u.full_name, s.student_id FROM ' . $memberTable . ' m JOIN students s ON s.id = m.student_id JOIN users u ON u.id = s.user_id WHERE u.full_name LIKE ? OR s.student_id LIKE ? ORDER BY u.full_name', 'ss', ['%' . $search . '%', '%' . $search . '%']));
$applications = sd_admin_rows(sd_admin_stmt($conn, 'SELECT a.application_id, a.status, a.applied_at, u.full_name AS student_name, s.student_id FROM sd_programme_applications a JOIN students s ON s.id = a.student_id JOIN users u ON u.id = s.user_id WHERE a.programme_key = ? ORDER BY a.applied_at DESC', 's', [$programme]));
$unregistered = sd_admin_rows(sd_admin_stmt($conn, 'SELECT s.id, s.student_id, u.full_name FROM students s JOIN users u ON u.id = s.user_id LEFT JOIN ' . $memberTable . ' m ON m.student_id = s.id WHERE m.' . $memberId . ' IS NULL ORDER BY u.full_name'));
$activities = sd_admin_rows(sd_admin_stmt($conn, 'SELECT * FROM ' . $activityTable . ' ORDER BY activity_date DESC'));

sd_admin_header($title, $description, $programme);
?>
<section class="sd-admin-section sd-admin-card">
    <div class="sd-admin-section-header"><h2>Registered <?php echo sd_admin_e(strtolower($memberLabel) . 's'); ?></h2><span class="sd-admin-muted"><?php echo count($members); ?> registered</span></div>
    <form class="sd-admin-toolbar" method="get"><input name="search" value="<?php echo sd_admin_e($search); ?>" placeholder="Search student name or ID"><button class="btn btn-primary">Search</button><a class="btn btn-outline-primary" href="<?php echo $isNcc ? 'ncc.php' : 'nss.php'; ?>">Reset</a></form>
    <div class="sd-admin-table-wrap"><table class="sd-admin-table"><thead><tr><th>Student</th><th>Student ID</th><th>Enrollment no.</th><th>Enrollment date</th><th>Status</th></tr></thead><tbody><?php foreach ($members as $member): ?><tr><td><?php echo sd_admin_e($member['full_name']); ?></td><td><?php echo sd_admin_e($member['student_id']); ?></td><td><?php echo sd_admin_e($member['enrollment_no']); ?></td><td><?php echo sd_admin_e($member['enrollment_date']); ?></td><td><?php echo sd_admin_e(ucfirst($member['status'])); ?></td></tr><?php endforeach; ?><?php if (!$members): ?><tr><td colspan="5">No registered <?php echo sd_admin_e(strtolower($memberLabel)); ?>s found.</td></tr><?php endif; ?></tbody></table></div>
</section>

<section class="sd-admin-section sd-admin-card"><div class="sd-admin-section-header"><h2>Students not registered</h2><span class="sd-admin-muted"><?php echo count($unregistered); ?> students</span></div><div class="sd-admin-table-wrap"><table class="sd-admin-table"><thead><tr><th>Student</th><th>Student ID</th></tr></thead><tbody><?php foreach ($unregistered as $student): ?><tr><td><?php echo sd_admin_e($student['full_name']); ?></td><td><?php echo sd_admin_e($student['student_id']); ?></td></tr><?php endforeach; ?><?php if (!$unregistered): ?><tr><td colspan="2">All students are registered.</td></tr><?php endif; ?></tbody></table></div></section>

<section class="sd-admin-section sd-admin-card"><div class="sd-admin-section-header"><h2>Registration applications</h2></div><div class="sd-admin-table-wrap"><table class="sd-admin-table"><thead><tr><th>Student</th><th>Student ID</th><th>Applied</th><th>Status</th><th>Action</th></tr></thead><tbody><?php foreach ($applications as $application): ?><tr><td><?php echo sd_admin_e($application['student_name']); ?></td><td><?php echo sd_admin_e($application['student_id']); ?></td><td><?php echo sd_admin_e($application['applied_at']); ?></td><td><?php echo sd_admin_e(ucfirst($application['status'])); ?></td><td><?php if ($application['status'] === 'pending'): ?><form class="sd-admin-actions" method="post"><input type="hidden" name="csrf" value="<?php echo sd_admin_e($sd_admin_csrf); ?>"><input type="hidden" name="action" value="review_application"><input type="hidden" name="application_id" value="<?php echo (int) $application['application_id']; ?>"><button class="btn btn-sm btn-primary" name="status" value="approved">Approve</button><button class="btn btn-sm btn-outline-danger" name="status" value="rejected">Reject</button></form><?php else: ?>—<?php endif; ?></td></tr><?php endforeach; ?><?php if (!$applications): ?><tr><td colspan="5">No applications found.</td></tr><?php endif; ?></tbody></table></div></section>

<section class="sd-admin-section sd-admin-card"><div class="sd-admin-section-header"><h2>Activities and attendance</h2></div><form class="sd-admin-form" method="post"><input type="hidden" name="csrf" value="<?php echo sd_admin_e($sd_admin_csrf); ?>"><input type="hidden" name="action" value="save_activity"><label>Title<input name="title" required></label><label>Type<select name="activity_type"><?php if ($isNcc): ?><option value="training">Training</option><option value="parade">Parade</option><option value="camp">Camp</option><option value="certificate">Certificate</option><option value="other">Other</option><?php else: ?><option value="community_service">Community service</option><option value="plantation">Plantation</option><option value="cleanliness">Cleanliness</option><option value="awareness">Awareness</option><option value="other">Other</option><?php endif; ?></select></label><label>Date<input type="date" name="activity_date" required></label><label>Venue<input name="venue"></label><label>Hours<input type="number" step="0.5" min="0" name="hours" value="0"></label><button class="btn btn-primary">Add activity</button></form><div class="sd-admin-table-wrap"><table class="sd-admin-table"><thead><tr><th>Activity</th><th>Type</th><th>Date</th><th>Venue</th><th>Hours</th><th>Action</th></tr></thead><tbody><?php foreach ($activities as $activity): ?><tr><td><?php echo sd_admin_e($activity['title']); ?></td><td><?php echo sd_admin_e(ucwords(str_replace('_', ' ', $activity['activity_type']))); ?></td><td><?php echo sd_admin_e($activity['activity_date']); ?></td><td><?php echo sd_admin_e($activity['venue']); ?></td><td><?php echo sd_admin_e($isNcc ? $activity['hours'] : $activity['service_hours']); ?></td><td><form method="post" onsubmit="return confirm('Delete this activity and attendance?');"><input type="hidden" name="csrf" value="<?php echo sd_admin_e($sd_admin_csrf); ?>"><input type="hidden" name="action" value="delete_activity"><input type="hidden" name="activity_id" value="<?php echo (int) $activity['activity_id']; ?>"><button class="btn btn-sm btn-outline-danger">Delete</button></form></td></tr><?php endforeach; ?><?php if (!$activities): ?><tr><td colspan="6">No activities found.</td></tr><?php endif; ?></tbody></table></div></section>

<section class="sd-admin-section sd-admin-card"><div class="sd-admin-section-header"><h2>Record attendance</h2></div><form class="sd-admin-form" method="post"><input type="hidden" name="csrf" value="<?php echo sd_admin_e($sd_admin_csrf); ?>"><input type="hidden" name="action" value="save_attendance"><label><?php echo sd_admin_e($memberLabel); ?><select name="member_id" required><option value="">Select</option><?php foreach ($members as $member): ?><option value="<?php echo (int) $member['member_id']; ?>"><?php echo sd_admin_e($member['full_name'] . ' (' . $member['enrollment_no'] . ')'); ?></option><?php endforeach; ?></select></label><label>Activity<select name="activity_id" required><option value="">Select</option><?php foreach ($activities as $activity): ?><option value="<?php echo (int) $activity['activity_id']; ?>"><?php echo sd_admin_e($activity['title'] . ' - ' . $activity['activity_date']); ?></option><?php endforeach; ?></select></label><label>Status<select name="attendance_status"><option value="present">Present</option><option value="absent">Absent</option><option value="excused">Excused</option></select></label><button class="btn btn-primary">Save attendance</button></form></section>
<?php sd_admin_footer(); ?>
