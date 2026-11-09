<?php
require_once __DIR__ . '/_bootstrap.php';
require_once __DIR__ . '/_layout.php';
$search = trim($_GET['search'] ?? '');
$sql = 'SELECT a.*, u.full_name FROM sd_audit_log a JOIN users u ON u.id = a.actor_user_id';
$params = []; $types = '';
if ($search !== '') { $sql .= ' WHERE a.action_key LIKE ? OR a.entity_type LIKE ? OR u.full_name LIKE ?'; $term = '%'.$search.'%'; $params = [$term,$term,$term]; $types = 'sss'; }
$sql .= ' ORDER BY a.created_at DESC LIMIT 200';
$rows = sd_admin_rows(sd_admin_stmt($conn, $sql, $types, $params));
sd_admin_header('Audit History', 'Review administrative changes made to Student Development records.', 'audit');
?>
<section class="sd-admin-card"><form class="sd-admin-toolbar" method="get"><input name="search" value="<?php echo sd_admin_e($search); ?>" placeholder="Search action, entity or administrator"><button class="btn btn-primary">Search</button></form><div class="sd-admin-table-wrap"><table class="sd-admin-table"><thead><tr><th>Time</th><th>Administrator</th><th>Action</th><th>Entity</th><th>Details</th></tr></thead><tbody><?php foreach($rows as $row): ?><tr><td><?php echo sd_admin_e($row['created_at']); ?></td><td><?php echo sd_admin_e($row['full_name']); ?></td><td><?php echo sd_admin_e(ucfirst($row['action_key'])); ?></td><td><?php echo sd_admin_e($row['entity_type'].' #'.$row['entity_id']); ?></td><td><?php echo sd_admin_e($row['details']); ?></td></tr><?php endforeach; ?><?php if(!$rows): ?><tr><td colspan="5">No audit records found.</td></tr><?php endif; ?></tbody></table></div></section>
<?php sd_admin_footer(); ?>
