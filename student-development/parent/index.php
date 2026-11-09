<?php
require_once __DIR__ . '/../user/_bootstrap.php';
require_once __DIR__ . '/../user/_layout.php';
if ($sd_user_role !== 'parent') { http_response_code(403); exit('Parent access only.'); }
$children = sd_user_parent_children($conn, $sd_user_id);
$selected = (int) ($_GET['child_id'] ?? ($children[0]['id'] ?? 0));
$validIds = array_map(static fn(array $child): int => (int) $child['id'], $children);
if (!in_array($selected, $validIds, true)) { $selected = $validIds[0] ?? 0; }
$child = $children[array_search($selected, $validIds, true)] ?? null;
$clubCount = $sportCount = $certCount = 0;
if ($selected > 0) {
    $clubCount = (int) (sd_user_rows(sd_user_stmt($conn, 'SELECT COUNT(*) total FROM sd_club_members WHERE student_id = ? AND status = "active"', 'i', [$selected]))[0]['total'] ?? 0);
    $sportCount = (int) (sd_user_rows(sd_user_stmt($conn, 'SELECT COUNT(*) total FROM sd_sport_applications WHERE student_id = ? AND status = "selected"', 'i', [$selected]))[0]['total'] ?? 0);
    $certCount = (int) (sd_user_rows(sd_user_stmt($conn, 'SELECT COUNT(*) total FROM sd_certificates WHERE student_id = ?', 'i', [$selected]))[0]['total'] ?? 0);
}
sd_user_header("Child's Development", 'View linked student activities, achievements, sports and programme status.', 'overview');
?>
<form class="sd-user-toolbar" method="get"><label>Child<select name="child_id" onchange="this.form.submit()"><?php foreach($children as $item): ?><option value="<?php echo (int) $item['id']; ?>" <?php echo $selected === (int) $item['id'] ? 'selected' : ''; ?>><?php echo sd_user_e($item['full_name']); ?></option><?php endforeach; ?></select></label></form>
<?php if ($child): ?><section class="sd-user-card"><h2><?php echo sd_user_e($child['full_name']); ?></h2><p class="sd-user-muted">Student ID: <?php echo sd_user_e($child['student_id']); ?> · Parent view is read-only.</p></section><section class="sd-user-grid"><?php foreach([[$clubCount,'Club memberships','fa-people-group'],[$sportCount,'Selected sports','fa-futbol'],[$certCount,'Certificates','fa-award']] as $card): ?><div class="sd-user-card"><div class="sd-user-muted"><i class="fa-solid <?php echo $card[2]; ?>"></i> <?php echo $card[1]; ?></div><div class="sd-user-value"><?php echo $card[0]; ?></div></div><?php endforeach; ?></section><?php else: ?><section class="sd-user-card"><h2>No linked student found</h2><p class="sd-user-muted">Ask an administrator to link your parent account to a student.</p></section><?php endif; ?>
<?php sd_user_footer(); ?>
