<?php

require_once __DIR__ . '/_bootstrap.php';

header('Content-Type: application/json; charset=utf-8');

$queries = [
    'registrations' => 'SELECT COUNT(*) AS total FROM sd_club_members WHERE status = "active"',
    'sports' => 'SELECT COUNT(DISTINCT student_id) AS total FROM sd_sport_applications WHERE status = "selected"',
    'achievements' => 'SELECT (SELECT COUNT(*) FROM sd_certificates) + (SELECT COUNT(*) FROM sd_sports_achievements) AS total',
    'ncc' => 'SELECT COUNT(*) AS total FROM sd_ncc_cadets WHERE status = "active"',
    'nss' => 'SELECT COUNT(*) AS total FROM sd_nss_volunteers WHERE status = "active"',
    'pending' => 'SELECT (SELECT COUNT(*) FROM sd_club_applications WHERE status = "pending") + (SELECT COUNT(*) FROM sd_sport_applications WHERE status = "pending") + (SELECT COUNT(*) FROM sd_programme_applications WHERE status = "pending") AS total',
];

$stats = [];
foreach ($queries as $key => $query) {
    $row = sd_admin_rows(sd_admin_stmt($conn, $query))[0] ?? [];
    $stats[$key] = (int) ($row['total'] ?? 0);
}

echo json_encode([
    'success' => true,
    'updated_at' => date(DATE_ATOM),
    'stats' => $stats,
]);
