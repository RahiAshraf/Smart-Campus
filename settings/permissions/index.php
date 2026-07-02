<?php

include('../../config/auth.php');
include('../../config/db.php');

requirePermission('permissions', 'view');
permission_ensure_finance_permissions($conn);

$page_title = 'Permission Management';
$success = '';
$error = '';

$grouped_permissions = permission_fetch_all_grouped($conn);
$role_matrix = permission_fetch_role_matrix($conn);

$available_roles = [
    'teacher',
    'student',
    'parent',
    'driver'
];

$selected_role =
strtolower(
trim($_GET['role'] ?? $available_roles[0])
);
if (
    !in_array(
        $selected_role,
        ['teacher', 'student', 'parent', 'driver'],
        true
    )
){
    $selected_role = 'teacher';
}

$selected_user_id = (int) ($_GET['user_id'] ?? 0);
$user_overrides = permission_fetch_user_overrides($conn, $selected_user_id);

$users_query = mysqli_query(
    $conn,
    "SELECT u.id, u.full_name, u.email, u.role,
            COALESCE(t.teacher_id, s.student_id, p.parent_id, u.admin_id) AS entity_id
     FROM users u
     LEFT JOIN teachers t ON t.user_id = u.id
     LEFT JOIN students s ON s.user_id = u.id
     LEFT JOIN parents p ON p.user_id = u.id
     ORDER BY u.role ASC, u.full_name ASC"
);

$users_list = [];
while ($row = mysqli_fetch_assoc($users_query)) {
    $users_list[] = $row;
}

$active_tab = ($_GET['tab'] ?? 'roles') === 'users' ? 'users' : 'roles';

$finance_modules = [
    'fees' => ['label' => 'Fees', 'icon' => 'fa-money-bill-wave'],
    'salaries' => ['label' => 'Salaries', 'icon' => 'fa-user-tie'],
    'billing' => ['label' => 'Billing', 'icon' => 'fa-chart-line'],
];

