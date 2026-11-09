<?php
require_once __DIR__ . '/_bootstrap.php';
require_once __DIR__ . '/_layout.php';

$report = $_GET['report'] ?? 'overview';
if (isset($_GET['download']) && $_GET['download'] === 'csv') {
    $rows = [];
    if ($report === 'certificates') {
        $rows = sd_admin_rows(sd_admin_stmt($conn, 'SELECT u.full_name, s.student_id, c.category, c.title, c.achievement_date, c.verification_status FROM sd_certificates c JOIN students s ON s.id = c.student_id JOIN users u ON u.id = s.user_id ORDER BY u.full_name'));
    } elseif ($report === 'programmes') {
        $rows = sd_admin_rows(sd_admin_stmt($conn, 'SELECT u.full_name, s.student_id, a.programme_key, a.status, a.applied_at FROM sd_programme_applications a JOIN students s ON s.id = a.student_id JOIN users u ON u.id = s.user_id ORDER BY a.programme_key, u.full_name'));
    } else {
        $rows = sd_admin_rows(sd_admin_stmt($conn, 'SELECT u.full_name, s.student_id, c.name club_name, cm.role, cm.joined_on FROM sd_club_members cm JOIN sd_clubs c ON c.club_id = cm.club_id JOIN students s ON s.id = cm.student_id JOIN users u ON u.id = s.user_id ORDER BY c.name, u.full_name'));
    }
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="student-development-' . $report . '.csv"');
    $out = fopen('php://output', 'w');
    if ($rows) { fputcsv($out, array_keys($rows[0])); foreach ($rows as $row) { fputcsv($out, $row); } }
    fclose($out); exit;
}
$clubMembers = (int)(sd_admin_rows(sd_admin_stmt($conn, 'SELECT COUNT(*) total FROM sd_club_members WHERE status = "active"'))[0]['total'] ?? 0);
$sportPlayers = (int)(sd_admin_rows(sd_admin_stmt($conn, 'SELECT COUNT(DISTINCT student_id) total FROM sd_sport_applications WHERE status = "selected"'))[0]['total'] ?? 0);
$certificates = (int)(sd_admin_rows(sd_admin_stmt($conn, 'SELECT COUNT(*) total FROM sd_certificates'))[0]['total'] ?? 0);
$programmes = (int)(sd_admin_rows(sd_admin_stmt($conn, 'SELECT COUNT(*) total FROM sd_programme_applications'))[0]['total'] ?? 0);
sd_admin_header('Development Reports', 'Export accurate participation, certification and programme records for institutional review.', 'reports');
?>
<section class="sd-admin-grid"><?php foreach([[$clubMembers,'Club memberships'],[$sportPlayers,'Selected sports players'],[$certificates,'Certificates'],[$programmes,'Programme applications']] as $card): ?><div class="sd-admin-card"><h3><?php echo $card[1]; ?></h3><div class="sd-admin-value"><?php echo number_format($card[0]); ?></div></div><?php endforeach; ?></section>
<section class="sd-admin-card"><h2>CSV exports</h2><p class="sd-admin-muted">Exports use current database records and contain no hardcoded values.</p><div class="sd-admin-actions"><a class="btn btn-primary" href="?report=clubs&download=csv">Export club members</a><a class="btn btn-primary" href="?report=certificates&download=csv">Export certificates</a><a class="btn btn-primary" href="?report=programmes&download=csv">Export NCC/NSS applications</a></div></section>
<?php sd_admin_footer(); ?>
