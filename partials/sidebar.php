
<?php

require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/permissions.php';
require_once __DIR__ . '/../shared/helpers/portal.php';

$current_page = basename($_SERVER['PHP_SELF']);
$uri = $_SERVER['REQUEST_URI'] ?? '';
$menu_groups = permission_portal_menu_groups();
$role = portal_get_role();

$logo_url = permission_role_dashboard_url();

function sidebar_resolve_url(array $config, string $role, string $module_key = ''): string
{
    if ($module_key === 'dashboard' || ($config['label'] ?? '') === 'Dashboard') {
        return permission_role_dashboard_url();
    }

    if ($module_key === 'profile' || ($config['label'] ?? '') === 'Profile') {
        return BASE_URL . 'profile/index.php';
    }

    return BASE_URL . ($config['url'] ?? '#');
}

?>


<div class="sidebar" id="sidebar">

<div class="sidebar-logo">

<a href="<?php echo $logo_url; ?>" class="logo-box sidebar-logo-link" style="text-decoration:none">

<i class="fa-solid fa-graduation-cap"></i>

<h2>MindMerge</h2>

</a>

<button class="menu-close" id="closeSidebar">

<i class="fa-solid fa-xmark"></i>

</button>

<button class="sidebar-collapse-toggle" id="sidebarCollapseToggle" type="button" title="Toggle sidebar">

<i class="fa-solid fa-angles-left"></i>

</button>

</div>

<ul class="sidebar-menu">

<?php foreach ($menu_groups as $group) { ?>

<li class="sidebar-group-label">

<span><?php echo htmlspecialchars($group['label']); ?></span>

</li>

<?php foreach ($group['items'] as $module_key => $config) {
    $is_active = permission_menu_is_active($config, $current_page, $uri,$module_key);

    /* Fix: teacher-attendance also contains attendance */
        if ($module_key === 'attendance' && strpos($uri, '/teacher-attendance/') !== false) {
            $is_active = false;
        }

        if ($module_key === 'teacher_attendance' && strpos($uri, '/teacher-attendance/') !== false) {
            $is_active = true;
        }

    $href = sidebar_resolve_url($config, $role, $module_key);

?>
<li>

<a
href="<?php echo htmlspecialchars($href); ?>"
class="<?php echo $is_active ? 'active' : ''; ?>"
>

<i class="fa-solid <?php echo htmlspecialchars($config['icon'] ?? 'fa-circle'); ?>"></i>

<span><?php echo htmlspecialchars($config['label'] ?? $module_key); ?></span>

</a>

</li>
<?php } ?>

<?php } ?>

</ul>

</div>