if (isset($_GET['saved'])) {
    $success = 'Permissions updated successfully.';
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo htmlspecialchars($page_title); ?> | MindMerge SmartCampus</title>
<link rel="stylesheet" href="../../assets/css/global.css">
<link rel="stylesheet" href="../../assets/css/layout.css">
<link rel="stylesheet" href="../../assets/css/components.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="../assets/css/material-theme.css">
<style>
.perm-role-picker{
display:flex;
gap:12px;
flex-wrap:wrap;
margin-bottom:20px;
}

.perm-role-btn{
padding:12px 20px;
border-radius:12px;
background:#f8fafc;
font-weight:600;
text-decoration:none;
border:1px solid #e5e7eb;
}

.perm-role-btn.active{
background:var(--primary);
color:#fff;
border-color:var(--primary);
}

.finance-perm-grid{
display:grid;
grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
gap:16px;
margin-bottom:20px;
}

.finance-perm-card{
background:var(--card);
border:1px solid rgba(148,163,184,0.18);
padding:18px;
  border-radius:2px;
    transition:.25s ease;

box-shadow:var(--shadow);
display:flex;
gap:14px;
align-items:center;
}

.finance-perm-card:hover{
    transform:translateY(-5px);
    box-shadow:0 16px 30px rgba(0,0,0,.14);
}

.finance-perm-icon{
    width:60px;
    height:60px;
    min-width:60px;

    display:grid;
    place-items:center;

    border-radius:2px;

    background:#3b82f6;
    color:#fff !important;

    font-size:25px;

    box-shadow:0 12px 24px rgba(0,0,0,.18);
}

.finance-perm-icon i{
    color:#fff !important;
    font-size:25px;
}

.finance-perm-card h3{
margin:0 0 4px;
font-size:16px;
}

.finance-perm-card p{
margin:0;
color:var(--muted);
font-size:13px;
}

.finance-perm-card:nth-child(1) .finance-perm-icon{ background:#10b981; }
.finance-perm-card:nth-child(2) .finance-perm-icon{ background:#ff5722; }
.finance-perm-card:nth-child(3) .finance-perm-icon{ background:#7c3aed; }
.finance-perm-card:nth-child(4) .finance-perm-icon{ background:#06b6d4; }
.finance-perm-card:nth-child(5) .finance-perm-icon{ background:#f59e0b; }
.finance-perm-card:nth-child(6) .finance-perm-icon{ background:#ef4444; }

</style>
</head>
<body>

<div class="app-layout">

<?php include('../../partials/sidebar.php'); ?>

<div class="main-content">

<?php include('../../partials/topbar.php'); ?>

<div class="page-content">

<div class="page-header">
<div>
<h1>Permission Management</h1>
<p>Control module visibility, role permissions, and user-specific overrides.</p>
</div>
</div>

<div class="finance-perm-grid">
<?php foreach ($finance_modules as $module_key => $module_meta) {
    $grant_count = 0;
    $total_count = 0;

    if (isset($grouped_permissions[$module_key])) {
        foreach ($grouped_permissions[$module_key]['permissions'] as $perm) {
            $total_count++;
            if (!empty($role_matrix[$selected_role][(int) $perm['permission_id']])) {
                $grant_count++;
            }
        }
    }
?>
<div class="finance-perm-card">
<div class="finance-perm-icon"><i class="fa-solid <?php echo htmlspecialchars($module_meta['icon']); ?>"></i></div>
<div>
<h3><?php echo htmlspecialchars($module_meta['label']); ?></h3>
<p><?php echo $active_tab === 'roles' ? (int) $grant_count . ' of ' . (int) $total_count . ' actions granted for ' . htmlspecialchars(ucfirst($selected_role)) : 'Finance access can be overridden per user.'; ?></p>
</div>
</div>
<?php } ?>
</div>

<?php if ($success !== '') { ?>
<div class="alert alert-success">
<i class="fa-solid fa-circle-check"></i>
<?php echo htmlspecialchars($success); ?>
</div>
<?php } ?>

<?php if ($error !== '') { ?>
<div class="alert alert-danger">
<i class="fa-solid fa-circle-xmark"></i>
<?php echo htmlspecialchars($error); ?>
</div>
<?php } ?>

<div class="perm-tabs">
<a href="?tab=roles&role=<?php echo urlencode($selected_role); ?>"
   class="perm-tab <?php echo $active_tab === 'roles' ? 'active' : ''; ?>">
<i class="fa-solid fa-users-gear"></i>
Role Permissions
</a>
<a href="?tab=users&user_id=<?php echo $selected_user_id; ?>"
   class="perm-tab <?php echo $active_tab === 'users' ? 'active' : ''; ?>">
<i class="fa-solid fa-user-shield"></i>
User Overrides
</a>
</div>

<?php if ($active_tab === 'roles') { ?>

<div class="dashboard-section">
<div class="section-header">
<h2>Role Permissions</h2>
<p>Admin role always has full access. Configure defaults for other roles.</p>
</div>

<div class="perm-role-picker">
<?php foreach (
[
'teacher' => 'Teacher',
'student' => 'Student',
'parent'  => 'Parent',
'driver'  => 'Driver'
]
as $role_key => $role_label
) { ?>
<a href="?tab=roles&role=<?php echo urlencode($role_key); ?>"
   class="perm-role-btn <?php echo $selected_role === $role_key ? 'active' : ''; ?>">
<?php echo htmlspecialchars($role_label); ?>
</a>
<?php } ?>
</div>

<?php if (canEdit('permissions')) { ?>
<form method="post" action="save.php">
<input type="hidden" name="save_type" value="role">
<input type="hidden" name="role" value="<?php echo htmlspecialchars($selected_role); ?>">

<div class="perm-matrix-wrap">
<table class="custom-table perm-matrix-table">
<thead>
<tr>
<th>Module</th>
<th>View</th>
<th>Create</th>
<th>Edit</th>
<th>Delete</th>
</tr>
</thead>
<tbody>
<?php foreach ($grouped_permissions as $module) {
    if ($module['module_key'] === 'permissions') {
        continue;
    }

    $by_action = [];
    foreach ($module['permissions'] as $perm) {
        $by_action[$perm['action_key']] = $perm;
    }
?>
<tr>
<td class="perm-module-name">
<strong><?php echo htmlspecialchars($module['label']); ?></strong>
<span><?php echo htmlspecialchars($module['module_key']); ?></span>
</td>
<?php foreach (PERMISSION_ACTIONS as $action) {
    if (!isset($by_action[$action])) {
        echo '<td class="perm-na">—</td>';
        continue;
    }
    $perm = $by_action[$action];
    $checked = !empty($role_matrix[$selected_role][(int) $perm['permission_id']]);
?>
<td>
<label class="perm-check">
<input type="checkbox"
       name="permissions[]"
       value="<?php echo (int) $perm['permission_id']; ?>"
       <?php echo $checked ? 'checked' : ''; ?>>
<span class="perm-check-ui"></span>
</label>
</td>
<?php } ?>
</tr>
<?php } ?>
</tbody>
</table>
</div>

<div class="form-actions">
<button type="submit" class="btn btn-primary">
<i class="fa-solid fa-floppy-disk"></i>
Save Role Permissions
</button>
</div>

</form>
<?php } else { ?>
<div class="alert alert-warning">
You have view-only access to permission settings.
</div>
<?php } ?>

</div>

<?php } else { ?>

<div class="dashboard-section">
<div class="section-header">
<h2>User-Specific Overrides</h2>
<p>Override role defaults for individual users. Checked = grant, unchecked override = revoke when saved.</p>
</div>

<form method="get" class="perm-user-select-form">
<input type="hidden" name="tab" value="users">
<div class="form-group">
<label class="form-label">Select User</label>
<select name="user_id" class="form-select" onchange="this.form.submit()">
<option value="0">— Choose a user —</option>
<?php foreach ($users_list as $u) {
    if ($u['role'] === 'admin') {
        continue;
    }
    $label = $u['full_name'] . ' (' . ucfirst($u['role']) . ')';
    if (!empty($u['entity_id'])) {
        $label .= ' — ' . $u['entity_id'];
    }
?>
<option value="<?php echo (int) $u['id']; ?>"
        <?php echo $selected_user_id === (int) $u['id'] ? 'selected' : ''; ?>>
<?php echo htmlspecialchars($label); ?>
</option>
<?php } ?>
</select>
</div>
</form>

<?php if ($selected_user_id > 0) {
    $selected_user = null;
    foreach ($users_list as $u) {
        if ((int) $u['id'] === $selected_user_id) {
            $selected_user = $u;
            break;
        }
    }
?>

<?php if ($selected_user && canEdit('permissions')) { ?>
<form method="post" action="save.php">
<input type="hidden" name="save_type" value="user">
<input type="hidden" name="user_id" value="<?php echo $selected_user_id; ?>">

<div class="perm-user-banner">
<i class="fa-solid fa-user"></i>
<div>
<h3><?php echo htmlspecialchars($selected_user['full_name']); ?></h3>
<p>Role: <?php echo htmlspecialchars(ucfirst($selected_user['role'])); ?>
<?php if (!empty($selected_user['entity_id'])) { ?>
 · ID: <?php echo htmlspecialchars($selected_user['entity_id']); ?>
<?php } ?>
</p>
</div>
</div>

<div class="perm-matrix-wrap">
<table class="custom-table perm-matrix-table">
<thead>
<tr>
<th>Permission</th>
<th>Role Default</th>
<th>Override</th>
</tr>
</thead>
<tbody>
<?php foreach ($grouped_permissions as $module) {
    if ($module['module_key'] === 'permissions') {
        continue;
    }

    foreach ($module['permissions'] as $perm) {
        $pid = (int) $perm['permission_id'];
        $role_default = !empty($role_matrix[$selected_user['role']][$pid]);
        $has_override = array_key_exists($pid, $user_overrides);
        $override_val = $has_override ? ($user_overrides[$pid] ? '1' : '0') : '';
?>
<tr>
<td class="perm-module-name">
<strong><?php echo htmlspecialchars($module['label']); ?></strong>
<span><?php echo htmlspecialchars($perm['label']); ?></span>
</td>
<td>
<span class="perm-effective <?php echo $role_default ? 'granted' : 'denied'; ?>">
<?php echo $role_default ? 'Granted' : 'Denied'; ?>
</span>
</td>
<td>
<select name="user_perm[<?php echo $pid; ?>]" class="form-select perm-override-select">
<option value="" <?php echo $override_val === '' ? 'selected' : ''; ?>>Inherit from role</option>
<option value="1" <?php echo $override_val === '1' ? 'selected' : ''; ?>>Grant</option>
<option value="0" <?php echo $override_val === '0' ? 'selected' : ''; ?>>Deny</option>
</select>
</td>
</tr>
<?php }
} ?>
</tbody>
</table>
</div>

<div class="form-actions">
<button type="submit" class="btn btn-primary">
<i class="fa-solid fa-floppy-disk"></i>
Save User Overrides
</button>
</div>

</form>
<?php } elseif ($selected_user) { ?>
<div class="alert alert-warning">You have view-only access to permission settings.</div>
<?php } ?>

<?php } else { ?>
<div class="empty-state">
<i class="fa-solid fa-user-shield"></i>
<h3>Select a user</h3>
<p>Choose a user above to configure individual permission overrides.</p>
</div>
<?php } ?>

</div>

<?php } ?>

</div>
</div>
</div>

<script src="../../assets/js/common.js"></script>

</body>
</html>
