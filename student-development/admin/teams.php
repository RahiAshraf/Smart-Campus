<?php
require_once __DIR__ . '/_bootstrap.php';
require_once __DIR__ . '/_layout.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    sd_admin_require_csrf($sd_admin_csrf);
    $action = $_POST['action'] ?? '';
    if ($action === 'save_team') {
        $sport = (int) $_POST['sport_id'];
        $name = trim($_POST['name'] ?? '');
        if ($sport > 0 && $name !== '') {
            sd_admin_stmt($conn, 'INSERT INTO sd_sports_teams (sport_id, name) VALUES (?, ?)', 'is', [$sport, $name]);
            sd_admin_audit($conn, $sd_admin_user_id, 'create', 'sports_team', (int) $conn->insert_id, $name);
            sd_admin_flash('Team created.');
        }
    } elseif ($action === 'delete_team') {
        $id = (int) $_POST['team_id'];
        sd_admin_stmt($conn, 'DELETE FROM sd_sports_teams WHERE team_id = ?', 'i', [$id]);
        sd_admin_audit($conn, $sd_admin_user_id, 'delete', 'sports_team', $id);
        sd_admin_flash('Team deleted.');
    } elseif ($action === 'save_member') {
        $team = (int) $_POST['team_id']; $student = (int) $_POST['student_id'];
        if ($team > 0 && $student > 0) {
            sd_admin_stmt($conn, 'INSERT IGNORE INTO sd_sports_team_members (team_id, student_id, joined_on) VALUES (?, ?, CURDATE())', 'ii', [$team, $student]);
            sd_admin_flash('Player added to team.');
        }
    } elseif ($action === 'delete_member') {
        sd_admin_stmt($conn, 'DELETE FROM sd_sports_team_members WHERE team_member_id = ?', 'i', [(int) $_POST['team_member_id']]);
        sd_admin_flash('Player removed from team.');
    }
    sd_admin_redirect('teams.php');
}
$sports = sd_admin_rows(sd_admin_stmt($conn, 'SELECT sport_id, name FROM sd_sports WHERE status = "active" ORDER BY name'));
$students = sd_admin_rows(sd_admin_stmt($conn, 'SELECT s.id, s.student_id, u.full_name FROM students s JOIN users u ON u.id = s.user_id ORDER BY u.full_name'));
$teams = sd_admin_rows(sd_admin_stmt($conn, 'SELECT t.team_id, t.name, sp.name sport_name, COUNT(tm.team_member_id) player_count FROM sd_sports_teams t JOIN sd_sports sp ON sp.sport_id = t.sport_id LEFT JOIN sd_sports_team_members tm ON tm.team_id = t.team_id GROUP BY t.team_id ORDER BY sp.name, t.name'));
$members = sd_admin_rows(sd_admin_stmt($conn, 'SELECT tm.team_member_id, t.name team_name, sp.name sport_name, u.full_name student_name, s.student_id FROM sd_sports_team_members tm JOIN sd_sports_teams t ON t.team_id = tm.team_id JOIN sd_sports sp ON sp.sport_id = t.sport_id JOIN students s ON s.id = tm.student_id JOIN users u ON u.id = s.user_id ORDER BY sp.name, t.name, u.full_name'));
sd_admin_header('Teams & Events', 'Manage sports teams, player rosters and event participation structures.', 'teams');
?>
<section class="sd-admin-section sd-admin-card"><h2>Create team</h2><form class="sd-admin-form" method="post"><input type="hidden" name="csrf" value="<?php echo sd_admin_e($sd_admin_csrf); ?>"><input type="hidden" name="action" value="save_team"><label>Sport<select name="sport_id" required><?php foreach($sports as $sport): ?><option value="<?php echo (int)$sport['sport_id']; ?>"><?php echo sd_admin_e($sport['name']); ?></option><?php endforeach; ?></select></label><label>Team name<input name="name" required></label><button class="btn btn-primary">Add team</button></form></section>
<section class="sd-admin-section sd-admin-card"><h2>Teams</h2><div class="sd-admin-table-wrap"><table class="sd-admin-table"><thead><tr><th>Sport</th><th>Team</th><th>Players</th><th>Action</th></tr></thead><tbody><?php foreach($teams as $team): ?><tr><td><?php echo sd_admin_e($team['sport_name']); ?></td><td><?php echo sd_admin_e($team['name']); ?></td><td><?php echo (int)$team['player_count']; ?></td><td><form method="post" onsubmit="return confirm('Delete this team and roster?');"><input type="hidden" name="csrf" value="<?php echo sd_admin_e($sd_admin_csrf); ?>"><input type="hidden" name="action" value="delete_team"><input type="hidden" name="team_id" value="<?php echo (int)$team['team_id']; ?>"><button class="btn btn-sm btn-outline-danger">Delete</button></form></td></tr><?php endforeach; ?><?php if(!$teams): ?><tr><td colspan="4">No teams found.</td></tr><?php endif; ?></tbody></table></div></section>
<section class="sd-admin-section sd-admin-card"><h2>Add player to team</h2><form class="sd-admin-form" method="post"><input type="hidden" name="csrf" value="<?php echo sd_admin_e($sd_admin_csrf); ?>"><input type="hidden" name="action" value="save_member"><label>Team<select name="team_id" required><?php foreach($teams as $team): ?><option value="<?php echo (int)$team['team_id']; ?>"><?php echo sd_admin_e($team['sport_name'].' — '.$team['name']); ?></option><?php endforeach; ?></select></label><label>Student<select name="student_id" required><?php foreach($students as $student): ?><option value="<?php echo (int)$student['id']; ?>"><?php echo sd_admin_e($student['full_name'].' ('.$student['student_id'].')'); ?></option><?php endforeach; ?></select></label><button class="btn btn-primary">Add player</button></form><div class="sd-admin-table-wrap"><table class="sd-admin-table"><thead><tr><th>Sport</th><th>Team</th><th>Student</th><th>Action</th></tr></thead><tbody><?php foreach($members as $member): ?><tr><td><?php echo sd_admin_e($member['sport_name']); ?></td><td><?php echo sd_admin_e($member['team_name']); ?></td><td><?php echo sd_admin_e($member['student_name'].' ('.$member['student_id'].')'); ?></td><td><form method="post" onsubmit="return confirm('Remove this player?');"><input type="hidden" name="csrf" value="<?php echo sd_admin_e($sd_admin_csrf); ?>"><input type="hidden" name="action" value="delete_member"><input type="hidden" name="team_member_id" value="<?php echo (int)$member['team_member_id']; ?>"><button class="btn btn-sm btn-outline-danger">Remove</button></form></td></tr><?php endforeach; ?><?php if(!$members): ?><tr><td colspan="4">No players assigned.</td></tr><?php endif; ?></tbody></table></div></section>
<?php sd_admin_footer(); ?>
