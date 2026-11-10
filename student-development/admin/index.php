<?php

require_once __DIR__ . '/_bootstrap.php';
require_once __DIR__ . '/_layout.php';

$stats = [];
$queries = [
    'registrations' => 'SELECT COUNT(*) AS total FROM sd_club_members WHERE status = "active"',
    'sports' => 'SELECT COUNT(DISTINCT student_id) AS total FROM sd_sport_applications WHERE status = "selected"',
    'achievements' => 'SELECT (SELECT COUNT(*) FROM sd_certificates) + (SELECT COUNT(*) FROM sd_sports_achievements) AS total',
    'ncc' => 'SELECT COUNT(*) AS total FROM sd_ncc_cadets WHERE status = "active"',
    'nss' => 'SELECT COUNT(*) AS total FROM sd_nss_volunteers WHERE status = "active"',
];

foreach ($queries as $key => $query) {
    $row = sd_admin_rows(sd_admin_stmt($conn, $query))[0] ?? [];
    $stats[$key] = (int) ($row['total'] ?? 0);
}

$clubCount = (int) (sd_admin_rows(sd_admin_stmt($conn, 'SELECT COUNT(*) AS total FROM sd_clubs WHERE status = "active"'))[0]['total'] ?? 0);
$sportCount = (int) (sd_admin_rows(sd_admin_stmt($conn, 'SELECT COUNT(*) AS total FROM sd_sports WHERE status = "active"'))[0]['total'] ?? 0);
$pending = (int) (sd_admin_rows(sd_admin_stmt($conn, 'SELECT (SELECT COUNT(*) FROM sd_club_applications WHERE status = "pending") + (SELECT COUNT(*) FROM sd_sport_applications WHERE status = "pending") + (SELECT COUNT(*) FROM sd_programme_applications WHERE status = "pending") AS total'))[0]['total'] ?? 0);

sd_admin_header('Student Development Management', 'Manage student activities, sports participation, certifications, achievements, NCC and NSS programmes.', 'index');
?>
<section class="sd-admin-grid">
    <?php foreach ([
        ['registrations', 'Club registrations', 'fa-people-group', 'clubs.php'],
        ['sports', 'Sports participants', 'fa-futbol', 'sports.php'],
        ['achievements', 'Certifications & achievements', 'fa-award', 'achievements.php'],
        ['ncc', 'NCC registered students', 'fa-shield-halved', 'ncc.php'],
        ['nss', 'NSS registered students', 'fa-hands-helping', 'nss.php'],
    ] as [$key, $label, $icon, $href]): ?>
        <a class="sd-admin-card" href="<?php echo $href; ?>" style="text-decoration:none;color:inherit">
            <div class="sd-admin-muted"><i class="fa-solid <?php echo $icon; ?>"></i> <?php echo sd_admin_e($label); ?></div>
            <div class="sd-admin-value" data-live-stat="<?php echo sd_admin_e($key); ?>"><?php echo number_format($stats[$key]); ?></div>
            <span class="sd-admin-muted">Open management module <i class="fa-solid fa-arrow-right"></i></span>
        </a>
    <?php endforeach; ?>
</section>

<section class="sd-admin-grid">
    <div class="sd-admin-card"><h3>Active clubs</h3><div class="sd-admin-value"><?php echo number_format($clubCount); ?></div></div>
    <div class="sd-admin-card"><h3>Active sports</h3><div class="sd-admin-value"><?php echo number_format($sportCount); ?></div></div>
    <div class="sd-admin-card"><h3>Pending registrations</h3><div class="sd-admin-value" data-live-stat="pending"><?php echo number_format($pending); ?></div><span class="sd-admin-muted" data-live-updated>Live data</span></div>
</section>

<section class="sd-admin-card">
    <h2>Management workspace</h2>
    <p class="sd-admin-muted">Use the dedicated modules above to search records, review registrations, maintain programmes and track student participation. The student and parent portals remain scoped to their own authorized development records.</p>
    <div class="sd-admin-actions">
        <a class="btn btn-primary" href="clubs.php"><i class="fa-solid fa-people-group"></i> Manage activities</a>
        <a class="btn btn-outline-primary" href="achievements.php"><i class="fa-solid fa-certificate"></i> Review certifications</a>
    </div>
</section>
<script>
(() => {
    const update = async () => {
        if (document.hidden) return;
        try {
            const response = await fetch('live.php', {headers: {'Accept': 'application/json'}, credentials: 'same-origin'});
            const payload = await response.json();
            if (!payload.success) return;
            Object.entries(payload.stats).forEach(([key, value]) => {
                document.querySelectorAll(`[data-live-stat="${key}"]`).forEach((element) => {
                    element.textContent = Number(value).toLocaleString();
                });
            });
            const stamp = document.querySelector('[data-live-updated]');
            if (stamp) stamp.textContent = 'Updated just now';
        } catch (error) {
            const stamp = document.querySelector('[data-live-updated]');
            if (stamp) stamp.textContent = 'Live update unavailable';
        }
    };
    document.addEventListener('visibilitychange', update);
    window.setInterval(update, 30000);
})();
</script>
<?php sd_admin_footer(); ?>
